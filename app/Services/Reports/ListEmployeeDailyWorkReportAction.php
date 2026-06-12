<?php

declare(strict_types=1);

namespace App\Services\Reports;

use App\Enums\TaskStatus;
use App\Models\Subtask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class ListEmployeeDailyWorkReportAction
{
    /**
     * @param  array{date?: string, employee_id?: int, search?: string}  $filters
     * @return array{
     *     employee: array{id: int, name: string, email: string},
     *     date: string,
     *     tasks: Collection<int, Task>,
     *     subtasks: Collection<int, Subtask>,
     *     stats: array{tasks_completed: int, subtasks_completed: int}
     * }
     */
    public function forEmployee(User $employee, array $filters): array
    {
        $date = $filters['date'] ?? now()->toDateString();

        $tasks = Task::query()
            ->where('assigned_to', $employee->id)
            ->where('status', TaskStatus::Done)
            ->whereDate('completed_at', $date)
            ->with(['project:id,name'])
            ->orderBy('completed_at')
            ->get();

        $subtasks = Subtask::query()
            ->where('is_completed', true)
            ->whereDate('completed_at', $date)
            ->whereHas('task', fn (Builder $query) => $query->where('assigned_to', $employee->id))
            ->with(['task:id,title,project_id', 'task.project:id,name'])
            ->orderBy('completed_at')
            ->get();

        return [
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
            ],
            'date' => $date,
            'tasks' => $tasks,
            'subtasks' => $subtasks,
            'stats' => [
                'tasks_completed' => $tasks->count(),
                'subtasks_completed' => $subtasks->count(),
            ],
        ];
    }

    /**
     * @param  array{date?: string, search?: string}  $filters
     */
    public function listEmployeesWithActivity(array $filters): LengthAwarePaginator
    {
        $date = $filters['date'] ?? now()->toDateString();

        $taskEmployeeIds = Task::query()
            ->where('status', TaskStatus::Done)
            ->whereDate('completed_at', $date)
            ->whereNotNull('assigned_to')
            ->pluck('assigned_to');

        $subtaskEmployeeIds = Subtask::query()
            ->where('is_completed', true)
            ->whereDate('completed_at', $date)
            ->whereHas('task', fn (Builder $query) => $query->whereNotNull('assigned_to'))
            ->with('task:id,assigned_to')
            ->get()
            ->pluck('task.assigned_to');

        $employeeIds = $taskEmployeeIds
            ->merge($subtaskEmployeeIds)
            ->unique()
            ->values();

        $query = User::query()
            ->whereIn('id', $employeeIds)
            ->orderBy('name');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $userQuery) use ($search): void {
                $userQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->paginate(25)->withQueryString();
    }

    /**
     * @return array{employees_with_activity: int, tasks_completed: int, subtasks_completed: int}
     */
    public function statsForDate(string $date): array
    {
        $taskEmployeeIds = Task::query()
            ->where('status', TaskStatus::Done)
            ->whereDate('completed_at', $date)
            ->whereNotNull('assigned_to')
            ->distinct()
            ->pluck('assigned_to');

        $subtaskEmployeeIds = Subtask::query()
            ->where('subtasks.is_completed', true)
            ->whereDate('subtasks.completed_at', $date)
            ->join('tasks', 'tasks.id', '=', 'subtasks.task_id')
            ->whereNotNull('tasks.assigned_to')
            ->distinct()
            ->pluck('tasks.assigned_to');

        return [
            'employees_with_activity' => $taskEmployeeIds
                ->merge($subtaskEmployeeIds)
                ->unique()
                ->count(),
            'tasks_completed' => Task::query()
                ->where('status', TaskStatus::Done)
                ->whereDate('completed_at', $date)
                ->count(),
            'subtasks_completed' => Subtask::query()
                ->where('is_completed', true)
                ->whereDate('completed_at', $date)
                ->count(),
        ];
    }
}
