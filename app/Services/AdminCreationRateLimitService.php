<?php

namespace App\Services;

use App\Models\AdminCreationLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminCreationRateLimitService
{
    const MAX_CREATIONS_PER_24_HOURS = 3;

    /**
     * Check if the creator can create an admin of the specified role.
     * 
     * @param User $creator The user creating the admin
     * @param string $role The role being created ('admin' or 'tech_admin')
     * @return array ['allowed' => bool, 'message' => string, 'remaining_time' => Carbon|null]
     */
    public function checkRateLimit(User $creator, string $role): array
    {
        // Get the 24-hour window start time
        $windowStart = Carbon::now()->subHours(24);

        // Count creations in the last 24 hours for this creator and role
        $count = AdminCreationLog::where('creator_id', $creator->id)
            ->where('created_role', $role)
            ->where('created_at', '>=', $windowStart)
            ->count();

        if ($count >= self::MAX_CREATIONS_PER_24_HOURS) {
            // Find the oldest creation in the window to calculate when limit resets
            $oldestCreation = AdminCreationLog::where('creator_id', $creator->id)
                ->where('created_role', $role)
                ->where('created_at', '>=', $windowStart)
                ->orderBy('created_at', 'asc')
                ->first();

            $resetTime = $oldestCreation 
                ? $oldestCreation->created_at->addHours(24)
                : Carbon::now()->addHours(24);

            $remainingMinutes = Carbon::now()->diffInMinutes($resetTime, false);

            return [
                'allowed' => false,
                'message' => "You have reached the maximum limit of " . self::MAX_CREATIONS_PER_24_HOURS . " {$role} admin creations per 24 hours.",
                'remaining_time' => $resetTime,
                'remaining_minutes' => max(0, $remainingMinutes),
            ];
        }

        return [
            'allowed' => true,
            'message' => '',
            'remaining_time' => null,
            'remaining_minutes' => null,
            'remaining_creations' => self::MAX_CREATIONS_PER_24_HOURS - $count,
        ];
    }

    /**
     * Log an admin creation event.
     * 
     * @param User $creator The user creating the admin
     * @param User $createdUser The newly created admin user
     * @param string $role The role that was created
     * @return AdminCreationLog
     */
    public function logCreation(User $creator, User $createdUser, string $role): AdminCreationLog
    {
        return AdminCreationLog::create([
            'creator_id' => $creator->id,
            'created_user_id' => $createdUser->id,
            'created_role' => $role,
        ]);
    }
}

