<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_social_account_id')->constrained()->cascadeOnDelete();
            $table->string('provider_content_id');
            $table->string('content_type', 20)->index(); // video | short | reel | post
            $table->string('title', 500)->nullable();
            $table->string('url', 2048)->nullable();
            $table->string('thumbnail_url', 2048)->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            // Latest normalised metrics for the item. History lives in creator_metric_snapshots.
            $table->json('metrics')->nullable();
            $table->timestamp('metrics_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['creator_social_account_id', 'provider_content_id'], 'social_contents_account_provider_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_contents');
    }
};
