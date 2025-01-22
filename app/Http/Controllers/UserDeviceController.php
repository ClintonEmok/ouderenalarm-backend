<?php

namespace App\Http\Controllers;

use App\Http\Resources\DeviceResource;
use Illuminate\Http\Request;
use App\Models\Device;
use Illuminate\Support\Facades\Auth;

class UserDeviceController extends Controller
{
    /**
     * Attach a device to the authenticated user using a phone number.
     */
//    TODO: Change phone number to device id
    public function addDevice(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|max:15',
            'nickname' => 'nullable|string|max:50',
        ]);

        // Find the device by phone number
        $device = Device::where('phone_number', $request->phone_number)->first();

        if (!$device) {
            return response()->json(['error' => 'Device not found'], 404);
        }

        if ($device->user_id) {
            return response()->json(['error' => 'Device is already assigned to another user'], 400);
        }

        // Attach the device to the authenticated user
        $user = Auth::user();
        $device->update(['user_id' => $user->id, 'nickname' => $request->nickname]);

        return response()->json(['message' => 'Device added successfully', 'device' => $device], 201);
    }

    /**
     * List all devices attached to the authenticated user.
     */
    public function listDevices()
    {
        $user = Auth::user();
        return DeviceResource::collection($user->devices);
    }

    /**
     * Detach a device from the user (remove it).
     */
    public function removeDevice($deviceId)
    {
        $user = Auth::user();
        $device = Device::where('id', $deviceId)->where('user_id', $user->id)->first();

        if (!$device) {
            return response()->json(['error' => 'Device not found or not attached to you'], 404);
        }

        $device->update(['user_id' => null, 'nickname' => null]);

        return response()->json(['message' => 'Device removed successfully']);
    }
}
