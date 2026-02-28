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

        // If role is being changed, prevent privilege escalation
        if (isset($data['role']) && $data['role'] !== $this->record->role) {
            // Use the changeRole policy method to prevent escalation
            if (!Gate::allows('changeRole', [$this->record, $data['role']])) {
                throw ValidationException::withMessages([
                    'role' => [
                        match ($currentUser?->role) {
                            'admin' => 'You can only change roles between User and Normal Admin. You cannot assign Technical Admin role.',
                            'tech_admin' => 'You cannot change user roles through this interface.',
                            default => 'You are not authorized to change this role.',
                        }
                    ],
                ]);
            }

            // STRICT RULE: Prevent escalating to tech_admin
            if ($data['role'] === 'tech_admin') {
                throw ValidationException::withMessages([
                    'role' => ['You cannot assign Technical Admin role. Role escalation is not allowed.'],
                ]);
            }

            // Prevent changing your own role
            if ($this->record->id === $currentUser->id) {
                throw ValidationException::withMessages([
                    'role' => ['You cannot change your own role.'],
                ]);
            }
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

