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
| Admin Routes (Full CRUD)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {

    // Roadmaps Admin
    Route::prefix('roadmaps')->group(function () {
        Route::get('/', [AdminRoadmapController::class, 'index']);
        Route::post('/', [AdminRoadmapController::class, 'store']);
        Route::put('/{id}', [AdminRoadmapController::class, 'update']);
        Route::delete('/{id}', [AdminRoadmapController::class, 'destroy']);
        Route::patch('/{id}/toggle-active', [AdminRoadmapController::class, 'toggleActive']);
    });

    // Learning Units Admin
    Route::get('/roadmaps/{roadmapId}/units', [LearningUnitController::class, 'adminIndex']);
    Route::post('/roadmaps/{roadmapId}/units', [LearningUnitController::class, 'store']);
    Route::put('/units/{unitId}', [LearningUnitController::class, 'update']);
    Route::delete('/units/{unitId}', [LearningUnitController::class, 'destroy']);
    Route::patch('/roadmaps/{roadmapId}/units/reorder', [LearningUnitController::class, 'reorder']);
    Route::patch('/units/{unitId}/toggle-active', [LearningUnitController::class, 'toggleActive']);

    // Lessons Admin
    Route::get('/units/{unitId}/lessons', [LessonController::class, 'adminIndex']);
    Route::post('/units/{unitId}/lessons', [LessonController::class, 'store']);
    Route::put('/lessons/{lessonId}', [LessonController::class, 'update']);
    Route::delete('/lessons/{lessonId}', [LessonController::class, 'destroy']);
    Route::patch('/units/{unitId}/lessons/reorder', [LessonController::class, 'reorder']);
    Route::patch('/lessons/{lessonId}/toggle-active', [LessonController::class, 'toggleActive']);

    // SubLessons Admin
    Route::get('/lessons/{lessonId}/sub-lessons', [SubLessonController::class, 'adminIndex']);
    Route::post('/lessons/{lessonId}/sub-lessons', [SubLessonController::class, 'store']);
    Route::put('/sub-lessons/{subLessonId}', [SubLessonController::class, 'update']);
    Route::delete('/sub-lessons/{subLessonId}', [SubLessonController::class, 'destroy']);
    Route::patch('/lessons/{lessonId}/sub-lessons/reorder', [SubLessonController::class, 'reorder']);

    // Resources Admin
    Route::get('/sub-lessons/{subLessonId}/resources', [ResourceController::class, 'adminIndex']);
    Route::post('/sub-lessons/{subLessonId}/resources', [ResourceController::class, 'store']);
    Route::put('/resources/{resourceId}', [ResourceController::class, 'update']);
    Route::delete('/resources/{resourceId}', [ResourceController::class, 'destroy']);
    Route::get('/resources/search', [ResourceController::class, 'search']);

    /*
    |--------------------------------------------------------------------------
    | Admin: Quiz + Questions + Challenge
    |--------------------------------------------------------------------------
    */

    // Quizzes
    Route::apiResource('quizzes', AdminQuizController::class);

    // Quiz Questions
    Route::get('quizzes/{quizId}/questions', [AdminQuizQuestionController::class, 'index']);
    Route::post('quizzes/{quizId}/questions', [AdminQuizQuestionController::class, 'store']);
    Route::put('questions/{questionId}', [AdminQuizQuestionController::class, 'update']);
    Route::delete('questions/{questionId}', [AdminQuizQuestionController::class, 'destroy']);

    // Challenges (under unit)
    Route::get('/units/{unitId}/challenges', [AdminChallengeController::class, 'index']);
    Route::post('/units/{unitId}/challenges', [AdminChallengeController::class, 'store']);

    Route::get('/challenges/{challengeId}', [AdminChallengeController::class, 'show']);
    Route::put('/challenges/{challengeId}', [AdminChallengeController::class, 'update']);
    Route::delete('/challenges/{challengeId}', [AdminChallengeController::class, 'destroy']);
    Route::patch('/challenges/{challengeId}/toggle-active', [AdminChallengeController::class, 'toggleActive']);
});

}); // End v1 prefix
