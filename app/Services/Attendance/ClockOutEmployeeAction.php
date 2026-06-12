<?php

declare(strict_types=1);

namespace App\Services\Attendance;

use App\Enums\AttendanceStatus;
use App\Events\Attendance\AttendanceUpdated;
use App\Exceptions\Attendance\AttendanceStateException;
use App\Models\Attendance;
use App\Models\AttendanceSegment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class ClockOutEmployeeAction
{
    public function __construct(
        private readonly AttendanceCalculator $calculator,
    ) {}

    public function execute(User $user, string $ipAddress): Attendance
    {
        $attendance = $this->findOpenAttendance($user);

        return DB::transaction(function () use ($attendance, $ipAddress): Attendance {
            $now = now();

            $this->closeOpenSegment($attendance, $now);

            $totals = $this->calculator->computeTotals(
                $attendance->fresh(['segments']),
            );

            $attendance->update([
                'clock_out' => $now,
                'clock_out_ip' => $ipAddress,
                'status' => AttendanceStatus::Completed,
                'total_minutes' => $totals['total_minutes'],
                'overtime_minutes' => $totals['overtime_minutes'],
                'hours_worked' => $totals['hours_worked'],
            ]);

            $attendance = $attendance->fresh(['segments']);

            AttendanceUpdated::dispatch($attendance, 'clock_out');

            return $attendance;
        });
    }

    private function findOpenAttendance(User $user): Attendance
    {
        $attendance = Attendance::query()
            ->where('user_id', $user->id)
            ->whereDate('date', now()->toDateString())
            ->whereNull('clock_out')
            ->first();

        if ($attendance === null) {
            throw new AttendanceStateException('No active clock-in found for today.');
        }

        return $attendance;
    }

    private function closeOpenSegment(Attendance $attendance, \Illuminate\Support\Carbon $endedAt): void
    {
        $openSegment = $attendance->segments()
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();

        if ($openSegment instanceof AttendanceSegment) {
            $openSegment->update(['ended_at' => $endedAt]);
        }
    }
}
