# V2 — Mode école (formation / mentoring)

**EN:** [v2-school-mode-mentoring.md](../en/v2-school-mode-mentoring.md)

## Intention

Une même entreprise peut mélanger **écoles classiques** et **écoles de mentoring**.  
Le mode est **par école** (`schools.context`), pas au niveau entreprise — les écrans globaux (agenda, liste écoles, trésorerie, référentiel) **ne changent pas** de libellés.

| Mode | Valeur | Programme | Cours | Groupe |
|------|--------|-----------|-------|--------|
| Formation (défaut) | `education` | Programme | Cours | Groupe |
| Mentoring | `mentoring` | Parcours | Activité | Étudiant |

## Panneaux de la fiche école

`school.show` est à onglets via `?panel=` (`resources/views/components/school-detail-tabs.blade.php`) :

| Panneau | Query | Contenu |
|---------|-------|---------|
| Cours / activités | `courses` (défaut) | Tableau des cours + bloc facturation |
| Groupes / étudiants | `groups` | Groupes actifs / inactifs liés à l’école |
| Détails | `details` | Adresse / métadonnées (`address` redirige ici) |
| Documents | `documents` | Documents de l’école |

`?focus=billing` force le panneau cours (la facturation y vit). Après édition, la redirection atterrit sur `?panel=details`.

## Où ça s’applique

- Fiche école (`school.show`) — badge Mentoring, en-têtes tableau cours, colonne facturation « groupe → étudiant », libellés d’onglets via `@schoolMsg`
- Formulaire d’édition (`school.edit`) — sélecteur « Mode de l’école »
- Fiche cours / activité — libellés + **étudiants multi-activités** (section « disponibles ») ; en formation, un groupe = un seul cours
- Création / édition de groupe depuis un cours (`returnCourseId`) — partage `schoolContextSchool`

## Changement d’école dans le fil d’Ariane

Sur fiche école show/edit, changer l’école du fil d’Ariane doit ouvrir **cette** école (et non revenir à l’URL précédente qui ré-écrirait la session).

| Point d’entrée | Comportement |
|----------------|--------------|
| `PlanningController` / `InvoiceController` `selectSchool` | Réécrit `/school/{id}` et `/school/{id}/edit` vers l’école nouvellement choisie, query string conservée |
| Session | Pose `school`, `school_id`, `last_school_id` ; efface le contexte cours |

## Où ça ne s’applique pas

Agenda, `/home`, trésorerie, sidebar, référentiel global (Programmes uniquement), plan de charge sans partage de contexte.

## Technique

| Élément | Emplacement |
|---------|-------------|
| Colonne | `schools.context` (défaut `education`) |
| Helper | `App\Support\SchoolContext` (`allowsMultiCourseLink`, `msg`) |
| Overlay i18n | `resources/lang/{fr,en}/school_mentoring.php` |
| Blade | `@schoolMsg('course')` — lit `schoolContextSchool` partagé |
| Partage | `SchoolController@show` / `@edit`, et `GroupController` en contexte cours |
| Onglets | `school-detail-tabs` + chargement panneau dans `SchoolController@show` |

Sans école partagée (dashboard, etc.), `@schoolMsg` retombe sur `messages.*`.

## Voir aussi

- [Gestion des groupes](gestion-groupes.md) — règle un cours (formation) vs multi-activités (mentoring)
- [V2 — navigation & modules](v2-navigation-modules.md)
- [V2 — facturation par école](v2-facturation-par-ecole.md)
