<?php

namespace Database\Factories;

use App\Models\OfficeLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OfficeLocation>
 */
class OfficeLocationFactory extends Factory
{
    protected $model = OfficeLocation::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company().' Office',
            'address' => fake()->address(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'radius_meters' => 100,
            'is_active' => true,
        ];
    }
}
