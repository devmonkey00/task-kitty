<?php

declare(strict_types=1);

namespace App\Services\Attendance;

use App\Models\OfficeLocation;

final class GeofenceService
{
    public function isWithinOffice(
        OfficeLocation $office,
        float $latitude,
        float $longitude,
    ): bool {
        $distanceMeters = $this->distanceMeters(
            $office->latitude,
            $office->longitude,
            $latitude,
            $longitude,
        );

        return $distanceMeters <= $office->radius_meters;
    }

    private function distanceMeters(
        float $latFrom,
        float $lonFrom,
        float $latTo,
        float $lonTo,
    ): float {
        $earthRadius = 6371000;
        $latFromRad = deg2rad($latFrom);
        $latToRad = deg2rad($latTo);
        $deltaLat = deg2rad($latTo - $latFrom);
        $deltaLon = deg2rad($lonTo - $lonFrom);

        $a = sin($deltaLat / 2) ** 2
            + cos($latFromRad) * cos($latToRad) * sin($deltaLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
