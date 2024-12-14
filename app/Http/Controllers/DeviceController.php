<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Http\Resources\DeviceResource;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    /**
     * Display a listing of the devices.
     *
     * @group Devices
     *
     * **List All Devices**
     *
     * This endpoint retrieves a list of all devices.
     *
     * @response 200 [
     *   {
     *     "id": 1,
     *     "user_id": 2,
     *     "alarm_code": "12345",
     *     "longitude": 12.34,
     *     "latitude": 56.78,
     *     "maps_link": "https://maps.google.com/?q=56.78,12.34",
     *     "phone_number": "+1234567890",
     *     "battery_percentage": 90
     *   }
     * ]
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $devices = Device::all();
        return DeviceResource::collection($devices);
    }

    /**
     * Store a newly created device in storage.
     *
     * @group Devices
     *
     * **Create Device**
     *
     * This endpoint creates a new device.
     *
     * @bodyParam user_id integer required The ID of the user associated with the device. Example: 1
     * @bodyParam alarm_code string optional The alarm code for the device. Example: 1234
     * @bodyParam longitude numeric optional The longitude of the device location. Example: 12.345
     * @bodyParam latitude numeric optional The latitude of the device location. Example: 54.321
     * @bodyParam maps_link string optional A Google Maps link for the device location. Example: https://maps.google.com/?q=12.345,54.321
     * @bodyParam phone_number string required The phone number associated with the device. Example: +1234567890
     * @bodyParam battery_percentage integer optional The battery percentage of the device. Example: 85
     *
     * @response 201 {
     *   "id": 1,
     *   "user_id": 2,
     *   "alarm_code": "12345",
     *   "longitude": 12.34,
     *   "latitude": 56.78,
     *   "maps_link": "https://maps.google.com/?q=56.78,12.34",
     *   "phone_number": "+1234567890",
     *   "battery_percentage": 90
     * }
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\DeviceResource
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
     *
     * @group Devices
     *
     * **Get Device**
     *
     * This endpoint retrieves a specific device by ID.
     *
     * @urlParam id integer required The ID of the device. Example: 1
     *
     * @response 200 {
     *   "id": 1,
     *   "user_id": 2,
     *   "alarm_code": "12345",
     *   "longitude": 12.34,
     *   "latitude": 56.78,
     *   "maps_link": "https://maps.google.com/?q=56.78,12.34",
     *   "phone_number": "+1234567890",
     *   "battery_percentage": 90
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "message": "Device not found"
     * }
     *
     * @param int $id
     * @return \App\Http\Resources\DeviceResource|\Illuminate\Http\JsonResponse
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
     *
     * @group Devices
     *
     * **Update Device**
     *
     * This endpoint updates a device.
     *
     * @urlParam id integer required The ID of the device to be updated. Example: 1
     *
     * @response 200 {
     *   "id": 1,
     *   "user_id": 2,
     *   "alarm_code": "12345",
     *   "longitude": 12.34,
     *   "latitude": 56.78,
     *   "maps_link": "https://maps.google.com/?q=56.78,12.34",
     *   "phone_number": "+1234567890",
     *   "battery_percentage": 90
     * }
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \App\Http\Resources\DeviceResource|\Illuminate\Http\JsonResponse
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
     *
     * @group Devices
     *
     * **Delete Device**
     *
     * This endpoint deletes a device.
     *
     * @urlParam id integer required The ID of the device to be deleted. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Device deleted successfully"
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "message": "Device not found"
     * }
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
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
