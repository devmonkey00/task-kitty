<?php

namespace Tests\Feature\Enterprise;

use App\Enums\EnterpriseRole;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskUpdateAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_employee_creator_can_update_task_status(): void
    {
        $this->seed();

        $teamLead = User::where('email', 'lead@taskkitty.test')->firstOrFail();
        $project = Project::firstOrFail();

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'status' => TaskStatus::Todo,
            'created_by' => $teamLead->id,
            'assigned_to' => null,
        ]);

        $this->assertFalse($teamLead->hasRole(EnterpriseRole::Employee->value));
        $this->assertTrue($teamLead->can('update', $task));

        $this->actingAs($teamLead)
            ->patch(route('tasks.status', $task), ['status' => TaskStatus::InProgress->value])
            ->assertRedirect();

        $this->assertSame(TaskStatus::InProgress, $task->fresh()->status);
    }

    public function test_non_employee_creator_can_update_task_fields(): void
    {
        $this->seed();

        $teamLead = User::where('email', 'lead@taskkitty.test')->firstOrFail();
        $project = Project::firstOrFail();

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'title' => 'Original',
            'created_by' => $teamLead->id,
        ]);

        $this->actingAs($teamLead)
            ->patch(route('tasks.update', $task), ['title' => 'Updated by creator'])
            ->assertRedirect();

        $this->assertSame('Updated by creator', $task->fresh()->title);
    }

    public function test_non_employee_non_creator_cannot_update_task(): void
    {
        $this->seed();

        $teamLead = User::where('email', 'lead@taskkitty.test')->firstOrFail();
        $employee = User::where('email', 'employee@taskkitty.test')->firstOrFail();
        $project = Project::firstOrFail();

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'created_by' => $employee->id,
            'assigned_to' => $employee->id,
        ]);

        $this->assertFalse($teamLead->can('update', $task));

        $this->actingAs($teamLead)
            ->patch(route('tasks.update', $task), ['title' => 'Blocked'])
            ->assertForbidden();
    }

    public function test_project_manager_can_update_any_task(): void
    {
        $this->seed();

        $projectManager = User::where('email', 'pm@taskkitty.test')->firstOrFail();
        $employee = User::where('email', 'employee@taskkitty.test')->firstOrFail();
        $project = Project::firstOrFail();

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'title' => 'Original',
            'created_by' => $employee->id,
            'assigned_to' => $employee->id,
        ]);

        $this->assertTrue($projectManager->isProjectManager());
        $this->assertTrue($projectManager->can('update', $task));

        $this->actingAs($projectManager)
            ->patch(route('tasks.update', $task), ['title' => 'Updated by PM'])
            ->assertRedirect();

        $this->assertSame('Updated by PM', $task->fresh()->title);

        $this->actingAs($projectManager)
            ->patch(route('tasks.status', $task), ['status' => TaskStatus::InProgress->value])
            ->assertRedirect();

        $this->assertSame(TaskStatus::InProgress, $task->fresh()->status);
    }

    public function test_employee_assignee_can_update_assigned_task(): void
    {
        $this->seed();

        $employee = User::where('email', 'employee@taskkitty.test')->firstOrFail();
        $manager = User::where('email', 'pm@taskkitty.test')->firstOrFail();
        $project = Project::firstOrFail();

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'created_by' => $manager->id,
            'assigned_to' => $employee->id,
        ]);

        $this->actingAs($employee)
            ->patch(route('tasks.update', $task), ['title' => 'Assignee update'])
            ->assertRedirect();

        $this->assertSame('Assignee update', $task->fresh()->title);
    }
}
