<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_reconciliations', function (Blueprint $table) {
            $table->dropForeign(['bank_statement_line_id']);
        });

        Schema::table('bank_reconciliations', function (Blueprint $table) {
            $table->dropUnique(['bank_statement_line_id']);
            $table->index('bank_statement_line_id');
            $table->foreign('bank_statement_line_id')
                ->references('id')
                ->on('bank_statement_lines')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bank_reconciliations', function (Blueprint $table) {
            $table->dropForeign(['bank_statement_line_id']);
        });

        Schema::table('bank_reconciliations', function (Blueprint $table) {
            $table->dropIndex(['bank_statement_line_id']);
            $table->unique('bank_statement_line_id');
            $table->foreign('bank_statement_line_id')
                ->references('id')
                ->on('bank_statement_lines')
                ->cascadeOnDelete();
        });
    }
};
