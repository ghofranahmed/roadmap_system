<?php

namespace App\Services;

use App\Models\RoadmapEnrollment;
use App\Models\LearningUnit;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Challenge;
use App\Models\LessonTracking;
use App\Models\QuizAttempt;
use App\Models\ChallengeAttempt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoadmapResetService
{
    /**
     * Reset all progress for a user's roadmap enrollment.
     * 
     * @param int $userId
     * @param int $roadmapId
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     * @throws \Exception
     */
    public function resetRoadmapProgress(int $userId, int $roadmapId): array
    {
        // Verify enrollment exists
        $enrollment = RoadmapEnrollment::where('user_id', $userId)
            ->where('roadmap_id', $roadmapId)
            ->first();

        if (!$enrollment) {
            return [
                'success' => false,
                'message' => 'Enrollment not found',
                'data' => null
            ];
        }

        try {
            DB::transaction(function () use ($userId, $roadmapId, $enrollment) {
                // 1) Reset enrollment
                $this->resetEnrollment($enrollment);

                // 2) Get all learning units for this roadmap
                $unitIds = LearningUnit::where('roadmap_id', $roadmapId)
                    ->pluck('id')
                    ->toArray();

                if (empty($unitIds)) {
                    return; // No units to reset
                }

                // 3) Reset lesson progress
                $this->resetLessonProgress($userId, $unitIds);

                // 4) Reset quiz attempts
                $this->resetQuizAttempts($userId, $unitIds);

                // 5) Reset challenge attempts
                $this->resetChallengeAttempts($userId, $unitIds);
            });

            return [
                'success' => true,
                'message' => 'Roadmap progress reset successfully',
                'data' => [
                    'enrollment_id' => $enrollment->id,
                    'roadmap_id' => $roadmapId,
                    'reset_at' => now()->toISOString()
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Failed to reset roadmap progress', [
                'user_id' => $userId,
                'roadmap_id' => $roadmapId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to reset roadmap progress: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Reset enrollment fields.
     */
    private function resetEnrollment(RoadmapEnrollment $enrollment): void
    {
        $enrollment->update([
            'started_at' => now(),
            'completed_at' => null,
            'xp_points' => 0,
            'status' => 'active',
        ]);
    }

    /**
     * Reset lesson progress for all lessons in the roadmap.
     */
    private function resetLessonProgress(int $userId, array $unitIds): void
    {
        // Get all lesson-type units from the provided unit IDs
        $lessonUnitIds = LearningUnit::whereIn('id', $unitIds)
            ->where('unit_type', 'lesson')
            ->pluck('id')
            ->toArray();

        if (empty($lessonUnitIds)) {
            return; // No lesson units
        }

        // Get all lesson IDs for these units
        $lessonIds = Lesson::whereIn('learning_unit_id', $lessonUnitIds)
            ->pluck('id')
            ->toArray();

        if (empty($lessonIds)) {
            return; // No lessons
        }

        // Reset all lesson trackings for this user and these lessons
        LessonTracking::where('user_id', $userId)
            ->whereIn('lesson_id', $lessonIds)
            ->update([
                'is_complete' => false,
                'last_updated_at' => null,
            ]);
    }

    /**
     * Delete all quiz attempts for quizzes in the roadmap.
     */
    private function resetQuizAttempts(int $userId, array $unitIds): void
    {
        // Get all quiz-type units from the provided unit IDs
        $quizUnitIds = LearningUnit::whereIn('id', $unitIds)
            ->where('unit_type', 'quiz')
            ->pluck('id')
            ->toArray();

        if (empty($quizUnitIds)) {
            return; // No quiz units
        }

        // Get all quiz IDs for these units
        $quizIds = Quiz::whereIn('learning_unit_id', $quizUnitIds)
            ->pluck('id')
            ->toArray();

        if (empty($quizIds)) {
            return; // No quizzes
        }

        // Delete all quiz attempts for this user and these quizzes
        QuizAttempt::where('user_id', $userId)
            ->whereIn('quiz_id', $quizIds)
            ->delete();
    }

    /**
     * Delete all challenge attempts for challenges in the roadmap.
     */
    private function resetChallengeAttempts(int $userId, array $unitIds): void
    {
        // Get all challenge-type units from the provided unit IDs
        $challengeUnitIds = LearningUnit::whereIn('id', $unitIds)
            ->where('unit_type', 'challenge')
            ->pluck('id')
            ->toArray();

        if (empty($challengeUnitIds)) {
            return; // No challenge units
        }

        // Get all challenge IDs for these units
        $challengeIds = Challenge::whereIn('learning_unit_id', $challengeUnitIds)
            ->pluck('id')
            ->toArray();

        if (empty($challengeIds)) {
            return; // No challenges
        }

        // Delete all challenge attempts for this user and these challenges
        ChallengeAttempt::where('user_id', $userId)
            ->whereIn('challenge_id', $challengeIds)
            ->delete();
    }
}

