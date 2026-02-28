<?php

namespace App\Policies;

use App\Models\SubLesson;
use App\Models\User;

class SubLessonPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isTechAdmin();
    }

    public function view(User $user, SubLesson $subLesson): bool
    {
        return $user->isTechAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isTechAdmin();
    }

    public function update(User $user, SubLesson $subLesson): bool
    {
        return $user->isTechAdmin();
    }

    public function delete(User $user, SubLesson $subLesson): bool
    {
        return $user->isTechAdmin();
    }

    public function reorder(User $user, SubLesson $subLesson): bool
    {
        return $user->isTechAdmin();
    }

    public function manage(User $user): bool
    {
        return $user->isTechAdmin();
    }
}


