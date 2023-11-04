<?php

use App\Models\User;
use App\Models\School;
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
        Schema::create('school_user', function (Blueprint $table) {
            $table->foreignIdFor(School::class)->constrained()
            ->onUpdate('cascade')   
            ->onDelete('restrict');
            $table->foreignIdFor(User::class)->constrained()
            ->onUpdate('cascade')   
            ->onDelete('restrict');

            $table->timestamps();

            $table->primary(['school_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_user');
    }
};
