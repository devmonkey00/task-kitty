<?php

declare(strict_types=1);

namespace App\Exceptions\Tasks;

use App\Models\Task;
use RuntimeException;

final class TaskDependencyBlockedException extends RuntimeException
{
    public static function blockedBy(Task $blockingTask): self
    {
        return new self(
            "This task cannot start until \"{$blockingTask->title}\" is completed.",
        );
    }
}
