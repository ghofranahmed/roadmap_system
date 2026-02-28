<?php

namespace App\Policies;

use App\Models\LearningUnit;
use App\Models\User;

class LearningUnitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isTechAdmin();
    }

    public function view(User $user, LearningUnit $unit): bool
    {
        return $user->isTechAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isTechAdmin();
    }

    public function update(User $user, LearningUnit $unit): bool
    {
        return $user->isTechAdmin();
    }

    public function delete(User $user, LearningUnit $unit): bool
    {
        return $user->isTechAdmin();
    }

    public function reorder(User $user, LearningUnit $unit): bool
    {
        return $user->isTechAdmin();
    }

    public function toggleActive(User $user, LearningUnit $unit): bool
    {
        return $user->isTechAdmin();
    }

    public function manage(User $user): bool
    {
        return $user->isTechAdmin();
    }
}


