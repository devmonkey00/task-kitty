<?php

declare(strict_types=1);

namespace App\Events\Tasks;

use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class TaskCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Task $task) {}

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('projects.'.$this->task->project_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'task.created';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $this->task->loadMissing(['assignee:id,name', 'creator:id,name']);
        $this->task->loadCount(['comments', 'subtasks']);

        return [
            'task' => (new TaskResource($this->task))->resolve(),
        ];
    }
}
