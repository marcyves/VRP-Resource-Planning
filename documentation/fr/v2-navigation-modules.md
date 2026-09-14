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

Les actions **Créer facture** / **Créer dépense** sont des boutons dans les pages (plus des onglets).  
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

Le sélecteur de cours du fil d’Ariane liste **tous les cours de l’école** (`School::getCourses('all')` dans `BreadcrumbComposer`) — il ne filtre pas sur l’année calendaire de l’agenda, donc un cours 2026 reste sélectionnable en naviguant 2027.

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
- `app/Http/View/Composers/BreadcrumbComposer.php` — liste complète des cours sur l’agenda
- `resources/js/sidebar.js` — persistance de la sidebar compacte

## Voir aussi

- [V2 — vue d’ensemble](v2-interface-utilisateur.md)
- [V2 — facturation par école](v2-facturation-par-ecole.md)
- [V2 — trésorerie & rapprochement bancaire](v2-tresorerie-rapprochement-bancaire.md)
- [V2 — mode école mentoring](v2-mode-ecole-mentoring.md)
- [Import calendrier](import-calendrier.md)
