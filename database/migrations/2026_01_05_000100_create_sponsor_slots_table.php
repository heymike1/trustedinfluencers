<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sponsor_slots', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('tagline', 120);
            $table->string('url', 2048);
            $table->string('logo_url', 2048)->nullable();
            // Card tint, picked from a fixed set so the rails stay on palette.
            $table->string('tint', 20)->default('blue');
            $table->string('side', 10)->default('left'); // left | right
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            // Optional booking window; null on either side means open-ended.
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('clicks')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'side', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sponsor_slots');
    }
};
