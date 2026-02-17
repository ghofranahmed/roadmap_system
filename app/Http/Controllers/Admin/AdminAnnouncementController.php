<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnnouncementRequest;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     * Create a new announcement (simple board post).
     * POST /admin/announcements
     */
    public function store(StoreAnnouncementRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        $announcement = Announcement::create($data);

        return $this->successResponse(
            $announcement->load('creator:id,username,email'),
            'Announcement created successfully',
            201
        );
    }

    /**
     * Update an existing announcement.
     * PUT /admin/announcements/{id}
     */
    public function update(StoreAnnouncementRequest $request, int $id): JsonResponse
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->update($request->validated());

        return $this->successResponse(
            $announcement->load('creator:id,username,email'),
            'Announcement updated successfully'
        );
    }

    /**
     * Delete an announcement.
     * DELETE /admin/announcements/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->delete();

        return $this->successResponse(null, 'Announcement deleted successfully');
    }
}
