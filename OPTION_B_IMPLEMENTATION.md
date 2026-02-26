# Option B Implementation: UserResource Restriction + CreateAdminPage

## Implementation Complete ✅

### Summary
- **UserResource**: Now restricted to `role:admin` only (no tech_admin access)
- **CreateAdminPage**: New dedicated page for `tech_admin` to create admins
- **UserPolicy**: Updated to enforce least privilege
- **Server-side enforcement**: All authorization checks in place

---

## Files Modified

### 1. `app/Policies/UserPolicy.php`

**Changes:**
- `viewAny()`: Changed from `isAnyAdmin()` → `isNormalAdmin()` ✅
- `view()`: Changed from `isAnyAdmin()` → `isNormalAdmin()` ✅
- `create()`: Changed from `isAnyAdmin()` → `isNormalAdmin()` ✅
- `update()`: Changed from `isAnyAdmin()` → `isNormalAdmin()` ✅
- `delete()`: Changed from `isAnyAdmin()` → `isNormalAdmin()` ✅
- `assignRole()`: Updated to only allow `admin` and `tech_admin` (removed `user`) ✅

**Key Code Snippet:**
```php
public function viewAny(User $user): bool
{
    return $user->isNormalAdmin(); // ✅ Only normal admin
}

public function assignRole(User $user, string $role): bool
{
    // Normal admin can only assign admin role
    if ($user->isNormalAdmin()) {
        return $role === 'admin';
    }

    // Technical admin can assign admin or tech_admin roles
    if ($user->isTechAdmin()) {
        return in_array($role, ['admin', 'tech_admin']);
    }

    return false;
}
```

---

### 2. `app/Filament/Resources/UserResource.php`

**Changes:**
- `canViewAny()`: Changed to explicitly check `isNormalAdmin()` (defense in depth) ✅

**Key Code Snippet:**
```php
public static function canViewAny(): bool
{
    $user = auth()->user();
    // Defense in depth: Explicitly restrict to normal admin only
    // tech_admin should use CreateAdminPage instead
    return $user?->isNormalAdmin() ?? false;
}
```

**Result:**
- ✅ Normal admin: Can see Users resource in navigation
- ✅ Tech admin: Cannot see Users resource (not in navigation, direct access blocked)

---

### 3. `app/Filament/Pages/CreateAdminPage.php` (NEW FILE)

**Purpose:** Dedicated page for tech_admin to create admin users

**Features:**
- ✅ Only visible to `tech_admin` (`canAccess()` method)
- ✅ Role selection: `admin` or `tech_admin` (no `user` option)
- ✅ Server-side authorization using `Gate::allows('assignRole')`
- ✅ Strong password validation (8+ chars, letters, numbers, symbols)
- ✅ Unique email/username validation
- ✅ Password hashing
- ✅ Success notification after creation

**Key Code Snippets:**

**Access Control:**
```php
public static function canAccess(): bool
{
    // Only tech_admin can see this page in navigation
    return auth()->user()?->isTechAdmin() ?? false;
}

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
```

**Form with Role Selection:**
```php
public function form(Form $form): Form
{
    // This page is only accessible to tech_admin, so show both options
    $roleOptions = [
        'admin' => 'Normal Admin',
        'tech_admin' => 'Technical Admin',
    ];

    return $form->schema([
        // Username, email, role, password fields...
        Forms\Components\Select::make('role')
            ->label('Admin Role')
            ->options($roleOptions)
            ->required()
            ->default('admin')
            ->helperText('Select the role for the new admin user.'),
        // ...
    ]);
}
```

**Server-Side Authorization:**
```php
public function create(): void
{
    $currentUser = auth()->user();

    // Server-side authorization check
    if (!$currentUser || !$currentUser->isTechAdmin()) {
        throw ValidationException::withMessages([
            'form' => ['Only Technical Admins can create admin users from this page.'],
        ]);
    }

    $data = $this->form->getState();

    // Server-side authorization: Check if user can assign the requested role
    if (!Gate::allows('assignRole', [User::class, $data['role']])) {
        throw ValidationException::withMessages([
            'data.role' => ['You are not authorized to assign this role.'],
        ]);
    }

    // Prevent creating 'user' role from this page
    if ($data['role'] === 'user') {
        throw ValidationException::withMessages([
            'data.role' => ['Regular users cannot be created from this page.'],
        ]);
    }

    // Create user with hashed password
    $user = User::create([
        'username' => $data['username'],
        'email' => $data['email'],
        'password' => $data['password'], // Already hashed in form
        'role' => $data['role'],
        'is_notifications_enabled' => $data['is_notifications_enabled'] ?? true,
    ]);
}
```

---

### 4. `resources/views/filament/pages/create-admin-page.blade.php` (NEW FILE)

**Purpose:** Blade view for CreateAdminPage

**Content:**
```blade
<x-filament-panels::page>
    <form wire:submit="create">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getFormActions()"
        />
    </form>
</x-filament-panels::page>
```

---

## Authorization Flow

### Normal Admin (`role:admin`)
1. **UserResource Access:**
   - ✅ Can see Users in navigation (`canViewAny()` returns true)
   - ✅ Can list/view/edit/delete users
   - ✅ Can create users via "Add Admin" button
   - ✅ Role options: `user`, `admin` (no `tech_admin`)

2. **CreateAdminPage Access:**
   - ❌ Cannot see page in navigation (`canAccess()` returns false)
   - ❌ Direct access blocked (403 error)

### Technical Admin (`role:tech_admin`)
1. **UserResource Access:**
   - ❌ Cannot see Users in navigation (`canViewAny()` returns false)
   - ❌ Direct URL access blocked (403 error)
   - ❌ No CRUD access to users

2. **CreateAdminPage Access:**
   - ✅ Can see "Create Admin" in navigation (System group)
   - ✅ Can access page
   - ✅ Can create `admin` or `tech_admin` users
   - ❌ Cannot create `user` role (blocked by validation)

---

## Security Enforcement Layers

### Layer 1: Policy (Gate)
- `UserPolicy::viewAny()` - Only normal admin
- `UserPolicy::assignRole()` - Role-based assignment check

### Layer 2: Resource Visibility
- `UserResource::canViewAny()` - Explicit `isNormalAdmin()` check

### Layer 3: Page Access Control
- `CreateAdminPage::canAccess()` - Only tech_admin
- `CreateAdminPage::mount()` - 403 if not tech_admin

### Layer 4: Form Validation
- Role dropdown filtered (no `user` option)
- Server-side `Gate::allows('assignRole')` check
- Explicit validation prevents `user` role creation

### Layer 5: Password & Data Validation
- Strong password requirements
- Unique email/username
- Password hashing via `Hash::make()`

---

## Validation Rules

### Username
- Required
- Max 255 characters
- Unique in `users` table

### Email
- Required
- Valid email format
- Max 255 characters
- Unique in `users` table

### Password
- Required
- Minimum 8 characters
- Must contain letters (mixed case)
- Must contain numbers
- Must contain symbols
- Confirmed (password_confirmation field)

### Role
- Required
- Must be `admin` or `tech_admin` (no `user` allowed)
- Server-side authorization check via `assignRole()` policy

---

## Navigation Structure

### Normal Admin Navigation:
```
📊 Dashboard
├── 👥 Users (UserResource) ✅
│   ├── List Users
│   ├── Add Admin
│   ├── Edit User
│   └── Delete User
├── 📢 Announcements
└── 💬 Chat Moderation
```

### Technical Admin Navigation:
```
📊 Dashboard
├── 📚 Content Management
│   ├── Roadmaps
│   ├── Learning Units
│   └── ...
└── ⚙️ System
    └── ➕ Create Admin (CreateAdminPage) ✅
```

---

## Testing Checklist

### Normal Admin Tests:
- [ ] Can see Users resource in navigation
- [ ] Can list all users
- [ ] Can create user via "Add Admin" button
- [ ] Role dropdown shows: `user`, `admin` (no `tech_admin`)
- [ ] Cannot access `/admin/create-admin` (403 error)
- [ ] Cannot see "Create Admin" in navigation

### Technical Admin Tests:
- [ ] Cannot see Users resource in navigation
- [ ] Cannot access `/admin/users` directly (403 error)
- [ ] Can see "Create Admin" in System group
- [ ] Can access `/admin/create-admin` page
- [ ] Role dropdown shows: `admin`, `tech_admin` (no `user`)
- [ ] Can create Normal Admin user
- [ ] Can create Technical Admin user
- [ ] Cannot create user with `user` role (validation error)

### Security Tests:
- [ ] Normal admin cannot assign `tech_admin` role (blocked)
- [ ] Tech admin cannot access UserResource (blocked)
- [ ] Password is properly hashed before saving
- [ ] Unique email/username validation works
- [ ] Strong password validation works

---

## Key Code Snippets Summary

### UserPolicy (Authorization)
```php
// CRUD methods - Normal admin only
public function viewAny(User $user): bool {
    return $user->isNormalAdmin();
}

// Role assignment - Different rules for each admin type
public function assignRole(User $user, string $role): bool {
    if ($user->isNormalAdmin()) {
        return $role === 'admin';
    }
    if ($user->isTechAdmin()) {
        return in_array($role, ['admin', 'tech_admin']);
    }
    return false;
}
```

### UserResource (Visibility)
```php
public static function canViewAny(): bool {
    return auth()->user()?->isNormalAdmin() ?? false;
}
```

### CreateAdminPage (Access Control)
```php
public static function canAccess(): bool {
    return auth()->user()?->isTechAdmin() ?? false;
}

// Server-side authorization in create()
if (!Gate::allows('assignRole', [User::class, $data['role']])) {
    throw ValidationException::withMessages([...]);
}
```

---

## Implementation Status: ✅ COMPLETE

- ✅ UserResource restricted to normal admin only
- ✅ CreateAdminPage created for tech_admin
- ✅ Role selection based on current user
- ✅ Server-side enforcement via Policy/Gate
- ✅ Password hashing and validations
- ✅ No database migrations required
- ✅ All security layers in place

Ready for testing!

