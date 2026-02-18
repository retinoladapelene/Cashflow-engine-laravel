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
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
