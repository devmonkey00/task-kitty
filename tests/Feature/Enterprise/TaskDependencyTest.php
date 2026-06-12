<?php

namespace Tests\Feature\Enterprise;

use App\Enums\TaskStatus;
use App\Exceptions\Tasks\TaskDependencyBlockedException;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskDependency;
use App\Models\User;
use App\Services\Tasks\MoveTaskAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskDependencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_move_to_in_progress_when_dependency_incomplete(): void
    {
        $this->seed();

        $pm = User::where('email', 'pm@taskkitty.test')->firstOrFail();
        $project = Project::firstOrFail();

        $blocker = Task::factory()->create([
            'project_id' => $project->id,
            'status' => TaskStatus::Todo,
            'created_by' => $pm->id,
        ]);

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'status' => TaskStatus::Todo,
            'created_by' => $pm->id,
        ]);

        TaskDependency::create([
            'task_id' => $task->id,
            'depends_on_task_id' => $blocker->id,
        ]);

        $this->expectException(TaskDependencyBlockedException::class);

        app(MoveTaskAction::class)->execute($task, [
            'status' => TaskStatus::InProgress->value,
        ]);
    }

    public function test_can_move_to_in_progress_when_dependency_done(): void
    {
        $this->seed();

        $pm = User::where('email', 'pm@taskkitty.test')->firstOrFail();
        $project = Project::firstOrFail();

        $blocker = Task::factory()->create([
            'project_id' => $project->id,
            'status' => TaskStatus::Done,
            'created_by' => $pm->id,
        ]);

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'status' => TaskStatus::Todo,
            'created_by' => $pm->id,
        ]);

        TaskDependency::create([
            'task_id' => $task->id,
            'depends_on_task_id' => $blocker->id,
        ]);

        $updated = app(MoveTaskAction::class)->execute($task, [
            'status' => TaskStatus::InProgress->value,
        ]);

        $this->assertSame(TaskStatus::InProgress, $updated->status);
    }
}
