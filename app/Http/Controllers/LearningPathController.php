<?php

namespace App\Http\Controllers;

use App\Http\Resources\LearningPathUnitResource;
use App\Models\LearningUnit;
use App\Models\Roadmap;
use Illuminate\Http\Request;

class LearningPathController extends Controller
{
    /**
     * GET /api/v1/roadmaps/{roadmapId}/learning-path
     * Get learning path for a roadmap with ordered units and entity summaries
     */
    public function show(Request $request, $roadmapId)
    {
        $user = $request->user();
        
        // Verify roadmap exists
        $roadmap = Roadmap::findOrFail($roadmapId);
        
        // Check enrollment (middleware should handle this, but double-check for public roadmaps)
        $enrollment = null;
        if ($user) {
            $enrollment = \App\Models\RoadmapEnrollment::where('user_id', $user->id)
                ->where('roadmap_id', $roadmapId)
                ->first();
            
            // If not enrolled and roadmap is not public, return 403
            // Note: Currently no 'is_public' field, so we require enrollment
            if (!$enrollment) {
                return $this->errorResponse(
                    'You must be enrolled in this roadmap to view the learning path.',
                    null,
                    403
                );
            }
        } else {
            return $this->errorResponse('Unauthenticated.', null, 401);
        }
        
        // Get learning units ordered by position
        $units = LearningUnit::where('roadmap_id', $roadmapId)
            ->where('is_active', true)
            ->orderBy('position')
            ->with([
                'lesson:id,learning_unit_id,title,description,position,is_active',
                'quiz:id,learning_unit_id,title,is_active,min_xp,max_xp',
                'challenge:id,learning_unit_id,title,description,min_xp,is_active',
            ])
            ->get();
        
        return $this->successResponse([
            'roadmap' => [
                'id' => $roadmap->id,
                'title' => $roadmap->title,
                'description' => $roadmap->description,
                'level' => $roadmap->level,
            ],
            'units' => LearningPathUnitResource::collection($units),
        ], 'Learning path retrieved successfully');
    }
}

