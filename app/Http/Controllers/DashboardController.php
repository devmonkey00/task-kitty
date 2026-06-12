<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Services\Attendance\AttendanceCalculator;
use App\Services\Attendance\AttendancePresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly AttendancePresenter $attendancePresenter,
        private readonly AttendanceCalculator $attendanceCalculator,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $today = now()->toDateString();

        $todayAttendance = Attendance::query()
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->with('segments')
            ->first();

        $recentAttendances = Attendance::query()
            ->where('user_id', $user->id)
            ->with('segments')
            ->orderByDesc('date')
            ->limit(7)
            ->get()
            ->map(function (Attendance $a) {
                $totals = $this->attendanceCalculator->computeTotals($a);

                return [
                    'id' => $a->id,
                    'date' => $a->date->toDateString(),
                    'status' => $a->status?->value,
                    'clock_in' => $a->clock_in?->copy()
                        ->timezone(config('app.timezone'))
                        ->toIso8601String(),
                    'clock_out' => $a->clock_out?->copy()
                        ->timezone(config('app.timezone'))
                        ->toIso8601String(),
                    'hours_worked' => $totals['hours_worked'],
                    'overtime_minutes' => $totals['overtime_minutes'],
                ];
            });

        return Inertia::render('Dashboard', [
            'attendance' => $this->attendancePresenter->toDashboardArray($todayAttendance),
            'recentAttendances' => $recentAttendances,
            'canManageLeave' => $user->canApproveLeave(),
            'requiresGeolocation' => (bool) config('enterprise.attendance.require_geofence', true)
                && $user->office_location_id !== null,
        ]);
    }
}
