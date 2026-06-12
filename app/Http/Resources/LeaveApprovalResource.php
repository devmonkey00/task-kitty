<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class LeaveApprovalResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'tier' => $this->tier->value,
            'tier_label' => $this->tier->label(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'acted_at' => $this->acted_at?->toISOString(),
        ];
    }
}
