# Business context — clients & projects

**FR:** [contexte-metier-clients-projets.md](../fr/contexte-metier-clients-projets.md)

## Goal

Reuse **the same system** (tables, routes, logic) for training **or** consulting-style work: **clients**, **projects**, **phases**, **teams**.

## Approach

| Layer | Approach |
|-------|----------|
| **UI (labels)** | Translation files + `consulting` profile |
| **Data** | Unchanged (`schools`, `courses`, …) |
| **URLs / code** | Unchanged (`/school`, `School` model, …) |

Translations are **enough for the UI**; a **per-company profile** is required if training and consulting coexist on one instance.

## Term mapping

| Technical (DB / code) | Training (`education`) | Consulting (`consulting`) |
|------------------------|------------------------|---------------------------|
| `school` | School | Client |
| `program` | Program | Project |
| `course` | Course | Phase |
| `group` | Group | Team |
| `semester` (field) | Semester | Period |
| `students` (headcount label) | students | members |

## Feasibility (summary)

| Goal | Feasibility |
|------|-------------|
| Adapt labels | **High** |
| Two domains on one instance | **Medium** (`companies.terminology_profile`) |
| Rename tables / URLs | **Low** (high cost, little benefit) |

## Out of scope for phase 1

- Renaming models or SQL migrations
- Cosmetic route aliases (`/client` → `/school`)
- Conditionally hiding the semester field (possible phase 2)

## Links

- [Phase 1 — terminology](phase-1-terminology.md)
- [Consulting labels](consulting-labels.md)
