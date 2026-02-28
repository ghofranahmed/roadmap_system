<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminNotificationApiController extends Controller
{
    use ApiResponse;

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * List all notifications (admin view).
     * GET /api/v1/admin/notifications
     */
    public function index(Request $request): JsonResponse
    {
        $query = Notification::with(['user:id,username,email', 'announcement:id,title'])
            ->orderByDesc('created_at');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('announcement_id')) {
            $query->where('announcement_id', $request->announcement_id);
        }

        if ($request->filled('read_status')) {
            if ($request->read_status === 'read') {
                $query->whereNotNull('read_at');
            } elseif ($request->read_status === 'unread') {
                $query->whereNull('read_at');
            }
        }

        $notifications = $query->paginate($request->get('per_page', 20));

        return $this->paginatedResponse($notifications, 'Notifications retrieved successfully');
    }

    /**
     * Create a new notification.
     * POST /api/v1/admin/notifications
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'type' => ['required', 'string', 'max:255'],
            'priority' => ['required', 'in:low,medium,high'],
            'delivery_type' => ['required', 'in:single,broadcast,targeted'],
            'user_id' => ['required_if:delivery_type,single', 'nullable', 'exists:users,id'],
            'user_ids' => ['required_if:delivery_type,targeted', 'nullable', 'array'],
            'user_ids.*' => ['exists:users,id'],
            'announcement_id' => ['nullable', 'exists:announcements,id'],
            'scheduled_at' => ['nullable', 'date'],
            'metadata' => ['nullable', 'array'],
        ]);

        $count = 0;
        $options = [
            'type' => $validated['type'],
            'priority' => $validated['priority'],
            'announcement_id' => $validated['announcement_id'] ?? null,
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'metadata' => $validated['metadata'] ?? null,
        ];

        try {
            switch ($validated['delivery_type']) {
                case 'single':
                    $notification = $this->notificationService->sendToUser(
                        $validated['user_id'],
                        $validated['title'],
                        $validated['message'],
                        $options
                    );
                    $count = 1;
                    break;

                case 'broadcast':
                    $count = $this->notificationService->sendBroadcast(
                        $validated['title'],
                        $validated['message'],
                        $options
                    );
                    break;

                case 'targeted':
                    $count = $this->notificationService->sendToUsers(
                        $validated['user_ids'],
                        $validated['title'],
                        $validated['message'],
                        $options
                    );
                    break;
            }

            return $this->successResponse(
                ['notifications_sent' => $count],
                "Notification sent to {$count} user(s) successfully.",
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create notification: ' . $e->getMessage(), null, 422);
        }
    }

    /**
     * Show a specific notification.
     * GET /api/v1/admin/notifications/{id}
     */
    public function show(int $id): JsonResponse
    {
        $notification = Notification::with(['user:id,username,email', 'announcement:id,title'])
            ->findOrFail($id);

        return $this->successResponse($notification, 'Notification retrieved successfully');
    }

    /**
     * Delete a notification.
     * DELETE /api/v1/admin/notifications/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $notification = Notification::findOrFail($id);
        $notification->delete();

        return $this->successResponse(null, 'Notification deleted successfully');
    }
}

