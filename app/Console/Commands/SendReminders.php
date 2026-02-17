<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendReminders extends Command
{
    protected $signature = 'reminders:send
                            {--inactive-days=7 : Number of days without login to consider a user inactive}
                            {--no-progress-days=5 : Number of days without lesson progress to trigger reminder}';

    protected $description = 'Send motivational reminders to inactive users and users with stale progress';

    public function handle(): int
    {
        $inactiveDays = (int) $this->option('inactive-days');
        $noProgressDays = (int) $this->option('no-progress-days');

        $this->info("Checking for inactive users (>{$inactiveDays} days)...");
        $inactiveCount = $this->sendInactiveReminders($inactiveDays);

        $this->info("Checking for users with stale progress (>{$noProgressDays} days)...");
        $staleCount = $this->sendStaleProgressReminders($noProgressDays);

        $this->info("Done. Sent {$inactiveCount} inactive reminder(s), {$staleCount} stale-progress reminder(s).");

        return self::SUCCESS;
    }

    /**
     * Send reminders to users who haven't logged in for X days.
     */
    private function sendInactiveReminders(int $days): int
    {
        $users = User::where('role', 'user')
            ->where('is_notifications_enabled', true)
            ->where(function ($q) use ($days) {
                $q->where('last_login_at', '<=', now()->subDays($days))
                  ->orWhere(function ($q2) use ($days) {
                      // Fallback: If last_login_at is null, use last_active_at
                      $q2->whereNull('last_login_at')
                         ->where(function ($q3) use ($days) {
                             $q3->where('last_active_at', '<=', now()->subDays($days))
                                ->orWhereNull('last_active_at');
                         });
                  });
            })
            ->get(['id', 'username', 'last_login_at', 'last_active_at']);

        if ($users->isEmpty()) {
            $this->info('No inactive users found.');
            return 0;
        }

        $notifications = [];
        $now = now();
        $today = $now->toDateString();

        foreach ($users as $user) {
            // Avoid duplicate reminder for today
            $alreadySent = Notification::where('user_id', $user->id)
                ->where('type', 'reminder')
                ->whereDate('created_at', $today)
                ->where('title', 'LIKE', '%miss you%')
                ->exists();

            if ($alreadySent) {
                continue;
            }

            $notifications[] = [
                'user_id'    => $user->id,
                'title'      => "We miss you, {$user->username}! 👋",
                'message'    => "It's been a while since your last visit. Your learning roadmaps are waiting for you. Come back and continue your journey!",
                'type'       => 'reminder',
                'is_active'  => true,
                'read_at'    => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($notifications)) {
            foreach (array_chunk($notifications, 500) as $chunk) {
                Notification::insert($chunk);
            }
        }

        $count = count($notifications);
        $this->info("Sent {$count} inactive reminder(s).");

        return $count;
    }

    /**
     * Send reminders to users enrolled but with no lesson progress for Y days.
     */
    private function sendStaleProgressReminders(int $days): int
    {
        $cutoff = now()->subDays($days);

        // Get users who are enrolled with active status
        $users = User::where('role', 'user')
            ->where('is_notifications_enabled', true)
            ->whereHas('enrollments', function ($q) {
                $q->where('status', 'active');
            })
            ->get(['id', 'username']);

        if ($users->isEmpty()) {
            $this->info('No enrolled users found.');
            return 0;
        }

        $notifications = [];
        $now = now();
        $today = $now->toDateString();

        foreach ($users as $user) {
            // Check latest lesson tracking activity for this user
            $latestTracking = DB::table('lesson_trackings')
                ->where('user_id', $user->id)
                ->selectRaw('MAX(COALESCE(last_updated_at, updated_at)) as latest_activity')
                ->first();

            $latestActivity = $latestTracking->latest_activity ?? null;

            // Skip if user has recent activity (within cutoff)
            if ($latestActivity && $latestActivity > $cutoff->toDateTimeString()) {
                continue;
            }

            // Avoid duplicate reminder for today
            $alreadySent = Notification::where('user_id', $user->id)
                ->where('type', 'reminder')
                ->whereDate('created_at', $today)
                ->where('title', 'LIKE', '%Keep going%')
                ->exists();

            if ($alreadySent) {
                continue;
            }

            $notifications[] = [
                'user_id'    => $user->id,
                'title'      => "Keep going, {$user->username}! 🚀",
                'message'    => "You're enrolled in a roadmap but haven't made progress recently. Even a small step counts — open a lesson and keep learning!",
                'type'       => 'reminder',
                'is_active'  => true,
                'read_at'    => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($notifications)) {
            foreach (array_chunk($notifications, 500) as $chunk) {
                Notification::insert($chunk);
            }
        }

        $count = count($notifications);
        $this->info("Sent {$count} stale-progress reminder(s).");

        return $count;
    }
}

