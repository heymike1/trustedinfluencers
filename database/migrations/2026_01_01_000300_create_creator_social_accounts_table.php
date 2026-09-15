<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creator_social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained()->cascadeOnDelete();
            $table->string('platform', 20);
            // Normalised handle without "@" and lower-cased where the platform is case-insensitive.
            $table->string('handle');
            $table->string('profile_url', 2048);
            // Canonical identity from the provider (YouTube channel ID, IG user ID, X user ID).
            $table->string('provider_account_id')->nullable();
            $table->string('display_name')->nullable();
            $table->string('avatar_url', 2048)->nullable();

            // Public data: may come from public APIs or the person who submitted the profile.
            $table->unsignedBigInteger('follower_count')->nullable();
            $table->json('public_data')->nullable();
            $table->timestamp('public_synced_at')->nullable();

            // Connection state (only meaningful once the creator has authenticated).
            $table->string('connection_status', 30)->default('unconnected')->index();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->json('scopes')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('sync_requested_at')->nullable();
            $table->text('last_sync_error')->nullable();
            $table->timestamp('disconnected_at')->nullable();

            $table->timestamps();

            $table->unique(['platform', 'handle']);
            $table->unique(['platform', 'provider_account_id']);
            $table->unique(['creator_id', 'platform']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creator_social_accounts');
    }
};
