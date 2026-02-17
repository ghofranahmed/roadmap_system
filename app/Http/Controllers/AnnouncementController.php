<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * List all active announcements (board view for users).
     * GET /announcements
     */
    public function index(Request $request): JsonResponse
    {
        $announcements = Announcement::active()
            ->select(['id', 'title', 'description', 'type', 'link', 'starts_at', 'ends_at', 'created_at'])
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 15));

        return $this->paginatedResponse($announcements, 'Announcements retrieved successfully');
    }

    /**
     * List active technical announcements.
     * GET /announcements/technical
     */
    public function technical(Request $request): JsonResponse
    {
        $announcements = Announcement::active()
            ->ofType('technical')
            ->select(['id', 'title', 'description', 'type', 'link', 'starts_at', 'ends_at', 'created_at'])
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 15));

        return $this->paginatedResponse($announcements, 'Technical announcements retrieved successfully');
    }

    /**
     * List active opportunity announcements.
     * GET /announcements/opportunities
     */
    public function opportunities(Request $request): JsonResponse
    {
        $announcements = Announcement::active()
            ->ofType('opportunity')
            ->select(['id', 'title', 'description', 'type', 'link', 'starts_at', 'ends_at', 'created_at'])
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 15));

        return $this->paginatedResponse($announcements, 'Opportunity announcements retrieved successfully');
    }
}
