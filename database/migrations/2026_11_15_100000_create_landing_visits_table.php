<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_visits', function (Blueprint $table) {
            $table->id();
            $table->timestamp('visited_at');
            $table->uuid('visitor_id');
            $table->boolean('is_returning')->default(false);
            $table->string('page', 32);
            $table->string('locale', 8)->nullable();
            $table->string('accept_language', 255)->nullable();
            $table->string('os', 64)->nullable();
            $table->string('device_type', 16)->nullable();
            $table->string('browser', 64)->nullable();
            $table->text('referer_url')->nullable();
            $table->string('referer_host', 255)->nullable();
            $table->string('keywords', 255)->nullable();
            $table->string('utm_source', 255)->nullable();
            $table->string('utm_medium', 255)->nullable();
            $table->string('utm_campaign', 255)->nullable();
            $table->string('utm_content', 255)->nullable();
            $table->string('utm_term', 255)->nullable();
            $table->string('landing_path', 255);
            $table->text('landing_query')->nullable();
            $table->string('country', 64)->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->string('cta', 16)->nullable();

            $table->index('visited_at');
            $table->index('visitor_id');
            $table->index('referer_host');
            $table->index('locale');
            $table->index('device_type');
            $table->index('utm_source');
            $table->index('page');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_visits');
    }
};
