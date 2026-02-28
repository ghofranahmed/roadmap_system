<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\AdminPasswordResetController;

// Authentication routes for Admin Panel
Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AdminLoginController::class, 'login']);
Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');

// Password Reset routes
Route::get('/forgot-password', [AdminPasswordResetController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AdminPasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AdminPasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [AdminPasswordResetController::class, 'reset'])->name('password.update');

// Admin routes (require authentication and admin role)
Route::middleware(['web', 'auth', 'is_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Dashboard
        Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        
        // Announcements CRUD (Normal Admin only)
        // Note: Protected by AnnouncementPolicy (isNormalAdmin() check in controller)
        Route::resource('announcements', \App\Http\Controllers\Admin\AnnouncementController::class);
        
        // Notifications Management (Normal Admin only)
        Route::middleware('role:admin')->group(function () {
            Route::resource('notifications', \App\Http\Controllers\Admin\NotificationController::class)
                ->only(['index', 'create', 'store', 'show', 'destroy']);
        });
        
        // Chat Moderation (Normal Admin only)
        Route::get('/chat-moderation', [\App\Http\Controllers\Admin\ChatModerationController::class, 'index'])->name('chat-moderation.index');
        Route::get('/chat-moderation/roadmaps/{roadmap}/members', [\App\Http\Controllers\Admin\ChatModerationController::class, 'members'])->name('chat-moderation.members');
        Route::post('/chat-moderation/roadmaps/{roadmap}/mute', [\App\Http\Controllers\Admin\ChatModerationController::class, 'mute'])->name('chat-moderation.mute');
        Route::post('/chat-moderation/roadmaps/{roadmap}/unmute', [\App\Http\Controllers\Admin\ChatModerationController::class, 'unmute'])->name('chat-moderation.unmute');
        Route::post('/chat-moderation/roadmaps/{roadmap}/ban', [\App\Http\Controllers\Admin\ChatModerationController::class, 'ban'])->name('chat-moderation.ban');
        Route::post('/chat-moderation/roadmaps/{roadmap}/unban', [\App\Http\Controllers\Admin\ChatModerationController::class, 'unban'])->name('chat-moderation.unban');
        
        // Smart Teacher Management (Normal Admin only)
        Route::get('/smart-teacher', [\App\Http\Controllers\Admin\SmartTeacherController::class, 'index'])->name('smart-teacher.index');
        Route::put('/smart-teacher', [\App\Http\Controllers\Admin\SmartTeacherController::class, 'update'])->name('smart-teacher.update');
        Route::get('/smart-teacher/logs', [\App\Http\Controllers\Admin\SmartTeacherController::class, 'logs'])->name('smart-teacher.logs');
        Route::get('/smart-teacher/sessions/{id}', [\App\Http\Controllers\Admin\SmartTeacherController::class, 'showSession'])->name('smart-teacher.show-session');
        
        // System Settings (Normal Admin only)
        Route::get('/system-settings', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'index'])->name('system-settings.index');
        Route::put('/system-settings', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'update'])->name('system-settings.update');
        Route::post('/system-settings/logo', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'uploadLogo'])->name('system-settings.upload-logo');
        Route::post('/system-settings/favicon', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'uploadFavicon'])->name('system-settings.upload-favicon');
        
        // Create Regular Admin (Regular Admin only)
        Route::middleware('role:admin')->group(function () {
            Route::get('/create-regular-admin', [\App\Http\Controllers\Admin\CreateRegularAdminController::class, 'create'])->name('create-regular-admin');
            Route::post('/create-regular-admin', [\App\Http\Controllers\Admin\CreateRegularAdminController::class, 'store'])->name('create-regular-admin.store');
        });
        
        // Create Tech Admin (Tech Admin only)
        Route::middleware('role:tech_admin')->group(function () {
            Route::get('/create-tech-admin', [\App\Http\Controllers\Admin\CreateTechAdminController::class, 'create'])->name('create-tech-admin');
            Route::post('/create-tech-admin', [\App\Http\Controllers\Admin\CreateTechAdminController::class, 'store'])->name('create-tech-admin.store');
            
            // Generic Create Admin route (Tech Admin can create any admin type)
            Route::get('/create-admin', [\App\Http\Controllers\Admin\CreateAdminController::class, 'create'])->name('create-admin');
            Route::post('/create-admin', [\App\Http\Controllers\Admin\CreateAdminController::class, 'store'])->name('create-admin.store');
        });
        
        // Placeholder routes for features under development
        // These show "Coming Soon" pages until full web interfaces are implemented
        // Note: API endpoints exist for these features in routes/api.php
        
        // User Management (Normal Admin) - Web UI implemented via UserController
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class)
            ->only(['index', 'show', 'edit', 'update', 'destroy']);
        Route::post('/users/{user}/revoke-tokens', [\App\Http\Controllers\Admin\UserController::class, 'revokeTokens'])
            ->name('users.revoke-tokens');

        // Content Management (Tech Admin) - Full web interfaces
        Route::middleware('role:tech_admin')->group(function () {
            // Roadmaps
            Route::resource('roadmaps', \App\Http\Controllers\Admin\RoadmapWebController::class);
            Route::post('/roadmaps/{roadmap}/toggle-active', [\App\Http\Controllers\Admin\RoadmapWebController::class, 'toggleActive'])
                ->name('roadmaps.toggle-active');

            // Learning Units
            Route::resource('learning-units', \App\Http\Controllers\Admin\LearningUnitWebController::class);
            Route::post('/learning-units/reorder', [\App\Http\Controllers\Admin\LearningUnitWebController::class, 'reorder'])
                ->name('learning-units.reorder');
            Route::post('/learning-units/{unit}/toggle-active', [\App\Http\Controllers\Admin\LearningUnitWebController::class, 'toggleActive'])
                ->name('learning-units.toggle-active');

            // Lessons
            Route::resource('lessons', \App\Http\Controllers\Admin\LessonWebController::class);
            Route::post('/lessons/reorder', [\App\Http\Controllers\Admin\LessonWebController::class, 'reorder'])
                ->name('lessons.reorder');
            Route::post('/lessons/{lesson}/toggle-active', [\App\Http\Controllers\Admin\LessonWebController::class, 'toggleActive'])
                ->name('lessons.toggle-active');

            // Sub-Lessons
            Route::resource('sub-lessons', \App\Http\Controllers\Admin\SubLessonWebController::class);
            Route::post('/sub-lessons/reorder', [\App\Http\Controllers\Admin\SubLessonWebController::class, 'reorder'])
                ->name('sub-lessons.reorder');

            // Resources
            Route::resource('resources', \App\Http\Controllers\Admin\ResourceWebController::class);
            Route::get('/resources/search', [\App\Http\Controllers\Admin\ResourceWebController::class, 'search'])
                ->name('resources.search');

            // Quizzes
            Route::resource('quizzes', \App\Http\Controllers\Admin\QuizWebController::class);

            // Quiz Questions
            Route::resource('quiz-questions', \App\Http\Controllers\Admin\QuizQuestionWebController::class);

            // Challenges
            Route::resource('challenges', \App\Http\Controllers\Admin\ChallengeWebController::class);
            Route::post('/challenges/{challenge}/toggle-active', [\App\Http\Controllers\Admin\ChallengeWebController::class, 'toggleActive'])
                ->name('challenges.toggle-active');
        });
    });
