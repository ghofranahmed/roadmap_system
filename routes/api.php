// routes/api.php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\EnrollmentController;
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
});



Route::middleware('auth:sanctum')->get('/me/community', [CommunityController::class, 'myCommunityRooms']);

