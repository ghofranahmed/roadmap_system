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

/*
|--------------------------------------------------------------------------
| AUTH Routes (Public)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
    Route::post('/verify-reset-token', [PasswordResetController::class, 'verifyToken']);
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
    Route::get('/reset-attempts', [PasswordResetController::class, 'getAttemptsRemaining']);

    // Route::post('/google', [SocialAuthController::class, 'google']);
    // Route::post('/github', [SocialAuthController::class, 'github']);
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

    // Lessons (Naming زي الأول)
    Route::get('/units/{unitId}/lessons', [LessonController::class, 'index']);
    Route::get('/lessons/{lessonId}', [LessonController::class, 'show']);

    // SubLessons (إضافات الثاني لكن naming زي الأول)
    Route::get('/lessons/{lessonId}/sub-lessons', [SubLessonController::class, 'index']);
    Route::get('/lessons/{lessonId}/sub-lessons/{subLessonId}', [SubLessonController::class, 'show']);

    // Resources (إضافات الثاني لكن naming زي الأول)
    Route::get('/sub-lessons/{subLessonId}/resources', [ResourceController::class, 'index']);
    Route::get('/sub-lessons/{subLessonId}/resources/{resourceId}', [ResourceController::class, 'show']);

    // Tracking (كل إضافات الثاني لكن بنفس style الأول)
    Route::prefix('lessons/{lessonId}/track')->group(function () {
        Route::post('/open', [LessonTrackingController::class, 'open']);
        Route::post('/complete', [LessonTrackingController::class, 'complete']);

        Route::patch('/progress', [LessonTrackingController::class, 'updateProgress']);
        Route::get('/', [LessonTrackingController::class, 'show']);
        Route::delete('/reset', [LessonTrackingController::class, 'reset']);
    });

    Route::get('/me/lessons/tracking', [LessonTrackingController::class, 'userLessons']);
    Route::get('/me/lessons/stats', [LessonTrackingController::class, 'userStats']);
});


/*
|--------------------------------------------------------------------------
| Admin Routes (Full CRUD)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {

    // Roadmaps Admin (زي الأول)
    Route::prefix('roadmaps')->group(function () {
        Route::get('/', [AdminRoadmapController::class, 'index']);
        Route::post('/add', [AdminRoadmapController::class, 'store']);
        Route::put('/{id}', [AdminRoadmapController::class, 'update']);
        Route::delete('/{id}', [AdminRoadmapController::class, 'destroy']);
        Route::patch('/{id}/toggle-active', [AdminRoadmapController::class, 'toggleActive']);
    });

    // Learning Units Admin (زي الأول)
    Route::get('/roadmaps/{roadmapId}/units', [LearningUnitController::class, 'adminIndex']);
    Route::post('/roadmaps/{roadmapId}/units', [LearningUnitController::class, 'store']);
    Route::put('/units/{unitId}', [LearningUnitController::class, 'update']);
    Route::delete('/units/{unitId}', [LearningUnitController::class, 'destroy']);
    Route::patch('/roadmaps/{roadmapId}/units/reorder', [LearningUnitController::class, 'reorder']);
    Route::patch('/units/{unitId}/toggle-active', [LearningUnitController::class, 'toggleActive']);

    // Lessons Admin (نفس منطق الأول + إضافات reorder/toggle)
    Route::get('/units/{unitId}/lessons', [LessonController::class, 'adminIndex']);
    Route::post('/units/{unitId}/lessons', [LessonController::class, 'store']);
    Route::put('/lessons/{lessonId}', [LessonController::class, 'update']);
    Route::delete('/lessons/{lessonId}', [LessonController::class, 'destroy']);
    Route::patch('/units/{unitId}/lessons/reorder', [LessonController::class, 'reorder']);
    Route::patch('/lessons/{lessonId}/toggle-active', [LessonController::class, 'toggleActive']);

    // SubLessons Admin (CRUD + reorder)
    Route::get('/lessons/{lessonId}/sub-lessons', [SubLessonController::class, 'adminIndex']);
    Route::post('/lessons/{lessonId}/sub-lessons', [SubLessonController::class, 'store']);
    Route::put('/sub-lessons/{subLessonId}', [SubLessonController::class, 'update']);
    Route::delete('/sub-lessons/{subLessonId}', [SubLessonController::class, 'destroy']);
    Route::patch('/lessons/{lessonId}/sub-lessons/reorder', [SubLessonController::class, 'reorder']);

    // Resources Admin (CRUD + search)
    Route::get('/sub-lessons/{subLessonId}/resources', [ResourceController::class, 'adminIndex']);
    Route::post('/sub-lessons/{subLessonId}/resources', [ResourceController::class, 'store']);
    Route::put('/resources/{resourceId}', [ResourceController::class, 'update']);
    Route::delete('/resources/{resourceId}', [ResourceController::class, 'destroy']);
    Route::get('/resources/search', [ResourceController::class, 'search']);
});
