<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * List published announcements relevant to the authenticated user.
     * GET /announcements
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $announcements = Announcement::published()
            ->relevantTo($user)
            ->select(['id', 'title', 'content', 'type', 'target_type', 'publish_at', 'created_at'])
            ->orderByDesc('publish_at')
            ->paginate($request->get('per_page', 15));

        return $this->paginatedResponse($announcements, 'Announcements retrieved successfully');
    }

    /**
     * List published technical announcements for the authenticated user.
     * GET /announcements/technical
     */
    public function technical(Request $request): JsonResponse
    {
        $user = $request->user();

        $announcements = Announcement::published()
            ->ofType('technical')
            ->relevantTo($user)
            ->select(['id', 'title', 'content', 'type', 'target_type', 'publish_at', 'created_at'])
            ->orderByDesc('publish_at')
            ->paginate($request->get('per_page', 15));

        return $this->paginatedResponse($announcements, 'Technical announcements retrieved successfully');
    }
}

