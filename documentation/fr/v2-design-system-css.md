# V2 — Design system CSS

**EN:** [v2-design-system-css.md](../en/v2-design-system-css.md)

## Architecture

Point d’entrée unique : `resources/css/app.css` (chargé par Vite).

```
global.css / semantic.css   → tokens, reset, typographie
theme.css                   → mode sombre (html[data-theme="dark"])
layout.css / shell.css      → grille app, sidebar, topbar
navigation.css              → menu latéral
buttons.css / forms.css     → boutons, champs, nice-form
tables.css                  → data-table, tableaux factures
cards.css / alerts.css      → cartes, messages
… domaines métier …         → schools, plannings, bills, treasury, etc.
tw-compat.css               → utilitaires résiduels (migration)
```

> Pas de Tailwind en dépendance npm. L’UI s’appuie sur des classes sémantiques et des composants Blade.

## Tokens principaux

Définis dans `global.css` (clair) et surchargés dans `theme.css` (sombre) :

| Token | Usage |
|-------|--------|
| `--brand-primary` | Actions, liens actifs |
| `--surface-page` / `--surface-card` | Fonds page et panneaux |
| `--text-heading` / `--text-muted` | Titres, labels secondaires |
| `--border-standard` | Contours cartes et champs |
| `--shadow-sm` … `--shadow-lg` | Élévation |
| `--rounded-lg` / `--rounded-xl` | Rayons de bordure |

## Composants UI récurrents

| Classe / composant | Fichier CSS | Usage |
|--------------------|-------------|--------|
| `.btn`, `.btn-primary`, `.btn-secondary` | `buttons.css` | Actions |
| `.nice-form`, `.nice-form--embedded` | `forms.css` | Formulaires structurés |
| `.data-table`, `.data-table--flat` | `tables.css` | Listes sessions, mapping, factures |
| `.resource-grid`, `.school-stat` | `cards.css`, `schools.css` | Grilles de listes (écoles, groupes, programmes) |
| `.planning-controls`, `.period-nav` | `plannings.css` | Navigation mensuelle |
| `.kpi-grid` | `dashboard.css` | Indicateurs plan de charge |
| `.module-tabs` | via composant Blade | Onglets de module |

## Composants Blade associés

| Composant | Rôle |
|-----------|------|
| `x-app-layout` | Layout authentifié |
| `x-module-tabs` | Onglets génériques |
| `x-kpi-grid` | Tuiles KPI |
| `x-period-selector` | Mois précédent / sélecteur / suivant |
| `x-school-billing-section` | Bloc préparation facturation |
| `x-table-invoices` | Tableau factures avec totaux |
| `x-button-primary` / `x-button-secondary` | Boutons stylés |
| `x-group-table` / `x-program-table` | Grilles `.resource-grid` + cartes |
| `x-group-card` / `x-program-card` | Carte ressource (liste) |
| `x-confirm-delete-modal` | Confirmation suppression (Alpine store) |

Stores Alpine : `createDeleteStore()` dans `resources/js/delete-store.js` (`groupDelete`, `programDelete`, `planningDelete`, `documentDelete`).

## Mode sombre

- Bascule via topbar → attribut `data-theme="dark"` sur `<html>`
- Tokens recalculés dans `theme.css`
- Préférence persistée (localStorage / script layout)

## Directives Blade utilitaires

Enregistrées dans `AppServiceProvider` :

| Directive | Rendu |
|-----------|--------|
| `@money($x)` | `number_format($x, 2)€` |
| `@moneyVAT($x)` | Montant TTC |
| `@monthName($m)` | Nom du mois localisé |

**Attention :** ne pas ajouter `€` après `@money` (le symbole est déjà inclus).

## Maintenance CSS

Lors de la v2, les classes legacy inutilisées ont été retirées (ex. `.cool-box`, `.card-wide`, `.mapping-table` isolé, grilles groupes obsolètes, modales Bootstrap dans `app.css`). Les modales passent par `<x-modal>` (Alpine) et `modals.css` — ne pas réintroduire `.modal-dialog` / `.modal.fade`.

Privilégier les patterns `.data-table` et `nice-form` pour les nouveaux écrans.

## Build front

```bash
npm run dev    # développement (Vite HMR)
npm run build  # production
```

## Voir aussi

- [V2 — revue de code & refactor listes](v2-revue-code-refactoring-listes.md)
- [V2 — vue d’ensemble](v2-interface-utilisateur.md)
- [Configuration](configuration.md)
