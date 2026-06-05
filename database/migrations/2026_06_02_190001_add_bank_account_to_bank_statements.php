<?php

use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\BankStatementImport;
use App\Models\BankStatementLine;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_statement_imports', function (Blueprint $table) {
            $table->foreignId('bank_account_id')->nullable()->after('company_id')->constrained()->nullOnDelete();
        });

        Schema::table('bank_statement_lines', function (Blueprint $table) {
            $table->foreignId('bank_account_id')->nullable()->after('company_id')->constrained()->nullOnDelete();
            $table->index(['bank_account_id', 'operation_date']);
        });

        $this->migrateExistingImports();
    }

    public function down(): void
    {
        Schema::table('bank_statement_lines', function (Blueprint $table) {
            $table->dropForeign(['bank_account_id']);
            $table->dropColumn('bank_account_id');
        });

        Schema::table('bank_statement_imports', function (Blueprint $table) {
            $table->dropForeign(['bank_account_id']);
            $table->dropColumn('bank_account_id');
        });
    }

    private function migrateExistingImports(): void
    {
        BankStatementImport::query()
            ->whereNull('bank_account_id')
            ->orderBy('id')
            ->each(function (BankStatementImport $import) {
                $bankName = $this->inferBankName($import->account_label);
                $bank = Bank::firstOrCreate(
                    ['company_id' => $import->company_id, 'name' => $bankName],
                );

                $accountNumber = $import->account_number ?: 'compte-'.$import->id;

                $account = BankAccount::firstOrCreate(
                    ['bank_id' => $bank->id, 'account_number' => $accountNumber],
                    [
                        'company_id' => $import->company_id,
                        'label' => $import->account_label,
                        'opening_date' => $import->period_start,
                        'opening_amount' => 0,
                    ],
                );

                $import->update(['bank_account_id' => $account->id]);

                BankStatementLine::where('bank_statement_import_id', $import->id)
                    ->update(['bank_account_id' => $account->id]);
            });
    }

    private function inferBankName(?string $accountLabel): string
    {
        if ($accountLabel && preg_match('/crédit agricole|credit agricole/i', $accountLabel)) {
            return 'Crédit Agricole';
        }

        return 'Banque';
    }
};
