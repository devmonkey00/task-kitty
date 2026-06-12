<?php

namespace App\Enums;

enum LeaveApprovalTier: int
{
    case TeamLead = 1;
    case HrAdmin = 2;

    public function label(): string
    {
        return match ($this) {
            self::TeamLead => 'Team Lead',
            self::HrAdmin => 'HR Admin',
        };
    }
}
