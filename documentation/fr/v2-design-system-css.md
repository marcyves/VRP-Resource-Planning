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
login.css                   → overrides résiduels page maintenance
marketing.css               → canvas public landing / auth (importé en dernier)
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
| `.marketing-page`, `.marketing-hero`, `.marketing-auth-card` | `marketing.css` | Landing publique et auth |

## Composants Blade associés

| Composant | Rôle |
|-----------|------|
| `x-app-layout` | Layout authentifié |
| `x-marketing-layout` | Canvas public (landing) ; `narrow` / `title` optionnels |
| `x-guest-layout` | Même layout marketing avec `narrow=true` (login, demande de compte, mots de passe, register, maintenance) |
| `x-module-tabs` | Onglets génériques |
| `x-kpi-grid` | Tuiles KPI |
| `x-period-selector` | Mois précédent / sélecteur / suivant |
| `x-school-billing-section` | Bloc préparation facturation |
| `x-table-invoices` | Tableau factures avec totaux |
| `x-button-primary` / `x-button-secondary` | Boutons stylés |
| `x-group-table` / `x-program-table` | Grilles `.resource-grid` + cartes |
| `x-group-card` / `x-program-card` | Carte ressource (liste) |
| `x-confirm-delete-modal` | Confirmation suppression (Alpine store) |

Stores Alpine : `createDeleteStore()` dans `resources/js/delete-store.js` (`groupDelete`, `programDelete`, `documentDelete`). La suppression et la duplication à date libre des sessions d’agenda utilisent des `<dialog>` natifs dans `resources/js/planning-calendar.js`, pas Alpine.

## Canvas marketing public

Les pages invitées partagent `resources/views/layouts/marketing.blade.php`. Source visuelle : `DESIGN.md` à la racine (et `.impeccable/design.json`). Périmètre produit : `PRODUCT.md` — la priorité design est le **prospect non authentifié**.

| Surface | Route / composant | Layout |
|---------|-------------------|--------|
| Landing | `/` (`welcome`) → `welcome.blade.php` | `x-marketing-layout` (pleine largeur) |
| Login, mot de passe, register | Vues Breeze | `x-guest-layout` |
| Demande de compte | `/demande-acces` | `x-guest-layout` |
| Maintenance | `maintenance.blade.php` | `x-guest-layout` |

Un visiteur déjà connecté sur `/` est redirigé par `WelcomeController` vers `User::homePath()` (`/home`, ou `/super-admin/companies` pour un super admin).

### Contraintes (code + `LandingPageTest`)

| Règle | Détail |
|-------|--------|
| Chrome wordmark | L’en-tête affiche seulement `config('app.name')`. Ne **pas** réintroduire `marketing-brand__logo` ni `public/images/VRP.jpeg` comme marque d’en-tête |
| Pas d’eyebrow | `messages.landing_eyebrow` existe encore dans les fichiers de langue mais n’est pas utilisé ; le titre hero est seul |
| Skip link | `.marketing-skip` → `#main-content` est obligatoire |
| Thème partagé | La bascule d’en-tête marketing utilise la même clé `vrp-theme` que la coque connectée |
| Inversion canvas | En thème sombre, les primaires header/hero passent papier-sur-encre ; la bande CTA finale reste clair-sur-navy **dans tous les thèmes** |
| Régions isolées | `body.marketing-page .marketing-main > section` ne doit pas hériter du chrome panneaux/listes de l’app |
| Copie | Uniquement `messages.landing_*` — pas de témoignages, tarifs ou logos clients inventés |
| Illustration hero | `public/images/VRP-login.jpg` est une image, pas une marque (`aria-hidden`) |
| Aperçu social résiduel | `x-metas` pointe encore itemprop / twitter:image vers `http://vrp.xdm-consulting.fr/images/VRP.jpeg`. Ce n’est **pas** le chrome d’en-tête |
| Layout guest inutilisé | `resources/views/layouts/guest.blade.php` embarque encore `VRP.jpeg` ; les pages invitées en prod utilisent `layouts/marketing.blade.php` |

Couverture : `tests/Feature/LandingPageTest.php`. Mail de demande de compte : [administration-plateforme.md](administration-plateforme.md#demande-de-compte-demande-acces).

## Mode sombre

- Bascule via topbar **ou** en-tête marketing → attribut `data-theme="dark"` sur `<html>`
- Tokens recalculés dans `theme.css` ; les pages marketing surchargent aussi `--marketing-*` dans `marketing.css`
- Préférence persistée (clé `localStorage` `vrp-theme` / script layout)

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
- [Administration plateforme](administration-plateforme.md) — landing publique vs `/register`
- [Configuration](configuration.md)
