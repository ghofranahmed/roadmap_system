<?php

namespace App\Policies;

use App\Models\QuizQuestion;
use App\Models\User;

class QuizQuestionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isTechAdmin();
    }

    public function view(User $user, QuizQuestion $question): bool
    {
        return $user->isTechAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isTechAdmin();
    }

    public function update(User $user, QuizQuestion $question): bool
    {
        return $user->isTechAdmin();
    }

    public function delete(User $user, QuizQuestion $question): bool
    {
        return $user->isTechAdmin();
    }

    public function manage(User $user): bool
    {
        return $user->isTechAdmin();
    }
}


