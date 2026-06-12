<?php

namespace Tests\Feature\Enterprise;

use App\Enums\LeaveApprovalTier;
use App\Enums\LeaveRequestStatus;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\Leave\ApproveLeaveRequestAction;
use App\Services\Leave\SubmitLeaveRequestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveApprovalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_submit_creates_two_approval_tiers(): void
    {
        $this->seed();

        $employee = User::where('email', 'employee@taskkitty.test')->firstOrFail();

        $leave = app(SubmitLeaveRequestAction::class)->execute($employee, [
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeeks(2)->toDateString(),
            'reason' => 'Vacation',
        ]);

        $this->assertSame(LeaveRequestStatus::Pending, $leave->status);
        $this->assertSame(LeaveApprovalTier::TeamLead, $leave->current_approval_tier);
        $this->assertCount(2, $leave->approvals);
    }

    public function test_team_lead_approval_advances_to_hr_tier(): void
    {
        $this->seed();

        $employee = User::where('email', 'employee@taskkitty.test')->firstOrFail();
        $lead = User::where('email', 'lead@taskkitty.test')->firstOrFail();

        $leave = app(SubmitLeaveRequestAction::class)->execute($employee, [
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeeks(2)->toDateString(),
            'reason' => 'Vacation',
        ]);

        $leave = app(ApproveLeaveRequestAction::class)->execute($lead, $leave);

        $this->assertSame(LeaveRequestStatus::Pending, $leave->status);
        $this->assertSame(LeaveApprovalTier::HrAdmin, $leave->current_approval_tier);
    }

    public function test_hr_approval_finalizes_request(): void
    {
        $this->seed();

        $employee = User::where('email', 'employee@taskkitty.test')->firstOrFail();
        $lead = User::where('email', 'lead@taskkitty.test')->firstOrFail();
        $hr = User::where('email', 'hr@taskkitty.test')->firstOrFail();

        $leave = app(SubmitLeaveRequestAction::class)->execute($employee, [
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeeks(2)->toDateString(),
            'reason' => 'Vacation',
        ]);

        $leave = app(ApproveLeaveRequestAction::class)->execute($lead, $leave);
        $leave = app(ApproveLeaveRequestAction::class)->execute($hr, $leave);

        $this->assertSame(LeaveRequestStatus::Approved, $leave->status);
        $this->assertNotNull($leave->hr_finalized_at);
    }
}
