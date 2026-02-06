// routes/api.php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\LearningUnitController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\{LessonController, SubLessonController, ResourceController, LessonTrackingController};

use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\RoadmapController;
use App\Http\Controllers\Admin\RoadmapController as AdminRoadmapController;

Route::prefix('auth')->group(function () {
     // =========================
    // PUBLIC (No token required)
    // =========================
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

     // =========================
     //Password reset routes
     // =========================
    Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
    Route::post('/verify-reset-token', [PasswordResetController::class, 'verifyToken']);
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
    Route::get('/reset-attempts', [PasswordResetController::class, 'getAttemptsRemaining']);


    /*Route::post('/google', [SocialAuthController::class, 'google']);   // id_token
    Route::post('/github', [SocialAuthController::class, 'github']);*/ 
});

// Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/update-account', [AuthController::class, 'updateAccount']);
    Route::post('/update-profile-picture', [AuthController::class, 'updateProfilePicture']);
     Route::delete('/delete-profile-picture', [AuthController::class, 'deleteProfilePicture']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::delete('/delete-account', [AuthController::class, 'deleteAccount']);
});

// for normal users
Route::prefix('/roadmaps')->group(function () {
    Route::get('/', [RoadmapController::class, 'index'])->name('roadmaps.index');
    Route::get('/search', [RoadmapController::class, 'search'])->name('roadmaps.search');
    Route::get('/{id}', [RoadmapController::class, 'show'])->name('roadmaps.show');
    Route::get('/{id}/enrollments', [RoadmapController::class, 'enrollments'])->name('roadmaps.enrollments');
});

    
// Routes للمسؤولين (محمية بـ middleware 'auth:sanctum' و 'admin')
Route::prefix('/admin/roadmaps')
    ->middleware(['auth:sanctum', 'admin'])->
    group(function () {
    Route::get('/', [AdminRoadmapController::class, 'index']);
    Route::post('/add', [AdminRoadmapController::class, 'store']);
    Route::put('/{id}', [AdminRoadmapController::class, 'update']);
    Route::delete('/{id}', [AdminRoadmapController::class, 'destroy']);
    Route::patch('/{id}/toggle-active', [AdminRoadmapController::class, 'toggleActive']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/roadmaps/{id}/enroll', [EnrollmentController::class, 'enroll']);
    Route::get('/me/enrollments', [EnrollmentController::class, 'myEnrollments']);
    Route::patch('/me/enrollments/{roadmapId}/status', [EnrollmentController::class, 'updateStatus']);
    Route::delete('/roadmaps/{id}/unenroll', [EnrollmentController::class, 'unenroll']);

});



Route::middleware('auth:sanctum')
->get('/me/community', [CommunityController::class, 'myCommunityRooms']);



/*
|--------------------------------------------------------------------------
| User Routes (Read Only + Enrolled Check)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'enrolled'])->group(function () {
    Route::get('/roadmaps/{roadmapId}/units', [LearningUnitController::class, 'index']);
    Route::get('/roadmaps/{roadmapId}/units/{unitId}', [LearningUnitController::class, 'show']);
});

/*
|--------------------------------------------------------------------------
| Admin Routes (Full CRUD)
|--------------------------------------------------------------------------
*/
// نفترض وجود middleware اسمه 'admin' للتحقق من صلاحية المسؤول
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    
    // Get & Create scoped by Roadmap
    Route::get('/roadmaps/{roadmapId}/units', [LearningUnitController::class, 'adminIndex']);
    Route::post('/roadmaps/{roadmapId}/units', [LearningUnitController::class, 'store']);

    // Update & Delete scoped by Unit ID directly
    Route::put('/units/{unitId}', [LearningUnitController::class, 'update']);
    Route::delete('/units/{unitId}', [LearningUnitController::class, 'destroy']);
    // 1. Reorder Units (Drag & Drop)
    // نستخدم PATCH لأننا نعدل جزئياً في مجموعة موارد
    Route::patch('/roadmaps/{roadmapId}/units/reorder', [LearningUnitController::class, 'reorder']);

    // 2. Toggle Active Status
    Route::patch('/units/{unitId}/toggle-active', [LearningUnitController::class, 'toggleActive']);
});

// User Routes (المتعلم)
Route::middleware(['auth:sanctum', 'enrolled'])->group(function () {
    Route::get('/units/{unitId}/lessons', [LessonController::class, 'index']);
    Route::get('/lessons/{lessonId}', [LessonController::class, 'show']);
    Route::get('/sub-lessons/{subLessonId}/resources', [ResourceController::class, 'index']);
    
    // Tracking
    Route::post('/lessons/{lessonId}/track/open', [LessonTrackingController::class, 'open']);
    Route::post('/lessons/{lessonId}/track/complete', [LessonTrackingController::class, 'complete']);
});

// Admin Routes (المسؤول)
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Lessons
    Route::get('/units/{unitId}/lessons', [LessonController::class, 'adminIndex']);
    Route::post('/units/{unitId}/lessons', [LessonController::class, 'store']);
    Route::put('/lessons/{lessonId}', [LessonController::class, 'update']);
    Route::delete('/lessons/{lessonId}', [LessonController::class, 'destroy']);

    // Sub-Lessons
    Route::get('/lessons/{lessonId}/sub-lessons', [SubLessonController::class, 'index']);
    Route::post('/lessons/{lessonId}/sub-lessons', [SubLessonController::class, 'store']);
    Route::put('/sub-lessons/{subLessonId}', [SubLessonController::class, 'update']);
    Route::delete('/sub-lessons/{subLessonId}', [SubLessonController::class, 'destroy']);

    // Resources
    Route::get('/sub-lessons/{subLessonId}/resources', [ResourceController::class, 'adminIndex']);
    Route::post('/sub-lessons/{subLessonId}/resources', [ResourceController::class, 'store']);
    Route::put('/resources/{resourceId}', [ResourceController::class, 'update']);
    Route::delete('/resources/{resourceId}', [ResourceController::class, 'destroy']);
});