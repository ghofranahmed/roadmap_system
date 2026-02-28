<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrolledRoadmapResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Ensure roadmap is loaded
        if (!$this->relationLoaded('roadmap')) {
            $this->load('roadmap');
        }
        
        $roadmap = $this->roadmap;
        if (!$roadmap) {
            return [];
        }
        
        $totalUnits = $roadmap->learningUnits()->count();
        $completedUnits = $this->calculateCompletedUnits($request->user());
        
        return [
            'enrollment_id' => $this->id,
            'roadmap_id' => $roadmap->id,
            'title' => $roadmap->title,
            'description' => $roadmap->description,
            'level' => $roadmap->level,
            'is_active' => $roadmap->is_active,
            'status' => $this->status,
            'xp_points' => $this->xp_points,
            'progress_percent' => $totalUnits > 0 ? round(($completedUnits / $totalUnits) * 100, 2) : 0,
            'completed_units' => $completedUnits,
            'total_units' => $totalUnits,
            'started_at' => $this->started_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
        ];
    }
    
    /**
     * Calculate completed units for the user
     * Optimized to avoid N+1 queries
     */
    private function calculateCompletedUnits($user): int
    {
        if (!$user) {
            return 0;
        }
        
        $roadmap = $this->roadmap;
        if (!$roadmap) {
            return 0;
        }
        
        // Eager load units with their related entities
        $units = $roadmap->learningUnits()
            ->with(['lesson', 'quiz', 'challenge'])
            ->get();
        
        if ($units->isEmpty()) {
            return 0;
        }
        
        // Collect all IDs for batch queries
        $lessonIds = $units->where('unit_type', 'lesson')
            ->pluck('lesson.id')
            ->filter()
            ->values();
        $quizIds = $units->where('unit_type', 'quiz')
            ->pluck('quiz.id')
            ->filter()
            ->values();
        $challengeIds = $units->where('unit_type', 'challenge')
            ->pluck('challenge.id')
            ->filter()
            ->values();
        
        // Batch query completed items
        $completedLessonIds = \App\Models\LessonTracking::where('user_id', $user->id)
            ->whereIn('lesson_id', $lessonIds)
            ->where('is_complete', true)
            ->pluck('lesson_id')
            ->toArray();
        
        $completedQuizIds = \App\Models\QuizAttempt::where('user_id', $user->id)
            ->whereIn('quiz_id', $quizIds)
            ->where('passed', true)
            ->pluck('quiz_id')
            ->toArray();
        
        $completedChallengeIds = \App\Models\ChallengeAttempt::where('user_id', $user->id)
            ->whereIn('challenge_id', $challengeIds)
            ->where('passed', true)
            ->pluck('challenge_id')
            ->toArray();
        
        // Count completed units
        $completed = 0;
        foreach ($units as $unit) {
            $isCompleted = false;
            
            if ($unit->unit_type === 'lesson' && $unit->lesson) {
                $isCompleted = in_array($unit->lesson->id, $completedLessonIds);
            } elseif ($unit->unit_type === 'quiz' && $unit->quiz) {
                $isCompleted = in_array($unit->quiz->id, $completedQuizIds);
            } elseif ($unit->unit_type === 'challenge' && $unit->challenge) {
                $isCompleted = in_array($unit->challenge->id, $completedChallengeIds);
            }
            
            if ($isCompleted) {
                $completed++;
            }
        }
        
        return $completed;
    }
}

