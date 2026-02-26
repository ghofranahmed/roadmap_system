<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy
{
    /**
     * Only normal admin (role:admin) can create announcements.
     * Matches route middleware: role:admin
     */
    public function create(User $user): bool
    {
        return $user->isNormalAdmin();
    }

    /**
     * Only normal admin (role:admin) can update announcements.
     * Matches route middleware: role:admin
     */
    public function update(User $user, Announcement $announcement): bool
    {
        return $user->isNormalAdmin();
    }

    /**
     * Only normal admin (role:admin) can delete announcements.
     * Matches route middleware: role:admin
     */
    public function delete(User $user, Announcement $announcement): bool
    {
        return $user->isNormalAdmin();
    }

    /**
     * Only normal admin (role:admin) can view all announcements (admin panel).
     * Matches route middleware: role:admin
     */
    public function viewAny(User $user): bool
    {
        return $user->isNormalAdmin();
    }
}

