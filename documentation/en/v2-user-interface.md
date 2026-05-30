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
| **Schools hub** | Home page = school list with invoiced / unbilled indicators |
| **Contextual billing** | Preparation on `school/show`, no longer under scheduling |

## Detailed guides

| Topic | Guide |
|-------|--------|
| Navigation, home, modules | [v2-navigation-modules.md](v2-navigation-modules.md) |
| Billing per school | [v2-billing-per-school.md](v2-billing-per-school.md) |
| CSS design system | [v2-design-system-css.md](v2-design-system-css.md) |
| Code review & list refactor | [v2-code-review-list-refactoring.md](v2-code-review-list-refactoring.md) |

## Typical user flow

1. **Sign in** → `/home` (school list)
2. Review **invoiced incl. VAT** and **unbilled** per school
3. Open a **school** → courses, address, invoices, documents, **billing preparation**
4. Browse by month or **jump to unbilled sessions** (backwards in time)
5. **Scheduling** (sidebar) to create / edit sessions
6. **Invoices** and **Treasury** for global financial tracking

## Compatibility

- Legacy `/billing*` URLs: redirect to session school or school list
- `/dashboard` route: alias to `/home`
- Terminology profiles (`education` / `consulting`): unchanged — see [phase-1-terminology.md](phase-1-terminology.md)

## See also

- [Main README](../../README.md#v2--user-interface)
- [School creation workflow](school-creation-workflow.md)
