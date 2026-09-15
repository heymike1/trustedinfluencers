<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Per-item time series that don't belong in the flat metrics map:
        // retention curve (YouTube) and daily views for the first 30 days.
        Schema::table('social_contents', function (Blueprint $table) {
            $table->json('insights')->nullable()->after('metrics');
        });

        // Account-level audience breakdowns, one current row per account. History goes to snapshots.
        Schema::create('creator_audience_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_social_account_id')->unique()->constrained()->cascadeOnDelete();
            $table->json('age')->nullable();        // {"18-24": 14.2, ...} percentages
            $table->json('gender')->nullable();     // {"female": 29, "male": 71}
            $table->json('countries')->nullable();  // {"GB": 38, "US": 24, ...}
            $table->json('cities')->nullable();     // {"Berlin": 12, ...}
            $table->json('devices')->nullable();    // {"mobile": 58, ...}
            $table->json('follower_type')->nullable(); // {"follower": 39, "non_follower": 61}
            $table->json('account_metrics')->nullable(); // 28/30-day account totals
            $table->timestamp('captured_at');
            $table->timestamps();
        });

        // Extra summary columns for ranking and filtering. Rebuilt by RefreshCreatorSummary.
        Schema::table('creators', function (Blueprint $table) {
            $table->decimal('average_view_percentage', 6, 2)->nullable()->after('engagement_rate');
            $table->unsignedBigInteger('median_views_7d')->nullable()->after('average_view_percentage');
            $table->decimal('posts_per_month', 6, 2)->nullable()->after('median_views_7d');
            $table->string('primary_platform', 20)->nullable()->after('posts_per_month')->index();
        });
    }

    public function down(): void
    {
        Schema::table('creators', function (Blueprint $table) {
            $table->dropColumn(['average_view_percentage', 'median_views_7d', 'posts_per_month', 'primary_platform']);
        });
        Schema::dropIfExists('creator_audience_insights');
        Schema::table('social_contents', function (Blueprint $table) {
            $table->dropColumn('insights');
        });
    }
};
