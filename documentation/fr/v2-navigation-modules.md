# V2 — Navigation et modules

**EN:** [v2-navigation-modules.md](../en/v2-navigation-modules.md)

## Coque applicative

| Élément | Fichiers | Rôle |
|---------|----------|------|
| Sidebar | `resources/views/layouts/navigation.blade.php`, `resources/js/sidebar.js`, `resources/css/shell.css`, `navigation.css` | Menu principal, mode compact, logo, déconnexion |
| Topbar | `resources/views/layouts/topbar.blade.php` | Titre de page, fil d’Ariane, bascule Edit/Browse, thème |
| Layout | `resources/views/layouts/app.blade.php` | Grille sidebar + contenu |

### Menu latéral (ordre — utilisateur entreprise)

1. **Agenda** → `planning.index` (+ calendrier admin sous `calendar.*`) — vues **mois** / **semaine** · panneau **Facturer les interventions du mois** (écoles avec sessions, priorisation non facturé)
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
| `scheduling-module-tabs` | Agenda | Planning · Calendrier |
| `treasury-module-tabs` | Trésorerie | Synthèse · Factures · Banque · Dépenses |
| `referential-module-tabs` | Référentiel | Programmes · Groupes |
| `settings-module-tabs` | Paramètres | Entreprise · profil |

Les actions **Créer facture** / **Créer dépense** sont des boutons dans les pages (plus des onglets).  
`workload-module-tabs` n’est plus utilisé sur le chemin quotidien (lien « plan de charge annuelle » depuis `/home`).

## Vues agenda (mois / semaine)

`PlanningController@index` persiste la vue dans la session (`planning_view`). Query `?view=month` ou `?view=week` ; toute autre valeur retombe sur **mois**. Bascule : `resources/views/components/planning-view-toggle.blade.php`.

| Vue | Période | Grille | Correspondance sessions |
|-----|---------|--------|-------------------------|
| **Mois** (défaut) | Mois calendaire (`Planning::getDetails`) | Cellules mois 7 colonnes (`planning-day`) | Jour du mois |
| **Semaine** | Lundi–dimanche (`Planning::getDetailsBetween`) | Grille horaire 08:00–20:00 (`planning-week-agenda`) | Date ISO complète de `begin` (pas le numéro du jour) |

Précédent / suivant (`planning.previous` / `planning.next`) appellent `Tools::shiftPlanningPeriod()` : **±1 semaine** en vue semaine (mois/année suivent le lundi), **±1 mois** en vue mois. Le début de semaine est stocké dans `planning_week_start` ; changer le sélecteur de mois le réinitialise au premier lundi du mois (ou aujourd’hui si le mois affiché est le mois courant).

Les KPI portent sur la **période visible** (semaine ou mois). Les montants s’affichent en TTC (`gain × 1,2`). Les événements hors 08:00–20:00 sont clampés / masqués (`Tools::weekEventPosition()`). Les jours du mois adjacent ont le style `--outside`.

### Duplication de session

Depuis une cellule jour ou un événement semaine (mode Edit) : copier une session vers **demain**, **semaine prochaine** ou une **date libre**, sans ressaisir cours, groupe, lieu ni tarif.

| Décalage | Horaire |
|----------|---------|
| `tomorrow` | Même heure, +1 jour |
| `next_week` | Même heure, +1 semaine |
| `custom` | Date choisie, heure de début d’origine ; durée conservée |

Bloqué si la session source a un `invoice_id`. Rejeté si une autre session du même `group_id` chevauche. Route : `POST /planning/{id}/duplicate` (`PlanningController::duplicate`). UI : `planning-duplicate-actions`, `planning-duplicate-dialog`, `planning-duplicate-modal`, store Alpine `resources/js/duplicate-store.js`.

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
- `resources/js/sidebar.js` — persistance de la sidebar compacte

## Voir aussi

- [V2 — vue d’ensemble](v2-interface-utilisateur.md)
- [V2 — facturation par école](v2-facturation-par-ecole.md)
- [V2 — trésorerie & rapprochement bancaire](v2-tresorerie-rapprochement-bancaire.md)
- [V2 — mode école mentoring](v2-mode-ecole-mentoring.md)
