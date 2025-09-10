<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('bank_name')->nullable();
            $table->string('iban_name')->nullable();
            $table->string('bank')->nullable();
            $table->string('branch')->nullable();
            $table->string('account')->nullable();
            $table->string('key')->nullable();
            $table->string('bic')->nullable();
            $table->string('iban')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('bank_name');
            $table->dropColumn('iban_name');
            $table->dropColumn('bank');
            $table->dropColumn('branch');
            $table->dropColumn('account');
            $table->dropColumn('key');
            $table->dropColumn('bic');
            $table->dropColumn('iban');
        });
    }
};
