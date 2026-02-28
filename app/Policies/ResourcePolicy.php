<?php

namespace App\Policies;

use App\Models\Resource;
use App\Models\User;

class ResourcePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isTechAdmin();
    }

    public function view(User $user, Resource $resource): bool
    {
        return $user->isTechAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isTechAdmin();
    }

    public function update(User $user, Resource $resource): bool
    {
        return $user->isTechAdmin();
    }

    public function delete(User $user, Resource $resource): bool
    {
        return $user->isTechAdmin();
    }

    public function search(User $user): bool
    {
        return $user->isTechAdmin();
    }

    public function manage(User $user): bool
    {
        return $user->isTechAdmin();
    }
}


