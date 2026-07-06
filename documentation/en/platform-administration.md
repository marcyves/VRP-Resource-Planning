# Platform administration and tenant provisioning

**FR:** [administration-plateforme.md](../fr/administration-plateforme.md)

## Intent

VRP is multi-tenant. A **super admin** account manages companies and their initial users from the platform area, while day-to-day modules remain scoped to one company.

The platform flow is meant for controlled tenant onboarding:

- create one super-admin account from the command line;
- sign in to `/super-admin/companies`;
- create each company with its billing prefix, terminology profile, and first administrator;
- add more tenant users from the company detail page.

Public self-registration is disabled by default and does not provision a company. Use the super-admin flow for normal tenant onboarding.

## Access model

| Actor | Required data | Allowed area | Boundary behavior |
|-------|---------------|--------------|-------------------|
| Super admin | `status_id` resolves to `super admin`, `company_id = null` | `/super-admin/companies` | Tenant routes redirect to the company admin list |
| Tenant admin/editor/reader | `company_id` set, role `Status::ADMIN`, `Status::EDITOR`, or `Status::READER` | `/home` and tenant modules | `/super-admin/*` returns 403 |
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

   The command prompts for a password unless `--password=` is supplied. It validates email uniqueness and Laravel's default password rules, creates a user without `company_id`, and assigns `Status::superAdminId()`.

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

Public registration creates a user account only; it does not create a company or assign a tenant role. For production onboarding, keep registration disabled and create tenants from `/super-admin/companies`.

## Troubleshooting

| Symptom | Check |
|---------|-------|
| Super admin gets 403 on `/super-admin/companies` | User has `status_id` matching the `super admin` status row |
| Super admin lands on `/home` then redirects | This is expected; tenant middleware redirects super admins to the platform area |
| Tenant user gets 403 on tenant pages | User is not super admin and must have `company_id` set |
| Company creation fails on bill prefix | Prefix must be alphanumeric, max 10 chars, and unique across companies |
| New course cannot use a program | Program belongs to another company or is missing `company_id` |
| `/register` returns 404 | `VRP_ALLOW_REGISTRATION` is false, which is the default |

## Key files

| File | Role |
|------|------|
| `app/Console/Commands/CreateSuperAdminCommand.php` | Bootstrap command |
| `app/Http/Middleware/EnsureSuperAdmin.php` | Protects platform routes |
| `app/Http/Middleware/EnsureTenantUser.php` | Keeps tenant routes company-scoped |
| `app/Http/Controllers/SuperAdmin/CompanyController.php` | Company list, create, update, delete |
| `app/Http/Controllers/SuperAdmin/CompanyUserController.php` | Adds tenant users |
| `app/Services/CompanyProvisioner.php` | Transactional company + first admin creation |
| `app/Services/CompanyUserProvisioner.php` | Tenant user creation and contact sync |
| `app/Services/CompanyDeleter.php` | Destructive tenant cleanup |
| `config/vrp.php` | `VRP_ALLOW_REGISTRATION` gate |
| `config/terminology.php` | Available terminology profiles |
| `tests/Feature/SuperAdmin/*` | Platform route and provisioning coverage |
| `tests/Feature/ProgramCompanyScopeTest.php` | Program tenant isolation |

## See also

- [Configuration](configuration.md)
- [Phase 1 - terminology](phase-1-terminology.md)
- [V2 - navigation and modules](v2-navigation-modules.md)
