# Design: Sidebar navigation — parcours métier (approche A)

**Date:** 2026-08-05  
**Status:** draft for user review  
**Product:** VRP Resource Planning  
**Related:** `documentation/fr/v2-navigation-modules.md`

## Problem

The v2 shell exposes only three sidebar items (Agenda, Treasury, Workload) while most destinations live in dense module tabs (especially Treasury with ~7 tabs). Daily work is **planning** and **billing**; treasury is secondary; programmes/groups are rare. Users cannot find billing (now on school `#billing`) or schools from the primary menu.

## Goals

- Put Agenda and school/billing entry points at the first level of the sidebar.
- Keep Treasury reachable without a tab strip full of create-actions.
- Park programmes/groups under a secondary **Referential** entry.
- Preserve terminology profiles (`education` / `consulting` / `medical`) via existing i18n keys.
- No return to global multi-school billing UI (v1).

## Non-goals

- Redesign of school billing business logic or invoice PDF.
- Super-admin navigation (unchanged: companies list).
- Native mobile app / PWA.
- Removing `school.dashboard` (annual workload KPIs) — only how it is reached.

## Usage priority (confirmed)

1. Agenda / planning — primary  
2. Invoice from school hours — primary  
3. Treasury — occasional  
4. Referential (programmes, groups) — rare  

## Design

### 1. Sidebar order (company user)

| Order | Item | Target | Notes |
|------:|------|--------|-------|
| 1 | Agenda | `planning.index` | Unchanged primary |
| 2 | Schools* | `home` | List + financial indicators; gateway to school show |
| 3 | Billing | See rule below | Shortcut to school `#billing` |
| 4 | Treasury | `treasury.index` | Summary entry |
| — | separator | | |
| 5 | Referential | Submenu: Programmes · Groups | Collapsed by default |
| 6 | Settings (optional) | Company / profile | Only if not already clear in topbar/footer |

\* Label from terminology: Schools / Clients / Structures.

**Compact mode:** icons for items 1–4; Referential icon opens flyout or accordion.  
**Mobile:** same order; Referential as accordion.

### 2. Billing item rule

1. If `session('school_id')` is set → navigate to `school/{id}#billing` (or named billing show).
2. Else → navigate to `home` and show a non-blocking hint to open a school to prepare invoicing.
3. Do **not** restore a global all-schools billing preparation screen.

### 3. Active states

| Routes | Active sidebar item |
|--------|---------------------|
| `planning.*`, `calendar.*` | Agenda |
| `home`, `school.*`, `course.*` (default) | Schools |
| School billing period routes / `school.show` focused on `#billing` | **Billing** (wins over Schools) |
| `treasury.*`, `invoice.*` | Treasury |
| `program.*`, `group.*` | Referential |

Exact detection for Billing vs Schools on `school.show` may use hash, query flag, or dedicated billing routes already present (`school.billing.*`).

### 4. Referential submenu

| Sub-item | Route |
|----------|-------|
| Programmes / Prestations / Projects | `program.index` |
| Groups / Patients / Teams | `group.index` |

Optional local mini-tabs Programmes | Groups only on those pages (not on `/home`).

### 5. Workload module tabs

Remove `workload-module-tabs` from the default school/home/course chrome.

| Screen | Navigation after change |
|--------|-------------------------|
| `/home` | Sidebar **Schools**; discreet link to annual workload `school.dashboard` |
| `/school/dashboard` | Breadcrumb + link back to school list |
| School / course pages | Existing breadcrumbs |
| Programmes / Groups | Sidebar Referential (+ optional mini-tabs) |

### 6. Treasury tabs (slim)

| Keep | Remove from tabs |
|------|------------------|
| Summary | Create invoice (become in-page button) |
| Invoices | Create expense (in-page button) |
| Bank | Fake tabs that are only `#anchors` |
| Expenses (unified notes + standalone) | Separate tabs for expense reports vs standalone |

Invoice creation paths remain: school `#billing` Create, and Invoices list primary button (requires school in session; same messaging as today).

### 7. Agenda tabs

Unchanged: Planning · Calendar (two tabs are acceptable).

## i18n

Add/use keys such as:

- `nav_billing` / existing billing labels  
- `nav_referential`  
- Reuse `schools`, `programs`, `groups`, `workload_plan` for submenu and home link  

Medical/consulting overrides follow existing `*_medical` / `*_consulting` patterns where school/program/group words appear in nav.

## Files likely touched (implementation later)

- `resources/views/layouts/navigation.blade.php`
- `resources/views/components/sidebar-nav-link.blade.php` (or new submenu component)
- `resources/views/components/treasury-module-tabs.blade.php`
- `resources/views/components/workload-module-tabs.blade.php` (usage reduced/removed)
- `resources/css/navigation.css` / `shell.css`
- `resources/lang/fr/messages.php`, `en/messages.php`, optional medical/consulting overrides
- Possibly a thin controller/redirect helper for the Billing sidebar link
- Docs: `documentation/fr/v2-navigation-modules.md` (+ EN)

## Testing

- Feature/browser: each sidebar target resolves; Billing with/without `school_id`.
- Active class correct for planning, home, school billing, treasury, program, group.
- Treasury create invoice/expense still reachable via buttons.
- Terminology profile does not break nav labels.
- Super-admin sidebar unchanged.

## Open points (resolved defaults)

| Point | Default in this spec |
|-------|----------------------|
| Annual workload access | Link from `/home`, not a fifth primary sidebar item |
| Settings in sidebar | Optional; prefer existing topbar/company tag |
| Billing active on `school.show` | Prefer `school.billing.*` + `#billing` / explicit flag over guessing |

## Approval

- Approach A: approved  
- Section 1 (sidebar + Billing rule): approved  
- Section 2 (Referential + workload tabs): approved  
- Section 3 (Treasury + active states): approved  

**Next:** user review of this file → then implementation plan (`writing-plans`).
