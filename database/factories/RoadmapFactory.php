<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RoadmapFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'level' => fake()->randomElement(['beginner', 'intermediate', 'advanced']),
            'description' => fake()->paragraph(),
            'is_active' => true,
        ];
    }
}
