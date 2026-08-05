# Gestion des groupes

**EN:** [group-management.md](../en/group-management.md)

## Modèle (BDD inchangée)

| Concept | Rôle |
|---------|------|
| **Groupe** (`groups`) | Ressource entreprise : nom, effectif, année. |
| **Lien cours** (`group_course`) | Pivot : rattache un groupe à un ou plusieurs cours. |
| **Actif** (`groups.active`) | Visible dans le planning. |
| **Archivé** | Masqué du planning ; liens conservés. |

Le schéma reste N–N. **Les règles métier sont appliquées en UI + validation**, selon le mode de l’école :

| Mode école | Règle |
|------------|--------|
| **Formation** (`education`) | Un groupe ↔ **un seul** cours |
| **Mentoring** (`mentoring`) | Un étudiant ↔ **plusieurs** activités |

## Parcours recommandé

1. Ouvrir une **école**, puis un **cours** (ou activité).
2. Sur la fiche cours : **Créer un groupe** / étudiant → lié automatiquement à ce cours.
3. **Planifier** : seuls les groupes actifs liés au cours apparaissent.
4. **Mentoring seulement** : section « Étudiants disponibles » pour rattacher un étudiant déjà créé à une autre activité.
5. **Archiver** / **délier** depuis la fiche cours.

**Référentiel → Groupes** : catalogue de consultation (actif / inactif). La **création** se fait depuis la fiche cours.

## Différence archive / délier

| Action | Effet |
|--------|--------|
| **Archiver** | Global : masqué du travail courant ; liens `group_course` conservés. |
| **Délier du cours** | Retire le lien avec **ce** cours. |
| **Supprimer** | Efface le groupe (refusé s’il a des sessions). |

## Implémentation

- `CourseController@show` — hub groupes ; section « disponibles » si mentoring.
- `GroupController::linkGroupToCourse` — refuse un 2ᵉ cours en formation.
- `SchoolContext::allowsMultiCourseLink`
- `group.index` — catalogue ; création via `group.new` depuis un cours.

## Liens

- [Mode école mentoring](v2-mode-ecole-mentoring.md)
- [Modèle de données](modele-donnees-formation.md)
- [Navigation V2](v2-navigation-modules.md)
