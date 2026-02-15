<?php

namespace App\Http\Controllers;

use App\Models\ChatRoom;
use App\Models\RoadmapEnrollment;
use Illuminate\Http\Request;

class CommunityController extends Controller
{
    public function myCommunityRooms(Request $request)
    {
        $user = $request->user();

        $roadmapIds = RoadmapEnrollment::where('user_id', $user->id)->pluck('roadmap_id');

        $rooms = ChatRoom::whereIn('roadmap_id', $roadmapIds)
            ->where('is_active', true)
            ->with('roadmap:id,title')
            ->orderByDesc('created_at')
            ->paginate(request()->get('per_page', 15));

        return $this->paginatedResponse($rooms, 'Community rooms retrieved successfully');
    }
}
