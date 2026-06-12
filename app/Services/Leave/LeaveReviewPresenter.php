<?php

declare(strict_types=1);

namespace App\Services\Leave;

use App\Enums\LeaveApprovalTier;
use App\Enums\LeaveRequestStatus;
use App\Models\LeaveRequest;
use App\Models\User;

final class LeaveReviewPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function forIndexRow(LeaveRequest $leave, User $viewer): array
    {
        $canReview = $viewer->can('review', $leave);

        return [
            'can_review' => $canReview,
            'review_action_label' => $canReview
                ? $this->reviewActionLabel($leave->current_approval_tier)
                : null,
            'current_tier_label' => $leave->current_approval_tier?->label(),
            'approval_progress' => $this->approvalProgress($leave),
            'awaiting_tier_label' => $leave->status === LeaveRequestStatus::Pending
                ? $leave->current_approval_tier?->label()
                : null,
        ];
    }

    private function reviewActionLabel(?LeaveApprovalTier $tier): ?string
    {
        return match ($tier) {
            LeaveApprovalTier::TeamLead => 'Approve as Team Lead',
            LeaveApprovalTier::HrAdmin => 'Finalize as HR',
            default => null,
        };
    }

    /**
     * @return list<array{tier: int, tier_label: string, status: string, status_label: string}>
     */
    private function approvalProgress(LeaveRequest $leave): array
    {
        if (! $leave->relationLoaded('approvals')) {
            return [];
        }

        return $leave->approvals->map(fn ($approval) => [
            'tier' => $approval->tier->value,
            'tier_label' => $approval->tier->label(),
            'status' => $approval->status->value,
            'status_label' => $approval->status->label(),
            'acted_at' => $approval->acted_at?->toISOString(),
        ])->values()->all();
    }
}
