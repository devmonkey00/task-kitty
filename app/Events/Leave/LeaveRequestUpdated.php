<?php

declare(strict_types=1);

namespace App\Events\Leave;

use App\Http\Resources\LeaveRequestResource;
use App\Models\LeaveRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class LeaveRequestUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public LeaveRequest $leaveRequest,
        public string $action,
    ) {}

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('leave-requests'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'leave.updated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $this->leaveRequest->loadMissing(['user:id,name,email', 'reviewer:id,name', 'approvals']);

        return [
            'action' => $this->action,
            'leaveRequest' => (new LeaveRequestResource($this->leaveRequest))->resolve(),
        ];
    }
}
