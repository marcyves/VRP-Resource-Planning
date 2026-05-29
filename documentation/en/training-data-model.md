# Training data model

**FR:** [modele-donnees-formation.md](../fr/modele-donnees-formation.md)

## Business hierarchy

```text
Company
 └── School
      └── Course ──► Program [global catalogue]
            └── Group via group_course pivot
```

## Main tables

| Entity | Table | Attachment |
|--------|-------|------------|
| School | `schools` | `company_id` |
| Program | `programs` | Global (not tied to a school) |
| Course | `courses` | `school_id` + `program_id` |
| Group | `groups` | `company_id` |
| Course ↔ group link | `group_course` | `course_id`, `group_id` |

## Laravel models

- `App\Models\School`
- `App\Models\Program`
- `App\Models\Course`
- `App\Models\Group`
- `App\Models\GroupCourse`

## Important notes

- A **program** is shared across schools: reusable catalogue.
- A **group** belongs to the **company**; the link to a course uses **`group_course`** (one group can be linked to several courses via “link”).
- **Planning** sessions (`plannings`) reference `course_id` and `group_id`.

## Links

- [Creation workflow](school-creation-workflow.md)
- [Clients & projects context](business-context-clients-projects.md)
