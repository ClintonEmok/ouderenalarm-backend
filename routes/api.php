<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



//
// 🔓 Public Routes
//
Route::post('/register', [\App\Http\Controllers\API\Auth\RegisterController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\API\Auth\AuthenticatedSessionController::class, 'loginToken']);
Route::get('/invites/validate', [\App\Http\Controllers\API\CaregiverController::class, 'validateToken']);

//
// 🔐 Authenticated Routes
//
Route::middleware('auth:sanctum')->group(function () {

    //
    // 🔒 Auth
    //
    Route::post('/logout', [\App\Http\Controllers\API\Auth\AuthenticatedSessionController::class, 'logoutToken']);

    //
    // 👤 User
    //
    Route::prefix('user')->controller(\App\Http\Controllers\API\UserController::class)->group(function () {
        Route::get('/', 'show');
        Route::put('/', 'update');
        Route::put('/password', 'updatePassword');
        Route::delete('/', 'destroy');
        Route::get('/caregivers', 'caregivers');
        Route::get('/patients', 'patients');
        Route::post('/caregivers/update', 'updateCaregiverPriorities');
    });

    //
    // 📱 Devices
    //
    Route::controller(\App\Http\Controllers\API\DeviceController::class)->group(function () {
        Route::get('/my-devices', 'index');
        Route::get('/my-devices/own', 'ownDevices');
        Route::get('/my-devices/caregiving', 'caregivingDevices');
        Route::post('/devices/assign', 'assign');
        Route::get('/devices/{id}', 'show');
        Route::delete('/devices/{id}', 'unassign');
    });

    //
    // 🚨 Alarms
    //
    Route::prefix('device-alarms')->controller(\App\Http\Controllers\API\DeviceAlarmController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{id}', 'show');
    });

    //
    // 🧑‍⚕️ Caregivers
    //
    Route::controller(\App\Http\Controllers\API\CaregiverController::class)->group(function () {
        Route::post('/caregivers/invite', 'invite');
        Route::post('/caregivers/accept', 'accept');
        Route::post('/caregivers/remove', 'remove');
        Route::get('/caregivers/invites/pending', 'pending');
    });
});