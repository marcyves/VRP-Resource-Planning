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
| Session tables | Group, schedule, hours, **HT**, **TTC**, invoice # |
| Totals | Hours and ex-VAT / incl-VAT amounts per course and school |
| Actions (Edit mode) | Assign existing invoice, create invoice |

### Session line amounts

Each intervention row shows HT and TTC next to hours (`Tools::getBillingInformation()`):

| Column | Source |
|--------|--------|
| Hours | `sessionDurationHours()` — badge warns when duration ≠ course `session_length` |
| HT | `planningGain()` stored as `gain` |
| TTC | `gain_ttc` = `round(gain * Tools::VAT_MULTIPLIER, 2)` (VAT multiplier **1.2**) |
| Invoice # | `planning.invoice_id` |

Course and school **totals** still print `gain` HT and `gain * 1.2` TTC inline — they do not sum the per-row rounded `gain_ttc` values.

`@money` already appends `€` (`AppServiceProvider`). Line cells use `@money($schedule['gain'])` / `@money($schedule['gain_ttc'])` with **no extra euro sign**. Totals use `number_format(...) € HT / ... € TTC`.

Table layout (`resources/css/bills.css`): `table-layout: fixed`; group 30% with ellipsis; schedule 25ch; hours 10ch; money 15ch; bill 10ch.

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
- `planningGain()` — ex-VAT session amount (`gain`)
- `gain_ttc` — `round(gain * VAT_MULTIPLIER, 2)` for the TTC column

Used by billing preparation **and** school list « unbilled » stats.

The footer **monthly gain** / **hour rate** strip under the school totals uses the same HT `monthlyGain` without a VAT label — do not confuse it with the TTC KPI cards on the agenda.

## Link with Treasury

Invoices created from the school billing section are listed under **Treasury**. When a credit bank statement line is matched to an invoice, bank reconciliation sets `Invoice::paid_at` to the bank operation date if it was still empty.

### Invoice creation and document recovery

The school billing **Create** action opens `InvoiceController@create` with `cmd=detailed`, the selected school, month, year, and billing date. The preview lines come from `Tools::getInvoiceDetails()`. On submit, `InvoiceController@store`:

1. creates an `Invoice` for the current company and school;
2. stores the amount as TTC (`amount`), deriving it from planning HT totals when needed;
3. writes the PDF through `InvoiceService::saveToDisk()`;
4. links the month's planning rows to the full invoice number (`company bill prefix + invoice id`).

If the PDF file later disappears from storage, viewing the invoice regenerates it with `InvoiceService::ensurePdfOnDisk()`. The service first uses the linked planning rows, then falls back to the invoice month/school planning details, and finally emits a single manual line from the invoice description and HT amount.

## « Jump to unbilled sessions » button

- Searches backwards month by month for the latest period with at least one session without an invoice
- **Disabled** when no earlier unbilled month exists
- Model methods: `School::findPreviousUnbilledPeriod()`, `hasPreviousUnbilledPeriod()`

## Key files

| File | Role |
|------|------|
| `app/Http/Controllers/BillingController.php` | Period navigation, setBill, jump unbilled |
| `app/Http/Controllers/InvoiceController.php` | Invoice creation, download, and paid lock behavior |
| `app/Services/InvoiceService.php` | PDF creation, missing-file regeneration, planning line reconstruction |
| `app/Http/Controllers/SchoolController.php` | Billing data on `show` |
| `app/Models/School.php` | `getBillingPlanning()`, unbilled lookup |
| `app/Models/User.php` | `getUnbilledStatsBySchool()` for the list |
| `resources/css/bills.css` | Billing section styles |

## See also

- [V2 — navigation](v2-navigation-modules.md)
- [V2 — treasury & bank reconciliation](v2-treasury-bank-reconciliation.md)
- [Electronic invoicing](electronic-invoicing.md)
- [Training data model](training-data-model.md)
