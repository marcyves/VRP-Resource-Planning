# Configuration — terminologie

**EN:** [configuration.md](../en/configuration.md)

## Variables d’environnement

| Variable | Défaut | Rôle |
|----------|--------|------|
| `APP_LOCALE` | `fr` | Langue de base (`fr`, `en`, `it`) |
| `TERMINOLOGY_PROFILE` | `education` | Profil pour invités / sans entreprise |
| `VRP_ALLOW_REGISTRATION` | `false` | Inscription publique `/register` |
| `VRP_ACCOUNT_REQUEST_EMAIL` | `MAIL_FROM_ADDRESS` si la clé est **absente** | Boîte de `/demande-acces`. Un `VRP_ACCOUNT_REQUEST_EMAIL=` vide dans `.env` **ne** bascule **pas** (chaîne vide = valeur définie) : le formulaire réussit, mais `AccountRequestController` journalise une `RuntimeException` et n’envoie pas de mail |
| `E_INVOICE_PLATFORM` | *(absent)* | `superpdp` pour SuperPDP ; sinon driver Null. Laisser absent sur les locataires live. |
| `E_INVOICE_ALLOW_PRODUCTION` | `false` | Verrou : l’émission SuperPDP production reste off tant que ce n’est pas `true` (`.env` serveur uniquement ; ne jamais committer `true`) |
| `E_INVOICE_WEBHOOK_URL` | `APP_URL` + chemin | URL webhook publique à coller chez SuperPDP |
| `E_INVOICE_REQUIRE_HTTPS_WEBHOOKS` | `false` | Rejeter `POST /webhooks/e-invoice/*` en HTTP (400). Activer sur IONOS après TLS + `TRUSTED_PROXIES` |
| `E_INVOICE_ALERT_EMAIL` | *(absent)* | Mail ops optionnel si émission/webhook en échec |
| `TRUSTED_PROXIES` | *(absent)* | `*` sur IONOS pour que Laravel voie le HTTPS |
| `LOGIN_STATS_GEO_MMDB` | `storage/app/geoip/GeoLite2-City.mmdb` | Fichier MaxMind GeoLite2 City (optionnel, ~60 Mo, **ne pas committer**). Compte gratuit MaxMind → télécharger GeoLite2-City → coller le `.mmdb` à cet emplacement. Absent = repli HTTP ou « géoloc inconnue » |
| `LOGIN_STATS_GEO_HTTP` | `true` | Repli HTTPS sans clé (`ipwho.is`) si le MMDB est absent. Timeout court ; n'interrompt jamais le login |
| `LOGIN_STATS_GEO_HTTP_URL` | `https://ipwho.is/{ip}` | Modèle d'URL sans clé API. `{ip}` est remplacé |
| `LOGIN_STATS_GEO_HTTP_TIMEOUT` | `1.5` | Secondes |
| `LOGIN_STATS_GEO_CACHE_TTL` | `2592000` (30 jours) | Durée de cache d'un libellé d'IP publique résolu (`login-stats-geo:{ip}`) |
| `LOGIN_STATS_GEO_FAILURE_CACHE_TTL` | `3600` | Durée de cache d'une recherche en échec, pour ne pas rappeler un résolveur mort à chaque connexion |
| `SUPERPDP_ENV` | `sandbox` | `sandbox` ou `production` (choix des credentials OAuth) |
| `SUPERPDP_CLIENT_ID` / `SUPERPDP_CLIENT_SECRET` | — | OAuth production (`client_credentials`) |
| `SUPERPDP_SANDBOX_CLIENT_ID` / `SUPERPDP_SANDBOX_CLIENT_SECRET` | — | OAuth sandbox si `SUPERPDP_ENV=sandbox` |
| `SUPERPDP_ACCESS_TOKEN` | — | Bearer optionnel (sans OAuth) |
| `SUPERPDP_WEBHOOK_SECRET` | — | Repli HMAC optionnel ; préférer super-admin → Facturation électronique. Secret absent → webhook **401** |

Exemple `.env.example` :

```env
APP_LOCALE=fr
TERMINOLOGY_PROFILE=consulting
VRP_ALLOW_REGISTRATION=false
# VRP_ACCOUNT_REQUEST_EMAIL=ops@example.com
# E_INVOICE_PLATFORM=superpdp
# E_INVOICE_ALLOW_PRODUCTION=false
# SUPERPDP_ENV=sandbox
```

Runbook facturation électronique : [facturation-electronique.md](facturation-electronique.md).

Statistiques de connexion : le repli HTTP envoie l'IP du visiteur à l'hôte de `LOGIN_STATS_GEO_HTTP_URL`. Comportement, filtres et piège `TRUSTED_PROXIES` : [administration de la plateforme](administration-plateforme.md#statistiques-de-connexion).

> Connecté : `companies.terminology_profile` **prime** sur `TERMINOLOGY_PROFILE`.

## Super administrateur plateforme

1. `php artisan migrate`
2. `php artisan vrp:create-super-admin admin@example.com "Super Admin"`
3. Connexion → `/super-admin/companies` — création entreprise + administrateur

Le super admin n'a pas d'entreprise rattachée ; les utilisateurs métier ont un `company_id` obligatoire.

Runbook détaillé : [Administration plateforme](administration-plateforme.md).

## Fiche entreprise (UI)

1. Admin ou éditeur
2. **Mon entreprise** → **Modifier**
3. **Contexte métier** : Formation ou Clients & projets
4. Enregistrer

## Instance mono-métier

| Besoin | Réglage |
|--------|---------|
| Consulting FR | `APP_LOCALE=fr` + profil `consulting` |
| Consulting EN | `APP_LOCALE=en` + profil `consulting` |
| Formation | profil `education` |

## Fichiers

- `config/terminology.php`, `config/app.php`, `config/vrp.php`, `config/electronic-invoicing.php`, `config/login_stats.php`, `.env.example`

## Liens

- [Phase 1 — terminologie](phase-1-terminologie.md)
- [Administration plateforme](administration-plateforme.md) — `/demande-acces` vs `/register`
- [Libellés consulting](libelles-consulting.md)
- [Facturation électronique](facturation-electronique.md)
