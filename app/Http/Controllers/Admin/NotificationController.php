<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of notifications.
     * Only Normal Admin can view notifications.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Notification::class);

        $query = Notification::with(['user:id,username,email', 'announcement:id,title'])
            ->orderByDesc('created_at');

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('read_status')) {
            if ($request->read_status === 'read') {
                $query->whereNotNull('read_at');
            } elseif ($request->read_status === 'unread') {
                $query->whereNull('read_at');
            }
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('announcement_id')) {
            $query->where('announcement_id', $request->announcement_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $notifications = $query->paginate(20)->withQueryString();

        // Stats for the dashboard cards
        $stats = [
            'total' => Notification::count(),
            'unread' => Notification::whereNull('read_at')->count(),
            'broadcast' => Notification::whereNull('user_id')->count(),
            'linked' => Notification::whereNotNull('announcement_id')->count(),
        ];

        return view('admin.notifications.index', compact('notifications', 'stats'));
    }

    /**
     * Show the form for creating a new notification.
     */
    public function create()
    {
        $this->authorize('create', Notification::class);

        $users = User::where('role', 'user')
            ->where('is_notifications_enabled', true)
            ->orderBy('username')
            ->get(['id', 'username', 'email']);

        $announcements = Announcement::orderByDesc('created_at')
            ->get(['id', 'title']);

        return view('admin.notifications.create', compact('users', 'announcements'));
    }

    /**
     * Store a newly created notification.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Notification::class);

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
            'metadata' => ['nullable', 'json'],
        ]);

        $count = 0;
        $options = [
            'type' => $validated['type'],
            'priority' => $validated['priority'],
            'announcement_id' => $validated['announcement_id'] ?? null,
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'metadata' => isset($validated['metadata']) ? json_decode($validated['metadata'], true) : null,
        ];

        try {
            switch ($validated['delivery_type']) {
                case 'single':
                    $this->notificationService->sendToUser(
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

            $message = $count > 0
                ? "Notification created and sent to {$count} user(s) successfully."
                : "Notification created but no eligible users found.";

            return redirect()
                ->route('admin.notifications.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create notification: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified notification.
     */
    public function show(Notification $notification)
    {
        $this->authorize('view', $notification);

        $notification->load(['user', 'announcement']);

        return view('admin.notifications.show', compact('notification'));
    }

    /**
     * Remove the specified notification.
     */
    public function destroy(Notification $notification)
    {
        $this->authorize('delete', $notification);

        $notification->delete();

        return redirect()
            ->route('admin.notifications.index')
            ->with('success', 'Notification deleted successfully.');
    }
}
