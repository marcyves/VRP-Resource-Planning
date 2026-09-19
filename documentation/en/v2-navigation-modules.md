# V2 — Navigation and modules

**FR:** [v2-navigation-modules.md](../fr/v2-navigation-modules.md)

## Application shell

| Element | Files | Role |
|---------|-------|------|
| Sidebar | `resources/views/layouts/navigation.blade.php`, `resources/js/sidebar.js`, `resources/css/shell.css`, `navigation.css` | Main menu, compact mode, logo, sign out |
| Topbar | `resources/views/layouts/topbar.blade.php` | Page title, breadcrumbs, Edit/Browse toggle, theme |
| Layout | `resources/views/layouts/app.blade.php` | Sidebar + content grid |

### Sidebar menu (order — company user)

1. **Agenda** → `planning.index` (+ admin calendar under `calendar.*`) — **month** / **week** views · **Invoice this month’s work** panel (schools with sessions, unbilled first)
2. **Schools** (terminology label) → `home`
3. **Treasury** → `treasury.index`
4. separator
5. **Referential** (submenu) → Programs · Groups

**Groups** catalog kept for browsing; **create** from the course page (training = one course / mentoring = multi — [group management](group-management.md)).
No top-level **Billing** sidebar item: preparation stays on `school.show#billing` (internal `nav.billing` shortcut kept).

The sidebar starts in compact mode unless `vrp-sidebar-compact` is `false` in `localStorage`.

## Home page

| Route | Name | Controller | Content |
|-------|------|--------------|---------|
| `/home` | `home` | `SchoolController@index` | School list, billing stats, chart, link to annual workload |
| `/dashboard` | `dashboard` | redirect | Alias → `home` |

Post-login constant: `RouteServiceProvider::HOME = '/home'`.

### Per-school indicators (list)

- **Invoiced incl. VAT** — sum of invoices for the current year
- **Unbilled incl. VAT** — sessions without `invoice_id`

## Module tabs

| Component | Module | Tabs |
|-----------|--------|------|
| `scheduling-module-tabs` | Scheduling | Planning · Calendar |
| `treasury-module-tabs` | Treasury | Summary · Invoices · Bank · Expenses |
| `referential-module-tabs` | Referential | Programs · Groups |
| `settings-module-tabs` | Settings | Company · profile |

**Create invoice** / **Create expense** are in-page buttons, not tabs.  
`workload-module-tabs` is no longer used on the daily path (annual workload link from `/home`).

## Agenda views (month / week)

`PlanningController@index` persists the selected view in session (`planning_view`). Query `?view=month` or `?view=week`; unknown values fall back to **month**. Toggle: `resources/views/components/planning-view-toggle.blade.php`.

| View | Period | Grid | Session match |
|------|--------|------|---------------|
| **Month** (default) | Calendar month (`Planning::getDetails`) | Classic 7-column month cells (`planning-day`) | Full ISO date of `begin` |
| **Week** | Monday–Sunday (`Planning::getDetailsBetween`) | Timed grid 08:00–20:00 (`planning-week-agenda`) | Full ISO date of `begin` (not day number) |

Prev / next (`planning.previous` / `planning.next`) call `Tools::shiftPlanningPeriod()`: **±1 week** in week view (month/year follow the Monday), **±1 month** in month view. Week start is stored in `planning_week_start`; changing the month selector resets it to the first Monday of that month (or today if the displayed month is current).

KPI cards reuse the **visible period** (week or month). Amounts display TTC (`gain × 1.2`). Events outside 08:00–20:00 are clamped / hidden (`Tools::weekEventPosition()`). Days that belong to the adjacent month get `--outside` styling.

Agenda lists sessions by **session date**, not by `courses.year`. A 2026 course with a January 2027 session still appears when browsing January 2027.

### Create a session from the grid

Requires **Edit** mode **and** a course selected in the breadcrumb (`session('course_id')`). Without a course, day numbers are disabled (`planning_select_course_first`).

Hidden form `#planning-create-form` posts to `planning.create.start` (`PlanningController::startCreate`). Click handlers in `resources/js/planning-calendar.js` copy `data-create-hour` / `data-create-minutes` into the form before submit.

| Trigger | Default time |
|---------|--------------|
| Month cell day number, or week day header | 08:00 |
| Week time slot (08h–19h) | That hour, `:00` |

`startCreate` stores `planning_create_date`, `planning_create_course_id`, hour and minutes, then redirects to `planning.create` (PRG). Hour is validated `8–19`.

The planning breadcrumb course selector lists **every course of the selected school** (`School::getCourses('all')` in `BreadcrumbComposer`) — it does not filter on the agenda calendar year, so a 2026 course remains selectable while browsing 2027.

### Session duplication

From a day cell, week event, or session edit (Edit mode): copy a session to **tomorrow**, **next week**, or a **custom date** without re-entering course, group, location, or rate.

| Offset | Schedule |
|--------|----------|
| `tomorrow` | Same clock time, +1 day |
| `next_week` | Same clock time, +1 week |
| `custom` | Chosen date, original start time; duration preserved |

Blocked when the source has an `invoice_id`. Rejected if another session for the same `group_id` overlaps. Route: `POST /planning/{id}/duplicate` (`PlanningController::duplicate`).

Custom date uses the native dialog `#planning-duplicate-dialog` (not Alpine). Triggers set `data-duplicate-url` and `data-duplicate-date`; `planning-calendar.js` copies them onto the dialog form. Same dialog on the month grid, week events, and session edit page.

## Workload plan vs school list

| Screen | Route | Purpose |
|--------|-------|---------|
| **School list** | `home` / `school.index` | Entry point, financial summary |
| **Workload plan** | `school.dashboard` | Annual KPIs, course tables per school |

## Key files

- `routes/web.php` — `nav.billing`, `home`, business resources
- `app/Http/Controllers/BillingNavController.php` — Billing shortcut
- `resources/views/components/sidebar-nav-group.blade.php`
- `resources/views/components/treasury-module-tabs.blade.php`
- `resources/views/components/planning-view-toggle.blade.php` — month / week switch
- `resources/views/components/planning-week-agenda.blade.php` — timed week grid + slot create
- `resources/js/planning-calendar.js` — create-from-grid hour, native duplicate/delete dialogs
- `app/Http/View/Composers/BreadcrumbComposer.php` — all-school course list on planning
- `resources/js/sidebar.js` — compact sidebar persistence

## See also

- [V2 — overview](v2-user-interface.md)
- [V2 — billing per school](v2-billing-per-school.md)
- [V2 — treasury & bank reconciliation](v2-treasury-bank-reconciliation.md)
- [V2 — school mode mentoring](v2-school-mode-mentoring.md)
