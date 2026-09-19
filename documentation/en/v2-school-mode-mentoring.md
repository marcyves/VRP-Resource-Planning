# V2 — School mode (training / mentoring)

**FR:** [v2-mode-ecole-mentoring.md](../fr/v2-mode-ecole-mentoring.md)

## Intent

One company can mix **classic schools** and **mentoring schools**.  
Mode is **per school** (`schools.context`), not company-wide — global screens (agenda, school list, treasury, referential) keep their usual labels.

| Mode | Value | Program | Course | Group |
|------|-------|---------|--------|-------|
| Training (default) | `education` | Program | Course | Group |
| Mentoring | `mentoring` | Pathway | Activity | Student |

## School detail panels

`school.show` is tabbed via `?panel=` (`resources/views/components/school-detail-tabs.blade.php`):

| Panel | Query | Content |
|-------|-------|---------|
| Courses / activities | `courses` (default) | Course table + billing block |
| Groups / students | `groups` | Linked active/inactive groups (occurrences for all years) |
| Details | `details` | Address / school metadata (`address` alias redirects here) |
| Documents | `documents` | School documents (PDF/DOC/DOCX, max 4 MB — see [school creation](school-creation-workflow.md#school-documents)) |

`?focus=billing` forces the courses panel (billing lives there). After edit, redirect lands on `?panel=details`.

## Where it applies

- School detail (`school.show`) — Mentoring badge, course table headers, billing “group → student”, tab labels via `@schoolMsg`
- Edit form (`school.edit`) — “School mode” select
- Course / activity page — labels + **students on multiple activities** (“available” section); in training, one group = one course
- Group create/edit when opened from a course (`returnCourseId`) — shares `schoolContextSchool`

## Breadcrumb school switch

On school show/edit, changing the breadcrumb school must open **that** school (not bounce to the previous URL which would re-bind session).

| Entry | Behaviour |
|-------|-----------|
| `PlanningController` / `InvoiceController` `selectSchool` | Rewrites `/school/{id}` and `/school/{id}/edit` redirects to the newly selected school, preserving query string |
| Session | Sets `school`, `school_id`, `last_school_id`; clears course context |

## Where it does not apply

Agenda, `/home`, treasury, sidebar, global referential (Programs only), workload views without shared school context.

## Technical

| Piece | Location |
|-------|----------|
| Column | `schools.context` (default `education`) |
| Helper | `App\Support\SchoolContext` (`allowsMultiCourseLink`, `msg`) |
| i18n overlay | `resources/lang/{fr,en}/school_mentoring.php` |
| Blade | `@schoolMsg('course')` — reads shared `schoolContextSchool` |
| Share | `SchoolController@show` / `@edit`, and `GroupController` when course-scoped |
| Tabs | `school-detail-tabs` + `SchoolController@show` panel loading |

Without a shared school (dashboard, etc.), `@schoolMsg` falls back to `messages.*`.

## See also

- [Group management](group-management.md) — training one-course vs mentoring multi-activity rules
- [V2 — navigation & modules](v2-navigation-modules.md)
- [V2 — billing per school](v2-billing-per-school.md)
- [Calendar import](calendar-import.md)
