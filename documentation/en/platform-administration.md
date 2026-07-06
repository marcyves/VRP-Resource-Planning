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
# Platform administration

**FR:** [administration-plateforme.md](../fr/administration-plateforme.md)

VRP is a multi-tenant application: each customer company owns its users and business data, while a separate **super admin** account provisions and maintains companies from `/super-admin/companies`.

## Intent

| Concern | Behavior |
|---------|----------|
| Tenant users | Must have a `company_id`; they use the regular VRP modules (`/home`, planning, treasury, company settings). |
| Super admins | Have no `company_id`; they only access the platform administration area. |
| Public registration | Disabled by default with `VRP_ALLOW_REGISTRATION=false`; tenant accounts should be provisioned from the platform UI. |

## Bootstrap a platform admin

Run migrations first so the `super admin` status exists and `users.company_id` is nullable:

```bash
php artisan migrate
php artisan vrp:create-super-admin admin@example.com "Platform Admin"
```

The command asks for the password securely when `--password` is omitted. Avoid passing `--password` in interactive shells because it can remain in command history.

After login, super admins are redirected to:

```text
/super-admin/companies
```

## Access boundaries

| Area | Route / middleware | Result |
|------|--------------------|--------|
| Platform admin | `/super-admin/companies*` with `auth`, `superadmin` | Only `User::isSuperAdmin()` may enter; other users receive 403. |
| Tenant app | `/home`, `/dashboard`, planning, billing, treasury, company settings with `auth`, `tenant`, `SetTerminologyLocale` | Super admins are redirected back to the company list; users without `company_id` receive 403. |
| Login / guest redirects | `AuthenticatedSessionController`, `RedirectIfAuthenticated` | Uses `User::homePath()` to choose the super-admin or tenant landing page. |

The sidebar follows the same split: super admins only see the company list, while tenant users see planning, treasury, and workload modules.

## Company provisioning workflow

Use **Create company** from `/super-admin/companies`.

| Field | Constraint / effect |
|-------|---------------------|
| Company name | Required, max 255 characters. |
| Invoice prefix | Required, alphanumeric, max 10 characters, unique in `companies.bill_prefix`; stored uppercase and used in invoice numbers. |
| Terminology profile | Must be one of the configured company profiles (`education`, `consulting`, `medical`). |
| Administrator name/email/password | Creates the first tenant admin; email must be unique and password follows Laravel password defaults. |

`CompanyProvisioner` wraps creation in a database transaction:

1. creates `companies` with the selected invoice prefix and terminology profile;
2. creates the first tenant admin with `status_id = Status::ADMIN`, `mode = Edit`, and the new `company_id`;
3. copies the admin contact details to `companies.contact_user_id`, `email`, `phone`, and `website` when available.

Example tenant payload:

```text
company_name: Acme Formation
bill_prefix: ACM
terminology_profile: education
admin_email: alice@acme.test
```

## Managing an existing company

The company detail page (`super-admin.companies.show`) supports:

- changing the company terminology profile;
- listing users for the company;
- adding users with role **Administrator**, **Editor**, or **Reader**;
- deleting the company and its related data.

When adding a user, `CompanyUserProvisioner` sets `mode = Edit` for admin/editor roles and `mode = Browse` for readers. If the company has no contact user yet and the new user is an admin, the company contact is synchronized from that user.

## Tenant isolation and shared data

The super-admin work also scopes programs per company:

- `programs.company_id` is required after migration;
- `Program::forCurrentCompany()` limits program lists to the authenticated user's company;
- `ProgramController` rejects another company's program with 404;
- `CourseController` validates that `program_id` belongs to the current company before creating or updating a course.

This prevents tenants from seeing or attaching courses to another company's programs.

## Deleting a company

Deletion is destructive. `CompanyDeleter` clears company contact/billing account pointers, then removes related tenant data in a transaction:

- schools, courses, calendars, documents, school-user links;
- groups, group-course links, planning rows tied to the company's groups/courses;
- invoices, programs, users;
- the company record itself.

It also clears legacy planning `invoice_id` values matching the company's invoice prefix before deleting invoices. Company-owned treasury balances, expenses, banks, bank accounts, bank imports, statement lines, and reconciliations rely on their `company_id` foreign keys with `cascadeOnDelete` when the company row is removed. Use the UI danger-zone confirmation only when the tenant data should be permanently removed.

## Configuration and source map

| Purpose | File |
|---------|------|
| Registration flag | `config/vrp.php`, `.env.example` (`VRP_ALLOW_REGISTRATION`) |
| Super-admin CLI | `app/Console/Commands/CreateSuperAdminCommand.php` |
| Route boundaries | `routes/web.php`, `app/Http/Middleware/EnsureSuperAdmin.php`, `app/Http/Middleware/EnsureTenantUser.php` |
| Provisioning/deletion | `app/Services/CompanyProvisioner.php`, `CompanyUserProvisioner.php`, `CompanyDeleter.php` |
| Company UI | `app/Http/Controllers/SuperAdmin/*`, `resources/views/super-admin/companies/*` |
| Tenant program isolation | `app/Models/Program.php`, `app/Http/Controllers/ProgramController.php`, `CourseController.php` |
| Feature coverage | `tests/Feature/SuperAdmin/*`, `tests/Feature/ProgramCompanyScopeTest.php` |

## Common pitfalls

- Keep `VRP_ALLOW_REGISTRATION=false` for managed multi-tenant deployments. Public registration creates a plain user without tenant context; tenant middleware requires `company_id` before regular modules can be used.
- Do not attach business records to a super admin. The platform account intentionally has `company_id = null`.
- Choose invoice prefixes carefully. They are unique, uppercased, and used to associate legacy planning invoice identifiers.
- Run migrations before creating the first super admin; otherwise the required status and nullable `users.company_id` may not exist.

## See also

- [Configuration](configuration.md)
- [Phase 1 — terminology](phase-1-terminology.md)
- [V2 — navigation and modules](v2-navigation-modules.md)
