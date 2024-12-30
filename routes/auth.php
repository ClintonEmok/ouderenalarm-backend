<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware('guest')
    ->name('register');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware('guest')
    ->name('password.email');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('password.store');

Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['auth', 'signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

// 🛠️ SPA Login (Session-based)
Route::post('/login', [AuthenticatedSessionController::class, 'loginSession'])
    ->middleware(['guest:web']) // Ensure the user is not already logged in
    ->name('login.session');

// 🛠️ API Login (Token-based)
Route::post('/api/login', [AuthenticatedSessionController::class, 'loginToken'])
    ->middleware(['guest:sanctum']) // Ensure the token is for Sanctum
    ->name('login.token');

// 🛠️ SPA Logout (Session-based)
Route::post('/logout', [AuthenticatedSessionController::class, 'logoutSession'])
    ->middleware(['auth:web']) // Only authenticated session users can logout
    ->name('logout.session');

// 🛠️ API Logout (Token-based)
Route::post('/api/logout', [AuthenticatedSessionController::class, 'logoutToken'])
    ->middleware(['auth:sanctum']) // Only authenticated token users can logout
    ->name('logout.token');
