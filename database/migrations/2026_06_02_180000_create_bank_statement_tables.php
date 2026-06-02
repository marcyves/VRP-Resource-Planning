<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_statement_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('file_name');
            $table->string('account_number')->nullable();
            $table->string('account_label')->nullable();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->decimal('statement_balance', 12, 2)->nullable();
            $table->unsignedInteger('lines_count')->default(0);
            $table->timestamps();
        });

        Schema::create('bank_statement_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_statement_import_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->date('operation_date');
            $table->text('label');
            $table->decimal('debit', 12, 2)->default(0);
            $table->decimal('credit', 12, 2)->default(0);
            $table->decimal('amount', 12, 2);
            $table->string('line_hash', 64);
            $table->unsignedSmallInteger('row_index')->default(0);
            $table->timestamps();

            $table->unique(['bank_statement_import_id', 'line_hash']);
            $table->index(['company_id', 'operation_date']);
        });

        Schema::create('bank_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_statement_line_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('reconcilable_type');
            $table->string('reconcilable_id');
            $table->index(['reconcilable_type', 'reconcilable_id']);
            $table->decimal('matched_amount', 12, 2);
            $table->timestamps();

            $table->unique(['bank_statement_line_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_reconciliations');
        Schema::dropIfExists('bank_statement_lines');
        Schema::dropIfExists('bank_statement_imports');
    }
};
