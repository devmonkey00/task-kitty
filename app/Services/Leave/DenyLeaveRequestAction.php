<?php

declare(strict_types=1);

namespace App\Services\Leave;

use App\Enums\LeaveApprovalStatus;
use App\Enums\LeaveRequestStatus;
use App\Events\Leave\LeaveRequestUpdated;
use App\Exceptions\Leave\LeaveApprovalException;
use App\Models\LeaveApproval;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class DenyLeaveRequestAction
{
    public function execute(User $approver, LeaveRequest $leaveRequest, ?string $comments = null): LeaveRequest
    {
        if ($leaveRequest->status !== LeaveRequestStatus::Pending) {
            throw new LeaveApprovalException('This leave request is no longer pending.');
        }

        return DB::transaction(function () use ($approver, $leaveRequest, $comments): LeaveRequest {
            $approval = $leaveRequest->approvals()
                ->where('tier', $leaveRequest->current_approval_tier)
                ->where('status', LeaveApprovalStatus::Pending)
                ->first();

            if (! $approval instanceof LeaveApproval) {
                throw new LeaveApprovalException('No pending approval step found for this request.');
            }

            $approval->update([
                'status' => LeaveApprovalStatus::Denied,
                'approver_id' => $approver->id,
                'comments' => $comments,
                'acted_at' => now(),
            ]);

            $leaveRequest->update([
                'status' => LeaveRequestStatus::Denied,
                'reviewed_by' => $approver->id,
                'reviewed_at' => now(),
            ]);

            $leaveRequest = $leaveRequest->fresh(['approvals', 'reviewer', 'user:id,name,email']);
            LeaveRequestUpdated::dispatch($leaveRequest, 'denied');

            return $leaveRequest;
        });
    }
}
