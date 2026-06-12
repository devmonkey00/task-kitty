<?php

namespace Tests\Feature\Enterprise;

use App\Enums\TaskStatus;
use App\Events\Tasks\TaskStatusUpdated;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\Tasks\MoveTaskAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TaskBroadcastingTest extends TestCase
{
    use RefreshDatabase;

    public function test_move_task_dispatches_broadcast_event(): void
    {
        $this->seed();

        Event::fake([TaskStatusUpdated::class]);

        $pm = User::where('email', 'pm@taskkitty.test')->firstOrFail();
        $project = Project::firstOrFail();

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'status' => TaskStatus::Todo,
            'created_by' => $pm->id,
        ]);

        app(MoveTaskAction::class)->execute($task, [
            'status' => TaskStatus::InProgress->value,
        ]);

        Event::assertDispatched(TaskStatusUpdated::class, function (TaskStatusUpdated $event) use ($task) {
            return $event->task->id === $task->id
                && $event->broadcastOn()[0]->name === 'private-projects.'.$task->project_id;
        });
    }
}
