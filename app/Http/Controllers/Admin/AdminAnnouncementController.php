<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnnouncementRequest;
use App\Models\Announcement;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAnnouncementController extends Controller
{
    /**
     * List all announcements (admin view).
     * GET /admin/announcements
     */
    public function index(Request $request): JsonResponse
    {
        $announcements = Announcement::with('creator:id,username,email')
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 15));

        return $this->paginatedResponse($announcements, 'Announcements retrieved successfully');
    }

    /**
     * Show a single announcement (admin view).
     * GET /admin/announcements/{id}
     */
    public function show(int $id): JsonResponse
    {
        $announcement = Announcement::with('creator:id,username,email')->findOrFail($id);

        return $this->successResponse($announcement, 'Announcement retrieved successfully');
    }

    /**
     * Create a new announcement.
     * POST /admin/announcements
     */
    public function store(StoreAnnouncementRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Determine status based on publish_at
        if (empty($data['publish_at'])) {
            // Publish immediately
            $data['status'] = 'published';
            $data['publish_at'] = now();
        } elseif (isset($data['status']) && $data['status'] === 'draft') {
            // Keep as draft
            $data['status'] = 'draft';
        } else {
            // Future date → scheduled
            $data['status'] = 'scheduled';
        }

        $data['created_by'] = $request->user()->id;

        $announcement = DB::transaction(function () use ($data) {
            $announcement = Announcement::create($data);

            // If published immediately, send notifications to target users
            if ($announcement->status === 'published') {
                $this->sendNotificationsToTargetUsers($announcement);
            }

            return $announcement;
        });

        return $this->successResponse(
            $announcement->load('creator:id,username,email'),
            'Announcement created successfully',
            201
        );
    }

    /**
     * Send notifications to targeted users for a published announcement.
     */
    public static function sendNotificationsToTargetUsers(Announcement $announcement): void
    {
        $userIds = self::resolveTargetUserIds($announcement);

        if (empty($userIds)) {
            return;
        }

        $notifications = [];
        $now = now();

        foreach ($userIds as $userId) {
            $notifications[] = [
                'user_id'         => $userId,
                'title'           => $announcement->title,
                'message'         => $announcement->content,
                'type'            => 'announcement',
                'announcement_id' => $announcement->id,
                'is_active'       => true,
                'read_at'         => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ];
        }

        // Bulk insert in chunks for performance
        foreach (array_chunk($notifications, 500) as $chunk) {
            Notification::insert($chunk);
        }
    }

    /**
     * Resolve user IDs based on target_type and target_rules.
     */
    public static function resolveTargetUserIds(Announcement $announcement): array
    {
        $rules = $announcement->target_rules ?? [];

        return match ($announcement->target_type) {
            'all' => User::where('role', 'user')
                         ->where('is_notifications_enabled', true)
                         ->pluck('id')
                         ->toArray(),

            'specific_users' => User::whereIn('id', $rules['user_ids'] ?? [])
                                    ->where('is_notifications_enabled', true)
                                    ->pluck('id')
                                    ->toArray(),

            'inactive_users' => self::getInactiveUserIds($rules),

            'low_progress' => self::getLowProgressUserIds($rules),

            default => [],
        };
    }

    /**
     * Get users who haven't logged in for X days.
     */
    private static function getInactiveUserIds(array $rules): array
    {
        $days = $rules['inactive_days'] ?? 7;

        return User::where('role', 'user')
            ->where('is_notifications_enabled', true)
            ->where(function ($q) use ($days) {
                $q->where('last_login_at', '<=', now()->subDays($days))
                  ->orWhereNull('last_login_at');
            })
            ->pluck('id')
            ->toArray();
    }

    /**
     * Get users with low progress (enrolled but progress <= max_progress%).
     */
    private static function getLowProgressUserIds(array $rules): array
    {
        $maxProgress = $rules['max_progress'] ?? 20;

        // Find users who are enrolled and have low lesson completion rate
        return User::where('role', 'user')
            ->where('is_notifications_enabled', true)
            ->whereHas('enrollments', function ($q) {
                $q->where('status', 'active');
            })
            ->get()
            ->filter(function (User $user) use ($maxProgress) {
                $totalLessons = DB::table('lesson_trackings')
                    ->where('user_id', $user->id)
                    ->count();

                if ($totalLessons === 0) {
                    return true; // No progress at all
                }

                $completedLessons = DB::table('lesson_trackings')
                    ->where('user_id', $user->id)
                    ->where('is_complete', true)
                    ->count();

                $progressPercent = ($completedLessons / $totalLessons) * 100;

                return $progressPercent <= $maxProgress;
            })
            ->pluck('id')
            ->toArray();
    }
}

