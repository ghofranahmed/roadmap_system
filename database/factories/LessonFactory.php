<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\LearningUnit;

class LessonFactory extends Factory
{
    public function definition(): array
    {
        return [
            'learning_unit_id' => LearningUnit::factory(),
            'title' => fake()->sentence(5),
            'description' => fake()->paragraph(),
            'position' => fake()->numberBetween(1, 30),
            'is_active' => true,
        ];
    }
}
