<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\SubLesson;

class ResourceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sub_lesson_id' => SubLesson::factory(),
            'title' => fake()->sentence(3),
            'type' => fake()->randomElement(['book', 'video', 'article']),
            'language' => fake()->randomElement(['ar', 'en']),
            'link' => fake()->url(),
        ];
    }
}
