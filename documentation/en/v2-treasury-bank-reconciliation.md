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

Legacy `/treasury/reconciliation*` URLs redirect to the bank screens.

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
| Credit | One invoice | Invoice must belong to the same company and not already have a bank reconciliation |
| Credit | Invoice group | At least two invoices, same school, total incl. VAT within EUR 0.02 of the bank line |
| Debit | Expense | Expense must belong to the same company and not already have a bank reconciliation |
| Debit | Expense report | Suggested reports belong to the same company, are not already reconciled, and have status `validated` or `paid` |

Auto-suggestions prefer amount matches within **EUR 0.02** and dates within **45 days**. If no strict suggestion exists, the screen can still show unmatched records, so operators should verify amount, date, and label before confirming a match.

A bank line is considered fully reconciled when the sum of its `matched_amount` values is within **EUR 0.02** of the absolute line amount (`BankStatementLine::isReconciled()`).

## Side effects

Matching is not just a link operation:

- matching an invoice sets `Invoice::paid_at` to the bank operation date if it was empty;
- matching a standalone expense sets `Expense::payment_date` to the bank operation date if it was empty;
- matching an expense report sets `status = paid`, `reimbursed_at` to the bank operation date, and fills `submitted_at` if needed.

Unmatching deletes the reconciliation rows for the statement line. It does **not** automatically revert `paid_at`, `payment_date`, or expense report status fields.

## Treasury balances and KPIs

`InvoiceDashboardService` builds monthly invoice KPIs for the summary screen:

- issued invoices by `bill_date`;
- paid invoices by `paid_at`;
- planned unbilled work from school billing planning;
- latest bank balance from `BankBalanceService`.

`BankBalanceService` uses the active billing bank account when one is configured. Otherwise it falls back to the yearly `TreasuryBalance` opening amount and deduplicated company statement lines.

Statement-line deduplication uses account, operation date, label, debit, and credit. The row index is not part of the balance deduplication key.

## Operational checklist

- Configure bank account opening date and opening amount before relying on balance KPIs.
- Select the billing bank account when only one account should drive invoice dashboard bank balances.
- Reconcile invoice groups only when the payment covers multiple invoices from the same school.
- Treat unmatch as a link removal only; adjust payment fields manually if the previous match marked a record paid incorrectly.

## Troubleshooting

| Symptom | Check |
|---------|-------|
| Import rejected | File is `.xlsx`, has recognizable Date / label / debit / credit columns, and contains operation rows |
| Bank import not visible | The import and selected bank account belong to the authenticated user's company |
| Line remains unreconciled | Sum of matched amounts differs from the absolute bank amount by more than EUR 0.02 |
| Invoice absent from suggestions | It may already have a bank reconciliation or belong to another company |
| Expense report absent from suggestions | Suggestions only include reports that are `validated` or `paid` and not already reconciled |

## Key files

| File | Role |
|------|------|
| `routes/web.php` | Treasury, bank import, match, and legacy redirect routes |
| `app/Http/Controllers/BankController.php` | Bank/account CRUD, statement import, filtering, match/unmatch actions |
| `app/Services/BankStatement/CaBankStatementParser.php` | XLSX statement parsing |
| `app/Services/BankStatement/BankReconciliationService.php` | Candidate lookup, matching rules, side effects |
| `app/Services/BankBalanceService.php` | Balance resolution and statement-line deduplication |
| `app/Services/InvoiceDashboardService.php` | Monthly invoice, planned work, and bank KPI data |
| `app/Models/BankReconciliation.php` | Polymorphic link to invoices, expenses, and expense reports |
| `resources/views/components/treasury-module-tabs.blade.php` | Treasury tab navigation |

## See also

- [V2 - navigation and modules](v2-navigation-modules.md)
- [V2 - billing per school](v2-billing-per-school.md)
