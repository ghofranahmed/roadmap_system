<?php

namespace App\Policies;

use App\Models\Roadmap;
use App\Models\User;

class RoadmapPolicy
{
    /**
     * Only technical admins can manage roadmaps.
     */
    public function viewAny(User $user): bool
    {
        return $user->isTechAdmin();
    }

    public function view(User $user, Roadmap $roadmap): bool
    {
        return $user->isTechAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isTechAdmin();
    }

    public function update(User $user, Roadmap $roadmap): bool
    {
        return $user->isTechAdmin();
    }

    public function delete(User $user, Roadmap $roadmap): bool
    {
        return $user->isTechAdmin();
    }

    public function toggleActive(User $user, Roadmap $roadmap): bool
    {
        return $user->isTechAdmin();
    }

    /**
     * Generic management ability (for API controllers if needed).
     */
    public function manage(User $user): bool
    {
        return $user->isTechAdmin();
    }
}


