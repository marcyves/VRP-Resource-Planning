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
| Program | `programs` | `company_id` (not tied to a school) |
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

- A **program** belongs to the **company** (`programs.company_id`, `Program::forCurrentCompany()`). It is reusable across that company's schools, not across tenants. List labels prefer `short_description` (nullable, max 80) over `name` (`Program::listLabel()`).
- **`schools.code`** is optional. When set, it must be unique **inside the company** (application validation on create/update — no unique DB index). See [Creation workflow](school-creation-workflow.md#school-code-optional-unique-per-company).
- A **group** belongs to the **company**; the link to a course uses **`group_course`** (one group can be linked to several courses via “link”).
- The **`active`** flag hides a group from planning without removing links or history.
- **Planning** sessions (`plannings`) reference `course_id` and `group_id`.

## Links

- [Group management](group-management.md)
- [Creation workflow](school-creation-workflow.md)
- [Clients & projects context](business-context-clients-projects.md)
