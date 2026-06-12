<?php

declare(strict_types=1);

namespace App\Services\Attendance;

use App\DTO\Attendance\AttendanceContextData;
use App\Enums\AttendanceStatus;
use App\Events\Attendance\AttendanceUpdated;
use App\Exceptions\Attendance\AttendanceStateException;
use App\Exceptions\Attendance\GeofenceViolationException;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class ClockInEmployeeAction
{
    public function __construct(
        private readonly GeofenceService $geofenceService,
    ) {}

    public function execute(User $user, AttendanceContextData $context): Attendance
    {
        $user->loadMissing('officeLocation');
        $today = now()->toDateString();

        $existing = Attendance::query()
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($existing !== null) {
            if ($existing->clock_out === null) {
                throw new AttendanceStateException('You are already clocked in.');
            }

            throw new AttendanceStateException('You have already completed attendance for today.');
        }

        $isWithinGeofence = $this->resolveGeofenceCompliance($user, $context);

        return DB::transaction(function () use ($user, $today, $context, $isWithinGeofence): Attendance {
            $now = now();

            $attendance = Attendance::create([
                'user_id' => $user->id,
                'date' => $today,
                'status' => AttendanceStatus::Active,
                'clock_in' => $now,
                'office_location_id' => $user->office_location_id,
                'clock_in_ip' => $context->ipAddress,
                'browser_fingerprint' => $context->browserFingerprint,
                'clock_in_latitude' => $context->latitude,
                'clock_in_longitude' => $context->longitude,
                'is_within_geofence' => $isWithinGeofence,
            ]);

            $attendance->segments()->create([
                'started_at' => $now,
                'ip_address' => $context->ipAddress,
                'user_agent' => $context->userAgent,
                'browser_fingerprint' => $context->browserFingerprint,
                'latitude' => $context->latitude,
                'longitude' => $context->longitude,
                'is_within_geofence' => $isWithinGeofence,
            ]);

            AttendanceUpdated::dispatch($attendance, 'clock_in');

            return $attendance;
        });
    }

    private function resolveGeofenceCompliance(User $user, AttendanceContextData $context): ?bool
    {
        $office = $user->officeLocation;

        if ($office === null) {
            return null;
        }

        if (! config('enterprise.attendance.require_geofence', true)) {
            return true;
        }

        if ($context->latitude === null || $context->longitude === null) {
            throw GeofenceViolationException::outsideOfficeRadius();
        }

        $within = $this->geofenceService->isWithinOffice(
            $office,
            $context->latitude,
            $context->longitude,
        );

        if (! $within) {
            throw GeofenceViolationException::outsideOfficeRadius();
        }

        return true;
    }
}
