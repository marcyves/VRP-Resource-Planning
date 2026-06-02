# Parcours groupes (vacataire)

**EN:** [group-workflow.md](../en/group-workflow.md)

## Règles métier

| Concept | Comportement |
|---------|--------------|
| **Groupe** | Appartient à l’entreprise (`company_id`). Nom, sigle, effectif, année. |
| **Lien cours** | Table `group_course` : un groupe peut être rattaché à **plusieurs cours**. |
| **Actif / archivé** | `active = true` : visible dans les listes « actives », le planning et « groupes disponibles ». `active = false` : archivé (historique), masqué du planning mais toujours consultable. |
| **Détacher d’un cours** | Supprime la ligne `group_course` uniquement — le groupe reste dans le catalogue. |
| **Supprimer un groupe** | Impossible s’il est référencé (sessions de planning, etc.). |

## Parcours recommandé

```text
1. Créer le groupe depuis la fiche cours  →  lien automatique + groupe actif
2. Planifier des sessions (agenda)      →  choix parmi les groupes actifs du cours
3. Réutiliser sur un autre cours        →  « Groupes disponibles » sur la fiche cours
4. Fin de période                       →  archiver (icône archive) pour alléger les listes
```

## Où agir dans l’interface

| Besoin | Écran |
|--------|--------|
| Créer et rattacher à un cours | Fiche cours → **Nouveau groupe** |
| Réutiliser un groupe existant | Fiche cours → **Groupes disponibles** (flèche) |
| Voir tous les groupes / archiver | **Groupes** (`/group`) |
| Voir cours liés et sessions | Fiche groupe (`/group/{id}`) |
| Créer groupe + session en une fois | Agenda → nouvelle session → « Nouveau groupe ci-dessous » |

## Ce qui a été harmonisé (technique)

- Création : toujours `active = true` (plus de groupe « inactif » à la création depuis un cours).
- Fiche cours : groupes actifs / archivés liés séparés ; textes d’aide (`groups_course_help`, etc.).
- Groupes disponibles : uniquement groupes **actifs** non encore liés au cours.
- Planning : liste déroulante limitée aux groupes **actifs** liés au cours (`getLinkedGroups(true)`).
- Index groupes : sessions affichées aussi pour les groupes inactifs paginés.
- Sécurité : accès limité aux groupes et cours de la même entreprise.

## Liens

- [Modèle de données formation](modele-donnees-formation.md)
- [Parcours création école](parcours-creation-ecole.md)
- [V2 — refactoring listes](v2-revue-code-refactoring-listes.md)
