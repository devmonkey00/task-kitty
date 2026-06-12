<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class EmployeeWorkReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canViewHrDashboard();
    }

    public function view(User $user, User $employee): bool
    {
        return $user->canViewHrDashboard() || $user->id === $employee->id;
    }
}
