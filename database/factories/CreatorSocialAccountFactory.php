<?php

namespace Database\Factories;

use App\Enums\ConnectionStatus;
use App\Enums\Platform;
use App\Models\Creator;
use App\Models\CreatorSocialAccount;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CreatorSocialAccount>
 */
class CreatorSocialAccountFactory extends Factory
{
    public function definition(): array
    {
        $platform = fake()->randomElement(Platform::cases());
        $handle = Str::lower(fake()->unique()->userName());

        return [
            'creator_id' => Creator::factory(),
            'platform' => $platform,
            'handle' => $handle,
            'profile_url' => $platform->profileUrl($handle),
            'follower_count' => fake()->numberBetween(1_000, 900_000),
            'connection_status' => ConnectionStatus::Unconnected,
        ];
    }

    public function platform(Platform $platform, ?string $handle = null): static
    {
        return $this->state(function (array $attributes) use ($platform, $handle) {
            $handle ??= $attributes['handle'];

            return [
                'platform' => $platform,
                'handle' => $handle,
                'profile_url' => $platform->profileUrl($handle),
            ];
        });
    }

    public function connected(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider_account_id' => $attributes['provider_account_id'] ?? 'acct_'.Str::random(12),
            'connection_status' => ConnectionStatus::Connected,
            'access_token' => 'fake-token:'.$attributes['platform']->value.':'.$attributes['handle'].':seed',
            'refresh_token' => 'fake-refresh:'.$attributes['handle'],
            'token_expires_at' => now()->addHour(),
            'scopes' => ['fake.read'],
            'connected_at' => now(),
            'last_synced_at' => now(),
        ]);
    }
}
