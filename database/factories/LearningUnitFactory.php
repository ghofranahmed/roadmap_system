<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Roadmap;

class LearningUnitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'roadmap_id' => Roadmap::factory(),
            'title' => fake()->sentence(4),
            'is_active' => true,
            'position' => fake()->numberBetween(1, 30),
        ];
    }
}
