<?php

namespace Database\Factories;

use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subtask>
 */
class SubtaskFactory extends Factory
{
    protected $model = Subtask::class;

    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'title' => fake()->sentence(3),
            'is_completed' => false,
            'position' => 0,
        ];
    }
}
