<?php

declare(strict_types=1);

namespace App\Services\Leave;

use App\Enums\LeaveApprovalStatus;
use App\Enums\LeaveApprovalTier;
use App\Enums\LeaveRequestStatus;
use App\Events\Leave\LeaveRequestUpdated;
use App\Exceptions\Leave\LeaveApprovalException;
use App\Models\LeaveApproval;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class ApproveLeaveRequestAction
{
    public function execute(User $approver, LeaveRequest $leaveRequest): LeaveRequest
    {
        if ($leaveRequest->status !== LeaveRequestStatus::Pending) {
            throw new LeaveApprovalException('This leave request is no longer pending.');
        }

        return DB::transaction(function () use ($approver, $leaveRequest): LeaveRequest {
            $tier = $leaveRequest->current_approval_tier;

            $approval = $this->pendingApprovalForTier($leaveRequest, $tier);

            $approval->update([
                'status' => LeaveApprovalStatus::Approved,
                'approver_id' => $approver->id,
                'acted_at' => now(),
            ]);

            if ($tier === LeaveApprovalTier::TeamLead) {
                $leaveRequest->update([
                    'current_approval_tier' => LeaveApprovalTier::HrAdmin,
                ]);

                $leaveRequest = $leaveRequest->fresh(['approvals', 'user:id,name,email']);
                LeaveRequestUpdated::dispatch($leaveRequest, 'tier_approved');

                return $leaveRequest;
            }

            $leaveRequest->update([
                'status' => LeaveRequestStatus::Approved,
                'reviewed_by' => $approver->id,
                'reviewed_at' => now(),
                'hr_finalized_at' => now(),
            ]);

            $leaveRequest = $leaveRequest->fresh(['approvals', 'reviewer', 'user:id,name,email']);
            LeaveRequestUpdated::dispatch($leaveRequest, 'approved');

            return $leaveRequest;
        });
    }

    private function pendingApprovalForTier(
        LeaveRequest $leaveRequest,
        LeaveApprovalTier $tier,
    ): LeaveApproval {
        $approval = $leaveRequest->approvals()
            ->where('tier', $tier)
            ->where('status', LeaveApprovalStatus::Pending)
            ->first();

        if ($approval === null) {
            throw new LeaveApprovalException('No pending approval step found for this request.');
        }

        return $approval;
    }
}
