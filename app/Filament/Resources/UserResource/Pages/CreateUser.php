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

        // Server-side authorization: Check if user can assign the requested role
        if (!Gate::allows('assignRole', [User::class, $data['role']])) {
            throw ValidationException::withMessages([
                'role' => [
                    match ($currentUser?->role) {
                        'admin' => 'You can only create Normal Admin users.',
                        'tech_admin' => 'Invalid role assignment.',
                        default => 'You are not authorized to assign this role.',
                    }
                ],
            ]);
        }

        // Additional validation: Normal admin can only create normal admins
        if ($currentUser?->isNormalAdmin()) {
            if ($data['role'] === 'tech_admin') {
                throw ValidationException::withMessages([
                    'role' => ['Normal admins cannot create technical admins.'],
                ]);
            }
            // Force admin role for normal admin
            $data['role'] = 'admin';
        }

        // Technical admin validation: Can create both admin and tech_admin
        if ($currentUser?->isTechAdmin()) {
            if (!in_array($data['role'], ['admin', 'tech_admin'])) {
                // For "Add Admin" feature, we focus on admin roles
                // But tech_admin can also create regular users if needed
                // This validation ensures they can create admin roles
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

