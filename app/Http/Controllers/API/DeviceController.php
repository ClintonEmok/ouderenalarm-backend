<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\DeviceResource;
use App\Models\Device;
use App\Models\DeviceAccessRequest;
use App\Notifications\NewDeviceAccessRequest;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @group Devices
 *
 * Endpoints for managing and viewing devices.
 */
class DeviceController extends Controller
{
    /**
     * Get all devices accessible to the authenticated user
     *
     * Returns devices the user owns (`own`) and devices they access through caregiving relationships (`caregiving`).
     *
     * @authenticated
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Devices owned by the user
        $own = $user->devices()
            ->with(['latestLocation', 'latestStatus', 'user'])
            ->get();

        // Devices of the user's patients (caregiving role)
        $caregiving = $user->patients()
            ->with(['devices.latestLocation', 'devices.latestStatus', 'devices.user'])
            ->get()
            ->pluck('devices')
            ->flatten()
            ->unique('id')
            ->reject(fn ($device) => $own->contains('id', $device->id))
            ->values();

        return response()->json([
            'own' => DeviceResource::collection($own),
            'caregiving' => DeviceResource::collection($caregiving),
        ]);
    }

    /**
     * Get devices owned by the authenticated user
     *
     * @authenticated
     */
    public function ownDevices(Request $request)
    {
        $devices = $request->user()->devices()
            ->with(['latestLocation', 'latestStatus', 'user'])
            ->get();

        return DeviceResource::collection($devices);
    }

    /**
     * Get devices accessible through caregiving relationships
     *
     * @authenticated
     */
    public function caregivingDevices(Request $request)
    {
        $user = $request->user();
        $ownIds = $user->devices()->pluck('id');

        $devices = $user->patients()
            ->with(['devices.latestLocation', 'devices.latestStatus', 'devices.user'])
            ->get()
            ->pluck('devices')
            ->flatten()
            ->unique('id')
            ->reject(fn ($device) => $ownIds->contains($device->id))
            ->values();

        return DeviceResource::collection($devices);
    }

    /**
     * Assign a device to the authenticated user using its phone number
     *
     * @bodyParam phone_number string required The phone number of the device. Example: +31612345678
     * @bodyParam nickname string optional A nickname for the device. Example: Grandma's Alarm
     *
     * @authenticated
     */
    public function assign(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => ['required', 'string'],
            'nickname' => ['nullable', 'string', 'max:255'],
        ]);

        $device = Device::where('phone_number', $validated['phone_number'])->first();

        if (! $device) {
            return response()->json(['message' => 'Device not found.'], 404);
        }

        if ($device->user_id) {
            return response()->json(['message' => 'Device is already assigned.'], 409);
        }

        $device->user_id = $request->user()->id;

        if (!empty($validated['nickname'])) {
            $device->nickname = $validated['nickname'];
        }

        $device->save();

        return response()->json([
            'message' => 'Device successfully assigned.',
            'device' => new DeviceResource($device->load(['latestLocation', 'latestStatus', 'user'])),
        ]);
    }

    /**
     * Get a specific device by ID, if user has access
     *
     * @urlParam id int required The ID of the device.
     *
     * @authenticated
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $device = Device::with(['latestLocation', 'latestStatus', 'user'])->findOrFail($id);

        $hasAccess = $device->user_id === $user->id
            || $user->patients()->where('id', $device->user_id)->exists();

        if (! $hasAccess) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return new DeviceResource($device);
    }

    /**
     * Unassign a device from the authenticated user
     *
     * This removes the ownership link between the user and the device.
     * Only the device owner can perform this action.
     *
     * @urlParam id int required The ID of the device to unassign.
     *
     * @authenticated
     */
    public function unassign(Request $request, $id)
    {
        $user = $request->user();
        $device = Device::findOrFail($id);

        if ($device->user_id !== $user->id) {
            return response()->json(['message' => 'You do not own this device.'], 403);
        }

        $device->user_id = null;
        $device->nickname = null;
        $device->save();

        return response()->json(['message' => 'Device unassigned successfully.']);
    }

    /**
     * Request access to an existing device
     *
     * Allows a user to request access to a device based on its phone number.
     * This request is submitted to the system administrator for review.
     *
     * @bodyParam phone_number string required The phone number of the device. Example: +31612345678
     * @bodyParam message string optional A custom message to explain the reason for the access request. Example: Ik wil toegang omdat ik voor deze persoon zorg.
     *
     * @response 201 {
     *  "message": "Access request submitted successfully.",
     *  "device_found": true,
     *  "device_id": 42,
     *  "access_request": {
     *    "id": 101,
     *    "phone_number": "+31612345678",
     *    "message": "Ik wil toegang omdat ik voor deze persoon zorg.",
     *    "created_at": "2025-10-06T11:23:45.000000Z"
     *  }
     * }
     *
     * @response 500 {
     *  "message": "Failed to submit device access request.",
     *  "error": "An unexpected error occurred."
     * }
     *
     * @authenticated
     */
    public function requestAccess(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => ['required', 'string'],
            'message' => ['nullable', 'string'],
        ]);

        try {
            $user = $request->user();

            // Optional: check if the device exists
            $device = Device::where('phone_number', $validated['phone_number'])->first();

            // Create the access request
            $accessRequest = DeviceAccessRequest::create([
                'user_id' => $user->id,
                'phone_number' => $validated['phone_number'],
                'message' => $validated['message'] ?? null,
            ]);

            // Ensure user is loaded for notification
            $accessRequest->load('user');

            // Send notification to admin (or role-based users)
            Notification::route('mail', 'clintonneemok11@gmail.com')
                ->notify(new NewDeviceAccessRequest($accessRequest));

            return response()->json([
                'message' => 'Access request submitted successfully.',
                'device_found' => (bool) $device,
                'device_id' => optional($device)->id,
                'access_request' => [
                    'id' => $accessRequest->id,
                    'phone_number' => $accessRequest->phone_number,
                    'message' => $accessRequest->message,
                    'created_at' => $accessRequest->created_at,
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to submit device access request', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to submit device access request.',
                'error' => app()->isLocal() ? $e->getMessage() : 'An unexpected error occurred.',
            ], 500);
        }
    }
}