<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\DeviceResource;
use App\Models\Device;
use Illuminate\Http\Request;

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

        $own = $user->devices()
            ->with(['latestLocation', 'latestStatus', 'user'])
            ->get();

        $caregiving = $user->caregivingPatients()
            ->with(['devices.latestLocation', 'devices.latestStatus', 'devices.user'])
            ->get()
            ->pluck('devices')
            ->flatten()
            ->unique('id')
            ->reject(fn ($d) => $own->contains('id', $d->id))
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

        $devices = $user->caregivingPatients()
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
            || $user->caregivingPatients()->where('id', $device->user_id)->exists();

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
}