# Quick Postman Setup Guide

## Step 1: Import Collection

1. Open Postman
2. Click **Import** button (top left)
3. Select the file: `Roadmap_API.postman_collection.json`
4. Collection will be imported with all endpoints organized

## Step 2: Create Environment

1. Click **Environments** (left sidebar)
2. Click **+** to create new environment
3. Name it: `Roadmap API Local`
4. Add these variables:

| Variable | Initial Value | Current Value |
|----------|---------------|---------------|
| `base_url` | `http://localhost/api/v1` | `http://localhost/api/v1` |
| `token` | (leave empty) | (will auto-fill after login) |
| `admin_token` | (leave empty) | (will auto-fill after admin login) |
| `user_id` | (leave empty) | (will auto-fill after login) |

5. Click **Save**

## Step 3: Select Environment

- Click the environment dropdown (top right)
- Select `Roadmap API Local`

## Step 4: Test Connection

1. Open collection: **Roadmap Learning Platform API v1**
2. Go to **Auth** → **Test Connection**
3. Click **Send**
4. Should get: `{"success": true, "message": "الاتصال ناجح..."}`

## Step 5: Register/Login

1. Go to **Auth** → **Register**
2. Update the JSON body with your test data
3. Click **Send**
4. Token will be **automatically saved** to environment variable
5. Or use **Login** endpoint

## Step 6: Test Authenticated Endpoints

Now all authenticated requests will use the saved token automatically!

Try:
- **Roadmaps** → **List Roadmaps**
- **Enrollments** → **Enroll in Roadmap**
- **Lessons** → **Get Learning Units**

---

## Quick Test Flow

### 1. Authentication
```
POST /auth/register → Save token
OR
POST /auth/login → Save token
```

### 2. Browse Roadmaps
```
GET /roadmaps → See available roadmaps
GET /roadmaps/1 → Get roadmap details
```

### 3. Enroll
```
POST /roadmaps/1/enroll → Enroll in roadmap
GET /me/enrollments → See your enrollments
```

### 4. Learn
```
GET /roadmaps/1/units → Get learning units
GET /units/1/lessons → Get lessons
POST /lessons/1/track/open → Track lesson
POST /lessons/1/track/complete → Complete lesson
```

### 5. Quiz
```
GET /units/1/quizzes → Get quiz
GET /quizzes/1 → Start attempt (get questions)
PUT /quiz-attempts/1/submit → Submit answers
```

### 6. Challenge
```
GET /units/1/challenges → Get challenge
POST /challenges/1/attempts → Start attempt
PUT /challenge-attempts/1/submit → Submit code
```

---

## Environment Variables Auto-Update

The collection includes **Test Scripts** that automatically:
- Save token after login/register
- Save user_id after login/register

You don't need to manually copy tokens!

---

## Tips

1. **Use Collection Variables**: All requests use `{{base_url}}` variable
2. **Auto Token Management**: Login/Register endpoints save token automatically
3. **Organized Folders**: Endpoints grouped by feature
4. **Pre-filled Examples**: Most requests have example JSON bodies
5. **Query Parameters**: Many requests have pre-configured query params

---

## Common Issues

### Token Not Working
- Check if token is saved in environment
- Try logging in again
- Check token format: `Bearer YOUR_TOKEN`

### 403 Forbidden
- Make sure you're enrolled in the roadmap
- Check if you have enough XP for challenges
- Verify all previous lessons are completed for quizzes

### 404 Not Found
- Check if IDs exist in database
- Verify base_url is correct
- Ensure route uses `/api/v1` prefix

---

## Testing Checklist

- [ ] Test connection works
- [ ] Register new user
- [ ] Login and verify token saved
- [ ] Get profile
- [ ] List roadmaps
- [ ] Enroll in roadmap
- [ ] Get learning units
- [ ] Complete a lesson
- [ ] Start quiz attempt
- [ ] Submit quiz
- [ ] Start challenge attempt
- [ ] Submit challenge code
- [ ] Test error responses (401, 403, 404)

---

**Ready to test! 🚀**

