<?php

namespace Database\Seeders;

use App\Enums\LeaveApprovalStatus;
use App\Enums\LeaveApprovalTier;
use App\Enums\LeaveRequestStatus;
use App\Enums\TaskDependencyType;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\LeaveApproval;
use App\Models\LeaveRequest;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Models\TaskDependency;
use App\Models\TimeLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class EnterpriseDemoSeeder extends Seeder
{
    public function run(): void
    {
        $manager = User::where('email', 'pm@taskkitty.test')->first();
        $employee = User::where('email', 'employee@taskkitty.test')->first();

        if (! $manager || ! $employee) {
            return;
        }

        $project = Project::factory()->create([
            'name' => 'Task Kitty Enterprise Rollout',
            'description' => 'Advanced task management, HR, and real-time collaboration.',
            'created_by' => $manager->id,
        ]);

        $taskA = Task::factory()->create([
            'project_id' => $project->id,
            'title' => 'Define RBAC matrix',
            'status' => TaskStatus::Done,
            'priority' => TaskPriority::High,
            'assigned_to' => $manager->id,
            'created_by' => $manager->id,
            'estimated_minutes' => 240,
            'actual_minutes' => 180,
            'position' => 0,
        ]);

        $taskB = Task::factory()->create([
            'project_id' => $project->id,
            'title' => 'Implement geofenced clock-in',
            'status' => TaskStatus::InProgress,
            'priority' => TaskPriority::High,
            'assigned_to' => $employee->id,
            'created_by' => $manager->id,
            'estimated_minutes' => 480,
            'position' => 0,
        ]);

        TaskDependency::create([
            'task_id' => $taskB->id,
            'depends_on_task_id' => $taskA->id,
            'type' => TaskDependencyType::FinishToStart,
        ]);

        $tags = Tag::whereIn('slug', ['feature', 'urgent'])->pluck('id');
        $taskB->tags()->sync($tags);

        TimeLog::factory()->create([
            'task_id' => $taskB->id,
            'user_id' => $employee->id,
            'minutes' => 120,
            'description' => 'API scaffolding',
        ]);

        $leave = LeaveRequest::factory()->create([
            'user_id' => $employee->id,
            'status' => LeaveRequestStatus::Pending,
            'current_approval_tier' => LeaveApprovalTier::TeamLead,
            'reason' => 'Family event',
        ]);

        foreach ([LeaveApprovalTier::TeamLead, LeaveApprovalTier::HrAdmin] as $tier) {
            LeaveApproval::create([
                'leave_request_id' => $leave->id,
                'tier' => $tier,
                'status' => LeaveApprovalStatus::Pending,
            ]);
        }
    }
}
