<?php

declare(strict_types=1);

namespace App\DTO\Attendance;

final readonly class AttendanceContextData
{
    public function __construct(
        public string $ipAddress,
        public ?string $userAgent = null,
        public ?string $browserFingerprint = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
    ) {}

    public static function fromRequest(\Illuminate\Http\Request $request): self
    {
        return new self(
            ipAddress: $request->ip() ?? '0.0.0.0',
            userAgent: $request->userAgent(),
            browserFingerprint: $request->input('browser_fingerprint'),
            latitude: $request->filled('latitude') ? (float) $request->input('latitude') : null,
            longitude: $request->filled('longitude') ? (float) $request->input('longitude') : null,
        );
    }
}
