<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\EmployeeDailyWorkReportResource;
use App\Models\User;
use App\Services\Reports\ListEmployeeDailyWorkReportAction;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class EmployeeWorkReportController extends Controller
{
    public function index(
        Request $request,
        ListEmployeeDailyWorkReportAction $listReport,
    ): Response {
        $viewer = $request->user();
        $canViewAny = $viewer->can('viewAnyEmployeeWorkReports');

        $filters = [
            'date' => $request->input('date', now()->toDateString()),
            'search' => $request->input('search'),
            'employee_id' => $request->input('employee_id'),
        ];

        if ($canViewAny && ! $filters['employee_id']) {
            Gate::authorize('viewAnyEmployeeWorkReports');

            $employees = $listReport->listEmployeesWithActivity($filters);

            return Inertia::render('Reports/DailyWork', [
                'mode' => 'overview',
                'employees' => $employees->through(fn (User $employee) => [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'email' => $employee->email,
                ]),
                'stats' => $listReport->statsForDate($filters['date']),
                'filters' => $filters,
                'canViewAny' => true,
            ]);
        }

        $employee = $filters['employee_id']
            ? User::query()->findOrFail($filters['employee_id'])
            : $viewer;

        Gate::authorize('viewEmployeeWorkReport', $employee);

        $report = $listReport->forEmployee($employee, $filters);

        return Inertia::render('Reports/DailyWork', [
            'mode' => 'detail',
            'report' => (new EmployeeDailyWorkReportResource($report))->resolve(),
            'filters' => [
                'date' => $filters['date'],
                'search' => $filters['search'],
                'employee_id' => $employee->id,
            ],
            'canViewAny' => $canViewAny,
        ]);
    }
}
