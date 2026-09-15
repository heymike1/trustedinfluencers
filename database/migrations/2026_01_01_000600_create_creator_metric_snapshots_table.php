<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creator_metric_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_social_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('social_content_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamp('captured_at')->index();
            $table->json('metrics');
            $table->json('raw_provider_data')->nullable();
            $table->timestamps();

            $table->index(['creator_social_account_id', 'captured_at'], 'metric_snapshots_account_captured_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creator_metric_snapshots');
    }
};
