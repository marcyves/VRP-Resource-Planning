# Facturation électronique (opérationnel)

**EN:** [electronic-invoicing.md](../en/electronic-invoicing.md)

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
            → SuperPdpClient::sendInvoicePdf(...)
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

## Configuration

```env
E_INVOICE_PLATFORM=superpdp
SUPERPDP_ENV=sandbox          # ou production
SUPERPDP_BASE_URL=https://api.superpdp.tech

# credentials production
SUPERPDP_CLIENT_ID=
SUPERPDP_CLIENT_SECRET=

# credentials sandbox (si SUPERPDP_ENV=sandbox)
SUPERPDP_SANDBOX_CLIENT_ID=
SUPERPDP_SANDBOX_CLIENT_SECRET=

# optionnel : bearer token (sans OAuth)
# SUPERPDP_ACCESS_TOKEN=

# optionnel : secret HMAC webhook
# SUPERPDP_WEBHOOK_SECRET=
```

Auth : flux `client_credentials` vers `POST /oauth2/token`, token mis en cache (`superpdp.access_token.{md5(client_id)}`, TTL = `expires_in − 60s`). Le choix sandbox / production passe par `SuperPdpConfig::activeCredentials()`.

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

## Parcours d’émission

1. Créer une facture → statut **`ready`** (`InvoiceController::store`).
2. PDF présent sur disque (`invoices/{bill_prefix}{id}.pdf`). Absent = blocage.
3. **Trésorerie → Factures** : le bouton e-facture n’apparaît que si `platformConfigured()` est vrai **et** le statut est `ready`.
4. `POST invoice.submitElectronic` valide puis soumet.
5. Succès : statut **`transmitted`**, `pdp_reference` renseigné, `rejection_reason` effacé.

### Règles de validation (bloquantes)

| Règle | Source |
|-------|--------|
| Statut `ready` | `invoices.electronic_invoice_status` |
| Facture non payée | `paid_at` null |
| Émetteur : SIREN + adresse complète | `companies` (`siren`, `address`, `city`, `zip`) |
| Client : SIREN ou SIRET + adresse | `schools` |
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
| Auth | HMAC-SHA256 du corps brut ; en-têtes `X-SuperPDP-Signature` ou `X-Webhook-Signature` |
| Succès | HTTP 204 |

Mapping des statuts (sous-chaîne dans `status` / `status_code`) :

| Indice payload | `PlatformEventType` | Mise à jour facture |
|----------------|---------------------|---------------------|
| reject / refus | `outbound.rejected` | `rejected` + motif |
| accept / valid | `outbound.accepted` | `accepted` |
| autre | `outbound.submitted` | `transmitted` |

Recherche facture : `pdp_reference` d’abord, puis partie numérique de `external_id`. La réception inbound (`inbound.received`) n’est **pas** encore persistée.

## Contraintes et pièges

- Sans `E_INVOICE_PLATFORM=superpdp` et credentials valides, le bouton UI reste masqué.
- SIREN/adresse ou PDF manquant → flash danger + liste d’erreurs.
- Webhook sans `SUPERPDP_WEBHOOK_SECRET` → toujours **401**.
- TVA du builder CII fixée à **20 %** (indépendante des taux société / cours).
- Pas d’UI de réception fournisseurs.

## Voir aussi

- [Roadmap — facturation électronique](roadmap-facturation-electronique.md)
- [V2 — trésorerie & rapprochement bancaire](v2-tresorerie-rapprochement-bancaire.md)
- [V2 — facturation par école](v2-facturation-par-ecole.md)
- [Configuration](configuration.md)
