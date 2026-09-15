<?php

namespace Database\Seeders;

use App\Models\CreatorCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CreatorCategorySeeder extends Seeder
{
    public const CATEGORIES = [
        'Finance', 'Tech', 'Fitness', 'Food', 'Travel', 'Gaming', 'Beauty', 'Education', 'Comedy', 'Lifestyle', 'Business', 'Photography',
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $i => $name) {
            CreatorCategory::updateOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'sort_order' => $i]);
        }
    }
}
