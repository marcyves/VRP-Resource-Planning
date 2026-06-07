<?php

namespace App\Services;

use App\Http\Utility\Tools;
use App\Models\Invoice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class InvoiceDashboardService
{
    public function __construct(
        private BankBalanceService $bankBalanceService,
    ) {}

    /**
     * @return array{
     *     year: int,
     *     totals: array<string, float|int|null>,
     *     months: list<array<string, mixed>>,
     *     chart: list<array<string, mixed>>
     * }
     */
    public function build(User $user, int $year): array
    {
        $companyId = $user->company_id;
        $locale = app()->getLocale();

        $issuedInvoices = Invoice::query()
            ->where('company_id', $companyId)
            ->whereYear('bill_date', $year)
            ->get();

        $paidInvoices = Invoice::query()
            ->where('company_id', $companyId)
            ->whereNotNull('paid_at')
            ->whereYear('paid_at', $year)
            ->get();

        [$bankAccounts, $treasuryBalance] = $this->bankBalanceService->resolveBillingContext($companyId, $year);
        $schools = $user->getSchools();

        $months = collect(range(1, 12))->map(function (int $month) use (
            $year,
            $locale,
            $issuedInvoices,
            $paidInvoices,
            $schools,
            $bankAccounts,
            $treasuryBalance,
            $companyId,
        ) {
            $issued = $this->filterInvoicesByMonth($issuedInvoices, 'bill_date', $month);
            $paid = $this->filterInvoicesByMonth($paidInvoices, 'paid_at', $month);
            [$workedHours, $workedAmountHt, $plannedCount, $plannedAmountHt] = $this->planningMetrics($schools, $year, $month);
            $monthEnd = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();
            $bank = $this->bankBalanceService->totalAt($bankAccounts, $treasuryBalance, $companyId, $year, $monthEnd);

            return [
                'month' => $month,
                'label' => Carbon::create($year, $month, 1)->locale($locale)->translatedFormat('M'),
                'issued_count' => $issued->count(),
                'issued_amount' => (float) $issued->sum('amount'),
                'issued_amount_ht' => (float) $issued->sum('amount') / 1.2,
                'paid_count' => $paid->count(),
                'paid_amount' => (float) $paid->sum('amount'),
                'paid_amount_ht' => (float) $paid->sum('amount') / 1.2,
                'worked_hours' => $workedHours,
                'worked_amount_ht' => $workedAmountHt,
                'hourly_rate' => $workedHours > 0 ? $workedAmountHt / $workedHours : null,
                'planned_count' => $plannedCount,
                'planned_amount_ht' => $plannedAmountHt,
                'planned_amount_ttc' => $plannedAmountHt * 1.2,
                'bank_balance' => $bank,
            ];
        });

        $totals = [
            'issued_count' => (int) $months->sum('issued_count'),
            'issued_amount' => (float) $months->sum('issued_amount'),
            'issued_amount_ht' => (float) $months->sum('issued_amount_ht'),
            'paid_count' => (int) $months->sum('paid_count'),
            'paid_amount' => (float) $months->sum('paid_amount'),
            'paid_amount_ht' => (float) $months->sum('paid_amount_ht'),
            'worked_hours' => (float) $months->sum('worked_hours'),
            'worked_amount_ht' => (float) $months->sum('worked_amount_ht'),
            'hourly_rate' => $months->sum('worked_hours') > 0
                ? $months->sum('worked_amount_ht') / $months->sum('worked_hours')
                : null,
            'planned_count' => (int) $months->sum('planned_count'),
            'planned_amount_ht' => (float) $months->sum('planned_amount_ht'),
            'planned_amount_ttc' => (float) $months->sum('planned_amount_ttc'),
            'projected_amount_ht' => (float) $months->sum('issued_amount_ht') + (float) $months->sum('planned_amount_ht'),
            'projected_amount_ttc' => (float) $months->sum('issued_amount') + (float) $months->sum('planned_amount_ttc'),
            'bank_balance' => $this->latestBankBalance($months),
        ];

        return [
            'year' => $year,
            'totals' => $totals,
            'months' => $months->all(),
            'chart' => $this->chartData($months),
        ];
    }

    /**
     * @return array{0: float, 1: float, 2: int, 3: float}
     */
    private function planningMetrics(Collection $schools, int $year, int $month): array
    {
        $planning = $schools->getBillingPlanning((string) $year, (string) $month);

        if (! $planning) {
            return [0.0, 0.0, 0, 0.0];
        }

        $workedHours = 0.0;
        $workedAmountHt = 0.0;
        $plannedCount = 0;
        $plannedAmountHt = 0.0;

        foreach ($planning as $event) {
            $duration = Tools::sessionDurationHours($event->begin, $event->end);
            $gain = Tools::planningGain($event->begin, $event->end, $event->rate, $event->billable_rate);

            $workedHours += $duration;
            $workedAmountHt += $gain;

            if (blank($event->invoice_id)) {
                $plannedCount++;
                $plannedAmountHt += $gain;
            }
        }

        return [$workedHours, $workedAmountHt, $plannedCount, $plannedAmountHt];
    }

    private function filterInvoicesByMonth(Collection $invoices, string $dateField, int $month): Collection
    {
        return $invoices->filter(
            fn (Invoice $invoice) => (int) Carbon::parse($invoice->{$dateField})->month === $month
        );
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $months
     */
    private function latestBankBalance(Collection $months): ?float
    {
        return $months
            ->pluck('bank_balance')
            ->filter(fn ($value) => $value !== null)
            ->last();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $months
     * @return list<array<string, mixed>>
     */
    private function chartData(Collection $months): array
    {
        $max = max(1, $months->flatMap(fn (array $month) => array_filter([
            $month['issued_amount'],
            $month['paid_amount'],
            $month['planned_amount_ht'] * 1.2,
            $month['bank_balance'],
        ], fn ($value) => $value !== null))->max());

        return $months
            ->map(fn (array $month) => $month + [
                'planned_amount_ttc' => $month['planned_amount_ht'] * 1.2,
                'issued_height' => max(2, (int) round($month['issued_amount'] / $max * 100)),
                'paid_height' => max(2, (int) round($month['paid_amount'] / $max * 100)),
                'planned_height' => max(2, (int) round(($month['planned_amount_ht'] * 1.2) / $max * 100)),
                'bank_height' => $month['bank_balance'] === null
                    ? 0
                    : max(2, (int) round($month['bank_balance'] / $max * 100)),
            ])
            ->all();
    }
}
