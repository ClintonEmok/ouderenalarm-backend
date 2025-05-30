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
     * Returns alarms that are either fall or SOS alerts, including basic device and user info,
     * and a list of caregivers currently en route.
     *
     * @response {
     *   "data": [
     *     {
     *       "id": 42,
     *       "created_at": "2025-05-30 12:34:56",
     *       "triggered_alerts": "Valalarm, Noodoproep",
     *       "device": {
     *         "imei": "123456789012345",
     *         "phone_number": "+31612345678",
     *         "connection_number": "CN001",
     *         "user": {
     *           "name": "Jan Jansen"
     *         }
     *       },
     *       "caregivers_en_route": "Piet Pietersen, Klaas Klaassen"
     *     }
     *   ],
     *   "links": {
     *     "first": "http://example.com/api/device-alarms?page=1",
     *     "last": "http://example.com/api/device-alarms?page=1",
     *     "prev": null,
     *     "next": null
     *   },
     *   "meta": {
     *     "current_page": 1,
     *     "from": 1,
     *     "last_page": 1,
     *     "path": "http://example.com/api/device-alarms",
     *     "per_page": 15,
     *     "to": 1,
     *     "total": 1
     *   }
     * }
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
     * Returns the full details of a specific alarm, including triggered alerts,
     * device and user information, and the list of caregivers en route.
     *
     * @urlParam id int required The ID of the alarm.
     *
     * @response {
     *   "id": 42,
     *   "created_at": "2025-05-30 12:34:56",
     *   "triggered_alerts": "Valalarm, Noodoproep",
     *   "device": {
     *     "imei": "123456789012345",
     *     "phone_number": "+31612345678",
     *     "connection_number": "CN001",
     *     "user": {
     *       "name": "Jan Jansen"
     *     }
     *   },
     *   "caregivers_en_route": "Piet Pietersen, Klaas Klaassen"
     * }
     *
     * @authenticated
     */
    public function show(Request $request, $id)
    {
        $alarm = DeviceAlarm::with('device.user')->findOrFail($id);

        return new DeviceAlarmResource($alarm);
    }
}