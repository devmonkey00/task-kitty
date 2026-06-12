<?php

namespace Tests\Feature\Enterprise;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Subtask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeWorkReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_view_own_daily_work_report(): void
    {
        $this->seed();

        $employee = User::where('email', 'employee@taskkitty.test')->firstOrFail();
        $project = Project::factory()->create();
        $today = now()->toDateString();

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'assigned_to' => $employee->id,
            'created_by' => $employee->id,
            'status' => TaskStatus::Done,
            'completed_at' => now(),
        ]);

        $parentTask = Task::factory()->create([
            'project_id' => $project->id,
            'assigned_to' => $employee->id,
            'created_by' => $employee->id,
        ]);

        Subtask::factory()->create([
            'task_id' => $parentTask->id,
            'title' => 'Wire API endpoints',
            'is_completed' => true,
            'completed_at' => now(),
        ]);

        $this->actingAs($employee)
            ->get(route('work-report.index', ['date' => $today]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Reports/DailyWork')
                ->where('mode', 'detail')
                ->has('report.tasks', 1)
                ->has('report.subtasks', 1)
                ->where('report.stats.tasks_completed', 1)
                ->where('report.stats.subtasks_completed', 1));
    }

    public function test_employee_cannot_view_another_employees_report(): void
    {
        $this->seed();

        $employee = User::where('email', 'employee@taskkitty.test')->firstOrFail();
        $manager = User::where('email', 'pm@taskkitty.test')->firstOrFail();

        $this->actingAs($employee)
            ->get(route('work-report.index', ['employee_id' => $manager->id]))
            ->assertForbidden();
    }

    public function test_hr_admin_can_view_employee_overview(): void
    {
        $this->seed();

        $hr = User::where('email', 'hr@taskkitty.test')->firstOrFail();

        $this->actingAs($hr)
            ->get(route('work-report.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Reports/DailyWork')
                ->where('mode', 'overview')
                ->has('stats')
                ->has('employees'));
    }
}
