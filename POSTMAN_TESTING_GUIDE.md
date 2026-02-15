# Postman Testing Guide - Roadmap Learning Platform API

## Base Configuration

### Base URL
```
http://localhost/api/v1
```
Or for production:
```
https://your-domain.com/api/v1
```

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

## 9. Admin Endpoints

### 9.1 List Roadmaps (Admin)
**GET** `/admin/roadmaps?per_page=20&level=beginner`

**Headers:**
```
Authorization: Bearer ADMIN_TOKEN
```

---

### 9.2 Create Roadmap (Admin)
**POST** `/admin/roadmaps`

**Headers:**
```
Authorization: Bearer ADMIN_TOKEN
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "title": "New Roadmap",
  "description": "Description here",
  "level": "beginner",
  "is_active": true
}
```

---

### 9.3 Update Roadmap (Admin)
**PUT** `/admin/roadmaps/{id}`

**Headers:**
```
Authorization: Bearer ADMIN_TOKEN
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "title": "Updated Title",
  "description": "Updated description",
  "level": "intermediate"
}
```

---

### 9.4 Delete Roadmap (Admin)
**DELETE** `/admin/roadmaps/{id}`

**Headers:**
```
Authorization: Bearer ADMIN_TOKEN
```

---

### 9.5 Toggle Roadmap Active (Admin)
**PATCH** `/admin/roadmaps/{id}/toggle-active`

**Headers:**
```
Authorization: Bearer ADMIN_TOKEN
```

---

## 10. Error Responses

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

In Postman, create an environment with:
- `base_url`: `http://localhost/api/v1`
- `token`: (will be set after login)

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

## Testing Workflow

### Complete User Journey Test:

1. **Register** → Save token
2. **Login** → Update token
3. **Get Roadmaps** → Browse available roadmaps
4. **Enroll in Roadmap** → Enroll in roadmap ID 1
5. **Get Learning Units** → Get units for roadmap
6. **Get Lessons** → Get lessons for a unit
7. **Open Lesson** → Track lesson opening
8. **Complete Lesson** → Mark lesson as complete
9. **Start Quiz** → Start quiz attempt
10. **Submit Quiz** → Submit answers
11. **Start Challenge** → Start challenge attempt
12. **Submit Challenge** → Submit code
13. **Get My Enrollments** → View progress

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

- [ ] Register new user
- [ ] Login and save token
- [ ] Get profile
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
- [ ] Test error responses (401, 403, 404, 422)
- [ ] Test rate limiting
- [ ] Test pagination

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

