<?php

namespace Database\Seeders;

use App\Models\OfficeLocation;
use Illuminate\Database\Seeder;

class OfficeLocationSeeder extends Seeder
{
    public function run(): void
    {
        OfficeLocation::firstOrCreate(
            ['name' => 'Head Office'],
            [
                'address' => '123 Main Street',
                'latitude' => 14.5995124,
                'longitude' => 120.9842195,
                'radius_meters' => 150,
                'is_active' => true,
            ],
        );
    }
}
