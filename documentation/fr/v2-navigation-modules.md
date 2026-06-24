# V2 — Navigation et modules

**EN:** [v2-navigation-modules.md](../en/v2-navigation-modules.md)

## Coque applicative

| Élément | Fichiers | Rôle |
|---------|----------|------|
| Sidebar | `resources/views/layouts/navigation.blade.php`, `resources/js/sidebar.js`, `resources/css/navigation.css`, `shell.css` | Menu principal, mode compact, logo, déconnexion |
| Topbar | `resources/views/layouts/topbar.blade.php` | Titre de page, fil d’Ariane, bascule Edit/Browse, thème |
| Layout | `resources/views/layouts/app.blade.php` | Grille sidebar + contenu |

### Menu latéral (ordre)

1. **Agenda** → `planning.index` (+ calendrier admin sous `calendar.*`)
2. **Trésorerie** → `treasury.index` (actif aussi pour `invoice.*`)
3. **Plan de charge** → `home` (module écoles / programmes / groupes)

Le **logo** et le 3ᵉ item mènent à **`/home`** (liste des écoles). La sidebar démarre en mode compact sauf si `vrp-sidebar-compact` vaut `false` dans `localStorage`.

## Page d’accueil

| Route | Nom | Contrôleur | Contenu |
|-------|-----|------------|---------|
| `/home` | `home` | `SchoolController@index` | Liste des écoles, stats facturation, graphique répartition |
| `/dashboard` | `dashboard` | redirect | Alias → `home` |

Constante post-login : `RouteServiceProvider::HOME = '/home'`.

### Indicateurs par école (liste)

- **Facturé TTC** — somme des factures de l’année courante
- **Non facturé** — montant HT + heures des sessions sans `invoice_id` (même formule que la préparation facturation)

## Onglets de module

Composants sous `resources/views/components/` :

| Composant | Module | Onglets |
|-----------|--------|---------|
| `workload-module-tabs` | Écoles / charge | Plan de charge · Écoles · Programmes · Groupes |
| `scheduling-module-tabs` | Agenda | Planning · Calendrier |
| `treasury-module-tabs` | Trésorerie | Synthèse · Factures · Créer facture · Banque · Notes de frais · Dépenses autonomes · Créer dépense |
| `settings-module-tabs` | Paramètres | Entreprise · profil |

Le composant générique `module-tabs` + `module-tab-icon` centralise le rendu.

## Plan de charge vs liste écoles

| Écran | Route | Usage |
|-------|-------|-------|
| **Liste écoles** | `home` / `school.index` | Porte d’entrée, vue financière synthétique |
| **Plan de charge** | `school.dashboard` | KPIs annuels, tableaux de cours par école |

## Fichiers clés

- `routes/web.php` — routes `home`, `dashboard`, ressources métier
- `app/Providers/RouteServiceProvider.php` — `HOME`
- `resources/views/components/workload-module-tabs.blade.php`
- `resources/views/components/treasury-module-tabs.blade.php`
- `resources/js/sidebar.js` — persistance de la sidebar compacte

## Voir aussi

- [V2 — vue d’ensemble](v2-interface-utilisateur.md)
- [V2 — facturation par école](v2-facturation-par-ecole.md)
- [V2 — trésorerie & rapprochement bancaire](v2-tresorerie-rapprochement-bancaire.md)
