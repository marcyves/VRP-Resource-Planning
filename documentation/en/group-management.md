# Group management

**FR:** [gestion-groupes.md](../fr/gestion-groupes.md)

## Model (unchanged DB)

| Concept | Role |
|---------|------|
| **Group** (`groups`) | Company resource: name, size, year. |
| **Course link** (`group_course`) | Pivot attaching a group to one or more courses. |
| **Active** | Shown in planning. |
| **Archived** | Hidden from planning; links kept. |

Schema stays many-to-many. **Business rules are UI + validation**, by school mode:

| School mode | Rule |
|-------------|------|
| **Training** (`education`) | One group ↔ **one** course |
| **Mentoring** (`mentoring`) | One student ↔ **several** activities |

## Recommended workflow

1. Open a **school**, then a **course** (or activity).
2. On the course page: **Create group** / student → auto-linked to that course.
3. **Schedule**: only active groups linked to the course appear.
4. **Mentoring only**: “Available students” to attach an existing student to another activity.
5. **Archive** / **unlink** from the course page.

**Referential → Groups**: browse catalog (active / inactive). **Create** from the course page.

## Archive vs unlink

| Action | Effect |
|--------|--------|
| **Archive** | Global: hidden from current work; `group_course` links kept. |
| **Unlink** | Removes link to **this** course. |
| **Delete** | Removes the group (blocked if sessions exist). |

## Implementation

- `CourseController@show` — groups hub; “available” section if mentoring.
- `GroupController::linkGroupToCourse` — rejects a second course in training mode.
- `SchoolContext::allowsMultiCourseLink`
- `group.index` — catalog; create via `group.new` from a course.

## Links

- [School mode mentoring](v2-school-mode-mentoring.md)
- [Training data model](training-data-model.md)
- [V2 — navigation](v2-navigation-modules.md)
- [Calendar import](calendar-import.md)
