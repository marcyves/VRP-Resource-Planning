# Platform administration and tenant provisioning

**FR:** [administration-plateforme.md](../fr/administration-plateforme.md)

## Intent

VRP is multi-tenant. A **super admin** account manages companies and their initial users from the platform area, while day-to-day modules remain scoped to one company.

The platform flow is meant for controlled tenant onboarding:

- create one super-admin account from the command line;
- sign in to `/super-admin/companies`;
- create each company with its billing prefix, terminology profile, and first administrator;
- add more tenant users from the company detail page.

Public self-registration is disabled by default and does not provision a company. Use the super-admin flow for normal tenant onboarding. Guests land on `/` (`WelcomeController`) and request an account at `/demande-acces`.

## Access model

| Actor | Required data | Allowed area | Boundary behavior |
|-------|---------------|--------------|-------------------|
| Super admin | `status_id` resolves to `super admin`, `company_id = null` | `/super-admin/companies` | Tenant routes redirect to the company admin list |
| Tenant admin | `company_id` set, role `Status::ADMIN` | `/home`, tenant modules, `/admin/login-stats` | `/super-admin/*` returns 403; login stats are company-scoped |
| Tenant editor/reader | `company_id` set, role `Status::EDITOR` or `Status::READER` | `/home` and tenant modules | `/admin/login-stats` and `/super-admin/*` return 403 |
| Authenticated user without company | Not a super admin and no `company_id` | None | Tenant middleware aborts 403 |

Route boundaries are defined in `routes/web.php`:

- `/super-admin/*` uses `auth` and the `superadmin` middleware alias;
- tenant routes use `auth`, `tenant`, and `SetTerminologyLocale`;
- login redirects through `User::homePath()`.

## Bootstrap runbook

1. Run migrations so the `super admin` status exists and `users.company_id` can be nullable:

   ```bash
   php artisan migrate
   ```

2. Create the first platform account:

   ```bash
   php artisan vrp:create-super-admin admin@example.com "Platform Admin"
   ```

   The command prompts for a password unless `--password=` is supplied. It validates email uniqueness and Laravel's default password rules, creates a user without `company_id`, and assigns `Status::superAdminId()`. Avoid `--password` in interactive shells: the value can remain in history.

3. Sign in at `/login`. Super admins are redirected to `/super-admin/companies`.

4. Keep `VRP_ALLOW_REGISTRATION=false` unless you intentionally want the legacy `/register` route available.

## Company provisioning workflow

From `/super-admin/companies/create`, a super admin provides:

| Field | Constraint | Effect |
|-------|------------|--------|
| Company name | required, max 255 chars | Creates `companies.name` |
| Bill prefix | required, max 10 chars, alphanumeric, unique | Stored uppercase in `companies.bill_prefix` |
| Terminology profile | one of `education`, `consulting`, `medical` | Drives tenant labels through `SetTerminologyLocale` |
| Admin name/email/password | required; email unique; password confirmed | Creates the first company user |

`CompanyProvisioner` wraps the operation in a database transaction. It creates the company, creates the first tenant admin with `status_id = Status::ADMIN` and `mode = Edit`, then syncs the company contact fields from that admin user.

## Managing tenant users

On `/super-admin/companies/{company}`, the super admin can add users to the selected company.

| Role | Stored status | Mode |
|------|---------------|------|
| Administrator | `Status::ADMIN` | `Edit` |
| Editor | `Status::EDITOR` | `Edit` |
| Reader | `Status::READER` | `Browse` |

The request only accepts these three tenant roles; the UI cannot create another super admin inside a company. If a company has no contact user yet and the new user is an admin, `CompanyUserProvisioner` makes that user the company contact.

## Tenant data boundaries

Most business data is reached through the authenticated user's `company_id`.

- Schools, invoices, and groups are loaded through user/company filters.
- Programs now have a required `company_id`; `ProgramController` only opens programs for the current company and attaches new programs to the current user's company.
- Courses are scoped through their school. Course create/update also require `program_id` to belong to the current company.
- Company terminology is resolved from `companies.terminology_profile`; the environment variable `TERMINOLOGY_PROFILE` is only the fallback when no company is loaded.

When adding a new tenant feature, follow the same pattern: protect the route with the `tenant` middleware and query records through the current user's company.

## Company deletion

Deleting a company from the super-admin area calls `CompanyDeleter` in a transaction. It clears company contact and billing-account references, then deletes or unlinks company data including:

- schools and their calendar mappings, calendar sources, documents, school-user rows, courses, group-course links, and course plannings;
- company groups and group plannings;
- invoices and programs for the company;
- tenant users and their school-user links;
- the company row.

This is destructive and not a soft delete. Export or back up tenant data before confirming deletion in production.

## Registration controls

| Setting | Default | Behavior |
|---------|---------|----------|
| `VRP_ALLOW_REGISTRATION` | `false` | `/register` GET and POST return 404 |
| `VRP_ALLOW_REGISTRATION=true` | opt-in | Public registration form is available |
| `VRP_ACCOUNT_REQUEST_EMAIL` | `MAIL_FROM_ADDRESS` if **unset** | Recipient for `/demande-acces` |

Public registration creates a user account only; it does not create a company or assign a tenant role. For production onboarding, keep registration disabled and create tenants from `/super-admin/companies`.

### Account request (`/demande-acces`)

The landing page and login link to **Request an account** (`account-request.create`). This is **not** self-provisioning. The public chrome is the marketing layout (wordmark only — [CSS design system](v2-design-system-css.md#public-marketing-canvas)).

1. Guest submits company name, contact, email, optional phone, terminology profile, message (`StoreAccountRequestRequest`; POST throttled `5,1`).
2. `AccountRequestController` mails `AccountRequestMail` to `config('vrp.account_request_email')`.
3. User sees a success flash. No user or company row is created.
4. An operator then provisions the tenant from `/super-admin/companies`.

If `VRP_ACCOUNT_REQUEST_EMAIL` is present but **empty** (as in `.env.example`), Laravel does not apply the `MAIL_FROM_ADDRESS` fallback. The form still redirects with success; the controller `report()`s a `RuntimeException` and sends nothing.

Coverage: `tests/Feature/LandingPageTest.php`.

## Troubleshooting

| Symptom | Check |
|---------|-------|
| Super admin gets 403 on `/super-admin/companies` | User has `status_id` matching the `super admin` status row |
| Super admin lands on `/home` then redirects | This is expected; tenant middleware redirects super admins to the platform area |
| Tenant user gets 403 on tenant pages | User is not super admin and must have `company_id` set |
| Company creation fails on bill prefix | Prefix must be alphanumeric, max 10 chars, and unique across companies |
| New course cannot use a program | Program belongs to another company or is missing `company_id` |
| `/register` returns 404 | `VRP_ALLOW_REGISTRATION` is false, which is the default |
| Account request succeeds but no email arrives | `VRP_ACCOUNT_REQUEST_EMAIL` is empty; omit the key to fall back to `MAIL_FROM_ADDRESS`, or set a real inbox |
| `/demande-acces` POST is 429 | Throttle `5,1` on `account-request.store` |
| Signed-in user still sees the public landing | `WelcomeController` should redirect to `User::homePath()`; check the session |

## Key files

| File | Role |
|------|------|
| `app/Console/Commands/CreateSuperAdminCommand.php` | Bootstrap command |
| `app/Http/Middleware/EnsureSuperAdmin.php` | Protects platform routes |
| `app/Http/Middleware/EnsureTenantUser.php` | Keeps tenant routes company-scoped |
| `app/Http/Controllers/SuperAdmin/CompanyController.php` | Company list, create, update, delete |
| `app/Http/Controllers/SuperAdmin/CompanyUserController.php` | Adds tenant users |
| `app/Http/Controllers/AccountRequestController.php` | Guest `/demande-acces` mail |
| `app/Http/Controllers/WelcomeController.php` | Guest landing `/`; signed-in redirect |
| `resources/views/layouts/marketing.blade.php` | Public chrome (wordmark, skip link, theme toggle) |
| `app/Services/CompanyProvisioner.php` | Transactional company + first admin creation |
| `app/Services/CompanyUserProvisioner.php` | Tenant user creation and contact sync |
| `app/Services/CompanyDeleter.php` | Destructive tenant cleanup |
| `config/vrp.php` | `VRP_ALLOW_REGISTRATION` and `VRP_ACCOUNT_REQUEST_EMAIL` |
| `config/terminology.php` | Available terminology profiles |
| `tests/Feature/SuperAdmin/*` | Platform route and provisioning coverage |
| `tests/Feature/LandingPageTest.php` | Landing + account-request coverage |
| `tests/Feature/ProgramCompanyScopeTest.php` | Program tenant isolation |

## Common pitfalls

- Keep `VRP_ALLOW_REGISTRATION=false` for managed multi-tenant deployments. Public registration creates a plain user without tenant context; tenant middleware requires `company_id` before regular modules can be used.
- Do not treat `/demande-acces` as signup. It only emails operators.
- Do not attach business records to a super admin. The platform account intentionally has `company_id = null`.
- Choose invoice prefixes carefully. They are unique, uppercased, and used to associate legacy planning invoice identifiers.
- Run migrations before creating the first super admin; otherwise the required status and nullable `users.company_id` may not exist.

## Login statistics

`Login`, `Failed`, and `Lockout` events are stored in `login_events` (IP, attempted username, timestamp, geolocation, `company_id` when known).

| Actor | Screen | Scope |
|-------|--------|-------|
| Super admin | `/super-admin/login-stats` | Every event, including failures with no company |
| Company admin | `/admin/login-stats` | Events for that `company_id` (successes and failures resolved to the company's users) |
| Editor / reader | — | 403 |

Geolocation: local GeoLite2-City file when present, otherwise a no-key HTTP lookup (`ipwho.is`) with a short timeout and cache. Missing geolocation never blocks login. See [Configuration](configuration.md).

## Landing traffic (platform)

First-party visits on the public marketing routes (`/`, `/fonctionnalites`, `/tarifs`, `/a-propos`, `/mentions-legales`), plus guest `/login` and `/demande-acces`. Super-admin only: `/super-admin/landing-traffic`. Not scoped by `company_id`. Nothing is sent to Google Analytics or any other third party. `DNT` / `Sec-GPC` skips recording. Authenticated requests are not logged (`LandingVisitRecorder::declined` when a user is present). Raw IP is hashed (`ip_hash`) and never shown. Country is filled only from the local GeoLite2 MMDB (same file as login stats) — no HTTP geo lookup.

The `page` column has three buckets only: `login`, `register` (`/register` and `/demande-acces`), and `landing` for **every other** tracked route, including the product, pricing, about, and legal pages. Tell those apart with `landing_path`. The screen filter **Home** therefore includes `/tarifs` and the other marketing paths. `locale` is the guest locale after `SetGuestLocale` (session language, otherwise `APP_LOCALE`).

A click on `[data-landing-cta]` (`login`, `register`, `pricing`) POSTs `/landing-visits/cta` (throttle 30/minute). That stamps `cta` on the latest row for this visitor cookie with `page = landing` and `cta` still null. A visit to `/tarifs` is itself a `landing` row, so a later click can land on that row instead of the home visit. Opening `/login` or `/demande-acces` sets `cta` to the page name immediately.

`LANDING_TRAFFIC_ENABLED=false` turns recording off. Bots (`UserAgentParser`) and prefetch (`Sec-Purpose` / `Purpose` containing `prefetch`) are skipped. Only HTTP 200 GET responses are stored.

Times are stored in UTC and displayed in `Europe/Rome`.

If `php artisan migrate` is not available on IONOS, create the table with:

```sql
CREATE TABLE `landing_visits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `visited_at` timestamp NOT NULL,
  `visitor_id` char(36) NOT NULL,
  `is_returning` tinyint(1) NOT NULL DEFAULT 0,
  `page` varchar(32) NOT NULL,
  `locale` varchar(8) DEFAULT NULL,
  `accept_language` varchar(255) DEFAULT NULL,
  `os` varchar(64) DEFAULT NULL,
  `device_type` varchar(16) DEFAULT NULL,
  `browser` varchar(64) DEFAULT NULL,
  `referer_url` text DEFAULT NULL,
  `referer_host` varchar(255) DEFAULT NULL,
  `keywords` varchar(255) DEFAULT NULL,
  `utm_source` varchar(255) DEFAULT NULL,
  `utm_medium` varchar(255) DEFAULT NULL,
  `utm_campaign` varchar(255) DEFAULT NULL,
  `utm_content` varchar(255) DEFAULT NULL,
  `utm_term` varchar(255) DEFAULT NULL,
  `landing_path` varchar(255) NOT NULL,
  `landing_query` text DEFAULT NULL,
  `country` varchar(64) DEFAULT NULL,
  `ip_hash` varchar(64) DEFAULT NULL,
  `cta` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `landing_visits_visited_at_index` (`visited_at`),
  KEY `landing_visits_visitor_id_index` (`visitor_id`),
  KEY `landing_visits_referer_host_index` (`referer_host`),
  KEY `landing_visits_locale_index` (`locale`),
  KEY `landing_visits_device_type_index` (`device_type`),
  KEY `landing_visits_utm_source_index` (`utm_source`),
  KEY `landing_visits_page_index` (`page`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## See also

- [Configuration](configuration.md)
- [Phase 1 — terminology](phase-1-terminology.md)
- [V2 — navigation and modules](v2-navigation-modules.md)
