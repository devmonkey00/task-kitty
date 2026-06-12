<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'assigned_to' => null,
            'status' => TaskStatus::Todo,
            'priority' => TaskPriority::Medium,
            'due_date' => fake()->optional()->dateTimeBetween('now', '+30 days'),
            'estimated_minutes' => fake()->optional()->numberBetween(30, 480),
            'actual_minutes' => 0,
            'position' => 0,
            'created_by' => User::factory(),
        ];
    }
}
