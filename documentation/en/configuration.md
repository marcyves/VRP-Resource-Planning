# Configuration — terminology

**FR:** [configuration.md](../fr/configuration.md)

## Environment variables

| Variable | Default | Role |
|----------|---------|------|
| `APP_LOCALE` | `fr` | Base language (`fr`, `en`, `it`) |
| `TERMINOLOGY_PROFILE` | `education` | Profile for guests / no company loaded |
| `VRP_ALLOW_REGISTRATION` | `false` | Public self-registration at `/register` |
| `E_INVOICE_PLATFORM` | _(unset)_ | `superpdp` enables the SuperPDP adapter; otherwise Null |
| `SUPERPDP_ENV` | `production` | Selects production vs sandbox OAuth credentials |
| `SUPERPDP_CLIENT_ID` / `SUPERPDP_CLIENT_SECRET` | — | Production OAuth app |
| `SUPERPDP_SANDBOX_CLIENT_ID` / `SUPERPDP_SANDBOX_CLIENT_SECRET` | — | Sandbox OAuth app |
| `SUPERPDP_ACCESS_TOKEN` | — | Optional bearer token (skips OAuth) |
| `SUPERPDP_WEBHOOK_SECRET` | — | HMAC secret for `/webhooks/e-invoice/superpdp` |

Example `.env.example`:

```env
APP_LOCALE=fr
TERMINOLOGY_PROFILE=consulting
VRP_ALLOW_REGISTRATION=false
# E_INVOICE_PLATFORM=superpdp
```

> When signed in, `companies.terminology_profile` **overrides** `TERMINOLOGY_PROFILE`.

## Platform super admin

1. `php artisan migrate`
2. `php artisan vrp:create-super-admin admin@example.com "Super Admin"`
3. Sign in -> `/super-admin/companies` to create a company and its first administrator

The super admin has no company attached; tenant users require a `company_id`.

## Company settings (UI)

1. Sign in as admin or editor
2. **My company** → **Edit**
3. **Business context**: Training or Clients & projects
4. Save

## Single-domain deployment

| Need | Settings |
|------|----------|
| Consulting (FR) | `APP_LOCALE=fr` + company profile `consulting` |
| Consulting (EN) | `APP_LOCALE=en` + profile `consulting` |
| Training | profile `education` |

## Files

- `config/terminology.php`, `config/app.php`, `config/vrp.php`, `config/electronic-invoicing.php`, `.env.example`

## Links

- [Phase 1 — terminology](phase-1-terminology.md)
- [Platform administration](platform-administration.md)
- [Electronic invoicing (ops)](electronic-invoicing.md)
- [Consulting labels](consulting-labels.md)
