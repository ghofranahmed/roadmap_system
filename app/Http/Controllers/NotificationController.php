<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * List notifications for the authenticated user.
     * GET /notifications
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = Notification::forUser($request->user()->id)
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 15));

        return $this->paginatedResponse($notifications, 'Notifications retrieved successfully');
    }

    /**
     * Mark a notification as read.
     * POST /notifications/{id}/read
     */
    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $notification = Notification::findOrFail($id);

        // Only the owner can mark their notification as read
        if ($notification->user_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized. You can only mark your own notifications.', null, 403);
        }

        // Already read check
        if ($notification->read_at !== null) {
            return $this->successResponse($notification, 'Notification already marked as read');
        }

        $notification->markAsRead();

        return $this->successResponse($notification->fresh(), 'Notification marked as read');
    }
}

