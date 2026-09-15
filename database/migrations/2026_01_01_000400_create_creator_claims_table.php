<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creator_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('creator_social_account_id')->constrained()->cascadeOnDelete();
            $table->string('platform', 20);
            $table->string('status', 20)->default('pending')->index(); // pending | verified | failed
            $table->string('expected_provider_account_id')->nullable();
            $table->string('returned_provider_account_id')->nullable();
            $table->string('returned_handle')->nullable();
            $table->string('failure_reason')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creator_claims');
    }
};
