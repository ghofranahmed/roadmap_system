<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    /**
     * Create notifications from an announcement when published.
     * 
     * @param Announcement $announcement
     * @return int Number of notifications created
     */
    public function createFromAnnouncement(Announcement $announcement): int
    {
        if (!$announcement->send_notification) {
            return 0;
        }

        $users = $this->getTargetUsers($announcement);
        $notifications = [];
        $now = now();

        foreach ($users as $user) {
            // Skip if user has notifications disabled
            if (!$user->is_notifications_enabled) {
                continue;
            }

            $notifications[] = [
                'user_id' => $user->id,
                'announcement_id' => $announcement->id,
                'title' => $announcement->title,
                'message' => $announcement->description,
                'type' => 'announcement',
                'priority' => 'medium',
                'is_active' => true,
                'read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
                'metadata' => json_encode([
                    'announcement_type' => $announcement->type,
                    'link' => $announcement->link,
                ]),
            ];
        }

        if (!empty($notifications)) {
            // Insert in chunks for performance
            foreach (array_chunk($notifications, 500) as $chunk) {
                Notification::insert($chunk);
            }
        }

        return count($notifications);
    }

    /**
     * Create a single user notification.
     * 
     * @param int $userId
     * @param string $title
     * @param string $message
     * @param array $options
     * @return Notification
     */
    public function sendToUser(int $userId, string $title, string $message, array $options = []): Notification
    {
        $user = User::findOrFail($userId);
        
        // Skip if user has notifications disabled
        if (!$user->is_notifications_enabled) {
            throw new \Exception("User has notifications disabled.");
        }

        return Notification::create([
            'user_id' => $userId,
            'announcement_id' => $options['announcement_id'] ?? null,
            'title' => $title,
            'message' => $message,
            'type' => $options['type'] ?? 'general',
            'priority' => $options['priority'] ?? 'medium',
            'is_active' => $options['is_active'] ?? true,
            'scheduled_at' => isset($options['scheduled_at']) ? Carbon::parse($options['scheduled_at']) : null,
            'metadata' => $options['metadata'] ?? null,
        ]);
    }

    /**
     * Create a broadcast notification (for all users or eligible users).
     * 
     * @param string $title
     * @param string $message
     * @param array $options
     * @return int Number of notifications created
     */
    public function sendBroadcast(string $title, string $message, array $options = []): int
    {
        // Get all users with notifications enabled
        $users = User::where('is_notifications_enabled', true)
            ->where('role', 'user') // Only regular users, not admins
            ->get();

        $notifications = [];
        $now = now();
        $scheduledAt = isset($options['scheduled_at']) ? Carbon::parse($options['scheduled_at']) : null;

        foreach ($users as $user) {
            $notifications[] = [
                'user_id' => $user->id, // Per-user rows for broadcast (consistent approach)
                'announcement_id' => $options['announcement_id'] ?? null,
                'title' => $title,
                'message' => $message,
                'type' => $options['type'] ?? 'general',
                'priority' => $options['priority'] ?? 'medium',
                'is_active' => $options['is_active'] ?? true,
                'scheduled_at' => $scheduledAt,
                'read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
                'metadata' => isset($options['metadata']) ? json_encode($options['metadata']) : null,
            ];
        }

        if (!empty($notifications)) {
            // Insert in chunks for performance
            foreach (array_chunk($notifications, 500) as $chunk) {
                Notification::insert($chunk);
            }
        }

        return count($notifications);
    }

    /**
     * Create notifications for a targeted group of users.
     * 
     * @param array $userIds
     * @param string $title
     * @param string $message
     * @param array $options
     * @return int Number of notifications created
     */
    public function sendToUsers(array $userIds, string $title, string $message, array $options = []): int
    {
        $users = User::whereIn('id', $userIds)
            ->where('is_notifications_enabled', true)
            ->get();

        $notifications = [];
        $now = now();
        $scheduledAt = isset($options['scheduled_at']) ? Carbon::parse($options['scheduled_at']) : null;

        foreach ($users as $user) {
            $notifications[] = [
                'user_id' => $user->id,
                'announcement_id' => $options['announcement_id'] ?? null,
                'title' => $title,
                'message' => $message,
                'type' => $options['type'] ?? 'general',
                'priority' => $options['priority'] ?? 'medium',
                'is_active' => $options['is_active'] ?? true,
                'scheduled_at' => $scheduledAt,
                'read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
                'metadata' => isset($options['metadata']) ? json_encode($options['metadata']) : null,
            ];
        }

        if (!empty($notifications)) {
            foreach (array_chunk($notifications, 500) as $chunk) {
                Notification::insert($chunk);
            }
        }

        return count($notifications);
    }

    /**
     * Get target users based on announcement target_type.
     * 
     * @param Announcement $announcement
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getTargetUsers(Announcement $announcement)
    {
        switch ($announcement->target_type) {
            case 'all':
                return User::where('is_notifications_enabled', true)
                    ->where('role', 'user')
                    ->get();

            case 'specific_users':
                $userIds = $announcement->target_rules ?? [];
                if (empty($userIds)) {
                    return collect([]);
                }
                return User::whereIn('id', $userIds)
                    ->where('is_notifications_enabled', true)
                    ->get();

            case 'inactive_users':
                // Users who haven't logged in for 7+ days
                return User::where('is_notifications_enabled', true)
                    ->where('role', 'user')
                    ->where(function ($q) {
                        $q->where('last_login_at', '<=', now()->subDays(7))
                          ->orWhere(function ($q2) {
                              $q2->whereNull('last_login_at')
                                 ->where('last_active_at', '<=', now()->subDays(7));
                          });
                    })
                    ->get();

            case 'low_progress':
                // Users enrolled but with no lesson progress for 5+ days
                return User::where('is_notifications_enabled', true)
                    ->where('role', 'user')
                    ->whereHas('enrollments', function ($q) {
                        $q->where('status', 'active');
                    })
                    ->whereDoesntHave('lessonProgress', function ($q) {
                        $q->where('last_updated_at', '>=', now()->subDays(5))
                          ->orWhere('updated_at', '>=', now()->subDays(5));
                    })
                    ->get();

            default:
                return collect([]);
        }
    }
}

