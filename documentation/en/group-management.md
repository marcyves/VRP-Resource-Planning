# Group management

**FR:** [gestion-groupes.md](../fr/gestion-groupes.md)

## Simplified model

| Concept | Role |
|---------|------|
| **Group** (`groups`) | Company resource: name, size, year. |
| **Course link** (`group_course`) | Attaches a group to one or more courses. |
| **Active** (`groups.active`) | Shown in planning and work lists. |
| **Archived** (`active = false`) | Kept for history; hidden from planning and pickers. |

A group does **not** belong to a single course: it belongs to the **company** and is **linked** to courses via `group_course`.

## Recommended workflow (adjunct teacher)

1. **Create or open a course** (course detail page).
2. **Create a group** via “New group” or from `/group` → group is active and **automatically linked to the course in session** if you opened a course page first.
3. **Schedule sessions** → only **active groups linked to the course** appear in planning.
4. **Reuse** a group on another course → “Available groups” section → link (arrow).
5. **End of year** → archive the group — hidden from planning but kept in the database.
6. **Remove from one course only** → trash on the course page (removes the link, not the group).

## Archive vs unlink

| Action | Effect |
|--------|--------|
| **Archive** | Global: hidden from current work; `group_course` links kept. |
| **Unlink from course** | Removes link to **this** course only; group stays active elsewhere. |
| **Delete** | Removes the group (blocked if planning sessions exist). |

## Screens

- **Course page**: active linked groups, then archived still linked, then “available” pool (active, not linked).
- **Group list** (`/group`): active catalog + paginated “Inactive groups”.

## Code reference

- `Course::getLinkedGroups(?bool $active)`
- `Course::getAvailableGroups()`
- `GroupController::store` — new groups always `active = true`
- `PlanningController` — course-linked active groups only

## Links

- [Training data model](training-data-model.md)
- [School creation workflow](school-creation-workflow.md)
