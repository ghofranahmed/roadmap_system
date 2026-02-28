<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function authorizeAccess(): void
    {
        // Check if user can create users
        abort_unless(
            Gate::allows('create', User::class),
            403,
            'You are not authorized to create users.'
        );
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $currentUser = auth()->user();

        // Ensure role is set (default to admin for "Add Admin" feature)
        if (!isset($data['role'])) {
            $data['role'] = 'admin';
        }

        // STRICT RULE: Each admin type can ONLY create their own type
        // Normal admin can only create admin
        if ($currentUser?->isNormalAdmin()) {
            // Force admin role - Regular Admin can ONLY create Regular Admin
            if ($data['role'] === 'tech_admin') {
                throw ValidationException::withMessages([
                    'role' => ['Normal admins cannot create technical admins. You can only create Normal Admin users.'],
                ]);
            }
            // If role is not set or is user, allow it (for user creation)
            // But if trying to create admin, ensure it's admin
            if ($data['role'] === 'admin') {
                // This is allowed - Regular Admin creating Regular Admin
            }
        }

        // Technical admin should use CreateAdminPage for creating tech admins
        // This resource is for user management only
        if ($currentUser?->isTechAdmin()) {
            // Tech Admin should not create admins via UserResource
            // They should use CreateAdminPage
            if ($data['role'] === 'admin' || $data['role'] === 'tech_admin') {
                throw ValidationException::withMessages([
                    'role' => ['Technical admins should use the Create Admin page to create admin accounts.'],
                ]);
            }
        }

        // Server-side authorization: Check if user can assign the requested role
        if ($data['role'] === 'admin' && !Gate::allows('assignRole', [User::class, $data['role']])) {
            throw ValidationException::withMessages([
                'role' => ['You are not authorized to assign this role.'],
            ]);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

