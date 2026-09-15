<?php

namespace Database\Factories;

use App\Models\CreatorCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CreatorCategory>
 */
class CreatorCategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(1, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
        ];
    }
}
