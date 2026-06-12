<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin LeaveRequest */
final class LeaveRequestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'start_date' => $this->start_date->toDateString(),
            'end_date' => $this->end_date->toDateString(),
            'reason' => $this->reason,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'current_approval_tier' => $this->current_approval_tier?->value,
            'current_approval_tier_label' => $this->current_approval_tier?->label(),
            'reviewed_at' => $this->reviewed_at?->toISOString(),
            'hr_finalized_at' => $this->hr_finalized_at?->toISOString(),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
            'reviewer' => $this->whenLoaded('reviewer', fn () => $this->reviewer ? [
                'id' => $this->reviewer->id,
                'name' => $this->reviewer->name,
            ] : null),
            'approvals' => LeaveApprovalResource::collection(
                $this->whenLoaded('approvals'),
            ),
        ];
    }
}
