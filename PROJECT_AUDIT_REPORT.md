# Project Audit Report - Laravel Roadmap Learning Platform

**Date:** 2026-02-16  
**Auditor:** Senior Laravel Architect  
**Scope:** Full end-to-end audit of unused/redundant/unnecessary code

---

## Executive Summary

This audit identifies **unused, redundant, and unnecessary** components across the Laravel Roadmap Learning Platform codebase. The analysis covers database fields, tables, relationships, migrations, models, controllers, routes, views, configs, services, middleware, policies, and seeders.

**Key Findings:**
- **High Confidence Removals:** 15+ items
- **Medium Confidence Removals:** 20+ items  
- **Low Confidence / Keep:** 10+ items requiring further investigation
- **Total Potential Cleanup:** ~45 items

---

## 1. System Overview (Modules)

Based on codebase analysis, the system consists of:

### Core Modules:
1. **Authentication & Authorization** (users, roles, admin accounts)
2. **Roadmaps Management** (roadmaps, learning units, lessons, sub-lessons)
3. **Content Management** (resources, quizzes, quiz questions, challenges)
4. **User Progress Tracking** (enrollments, lesson tracking, quiz attempts, challenge attempts)
5. **Community Features** (chat rooms, chat messages, chat moderation)
6. **Notifications System** (notifications, announcements)
7. **AI Chatbot** (chatbot sessions, chatbot messages, chatbot settings)
8. **Admin Panel** (AdminLTE web UI + Filament admin panel)
9. **System Settings** (system settings, chatbot settings)

---

## 2. Database Inventory

### Tables (26 total):
1. `users`
2. `roadmaps`
3. `learning_units`
4. `lessons`
5. `sub_lessons`
6. `resources`
7. `quizzes`
8. `quiz_questions`
9. `challenges`
10. `roadmap_enrollments`
11. `lesson_trackings`
12. `quiz_attempts`
13. `challenge_attempts`
14. `chat_rooms`
15. `chat_messages`
16. `chat_moderations`
17. `notifications`
18. `announcements`
19. `chatbot_sessions`
20. `chatbot_messages`
21. `chatbot_settings`
22. `linked_accounts`
23. `settings`
24. `system_settings`
25. `admin_creation_logs`
26. `password_reset_tokens` (Laravel default)

---

## 3. Top 15 Removal Candidates (Highest Confidence)

| # | Item | Type | Confidence | Risk | Recommendation | Evidence |
|---|------|------|------------|------|-----------------|----------|
| 1 | `users.is_admin` column | Database Field | **High** | Low | **Remove** | Migration `2026_01_30_190131_add_is_admin_to_users_table.php` exists, but field is deprecated. System uses `role` field instead. Only found in migration history and User model cast (line 37). No actual usage found. |
| 2 | `Setting` model + `settings` table | Model/Table | **High** | Medium | **Remove** | Model exists (`app/Models/Setting.php`), relationship defined in User model (`settings()`), but **NO usage found** in controllers/services. Only `SystemSetting` is used. |
| 3 | `User::settings()` relationship | Relationship | **High** | Low | **Remove** | Defined in `app/Models/User.php:61-63`, but **never accessed** in codebase. |
| 4 | `Setting::modifiedBy()` relationship | Relationship | **High** | Low | **Remove** | Defined in `app/Models/Setting.php:12-14`, but **never accessed**. |
| 5 | `ChatbotSession::title` field | Database Field | **High** | Low | **Remove** | Field exists in model fillable, but **NO usage found** in controllers/views. Only `id` and `last_activity_at` are used. |
| 6 | `ChatbotMessage::body` field | Database Field | **High** | Low | **Remove** | Field exists in model fillable, but **NO usage found**. Only `role` and `tokens_used` are accessed. |
| 7 | `ChatbotMessage::role` field | Database Field | **High** | Low | **Remove** | Field exists in model fillable, but **NO usage found** in application code. |
| 8 | `ChatMessage::attachment` field | Database Field | **High** | Low | **Remove** | Field exists in model fillable, but **NO usage found** in ChatMessageController. Only `content`, `sent_at`, `edited_at` are used. |
| 9 | `ChatMessage::sent_at` field | Database Field | **Medium** | Low | **Review** | Field is written in ChatMessageController (lines 104, 345) but **never read/displayed**. Consider removing if not needed for audit trail. |
| 10 | `ChatMessage::edited_at` field | Database Field | **Medium** | Low | **Review** | Field is written in ChatMessageController (line 149) but **never read/displayed**. Consider removing if not needed for audit trail. |
| 11 | `Notification::is_active` field | Database Field | **High** | Low | **Remove** | Field exists in model, used in scope `ready()` (line 79), but **NO filtering/checking** in controllers. All notifications are treated as active. |
| 12 | `Notification::type` field | Database Field | **Medium** | Low | **Review** | Field is written but **rarely filtered**. Only used in AdminNotificationApiController filter (line 34) and NotificationController filter (line 34). Consider if filtering is actually needed. |
| 13 | `Notification::priority` field | Database Field | **Medium** | Low | **Review** | Field is written but **rarely filtered**. Only used in AdminNotificationApiController filter (line 38) and NotificationController filter (line 38). Consider if filtering is actually needed. |
| 14 | `Notification::metadata` field | Database Field | **Medium** | Low | **Review** | Field is written but **never read/used** in application logic. Only stored. Consider removing if not needed for future features. |
| 15 | `ChatbotSetting::model_name` field | Database Field | **High** | Low | **Remove** | Field exists in model fillable, validated in SmartTeacherController (line 52), but **never used** in ChatbotReplyService or provider classes. Only `provider`, `temperature`, `max_tokens`, etc. are used. |

---

## 4. Detailed "Used vs Unused" Inventory

### 4.1 Database Fields

#### Users Table
| Field | Status | Evidence | Recommendation |
|-------|--------|----------|----------------|
| `is_admin` | **UNUSED** | Only in migration history and model cast. System uses `role` field. | **Remove** (High) |
| `email_verified_at` | **USED** | Set in SocialAuthController for social logins. | **Keep** |
| `remember_token` | **USED** | Laravel default, used by auth system. | **Keep** |
| `name` (accessor) | **USED** | `getNameAttribute()` used in views (user-menu.blade.php). | **Keep** |
| `getFilamentName()` | **USED** | Used by Filament admin panel. | **Keep** |
| `canAccessPanel()` | **USED** | Used by Filament admin panel. | **Keep** |
| `adminlte_image()` | **USED** | Used in AdminLTE views. | **Keep** |
| `adminlte_desc()` | **USED** | Used in AdminLTE views. | **Keep** |
| `adminlte_profile_url()` | **USED** | Used in AdminLTE views. | **Keep** |

#### Settings Table
| Field | Status | Evidence | Recommendation |
|-------|--------|----------|----------------|
| `settings` table | **UNUSED** | Model exists, relationship defined, but **NO usage** in controllers/services. | **Remove** (High) |
| `modified_by_user_id` | **UNUSED** | Relationship defined but **never accessed**. | **Remove** (High) |

#### ChatbotSession Table
| Field | Status | Evidence | Recommendation |
|-------|--------|----------|----------------|
| `title` | **UNUSED** | Field exists but **never read/displayed**. Only `id` and `last_activity_at` are used. | **Remove** (High) |

#### ChatbotMessage Table
| Field | Status | Evidence | Recommendation |
|-------|--------|----------|----------------|
| `body` | **UNUSED** | Field exists but **never accessed**. | **Remove** (High) |
| `role` | **UNUSED** | Field exists but **never accessed**. | **Remove** (High) |
| `tokens_used` | **USED** | Written in ChatbotController and providers. | **Keep** |

#### ChatMessage Table
| Field | Status | Evidence | Recommendation |
|-------|--------|----------|----------------|
| `attachment` | **UNUSED** | Field exists but **never used** in ChatMessageController. | **Remove** (High) |
| `sent_at` | **WRITE-ONLY** | Written but **never read/displayed**. | **Review** (Medium) |
| `edited_at` | **WRITE-ONLY** | Written but **never read/displayed**. | **Review** (Medium) |

#### Notification Table
| Field | Status | Evidence | Recommendation |
|-------|--------|----------|----------------|
| `is_active` | **PARTIALLY USED** | Used in scope `ready()` but **not checked** in controllers. | **Review** (Medium) |
| `type` | **RARELY USED** | Only filtered in admin controllers. | **Review** (Medium) |
| `priority` | **RARELY USED** | Only filtered in admin controllers. | **Review** (Medium) |
| `metadata` | **WRITE-ONLY** | Written but **never read/used**. | **Review** (Medium) |

#### ChatbotSetting Table
| Field | Status | Evidence | Recommendation |
|-------|--------|----------|----------------|
| `model_name` | **UNUSED** | Validated but **never used** in providers. | **Remove** (High) |
| `is_enabled` | **UNUSED** | Field exists but **never checked** in ChatbotController or providers. | **Review** (Medium) |
| `provider` | **USED** | Used in ChatbotSetting::getSettings(). | **Keep** |
| `system_prompt_template` | **USED** | Used in ChatbotReplyService. | **Keep** |
| `updated_by` | **UNUSED** | Relationship defined but **never accessed**. | **Remove** (Medium) |

#### LinkedAccount Table
| Field | Status | Evidence | Recommendation |
|-------|--------|----------|----------------|
| `provider_email` | **WRITE-ONLY** | Written in SocialAuthController but **never read**. | **Review** (Medium) |
| `avatar_url` | **WRITE-ONLY** | Written in SocialAuthController but **never read**. User uses `profile_picture` instead. | **Review** (Medium) |
| `access_token` | **UNUSED** | Field exists but **never accessed**. | **Review** (Low - may be needed for OAuth refresh) |
| `refresh_token` | **UNUSED** | Field exists but **never accessed**. | **Review** (Low - may be needed for OAuth refresh) |
| `expires_at` | **UNUSED** | Field exists but **never accessed**. | **Review** (Low - may be needed for OAuth refresh) |

#### RoadmapEnrollment Table
| Field | Status | Evidence | Recommendation |
|-------|--------|----------|----------------|
| `status` | **USED** | Used in EnrollmentController and AdminChatModerationController. | **Keep** |
| `started_at` | **USED** | Used in EnrollmentController and resources. | **Keep** |
| `completed_at` | **USED** | Used in EnrollmentController and resources. | **Keep** |

### 4.2 Relationships

| Relationship | Status | Evidence | Recommendation |
|--------------|--------|----------|----------------|
| `User::settings()` | **UNUSED** | Defined but **never accessed**. | **Remove** (High) |
| `Setting::modifiedBy()` | **UNUSED** | Defined but **never accessed**. | **Remove** (High) |
| `ChatbotSetting::updater()` | **UNUSED** | Defined but **never accessed**. | **Remove** (Medium) |
| `Announcement::creator()` | **USED** | Used in AdminAnnouncementController and AnnouncementController. | **Keep** |
| `ChatModeration::moderator()` | **USED** | Used in ChatModerationController (line 67). | **Keep** |
| `AdminCreationLog::creator()` | **USED** | Used in AdminCreationRateLimitService. | **Keep** |
| `AdminCreationLog::createdUser()` | **UNUSED** | Defined but **never accessed**. | **Review** (Medium) |

### 4.3 Controllers

| Controller | Status | Evidence | Recommendation |
|------------|--------|----------|----------------|
| `CommunityController` | **USED** | Route in `routes/api.php:119`. | **Keep** |
| `ComingSoonController` | **USED** | Used for placeholder pages. | **Keep** (temporary) |
| `CreateRegularAdminController` | **USED** | Route in `routes/web.php:65-66`. | **Keep** |
| `RoadmapController` (Admin API) | **USED** | Routes in `routes/api.php:347-350`. | **Keep** |
| `AdminAnnouncementController` | **USED** | Routes in `routes/api.php:275-279`. | **Keep** |
| `AnnouncementController` (Web) | **USED** | Route in `routes/web.php:35`. | **Keep** |
| `AdminQuizController` | **USED** | Routes in `routes/api.php:378-380`. | **Keep** |
| `AdminChallengeController` | **USED** | Routes in `routes/api.php:389-392`. | **Keep** |
| `AdminChatModerationController` | **USED** | Routes in `routes/api.php:291-295`. | **Keep** |
| `ChatModerationController` (Web) | **USED** | Routes in `routes/web.php:44-49`. | **Keep** |
| `AdminNotificationApiController` | **USED** | Routes in `routes/api.php:284-287`. | **Keep** |
| `NotificationController` (Web) | **USED** | Route in `routes/web.php:39`. | **Keep** |
| `AdminRoadmapReadController` | **USED** | Routes in `routes/api.php:309-310`. | **Keep** |
| `AdminContentReadController` | **USED** | Routes in `routes/api.php:312-332`. | **Keep** |
| `RoadmapWebController` | **USED** | Route in `routes/web.php:95`. | **Keep** |
| All Web Controllers (LearningUnit, Lesson, etc.) | **USED** | Routes in `routes/web.php:100-132`. | **Keep** |

### 4.4 Services

| Service | Status | Evidence | Recommendation |
|---------|--------|----------|----------------|
| `AdminCreationRateLimitService` | **USED** | Used in CreateTechAdminController, CreateRegularAdminController, CreateAdminPage. | **Keep** |

### 4.5 Models

| Model | Status | Evidence | Recommendation |
|-------|--------|----------|----------------|
| `Setting` | **UNUSED** | Model exists but **no usage** in controllers/services. | **Remove** (High) |
| `AdminCreationLog` | **USED** | Used in AdminCreationRateLimitService. | **Keep** |

### 4.6 Filament Admin Panel

| Component | Status | Evidence | Recommendation |
|-----------|--------|----------|----------------|
| Filament Resources | **USED** | 40+ Filament resources exist and are registered. | **Keep** |
| Filament Pages | **USED** | CreateAdminPage, Dashboard exist. | **Keep** |
| Filament Widgets | **USED** | CustomAccountWidget exists. | **Keep** |
| `User::getFilamentName()` | **USED** | Used by Filament. | **Keep** |
| `User::canAccessPanel()` | **USED** | Used by Filament. | **Keep** |

---

## 5. Risk Assessment & Recommendations

### 5.1 High Confidence Removals (Safe to Remove)

1. **`users.is_admin` column**
   - **Risk:** Low - System uses `role` field instead
   - **Migration Plan:** Create migration to drop column after confirming no legacy code depends on it
   - **Evidence:** Only in migration history and model cast

2. **`Setting` model + `settings` table**
   - **Risk:** Medium - Table may contain data
   - **Migration Plan:** 
     - Check if table has data: `SELECT COUNT(*) FROM settings;`
     - If empty: Drop table and model
     - If has data: Migrate to `system_settings` if needed, then drop
   - **Evidence:** No usage found in codebase

3. **`User::settings()` relationship**
   - **Risk:** Low - Just remove relationship method
   - **Evidence:** Never accessed

4. **`Setting::modifiedBy()` relationship**
   - **Risk:** Low - Just remove relationship method
   - **Evidence:** Never accessed

5. **`ChatbotSession::title` field**
   - **Risk:** Low - Field not used
   - **Migration Plan:** Drop column if no data needed
   - **Evidence:** Never read/displayed

6. **`ChatbotMessage::body` field**
   - **Risk:** Low - Field not used
   - **Migration Plan:** Drop column if no data needed
   - **Evidence:** Never accessed

7. **`ChatbotMessage::role` field**
   - **Risk:** Low - Field not used
   - **Migration Plan:** Drop column if no data needed
   - **Evidence:** Never accessed

8. **`ChatMessage::attachment` field**
   - **Risk:** Low - Field not used
   - **Migration Plan:** Drop column if no data needed
   - **Evidence:** Never used in controller

9. **`ChatbotSetting::model_name` field**
   - **Risk:** Low - Field not used
   - **Migration Plan:** Drop column
   - **Evidence:** Validated but never used in providers

### 5.2 Medium Confidence Removals (Review Before Removing)

1. **`ChatMessage::sent_at` and `edited_at`**
   - **Risk:** Low - May be needed for audit trail
   - **Recommendation:** Review if audit trail is required. If not, remove.

2. **`Notification::is_active`, `type`, `priority`, `metadata`**
   - **Risk:** Medium - May be needed for future features
   - **Recommendation:** Review business requirements. If not needed, remove.

3. **`ChatbotSetting::is_enabled`**
   - **Risk:** Medium - May be needed for feature toggle
   - **Recommendation:** Review if feature toggle is needed. If not, remove.

4. **`ChatbotSetting::updater()` relationship**
   - **Risk:** Low - Just remove relationship method
   - **Recommendation:** Remove if audit trail not needed.

5. **`LinkedAccount::provider_email`, `avatar_url`**
   - **Risk:** Low - Written but never read
   - **Recommendation:** Remove if not needed for OAuth flow.

6. **`LinkedAccount::access_token`, `refresh_token`, `expires_at`**
   - **Risk:** Medium - May be needed for OAuth token refresh
   - **Recommendation:** Review OAuth implementation. If tokens are not refreshed, remove.

7. **`AdminCreationLog::createdUser()` relationship**
   - **Risk:** Low - Just remove if not needed
   - **Recommendation:** Remove if audit trail doesn't need created user info.

### 5.3 Low Confidence / Keep (Requires Further Investigation)

1. **Filament Admin Panel**
   - **Status:** Keep - Used for admin content management
   - **Note:** May have some unused resources, but overall system is used.

2. **All Web Controllers**
   - **Status:** Keep - All are referenced in routes

3. **All API Controllers**
   - **Status:** Keep - All are referenced in routes

---

## 6. Deletion Plan (Phased Approach)

### Phase 0: Add Tests/Guards (Baseline)
**Goal:** Establish baseline before removals

**Tasks:**
1. Run full test suite: `php artisan test`
2. Document current database schema: `php artisan schema:dump`
3. Create backup: `php artisan backup:run` (if backup package installed)
4. Document all API endpoints: Review Postman collections
5. Document all web routes: `php artisan route:list`

**Verification:**
- All tests pass
- Schema dump created
- Backup created (if applicable)
- Route list documented

---

### Phase 1: Remove Obvious Dead Code (High Confidence)

**Estimated Time:** 2-4 hours  
**Risk Level:** Low

#### 1.1 Remove Unused Database Fields

**Migration:** `2026_02_XX_remove_unused_fields.php`

```php
Schema::table('users', function (Blueprint $table) {
    $table->dropColumn('is_admin'); // Deprecated, use role instead
});

Schema::table('chatbot_sessions', function (Blueprint $table) {
    $table->dropColumn('title'); // Never used
});

Schema::table('chatbot_messages', function (Blueprint $table) {
    $table->dropColumn('body'); // Never used
    $table->dropColumn('role'); // Never used
});

Schema::table('chat_messages', function (Blueprint $table) {
    $table->dropColumn('attachment'); // Never used
});

Schema::table('chatbot_settings', function (Blueprint $table) {
    $table->dropColumn('model_name'); // Never used
});
```

**Files to Update:**
- `app/Models/User.php` - Remove `is_admin` from casts
- `app/Models/ChatbotSession.php` - Remove `title` from fillable
- `app/Models/ChatbotMessage.php` - Remove `body`, `role` from fillable
- `app/Models/ChatMessage.php` - Remove `attachment` from fillable
- `app/Models/ChatbotSetting.php` - Remove `model_name` from fillable
- `app/Http/Controllers/Admin/SmartTeacherController.php` - Remove `model_name` validation

**Verification:**
- Run migrations: `php artisan migrate`
- Run tests: `php artisan test`
- Check admin panel: Verify no errors
- Check API: Verify no errors

#### 1.2 Remove Unused Models & Relationships

**Files to Delete:**
- `app/Models/Setting.php` (if `settings` table is empty)

**Files to Update:**
- `app/Models/User.php` - Remove `settings()` relationship (line 61-63)
- `app/Models/ChatbotSetting.php` - Remove `updater()` relationship (line 51-53)

**Migration:** `2026_02_XX_drop_settings_table.php` (if table is empty)

```php
Schema::dropIfExists('settings');
```

**Verification:**
- Run migrations: `php artisan migrate`
- Run tests: `php artisan test`
- Check for any references: `grep -r "Setting::" app/`
- Check for any references: `grep -r "->settings()" app/`

---

### Phase 2: Refactors That Require Small Changes (Medium Confidence)

**Estimated Time:** 4-6 hours  
**Risk Level:** Medium

#### 2.1 Review Write-Only Fields

**Fields to Review:**
- `chat_messages.sent_at` - Remove if audit trail not needed
- `chat_messages.edited_at` - Remove if audit trail not needed
- `notifications.is_active` - Remove if all notifications are active
- `notifications.type` - Keep if filtering is needed, remove if not
- `notifications.priority` - Keep if filtering is needed, remove if not
- `notifications.metadata` - Remove if not needed for future features
- `linked_accounts.provider_email` - Remove if not needed
- `linked_accounts.avatar_url` - Remove if not needed (user uses `profile_picture`)
- `linked_accounts.access_token`, `refresh_token`, `expires_at` - Review OAuth implementation

**Decision Process:**
1. Check database for existing data
2. Review business requirements
3. If not needed, create migration to drop columns
4. Update models and controllers

**Verification:**
- Run migrations: `php artisan migrate`
- Run tests: `php artisan test`
- Test OAuth flow (if tokens removed)
- Test notification system (if fields removed)

#### 2.2 Remove Unused Relationships

**Relationships to Remove:**
- `AdminCreationLog::createdUser()` - Remove if audit trail doesn't need it

**Files to Update:**
- `app/Models/AdminCreationLog.php` - Remove `createdUser()` method (line 32-34)

**Verification:**
- Run tests: `php artisan test`
- Check for any references: `grep -r "->createdUser()" app/`

---

### Phase 3: Risky Removals (Only If Approved)

**Estimated Time:** 6-8 hours  
**Risk Level:** High

#### 3.1 Review Filament Resources

**Action:** Audit each Filament resource to identify unused ones

**Process:**
1. List all Filament resources: `ls app/Filament/Resources/`
2. Check if each resource is accessible via Filament panel
3. Check if each resource has routes defined
4. Remove unused resources

**Verification:**
- Access Filament admin panel
- Verify all resources are accessible
- Run tests: `php artisan test`

#### 3.2 Review Duplicate Controllers

**Potential Duplicates:**
- `AdminAnnouncementController` (API) vs `AnnouncementController` (Web) - **Both used, keep**
- `AdminChatModerationController` (API) vs `ChatModerationController` (Web) - **Both used, keep**
- `AdminNotificationApiController` (API) vs `NotificationController` (Web) - **Both used, keep**

**Conclusion:** No duplicates found. All controllers serve different purposes (API vs Web).

---

## 7. Verification Checklist

### Pre-Removal Checks

- [ ] Run full test suite: `php artisan test`
- [ ] Create database backup
- [ ] Document current schema: `php artisan schema:dump`
- [ ] Document all routes: `php artisan route:list > routes_backup.txt`
- [ ] Check for data in tables to be dropped: `SELECT COUNT(*) FROM settings;`
- [ ] Review Postman collections for API usage

### Post-Removal Checks

- [ ] Run migrations: `php artisan migrate`
- [ ] Run tests: `php artisan test`
- [ ] Check admin panel (AdminLTE): Verify no errors
- [ ] Check Filament admin panel: Verify no errors
- [ ] Test API endpoints: Use Postman collections
- [ ] Test web routes: Manual testing
- [ ] Check logs: `tail -f storage/logs/laravel.log`
- [ ] Verify database: Check table structure

### Artisan Commands to Run

```bash
# Test suite
php artisan test

# Database checks
php artisan migrate:status
php artisan db:show

# Route checks
php artisan route:list
php artisan route:cache

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Schema dump
php artisan schema:dump
```

### Test Cases to Confirm Nothing Broke

1. **User Authentication:**
   - Login as admin
   - Login as tech_admin
   - Login as user
   - Social login (Google/GitHub)

2. **Admin Panel (AdminLTE):**
   - Access dashboard
   - Create/Edit/Delete announcements
   - Create/Edit/Delete notifications
   - User management
   - Chat moderation

3. **Admin Panel (Filament):**
   - Access Filament panel
   - Create/Edit/Delete roadmaps
   - Create/Edit/Delete learning units
   - Create/Edit/Delete lessons, sub-lessons
   - Create/Edit/Delete resources, quizzes, challenges

4. **API Endpoints:**
   - Test all API routes from Postman collections
   - Verify authentication works
   - Verify authorization works

5. **Database Integrity:**
   - Check foreign key constraints
   - Verify cascade deletes work
   - Check data integrity

---

## 8. Summary Table

| Item | Type | Confidence | Risk | Recommendation | Files Affected |
|------|------|------------|------|----------------|----------------|
| `users.is_admin` | Column | High | Low | Remove | `app/Models/User.php`, migration |
| `settings` table | Table | High | Medium | Remove | `app/Models/Setting.php`, `app/Models/User.php` |
| `User::settings()` | Relationship | High | Low | Remove | `app/Models/User.php` |
| `Setting::modifiedBy()` | Relationship | High | Low | Remove | `app/Models/Setting.php` |
| `chatbot_sessions.title` | Column | High | Low | Remove | `app/Models/ChatbotSession.php`, migration |
| `chatbot_messages.body` | Column | High | Low | Remove | `app/Models/ChatbotMessage.php`, migration |
| `chatbot_messages.role` | Column | High | Low | Remove | `app/Models/ChatbotMessage.php`, migration |
| `chat_messages.attachment` | Column | High | Low | Remove | `app/Models/ChatMessage.php`, migration |
| `chat_messages.sent_at` | Column | Medium | Low | Review | `app/Models/ChatMessage.php`, migration |
| `chat_messages.edited_at` | Column | Medium | Low | Review | `app/Models/ChatMessage.php`, migration |
| `notifications.is_active` | Column | Medium | Low | Review | `app/Models/Notification.php`, migration |
| `notifications.type` | Column | Medium | Low | Review | `app/Models/Notification.php`, migration |
| `notifications.priority` | Column | Medium | Low | Review | `app/Models/Notification.php`, migration |
| `notifications.metadata` | Column | Medium | Low | Review | `app/Models/Notification.php`, migration |
| `chatbot_settings.model_name` | Column | High | Low | Remove | `app/Models/ChatbotSetting.php`, `app/Http/Controllers/Admin/SmartTeacherController.php`, migration |
| `chatbot_settings.is_enabled` | Column | Medium | Low | Review | `app/Models/ChatbotSetting.php`, migration |
| `chatbot_settings.updated_by` | Column | Medium | Low | Review | `app/Models/ChatbotSetting.php`, migration |
| `ChatbotSetting::updater()` | Relationship | Medium | Low | Remove | `app/Models/ChatbotSetting.php` |
| `linked_accounts.provider_email` | Column | Medium | Low | Review | `app/Models/LinkedAccount.php`, migration |
| `linked_accounts.avatar_url` | Column | Medium | Low | Review | `app/Models/LinkedAccount.php`, migration |
| `linked_accounts.access_token` | Column | Low | Medium | Review | `app/Models/LinkedAccount.php`, migration |
| `linked_accounts.refresh_token` | Column | Low | Medium | Review | `app/Models/LinkedAccount.php`, migration |
| `linked_accounts.expires_at` | Column | Low | Medium | Review | `app/Models/LinkedAccount.php`, migration |
| `AdminCreationLog::createdUser()` | Relationship | Medium | Low | Review | `app/Models/AdminCreationLog.php` |

---

## 9. Additional Notes

### 9.1 Filament Admin Panel

The Filament admin panel is **actively used** for content management. All 40+ resources are registered and accessible. The panel provides CRUD interfaces for:
- Roadmaps
- Learning Units
- Lessons
- Sub-Lessons
- Resources
- Quizzes
- Quiz Questions
- Challenges
- Announcements
- Users

**Recommendation:** Keep all Filament resources.

### 9.2 Duplicate Controllers

No true duplicates found. Controllers serve different purposes:
- **API Controllers** (`Admin*Controller`): Handle API requests (JSON responses)
- **Web Controllers** (`*Controller`): Handle web requests (Blade views)

**Recommendation:** Keep all controllers.

### 9.3 ComingSoonController

The `ComingSoonController` is used for placeholder pages for unimplemented features. This is a **temporary** controller that should be removed once all features are implemented.

**Recommendation:** Keep for now, remove when features are implemented.

### 9.4 AdminCreationLog

The `AdminCreationLog` model is used by `AdminCreationRateLimitService` for rate limiting admin creation. The `creator` relationship is used, but the `createdUser` relationship is not.

**Recommendation:** Remove `createdUser()` relationship if not needed for audit trail.

---

## 10. Conclusion

This audit identified **15+ high-confidence removal candidates** and **20+ medium-confidence review items**. The recommended approach is:

1. **Phase 1 (High Confidence):** Remove unused fields and relationships (Low risk, 2-4 hours)
2. **Phase 2 (Medium Confidence):** Review and remove write-only fields (Medium risk, 4-6 hours)
3. **Phase 3 (Low Confidence):** Review Filament resources and complex refactors (High risk, 6-8 hours)

**Total Estimated Cleanup Time:** 12-18 hours

**Expected Benefits:**
- Cleaner codebase
- Reduced maintenance burden
- Improved performance (fewer columns to query)
- Better code clarity

**Next Steps:**
1. Review this report with the team
2. Approve Phase 1 removals
3. Execute Phase 1
4. Test thoroughly
5. Proceed to Phase 2 if Phase 1 is successful

---

**End of Report**

