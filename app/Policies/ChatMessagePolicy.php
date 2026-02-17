<?php

namespace App\Policies;

use App\Models\ChatMessage;
use App\Models\User;

class ChatMessagePolicy
{
    /**
     * Can the user update this message?
     * - Owner can edit own message
     * - Admin / tech_admin can edit any message
     */
    public function update(User $user, ChatMessage $message): bool
    {
        if ($user->id === $message->user_id) {
            return true;
        }

        return in_array($user->role, ['admin', 'tech_admin']);
    }

    /**
     * Can the user delete this message?
     * - Owner can delete own message
     * - Admin / tech_admin can delete any message
     */
    public function delete(User $user, ChatMessage $message): bool
    {
        if ($user->id === $message->user_id) {
            return true;
        }

        return in_array($user->role, ['admin', 'tech_admin']);
    }
}

