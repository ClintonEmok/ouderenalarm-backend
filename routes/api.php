<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Jobs\SendTestPushNotificationJob;



//
// 🔓 Public Routes
//
Route::post('/register', [\App\Http\Controllers\API\Auth\RegisterController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\API\Auth\AuthSessionController::class, 'loginToken']);
Route::get('/invites/validate', [\App\Http\Controllers\API\CaregiverController::class, 'validateToken']);

//
// 🔐 Authenticated Routes
//
Route::middleware('auth:sanctum')->group(function () {

    //
    // 🔒 Auth
    //
    Route::post('/logout', [\App\Http\Controllers\API\Auth\AuthSessionController::class, 'logoutToken']);

    //
    // 👤 User
    //
    Route::prefix('user')->controller(\App\Http\Controllers\API\UserController::class)->middleware('auth:sanctum')->group(function () {
        // Profile
        Route::get('/', 'show');
        Route::put('/', 'update');
        Route::put('/password', 'updatePassword');
        Route::delete('/', 'destroy');

        // Caregivers & Patients
        Route::get('/caregivers', 'caregivers');
        Route::get('/patients', 'patients');
        Route::post('/caregivers/update', 'updateCaregiverPriorities');

        // Notes
        Route::get('/notes', 'userNotes');
        Route::post('/notes', 'storeUserNote');
        Route::put('/notes/{note}', 'updateUserNote');
        Route::delete('/notes/{note}', 'deleteUserNote');
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

    Route::post('/push-tokens', [\App\Http\Controllers\API\PushTokenController::class, 'store']);
    Route::delete('/push-tokens', [\App\Http\Controllers\API\PushTokenController::class, 'destroy']);
    Route::post('/test-push', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'token' => 'required|string',
        ]);

        SendTestPushNotificationJob::dispatch($request->token);

        return response()->json(['status' => 'queued']);
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
        Route::patch('/caregivers/reorder', [\App\Http\Controllers\API\CaregiverController::class, 'reorderCaregivers']);
    });
});