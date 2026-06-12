<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Attendance */
final class AttendanceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $status = $this->status;

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'date' => $this->date->toDateString(),
            'status' => $status?->value,
            'status_label' => $status?->label(),
            'clock_in' => $this->clock_in?->copy()
                ->timezone(config('app.timezone'))
                ->toIso8601String(),
            'clock_out' => $this->clock_out?->copy()
                ->timezone(config('app.timezone'))
                ->toIso8601String(),
            'hours_worked' => $this->hours_worked,
            'total_minutes' => $this->total_minutes,
            'overtime_minutes' => $this->overtime_minutes,
            'is_within_geofence' => $this->is_within_geofence,
        ];
    }
}
