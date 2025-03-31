<?php

namespace App\Http\Controllers;

use App\Http\Resources\EmergencyResource;
use App\Models\EmergencyLink;
use App\Models\User;
use Illuminate\Http\Request;

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
    /**
     * Add a caregiver to the emergency using the unique code.
     */
    public function addCaregiverOnTheWay(Request $request, $code)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id', // Validate caregiver user
        ]);

        // Find emergency using the unique code
        $emergencyLink = EmergencyLink::where('link', 'like', "%/{$code}")->first();

        if (!$emergencyLink || $emergencyLink->isExpired()) {
            return response()->json(['error' => 'This emergency is expired or invalid.'], 404);
        }

        $user = User::findOrFail($request->user_id);

        // Attach caregiver to emergency link if not already added
        if (!$emergencyLink->caregiversOnTheWay()->where('user_id', $user->id)->exists()) {
            $emergencyLink->caregiversOnTheWay()->attach($user->id);
        }

        return response()->json([
            'message' => "{$user->name} is on the way.",
            'caregivers_on_the_way' => $emergencyLink->caregiversOnTheWay()->get(),
        ]);
    }

    /**
     * Remove a caregiver from the emergency using the unique code.
     */
    public function removeCaregiverOnTheWay(Request $request, $code)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Find emergency using the unique code
        $emergencyLink = EmergencyLink::where('link', 'like', "%/{$code}")->first();

        if (!$emergencyLink || $emergencyLink->isExpired()) {
            return response()->json(['error' => 'This emergency is expired or invalid.'], 404);
        }

        $user = User::findOrFail($request->user_id);

        // Detach the caregiver
        $emergencyLink->caregiversOnTheWay()->detach($user->id);

        return response()->json([
            'message' => "{$user->name} is no longer responding.",
            'caregivers_on_the_way' => $emergencyLink->caregiversOnTheWay()->get(),
        ]);
    }
}
