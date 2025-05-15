<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\DeviceAlarmResource;
use App\Models\DeviceAlarm;
use Illuminate\Http\Request;

/**
 * @group Device Alarms
 *
 * Retrieve relevant alarms triggered by devices.
 */
class DeviceAlarmController extends Controller
{
    /**
     * List relevant device alarms
     *
     * Only returns alarms that are fall or SOS alerts.
     *
     * @authenticated
     */
    public function index(Request $request)
    {
        $alarms = DeviceAlarm::with('device.user')
            ->where(function ($query) {
                $query->where('fall_down_alert', true)
                    ->orWhere('sos_alert', true);
            })
            ->orderByDesc('created_at')
            ->paginate(15);

        return DeviceAlarmResource::collection($alarms);
    }

    /**
     * Show details for a specific device alarm
     *
     * @urlParam id int required The ID of the alarm.
     *
     * @authenticated
     */
    public function show(Request $request, $id)
    {
        $alarm = DeviceAlarm::with('device.user')->findOrFail($id);

        return new DeviceAlarmResource($alarm);
    }
}