<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Attendance */
final class HrAttendanceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...(new AttendanceResource($this))->resolve(),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
            'office' => $this->whenLoaded('officeLocation', fn () => $this->officeLocation ? [
                'id' => $this->officeLocation->id,
                'name' => $this->officeLocation->name,
            ] : null),
            'clock_in_ip_masked' => $this->maskIp($this->clock_in_ip),
            'geofence_label' => $this->geofenceLabel(),
        ];
    }

    private function maskIp(?string $ip): ?string
    {
        if ($ip === null || $ip === '') {
            return null;
        }

        $parts = explode('.', $ip);

        if (count($parts) === 4) {
            return $parts[0].'.'.$parts[1].'.'.$parts[2].'.xxx';
        }

        return substr($ip, 0, max(0, strlen($ip) - 4)).'****';
    }

    private function geofenceLabel(): string
    {
        if ($this->is_within_geofence === null) {
            return 'N/A';
        }

        return $this->is_within_geofence ? 'In range' : 'Out of range';
    }
}
