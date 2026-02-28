<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine if the user can view any users.
     * Only normal admin can view users (tech_admin uses dedicated CreateAdminPage).
     */
    public function viewAny(User $user): bool
    {
        return $user->isNormalAdmin();
    }

    /**
     * Determine if the user can view the user.
     * Only normal admin can view individual users.
     */
    public function view(User $user, User $model): bool
    {
        return $user->isNormalAdmin();
    }

    /**
     * Determine if the user can create users.
     * Only normal admin can create users via UserResource (tech_admin uses dedicated CreateAdminPage).
     */
    public function create(User $user): bool
    {
        return $user->isNormalAdmin();
    }

    /**
     * Determine if the user can update the user.
     * Only normal admin can update users.
     * Role changes are restricted via changeRole() method.
     */
    public function update(User $user, User $model): bool
    {
        if (!$user->isNormalAdmin()) {
            return false;
        }

        // Users can update themselves, but role changes are restricted via changeRole()
        return true;
    }

    /**
     * Determine if the user can delete the user.
     * Only normal admin can delete users.
     */
    public function delete(User $user, User $model): bool
    {
        if (!$user->isNormalAdmin()) {
            return false;
        }

        // Prevent deleting yourself
        return $user->id !== $model->id;
    }

    /**
     * Determine if the user can assign a specific role.
     * Used by CreateAdminPage for creating admin users.
     * STRICT RULE: Each admin type can ONLY create their own type.
     * Normal admin can only assign: admin
     * Technical admin can only assign: tech_admin
     * Note: 'user' role is NOT allowed from CreateAdminPage (this is for creating admins only).
     */
    public function assignRole(User $user, string $role): bool
    {
        // Normal admin can only assign admin role
        if ($user->isNormalAdmin()) {
            return $role === 'admin';
        }

        // Technical admin can ONLY assign tech_admin role (strict separation)
        if ($user->isTechAdmin()) {
            return $role === 'tech_admin';
        }

        return false;
    }

    /**
     * Determine if the user can change a role.
     * Prevents privilege escalation through role mutation.
     */
    public function changeRole(User $user, User $targetUser, string $newRole): bool
    {
        // Prevent self-role change
        if ($user->id === $targetUser->id) {
            return false;
        }

        // Only normal admins can update users (but not change roles to higher privileges)
        if (!$user->isNormalAdmin()) {
            return false;
        }

        // Prevent escalating to tech_admin
        if ($newRole === 'tech_admin') {
            return false;
        }

        // Normal admin can change roles within their scope (user <-> admin)
        return in_array($newRole, ['user', 'admin']);
    }
}

