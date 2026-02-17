<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'profile_picture' => null,
            'last_active_at' => fake()->optional()->dateTimeBetween('-30 days', 'now'),
            'remember_token' => Str::random(10),
            'role' => 'user',
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => 'admin']);
    }

    public function techAdmin(): static
    {
        return $this->state(fn () => ['role' => 'tech_admin']);
    }
}
