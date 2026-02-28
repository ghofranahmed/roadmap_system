# Learning Path API Implementation Summary

## Overview
This document summarizes the implementation of the Learning Path API endpoints for the mobile app, including enrolled roadmaps, learning path units with lock/completion status, and enhanced sub-lessons with resources.

## Files Created

### Migrations
1. `database/migrations/2026_02_23_120000_add_title_to_quizzes_table.php`
   - Adds `title` column to `quizzes` table (nullable string)

### Controllers
2. `app/Http/Controllers/LearningPathController.php`
   - New controller for learning path endpoint

### API Resources
3. `app/Http/Resources/EnrolledRoadmapResource.php`
   - Resource for enrolled roadmaps with progress calculation
4. `app/Http/Resources/LearningPathUnitResource.php`
   - Resource for learning path units with lock/completion logic
5. `app/Http/Resources/SubLessonResource.php`
   - Resource for sub-lessons with optional resources

## Files Modified

### Models
1. `app/Models/Quiz.php`
   - Added `title` to `$fillable` array

2. `app/Models/LearningUnit.php`
   - Added one-to-one relations: `lesson()`, `quiz()`, `challenge()`
   - Kept existing `hasMany` relations for backward compatibility

### Controllers
3. `app/Http/Controllers/EnrollmentController.php`
   - Added `myEnrolledRoadmaps()` method
   - Uses `EnrolledRoadmapResource` for response

4. `app/Http/Controllers/LessonController.php`
   - Fixed `show()` method signature to match route (single `$lessonId` parameter)
   - Removed dependency on `$learningUnitId` parameter

5. `app/Http/Controllers/SubLessonController.php`
   - Updated `index()` to support `?include=resources` query parameter
   - Uses `SubLessonResource` when resources are included

### Request Validation
6. `app/Http/Requests/StoreQuizRequest.php`
   - Added `title` validation rule (nullable, string, max:255)

7. `app/Http/Requests/UpdateQuizRequest.php`
   - Added `title` validation rule (sometimes, nullable, string, max:255)

### Routes
8. `routes/api.php`
   - Added `GET /api/v1/me/enrolled-roadmaps` route
   - Added `GET /api/v1/roadmaps/{roadmapId}/learning-path` route

## New API Endpoints

### 1. GET /api/v1/me/enrolled-roadmaps
**Authentication:** Required (auth:sanctum)

**Description:** Returns enrolled roadmaps in roadmap-first format for "My Account" tab

**Response Example:**
```json
{
  "success": true,
  "message": "Enrolled roadmaps retrieved successfully",
  "data": [
    {
      "enrollment_id": 12,
      "roadmap_id": 3,
      "title": "Backend Laravel",
      "description": "Learn Laravel backend development",
      "level": "beginner",
      "is_active": true,
      "status": "active",
      "xp_points": 40,
      "progress_percent": 33.33,
      "completed_units": 2,
      "total_units": 6,
      "started_at": "2026-02-20T10:00:00.000000Z",
      "completed_at": null
    }
  ]
}
```

### 2. GET /api/v1/roadmaps/{roadmapId}/learning-path
**Authentication:** Required (auth:sanctum)
**Middleware:** `enrolled` (must be enrolled in roadmap)

**Description:** Returns learning path with ordered units, entity summaries, and lock/completion status

**Response Example:**
```json
{
  "success": true,
  "message": "Learning path retrieved successfully",
  "data": {
    "roadmap": {
      "id": 3,
      "title": "Backend Laravel",
      "description": "Learn Laravel backend development",
      "level": "beginner"
    },
    "units": [
      {
        "id": 21,
        "position": 1,
        "unit_type": "lesson",
        "label": "Intro to HTTP",
        "entity": {
          "id": 55,
          "type": "lesson",
          "title": "Intro to HTTP"
        },
        "is_locked": false,
        "is_completed": true
      },
      {
        "id": 22,
        "position": 2,
        "unit_type": "quiz",
        "label": "HTTP Quiz 1",
        "entity": {
          "id": 18,
          "type": "quiz",
          "title": "HTTP Quiz 1"
        },
        "is_locked": false,
        "is_completed": false
      },
      {
        "id": 23,
        "position": 3,
        "unit_type": "challenge",
        "label": "Build REST Endpoint",
        "entity": {
          "id": 9,
          "type": "challenge",
          "title": "Build REST Endpoint"
        },
        "is_locked": true,
        "is_completed": false
      }
    ]
  }
}
```

### 3. GET /api/v1/lessons/{lessonId}
**Authentication:** Required (auth:sanctum)
**Middleware:** `enrolled`

**Description:** Get lesson details (fixed signature)

**Response Example:**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "id": 55,
    "learning_unit_id": 21,
    "title": "Intro to HTTP",
    "description": "Learn about HTTP methods",
    "position": 1,
    "is_active": true,
    "created_at": "2026-02-20T10:00:00.000000Z",
    "sub_lessons": [
      {
        "id": 101,
        "lesson_id": 55,
        "position": 1,
        "description": "HTTP Methods",
        "created_at": "2026-02-20T10:00:00.000000Z"
      }
    ]
  }
}
```

### 4. GET /api/v1/lessons/{lessonId}/sub-lessons?include=resources
**Authentication:** Required (auth:sanctum)
**Middleware:** `enrolled`

**Description:** Get sub-lessons with optional resources

**Query Parameters:**
- `include=resources` (optional): Include resources in response

**Response Example (with resources):**
```json
{
  "success": true,
  "message": "Sub-lessons retrieved successfully",
  "data": [
    {
      "id": 101,
      "lesson_id": 55,
      "position": 1,
      "description": "HTTP Methods",
      "created_at": "2026-02-20T10:00:00.000000Z",
      "resources": [
        {
          "id": 900,
          "title": "MDN HTTP Methods",
          "type": "article",
          "language": "en",
          "link": "https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods",
          "created_at": "2026-02-20T10:00:00.000000Z"
        }
      ]
    }
  ]
}
```

**Response Example (without resources):**
```json
{
  "success": true,
  "message": "Success",
  "data": [
    {
      "id": 101,
      "lesson_id": 55,
      "position": 1,
      "description": "HTTP Methods",
      "created_at": "2026-02-20T10:00:00.000000Z"
    }
  ]
}
```

## Lock/Completion Logic

### Lesson Units
- **Locked:** If any previous lesson unit (by position) is not completed
- **Completed:** If `lesson_trackings` has `is_complete = true` for the lesson

### Quiz Units
- **Locked:** If not all previous lesson units are completed (uses QuizPolicy logic)
- **Completed:** If `quiz_attempts` has `passed = true` for the quiz

### Challenge Units
- **Locked:** If user's `xp_points` in enrollment < challenge's `min_xp` (uses ChallengePolicy logic)
- **Completed:** If `challenge_attempts` has `passed = true` for the challenge

## Database Changes

### Migration Required
Run the migration to add `title` column to `quizzes` table:
```bash
php artisan migrate
```

**Note:** Existing quizzes will have `title = null`. The learning path API will fallback to `learning_units.title` if quiz title is null.

## Verification Checklist

### 1. Test Enrolled Roadmaps Endpoint
- **URL:** `GET /api/v1/me/enrolled-roadmaps`
- **Headers:** `Authorization: Bearer {token}`
- **Expected:** List of enrolled roadmaps with progress_percent, completed_units, total_units

### 2. Test Learning Path Endpoint
- **URL:** `GET /api/v1/roadmaps/{roadmapId}/learning-path`
- **Headers:** `Authorization: Bearer {token}`
- **Expected:** Ordered units with entity summaries, lock/completion flags
- **Test Cases:**
  - Units ordered by position ASC
  - Each unit has correct entity data based on unit_type
  - Lock flags reflect actual user progress
  - Completion flags reflect actual user progress

### 3. Test Lesson Endpoint (Fixed)
- **URL:** `GET /api/v1/lessons/{lessonId}`
- **Headers:** `Authorization: Bearer {token}`
- **Expected:** Lesson details without requiring learning_unit_id

### 4. Test Sub-Lessons with Resources
- **URL:** `GET /api/v1/lessons/{lessonId}/sub-lessons?include=resources`
- **Headers:** `Authorization: Bearer {token}`
- **Expected:** Sub-lessons with nested resources array

### 5. Test Sub-Lessons without Resources
- **URL:** `GET /api/v1/lessons/{lessonId}/sub-lessons`
- **Headers:** `Authorization: Bearer {token}`
- **Expected:** Sub-lessons without resources (lightweight response)

### 6. Test Quiz Title (Admin)
- **URL:** `POST /api/v1/admin/quizzes`
- **Body:** Include `title` field
- **Expected:** Quiz created with title

## Backward Compatibility

✅ All existing endpoints remain unchanged and functional:
- `GET /api/v1/me/enrollments` (original endpoint still works)
- `GET /api/v1/roadmaps/{roadmapId}/units` (original endpoint still works)
- All other existing endpoints unchanged

## Notes

1. **Quiz Title:** The `title` field is nullable. If null, the learning path API will use `learning_units.title` as fallback.

2. **Public Roadmaps:** Currently, the learning path endpoint requires enrollment. To support public roadmaps, add an `is_public` field to the `roadmaps` table and update the middleware/controller logic.

3. **Performance:** All endpoints use eager loading to avoid N+1 queries.

4. **Lock Logic:** Lock status is calculated dynamically based on user progress. For lessons, it checks previous lesson completions. For quizzes/challenges, it uses the existing policy logic.

## Next Steps (Optional Enhancements)

1. Add `is_public` field to roadmaps table for public roadmap support
2. Add caching for learning path responses (if needed for performance)
3. Add unit tests for new endpoints
4. Add API documentation (Swagger/OpenAPI)

