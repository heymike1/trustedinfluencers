<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The prices were free text ("€250"), which a payment provider cannot charge. They become whole
 * numbers with the currency beside them; an existing override is converted rather than lost.
 */
return new class extends Migration
{
    public function up(): void
    {
        $currency = null;

        foreach (['social.sponsors.price', 'social.sponsors.advance_price'] as $key) {
            $row = DB::table('settings')->where('key', $key)->first();

            if (! $row) {
                continue;
            }

            $value = (string) json_decode($row->value, true);
            $currency ??= match (true) {
                str_contains($value, '$') => 'usd',
                str_contains($value, '£') => 'gbp',
                str_contains($value, '€') => 'eur',
                default => null,
            };

            $amount = (int) preg_replace('/\D/', '', $value);

            DB::table('settings')->where('key', $key)->update(['value' => json_encode($amount)]);
        }

        if ($currency) {
            DB::table('settings')->updateOrInsert(
                ['key' => 'social.sponsors.currency'],
                ['value' => json_encode($currency), 'updated_at' => now(), 'created_at' => now()],
            );
        }

        Cache::forget('settings.overrides');
    }

    public function down(): void
    {
        // The numbers still read fine; there is nothing to put back.
    }
};
