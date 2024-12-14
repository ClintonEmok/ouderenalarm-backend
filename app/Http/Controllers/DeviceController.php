<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Http\Resources\DeviceResource;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    /**
     * Display a listing of the devices.
     */
    public function index()
    {
        $devices = Device::all();
        return DeviceResource::collection($devices);
    }

    /**
     * Store a newly created device in storage.
     */
    public function store(Request $request)
    {


        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'alarm_code' => 'nullable|string|max:20',
            'longitude' => 'nullable|numeric|between:-180,180',
            'latitude' => 'nullable|numeric|between:-90,90',
            'maps_link' => 'nullable|string|max:2083',
            'phone_number' => 'required|string|max:15',
            'battery_percentage' => 'nullable|integer|between:0,100',
        ]);

        $device = Device::create($validatedData);

        return new DeviceResource($device);
    }

    /**
     * Display the specified device.
     */
    public function show($id)
    {
        $device = Device::find($id);

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found'
            ], 404);
        }

        return new DeviceResource($device);
    }

    /**
     * Update the specified device in storage.
     */
    public function update(Request $request, $id)
    {
        $device = Device::find($id);

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found'
            ], 404);
        }

        $validatedData = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'alarm_code' => 'nullable|string|max:20',
            'longitude' => 'sometimes|nullable|numeric|between:-180,180',
            'latitude' => 'sometimes|nullable|numeric|between:-90,90',
            'maps_link' => 'nullable|string|max:2083',
            'phone_number' => 'sometimes|nullable|string|max:15',
            'battery_percentage' => 'nullable|integer|between:0,100',
        ]);

        $device->update($validatedData);

        return new DeviceResource($device);
    }

    /**
     * Remove the specified device from storage.
     */
    public function destroy($id)
    {
        $device = Device::find($id);

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found'
            ], 404);
        }

        $device->delete();

        return response()->json([
            'success' => true,
            'message' => 'Device deleted successfully'
        ]);
    }
}
