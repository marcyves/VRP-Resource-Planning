<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('electronic_invoice_status', 20)->default('draft')->after('school_id');
            $table->string('pdp_reference')->nullable()->after('electronic_invoice_status');
            $table->timestamp('electronic_status_at')->nullable()->after('pdp_reference');
            $table->text('rejection_reason')->nullable()->after('electronic_status_at');
        });

        DB::table('invoices')->update(['electronic_invoice_status' => 'ready']);
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'electronic_invoice_status',
                'pdp_reference',
                'electronic_status_at',
                'rejection_reason',
            ]);
        });
    }
};
