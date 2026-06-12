<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AttendanceStatus;
use App\Enums\LeaveRequestStatus;
use App\Http\Resources\HrAttendanceResource;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Services\Hr\ListHrAttendancesAction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class HrDashboardController extends Controller
{
    public function index(Request $request, ListHrAttendancesAction $listAttendances): Response
    {
        $this->authorize('viewAny', Attendance::class);

        $filters = [
            'date' => $request->input('date', now()->toDateString()),
            'status' => $request->input('status'),
            'search' => $request->input('search'),
        ];

        $paginator = $listAttendances->execute($filters);

        return Inertia::render('Hr/Dashboard', [
            'attendances' => HrAttendanceResource::collection($paginator),
            'stats' => $listAttendances->statsForDate($filters['date']),
            'pendingLeaveCount' => LeaveRequest::query()
                ->where('status', LeaveRequestStatus::Pending)
                ->count(),
            'filters' => $filters,
            'statusOptions' => collect(AttendanceStatus::cases())->map(fn (AttendanceStatus $s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ])->values()->all(),
        ]);
    }
}
