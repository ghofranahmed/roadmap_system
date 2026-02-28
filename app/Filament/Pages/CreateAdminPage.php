<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Services\AdminCreationRateLimitService;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class CreateAdminPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static string $view = 'filament.pages.create-admin-page';

    protected static ?string $navigationLabel = 'Create Admin';

    protected static ?string $title = 'Create New Admin';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 1;

    public ?array $data = [];

    public function mount(): void
    {
        // Only tech_admin can access this page
        abort_unless(
            auth()->user()?->isTechAdmin() ?? false,
            403,
            'Only Technical Admins can access this page.'
        );

        $this->form->fill();
    }

    public static function canAccess(): bool
    {
        // Only tech_admin can see this page in navigation
        return auth()->user()?->isTechAdmin() ?? false;
    }

    public function form(Form $form): Form
    {
        $currentUser = auth()->user();
        $isTechAdmin = $currentUser?->isTechAdmin() ?? false;

        // STRICT RULE: Tech Admin can ONLY create Tech Admin
        // This page is only accessible to tech_admin
        $roleOptions = [
            'tech_admin' => 'Technical Admin',
        ];

        return $form
            ->schema([
                Section::make('Admin Information')
                    ->description('Create a new admin user. Regular users cannot be created from this page.')
                    ->schema([
                        Forms\Components\TextInput::make('username')
                            ->required()
                            ->maxLength(255)
                            ->unique(table: 'users', column: 'username')
                            ->validationMessages([
                                'unique' => 'This username is already taken.',
                                'required' => 'Username is required.',
                            ])
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(table: 'users', column: 'email')
                            ->validationMessages([
                                'unique' => 'This email is already registered.',
                                'email' => 'Please enter a valid email address.',
                                'required' => 'Email is required.',
                            ])
                            ->columnSpanFull(),

                        Forms\Components\Select::make('role')
                            ->label('Admin Role')
                            ->options($roleOptions)
                            ->required()
                            ->default('tech_admin')
                            ->disabled() // Force tech_admin only
                            ->helperText('You can only create Technical Admin accounts.')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->rule(Password::min(8)->letters()->mixedCase()->numbers()->symbols())
                            ->validationMessages([
                                'required' => 'Password is required.',
                                'min' => 'Password must be at least 8 characters.',
                            ])
                            ->helperText('Password must be at least 8 characters with letters, numbers, and symbols.')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('password_confirmation')
                            ->password()
                            ->required()
                            ->same('password')
                            ->validationMessages([
                                'required' => 'Password confirmation is required.',
                                'same' => 'Password confirmation does not match.',
                            ])
                            ->dehydrated(false) // Don't save this field
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_notifications_enabled')
                            ->label('Enable Notifications')
                            ->default(true)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function create(AdminCreationRateLimitService $rateLimitService): void
    {
        $currentUser = auth()->user();

        // Server-side authorization check - only tech_admin can access this page
        if (!$currentUser || !$currentUser->isTechAdmin()) {
            throw ValidationException::withMessages([
                'form' => ['Only Technical Admins can create admin users from this page.'],
            ]);
        }

        // Check rate limit
        $rateLimitCheck = $rateLimitService->checkRateLimit($currentUser, 'tech_admin');
        if (!$rateLimitCheck['allowed']) {
            $remainingTime = $rateLimitCheck['remaining_time'];
            $remainingMinutes = $rateLimitCheck['remaining_minutes'];
            $hours = floor($remainingMinutes / 60);
            $minutes = $remainingMinutes % 60;
            
            throw ValidationException::withMessages([
                'form' => [
                    $rateLimitCheck['message'] . 
                    ($remainingTime ? " You can create another admin in " . 
                        ($hours > 0 ? "{$hours} hour(s) and " : "") . 
                        "{$minutes} minute(s)." : "")
                ],
            ]);
        }

        $data = $this->form->getState();

        // STRICT RULE: Force tech_admin role - Tech Admin can ONLY create Tech Admin
        $data['role'] = 'tech_admin';

        // Server-side authorization: Check if user can assign the requested role
        if (!Gate::allows('assignRole', [User::class, $data['role']])) {
            throw ValidationException::withMessages([
                'data.role' => ['You are not authorized to assign this role. You can only create Technical Admin accounts.'],
            ]);
        }

        // Validate that role is tech_admin (strict enforcement)
        if ($data['role'] !== 'tech_admin') {
            throw ValidationException::withMessages([
                'data.role' => ['Invalid role. You can only create Technical Admin accounts.'],
            ]);
        }

        // Create the user
        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'], // Already hashed in form
            'role' => $data['role'],
            'is_notifications_enabled' => $data['is_notifications_enabled'] ?? true,
        ]);

        // Log the creation for rate limiting
        $rateLimitService->logCreation($currentUser, $user, 'tech_admin');

        // Show success notification
        Notification::make()
            ->title('Admin Created Successfully')
            ->success()
            ->body("Admin user '{$user->username}' has been created with role: " . match($user->role) {
                'admin' => 'Normal Admin',
                'tech_admin' => 'Technical Admin',
                default => $user->role,
            })
            ->send();

        // Reset form
        $this->form->fill();
    }

    protected function getFormActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('create')
                ->label('Create Admin')
                ->submit('create')
                ->icon('heroicon-o-check')
                ->color('success')
                ->size('lg'),
        ];
    }
}

