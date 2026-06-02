# Group workflow (instructor)

**FR:** [parcours-groupes.md](../fr/parcours-groupes.md)

## Business rules

| Concept | Behavior |
|---------|----------|
| **Group** | Belongs to the company (`company_id`). Name, short name, size, year. |
| **Course link** | `group_course` pivot: one group can be linked to **several courses**. |
| **Active / archived** | `active = true`: shown in active lists, planning, and “available groups”. `active = false`: archived (history), hidden from planning but still viewable. |
| **Detach from a course** | Deletes the `group_course` row only — the group remains in the catalog. |
| **Delete a group** | Blocked when referenced (planning sessions, etc.). |

## Recommended flow

```text
1. Create the group from the course page  →  auto-link + active group
2. Schedule sessions (calendar)             →  pick from active groups on that course
3. Reuse on another course                →  “Available groups” on the course page
4. End of term                            →  archive (archive icon) to simplify lists
```

## Where to act in the UI

| Goal | Screen |
|------|--------|
| Create and attach to a course | Course page → **New group** |
| Reuse an existing group | Course page → **Available groups** (arrow) |
| Browse all groups / archive | **Groups** (`/group`) |
| See linked courses and sessions | Group detail (`/group/{id}`) |
| Create group + session at once | Calendar → new session → “New group below” |

## Technical alignment

- Creation always sets `active = true`.
- Course page splits active vs archived linked groups with inline help.
- Available groups: active company groups not yet linked to the course.
- Planning dropdown: active linked groups only (`getLinkedGroups(true)`).
- Group index: planning counts include paginated inactive groups.
- Access scoped to the user’s company for groups and courses.

## Links

- [Training data model](training-data-model.md)
- [School creation workflow](school-creation-workflow.md)
