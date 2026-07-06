# Configuration — terminology

**FR:** [configuration.md](../fr/configuration.md)

## Environment variables

| Variable | Default | Role |
|----------|---------|------|
| `APP_LOCALE` | `fr` | Base language (`fr`, `en`, `it`) |
| `TERMINOLOGY_PROFILE` | `education` | Profile for guests / no company loaded |
| `VRP_ALLOW_REGISTRATION` | `false` | Public self-registration at `/register` |

Example `.env.example`:

```env
APP_LOCALE=fr
TERMINOLOGY_PROFILE=consulting
VRP_ALLOW_REGISTRATION=false
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

- `config/terminology.php`, `config/app.php`, `config/vrp.php`, `.env.example`

## Links

- [Phase 1 — terminology](phase-1-terminology.md)
- [Platform administration](platform-administration.md)
- [Consulting labels](consulting-labels.md)
