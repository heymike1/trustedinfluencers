<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Spots are sold in dollars, so there is no currency to choose and nothing to store about it.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->where('key', 'social.sponsors.currency')->delete();
        DB::table('sponsor_slots')->whereNotNull('currency')->update(['currency' => 'usd']);

        Cache::forget('settings.overrides');
    }

    public function down(): void
    {
        // Nothing to restore: the amounts were never touched, only what we call them.
    }
};
