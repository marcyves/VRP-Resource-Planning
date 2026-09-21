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
| Programme | `programs` | `company_id` (pas rattaché à une école) |
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

- Un **programme** appartient à l’**entreprise** (`programs.company_id`, `Program::forCurrentCompany()`). Il est réutilisable entre les écoles de **cette** entreprise, pas entre tenants. Les listes affichent `short_description` (nullable, max 80) s’il est renseigné, sinon `name` (`Program::listLabel()`).
- **`schools.code`** est optionnel. S’il est saisi, il doit être unique **dans l’entreprise** (validation applicative à la création/édition — pas d’index unique en base). Voir [Parcours de création](parcours-creation-ecole.md#code-école-optionnel-unique-par-entreprise).
- Un **groupe** appartient à l’**entreprise** ; le lien au cours passe par **`group_course`** (un groupe peut être rattaché à plusieurs cours via « lier »).
- Le flag **`active`** masque un groupe du planning sans supprimer ses liens ni son historique.
- Les **sessions** de planning (`plannings`) pointent vers `course_id` et `group_id`.

## Liens

- [Gestion des groupes](gestion-groupes.md)
- [Parcours de création](parcours-creation-ecole.md)
- [Contexte clients & projets](contexte-metier-clients-projets.md)
