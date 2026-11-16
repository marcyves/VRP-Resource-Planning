# V2 — CSS design system

**FR:** [v2-design-system-css.md](../fr/v2-design-system-css.md)

## Architecture

Single entry point: `resources/css/app.css` (loaded by Vite).

```
global.css / semantic.css   → tokens, reset, typography
theme.css                   → dark mode (html[data-theme="dark"])
layout.css / shell.css      → app grid, sidebar, topbar
navigation.css              → side menu
buttons.css / forms.css     → buttons, fields, nice-form
tables.css                  → data-table, invoice tables
cards.css / alerts.css        → cards, alerts
… domain sheets …           → schools, plannings, bills, treasury, etc.
login.css                   → leftover maintenance-form overrides
marketing.css               → public landing / auth canvas (imported last)
tw-compat.css               → leftover utilities (migration)
```

> No Tailwind npm dependency. UI relies on semantic classes and Blade components.

## Main tokens

Defined in `global.css` (light) and overridden in `theme.css` (dark):

| Token | Usage |
|-------|--------|
| `--brand-primary` | Actions, active links |
| `--surface-page` / `--surface-card` | Page and panel backgrounds |
| `--text-heading` / `--text-muted` | Headings, secondary labels |
| `--border-standard` | Card and field borders |
| `--shadow-sm` … `--shadow-lg` | Elevation |
| `--rounded-lg` / `--rounded-xl` | Border radius |

## Recurring UI components

| Class / component | CSS file | Usage |
|-------------------|----------|--------|
| `.btn`, `.btn-primary`, `.btn-secondary` | `buttons.css` | Actions |
| `.nice-form`, `.nice-form--embedded` | `forms.css` | Structured forms |
| `.data-table`, `.data-table--flat` | `tables.css` | Sessions, mapping, invoices |
| `.resource-grid`, `.school-stat` | `cards.css`, `schools.css` | Resource list grids (schools, groups, programs) |
| `.planning-controls`, `.period-nav` | `plannings.css` | Monthly navigation |
| `.kpi-grid` | `dashboard.css` | Workload KPIs |
| `.module-tabs` | via Blade component | Module tabs |
| `.marketing-page`, `.marketing-hero`, `.marketing-auth-card` | `marketing.css` | Public landing and auth |

## Related Blade components

| Component | Role |
|-----------|------|
| `x-app-layout` | Authenticated layout |
| `x-marketing-layout` | Public canvas (landing); optional `narrow` / `title` |
| `x-guest-layout` | Same marketing layout with `narrow=true` (login, account request, passwords, register, maintenance) |
| `x-module-tabs` | Generic tabs |
| `x-kpi-grid` | KPI tiles |
| `x-period-selector` | Previous month / selector / next |
| `x-school-billing-section` | Billing preparation block |
| `x-table-invoices` | Invoice table with totals |
| `x-button-primary` / `x-button-secondary` | Styled buttons |
| `x-group-table` / `x-program-table` | `.resource-grid` wrappers + cards |
| `x-group-card` / `x-program-card` | Resource card (list) |
| `x-confirm-delete-modal` | Delete confirmation (Alpine store) |

Alpine stores: `createDeleteStore()` in `resources/js/delete-store.js` (`groupDelete`, `programDelete`, `documentDelete`). Planning session delete and custom-date duplicate use native `<dialog>` elements in `resources/js/planning-calendar.js`, not Alpine.

## Public marketing canvas

Guest surfaces share `resources/views/layouts/marketing.blade.php`. Visual source of truth: root `DESIGN.md` (and `.impeccable/design.json`). Product scope: `PRODUCT.md` — design priority is the **unauthenticated prospect**.

The public site is a small set of real routes (`WelcomeController`), not one long page with header anchors. Home still repeats short sections and links out.

| Surface | Route | View | Layout |
|---------|-------|------|--------|
| Home | `/` (`welcome`) | `welcome.blade.php` | `x-marketing-layout` |
| Product | `/fonctionnalites` (`marketing.product`) | `marketing/product.blade.php` | same, with `$title` |
| Pricing | `/tarifs` (`marketing.pricing`) | `marketing/pricing.blade.php` | same |
| About | `/a-propos` (`marketing.about`) | `marketing/about.blade.php` | same |
| Legal | `/mentions-legales` (`marketing.legal`) | `marketing/legal.blade.php` | same, no closing CTA band |
| Login, password, register | Breeze auth views | `x-guest-layout` (`narrow=true`) | |
| Account request | `/demande-acces` | `x-guest-layout` | |
| Maintenance | `maintenance.blade.php` | `x-guest-layout` | |

Only `/` redirects a signed-in visitor (`WelcomeController::index` → `User::homePath()`: `/home`, or `/super-admin/companies` for a super admin). Product, pricing, about, and legal stay reachable while signed in.

### Home section order

`LandingPageTest` locks this order. The hero is first and contains `VRP-login.jpg` only — the product captures are not inside it.

1. Hero — title, lead, trust line, account request, login, link to `/fonctionnalites`
2. Before / after compare
3. Three steps (agenda, billing, treasury)
4. `#captures` — Alpine stage, `landing-agenda.jpg` / `landing-treasury.jpg` (`marketing/partials/screens.blade.php`)
5. `#fonctionnalites` — six cards, then a link to the product page
6. Sectors (education, consulting, medical) and three personas
7. `#tarifs` — shared offer partial, then a link to `/tarifs`
8. `#a-propos` teaser, then the CTA band

`/fonctionnalites` lists eight modules (agenda, activity, billing, expenses, bank, documents, vocabulary, tenancy) and includes the same captures partial. `/tarifs` reuses `marketing/partials/pricing-offer.blade.php` and adds six `<details>` FAQ items. About covers product, editor (XDM Consulting), and licence. Legal covers publisher, hosting, privacy, IP, and contact.

The center header menu (`.marketing-menu`) points at those routes. It is omitted when the layout is `narrow`. At ≤640px the menu and `.marketing-nav__link` hide; the wordmark, locale codes, theme toggle, and compact account-request button stay.

### Guest language

`GET /langue/{locale}` (`locale.switch`, `fr` | `en` | `it`) stores `session('locale')` and redirects back (`route('welcome')` if there is no previous URL). `SetGuestLocale` sits in the `web` middleware group and applies that session value only when the request has **no** user. A signed-in company keeps `SetTerminologyLocale`. Clicking EN while signed in still writes the session key; it takes effect after sign-out. Allowed codes are `config('terminology.base_locales')`. With no session locale, guests use `APP_LOCALE`.

### Constraints (verified in code + `LandingPageTest`)

| Rule | Detail |
|------|--------|
| Wordmark chrome | Header shows `config('app.name')` only. Do **not** restore `marketing-brand__logo` or `public/images/VRP.jpeg` as a header mark |
| In-flow header | `.marketing-header` is `position: relative` with **no** `backdrop-filter`. Sticky chrome painted this bar over the `/tarifs` offer (`.marketing-pricing__offer` is also `position: relative`). Do not put sticky back |
| No eyebrow | `messages.landing_eyebrow` still exists in lang files but is unused; the hero title stands alone |
| Skip link | `.marketing-skip` → `#main-content` is required |
| Shared theme | Marketing header toggle uses the same `vrp-theme` localStorage key as the signed-in shell |
| Canvas invert | Dark-theme header/hero primaries flip to paper-on-ink; the closing CTA band stays light-on-navy in **every** theme |
| Isolated regions | `body.marketing-page .marketing-main > section` must not inherit signed-in panel/list chrome |
| Copy | `messages.landing_*` only — pricing block (0 € first month, then 10 € / month); no invented testimonials or customer logos |
| Hero illustration | `public/images/VRP-login.jpg` is an image, not a mark (`aria-hidden`) |
| Social preview leftover | `x-metas` still sets itemprop / twitter:image to `http://vrp.xdm-consulting.fr/images/VRP.jpeg`. That is **not** header chrome |
| Unused guest layout | `resources/views/layouts/guest.blade.php` still embeds `VRP.jpeg`; live guest pages use `layouts/marketing.blade.php` |

Coverage: `tests/Feature/LandingPageTest.php`. Account-request mail behaviour: [platform-administration.md](platform-administration.md#account-request-demande-acces).

## Dark mode

- Toggle via topbar **or** marketing header → `data-theme="dark"` on `<html>`
- Tokens recomputed in `theme.css`; marketing pages also override `--marketing-*` in `marketing.css`
- Preference persisted (`localStorage` key `vrp-theme` / layout script)

## Utility Blade directives

Registered in `AppServiceProvider`:

| Directive | Output |
|-----------|--------|
| `@money($x)` | `number_format($x, 2)€` |
| `@moneyVAT($x)` | incl-VAT amount |
| `@monthName($m)` | Localized month name |

**Note:** do not append `€` after `@money` (symbol is already included).

## CSS maintenance

During v2, unused legacy classes were removed (e.g. `.cool-box`, `.card-wide`, orphan `.mapping-table`, old group grids, Bootstrap modals in `app.css`). Modals use `<x-modal>` (Alpine) and `modals.css` — do not reintroduce `.modal-dialog` / `.modal.fade`.

Prefer `.data-table` and `nice-form` patterns for new screens.

## Front build

```bash
npm run dev    # development (Vite HMR)
npm run build  # production
```

## See also

- [V2 — code review & list refactor](v2-code-review-list-refactoring.md)
- [V2 — overview](v2-user-interface.md)
- [Platform administration](platform-administration.md) — public landing vs `/register`
- [Configuration](configuration.md)
