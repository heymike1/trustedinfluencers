<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creators', function (Blueprint $table) {
            $table->id();
            // The owning user. Null until the real creator claims the profile.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('creator_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('bio')->nullable();
            $table->string('avatar_url', 2048)->nullable();
            $table->string('location')->nullable();
            $table->string('website', 2048)->nullable();
            $table->string('contact_email')->nullable();
            $table->boolean('contact_enabled')->default(true);
            $table->timestamp('claimed_at')->nullable();
            $table->string('status', 20)->default('active')->index(); // active | hidden | merged
            $table->foreignId('merged_into_creator_id')->nullable()->constrained('creators')->nullOnDelete();

            // Denormalised summary used only for marketplace filtering/sorting.
            // Rebuilt by RefreshCreatorSummary after every sync; never edited by hand.
            $table->unsignedBigInteger('follower_count')->default(0)->index();
            $table->unsignedBigInteger('median_views')->nullable()->index();
            $table->unsignedBigInteger('average_views')->nullable()->index();
            $table->decimal('engagement_rate', 8, 4)->nullable()->index();
            $table->boolean('has_verified_metrics')->default(false)->index();
            $table->timestamp('metrics_synced_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creators');
    }
};
