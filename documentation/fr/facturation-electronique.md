# Facturation électronique (opérationnel)

**EN:** [electronic-invoicing.md](../en/electronic-invoicing.md)

> **Pas en production.** SuperPDP est un POC. La préparation du go-live est en cours ; **ne pas** poser `E_INVOICE_ALLOW_PRODUCTION=true` ni brancher les locataires de vrp.xdm-consulting.fr sur la PA live tant que Marc ne l’a pas autorisé.

Guide opérationnel de la couche PA **déjà implémentée** et du POC SuperPDP. Stratégie, phases et contexte réglementaire : [roadmap](roadmap-facturation-electronique.md).

## Intention

VRP prépare les factures (planning, PDF, identifiants légaux) et peut **émettre une e-facture structurée** vers une Plateforme agréée (PA). Le domaine ne parle qu’à `ElectronicInvoicePlatform` ; SuperPDP est le premier adaptateur.

## Architecture (code actuel)

```text
Trésorerie → Factures
  POST /invoice/{invoice}/submit-electronic
    → ElectronicInvoiceService::submit()
        → ElectronicInvoiceValidator
        → SuperPdpPlatform::submitOutbound()
            → ElectronicInvoiceCiiBuilder (XML CII EN 16931)
            → SuperPdpClient::convertInvoice(cii → factur-x)
            → SuperPdpClient::sendInvoicePdf(...)   # PDF Factur-X issu du convert, pas le PDF TCPDF
        → statut facture = transmitted + pdp_reference

POST /webhooks/e-invoice/superpdp  (CSRF exclu)
  → verifyWebhook (HMAC) → parseWebhook → applyEvent
```

| Élément | Chemin |
|---------|--------|
| Contrat | `app/Contracts/ElectronicInvoicePlatform.php` |
| Binding | `app/Providers/ElectronicInvoicingServiceProvider.php` |
| Orchestration | `app/Services/ElectronicInvoicing/ElectronicInvoiceService.php` |
| Validateur | `app/Services/ElectronicInvoicing/ElectronicInvoiceValidator.php` |
| Builder CII | `app/Services/ElectronicInvoicing/ElectronicInvoiceCiiBuilder.php` |
| Driver Null | `app/Platforms/NullElectronicInvoicePlatform.php` |
| SuperPDP | `app/Platforms/SuperPdp/` |
| Config | `config/electronic-invoicing.php` |
| Bouton UI | `resources/views/components/table-invoices.blade.php` |

Drivers : `null` (défaut si `E_INVOICE_PLATFORM` absent/autre) ou `superpdp`. B2Brouter **n’est pas** encore implémenté. Le contrat codé se limite à l’émission + webhook (`isConfigured`, `submitOutbound`, `parseWebhook`, `verifyWebhook`) — pas d’onboarding entreprise ni de fetch inbound.

**Verrou production :** `E_INVOICE_ALLOW_PRODUCTION` vaut `false` par défaut. `SUPERPDP_ENV` vaut `sandbox` par défaut. Si `SUPERPDP_ENV=production` sans le flag, `isConfigured()` est faux (bouton masqué ; `superpdp:send-test` refusé). `superpdp:test` peut encore vérifier l’OAuth et affiche un avertissement.

**Interrupteur locataire :** `companies.electronic_invoicing_enabled` vaut **false** par défaut. Le bouton **e** (Trésorerie et fiche école) exige la PA process **et** ce flag. Runbook go-live : [facturation-electronique-go-live.md](facturation-electronique-go-live.md).

## Configuration

```env
# Laisser absent sur les locataires live
# E_INVOICE_PLATFORM=superpdp
E_INVOICE_ALLOW_PRODUCTION=false
SUPERPDP_ENV=sandbox
SUPERPDP_BASE_URL=https://api.superpdp.tech

# credentials production (inutilisés tant que le verrou est fermé)
SUPERPDP_CLIENT_ID=
SUPERPDP_CLIENT_SECRET=

# credentials sandbox (si SUPERPDP_ENV=sandbox)
SUPERPDP_SANDBOX_CLIENT_ID=
SUPERPDP_SANDBOX_CLIENT_SECRET=

# optionnel : bearer token (sans OAuth)
# SUPERPDP_ACCESS_TOKEN=

# obligatoire pour les callbacks de statut, sauf secret saisi en super-admin → Facturation électronique
# SUPERPDP_WEBHOOK_SECRET=
```

Auth : flux `client_credentials` vers `POST /oauth2/token`, token mis en cache (`superpdp.access_token.{env}.{md5(client_id)}`, TTL = `expires_in − 60s`). Le choix sandbox / production passe par `SuperPdpConfig::activeCredentials()`.

Vérifier la connexion :

```bash
php artisan superpdp:test
```

Commandes utiles :

| Commande | Rôle |
|----------|------|
| `php artisan superpdp:send-test` | Envoie une facture sandbox générée par SuperPDP |
| `php artisan superpdp:send-test --invoice={id}` | Soumet une facture VRP prête |
| `php artisan superpdp:setup-tricatel` | Prépare l’école acheteur sandbox (adresse PEPPOL) |
| `php artisan superpdp:go-live-check` | Checklist go-live (aucun secret affiché) |

## Parcours d’émission

1. Créer une facture → statut **`ready`** (`InvoiceController::store`).
2. PDF présent sur disque (`invoices/{bill_prefix}{id}.pdf`). Absent = blocage.
3. Bouton **e** (mode **Édition** uniquement) sur **Trésorerie → Factures** et **fiche école** (`school/show`) si la PA est configurée **et** l’opt-in société est on, que la facture est **impayée**, et que le statut est **`ready`**. Masqué si brouillon, payée, `transmitted`, `accepted` ou `rejected`.
4. `POST invoice.submitElectronic` valide puis soumet. Pas de promotion automatique `draft` → `ready`.
5. Succès : statut **`transmitted`**, `pdp_reference` renseigné, `rejection_reason` effacé.

### Règles de validation (bloquantes)

| Règle | Source |
|-------|--------|
| Statut `ready` | `invoices.electronic_invoice_status` |
| Facture non payée | `paid_at` null |
| Date de facture | `bill_date` |
| Montant > 0 | `amount` |
| Émetteur : SIREN (9 chiffres) ou SIRET (14) + adresse | `companies` |
| Client : SIREN ou SIRET (mêmes formats) + adresse | `schools` |
| PDF en stockage | `Storage::exists(...)` |

Le suivi **payée** (`paid_at`) reste indépendant du statut e-facture.

### Payload

L’envoi utilise **CII XML → Factur-X** via l’API convert SuperPDP. Le PDF TCPDF reste pour l’affichage VRP ; ce n’est **pas** le payload structuré.

Les lignes viennent de `Tools::getInvoiceDetails()` (lignes planning de type `T`). Si aucune ne qualifie, le builder retombe sur `invoice.amount / 1.2` en une ligne C62. La TVA du builder CII est actuellement **fixe à 20 %**. L’échéance CII est **date d’émission + 1 jour**.

En sandbox, sans `electronic_address` sur l’école, un routage par défaut (`SUPERPDP_SANDBOX_BUYER_*`) peut être injecté. Sinon l’adresse électronique de l’école est utilisée (`CiiTradeParty::fromSchool`).

## Webhooks

| Élément | Valeur |
|---------|--------|
| Route | `POST /webhooks/e-invoice/{platform}` — seul `superpdp` accepté (autre plateforme → 404) |
| CSRF | Exclu dans `VerifyCsrfToken` (`webhooks/e-invoice/*`) |
| Auth | HMAC-SHA256 du corps brut ; en-têtes `X-SuperPDP-Signature` ou `X-Webhook-Signature`. Secret : **super-admin → Facturation électronique** d’abord (chiffré), puis repli optionnel `SUPERPDP_WEBHOOK_SECRET` |
| Succès | HTTP 204 |

Mapping des statuts (sous-chaîne dans `status` / `status_code`) :

| Indice payload | `PlatformEventType` | Mise à jour facture |
|----------------|---------------------|---------------------|
| reject / refus | `outbound.rejected` | `rejected` + motif |
| accept / valid | `outbound.accepted` | `accepted` |
| autre | `outbound.submitted` | `transmitted` |

Recherche facture : `pdp_reference` d’abord, puis partie numérique de `external_id`. La réception inbound (`inbound.received`) n’est **pas** encore persistée.

## Contraintes et pièges

- Sans `E_INVOICE_PLATFORM=superpdp` et credentials **sandbox** valides, le bouton UI reste masqué. La colonne de statut reste visible.
- `SUPERPDP_ENV=production` sans `E_INVOICE_ALLOW_PRODUCTION=true` masque aussi le bouton et bloque `superpdp:send-test`.
- SIREN/adresse ou PDF manquant → flash danger + liste d’erreurs.
- Webhook sans secret HMAC (UI super-admin ou `SUPERPDP_WEBHOOK_SECRET`) → toujours **401**. Facture introuvable → **204** (journalisé). Erreur inattendue → **500**.
- TVA du builder CII fixée à **20 %** (indépendante des taux société / cours).
- Pas d’UI de réception fournisseurs.
- L’émission exige **à la fois** le verrou process et l’opt-in société. Allumer l’env production sans opt-in n’émet pour personne.
- Journaux : `storage/logs/e-invoice.log`. Mail optionnel `E_INVOICE_ALERT_EMAIL`.
- Cut-over production : [facturation-electronique-go-live.md](facturation-electronique-go-live.md).

## Voir aussi

- [Roadmap — facturation électronique](roadmap-facturation-electronique.md)
- [V2 — trésorerie & rapprochement bancaire](v2-tresorerie-rapprochement-bancaire.md)
- [V2 — facturation par école](v2-facturation-par-ecole.md)
- [Configuration](configuration.md)
