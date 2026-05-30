# V2 — Code review and resource list refactoring

**FR:** [v2-revue-code-refactoring-listes.md](../fr/v2-revue-code-refactoring-listes.md)

Summary document: global repository review (focus on group / program / school lists), improvement ideas, and **completed steps** from the card + grid refactor.

## Context

The **groups** and **programs** index refactor aimed to:

- Extract reusable Blade cards (`group-card`, `program-card`)
- Align presentation on the `.card-content` shell (`cards.css`)
- Reduce duplicated CSS, JS, and i18n

**Schools** already used a card grid; changes fold them into the same `.resource-grid` system.

---

## Code review — main findings

### What works well

| Item | Detail |
|------|--------|
| Blade components | `group-table` → `group-card` → `group-table-sessions` — good separation |
| Card shell | Shared `.card-content`, `.card-content-text`, `.card-content-end` |
| Index delete UX | Alpine + confirmation before delete (groups, programs, planning) |
| i18n | `resources/lang/{en,fr}/messages.php` + `*_consulting` profiles via merge |

### Duplication spotted (before refactor)

| Area | Issue |
|------|--------|
| Grid CSS | `.group-grid`, `.program-grid`, `.school-grid` + duplicate in `semantic.css` |
| Alpine stores | `groupDelete`, `programDelete`, `planningDelete` nearly identical |
| Blade modals | Confirmation markup copied on 3 index views |
| Delete i18n | 6 keys (`*_delete_confirm_title/description`) for 3 entities |
| Cards | `group-card` / `program-card` / school header — same shell |
| Program index | Inline `@foreach` vs `<x-group-table>` |
| Detail pages | `group/show` reuses `program-*` CSS classes |
| `semantic.css` | Broad rules (`main section > ul > li`) conflicting with domain hovers |

### Group vs program asymmetry

| Aspect | Groups | Programs |
|--------|--------|----------|
| List wrapper | `<x-group-table>` | Inline loop (fixed → `program-table`) |
| Inactive / search | Yes | No |
| Create form | `<x-form-group-create>` | Inline form |
| Multi-tenant scope | `User::getGroups()` | `Program::all()` (business validation needed) |
| Show destroy | Direct POST | Direct POST (index uses modal) |

### Complexity / tech debt (not addressed yet)

- `Auth::user()->getMode() == 'Edit'` repeated in ~20 views → `@editMode` Blade candidate
- `GroupController::store()` — hard-to-follow branches (`course_id == 0`, etc.)
- Two modal systems (Alpine `<x-modal>` + legacy Bootstrap in `app.css`)
- Multiple CSS entries in `vite.config.js` while runtime only loads `app.css`
- Propagated typo: `occurences` / `getGroupOccurences`
- jQuery modal on `school/show` vs Alpine elsewhere

---

## Completed steps

### 1 — Delete flow: Alpine store + generic modal

**Goal:** one JS factory and one Blade component for all delete confirmations.

| File | Role |
|------|------|
| `resources/js/delete-store.js` | `createDeleteStore(modalName, fields)` |
| `resources/js/app.js` | Instantiates `planningDelete`, `programDelete`, `groupDelete` |
| `resources/views/components/confirm-delete-modal.blade.php` | Parameterized modal (`store`, `entity`, `hints`) |

**Updated views:** `group/index`, `program/index`, `planning/index`.

**Unchanged card API:**

```js
$store.groupDelete.request(url, name)
$store.programDelete.request(url, name)
$store.planningDelete.request(url, label, date)
```

**Modal hints:** labeled field (`name`, `date`) or `plain: true` (planning label only).

---

### i18n — Unified delete modal strings

| Before | After |
|--------|--------|
| `group_delete_confirm_title` + `program_*` + `planning_*` (×2 fields) | Single `delete_confirm_title` |
| 3 entity-specific descriptions | `delete_confirm_description_{group\|program\|session}` |

`<x-confirm-delete-modal>` accepts `entity="group|program|session"` and resolves translations internally.

`en_consulting` / `fr_consulting` inherit via `array_merge` from `en` / `fr` — no extra keys required.

French descriptions stay per-entity for correct grammar (`Ce groupe…`, `Cette session…`).

---

### 2 — Shared CSS grid `.resource-grid`

**Goal:** single source for card lists (schools, groups, programs).

| File | Change |
|------|--------|
| `resources/css/cards.css` | Added `.resource-grid`, `.resource-grid--inactive`, breakpoints, hover |
| `groups.css`, `programs.css`, `schools.css` | Removed duplicated `*-grid` rules |
| `semantic.css` | Removed `school-grid` block; `:not(.resource-grid)` for flex lists |

**Views:**

- `group-table.blade.php` → `resource-grid` / `resource-grid--inactive`
- `program/index.blade.php`
- `school/index.blade.php` (active + inactive)

**Removed classes:** `group-grid`, `program-grid`, `school-grid`, `group-grid--inactive`, `school-grid--inactive`.

---

### 3 — `<x-program-table>` component

**Goal:** parity with `<x-group-table>`.

| File | Role |
|------|------|
| `resources/views/components/program-table.blade.php` | Loops `programs` + `<x-program-card>` |
| `resources/views/program/index.blade.php` | `<x-program-table :programs="$programs" />` |

Props: `programs`, `active` (default `true`) — ready for a future inactive section (`:active="false"`).

---

## Reference files (current state)

| Role | Path |
|------|------|
| Group card | `resources/views/components/group-card.blade.php` |
| Program card | `resources/views/components/program-card.blade.php` |
| Group table | `resources/views/components/group-table.blade.php` |
| Program table | `resources/views/components/program-table.blade.php` |
| Delete modal | `resources/views/components/confirm-delete-modal.blade.php` |
| JS store | `resources/js/delete-store.js`, `resources/js/app.js` |
| Grid CSS | `resources/css/cards.css` (`.resource-grid`) |
| Indexes | `resources/views/{group,program,school}/index.blade.php` |

---

## Remaining improvements (by priority)

| # | Item | Impact |
|---|------|--------|
| 4 | Scope `Program` by `company_id` (if required) | Multi-tenant |
| 5 | `<x-resource-card>` with stats / action slots | Less card duplication |
| 6 | Neutral detail layout (`entity-detail-*`) | `group/show` + `program/show` |
| 7 | `@editMode` Blade | ~20 views |
| 8 | Consistent delete UX (modal vs POST on show) | UX |
| 9 | `<x-form-program-create>` | 3 program forms |
| 10 | CSS cleanup (legacy modals, unified stats, Vite) | Maintenance |
| 11 | Fix `occurences` → `occurrences` | Naming |
| 12 | `@formatDateTime` or accessor for sessions | Dates |

---

## See also

- [V2 — CSS design system](v2-design-system-css.md) — tokens, `.resource-grid`, Vite build
- [V2 — user interface](v2-user-interface.md) — UI overview
- [Documentation index](../README.md)
