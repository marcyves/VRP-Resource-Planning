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
