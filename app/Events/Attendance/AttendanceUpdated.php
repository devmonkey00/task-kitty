<?php

declare(strict_types=1);

namespace App\Events\Attendance;

use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Services\Attendance\AttendancePresenter;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class AttendanceUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Attendance $attendance,
        public string $action,
    ) {}

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('users.'.$this->attendance->user_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'attendance.updated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $presenter = app(AttendancePresenter::class);

        $attendance = $this->attendance->fresh(['segments']);

        return [
            'action' => $this->action,
            'attendance' => $presenter->toDashboardArray($attendance),
            'record' => (new AttendanceResource($attendance))->resolve(),
        ];
    }
}
