<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The rail period became a number of days, so the old free-text override has nothing to override.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->where('key', 'social.sponsors.period')->delete();

        Cache::forget('settings.overrides');
    }

    public function down(): void
    {
        // Nothing to restore: the key is no longer read anywhere.
    }
};
