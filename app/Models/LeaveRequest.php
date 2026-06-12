<?php

namespace App\Models;

use App\Enums\LeaveApprovalTier;
use App\Enums\LeaveRequestStatus;
use App\Models\Concerns\LogsActivityTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveRequest extends Model
{
    /** @use HasFactory<\Database\Factories\LeaveRequestFactory> */
    use HasFactory, LogsActivityTrait;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'start_date',
        'end_date',
        'reason',
        'status',
        'current_approval_tier',
        'reviewed_by',
        'reviewed_at',
        'hr_finalized_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => LeaveRequestStatus::class,
            'current_approval_tier' => LeaveApprovalTier::class,
            'reviewed_at' => 'datetime',
            'hr_finalized_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(LeaveApproval::class)->orderBy('tier');
    }
}
