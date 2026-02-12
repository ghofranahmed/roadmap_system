<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        $username = fake()->unique()->userName();

        return [
            'username' => $username,
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'profile_picture' => null,
            'last_active_at' => fake()->optional()->dateTimeBetween('-30 days', 'now'),
            'remember_token' => Str::random(10),

            // social fields
            'google_id' => null,
            'github_id' => null,
            'avatar' => fake()->optional()->imageUrl(200, 200),

            // is_admin موجود عندك default=false
            'is_admin' => false,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['is_admin' => true]);
    }
}
