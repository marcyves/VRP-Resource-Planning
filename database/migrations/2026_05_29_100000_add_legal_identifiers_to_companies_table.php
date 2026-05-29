<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('siren', 9)->nullable()->after('bill_prefix');
            $table->string('siret', 14)->nullable()->after('siren');
            $table->string('vat_number', 20)->nullable()->after('siret');
            $table->string('legal_form')->nullable()->after('vat_number');
            $table->string('share_capital')->nullable()->after('legal_form');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['siren', 'siret', 'vat_number', 'legal_form', 'share_capital']);
        });
    }
};
