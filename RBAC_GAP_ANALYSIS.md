# RBAC Gap Analysis: UserResource Access Control

## Current State vs Required State

### Required Access Rules:
- **Normal Admin (`role:admin`)**: Full Users CRUD (viewAny, view, create, update, delete)
- **Technical Admin (`role:tech_admin`)**: NO Users CRUD access, but can add admins via dedicated page (NOT UserResource)

### Current Implementation (INCORRECT):

#### UserPolicy (`app/Policies/UserPolicy.php`):
- ❌ `viewAny()` returns `$user->isAnyAdmin()` → **ALLOWS tech_admin**
- ❌ `view()` returns `$user->isAnyAdmin()` → **ALLOWS tech_admin**
- ❌ `create()` returns `$user->isAnyAdmin()` → **ALLOWS tech_admin**
- ❌ `update()` returns `$user->isAnyAdmin()` → **ALLOWS tech_admin**
- ❌ `delete()` returns `$user->isAnyAdmin()` → **ALLOWS tech_admin**

#### UserResource (`app/Filament/Resources/UserResource.php`):
- ❌ `canViewAny()` uses `Gate::allows('viewAny', User::class)` → **ALLOWS tech_admin** (because policy allows it)

### Impact:
**Technical Admin currently has FULL Users CRUD access**, which violates the requirement:
- Can view all users (list)
- Can create users
- Can edit users
- Can delete users

This is a **security gap** - tech_admin should NOT have access to UserResource at all.

---

## Proposed Solution

### 1. Restrict UserPolicy to Normal Admin Only

**File:** `app/Policies/UserPolicy.php`

**Changes:**
- `viewAny()` → Change from `isAnyAdmin()` to `isNormalAdmin()`
- `view()` → Change from `isAnyAdmin()` to `isNormalAdmin()`
- `create()` → Change from `isAnyAdmin()` to `isNormalAdmin()`
- `update()` → Change from `isAnyAdmin()` to `isNormalAdmin()`
- `delete()` → Change from `isAnyAdmin()` to `isNormalAdmin()`
- Keep `assignRole()` as-is (used by dedicated page)

### 2. Update UserResource Authorization

**File:** `app/Filament/Resources/UserResource.php`

**Changes:**
- `canViewAny()` → Change to explicitly check `isNormalAdmin()` (defense in depth)

### 3. Create Dedicated "Add Admin" Page for Tech Admin

**New Files to Create:**
- `app/Filament/Pages/AddAdmin.php` - Standalone page for tech_admin to add admins
- This page will:
  - Only be visible to tech_admin
  - Use UserPolicy::assignRole() for authorization
  - Create users directly (bypassing UserResource)
  - Allow creating both `admin` and `tech_admin` users

---

## Files to Modify

### Existing Files (3 files):
1. `app/Policies/UserPolicy.php`
   - Change all CRUD methods from `isAnyAdmin()` to `isNormalAdmin()`
   - Keep `assignRole()` method (needed for dedicated page)

2. `app/Filament/Resources/UserResource.php`
   - Change `canViewAny()` to explicitly check `isNormalAdmin()`

3. `app/Filament/Resources/UserResource/Pages/ListUsers.php`
   - No changes needed (already uses Gate, will automatically respect policy change)

### New Files to Create (1 file):
4. `app/Filament/Pages/AddAdmin.php`
   - New standalone page for tech_admin
   - Form to create admin/tech_admin users
   - Uses UserPolicy::assignRole() for authorization
   - Not part of UserResource

---

## Detailed Code Changes

### Change 1: UserPolicy.php

**Current:**
```php
public function viewAny(User $user): bool
{
    return $user->isAnyAdmin(); // ❌ Allows tech_admin
}
```

**Proposed:**
```php
public function viewAny(User $user): bool
{
    return $user->isNormalAdmin(); // ✅ Only normal admin
}
```

**Apply same change to:** `view()`, `create()`, `update()`, `delete()`

---

### Change 2: UserResource.php

**Current:**
```php
public static function canViewAny(): bool
{
    $user = auth()->user();
    return $user && \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\User::class);
    // ❌ Will allow tech_admin if policy allows it
}
```

**Proposed:**
```php
public static function canViewAny(): bool
{
    $user = auth()->user();
    // Defense in depth: Explicitly check normal admin
    return $user?->isNormalAdmin() ?? false;
}
```

---

### Change 3: Create AddAdmin.php (NEW FILE)

**Location:** `app/Filament/Pages/AddAdmin.php`

**Purpose:** Standalone page for tech_admin to add admins without accessing UserResource

**Features:**
- Only visible to tech_admin
- Form to create admin/tech_admin users
- Uses UserPolicy::assignRole() for authorization
- Creates user directly (bypasses UserResource)
- Shows success message and redirects

---

## Security Impact

### Before (Current - VULNERABLE):
- ❌ Tech_admin can view all users
- ❌ Tech_admin can create/edit/delete users
- ❌ Tech_admin has full Users CRUD access

### After (Fixed - SECURE):
- ✅ Tech_admin CANNOT access UserResource
- ✅ Tech_admin CANNOT view/edit/delete users
- ✅ Tech_admin CAN create admins via dedicated page only
- ✅ Normal admin retains full Users CRUD access

---

## Testing Checklist (After Fix)

1. **Normal Admin:**
   - ✅ Can access Users resource
   - ✅ Can list/view/edit/delete users
   - ✅ Can create users via "Add Admin" button

2. **Technical Admin:**
   - ✅ CANNOT see Users resource in navigation
   - ✅ CANNOT access `/admin/users` directly (403 error)
   - ✅ CAN access dedicated "Add Admin" page
   - ✅ CAN create admin/tech_admin users via dedicated page
   - ✅ CANNOT view/edit/delete existing users

---

## Summary

**Gap Identified:** ✅ YES - tech_admin currently has full Users CRUD access

**Files to Modify:** 3 existing files + 1 new file

**Risk Level:** HIGH - Security violation (privilege escalation)

**Fix Complexity:** LOW - Simple policy changes + new dedicated page

**Ready for Implementation:** ✅ YES

