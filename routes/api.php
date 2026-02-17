<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\SocialAuthController;

use App\Http\Controllers\RoadmapController;
use App\Http\Controllers\Admin\RoadmapController as AdminRoadmapController;

use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\CommunityController;

use App\Http\Controllers\LearningUnitController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\SubLessonController;
use App\Http\Controllers\ResourceController;

use App\Http\Controllers\LessonTrackingController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\ChallengeController;

use App\Http\Controllers\Admin\AdminQuizController;
use App\Http\Controllers\Admin\AdminChallengeController;
use App\Http\Controllers\Admin\AdminQuizQuestionController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminChatModerationController;
use App\Http\Controllers\Admin\AdminAnnouncementController;
use App\Http\Controllers\Admin\ReadOnly\AdminRoadmapReadController;
use App\Http\Controllers\Admin\ReadOnly\AdminContentReadController;
use App\Http\Controllers\ChatMessageController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| API v1 Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {

/*
|--------------------------------------------------------------------------
| AUTH Routes (Public)
|--------------------------------------------------------------------------
*/
Route::get('/test-connection', function () {
    return response()->json([
        'success' => true,
        'message' => 'الاتصال ناجح والجهاز واصل بالإنترنت!',
        'data' => null
    ]);
});

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword'])->middleware('throttle:3,1');
    Route::post('/verify-reset-token', [PasswordResetController::class, 'verifyToken'])->middleware('throttle:5,1');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->middleware('throttle:3,1');
    Route::get('/reset-attempts', [PasswordResetController::class, 'getAttemptsRemaining']);

    Route::post('/google', [SocialAuthController::class, 'google'])->middleware('throttle:5,1');
    Route::post('/github', [SocialAuthController::class, 'github'])->middleware('throttle:5,1');
});

/*
|--------------------------------------------------------------------------
| Protected Routes (All authenticated users)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/update-account', [AuthController::class, 'updateAccount']);

    Route::post('/update-profile-picture', [AuthController::class, 'updateProfilePicture']);
    Route::delete('/delete-profile-picture', [AuthController::class, 'deleteProfilePicture']);

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::delete('/delete-account', [AuthController::class, 'deleteAccount']);
    
    Route::patch('/me/notifications', [AuthController::class, 'updateNotificationPreference']);
});

/*
|--------------------------------------------------------------------------
| Roadmaps (Public - Read Only)
|--------------------------------------------------------------------------
*/
Route::prefix('roadmaps')->group(function () {
    Route::get('/', [RoadmapController::class, 'index'])->name('roadmaps.index');
    Route::get('/search', [RoadmapController::class, 'search'])->name('roadmaps.search');
    Route::get('/{id}', [RoadmapController::class, 'show'])->name('roadmaps.show');
    Route::get('/{id}/enrollments', [RoadmapController::class, 'enrollments'])->name('roadmaps.enrollments');
});

/*
|--------------------------------------------------------------------------
| Enrollments (Authenticated users)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/roadmaps/{id}/enroll', [EnrollmentController::class, 'enroll']);
    Route::get('/me/enrollments', [EnrollmentController::class, 'myEnrollments']);
    Route::patch('/me/enrollments/{roadmapId}/status', [EnrollmentController::class, 'updateStatus']);
    Route::delete('/roadmaps/{id}/unenroll', [EnrollmentController::class, 'unenroll']);
});

/*
|--------------------------------------------------------------------------
| Community (Authenticated users)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->get('/me/community', [CommunityController::class, 'myCommunityRooms']);

/*
|--------------------------------------------------------------------------
| User Routes (Read Only + Enrolled Check)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'enrolled'])->group(function () {

    // Learning Units
    Route::get('/roadmaps/{roadmapId}/units', [LearningUnitController::class, 'index']);
    Route::get('/roadmaps/{roadmapId}/units/{unitId}', [LearningUnitController::class, 'show']);

    // Lessons
    Route::get('/units/{unitId}/lessons', [LessonController::class, 'index']);
    Route::get('/lessons/{lessonId}', [LessonController::class, 'show']);

    // SubLessons
    Route::get('/lessons/{lessonId}/sub-lessons', [SubLessonController::class, 'index']);
    Route::get('/lessons/{lessonId}/sub-lessons/{subLessonId}', [SubLessonController::class, 'show']);

    // Resources
    Route::get('/sub-lessons/{subLessonId}/resources', [ResourceController::class, 'index']);
    Route::get('/sub-lessons/{subLessonId}/resources/{resourceId}', [ResourceController::class, 'show']);

    // Tracking (✅ مطابق لجدول lesson_trackings الحالي: is_complete + last_updated_at)
    Route::prefix('lessons/{lessonId}/track')->group(function () {
        Route::post('/open', [LessonTrackingController::class, 'open']);
        Route::post('/complete', [LessonTrackingController::class, 'complete']);
        Route::get('/', [LessonTrackingController::class, 'show']);
        Route::delete('/reset', [LessonTrackingController::class, 'reset']);
    });

    Route::get('/me/lessons/tracking', [LessonTrackingController::class, 'userLessons']);
    Route::get('/me/lessons/stats', [LessonTrackingController::class, 'userStats']);

    /*
    |--------------------------------------------------------------------------
    | Quiz + Challenge (Student - enrolled)
    |--------------------------------------------------------------------------
    */

    // ===== QUIZZES =====
    Route::get('/units/{unitId}/quizzes', [QuizController::class, 'index']);
    Route::get('/quizzes/{quizId}', [QuizController::class, 'startAttempt'])->middleware('throttle:5,1');
    Route::put('/quiz-attempts/{attemptId}/submit', [QuizController::class, 'submitAttempt'])->middleware('throttle:5,1');
    Route::get('/quiz-attempts/{attemptId}', [QuizController::class, 'showAttempt']);
    Route::get('/quizzes/{quizId}/my-attempts', [QuizController::class, 'myAttempts']);

    // ===== CHALLENGES =====
    Route::get('/units/{unitId}/challenges', [ChallengeController::class, 'index']);
    Route::post('/challenges/{challengeId}/attempts', [ChallengeController::class, 'startAttempt'])
        ->middleware('throttle:5,1');
    Route::put('/challenge-attempts/{challengeAttemptId}/submit', [ChallengeController::class, 'submitAttempt'])
        ->middleware('throttle:10,1');
    Route::get('/challenge-attempts/{challengeAttemptId}', [ChallengeController::class, 'showAttempt']);
    Route::get('/challenges/{challengeId}/my-attempts', [ChallengeController::class, 'myAttempts']);
});

/*
|--------------------------------------------------------------------------
| Chat Messages (Authenticated users)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // List & send messages (roadmap-scoped)
    Route::get('/roadmaps/{roadmapId}/chat/messages', [ChatMessageController::class, 'index']);
    Route::post('/roadmaps/{roadmapId}/chat/messages', [ChatMessageController::class, 'store']);

    // Edit & delete messages (message-scoped)
    Route::patch('/chat/messages/{messageId}', [ChatMessageController::class, 'update']);
    Route::delete('/chat/messages/{messageId}', [ChatMessageController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Announcements (Authenticated users)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/announcements', [AnnouncementController::class, 'index']);
    Route::get('/announcements/technical', [AnnouncementController::class, 'technical']);
});

/*
|--------------------------------------------------------------------------
| Notifications (Authenticated users)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
});

/*
|--------------------------------------------------------------------------
| Admin Routes — Normal Admin Only (role:admin)
|--------------------------------------------------------------------------
| User management, announcements, and chat moderation.
| These endpoints are EXCLUSIVELY for the normal admin role.
*/
Route::middleware(['auth:sanctum', 'role:admin'])
    ->prefix('admin')
    ->group(function () {

    // ─── A) User Management (admin only) ────────────────────────
    Route::prefix('users')->group(function () {
        Route::get('/', [AdminUserController::class, 'index']);
        Route::get('/{id}', [AdminUserController::class, 'show']);
        Route::put('/{id}', [AdminUserController::class, 'update']);
        Route::delete('/{id}', [AdminUserController::class, 'destroy']);
        Route::post('/{id}/revoke-tokens', [AdminUserController::class, 'revokeTokens']);
    });

    // ─── B) Announcements Management (admin only) ───────────────
    Route::prefix('announcements')->group(function () {
        Route::get('/', [AdminAnnouncementController::class, 'index']);
        Route::post('/', [AdminAnnouncementController::class, 'store']);
        Route::get('/{id}', [AdminAnnouncementController::class, 'show']);
    });

    // ─── C) Chat Moderation (admin only) ────────────────────────
    Route::post('/roadmaps/{roadmapId}/chat/mute', [AdminChatModerationController::class, 'mute']);
    Route::post('/roadmaps/{roadmapId}/chat/unmute', [AdminChatModerationController::class, 'unmute']);
    Route::post('/roadmaps/{roadmapId}/chat/ban', [AdminChatModerationController::class, 'ban']);
    Route::post('/roadmaps/{roadmapId}/chat/unban', [AdminChatModerationController::class, 'unban']);
    Route::get('/roadmaps/{roadmapId}/chat/members', [AdminChatModerationController::class, 'members']);
});

/*
|--------------------------------------------------------------------------
| Admin Routes — Shared READ-ONLY Content (role:admin,tech_admin)
|--------------------------------------------------------------------------
| Both admin and tech_admin can read content in the admin panel.
| List + show ONLY — no create / update / delete.
*/
Route::middleware(['auth:sanctum', 'role:admin,tech_admin'])
    ->prefix('admin')
    ->group(function () {

    Route::get('/roadmaps', [AdminRoadmapReadController::class, 'index']);
    Route::get('/roadmaps/{id}', [AdminRoadmapReadController::class, 'show']);

    Route::get('/roadmaps/{roadmapId}/units', [AdminContentReadController::class, 'unitsIndex']);
    Route::get('/units/{unitId}', [AdminContentReadController::class, 'unitShow']);

    Route::get('/units/{unitId}/lessons', [AdminContentReadController::class, 'lessonsIndex']);
    Route::get('/lessons/{lessonId}', [AdminContentReadController::class, 'lessonShow']);

    Route::get('/lessons/{lessonId}/sub-lessons', [AdminContentReadController::class, 'subLessonsIndex']);
    Route::get('/sub-lessons/{subLessonId}', [AdminContentReadController::class, 'subLessonShow']);

    Route::get('/sub-lessons/{subLessonId}/resources', [AdminContentReadController::class, 'resourcesIndex']);
    Route::get('/resources/{resourceId}', [AdminContentReadController::class, 'resourceShow']);

    Route::get('/resources/search', [ResourceController::class, 'search']);

    Route::get('/units/{unitId}/quizzes', [AdminContentReadController::class, 'quizzesIndex']);
    Route::get('/quizzes/{quizId}', [AdminContentReadController::class, 'quizShow']);
    Route::get('/quizzes/{quizId}/questions', [AdminQuizQuestionController::class, 'index']);

    Route::get('/units/{unitId}/challenges', [AdminContentReadController::class, 'challengesIndex']);
    Route::get('/challenges/{challengeId}', [AdminContentReadController::class, 'challengeShow']);
});

/*
|--------------------------------------------------------------------------
| Admin Routes — Tech Admin Only (role:tech_admin)
|--------------------------------------------------------------------------
| Full content CRUD (write operations). NO access to user management.
| GET routes are handled by the shared read group above.
*/
Route::middleware(['auth:sanctum', 'role:tech_admin'])
    ->prefix('admin')
    ->group(function () {

    // ─── Roadmaps (create / update / delete / toggle) ───────────
    Route::post('/roadmaps', [AdminRoadmapController::class, 'store']);
    Route::put('/roadmaps/{id}', [AdminRoadmapController::class, 'update']);
    Route::delete('/roadmaps/{id}', [AdminRoadmapController::class, 'destroy']);
    Route::patch('/roadmaps/{id}/toggle-active', [AdminRoadmapController::class, 'toggleActive']);

    // ─── Learning Units (create / update / delete / reorder / toggle) ──
    Route::post('/roadmaps/{roadmapId}/units', [LearningUnitController::class, 'store']);
    Route::put('/units/{unitId}', [LearningUnitController::class, 'update']);
    Route::delete('/units/{unitId}', [LearningUnitController::class, 'destroy']);
    Route::patch('/roadmaps/{roadmapId}/units/reorder', [LearningUnitController::class, 'reorder']);
    Route::patch('/units/{unitId}/toggle-active', [LearningUnitController::class, 'toggleActive']);

    // ─── Lessons (create / update / delete / reorder / toggle) ──
    Route::post('/units/{unitId}/lessons', [LessonController::class, 'store']);
    Route::put('/lessons/{lessonId}', [LessonController::class, 'update']);
    Route::delete('/lessons/{lessonId}', [LessonController::class, 'destroy']);
    Route::patch('/units/{unitId}/lessons/reorder', [LessonController::class, 'reorder']);
    Route::patch('/lessons/{lessonId}/toggle-active', [LessonController::class, 'toggleActive']);

    // ─── SubLessons (create / update / delete / reorder) ────────
    Route::post('/lessons/{lessonId}/sub-lessons', [SubLessonController::class, 'store']);
    Route::put('/sub-lessons/{subLessonId}', [SubLessonController::class, 'update']);
    Route::delete('/sub-lessons/{subLessonId}', [SubLessonController::class, 'destroy']);
    Route::patch('/lessons/{lessonId}/sub-lessons/reorder', [SubLessonController::class, 'reorder']);

    // ─── Resources (create / update / delete) ───────────────────
    Route::post('/sub-lessons/{subLessonId}/resources', [ResourceController::class, 'store']);
    Route::put('/resources/{resourceId}', [ResourceController::class, 'update']);
    Route::delete('/resources/{resourceId}', [ResourceController::class, 'destroy']);

    // ─── Quizzes (create / update / delete) ─────────────────────
    Route::post('/quizzes', [AdminQuizController::class, 'store']);
    Route::put('/quizzes/{quiz}', [AdminQuizController::class, 'update']);
    Route::delete('/quizzes/{quiz}', [AdminQuizController::class, 'destroy']);

    // ─── Quiz Questions (create / update / delete) ──────────────
    Route::post('/quizzes/{quizId}/questions', [AdminQuizQuestionController::class, 'store']);
    Route::put('/questions/{questionId}', [AdminQuizQuestionController::class, 'update']);
    Route::delete('/questions/{questionId}', [AdminQuizQuestionController::class, 'destroy']);

    // ─── Challenges (create / update / delete / toggle) ─────────
    Route::post('/units/{unitId}/challenges', [AdminChallengeController::class, 'store']);
    Route::put('/challenges/{challengeId}', [AdminChallengeController::class, 'update']);
    Route::delete('/challenges/{challengeId}', [AdminChallengeController::class, 'destroy']);
    Route::patch('/challenges/{challengeId}/toggle-active', [AdminChallengeController::class, 'toggleActive']);
});

}); // End v1 prefix
