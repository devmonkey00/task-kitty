<?php

namespace App\Policies;

use App\Models\Subtask;
use App\Models\User;

class SubtaskPolicy
{
    public function create(User $user, \App\Models\Task $task): bool
    {
        return $user->can('update', $task);
    }

    public function update(User $user, Subtask $subtask): bool
    {
        return $user->can('update', $subtask->task);
    }

    public function delete(User $user, Subtask $subtask): bool
    {
        return $user->can('update', $subtask->task);
    }
}
