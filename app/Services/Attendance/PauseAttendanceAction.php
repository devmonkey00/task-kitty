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

final class PauseAttendanceAction
{
    public function __construct(
        private readonly AttendanceCalculator $calculator,
    ) {}

    public function execute(User $user): Attendance
    {
        $attendance = $this->findActiveAttendance($user);

        return DB::transaction(function () use ($attendance): Attendance {
            $now = now();

            $openSegment = $attendance->segments()
                ->whereNull('ended_at')
                ->latest('started_at')
                ->first();

            if ($openSegment instanceof AttendanceSegment) {
                $openSegment->update(['ended_at' => $now]);
            }

            $attendance->unsetRelation('segments');
            $this->calculator->recalculateTotals($attendance);

            $attendance->update([
                'status' => AttendanceStatus::Paused,
                'total_minutes' => $attendance->total_minutes,
                'overtime_minutes' => $attendance->overtime_minutes,
                'hours_worked' => $attendance->hours_worked,
            ]);

            $attendance = $attendance->fresh(['segments']);

            AttendanceUpdated::dispatch($attendance, 'pause');

            return $attendance;
        });
    }

    private function findActiveAttendance(User $user): Attendance
    {
        $attendance = Attendance::query()
            ->where('user_id', $user->id)
            ->whereDate('date', now()->toDateString())
            ->whereNull('clock_out')
            ->where('status', AttendanceStatus::Active)
            ->first();

        if ($attendance === null) {
            throw new AttendanceStateException('No active session to pause.');
        }

        return $attendance;
    }
}
