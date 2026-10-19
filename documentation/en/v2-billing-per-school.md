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
| Actions (Edit mode) | **Invoice** one course, or assign / **Invoice entire month** when the month is fully unbilled |

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

### Invoice scope: one course or the whole month

Edit mode only. Each course header is one row: course name, billing date, and the action (`bills.css` `.billing-course-header`, `flex-wrap: nowrap`).

| Situation | What the screen offers |
|-----------|------------------------|
| Course has at least one session with empty `planning.invoice_id` | Date + **Invoice** (`invoice_create_this_course`). GET `invoice.create` with `from_school_billing=1`, `school_id`, `course_id`, `month`, `year`, `cmd=detailed`. |
| Every session of that course already has an invoice | **Invoice already assigned**. This screen does not open a second invoice for that course. |
| Every session of the month is unbilled | Footer: assign an existing invoice (`school.billing.setBill`, no `course_id`) and **Invoice entire month** (same create URL, no `course_id`). |
| Month is mixed | Footer actions stay hidden. Unbilled courses still show **Invoice**. |

`invoices.course_id` (nullable FK to `courses`, `nullOnDelete`, migration `2026_10_19_100000_add_comment_and_course_id_to_invoices_table`) stores the scope. A whole-month invoice keeps it `null`. `Invoice::course()`.

`InvoiceController::resolveCourseForSchool`: a blank `course_id` means the month; a course that is not on that school is **403**. The school must be in `User::getSchools()` or create and store also return **403**. Readers are **403**; admins and editors may create.

Preview lines come from `Tools::getInvoiceDetails(..., $courseId)`. `Planning::getPlanningBySchoolAndDate` filters `plannings.course_id` only when a course is set. On save, `InvoiceService::linkPlanningToInvoice` writes `company bill prefix + invoice id` onto **every** session in that window, including sessions that already had a number. The school screen hides **Invoice entire month** once any session is billed, so a mixed month cannot be restamped in one shot. **Invoice** on a course that is only partly billed still restamps every session of that course for the month. A Treasury create that posts a `course_id` does the same. `BillingController::setBill` overwrites the same way; the assign form is shown only when the month is fully unbilled and it does not send `course_id`.

From school billing, scope is a hidden `course_id`. The hint shows the course name or **Entire month**. Description defaults to the course name. `from_school_billing` redirects to `school.show#billing`.

Outside school billing, create shows a **Scope** select (`invoice_scope`): empty = entire month, otherwise every course of the school (`$school->courses()`, name order, **including archived** — not `SchoolCourseOptions`). The select does not reload the preview. The preview follows the GET `course_id`; `store` links whatever `course_id` is posted. Re-open create with the intended query to change the lines.

Stored `amount`: a filled `amount` is saved as TTC. An empty amount becomes `round(planning HT × 1.2, 2)`. Matching planning rows still drive the PDF. A manual invoice (no sessions, amount filled) stores one flat line and still keeps `course_id` when one was posted.

### Invoice comment

`invoices.comment` is nullable text, validated `nullable|string|max:2000`. A blank value is stored as `null`.

| Surface | Behavior |
|---------|----------|
| Create and edit | Textarea. Help text: shown on the invoice screen and on the PDF. Edit does not change `course_id`. A paid invoice (`paid_at` set) cannot be edited. Update regenerates the PDF. |
| Treasury list | Under the description (`.invoice-table__comment`, `white-space: pre-wrap`). |
| PDF | Once, in the **Désignation** column, immediately under the first type `T` line (course title). `InvoiceGenerator::drawDesignationComment`: Helvetica Bold **12 pt** (`setTitleFont`), `MultiCell` width **108**. A multi-course invoice does not repeat it under later titles. With no `T` line, it is drawn once after the last line. It is not under the payment terms. |
| CII | Extra `IncludedNote`, subject code **`AAI`**, after legal notes `PMT`, `PMD`, `AAB`. Whitespace-only comments are omitted. Not a priced line. |

### Document recovery

If the PDF later disappears, viewing the invoice regenerates it with `InvoiceService::ensurePdfOnDisk()`. The service uses linked planning rows first, then `Tools::getInvoiceDetails()` for the invoice month, school, and `course_id`, and finally one manual line from the description and HT amount.

## « Jump to unbilled sessions » button

- Searches backwards month by month for the latest period with at least one session without an invoice
- **Disabled** when no earlier unbilled month exists
- Model methods: `School::findPreviousUnbilledPeriod()`, `hasPreviousUnbilledPeriod()`

## Key files

| File | Role |
|------|------|
| `app/Http/Controllers/BillingController.php` | Period navigation, setBill, jump unbilled |
| `app/Http/Controllers/InvoiceController.php` | Scope, comment, download, and paid lock |
| `app/Services/InvoiceService.php` | PDF, regeneration, course-scoped planning link |
| `app/Classes/InvoiceGenerator.php` | Comment in the Désignation column |
| `app/Http/Controllers/SchoolController.php` | Billing data on `show` |
| `app/Models/School.php` | `getBillingPlanning()`, unbilled lookup |
| `app/Models/User.php` | `getUnbilledStatsBySchool()` for the list |
| `resources/css/bills.css` | Billing section styles |

## See also

- [V2 — navigation](v2-navigation-modules.md)
- [V2 — treasury & bank reconciliation](v2-treasury-bank-reconciliation.md)
- [Electronic invoicing](electronic-invoicing.md)
- [Training data model](training-data-model.md)
