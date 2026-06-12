<?php

namespace App\Models;

use App\Enums\LeaveApprovalStatus;
use App\Enums\LeaveApprovalTier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveApproval extends Model
{
    protected $fillable = [
        'leave_request_id',
        'tier',
        'approver_id',
        'status',
        'comments',
        'acted_at',
    ];

    protected function casts(): array
    {
        return [
            'tier' => LeaveApprovalTier::class,
            'status' => LeaveApprovalStatus::class,
            'acted_at' => 'datetime',
        ];
    }

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
