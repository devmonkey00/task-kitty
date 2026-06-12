<?php

declare(strict_types=1);

namespace App\Services\Attendance;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use Illuminate\Support\Carbon;

final class AttendancePresenter
{
    public function __construct(
        private readonly AttendanceCalculator $calculator,
    ) {}

    private function formatDateTime(?Carbon $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value->copy()
            ->timezone(config('app.timezone'))
            ->toIso8601String();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function toDashboardArray(?Attendance $attendance): ?array
    {
        if ($attendance === null) {
            return null;
        }

        $status = $attendance->status ?? AttendanceStatus::Active;
        $isOpen = $attendance->clock_out === null
            && in_array($status, [AttendanceStatus::Active, AttendanceStatus::Paused], true);

        $totals = $this->calculator->computeTotals($attendance);

        return [
            'id' => $attendance->id,
            'date' => $attendance->date->toDateString(),
            'status' => $status->value,
            'status_label' => $status->label(),
            'clock_in' => $this->formatDateTime($attendance->clock_in),
            'clock_out' => $this->formatDateTime($attendance->clock_out),
            'hours_worked' => $totals['hours_worked'],
            'total_minutes' => $totals['total_minutes'],
            'overtime_minutes' => $totals['overtime_minutes'],
            'is_clocked_in' => $isOpen,
            'is_active' => $status === AttendanceStatus::Active,
            'is_paused' => $status === AttendanceStatus::Paused,
            'can_pause' => $status === AttendanceStatus::Active && $isOpen,
            'can_resume' => $status === AttendanceStatus::Paused && $isOpen,
            'can_clock_out' => $isOpen,
        ];
    }
}
