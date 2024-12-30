<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\DeviceController;
use \App\Http\Controllers\UserController;



Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('devices', DeviceController::class);
    Route::get('/user', [UserController::class, 'show']); // Get user info
    Route::put('/user', [UserController::class, 'update']); // Update user info
    Route::put('/user/password', [UserController::class, 'updatePassword']); // Update user password
    Route::delete('/user', [UserController::class, 'destroy']); // Delete user account
});




