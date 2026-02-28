<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;

class NotificationPolicy
{
    /**
     * Only normal admin (role:admin) can view all notifications (admin panel).
     */
    public function viewAny(User $user): bool
    {
        return $user->isNormalAdmin();
    }

    /**
     * Only normal admin (role:admin) can view a specific notification.
     */
    public function view(User $user, Notification $notification): bool
    {
        return $user->isNormalAdmin();
    }

    /**
     * Only normal admin (role:admin) can create notifications.
     */
    public function create(User $user): bool
    {
        return $user->isNormalAdmin();
    }

    /**
     * Only normal admin (role:admin) can delete notifications.
     */
    public function delete(User $user, Notification $notification): bool
    {
        return $user->isNormalAdmin();
    }
}

