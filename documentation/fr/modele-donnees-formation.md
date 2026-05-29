# Modèle de données — formation

**EN:** [training-data-model.md](../en/training-data-model.md)

## Hiérarchie métier

```text
Entreprise (company)
 └── École (school)
      └── Cours (course) ──► Programme (program) [catalogue global]
            └── Groupe (group) via table pivot group_course
```

## Tables principales

| Entité | Table | Rattachement |
|--------|-------|----------------|
| École | `schools` | `company_id` |
| Programme | `programs` | Global (pas lié à une école) |
| Cours | `courses` | `school_id` + `program_id` |
| Groupe | `groups` | `company_id` |
| Lien cours ↔ groupe | `group_course` | `course_id`, `group_id` |

## Modèles Laravel

- `App\Models\School`
- `App\Models\Program`
- `App\Models\Course`
- `App\Models\Group`
- `App\Models\GroupCourse`

## Points importants

- Un **programme** est partagé entre écoles : catalogue réutilisable.
- Un **groupe** appartient à l’**entreprise** ; le lien au cours passe par **`group_course`** (un groupe peut être rattaché à plusieurs cours via « lier »).
- Les **sessions** de planning (`plannings`) pointent vers `course_id` et `group_id`.

## Liens

- [Parcours de création](parcours-creation-ecole.md)
- [Contexte clients & projets](contexte-metier-clients-projets.md)
