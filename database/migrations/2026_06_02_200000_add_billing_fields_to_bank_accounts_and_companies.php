<?php

use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\Company;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->string('iban_holder')->nullable()->after('label');
            $table->string('rib_bank_code', 10)->nullable()->after('iban_holder');
            $table->string('rib_branch_code', 10)->nullable()->after('rib_bank_code');
            $table->string('rib_account_number', 20)->nullable()->after('rib_branch_code');
            $table->string('rib_key', 5)->nullable()->after('rib_account_number');
            $table->string('iban', 50)->nullable()->after('rib_key');
            $table->string('bic', 20)->nullable()->after('iban');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->foreignId('billing_bank_account_id')
                ->nullable()
                ->after('contact_user_id')
                ->constrained('bank_accounts')
                ->nullOnDelete();
        });

        $this->migrateCompanyBillingData();
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['billing_bank_account_id']);
            $table->dropColumn('billing_bank_account_id');
        });

        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropColumn([
                'iban_holder',
                'rib_bank_code',
                'rib_branch_code',
                'rib_account_number',
                'rib_key',
                'iban',
                'bic',
            ]);
        });
    }

    private function migrateCompanyBillingData(): void
    {
        Company::query()->each(function (Company $company) {
            if ($company->billing_bank_account_id) {
                return;
            }

            if (! $this->companyHasLegacyBankData($company)) {
                return;
            }

            $bank = Bank::firstOrCreate(
                ['company_id' => $company->id, 'name' => $company->bank_name ?: 'Banque'],
            );

            $accountNumber = $this->resolveAccountNumber($company);

            $account = BankAccount::firstOrCreate(
                ['bank_id' => $bank->id, 'account_number' => $accountNumber],
                [
                    'company_id' => $company->id,
                    'label' => $company->iban_name ?: 'Facturation',
                ],
            );

            $account->update([
                'iban_holder' => $company->iban_name,
                'rib_bank_code' => $company->bank,
                'rib_branch_code' => $company->branch,
                'rib_account_number' => $company->account,
                'rib_key' => $company->key,
                'iban' => $company->iban,
                'bic' => $company->bic,
                'label' => $account->label ?: $company->iban_name,
            ]);

            $company->update(['billing_bank_account_id' => $account->id]);
        });
    }

    private function companyHasLegacyBankData(Company $company): bool
    {
        return collect([
            $company->bank_name,
            $company->iban_name,
            $company->bank,
            $company->branch,
            $company->account,
            $company->key,
            $company->iban,
            $company->bic,
        ])->contains(fn ($value) => filled($value));
    }

    private function resolveAccountNumber(Company $company): string
    {
        if (filled($company->account)) {
            return $company->account;
        }

        if (filled($company->iban)) {
            $normalized = preg_replace('/\s+/', '', $company->iban);

            return substr($normalized, -11) ?: 'facturation-'.$company->id;
        }

        return 'facturation-'.$company->id;
    }
};
