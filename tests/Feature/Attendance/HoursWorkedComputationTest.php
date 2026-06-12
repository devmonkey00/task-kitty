<?php

namespace Tests\Feature\Attendance;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\User;
use App\Services\Attendance\AttendancePresenter;
use App\Services\Attendance\ClockOutEmployeeAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HoursWorkedComputationTest extends TestCase
{
    use RefreshDatabase;

    public function test_clock_out_persists_hours_worked_from_segments(): void
    {
        $user = User::factory()->create();
        $clockIn = now()->subMinutes(125);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => AttendanceStatus::Active,
            'clock_in' => $clockIn,
            'clock_out' => null,
        ]);

        $attendance->segments()->create([
            'started_at' => $clockIn,
            'ended_at' => null,
        ]);

        app(ClockOutEmployeeAction::class)->execute($user, '127.0.0.1');

        $attendance->refresh();

        $this->assertGreaterThanOrEqual(2.0, (float) $attendance->hours_worked);
        $this->assertGreaterThanOrEqual(120, $attendance->total_minutes);
    }

    public function test_presenter_shows_live_hours_while_clocked_in(): void
    {
        $user = User::factory()->create();
        $clockIn = now()->subMinutes(45);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => AttendanceStatus::Active,
            'clock_in' => $clockIn,
            'clock_out' => null,
            'hours_worked' => null,
            'total_minutes' => 0,
        ]);

        $attendance->segments()->create([
            'started_at' => $clockIn,
            'ended_at' => null,
        ]);

        $payload = app(AttendancePresenter::class)->toDashboardArray(
            $attendance->fresh(['segments']),
        );

        $this->assertNotNull($payload);
        $this->assertGreaterThanOrEqual(0.5, $payload['hours_worked']);
        $this->assertGreaterThanOrEqual(30, $payload['total_minutes']);
    }

    public function test_fallback_uses_clock_in_and_clock_out_when_no_segments(): void
    {
        $user = User::factory()->create();
        $clockIn = now()->subHours(2);
        $clockOut = now();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => AttendanceStatus::Completed,
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'hours_worked' => null,
            'total_minutes' => 0,
        ]);

        $payload = app(AttendancePresenter::class)->toDashboardArray($attendance);

        $this->assertGreaterThanOrEqual(1.9, $payload['hours_worked']);
    }
}
