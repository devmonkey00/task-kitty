<?php

declare(strict_types=1);

namespace App\Services\Tasks;

use App\Enums\TaskStatus;
use App\Events\Tasks\TaskStatusUpdated;
use App\Models\Task;

final class MoveTaskAction
{
    public function __construct(
        private readonly TaskDependencyValidator $dependencyValidator,
    ) {}

    /**
     * @param  array{status?: string, position?: int}  $data
     */
    public function execute(Task $task, array $data): Task
    {
        $newStatus = isset($data['status'])
            ? TaskStatus::from($data['status'])
            : null;

        if (
            $newStatus === TaskStatus::InProgress
            && $task->status !== TaskStatus::InProgress
        ) {
            $this->dependencyValidator->assertCanStart($task);
        }

        if (isset($data['position'])) {
            $task->update($this->withCompletionTimestamp($task, $data));
            $task = $task->fresh();
        } else {
            $position = $task->project->tasks()
                ->where('status', $data['status'])
                ->max('position') ?? -1;

            $task->update($this->withCompletionTimestamp($task, [
                'status' => $data['status'],
                'position' => $position + 1,
            ]));

            $task = $task->fresh();
        }

        $task->load(['assignee:id,name', 'creator:id,name']);
        $task->loadCount([
            'comments',
            'subtasks',
            'subtasks as subtasks_completed_count' => fn ($q) => $q->where('is_completed', true),
        ]);

        TaskStatusUpdated::dispatch($task);

        return $task;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withCompletionTimestamp(Task $task, array $data): array
    {
        if (! isset($data['status'])) {
            return $data;
        }

        $newStatus = TaskStatus::from($data['status']);

        if ($newStatus === TaskStatus::Done && $task->status !== TaskStatus::Done) {
            $data['completed_at'] = now();
        } elseif ($newStatus !== TaskStatus::Done && $task->status === TaskStatus::Done) {
            $data['completed_at'] = null;
        }

        return $data;
    }
}
