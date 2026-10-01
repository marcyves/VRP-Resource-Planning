<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')
                ->nullable()
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->string('username');
            $table->string('ip', 45);
            $table->string('geo_label')->nullable();
            $table->boolean('success');
            $table->boolean('locked_out')->default(false);
            $table->timestamp('occurred_at');

            $table->index('occurred_at');
            $table->index('ip');
            $table->index('company_id');
            $table->index('success');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_events');
    }
};
