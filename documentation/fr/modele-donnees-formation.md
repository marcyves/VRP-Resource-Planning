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
- **`groups.active`** masque un groupe du planning sans supprimer ses liens ni son historique.
- **`courses.active`** (booléen, défaut true) est distinct. Il masque un cours des **menus déroulants** agenda, planning et calendrier (`SchoolCourseOptions`, `Course::scopeActive`). Les tableaux de cours de l’école incluent toujours les cours archivés, et la fiche cours n’édite pas le flag. Voir [Navigation — menus déroulants](v2-navigation-modules.md#menus-déroulants-école-et-cours).
- Les **sessions** de planning (`plannings`) pointent vers `course_id` et `group_id`. La création manuelle enregistre la `end` soumise (préremplie depuis `courses.session_length`) ; cette durée n’est pas une colonne de `plannings`.
- Les **factures** peuvent pointer vers un cours (`invoices.course_id`, nullable, `nullOnDelete`). `null` signifie le mois entier de l’école. `invoices.comment` (optionnel, max 2000) est un texte libre sur le PDF et la note CII `AAI`. Voir [Facturation par école](v2-facturation-par-ecole.md#périmètre--un-cours-ou-tout-le-mois).

## Liens

- [Gestion des groupes](gestion-groupes.md)
- [Parcours de création](parcours-creation-ecole.md)
- [Contexte clients & projets](contexte-metier-clients-projets.md)
