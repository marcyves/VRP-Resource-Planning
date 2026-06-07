<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\BankStatementLine;
use App\Models\Company;
use App\Models\TreasuryBalance;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BankBalanceService
{
    public function resolveBillingContext(int $companyId, int $year): array
    {
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

        $bankAccounts = $billingBankAccount && $billingBankAccount->active
            ? collect([$billingBankAccount])
            : collect();

        return [$bankAccounts, $treasuryBalance, $billingBankAccount];
    }

    public function totalAt(Collection $bankAccounts, TreasuryBalance $treasuryBalance, int $companyId, int $year, Carbon $at): ?float
    {
        if ($bankAccounts->isNotEmpty()) {
            $total = 0;
            $hasBalance = false;

            foreach ($bankAccounts as $account) {
                $balance = $this->accountBalanceAt($account, $year, $at);
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

        $lines = $this->deduplicatedLinesBetween($companyId, $openingDate, $at);

        return (float) $treasuryBalance->opening_amount + $lines->sum('amount');
    }

    public function accountBalanceAt(BankAccount $account, int $year, Carbon $at): ?float
    {
        $openingDate = $account->opening_date
            ? Carbon::parse($account->opening_date)->startOfDay()
            : Carbon::create($year, 1, 1)->startOfDay();

        if ($at->lt($openingDate)) {
            return null;
        }

        $lines = $this->deduplicatedLinesBetween(
            $account->company_id,
            $openingDate,
            $at,
            $account->id,
        );

        return (float) $account->opening_amount + $lines->sum('amount');
    }

    /**
     * @return Collection<int, BankStatementLine>
     */
    public function deduplicatedLinesBetween(int $companyId, Carbon $from, Carbon $to, ?int $bankAccountId = null): Collection
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
}
