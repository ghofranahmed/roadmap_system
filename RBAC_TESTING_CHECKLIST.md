# RBAC Testing Checklist - Filament Admin Panel

## Prerequisites

Before testing, ensure you have:
- ✅ Two test accounts created:
  - **Normal Admin**: `role=admin` (e.g., `admin@test.com` / `password`)
  - **Technical Admin**: `role=tech_admin` (e.g., `techadmin@test.com` / `password`)
- ✅ Filament panel accessible at `/admin`
- ✅ Database seeded with test data

---

## Part 1: Normal Admin (role=admin) Testing

### Test 1.1: Access Users Resource ✅

**Steps:**
1. Logout if logged in
2. Login as Normal Admin (`admin@test.com`)
3. Navigate to Filament panel (`/admin`)
4. Check left sidebar navigation

**Expected Results:**
- ✅ Dashboard visible
- ✅ **Users** visible in "User Management" group
- ✅ **Announcements** visible in "User Management" group
- ❌ Content Management resources NOT visible
- ✅ Can click on "Users" → Should load users list page

**Direct URL Test:**
- ✅ Navigate directly to `/admin/users` → Should load successfully
- ✅ Should see users table with columns: ID, Username, Email, Role, Notifications, Created At

---

### Test 1.2: Create Admin User (Normal Admin Only) ✅

**Steps:**
1. While logged in as Normal Admin
2. Navigate to Users → Click "Add Admin" button (top right)
3. Fill form:
   - Username: `newadmin1`
   - Email: `newadmin1@test.com`
   - Role: Check dropdown options
   - Password: `Test123!@#`
   - Password Confirmation: `Test123!@#`
   - Notifications Enabled: ✓ (checked)
4. Submit form

**Expected Results:**
- ✅ Role dropdown shows ONLY: "User" and "Normal Admin" (NO "Technical Admin" option)
- ✅ Form submits successfully
- ✅ Success message appears
- ✅ Redirected to users list
- ✅ New user appears in list with role "Normal Admin" (green badge)
- ✅ User can login with credentials

**Verify Created User:**
- ✅ Check database: `SELECT * FROM users WHERE email = 'newadmin1@test.com'`
- ✅ Role should be: `admin`
- ✅ Password should be hashed (not plain text)

---

### Test 1.3: Attempt to Create Tech Admin (Should Fail) ❌

**Steps:**
1. While logged in as Normal Admin
2. Navigate to Users → Click "Add Admin"
3. Fill form:
   - Username: `techadmin1`
   - Email: `techadmin1@test.com`
   - Role: Check dropdown
   - Password: `Test123!@#`
   - Password Confirmation: `Test123!@#`
4. Try to submit

**Expected Results:**
- ✅ Role dropdown shows ONLY: "User" and "Normal Admin" (NO "Technical Admin")
- ✅ If somehow tech_admin is selected (via browser dev tools manipulation):
  - Form submission fails
  - Error message: "Normal admins cannot assign technical admin role."
  - User is NOT created

**Browser Dev Tools Test (Advanced):**
1. Open browser DevTools (F12)
2. Inspect role dropdown
3. Try to manually change value to `tech_admin`
4. Submit form
5. **Expected:** Server-side validation blocks it with error message

---

### Test 1.4: Edit User ✅

**Steps:**
1. While logged in as Normal Admin
2. Navigate to Users list
3. Find a user (preferably one you created)
4. Click "Edit" action (pencil icon)
5. Modify:
   - Change username (e.g., add "_edited")
   - Change email (e.g., add "_edited")
   - Try to change role dropdown
   - Leave password empty (should not update password)
6. Click "Save"

**Expected Results:**
- ✅ Edit form loads successfully
- ✅ Role dropdown shows: "User" and "Normal Admin" (NO "Technical Admin")
- ✅ If editing a tech_admin user: Role field should be disabled/grayed out
- ✅ Form saves successfully
- ✅ Changes reflected in users list
- ✅ Password unchanged (since left empty)

**Edit Tech Admin User (If Exists):**
- ✅ Find a user with role "Technical Admin"
- ✅ Click Edit
- ✅ Role field should be disabled (cannot change tech_admin role)
- ✅ Other fields editable

---

### Test 1.5: Delete User ✅

**Steps:**
1. While logged in as Normal Admin
2. Navigate to Users list
3. Find a test user (NOT yourself)
4. Click "Delete" action (trash icon)
5. Confirm deletion in modal

**Expected Results:**
- ✅ Delete action visible
- ✅ Confirmation modal appears
- ✅ After confirmation, user deleted
- ✅ Success message appears
- ✅ User removed from list

**Self-Delete Prevention:**
- ✅ Try to delete your own account
- ✅ **Expected:** Error message: "You cannot delete your own account."
- ✅ User NOT deleted

---

### Test 1.6: Revoke Tokens ✅

**Steps:**
1. While logged in as Normal Admin
2. Navigate to Users list
3. Find a user (preferably one with active sessions)
4. Click "Revoke Tokens" action (key icon)
5. Confirm in modal

**Expected Results:**
- ✅ "Revoke Tokens" action visible
- ✅ Confirmation modal appears with message: "This will log out the user from all devices. Are you sure?"
- ✅ After confirmation, tokens revoked
- ✅ Success notification: "All tokens for {username} have been revoked successfully."
- ✅ User logged out from all devices (if they had active sessions)

---

### Test 1.7: Cannot Access CreateAdminPage ❌

**Steps:**
1. While logged in as Normal Admin
2. Check left sidebar navigation

**Expected Results:**
- ❌ "Create Admin" page NOT visible in navigation
- ❌ No "System" group visible (or System group exists but empty)

**Direct URL Test:**
- ❌ Navigate directly to `/admin/create-admin`
- ✅ **Expected:** 403 Forbidden error OR redirect to dashboard
- ✅ Error message: "Only Technical Admins can access this page."

---

## Part 2: Technical Admin (role=tech_admin) Testing

### Test 2.1: Cannot Access Users Resource ❌

**Steps:**
1. Logout
2. Login as Technical Admin (`techadmin@test.com`)
3. Navigate to Filament panel (`/admin`)
4. Check left sidebar navigation

**Expected Results:**
- ✅ Dashboard visible
- ❌ **Users** NOT visible in navigation
- ❌ **Announcements** NOT visible in navigation
- ✅ Content Management resources visible (Roadmaps, Learning Units, etc.)
- ✅ "Create Admin" visible in "System" group

**Direct URL Test:**
- ❌ Navigate directly to `/admin/users`
- ✅ **Expected:** 403 Forbidden error OR redirect
- ✅ Error message: "Unauthorized" or similar

**Direct URL Tests (All UserResource Pages):**
- ❌ `/admin/users` → 403
- ❌ `/admin/users/create` → 403
- ❌ `/admin/users/1/edit` → 403
- ✅ All should return 403 Forbidden

---

### Test 2.2: Can Access CreateAdminPage ✅

**Steps:**
1. While logged in as Technical Admin
2. Check left sidebar navigation
3. Look for "Create Admin" in "System" group
4. Click on "Create Admin"

**Expected Results:**
- ✅ "Create Admin" visible in navigation (System group)
- ✅ Page loads successfully
- ✅ Form displays with fields:
  - Username
  - Email
  - Admin Role (dropdown)
  - Password
  - Password Confirmation
  - Enable Notifications

**Direct URL Test:**
- ✅ Navigate directly to `/admin/create-admin`
- ✅ Page loads successfully (no 403 error)

---

### Test 2.3: Create Normal Admin User ✅

**Steps:**
1. While logged in as Technical Admin
2. Navigate to "Create Admin" page
3. Fill form:
   - Username: `newadmin2`
   - Email: `newadmin2@test.com`
   - Role: Select "Normal Admin"
   - Password: `Test123!@#`
   - Password Confirmation: `Test123!@#`
   - Enable Notifications: ✓
4. Click "Create Admin" button

**Expected Results:**
- ✅ Role dropdown shows: "Normal Admin" and "Technical Admin" (NO "User" option)
- ✅ Form submits successfully
- ✅ Success notification: "Admin Created Successfully - Admin user 'newadmin2' has been created with role: Normal Admin"
- ✅ Form resets (ready for next creation)
- ✅ User created in database with role `admin`
- ✅ User can login with credentials

**Verify in Database:**
```sql
SELECT id, username, email, role FROM users WHERE email = 'newadmin2@test.com';
-- Expected: role = 'admin'
```

---

### Test 2.4: Create Technical Admin User ✅

**Steps:**
1. While logged in as Technical Admin
2. Navigate to "Create Admin" page
3. Fill form:
   - Username: `newtechadmin1`
   - Email: `newtechadmin1@test.com`
   - Role: Select "Technical Admin"
   - Password: `Test123!@#`
   - Password Confirmation: `Test123!@#`
   - Enable Notifications: ✓
4. Click "Create Admin" button

**Expected Results:**
- ✅ Form submits successfully
- ✅ Success notification: "Admin Created Successfully - Admin user 'newtechadmin1' has been created with role: Technical Admin"
- ✅ User created in database with role `tech_admin`
- ✅ New tech_admin can login and access tech_admin features

**Verify in Database:**
```sql
SELECT id, username, email, role FROM users WHERE email = 'newtechadmin1@test.com';
-- Expected: role = 'tech_admin'
```

---

### Test 2.5: Cannot Create User Role (Validation) ❌

**Steps:**
1. While logged in as Technical Admin
2. Navigate to "Create Admin" page
3. Open browser DevTools (F12)
4. Inspect role dropdown
5. Try to manually set value to `user` (if possible)
6. Or try to submit with invalid role via form manipulation

**Expected Results:**
- ✅ Role dropdown does NOT show "User" option (UI level)
- ✅ If `user` role is somehow submitted:
  - Form validation fails
  - Error message: "Regular users cannot be created from this page. Only admin roles are allowed."
  - User NOT created

**Server-Side Test (API/Form Manipulation):**
- Try to POST to form with `role=user` in data
- **Expected:** Validation error prevents creation

---

### Test 2.6: Cannot Access User Management Features ❌

**Steps:**
1. While logged in as Technical Admin
2. Try to access various user management URLs directly

**Expected Results:**
- ❌ `/admin/users` → 403 Forbidden
- ❌ `/admin/users/create` → 403 Forbidden
- ❌ `/admin/users/1` → 403 Forbidden
- ❌ `/admin/users/1/edit` → 403 Forbidden
- ❌ `/admin/announcements` → 403 Forbidden (if AnnouncementResource also restricted)

**Verify Policy Enforcement:**
- All UserResource pages should be blocked
- Error should be consistent (403 or redirect)

---

## Part 3: Content Resources Verification (Tech Admin Only)

### Test 3.1: Normal Admin Cannot Access Content CRUD ❌

**Steps:**
1. Login as Normal Admin
2. Check navigation sidebar
3. Try to access content management URLs directly

**Expected Results:**
- ❌ Content Management group NOT visible in navigation
- ❌ `/admin/roadmaps` → 403 or not accessible
- ❌ `/admin/roadmaps/create` → 403
- ❌ `/admin/learning-units` → 403
- ❌ All content CRUD resources blocked

**Note:** Normal admin should have read-only access via API only (not Filament)

---

### Test 3.2: Technical Admin Can Access Content CRUD ✅

**Steps:**
1. Login as Technical Admin
2. Check navigation sidebar
3. Verify content resources are visible

**Expected Results:**
- ✅ Content Management group visible
- ✅ Roadmaps visible and accessible
- ✅ Learning Units visible and accessible
- ✅ Lessons visible and accessible
- ✅ Sub-Lessons visible and accessible
- ✅ Resources visible and accessible
- ✅ Quizzes visible and accessible
- ✅ Quiz Questions visible and accessible
- ✅ Challenges visible and accessible

**Quick CRUD Test:**
- ✅ Can create new roadmap
- ✅ Can edit existing roadmap
- ✅ Can delete roadmap (if no active enrollments)
- ✅ Can toggle active status

---

## Part 4: Quick Sanity Check - API Endpoints

### Test 4.1: Verify API Endpoints Still Protected ✅

**Purpose:** Ensure Filament changes didn't accidentally expose API endpoints

**Test Endpoints:**

#### Normal Admin API Endpoints (Should Work):
```bash
# User Management (role:admin required)
GET /api/v1/admin/users
GET /api/v1/admin/users/{id}
PUT /api/v1/admin/users/{id}
DELETE /api/v1/admin/users/{id}
POST /api/v1/admin/users/{id}/revoke-tokens

# Announcements (role:admin required)
GET /api/v1/admin/announcements
POST /api/v1/admin/announcements
PUT /api/v1/admin/announcements/{id}
DELETE /api/v1/admin/announcements/{id}

# Chat Moderation (role:admin required)
POST /api/v1/admin/roadmaps/{id}/chat/mute
POST /api/v1/admin/roadmaps/{id}/chat/ban
```

**Expected Results:**
- ✅ All endpoints return 200/201 (success) when authenticated as Normal Admin
- ✅ All endpoints return 403 (forbidden) when authenticated as Technical Admin
- ✅ All endpoints return 401 (unauthorized) when not authenticated

#### Technical Admin API Endpoints (Should Work):
```bash
# Content CRUD (role:tech_admin required)
POST /api/v1/admin/roadmaps
PUT /api/v1/admin/roadmaps/{id}
DELETE /api/v1/admin/roadmaps/{id}
POST /api/v1/admin/roadmaps/{roadmapId}/units
PUT /api/v1/admin/units/{unitId}
DELETE /api/v1/admin/units/{unitId}
POST /api/v1/admin/quizzes
POST /api/v1/admin/challenges
```

**Expected Results:**
- ✅ All endpoints return 200/201 (success) when authenticated as Technical Admin
- ✅ All endpoints return 403 (forbidden) when authenticated as Normal Admin
- ✅ All endpoints return 401 (unauthorized) when not authenticated

#### Shared Read-Only Endpoints (Both Admins):
```bash
# Content Read-Only (role:admin,tech_admin)
GET /api/v1/admin/roadmaps
GET /api/v1/admin/roadmaps/{id}
GET /api/v1/admin/roadmaps/{roadmapId}/units
GET /api/v1/admin/units/{unitId}
GET /api/v1/admin/quizzes/{quizId}
```

**Expected Results:**
- ✅ All endpoints return 200 (success) when authenticated as Normal Admin
- ✅ All endpoints return 200 (success) when authenticated as Technical Admin
- ✅ All endpoints return 401 (unauthorized) when not authenticated

---

### Test 4.2: Verify No New Endpoints Exposed ❌

**Steps:**
1. Check if any new API routes were added
2. Verify existing routes still have proper middleware

**Expected Results:**
- ✅ No new routes added to `routes/api.php`
- ✅ All admin routes still have `role:admin` or `role:tech_admin` middleware
- ✅ User creation endpoint still missing (as per original design - only via Filament)

**Quick Route Check:**
```bash
# Run in terminal
php artisan route:list --path=admin
```

**Expected:**
- ✅ All routes show proper middleware
- ✅ No unexpected routes exposed

---

## Part 5: Edge Cases & Security Tests

### Test 5.1: Role Escalation Prevention ✅

**Scenario:** Normal admin tries to escalate privileges

**Steps:**
1. Login as Normal Admin
2. Create a new admin user
3. Try to edit that user and change role to `tech_admin`

**Expected Results:**
- ✅ Role dropdown does NOT show "Technical Admin" option
- ✅ If somehow tech_admin is selected (dev tools):
  - Form submission fails
  - Error: "Normal admins cannot assign technical admin role."
  - Role NOT changed

---

### Test 5.2: Password Validation ✅

**Test Weak Passwords:**
1. Try to create user with password: `123456`
2. Try to create user with password: `password`
3. Try to create user with password: `Test123` (no symbols)

**Expected Results:**
- ❌ All weak passwords rejected
- ✅ Error messages appear for each validation rule
- ✅ User NOT created

**Test Strong Password:**
1. Create user with password: `Test123!@#`
2. **Expected:** ✅ User created successfully

---

### Test 5.3: Unique Email/Username Validation ✅

**Steps:**
1. Create user with email: `test@test.com`
2. Try to create another user with same email: `test@test.com`

**Expected Results:**
- ❌ Second creation fails
- ✅ Error: "This email is already registered."
- ✅ User NOT created

**Repeat for username:**
- ✅ Same validation applies

---

### Test 5.4: Session Persistence ✅

**Steps:**
1. Login as Normal Admin
2. Perform various actions (create, edit, delete users)
3. Check if session remains active
4. Logout
5. Login as Technical Admin
6. Verify correct navigation appears

**Expected Results:**
- ✅ Session persists across actions
- ✅ Navigation updates correctly based on role
- ✅ No unauthorized access after role change

---

## Part 6: Responsive Design Check (Optional)

### Test 6.1: Mobile View ✅

**Steps:**
1. Open Filament panel on mobile device (or browser dev tools mobile view)
2. Test navigation sidebar
3. Test forms (CreateAdminPage, UserResource forms)
4. Test tables (users list)

**Expected Results:**
- ✅ Sidebar collapses on mobile
- ✅ Forms stack vertically
- ✅ Tables are scrollable
- ✅ Buttons are touch-friendly (min 44px)
- ✅ All features accessible

---

## Quick Sanity Check Summary

### ✅ Must Pass (Critical):
- [ ] Normal admin can access Users resource
- [ ] Normal admin cannot create tech_admin
- [ ] Tech admin cannot access Users resource
- [ ] Tech admin can access CreateAdminPage
- [ ] Tech admin can create admin/tech_admin
- [ ] Content resources remain tech_admin only
- [ ] API endpoints still properly protected

### ⚠️ Should Pass (Important):
- [ ] Password validation works
- [ ] Unique email/username validation works
- [ ] Role escalation prevention works
- [ ] Revoke tokens works
- [ ] Delete user works (except self)

---

## Test Results Template

```
Date: ___________
Tester: ___________

Normal Admin Tests:
[ ] Test 1.1: Access Users Resource
[ ] Test 1.2: Create Admin User
[ ] Test 1.3: Cannot Create Tech Admin
[ ] Test 1.4: Edit User
[ ] Test 1.5: Delete User
[ ] Test 1.6: Revoke Tokens
[ ] Test 1.7: Cannot Access CreateAdminPage

Technical Admin Tests:
[ ] Test 2.1: Cannot Access Users Resource
[ ] Test 2.2: Can Access CreateAdminPage
[ ] Test 2.3: Create Normal Admin
[ ] Test 2.4: Create Technical Admin
[ ] Test 2.5: Cannot Create User Role
[ ] Test 2.6: Cannot Access User Management

Content Resources:
[ ] Test 3.1: Normal Admin Cannot Access Content CRUD
[ ] Test 3.2: Technical Admin Can Access Content CRUD

API Sanity Check:
[ ] Test 4.1: API Endpoints Still Protected
[ ] Test 4.2: No New Endpoints Exposed

Edge Cases:
[ ] Test 5.1: Role Escalation Prevention
[ ] Test 5.2: Password Validation
[ ] Test 5.3: Unique Email/Username
[ ] Test 5.4: Session Persistence

Overall Status: ✅ PASS / ❌ FAIL
Notes: ________________________________
```

---

## Troubleshooting

### If Normal Admin Cannot Access Users:
- Check: `UserPolicy::viewAny()` returns `isNormalAdmin()`
- Check: `UserResource::canViewAny()` returns `isNormalAdmin()`
- Check: User role in database is exactly `admin` (not `tech_admin`)

### If Tech Admin Can Access Users:
- Check: `UserPolicy::viewAny()` does NOT return `isAnyAdmin()`
- Check: `UserResource::canViewAny()` explicitly checks `isNormalAdmin()`
- Check: Policy is registered in `AppServiceProvider`

### If CreateAdminPage Not Visible:
- Check: `CreateAdminPage::canAccess()` returns `isTechAdmin()`
- Check: Page is in `app/Filament/Pages/` directory
- Check: Filament auto-discovers pages

### If API Endpoints Broken:
- Check: Routes still have proper middleware
- Check: No syntax errors in route files
- Check: Controllers still have constructor checks

---

**End of Testing Checklist**

