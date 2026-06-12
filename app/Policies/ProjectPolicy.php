<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->canManageProjects();
    }

    public function update(User $user, Project $project): bool
    {
        return $user->isSuperAdmin()
            || $project->created_by === $user->id;
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->isSuperAdmin()
            || $project->created_by === $user->id;
    }
}
