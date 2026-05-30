# V2 — Billing per school

**FR:** [v2-facturation-par-ecole.md](../fr/v2-facturation-par-ecole.md)

## Major change

**Billing preparation** is no longer under the **Scheduling** module (`/billing`). It lives on the **school detail** page (`school/show`), anchored at `#billing`.

### Before (v1)

- Scheduling tabs: Planning · Calendar · **Billing preparation** · By date
- Global view across all schools

### After (v2)

- Scheduling: Planning · Calendar only
- Preparation: **one school at a time**, with its own monthly navigation

## School detail screen

Component: `resources/views/components/school-billing-section.blade.php`

| Block | Description |
|-------|-------------|
| Period selector | Previous / next month, month dropdown |
| **By date** toggle | Chronological vs by-course grouping (`school_billing_by_date` session) |
| Session tables | Group, schedule, hours, invoice # |
| Totals | Hours and ex-VAT / incl-VAT amounts per course and school |
| Actions (Edit mode) | Assign existing invoice, create invoice |

## Billing session keys

| Key | Role |
|-----|------|
| `billing_year` | **Calendar** year for the billing period (separate from academic `current_year`) |
| `current_month` | Displayed month |
| `school_billing_by_date` | Whether by-date view is active |

Initialization: `billing_year` defaults to current year if missing (`Tools::getBillingYear`).

## Routes

| Route | Action |
|-------|--------|
| `school/{school}/billing/previous` | Previous month |
| `school/{school}/billing/next` | Next month |
| `school/{school}/billing/by-date` | Toggle by-date view |
| `school/{school}/billing/jump-unbilled` | Jump to latest earlier month with unbilled sessions |
| `school/{school}/billing/set-bill` | Attach invoice to month's sessions |

Legacy: `/billing*` redirects to `school.show#billing` when a school is in session.

## Amount calculation

Centralized in `App\Http\Utility\Tools`:

- `sessionDurationHours()` — actual session duration
- `billableMultiplier()` — normalizes billable rate (calendar import fix)
- `planningGain()` — ex-VAT session amount

Used by billing preparation **and** school list « unbilled » stats.

## « Jump to unbilled sessions » button

- Searches backwards month by month for the latest period with at least one session without an invoice
- **Disabled** when no earlier unbilled month exists
- Model methods: `School::findPreviousUnbilledPeriod()`, `hasPreviousUnbilledPeriod()`

## Key files

| File | Role |
|------|------|
| `app/Http/Controllers/BillingController.php` | Period navigation, setBill, jump unbilled |
| `app/Http/Controllers/SchoolController.php` | Billing data on `show` |
| `app/Models/School.php` | `getBillingPlanning()`, unbilled lookup |
| `app/Models/User.php` | `getUnbilledStatsBySchool()` for the list |
| `resources/css/bills.css` | Billing section styles |

## See also

- [V2 — navigation](v2-navigation-modules.md)
- [Training data model](training-data-model.md)
