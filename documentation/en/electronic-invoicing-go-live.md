# Electronic invoicing go-live (SuperPDP / IONOS)

**FR:** [facturation-electronique-go-live.md](../fr/facturation-electronique-go-live.md)

Production runbook for `https://vrp.xdm-consulting.fr`. Secrets stay **on the server** (IONOS `.env`). The repo never commits `E_INVOICE_ALLOW_PRODUCTION=true` or credentials.

Operational layer: [electronic-invoicing.md](electronic-invoicing.md). File deploy: [production-sftp-deploy.md](production-sftp-deploy.md).

## Code vs SuperPDP vs IONOS

| In code (after go-live PR) | At SuperPDP | On IONOS |
|-----------------------|-------------|----------|
| `E_INVOICE_ALLOW_PRODUCTION` defaults **false** | Production **Application** (OAuth) | Edit `.env` **by hand** (SFTP script never uploads it) |
| Per-company `electronic_invoicing_enabled` (default **off**) | Webhook HMAC secret | `php artisan migrate` **or** SQL below |
| Webhook URL from `APP_URL` / `E_INVOICE_WEBHOOK_URL` | Register `https://vrp.xdm-consulting.fr/webhooks/e-invoice/superpdp` | HTTPS + maybe `TRUSTED_PROXIES=*` |
| Logs `storage/logs/e-invoice.log` + optional mail | PA profile SIREN = VRP issuer | `E_INVOICE_ALERT_EMAIL` optional |
| `php artisan superpdp:go-live-check` (no secrets printed) | Confirm `/companies/me` | Flip the lock **true** only at go-live |

`./scripts/deploy-xdm-vrp.sh` does **not** upload `.env` and does **not** run migrations.

## Production SQL (before or with the deploy)

```sql
ALTER TABLE `companies`
  ADD `electronic_invoicing_enabled` TINYINT(1) NOT NULL DEFAULT 0;
```

Every tenant stays **off**. Enable only the pilot company (**My company** or super-admin).

## Server `.env` (never in Git)

```env
APP_URL=https://vrp.xdm-consulting.fr
TRUSTED_PROXIES=*

E_INVOICE_PLATFORM=superpdp
SUPERPDP_ENV=production
E_INVOICE_ALLOW_PRODUCTION=false
SUPERPDP_BASE_URL=https://api.superpdp.tech
SUPERPDP_CLIENT_ID=
SUPERPDP_CLIENT_SECRET=
SUPERPDP_WEBHOOK_SECRET=
E_INVOICE_WEBHOOK_URL=https://vrp.xdm-consulting.fr/webhooks/e-invoice/superpdp
E_INVOICE_REQUIRE_HTTPS_WEBHOOKS=false
E_INVOICE_ALERT_EMAIL=
```

Leave `E_INVOICE_ALLOW_PRODUCTION=false` until OAuth, HTTPS webhook, and company opt-in are verified.

If Laravel logs webhooks as HTTP: set `TRUSTED_PROXIES=*`, then `E_INVOICE_REQUIRE_HTTPS_WEBHOOKS=true`.

If `bootstrap/cache/config.php` exists, `.env` changes are ignored until `config:clear` / `config:cache` **on the server**.

## SuperPDP

1. Production PA account (not the sandbox Application).
2. **Settings → Applications**: production app, `client_id` / `client_secret`.
3. Webhook `POST https://vrp.xdm-consulting.fr/webhooks/e-invoice/superpdp`
4. Header `X-SuperPDP-Signature` = HMAC-SHA256 of the raw body.
5. SuperPDP profile SIREN matches VRP **My company**.

## Live cut-over

1. Approve/merge the go-live follow-up PR (PR 42 is already merged; this code adds per-tenant opt-in + IONOS runbook), deploy (`./scripts/deploy-xdm-vrp.sh --upload`).
2. ALTER / migrate production DB.
3. Fill `.env` **with the lock still false**.
4. `php artisan superpdp:test` then `php artisan superpdp:go-live-check`.
5. Opt in **one** company in VRP; complete legal IDs.
6. On the server only: `E_INVOICE_ALLOW_PRODUCTION=true` and refresh config cache.
7. Low-stakes pilot invoice → e button → `transmitted` → webhook `accepted` / `rejected`.
8. Watch `storage/logs/e-invoice.log`.

Rollback: set `E_INVOICE_ALLOW_PRODUCTION=false` and/or disable the company switch.
