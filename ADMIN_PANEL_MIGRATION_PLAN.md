# Admin Panel Migration Plan: Filament → AdminLTE

## 1. RBAC Summary

### Role Storage
- **Location**: `users.role` column (string)
- **Values**: `'user'`, `'admin'`, `'tech_admin'`
- **No separate roles/permissions tables** - roles are stored directly on User model

### User Model Helpers (`app/Models/User.php`)
```php
isNormalAdmin(): bool    // role === 'admin'
isTechAdmin(): bool     // role === 'tech_admin'
isAnyAdmin(): bool      // role in ['admin', 'tech_admin']
hasRole(string $role): bool
```

### Middleware
- **`AdminMiddleware`**: Checks `isAnyAdmin()` (both admin types)
- **`RoleMiddleware`**: Checks specific role(s) via route parameter
- **Usage**: Routes use `middleware(['web', 'auth', 'is_admin'])` or `role:admin` / `role:tech_admin`

### Policies Registered (`app/Providers/AppServiceProvider.php`)
- `UserPolicy` - User management
- `AnnouncementPolicy` - Announcements
- `QuizPolicy` - Quizzes
- `ChallengePolicy` - Challenges
- `ChatMessagePolicy` - Chat messages
- `QuizAttemptPolicy` - Quiz attempts
- `ChallengeAttemptPolicy` - Challenge attempts

### Permission Mapping

| Role | Permissions |
|------|-------------|
| **Normal Admin** (`admin`) | - View/Edit/Delete Users (via UserPolicy)<br>- Full CRUD Announcements (via AnnouncementPolicy)<br>- Chat Moderation (mute/ban/unmute/unban)<br>- Read-only access to all content (roadmaps, units, lessons, quizzes, challenges)<br>- Cannot create users (missing endpoint, uses CreateAdminPage for tech_admin only) |
| **Technical Admin** (`tech_admin`) | - Full CRUD for all content (roadmaps, units, lessons, sub-lessons, resources, quizzes, questions, challenges)<br>- Create Admin users (via CreateAdminPage)<br>- Toggle active status, reorder operations<br>- NO access to: Users CRUD, Announcements, Chat Moderation |

---

## 2. Admin Features Map

### 2.1 User Management
- **Model**: `User`
- **Policy**: `UserPolicy`
- **Access**: Normal Admin ONLY (`isNormalAdmin()`)
- **Filament Resource**: `UserResource`
- **Features**:
  - List users (with filters: role, search)
  - View user details
  - Edit user (username, email, role, notifications)
  - Delete user (cannot delete self)
  - Revoke tokens (logout from all devices)
  - **MISSING**: Create user endpoint (tech_admin uses CreateAdminPage instead)

### 2.2 Announcements
- **Model**: `Announcement`
- **Policy**: `AnnouncementPolicy`
- **Access**: Normal Admin ONLY (`isNormalAdmin()`)
- **Filament Resource**: `AnnouncementResource`
- **Features**:
  - Full CRUD (Create, Read, Update, Delete)
  - Fields: title, description, type (general/technical/opportunity), link, starts_at, ends_at
  - Filters: type, search
  - Navigation Group: "User Management"

### 2.3 Chat Moderation
- **Model**: `ChatModeration`, `ChatMessage`
- **Policy**: `ChatMessagePolicy` (for edit/delete messages)
- **Access**: Normal Admin ONLY (`isNormalAdmin()`)
- **Controller**: `AdminChatModerationController`
- **Features**:
  - View chat members for a roadmap
  - Mute user (temporary silence)
  - Unmute user
  - Ban user (permanent ban)
  - Unban user
  - Edit/Delete chat messages
  - **Note**: Cannot moderate admins (admin/tech_admin roles protected)

### 2.4 Roadmaps (Content Management)
- **Model**: `Roadmap`
- **Policy**: None (relies on route middleware)
- **Access**: 
  - **Read**: Both admin types (`role:admin,tech_admin`)
  - **Write**: Technical Admin ONLY (`isTechAdmin()`)
- **Filament Resource**: `RoadmapResource`
- **Features**:
  - Full CRUD (Tech Admin only)
  - Fields: title, description, level (beginner/intermediate/advanced), is_active
  - Toggle active status
  - Navigation Group: "Content Management"

### 2.5 Learning Units
- **Model**: `LearningUnit`
- **Policy**: None (relies on route middleware)
- **Access**: 
  - **Read**: Both admin types
  - **Write**: Technical Admin ONLY
- **Filament Resource**: `LearningUnitResource`
- **Features**:
  - Full CRUD (Tech Admin only)
  - Reorder units
  - Toggle active status
  - Navigation Group: "Content Management"

### 2.6 Lessons
- **Model**: `Lesson`
- **Policy**: None (relies on route middleware)
- **Access**: 
  - **Read**: Both admin types
  - **Write**: Technical Admin ONLY
- **Filament Resource**: `LessonResource`
- **Features**:
  - Full CRUD (Tech Admin only)
  - Reorder lessons
  - Toggle active status
  - Navigation Group: "Content Management"

### 2.7 Sub-Lessons
- **Model**: `SubLesson`
- **Policy**: None (relies on route middleware)
- **Access**: 
  - **Read**: Both admin types
  - **Write**: Technical Admin ONLY
- **Filament Resource**: `SubLessonResource`
- **Features**:
  - Full CRUD (Tech Admin only)
  - Reorder sub-lessons
  - Navigation Group: "Content Management"

### 2.8 Resources
- **Model**: `Resource`
- **Policy**: None (relies on route middleware)
- **Access**: 
  - **Read**: Both admin types
  - **Write**: Technical Admin ONLY
- **Filament Resource**: `ResourceResource`
- **Features**:
  - Full CRUD (Tech Admin only)
  - Navigation Group: "Content Management"

### 2.9 Quizzes
- **Model**: `Quiz`
- **Policy**: `QuizPolicy` (requires `isTechAdmin()`)
- **Access**: 
  - **Read**: Both admin types
  - **Write**: Technical Admin ONLY
- **Filament Resource**: `QuizResource`
- **Features**:
  - Full CRUD (Tech Admin only)
  - Fields: learning_unit_id, min_xp, max_xp, is_active
  - Navigation Group: "Content Management"

### 2.10 Quiz Questions
- **Model**: `QuizQuestion`
- **Policy**: None (relies on route middleware)
- **Access**: 
  - **Read**: Both admin types
  - **Write**: Technical Admin ONLY
- **Filament Resource**: `QuizQuestionResource`
- **Features**:
  - Full CRUD (Tech Admin only)
  - Navigation Group: "Content Management"

### 2.11 Challenges
- **Model**: `Challenge`
- **Policy**: `ChallengePolicy` (requires `isTechAdmin()`)
- **Access**: 
  - **Read**: Both admin types
  - **Write**: Technical Admin ONLY
- **Filament Resource**: `ChallengeResource`
- **Features**:
  - Full CRUD (Tech Admin only)
  - Toggle active status
  - Navigation Group: "Content Management"

### 2.12 Create Admin (System Feature)
- **Model**: `User`
- **Policy**: `UserPolicy::assignRole()`
- **Access**: Technical Admin ONLY (`isTechAdmin()`)
- **Filament Page**: `CreateAdminPage`
- **Features**:
  - Create admin users (admin or tech_admin roles only)
  - Cannot create regular 'user' role from this page
  - Strong password validation
  - Server-side role assignment validation
  - Navigation Group: "System"

### 2.13 Dashboard
- **Filament Page**: `Dashboard`
- **Access**: Both admin types
- **Features**:
  - Role-specific headings/subheadings
  - Normal Admin: "Manage users, announcements, and chat moderation"
  - Technical Admin: "Manage all content: roadmaps, units, lessons, quizzes, and challenges"

---

## 3. Menu Structure (AdminLTE)

### Normal Admin Menu (`role:admin`)
```
📊 Dashboard
├── 👥 User Management
│   ├── Users
│   └── Announcements
├── 💬 Community
│   └── Chat Moderation
└── 📚 Content (Read-Only)
    ├── Roadmaps
    ├── Learning Units
    ├── Lessons
    ├── Sub-Lessons
    ├── Resources
    ├── Quizzes
    └── Challenges
```

### Technical Admin Menu (`role:tech_admin`)
```
📊 Dashboard
├── 📚 Content Management
│   ├── Roadmaps
│   ├── Learning Units
│   ├── Lessons
│   ├── Sub-Lessons
│   ├── Resources
│   ├── Quizzes
│   ├── Quiz Questions
│   └── Challenges
└── ⚙️ System
    └── Create Admin
```

---

## 4. Implementation Priority

### Phase A: Foundation (Current)
1. ✅ Dashboard + Menu + RBAC wiring
2. ✅ Announcements CRUD (already done)

### Phase B: User Management
3. Users CRUD (List, View, Edit, Delete, Revoke Tokens)
4. Create Admin page (for tech_admin)

### Phase C: Content Management (Tech Admin)
5. Roadmaps CRUD
6. Learning Units CRUD
7. Lessons CRUD
8. Sub-Lessons CRUD
9. Resources CRUD
10. Quizzes CRUD
11. Quiz Questions CRUD
12. Challenges CRUD

### Phase D: Community
13. Chat Moderation interface

---

## 5. Security Requirements Checklist

- [x] All admin routes under `middleware(['web', 'auth', 'is_admin'])` and `prefix('admin')`
- [x] Every controller method calls `$this->authorize(...)` with Policies
- [x] Menu items use `'can' => function() { ... }` for visibility
- [x] Direct URL access returns 403 for unauthorized users
- [x] Role assignment validated server-side via `UserPolicy::assignRole()`
- [x] Normal Admin cannot assign tech_admin role
- [x] Technical Admin cannot access User/Announcement management

---

## 6. Files to Create/Modify

### Controllers
- `app/Http/Controllers/Admin/DashboardController.php` (NEW)
- `app/Http/Controllers/Admin/UserController.php` (NEW - web version)
- `app/Http/Controllers/Admin/CreateAdminController.php` (NEW)
- `app/Http/Controllers/Admin/AnnouncementController.php` (EXISTS - verify)

### Views
- `resources/views/admin/dashboard.blade.php` (NEW)
- `resources/views/admin/users/index.blade.php` (NEW)
- `resources/views/admin/users/show.blade.php` (NEW)
- `resources/views/admin/users/edit.blade.php` (NEW)
- `resources/views/admin/create-admin.blade.php` (NEW)
- `resources/views/admin/announcements/*.blade.php` (EXISTS - verify)

### Routes
- Update `routes/web.php` with all admin routes

### Config
- Update `config/adminlte.php` menu with role-based visibility

