<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cards link straight to the sponsor with utm tags on them, so the visit lands in the sponsor's
 * own analytics and there is no counter of ours to keep.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sponsor_slots', function (Blueprint $table) {
            $table->dropColumn('clicks');
        });
    }

    public function down(): void
    {
        Schema::table('sponsor_slots', function (Blueprint $table) {
            $table->unsignedInteger('clicks')->default(0);
        });
    }
};
