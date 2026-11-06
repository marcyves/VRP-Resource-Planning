# V2 - Treasury and bank reconciliation

**FR:** [v2-tresorerie-rapprochement-bancaire.md](../fr/v2-tresorerie-rapprochement-bancaire.md)

## Intent

The **Treasury** module groups global financial tracking in one place:

- issued and paid invoices;
- invoice creation from the current school context;
- bank accounts and statement imports;
- invoice, expense, and expense report reconciliation;
- expense reports and standalone expenses.

The bank flow is designed to connect real bank operations to VRP records without changing the billing preparation workflow on each school detail page.

## Main entry points

| Screen | Route | Controller / view |
|--------|-------|-------------------|
| Treasury summary | `/treasury` | `TreasuryController@index`, `resources/views/treasury/index.blade.php` |
| Invoice list | `/treasury/invoices` | `TreasuryController@invoices`, `resources/views/treasury/invoices.blade.php` |
| Bank accounts and imports | `/treasury/bank` | `BankController@index`, `resources/views/treasury/bank/index.blade.php` |
| Imported statement details | `/treasury/bank/imports/{import}` | `BankController@show`, `resources/views/treasury/bank/show.blade.php` |
| Expense report | `/treasury/reports/{expenseReport}` | `TreasuryController@showReport`, `resources/views/treasury/report.blade.php` |
| Create / edit expense | `/treasury/expenses/create`, `/treasury/expenses/{expense}/edit` | `TreasuryController`, `resources/views/treasury/expense-form.blade.php` |

Legacy `/treasury/reconciliation*` URLs redirect to the bank screens.

## Expenses and expense reports

Outflows are company-scoped (`expenses.company_id`, `expense_reports.company_id`). There is no separate “create report” screen: the first expense that opts into a report creates that month’s row. One report per company, year, and month (`unique` on those three columns).

| Kind | Storage | Screen |
|------|---------|--------|
| Report line | `expenses.expense_report_id` set | `/treasury` `#expense-reports` |
| Standalone expense | `expense_report_id` null | `#standalone-expenses` |

The summary year is session `current_year`. The value `all` still uses the current calendar year for the closing card, the report list, and standalone expenses.

### Create and edit

UI buttons (create, edit, delete, validate, pay) render only when `User::getMode()` is `Edit`. `TreasuryController` does **not** check that mode. Any signed-in tenant user who can call the route can write. A record from another company is **403**.

`GET /treasury/expenses/create` (button **Create expense**) does not pass `standalone=1`, so **Include in expense report** starts checked. Uncheck it for a standalone line; the payment-date field is shown only then. `?standalone=1` starts unchecked. `?report_id=` prefills that report and returns **403** unless status is `draft`.

| Field | Constraint |
|-------|------------|
| `expense_date`, `label` | Required. Label max 255 |
| `amount` | Required numeric ≥ 0. This is the **incl-VAT** amount used in totals, the PDF, and bank matching |
| `tax_amount` | Optional numeric **0–100**. It is a **VAT percent**, not a currency amount (label “Tax %”). Empty is stored as **20**. Displayed ex-VAT = `amount / (1 + percent/100)` |
| `payment_date` | Standalone only. Forced to `null` when the line is on a report |
| `vendor`, `category`, `payment_method` | Optional, max 255. Vendor and category datalists are distinct values already used by the company |
| `notes` | Validated and fillable, but the form has **no notes input** |
| Report link | Checkbox `include_in_expense_report` **or** a posted `expense_report_id`. The month comes from `expense_date` via `firstOrCreate` on `(company_id, year, month)`, except a non-recurring post that sends `expense_report_id` (that id is used as-is) |

**Recurring** (`is_recurring`, frequency `monthly` or `yearly`, `recurring_until` on or after `expense_date`): `store` inserts one row per period from the start date through `recurring_until` inclusive (`addMonthNoOverflow` / `addYearNoOverflow`). Each row resolves its own month’s report when the checkbox is on. `update` rewrites **that row only** and does not generate the rest of the series.

Lines that already belong to a report can be edited or deleted only while the report is `draft` (otherwise **403**). The edit form still posts a hidden `expense_report_id`, and the server treats any filled id as attachment, so unchecking **Include in expense report** does **not** detach the line.

`store` does **not** require the target report to be `draft`. `firstOrCreate` will attach a new line to a validated or paid report for that month. The `?report_id=` create path is the one that checks draft.

### Report status

`expense_reports.status` defaults to `draft`. Values in use: `draft`, `validated`, `paid`. There is no `submitted` status. The closing card label **Submitted** is the **draft** total. `submitted_at` is a date filled when the report is validated.

| Action | From | To | Effect |
|--------|------|-----|--------|
| Validate | `draft` | `validated` | Sets `submitted_at` if empty |
| Cancel validation | `validated` | `draft` | Clears `submitted_at` |
| Mark paid | `validated` | `paid` | Requires `payment_date`; sets `reimbursed_at`; fills `submitted_at` if empty |
| Cancel payment | `paid` | `validated` | Clears `reimbursed_at` |

Validate on a paid report is **403**. Pay when status is neither `validated` nor `paid` is **403**. The beneficiary printed on the report screen and the PDF is the **signed-in user’s name**, not a stored field. PDF route: `GET /treasury/reports/{expenseReport}/pdf`, filename `note-de-frais-{YYYY}-{MM}.pdf`.

### Closing card

`#treasury-balance` is not `BankBalanceService`:

- opening amount from `companies.billing_bank_account_id` when that account exists (**active is not checked**), otherwise the year’s `TreasuryBalance` (created at 0 on 1 January if missing);
- plus the sum of stored `invoices.amount` for invoices with `paid_at` set and `bill_date` in that year (current create/edit stores TTC; this card does **not** call `amountTtc()`, so a legacy HT row is summed as stored);
- minus draft, validated, and paid report totals (every status);
- minus every standalone `amount` for the year, whether or not `payment_date` is set.

`InvoiceDashboardService` and `BankBalanceService` still prefer the **active** billing account. Do not read this card as that bank balance.

## Invoice amounts and PDF files

Invoices store their primary `amount` as the TTC amount for current create and edit flows. `Invoice::amountHt()` and `Invoice::amountTtc()` normalize editing and bank matching, including older planning invoices whose stored amount may equal the HT planning total.

The invoice PDF action (`GET /invoice/{invoice}`) is resilient to missing files: `InvoiceController@show` calls `InvoiceService::ensurePdfOnDisk()` before returning `Storage::download()`. If the expected file (`invoices/{company bill prefix}{invoice id}.pdf`) is absent, the service rebuilds it from the database:

1. linked planning rows already attached to the invoice number;
2. planning details for the invoice `bill_date` month, school, and optional `invoices.course_id`;
3. a fallback manual line using the invoice description and HT amount.

The invoice list prints `invoices.comment` under the description. Scope rules, the PDF **Désignation** comment, and the CII note are in [Billing per school](v2-billing-per-school.md#invoice-scope-one-course-or-the-whole-month).

## Bank import workflow

1. Create a bank, then a bank account under that bank.
2. Optionally mark one account as the company's **billing bank account**.
3. Import a statement file from `/treasury/bank`.
4. Review parsed lines on the import detail screen.
5. Match each unreconciled line to a suggested invoice, invoice group, expense, or expense report.

### Import constraints

| Constraint | Detail |
|------------|--------|
| File type | `.xlsx` only (`statement_file`, max 10 MB) |
| Parser | `App\Services\BankStatement\CaBankStatementParser` |
| Expected columns | Date, label, debit in euros, credit in euros |
| Scope | Every bank, account, import, line, and reconciliation is scoped by `company_id` |

The parser stores a `BankStatementImport` and one `BankStatementLine` per operation. Credit amounts are positive; debit amounts are negative.

## Matching rules

`App\Services\BankStatement\BankReconciliationService` creates polymorphic `BankReconciliation` rows.

| Bank line | Match target | Rules |
|-----------|--------------|-------|
| Credit | One invoice | Invoice must belong to the same company, not already have a bank reconciliation, and match the bank credit using `Invoice::amountTtc()` |
| Credit | Invoice group | At least two invoices, same school, total TTC within EUR 0.02 of the bank line |
| Debit | Expense | Expense must belong to the same company and not already have a bank reconciliation |
| Debit | Expense report | Suggested reports belong to the same company, are not already reconciled, and have status `validated` or `paid` |

Auto-suggestions prefer amount matches within **EUR 0.02** and dates within **45 days**. For invoice credits, the compared amount is TTC, even when a legacy row needs HT/TTC normalization. If no strict suggestion exists, the screen can still show unmatched records, so operators should verify amount, date, and label before confirming a match.

A bank line is considered fully reconciled when the sum of its `matched_amount` values is within **EUR 0.02** of the absolute line amount (`BankStatementLine::isReconciled()`).

## Side effects

Matching is not just a link operation:

- matching an invoice sets `Invoice::paid_at` to the bank operation date if it was empty;
- matching a standalone expense sets `Expense::payment_date` to the bank operation date if it was empty;
- matching an expense report sets `status = paid`, `reimbursed_at` to the bank operation date, and fills `submitted_at` if needed.

Unmatching deletes the reconciliation rows for the statement line. It does **not** automatically revert `paid_at`, `payment_date`, or expense report status fields.

## Treasury balances and KPIs

`InvoiceDashboardService` builds monthly invoice KPIs for the summary screen:

- issued invoices by `bill_date`, summed as TTC with derived HT (`amount / 1.2`);
- paid invoices by `paid_at`, summed as TTC with derived HT;
- planned unbilled work from school billing planning, calculated HT and projected as TTC for charting;
- latest bank balance from `BankBalanceService`.

The closing card formula (draft reports labeled **Submitted**, billing account used even when inactive, stored invoice `amount`) is under [Expenses and expense reports](#closing-card).

`BankBalanceService` uses the active billing bank account when one is configured. Otherwise it falls back to the yearly `TreasuryBalance` opening amount and deduplicated company statement lines.

Statement-line deduplication uses account, operation date, label, debit, and credit. The row index is not part of the balance deduplication key.

## Operational checklist

- Configure bank account opening date and opening amount before relying on balance KPIs.
- Select the billing bank account when only one account should drive invoice dashboard bank balances.
- Reconcile invoice groups only when the payment covers multiple invoices from the same school.
- Treat unmatch as a link removal only; adjust payment fields manually if the previous match marked a record paid incorrectly.
- Uncheck **Include in expense report** when the charge should stay off the monthly note. The create button starts with that box checked.
- Treat `tax_amount` as a VAT percent (default 20), and `amount` as incl-VAT.

## Troubleshooting

| Symptom | Check |
|---------|-------|
| Import rejected | File is `.xlsx`, has recognizable Date / label / debit / credit columns, and contains operation rows |
| Bank import not visible | The import and selected bank account belong to the authenticated user's company |
| Line remains unreconciled | Sum of matched amounts differs from the absolute bank amount by more than EUR 0.02 |
| Invoice absent from suggestions | It may already have a bank reconciliation or belong to another company |
| Expense report absent from suggestions | Suggestions only include reports that are `validated` or `paid` and not already reconciled |
| New expense landed on a validated note | `store` uses `firstOrCreate` for that month and does not require `draft`. Edit and delete of its lines then return **403** |
| Unchecking “include in report” kept the line on the note | Edit posts a hidden `expense_report_id`; a filled id still attaches the line |
| “Submitted” total looks like a fourth status | That row is `status = draft` |

## Key files

| File | Role |
|------|------|
| `routes/web.php` | Treasury, bank import, match, and legacy redirect routes |
| `app/Http/Controllers/BankController.php` | Bank/account CRUD, statement import, filtering, match/unmatch actions |
| `app/Services/BankStatement/CaBankStatementParser.php` | XLSX statement parsing |
| `app/Services/BankStatement/BankReconciliationService.php` | Candidate lookup, matching rules, side effects |
| `app/Services/InvoiceService.php` | Invoice PDF path resolution, regeneration, and planning/manual line reconstruction |
| `app/Services/BankBalanceService.php` | Balance resolution and statement-line deduplication |
| `app/Services/InvoiceDashboardService.php` | Monthly invoice, planned work, and bank KPI data |
| `app/Http/Controllers/TreasuryController.php` | Summary, expenses, report status, report PDF |
| `app/Models/Expense.php`, `app/Models/ExpenseReport.php` | Standalone lines and monthly notes |
| `app/Models/BankReconciliation.php` | Polymorphic link to invoices, expenses, and expense reports |
| `resources/views/components/treasury-module-tabs.blade.php` | Treasury tab navigation |

## See also

- [V2 - navigation and modules](v2-navigation-modules.md)
- [V2 - billing per school](v2-billing-per-school.md)
- [Electronic invoicing](electronic-invoicing.md)
