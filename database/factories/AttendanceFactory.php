<?php

namespace Database\Factories;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        $clockIn = fake()->dateTimeBetween('-1 day', 'now');

        return [
            'user_id' => User::factory(),
            'date' => now()->toDateString(),
            'status' => AttendanceStatus::Completed,
            'clock_in' => $clockIn,
            'clock_out' => (clone $clockIn)->modify('+8 hours'),
            'hours_worked' => 8,
            'total_minutes' => 480,
            'overtime_minutes' => 0,
            'clock_in_ip' => fake()->ipv4(),
            'browser_fingerprint' => fake()->sha256(),
            'is_within_geofence' => true,
        ];
    }
}
