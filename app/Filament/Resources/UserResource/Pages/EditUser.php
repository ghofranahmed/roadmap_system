<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function authorizeAccess(): void
    {
        // Check if user can update this user
        abort_unless(
            Gate::allows('update', $this->record),
            403,
            'You are not authorized to update this user.'
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->authorize(fn () => Gate::allows('delete', $this->record))
                ->before(function () {
                    if ($this->record->id === auth()->id()) {
                        throw new \Exception('You cannot delete your own account.');
                    }
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $currentUser = auth()->user();

        // If role is being changed, check authorization
        if (isset($data['role']) && $data['role'] !== $this->record->role) {
            // Server-side authorization: Check if user can assign the requested role
            if (!Gate::allows('assignRole', [User::class, $data['role']])) {
                throw ValidationException::withMessages([
                    'role' => [
                        match ($currentUser?->role) {
                            'admin' => 'You can only assign User or Normal Admin roles.',
                            'tech_admin' => 'Invalid role assignment.',
                            default => 'You are not authorized to assign this role.',
                        }
                    ],
                ]);
            }

            // Additional validation: Normal admin cannot assign tech_admin role
            if ($currentUser?->isNormalAdmin() && $data['role'] === 'tech_admin') {
                throw ValidationException::withMessages([
                    'role' => ['Normal admins cannot assign technical admin role.'],
                ]);
            }

            // Prevent downgrading tech_admin to lower role (optional business rule)
            // Uncomment if you want to prevent tech_admin from being downgraded
            // if ($this->record->role === 'tech_admin' && $data['role'] !== 'tech_admin') {
            //     throw ValidationException::withMessages([
            //         'role' => ['Cannot downgrade technical admin role.'],
            //     ]);
            // }
        }

        // Remove password from data if empty
        if (empty($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

