<?php

declare(strict_types=1);

namespace App\Exceptions\Attendance;

use RuntimeException;

final class GeofenceViolationException extends RuntimeException
{
    public static function outsideOfficeRadius(): self
    {
        return new self('Clock-in is only allowed within the designated office geofence.');
    }
}
