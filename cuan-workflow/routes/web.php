<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
})->name('index');

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/reset-password', function () {
    return view('reset-password');
})->name('reset-password');

Route::get('/admin', function () {
    return view('admin');
})->name('admin');

// Google Auth Routes
use App\Http\Controllers\AuthController;
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
