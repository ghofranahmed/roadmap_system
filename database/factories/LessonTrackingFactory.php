<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LessonTrackingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'is_complete' => fake()->boolean(30),
            'last_updated_at' => fake()->optional()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
