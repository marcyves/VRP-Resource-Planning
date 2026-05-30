# V2 — Navigation and modules

**FR:** [v2-navigation-modules.md](../fr/v2-navigation-modules.md)

## Application shell

| Element | Files | Role |
|---------|-------|------|
| Sidebar | `resources/views/layouts/navigation.blade.php`, `resources/css/navigation.css`, `shell.css` | Main menu, logo, sign out |
| Topbar | `resources/views/layouts/topbar.blade.php` | Page title, breadcrumbs, Edit/Browse toggle, theme |
| Layout | `resources/views/layouts/app.blade.php` | Sidebar + content grid |

### Sidebar menu (order)

1. **Scheduling** → `planning.index` (+ admin calendar under `calendar.*`)
2. **Invoices** → `invoice.index`
3. **Treasury** → `treasury.index`
4. **Workload plan** → `home` (schools / programs / groups module)

The **logo** and 4th item both go to **`/home`** (school list).

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
| `invoice-module-tabs` | Invoices | (invoice screens) |
| `treasury-module-tabs` | Treasury | Treasury · profile |
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

## See also

- [V2 — overview](v2-user-interface.md)
- [V2 — billing per school](v2-billing-per-school.md)
