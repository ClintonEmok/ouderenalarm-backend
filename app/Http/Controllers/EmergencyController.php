<?php

namespace App\Http\Controllers;

use App\Http\Resources\EmergencyResource;
use App\Models\EmergencyLink;

class EmergencyController extends Controller
{
    /**
     * Display emergency details.
     */
    public function show($id)
    {
        $emergencyLink = EmergencyLink::with('deviceAlarm.device.user.caregivers')
            ->where('link', 'like', "%/{$id}")
            ->first();

        if (!$emergencyLink || $emergencyLink->isExpired()) {
            return response()->json(['error' => 'This link is expired or invalid.'], 404);
        }

        return new EmergencyResource($emergencyLink);
    }
}
