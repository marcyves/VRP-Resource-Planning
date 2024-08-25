<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Group;
use App\Models\Course;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('group_course', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Group::class)->constrained()
            ->onUpdate('cascade')   
            ->onDelete('restrict');
            $table->foreignIdFor(Course::class)->constrained()
            ->onUpdate('cascade')   
            ->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_course');
    }
};
