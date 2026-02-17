<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\AdArsenalController;
use App\Http\Controllers\RoadmapController;
use App\Http\Controllers\AdminController;
use App\Http\Middleware\EnsureUserIsAdmin;

// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/arsenal', [AdArsenalController::class, 'index']); // Public access to ads

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth & User
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Settings API
    Route::get('/settings/activity-logs', [App\Http\Controllers\SettingsController::class, 'activityLogs']);
    Route::post('/settings/profile', [App\Http\Controllers\SettingsController::class, 'updateProfile']);
    Route::post('/settings/password', [App\Http\Controllers\SettingsController::class, 'updatePassword']);


    // Business Profile
    Route::get('/business', [BusinessController::class, 'index']);
    Route::post('/business', [BusinessController::class, 'update']);

    // Roadmap
    Route::get('/roadmap', [RoadmapController::class, 'index']);
    Route::post('/roadmap/update', [RoadmapController::class, 'update']);

    // Admin Routes
    Route::middleware(EnsureUserIsAdmin::class)->prefix('admin')->group(function () {
        Route::get('/users', [AdminController::class, 'users']);
        Route::get('/stats', [AdminController::class, 'stats']);
        Route::get('/charts', [AdminController::class, 'charts']);
        Route::post('/users/{user}/ban', [AdminController::class, 'ban']);
        Route::post('/users/{user}/unban', [AdminController::class, 'unban']);
        Route::post('/users/{user}/promote', [AdminController::class, 'promote']);
        
        // Ad Arsenal Management
        Route::post('/arsenal', [AdArsenalController::class, 'store']);
        Route::put('/arsenal/{adArsenal}', [AdArsenalController::class, 'update']);
        Route::delete('/arsenal/{adArsenal}', [AdArsenalController::class, 'destroy']);
    });
});
