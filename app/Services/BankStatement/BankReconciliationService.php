<?php

namespace App\Services\BankStatement;

use App\Models\BankReconciliation;
use App\Models\BankStatementImport;
use App\Models\BankStatementLine;
use App\Models\Expense;
use App\Models\ExpenseReport;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BankReconciliationService
{
    public function __construct(
        private readonly CaBankStatementParser $parser = new CaBankStatementParser,
    ) {}

    public function import(UploadedFile $file, int $companyId, ?int $userId): BankStatementImport
    {
        $parsed = $this->parser->parse($file->getRealPath());

        return DB::transaction(function () use ($file, $companyId, $userId, $parsed) {
            $import = BankStatementImport::create([
                'company_id' => $companyId,
                'user_id' => $userId,
                'file_name' => $file->getClientOriginalName(),
                'account_number' => $parsed['account_number'],
                'account_label' => $parsed['account_label'],
                'period_start' => $parsed['period_start'],
                'period_end' => $parsed['period_end'],
                'statement_balance' => $parsed['statement_balance'],
                'lines_count' => count($parsed['lines']),
            ]);

            foreach ($parsed['lines'] as $line) {
                BankStatementLine::create([
                    'bank_statement_import_id' => $import->id,
                    'company_id' => $companyId,
                    'operation_date' => $line['operation_date'],
                    'label' => $line['label'],
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                    'amount' => $line['amount'],
                    'line_hash' => $this->lineHash($line),
                    'row_index' => $line['row_index'],
                ]);
            }

            return $import;
        });
    }

    /**
     * @param  array{operation_date: string, label: string, debit: float, credit: float, amount: float, row_index: int}  $line
     */
    private function lineHash(array $line): string
    {
        return hash('sha256', implode('|', [
            $line['operation_date'],
            $line['label'],
            number_format($line['debit'], 2, '.', ''),
            number_format($line['credit'], 2, '.', ''),
            (string) $line['row_index'],
        ]));
    }

    /**
     * @return array<string, Collection<int, object>>
     */
    public function matchCandidates(BankStatementLine $line, int $companyId): array
    {
        $amount = $line->absoluteAmount();
        $date = $line->operation_date;
        $tolerance = 0.02;
        $windowDays = 45;

        if ($line->isCredit()) {
            return [
                'invoices' => $this->invoiceCandidates($companyId, $amount, $date, $tolerance, $windowDays),
                'expenses' => collect(),
                'expense_reports' => collect(),
            ];
        }

        return [
            'invoices' => collect(),
            'expenses' => $this->expenseCandidates($companyId, $amount, $date, $tolerance, $windowDays),
            'expense_reports' => $this->expenseReportCandidates($companyId, $amount, $date, $tolerance, $windowDays),
        ];
    }

    public function match(BankStatementLine $line, string $type, string|int $id, int $companyId): BankReconciliation
    {
        if ($line->isReconciled()) {
            throw new RuntimeException(__('messages.bank_line_already_reconciled'));
        }

        $reconcilable = $this->resolveReconcilable($type, $id, $companyId, $line);

        return DB::transaction(function () use ($line, $reconcilable, $companyId) {
            $reconciliation = BankReconciliation::create([
                'bank_statement_line_id' => $line->id,
                'company_id' => $companyId,
                'reconcilable_type' => $reconcilable::class,
                'reconcilable_id' => $reconcilable->getKey(),
                'matched_amount' => $line->absoluteAmount(),
            ]);

            $this->applyReconciliationSideEffects($line, $reconcilable);

            return $reconciliation;
        });
    }

    public function unmatch(BankReconciliation $reconciliation): void
    {
        $reconciliation->delete();
    }

    private function resolveReconcilable(string $type, string|int $id, int $companyId, BankStatementLine $line): Invoice|Expense|ExpenseReport
    {
        return match ($type) {
            'invoice' => $this->findInvoiceCandidate($id, $companyId, $line),
            'expense' => $this->findExpenseCandidate($id, $companyId, $line),
            'expense_report' => $this->findExpenseReportCandidate($id, $companyId, $line),
            default => throw new RuntimeException(__('messages.bank_match_invalid_type')),
        };
    }

    private function findInvoiceCandidate(string|int $id, int $companyId, BankStatementLine $line): Invoice
    {
        abort_unless($line->isCredit(), 422);

        $invoice = Invoice::where('company_id', $companyId)
            ->whereDoesntHave('bankReconciliation')
            ->whereKey($id)
            ->firstOrFail();

        return $invoice;
    }

    private function findExpenseCandidate(string|int $id, int $companyId, BankStatementLine $line): Expense
    {
        abort_unless($line->isDebit(), 422);

        return Expense::where('company_id', $companyId)
            ->whereDoesntHave('bankReconciliation')
            ->whereKey($id)
            ->firstOrFail();
    }

    private function findExpenseReportCandidate(string|int $id, int $companyId, BankStatementLine $line): ExpenseReport
    {
        abort_unless($line->isDebit(), 422);

        return ExpenseReport::where('company_id', $companyId)
            ->whereDoesntHave('bankReconciliation')
            ->withSum('expenses', 'amount')
            ->whereKey($id)
            ->firstOrFail();
    }

    private function applyReconciliationSideEffects(BankStatementLine $line, Invoice|Expense|ExpenseReport $reconcilable): void
    {
        $date = $line->operation_date;

        if ($reconcilable instanceof Invoice) {
            if (! $reconcilable->paid_at) {
                $reconcilable->paid_at = Carbon::parse($date)->startOfDay();
                $reconcilable->save();
            }

            return;
        }

        if ($reconcilable instanceof Expense) {
            if (! $reconcilable->payment_date) {
                $reconcilable->payment_date = $date;
                $reconcilable->save();
            }

            return;
        }

        if ($reconcilable->status !== 'paid') {
            $reconcilable->update([
                'status' => 'paid',
                'reimbursed_at' => $date,
                'submitted_at' => $reconcilable->submitted_at ?? $date,
            ]);
        }
    }

    private function invoiceCandidates(int $companyId, float $amount, $date, float $tolerance, int $windowDays): Collection
    {
        $from = Carbon::parse($date)->subDays($windowDays);
        $to = Carbon::parse($date)->addDays($windowDays);

        $base = Invoice::where('company_id', $companyId)
            ->whereDoesntHave('bankReconciliation');

        $strict = (clone $base)
            ->whereBetween('bill_date', [$from, $to])
            ->get()
            ->filter(fn (Invoice $invoice) => abs((float) $invoice->amount - $amount) <= $tolerance);

        $pool = $strict->isNotEmpty() ? $strict : $base->get();

        return $pool
            ->sortBy(fn (Invoice $invoice) => abs((float) $invoice->amount - $amount))
            ->values();
    }

    private function expenseCandidates(int $companyId, float $amount, $date, float $tolerance, int $windowDays): Collection
    {
        $from = Carbon::parse($date)->subDays($windowDays);
        $to = Carbon::parse($date)->addDays($windowDays);

        $base = Expense::where('company_id', $companyId)
            ->whereDoesntHave('bankReconciliation');

        $strict = (clone $base)
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('expense_date', [$from, $to])
                    ->orWhereBetween('payment_date', [$from, $to]);
            })
            ->get()
            ->filter(fn (Expense $expense) => abs((float) $expense->amount - $amount) <= $tolerance);

        $pool = $strict->isNotEmpty() ? $strict : $base->get();

        return $pool
            ->sortBy(fn (Expense $expense) => abs((float) $expense->amount - $amount))
            ->values();
    }

    private function expenseReportCandidates(int $companyId, float $amount, $date, float $tolerance, int $windowDays): Collection
    {
        $from = Carbon::parse($date)->subDays($windowDays);
        $to = Carbon::parse($date)->addDays($windowDays);

        $base = ExpenseReport::where('company_id', $companyId)
            ->whereDoesntHave('bankReconciliation')
            ->whereIn('status', ['validated', 'paid'])
            ->withSum('expenses', 'amount');

        $reports = $base->get();

        $strict = $reports->filter(function (ExpenseReport $report) use ($amount, $tolerance, $from, $to) {
            $reportTotal = (float) ($report->expenses_sum_amount ?? 0);
            if (abs($reportTotal - $amount) > $tolerance) {
                return false;
            }
            $anchor = Carbon::create($report->year, $report->month, 1);

            return $anchor->between($from, $to);
        });

        $pool = $strict->isNotEmpty() ? $strict : $reports;

        return $pool
            ->sortBy(fn (ExpenseReport $report) => abs((float) ($report->expenses_sum_amount ?? 0) - $amount))
            ->values();
    }
}
