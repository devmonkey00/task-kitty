<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

final class AttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canViewHrDashboard();
    }

    public function view(User $user, Attendance $attendance): bool
    {
        return $user->canViewHrDashboard()
            || $attendance->user_id === $user->id;
    }
}
