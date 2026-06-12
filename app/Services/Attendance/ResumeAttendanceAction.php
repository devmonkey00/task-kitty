<?php

declare(strict_types=1);

namespace App\Services\Attendance;

use App\DTO\Attendance\AttendanceContextData;
use App\Enums\AttendanceStatus;
use App\Events\Attendance\AttendanceUpdated;
use App\Exceptions\Attendance\AttendanceStateException;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class ResumeAttendanceAction
{
    public function execute(User $user, AttendanceContextData $context): Attendance
    {
        $attendance = $this->findPausedAttendance($user);

        return DB::transaction(function () use ($attendance, $context): Attendance {
            $now = now();

            $attendance->segments()->create([
                'started_at' => $now,
                'ip_address' => $context->ipAddress,
                'user_agent' => $context->userAgent,
                'browser_fingerprint' => $context->browserFingerprint,
                'latitude' => $context->latitude,
                'longitude' => $context->longitude,
                'is_within_geofence' => $attendance->is_within_geofence,
            ]);

            $attendance->update(['status' => AttendanceStatus::Active]);

            $attendance = $attendance->fresh(['segments']);

            AttendanceUpdated::dispatch($attendance, 'resume');

            return $attendance;
        });
    }

    private function findPausedAttendance(User $user): Attendance
    {
        $attendance = Attendance::query()
            ->where('user_id', $user->id)
            ->whereDate('date', now()->toDateString())
            ->whereNull('clock_out')
            ->where('status', AttendanceStatus::Paused)
            ->first();

        if ($attendance === null) {
            throw new AttendanceStateException('No paused session to resume.');
        }

        return $attendance;
    }
}
