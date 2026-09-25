<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Buying is what gets you a spot, so a checkout no longer reserves one and there is nothing to
 * time out. Anything still sitting in a checkout is closed off.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('sponsor_slots')->where('status', 'pending')->update(['status' => 'cancelled', 'position' => null]);

        Schema::table('sponsor_slots', function (Blueprint $table) {
            $table->dropColumn('reserved_until');
        });

        DB::table('settings')->where('key', 'social.sponsors.hold_minutes')->delete();

        Cache::forget('settings.overrides');
    }

    public function down(): void
    {
        Schema::table('sponsor_slots', function (Blueprint $table) {
            $table->timestamp('reserved_until')->nullable()->after('status');
        });
    }
};
