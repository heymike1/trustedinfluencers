<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Config values an admin may change without a deploy. Each row overrides one config key, so the
 * rest of the app keeps reading config() and never needs to know where the value came from.
 *
 * Secrets (API credentials, mail, database) stay in .env and are deliberately not listed here.
 */
class Settings
{
    private const CACHE_KEY = 'settings.overrides';

    /** Keys an admin is allowed to set, with the type they are stored as. */
    public const EDITABLE = [
        'social.sponsors.slots_per_rail' => 'int',
        'social.sponsors.price' => 'int',
        'social.sponsors.advance_price' => 'int',
        'social.sponsors.currency' => 'string',
        'social.sponsors.days' => 'int',
        'social.sponsors.hold_minutes' => 'int',
        'social.sponsors.contact' => 'string',
        'social.enabled_platforms' => 'array',
        'social.sync.refresh_every_hours' => 'int',
        'social.sync.manual_cooldown_minutes' => 'int',
        'social.sync.stale_after_days' => 'int',
        'social.sync.content_limit' => 'int',
        'social.contact.forward_to_public_email' => 'bool',
        'app.tagline' => 'string',
    ];

    /** @return array<string, mixed> */
    public function all(): array
    {
        if (! $this->tableExists()) {
            return [];
        }

        return Cache::rememberForever(self::CACHE_KEY, fn () => DB::table('settings')
            ->pluck('value', 'key')
            ->map(fn (string $value) => json_decode($value, true))
            ->all());
    }

    /** Push every stored override onto the config, so config() is the single way to read them. */
    public function apply(): void
    {
        foreach ($this->all() as $key => $value) {
            if (array_key_exists($key, self::EDITABLE)) {
                config([$key => $value]);
            }
        }
    }

    public function set(string $key, mixed $value): void
    {
        abort_unless(array_key_exists($key, self::EDITABLE), 500, "Setting {$key} is not editable.");

        DB::table('settings')->updateOrInsert(
            ['key' => $key],
            ['value' => json_encode($value), 'updated_at' => now(), 'created_at' => now()],
        );

        Cache::forget(self::CACHE_KEY);
        config([$key => $value]);
    }

    /** Drop the override and fall back to whatever the config file (and .env) says. */
    public function forget(string $key): void
    {
        DB::table('settings')->where('key', $key)->delete();
        Cache::forget(self::CACHE_KEY);
    }

    public function isOverridden(string $key): bool
    {
        return array_key_exists($key, $this->all());
    }

    private function tableExists(): bool
    {
        try {
            return Schema::hasTable('settings');
        } catch (\Throwable) {
            return false;
        }
    }
}
