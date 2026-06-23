# V2 — Navigation and modules

**FR:** [v2-navigation-modules.md](../fr/v2-navigation-modules.md)

## Application shell

| Element | Files | Role |
|---------|-------|------|
| Sidebar | `resources/views/layouts/navigation.blade.php`, `resources/js/sidebar.js`, `resources/css/navigation.css`, `shell.css` | Main menu, compact mode, logo, sign out |
| Topbar | `resources/views/layouts/topbar.blade.php` | Page title, breadcrumbs, Edit/Browse toggle, theme |
| Layout | `resources/views/layouts/app.blade.php` | Sidebar + content grid |

### Sidebar menu (order)

1. **Scheduling** → `planning.index` (+ admin calendar under `calendar.*`)
2. **Treasury** → `treasury.index` (also active for `invoice.*`)
3. **Workload plan** → `home` (schools / programs / groups module)

The **logo** and 3rd item both go to **`/home`** (school list). The sidebar starts in compact mode unless `vrp-sidebar-compact` is set to `false` in `localStorage`.

## Home page

| Route | Name | Controller | Content |
|-------|------|--------------|---------|
| `/home` | `home` | `SchoolController@index` | School list, billing stats, pie chart |
| `/dashboard` | `dashboard` | redirect | Alias → `home` |

Post-login constant: `RouteServiceProvider::HOME = '/home'`.

### Per-school indicators (list)

- **Invoiced incl. VAT** — sum of invoices for the current year
- **Unbilled** — ex-VAT amount + hours for sessions without `invoice_id` (same formula as billing preparation)

## Module tabs

Components under `resources/views/components/`:

| Component | Module | Tabs |
|-----------|--------|------|
| `workload-module-tabs` | Schools / workload | Workload plan · Schools · Programs · Groups |
| `scheduling-module-tabs` | Scheduling | Planning · Calendar |
| `treasury-module-tabs` | Treasury | Summary · Invoices · Create invoice · Bank · Expense reports · Standalone expenses · Create expense |
| `settings-module-tabs` | Settings | Company · profile |

Generic `module-tabs` + `module-tab-icon` handle rendering.

## Workload plan vs school list

| Screen | Route | Purpose |
|--------|-------|---------|
| **School list** | `home` / `school.index` | Entry point, financial summary |
| **Workload plan** | `school.dashboard` | Annual KPIs, course tables per school |

## Key files

- `routes/web.php` — `home`, `dashboard`, business resources
- `app/Providers/RouteServiceProvider.php` — `HOME`
- `resources/views/components/workload-module-tabs.blade.php`
- `resources/views/components/treasury-module-tabs.blade.php`
- `resources/js/sidebar.js` — compact sidebar persistence

## See also

- [V2 — overview](v2-user-interface.md)
- [V2 — billing per school](v2-billing-per-school.md)
- [V2 — treasury & bank reconciliation](v2-treasury-bank-reconciliation.md)
