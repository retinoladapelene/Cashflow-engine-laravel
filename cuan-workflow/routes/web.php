<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    $settings = \App\Models\SystemSetting::all()->pluck('value', 'key');
    return view('index', ['settings' => $settings]);
})->name('index');

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/reset-password/{token}', function ($token) {
    return view('reset-password', ['token' => $token]);
})->name('password.reset');

Route::get('/admin', function () {
    return view('admin');
})->name('admin');

Route::get('/settings', [SettingsController::class, 'index'])->name('settings');

Route::middleware('auth:sanctum')->group(function () {
    // API routes migrated to api.php
});

Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']); // Fixed namespace

Route::middleware(['auth'])->group(function () {
    Route::post('/mentor/roadmap/generate', [App\Http\Controllers\MentorController::class, 'generateRoadmap']);
    Route::get('/mentor/roadmap', [App\Http\Controllers\MentorController::class, 'getRoadmap']);
    Route::post('/mentor/roadmap/action/{id}/toggle', [App\Http\Controllers\MentorController::class, 'toggleAction']);
});

// Mentor Lab Public/Session Routes (Allow Guest but start Session)
Route::get('/mentor/preset', [App\Http\Controllers\MentorController::class, 'plannerPreset']);
Route::post('/mentor/calculate', [App\Http\Controllers\MentorController::class, 'calculate']);
Route::post('/mentor/simulate', [App\Http\Controllers\MentorController::class, 'simulate']);
Route::post('/mentor/upsell', [App\Http\Controllers\MentorController::class, 'upsell']);
Route::get('/mentor/feasibility', [App\Http\Controllers\MentorController::class, 'checkFeasibility']);
