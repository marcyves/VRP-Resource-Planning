# Gestion des groupes

**EN:** [group-management.md](../en/group-management.md)

## Modèle simplifié

| Concept | Rôle |
|---------|------|
| **Groupe** (`groups`) | Ressource de l’entreprise : nom, effectif, année. |
| **Lien cours** (`group_course`) | Rattache un groupe à un ou plusieurs cours. |
| **Actif** (`groups.active`) | Visible dans le planning et les listes de travail. |
| **Archivé** (`active = false`) | Conservé pour l’historique ; masqué du planning et des sélections. |

Un groupe **n’appartient pas** à un seul cours : il appartient à l’entreprise et se **lie** aux cours via `group_course`.

## Parcours recommandé (vacataire)

1. **Créer ou ouvrir un cours** (fiche cours).
2. **Créer un groupe** depuis « Nouveau groupe » ou depuis la liste `/group` → le groupe est actif et **lié automatiquement au cours en session** (`course_id` / `course` dans la session), si vous avez ouvert une fiche cours avant.
3. **Planifier** : seuls les groupes **actifs liés au cours** apparaissent dans l’agenda.
4. **Réutiliser** un groupe sur un autre cours : section « Groupes disponibles » → lier (flèche).
5. **Clôturer une année** : archiver le groupe (icône archive) — il disparaît du planning mais reste en base.
6. **Retirer d’un cours seulement** : corbeille sur la fiche cours (supprime le lien, pas le groupe).

## Différence archive / délier

| Action | Effet |
|--------|--------|
| **Archiver** | Global : plus visible nulle part pour le travail courant ; liens `group_course` conservés. |
| **Délier du cours** | Retire uniquement le lien avec **ce** cours ; le groupe reste actif pour les autres cours. |
| **Supprimer** | Efface le groupe (refusé s’il a des sessions planifiées). |

## Vues

- **Fiche cours** : groupes actifs, puis archivés encore liés, puis pool « disponibles » (actifs non liés).
- **Liste groupes** (`/group`) : catalogue actif + section « Groupes inactifs » paginée.

## Implémentation (référence code)

- `Course::getLinkedGroups(?bool $active)` — groupes liés, filtre actif optionnel.
- `Course::getAvailableGroups()` — actifs non liés à ce cours.
- `GroupController::store` — nouveaux groupes toujours `active = true` ; lien `group_course` si création depuis un cours.
- `PlanningController` — sélection limitée aux groupes actifs liés au cours.

## Liens

- [Modèle de données](modele-donnees-formation.md)
- [Parcours de création](parcours-creation-ecole.md)
