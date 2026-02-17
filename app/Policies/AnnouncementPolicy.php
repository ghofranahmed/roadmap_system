<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy
{
    /**
     * Only admin or tech_admin can create announcements.
     */
    public function create(User $user): bool
    {
        return $user->isAnyAdmin();
    }

    /**
     * Only admin or tech_admin can update announcements.
     */
    public function update(User $user, Announcement $announcement): bool
    {
        return $user->isAnyAdmin();
    }

    /**
     * Only admin or tech_admin can delete announcements.
     */
    public function delete(User $user, Announcement $announcement): bool
    {
        return $user->isAnyAdmin();
    }

    /**
     * Only admin or tech_admin can view all announcements (admin panel).
     */
    public function viewAny(User $user): bool
    {
        return $user->isAnyAdmin();
    }
}

