# V2 — Navigation and modules

**FR:** [v2-navigation-modules.md](../fr/v2-navigation-modules.md)

## Application shell

| Element | Files | Role |
|---------|-------|------|
| Sidebar | `resources/views/layouts/navigation.blade.php`, `resources/js/sidebar.js`, `resources/css/shell.css`, `navigation.css` | Main menu, compact mode, logo, sign out |
| Topbar | `resources/views/layouts/topbar.blade.php` | Page title, breadcrumbs, Edit/Browse toggle, theme |
| Layout | `resources/views/layouts/app.blade.php` | Sidebar + content grid |

### Sidebar menu (order — company user)

1. **Agenda** → `planning.index` (+ **Calendar** tab `calendar.index`) — **month** / **week** views · **Invoice this month’s work** panel (schools with sessions, unbilled first). ICS import: [calendar-import.md](calendar-import.md)
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
| `scheduling-module-tabs` | Scheduling | Planning · Calendar (`/admin/calendars` — ICS import) |
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

### School and course dropdowns

`App\Support\SchoolCourseOptions` builds the lists for the agenda breadcrumb, planning school selection, the session-edit course list, and calendar-import school/course selects. A course is usable when `courses.active` is true (`Course::scopeActive`).

| Surface | Method | Omitted |
|---------|--------|---------|
| Agenda and workload breadcrumb schools | `schoolsFor` | Schools with no active course, ordered by name. **Invoice** breadcrumbs still use `User::getSchools()` (every client). |
| Agenda breadcrumb courses | `coursesForSchool` | Archived courses of the selected school. The list does **not** follow agenda `current_year`, so a 2026 course stays selectable while browsing 2027. |
| Session edit | `coursesForCompany` | Archived courses, company-wide. |
| Calendar import | `schoolsFor`, `coursesForSchool` | Same filters. Groups stay `$user->getGroups()`. |

Sort is `Course::applyListOrder`: year, semester, school name, program name, course name. Breadcrumb options read `(program list label) course name` (`Course::programListLabel()`, short description when set). The edit form shows the course name only.

`PlanningController::selectSchool` returns **403** when the school is not in `schoolsFor`. `selectCourse` loads an **active** course in the user's company (`findOrFail`, so an archived id is **404**).

The current id is kept in the list even when archived: `session('course_id')` on the breadcrumb, `planning.course_id` on edit. Posting that archived id to `planning.selectCourse` still 404s — the POST query has no exception.

School course tables (`School::getCourses`) are not filtered, so an archived course remains on `school.show`. Course create/edit does not expose the flag (`CourseController::courseRules()`). Migration `2026_10_06_120000_add_active_flag_to_courses_table` adds `courses.active` boolean, default **true**.

### New session form

`GET planning.create` (`resources/views/planning/create.blade.php`) needs `planning_create_date` and `planning_create_course_id`. Missing either key redirects to the agenda.

Three rows (`planning-session-form__row--when`, `--billing-create`, `--assignments`):

| Row | Fields | What is stored |
|-----|--------|----------------|
| When | Date (read-only), begin, end | `plannings.begin` / `end` |
| Billing | Standard duration (editable), hourly rate, intervention amount | Nothing. Duration is not written to the course or the session. `billable_rate` is not posted (column default **1**). |
| Assignment | Group, course name (read-only) | `group_id`, `course_id`. `location` is stored as `na`. |

- Standard duration starts from `courses.session_length` (comma or dot). Changing begin or duration recomputes end; changing end recomputes the duration field (Alpine on the fieldset).
- Server prefill is `Tools::planningEndFromSessionLength`: hours × 60, then rounded to the nearest 5 minutes. Example: 10:00 and `2,5` → 12:30.
- `store` keeps the submitted end when it is **strictly after** begin. If end ≤ begin, it falls back to that helper with the submitted `session_length`. It does not update `courses.session_length`.
- Begin hours **8–21**, end hours **8–23**, minutes in steps of 5.
- Hourly rate is `courses.rate` (stored **HT**). Intervention amount is `Tools::planningGain(begin, end, rate, 1)` — duration × rate. Agenda KPI cards still show TTC (`gain × 1.2`); this form does not.
- Groups are the active links of that course (`getLinkedGroups(true)`). Value `0` creates a group and a `group_course` row from the inline fields.
- Labels follow the company terminology profile.
- A successful save clears breadcrumb `course` / `course_id` and returns to `planning.index`.

### Edit session form

`planning.edit` uses the same three rows (`--when`, `--billing`, `--assignments`).

| Row | Fields | Notes |
|-----|--------|-------|
| When | Day, month, year, begin, end | Years are the session year ± 2. Begin and end hours **8–21** (create allows end hour 22–23). |
| Billing | Estimated duration, hourly rate, billable rate, billed amount | Duration, rate, and amount are read-only. Only `billable_rate` is posted. |
| Assignment | Group, course | Company-wide usable courses; the current course stays if archived. |

- The browser recomputes duration and amount when time, course, or billable rate changes. Amount = hours × selected course rate × `Tools::billableMultiplier(billable_rate, rate)`. A rate ≤ 0 counts as **1**. A value equal to the course hourly rate counts as **1** (legacy calendar rows). Any other value **> 1** is a percent (`/ 100`).
- `update` writes `begin`, `end`, `group_id`, `course_id`, and `billable_rate`. It does not rebuild end from a standard duration, and it does not reject end ≤ begin.
- `invoice_id` disables the fieldset and blocks save (`session_locked_by_invoice`). Duplicate actions are hidden.
- Group options are the active links of the course loaded on this GET, plus the current group if it is inactive. Changing the course select updates the rate preview only; it does not reload groups.
- Success goes to `school.show#billing` when `school_id` is in session, otherwise `planning.index`.

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
- `app/Support/SchoolCourseOptions.php` — usable school and course lists
- `app/Http/View/Composers/BreadcrumbComposer.php` — agenda course list (active courses, any catalogue year)
- `resources/views/planning/create.blade.php`, `edit.blade.php` — three-row session forms
- `app/Http/Utility/Tools.php` — `planningEndFromSessionLength`, `planningGain`
- `resources/js/sidebar.js` — compact sidebar persistence

## See also

- [V2 — overview](v2-user-interface.md)
- [V2 — billing per school](v2-billing-per-school.md)
- [V2 — treasury & bank reconciliation](v2-treasury-bank-reconciliation.md)
- [V2 — school mode mentoring](v2-school-mode-mentoring.md)
- [Calendar import](calendar-import.md)
