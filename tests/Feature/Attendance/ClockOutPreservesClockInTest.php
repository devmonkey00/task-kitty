<?php

namespace Tests\Feature\Attendance;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\User;
use App\Services\Attendance\ClockOutEmployeeAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockOutPreservesClockInTest extends TestCase
{
    use RefreshDatabase;

    public function test_clock_out_does_not_change_clock_in_timestamp(): void
    {
        $user = User::factory()->create();
        $clockIn = now()->subHours(3);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => AttendanceStatus::Active,
            'clock_in' => $clockIn,
            'clock_out' => null,
        ]);

        $attendance->segments()->create([
            'started_at' => $clockIn,
            'ended_at' => now(),
        ]);

        $originalClockIn = $attendance->fresh()->clock_in?->toDateTimeString();

        app(ClockOutEmployeeAction::class)->execute($user, '127.0.0.1');

        $attendance->refresh();

        $this->assertSame($originalClockIn, $attendance->clock_in?->toDateTimeString());
        $this->assertNotNull($attendance->clock_out);
        $this->assertSame(AttendanceStatus::Completed, $attendance->status);
    }
}
