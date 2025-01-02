<?php

namespace App\Http\Controllers;

use App\Http\Resources\DeviceResource;
use App\Models\Device;
use App\Models\User;
use App\Models\CaregiverPatient;
use Illuminate\Http\Request;

class CaregiverPatientController extends Controller
{
    public function index(Request $request)
    {
        // Get caregivers for the authenticated user
        $caregivers = $request->user()->caregivers;

        return response()->json($caregivers);
    }

    public function store(Request $request)
    {
        $request->validate([
            'caregiver_id' => 'required|exists:users,id',
        ]);

        $caregiverPatient = CaregiverPatient::create([
            'caregiver_id' => $request->caregiver_id,
            'patient_id' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Caregiver added successfully',
            'data' => $caregiverPatient,
        ]);
    }

    public function getPatientDevices(Request $request)
    {
        $caregiver = $request->user(); // Authenticated caregiver

        // Fetch devices for all associated patients
        $devices = Device::whereIn('user_id', $caregiver->patients->pluck('id'))->get();

        return DeviceResource::collection($devices);
    }

    public function remove($id)
    {
        $caregiverPatient = CaregiverPatient::where('caregiver_id', $id)
            ->where('patient_id', auth()->id())
            ->first();

        if (!$caregiverPatient) {
            return response()->json(['message' => 'Caregiver not found'], 404);
        }

        $caregiverPatient->delete();

        return response()->json(['message' => 'Caregiver removed successfully']);
    }
}
