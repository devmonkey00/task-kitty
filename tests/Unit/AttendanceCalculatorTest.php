<?php

namespace Tests\Unit;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\AttendanceSegment;
use App\Models\User;
use App\Services\Attendance\AttendanceCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_recalculate_totals_uses_absolute_segment_duration(): void
    {
        $user = User::factory()->create();
        $startedAt = now()->subHours(2);
        $endedAt = now();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => AttendanceStatus::Active,
            'clock_in' => $startedAt,
            'clock_out' => null,
        ]);

        AttendanceSegment::query()->create([
            'attendance_id' => $attendance->id,
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
        ]);

        app(AttendanceCalculator::class)->recalculateTotals($attendance);
        $attendance->save();

        $this->assertGreaterThan(0, $attendance->total_minutes);
        $this->assertGreaterThanOrEqual(0, $attendance->hours_worked);
        $this->assertSame(
            $attendance->total_minutes,
            (int) round($attendance->hours_worked * 60),
        );
    }

    public function test_recalculate_totals_never_stores_negative_minutes(): void
    {
        $user = User::factory()->create();
        $startedAt = now();
        $endedAt = now()->subHours(2);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => AttendanceStatus::Completed,
            'clock_in' => $endedAt,
            'clock_out' => $startedAt,
        ]);

        AttendanceSegment::query()->create([
            'attendance_id' => $attendance->id,
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
        ]);

        app(AttendanceCalculator::class)->recalculateTotals($attendance);

        $this->assertGreaterThanOrEqual(0, $attendance->total_minutes);
        $this->assertGreaterThanOrEqual(0, $attendance->hours_worked);
    }
}
