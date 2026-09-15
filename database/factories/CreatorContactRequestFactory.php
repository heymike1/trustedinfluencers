<?php

namespace Database\Factories;

use App\Models\Creator;
use App\Models\CreatorContactRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CreatorContactRequest>
 */
class CreatorContactRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'creator_id' => Creator::factory(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'company' => fake()->company(),
            'subject' => fake()->sentence(3),
            'message' => fake()->paragraph(),
        ];
    }
}
