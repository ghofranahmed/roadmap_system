<?php

namespace App\Policies;

use App\Models\Challenge;
use App\Models\User;
use App\Models\LearningUnit;
use App\Models\RoadmapEnrollment;

class ChallengePolicy
{
    /**
     * Student-facing: can the user view this challenge?
     */
    public function view(User $user, Challenge $challenge): bool
    {
        $unit = LearningUnit::find($challenge->learning_unit_id);
        if (!$unit || !$unit->is_active || $unit->unit_type !== 'challenge') return false;
        if (!$challenge->is_active) return false;

        // Must be enrolled
        $enrollment = RoadmapEnrollment::where('user_id', $user->id)
            ->where('roadmap_id', $unit->roadmap_id)
            ->first();
        if (!$enrollment) return false;

        // Challenge unlock rule: total quiz points in this roadmap
        return (int)$enrollment->xp_points >= (int)$challenge->min_xp;
    }

    public function attempt(User $user, Challenge $challenge): bool
    {
        return $this->view($user, $challenge);
    }

    /**
     * Admin-facing: can the user manage challenge content in general?
     */
    public function manage(User $user): bool
    {
        return $user->isTechAdmin();
    }

    /**
     * Admin-facing: standard CRUD abilities for content management.
     * Only technical admins can manage challenge content.
     */
    public function viewAny(User $user): bool
    {
        return $user->isTechAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isTechAdmin();
    }

    public function update(User $user, Challenge $challenge): bool
    {
        return $user->isTechAdmin();
    }

    public function delete(User $user, Challenge $challenge): bool
    {
        return $user->isTechAdmin();
    }
}
