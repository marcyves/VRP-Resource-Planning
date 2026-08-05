# V2 — School mode (training / mentoring)

**FR:** [v2-mode-ecole-mentoring.md](../fr/v2-mode-ecole-mentoring.md)

## Intent

One company can mix **classic schools** and **mentoring schools**.  
Mode is **per school** (`schools.context`), not company-wide — global screens (agenda, school list, treasury, referential) keep their usual labels.

| Mode | Value | Program | Course | Group |
|------|-------|---------|--------|-------|
| Training (default) | `education` | Program | Course | Group |
| Mentoring | `mentoring` | Pathway | Activity | Student |

## Where it applies

- School detail (`school.show`) — Mentoring badge, course table headers, billing “group → student”
- Edit form (`school.edit`) — “School mode” select
- Course / activity page — labels + **students on multiple activities** (“available” section); in training, one group = one course

## Where it does not apply

Agenda, `/home`, treasury, sidebar, global referential (Programs only), workload views without shared school context.

## Technical

| Piece | Location |
|-------|----------|
| Column | `schools.context` (default `education`) |
| Helper | `App\Support\SchoolContext` |
| i18n overlay | `resources/lang/{fr,en}/school_mentoring.php` |
| Blade | `@schoolMsg('course')` — reads shared `schoolContextSchool` |
| Share | `SchoolController@show` / `@edit` → `view()->share('schoolContextSchool', $school)` |

Without a shared school (dashboard, etc.), `@schoolMsg` falls back to `messages.*`.
