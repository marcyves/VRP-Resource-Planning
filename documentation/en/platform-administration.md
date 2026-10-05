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
| Every login is the proxy's city | `TRUSTED_PROXIES` is unset in front of the host. Production sets `TRUSTED_PROXIES=*` |
| Public IPs stay "unknown location" | GeoLite2 file missing and HTTP lookup off, timing out, or cached as a miss (`login_stats.geo_*` in the log) |
| Company admin cannot find an unknown email | Expected: that failure has `company_id = null` and only the super admin sees it |

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
| `app/Listeners/RecordLoginStatistics.php` | `Login` / `Failed` / `Lockout` → `login_events` |
| `tests/Feature/LoginStatisticsTest.php` | Recording, admin scope, outcome filter |

## Common pitfalls

- Keep `VRP_ALLOW_REGISTRATION=false` for managed multi-tenant deployments. Public registration creates a plain user without tenant context; tenant middleware requires `company_id` before regular modules can be used.
- Do not treat `/demande-acces` as signup. It only emails operators.
- Do not attach business records to a super admin. The platform account intentionally has `company_id = null`.
- Choose invoice prefixes carefully. They are unique, uppercased, and used to associate legacy planning invoice identifiers.
- Run migrations before creating the first super admin; otherwise the required status and nullable `users.company_id` may not exist.

## Login statistics

Admin-only log of sign-in attempts (sidebar label **Logins**). Recording never blocks authentication: `RecordLoginStatistics` swallows its own errors (`login_stats.record_failed`), and a geolocation failure still stores the row with an empty location.

### Where it appears

| Actor | Sidebar | Route | Scope |
|-------|---------|-------|-------|
| Super admin | Shield item after Companies and Electronic invoicing | `GET /super-admin/login-stats` (`super-admin.login-stats.index`) | Every row, including failures with no company |
| Company admin (`Status::ADMIN`) | Shield item after the Treasury separator, before Referential | `GET /admin/login-stats` (`login-stats.index`) | Rows whose `company_id` matches the admin |
| Editor / reader | No link | Same tenant URL | 403 (`User::isAdmin()`) |

A company admin who opens the super-admin URL gets 403 from the `superadmin` middleware.

### What is stored

Table `login_events` (migration `2026_10_01_100000_create_login_events_table`). No `created_at` / `updated_at`. The password is never written.

| Column | Source |
|--------|--------|
| `username` | Trimmed email from the attempt. Success rows use `$user->email` |
| `user_id` | Matched user, or null |
| `company_id` | That user's company, or null |
| `ip` | `$request->ip()`, or `0.0.0.0` when empty. Depends on `TRUSTED_PROXIES` |
| `geo_label` | See [Geolocation](#geolocation) |
| `success` / `locked_out` | Outcome |
| `occurred_at` | `now()` when the row is written |

`EventServiceProvider` subscribes `RecordLoginStatistics`, which calls `LoginEventRecorder`.

| Auth event | Row |
|------------|-----|
| `Login` | `success = true`, `locked_out = false` |
| `Failed` | `success = false`, `locked_out = false`. A known email is resolved to user and company even when the password is wrong |
| `Lockout` | `success = false`, `locked_out = true`. `Auth::attempt` is not called, so this request does not also write a `Failed` row |

`LoginRequest` allows **5** failures per `transliterate(lowercase email)|ip`. The next attempt fires `Lockout`, and every further attempt while the limiter holds adds another lockout row. The table labels lockout on its own; the **Failed** filter still includes those rows (`success = false`).

Unknown emails stay `company_id = null`, so company admins do not see them. Deleting a company cascades its events. Deleting a user nulls `user_id` and keeps the username. There is no purge command; the table grows until rows are removed with the company or by hand.

### Screen

`resources/views/login-stats/index.blade.php`, styles in `resources/css/login-stats.css`.

- Counts and the success/failed bar use the **filtered** set. They are not a time series.
- GET filters, kept across pages: `outcome` = `all` \| `success` \| `failed`; `from` / `to` are inclusive calendar days on `occurred_at` (`00:00:00` through `23:59:59`); `q` is a substring match on `username`, `ip`, and the stored `geo_label` (max 255). Super admins also get `company_id`: empty = all companies, `none` = `company_id` null, otherwise a company id. Tenant admins ignore `company_id`.
- The table is 50 rows, newest `occurred_at` then `id`. Times render in `config('app.timezone')`.
- Location text: stored `local` becomes `messages.login_stats_geo_local` (“Local network”); null or empty becomes the unknown label; any other value is shown as stored.

### Geolocation

`App\Services\GeoLocator`. Private or reserved addresses (loopback, `0.0.0.0`, other non-public ranges) are stored as the literal `local` and are not looked up.

Public IPs try GeoLite2-City (`geoip2/geoip2`) when `LOGIN_STATS_GEO_MMDB` is readable. The label is `city, subdivision, country`. If that misses and `LOGIN_STATS_GEO_HTTP` is true, the app GETs `LOGIN_STATS_GEO_HTTP_URL` (default `https://ipwho.is/{ip}`) with `LOGIN_STATS_GEO_HTTP_TIMEOUT` (1.5s). That request sends the client IP to ipwho.is. Set `LOGIN_STATS_GEO_HTTP=false` to stay on the local file only.

Cache key `login-stats-geo:{ip}`: a label lasts `LOGIN_STATS_GEO_CACHE_TTL` (default 30 days); a miss lasts `LOGIN_STATS_GEO_FAILURE_CACHE_TTL` (default 1 hour). Variable list: [Configuration](configuration.md).

### Pitfalls

- With `TRUSTED_PROXIES` unset behind the host, every row is the proxy address and is geolocated as that proxy. The production deploy sets `TRUSTED_PROXIES=*`.
- Sail and a local browser record `local`, not a city. Searching the translated phrase “Local network” does not match the stored value `local`.
- A missing or unreadable `.mmdb`, or no `GeoIp2\Database\Reader` class, skips the file. HTTP then runs unless it is disabled. Do not commit the database (~60 MB).
- `phpunit.xml` sets `LOGIN_STATS_GEO_HTTP=false`. Coverage: `tests/Feature/LoginStatisticsTest.php`, `tests/Unit/GeoLocatorTest.php`.

## See also

- [Configuration](configuration.md)
- [Phase 1 — terminology](phase-1-terminology.md)
- [V2 — navigation and modules](v2-navigation-modules.md)
