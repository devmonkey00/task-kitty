<?php

use App\Models\LeaveRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function (User $user, int $id): bool {
    return (int) $user->id === $id;
});

Broadcast::channel('projects.{project}', function (User $user, Project $project): bool {
    return $user->can('view', $project);
});

Broadcast::channel('users.{userId}', function (User $user, int $userId): bool {
    return (int) $user->id === $userId;
});

Broadcast::channel('leave-requests', function (User $user): bool {
    return $user->can('viewAny', LeaveRequest::class)
        && $user->canApproveLeave();
});
