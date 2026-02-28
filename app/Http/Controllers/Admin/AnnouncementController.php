<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnnouncementRequest;
use App\Models\Announcement;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AnnouncementController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Display a listing of announcements.
     * Only Normal Admin can view announcements.
     */
    public function index(Request $request)
    {
        // Check authorization using policy
        $this->authorize('viewAny', Announcement::class);

        $query = Announcement::with('creator:id,username,email')
            ->orderByDesc('created_at');

        // Apply type filter if provided
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Apply status filter if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply search if provided
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('creator', function ($q) use ($search) {
                      $q->where('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $announcements = $query->paginate(15)->withQueryString();

        return view('admin.announcements.index', compact('announcements'));
    }

    /**
     * Display the specified announcement details.
     * Only Normal Admin can view announcements.
     */
    public function show(Announcement $announcement)
    {
        // Use the same policy gate as index (admin-only)
        $this->authorize('viewAny', Announcement::class);

        return view('admin.announcements.show', compact('announcement'));
    }

    /**
     * Show the form for creating a new announcement.
     * Only Normal Admin can create announcements.
     */
    public function create()
    {
        // Check authorization using policy
        $this->authorize('create', Announcement::class);

        return view('admin.announcements.create');
    }

    /**
     * Store a newly created announcement.
     * Only Normal Admin can create announcements.
     */
    public function store(StoreAnnouncementRequest $request)
    {
        // Check authorization using policy
        $this->authorize('create', Announcement::class);

        $data = $request->validated();
        $data['created_by'] = $request->user()->id;
        
        // Set status based on publish action
        if ($request->has('publish')) {
            $data['status'] = 'published';
        } else {
            $data['status'] = 'draft';
        }

        $announcement = Announcement::create($data);

        // Create notifications if send_notification is enabled and status is published
        if ($announcement->send_notification && $announcement->status === 'published') {
            $count = $this->notificationService->createFromAnnouncement($announcement);
            $message = $count > 0 
                ? "Announcement created and {$count} notification(s) sent successfully."
                : "Announcement created successfully. No notifications sent (no eligible users).";
        } else {
            $message = 'Announcement created successfully.';
        }

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', $message);
    }

    /**
     * Show the form for editing the specified announcement.
     * Only Normal Admin can edit announcements.
     */
    public function edit(Announcement $announcement)
    {
        // Check authorization using policy
        $this->authorize('update', $announcement);

        return view('admin.announcements.edit', compact('announcement'));
    }

    /**
     * Update the specified announcement.
     * Only Normal Admin can update announcements.
     */
    public function update(StoreAnnouncementRequest $request, Announcement $announcement)
    {
        // Check authorization using policy
        $this->authorize('update', $announcement);

        $data = $request->validated();
        
        // Handle publish action
        $wasPublished = $announcement->status === 'published';
        if ($request->has('publish')) {
            $data['status'] = 'published';
        } elseif ($request->has('unpublish')) {
            $data['status'] = 'draft';
        }

        $announcement->update($data);

        // Create notifications if:
        // 1. send_notification is enabled
        // 2. Status changed to published (was draft before)
        // 3. No notifications already exist for this announcement
        if ($announcement->send_notification && 
            $announcement->status === 'published' && 
            !$wasPublished &&
            !$announcement->notifications()->exists()) {
            $count = $this->notificationService->createFromAnnouncement($announcement);
            $message = $count > 0 
                ? "Announcement updated and {$count} notification(s) sent successfully."
                : "Announcement updated successfully. No notifications sent (no eligible users).";
        } else {
            $message = 'Announcement updated successfully.';
        }

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', $message);
    }

    /**
     * Remove the specified announcement.
     * Only Normal Admin can delete announcements.
     */
    public function destroy(Announcement $announcement)
    {
        // Check authorization using policy
        $this->authorize('delete', $announcement);

        $announcement->delete();

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Announcement deleted successfully.');
    }
}

