<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use App\Models\Concerns\LogsActivityTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    /** @use HasFactory<\Database\Factories\AttendanceFactory> */
    use HasFactory, LogsActivityTrait;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'date',
        'status',
        'clock_in',
        'clock_out',
        'hours_worked',
        'total_minutes',
        'overtime_minutes',
        'office_location_id',
        'clock_in_ip',
        'clock_out_ip',
        'browser_fingerprint',
        'clock_in_latitude',
        'clock_in_longitude',
        'is_within_geofence',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'status' => AttendanceStatus::class,
            'clock_in' => 'datetime',
            'clock_out' => 'datetime',
            'hours_worked' => 'decimal:2',
            'total_minutes' => 'integer',
            'overtime_minutes' => 'integer',
            'clock_in_latitude' => 'decimal:7',
            'clock_in_longitude' => 'decimal:7',
            'is_within_geofence' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function officeLocation(): BelongsTo
    {
        return $this->belongsTo(OfficeLocation::class);
    }

    public function segments(): HasMany
    {
        return $this->hasMany(AttendanceSegment::class)->orderBy('started_at');
    }

    public function calculateHoursWorked(): ?float
    {
        if ($this->total_minutes > 0) {
            return round($this->total_minutes / 60, 2);
        }

        if (! $this->clock_in || ! $this->clock_out) {
            return null;
        }

        return round($this->clock_in->diffInMinutes($this->clock_out, true) / 60, 2);
    }
}
