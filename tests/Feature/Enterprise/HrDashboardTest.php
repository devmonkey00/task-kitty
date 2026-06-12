<?php

namespace Tests\Feature\Enterprise;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HrDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_admin_can_view_dashboard(): void
    {
        $this->seed();

        $hr = User::where('email', 'hr@taskkitty.test')->firstOrFail();

        $this->actingAs($hr)
            ->get(route('hr.dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Hr/Dashboard')
                ->has('stats')
                ->has('attendances'));
    }

    public function test_employee_cannot_view_hr_dashboard(): void
    {
        $this->seed();

        $employee = User::where('email', 'employee@taskkitty.test')->firstOrFail();

        $this->actingAs($employee)
            ->get(route('hr.dashboard'))
            ->assertForbidden();
    }
}
