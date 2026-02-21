# Postman Feature Testing Guide — Roadmap System API

> **Base URL:** `http://roadmap_system.test/api/v1`
> **Generated from:** actual routes (`routes/api.php`), controllers, and FormRequest validators.

---

## 1 — Feature → Route Mapping Table

| # | Feature | Method | Full Path | Auth |
|---|---------|--------|-----------|------|
| 1 | Update Profile (user info) | `PUT` | `/api/v1/update-account` | Bearer (user) |
| 2 | Update Profile Picture | `POST` | `/api/v1/update-profile-picture` | Bearer (user) |
| 3 | Delete Profile Picture | `DELETE` | `/api/v1/delete-profile-picture` | Bearer (user) |
| 4 | Forgot Password | `POST` | `/api/v1/auth/forgot-password` | Guest (public) |
| 5 | Verify Reset Token | `POST` | `/api/v1/auth/verify-reset-token` | Guest (public) |
| 6 | Reset Password | `POST` | `/api/v1/auth/reset-password` | Guest (public) |
| 7 | Get Reset Attempts | `GET` | `/api/v1/auth/reset-attempts?email=` | Guest (public) |
| 8 | Delete Account (self) | `DELETE` | `/api/v1/delete-account` | Bearer (user) |
| 9 | Delete Account (admin) | `DELETE` | `/api/v1/admin/users/{id}` | Bearer (admin) |
| 10 | Unenroll from Roadmap | `DELETE` | `/api/v1/roadmaps/{id}/unenroll` | Bearer (user) |
| 11 | Reset Lesson Tracking | `DELETE` | `/api/v1/lessons/{lessonId}/track/reset` | Bearer (enrolled user) |
| 12 | Update Enrollment Status | `PATCH` | `/api/v1/me/enrollments/{roadmapId}/status` | Bearer (user) |

> **MISSING: "Reset entire roadmap progress" (bulk reset all lesson trackings for a roadmap in one call)**
> There is no single endpoint that resets ALL progress for a roadmap enrollment. The existing behavior is:
> - **Unenroll + Re-enroll** (DELETE `/roadmaps/{id}/unenroll` then POST `/roadmaps/{id}/enroll`) — deletes the enrollment row; lesson trackings remain orphaned.
> - **Reset individual lesson** (DELETE `/lessons/{lessonId}/track/reset`) — sets `is_complete=false` for one lesson.
>
> **Proposed endpoint (not yet implemented):**
> ```
> DELETE /api/v1/me/enrollments/{roadmapId}/reset-progress
> Auth: Bearer (user, enrolled)
> Behavior: Reset all lesson_trackings where the lessons belong to this roadmap for the current user (set is_complete=false).
> Response: { success: true, message: "تم إعادة تعيين التقدم", data: { lessons_reset: 12 } }
> ```

---

## 2 — Environment Variables

Create a Postman Environment named **`Roadmap System - Local`** with these variables:

| Variable | Initial Value | Description |
|----------|---------------|-------------|
| `base_url` | `http://roadmap_system.test/api/v1` | API base URL |
| `token_user` | *(empty)* | Set after user login |
| `token_admin` | *(empty)* | Set after admin login |
| `user_id` | *(empty)* | Set after GET /profile |
| `user_email` | `testuser@example.com` | Test user email |
| `user_password` | `Test@1234` | Test user password |
| `admin_email` | `admin@example.com` | Admin email |
| `admin_password` | `Admin@1234` | Admin password |
| `roadmap_id` | *(empty)* | Set from enrollments |
| `lesson_id` | *(empty)* | Set from lesson listing |

### Common Headers (apply to all requests)

| Header | Value |
|--------|-------|
| `Accept` | `application/json` |
| `Content-Type` | `application/json` *(except file uploads)* |
| `Authorization` | `Bearer {{token_user}}` or `Bearer {{token_admin}}` *(protected routes only)* |

---

## 3 — Authentication Setup Requests

### 3.1 Login as Normal User

```
POST {{base_url}}/auth/login
```

**Headers:**
| Header | Value |
|--------|-------|
| Accept | application/json |
| Content-Type | application/json |

**Body (raw JSON):**
```json
{
    "email": "{{user_email}}",
    "password": "{{user_password}}"
}
```

**Expected Response (200):**
```json
{
    "success": true,
    "message": "تم تسجيل الدخول بنجاح",
    "data": {
        "user": {
            "id": 1,
            "username": "testuser",
            "email": "testuser@example.com",
            "role": "user",
            ...
        },
        "token": "1|abcdef123456...",
        "token_type": "Bearer"
    }
}
```

**Postman Tests script:**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

const json = pm.response.json();

pm.test("Login successful", function () {
    pm.expect(json.success).to.be.true;
    pm.expect(json.data.token).to.be.a("string");
});

if (json.success && json.data.token) {
    pm.environment.set("token_user", json.data.token);
    pm.environment.set("user_id", json.data.user.id);
    pm.environment.set("user_email", json.data.user.email);
}
```

---

### 3.2 Login as Admin

```
POST {{base_url}}/auth/login
```

**Body (raw JSON):**
```json
{
    "email": "{{admin_email}}",
    "password": "{{admin_password}}"
}
```

**Postman Tests script:**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

const json = pm.response.json();

pm.test("Admin login successful", function () {
    pm.expect(json.success).to.be.true;
    pm.expect(json.data.token).to.be.a("string");
});

if (json.success && json.data.token) {
    pm.environment.set("token_admin", json.data.token);
}
```

---

### 3.3 Get My Profile (save user_id)

```
GET {{base_url}}/profile
```

**Headers:**
| Header | Value |
|--------|-------|
| Authorization | Bearer {{token_user}} |
| Accept | application/json |

**Expected Response (200):**
```json
{
    "success": true,
    "message": "Success",
    "data": {
        "id": 1,
        "username": "testuser",
        "email": "testuser@example.com",
        "profile_picture": null,
        "role": "user",
        "is_notifications_enabled": true,
        ...
    }
}
```

**Postman Tests script:**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

const json = pm.response.json();

pm.test("Profile retrieved", function () {
    pm.expect(json.success).to.be.true;
    pm.expect(json.data.id).to.be.a("number");
});

if (json.success) {
    pm.environment.set("user_id", json.data.id);
}
```

---

## 4 — Feature Tests

---

### 4.1 Update Profile

**Route:** `PUT {{base_url}}/update-account`
**Auth:** Bearer {{token_user}}
**Controller:** `AuthController@updateAccount`
**Request class:** `UpdateAccountRequest`

#### Validation Rules (source of truth)
| Field | Rules |
|-------|-------|
| `username` | `sometimes\|string\|max:255` |
| `email` | `sometimes\|string\|email\|max:255\|unique:users,email,{current_user}` |
| `current_password` | `required_with:password\|string` |
| `password` | `sometimes\|string\|confirmed\|min:8 + letters + mixedCase + numbers + symbols` |
| `profile_picture` | `nullable\|image\|mimes:jpeg,png,jpg,gif,webp\|max:2048` |

---

#### 4.1.1 Positive Test — Update username

```
PUT {{base_url}}/update-account
```

**Headers:**
| Header | Value |
|--------|-------|
| Authorization | Bearer {{token_user}} |
| Accept | application/json |
| Content-Type | application/json |

**Body (raw JSON):**
```json
{
    "username": "updated_username"
}
```

**Expected Response (200):**
```json
{
    "success": true,
    "message": "تم تحديث الحساب بنجاح",
    "data": {
        "id": 1,
        "username": "updated_username",
        "email": "testuser@example.com",
        ...
    }
}
```

**Postman Tests:**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Username updated", function () {
    const json = pm.response.json();
    pm.expect(json.success).to.be.true;
    pm.expect(json.data.username).to.eql("updated_username");
});
```

---

#### 4.1.2 Positive Test — Update password

```
PUT {{base_url}}/update-account
```

**Body (raw JSON):**
```json
{
    "current_password": "Test@1234",
    "password": "NewPass@5678",
    "password_confirmation": "NewPass@5678"
}
```

**Expected Response (200):**
```json
{
    "success": true,
    "message": "تم تحديث الحساب بنجاح",
    "data": { ... }
}
```

---

#### 4.1.3 Validation Test — Invalid email format

**Body:**
```json
{
    "email": "not-an-email"
}
```

**Expected Response (422):**
```json
{
    "message": "...",
    "errors": {
        "email": ["The email field must be a valid email address."]
    }
}
```

**Postman Tests:**
```javascript
pm.test("Status code is 422", function () {
    pm.response.to.have.status(422);
});
```

---

#### 4.1.4 Validation Test — Password without current_password

**Body:**
```json
{
    "password": "NewPass@5678",
    "password_confirmation": "NewPass@5678"
}
```

**Expected Response (422):**
```json
{
    "message": "...",
    "errors": {
        "current_password": ["كلمة المرور الحالية مطلوبة لتغيير كلمة المرور"]
    }
}
```

---

#### 4.1.5 Validation Test — Wrong current password

**Body:**
```json
{
    "current_password": "WrongPassword123!",
    "password": "NewPass@5678",
    "password_confirmation": "NewPass@5678"
}
```

**Expected Response (401):**
```json
{
    "success": false,
    "message": "كلمة المرور الحالية غير صحيحة"
}
```

---

#### 4.1.6 Unauthorized Test — No token

```
PUT {{base_url}}/update-account
```
*(Remove Authorization header)*

**Body:**
```json
{
    "username": "hacker"
}
```

**Expected Response (401):**
```json
{
    "message": "Unauthenticated."
}
```

**Postman Tests:**
```javascript
pm.test("Status code is 401", function () {
    pm.response.to.have.status(401);
});
```

---

### 4.2 Update Profile Picture (Upload)

**Route:** `POST {{base_url}}/update-profile-picture`
**Auth:** Bearer {{token_user}}
**Controller:** `AuthController@updateProfilePicture`
**Request class:** `UpdateProfilePictureRequest`

#### Validation Rules
| Field | Rules |
|-------|-------|
| `profile_picture` | `required\|image\|mimes:jpeg,png,jpg,gif,webp\|max:2048` |

---

#### 4.2.1 Positive Test — Upload valid image

```
POST {{base_url}}/update-profile-picture
```

**Headers:**
| Header | Value |
|--------|-------|
| Authorization | Bearer {{token_user}} |
| Accept | application/json |

> ⚠️ Do NOT set `Content-Type` manually. Postman sets it to `multipart/form-data` automatically when using form-data body.

**Body (form-data):**
| Key | Type | Value |
|-----|------|-------|
| `profile_picture` | File | *(select a .jpg or .png file < 2MB)* |

**Expected Response (200):**
```json
{
    "success": true,
    "message": "تم تحديث الصورة الشخصية بنجاح",
    "data": {
        "profile_picture_url": "http://roadmap_system.test/storage/profile_pictures/abc123.jpg",
        "user": {
            "id": 1,
            "username": "testuser",
            "profile_picture": "profile_pictures/abc123.jpg",
            ...
        }
    }
}
```

**Postman Tests:**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Profile picture URL returned", function () {
    const json = pm.response.json();
    pm.expect(json.success).to.be.true;
    pm.expect(json.data.profile_picture_url).to.be.a("string");
});
```

---

#### 4.2.2 Validation Test — Invalid file type (e.g. .pdf)

**Body (form-data):**
| Key | Type | Value |
|-----|------|-------|
| `profile_picture` | File | *(select a .pdf file)* |

**Expected Response (422):**
```json
{
    "message": "...",
    "errors": {
        "profile_picture": ["يجب أن تكون الصورة بصيغة jpeg, png, jpg, gif, أو webp"]
    }
}
```

---

#### 4.2.3 Validation Test — Oversized file (> 2MB)

**Body (form-data):**
| Key | Type | Value |
|-----|------|-------|
| `profile_picture` | File | *(select a .jpg > 2MB)* |

**Expected Response (422):**
```json
{
    "message": "...",
    "errors": {
        "profile_picture": ["حجم الصورة يجب أن لا يتعدى 2MB"]
    }
}
```

---

#### 4.2.4 Validation Test — No file sent

**Body:** *(empty form-data, no profile_picture key)*

**Expected Response (422):**
```json
{
    "message": "...",
    "errors": {
        "profile_picture": ["الصورة مطلوبة"]
    }
}
```

---

#### 4.2.5 Unauthorized Test — No token

*(Remove Authorization header)*

**Expected Response (401):**
```json
{
    "message": "Unauthenticated."
}
```

---

### 4.3 Delete Profile Picture

**Route:** `DELETE {{base_url}}/delete-profile-picture`
**Auth:** Bearer {{token_user}}
**Controller:** `AuthController@deleteProfilePicture`
**Body:** None

---

#### 4.3.1 Positive Test — Delete existing picture

> ⚠️ First upload a picture using 4.2.1, then call this.

```
DELETE {{base_url}}/delete-profile-picture
```

**Headers:**
| Header | Value |
|--------|-------|
| Authorization | Bearer {{token_user}} |
| Accept | application/json |

**Expected Response (200):**
```json
{
    "success": true,
    "message": "تم حذف الصورة الشخصية بنجاح",
    "data": {
        "id": 1,
        "username": "testuser",
        "profile_picture": null,
        ...
    }
}
```

**Postman Tests:**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Picture is null", function () {
    const json = pm.response.json();
    pm.expect(json.data.profile_picture).to.be.null;
});
```

---

#### 4.3.2 Already Deleted Case — No picture to delete

```
DELETE {{base_url}}/delete-profile-picture
```

**Expected Response (404):**
```json
{
    "success": false,
    "message": "لا توجد صورة شخصية لحذفها"
}
```

**Postman Tests:**
```javascript
pm.test("Status code is 404", function () {
    pm.response.to.have.status(404);
});
```

---

#### 4.3.3 Unauthorized Test — No token

**Expected Response (401):**
```json
{
    "message": "Unauthenticated."
}
```

---

### 4.4 Forgot Password

**Route:** `POST {{base_url}}/auth/forgot-password`
**Auth:** None (public)
**Controller:** `PasswordResetController@forgotPassword`
**Request class:** `ForgotPasswordRequest`
**Throttle:** 3 requests per minute

#### Validation Rules
| Field | Rules |
|-------|-------|
| `email` | `required\|email` |

---

#### 4.4.1 Positive Test — Valid email

```
POST {{base_url}}/auth/forgot-password
```

**Headers:**
| Header | Value |
|--------|-------|
| Accept | application/json |
| Content-Type | application/json |

**Body:**
```json
{
    "email": "testuser@example.com"
}
```

**Expected Response (200):**
```json
{
    "status": "success",
    "message": "تم إرسال رابط إعادة التعيين إلى بريدك الإلكتروني"
}
```

**Postman Tests:**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Success message", function () {
    const json = pm.response.json();
    pm.expect(json.status).to.eql("success");
});
```

---

#### 4.4.2 Non-existing Email

**Body:**
```json
{
    "email": "nonexistent@example.com"
}
```

**Expected Response (404):**
```json
{
    "status": "error",
    "message": "المستخدم غير موجود"
}
```

**Postman Tests:**
```javascript
pm.test("Status code is 404", function () {
    pm.response.to.have.status(404);
});
```

---

#### 4.4.3 Validation Test — Missing email

**Body:**
```json
{}
```

**Expected Response (422):**
```json
{
    "message": "...",
    "errors": {
        "email": ["البريد الإلكتروني مطلوب"]
    }
}
```

---

#### 4.4.4 Validation Test — Invalid email format

**Body:**
```json
{
    "email": "not-valid"
}
```

**Expected Response (422):**
```json
{
    "message": "...",
    "errors": {
        "email": ["البريد الإلكتروني غير صالح"]
    }
}
```

---

#### 4.4.5 Throttle Test

Send the same request **4 times quickly** (limit is 3/min).

**Expected Response on 4th call (429):**
```json
{
    "message": "Too Many Attempts."
}
```

---

### 4.5 Reset Password

**Route:** `POST {{base_url}}/auth/reset-password`
**Auth:** None (public)
**Controller:** `PasswordResetController@resetPassword`
**Request class:** `ResetPasswordRequest`
**Throttle:** 3 requests per minute

#### Validation Rules
| Field | Rules |
|-------|-------|
| `token` | `required\|string` |
| `email` | `required\|string\|email\|exists:users,email` |
| `password` | `required\|string\|confirmed\|min:8 + letters + mixedCase + numbers + symbols` |

> **How to get a valid token:** Call forgot-password first, then check the `password_reset_tokens` table in the database, or check the email received in Mailtrap.

---

#### 4.5.1 Positive Test — Valid reset

```
POST {{base_url}}/auth/reset-password
```

**Headers:**
| Header | Value |
|--------|-------|
| Accept | application/json |
| Content-Type | application/json |

**Body:**
```json
{
    "token": "PASTE_TOKEN_FROM_EMAIL_OR_DB",
    "email": "testuser@example.com",
    "password": "NewSecure@Pass1",
    "password_confirmation": "NewSecure@Pass1"
}
```

**Expected Response (200):**
```json
{
    "status": "success",
    "message": "تم تغيير كلمة المرور بنجاح"
}
```

**Postman Tests:**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Password reset success", function () {
    pm.expect(pm.response.json().status).to.eql("success");
});
```

---

#### 4.5.2 Invalid Token

**Body:**
```json
{
    "token": "invalid-token-abc123",
    "email": "testuser@example.com",
    "password": "NewSecure@Pass1",
    "password_confirmation": "NewSecure@Pass1"
}
```

**Expected Response (400):**
```json
{
    "status": "error",
    "message": "الرمز غير صالح أو منتهي الصلاحية"
}
```

---

#### 4.5.3 Weak Password — Missing symbols

**Body:**
```json
{
    "token": "VALID_TOKEN",
    "email": "testuser@example.com",
    "password": "weakpass1",
    "password_confirmation": "weakpass1"
}
```

**Expected Response (422):**
```json
{
    "message": "...",
    "errors": {
        "password": ["...must contain at least one symbol...", "...must contain uppercase and lowercase..."]
    }
}
```

---

#### 4.5.4 Password Mismatch

**Body:**
```json
{
    "token": "VALID_TOKEN",
    "email": "testuser@example.com",
    "password": "NewSecure@Pass1",
    "password_confirmation": "DifferentPass@2"
}
```

**Expected Response (422):**
```json
{
    "message": "...",
    "errors": {
        "password": ["تأكيد كلمة المرور غير متطابق"]
    }
}
```

---

#### 4.5.5 Non-existing Email

**Body:**
```json
{
    "token": "some-token",
    "email": "ghost@example.com",
    "password": "NewSecure@Pass1",
    "password_confirmation": "NewSecure@Pass1"
}
```

**Expected Response (422):**
```json
{
    "message": "...",
    "errors": {
        "email": ["البريد الإلكتروني غير مسجل في النظام"]
    }
}
```

---

### 4.6 Delete Account (Self)

**Route:** `DELETE {{base_url}}/delete-account`
**Auth:** Bearer {{token_user}}
**Controller:** `AuthController@deleteAccount`
**Request class:** `DeleteAccountRequest`

#### Validation Rules
| Field | Rules |
|-------|-------|
| `password` | `required\|string` |

> ⚠️ **Safety confirmation:** The user must provide their current password to confirm deletion. After deletion, the bearer token becomes invalid.

---

#### 4.6.1 Positive Test — Delete own account

> ⚠️ Use a **throwaway test user** for this test. After deletion the token is invalidated.

```
DELETE {{base_url}}/delete-account
```

**Headers:**
| Header | Value |
|--------|-------|
| Authorization | Bearer {{token_user}} |
| Accept | application/json |
| Content-Type | application/json |

**Body:**
```json
{
    "password": "{{user_password}}"
}
```

**Expected Response (200):**
```json
{
    "success": true,
    "message": "تم حذف الحساب بنجاح",
    "data": null
}
```

**Postman Tests:**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Account deleted", function () {
    pm.expect(pm.response.json().success).to.be.true;
});
```

**Follow-up verification:** Try calling `GET /profile` with the same token:

```
GET {{base_url}}/profile
Authorization: Bearer {{token_user}}
```

**Expected Response (401):**
```json
{
    "message": "Unauthenticated."
}
```

---

#### 4.6.2 Wrong Password

**Body:**
```json
{
    "password": "WrongPassword!"
}
```

**Expected Response (401):**
```json
{
    "success": false,
    "message": "كلمة المرور غير صحيحة"
}
```

---

#### 4.6.3 Missing Password

**Body:**
```json
{}
```

**Expected Response (422):**
```json
{
    "message": "...",
    "errors": {
        "password": ["كلمة المرور مطلوبة للتأكيد"]
    }
}
```

---

#### 4.6.4 Unauthorized Test — No token

```
DELETE {{base_url}}/delete-account
```
*(No Authorization header)*

**Expected Response (401):**
```json
{
    "message": "Unauthenticated."
}
```

---

### 4.7 Delete Account (Admin Deletes User)

**Route:** `DELETE {{base_url}}/admin/users/{id}`
**Auth:** Bearer {{token_admin}} (role: `admin`)
**Controller:** `AdminUserController@destroy`
**Body:** None
**Path param:** `{id}` = target user's ID

> This route exists and is guarded by middleware `auth:sanctum` + `role:admin`. The controller also has a constructor middleware check for `isNormalAdmin()`.

---

#### 4.7.1 Positive Test — Admin deletes a user

```
DELETE {{base_url}}/admin/users/{{user_id}}
```

**Headers:**
| Header | Value |
|--------|-------|
| Authorization | Bearer {{token_admin}} |
| Accept | application/json |

**Expected Response (204):**
```json
{
    "success": true,
    "message": "تم حذف المستخدم بنجاح",
    "data": null
}
```

**Postman Tests:**
```javascript
pm.test("Status code is 200 or 204", function () {
    pm.expect(pm.response.code).to.be.oneOf([200, 204]);
});
```

---

#### 4.7.2 Forbidden Test — Non-admin user tries to delete

```
DELETE {{base_url}}/admin/users/5
```

**Headers:**
| Header | Value |
|--------|-------|
| Authorization | Bearer {{token_user}} |
| Accept | application/json |

**Expected Response (403):**
```json
{
    "success": false,
    "message": "Unauthorized. Admin role required."
}
```

**Postman Tests:**
```javascript
pm.test("Status code is 403", function () {
    pm.response.to.have.status(403);
});
```

---

#### 4.7.3 Admin Tries to Delete Self

```
DELETE {{base_url}}/admin/users/{{admin_own_id}}
```

**Expected Response (403):**
```json
{
    "success": false,
    "message": "لا يمكنك حذف حسابك الخاص"
}
```

---

#### 4.7.4 Not Found — Invalid user ID

```
DELETE {{base_url}}/admin/users/99999
```

**Headers:**
| Header | Value |
|--------|-------|
| Authorization | Bearer {{token_admin}} |
| Accept | application/json |

**Expected Response (404):**
```json
{
    "message": "No query results for model [App\\Models\\User] 99999"
}
```

**Postman Tests:**
```javascript
pm.test("Status code is 404", function () {
    pm.response.to.have.status(404);
});
```

---

### 4.8 Reset Roadmap Attempts / Reset Enrollment Progress

#### Existing Behavior Analysis

There is **no single "reset all roadmap progress" endpoint**. The available related endpoints are:

| Action | Method | Path | What it does |
|--------|--------|------|--------------|
| Unenroll | `DELETE` | `/roadmaps/{id}/unenroll` | Deletes the enrollment row entirely |
| Re-enroll | `POST` | `/roadmaps/{id}/enroll` | Creates fresh enrollment (xp_points=0, status=active) |
| Reset single lesson | `DELETE` | `/lessons/{lessonId}/track/reset` | Sets `is_complete=false` for one lesson tracking |
| Update enrollment status | `PATCH` | `/me/enrollments/{roadmapId}/status` | Sets status to active/paused/completed |

**To "restart" a roadmap, the workflow is: Unenroll → Re-enroll.**

---

#### 4.8.1 Unenroll from Roadmap

```
DELETE {{base_url}}/roadmaps/{{roadmap_id}}/unenroll
```

**Headers:**
| Header | Value |
|--------|-------|
| Authorization | Bearer {{token_user}} |
| Accept | application/json |

**Expected Response (200):**
```json
{
    "success": true,
    "message": "تم إلغاء الاشتراك بنجاح",
    "data": null
}
```

**Postman Tests:**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Unenrolled", function () {
    pm.expect(pm.response.json().success).to.be.true;
});
```

---

#### 4.8.2 Not Enrolled Case

```
DELETE {{base_url}}/roadmaps/{{roadmap_id}}/unenroll
```
*(Call again after already unenrolled)*

**Expected Response (404):**
```json
{
    "success": false,
    "message": "أنت غير مشترك في هذا المسار"
}
```

---

#### 4.8.3 Re-enroll (Fresh Start)

```
POST {{base_url}}/roadmaps/{{roadmap_id}}/enroll
```

**Headers:**
| Header | Value |
|--------|-------|
| Authorization | Bearer {{token_user}} |
| Accept | application/json |

**Expected Response (201):**
```json
{
    "success": true,
    "message": "تم الاشتراك في المسار بنجاح",
    "data": {
        "enrollment": {
            "id": 10,
            "user_id": 1,
            "roadmap_id": 1,
            "started_at": "2026-02-22T...",
            "status": "active",
            "xp_points": 0
        },
        "chat_room": { ... }
    }
}
```

**Postman Tests:**
```javascript
pm.test("Status code is 201", function () {
    pm.response.to.have.status(201);
});

pm.test("Fresh enrollment", function () {
    const json = pm.response.json();
    pm.expect(json.data.enrollment.xp_points).to.eql(0);
    pm.expect(json.data.enrollment.status).to.eql("active");
});
```

---

#### 4.8.4 Reset Individual Lesson Tracking

```
DELETE {{base_url}}/lessons/{{lesson_id}}/track/reset
```

**Headers:**
| Header | Value |
|--------|-------|
| Authorization | Bearer {{token_user}} |
| Accept | application/json |

**Expected Response (200):**
```json
{
    "success": true,
    "message": "تم إعادة تعيين تتبع الدرس",
    "data": {
        "lesson_id": 5,
        "is_complete": false,
        "last_updated_at": "2026-02-22T..."
    }
}
```

**Postman Tests:**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Lesson reset to incomplete", function () {
    pm.expect(pm.response.json().data.is_complete).to.be.false;
});
```

---

#### 4.8.5 Reset Lesson — No Tracking Exists

```
DELETE {{base_url}}/lessons/{{lesson_id}}/track/reset
```
*(For a lesson that was never opened)*

**Expected Response (404):**
```json
{
    "success": false,
    "message": "لا يوجد تتبع لهذا الدرس"
}
```

---

#### 4.8.6 Unauthorized Test — No token

```
DELETE {{base_url}}/roadmaps/1/unenroll
```
*(No Authorization header)*

**Expected Response (401):**
```json
{
    "message": "Unauthenticated."
}
```

---

## 5 — Helper: Verify Reset Token (bonus)

**Route:** `POST {{base_url}}/auth/verify-reset-token`
**Auth:** None (public)

**Body:**
```json
{
    "token": "PASTE_TOKEN",
    "email": "testuser@example.com"
}
```

**Expected Response (200):**
```json
{
    "status": "success",
    "message": "الرمز صالح",
    "expires_at": "2026-02-22 14:30:00"
}
```

**Invalid token (400):**
```json
{
    "status": "error",
    "message": "الرمز غير صالح أو منتهي الصلاحية"
}
```

---

## 6 — Helper: Get Reset Attempts Remaining (bonus)

**Route:** `GET {{base_url}}/auth/reset-attempts?email=testuser@example.com`
**Auth:** None (public)

**Expected Response (200):**
```json
{
    "status": "success",
    "data": {
        "email_attempts_remaining": 3,
        "ip_attempts_remaining": 6,
        "max_email_attempts": 3,
        "max_ip_attempts": 6,
        "is_email_blocked": false,
        "is_ip_blocked": false
    }
}
```

---

## 7 — Recommended Test Execution Order

1. **Login as user** (3.1) → saves `token_user`
2. **Login as admin** (3.2) → saves `token_admin`
3. **Get profile** (3.3) → saves `user_id`
4. **Update profile** (4.1.1–4.1.6)
5. **Upload profile picture** (4.2.1) → then **Delete profile picture** (4.3.1, 4.3.2)
6. **Forgot password** (4.4.1–4.4.5) → copy token from DB/email
7. **Verify reset token** (Section 5) → then **Reset password** (4.5.1–4.5.5)
8. **Enroll in a roadmap** → then **Unenroll** (4.8.1) → **Re-enroll** (4.8.3)
9. **Open + complete a lesson** → then **Reset lesson tracking** (4.8.4)
10. **Admin delete user** (4.7.1–4.7.4)
11. **Delete own account** (4.6.1) ← run **last** (invalidates token)

---

## MISSING Features Summary

| Feature | Status | Proposed Endpoint |
|---------|--------|-------------------|
| Reset ALL roadmap progress (bulk) | ❌ NOT IMPLEMENTED | `DELETE /api/v1/me/enrollments/{roadmapId}/reset-progress` |

> **Workaround:** Unenroll + Re-enroll (loses enrollment history), or reset lessons individually.

