<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;

class LessonPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isTechAdmin();
    }

    public function view(User $user, Lesson $lesson): bool
    {
        return $user->isTechAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isTechAdmin();
    }

    public function update(User $user, Lesson $lesson): bool
    {
        return $user->isTechAdmin();
    }

    public function delete(User $user, Lesson $lesson): bool
    {
        return $user->isTechAdmin();
    }

    public function reorder(User $user, Lesson $lesson): bool
    {
        return $user->isTechAdmin();
    }

    public function toggleActive(User $user, Lesson $lesson): bool
    {
        return $user->isTechAdmin();
    }

    public function manage(User $user): bool
    {
        return $user->isTechAdmin();
    }
}


