# Go-live facturation électronique (SuperPDP / IONOS)

**EN:** [electronic-invoicing-go-live.md](../en/electronic-invoicing-go-live.md)

Runbook **production** pour `https://vrp.xdm-consulting.fr`. Le dépôt ne contient **jamais** `E_INVOICE_ALLOW_PRODUCTION=true` ni de credentials. Le secret HMAC webhook se saisit dans l’UI super-admin (chiffré en base) ; `.env` `SUPERPDP_WEBHOOK_SECRET` reste un repli optionnel.

Couche métier : [facturation-electronique.md](facturation-electronique.md). Déploiement fichiers : [mise-en-production-sftp.md](mise-en-production-sftp.md).

## Ce que le code fait vs ce que Marc fait

| Dans le code | Chez SuperPDP | Sur IONOS (serveur) |
|--------------|---------------|---------------------|
| Verrou `E_INVOICE_ALLOW_PRODUCTION` **false** par défaut | Créer une **Application production** (OAuth) | Éditer `.env` **à la main** (jamais uploadé par le script SFTP) |
| Interrupteur **par société** `electronic_invoicing_enabled` (défaut **off**) | Secret HMAC webhook | `php artisan migrate` **ou** SQL ci-dessous |
| Secret HMAC saisissable en **super-admin → Facturation électronique** (chiffré) | Coller l’URL webhook affichée dans VRP | SSL HTTPS + éventuellement `TRUSTED_PROXIES=*` |
| URL webhook calculée (`APP_URL` ou `E_INVOICE_WEBHOOK_URL`) | Coller `https://vrp.xdm-consulting.fr/webhooks/e-invoice/superpdp` | `E_INVOICE_ALERT_EMAIL` si on veut une alerte |
| Logs `storage/logs/e-invoice.log` + mail optionnel | Compte PA lié au SIREN émetteur | Poser le verrou **true** seulement au moment du go-live |
| `php artisan superpdp:go-live-check` (aucun secret affiché) | Vérifier le profil `/companies/me` | |

Le script `./scripts/deploy-xdm-vrp.sh` **n’envoie pas** `.env` et **ne lance pas** les migrations.

## SQL à exécuter sur la BDD prod (avant ou avec le code)

```sql
ALTER TABLE `companies`
  ADD `electronic_invoicing_enabled` TINYINT(1) NOT NULL DEFAULT 0;

CREATE TABLE `platform_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `superpdp_webhook_secret` text NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Toutes les sociétés restent **off**. Activer seulement le locataire pilote (UI **Mon entreprise** ou super-admin).

Le secret HMAC se saisit ensuite dans **Administration plateforme → Facturation électronique** (jamais le secret en clair dans Git).

## `.env` serveur (secrets hors Git)

Ne **pas** copier les valeurs dans le dépôt. Sur IONOS uniquement :

```env
APP_URL=https://vrp.xdm-consulting.fr
TRUSTED_PROXIES=*

E_INVOICE_PLATFORM=superpdp
SUPERPDP_ENV=production
E_INVOICE_ALLOW_PRODUCTION=false
SUPERPDP_BASE_URL=https://api.superpdp.tech
SUPERPDP_CLIENT_ID=          # Application production SuperPDP
SUPERPDP_CLIENT_SECRET=
# Optionnel : repli si aucun secret n’est enregistré dans super-admin → Facturation électronique
# SUPERPDP_WEBHOOK_SECRET=
E_INVOICE_WEBHOOK_URL=https://vrp.xdm-consulting.fr/webhooks/e-invoice/superpdp
E_INVOICE_REQUIRE_HTTPS_WEBHOOKS=false
E_INVOICE_ALERT_EMAIL=       # optionnel
```

`E_INVOICE_ALLOW_PRODUCTION=false` **tant que** OAuth, webhook HTTPS et opt-in société ne sont pas validés.

Si Laravel ne voit pas HTTPS (webhook « HTTP » dans les logs) : garder `TRUSTED_PROXIES=*` (TLS chez l’hébergeur) puis passer `E_INVOICE_REQUIRE_HTTPS_WEBHOOKS=true`.

**Cache config IONOS :** si `bootstrap/cache/config.php` existe, un changement de `.env` est **ignoré** tant qu’on ne régénère pas le cache **sur le serveur** (`php artisan config:clear` / `config:cache` **en SSH**, jamais un `config.php` local uploadé).

## Chez SuperPDP

1. Compte PA production (pas l’Application sandbox).
2. **Paramètres → Applications** : nouvelle app **production**, `client_id` / `client_secret`.
3. Webhook : `POST https://vrp.xdm-consulting.fr/webhooks/e-invoice/superpdp` (copier l’URL affichée dans **super-admin → Facturation électronique**)
4. En-tête attendu : `X-SuperPDP-Signature` (HMAC-SHA256 du corps brut) avec le secret saisi dans VRP (ou le repli `.env` `SUPERPDP_WEBHOOK_SECRET`).
5. SIREN du profil SuperPDP = SIREN **Mon entreprise** VRP.

## Séquence go-live réel

1. Approuver et merger le PR, déployer le code (`./scripts/deploy-xdm-vrp.sh --upload`).
2. ALTER / migrate sur la BDD prod (`electronic_invoicing_enabled` + table `platform_settings`).
3. Remplir `.env` **sans** ouvrir le verrou (`E_INVOICE_ALLOW_PRODUCTION=false`). OAuth reste dans `.env`.
4. Super-admin → **Facturation électronique** : coller le secret HMAC SuperPDP (le champ reste masqué après enregistrement).
5. `php artisan superpdp:test` — OAuth OK, env API = production, URL webhook HTTPS.
6. `php artisan superpdp:go-live-check` — secret présent (interface super-admin), **aucune** valeur secrète affichée, verrou encore fermé.
7. Activer l’interrupteur **une** société (XDM) dans VRP.
8. Compléter SIREN/SIRET/adresses société + clients B2B.
9. Quand Marc est prêt : sur le serveur **seulement**, `E_INVOICE_ALLOW_PRODUCTION=true` puis vider/regénérer le cache config.
10. Une facture pilote à faible enjeu → Trésorerie → bouton e → statut `transmitted` puis webhook `accepted` / `rejected`.
11. Suivre `storage/logs/e-invoice.log`.

Pour **revenir en arrière** : `E_INVOICE_ALLOW_PRODUCTION=false` et/ou décocher l’interrupteur société. Ne pas laisser le verrou ouvert si d’autres locataires sont opt-in.

## Checklist Marc (jour J)

- [ ] PR mergée et code sur IONOS
- [ ] Colonne `electronic_invoicing_enabled` et table `platform_settings` en base
- [ ] Application SuperPDP **production** créée
- [ ] `.env` IONOS : credentials OAuth (pas dans Git) ; verrou encore `false`
- [ ] Secret HMAC saisi dans **super-admin → Facturation électronique** (pas le secret en clair dans Git)
- [ ] `APP_URL=https://vrp.xdm-consulting.fr`
- [ ] Webhook enregistré chez SuperPDP
- [ ] `superpdp:test` OK
- [ ] `superpdp:go-live-check` : HTTPS oui, secret oui, verrou fermé puis ouvert **volontairement**
- [ ] Une seule société opt-in
- [ ] Facture pilote réelle suivie jusqu’à `accepted` ou `rejected`
