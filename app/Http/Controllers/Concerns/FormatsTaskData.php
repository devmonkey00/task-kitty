<?php

namespace App\Http\Controllers\Concerns;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Resources\TaskResource;
use App\Models\Task;

trait FormatsTaskData
{
    protected function taskToArray(Task $task): array
    {
        $data = (new TaskResource($task))->resolve();

        if ($user = request()->user()) {
            $data['can_update'] = $user->can('update', $task);
        }

        return $data;
    }

    protected function enumOptions(): array
    {
        return [
            'statuses' => collect(TaskStatus::cases())->map(fn (TaskStatus $s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ])->values()->all(),
            'priorities' => collect(TaskPriority::cases())->map(fn (TaskPriority $p) => [
                'value' => $p->value,
                'label' => $p->label(),
            ])->values()->all(),
        ];
    }
}
