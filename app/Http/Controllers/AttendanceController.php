<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\Attendance\AttendanceContextData;
use App\Http\Requests\ClockInRequest;
use App\Services\Attendance\ClockInEmployeeAction;
use App\Services\Attendance\ClockOutEmployeeAction;
use App\Services\Attendance\PauseAttendanceAction;
use App\Services\Attendance\ResumeAttendanceAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class AttendanceController extends Controller
{
    public function clockIn(
        ClockInRequest $request,
        ClockInEmployeeAction $clockIn,
    ): RedirectResponse {
        $clockIn->execute(
            $request->user(),
            AttendanceContextData::fromRequest($request),
        );

        return back()->with('success', 'Clocked in successfully.');
    }

    public function clockOut(
        Request $request,
        ClockOutEmployeeAction $clockOut,
    ): RedirectResponse {
        $clockOut->execute(
            $request->user(),
            $request->ip() ?? '0.0.0.0',
        );

        return back()->with('success', 'Clocked out successfully.');
    }

    public function pause(
        Request $request,
        PauseAttendanceAction $pause,
    ): RedirectResponse {
        $pause->execute($request->user());

        return back()->with('success', 'Break started.');
    }

    public function resume(
        ClockInRequest $request,
        ResumeAttendanceAction $resume,
    ): RedirectResponse {
        $resume->execute(
            $request->user(),
            AttendanceContextData::fromRequest($request),
        );

        return back()->with('success', 'Work resumed.');
    }
}
