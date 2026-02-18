<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AuthController;



use App\Http\Controllers\BusinessController;

Route::get('/', [BusinessController::class, 'index'])->name('index');

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


Route::middleware(['auth:sanctum'])->group(function () {
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
Route::get('/mentor/simulation/latest', [App\Http\Controllers\MentorController::class, 'getLatestSimulation']);

// Reverse Goal Planner V2
Route::post('/reverse-planner/calculate', [App\Http\Controllers\ReverseGoalPlannerController::class, 'calculate']);

// Profit Simulator (v2.0)
Route::post('/profit-simulator/simulate', [App\Http\Controllers\ProfitSimulatorController::class, 'simulate']);
Route::post('/profit-simulator/store', [App\Http\Controllers\ProfitSimulatorController::class, 'store']);

// Context-Aware Learning Engine
Route::match(['get', 'post'], '/api/context-evaluate', [App\Http\Controllers\EducationController::class, 'evaluateContext']);
Route::get('/api/education/{termKey}', [App\Http\Controllers\EducationController::class, 'getTerm']);
Route::post('/api/behavior-log', [App\Http\Controllers\EducationController::class, 'logBehavior']);
