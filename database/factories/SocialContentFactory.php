<?php

namespace Database\Factories;

use App\Enums\ContentType;
use App\Models\CreatorSocialAccount;
use App\Models\SocialContent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SocialContent>
 */
class SocialContentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'creator_social_account_id' => CreatorSocialAccount::factory(),
            'provider_content_id' => Str::random(11),
            'content_type' => ContentType::Video,
            'title' => fake()->sentence(4),
            'url' => fake()->url(),
            'published_at' => fake()->dateTimeBetween('-90 days'),
            'metrics' => null,
        ];
    }

    public function withMetrics(array $metrics): static
    {
        return $this->state(fn () => ['metrics' => $metrics, 'metrics_synced_at' => now()]);
    }
}
