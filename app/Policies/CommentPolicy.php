<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;

class CommentPolicy
{
    public function create(User $user, Task $task): bool
    {
        return $user->can('view', $task);
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $user->isSuperAdmin()
            || $comment->user_id === $user->id;
    }
}
