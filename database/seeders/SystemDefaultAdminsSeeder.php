<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SystemDefaultAdminsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates the two default system admin accounts if they don't exist.
     * This seeder is idempotent - it will not update existing accounts.
     */
    public function run(): void
    {
        // Create System Admin (Normal Admin)
        User::firstOrCreate(
            ['email' => 'admin@system.com'],
            [
                'username' => 'system_admin',
                'email' => 'admin@system.com',
                'role' => 'admin',
                'password' => Hash::make('admin123'),
                'is_notifications_enabled' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create System Tech Admin (Technical Admin)
        User::firstOrCreate(
            ['email' => 'techadmin@system.com'],
            [
                'username' => 'system_tech_admin',
                'email' => 'techadmin@system.com',
                'role' => 'tech_admin',
                'password' => Hash::make('techadmin123'),
                'is_notifications_enabled' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}

