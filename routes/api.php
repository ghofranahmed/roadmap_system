// routes/api.php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\PasswordResetController;



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


    Route::post('/google', [SocialAuthController::class, 'google']);   // id_token
    Route::post('/github', [SocialAuthController::class, 'github']); 
});

// Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/update-account', [AuthController::class, 'updateAccount']);
    Route::post('/update-profile-picture', [AuthController::class, 'updateProfilePicture']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::delete('/delete-account', [AuthController::class, 'deleteAccount']);
});


