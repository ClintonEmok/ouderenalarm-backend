<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\DeviceController;
use \App\Http\Controllers\UserController;
use \App\Http\Controllers\CaregiverController;
use \App\Http\Controllers\CaregiverPatientController;



Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('devices', DeviceController::class);
    Route::get('/user', [UserController::class, 'show']); // Get user info
    Route::put('/user', [UserController::class, 'update']); // Update user info
    Route::put('/user/password', [UserController::class, 'updatePassword']); // Update user password
    Route::delete('/user', [UserController::class, 'destroy']); // Delete user account
    Route::post('/caregivers/invite', [CaregiverController::class, 'invite'])->name('caregiver.invite');
    Route::post('/caregivers/accept', [CaregiverController::class, 'accept'])->name('caregiver.accept');
    // Route to list caregivers for a user
    Route::get('/caregivers', [CaregiverController::class, 'index'])->name('caregiver.index');

    // Optional: Route to remove a caregiver relationship
    Route::delete('/caregivers/{caregiverId}', [CaregiverPatientController::class, 'remove'])->name('caregiver.remove');
    Route::get('/caregivers/patient-devices', [CaregiverPatientController::class, 'getPatientDevices']);
});




