<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Challenge;
use App\Models\ChallengeAttempt;
use App\Models\RoadmapEnrollment;

class ChallengeAttemptPolicy
{
    /**
     * هل يمكن بدء محاولة؟
     */
    public function create(User $user, Challenge $challenge): bool
    {
        if (!$challenge->is_active) {
            return false;
        }

        $unit = $challenge->learningUnit;
        if (!$unit) return false;

        $enrollment = RoadmapEnrollment::where('user_id', $user->id)
            ->where('roadmap_id', $unit->roadmap_id)
            ->first();

        if (!$enrollment) return false;

        // Check XP unlock requirement
        if ((int)$enrollment->xp_points < (int)$challenge->min_xp) {
            return false;
        }

        // Prevent multiple active attempts (attempts without execution_output are considered active)
        $activeAttempt = \App\Models\ChallengeAttempt::where('challenge_id', $challenge->id)
            ->where('user_id', $user->id)
            ->whereNull('execution_output')
            ->exists();

        if ($activeAttempt) {
            return false;
        }

        return true;
    }

    /**
     * عرض المحاولة
     */
    public function view(User $user, ChallengeAttempt $attempt): bool
    {
        return $user->isTechAdmin() || $user->id === $attempt->user_id;
    }

    /**
     * إرسال الكود (مرة واحدة فقط)
     */
    public function update(User $user, ChallengeAttempt $attempt): bool
    {
        if ($user->id !== $attempt->user_id) return false;

        // Prevent resubmitting after already submitted
        if (!is_null($attempt->execution_output)) return false;

        // Prevent resubmitting after passing
        if ($attempt->passed) return false;

        return true;
    }

    /**
     * حذف المحاولة (Admin فقط)
     */
    public function delete(User $user, ChallengeAttempt $attempt): bool
    {
        return $user->isTechAdmin();
    }
}
