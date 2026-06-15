# V2 — Treasury and bank reconciliation

**FR:** [v2-tresorerie-rapprochement-bancaire.md](../fr/v2-tresorerie-rapprochement-bancaire.md)

## Intent

Treasury is the operational hub for invoicing, cash visibility and expenses. In v2, invoice listing and billing KPIs moved into the **Treasury** module, while bank management adds account setup, statement import and manual reconciliation against invoices or expenses.

## User workflow

| Step | Screen | Purpose |
|------|--------|---------|
| 1 | `/treasury` | Review the yearly invoice dashboard, closing balance and expense sections |
| 2 | `/treasury/invoices` | Search, filter and sort invoices by description, school/client and payment status |
| 3 | `/treasury/bank` | Create banks, add accounts, define opening balances and choose the billing bank account |
| 4 | `/treasury/bank` | Import a bank statement for one account (`.xlsx`) |
| 5 | `/treasury/bank/imports/{import}` | Review lines, filter reconciled/unreconciled operations and match lines |

Mutations in the bank screens are shown only in **Edit** mode. Authenticated users can still view the module and imported lines.

## Module architecture

| Area | Main codepaths | Notes |
|------|----------------|-------|
| Treasury summary | `TreasuryController::index`, `resources/views/treasury/index.blade.php` | Uses `InvoiceDashboardService` for invoice KPIs and chart data |
| Invoice list | `TreasuryController::invoices`, `resources/views/treasury/invoices.blade.php`, `components/table-invoices.blade.php` | `/invoice` resource routes still create/edit invoices; the list tab is `/treasury/invoices` |
| Bank/account management | `BankController::index`, `storeBank`, `storeAccount`, `updateBillingAccount` | Banks and account numbers are unique per company/bank |
| Statement import | `BankReconciliationService::import`, `CaBankStatementParser`, `XlsxSheetReader` | Import writes `bank_statement_imports` and `bank_statement_lines` in a transaction |
| Reconciliation | `BankReconciliationService::match*`, `BankStatementLine::isReconciled()` | Reconciles by summing `matched_amount` with a `0.02` tolerance |
| Balance KPIs | `BankBalanceService`, `InvoiceDashboardService` | Uses the active billing account when configured; otherwise falls back to treasury opening balance plus deduplicated imported lines |

## Routes

| Route | Action |
|-------|--------|
| `GET /treasury` | Treasury dashboard and expense sections |
| `GET /treasury/invoices` | Invoice list with filters |
| `GET /treasury/bank` | Bank cards, accounts, imports |
| `POST /treasury/bank/banks` | Create a bank |
| `POST /treasury/bank/accounts` | Create a bank account |
| `PUT /treasury/bank/billing-account` | Choose the company's billing bank account |
| `POST /treasury/bank/import` | Import a statement file |
| `GET /treasury/bank/imports/{import}` | Review imported lines |
| `POST /treasury/bank/imports/{import}/lines/{line}/match` | Reconcile a line |
| `DELETE /treasury/bank/imports/{import}/matches/{reconciliation}` | Remove reconciliation for a line |

Legacy `/treasury/reconciliation` routes redirect to the bank screens.

## Statement import format

The current parser is specific to the bank export handled by `CaBankStatementParser`:

- File type: `.xlsx`, maximum upload size `10240` KB.
- The first worksheet is read directly from the XLSX ZIP/XML structure; no spreadsheet engine is used.
- A header row is required with `Date`, `Libellé`/`Libelle` and `Débit euros`/`Debit euros`.
- Columns are interpreted as:
  - `A`: operation date (`dd/mm/yyyy` or Excel serial date)
  - `B`: label
  - `C`: debit amount
  - `D`: credit amount
- Metadata is detected before the header when present: account number, account label, period start/end and statement balance.
- Empty rows, rows without a label, rows without an amount, and rows with an unreadable date are ignored.

If no header or no operations are found, the import fails with a user-facing error message.

## Matching rules

| Bank line | Candidate types | Side effect when matched |
|-----------|-----------------|--------------------------|
| Credit (`amount > 0`) | Unreconciled invoices, or groups of invoices for the same school/client | Sets `Invoice::paid_at` to the bank operation date when empty |
| Debit (`amount < 0`) | Standalone expenses and validated/paid expense reports | Sets `Expense::payment_date`, or marks `ExpenseReport` as paid |

Candidate suggestions first prefer records within a 45-day window and within `0.02` of the bank amount. If strict matches are unavailable, the UI falls back to broader unreconciled candidates sorted by amount proximity.

Grouped invoice reconciliation is allowed only when:

- at least two invoices are selected;
- all invoices belong to the same school/client;
- the sum of their `amountTtc()` values matches the credit line within `0.02`;
- none of the invoices is already reconciled.

## Balance calculations

There are two related balance concepts:

| Display | Source |
|---------|--------|
| Invoice dashboard bank balance | `BankBalanceService::totalAt()` at each month end |
| Treasury closing balance | Opening amount + paid invoices - submitted/validated/paid expense reports - standalone expenses |

For dashboard bank balances, duplicate imported lines are deduplicated by account, date, label, debit and credit before summing. If a company has an active billing bank account, only that account is used; without one, all company statement lines are considered from the treasury opening date.

## Operational notes and pitfalls

- Select the billing bank account before relying on dashboard bank-balance KPIs or invoice PDF bank details.
- Import one statement against the matching account. The parser can update the account number/label from file metadata.
- Deleting a bank account deletes its imports, lines and reconciliations, and clears companies that used it as billing account.
- Deleting an import cascades to its lines and reconciliations.
- Unmatching deletes the reconciliation records for the line; it does not currently revert `paid_at`, `payment_date` or expense report status side effects.
- Invoice amounts may be stored as HT for some legacy records; reconciliation uses `Invoice::amountTtc()` to compare against bank credits.

## Key files

| File | Role |
|------|------|
| `app/Http/Controllers/TreasuryController.php` | Treasury dashboard, invoice list, expenses and expense reports |
| `app/Http/Controllers/BankController.php` | Bank/account CRUD, import screens, match/unmatch actions |
| `app/Services/InvoiceDashboardService.php` | Monthly invoice, planning and bank KPIs |
| `app/Services/BankBalanceService.php` | Billing-account context and deduplicated bank balances |
| `app/Services/BankStatement/BankReconciliationService.php` | Import, candidate search, reconciliation side effects |
| `app/Services/BankStatement/CaBankStatementParser.php` | Current XLSX statement parser |
| `app/Models/Bank*.php`, `BankStatement*.php`, `BankReconciliation.php` | Treasury bank domain models |

## See also

- [V2 — navigation](v2-navigation-modules.md)
- [V2 — billing per school](v2-billing-per-school.md)
