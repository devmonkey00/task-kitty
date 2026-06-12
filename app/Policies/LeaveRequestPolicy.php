<?php

namespace App\Policies;

use App\Enums\LeaveApprovalTier;
use App\Enums\LeaveRequestStatus;
use App\Models\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->canApproveLeave()
            || $leaveRequest->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function review(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($leaveRequest->status !== LeaveRequestStatus::Pending) {
            return false;
        }

        $tier = $leaveRequest->current_approval_tier;

        if ($tier === LeaveApprovalTier::TeamLead) {
            return $user->hasAnyRole([
                \App\Enums\EnterpriseRole::SuperAdmin->value,
                \App\Enums\EnterpriseRole::TeamLead->value,
            ]) || $leaveRequest->user->team_lead_id === $user->id;
        }

        if ($tier === LeaveApprovalTier::HrAdmin) {
            return $user->canFinalizeLeave();
        }

        return false;
    }
}
