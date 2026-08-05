# V2 — Mode école (formation / mentoring)

**EN:** [v2-school-mode-mentoring.md](../en/v2-school-mode-mentoring.md)

## Intention

Une même entreprise peut mélanger **écoles classiques** et **écoles de mentoring**.  
Le mode est **par école** (`schools.context`), pas au niveau entreprise — les écrans globaux (agenda, liste écoles, trésorerie, référentiel) **ne changent pas** de libellés.

| Mode | Valeur | Programme | Cours | Groupe |
|------|--------|-----------|-------|--------|
| Formation (défaut) | `education` | Programme | Cours | Groupe |
| Mentoring | `mentoring` | Parcours | Activité | Étudiant |

## Où ça s’applique

- Fiche école (`school.show`) — badge Mentoring, en-têtes tableau cours, colonne facturation « groupe → étudiant »
- Formulaire d’édition (`school.edit`) — sélecteur « Mode de l’école »
- Fiche cours / activité — libellés + **étudiants multi-activités** (section « disponibles ») ; en formation, un groupe = un seul cours

## Où ça ne s’applique pas

Agenda, `/home`, trésorerie, sidebar, référentiel global (Programmes uniquement), plan de charge sans partage de contexte.

## Technique

| Élément | Emplacement |
|---------|-------------|
| Colonne | `schools.context` (défaut `education`) |
| Helper | `App\Support\SchoolContext` |
| Overlay i18n | `resources/lang/{fr,en}/school_mentoring.php` |
| Blade | `@schoolMsg('course')` — lit `schoolContextSchool` partagé |
| Partage | `SchoolController@show` / `@edit` → `view()->share('schoolContextSchool', $school)` |

Sans école partagée (dashboard, etc.), `@schoolMsg` retombe sur `messages.*`.
