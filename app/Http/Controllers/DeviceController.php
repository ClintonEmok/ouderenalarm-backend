<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Http\Resources\DeviceResource;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    /**
     * Display a listing of the user's devices.
     *
     * @group Devices
     * @authenticated
     *
     * **List All Devices for Authenticated User**
     *
     * This endpoint retrieves a list of all devices that belong to the authenticated user.
     *
     * @response 200
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $devices = $request->user()->devices()->get();
        return DeviceResource::collection($devices);
    }

    /**
     * Store a newly created device for the authenticated user.
     *
     * @group Devices
     * @authenticated
     *
     * **Create Device**
     *
     * This endpoint creates a new device for the authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\DeviceResource
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'alarm_code' => 'nullable|string|max:20',
            'longitude' => 'nullable|numeric|between:-180,180',
            'latitude' => 'nullable|numeric|between:-90,90',
            'maps_link' => 'nullable|string|max:2083',
            'phone_number' => 'required|string|max:15',
            'battery_percentage' => 'nullable|integer|between:0,100',
        ]);

        // Ensure the device belongs to the authenticated user
        $validatedData['user_id'] = $request->user()->id;

        $device = Device::create($validatedData);

        return new DeviceResource($device);
    }

    /**
     * Display the specified device for the authenticated user.
     *
     * @group Devices
     * @authenticated
     *
     * **Get Device**
     *
     * This endpoint retrieves a specific device by ID, but only if it belongs to the authenticated user.
     *
     * @param int $id
     * @return \App\Http\Resources\DeviceResource|\Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $device = $request->user()->devices()->find($id);

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found'
            ], 404);
        }

        return new DeviceResource($device);
    }

    /**
     * Update the specified device for the authenticated user.
     *
     * @group Devices
     * @authenticated
     *
     * **Update Device**
     *
     * This endpoint updates a device for the authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \App\Http\Resources\DeviceResource|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $device = $request->user()->devices()->find($id);

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found'
            ], 404);
        }

        $validatedData = $request->validate([
            'alarm_code' => 'nullable|string|max:20',
            'longitude' => 'nullable|numeric|between:-180,180',
            'latitude' => 'nullable|numeric|between:-90,90',
            'maps_link' => 'nullable|string|max:2083',
            'phone_number' => 'nullable|string|max:15',
            'battery_percentage' => 'nullable|integer|between:0,100',
        ]);

        $device->update($validatedData);

        return new DeviceResource($device);
    }

    /**
     * Remove the specified device for the authenticated user.
     *
     * @group Devices
     * @authenticated
     *
     * **Delete Device**
     *
     * This endpoint deletes a device, but only if it belongs to the authenticated user.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $device = $request->user()->devices()->find($id);

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
