# Capabilities Matrix: Admin vs Tech Admin

## Quick Reference

### Normal Admin (`role:admin`)

| Module | List | Show | Create | Update | Delete | Special |
|--------|------|------|--------|---------|--------|---------|
| **Users** | ✅ | ✅ | ❌ **MISSING** | ✅ | ✅ | ✅ Revoke Tokens |
| **Announcements** | ✅ | ✅ | ✅ | ✅ | ✅ | - |
| **Chat Moderation** | ✅ | ✅ | ✅ | ✅ | ✅ | Mute/Unmute/Ban/Unban |
| **Content (All)** | ✅ | ✅ | ❌ | ❌ | ❌ | Read-Only |

### Technical Admin (`role:tech_admin`)

| Module | List | Show | Create | Update | Delete | Special |
|--------|------|------|--------|---------|--------|---------|
| **Users** | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ No Access |
| **Announcements** | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ No Access |
| **Chat Moderation** | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ No Access |
| **Roadmaps** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ Toggle Active |
| **Learning Units** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ Reorder, Toggle |
| **Lessons** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ Reorder, Toggle |
| **SubLessons** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ Reorder |
| **Resources** | ✅ | ✅ | ✅ | ✅ | ✅ | - |
| **Quizzes** | ✅ | ✅ | ✅ | ✅ | ✅ | - |
| **Quiz Questions** | ✅ | ✅ | ✅ | ✅ | ✅ | - |
| **Challenges** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ Toggle Active |

---

## Filament Panel Expected Pages

### Normal Admin Panel Navigation

```
📊 Dashboard
├── 👥 Users
│   ├── List Users
│   ├── View User
│   ├── Edit User
│   ├── Delete User
│   └── Revoke Tokens
├── 📢 Announcements
│   ├── List Announcements
│   ├── Create Announcement
│   ├── Edit Announcement
│   └── Delete Announcement
├── 💬 Chat Moderation
│   ├── View Members
│   ├── Mute User
│   ├── Unmute User
│   ├── Ban User
│   └── Unban User
└── 📚 Content (Read-Only)
    ├── Roadmaps (View Only)
    ├── Learning Units (View Only)
    ├── Lessons (View Only)
    ├── SubLessons (View Only)
    ├── Resources (View Only)
    ├── Quizzes (View Only)
    └── Challenges (View Only)
```

### Technical Admin Panel Navigation

```
📊 Dashboard
└── 📚 Content Management
    ├── 🗺️ Roadmaps
    │   ├── List Roadmaps
    │   ├── Create Roadmap
    │   ├── Edit Roadmap
    │   ├── Delete Roadmap
    │   └── Toggle Active
    ├── 📖 Learning Units
    │   ├── List Units
    │   ├── Create Unit
    │   ├── Edit Unit
    │   ├── Delete Unit
    │   ├── Reorder Units
    │   └── Toggle Active
    ├── 📝 Lessons
    │   ├── List Lessons
    │   ├── Create Lesson
    │   ├── Edit Lesson
    │   ├── Delete Lesson
    │   ├── Reorder Lessons
    │   └── Toggle Active
    ├── 📄 SubLessons
    │   ├── List SubLessons
    │   ├── Create SubLesson
    │   ├── Edit SubLesson
    │   ├── Delete SubLesson
    │   └── Reorder SubLessons
    ├── 📎 Resources
    │   ├── List Resources
    │   ├── Create Resource
    │   ├── Edit Resource
    │   └── Delete Resource
    ├── ❓ Quizzes
    │   ├── List Quizzes
    │   ├── Create Quiz
    │   ├── Edit Quiz
    │   └── Delete Quiz
    ├── 📝 Quiz Questions
    │   ├── List Questions
    │   ├── Create Question
    │   ├── Edit Question
    │   └── Delete Question
    └── 🎯 Challenges
        ├── List Challenges
        ├── Create Challenge
        ├── Edit Challenge
        ├── Delete Challenge
        └── Toggle Active
```

---

## API Endpoints Summary

### Normal Admin Endpoints (`/admin/*`)

**Users:**
- `GET /admin/users` - List users
- `GET /admin/users/{id}` - Show user
- `PUT /admin/users/{id}` - Update user
- `DELETE /admin/users/{id}` - Delete user
- `POST /admin/users/{id}/revoke-tokens` - Revoke tokens
- `POST /admin/users` - **MISSING** - Create user

**Announcements:**
- `GET /admin/announcements` - List announcements
- `POST /admin/announcements` - Create announcement
- `GET /admin/announcements/{id}` - Show announcement
- `PUT /admin/announcements/{id}` - Update announcement
- `DELETE /admin/announcements/{id}` - Delete announcement

**Chat Moderation:**
- `POST /admin/roadmaps/{roadmapId}/chat/mute` - Mute user
- `POST /admin/roadmaps/{roadmapId}/chat/unmute` - Unmute user
- `POST /admin/roadmaps/{roadmapId}/chat/ban` - Ban user
- `POST /admin/roadmaps/{roadmapId}/chat/unban` - Unban user
- `GET /admin/roadmaps/{roadmapId}/chat/members` - List members

**Content (Read-Only):**
- All GET endpoints for roadmaps, units, lessons, sub-lessons, resources, quizzes, questions, challenges

### Technical Admin Endpoints (`/admin/*`)

**Content (Full CRUD):**
- All POST/PUT/DELETE/PATCH endpoints for:
  - Roadmaps (create, update, delete, toggle-active)
  - Learning Units (create, update, delete, reorder, toggle-active)
  - Lessons (create, update, delete, reorder, toggle-active)
  - SubLessons (create, update, delete, reorder)
  - Resources (create, update, delete)
  - Quizzes (create, update, delete)
  - Quiz Questions (create, update, delete)
  - Challenges (create, update, delete, toggle-active)

**No Access To:**
- User management endpoints
- Announcement management endpoints
- Chat moderation endpoints

---

## Critical Issues Found

### 🔴 High Priority

1. **Missing User Creation Endpoint**
   - No `store()` method in `AdminUserController`
   - Route `POST /admin/users` doesn't exist
   - Required for: Normal Admin to create Normal Admins, Tech Admin to create both

2. **AnnouncementPolicy Mismatch**
   - Policy allows both `admin` and `tech_admin`
   - Routes only allow `role:admin`
   - Fix: Either restrict policy to `isNormalAdmin()` or allow tech_admin in routes

3. **No UserPolicy**
   - Cannot use Laravel authorization for user management
   - Missing role-based validation in user creation/update

4. **Filament Not Configured**
   - Package listed in composer.json but no panel provider exists
   - No Filament Resources created
   - No admin panel available

### ⚠️ Medium Priority

5. **Missing Content Policies**
   - No policies for Roadmap, LearningUnit, Lesson, SubLesson, Resource, QuizQuestion
   - Currently relies only on middleware/constructor checks

6. **User Update Role Validation**
   - No check to prevent Normal Admin from assigning `tech_admin` role
   - Should add validation logic

---

## Implementation Status

| Component | Status | Notes |
|-----------|--------|-------|
| Route Middleware | ✅ Complete | Properly configured |
| Controller Checks | ✅ Mostly Complete | Some inconsistencies |
| Policies | ⚠️ Partial | Missing UserPolicy and content policies |
| Filament Panel | ❌ Not Configured | Package listed but not set up |
| User Creation | ❌ Missing | No store() method |
| Role Validation | ⚠️ Partial | Needs enhancement in user update |

---

**Last Updated:** Analysis Date

