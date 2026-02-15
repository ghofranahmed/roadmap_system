<?php

namespace App\Policies;

use App\Models\Challenge;
use App\Models\User;
use App\Models\LearningUnit;
use App\Models\RoadmapEnrollment;

class ChallengePolicy
{
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

    public function manage(User $user): bool
    {
        return (bool) $user->is_admin;
    }
}
