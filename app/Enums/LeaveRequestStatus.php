<?php

namespace App\Enums;

enum LeaveRequestStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Denied = 'denied';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Denied => 'Denied',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
