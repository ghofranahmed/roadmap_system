<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Lesson;

class SubLessonFactory extends Factory
{
    public function definition(): array
    {
        return [
            'lesson_id' => Lesson::factory(),
            'position' => fake()->numberBetween(1, 10),
            'description' => fake()->optional()->paragraph(),
        ];
    }
}
