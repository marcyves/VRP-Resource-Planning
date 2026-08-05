# V2 — Navigation et modules

**EN:** [v2-navigation-modules.md](../en/v2-navigation-modules.md)

## Coque applicative

| Élément | Fichiers | Rôle |
|---------|----------|------|
| Sidebar | `resources/views/layouts/navigation.blade.php`, `resources/js/sidebar.js`, `resources/css/shell.css`, `navigation.css` | Menu principal, mode compact, logo, déconnexion |
| Topbar | `resources/views/layouts/topbar.blade.php` | Titre de page, fil d’Ariane, bascule Edit/Browse, thème |
| Layout | `resources/views/layouts/app.blade.php` | Grille sidebar + contenu |

### Menu latéral (ordre — utilisateur entreprise)

1. **Agenda** → `planning.index` (+ calendrier admin sous `calendar.*`) — panneau **Facturer les interventions du mois** (écoles avec sessions, priorisation non facturé)
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
- `resources/js/sidebar.js` — persistance de la sidebar compacte

## Voir aussi

- [V2 — vue d’ensemble](v2-interface-utilisateur.md)
- [V2 — facturation par école](v2-facturation-par-ecole.md)
- [V2 — trésorerie & rapprochement bancaire](v2-tresorerie-rapprochement-bancaire.md)
- [V2 — mode école mentoring](v2-mode-ecole-mentoring.md)
