# Electronic invoicing (operational)

**FR:** [facturation-electronique.md](../fr/facturation-electronique.md)

Operational guide for the **implemented** PA-agnostic layer and SuperPDP POC. Strategy, phases, and regulatory context live in the [roadmap](roadmap-electronic-invoicing.md).

## Intent

VRP prepares invoices (planning, PDF, legal identifiers) and can **submit structured e-invoices** to a certified Plateforme agréée (PA). Domain code talks only to `ElectronicInvoicePlatform`; SuperPDP is the first adapter.

## Architecture (as coded)

```text
Treasury → Factures
  POST /invoice/{invoice}/submit-electronic
    → ElectronicInvoiceService::submit()
        → ElectronicInvoiceValidator
        → SuperPdpPlatform::submitOutbound()
            → ElectronicInvoiceCiiBuilder (CII EN 16931 XML)
            → SuperPdpClient::convertInvoice(cii → factur-x)
            → SuperPdpClient::sendInvoicePdf(...)
        → invoice status = transmitted + pdp_reference

POST /webhooks/e-invoice/superpdp  (CSRF excluded)
  → verifyWebhook (HMAC) → parseWebhook → applyEvent
```

| Piece | Path |
|-------|------|
| Contract | `app/Contracts/ElectronicInvoicePlatform.php` |
| Binding | `app/Providers/ElectronicInvoicingServiceProvider.php` |
| Orchestration | `app/Services/ElectronicInvoicing/ElectronicInvoiceService.php` |
| Validator | `app/Services/ElectronicInvoicing/ElectronicInvoiceValidator.php` |
| CII builder | `app/Services/ElectronicInvoicing/ElectronicInvoiceCiiBuilder.php` |
| Null driver | `app/Platforms/NullElectronicInvoicePlatform.php` |
| SuperPDP | `app/Platforms/SuperPdp/` |
| Config | `config/electronic-invoicing.php` |
| UI submit | `resources/views/components/table-invoices.blade.php` |

Drivers: `null` (default when `E_INVOICE_PLATFORM` is unset/other) or `superpdp`. B2Brouter is **not** implemented yet.

## Configuration

```env
E_INVOICE_PLATFORM=superpdp
SUPERPDP_ENV=sandbox          # or production
SUPERPDP_BASE_URL=https://api.superpdp.tech

# production credentials
SUPERPDP_CLIENT_ID=
SUPERPDP_CLIENT_SECRET=

# sandbox credentials (used when SUPERPDP_ENV=sandbox)
SUPERPDP_SANDBOX_CLIENT_ID=
SUPERPDP_SANDBOX_CLIENT_SECRET=

# optional: skip OAuth and use a bearer token
# SUPERPDP_ACCESS_TOKEN=

# optional: webhook HMAC secret
# SUPERPDP_WEBHOOK_SECRET=
```

Auth flow: `client_credentials` against `POST /oauth2/token`, token cached. Sandbox vs production credentials are selected by `SuperPdpConfig::activeCredentials()`.

Check connectivity:

```bash
php artisan superpdp:test
```

Optional helpers:

| Command | Role |
|---------|------|
| `php artisan superpdp:send-test` | Send SuperPDP-generated sandbox invoice |
| `php artisan superpdp:send-test --invoice={id}` | Submit a ready VRP invoice |
| `php artisan superpdp:setup-tricatel` | Seed sandbox buyer school (Tricatel / PEPPOL address) |

## Submit workflow

1. Create an invoice → status set to **`ready`** (`InvoiceController::store`).
2. Ensure PDF exists on disk (`invoices/{bill_prefix}{id}.pdf`). Missing PDF blocks submit.
3. Open **Treasury → Invoices**. The e-invoice button appears only when `ElectronicInvoiceService::platformConfigured()` is true **and** status is `ready`.
4. `POST invoice.submitElectronic` validates then submits.
5. On success: status **`transmitted`**, `pdp_reference` set, `rejection_reason` cleared.

### Validation rules (blocking)

| Rule | Source |
|------|--------|
| Status must be `ready` | `invoices.electronic_invoice_status` |
| Invoice not paid | `paid_at` null |
| Issuer SIREN + full address | `companies` |
| Client SIREN or SIRET + full address | `schools` |
| PDF present in storage | `Storage::exists(...)` |

Paid tracking (`paid_at`) stays independent of e-invoice status.

### Payload note

Outbound uses **CII XML → Factur-X** via SuperPDP convert API. The TCPDF PDF stays for VRP display; it is **not** the structured payload sent as the e-invoice.

Sandbox without a school `electronic_address` can inject default routing (`SUPERPDP_SANDBOX_BUYER_*`). If the school has `electronic_address`, that value is used instead.

## Webhooks

| Item | Value |
|------|-------|
| Route | `POST /webhooks/e-invoice/{platform}` — only `superpdp` accepted |
| CSRF | Excluded in `VerifyCsrfToken` |
| Auth | HMAC-SHA256 of raw body; headers `X-SuperPDP-Signature` or `X-Webhook-Signature` |
| Success | HTTP 204 |

Status mapping (substring match on payload status):

| Payload hint | `PlatformEventType` | Invoice update |
|--------------|---------------------|----------------|
| reject / refus | `outbound.rejected` | `rejected` + reason |
| accept / valid | `outbound.accepted` | `accepted` |
| other | `outbound.submitted` | `transmitted` |

Invoice lookup: `pdp_reference` first, then numeric part of `external_id` as VRP invoice id. Inbound reception (`inbound.received`) is parsed as a type but **not** persisted yet.

## Constraints and pitfalls

- Without `E_INVOICE_PLATFORM=superpdp` and valid credentials, the UI submit button stays hidden (`NullElectronicInvoicePlatform::isConfigured()` → false).
- Missing SIREN/address or PDF yields a flash danger + warning list of validation messages.
- Webhooks without `SUPERPDP_WEBHOOK_SECRET` always return **401**.
- VAT in the CII builder is currently fixed at **20%**.
- Supplier invoice reception UI is not built yet.

## See also

- [Roadmap — electronic invoicing](roadmap-electronic-invoicing.md)
- [V2 — treasury & bank reconciliation](v2-treasury-bank-reconciliation.md)
- [V2 — billing per school](v2-billing-per-school.md)
- [Configuration](configuration.md)
