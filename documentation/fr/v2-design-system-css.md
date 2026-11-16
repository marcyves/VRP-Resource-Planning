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

Le site public est un petit jeu de routes réelles (`WelcomeController`), plus une seule page à ancres d’en-tête. L’accueil garde des sections courtes qui renvoient vers ces routes.

| Surface | Route | Vue | Layout |
|---------|-------|-----|--------|
| Accueil | `/` (`welcome`) | `welcome.blade.php` | `x-marketing-layout` |
| Produit | `/fonctionnalites` (`marketing.product`) | `marketing/product.blade.php` | idem, avec `$title` |
| Tarifs | `/tarifs` (`marketing.pricing`) | `marketing/pricing.blade.php` | idem |
| À propos | `/a-propos` (`marketing.about`) | `marketing/about.blade.php` | idem |
| Mentions | `/mentions-legales` (`marketing.legal`) | `marketing/legal.blade.php` | idem, sans bande CTA |
| Login, mot de passe, register | Vues Breeze | `x-guest-layout` (`narrow=true`) | |
| Demande de compte | `/demande-acces` | `x-guest-layout` | |
| Maintenance | `maintenance.blade.php` | `x-guest-layout` | |

Seul `/` redirige un visiteur déjà connecté (`WelcomeController::index` → `User::homePath()` : `/home`, ou `/super-admin/companies` pour un super admin). Produit, tarifs, à propos et mentions restent accessibles une fois connecté.

### Ordre des sections d’accueil

`LandingPageTest` fige cet ordre. Le hero est en premier et ne contient que `VRP-login.jpg` — les captures produit n’y sont pas.

1. Hero — titre, chapô, ligne de confiance, demande de compte, connexion, lien vers `/fonctionnalites`
2. Comparatif avant / après
3. Trois étapes (agenda, facturation, trésorerie)
4. `#captures` — scène Alpine, `landing-agenda.jpg` / `landing-treasury.jpg` (`marketing/partials/screens.blade.php`)
5. `#fonctionnalites` — six cartes, puis un lien vers la page produit
6. Secteurs (formation, conseil, médical) et trois personas
7. `#tarifs` — partial d’offre partagé, puis un lien vers `/tarifs`
8. Teaser `#a-propos`, puis la bande CTA

`/fonctionnalites` liste huit modules (agenda, activité, facturation, dépenses, banque, documents, vocabulaire, tenants) et réinclut les captures. `/tarifs` réutilise `marketing/partials/pricing-offer.blade.php` et ajoute six FAQ en `<details>`. À propos couvre le produit, l’éditeur (XDM Consulting) et la licence. Les mentions couvrent éditeur, hébergement, vie privée, propriété intellectuelle et contact.

Le menu central (`.marketing-menu`) pointe vers ces routes. Il est omis quand le layout est `narrow`. Sous 640 px, le menu et `.marketing-nav__link` disparaissent ; le wordmark, les codes de langue, le thème et le bouton compact de demande de compte restent.

### Langue des invités

`GET /langue/{locale}` (`locale.switch`, `fr` | `en` | `it`) enregistre `session('locale')` et revient en arrière (`route('welcome')` s’il n’y a pas d’URL précédente). `SetGuestLocale` est dans le groupe `web` et n’applique cette session que si la requête n’a **pas** d’utilisateur. Une entreprise connectée garde `SetTerminologyLocale`. Cliquer EN une fois connecté écrit quand même la clé de session ; elle prend effet après déconnexion. Les codes autorisés sont `config('terminology.base_locales')`. Sans locale de session, les invités utilisent `APP_LOCALE`.

### Contraintes (code + `LandingPageTest`)

| Règle | Détail |
|-------|--------|
| Chrome wordmark | L’en-tête affiche seulement `config('app.name')`. Ne **pas** réintroduire `marketing-brand__logo` ni `public/images/VRP.jpeg` comme marque d’en-tête |
| En-tête dans le flux | `.marketing-header` est en `position: relative`, **sans** `backdrop-filter`. Un chrome sticky peignait cette barre par-dessus l’offre `/tarifs` (`.marketing-pricing__offer` est aussi `position: relative`). Ne pas remettre le sticky |
| Pas d’eyebrow | `messages.landing_eyebrow` existe encore dans les fichiers de langue mais n’est pas utilisé ; le titre hero est seul |
| Skip link | `.marketing-skip` → `#main-content` est obligatoire |
| Thème partagé | La bascule d’en-tête marketing utilise la même clé `vrp-theme` que la coque connectée |
| Inversion canvas | En thème sombre, les primaires header/hero passent papier-sur-encre ; la bande CTA finale reste clair-sur-navy **dans tous les thèmes** |
| Régions isolées | `body.marketing-page .marketing-main > section` ne doit pas hériter du chrome panneaux/listes de l’app |
| Copie | Uniquement `messages.landing_*` — bloc tarif (0 € le premier mois, puis 10 € / mois) ; pas de témoignages ni logos clients inventés |
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
