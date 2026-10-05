# Configuration — terminology

**FR:** [configuration.md](../fr/configuration.md)

## Environment variables

| Variable | Default | Role |
|----------|---------|------|
| `APP_LOCALE` | `fr` | Base language (`fr`, `en`, `it`) |
| `TERMINOLOGY_PROFILE` | `education` | Profile for guests / no company loaded |
| `VRP_ALLOW_REGISTRATION` | `false` | Public self-registration at `/register` |
| `VRP_ACCOUNT_REQUEST_EMAIL` | `MAIL_FROM_ADDRESS` if the key is **unset** | Inbox for `/demande-acces`. An empty `VRP_ACCOUNT_REQUEST_EMAIL=` in `.env` does **not** fall back (empty string is set) — the form still succeeds, but `AccountRequestController` reports a `RuntimeException` and sends no mail |
| `E_INVOICE_PLATFORM` | *(unset)* | `superpdp` to bind SuperPDP; otherwise Null driver. Leave unset on live tenants. |
| `E_INVOICE_ALLOW_PRODUCTION` | `false` | Hard lock: live SuperPDP submit stays off until this is `true` (server `.env` only; never commit `true`) |
| `E_INVOICE_WEBHOOK_URL` | `APP_URL` + path | Public webhook URL to register at SuperPDP |
| `E_INVOICE_REQUIRE_HTTPS_WEBHOOKS` | `false` | Reject `POST /webhooks/e-invoice/*` over HTTP (400). Enable on IONOS after TLS + `TRUSTED_PROXIES` |
| `E_INVOICE_ALERT_EMAIL` | *(unset)* | Optional ops mail on submit/webhook failure |
| `TRUSTED_PROXIES` | *(unset)* | `*` on IONOS so HTTPS is visible to Laravel |
| `LOGIN_STATS_GEO_MMDB` | `storage/app/geoip/GeoLite2-City.mmdb` | Optional MaxMind GeoLite2 City file (~60 MB, **do not commit**). Free MaxMind account → download GeoLite2-City → place the `.mmdb` there. Missing file falls back to HTTP or “unknown location” |
| `LOGIN_STATS_GEO_HTTP` | `true` | No-key HTTPS fallback (`ipwho.is`) when the MMDB is absent. Short timeout; never blocks login |
| `LOGIN_STATS_GEO_HTTP_URL` | `https://ipwho.is/{ip}` | No-key URL template; `{ip}` is substituted |
| `LOGIN_STATS_GEO_HTTP_TIMEOUT` | `1.5` | Seconds |
| `LOGIN_STATS_GEO_CACHE_TTL` | `2592000` (30 days) | How long a resolved public-IP label stays in cache (`login-stats-geo:{ip}`) |
| `LOGIN_STATS_GEO_FAILURE_CACHE_TTL` | `3600` | How long a failed lookup is cached, so a dead resolver is not called on every sign-in |
| `SUPERPDP_ENV` | `sandbox` | `sandbox` or `production` (selects OAuth credentials) |
| `SUPERPDP_CLIENT_ID` / `SUPERPDP_CLIENT_SECRET` | — | Production OAuth (`client_credentials`) |
| `SUPERPDP_SANDBOX_CLIENT_ID` / `SUPERPDP_SANDBOX_CLIENT_SECRET` | — | Sandbox OAuth when `SUPERPDP_ENV=sandbox` |
| `SUPERPDP_ACCESS_TOKEN` | — | Optional bearer token (skips OAuth) |
| `SUPERPDP_WEBHOOK_SECRET` | — | Optional HMAC fallback ; prefer super-admin → Electronic invoicing. Missing secret → webhook **401** |

Example `.env.example`:

```env
APP_LOCALE=fr
TERMINOLOGY_PROFILE=consulting
VRP_ALLOW_REGISTRATION=false
# VRP_ACCOUNT_REQUEST_EMAIL=ops@example.com
# E_INVOICE_PLATFORM=superpdp
# E_INVOICE_ALLOW_PRODUCTION=false
# SUPERPDP_ENV=sandbox
```

Electronic invoicing runbook: [electronic-invoicing.md](electronic-invoicing.md).

Login statistics: the HTTP fallback sends the visitor IP to the host in `LOGIN_STATS_GEO_HTTP_URL`. Behaviour, filters, and the `TRUSTED_PROXIES` pitfall: [platform administration](platform-administration.md#login-statistics).

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

- `config/terminology.php`, `config/app.php`, `config/vrp.php`, `config/electronic-invoicing.php`, `config/login_stats.php`, `.env.example`

## Links

- [Phase 1 — terminology](phase-1-terminology.md)
- [Platform administration](platform-administration.md) — `/demande-acces` vs `/register`
- [Consulting labels](consulting-labels.md)
- [Electronic invoicing](electronic-invoicing.md)
