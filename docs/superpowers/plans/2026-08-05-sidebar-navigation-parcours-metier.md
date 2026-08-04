# Sidebar parcours métier — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Restructure the company-user sidebar and slim module tabs per `docs/superpowers/specs/2026-08-05-sidebar-navigation-parcours-metier-design.md`.

**Architecture:** Primary destinations move into the sidebar (Agenda, Schools, Billing, Treasury, Referential submenu). Treasury create-actions become in-page buttons. Workload module tabs are removed from daily pages; annual workload remains reachable from `/home`.

**Tech Stack:** Laravel 11 Blade, Alpine/vanilla JS as already used, existing `sidebar-nav-link` / `module-tabs` / CSS in `navigation.css`.

## Global Constraints

- No global multi-school billing UI (v1).
- Terminology via `__('messages.*')` + medical/consulting overrides where school/program/group words appear.
- Super-admin sidebar unchanged.
- Billing: `session('school_id')` → school `#billing`; else `/home` + flash/hint.

---

## File map

| File | Responsibility |
|------|----------------|
| `routes/web.php` | Optional named route `nav.billing` redirect |
| `app/Http/Controllers/...` or closure | Billing nav redirect |
| `resources/views/layouts/navigation.blade.php` | New sidebar order + referential |
| `resources/views/components/sidebar-nav-group.blade.php` | Referential expand/accordion |
| `resources/css/navigation.css` | Submenu + separator styles |
| `resources/views/components/treasury-module-tabs.blade.php` | 4 tabs only |
| `resources/views/treasury/invoices.blade.php` (etc.) | Create buttons if missing |
| `resources/views/school/index.blade.php` | Drop workload tabs; link to dashboard |
| Other views using `workload-module-tabs` | Remove or replace |
| `resources/lang/fr/messages.php`, `en/messages.php` | `nav_referential`, billing hint, etc. |
| `documentation/fr/v2-navigation-modules.md` (+ EN) | Reflect new IA |

---

### Task 1: Billing nav redirect

- [x] Add route `GET /nav/billing` → BillingNavController
- [x] Flash hint on home when no school
- [ ] Feature test (needs DB/mysql or Sail) — written as `BillingNavTest`

### Task 2: Sidebar IA

- [x] Reorder nav + referential group
- [x] Active states + focus=billing
- [x] i18n FR/EN + medical overrides

### Task 3: Slim treasury tabs + create buttons

- [x] Four tabs; create buttons on invoices + treasury expenses

### Task 4: Remove workload-module-tabs from daily chrome

- [x] Removed from school/course/dashboard; referential tabs on program/group
- [x] Annual workload link on `/home`

### Task 5: Docs + smoke

- [x] FR/EN navigation docs updated
- [ ] Manual smoke in browser / Sail tests
- [ ] Commit when user requests

---

**Spec:** `docs/superpowers/specs/2026-08-05-sidebar-navigation-parcours-metier-design.md`
