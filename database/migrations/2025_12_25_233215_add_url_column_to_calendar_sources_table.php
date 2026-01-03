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
        Schema::table('calendar_sources', function (Blueprint $table) {
            $table->string('filename')->nullable()->change();
            $table->string('storage_path')->nullable()->after('filename');
            $table->string('url')->nullable()->after('filename');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calendar_sources', function (Blueprint $table) {
            $table->string('filename')->nullable()->change();            
            $table->dropColumn('url');
        });
    }
};
