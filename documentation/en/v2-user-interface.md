# V2 — User interface

**FR:** [v2-interface-utilisateur.md](../fr/v2-interface-utilisateur.md)

## Context

**V2** modernizes the VRP Resource Planning experience: unified app shell, CSS design system, factored Blade components, and **billing preparation** moved from the Scheduling module to each school's detail page.

This is a **presentation and navigation** refactor: data model, business routes (`school`, `course`, `planning`, …), and PDF invoice logic are unchanged.

## Guiding principles

| Principle | Detail |
|-----------|--------|
| **Modular CSS** | Domain-specific sheets (`resources/css/`), tokens in `theme.css` / `global.css` — no Tailwind npm dependency |
| **Blade components** | Module headers, tables, forms (`nice-form`), KPIs, period selector |
| **Side navigation** | 260 px sidebar, topbar, breadcrumbs, dark mode |
| **Schools hub** | `/home` = school list with invoiced / unbilled indicators |
| **Contextual billing** | Sidebar **Invoice** is the month list; preparation of one client stays on `school/show?panel=billing` |
| **Public canvas** | Home, product, pricing, about, and legal share the marketing layout — [CSS design system](v2-design-system-css.md#public-marketing-canvas) |

## Detailed guides

| Topic | Guide |
|-------|--------|
| Navigation, home, modules | [v2-navigation-modules.md](v2-navigation-modules.md) |
| Calendar import (ICS) | [calendar-import.md](calendar-import.md) |
| Billing per school | [v2-billing-per-school.md](v2-billing-per-school.md) |
| Treasury and bank reconciliation | [v2-treasury-bank-reconciliation.md](v2-treasury-bank-reconciliation.md) |
| Electronic invoicing | [electronic-invoicing.md](electronic-invoicing.md) |
| CSS design system | [v2-design-system-css.md](v2-design-system-css.md) |
| Code review & list refactor | [v2-code-review-list-refactoring.md](v2-code-review-list-refactoring.md) |

## Typical user flow

Guests start on `/` and can open `/fonctionnalites`, `/tarifs`, `/a-propos`, and `/mentions-legales`, or switch language with `/langue/{fr|en|it}`. Account opening is `/demande-acces`. A signed-in visit to `/` goes to `User::homePath()`.

1. **Sign in** → `User::homePath()` (`/home` for a company user). `RouteServiceProvider::HOME` (`/planning`) is the registration and email-verification target, not the password-login target — see [navigation](v2-navigation-modules.md#home-page).
2. Review **invoiced incl. VAT** and **unbilled** per school, or open **Agenda** / **Invoice** from the sidebar.
3. **Invoice** lists clients with unbilled sessions for the agenda month, then **Prepare** opens that school’s billing panel.
4. On the school sheet, browse by month or **jump to unbilled sessions** (backwards in time).
5. **Agenda** to create / edit sessions — **month** or **week** view, click a day or week slot (end time prefilled from the course standard duration), duplicate to tomorrow / next week. ICS import is **Import a calendar** ([calendar-import.md](calendar-import.md), [session forms](v2-navigation-modules.md#new-session-form)).
6. **Finance** (invoices, expenses, bank) for the invoice list (including e-invoice submit when SuperPDP is configured), expenses, and bank reconciliation.

## Compatibility

- Legacy `/billing*` URLs: redirect to session school or school list
- `/dashboard` route: alias to `/home`
- Terminology profiles (`education` / `consulting`): unchanged — see [phase-1-terminology.md](phase-1-terminology.md)

## See also

- [Main README](../../README.md#v2--user-interface)
- [School creation workflow](school-creation-workflow.md)
