<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creator_performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained()->cascadeOnDelete();
            $table->foreignId('creator_social_account_id')->constrained()->cascadeOnDelete();
            $table->string('platform', 20);
            $table->string('content_type', 20);
            $table->string('calculation_window', 20); // last_10 | last_20 | last_30 | last_30_days | last_90_days
            $table->unsignedInteger('sample_size');
            $table->unsignedBigInteger('average_views')->nullable();
            $table->unsignedBigInteger('median_views')->nullable();
            $table->unsignedBigInteger('average_reach')->nullable();
            $table->unsignedBigInteger('median_reach')->nullable();
            $table->unsignedBigInteger('average_impressions')->nullable();
            $table->unsignedBigInteger('median_impressions')->nullable();
            $table->decimal('engagement_rate', 8, 4)->nullable();
            $table->decimal('average_watch_time', 10, 2)->nullable(); // seconds
            $table->decimal('average_view_percentage', 6, 2)->nullable();
            // Platform-specific aggregates that are not filtered on (e.g. avg profile clicks).
            $table->json('extra')->nullable();
            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->unique(['creator_social_account_id', 'content_type', 'calculation_window'], 'performance_metrics_account_type_window_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creator_performance_metrics');
    }
};
