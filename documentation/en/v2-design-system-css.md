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

## Related Blade components

| Component | Role |
|-----------|------|
| `x-app-layout` | Authenticated layout |
| `x-module-tabs` | Generic tabs |
| `x-kpi-grid` | KPI tiles |
| `x-period-selector` | Previous month / selector / next |
| `x-school-billing-section` | Billing preparation block |
| `x-table-invoices` | Invoice table with totals |
| `x-button-primary` / `x-button-secondary` | Styled buttons |

## Dark mode

- Toggle via topbar → `data-theme="dark"` on `<html>`
- Tokens recomputed in `theme.css`
- Preference persisted (localStorage / layout script)

## Utility Blade directives

Registered in `AppServiceProvider`:

| Directive | Output |
|-----------|--------|
| `@money($x)` | `number_format($x, 2)€` |
| `@moneyVAT($x)` | incl-VAT amount |
| `@monthName($m)` | Localized month name |

**Note:** do not append `€` after `@money` (symbol is already included).

## CSS maintenance

During v2, unused legacy classes were removed (e.g. `.cool-box`, `.card-wide`, orphan `.mapping-table`, old group grids). Prefer `.data-table` and `nice-form` patterns for new screens.

## Front build

```bash
npm run dev    # development (Vite HMR)
npm run build  # production
```

## See also

- [V2 — overview](v2-user-interface.md)
- [Configuration](configuration.md)
