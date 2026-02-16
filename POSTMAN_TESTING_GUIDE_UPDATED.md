# Postman Testing Guide - Roadmap Learning Platform API

## Base Configuration

### Base URL
**Local Testing:**
```
http://roadmap_system.test/api/v1
```

**Production (Render):**
```
https://YOUR-APP.onrender.com/api/v1
```

**Note:** For OAuth testing, use the Render production URL. For all other endpoints, use the local URL.

### Headers
For authenticated requests, add:
```
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json
Accept: application/json
```

---

## 1. Authentication Endpoints

### 1.1 Register User
**POST** `/auth/register`

**Headers:**
```
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "username": "testuser",
  "email": "test@example.com",
  "password": "Test123!@#",
  "password_confirmation": "Test123!@#"
}
```

**Expected Response (201):**
```json
{
  "success": true,
  "message": "تم تسجيل المستخدم بنجاح",
  "data": {
    "user": {
      "id": 1,
      "username": "testuser",
      "email": "test@example.com"
    },
    "token": "1|xxxxxxxxxxxxx",
    "token_type": "Bearer"
  }
}
```

**Save the token** from response for subsequent requests!

---

### 1.2 Login
**POST** `/auth/login`

**Headers:**
```
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "email": "test@example.com",
  "password": "Test123!@#"
}
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "تم تسجيل الدخول بنجاح",
  "data": {
    "user": {...},
    "token": "2|xxxxxxxxxxxxx",
    "token_type": "Bearer"
  }
}
```

---

### 1.3 Get Profile
**GET** `/profile`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "id": 1,
    "username": "testuser",
    "email": "test@example.com",
    ...
  }
}
```

---

### 1.4 Logout
**POST** `/logout`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "تم تسجيل الخروج بنجاح",
  "data": null
}
```

---

### 1.5 Update Notification Preference
**PATCH** `/me/notifications`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "is_notifications_enabled": true
}
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "تم تحديث تفضيلات الإشعارات بنجاح",
  "data": {
    "is_notifications_enabled": true
  }
}
```

**Postman Tests:**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response contains notification preference", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.have.property('is_notifications_enabled');
});
```

---

### 1.6 Google OAuth Login
**POST** `/auth/google`

**⚠️ IMPORTANT:** Use Render production URL for OAuth testing: `{{render_url}}/auth/google`

**Full URL:**
```
https://YOUR-APP.onrender.com/api/v1/auth/google
```

**Headers:**
```
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "id_token": "YOUR_GOOGLE_ID_TOKEN"
}
```

**Expected Response (200):**
```json
{
  "status": "success",
  "token": "1|abcdefghijklmnopqrstuvwxyz1234567890",
  "user": {
    "id": 1,
    "username": "john-doe",
    "email": "john.doe@example.com"
  }
}
```

**Postman Tests:**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response contains token", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('token');
    if (jsonData.token) {
        pm.environment.set('token', jsonData.token);
        console.log('Token saved:', jsonData.token);
    }
});

pm.test("Response contains user.id", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.user).to.have.property('id');
});
```

**How to Get Google ID Token:**
1. Visit [Google OAuth Playground](https://developers.google.com/oauthplayground/)
2. Click ⚙️ → Check "Use your own OAuth credentials"
3. Enter your `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET` (from Render environment variables)
4. Select scopes: `userinfo.email`, `userinfo.profile`, `openid`
5. Click "Authorize APIs" → Sign in with your Google account
6. Click "Exchange authorization code for tokens"
7. Copy the `id_token` value (long JWT string) from the response
8. ⚠️ **Note:** ID tokens expire after 1 hour

**How to Simulate Invalid Token Error:**
- Use an expired token
- Use a malformed token: `"invalid_token_string"`
- Use an empty string: `""`
- Omit the `id_token` field entirely

**Error Response (401):**
```json
{
  "status": "error",
  "message": "Invalid Google token"
}
```

**Error Response (422) - Missing Field:**
```json
{
  "message": "The id token field is required.",
  "errors": {
    "id_token": ["The id token field is required."]
  }
}
```

---

### 1.7 GitHub OAuth Login
**POST** `/auth/github`

**⚠️ IMPORTANT:** Use Render production URL for OAuth testing: `{{render_url}}/auth/github`

**Full URL:**
```
https://YOUR-APP.onrender.com/api/v1/auth/github
```

**Headers:**
```
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "code": "YOUR_GITHUB_OAUTH_CODE"
}
```

**Expected Response (200):**
```json
{
  "status": "success",
  "token": "2|abcdefghijklmnopqrstuvwxyz1234567890",
  "user": {
    "id": 1,
    "username": "johndoe",
    "email": "john.doe@example.com"
  }
}
```

**Postman Tests:**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response contains token", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData).to.have.property('token');
    if (jsonData.token) {
        pm.environment.set('token', jsonData.token);
        console.log('Token saved:', jsonData.token);
    }
});

pm.test("Response contains user.id", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.user).to.have.property('id');
});
```

**How to Get GitHub OAuth Code:**
1. Construct the authorization URL:
   ```
   https://github.com/login/oauth/authorize?client_id=YOUR_GITHUB_CLIENT_ID&scope=user:email&redirect_uri=https://YOUR-APP.onrender.com/api/v1/auth/github/callback
   ```
2. Visit the URL in your browser
3. Authorize the application with your GitHub account
4. You'll be redirected to your callback URL with `?code=abc123...` in the query string
5. Copy the `code` value from the URL
6. ⚠️ **Use within 10 minutes** (codes expire quickly)
7. ⚠️ **Note:** Each code can only be used once

**Alternative Method (Using Postman OAuth 2.0 Helper):**
1. In Postman, select "OAuth 2.0" as authorization type
2. Enter your GitHub Client ID and Client Secret
3. Authorization URL: `https://github.com/login/oauth/authorize`
4. Access Token URL: `https://github.com/login/oauth/access_token`
5. Click "Get New Access Token" → This will open browser for authorization
6. After authorization, you'll get an access token (not a code)
7. For testing the `/auth/github` endpoint, you still need the authorization code from the redirect URL

**How to Simulate Invalid Code Error:**
- Use an expired code (wait > 10 minutes)
- Use a code that was already used
- Use a malformed code: `"invalid_code_string"`
- Use an empty string: `""`
- Omit the `code` field entirely

**Error Response (401):**
```json
{
  "status": "error",
  "message": "Invalid GitHub code"
}
```

**Error Response (401) - Exchange Failed:**
```json
{
  "status": "error",
  "message": "Failed to exchange code for token"
}
```

**Error Response (422) - Missing Field:**
```json
{
  "message": "The code field is required.",
  "errors": {
    "code": ["The code field is required."]
  }
}
```

---

## 2. Roadmap Endpoints

### 2.1 List Roadmaps (Public)
**GET** `/roadmaps?per_page=10&page=1&level=beginner`

**Query Parameters:**
- `per_page` (optional): Items per page (default: 10)
- `page` (optional): Page number
- `level` (optional): beginner | intermediate | advanced
- `is_active` (optional): true | false
- `search` (optional): Search term
- `order_by` (optional): created_at | enrollments_count
- `order_direction` (optional): asc | desc

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Success",
  "data": [...],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 10,
    "total": 50,
    ...
  }
}
```

---

### 2.2 Get Single Roadmap
**GET** `/roadmaps/{id}?with_details=true&include_content=false`

**Query Parameters:**
- `with_details` (optional): true | false
- `include_content` (optional): true | false

**Expected Response (200):**
```json
{
  "success": true,
  "message": "تم جلب المسار بنجاح",
  "data": {
    "id": 1,
    "title": "Laravel Backend",
    "level": "intermediate",
    ...
  }
}
```

---

### 2.3 Search Roadmaps
**GET** `/roadmaps/search?query=laravel&level=intermediate&limit=10`

**Query Parameters:**
- `query` (optional): Search term
- `level` (optional): beginner | intermediate | advanced
- `limit` (optional): Max results (default: 10)

---

## 3. Enrollment Endpoints

### 3.1 Enroll in Roadmap
**POST** `/roadmaps/{id}/enroll`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
```

**Expected Response (201):**
```json
{
  "success": true,
  "message": "تم الاشتراك في المسار بنجاح",
  "data": {
    "enrollment": {
      "id": 1,
      "user_id": 1,
      "roadmap_id": 1,
      "status": "active",
      "xp_points": 0,
      ...
    },
    "chat_room": {
      "id": 1,
      "name": "...",
      ...
    }
  }
}
```

---

### 3.2 Get My Enrollments
**GET** `/me/enrollments?per_page=15`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Enrollments retrieved successfully",
  "data": [...],
  "meta": {...}
}
```

---

### 3.3 Update Enrollment Status
**PATCH** `/me/enrollments/{roadmapId}/status`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "status": "paused"
}
```
Valid values: `active`, `paused`, `completed`

---

### 3.4 Unenroll
**DELETE** `/roadmaps/{id}/unenroll`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
```

---

## 4. Learning Units & Lessons

### 4.1 Get Learning Units
**GET** `/roadmaps/{roadmapId}/units`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
```

**Note:** Requires enrollment in the roadmap

---

### 4.2 Get Lessons
**GET** `/units/{unitId}/lessons`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
```

---

### 4.3 Get Single Lesson
**GET** `/lessons/{lessonId}`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
```

---

### 4.4 Get Sub-Lessons
**GET** `/lessons/{lessonId}/sub-lessons`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
```

---

### 4.5 Get Resources
**GET** `/sub-lessons/{subLessonId}/resources`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
```

---

## 5. Lesson Tracking

### 5.1 Open Lesson (Track Start)
**POST** `/lessons/{lessonId}/track/open`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "تم تسجيل فتح الدرس",
  "data": {
    "lesson_id": 1,
    "is_complete": false,
    "last_updated_at": "2026-02-15 10:00:00"
  }
}
```

---

### 5.2 Complete Lesson
**POST** `/lessons/{lessonId}/track/complete`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
```

---

### 5.3 Get Lesson Tracking
**GET** `/lessons/{lessonId}/track`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
```

---

### 5.4 Reset Lesson Tracking
**DELETE** `/lessons/{lessonId}/track/reset`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
```

---

### 5.5 Get All My Lesson Trackings
**GET** `/me/lessons/tracking?per_page=20`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
```

---

### 5.6 Get Lesson Stats
**GET** `/me/lessons/stats`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "total_tracked": 25,
    "completed": 15,
    "completion_rate": 60.0
  }
}
```

---

## 6. Quiz Endpoints

### 6.1 Get Quiz for Unit
**GET** `/units/{unitId}/quizzes`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
```

**Note:** Requires enrollment and all previous lessons completed

---

### 6.2 Start Quiz Attempt
**GET** `/quizzes/{quizId}`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Quiz attempt started successfully",
  "data": {
    "quiz": {
      "id": 1,
      "learning_unit_id": 5,
      "min_xp": 50,
      "max_xp": 100,
      "questions": [
        {
          "id": 1,
          "question_text": "What is Laravel?",
          "options": ["A", "B", "C", "D"],
          "order": 1,
          "question_xp": 10
        }
      ]
    },
    "attempt_id": 1
  }
}
```

**Note:** Questions don't include `correct_answer` - it's hidden!

---

### 6.3 Submit Quiz Attempt
**PUT** `/quiz-attempts/{attemptId}/submit`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "answers": {
    "1": "A",
    "2": "B",
    "3": "C"
  }
}
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Quiz attempt submitted successfully",
  "data": {
    "attempt": {
      "id": 1,
      "score": 80,
      "passed": true,
      ...
    },
    "score": 80,
    "passed": true,
    "earned_points": 80
  }
}
```

---

### 6.4 Get Quiz Attempt
**GET** `/quiz-attempts/{attemptId}`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
```

---

### 6.5 Get My Quiz Attempts
**GET** `/quizzes/{quizId}/my-attempts?per_page=15`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
```

---

## 7. Challenge Endpoints

### 7.1 Get Challenge for Unit
**GET** `/units/{unitId}/challenges`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "xp_points": 150,
    "challenge": {
      "id": 1,
      "title": "Reverse String",
      "description": "Write a function to reverse a string",
      "min_xp": 100,
      "language": "python",
      "starter_code": "def reverse_string(s):\n    # Your code here\n    pass",
      "is_active": true,
      "is_unlocked": true
    }
  }
}
```

**Note:** `test_cases` are hidden from response!

---

### 7.2 Start Challenge Attempt
**POST** `/challenges/{challengeId}/attempts`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
```

**Expected Response (201):**
```json
{
  "success": true,
  "message": "Attempt started successfully",
  "data": {
    "id": 1,
    "challenge_id": 1,
    "user_id": 1,
    "submitted_code": "def reverse_string(s):\n    # Your code here\n    pass",
    "execution_output": null,
    "passed": false,
    "created_at": "...",
    "updated_at": "..."
  }
}
```

**Note:** Will fail if:
- User already has an active attempt
- User doesn't have enough XP (min_xp requirement)
- Challenge is not active

---

### 7.3 Submit Challenge Attempt
**PUT** `/challenge-attempts/{challengeAttemptId}/submit`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "code": "def reverse_string(s):\n    return s[::-1]"
}
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Attempt submitted successfully",
  "data": {
    "passed": true,
    "attempt": {
      "id": 1,
      "submitted_code": "...",
      "execution_output": "[{\"case\":1,\"passed\":true,\"output\":\"olleh\",\"expected_output\":\"olleh\"}]",
      "passed": true,
      ...
    },
    "details": [
      {
        "case": 1,
        "passed": true,
        "output": "olleh",
        "expected_output": "olleh",
        "error": null
      }
    ]
  }
}
```

**Note:** Will fail if:
- Attempt already submitted (execution_output is not null)
- Attempt already passed
- Rate limit exceeded (10 requests per minute)

---

### 7.4 Get Challenge Attempt
**GET** `/challenge-attempts/{challengeAttemptId}`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
```

---

### 7.5 Get My Challenge Attempts
**GET** `/challenges/{challengeId}/my-attempts?per_page=15`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
```

---

## 8. Community Endpoints

### 8.1 Get My Community Rooms
**GET** `/me/community?per_page=15`

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "Community rooms retrieved successfully",
  "data": [...],
  "meta": {...}
}
```

---

## 9. Admin CRUD Endpoints (Technical Admin Only)

**⚠️ IMPORTANT:** These endpoints are for **TECHNICAL ADMIN ONLY** (`role=tech_admin`). Normal admin (`role=admin`) **MUST NOT** access them and will receive 403 Forbidden.

**Required Header:**
```
Authorization: Bearer {{tech_admin_token}}
```

### 9.1 Roadmaps Management

#### 9.1.1 List Roadmaps (Tech Admin)
**GET** `/admin/roadmaps?per_page=20&level=beginner&is_active=true&search=laravel`

**Query Parameters:**
- `per_page` (optional): Items per page (default: 20)
- `level` (optional): beginner | intermediate | advanced
- `is_active` (optional): true | false
- `search` (optional): Search term
- `order_by` (optional): created_at | enrollments_count
- `order_direction` (optional): asc | desc

**Expected Response (200):**
```json
{
  "success": true,
  "message": "تم جلب المسارات بنجاح",
  "data": [...],
  "meta": {...}
}
```

**Postman Tests:**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});
```

---

#### 9.1.2 Create Roadmap
**POST** `/admin/roadmaps`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "title": "Laravel Advanced Backend",
  "description": "Complete guide to Laravel backend development",
  "level": "intermediate",
  "is_active": true
}
```

**Expected Response (201):**
```json
{
  "success": true,
  "message": "تم إنشاء المسار بنجاح",
  "data": {
    "id": 1,
    "title": "Laravel Advanced Backend",
    "level": "intermediate",
    "is_active": true,
    ...
  }
}
```

**Postman Tests (Auto-save ID):**
```javascript
pm.test("Status code is 201", function () {
    pm.response.to.have.status(201);
});

if (pm.response.code === 201) {
    var jsonData = pm.response.json();
    if (jsonData.success && jsonData.data.id) {
        pm.environment.set('roadmap_id', jsonData.data.id);
        console.log('Roadmap ID saved:', jsonData.data.id);
    }
}
```

**Error Response (403) - Normal Admin:**
```json
{
  "success": false,
  "message": "Unauthorized. Technical admin role required."
}
```

---

#### 9.1.3 Update Roadmap
**PUT** `/admin/roadmaps/{{roadmap_id}}`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "title": "Updated Roadmap Title",
  "description": "Updated description",
  "level": "advanced",
  "is_active": false
}
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "تم تحديث المسار بنجاح",
  "data": {...}
}
```

---

#### 9.1.4 Delete Roadmap
**DELETE** `/admin/roadmaps/{{roadmap_id}}`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "تم حذف المسار بنجاح",
  "data": null
}
```

---

#### 9.1.5 Toggle Roadmap Active Status
**PATCH** `/admin/roadmaps/{{roadmap_id}}/toggle-active`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "تم تحديث حالة المسار بنجاح",
  "data": {
    "id": 1,
    "is_active": false,
    ...
  }
}
```

---

### 9.2 Learning Units Management

#### 9.2.1 List Learning Units for Roadmap
**GET** `/admin/roadmaps/{{roadmap_id}}/units`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
```

**Expected Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Introduction to Laravel",
      "position": 1,
      "unit_type": "lesson",
      "is_active": true,
      ...
    }
  ]
}
```

---

#### 9.2.2 Create Learning Unit
**POST** `/admin/roadmaps/{{roadmap_id}}/units`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "title": "Advanced Routing",
  "position": 2
}
```

**Expected Response (201):**
```json
{
  "success": true,
  "data": {
    "id": 5,
    "title": "Advanced Routing",
    "roadmap_id": 1,
    "position": 2,
    ...
  }
}
```

**Postman Tests (Auto-save ID):**
```javascript
if (pm.response.code === 201) {
    var jsonData = pm.response.json();
    if (jsonData.success && jsonData.data.id) {
        pm.environment.set('unit_id', jsonData.data.id);
    }
}
```

---

#### 9.2.3 Update Learning Unit
**PUT** `/admin/units/{{unit_id}}`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "title": "Updated Unit Title",
  "position": 3
}
```

---

#### 9.2.4 Delete Learning Unit
**DELETE** `/admin/units/{{unit_id}}`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
```

---

#### 9.2.5 Reorder Learning Units
**PATCH** `/admin/roadmaps/{{roadmap_id}}/units/reorder`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "unit_ids": [3, 1, 2, 4]
}
```

---

#### 9.2.6 Toggle Learning Unit Active Status
**PATCH** `/admin/units/{{unit_id}}/toggle-active`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
```

---

### 9.3 Lessons Management

#### 9.3.1 List Lessons for Unit
**GET** `/admin/units/{{unit_id}}/lessons`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
```

**Expected Response (200):**
```json
{
  "success": true,
  "data": [...]
}
```

---

#### 9.3.2 Create Lesson
**POST** `/admin/units/{{unit_id}}/lessons`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "title": "Understanding Routes",
  "description": "Learn how Laravel routing works",
  "position": 1,
  "is_active": true
}
```

**Expected Response (201):**
```json
{
  "success": true,
  "data": {
    "id": 10,
    "title": "Understanding Routes",
    "learning_unit_id": 5,
    ...
  }
}
```

**Postman Tests (Auto-save ID):**
```javascript
if (pm.response.code === 201) {
    var jsonData = pm.response.json();
    if (jsonData.success && jsonData.data.id) {
        pm.environment.set('lesson_id', jsonData.data.id);
    }
}
```

---

#### 9.3.3 Update Lesson
**PUT** `/admin/lessons/{{lesson_id}}`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "title": "Updated Lesson Title",
  "description": "Updated description",
  "is_active": false
}
```

---

#### 9.3.4 Delete Lesson
**DELETE** `/admin/lessons/{{lesson_id}}`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
```

---

#### 9.3.5 Reorder Lessons
**PATCH** `/admin/units/{{unit_id}}/lessons/reorder`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "lesson_ids": [2, 1, 3, 4]
}
```

---

#### 9.3.6 Toggle Lesson Active Status
**PATCH** `/admin/lessons/{{lesson_id}}/toggle-active`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
```

---

### 9.4 SubLessons Management

#### 9.4.1 List SubLessons for Lesson
**GET** `/admin/lessons/{{lesson_id}}/sub-lessons`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
```

---

#### 9.4.2 Create SubLesson
**POST** `/admin/lessons/{{lesson_id}}/sub-lessons`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "description": "Route parameters and constraints",
  "position": 1
}
```

**Expected Response (201):**
```json
{
  "success": true,
  "data": {
    "id": 15,
    "lesson_id": 10,
    "description": "Route parameters and constraints",
    ...
  }
}
```

**Postman Tests (Auto-save ID):**
```javascript
if (pm.response.code === 201) {
    var jsonData = pm.response.json();
    if (jsonData.success && jsonData.data.id) {
        pm.environment.set('sublesson_id', jsonData.data.id);
    }
}
```

---

#### 9.4.3 Update SubLesson
**PUT** `/admin/sub-lessons/{{sublesson_id}}`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "description": "Updated sub-lesson description"
}
```

---

#### 9.4.4 Delete SubLesson
**DELETE** `/admin/sub-lessons/{{sublesson_id}}`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
```

---

#### 9.4.5 Reorder SubLessons
**PATCH** `/admin/lessons/{{lesson_id}}/sub-lessons/reorder`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "sublesson_ids": [2, 1, 3]
}
```

---

### 9.5 Resources Management

#### 9.5.1 List Resources for SubLesson
**GET** `/admin/sub-lessons/{{sublesson_id}}/resources`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
```

---

#### 9.5.2 Create Resource
**POST** `/admin/sub-lessons/{{sublesson_id}}/resources`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "title": "Laravel Routing Documentation",
  "type": "article",
  "language": "en",
  "link": "https://laravel.com/docs/routing"
}
```

**Valid Values:**
- `type`: book | video | article
- `language`: ar | en

**Expected Response (201):**
```json
{
  "success": true,
  "data": {
    "id": 20,
    "title": "Laravel Routing Documentation",
    "type": "article",
    "language": "en",
    "link": "https://laravel.com/docs/routing",
    ...
  }
}
```

**Postman Tests (Auto-save ID):**
```javascript
if (pm.response.code === 201) {
    var jsonData = pm.response.json();
    if (jsonData.success && jsonData.data.id) {
        pm.environment.set('resource_id', jsonData.data.id);
    }
}
```

---

#### 9.5.3 Update Resource
**PUT** `/admin/resources/{{resource_id}}`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "title": "Updated Resource Title",
  "link": "https://updated-link.com"
}
```

---

#### 9.5.4 Delete Resource
**DELETE** `/admin/resources/{{resource_id}}`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
```

---

#### 9.5.5 Search Resources
**GET** `/admin/resources/search?query=laravel&type=article&language=en`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
```

**Query Parameters:**
- `query` (optional): Search term
- `type` (optional): book | video | article
- `language` (optional): ar | en

---

### 9.6 Quizzes Management

#### 9.6.1 List All Quizzes
**GET** `/admin/quizzes?per_page=15`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
```

**Expected Response (200):**
```json
{
  "success": true,
  "data": [...],
  "meta": {...}
}
```

---

#### 9.6.2 Create Quiz
**POST** `/admin/quizzes`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "learning_unit_id": 5,
  "min_xp": 50,
  "max_xp": 100,
  "is_active": true
}
```

**Expected Response (201):**
```json
{
  "success": true,
  "data": {
    "id": 3,
    "learning_unit_id": 5,
    "min_xp": 50,
    "max_xp": 100,
    "is_active": true,
    ...
  }
}
```

**Postman Tests (Auto-save ID):**
```javascript
if (pm.response.code === 201) {
    var jsonData = pm.response.json();
    if (jsonData.success && jsonData.data.id) {
        pm.environment.set('quiz_id', jsonData.data.id);
    }
}
```

---

#### 9.6.3 Get Single Quiz
**GET** `/admin/quizzes/{{quiz_id}}`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
```

---

#### 9.6.4 Update Quiz
**PUT** `/admin/quizzes/{{quiz_id}}`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "min_xp": 60,
  "max_xp": 120,
  "is_active": false
}
```

---

#### 9.6.5 Delete Quiz
**DELETE** `/admin/quizzes/{{quiz_id}}`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
```

---

### 9.7 Quiz Questions Management

#### 9.7.1 List Questions for Quiz
**GET** `/admin/quizzes/{{quiz_id}}/questions`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
```

**Expected Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "question_text": "What is Laravel?",
      "options": ["A", "B", "C", "D"],
      "correct_answer": "A",
      "question_xp": 10,
      "order": 1,
      ...
    }
  ]
}
```

---

#### 9.7.2 Create Quiz Question
**POST** `/admin/quizzes/{{quiz_id}}/questions`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "question_text": "What is the default database driver in Laravel?",
  "options": ["MySQL", "PostgreSQL", "SQLite", "MongoDB"],
  "correct_answer": "SQLite",
  "question_xp": 15,
  "order": 1
}
```

**Expected Response (201):**
```json
{
  "success": true,
  "data": {
    "id": 5,
    "quiz_id": 3,
    "question_text": "What is the default database driver in Laravel?",
    "options": ["MySQL", "PostgreSQL", "SQLite", "MongoDB"],
    "correct_answer": "SQLite",
    "question_xp": 15,
    "order": 1,
    ...
  }
}
```

**Postman Tests (Auto-save ID):**
```javascript
if (pm.response.code === 201) {
    var jsonData = pm.response.json();
    if (jsonData.success && jsonData.data.id) {
        pm.environment.set('question_id', jsonData.data.id);
    }
}
```

---

#### 9.7.3 Update Quiz Question
**PUT** `/admin/questions/{{question_id}}`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "question_text": "Updated question text",
  "correct_answer": "PostgreSQL",
  "question_xp": 20
}
```

---

#### 9.7.4 Delete Quiz Question
**DELETE** `/admin/questions/{{question_id}}`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
```

---

### 9.8 Challenges Management

#### 9.8.1 List Challenge for Unit
**GET** `/admin/units/{{unit_id}}/challenges`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
```

**Expected Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 2,
    "title": "Reverse String Challenge",
    "description": "Write a function to reverse a string",
    "min_xp": 100,
    "language": "python",
    "starter_code": "def reverse_string(s):\n    pass",
    "is_active": true,
    ...
  }
}
```

---

#### 9.8.2 Create Challenge
**POST** `/admin/units/{{unit_id}}/challenges`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "title": "Array Sum Challenge",
  "description": "Write a function that sums all numbers in an array",
  "min_xp": 150,
  "language": "javascript",
  "starter_code": "function sumArray(arr) {\n    // Your code here\n}",
  "test_cases": [
    {
      "stdin": null,
      "expected_output": "15"
    },
    {
      "stdin": null,
      "expected_output": "0"
    }
  ],
  "is_active": true
}
```

**Valid Languages:** php, python, javascript, java, cpp

**Expected Response (201):**
```json
{
  "success": true,
  "data": {
    "id": 4,
    "learning_unit_id": 5,
    "title": "Array Sum Challenge",
    "min_xp": 150,
    "language": "javascript",
    "is_active": true,
    ...
  }
}
```

**Postman Tests (Auto-save ID):**
```javascript
if (pm.response.code === 201) {
    var jsonData = pm.response.json();
    if (jsonData.success && jsonData.data.id) {
        pm.environment.set('challenge_id', jsonData.data.id);
    }
}
```

---

#### 9.8.3 Get Single Challenge
**GET** `/admin/challenges/{{challenge_id}}`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
```

---

#### 9.8.4 Update Challenge
**PUT** `/admin/challenges/{{challenge_id}}`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "title": "Updated Challenge Title",
  "min_xp": 200,
  "is_active": false
}
```

---

#### 9.8.5 Delete Challenge
**DELETE** `/admin/challenges/{{challenge_id}}`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
```

---

#### 9.8.6 Toggle Challenge Active Status
**PATCH** `/admin/challenges/{{challenge_id}}/toggle-active`

**Headers:**
```
Authorization: Bearer {{tech_admin_token}}
```

---

### 9.9 Negative Test Cases

**Test: Normal Admin Tries Tech Endpoint (403):**
```
GET /admin/roadmaps
Authorization: Bearer {{admin_token}}
```

**Expected Response (403):**
```json
{
  "success": false,
  "message": "Unauthorized. Required role: tech_admin"
}
```

**Postman Tests:**
```javascript
pm.test("Status code is 403", function () {
    pm.response.to.have.status(403);
});

pm.test("Response indicates role requirement", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.message).to.include('tech_admin');
});
```

---

## 10. Admin User Management (Normal Admin Only)

**⚠️ IMPORTANT:** These endpoints are for **NORMAL ADMIN ONLY** (`role=admin`). Technical admin (`role=tech_admin`) **MUST NOT** access them and will receive 403 Forbidden.

**Required Header:**
```
Authorization: Bearer {{admin_token}}
```

### 10.1 List Users
**GET** `/admin/users?per_page=15&role=user&email=test&username=john&order_by=created_at&order_direction=desc`

**Query Parameters:**
- `per_page` (optional): Items per page (default: 15)
- `role` (optional): user | admin | tech_admin
- `email` (optional): Filter by email (partial match)
- `username` (optional): Filter by username (partial match)
- `order_by` (optional): Field to order by (default: created_at)
- `order_direction` (optional): asc | desc (default: desc)

**Expected Response (200):**
```json
{
  "success": true,
  "message": "تم جلب المستخدمين بنجاح",
  "data": [
    {
      "id": 1,
      "username": "testuser",
      "email": "test@example.com",
      "role": "user",
      "is_notifications_enabled": true,
      "created_at": "2026-02-15T10:00:00.000000Z",
      ...
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75,
    ...
  }
}
```

**Postman Tests:**
```javascript
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response contains users array", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.data).to.be.an('array');
});
```

---

### 10.2 Get Single User
**GET** `/admin/users/{{user_id}}`

**Headers:**
```
Authorization: Bearer {{admin_token}}
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "تم جلب المستخدم بنجاح",
  "data": {
    "id": 1,
    "username": "testuser",
    "email": "test@example.com",
    "role": "user",
    "is_notifications_enabled": true,
    "email_verified_at": "2026-02-15T10:00:00.000000Z",
    ...
  }
}
```

**Postman Tests (Auto-save ID):**
```javascript
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    if (jsonData.success && jsonData.data.id) {
        pm.environment.set('user_id', jsonData.data.id);
    }
}
```

---

### 10.3 Update User
**PUT** `/admin/users/{{user_id}}`

**Headers:**
```
Authorization: Bearer {{admin_token}}
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "username": "updated_username",
  "email": "updated@example.com",
  "role": "admin",
  "password": "NewPassword123!@#",
  "password_confirmation": "NewPassword123!@#"
}
```

**Note:** All fields are optional. Only include fields you want to update.

**Expected Response (200):**
```json
{
  "success": true,
  "message": "تم تحديث المستخدم بنجاح",
  "data": {
    "id": 1,
    "username": "updated_username",
    "email": "updated@example.com",
    "role": "admin",
    ...
  }
}
```

**Error Response (422) - Invalid Role:**
```json
{
  "message": "The selected role is invalid.",
  "errors": {
    "role": ["The selected role is invalid."]
  }
}
```

---

### 10.4 Delete User
**DELETE** `/admin/users/{{user_id}}`

**Headers:**
```
Authorization: Bearer {{admin_token}}
```

**Expected Response (204):**
```json
{
  "success": true,
  "message": "تم حذف المستخدم بنجاح",
  "data": null
}
```

**Error Response (403) - Cannot Delete Self:**
```json
{
  "success": false,
  "message": "لا يمكنك حذف حسابك الخاص"
}
```

---

### 10.5 Revoke All User Tokens (Logout Everywhere)
**POST** `/admin/users/{{user_id}}/revoke-tokens`

**Headers:**
```
Authorization: Bearer {{admin_token}}
```

**Expected Response (200):**
```json
{
  "success": true,
  "message": "تم إلغاء جميع الجلسات بنجاح",
  "data": null
}
```

---

### 10.6 Negative Test Cases

**Test: Tech Admin Tries User Endpoint (403):**
```
GET /admin/users
Authorization: Bearer {{tech_admin_token}}
```

**Expected Response (403):**
```json
{
  "success": false,
  "message": "Unauthorized. Required role: admin"
}
```

**Postman Tests:**
```javascript
pm.test("Status code is 403", function () {
    pm.response.to.have.status(403);
});

pm.test("Response indicates admin role requirement", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.message).to.include('admin');
});
```

**Test: Regular User Tries Admin Endpoint (403):**
```
GET /admin/users
Authorization: Bearer {{token}}
```

**Expected Response (403):**
```json
{
  "success": false,
  "message": "Unauthorized. Required role: admin"
}
```

---

## 11. Error Responses

### 401 Unauthorized
```json
{
  "success": false,
  "message": "بيانات الدخول غير صحيحة",
  "errors": null
}
```

### 403 Forbidden
```json
{
  "success": false,
  "message": "You are not enrolled in the roadmap associated with this content.",
  "errors": null
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "المسار غير موجود",
  "errors": null
}
```

### 422 Validation Error
```json
{
  "success": false,
  "message": "Validation Error",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

### 429 Too Many Requests
```json
{
  "success": false,
  "message": "Too Many Attempts.",
  "errors": null
}
```

---

## Postman Collection Setup

### Step 1: Create Environment Variables

In Postman, create an environment with the following variables:

**Base URLs:**
- `base_url`: `http://roadmap_system.test/api/v1` (for local testing)
- `render_url`: `https://YOUR-APP.onrender.com/api/v1` (for OAuth testing)

**Authentication Tokens:**
- `token`: (automatically set after login/register - for regular users)
- `admin_token`: (set manually after logging in as normal admin)
- `tech_admin_token`: (set manually after logging in as technical admin)

**Resource IDs (Auto-saved by Postman tests):**
- `roadmap_id`: (auto-saved after creating roadmap)
- `unit_id`: (auto-saved after creating learning unit)
- `lesson_id`: (auto-saved after creating lesson)
- `sublesson_id`: (auto-saved after creating sub-lesson)
- `resource_id`: (auto-saved after creating resource)
- `quiz_id`: (auto-saved after creating quiz)
- `question_id`: (auto-saved after creating quiz question)
- `challenge_id`: (auto-saved after creating challenge)
- `user_id`: (auto-saved after viewing/creating user)

### Step 2: Create Pre-request Script (for authenticated requests)

Add this to your request's "Pre-request Script" tab:
```javascript
pm.request.headers.add({
    key: 'Authorization',
    value: 'Bearer ' + pm.environment.get('token')
});
```

### Step 3: Auto-save Token After Login

In the Login request's "Tests" tab:
```javascript
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    if (jsonData.success && jsonData.data.token) {
        pm.environment.set('token', jsonData.data.token);
        console.log('Token saved:', jsonData.data.token);
    }
}
```

---

## Testing Workflows

### Complete User Journey Test:

1. **Register** → Save token to `token` variable
2. **Login** → Update `token` variable
3. **Get Profile** → Verify user data
4. **Update Notification Preference** → Toggle `is_notifications_enabled`
5. **Get Roadmaps** → Browse available roadmaps
6. **Enroll in Roadmap** → Enroll in roadmap (save `roadmap_id`)
7. **Get Learning Units** → Get units for roadmap
8. **Get Lessons** → Get lessons for a unit
9. **Open Lesson** → Track lesson opening
10. **Complete Lesson** → Mark lesson as complete
11. **Start Quiz** → Start quiz attempt
12. **Submit Quiz** → Submit answers
13. **Start Challenge** → Start challenge attempt
14. **Submit Challenge** → Submit code
15. **Get My Enrollments** → View progress
16. **Get Lesson Stats** → View completion statistics

---

### Tech Admin Journey Test:

**Prerequisites:** Login as user with `role=tech_admin` and save token to `tech_admin_token`

1. **Login as Tech Admin** → Save token to `tech_admin_token`
2. **Create Roadmap** → Save `roadmap_id` from response
3. **Create Learning Unit** → Save `unit_id` from response
4. **Create Lesson** → Save `lesson_id` from response
5. **Create SubLesson** → Save `sublesson_id` from response
6. **Create Resource** → Save `resource_id` from response
7. **Create Quiz** → Save `quiz_id` from response
8. **Create Quiz Question** → Save `question_id` from response
9. **Create Challenge** → Save `challenge_id` from response
10. **Toggle Active States** → Test toggle-active endpoints
11. **Reorder Units/Lessons/SubLessons** → Test reorder endpoints
12. **Update Resources** → Test update endpoints
13. **Delete Resources** → Test delete endpoints
14. **Verify Student Visibility** → Login as regular user, verify content is visible

**Negative Tests:**
- Try accessing `/admin/users` with `tech_admin_token` → Should return 403
- Try accessing tech endpoints with `admin_token` → Should return 403

---

### Normal Admin Journey Test:

**Prerequisites:** Login as user with `role=admin` and save token to `admin_token`

1. **Login as Normal Admin** → Save token to `admin_token`
2. **List Users** → View all users with filters
3. **Filter by Role** → Test `?role=user`, `?role=admin`, `?role=tech_admin`
4. **Filter by Email** → Test `?email=test`
5. **Filter by Username** → Test `?username=john`
6. **Get Single User** → View user details (save `user_id`)
7. **Update User Role** → Change user role (test all: user, admin, tech_admin)
8. **Update User Email/Username** → Test user updates
9. **Reset User Password** → Test password reset
10. **Revoke User Tokens** → Test logout everywhere
11. **Delete User** → Test user deletion (cannot delete self)

**Negative Tests:**
- Try accessing `/admin/roadmaps` with `admin_token` → Should return 403
- Try accessing `/admin/quizzes` with `admin_token` → Should return 403
- Try deleting own account → Should return 403

---

### OAuth Testing Workflow (Render Production):

**Prerequisites:** Use `render_url` environment variable

1. **Get Google ID Token** → Use Google OAuth Playground
2. **Test Google OAuth** → `POST {{render_url}}/auth/google` with `id_token`
3. **Verify Response** → Check token and user data are returned
4. **Save Token** → Token auto-saved to `token` variable
5. **Get GitHub OAuth Code** → Visit GitHub OAuth authorization URL
6. **Test GitHub OAuth** → `POST {{render_url}}/auth/github` with `code`
7. **Verify Response** → Check token and user data are returned
8. **Save Token** → Token auto-saved to `token` variable
9. **Test Invalid Tokens** → Test error handling with invalid tokens/codes

---

## CORS Configuration for Mobile Clients

### Overview
The API is configured to accept requests from mobile applications. CORS (Cross-Origin Resource Sharing) is handled at the server level.

### Important Notes:
1. **Authorization Header:** Mobile clients must include `Authorization: Bearer TOKEN` in all authenticated requests
2. **Content-Type:** Always use `Content-Type: application/json` for POST/PUT/PATCH requests
3. **Accept Header:** Include `Accept: application/json` to ensure JSON responses
4. **OAuth Callbacks:** For OAuth flows, ensure your callback URLs are properly configured in:
   - Google Cloud Console (for Google OAuth)
   - GitHub OAuth App settings (for GitHub OAuth)
5. **Render Deployment:** When deployed on Render, ensure CORS is configured to allow your mobile app's origin

### Testing CORS in Postman:
- CORS is not enforced in Postman (it's a browser security feature)
- To test CORS, use browser DevTools or a tool like `curl` with CORS headers
- Example curl command:
  ```bash
  curl -X GET "https://YOUR-APP.onrender.com/api/v1/roadmaps" \
    -H "Origin: https://your-mobile-app.com" \
    -H "Access-Control-Request-Method: GET" \
    -v
  ```

### Mobile Client Best Practices:
1. Store tokens securely (use secure storage, not plain text)
2. Implement token refresh logic
3. Handle 401 responses by redirecting to login
4. Include proper error handling for network failures
5. Test OAuth flows on actual devices (not just emulators)

---

## Common Issues & Solutions

### Issue: 401 Unauthorized
**Solution:** 
- Check if token is included in Authorization header
- Token format: `Bearer YOUR_TOKEN`
- Token might be expired - login again

### Issue: 403 Forbidden (Enrollment)
**Solution:**
- User must enroll in roadmap first
- Use `/roadmaps/{id}/enroll` endpoint

### Issue: 403 Forbidden (Challenge/Quiz)
**Solution:**
- For Quiz: All previous lessons must be completed
- For Challenge: User must have enough XP (check `min_xp` requirement)

### Issue: 422 Validation Error
**Solution:**
- Check request body format
- Ensure all required fields are present
- Check field types (string, integer, etc.)

### Issue: 429 Too Many Requests
**Solution:**
- Rate limit exceeded
- Wait 1 minute before retrying
- Challenge submissions: 10 per minute
- Quiz attempts: 5 per minute

---

## Rate Limits Summary

| Endpoint | Limit |
|----------|-------|
| Login/Register | 5 per minute |
| Challenge Attempts | 5 per minute |
| Challenge Submissions | 10 per minute |
| Quiz Attempts | 5 per minute |
| Password Reset | 3 per minute |

---

## Tips

1. **Use Postman Collections**: Organize requests by category
2. **Use Environments**: Switch between local/production easily
3. **Save Responses**: Use Postman's "Save Response" feature
4. **Test Error Cases**: Test with invalid data, missing tokens, etc.
5. **Check Headers**: Always verify `Content-Type: application/json`
6. **Token Management**: Use environment variables for tokens
7. **Pagination**: Test with different `per_page` values

---

## Quick Test Checklist

### User Endpoints
- [ ] Register new user
- [ ] Login and save token
- [ ] Get profile
- [ ] Update notification preference (toggle on/off)
- [ ] List roadmaps
- [ ] Enroll in roadmap
- [ ] Get learning units
- [ ] Get lessons
- [ ] Track lesson (open/complete)
- [ ] Start quiz attempt
- [ ] Submit quiz
- [ ] Start challenge attempt
- [ ] Submit challenge code
- [ ] Get my enrollments
- [ ] Get lesson stats

### OAuth Endpoints (Render)
- [ ] Google OAuth login (get id_token, test endpoint, save token)
- [ ] GitHub OAuth login (get code, test endpoint, save token)
- [ ] Test invalid Google token (401)
- [ ] Test invalid GitHub code (401)

### Tech Admin Endpoints
- [ ] Login as tech_admin and save tech_admin_token
- [ ] Create roadmap (save roadmap_id)
- [ ] Create learning unit (save unit_id)
- [ ] Create lesson (save lesson_id)
- [ ] Create sub-lesson (save sublesson_id)
- [ ] Create resource (save resource_id)
- [ ] Create quiz (save quiz_id)
- [ ] Create quiz question (save question_id)
- [ ] Create challenge (save challenge_id)
- [ ] Toggle active states (roadmap, unit, lesson, challenge)
- [ ] Reorder units/lessons/sub-lessons
- [ ] Update resources
- [ ] Delete resources
- [ ] Test normal admin accessing tech endpoints (403)

### Normal Admin Endpoints
- [ ] Login as admin and save admin_token
- [ ] List users with filters (role, email, username)
- [ ] Get single user (save user_id)
- [ ] Update user (role, email, username, password)
- [ ] Revoke user tokens
- [ ] Delete user
- [ ] Test tech admin accessing user endpoints (403)
- [ ] Test regular user accessing admin endpoints (403)

### Error & Edge Cases
- [ ] Test error responses (401, 403, 404, 422)
- [ ] Test rate limiting (429)
- [ ] Test pagination
- [ ] Test validation errors
- [ ] Test role separation (403 checks)

---

## Example Postman Collection JSON

You can import this into Postman:

```json
{
  "info": {
    "name": "Roadmap Learning Platform API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "variable": [
    {
      "key": "base_url",
      "value": "http://localhost/api/v1"
    },
    {
      "key": "token",
      "value": ""
    }
  ],
  "item": [
    {
      "name": "Auth",
      "item": [
        {
          "name": "Register",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Content-Type",
                "value": "application/json"
              }
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"username\": \"testuser\",\n  \"email\": \"test@example.com\",\n  \"password\": \"Test123!@#\",\n  \"password_confirmation\": \"Test123!@#\"\n}"
            },
            "url": {
              "raw": "{{base_url}}/auth/register",
              "host": ["{{base_url}}"],
              "path": ["auth", "register"]
            }
          }
        }
      ]
    }
  ]
}
```

---

**Happy Testing! 🚀**

