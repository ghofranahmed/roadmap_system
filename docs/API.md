# Roadmap Learning Platform — API Documentation v1

> **Last updated:** 2026-02-20
> **Auth method:** Laravel Sanctum (Bearer Token)
> **Default response format:** JSON

---

## Table of Contents

- [Introduction](#introduction)
- [Base URL](#base-url)
- [Authentication (Sanctum Bearer Token)](#authentication-sanctum-bearer-token)
- [Standard Response Format](#standard-response-format)
- [Mobile Quick Start](#mobile-quick-start)
- [1. Auth Endpoints](#1-auth-endpoints)
- [2. Profile / Me Endpoints](#2-profile--me-endpoints)
- [3. Roadmaps Endpoints](#3-roadmaps-endpoints)
- [4. Enrollments](#4-enrollments)
- [5. Units / Lessons / SubLessons / Resources](#5-units--lessons--sublessons--resources)
- [6. Lesson Tracking](#6-lesson-tracking)
- [7. Quizzes / Quiz Attempts](#7-quizzes--quiz-attempts)
- [8. Challenges / Challenge Attempts](#8-challenges--challenge-attempts)
- [9. Community / Chat Messages](#9-community--chat-messages)
- [10. Chatbot](#10-chatbot)
- [11. Notifications](#11-notifications)
- [12. Announcements](#12-announcements)
- [13. Admin — Normal Admin (`role:admin`)](#13-admin--normal-admin-roleadmin)
- [14. Admin — Shared Read-Only (`role:admin,tech_admin`)](#14-admin--shared-read-only-roleadmintech_admin)
- [15. Admin — Tech Admin Content CRUD (`role:tech_admin`)](#15-admin--tech-admin-content-crud-roletech_admin)
- [Notes / TODO](#notes--todo)

---

## Introduction

This document describes **every** endpoint exposed by the Roadmap Learning Platform API v1.
It is written for **mobile developers** (Flutter / Kotlin / Swift) who need to integrate with the backend.

**Key concepts:**

| Term | Meaning |
|------|---------|
| **Roadmap** | A learning path (e.g. "Backend with Laravel") |
| **Learning Unit** | A chapter inside a roadmap |
| **Lesson** | A lesson inside a unit |
| **SubLesson** | A sub-section inside a lesson |
| **Resource** | A link (video / article / book) attached to a sub-lesson |
| **Enrollment** | When a user subscribes to a roadmap |
| **Quiz** | Multiple-choice test attached to a unit |
| **Challenge** | A coding challenge attached to a unit |

---

## Base URL

| Environment | Base URL |
|-------------|----------|
| Local (Laravel Herd) | `http://roadmap_system.test/api/v1` |
| Production (Render) | `https://YOUR-APP.onrender.com/api/v1` |

> **All paths below are relative to the base URL.**
> Example: `POST /auth/login` → `http://roadmap_system.test/api/v1/auth/login`

---

## Authentication (Sanctum Bearer Token)

This API uses **Laravel Sanctum** personal access tokens.

### How it works

1. You call `POST /auth/login` (or `/auth/register`) and receive a `token` in the response.
2. For every **protected** endpoint you add this header:

```
Authorization: Bearer <your-token-here>
```

3. When the user logs out, call `POST /logout` — the token is deleted server-side.

### Example header

```
GET /profile HTTP/1.1
Host: roadmap_system.test
Accept: application/json
Authorization: Bearer 1|abc123def456...
```

> **Important:** Sanctum tokens look like `1|xxxxxxx`. Always send the **full** string including the number and pipe.

---

## Standard Response Format

Every endpoint returns JSON in one of these shapes:

### Success response

```json
{
  "success": true,
  "message": "Success",
  "data": { }
}
```

### Success response (paginated)

```json
{
  "success": true,
  "message": "Success",
  "data": [ ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 73,
    "from": 1,
    "to": 15
  },
  "links": {
    "first": "...?page=1",
    "last": "...?page=5",
    "prev": null,
    "next": "...?page=2"
  }
}
```

### Error response

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Validation message"]
  }
}
```

> `errors` key is **only** present when there are validation errors or extra detail. Otherwise it is omitted.

---

## Mobile Quick Start

### Step 1 — Login and get a token

```
POST /auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "MyP@ss123"
}
```

Response (200):

```json
{
  "success": true,
  "message": "تم تسجيل الدخول بنجاح",
  "data": {
    "user": {
      "id": 1,
      "username": "ahmed",
      "email": "user@example.com",
      "role": "user",
      "profile_picture": null,
      "is_notifications_enabled": true,
      "last_active_at": "2026-02-20T10:00:00.000000Z",
      "last_login_at": "2026-02-20T10:00:00.000000Z",
      "created_at": "2026-01-01T00:00:00.000000Z",
      "updated_at": "2026-02-20T10:00:00.000000Z"
    },
    "token": "1|abcDEF123456...",
    "token_type": "Bearer"
  }
}
```

### Step 2 — Store the token

Save the **full token string** (e.g. `1|abcDEF123456...`) in secure storage:

| Platform | Recommended Storage |
|----------|-------------------|
| Flutter | `flutter_secure_storage` |
| Android | `EncryptedSharedPreferences` |
| iOS | `Keychain` |

### Step 3 — Call your first protected endpoint

```
GET /profile
Authorization: Bearer 1|abcDEF123456...
Accept: application/json
```

### Common mistakes

| Mistake | What happens |
|---------|-------------|
| Missing `Authorization` header | 401 `{"message":"Unauthenticated."}` |
| Writing `Token` instead of `Bearer` | 401 Unauthenticated |
| Forgetting `Accept: application/json` | Laravel may return HTML instead of JSON |
| Using wrong base URL (missing `/api/v1`) | 404 Not Found |
| Sending `password_confirmation` on login | Unnecessary — login only needs `email` + `password` |
| Token expired / revoked | 401 Unauthenticated — ask user to login again |

---

## 1. Auth Endpoints

### 1.1 Test Connection

**Purpose:** Quick check that the server is reachable. Use it on app launch.

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/test-connection` |
| **Auth** | Not required |

**Request Headers:**

```
Accept: application/json
```

**Request Body:** No body

**Success Response (200):**

```json
{
  "success": true,
  "message": "الاتصال ناجح والجهاز واصل بالإنترنت!",
  "data": null
}
```

---

### 1.2 Register

**Purpose:** Create a new user account and receive an auth token.

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/auth/register` |
| **Auth** | Not required |
| **Rate Limit** | 5 requests per minute |

**Request Headers:**

```
Accept: application/json
Content-Type: application/json
```

**Request Body:**

```json
{
  "username": "ahmed_dev",
  "email": "ahmed@example.com",
  "password": "MyStr0ng@Pass",
  "password_confirmation": "MyStr0ng@Pass"
}
```

| Field | Type | Rules |
|-------|------|-------|
| `username` | string | **Required.** Max 255 chars. |
| `email` | string | **Required.** Valid email. Unique in `users` table. Max 255 chars. |
| `password` | string | **Required.** Min 8 chars, must contain: uppercase + lowercase letters, numbers, symbols. Must match `password_confirmation`. |
| `password_confirmation` | string | **Required.** Must match `password`. |

**Success Response (201):**

```json
{
  "success": true,
  "message": "تم تسجيل المستخدم بنجاح",
  "data": {
    "user": {
      "id": 5,
      "username": "ahmed_dev",
      "email": "ahmed@example.com",
      "role": "user",
      "profile_picture": null,
      "is_notifications_enabled": true,
      "last_active_at": "2026-02-20T10:00:00.000000Z",
      "created_at": "2026-02-20T10:00:00.000000Z",
      "updated_at": "2026-02-20T10:00:00.000000Z"
    },
    "token": "5|newTokenString...",
    "token_type": "Bearer"
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 422 | Validation fails | `{"success":false,"message":"...","errors":{"email":["البريد الإلكتروني مستخدم بالفعل"],"password":["تأكيد كلمة المرور غير متطابق"]}}` |
| 429 | Rate limit exceeded | Too many requests — wait 1 minute |

---

### 1.3 Login

**Purpose:** Authenticate an existing user and get a Bearer token.

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/auth/login` |
| **Auth** | Not required |
| **Rate Limit** | 5 requests per minute |

**Request Headers:**

```
Accept: application/json
Content-Type: application/json
```

**Request Body:**

```json
{
  "email": "ahmed@example.com",
  "password": "MyStr0ng@Pass"
}
```

| Field | Type | Rules |
|-------|------|-------|
| `email` | string | **Required.** Valid email format. |
| `password` | string | **Required.** |

> **⚠️ Login does NOT require `password_confirmation`.** Only `email` and `password`.

**Success Response (200):**

```json
{
  "success": true,
  "message": "تم تسجيل الدخول بنجاح",
  "data": {
    "user": {
      "id": 1,
      "username": "ahmed_dev",
      "email": "ahmed@example.com",
      "role": "user",
      "profile_picture": null,
      "is_notifications_enabled": true,
      "last_active_at": "2026-02-20T10:00:00.000000Z",
      "last_login_at": "2026-02-20T10:00:00.000000Z",
      "created_at": "2026-01-01T00:00:00.000000Z",
      "updated_at": "2026-02-20T10:00:00.000000Z"
    },
    "token": "1|abcDEF123456...",
    "token_type": "Bearer"
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Wrong email or password | `{"success":false,"message":"بيانات الدخول غير صحيحة"}` |
| 422 | Validation fails | `{"success":false,"message":"...","errors":{"email":["البريد الإلكتروني مطلوب"]}}` |
| 429 | Rate limit exceeded | Too many requests |

---

### 1.4 Google Social Login

**Purpose:** Login or register using a Google `id_token` (from Google Sign-In on mobile).

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/auth/google` |
| **Auth** | Not required |
| **Rate Limit** | 5 requests per minute |

**Request Headers:**

```
Accept: application/json
Content-Type: application/json
```

**Request Body:**

```json
{
  "id_token": "eyJhbGciOi..."
}
```

| Field | Type | Rules |
|-------|------|-------|
| `id_token` | string | **Required.** The Google ID token from mobile SDK. |

**Success Response (200):**

```json
{
  "status": "success",
  "token": "3|googleToken...",
  "user": {
    "id": 3,
    "username": "ahmed-ali",
    "email": "ahmed@gmail.com"
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Invalid Google token | `{"status":"error","message":"Invalid Google token"}` |
| 422 | Missing data in token | `{"status":"error","message":"Google token missing required data"}` |

---

### 1.5 GitHub Social Login

**Purpose:** Login or register using a GitHub OAuth `code` (from GitHub OAuth flow on mobile).

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/auth/github` |
| **Auth** | Not required |
| **Rate Limit** | 5 requests per minute |

**Request Headers:**

```
Accept: application/json
Content-Type: application/json
```

**Request Body:**

```json
{
  "code": "github_oauth_code_here"
}
```

| Field | Type | Rules |
|-------|------|-------|
| `code` | string | **Required.** GitHub OAuth authorization code. |

**Success Response (200):**

```json
{
  "status": "success",
  "token": "4|githubToken...",
  "user": {
    "id": 4,
    "username": "ahmed-dev",
    "email": "ahmed@github.com"
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Invalid/expired code | `{"status":"error","message":"Invalid GitHub code"}` |
| 422 | Missing email on GitHub | `{"status":"error","message":"GitHub account missing required data"}` |

---

### 1.6 Forgot Password

**Purpose:** Send a password reset link to the user's email.

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/auth/forgot-password` |
| **Auth** | Not required |
| **Rate Limit** | 3 requests per minute |

**Request Headers:**

```
Accept: application/json
Content-Type: application/json
```

**Request Body:**

```json
{
  "email": "ahmed@example.com"
}
```

| Field | Type | Rules |
|-------|------|-------|
| `email` | string | **Required.** Valid email format. |

**Success Response (200):**

```json
{
  "status": "success",
  "message": "تم إرسال رابط إعادة التعيين إلى بريدك الإلكتروني"
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 404 | Email not found in users table | `{"status":"error","message":"المستخدم غير موجود"}` |
| 422 | Validation fails | `{"success":false,"message":"...","errors":{"email":["البريد الإلكتروني مطلوب"]}}` |
| 429 | Rate limit exceeded | Too many requests |
| 500 | Mail sending failed | `{"status":"error","message":"حدث خطأ في إرسال البريد الإلكتروني أو إدخال البيانات في قاعدة البيانات"}` |

---

### 1.7 Verify Reset Token

**Purpose:** Check if a password reset token is still valid (not expired).

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/auth/verify-reset-token` |
| **Auth** | Not required |
| **Rate Limit** | 5 requests per minute |

**Request Headers:**

```
Accept: application/json
Content-Type: application/json
```

**Request Body:**

```json
{
  "token": "random64chartoken...",
  "email": "ahmed@example.com"
}
```

| Field | Type | Rules |
|-------|------|-------|
| `token` | string | **Required.** The reset token from the email link. |
| `email` | string | **Required.** Valid email. Must exist in `users` table. |

**Success Response (200):**

```json
{
  "status": "success",
  "message": "الرمز صالح",
  "expires_at": "2026-02-20 11:00:00"
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 400 | Token invalid or expired | `{"status":"error","message":"الرمز غير صالح أو منتهي الصلاحية"}` |
| 422 | Validation fails | `{"message":"...","errors":{"email":["..."]}}` |

---

### 1.8 Reset Password

**Purpose:** Set a new password using the reset token received by email.

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/auth/reset-password` |
| **Auth** | Not required |
| **Rate Limit** | 3 requests per minute |

**Request Headers:**

```
Accept: application/json
Content-Type: application/json
```

**Request Body:**

```json
{
  "token": "random64chartoken...",
  "email": "ahmed@example.com",
  "password": "NewStr0ng@Pass",
  "password_confirmation": "NewStr0ng@Pass"
}
```

| Field | Type | Rules |
|-------|------|-------|
| `token` | string | **Required.** |
| `email` | string | **Required.** Valid email. Must exist in `users` table. |
| `password` | string | **Required.** Min 8 chars, must contain: uppercase + lowercase letters, numbers, symbols. Must match `password_confirmation`. |
| `password_confirmation` | string | **Required.** Must match `password`. |

**Success Response (200):**

```json
{
  "status": "success",
  "message": "تم تغيير كلمة المرور بنجاح"
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 400 | Token invalid / expired | `{"status":"error","message":"الرمز غير صالح أو منتهي الصلاحية"}` |
| 400 | Weak password | `{"status":"error","message":"كلمة المرور يجب أن تحتوي على 8 أحرف على الأقل، مع أرقام وأحرف ورموز"}` |
| 422 | Validation fails | `{"success":false,"message":"...","errors":{"password":["تأكيد كلمة المرور غير متطابق"]}}` |

---

### 1.9 Get Reset Attempts Remaining

**Purpose:** Check how many password reset attempts remain (per email and per IP).

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/auth/reset-attempts` |
| **Auth** | Not required |

**Query Parameters:**

| Param | Type | Rules |
|-------|------|-------|
| `email` | string | **Required.** Valid email. Must exist in `users` table. |

**Request Headers:**

```
Accept: application/json
```

**Request Body:** No body

**Example:** `GET /auth/reset-attempts?email=ahmed@example.com`

**Success Response (200):**

```json
{
  "status": "success",
  "data": {
    "email_attempts_remaining": 2,
    "ip_attempts_remaining": 5,
    "max_email_attempts": 3,
    "max_ip_attempts": 6,
    "is_email_blocked": false,
    "is_ip_blocked": false
  }
}
```

If blocked, extra fields appear:

```json
{
  "status": "success",
  "data": {
    "email_attempts_remaining": 0,
    "ip_attempts_remaining": 4,
    "max_email_attempts": 3,
    "max_ip_attempts": 6,
    "is_email_blocked": true,
    "is_ip_blocked": false,
    "email_blocked_until": "2026-02-20 11:30:00",
    "email_blocked_seconds": 1800
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 422 | Email missing or not registered | `{"status":"error","message":"البيانات غير صالحة","errors":{"email":["البريد الإلكتروني غير مسجل في النظام"]}}` |

---

## 2. Profile / Me Endpoints

### 2.1 Get Profile

**Purpose:** Get the currently authenticated user's profile data.

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/profile` |
| **Auth** | **Required** (Bearer token) |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200):**

```json
{
  "success": true,
  "message": "Success",
  "data": {
    "id": 1,
    "username": "ahmed_dev",
    "email": "ahmed@example.com",
    "role": "user",
    "profile_picture": "profile_pictures/abc123.jpg",
    "is_notifications_enabled": true,
    "email_verified_at": null,
    "last_active_at": "2026-02-20T10:00:00.000000Z",
    "last_login_at": "2026-02-20T09:00:00.000000Z",
    "created_at": "2026-01-01T00:00:00.000000Z",
    "updated_at": "2026-02-20T10:00:00.000000Z"
  }
}
```

> **Tip:** `profile_picture` is a relative path. To build the full URL:
> `{base_url_without_api}/storage/{profile_picture}` → e.g. `http://roadmap_system.test/storage/profile_pictures/abc123.jpg`

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Missing or invalid token | `{"message":"Unauthenticated."}` |

---

### 2.2 Update Account

**Purpose:** Update username, email, password, or profile picture for the current user.

| | |
|---|---|
| **Method** | `PUT` |
| **Path** | `/update-account` |
| **Auth** | **Required** (Bearer token) |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
Content-Type: multipart/form-data
```

> Use `multipart/form-data` if uploading a profile picture. Otherwise `application/json` works for text-only updates.

**Request Body (all fields optional):**

```json
{
  "username": "new_username",
  "email": "new@example.com",
  "current_password": "OldP@ss123",
  "password": "NewP@ss456",
  "password_confirmation": "NewP@ss456"
}
```

| Field | Type | Rules |
|-------|------|-------|
| `username` | string | Optional. Max 255 chars. |
| `email` | string | Optional. Valid email. Unique (excluding current user). Max 255 chars. |
| `current_password` | string | **Required if** `password` is provided. |
| `password` | string | Optional. Min 8 chars, must contain: uppercase + lowercase, numbers, symbols. Must match `password_confirmation`. |
| `password_confirmation` | string | Required if `password` is provided. |
| `profile_picture` | file | Optional. Image (jpeg, png, jpg, gif, webp). Max 2 MB. |

**Success Response (200):**

```json
{
  "success": true,
  "message": "تم تحديث الحساب بنجاح",
  "data": {
    "id": 1,
    "username": "new_username",
    "email": "new@example.com",
    "role": "user",
    "profile_picture": "profile_pictures/new123.jpg",
    "is_notifications_enabled": true,
    "last_active_at": "2026-02-20T10:05:00.000000Z",
    "created_at": "2026-01-01T00:00:00.000000Z",
    "updated_at": "2026-02-20T10:05:00.000000Z"
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 401 | Wrong current password | `{"success":false,"message":"كلمة المرور الحالية غير صحيحة"}` |
| 422 | Validation fails | `{"success":false,"message":"...","errors":{"email":["البريد الإلكتروني مستخدم بالفعل"],"profile_picture":["حجم الصورة يجب أن لا يتعدى 2MB"]}}` |

---

### 2.3 Update Profile Picture

**Purpose:** Upload or replace the user's profile picture (dedicated endpoint).

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/update-profile-picture` |
| **Auth** | **Required** (Bearer token) |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
Content-Type: multipart/form-data
```

**Request Body:**

| Field | Type | Rules |
|-------|------|-------|
| `profile_picture` | file | **Required.** Image (jpeg, png, jpg, gif, webp). Max 2 MB. |

**Success Response (200):**

```json
{
  "success": true,
  "message": "تم تحديث الصورة الشخصية بنجاح",
  "data": {
    "profile_picture_url": "http://roadmap_system.test/storage/profile_pictures/new456.jpg",
    "user": {
      "id": 1,
      "username": "ahmed_dev",
      "email": "ahmed@example.com",
      "role": "user",
      "profile_picture": "profile_pictures/new456.jpg",
      "is_notifications_enabled": true,
      "last_active_at": "2026-02-20T10:10:00.000000Z",
      "created_at": "2026-01-01T00:00:00.000000Z",
      "updated_at": "2026-02-20T10:10:00.000000Z"
    }
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 422 | Missing or invalid file | `{"success":false,"message":"...","errors":{"profile_picture":["الصورة مطلوبة"]}}` |
| 422 | File too large | `{"success":false,"message":"...","errors":{"profile_picture":["حجم الصورة يجب أن لا يتعدى 2MB"]}}` |

---

### 2.4 Delete Profile Picture

**Purpose:** Remove the user's profile picture.

| | |
|---|---|
| **Method** | `DELETE` |
| **Path** | `/delete-profile-picture` |
| **Auth** | **Required** (Bearer token) |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200):**

```json
{
  "success": true,
  "message": "تم حذف الصورة الشخصية بنجاح",
  "data": {
    "id": 1,
    "username": "ahmed_dev",
    "email": "ahmed@example.com",
    "role": "user",
    "profile_picture": null,
    "is_notifications_enabled": true,
    "last_active_at": "2026-02-20T10:15:00.000000Z",
    "created_at": "2026-01-01T00:00:00.000000Z",
    "updated_at": "2026-02-20T10:15:00.000000Z"
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 404 | No picture to delete | `{"success":false,"message":"لا توجد صورة شخصية لحذفها"}` |

---

### 2.5 Update Notification Preference

**Purpose:** Enable or disable push notifications for the current user.

| | |
|---|---|
| **Method** | `PATCH` |
| **Path** | `/me/notifications` |
| **Auth** | **Required** (Bearer token) |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
Content-Type: application/json
```

**Request Body:**

```json
{
  "is_notifications_enabled": false
}
```

| Field | Type | Rules |
|-------|------|-------|
| `is_notifications_enabled` | boolean | **Required.** `true` or `false`. |

**Success Response (200):**

```json
{
  "success": true,
  "message": "تم تحديث تفضيلات الإشعارات بنجاح",
  "data": {
    "is_notifications_enabled": false
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 422 | Validation fails | `{"message":"...","errors":{"is_notifications_enabled":["The is notifications enabled field is required."]}}` |

---

### 2.6 Logout

**Purpose:** Revoke the current token (log the user out from this device).

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/logout` |
| **Auth** | **Required** (Bearer token) |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200):**

```json
{
  "success": true,
  "message": "تم تسجيل الخروج بنجاح",
  "data": null
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |

---

### 2.7 Delete Account

**Purpose:** Permanently delete the authenticated user's account. Requires password confirmation.

| | |
|---|---|
| **Method** | `DELETE` |
| **Path** | `/delete-account` |
| **Auth** | **Required** (Bearer token) |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
Content-Type: application/json
```

**Request Body:**

```json
{
  "password": "MyStr0ng@Pass"
}
```

| Field | Type | Rules |
|-------|------|-------|
| `password` | string | **Required.** The user's current password for confirmation. |

**Success Response (200):**

```json
{
  "success": true,
  "message": "تم حذف الحساب بنجاح",
  "data": null
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 401 | Wrong password | `{"success":false,"message":"كلمة المرور غير صحيحة"}` |
| 422 | Password not provided | `{"success":false,"message":"...","errors":{"password":["كلمة المرور مطلوبة للتأكيد"]}}` |

---

## 3. Roadmaps Endpoints

> These endpoints are **public** (no auth required). They return only **active** roadmaps for search/show.

### 3.1 List All Roadmaps

**Purpose:** Get a paginated list of all roadmaps with optional filters and sorting.

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/roadmaps` |
| **Auth** | Not required |

**Request Headers:**

```
Accept: application/json
```

**Query Parameters (all optional):**

| Param | Type | Rules | Default |
|-------|------|-------|---------|
| `level` | string | One of: `beginner`, `intermediate`, `advanced` | — |
| `is_active` | boolean | `true` or `false` | — |
| `search` | string | Min 2, max 100 chars. Searches title & description. | — |
| `order_by` | string | One of: `created_at`, `title`, `level`, `updated_at`, `enrollments_count` | `created_at` |
| `order_direction` | string | `asc` or `desc` | `desc` |
| `per_page` | integer | 1–100 | `10` |
| `page` | integer | Min 1 | `1` |

**Example:** `GET /roadmaps?level=beginner&order_by=title&order_direction=asc&per_page=5`

**Request Body:** No body

**Success Response (200):**

```json
{
  "data": [
    {
      "id": 1,
      "title": "Backend with Laravel",
      "description": "Learn backend development step by step",
      "level": "beginner",
      "level_arabic": "مبتدئ",
      "is_active": true,
      "created_at": "2026-01-15 10:00:00",
      "updated_at": "2026-02-01 12:00:00"
    },
    {
      "id": 2,
      "title": "Frontend with React",
      "description": "Master modern frontend development",
      "level": "intermediate",
      "level_arabic": "متوسط",
      "is_active": true,
      "created_at": "2026-01-20 14:00:00",
      "updated_at": "2026-02-05 09:00:00"
    }
  ],
  "success": true,
  "message": "تم جلب المسارات بنجاح",
  "meta": {
    "total": 12,
    "per_page": 5,
    "current_page": 1,
    "last_page": 3,
    "from": 1,
    "to": 5,
    "version": "1.0.0",
    "api_version": "v1"
  },
  "links": {
    "first": "...?page=1",
    "last": "...?page=3",
    "prev": null,
    "next": "...?page=2"
  }
}
```

> **Note:** This endpoint uses `RoadmapResource` which adds `level_arabic` and `meta.version` / `meta.api_version` fields.

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 422 | Invalid filter value | `{"success":false,"message":"خطأ في التحقق من البيانات","errors":{"level":["المستوى يجب أن يكون أحد: beginner, intermediate, advanced"]}}` |

---

### 3.2 Search Roadmaps

**Purpose:** Smart search across active roadmaps by title or description, with optional level filter.

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/roadmaps/search` |
| **Auth** | Not required |

**Request Headers:**

```
Accept: application/json
```

**Query Parameters:**

| Param | Type | Rules | Default |
|-------|------|-------|---------|
| `query` | string | **Required.** Min 2, max 100 chars. | — |
| `level` | string | Optional. One of: `beginner`, `intermediate`, `advanced` | — |
| `limit` | integer | Optional. 1–20. | `10` |

**Example:** `GET /roadmaps/search?query=laravel&level=beginner&limit=5`

**Request Body:** No body

**Success Response (200):**

```json
{
  "success": true,
  "message": "تم البحث بنجاح",
  "data": {
    "roadmaps": [
      {
        "id": 1,
        "title": "Backend with Laravel",
        "description": "Learn backend development step by step",
        "level": "beginner",
        "level_arabic": "مبتدئ",
        "is_active": true,
        "created_at": "2026-01-15 10:00:00",
        "updated_at": "2026-02-01 12:00:00"
      }
    ],
    "meta": {
      "total_results": 1,
      "search_query": "laravel"
    }
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 422 | Missing or short query | `{"success":false,"message":"خطأ في التحقق من البيانات","errors":{"query":["كلمة البحث مطلوبة"]}}` |
| 422 | Invalid level | `{"success":false,"message":"...","errors":{"level":["المستوى يجب أن يكون أحد: beginner, intermediate, advanced"]}}` |

---

### 3.3 Show Single Roadmap

**Purpose:** Get detailed information about a specific roadmap by ID.

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/roadmaps/{id}` |
| **Auth** | Not required |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `id` | integer | **Required.** The roadmap ID. Must exist in `roadmaps` table. |

**Query Parameters (all optional):**

| Param | Type | Rules | Default |
|-------|------|-------|---------|
| `with_details` | boolean | If `true`, includes learning units with lesson counts. | `false` |
| `include_content` | boolean | If `true`, includes chat room and units with lessons. | `false` |
| `track_view` | boolean | If `true`, tracks view count. | `true` |

**Request Headers:**

```
Accept: application/json
```

**Example:** `GET /roadmaps/1?with_details=true`

**Request Body:** No body

**Success Response (200):**

```json
{
  "success": true,
  "message": "تم جلب المسار بنجاح",
  "data": {
    "id": 1,
    "title": "Backend with Laravel",
    "description": "Learn backend development step by step",
    "level": "beginner",
    "level_arabic": "مبتدئ",
    "is_active": true,
    "created_at": "2026-01-15 10:00:00",
    "updated_at": "2026-02-01 12:00:00"
  }
}
```

When `with_details=true`, the roadmap model also loads `learningUnits` (with `lessons_count`) and counts:

```json
{
  "success": true,
  "message": "تم جلب المسار بنجاح",
  "data": {
    "id": 1,
    "title": "Backend with Laravel",
    "description": "Learn backend development step by step",
    "level": "beginner",
    "level_arabic": "مبتدئ",
    "is_active": true,
    "created_at": "2026-01-15 10:00:00",
    "updated_at": "2026-02-01 12:00:00",
    "enrollments_count": 42,
    "learning_units_count": 6
  }
}
```

> **Note:** The response wraps data through `RoadmapResource`. Eager-loaded relations (`learningUnits`, `chatRoom`) are included in the underlying model when query params request them.

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 404 | Roadmap not found | `{"success":false,"message":"المسار غير موجود"}` |
| 422 | Invalid ID format | `{"success":false,"message":"خطأ في التحقق من البيانات","errors":{"id":["معرف المسار يجب أن يكون رقمًا"]}}` |

---

### 3.4 Get Roadmap Enrollments

**Purpose:** List all enrollments (users) for a specific roadmap.

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/roadmaps/{id}/enrollments` |
| **Auth** | Not required |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `id` | integer | **Required.** The roadmap ID. |

**Request Headers:**

```
Accept: application/json
```

**Request Body:** No body

**Success Response (200):**

```json
{
  "success": true,
  "message": "Success",
  "data": [
    {
      "id": 1,
      "user_id": 3,
      "roadmap_id": 1,
      "status": "active",
      "xp_points": 150,
      "started_at": "2026-02-01T10:00:00.000000Z",
      "completed_at": null,
      "created_at": "2026-02-01T10:00:00.000000Z",
      "updated_at": "2026-02-15T14:00:00.000000Z",
      "user": {
        "id": 3,
        "username": "sara_dev",
        "email": "sara@example.com",
        "profile_picture": null
      }
    }
  ]
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 404 | Roadmap not found | `{"message":"No query results for model [App\\Models\\Roadmap] 999"}` (standard Laravel 404) |

---

## 4. Enrollments

> All enrollment endpoints require **authentication** (`auth:sanctum`).

### 4.1 Enroll in a Roadmap

**Purpose:** Subscribe the current user to a roadmap. Also returns the roadmap's chat room.

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/roadmaps/{id}/enroll` |
| **Auth** | **Required** (Bearer token) |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `id` | integer | **Required.** The roadmap ID. |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (201) — new enrollment:**

```json
{
  "success": true,
  "message": "تم الاشتراك في المسار بنجاح",
  "data": {
    "enrollment": {
      "id": 10,
      "user_id": 1,
      "roadmap_id": 3,
      "status": "active",
      "xp_points": 0,
      "started_at": "2026-02-20T10:00:00.000000Z",
      "completed_at": null,
      "created_at": "2026-02-20T10:00:00.000000Z",
      "updated_at": "2026-02-20T10:00:00.000000Z"
    },
    "chat_room": {
      "id": 3,
      "name": "غرفة دردشة - Backend with Laravel",
      "is_active": true,
      "roadmap_id": 3,
      "created_at": "2026-01-15 10:00:00",
      "updated_at": "2026-01-15 10:00:00"
    }
  }
}
```

**Already enrolled (200):**

```json
{
  "success": true,
  "message": "أنت مشترك بالفعل في هذا المسار",
  "data": {
    "enrollment": { "..." : "..." },
    "chat_room": { "..." : "..." }
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Roadmap is not active | `{"success":false,"message":"هذا المسار غير متاح حاليا"}` |
| 404 | Roadmap not found | Standard Laravel 404 |

---

### 4.2 My Enrollments

**Purpose:** List all roadmaps the current user is enrolled in (paginated).

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/me/enrollments` |
| **Auth** | **Required** (Bearer token) |

**Query Parameters:**

| Param | Type | Default |
|-------|------|---------|
| `per_page` | integer | `15` |
| `page` | integer | `1` |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200) — paginated:**

```json
{
  "success": true,
  "message": "Enrollments retrieved successfully",
  "data": [
    {
      "id": 10,
      "user_id": 1,
      "roadmap_id": 3,
      "status": "active",
      "xp_points": 150,
      "started_at": "2026-02-01T10:00:00.000000Z",
      "completed_at": null,
      "created_at": "2026-02-01T10:00:00.000000Z",
      "updated_at": "2026-02-15T14:00:00.000000Z",
      "roadmap": {
        "id": 3,
        "title": "Backend with Laravel",
        "level": "beginner",
        "is_active": true
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 2,
    "from": 1,
    "to": 2
  },
  "links": {
    "first": "...?page=1",
    "last": "...?page=1",
    "prev": null,
    "next": null
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |

---

### 4.3 Update Enrollment Status

**Purpose:** Change enrollment status to `active`, `paused`, or `completed`.

| | |
|---|---|
| **Method** | `PATCH` |
| **Path** | `/me/enrollments/{roadmapId}/status` |
| **Auth** | **Required** (Bearer token) |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `roadmapId` | integer | **Required.** The roadmap ID. |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
Content-Type: application/json
```

**Request Body:**

```json
{
  "status": "completed"
}
```

| Field | Type | Rules |
|-------|------|-------|
| `status` | string | **Required.** One of: `active`, `paused`, `completed`. |

**Success Response (200):**

```json
{
  "success": true,
  "message": "تم تحديث حالة الاشتراك",
  "data": {
    "id": 10,
    "user_id": 1,
    "roadmap_id": 3,
    "status": "completed",
    "xp_points": 300,
    "started_at": "2026-02-01T10:00:00.000000Z",
    "completed_at": "2026-02-20T11:00:00.000000Z",
    "created_at": "2026-02-01T10:00:00.000000Z",
    "updated_at": "2026-02-20T11:00:00.000000Z"
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 404 | Enrollment not found | Standard Laravel 404 |
| 422 | Invalid status | `{"success":false,"message":"...","errors":{"status":["Status must be one of: active, paused, completed"]}}` |

---

### 4.4 Unenroll from a Roadmap

**Purpose:** Cancel enrollment (delete) from a roadmap.

| | |
|---|---|
| **Method** | `DELETE` |
| **Path** | `/roadmaps/{id}/unenroll` |
| **Auth** | **Required** (Bearer token) |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `id` | integer | **Required.** The roadmap ID. |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200):**

```json
{
  "success": true,
  "message": "تم إلغاء الاشتراك بنجاح",
  "data": null
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 404 | Not enrolled | `{"success":false,"message":"أنت غير مشترك في هذا المسار"}` |

---

## 5. Units / Lessons / SubLessons / Resources

> All endpoints in this section require **authentication** + **enrollment** in the parent roadmap (`auth:sanctum` + `enrolled` middleware). The `enrolled` middleware checks that the user is enrolled in the roadmap that owns the requested content.

### 5.1 List Learning Units

**Purpose:** Get all learning units for a specific roadmap, ordered by position.

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/roadmaps/{roadmapId}/units` |
| **Auth** | **Required** (Bearer token + enrolled) |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `roadmapId` | integer | **Required.** The roadmap ID the user is enrolled in. |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200):**

```json
{
  "success": true,
  "message": "Success",
  "data": [
    {
      "id": 1,
      "title": "Introduction to PHP",
      "roadmap_id": 3,
      "position": 1,
      "is_active": true,
      "unit_type": "quiz",
      "created_at": "2026-01-15T10:00:00.000000Z",
      "updated_at": "2026-01-15T10:00:00.000000Z",
      "lessons_count": 5,
      "quizzes_count": 1,
      "challenges_count": 0
    },
    {
      "id": 2,
      "title": "Coding Challenge: PHP Basics",
      "roadmap_id": 3,
      "position": 2,
      "is_active": true,
      "unit_type": "challenge",
      "created_at": "2026-01-16T10:00:00.000000Z",
      "updated_at": "2026-01-16T10:00:00.000000Z",
      "lessons_count": 0,
      "quizzes_count": 0,
      "challenges_count": 1
    }
  ]
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Not enrolled in this roadmap | Enrollment middleware returns 403 |

---

### 5.2 Show Single Learning Unit

**Purpose:** Get details of one learning unit within a roadmap.

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/roadmaps/{roadmapId}/units/{unitId}` |
| **Auth** | **Required** (Bearer token + enrolled) |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `roadmapId` | integer | **Required.** The roadmap ID. |
| `unitId` | integer | **Required.** The unit ID (must belong to the roadmap). |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200):**

```json
{
  "success": true,
  "message": "Success",
  "data": {
    "id": 1,
    "title": "Introduction to PHP",
    "roadmap_id": 3,
    "position": 1,
    "is_active": true,
    "unit_type": "quiz",
    "created_at": "2026-01-15T10:00:00.000000Z",
    "updated_at": "2026-01-15T10:00:00.000000Z"
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Not enrolled | Enrollment middleware returns 403 |
| 404 | Unit not found or doesn't belong to roadmap | Standard Laravel 404 |

---

### 5.3 List Lessons

**Purpose:** Get all **active** lessons in a learning unit, ordered by position.

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/units/{unitId}/lessons` |
| **Auth** | **Required** (Bearer token + enrolled) |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `unitId` | integer | **Required.** The learning unit ID. |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200):**

```json
{
  "success": true,
  "message": "Success",
  "data": [
    {
      "id": 10,
      "title": "What is PHP?",
      "position": 1,
      "description": "A brief introduction to PHP",
      "created_at": "2026-01-15T10:00:00.000000Z",
      "learning_unit_id": 1,
      "sub_lessons_count": 3
    },
    {
      "id": 11,
      "title": "Variables and Types",
      "position": 2,
      "description": "Learn about PHP variables",
      "created_at": "2026-01-16T10:00:00.000000Z",
      "learning_unit_id": 1,
      "sub_lessons_count": 2
    }
  ]
}
```

> **Note:** Only lessons with `is_active = true` are returned. The response selects specific columns: `id`, `title`, `position`, `description`, `created_at`, `learning_unit_id`.

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Not enrolled | Enrollment middleware returns 403 |

---

### 5.4 Show Single Lesson

**Purpose:** Get a single active lesson with its sub-lessons.

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/lessons/{lessonId}` |
| **Auth** | **Required** (Bearer token + enrolled) |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `lessonId` | integer | **Required.** The lesson ID (must be active). |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200):**

```json
{
  "data": {
    "id": 10,
    "title": "What is PHP?",
    "description": "A brief introduction to PHP",
    "position": 1,
    "created_at": "2026-01-15T10:00:00.000000Z",
    "sub_lessons": [
      {
        "id": 20,
        "description": "History of PHP",
        "position": 1,
        "lesson_id": 10,
        "created_at": "2026-01-15T10:00:00.000000Z"
      },
      {
        "id": 21,
        "description": "PHP vs other languages",
        "position": 2,
        "lesson_id": 10,
        "created_at": "2026-01-15T11:00:00.000000Z"
      }
    ]
  }
}
```

> **Note:** This endpoint returns `{"data": ...}` directly (not wrapped in ApiResponse `success`/`message` envelope).

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Not enrolled | Enrollment middleware returns 403 |
| 404 | Lesson not found or inactive | Standard Laravel 404 |

---

### 5.5 List SubLessons

**Purpose:** Get all sub-lessons for a specific lesson, ordered by position.

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/lessons/{lessonId}/sub-lessons` |
| **Auth** | **Required** (Bearer token + enrolled) |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `lessonId` | integer | **Required.** The lesson ID. |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200):**

```json
{
  "success": true,
  "message": "Success",
  "data": [
    {
      "id": 20,
      "description": "History of PHP",
      "position": 1,
      "created_at": "2026-01-15T10:00:00.000000Z",
      "lesson_id": 10,
      "resources_count": 3
    },
    {
      "id": 21,
      "description": "PHP vs other languages",
      "position": 2,
      "created_at": "2026-01-15T11:00:00.000000Z",
      "lesson_id": 10,
      "resources_count": 2
    }
  ]
}
```

> **Note:** Response selects specific columns: `id`, `description`, `position`, `created_at`, `lesson_id`.

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Not enrolled | Enrollment middleware returns 403 |

---

### 5.6 Show Single SubLesson

**Purpose:** Get a specific sub-lesson with its resources.

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/lessons/{lessonId}/sub-lessons/{subLessonId}` |
| **Auth** | **Required** (Bearer token + enrolled) |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `lessonId` | integer | **Required.** The parent lesson ID. |
| `subLessonId` | integer | **Required.** The sub-lesson ID (must belong to the lesson). |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200):**

```json
{
  "data": {
    "id": 20,
    "description": "History of PHP",
    "position": 1,
    "created_at": "2026-01-15T10:00:00.000000Z",
    "resources": [
      {
        "id": 50,
        "title": "PHP Official Docs",
        "type": "article",
        "language": "en",
        "link": "https://www.php.net/docs.php",
        "created_at": "2026-01-15T10:00:00.000000Z"
      },
      {
        "id": 51,
        "title": "PHP Crash Course",
        "type": "video",
        "language": "ar",
        "link": "https://youtube.com/watch?v=example",
        "created_at": "2026-01-15T11:00:00.000000Z"
      }
    ]
  }
}
```

> **Note:** This endpoint returns `{"data": ...}` directly (not wrapped in ApiResponse envelope).

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Not enrolled | Enrollment middleware returns 403 |
| 404 | SubLesson not found | Standard Laravel 404 |

---

### 5.7 List Resources

**Purpose:** Get all resources for a specific sub-lesson.

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/sub-lessons/{subLessonId}/resources` |
| **Auth** | **Required** (Bearer token + enrolled) |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `subLessonId` | integer | **Required.** The sub-lesson ID. |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200):**

```json
{
  "success": true,
  "message": "Success",
  "data": [
    {
      "id": 50,
      "title": "PHP Official Docs",
      "type": "article",
      "language": "en",
      "link": "https://www.php.net/docs.php",
      "created_at": "2026-01-15T10:00:00.000000Z"
    },
    {
      "id": 51,
      "title": "PHP Crash Course",
      "type": "video",
      "language": "ar",
      "link": "https://youtube.com/watch?v=example",
      "created_at": "2026-01-15T11:00:00.000000Z"
    }
  ]
}
```

> **Resource types:** `book`, `video`, `article`
> **Languages:** `ar`, `en`

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Not enrolled | Enrollment middleware returns 403 |

---

### 5.8 Show Single Resource

**Purpose:** Get details of a single resource.

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/sub-lessons/{subLessonId}/resources/{resourceId}` |
| **Auth** | **Required** (Bearer token + enrolled) |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `subLessonId` | integer | **Required.** The sub-lesson ID. |
| `resourceId` | integer | **Required.** The resource ID (must belong to the sub-lesson). |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200):**

```json
{
  "data": {
    "id": 50,
    "title": "PHP Official Docs",
    "type": "article",
    "language": "en",
    "link": "https://www.php.net/docs.php",
    "created_at": "2026-01-15T10:00:00.000000Z"
  }
}
```

> **Note:** This endpoint returns `{"data": ...}` directly.

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Not enrolled | Enrollment middleware returns 403 |
| 404 | Resource not found | Standard Laravel 404 |

---

## 6. Lesson Tracking

> All tracking endpoints require **authentication** + **enrollment** (`auth:sanctum` + `enrolled` middleware).
> These endpoints track whether a user has opened or completed a lesson.

### 6.1 Open Lesson (Track)

**Purpose:** Record that the user has opened/viewed a lesson. Creates a tracking record if one doesn't exist, or updates `last_updated_at`.

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/lessons/{lessonId}/track/open` |
| **Auth** | **Required** (Bearer token + enrolled) |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `lessonId` | integer | **Required.** The lesson ID (must be active). |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200):**

```json
{
  "success": true,
  "message": "تم تسجيل فتح الدرس",
  "data": {
    "lesson_id": 10,
    "is_complete": false,
    "last_updated_at": "2026-02-20T10:00:00.000000Z"
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Not enrolled in the parent roadmap | `{"success":false,"message":"يجب الاشتراك في المسار أولاً"}` |
| 404 | Lesson not found or inactive | Standard Laravel 404 |

---

### 6.2 Complete Lesson (Track)

**Purpose:** Mark a lesson as completed for the current user.

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/lessons/{lessonId}/track/complete` |
| **Auth** | **Required** (Bearer token + enrolled) |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `lessonId` | integer | **Required.** The lesson ID (must be active). |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200):**

```json
{
  "success": true,
  "message": "تم إكمال الدرس بنجاح",
  "data": {
    "lesson_id": 10,
    "is_complete": true,
    "last_updated_at": "2026-02-20T10:05:00.000000Z"
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Not enrolled in the parent roadmap | `{"success":false,"message":"يجب الاشتراك في المسار أولاً"}` |
| 404 | Lesson not found or inactive | Standard Laravel 404 |

---

### 6.3 Show Lesson Tracking Status

**Purpose:** Get the current tracking status (complete / not complete) for a lesson.

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/lessons/{lessonId}/track` |
| **Auth** | **Required** (Bearer token + enrolled) |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `lessonId` | integer | **Required.** The lesson ID. |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200) — tracking exists:**

```json
{
  "success": true,
  "message": "Success",
  "data": {
    "lesson_id": 10,
    "is_complete": true,
    "last_updated_at": "2026-02-20T10:05:00.000000Z"
  }
}
```

**Success Response (200) — no tracking record yet:**

```json
{
  "success": true,
  "message": "Success",
  "data": null
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Not enrolled | Enrollment middleware returns 403 |

---

### 6.4 Reset Lesson Tracking

**Purpose:** Reset a lesson back to "not completed" (keeps the tracking record but sets `is_complete = false`).

| | |
|---|---|
| **Method** | `DELETE` |
| **Path** | `/lessons/{lessonId}/track/reset` |
| **Auth** | **Required** (Bearer token + enrolled) |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `lessonId` | integer | **Required.** The lesson ID. |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200):**

```json
{
  "success": true,
  "message": "تم إعادة تعيين تتبع الدرس",
  "data": {
    "lesson_id": 10,
    "is_complete": false,
    "last_updated_at": "2026-02-20T10:10:00.000000Z"
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Not enrolled | Enrollment middleware returns 403 |
| 404 | No tracking record exists | `{"success":false,"message":"لا يوجد تتبع لهذا الدرس"}` |

---

### 6.5 All My Lesson Trackings

**Purpose:** Get all lesson tracking records for the current user (paginated), with lesson and unit info.

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/me/lessons/tracking` |
| **Auth** | **Required** (Bearer token + enrolled) |

**Query Parameters:**

| Param | Type | Default |
|-------|------|---------|
| `per_page` | integer | `20` |
| `page` | integer | `1` |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200) — paginated:**

```json
{
  "success": true,
  "message": "Tracking data retrieved successfully",
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "lesson_id": 10,
      "is_complete": true,
      "last_updated_at": "2026-02-20T10:05:00.000000Z",
      "created_at": "2026-02-18T09:00:00.000000Z",
      "updated_at": "2026-02-20T10:05:00.000000Z",
      "lesson": {
        "id": 10,
        "title": "What is PHP?",
        "learning_unit_id": 1,
        "learning_unit": {
          "id": 1,
          "title": "Introduction to PHP",
          "roadmap_id": 3
        }
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 20,
    "total": 5,
    "from": 1,
    "to": 5
  },
  "links": {
    "first": "...?page=1",
    "last": "...?page=1",
    "prev": null,
    "next": null
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |

---

### 6.6 My Lesson Stats

**Purpose:** Get summary statistics — total tracked lessons, completed count, and completion rate.

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/me/lessons/stats` |
| **Auth** | **Required** (Bearer token + enrolled) |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200):**

```json
{
  "success": true,
  "message": "Success",
  "data": {
    "total_tracked": 12,
    "completed": 8,
    "completion_rate": 66.67
  }
}
```

> `completion_rate` is a percentage (0–100), rounded to 2 decimals.

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |

---

## 7. Quizzes / Quiz Attempts

> All quiz endpoints require **authentication** + **enrollment** (`auth:sanctum` + `enrolled` middleware).
> Each learning unit has **at most one quiz**.

### 7.1 List Quizzes for a Unit

**Purpose:** Get the quiz (if any) for a specific learning unit. Returns one quiz or `null`.

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/units/{unitId}/quizzes` |
| **Auth** | **Required** (Bearer token + enrolled) |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `unitId` | integer | **Required.** The learning unit ID. |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200) — quiz exists:**

```json
{
  "success": true,
  "message": "Success",
  "data": {
    "id": 5,
    "learning_unit_id": 1,
    "is_active": true,
    "min_xp": 50,
    "max_xp": 100,
    "created_at": "2026-01-20T10:00:00.000000Z",
    "updated_at": "2026-01-20T10:00:00.000000Z",
    "learning_unit": {
      "id": 1,
      "title": "Introduction to PHP",
      "roadmap_id": 3
    }
  }
}
```

**Success Response (200) — no quiz:**

```json
{
  "success": true,
  "message": "Success",
  "data": null
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Not enrolled | Enrollment middleware returns 403 |

---

### 7.2 Start Quiz Attempt

**Purpose:** Start a new quiz attempt. Returns the quiz questions **without correct answers** and creates a new attempt record (score = 0).

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/quizzes/{quizId}` |
| **Auth** | **Required** (Bearer token + enrolled) |
| **Rate Limit** | 5 requests per minute |
| **Policy** | `QuizPolicy@view` — all lessons in prior units must be completed to unlock |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `quizId` | integer | **Required.** The quiz ID. |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (201):**

```json
{
  "success": true,
  "message": "Quiz attempt started successfully",
  "data": {
    "quiz": {
      "id": 5,
      "learning_unit_id": 1,
      "min_xp": 50,
      "max_xp": 100,
      "questions": [
        {
          "id": 101,
          "quiz_id": 5,
          "question_text": "What does PHP stand for?",
          "options": [
            "Personal Home Page",
            "PHP: Hypertext Preprocessor",
            "Pre-HTML Processor",
            "Public HTML Parser"
          ],
          "order": 1,
          "question_xp": 10
        },
        {
          "id": 102,
          "quiz_id": 5,
          "question_text": "Which symbol starts a PHP variable?",
          "options": ["#", "$", "@", "&"],
          "order": 2,
          "question_xp": 10
        }
      ]
    },
    "attempt_id": 42
  }
}
```

> **Note:** Questions do NOT include `correct_answer` — that field is excluded from the select.

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Quiz locked (previous lessons not completed) | `{"message":"This action is unauthorized."}` (QuizPolicy) |
| 404 | Quiz not found | Standard Laravel 404 |
| 429 | Rate limit exceeded | Too many requests |

---

### 7.3 Submit Quiz Attempt

**Purpose:** Submit answers for a quiz attempt. The server grades the answers, calculates the score, determines pass/fail, and awards XP to the enrollment (best-score system — only the improvement is added).

| | |
|---|---|
| **Method** | `PUT` |
| **Path** | `/quiz-attempts/{attemptId}/submit` |
| **Auth** | **Required** (Bearer token + enrolled) |
| **Rate Limit** | 5 requests per minute |
| **Policy** | `QuizAttemptPolicy@update` — attempt must belong to the user and not already submitted |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `attemptId` | integer | **Required.** The attempt ID returned from "Start Quiz Attempt". |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
Content-Type: application/json
```

**Request Body:**

```json
{
  "answers": {
    "101": "PHP: Hypertext Preprocessor",
    "102": "$"
  }
}
```

| Field | Type | Rules |
|-------|------|-------|
| `answers` | object (map) | **Required.** Keys are question IDs (as strings), values are the selected answer strings. Min 1 entry. |
| `answers.*` | string | **Required.** The chosen option text. |

> **How to build the answers object:** For each question in the quiz, use the question `id` as the key and the selected option text as the value.

**Success Response (200):**

```json
{
  "success": true,
  "message": "Quiz attempt submitted successfully",
  "data": {
    "attempt": {
      "id": 42,
      "quiz_id": 5,
      "user_id": 1,
      "answers": {
        "101": "PHP: Hypertext Preprocessor",
        "102": "$"
      },
      "score": 20,
      "passed": false,
      "created_at": "2026-02-20T10:00:00.000000Z",
      "updated_at": "2026-02-20T10:02:00.000000Z"
    },
    "score": 20,
    "passed": false,
    "earned_points": 20
  }
}
```

> **Scoring logic:**
> - Each correct answer adds `question_xp` to the score.
> - `passed` = `true` if `score >= quiz.min_xp`.
> - `earned_points` = `min(score, quiz.max_xp)`.
> - XP added to enrollment = improvement over previous best score only (delta system).

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Attempt doesn't belong to user, or already submitted | `{"message":"This action is unauthorized."}` (QuizAttemptPolicy) |
| 404 | Attempt not found | Standard Laravel 404 |
| 422 | Missing or invalid answers | `{"success":false,"message":"...","errors":{"answers":["Answers are required"]}}` |
| 429 | Rate limit exceeded | Too many requests |

---

### 7.4 Show Quiz Attempt

**Purpose:** Get the details of a specific quiz attempt (including questions with correct answers — for review).

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/quiz-attempts/{attemptId}` |
| **Auth** | **Required** (Bearer token + enrolled) |
| **Policy** | `QuizAttemptPolicy@view` — must be the attempt owner |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `attemptId` | integer | **Required.** The attempt ID. |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200):**

```json
{
  "success": true,
  "message": "Success",
  "data": {
    "id": 42,
    "quiz_id": 5,
    "user_id": 1,
    "answers": {
      "101": "PHP: Hypertext Preprocessor",
      "102": "$"
    },
    "score": 20,
    "passed": false,
    "created_at": "2026-02-20T10:00:00.000000Z",
    "updated_at": "2026-02-20T10:02:00.000000Z",
    "quiz": {
      "id": 5,
      "learning_unit_id": 1,
      "is_active": true,
      "min_xp": 50,
      "max_xp": 100,
      "questions": [
        {
          "id": 101,
          "quiz_id": 5,
          "question_text": "What does PHP stand for?",
          "options": ["Personal Home Page", "PHP: Hypertext Preprocessor", "Pre-HTML Processor", "Public HTML Parser"],
          "correct_answer": "PHP: Hypertext Preprocessor",
          "question_xp": 10,
          "order": 1
        }
      ],
      "learning_unit": {
        "id": 1,
        "title": "Introduction to PHP",
        "roadmap_id": 3
      }
    }
  }
}
```

> **Note:** Unlike "Start Attempt", this response **includes** `correct_answer` for each question (so the user can review).

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Not the attempt owner | `{"message":"This action is unauthorized."}` |
| 404 | Attempt not found | Standard Laravel 404 |

---

### 7.5 My Quiz Attempts

**Purpose:** List all of the current user's attempts for a specific quiz (paginated, newest first).

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/quizzes/{quizId}/my-attempts` |
| **Auth** | **Required** (Bearer token + enrolled) |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `quizId` | integer | **Required.** The quiz ID. |

**Query Parameters:**

| Param | Type | Default |
|-------|------|---------|
| `per_page` | integer | `15` |
| `page` | integer | `1` |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200) — paginated:**

```json
{
  "success": true,
  "message": "Attempts retrieved successfully",
  "data": [
    {
      "id": 42,
      "quiz_id": 5,
      "user_id": 1,
      "answers": { "101": "PHP: Hypertext Preprocessor", "102": "$" },
      "score": 20,
      "passed": true,
      "created_at": "2026-02-20T10:02:00.000000Z",
      "updated_at": "2026-02-20T10:02:00.000000Z",
      "quiz": {
        "id": 5,
        "learning_unit_id": 1,
        "min_xp": 50,
        "max_xp": 100
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 3,
    "from": 1,
    "to": 3
  },
  "links": {
    "first": "...?page=1",
    "last": "...?page=1",
    "prev": null,
    "next": null
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Not enrolled | Enrollment middleware returns 403 |

---

## 8. Challenges / Challenge Attempts

> All challenge endpoints require **authentication** + **enrollment** (`auth:sanctum` + `enrolled` middleware).
> Each challenge-type learning unit has **at most one challenge**.
> Challenges are coding problems that run test cases against user-submitted code via a compiler service.

### 8.1 List Challenges for a Unit

**Purpose:** Get the challenge (if any) for a specific learning unit. Also returns the user's current XP.

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/units/{unitId}/challenges` |
| **Auth** | **Required** (Bearer token + enrolled) |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `unitId` | integer | **Required.** The learning unit ID (must have `unit_type = challenge`). |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200) — challenge exists:**

```json
{
  "success": true,
  "message": "Success",
  "data": {
    "xp_points": 150,
    "challenge": {
      "id": 8,
      "learning_unit_id": 2,
      "title": "FizzBuzz in Python",
      "description": "Write a FizzBuzz program",
      "min_xp": 100,
      "language": "python",
      "starter_code": "# Write your solution here\n",
      "is_active": true,
      "is_unlocked": true,
      "created_at": "2026-01-20T10:00:00.000000Z",
      "updated_at": "2026-01-20T10:00:00.000000Z"
    }
  }
}
```

> **Note:** `test_cases` are **hidden** from the client. `is_unlocked` indicates whether the `ChallengePolicy` allows the user to attempt this challenge.

**Success Response (200) — no challenge for this unit:**

```json
{
  "success": true,
  "message": "Success",
  "data": {
    "xp_points": 150,
    "challenge": null
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Not enrolled | Enrollment middleware returns 403 |
| 422 | Unit is not a challenge unit | `{"success":false,"message":"This unit is not a challenge unit"}` |

---

### 8.2 Start Challenge Attempt

**Purpose:** Create a new attempt for a challenge. The attempt starts with the challenge's `starter_code`.

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/challenges/{challengeId}/attempts` |
| **Auth** | **Required** (Bearer token + enrolled) |
| **Rate Limit** | 5 requests per minute |
| **Policy** | `ChallengeAttemptPolicy@create` — challenge must be unlocked |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `challengeId` | integer | **Required.** The challenge ID. |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (201):**

```json
{
  "success": true,
  "message": "Attempt started successfully",
  "data": {
    "id": 25,
    "challenge_id": 8,
    "user_id": 1,
    "submitted_code": "# Write your solution here\n",
    "execution_output": null,
    "passed": false,
    "created_at": "2026-02-20T10:00:00.000000Z",
    "updated_at": "2026-02-20T10:00:00.000000Z"
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Challenge locked (not enough XP or prior quizzes not passed) | `{"message":"This action is unauthorized."}` |
| 404 | Challenge not found | Standard Laravel 404 |
| 429 | Rate limit exceeded | Too many requests |

---

### 8.3 Submit Challenge Attempt

**Purpose:** Submit code for a challenge attempt. The server runs the code against all test cases and returns pass/fail results per case.

| | |
|---|---|
| **Method** | `PUT` |
| **Path** | `/challenge-attempts/{challengeAttemptId}/submit` |
| **Auth** | **Required** (Bearer token + enrolled) |
| **Rate Limit** | 10 requests per minute |
| **Policy** | `ChallengeAttemptPolicy@update` — attempt must belong to the user |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `challengeAttemptId` | integer | **Required.** The attempt ID returned from "Start Challenge Attempt". |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
Content-Type: application/json
```

**Request Body:**

```json
{
  "code": "for i in range(1, 101):\n    if i % 15 == 0:\n        print('FizzBuzz')\n    elif i % 3 == 0:\n        print('Fizz')\n    elif i % 5 == 0:\n        print('Buzz')\n    else:\n        print(i)"
}
```

| Field | Type | Rules |
|-------|------|-------|
| `code` | string | **Required.** Min 1 character. The user's source code. |

**Success Response (200) — all test cases passed:**

```json
{
  "success": true,
  "message": "Attempt submitted successfully",
  "data": {
    "passed": true,
    "attempt": {
      "id": 25,
      "challenge_id": 8,
      "user_id": 1,
      "submitted_code": "for i in range(1, 101):\n    ...",
      "execution_output": "[{\"case\":1,\"passed\":true,...}]",
      "passed": true,
      "created_at": "2026-02-20T10:00:00.000000Z",
      "updated_at": "2026-02-20T10:05:00.000000Z"
    },
    "details": [
      {
        "case": 1,
        "passed": true,
        "output": "1\n2\nFizz\n4\nBuzz\n...",
        "expected_output": "1\n2\nFizz\n4\nBuzz\n...",
        "error": null
      },
      {
        "case": 2,
        "passed": true,
        "output": "FizzBuzz",
        "expected_output": "FizzBuzz",
        "error": null
      }
    ]
  }
}
```

**Success Response (200) — some test cases failed:**

```json
{
  "success": true,
  "message": "Attempt submitted successfully",
  "data": {
    "passed": false,
    "attempt": {
      "id": 25,
      "challenge_id": 8,
      "user_id": 1,
      "submitted_code": "print('hello')",
      "execution_output": "[...]",
      "passed": false,
      "created_at": "2026-02-20T10:00:00.000000Z",
      "updated_at": "2026-02-20T10:05:00.000000Z"
    },
    "details": [
      {
        "case": 1,
        "passed": false,
        "output": "hello",
        "expected_output": "1\n2\nFizz\n...",
        "error": null
      }
    ]
  }
}
```

> **Details array:** One entry per test case. `passed` tells you if that specific case matched. `error` contains compiler/runtime errors if any.

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Attempt doesn't belong to user | `{"message":"This action is unauthorized."}` |
| 403 | Attempt already passed | `{"success":false,"message":"This attempt has already been passed and cannot be resubmitted"}` |
| 404 | Attempt not found | Standard Laravel 404 |
| 422 | Missing code | `{"success":false,"message":"...","errors":{"code":["The code field is required."]}}` |
| 429 | Rate limit exceeded | Too many requests |

---

### 8.4 Show Challenge Attempt

**Purpose:** Get details of a specific challenge attempt (for review).

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/challenge-attempts/{challengeAttemptId}` |
| **Auth** | **Required** (Bearer token + enrolled) |
| **Policy** | `ChallengeAttemptPolicy@view` — must be the attempt owner |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `challengeAttemptId` | integer | **Required.** The attempt ID. |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200):**

```json
{
  "success": true,
  "message": "Success",
  "data": {
    "id": 25,
    "challenge_id": 8,
    "user_id": 1,
    "submitted_code": "for i in range(1, 101):\n    ...",
    "execution_output": "[{\"case\":1,\"passed\":true,...}]",
    "passed": true,
    "created_at": "2026-02-20T10:00:00.000000Z",
    "updated_at": "2026-02-20T10:05:00.000000Z",
    "challenge": {
      "id": 8,
      "learning_unit_id": 2,
      "title": "FizzBuzz in Python",
      "description": "Write a FizzBuzz program",
      "min_xp": 100,
      "language": "python",
      "starter_code": "# Write your solution here\n",
      "is_active": true,
      "created_at": "2026-01-20T10:00:00.000000Z",
      "updated_at": "2026-01-20T10:00:00.000000Z"
    }
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Not the attempt owner | `{"message":"This action is unauthorized."}` |
| 404 | Attempt not found | Standard Laravel 404 |

---

### 8.5 My Challenge Attempts

**Purpose:** List all of the current user's attempts for a specific challenge (paginated, newest first).

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/challenges/{challengeId}/my-attempts` |
| **Auth** | **Required** (Bearer token + enrolled) |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `challengeId` | integer | **Required.** The challenge ID. |

**Query Parameters:**

| Param | Type | Default |
|-------|------|---------|
| `per_page` | integer | `15` |
| `page` | integer | `1` |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200) — paginated:**

```json
{
  "success": true,
  "message": "Attempts retrieved successfully",
  "data": [
    {
      "id": 25,
      "challenge_id": 8,
      "user_id": 1,
      "submitted_code": "for i in range(1, 101):\n    ...",
      "execution_output": "[...]",
      "passed": true,
      "created_at": "2026-02-20T10:05:00.000000Z",
      "updated_at": "2026-02-20T10:05:00.000000Z",
      "challenge": {
        "id": 8,
        "title": "FizzBuzz in Python",
        "language": "python",
        "min_xp": 100
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 2,
    "from": 1,
    "to": 2
  },
  "links": {
    "first": "...?page=1",
    "last": "...?page=1",
    "prev": null,
    "next": null
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Not enrolled | Enrollment middleware returns 403 |

---

## 9. Community / Chat Messages

> Community endpoints require **authentication** (`auth:sanctum`).

### 9.1 My Community Rooms

**Purpose:** List all active chat rooms for roadmaps the current user is enrolled in.

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/me/community` |
| **Auth** | **Required** (Bearer token) |

**Query Parameters:**

| Param | Type | Default |
|-------|------|---------|
| `per_page` | integer | `15` |
| `page` | integer | `1` |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200) — paginated:**

```json
{
  "success": true,
  "message": "Community rooms retrieved successfully",
  "data": [
    {
      "id": 3,
      "name": "غرفة دردشة - Backend with Laravel",
      "is_active": true,
      "roadmap_id": 3,
      "created_at": "2026-01-15T10:00:00.000000Z",
      "updated_at": "2026-01-15T10:00:00.000000Z",
      "roadmap": {
        "id": 3,
        "title": "Backend with Laravel"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 2,
    "from": 1,
    "to": 2
  },
  "links": {
    "first": "...?page=1",
    "last": "...?page=1",
    "prev": null,
    "next": null
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |

---

### 9.2 List Chat Messages

**Purpose:** Get paginated messages for a roadmap's chat room. Accessible by enrolled users **or** admins.

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/roadmaps/{roadmapId}/chat/messages` |
| **Auth** | **Required** (Bearer token) |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `roadmapId` | integer | **Required.** The roadmap ID. |

**Query Parameters:**

| Param | Type | Default |
|-------|------|---------|
| `per_page` | integer | `30` |
| `page` | integer | `1` |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200) — paginated (newest first):**

```json
{
  "success": true,
  "message": "Messages retrieved successfully",
  "data": [
    {
      "id": 150,
      "chat_room_id": 3,
      "user_id": 5,
      "content": "Hello everyone!",
      "sent_at": "2026-02-20T10:05:00.000000Z",
      "edited_at": null,
      "created_at": "2026-02-20T10:05:00.000000Z",
      "updated_at": "2026-02-20T10:05:00.000000Z",
      "user": {
        "id": 5,
        "username": "ahmed_dev",
        "profile_picture": "https://example.com/avatars/5.jpg"
      }
    }
  ],
  "meta": { "current_page": 1, "last_page": 3, "per_page": 30, "total": 90, "from": 1, "to": 30 },
  "links": { "first": "...?page=1", "last": "...?page=3", "prev": null, "next": "...?page=2" }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Not enrolled & not admin | `{"success":false,"message":"You must be enrolled in this roadmap to view its chat."}` |
| 404 | Roadmap not found or no chat room | `{"success":false,"message":"Roadmap not found."}` or `{"success":false,"message":"Chat room is not available for this roadmap."}` |

---

### 9.3 Send Chat Message

**Purpose:** Send a message in a roadmap's chat room. User must be enrolled and not muted/banned.

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/roadmaps/{roadmapId}/chat/messages` |
| **Auth** | **Required** (Bearer token) |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `roadmapId` | integer | **Required.** The roadmap ID. |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
Content-Type: application/json
```

**Request Body:**

```json
{
  "content": "Hello everyone! I need help with PHP arrays."
}
```

| Field | Type | Rules |
|-------|------|-------|
| `content` | string | **Required.** Max 2000 characters. |

**Success Response (201):**

```json
{
  "success": true,
  "message": "Message sent successfully",
  "data": {
    "id": 151,
    "chat_room_id": 3,
    "user_id": 5,
    "content": "Hello everyone! I need help with PHP arrays.",
    "sent_at": "2026-02-20T10:10:00.000000Z",
    "edited_at": null,
    "created_at": "2026-02-20T10:10:00.000000Z",
    "updated_at": "2026-02-20T10:10:00.000000Z",
    "user": {
      "id": 5,
      "username": "ahmed_dev",
      "profile_picture": "https://example.com/avatars/5.jpg"
    }
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Not enrolled | `{"success":false,"message":"You must be enrolled in this roadmap to send messages."}` |
| 403 | User is banned | `{"success":false,"message":"You are banned from this chat room.","errors":{"reason":"Spamming"}}` |
| 403 | User is muted | `{"success":false,"message":"You are muted in this chat room until 2026-03-01 00:00:00.","errors":{"reason":"Inappropriate language","muted_until":"2026-03-01T00:00:00.000000Z"}}` |
| 404 | Roadmap / chat room not found | `{"success":false,"message":"Chat room is not available for this roadmap."}` |
| 422 | Validation error | `{"success":false,"message":"Validation failed.","errors":{"content":["The content field is required."]}}` |

---

### 9.4 Edit Chat Message

**Purpose:** Edit a chat message. Only the message owner or admin/tech_admin can edit.

| | |
|---|---|
| **Method** | `PATCH` |
| **Path** | `/chat/messages/{messageId}` |
| **Auth** | **Required** (Bearer token) |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `messageId` | integer | **Required.** The message ID. |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
Content-Type: application/json
```

**Request Body:**

```json
{
  "content": "Updated message content here."
}
```

| Field | Type | Rules |
|-------|------|-------|
| `content` | string | **Required.** Max 2000 characters. |

**Success Response (200):**

```json
{
  "success": true,
  "message": "Message updated successfully",
  "data": {
    "id": 151,
    "chat_room_id": 3,
    "user_id": 5,
    "content": "Updated message content here.",
    "sent_at": "2026-02-20T10:10:00.000000Z",
    "edited_at": "2026-02-20T10:15:00.000000Z",
    "created_at": "2026-02-20T10:10:00.000000Z",
    "updated_at": "2026-02-20T10:15:00.000000Z",
    "user": {
      "id": 5,
      "username": "ahmed_dev",
      "profile_picture": "https://example.com/avatars/5.jpg"
    }
  }
}
```

> **Note:** `edited_at` is set when a message is updated. You can use this to show "(edited)" in the UI.

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Not the message owner & not admin | `{"success":false,"message":"You are not authorized to edit this message."}` |
| 404 | Message not found | `{"success":false,"message":"Message not found."}` |
| 422 | Validation error | `{"success":false,"message":"Validation failed.","errors":{"content":["The content field is required."]}}` |

---

### 9.5 Delete Chat Message

**Purpose:** Soft-delete a chat message. Only the message owner or admin/tech_admin can delete.

| | |
|---|---|
| **Method** | `DELETE` |
| **Path** | `/chat/messages/{messageId}` |
| **Auth** | **Required** (Bearer token) |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `messageId` | integer | **Required.** The message ID. |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200):**

```json
{
  "success": true,
  "message": "Message deleted successfully",
  "data": null
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Not the message owner & not admin | `{"success":false,"message":"You are not authorized to delete this message."}` |
| 404 | Message not found | `{"success":false,"message":"Message not found."}` |

---

## 10. Chatbot

> All chatbot endpoints require **authentication** (`auth:sanctum`).
> The chatbot is an AI "Smart Teacher" that answers programming questions. Each user can have multiple sessions (conversations).

### 10.1 List My Chatbot Sessions

**Purpose:** Get all chatbot sessions for the current user (paginated, most recently active first).

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/chatbot/sessions` |
| **Auth** | **Required** (Bearer token) |

**Query Parameters:**

| Param | Type | Default |
|-------|------|---------|
| `per_page` | integer | `15` |
| `page` | integer | `1` |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200) — paginated:**

```json
{
  "success": true,
  "message": "Sessions retrieved successfully",
  "data": [
    {
      "id": 12,
      "user_id": 1,
      "title": "PHP array functions",
      "last_activity_at": "2026-02-20T10:30:00.000000Z",
      "created_at": "2026-02-20T09:00:00.000000Z",
      "updated_at": "2026-02-20T10:30:00.000000Z"
    },
    {
      "id": 8,
      "user_id": 1,
      "title": "What is MVC?",
      "last_activity_at": "2026-02-19T14:00:00.000000Z",
      "created_at": "2026-02-19T13:00:00.000000Z",
      "updated_at": "2026-02-19T14:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 2,
    "from": 1,
    "to": 2
  },
  "links": {
    "first": "...?page=1",
    "last": "...?page=1",
    "prev": null,
    "next": null
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |

---

### 10.2 Create Chatbot Session

**Purpose:** Create a new empty chatbot session (conversation). You can optionally give it a title.

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/chatbot/sessions` |
| **Auth** | **Required** (Bearer token) |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
Content-Type: application/json
```

**Request Body:**

```json
{
  "title": "Help with Laravel Eloquent"
}
```

| Field | Type | Rules |
|-------|------|-------|
| `title` | string | **Optional.** Max 255 characters. If omitted, title will be `null`. |

**Success Response (201):**

```json
{
  "success": true,
  "message": "Session created successfully",
  "data": {
    "id": 13,
    "user_id": 1,
    "title": "Help with Laravel Eloquent",
    "last_activity_at": null,
    "created_at": "2026-02-20T11:00:00.000000Z",
    "updated_at": "2026-02-20T11:00:00.000000Z"
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 422 | Title too long | `{"success":false,"message":"...","errors":{"title":["The title may not be greater than 255 characters."]}}` |

---

### 10.3 List Session Messages (Conversation History)

**Purpose:** Get all messages in a specific chatbot session (paginated, chronological order).

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/chatbot/sessions/{id}/messages` |
| **Auth** | **Required** (Bearer token) |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `id` | integer | **Required.** The chatbot session ID (must belong to you). |

**Query Parameters:**

| Param | Type | Default |
|-------|------|---------|
| `per_page` | integer | `30` |
| `page` | integer | `1` |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200) — paginated:**

```json
{
  "success": true,
  "message": "Messages retrieved successfully",
  "data": [
    {
      "id": 100,
      "chatbot_session_id": 12,
      "role": "user",
      "body": "What is an array in PHP?",
      "tokens_used": null,
      "created_at": "2026-02-20T09:00:00.000000Z",
      "updated_at": "2026-02-20T09:00:00.000000Z"
    },
    {
      "id": 101,
      "chatbot_session_id": 12,
      "role": "assistant",
      "body": "An array in PHP is a data structure that stores multiple values under a single variable...",
      "tokens_used": 150,
      "created_at": "2026-02-20T09:00:05.000000Z",
      "updated_at": "2026-02-20T09:00:05.000000Z"
    }
  ],
  "meta": { "current_page": 1, "last_page": 1, "per_page": 30, "total": 4, "from": 1, "to": 4 },
  "links": { "first": "...?page=1", "last": "...?page=1", "prev": null, "next": null }
}
```

> **Message roles:** `"user"` = your message, `"assistant"` = AI reply.

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Session belongs to another user | `{"success":false,"message":"You can only access your own sessions."}` |
| 404 | Session not found | `{"success":false,"message":"Session not found."}` |

---

### 10.4 Send Message to Session

**Purpose:** Send a message to an existing chatbot session. The AI will generate a reply and both messages are stored.

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/chatbot/sessions/{id}/messages` |
| **Auth** | **Required** (Bearer token) |
| **Rate Limit** | 15 requests per minute |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `id` | integer | **Required.** The chatbot session ID (must belong to you). |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
Content-Type: application/json
```

**Request Body:**

```json
{
  "message": "How do I use array_map in PHP?"
}
```

| Field | Type | Rules |
|-------|------|-------|
| `message` | string | **Required.** Max 5000 characters. |

> **Note:** `session_id` is NOT needed in the body here — it's taken from the URL path.

**Success Response (201):**

```json
{
  "success": true,
  "message": "Reply generated successfully",
  "data": {
    "session": {
      "id": 12,
      "title": "PHP array functions",
      "last_activity_at": "2026-02-20T10:35:00.000000Z"
    },
    "user_message": {
      "id": 102,
      "chatbot_session_id": 12,
      "role": "user",
      "body": "How do I use array_map in PHP?",
      "tokens_used": null,
      "created_at": "2026-02-20T10:35:00.000000Z",
      "updated_at": "2026-02-20T10:35:00.000000Z"
    },
    "assistant_message": {
      "id": 103,
      "chatbot_session_id": 12,
      "role": "assistant",
      "body": "array_map() applies a callback function to each element of an array...",
      "tokens_used": 200,
      "created_at": "2026-02-20T10:35:03.000000Z",
      "updated_at": "2026-02-20T10:35:03.000000Z"
    }
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Session belongs to another user | `{"success":false,"message":"You can only access your own sessions."}` |
| 404 | Session not found | `{"success":false,"message":"Session not found."}` |
| 422 | Missing message | `{"success":false,"message":"...","errors":{"message":["Message is required."]}}` |
| 429 | Rate limit exceeded | Too many requests |

---

### 10.5 Send Message — Mobile Quick Endpoint ⭐

**Purpose:** Send a message with optional auto-session creation. If `session_id` is omitted, the server creates a new session automatically using the first 50 characters of the message as the title. **This is the recommended endpoint for mobile apps.**

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/chatbot/messages` |
| **Auth** | **Required** (Bearer token) |
| **Rate Limit** | 15 requests per minute |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
Content-Type: application/json
```

**Request Body — with existing session:**

```json
{
  "message": "Can you explain closures?",
  "session_id": 12
}
```

**Request Body — auto-create session (omit session_id):**

```json
{
  "message": "What is dependency injection in Laravel?"
}
```

| Field | Type | Rules |
|-------|------|-------|
| `message` | string | **Required.** Max 5000 characters. |
| `session_id` | integer | **Optional.** If provided, must exist in `chatbot_sessions` table and belong to you. If omitted, a new session is auto-created. |

**Success Response (201):**

```json
{
  "success": true,
  "message": "Reply generated successfully",
  "data": {
    "session": {
      "id": 14,
      "title": "What is dependency injection in Laravel?",
      "last_activity_at": "2026-02-20T11:00:05.000000Z"
    },
    "user_message": {
      "id": 104,
      "chatbot_session_id": 14,
      "role": "user",
      "body": "What is dependency injection in Laravel?",
      "tokens_used": null,
      "created_at": "2026-02-20T11:00:00.000000Z",
      "updated_at": "2026-02-20T11:00:00.000000Z"
    },
    "assistant_message": {
      "id": 105,
      "chatbot_session_id": 14,
      "role": "assistant",
      "body": "Dependency injection is a design pattern where dependencies are provided to a class...",
      "tokens_used": 180,
      "created_at": "2026-02-20T11:00:05.000000Z",
      "updated_at": "2026-02-20T11:00:05.000000Z"
    }
  }
}
```

> **Tip for mobile:** Use this single endpoint for everything. Save the `session.id` from the first response to continue the conversation.

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | session_id belongs to another user | `{"success":false,"message":"You can only access your own sessions."}` |
| 404 | session_id not found | `{"success":false,"message":"Session not found."}` |
| 422 | Missing message | `{"success":false,"message":"...","errors":{"message":["Message is required."]}}` |
| 422 | Invalid session_id | `{"success":false,"message":"...","errors":{"session_id":["The selected session does not exist."]}}` |
| 429 | Rate limit exceeded | Too many requests |

---

### 10.6 Delete Chatbot Session

**Purpose:** Delete a chatbot session and all its messages (cascade delete).

| | |
|---|---|
| **Method** | `DELETE` |
| **Path** | `/chatbot/sessions/{id}` |
| **Auth** | **Required** (Bearer token) |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `id` | integer | **Required.** The chatbot session ID (must belong to you). |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200):**

```json
{
  "success": true,
  "message": "Session deleted successfully",
  "data": null
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Session belongs to another user | `{"success":false,"message":"You can only access your own sessions."}` |
| 404 | Session not found | `{"success":false,"message":"Session not found."}` |

---

## 11. Notifications

> All notification endpoints require **authentication** (`auth:sanctum`).

### 11.1 List My Notifications

**Purpose:** Get all notifications for the current user (paginated, newest first).

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/notifications` |
| **Auth** | **Required** (Bearer token) |

**Query Parameters:**

| Param | Type | Default |
|-------|------|---------|
| `per_page` | integer | `15` |
| `page` | integer | `1` |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200) — paginated:**

```json
{
  "success": true,
  "message": "Notifications retrieved successfully",
  "data": [
    {
      "id": 30,
      "user_id": 1,
      "title": "Quiz Passed!",
      "body": "You passed the PHP Basics quiz with 90 XP.",
      "type": "quiz_result",
      "read_at": null,
      "created_at": "2026-02-20T10:00:00.000000Z",
      "updated_at": "2026-02-20T10:00:00.000000Z"
    },
    {
      "id": 28,
      "user_id": 1,
      "title": "New Announcement",
      "body": "Check out the latest tech opportunities.",
      "type": "announcement",
      "read_at": "2026-02-19T15:00:00.000000Z",
      "created_at": "2026-02-19T14:00:00.000000Z",
      "updated_at": "2026-02-19T15:00:00.000000Z"
    }
  ],
  "meta": { "current_page": 1, "last_page": 1, "per_page": 15, "total": 5, "from": 1, "to": 5 },
  "links": { "first": "...?page=1", "last": "...?page=1", "prev": null, "next": null }
}
```

> **Tip:** `read_at = null` means unread. You can use this to show a badge count.

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |

---

### 11.2 Mark Notification as Read

**Purpose:** Mark a single notification as read. Only the notification owner can do this.

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/notifications/{id}/read` |
| **Auth** | **Required** (Bearer token) |

**Path Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `id` | integer | **Required.** The notification ID. |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200):**

```json
{
  "success": true,
  "message": "Notification marked as read",
  "data": {
    "id": 30,
    "user_id": 1,
    "title": "Quiz Passed!",
    "body": "You passed the PHP Basics quiz with 90 XP.",
    "type": "quiz_result",
    "read_at": "2026-02-20T10:10:00.000000Z",
    "created_at": "2026-02-20T10:00:00.000000Z",
    "updated_at": "2026-02-20T10:10:00.000000Z"
  }
}
```

**Already read (200):**

```json
{
  "success": true,
  "message": "Notification already marked as read",
  "data": { "..." : "..." }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Not the notification owner | `{"success":false,"message":"Unauthorized. You can only mark your own notifications."}` |
| 404 | Notification not found | Standard Laravel 404 |

---

## 12. Announcements

> All announcement endpoints require **authentication** (`auth:sanctum`).
> These are read-only endpoints for users. Admin management is in [Section 13](#13-admin--normal-admin-roleadmin).

### 12.1 List All Announcements

**Purpose:** Get all currently active announcements (any type), newest first.

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/announcements` |
| **Auth** | **Required** (Bearer token) |

**Query Parameters:**

| Param | Type | Default |
|-------|------|---------|
| `per_page` | integer | `15` |
| `page` | integer | `1` |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200) — paginated:**

```json
{
  "success": true,
  "message": "Announcements retrieved successfully",
  "data": [
    {
      "id": 5,
      "title": "Platform Maintenance",
      "description": "The platform will be down for maintenance on Saturday.",
      "type": "general",
      "link": null,
      "starts_at": "2026-02-22T00:00:00.000000Z",
      "ends_at": "2026-02-22T06:00:00.000000Z",
      "created_at": "2026-02-20T09:00:00.000000Z"
    },
    {
      "id": 4,
      "title": "Laravel 12 Released!",
      "description": "Check out the new features in Laravel 12.",
      "type": "technical",
      "link": "https://laravel.com/blog/laravel-12",
      "starts_at": null,
      "ends_at": null,
      "created_at": "2026-02-18T12:00:00.000000Z"
    }
  ],
  "meta": { "current_page": 1, "last_page": 1, "per_page": 15, "total": 4, "from": 1, "to": 4 },
  "links": { "first": "...?page=1", "last": "...?page=1", "prev": null, "next": null }
}
```

> **Announcement types:** `general`, `technical`, `opportunity`
> Only **active** announcements are returned (filtered by `starts_at` / `ends_at` scope).

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |

---

### 12.2 List Technical Announcements

**Purpose:** Get only `technical` type active announcements.

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/announcements/technical` |
| **Auth** | **Required** (Bearer token) |

Same query params, headers, and response shape as [12.1](#121-list-all-announcements), but filtered to `type = "technical"` only.

**Success message:** `"Technical announcements retrieved successfully"`

---

### 12.3 List Opportunity Announcements

**Purpose:** Get only `opportunity` type active announcements.

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/announcements/opportunities` |
| **Auth** | **Required** (Bearer token) |

Same query params, headers, and response shape as [12.1](#121-list-all-announcements), but filtered to `type = "opportunity"` only.

**Success message:** `"Opportunity announcements retrieved successfully"`

---

## 13. Admin — Normal Admin (`role:admin`)

> All endpoints in this section require **authentication** + **`role:admin`** middleware.
> These are accessible only to the **normal admin** role.
> The path prefix is `/admin/...`.

### 13.1 User Management

#### 13.1.1 List Users

**Purpose:** List all users with filtering and pagination.

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/admin/users` |
| **Auth** | **Required** (Bearer token + `role:admin`) |

**Query Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `role` | string | Filter by role (`user`, `admin`, `tech_admin`) |
| `email` | string | Partial email search |
| `username` | string | Partial username search |
| `is_active` | boolean | Filter by active status |
| `order_by` | string | Column to sort by (default: `created_at`) |
| `order_direction` | string | `asc` or `desc` (default: `desc`) |
| `per_page` | integer | Pagination size (default: `15`) |
| `page` | integer | Page number |

**Request Headers:**

```
Accept: application/json
Authorization: Bearer <token>
```

**Request Body:** No body

**Success Response (200) — paginated:**

```json
{
  "success": true,
  "message": "تم جلب المستخدمين بنجاح",
  "data": [
    {
      "id": 1,
      "username": "admin_user",
      "email": "admin@example.com",
      "role": "admin",
      "profile_picture": null,
      "notification_enabled": true,
      "created_at": "2026-01-01T00:00:00.000000Z",
      "updated_at": "2026-01-01T00:00:00.000000Z"
    }
  ],
  "meta": { "current_page": 1, "last_page": 1, "per_page": 15, "total": 10, "from": 1, "to": 10 },
  "links": { "..." : "..." }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Not admin role | `{"success":false,"message":"Unauthorized. Admin role required."}` |

---

#### 13.1.2 Show User

**Purpose:** Get details of a single user.

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/admin/users/{id}` |
| **Auth** | **Required** (Bearer token + `role:admin`) |

**Success Response (200):**

```json
{
  "success": true,
  "message": "تم جلب المستخدم بنجاح",
  "data": {
    "id": 5,
    "username": "ahmed_dev",
    "email": "ahmed@example.com",
    "role": "user",
    "profile_picture": "https://example.com/avatars/5.jpg",
    "notification_enabled": true,
    "created_at": "2026-01-15T10:00:00.000000Z",
    "updated_at": "2026-02-10T14:00:00.000000Z"
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Not admin role | `{"success":false,"message":"Unauthorized. Admin role required."}` |
| 404 | User not found | Standard Laravel 404 |

---

#### 13.1.3 Update User

**Purpose:** Update user details (username, email, role, password).

| | |
|---|---|
| **Method** | `PUT` |
| **Path** | `/admin/users/{id}` |
| **Auth** | **Required** (Bearer token + `role:admin`) |

**Request Body:**

```json
{
  "username": "new_username",
  "email": "new@example.com",
  "role": "tech_admin",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

| Field | Type | Rules |
|-------|------|-------|
| `username` | string | **Optional.** Max 255, unique (ignoring current user). |
| `email` | string | **Optional.** Valid email, max 255, unique. |
| `role` | string | **Optional.** One of: `user`, `admin`, `tech_admin`. |
| `password` | string | **Optional.** Min 8, must include `password_confirmation`. |

**Success Response (200):**

```json
{
  "success": true,
  "message": "تم تحديث المستخدم بنجاح",
  "data": { "id": 5, "username": "new_username", "email": "new@example.com", "role": "tech_admin", "..." : "..." }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 403 | Not admin | `{"success":false,"message":"Unauthorized. Admin role required."}` |
| 404 | User not found | Standard Laravel 404 |
| 422 | Validation | `{"success":false,"message":"...","errors":{"email":["The email has already been taken."]}}` |

---

#### 13.1.4 Delete User

**Purpose:** Delete a user account. Cannot delete yourself.

| | |
|---|---|
| **Method** | `DELETE` |
| **Path** | `/admin/users/{id}` |
| **Auth** | **Required** (Bearer token + `role:admin`) |

**Success Response (204):**

```json
{
  "success": true,
  "message": "تم حذف المستخدم بنجاح",
  "data": null
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 403 | Not admin or deleting yourself | `{"success":false,"message":"لا يمكنك حذف حسابك الخاص"}` |
| 404 | User not found | Standard Laravel 404 |

---

#### 13.1.5 Revoke All User Tokens

**Purpose:** Log out a user from all devices by revoking all their Sanctum tokens.

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/admin/users/{id}/revoke-tokens` |
| **Auth** | **Required** (Bearer token + `role:admin`) |

**Request Body:** No body

**Success Response (200):**

```json
{
  "success": true,
  "message": "تم إلغاء جميع الجلسات بنجاح",
  "data": null
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 403 | Not admin | `{"success":false,"message":"Unauthorized. Admin role required."}` |
| 404 | User not found | Standard Laravel 404 |

---

### 13.2 Announcement Management (Admin)

#### 13.2.1 List All Announcements (Admin View)

**Purpose:** List all announcements (including inactive) with creator info, for the admin panel.

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/admin/announcements` |
| **Auth** | **Required** (Bearer token + `role:admin`) |

**Query Parameters:**

| Param | Type | Default |
|-------|------|---------|
| `per_page` | integer | `15` |
| `page` | integer | `1` |

**Success Response (200) — paginated:**

```json
{
  "success": true,
  "message": "Announcements retrieved successfully",
  "data": [
    {
      "id": 5,
      "title": "Platform Maintenance",
      "description": "...",
      "type": "general",
      "link": null,
      "starts_at": "2026-02-22T00:00:00.000000Z",
      "ends_at": "2026-02-22T06:00:00.000000Z",
      "created_by": 1,
      "created_at": "2026-02-20T09:00:00.000000Z",
      "updated_at": "2026-02-20T09:00:00.000000Z",
      "creator": {
        "id": 1,
        "username": "admin_user",
        "email": "admin@example.com"
      }
    }
  ],
  "meta": { "..." : "..." },
  "links": { "..." : "..." }
}
```

---

#### 13.2.2 Show Announcement (Admin View)

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/admin/announcements/{id}` |
| **Auth** | **Required** (Bearer token + `role:admin`) |

**Success Response (200):** Same shape as single item from list above, wrapped in `successResponse`.

---

#### 13.2.3 Create Announcement

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/admin/announcements` |
| **Auth** | **Required** (Bearer token + `role:admin`) |

**Request Body:**

```json
{
  "title": "New Tech Meetup",
  "description": "Join us for a Laravel meetup this Friday.",
  "type": "technical",
  "link": "https://meetup.example.com/laravel",
  "starts_at": "2026-02-25",
  "ends_at": "2026-02-26"
}
```

| Field | Type | Rules |
|-------|------|-------|
| `title` | string | **Required.** Max 255. |
| `description` | string | **Required.** |
| `type` | string | **Required.** One of: `general`, `technical`, `opportunity`. |
| `link` | string | **Optional.** Valid URL, max 2048. |
| `starts_at` | date | **Optional.** |
| `ends_at` | date | **Optional.** Must be after or equal to `starts_at`. |

**Success Response (201):**

```json
{
  "success": true,
  "message": "Announcement created successfully",
  "data": {
    "id": 6,
    "title": "New Tech Meetup",
    "description": "Join us for a Laravel meetup this Friday.",
    "type": "technical",
    "link": "https://meetup.example.com/laravel",
    "starts_at": "2026-02-25T00:00:00.000000Z",
    "ends_at": "2026-02-26T00:00:00.000000Z",
    "created_by": 1,
    "created_at": "2026-02-20T12:00:00.000000Z",
    "updated_at": "2026-02-20T12:00:00.000000Z",
    "creator": { "id": 1, "username": "admin_user", "email": "admin@example.com" }
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 403 | Not admin | `{"success":false,"message":"Unauthorized. Admin role required."}` |
| 422 | Validation | `{"success":false,"message":"...","errors":{"type":["Type must be one of: general, technical, opportunity."]}}` |

---

#### 13.2.4 Update Announcement

| | |
|---|---|
| **Method** | `PUT` |
| **Path** | `/admin/announcements/{id}` |
| **Auth** | **Required** (Bearer token + `role:admin`) |

Same body fields as Create. **Success message:** `"Announcement updated successfully"`

---

#### 13.2.5 Delete Announcement

| | |
|---|---|
| **Method** | `DELETE` |
| **Path** | `/admin/announcements/{id}` |
| **Auth** | **Required** (Bearer token + `role:admin`) |

**Success Response (200):**

```json
{
  "success": true,
  "message": "Announcement deleted successfully",
  "data": null
}
```

---

### 13.3 Chat Moderation (Admin)

> These endpoints manage mute/ban/unban for users in roadmap chat rooms.

#### 13.3.1 Mute User in Chat

**Purpose:** Mute a user in a roadmap's chat room. Muted users cannot send messages.

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/admin/roadmaps/{roadmapId}/chat/mute` |
| **Auth** | **Required** (Bearer token + `role:admin`) |

**Request Body:**

```json
{
  "user_id": 5,
  "reason": "Spamming messages",
  "muted_until": "2026-03-01T00:00:00Z"
}
```

| Field | Type | Rules |
|-------|------|-------|
| `user_id` | integer | **Required.** Must exist in `users` table. |
| `reason` | string | **Optional.** Max 500 characters. |
| `muted_until` | datetime | **Optional.** Must be in the future. If omitted, mute is indefinite. |

**Success Response (200):**

```json
{
  "success": true,
  "message": "User muted successfully",
  "data": {
    "id": 10,
    "chat_room_id": 3,
    "user_id": 5,
    "type": "mute",
    "reason": "Spamming messages",
    "muted_until": "2026-03-01T00:00:00.000000Z",
    "created_by": 1,
    "created_at": "2026-02-20T12:00:00.000000Z",
    "updated_at": "2026-02-20T12:00:00.000000Z"
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 403 | Not admin or target is admin | `{"success":false,"message":"Cannot mute an admin user."}` |
| 404 | Roadmap / chat room not found | `{"success":false,"message":"Chat room not found for this roadmap."}` |
| 422 | Validation | `{"success":false,"message":"Validation failed.","errors":{"user_id":["The selected user id is invalid."]}}` |

---

#### 13.3.2 Unmute User

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/admin/roadmaps/{roadmapId}/chat/unmute` |
| **Auth** | **Required** (Bearer token + `role:admin`) |

**Request Body:**

```json
{
  "user_id": 5
}
```

| Field | Type | Rules |
|-------|------|-------|
| `user_id` | integer | **Required.** Must exist in `users` table. |

**Success Response (200):**

```json
{
  "success": true,
  "message": "User unmuted successfully",
  "data": null
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 404 | User is not muted | `{"success":false,"message":"User is not muted in this chat room."}` |

---

#### 13.3.3 Ban User from Chat

**Purpose:** Permanently ban a user from a roadmap's chat room.

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/admin/roadmaps/{roadmapId}/chat/ban` |
| **Auth** | **Required** (Bearer token + `role:admin`) |

**Request Body:**

```json
{
  "user_id": 5,
  "reason": "Repeated violations"
}
```

| Field | Type | Rules |
|-------|------|-------|
| `user_id` | integer | **Required.** Must exist in `users` table. |
| `reason` | string | **Optional.** Max 500 characters. |

**Success Response (200):**

```json
{
  "success": true,
  "message": "User banned from chat successfully",
  "data": {
    "id": 11,
    "chat_room_id": 3,
    "user_id": 5,
    "type": "ban",
    "reason": "Repeated violations",
    "muted_until": null,
    "created_by": 1,
    "created_at": "2026-02-20T12:00:00.000000Z",
    "updated_at": "2026-02-20T12:00:00.000000Z"
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 403 | Target is admin | `{"success":false,"message":"Cannot ban an admin user."}` |
| 404 | Roadmap / chat room not found | `{"success":false,"message":"Roadmap not found."}` |

---

#### 13.3.4 Unban User from Chat

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/admin/roadmaps/{roadmapId}/chat/unban` |
| **Auth** | **Required** (Bearer token + `role:admin`) |

**Request Body:**

```json
{
  "user_id": 5
}
```

**Success Response (200):**

```json
{
  "success": true,
  "message": "User unbanned from chat successfully",
  "data": null
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 404 | User is not banned | `{"success":false,"message":"User is not banned in this chat room."}` |

---

#### 13.3.5 List Chat Members

**Purpose:** List all enrolled users in a roadmap's chat room, with mute/ban status.

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/admin/roadmaps/{roadmapId}/chat/members` |
| **Auth** | **Required** (Bearer token + `role:admin`) |

**Query Parameters:**

| Param | Type | Default |
|-------|------|---------|
| `per_page` | integer | `30` |
| `page` | integer | `1` |

**Success Response (200):**

```json
{
  "success": true,
  "message": "Chat members retrieved successfully",
  "data": [
    {
      "user": {
        "id": 5,
        "username": "ahmed_dev",
        "email": "ahmed@example.com",
        "profile_picture": "https://example.com/avatars/5.jpg",
        "role": "user"
      },
      "enrolled_at": "2026-02-01T10:00:00.000000Z",
      "status": "active",
      "is_muted": false,
      "is_banned": false
    },
    {
      "user": {
        "id": 7,
        "username": "banned_user",
        "email": "banned@example.com",
        "profile_picture": null,
        "role": "user"
      },
      "enrolled_at": "2026-02-05T10:00:00.000000Z",
      "status": "active",
      "is_muted": false,
      "is_banned": true
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 30,
    "total": 15
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 403 | Not admin | `{"success":false,"message":"Unauthorized. Admin role required."}` |
| 404 | Roadmap / chat room not found | `{"success":false,"message":"Chat room not found for this roadmap."}` |

---

## 14. Admin — Shared Read-Only (`role:admin,tech_admin`)

> These read-only endpoints are accessible by **both** `admin` and `tech_admin` roles.
> Path prefix: `/admin/...`

### 14.1 Roadmaps (Read-Only)

#### 14.1.1 List Roadmaps (Admin Panel)

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/admin/roadmaps` |
| **Auth** | **Required** (Bearer token + `role:admin` or `role:tech_admin`) |

**Query Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `level` | string | Filter by `beginner`, `intermediate`, `advanced` |
| `is_active` | boolean | Filter by active status |
| `search` | string | Search in title/description |
| `order_by` | string | Column to sort (default: `created_at`) |
| `order_direction` | string | `asc` or `desc` (default: `desc`) |
| `per_page` | integer | Default: `20` |

**Success Response (200) — paginated:** Roadmap objects with `enrollments_count` and `learning_units_count`.

---

#### 14.1.2 Show Roadmap (Admin Panel)

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/admin/roadmaps/{id}` |
| **Auth** | **Required** (Bearer token + `role:admin` or `role:tech_admin`) |

**Success Response (200):** RoadmapResource with `chatRoom`, `learningUnits` (with `lessons_count`, ordered by position), `enrollments_count`, `learning_units_count`.

---

### 14.2 Content Read-Only

> All of the following return content **without** `is_active` filtering (admin sees everything).

#### 14.2.1 List Units (Admin)

| **Method** | `GET` |
|---|---|
| **Path** | `/admin/roadmaps/{roadmapId}/units` |

Returns all units with `lessons_count`, `quizzes_count`, `challenges_count`.

#### 14.2.2 Show Unit (Admin)

| **Method** | `GET` |
|---|---|
| **Path** | `/admin/units/{unitId}` |

#### 14.2.3 List Lessons (Admin)

| **Method** | `GET` |
|---|---|
| **Path** | `/admin/units/{unitId}/lessons` |

Returns all lessons with `sub_lessons_count`.

#### 14.2.4 Show Lesson (Admin)

| **Method** | `GET` |
|---|---|
| **Path** | `/admin/lessons/{lessonId}` |

Returns lesson with sub-lessons loaded.

#### 14.2.5 List SubLessons (Admin)

| **Method** | `GET` |
|---|---|
| **Path** | `/admin/lessons/{lessonId}/sub-lessons` |

Returns sub-lessons with resources loaded.

#### 14.2.6 Show SubLesson (Admin)

| **Method** | `GET` |
|---|---|
| **Path** | `/admin/sub-lessons/{subLessonId}` |

#### 14.2.7 List Resources (Admin)

| **Method** | `GET` |
|---|---|
| **Path** | `/admin/sub-lessons/{subLessonId}/resources` |

#### 14.2.8 Show Resource (Admin)

| **Method** | `GET` |
|---|---|
| **Path** | `/admin/resources/{resourceId}` |

#### 14.2.9 Search Resources (Admin)

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/admin/resources/search` |

**Query Parameters:**

| Param | Type | Description |
|-------|------|-------------|
| `type` | string | Filter by `book`, `video`, `article` |
| `language` | string | Filter by `ar`, `en` |
| `search` | string | Search in title |
| `sub_lesson_id` | integer | Filter by sub-lesson |
| `per_page` | integer | Default: `20` |

Returns paginated resources.

#### 14.2.10 List Quizzes for Unit (Admin)

| **Method** | `GET` |
|---|---|
| **Path** | `/admin/units/{unitId}/quizzes` |

Returns quizzes with `learningUnit` and `questions_count`.

#### 14.2.11 Show Quiz (Admin)

| **Method** | `GET` |
|---|---|
| **Path** | `/admin/quizzes/{quizId}` |

Returns quiz with all questions and `learningUnit`.

#### 14.2.12 List Quiz Questions (Admin)

| **Method** | `GET` |
|---|---|
| **Path** | `/admin/quizzes/{quizId}/questions` |

Returns all questions ordered by `order`.

#### 14.2.13 List Challenges for Unit (Admin)

| **Method** | `GET` |
|---|---|
| **Path** | `/admin/units/{unitId}/challenges` |

Returns the challenge (or null) with `learningUnit`.

#### 14.2.14 Show Challenge (Admin)

| **Method** | `GET` |
|---|---|
| **Path** | `/admin/challenges/{challengeId}` |

Returns challenge with `learningUnit`.

> **All Read-Only Admin Endpoints Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Not admin or tech_admin | Middleware returns 403 |
| 404 | Resource not found | Standard Laravel 404 |

---

## 15. Admin — Tech Admin Content CRUD (`role:tech_admin`)

> All endpoints in this section require **authentication** + **`role:tech_admin`** middleware.
> These cover **write operations** (create / update / delete) on educational content.
> Path prefix: `/admin/...`

### 15.1 Roadmap Management (Tech Admin)

#### 15.1.1 Create Roadmap

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/admin/roadmaps` |
| **Auth** | **Required** (Bearer token + `role:tech_admin`) |

**Request Body:**

```json
{
  "title": "Backend with Laravel",
  "description": "Learn Laravel from scratch to advanced.",
  "level": "beginner",
  "is_active": true
}
```

| Field | Type | Rules |
|-------|------|-------|
| `title` | string | **Required.** Max 255. |
| `description` | string | **Required.** |
| `level` | string | **Required.** One of: `beginner`, `intermediate`, `advanced`. |
| `is_active` | boolean | **Optional.** |

> **Side effect:** A chat room is automatically created for the new roadmap.

**Success Response (201):** Returns `RoadmapResource`.

```json
{
  "success": true,
  "message": "تم إنشاء المسار بنجاح",
  "data": {
    "id": 4,
    "title": "Backend with Laravel",
    "description": "Learn Laravel from scratch to advanced.",
    "level": "beginner",
    "level_arabic": "مبتدئ",
    "is_active": true,
    "created_at": "2026-02-20 12:00:00",
    "updated_at": "2026-02-20 12:00:00"
  }
}
```

**Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 403 | Not tech_admin | `{"success":false,"message":"Unauthorized. Technical admin role required."}` |
| 422 | Validation | `{"success":false,"message":"...","errors":{"level":["مستوى المسار يجب أن يكون واحدًا من: beginner, intermediate, advanced"]}}` |

---

#### 15.1.2 Update Roadmap

| | |
|---|---|
| **Method** | `PUT` |
| **Path** | `/admin/roadmaps/{id}` |
| **Auth** | **Required** (Bearer token + `role:tech_admin`) |

Same fields as Create but all are **optional** (for partial update). Returns `RoadmapResource`.

---

#### 15.1.3 Delete Roadmap

| | |
|---|---|
| **Method** | `DELETE` |
| **Path** | `/admin/roadmaps/{id}` |
| **Auth** | **Required** (Bearer token + `role:tech_admin`) |

**Blocked if:** Active enrollments exist (returns 409 Conflict).

```json
{
  "success": false,
  "message": "لا يمكن حذف المسار لأنه يحتوي على اشتراكات نشطة"
}
```

**Success Response (200):**

```json
{ "success": true, "message": "تم حذف المسار بنجاح", "data": null }
```

---

#### 15.1.4 Toggle Roadmap Active

| | |
|---|---|
| **Method** | `PATCH` |
| **Path** | `/admin/roadmaps/{id}/toggle-active` |
| **Auth** | **Required** (Bearer token + `role:tech_admin`) |

**Request Body:** No body

**Success Response (200):** Returns `RoadmapResource` with toggled `is_active` status.

---

### 15.2 Learning Unit Management (Tech Admin)

#### 15.2.1 Create Unit

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/admin/roadmaps/{roadmapId}/units` |
| **Auth** | **Required** (Bearer token + `role:tech_admin`) |

**Request Body:**

```json
{
  "title": "Introduction to PHP",
  "position": 1
}
```

| Field | Type | Rules |
|-------|------|-------|
| `title` | string | **Required.** Max 255. |
| `position` | integer | **Optional.** Min 1. Auto-calculated if omitted (last position + 1). |

**Success Response (201):** Returns the created unit.

---

#### 15.2.2 Update Unit

| | |
|---|---|
| **Method** | `PUT` |
| **Path** | `/admin/units/{unitId}` |

Same fields, all optional. **Success:** `"Unit updated successfully"`

---

#### 15.2.3 Delete Unit

| | |
|---|---|
| **Method** | `DELETE` |
| **Path** | `/admin/units/{unitId}` |

**Success:** `"Unit deleted successfully"`

---

#### 15.2.4 Reorder Units

| | |
|---|---|
| **Method** | `PATCH` |
| **Path** | `/admin/roadmaps/{roadmapId}/units/reorder` |

**Request Body:**

```json
{
  "unit_ids": [3, 1, 2]
}
```

| Field | Type | Rules |
|-------|------|-------|
| `unit_ids` | array of integers | **Required.** Each ID must exist in `learning_units` and belong to this roadmap. |

**Success:** `"Units reordered successfully"`

---

#### 15.2.5 Toggle Unit Active

| | |
|---|---|
| **Method** | `PATCH` |
| **Path** | `/admin/units/{unitId}/toggle-active` |

**Success (200):**

```json
{
  "success": true,
  "message": "Unit status updated",
  "data": { "unit_id": 1, "is_active": false, "title": "Introduction to PHP" }
}
```

---

### 15.3 Lesson Management (Tech Admin)

#### 15.3.1 Create Lesson

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/admin/units/{unitId}/lessons` |

**Request Body:**

```json
{
  "title": "What is PHP?",
  "description": "A brief introduction",
  "position": 1,
  "is_active": true
}
```

| Field | Type | Rules |
|-------|------|-------|
| `title` | string | **Required.** Max 255. |
| `description` | string | **Optional.** |
| `position` | integer | **Optional.** Min 1. |
| `is_active` | boolean | **Optional.** |

---

#### 15.3.2 Update Lesson

| **Method** | `PUT` | **Path** | `/admin/lessons/{lessonId}` |
|---|---|---|---|

All fields optional.

#### 15.3.3 Delete Lesson

| **Method** | `DELETE` | **Path** | `/admin/lessons/{lessonId}` |
|---|---|---|---|

#### 15.3.4 Reorder Lessons

| **Method** | `PATCH` | **Path** | `/admin/units/{unitId}/lessons/reorder` |
|---|---|---|---|

**Body:** `{ "lesson_ids": [5, 3, 4] }`

#### 15.3.5 Toggle Lesson Active

| **Method** | `PATCH` | **Path** | `/admin/lessons/{lessonId}/toggle-active` |
|---|---|---|---|

---

### 15.4 SubLesson Management (Tech Admin)

#### 15.4.1 Create SubLesson

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/admin/lessons/{lessonId}/sub-lessons` |

**Request Body:**

```json
{
  "description": "History of PHP",
  "position": 1
}
```

| Field | Type | Rules |
|-------|------|-------|
| `description` | string | **Required.** |
| `position` | integer | **Optional.** Min 1. |

#### 15.4.2 Update SubLesson

| **Method** | `PUT` | **Path** | `/admin/sub-lessons/{subLessonId}` |
|---|---|---|---|

#### 15.4.3 Delete SubLesson

| **Method** | `DELETE` | **Path** | `/admin/sub-lessons/{subLessonId}` |
|---|---|---|---|

#### 15.4.4 Reorder SubLessons

| **Method** | `PATCH` | **Path** | `/admin/lessons/{lessonId}/sub-lessons/reorder` |
|---|---|---|---|

**Body:** `{ "sublesson_ids": [22, 20, 21] }`

---

### 15.5 Resource Management (Tech Admin)

#### 15.5.1 Create Resource

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/admin/sub-lessons/{subLessonId}/resources` |

**Request Body:**

```json
{
  "title": "PHP Official Docs",
  "type": "article",
  "language": "en",
  "link": "https://www.php.net/docs.php"
}
```

| Field | Type | Rules |
|-------|------|-------|
| `title` | string | **Required.** Max 255. |
| `type` | string | **Required.** One of: `book`, `video`, `article`. |
| `language` | string | **Required.** One of: `ar`, `en`. |
| `link` | string | **Required.** Valid URL. |

#### 15.5.2 Update Resource

| **Method** | `PUT` | **Path** | `/admin/resources/{resourceId}` |
|---|---|---|---|

All fields optional.

#### 15.5.3 Delete Resource

| **Method** | `DELETE` | **Path** | `/admin/resources/{resourceId}` |
|---|---|---|---|

---

### 15.6 Quiz Management (Tech Admin)

#### 15.6.1 Create Quiz

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/admin/quizzes` |

**Request Body:**

```json
{
  "learning_unit_id": 1,
  "is_active": true,
  "min_xp": 50,
  "max_xp": 100
}
```

| Field | Type | Rules |
|-------|------|-------|
| `learning_unit_id` | integer | **Required.** Must exist in `learning_units`. |
| `is_active` | boolean | **Optional.** |
| `min_xp` | integer | **Required.** Min 0. Must be ≤ `max_xp`. |
| `max_xp` | integer | **Required.** Min 0. |

**Success Response (201).**

---

#### 15.6.2 Update Quiz

| **Method** | `PUT` | **Path** | `/admin/quizzes/{quiz}` |
|---|---|---|---|

All fields optional.

#### 15.6.3 Delete Quiz

| **Method** | `DELETE` | **Path** | `/admin/quizzes/{quiz}` |
|---|---|---|---|

---

### 15.7 Quiz Question Management (Tech Admin)

#### 15.7.1 Create Question

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/admin/quizzes/{quizId}/questions` |

**Request Body:**

```json
{
  "question_text": "What does PHP stand for?",
  "options": [
    "Personal Home Page",
    "PHP: Hypertext Preprocessor",
    "Pre-HTML Processor",
    "Public HTML Parser"
  ],
  "correct_answer": "PHP: Hypertext Preprocessor",
  "question_xp": 10,
  "order": 1
}
```

| Field | Type | Rules |
|-------|------|-------|
| `question_text` | string | **Required.** Max 5000. |
| `options` | array of strings | **Required.** Min 2, max 10 options. Each max 500. |
| `correct_answer` | string | **Required.** Max 500. Should match one of the options. |
| `question_xp` | integer | **Optional.** 0–100. |
| `order` | integer | **Optional.** Min 1. |

**Success Response (201).**

---

#### 15.7.2 Update Question

| **Method** | `PUT` | **Path** | `/admin/questions/{questionId}` |
|---|---|---|---|

All fields optional.

#### 15.7.3 Delete Question

| **Method** | `DELETE` | **Path** | `/admin/questions/{questionId}` |
|---|---|---|---|

---

### 15.8 Challenge Management (Tech Admin)

#### 15.8.1 Create Challenge (or Update Existing)

**Purpose:** Create or update the challenge for a learning unit. Uses `updateOrCreate` since each unit has only one challenge.

| | |
|---|---|
| **Method** | `POST` |
| **Path** | `/admin/units/{unitId}/challenges` |
| **Auth** | **Required** (Bearer token + `role:tech_admin`) |

**Request Body:**

```json
{
  "title": "FizzBuzz in Python",
  "description": "Write a FizzBuzz program that prints 1 to 100.",
  "min_xp": 100,
  "language": "python",
  "starter_code": "# Write your solution here\n",
  "test_cases": [
    {
      "stdin": "",
      "expected_output": "1\n2\nFizz\n4\nBuzz\n..."
    },
    {
      "stdin": "",
      "expected_output": "FizzBuzz"
    }
  ],
  "is_active": true
}
```

| Field | Type | Rules |
|-------|------|-------|
| `title` | string | **Required.** Max 255. |
| `description` | string | **Optional.** |
| `min_xp` | integer | **Required.** Min 0. |
| `language` | string | **Required.** One of: `php`, `python`, `javascript`, `java`, `cpp`. |
| `starter_code` | string | **Optional.** |
| `test_cases` | array | **Required.** Min 1 test case. |
| `test_cases.*.stdin` | string | **Optional.** Input for the test case. |
| `test_cases.*.expected_output` | string | **Required.** Expected stdout. |
| `is_active` | boolean | **Optional.** |

> **Note:** The unit must have `unit_type = challenge`, otherwise returns 422.

**Success Response (201).**

---

#### 15.8.2 Update Challenge

| **Method** | `PUT` | **Path** | `/admin/challenges/{challengeId}` |
|---|---|---|---|

Same fields as Create.

#### 15.8.3 Delete Challenge

| **Method** | `DELETE` | **Path** | `/admin/challenges/{challengeId}` |
|---|---|---|---|

#### 15.8.4 Toggle Challenge Active

| **Method** | `PATCH` | **Path** | `/admin/challenges/{challengeId}/toggle-active` |
|---|---|---|---|

**Request Body:** No body. Toggles `is_active`.

> **All Tech Admin Endpoints Common Errors:**

| Status | Condition | Example |
|--------|-----------|---------|
| 401 | Not authenticated | `{"message":"Unauthenticated."}` |
| 403 | Not tech_admin | `{"success":false,"message":"Unauthorized. Technical admin role required."}` |
| 404 | Resource not found | Standard Laravel 404 |
| 422 | Validation errors | Standard validation response |

---

## Notes / TODO

The following items could not be fully documented or need clarification:

1. **`GET /test-connection`** — A public health-check endpoint at `/v1/test-connection`. Returns `{"success":true,"message":"الاتصال ناجح والجهاز واصل بالإنترنت!","data":null}`. No auth required. Useful for mobile apps to verify connectivity.

2. **`GET /roadmaps/{id}/enrollments`** — Listed in routes but the `RoadmapController@enrollments` method was not fully inspected. Likely returns enrollment count/stats for a roadmap (public).

3. **`PATCH /me/notifications`** — Updates the user's `notification_enabled` preference (`AuthController@updateNotificationPreference`). Request body likely: `{"notification_enabled": false}`. Exact validation rules need to be verified in the controller.

4. **`GET /auth/reset-attempts`** — Returns the number of remaining password reset attempts (`PasswordResetController@getAttemptsRemaining`). Exact response shape depends on implementation.

5. **Notification types** — The exact set of notification types (e.g., `quiz_result`, `announcement`, `enrollment`, etc.) depends on what the system generates and was not enumerated from models.

6. **ChatbotReplyService** — The chatbot AI reply generation depends on an external service (likely OpenAI). If the service is unreachable or API keys are missing, the endpoint may return 500 errors.

7. **Admin `getStats` endpoint** — `Admin\RoadmapController@getStats` exists in the controller code but has **no route defined** in `routes/api.php`. It is currently unreachable.

8. **Soft Deletes** — `ChatMessage` uses soft deletes. Deleted messages may still appear with `deleted_at` set if the query is not filtered.

---

> **End of Part 3 — Documentation Complete.** All endpoint groups have been documented.

