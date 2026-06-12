<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceSegment extends Model
{
    protected $fillable = [
        'attendance_id',
        'started_at',
        'ended_at',
        'ip_address',
        'user_agent',
        'browser_fingerprint',
        'latitude',
        'longitude',
        'is_within_geofence',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_within_geofence' => 'boolean',
        ];
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function durationMinutes(): ?int
    {
        if (! $this->started_at || ! $this->ended_at) {
            return null;
        }

        return max(0, (int) $this->started_at->diffInMinutes($this->ended_at, true));
    }
}
