<?php

declare(strict_types=1);

namespace App\Services\Attendance;

use App\Models\Attendance;

final class AttendanceCalculator
{
    /**
     * @return array{total_minutes: int, overtime_minutes: int, hours_worked: float}
     */
    public function computeTotals(Attendance $attendance): array
    {
        $attendance->loadMissing('segments');

        $totalMinutes = (int) $attendance->segments
            ->filter(fn ($segment) => $segment->ended_at !== null)
            ->sum(fn ($segment) => (int) ($segment->durationMinutes() ?? 0));

        $openSegment = $attendance->segments
            ->first(fn ($segment) => $segment->ended_at === null);

        if ($openSegment?->started_at) {
            $totalMinutes += max(
                0,
                (int) $openSegment->started_at->diffInMinutes(now(), true),
            );
        }

        if ($totalMinutes === 0) {
            $totalMinutes = $this->fallbackMinutesFromClockTimes($attendance);
        }

        $totalMinutes = max(0, $totalMinutes);

        $standardMinutes = (int) config('enterprise.attendance.standard_work_minutes', 480);
        $overtimeMinutes = max(0, $totalMinutes - $standardMinutes);

        return [
            'total_minutes' => $totalMinutes,
            'overtime_minutes' => $overtimeMinutes,
            'hours_worked' => round($totalMinutes / 60, 2),
        ];
    }

    public function recalculateTotals(Attendance $attendance): void
    {
        $totals = $this->computeTotals($attendance);

        $attendance->total_minutes = $totals['total_minutes'];
        $attendance->overtime_minutes = $totals['overtime_minutes'];
        $attendance->hours_worked = $totals['hours_worked'];
    }

    private function fallbackMinutesFromClockTimes(Attendance $attendance): int
    {
        if ($attendance->clock_in === null) {
            return 0;
        }

        $end = $attendance->clock_out ?? now();

        return max(0, (int) $attendance->clock_in->diffInMinutes($end, true));
    }
}
