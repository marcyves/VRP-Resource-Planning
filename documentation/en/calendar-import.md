# Calendar import (ICS)

**FR:** [import-calendrier.md](../fr/import-calendrier.md)

Operational guide for the **external calendar → VRP** flow. Agenda views, duplication, and billing stay in [navigation](v2-navigation-modules.md) and [billing per school](v2-billing-per-school.md).

## Intent

Operators paste an `.ics` file or a remote ICS URL onto a **school**. VRP extracts unique labels, the operator maps each label to a **course and a group**, then sessions are created in `plannings`. Mappings are remembered **per school** for later imports.

This is one-way. VRP does not export an ICS feed.

## Where it lives

Agenda sidebar → **Calendar** tab (`scheduling-module-tabs`).

| Route | Name | Action |
|-------|------|--------|
| `GET /admin/calendars` | `calendar.index` | Upload form + import history |
| `POST /admin/calendars/upload` | `calendar.upload` | Analyse file/URL, then mapping screen |
| `POST /admin/calendars/import` | `calendar.import` | Persist mappings and create sessions |
| `POST /admin/calendars/reimport/{source}` | `calendar.reimport` | Re-run with stored mappings (summary field) |
| `DELETE /admin/calendars/delete/{source}` | `calendar.destroy` | Delete the `calendar_sources` row |

Auth + tenant middleware. **Reimport** and **Delete** buttons render only in **Edit** mode.

```text
Agenda → Calendar
  POST /admin/calendars/upload
    → CalendarFileController::upload
        → CalendarService::getUniqueLabelsFromIcs (SUMMARY)
        → calendar.mapping view
  POST /admin/calendars/import
    → CalendarMapping::updateOrCreate (per school + label)
    → CalendarService::executeFinalImport
        → Planning::create (billable_rate = 1)
```

## Workflow

1. Open **Agenda → Calendar**.
2. Choose the **destination school**.
3. Either upload an `.ics` file **or** paste a public ICS URL (not both required by validation — supply one).
4. **Analyse** opens the mapping screen with one sample event (summary, schedule, location, description).
5. Keep the ICS source field on **Titre (Summary)** unless you know the event lookup key matches the mapping rows (see constraints).
6. For each detected label, pick a **course** (pricing) **and** a **group**. Rows with neither are skipped.
7. **Validate and import**. Flash reports `created` vs `skipped` (already overlapping, or unmapped).
8. Sessions appear on the planning month/week grids like manually created ones.

History table: date, school, file name or remote-feed link, plus reimport / delete in Edit mode.

## Mapping and persistence

| Piece | Behavior |
|-------|----------|
| Label extraction | Unique values of ICS **SUMMARY** (`CalendarService::getUniqueLabelsFromIcs`) |
| Lookup at import | `$event[$ics_source_field]` (form default: summary; options: description, location) |
| Mapping UI | Course select = courses of that school; group select = `$user->getGroups()` (company groups, not school-filtered) |
| Stored mapping | One morph per `(school_id, ics_label)`: **course if set, otherwise group** (`CalendarMapping`) |
| First import | Uses the form arrays (`course_id` + `group_id`) so both IDs can be applied together |
| Reimport | Rebuilds mappings from `CalendarMapping` only, and always uses field `summary` |

`CalendarMatcher` (name-contains heuristics) is **not** wired to this UI.

## Session creation rules (`executeFinalImport`)

Parser: `u01jmg3/ics-parser` (`ICal`), timezone **Europe/Paris**, `defaultSpan` **2** (recurring expansion window).

| Rule | Detail |
|------|--------|
| Required pair | Both `course_id` and `group_id` must resolve. Group-only rows take the group's **first** linked course. Course-only rows are **skipped**. |
| Collision | Skip if another session for the same `group_id` overlaps `[begin, end)`. |
| Location | Copied from the ICS `LOCATION`. |
| Rate | `billable_rate` is stored as **1** (full duration). Course hourly rate is **not** copied into `billable_rate`. |
| Source FK | The create payload includes `calendar_source_id`, but `Planning::$fillable` does **not**, so Eloquent discards it. Imported rows are not tied to the source for cascade delete. |

`Tools::billableMultiplier()` still treats a `billable_rate` that equals the course hourly rate as **1** (legacy imports that stored the rate by mistake). Values `> 1` that are not the course rate are treated as percents (`/ 100`).

## Constraints and pitfalls

- **Both course and group** are required to insert a planning row. The mapping column is labelled “group (optional)” in the UI; the importer still skips course-only rows.
- **Reimport after mapping a course** (the stored morph prefers course) typically skips every event, because the rebuilt mapping has no `group_id`. Reimport is reliable only when the stored morph is a **group** (then the first linked course is inferred).
- Changing **ICS source field** to description/location while mapping rows were built from **summaries** makes lookup keys miss → all skipped.
- Upload validation allows both `ics_file` and `ics_url` to be empty (`nullable`). Always provide one.
- Destroy deletes the `calendar_sources` row. It tries `Storage::delete('calendars/'.$filename)`, which is not the hashed `storage_path` written at upload, so the file under `storage/app/calendars/` is often left behind. Sessions remain (no stored `calendar_source_id`).
- Remote URL is fetched server-side; the host must be reachable from the app (no auth header support in code).
- No tenant filter on `CalendarSource::latest()` in `index` — history is global to the database, not `company_id`.

## Architecture

| Piece | Path |
|-------|------|
| Routes | `routes/web.php` prefix `admin/calendars` |
| Controller | `app/Http/Controllers/CalendarFileController.php` |
| Parser / import | `app/Services/CalendarService.php` |
| Unused matcher | `app/Services/CalendarMatcher.php` |
| Models | `CalendarSource`, `CalendarMapping` (morph to `Course` or `Group`) |
| Views | `resources/views/calendar/manage.blade.php`, `mapping.blade.php` |
| Tabs | `resources/views/components/scheduling-module-tabs.blade.php` |
| Tables | `calendar_sources`, `calendar_mappings`, `plannings.calendar_source_id` (nullable FK, cascade if set) |

`CalendarController::readICSFile` (JSON parse) is unused; its route is commented out.

## See also

- [V2 — navigation & modules](v2-navigation-modules.md)
- [Group management](group-management.md)
- [V2 — billing per school](v2-billing-per-school.md) (`billableMultiplier`)
- [Roadmap — PWA & offline](roadmap-pwa-offline.md) — ICS remains the only external → VRP feed
