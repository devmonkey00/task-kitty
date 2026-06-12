<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['name' => 'Bug', 'color' => '#ef4444'],
            ['name' => 'Feature', 'color' => '#3b82f6'],
            ['name' => 'Urgent', 'color' => '#f59e0b'],
            ['name' => 'HR', 'color' => '#8b5cf6'],
        ];

        foreach ($tags as $tag) {
            Tag::firstOrCreate(
                ['slug' => Str::slug($tag['name'])],
                ['name' => $tag['name'], 'color' => $tag['color']],
            );
        }
    }
}
