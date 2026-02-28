<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class LearningPathUnitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $entity = $this->getEntityData();
        $locked = $this->isLocked($user);
        $completed = $this->isCompleted($user);
        
        return [
            'id' => $this->id,
            'position' => $this->position,
            'unit_type' => $this->unit_type,
            'label' => $this->getLabel(),
            'entity' => $entity,
            'is_locked' => $locked,
            'is_completed' => $completed,
        ];
    }
    
    /**
     * Get entity data based on unit_type
     */
    private function getEntityData(): ?array
    {
        if ($this->unit_type === 'lesson' && $this->relationLoaded('lesson') && $this->lesson) {
            return [
                'id' => $this->lesson->id,
                'type' => 'lesson',
                'title' => $this->lesson->title,
            ];
        }
        
        if ($this->unit_type === 'quiz' && $this->relationLoaded('quiz') && $this->quiz) {
            return [
                'id' => $this->quiz->id,
                'type' => 'quiz',
                'title' => $this->quiz->title ?? $this->title, // Fallback to unit title if quiz title is null
            ];
        }
        
        if ($this->unit_type === 'challenge' && $this->relationLoaded('challenge') && $this->challenge) {
            return [
                'id' => $this->challenge->id,
                'type' => 'challenge',
                'title' => $this->challenge->title,
            ];
        }
        
        return null;
    }
    
    /**
     * Get label for display
     */
    private function getLabel(): string
    {
        $entity = $this->getEntityData();
        return $entity['title'] ?? $this->title ?? 'Untitled';
    }
    
    /**
     * Check if unit is locked for user
     */
    private function isLocked($user): bool
    {
        if (!$user) {
            return true;
        }
        
        // Lessons: locked if previous lesson units are not completed
        if ($this->unit_type === 'lesson') {
            $previousLessonUnits = \App\Models\LearningUnit::where('roadmap_id', $this->roadmap_id)
                ->where('unit_type', 'lesson')
                ->where('position', '<', $this->position)
                ->orderBy('position')
                ->get();
            
            foreach ($previousLessonUnits as $prevUnit) {
                if ($prevUnit->lesson) {
                    $completed = \App\Models\LessonTracking::where('user_id', $user->id)
                        ->where('lesson_id', $prevUnit->lesson->id)
                        ->where('is_complete', true)
                        ->exists();
                    
                    if (!$completed) {
                        return true;
                    }
                }
            }
            
            return false;
        }
        
        // Quiz: locked if previous lesson units are not completed (using QuizPolicy logic)
        if ($this->unit_type === 'quiz') {
            $quiz = $this->quiz;
            if (!$quiz) {
                return true;
            }
            
            // Use QuizPolicy logic: all previous lesson units must be completed
            $prevLessonIds = \Illuminate\Support\Facades\DB::table('learning_units as lu')
                ->join('lessons as l', 'l.learning_unit_id', '=', 'lu.id')
                ->where('lu.roadmap_id', $this->roadmap_id)
                ->where('lu.is_active', 1)
                ->where('lu.unit_type', 'lesson')
                ->where('lu.position', '<', $this->position)
                ->pluck('l.id');
            
            if ($prevLessonIds->isEmpty()) {
                return false;
            }
            
            $completedCount = \App\Models\LessonTracking::where('user_id', $user->id)
                ->whereIn('lesson_id', $prevLessonIds)
                ->where('is_complete', 1)
                ->count();
            
            return $completedCount !== $prevLessonIds->count();
        }
        
        // Challenge: locked if user doesn't have enough XP (using ChallengePolicy logic)
        if ($this->unit_type === 'challenge') {
            $challenge = $this->challenge;
            if (!$challenge) {
                return true;
            }
            
            $enrollment = \App\Models\RoadmapEnrollment::where('user_id', $user->id)
                ->where('roadmap_id', $this->roadmap_id)
                ->first();
            
            if (!$enrollment) {
                return true;
            }
            
            return (int)$enrollment->xp_points < (int)$challenge->min_xp;
        }
        
        return false;
    }
    
    /**
     * Check if unit is completed for user
     */
    private function isCompleted($user): bool
    {
        if (!$user) {
            return false;
        }
        
        if ($this->unit_type === 'lesson' && $this->lesson) {
            return \App\Models\LessonTracking::where('user_id', $user->id)
                ->where('lesson_id', $this->lesson->id)
                ->where('is_complete', true)
                ->exists();
        }
        
        if ($this->unit_type === 'quiz' && $this->quiz) {
            return \App\Models\QuizAttempt::where('user_id', $user->id)
                ->where('quiz_id', $this->quiz->id)
                ->where('passed', true)
                ->exists();
        }
        
        if ($this->unit_type === 'challenge' && $this->challenge) {
            return \App\Models\ChallengeAttempt::where('user_id', $user->id)
                ->where('challenge_id', $this->challenge->id)
                ->where('passed', true)
                ->exists();
        }
        
        return false;
    }
}

