# V2 — Navigation et modules

**EN:** [v2-navigation-modules.md](../en/v2-navigation-modules.md)

## Coque applicative

| Élément | Fichiers | Rôle |
|---------|----------|------|
| Sidebar | `resources/views/layouts/navigation.blade.php`, `resources/js/sidebar.js`, `resources/css/shell.css`, `navigation.css` | Menu principal, mode compact, logo, déconnexion |
| Topbar | `resources/views/layouts/topbar.blade.php` | Titre de page, fil d’Ariane, bascule Edit/Browse, thème |
| Layout | `resources/views/layouts/app.blade.php` | Grille sidebar + contenu |

### Menu latéral (ordre — utilisateur entreprise)

1. **Agenda** → `planning.index` (+ onglet **Calendrier** `calendar.index`) — vues **mois** / **semaine** · panneau **Facturer les interventions du mois** (écoles avec sessions, priorisation non facturé). Import ICS : [import-calendrier.md](import-calendrier.md)
2. **Écoles** (libellé terminologique) → `home`
3. **Trésorerie** → `treasury.index`
4. séparateur
5. **Référentiel** (sous-menu) → Programmes · Groupes

Catalogue **Groupes** conservé pour consultation ; **création** depuis la fiche cours (règles formation 1 cours / mentoring multi — [gestion des groupes](gestion-groupes.md)).
Pas d’entrée sidebar **Facturation** : la préparation reste sur `school.show#billing` (raccourci `nav.billing` conservé pour usage interne).

La sidebar démarre en mode compact sauf si `vrp-sidebar-compact` vaut `false` dans `localStorage`.

## Page d’accueil

| Route | Nom | Contrôleur | Contenu |
|-------|-----|------------|---------|
| `/home` | `home` | `SchoolController@index` | Liste des écoles, stats facturation, graphique, lien plan de charge annuel |
| `/dashboard` | `dashboard` | redirect | Alias → `home` |

Constante post-login : `RouteServiceProvider::HOME = '/home'`.

### Indicateurs par école (liste)

- **Facturé TTC** — somme des factures de l’année courante
- **Non facturé TTC** — sessions sans `invoice_id` (TVA incluse à l’affichage)

## Onglets de module

| Composant | Module | Onglets |
|-----------|--------|---------|
| `scheduling-module-tabs` | Agenda | Planning · Calendrier (`/admin/calendars` — import ICS) |
| `treasury-module-tabs` | Trésorerie | Synthèse · Factures · Banque · Dépenses |
| `referential-module-tabs` | Référentiel | Programmes · Groupes |
| `settings-module-tabs` | Paramètres | Entreprise · profil |

Les actions **Créer facture** / **Créer dépense** sont des boutons dans les pages (plus des onglets). La facturation école propose **Facturer** par cours, et **Facturer tout le mois** seulement si le mois est entièrement non facturé — voir [Facturation par école](v2-facturation-par-ecole.md#périmètre--un-cours-ou-tout-le-mois).  
`workload-module-tabs` n’est plus utilisé sur le chemin quotidien (lien « plan de charge annuelle » depuis `/home`).

## Vues agenda (mois / semaine)

`PlanningController@index` mémorise la vue en session (`planning_view`). Query `?view=month` ou `?view=week` ; valeur inconnue → **mois**. Bascule : `resources/views/components/planning-view-toggle.blade.php`.

| Vue | Période | Grille | Correspondance session |
|-----|---------|--------|------------------------|
| **Mois** (défaut) | Mois calendaire (`Planning::getDetails`) | Cellules mois 7 colonnes (`planning-day`) | Date ISO complète de `begin` |
| **Semaine** | Lundi–dimanche (`Planning::getDetailsBetween`) | Grille horaire 08:00–20:00 (`planning-week-agenda`) | Date ISO complète de `begin` (pas le numéro du jour) |

Précédent / suivant (`planning.previous` / `planning.next`) passe par `Tools::shiftPlanningPeriod()` : **±1 semaine** en vue semaine (mois/année suivent le lundi), **±1 mois** en vue mois. Le début de semaine est dans `planning_week_start` ; changer le sélecteur de mois le remet au premier lundi de ce mois (ou aujourd’hui si le mois affiché est le mois courant).

Les KPI portent sur la **période visible** (semaine ou mois). Montants affichés en TTC (`gain × 1,2`). Les événements hors 08:00–20:00 sont clampés / masqués (`Tools::weekEventPosition()`). Les jours du mois adjacent ont le style `--outside`.

L’agenda liste les sessions par **date de séance**, pas par `courses.year`. Un cours 2026 avec une séance en janvier 2027 apparaît bien en naviguant janvier 2027.

### Créer une session depuis la grille

Nécessite le mode **Edit** **et** un cours sélectionné dans le fil d’Ariane (`session('course_id')`). Sans cours, les numéros de jour sont désactivés (`planning_select_course_first`).

Le formulaire caché `#planning-create-form` poste vers `planning.create.start` (`PlanningController::startCreate`). `resources/js/planning-calendar.js` recopie `data-create-hour` / `data-create-minutes` dans le formulaire avant l’envoi.

| Déclencheur | Heure par défaut |
|-------------|------------------|
| Numéro du jour (mois) ou en-tête de jour (semaine) | 08:00 |
| Créneau horaire semaine (08h–19h) | Cette heure, `:00` |

`startCreate` stocke `planning_create_date`, `planning_create_course_id`, heure et minutes, puis redirige vers `planning.create` (PRG). L’heure est validée `8–19`.

### Menus déroulants école et cours

`App\Support\SchoolCourseOptions` alimente le fil d’Ariane de l’agenda, la sélection d’école du planning, la liste de cours à l’édition d’une séance, et les sélecteurs école/cours de l’import calendrier. Un cours est utilisable quand `courses.active` est vrai (`Course::scopeActive`).

| Surface | Méthode | Exclus |
|---------|---------|--------|
| Écoles du fil d’Ariane agenda et plan de charge | `schoolsFor` | Écoles sans cours actif, tri par nom. Le fil **facture** garde `User::getSchools()` (tous les clients). |
| Cours du fil d’Ariane agenda | `coursesForSchool` | Cours archivés de l’école choisie. La liste **ne suit pas** `current_year` de l’agenda : un cours 2026 reste sélectionnable en 2027. |
| Édition de séance | `coursesForCompany` | Cours archivés, toute l’entreprise. |
| Import calendrier | `schoolsFor`, `coursesForSchool` | Mêmes filtres. Les groupes restent `$user->getGroups()`. |

Tri : `Course::applyListOrder` — année, semestre, nom d’école, nom de programme, nom de cours. Le fil d’Ariane affiche `(libellé programme) nom du cours` (`Course::programListLabel()`, description courte si elle existe). Le formulaire d’édition n’affiche que le nom du cours.

`PlanningController::selectSchool` répond **403** si l’école n’est pas dans `schoolsFor`. `selectCourse` charge un cours **actif** de l’entreprise (`findOrFail` : un id archivé est un **404**).

L’id courant reste dans la liste même s’il est archivé : `session('course_id')` dans le fil d’Ariane, `planning.course_id` à l’édition. Reposter cet id archivé vers `planning.selectCourse` reste un 404 — la requête POST n’a pas d’exception.

Les tableaux de cours (`School::getCourses`) ne filtrent pas : un cours archivé reste sur `school.show`. La fiche cours ne propose pas le flag (`CourseController::courseRules()`). La migration `2026_10_06_120000_add_active_flag_to_courses_table` ajoute `courses.active`, booléen, défaut **true**.

### Formulaire nouvelle séance

`GET planning.create` (`resources/views/planning/create.blade.php`) exige `planning_create_date` et `planning_create_course_id`. L’un des deux manque → retour à l’agenda.

Trois rangées (`planning-session-form__row--when`, `--billing-create`, `--assignments`) :

| Rangée | Champs | Persisté |
|--------|--------|----------|
| Quand | Date (lecture seule), début, fin | `plannings.begin` / `end` |
| Facturation | Durée standard (éditable), tarif horaire, montant de l’intervention | Rien. La durée n’est écrite ni sur le cours ni sur la séance. `billable_rate` n’est pas posté (défaut de colonne **1**). |
| Affectation | Groupe, nom du cours (lecture seule) | `group_id`, `course_id`. `location` vaut `na`. |

- La durée standard part de `courses.session_length` (virgule ou point). Changer le début ou la durée recalcule la fin ; changer la fin recalcule le champ durée (Alpine sur le fieldset).
- Préremplissage serveur : `Tools::planningEndFromSessionLength` — heures × 60, arrondi au multiple de 5 minutes le plus proche. Exemple : 10:00 et `2,5` → 12:30.
- `store` garde la fin soumise si elle est **strictement après** le début. Si fin ≤ début, repli sur le même helper avec la `session_length` soumise. `courses.session_length` n’est pas mis à jour.
- Heures de début **8–21**, heures de fin **8–23**, minutes par pas de 5.
- Le tarif horaire est `courses.rate` (stocké **HT**). Le montant d’intervention est `Tools::planningGain(begin, end, rate, 1)` — durée × tarif. Les cartes KPI de l’agenda restent en TTC (`gain × 1,2`) ; ce formulaire non.
- Groupes : liens actifs du cours (`getLinkedGroups(true)`). La valeur `0` crée un groupe et une ligne `group_course` depuis les champs inline.
- Les libellés suivent le profil de terminologie.
- Un enregistrement réussi efface `course` / `course_id` du fil d’Ariane et renvoie vers `planning.index`.

### Formulaire d’édition

`planning.edit` reprend les trois rangées (`--when`, `--billing`, `--assignments`).

| Rangée | Champs | Notes |
|--------|--------|-------|
| Quand | Jour, mois, année, début, fin | Années = année de la séance ± 2. Heures de début et de fin **8–21** (la création autorise une fin à 22–23). |
| Facturation | Durée indicative, tarif horaire, taux facturable, montant facturé | Durée, tarif et montant en lecture seule. Seul `billable_rate` est posté. |
| Affectation | Groupe, cours | Cours utilisables de toute l’entreprise ; le cours courant reste s’il est archivé. |

- Le navigateur recalcule durée et montant quand l’horaire, le cours ou le taux change. Montant = heures × tarif du cours choisi × `Tools::billableMultiplier(billable_rate, rate)`. Un taux ≤ 0 compte pour **1**. Une valeur égale au tarif horaire du cours compte pour **1** (anciennes lignes d’import). Toute autre valeur **> 1** est un pourcentage (`/ 100`).
- `update` écrit `begin`, `end`, `group_id`, `course_id` et `billable_rate`. Il ne reconstruit pas la fin depuis une durée standard, et il ne refuse pas fin ≤ début.
- Un `invoice_id` désactive le fieldset et bloque l’enregistrement (`session_locked_by_invoice`). Les actions de duplication sont masquées.
- Les groupes sont les liens actifs du cours chargé pour ce GET, plus le groupe courant s’il est inactif. Changer le cours ne met à jour que l’aperçu du tarif ; la liste des groupes n’est pas rechargée.
- Succès : `school.show#billing` si `school_id` est en session, sinon `planning.index`.

### Duplication de session

Depuis une cellule jour, un événement semaine ou l’édition de session (mode Edit) : copier une session vers **demain**, **la semaine suivante** ou une **date libre**, sans ressaisir cours, groupe, lieu ni taux.

| Offset | Horaires cibles |
|--------|-----------------|
| `tomorrow` | Même heure, +1 jour |
| `next_week` | Même heure, +1 semaine |
| `custom` | Date choisie, heure de début d’origine ; durée conservée |

Bloqué si la source a un `invoice_id`. Refusé si une autre session du même `group_id` chevauche. Route : `POST /planning/{id}/duplicate` (`PlanningController::duplicate`).

La date libre utilise la boîte de dialogue native `#planning-duplicate-dialog` (pas Alpine). Les boutons posent `data-duplicate-url` et `data-duplicate-date` ; `planning-calendar.js` les recopie sur le formulaire. Même dialogue sur la grille mois, les événements semaine et la page d’édition.

## Plan de charge vs liste écoles

| Écran | Route | Usage |
|-------|-------|-------|
| **Liste écoles** | `home` / `school.index` | Porte d’entrée, vue financière synthétique |
| **Plan de charge** | `school.dashboard` | KPIs annuels, tableaux de cours par école |

## Fichiers clés

- `routes/web.php` — `nav.billing`, `home`, ressources métier
- `app/Http/Controllers/BillingNavController.php` — raccourci Facturation
- `resources/views/components/sidebar-nav-group.blade.php`
- `resources/views/components/treasury-module-tabs.blade.php`
- `resources/views/components/planning-view-toggle.blade.php` — bascule mois / semaine
- `resources/views/components/planning-week-agenda.blade.php` — grille horaire + création sur créneau
- `resources/js/planning-calendar.js` — heure de création, dialogues natifs duplication/suppression
- `app/Support/SchoolCourseOptions.php` — listes d’écoles et de cours utilisables
- `app/Http/View/Composers/BreadcrumbComposer.php` — cours de l’agenda (actifs, quelle que soit l’année catalogue)
- `resources/views/planning/create.blade.php`, `edit.blade.php` — formulaires de séance en trois rangées
- `app/Http/Utility/Tools.php` — `planningEndFromSessionLength`, `planningGain`
- `resources/js/sidebar.js` — persistance de la sidebar compacte

## Voir aussi

- [V2 — vue d’ensemble](v2-interface-utilisateur.md)
- [V2 — facturation par école](v2-facturation-par-ecole.md)
- [V2 — trésorerie & rapprochement bancaire](v2-tresorerie-rapprochement-bancaire.md)
- [V2 — mode école mentoring](v2-mode-ecole-mentoring.md)
- [Import calendrier](import-calendrier.md)
