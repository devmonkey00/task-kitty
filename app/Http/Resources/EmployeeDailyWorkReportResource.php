<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

final class EmployeeDailyWorkReportResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array{
         *     employee: array{id: int, name: string, email: string},
         *     date: string,
         *     tasks: Collection<int, Task>,
         *     subtasks: Collection<int, Subtask>,
         *     stats: array{tasks_completed: int, subtasks_completed: int}
         * } $data
         */
        $data = $this->resource;

        return [
            'employee' => $data['employee'],
            'date' => $data['date'],
            'stats' => $data['stats'],
            'tasks' => $data['tasks']->map(fn (Task $task) => [
                'id' => $task->id,
                'title' => $task->title,
                'completed_at' => $task->completed_at?->toIso8601String(),
                'project' => $task->project ? [
                    'id' => $task->project->id,
                    'name' => $task->project->name,
                ] : null,
            ])->values()->all(),
            'subtasks' => $data['subtasks']->map(fn (Subtask $subtask) => [
                'id' => $subtask->id,
                'title' => $subtask->title,
                'completed_at' => $subtask->completed_at?->toIso8601String(),
                'task' => [
                    'id' => $subtask->task->id,
                    'title' => $subtask->task->title,
                    'project' => $subtask->task->project ? [
                        'id' => $subtask->task->project->id,
                        'name' => $subtask->task->project->name,
                    ] : null,
                ],
            ])->values()->all(),
        ];
    }
}
