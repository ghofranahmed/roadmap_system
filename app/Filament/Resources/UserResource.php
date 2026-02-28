<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $modelLabel = 'User';

    protected static ?string $pluralModelLabel = 'Users';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        // Defense in depth: Explicitly restrict to normal admin only
        // tech_admin should use CreateAdminPage instead
        return $user?->isNormalAdmin() ?? false;
    }

    public static function form(Form $form): Form
    {
        $currentUser = auth()->user();
        $isNormalAdmin = $currentUser?->isNormalAdmin() ?? false;
        $isTechAdmin = $currentUser?->isTechAdmin() ?? false;

        // Determine available role options based on current user
        // STRICT RULE: Each admin type can only create their own type
        $roleOptions = [];
        if ($isNormalAdmin) {
            // Normal admin can only assign: user, admin (for creating regular admins)
            $roleOptions = [
                'user' => 'User',
                'admin' => 'Normal Admin',
            ];
        } elseif ($isTechAdmin) {
            // Technical admin should use CreateAdminPage for creating tech admins
            // This resource is mainly for user management
            $roleOptions = [
                'user' => 'User',
            ];
        }

        return $form
            ->schema([
                Forms\Components\TextInput::make('username')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true, table: 'users')
                    ->validationMessages([
                        'unique' => 'This username is already taken.',
                    ]),

                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true, table: 'users')
                    ->validationMessages([
                        'unique' => 'This email is already registered.',
                        'email' => 'Please enter a valid email address.',
                    ]),

                Forms\Components\Select::make('role')
                    ->options($roleOptions)
                    ->required()
                    ->default('admin') // Default to admin for "Add Admin" feature
                    ->helperText(
                        $isNormalAdmin 
                            ? 'You can only create Normal Admin users.' 
                            : ($isTechAdmin ? 'Use Create Admin page to create Technical Admins.' : '')
                    )
                    ->visible(fn () => $isNormalAdmin || $isTechAdmin),

                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn ($livewire) => $livewire instanceof Pages\CreateUser)
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->rule(Password::min(8)->letters()->mixedCase()->numbers()->symbols())
                    ->validationMessages([
                        'min' => 'Password must be at least 8 characters.',
                    ])
                    ->visible(fn ($livewire) => $livewire instanceof Pages\CreateUser || $livewire instanceof Pages\EditUser)
                    ->helperText('Password must be at least 8 characters with letters, numbers, and symbols.'),

                Forms\Components\Toggle::make('is_notifications_enabled')
                    ->label('Notifications Enabled')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('username')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'user' => 'primary',
                        'admin' => 'success',
                        'tech_admin' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'user' => 'User',
                        'admin' => 'Normal Admin',
                        'tech_admin' => 'Technical Admin',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_notifications_enabled')
                    ->label('Notifications')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'user' => 'User',
                        'admin' => 'Normal Admin',
                        'tech_admin' => 'Technical Admin',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('revokeTokens')
                    ->label('Revoke Tokens')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Revoke All Tokens')
                    ->modalDescription('This will log out the user from all devices. Are you sure?')
                    ->modalSubmitActionLabel('Yes, Revoke Tokens')
                    ->action(function (User $record) {
                        $record->tokens()->delete();
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Tokens Revoked')
                            ->success()
                            ->body("All tokens for {$record->username} have been revoked.")
                            ->send();
                    })
                    ->visible(fn () => auth()->user()?->isNormalAdmin() ?? false)
                    ->successNotificationTitle('All tokens have been revoked successfully.'),

                Tables\Actions\EditAction::make()
                    ->authorize(fn (User $record) => \Illuminate\Support\Facades\Gate::allows('update', $record)),
                    
                Tables\Actions\DeleteAction::make()
                    ->authorize(fn (User $record) => \Illuminate\Support\Facades\Gate::allows('delete', $record))
                    ->before(function (User $record) {
                        if ($record->id === auth()->id()) {
                            throw new \Exception('You cannot delete your own account.');
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}

