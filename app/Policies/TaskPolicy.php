<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Task $task): bool
    {
        if ($user->canEditTasks()) {
            return true;
        }

        if ($this->userCreatedTask($user, $task)) {
            return true;
        }

        return $user->isEmployee() && $this->userIsAssignee($user, $task);
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->canEditTasks()
            || $this->userCreatedTask($user, $task);
    }

    private function userCreatedTask(User $user, Task $task): bool
    {
        return $task->created_by !== null
            && (int) $task->created_by === (int) $user->id;
    }

    private function userIsAssignee(User $user, Task $task): bool
    {
        return $task->assigned_to !== null
            && (int) $task->assigned_to === (int) $user->id;
    }
}
