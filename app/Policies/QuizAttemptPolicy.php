<?php

namespace App\Policies;

use App\Models\User;
use App\Models\QuizAttempt;

class QuizAttemptPolicy
{
    /**
     * Determine whether the user can view any attempts (admin maybe).
     */
    public function viewAny(User $user): bool
    {
        return $user->isTechAdmin(); // فقط المسؤول التقني يمكنه رؤية قائمة المحاولات (للكويز)
    }

    /**
     * Determine whether the user can view the attempt.
     */
    public function view(User $user, QuizAttempt $attempt): bool
    {
        // المستخدم يرى محاولاته فقط، أو المسؤول التقني يرى الكل
        return $user->id === $attempt->user_id || $user->isTechAdmin();
    }

    /**
     * Determine whether the user can create an attempt.
     * عادةً يتم السماح لأي مستخدم مصادق عليه، لكن يمكن إضافة شروط إضافية.
     */
    public function create(User $user): bool
    {
        return true; // أي مستخدم مسجل يمكنه بدء محاولة (سيتم التحقق من الاشتراك في middleware آخر)
    }

    /**
     * Determine whether the user can update the attempt (i.e., submit answers).
     * نسمح فقط إذا كانت المحاولة تخصه ولم يتم إنهاؤها بعد (score == 0 && passed == false).
     */
    public function update(User $user, QuizAttempt $attempt): bool
    {
        return $user->id === $attempt->user_id 
            && $attempt->score === 0 
            && $attempt->passed === false;
    }

    /**
     * Determine whether the user can delete the attempt (admin only).
     */
    public function delete(User $user, QuizAttempt $attempt): bool
    {
        return $user->isTechAdmin();
    }
}