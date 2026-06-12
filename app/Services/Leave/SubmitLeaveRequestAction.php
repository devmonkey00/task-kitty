<?php

declare(strict_types=1);

namespace App\Services\Leave;

use App\Enums\LeaveApprovalStatus;
use App\Enums\LeaveApprovalTier;
use App\Enums\LeaveRequestStatus;
use App\Events\Leave\LeaveRequestUpdated;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class SubmitLeaveRequestAction
{
    /**
     * @param  array{start_date: string, end_date: string, reason: string}  $data
     */
    public function execute(User $user, array $data): LeaveRequest
    {
        return DB::transaction(function () use ($user, $data): LeaveRequest {
            $leaveRequest = LeaveRequest::create([
                ...$data,
                'user_id' => $user->id,
                'status' => LeaveRequestStatus::Pending,
                'current_approval_tier' => LeaveApprovalTier::TeamLead,
            ]);

            foreach ([LeaveApprovalTier::TeamLead, LeaveApprovalTier::HrAdmin] as $tier) {
                $leaveRequest->approvals()->create([
                    'tier' => $tier,
                    'status' => LeaveApprovalStatus::Pending,
                ]);
            }

            $leaveRequest = $leaveRequest->load(['approvals', 'user:id,name,email']);

            LeaveRequestUpdated::dispatch($leaveRequest, 'submitted');

            return $leaveRequest;
        });
    }
}
