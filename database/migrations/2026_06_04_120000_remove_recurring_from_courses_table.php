<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('courses', 'recurring')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropColumn('recurring');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('courses', 'recurring')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->boolean('recurring')->default(false)->after('rate');
            });
        }
    }
};
