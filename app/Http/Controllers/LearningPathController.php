<?php

namespace App\Http\Controllers;

use App\Http\Resources\LearningPathUnitResource;
use App\Models\ChallengeAttempt;
use App\Models\LearningUnit;
use App\Models\LessonTracking;
use App\Models\QuizAttempt;
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

        // Batch collect entity IDs per unit type
        $lessonIds = $units
            ->where('unit_type', 'lesson')
            ->pluck('lesson.id')
            ->filter()
            ->values();

        $quizIds = $units
            ->where('unit_type', 'quiz')
            ->pluck('quiz.id')
            ->filter()
            ->values();

        $challengeIds = $units
            ->where('unit_type', 'challenge')
            ->pluck('challenge.id')
            ->filter()
            ->values();

        // Batch fetch lesson tracking (single query)
        $lessonTrackingByLessonId = LessonTracking::where('user_id', $user->id)
            ->whereIn('lesson_id', $lessonIds)
            ->get(['lesson_id', 'is_complete', 'last_updated_at'])
            ->keyBy('lesson_id');

        // Batch fetch quiz attempts (single query), then compute latest + completed maps in memory
        $quizAttempts = QuizAttempt::where('user_id', $user->id)
            ->whereIn('quiz_id', $quizIds)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get(['id', 'quiz_id', 'score', 'passed', 'created_at', 'updated_at']);

        $latestQuizAttemptByQuizId = $quizAttempts
            ->groupBy('quiz_id')
            ->map(fn ($attempts) => $attempts->first());

        $quizCompletedByQuizId = $quizAttempts
            ->groupBy('quiz_id')
            ->map(fn ($attempts) => $attempts->contains(fn ($attempt) => (bool) $attempt->passed));

        // Batch fetch challenge attempts (single query), then compute latest + completed maps in memory
        $challengeAttempts = ChallengeAttempt::where('user_id', $user->id)
            ->whereIn('challenge_id', $challengeIds)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get(['id', 'challenge_id', 'passed', 'created_at', 'updated_at']);

        $latestChallengeAttemptByChallengeId = $challengeAttempts
            ->groupBy('challenge_id')
            ->map(fn ($attempts) => $attempts->first());

        $challengeCompletedByChallengeId = $challengeAttempts
            ->groupBy('challenge_id')
            ->map(fn ($attempts) => $attempts->contains(fn ($attempt) => (bool) $attempt->passed));

        // Compute lock + completion maps in one ordered pass (no per-unit queries)
        $unitLockMap = [];
        $unitCompletionMap = [];
        $allPreviousLessonsCompleted = true;
        $enrollmentXp = (int) ($enrollment->xp_points ?? 0);

        foreach ($units as $unit) {
            $isLocked = false;
            $isCompleted = false;

            if ($unit->unit_type === 'lesson') {
                $lessonId = $unit->lesson?->id;
                $tracking = $lessonId ? $lessonTrackingByLessonId->get($lessonId) : null;
                $isCompleted = (bool) ($tracking?->is_complete ?? false);
                $isLocked = !$allPreviousLessonsCompleted;

                if (!$isCompleted) {
                    $allPreviousLessonsCompleted = false;
                }
            } elseif ($unit->unit_type === 'quiz') {
                $quizId = $unit->quiz?->id;
                $isCompleted = $quizId ? (bool) ($quizCompletedByQuizId->get($quizId) ?? false) : false;
                $isLocked = !$allPreviousLessonsCompleted;
            } elseif ($unit->unit_type === 'challenge') {
                $challengeId = $unit->challenge?->id;
                $isCompleted = $challengeId ? (bool) ($challengeCompletedByChallengeId->get($challengeId) ?? false) : false;
                $requiredXp = (int) ($unit->challenge?->min_xp ?? PHP_INT_MAX);
                $isLocked = $enrollmentXp < $requiredXp;
            }

            $unitLockMap[$unit->id] = $isLocked;
            $unitCompletionMap[$unit->id] = $isCompleted;
        }

        // Share precomputed maps with resource to eliminate N+1 lookups
        $request->attributes->set('lesson_tracking_by_lesson_id', $lessonTrackingByLessonId);
        $request->attributes->set('latest_quiz_attempt_by_quiz_id', $latestQuizAttemptByQuizId);
        $request->attributes->set('latest_challenge_attempt_by_challenge_id', $latestChallengeAttemptByChallengeId);
        $request->attributes->set('unit_lock_map', $unitLockMap);
        $request->attributes->set('unit_completion_map', $unitCompletionMap);
        
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

