<?php

namespace Database\Factories;

use App\Enums\LeaveApprovalTier;
use App\Enums\LeaveRequestStatus;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveRequest>
 */
class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+1 week', '+1 month');
        $end = fake()->dateTimeBetween($start, (clone $start)->modify('+5 days'));

        return [
            'user_id' => User::factory(),
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'reason' => fake()->sentence(),
            'status' => LeaveRequestStatus::Pending,
            'current_approval_tier' => LeaveApprovalTier::TeamLead,
        ];
    }
}
