<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankStatementLine;
use App\Models\Company;
use App\Models\Expense;
use App\Models\ExpenseReport;
use App\Models\Invoice;
use App\Models\TreasuryBalance;
use App\Services\ElectronicInvoicing\ElectronicInvoiceService;
use App\Services\InvoiceDashboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use TCPDF;

class TreasuryController extends Controller
{
    public function index(InvoiceDashboardService $invoiceDashboardService)
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $current_year = session('current_year', now()->format('Y'));
        $year = $current_year === 'all' ? now()->year : (int) $current_year;
        $dashboard = $invoiceDashboardService->build($user, $year);

        $invoices = Invoice::where('company_id', $companyId)
            ->whereYear('bill_date', $year)
            ->get();

        $invoiceTotal = $invoices->sum('amount');
        $invoicePaidTotal = $invoices->whereNotNull('paid_at')->sum('amount');

        $reports = ExpenseReport::with('expenses')
            ->where('company_id', $companyId)
            ->where('year', $year)
            ->orderBy('month')
            ->get();

        $standaloneExpenses = Expense::where('company_id', $companyId)
            ->whereNull('expense_report_id')
            ->whereYear('expense_date', $year)
            ->orderByDesc('expense_date')
            ->get();

        $expenseTotal = $reports->sum(fn ($report) => $report->expenses->sum('amount'));
        $submittedExpenseReportTotal = $reports
            ->where('status', 'draft')
            ->sum(fn ($report) => $report->expenses->sum('amount'));
        $validatedExpenseReportTotal = $reports
            ->where('status', 'validated')
            ->sum(fn ($report) => $report->expenses->sum('amount'));
        $paidExpenseReportTotal = $reports
            ->where('status', 'paid')
            ->sum(fn ($report) => $report->expenses->sum('amount'));
        $standaloneTotal = $standaloneExpenses->sum('amount');
        $company = Company::with('billingBankAccount')->findOrFail($companyId);
        $billingBankAccount = $company->billingBankAccount;
        $treasuryBalance = TreasuryBalance::firstOrCreate(
            [
                'company_id' => $companyId,
                'year' => $year,
            ],
            [
                'opening_date' => Carbon::create($year, 1, 1)->toDateString(),
                'opening_amount' => 0,
            ]
        );
        $openingAmount = $billingBankAccount
            ? (float) $billingBankAccount->opening_amount
            : (float) $treasuryBalance->opening_amount;
        $closingBalance = $openingAmount
            + $invoicePaidTotal
            - $submittedExpenseReportTotal
            - $validatedExpenseReportTotal
            - $paidExpenseReportTotal
            - $standaloneTotal;

        return view('treasury.index', compact(
            'year',
            'current_year',
            'dashboard',
            'invoiceTotal',
            'invoicePaidTotal',
            'reports',
            'standaloneExpenses',
            'expenseTotal',
            'submittedExpenseReportTotal',
            'validatedExpenseReportTotal',
            'paidExpenseReportTotal',
            'standaloneTotal',
            'closingBalance',
        ));
    }

    public function invoices(Request $request, ElectronicInvoiceService $electronicInvoiceService)
    {
        $user = Auth::user();
        $current_year = session('current_year', now()->format('Y'));
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'desc') === 'asc' ? 'asc' : 'desc';

        if (! in_array($sort, ['id', 'description', 'school'], true)) {
            $sort = 'id';
        }

        $query = Invoice::query()
            ->select(['invoices.*', 'schools.name as school'])
            ->join('schools', 'schools.id', '=', 'invoices.school_id')
            ->where('invoices.company_id', $user->company_id);

        if ($current_year !== 'all') {
            $query->whereYear('invoices.bill_date', (int) $current_year);
        }

        if ($request->filled('q')) {
            $query->where('invoices.description', 'like', '%'.$request->string('q').'%');
        }

        if ($request->filled('school_id')) {
            $query->where('invoices.school_id', (int) $request->input('school_id'));
        }

        match ($request->input('payment')) {
            'paid' => $query->whereNotNull('invoices.paid_at'),
            'unpaid' => $query->whereNull('invoices.paid_at'),
            default => null,
        };

        match ($sort) {
            'description' => $query->orderBy('invoices.description', $direction),
            'school' => $query->orderBy('schools.name', $direction)->orderBy('invoices.id'),
            default => $query->orderBy('invoices.id', $direction),
        };

        $bills = $query->get();
        $schools = $user->getSchools();
        $filters = array_filter([
            'q' => $request->input('q'),
            'school_id' => $request->input('school_id'),
            'payment' => $request->input('payment'),
        ], fn ($value) => filled($value));

        $electronicInvoicingEnabled = $electronicInvoiceService->platformConfigured();

        return view('treasury.invoices', compact(
            'bills',
            'schools',
            'current_year',
            'sort',
            'direction',
            'filters',
            'electronicInvoicingEnabled',
        ));
    }

    public function createExpense()
    {
        $report = null;
        if (request('report_id')) {
            $report = ExpenseReport::findOrFail(request('report_id'));
            $this->authorizeCompany($report);
            $this->ensureReportIsDraft($report);
        }

        $expense = new Expense([
            'expense_report_id' => $report?->id,
            'expense_date' => $report
                ? Carbon::create($report->year, $report->month, 1)->toDateString()
                : now()->toDateString(),
            'is_recurring' => false,
        ]);
        $options = $this->expenseOptions();

        return view('treasury.expense-form', compact('expense', 'options'));
    }

    public function showReport(ExpenseReport $expenseReport)
    {
        $this->authorizeCompany($expenseReport);
        $expenseReport->load(['expenses' => fn ($query) => $query->orderBy('expense_date')]);

        return view('treasury.report', compact('expenseReport'));
    }

    public function validateReport(ExpenseReport $expenseReport)
    {
        $this->authorizeCompany($expenseReport);

        if ($expenseReport->status === 'validated') {
            $expenseReport->update([
                'status' => 'draft',
                'submitted_at' => null,
            ]);

            session()->flash('success', __('messages.expense_report_validation_cancelled'));

            return redirect()->route('treasury.reports.show', $expenseReport);
        }

        abort_if($expenseReport->status === 'paid', 403);

        $expenseReport->update([
            'status' => 'validated',
            'submitted_at' => $expenseReport->submitted_at ?? now()->toDateString(),
        ]);

        session()->flash('success', __('messages.expense_report_validated'));

        return redirect()->route('treasury.reports.show', $expenseReport);
    }

    public function payReport(Request $request, ExpenseReport $expenseReport)
    {
        $this->authorizeCompany($expenseReport);

        if ($expenseReport->status === 'paid') {
            $expenseReport->update([
                'status' => 'validated',
                'reimbursed_at' => null,
            ]);

            session()->flash('success', __('messages.expense_report_payment_cancelled'));

            return redirect()->route('treasury.reports.show', $expenseReport);
        }

        abort_unless($expenseReport->status === 'validated', 403);

        $validated = $request->validate([
            'payment_date' => 'required|date',
        ]);

        $expenseReport->update([
            'status' => 'paid',
            'submitted_at' => $expenseReport->submitted_at ?? now()->toDateString(),
            'reimbursed_at' => $validated['payment_date'],
        ]);

        session()->flash('success', __('messages.expense_report_paid'));

        return redirect()->route('treasury.reports.show', $expenseReport);
    }

    public function downloadReportPdf(ExpenseReport $expenseReport)
    {
        $this->authorizeCompany($expenseReport);
        $expenseReport->load(['expenses' => fn ($query) => $query->orderBy('expense_date')]);

        $pdf = $this->buildReportPdf($expenseReport);
        $filename = sprintf('note-de-frais-%04d-%02d.pdf', $expenseReport->year, $expenseReport->month);

        return response($pdf->Output($filename, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function storeExpense(Request $request)
    {
        $attributes = $this->expenseAttributes($request);
        $expense = $request->boolean('is_recurring') && filled($attributes['recurring_until'])
            ? $this->createRecurringExpenses($attributes, $request)
            : Expense::create($attributes);

        session()->flash('success', __('messages.expense_saved'));

        return $this->redirectAfterExpenseChange($expense);
    }

    public function editExpense(Expense $expense)
    {
        $this->authorizeCompany($expense);
        $this->ensureExpenseIsEditable($expense);
        $options = $this->expenseOptions();

        return view('treasury.expense-form', compact('expense', 'options'));
    }

    public function updateExpense(Request $request, Expense $expense)
    {
        $this->authorizeCompany($expense);
        $this->ensureExpenseIsEditable($expense);
        $expense->update($this->expenseAttributes($request));

        session()->flash('success', __('messages.expense_saved'));

        return $this->redirectAfterExpenseChange($expense);
    }

    public function destroyExpense(Expense $expense)
    {
        $this->authorizeCompany($expense);
        $this->ensureExpenseIsEditable($expense);
        $reportId = $expense->expense_report_id;
        $expense->delete();

        session()->flash('success', __('messages.expense_deleted'));

        return $reportId
            ? redirect()->route('treasury.reports.show', $reportId)
            : redirect()->route('treasury.index');
    }

    private function expenseAttributes(Request $request, ?Carbon $expenseDate = null, ?Carbon $paymentDate = null): array
    {
        $validated = $request->validate([
            'expense_date' => 'required|date',
            'payment_date' => 'nullable|date',
            'label' => 'required|max:255',
            'vendor' => 'nullable|max:255',
            'amount' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0|max:100',
            'category' => 'nullable|max:255',
            'payment_method' => 'nullable|max:255',
            'expense_report_id' => 'nullable|integer|exists:expense_reports,id',
            'include_in_expense_report' => 'nullable|boolean',
            'is_recurring' => 'nullable|boolean',
            'recurring_frequency' => 'nullable|required_if:is_recurring,1|in:monthly,yearly',
            'recurring_until' => 'nullable|required_if:is_recurring,1|date|after_or_equal:expense_date',
            'notes' => 'nullable',
        ]);

        $date = $expenseDate ?: Carbon::parse($validated['expense_date']);
        $paidAt = $paymentDate ?: ($validated['payment_date'] ?? null);
        $reportId = null;

        if ($request->boolean('include_in_expense_report') || $request->filled('expense_report_id')) {
            $report = $request->filled('expense_report_id') && ! $request->boolean('is_recurring')
                ? ExpenseReport::findOrFail($validated['expense_report_id'])
                : ExpenseReport::firstOrCreate([
                    'company_id' => Auth::user()->company_id,
                    'year' => $date->year,
                    'month' => $date->month,
                ]);

            $this->authorizeCompany($report);
            $reportId = $report->id;
        }

        return [
            'company_id' => Auth::user()->company_id,
            'expense_report_id' => $reportId,
            'expense_date' => $date->toDateString(),
            'payment_date' => $reportId ? null : ($paidAt ? Carbon::parse($paidAt)->toDateString() : null),
            'label' => $validated['label'],
            'vendor' => $validated['vendor'] ?? null,
            'amount' => $validated['amount'],
            'tax_amount' => $validated['tax_amount'] ?? $this->defaultTaxRate(),
            'category' => $validated['category'] ?? null,
            'payment_method' => $validated['payment_method'] ?? null,
            'is_recurring' => $request->boolean('is_recurring'),
            'recurring_frequency' => $request->boolean('is_recurring') ? ($validated['recurring_frequency'] ?? null) : null,
            'recurring_until' => $request->boolean('is_recurring') ? ($validated['recurring_until'] ?? null) : null,
            'notes' => $validated['notes'] ?? null,
        ];
    }

    private function createRecurringExpenses(array $attributes, Request $request): Expense
    {
        $date = Carbon::parse($attributes['expense_date']);
        $paymentDate = $attributes['payment_date'] ? Carbon::parse($attributes['payment_date']) : null;
        $until = Carbon::parse($attributes['recurring_until']);
        $frequency = $attributes['recurring_frequency'];
        $firstExpense = null;

        while ($date->lessThanOrEqualTo($until)) {
            $occurrenceAttributes = $this->expenseAttributes($request, $date, $paymentDate);
            $expense = Expense::create($occurrenceAttributes);
            $firstExpense ??= $expense;

            if ($frequency === 'yearly') {
                $date->addYearNoOverflow();
                $paymentDate?->addYearNoOverflow();
            } else {
                $date->addMonthNoOverflow();
                $paymentDate?->addMonthNoOverflow();
            }
        }

        return $firstExpense;
    }

    private function ensureExpenseIsEditable(Expense $expense): void
    {
        if (! $expense->expense_report_id) {
            return;
        }

        $expense->loadMissing('report');
        $this->ensureReportIsDraft($expense->report);
    }

    private function ensureReportIsDraft(ExpenseReport $report): void
    {
        abort_unless($report->status === 'draft', 403);
    }

    private function defaultTaxRate(): float
    {
        return 20;
    }

    private function authorizeCompany(Expense|ExpenseReport|TreasuryBalance $record): void
    {
        abort_unless($record->company_id === Auth::user()->company_id, 403);
    }

    private function expenseOptions(): array
    {
        $companyId = Auth::user()->company_id;

        return [
            'vendors' => Expense::where('company_id', $companyId)
                ->whereNotNull('vendor')
                ->where('vendor', '!=', '')
                ->distinct()
                ->orderBy('vendor')
                ->pluck('vendor'),
            'categories' => Expense::where('company_id', $companyId)
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->distinct()
                ->orderBy('category')
                ->pluck('category'),
        ];
    }

    private function redirectAfterExpenseChange(Expense $expense)
    {
        return $expense->expense_report_id
            ? redirect()->route('treasury.reports.show', $expense->expense_report_id)
            : redirect()->route('treasury.index');
    }

    private function monthlyTreasuryChartData(int $year, $invoices, $expenses, array $plannedAmounts, ?BankAccount $billingBankAccount, TreasuryBalance $treasuryBalance, int $companyId): array
    {
        $bankAccounts = $billingBankAccount && $billingBankAccount->active
            ? collect([$billingBankAccount])
            : collect();

        $months = collect(range(1, 12))->map(function (int $month) use ($year, $invoices, $expenses, $plannedAmounts, $bankAccounts, $treasuryBalance, $companyId) {
            $issued = $invoices
                ->filter(fn ($invoice) => Carbon::parse($invoice->bill_date)->month === $month)
                ->sum('amount');
            $paid = $invoices
                ->filter(fn ($invoice) => $invoice->paid_at && Carbon::parse($invoice->bill_date)->month === $month)
                ->sum('amount');
            $spent = $expenses
                ->filter(fn ($expense) => Carbon::parse($expense->expense_date)->month === $month)
                ->sum('amount');
            $planned = ($plannedAmounts[$month - 1] ?? 0) * 1.2;

            $monthEnd = Carbon::create($year, $month, 1)->endOfDay();
            $bank = $this->totalBankBalanceAt($bankAccounts, $treasuryBalance, $companyId, $year, $monthEnd);

            return [
                'label' => Carbon::create($year, $month, 1)->translatedFormat('M'),
                'issued' => $issued,
                'paid' => $paid,
                'spent' => $spent,
                'planned' => $planned,
                'bank' => $bank,
            ];
        });

        $max = max(1, $months->flatMap(fn ($month) => array_filter([
            $month['issued'],
            $month['paid'],
            $month['spent'],
            $month['planned'],
            $month['bank'],
        ], fn ($value) => $value !== null))->max());

        return $months
            ->map(fn (array $month) => $month + [
                'issued_height' => max(2, round($month['issued'] / $max * 100)),
                'paid_height' => max(2, round($month['paid'] / $max * 100)),
                'spent_height' => max(2, round($month['spent'] / $max * 100)),
                'planned_height' => max(2, round($month['planned'] / $max * 100)),
                'bank_height' => $month['bank'] === null ? 0 : max(2, round($month['bank'] / $max * 100)),
            ])
            ->all();
    }

    private function totalBankBalanceAt($bankAccounts, TreasuryBalance $treasuryBalance, int $companyId, int $year, Carbon $at): ?float
    {
        if ($bankAccounts->isNotEmpty()) {
            $total = 0;
            $hasBalance = false;

            foreach ($bankAccounts as $account) {
                $balance = $this->bankAccountBalanceAt($account, $year, $at);
                if ($balance !== null) {
                    $hasBalance = true;
                    $total += $balance;
                }
            }

            return $hasBalance ? $total : null;
        }

        $openingDate = Carbon::parse($treasuryBalance->opening_date)->startOfDay();
        if ($at->lt($openingDate)) {
            return null;
        }

        $lines = $this->deduplicatedBankLinesBetween($companyId, $openingDate, $at);

        return (float) $treasuryBalance->opening_amount + $lines->sum('amount');
    }

    private function bankAccountBalanceAt(BankAccount $account, int $year, Carbon $at): ?float
    {
        $openingDate = $account->opening_date
            ? Carbon::parse($account->opening_date)->startOfDay()
            : Carbon::create($year, 1, 1)->startOfDay();

        if ($at->lt($openingDate)) {
            return null;
        }

        $lines = $this->deduplicatedBankLinesBetween(
            $account->company_id,
            $openingDate,
            $at,
            $account->id,
        );

        return (float) $account->opening_amount + $lines->sum('amount');
    }

    /**
     * @return \Illuminate\Support\Collection<int, BankStatementLine>
     */
    private function deduplicatedBankLinesBetween(int $companyId, Carbon $from, Carbon $to, ?int $bankAccountId = null)
    {
        $query = BankStatementLine::where('company_id', $companyId)
            ->whereBetween('operation_date', [$from->toDateString(), $to->toDateString()]);

        if ($bankAccountId !== null) {
            $query->where('bank_account_id', $bankAccountId);
        }

        return $query
            ->orderBy('operation_date')
            ->orderBy('row_index')
            ->get()
            ->unique(fn (BankStatementLine $line) => implode('|', [
                $line->bank_account_id,
                $line->operation_date->toDateString(),
                $line->label,
                number_format((float) $line->debit, 2, '.', ''),
                number_format((float) $line->credit, 2, '.', ''),
            ]))
            ->values();
    }

    private function buildReportPdf(ExpenseReport $expenseReport): TCPDF
    {
        $company = Auth::user()->company;
        $beneficiary = Auth::user()->name;
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor(Auth::user()->name);
        $pdf->SetTitle(__('messages.expense_report_detail'));
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 12, 10);
        $pdf->SetAutoPageBreak(true, 12);
        $pdf->AddPage();

        $logo = public_path('icons/logo-XDM.png');
        if (file_exists($logo)) {
            $pdf->Image($logo, 10, 10, 22, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }

        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 8, __('messages.expense_report_detail'), 0, 1, 'R');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, sprintf('%02d/%04d', $expenseReport->month, $expenseReport->year), 0, 1, 'R');

        $y = 34;
        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetXY(10, $y);
        $pdf->Cell(82, 6, __('messages.company'), 0, 1);
        $pdf->Rect(10, $y + 7, 82, 34, 'F');
        $pdf->SetXY(12, $y + 10);
        $pdf->Cell(78, 5, $company->name, 0, 1);
        $pdf->SetFont('helvetica', '', 9);
        foreach (array_filter([$company->address, trim($company->zip.' '.$company->city), $company->country, $company->email]) as $line) {
            $pdf->SetX(12);
            $pdf->Cell(78, 4.5, $line, 0, 1);
        }

        $pdf->SetXY(105, $y);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(82, 6, __('messages.expense_reports'), 0, 1);
        $pdf->Rect(105, $y + 7, 90, 34, 'D');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetXY(107, $y + 10);
        $pdf->Cell(86, 5, __('messages.beneficiary').': '.$beneficiary, 0, 1);
        $pdf->SetX(107);
        $pdf->Cell(86, 5, __('messages.status').': '.__('messages.expense_report_status_'.$expenseReport->status), 0, 1);
        if ($expenseReport->reimbursed_at) {
            $pdf->SetX(107);
            $pdf->Cell(86, 5, __('messages.payment_date').': '.Carbon::parse($expenseReport->reimbursed_at)->format('d/m/Y'), 0, 1);
        }

        $tableY = $y + 52;
        $pdf->SetXY(10, $tableY);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(22, 7, __('messages.date'), 1, 0, 'L', true);
        $pdf->Cell(55, 7, __('messages.description'), 1, 0, 'L', true);
        $pdf->Cell(32, 7, __('messages.category'), 1, 0, 'L', true);
        $pdf->Cell(24, 7, __('messages.vendor'), 1, 0, 'L', true);
        $pdf->Cell(17, 7, __('messages.amount_ht'), 1, 0, 'R', true);
        $pdf->Cell(17, 7, __('messages.tax_amount'), 1, 0, 'R', true);
        $pdf->Cell(18, 7, __('messages.amount_ttc'), 1, 1, 'R', true);

        $pdf->SetFont('helvetica', '', 8);
        $totalHt = 0;
        $totalTtc = 0;
        foreach ($expenseReport->expenses as $expense) {
            $taxRate = $this->expenseTaxRate($expense);
            $ht = $this->expenseNetAmount($expense);
            $totalHt += $ht;
            $totalTtc += $expense->amount;
            $pdf->Cell(22, 6, Carbon::parse($expense->expense_date)->format('d/m/Y'), 1);
            $pdf->Cell(55, 6, $expense->label, 1);
            $pdf->Cell(32, 6, $expense->category, 1);
            $pdf->Cell(24, 6, $expense->vendor, 1);
            $pdf->Cell(17, 6, $this->formatPdfMoney($ht), 1, 0, 'R');
            $pdf->Cell(17, 6, number_format($taxRate, 2, ',', ' ').'%', 1, 0, 'R');
            $pdf->Cell(18, 6, $this->formatPdfMoney($expense->amount), 1, 1, 'R');
        }

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(133, 8, __('messages.total'), 1, 0, 'R', true);
        $pdf->Cell(17, 8, $this->formatPdfMoney($totalHt), 1, 0, 'R', true);
        $pdf->Cell(17, 8, '', 1, 0, 'R', true);
        $pdf->Cell(18, 8, $this->formatPdfMoney($totalTtc), 1, 1, 'R', true);

        return $pdf;
    }

    private function formatPdfMoney(float|int|string|null $amount): string
    {
        return number_format((float) $amount, 2, ',', ' ').' €';
    }

    private function expenseTaxRate(Expense $expense): float
    {
        return (float) ($expense->tax_amount ?? 20);
    }

    private function expenseNetAmount(Expense $expense): float
    {
        return (float) $expense->amount / (1 + ($this->expenseTaxRate($expense) / 100));
    }
}
