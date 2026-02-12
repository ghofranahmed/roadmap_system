<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RoadmapEnrollmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'started_at' => fake()->optional()->dateTimeBetween('-60 days', '-1 day'),
            'completed_at' => null,
            'xp_points' => fake()->numberBetween(0, 3000),
            'status' => fake()->randomElement(['active','completed','paused']),
        ];
    }
}

