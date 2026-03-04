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

        // Allow creating new attempts anytime (controller will handle closing previous active attempts)
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

        // Enforce XP unlock requirement (same as create policy)
        $challenge = $attempt->challenge;
        if (!$challenge || !$challenge->is_active) {
            return false;
        }

        $unit = $challenge->learningUnit;
        if (!$unit) return false;

        $enrollment = RoadmapEnrollment::where('user_id', $user->id)
            ->where('roadmap_id', $unit->roadmap_id)
            ->first();

        if (!$enrollment) return false;

        // Check XP unlock requirement - prevent submission if locked
        if ((int)$enrollment->xp_points < (int)$challenge->min_xp) {
            return false;
        }

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
