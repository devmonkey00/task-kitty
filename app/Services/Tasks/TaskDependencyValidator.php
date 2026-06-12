<?php

declare(strict_types=1);

namespace App\Services\Tasks;

use App\Enums\TaskStatus;
use App\Exceptions\Tasks\TaskDependencyBlockedException;
use App\Models\Task;

final class TaskDependencyValidator
{
    public function assertCanStart(Task $task): void
    {
        $task->loadMissing('dependencies.dependsOn');

        foreach ($task->dependencies as $dependency) {
            $blockingTask = $dependency->dependsOn;

            if ($blockingTask === null) {
                continue;
            }

            if ($blockingTask->status !== TaskStatus::Done) {
                throw TaskDependencyBlockedException::blockedBy($blockingTask);
            }
        }
    }
}
