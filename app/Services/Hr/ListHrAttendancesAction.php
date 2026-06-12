<?php

declare(strict_types=1);

namespace App\Services\Hr;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ListHrAttendancesAction
{
    /**
     * @param  array{date?: string, status?: string, search?: string}  $filters
     */
    public function execute(array $filters): LengthAwarePaginator
    {
        $date = $filters['date'] ?? now()->toDateString();

        $query = Attendance::query()
            ->with([
                'user:id,name,email',
                'officeLocation:id,name',
            ])
            ->whereDate('date', $date)
            ->orderByDesc('clock_in')
            ->orderBy('user_id');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('user', function (Builder $userQuery) use ($search): void {
                $userQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->paginate(25)->withQueryString();
    }

    /**
     * @return array{total: int, active: int, paused: int, completed: int, with_overtime: int}
     */
    public function statsForDate(string $date): array
    {
        $rows = Attendance::query()->whereDate('date', $date);

        return [
            'total' => (clone $rows)->count(),
            'active' => (clone $rows)->where('status', AttendanceStatus::Active)->count(),
            'paused' => (clone $rows)->where('status', AttendanceStatus::Paused)->count(),
            'completed' => (clone $rows)->where('status', AttendanceStatus::Completed)->count(),
            'with_overtime' => (clone $rows)->where('overtime_minutes', '>', 0)->count(),
        ];
    }
}
