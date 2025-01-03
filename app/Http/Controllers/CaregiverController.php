<?php

namespace App\Http\Controllers;

use App\Http\Resources\CaregiverResource;
use App\Mail\CaregiverInvitationMailable;
use App\Models\User;
use App\Models\CaregiverInvitation;
use App\Models\CaregiverPatient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CaregiverController extends Controller
{
    /**
     * Invite a caregiver by email.
     */
    public function invite(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $patient = $request->user(); // Authenticated user as the patient

        // Check if the caregiver is already linked
        $existingCaregiver = User::where('email', $request->email)->first();

        if ($existingCaregiver && CaregiverPatient::where('caregiver_id', $existingCaregiver->id)
                ->where('patient_id', $patient->id)->exists()) {
            return response()->json(['message' => 'This user is already a caregiver.'], 400);
        }

        // Check if an invitation is already sent
        if (CaregiverInvitation::where('email', $request->email)
            ->where('patient_id', $patient->id)->exists()) {
            return response()->json(['message' => 'This user has already been invited.'], 400);
        }

        // Create a new invitation
        $token = Str::random(60);
        CaregiverInvitation::create([
            'email' => $request->email,
            'token' => $token,
            'patient_id' => $patient->id,
        ]);

        // Send the invitation email
        Mail::to($request->email)->send(new CaregiverInvitationMailable($token, $patient->name, $request->email));

        return response()->json(['message' => 'Invitation sent successfully'], 200);
    }

    /**
     * Accept an invitation to become a caregiver.
     */
    public function accept(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'name' => 'required',
            'password' => 'nullable|min:8|confirmed', // Password is only required for new accounts
        ]);

        // Validate the invitation
        $invitation = CaregiverInvitation::where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$invitation) {
            return response()->json(['message' => 'Invalid or expired token'], 400);
        }

        // Check if a user exists with the provided email
        $caregiver = User::firstWhere('email', $request->email);

        // If no user exists, handle the creation flow
        if (!$caregiver) {
            if (empty($request->password)) {
                return response()->json(['message' => 'Password is required for new accounts.'], 400);
            }

            // Create a new caregiver account (we need to add name)
            $caregiver = User::create([
                'email' => $request->email,
                'name' => $request->name,
                'password' => Hash::make($request->password),
            ]);
        } elseif ($request->user()) {
            // If authenticated, check if the logged-in user matches the invitation
            if ($request->user()->id !== $caregiver->id) {
                return response()->json(['message' => 'Unauthorized to accept this invitation.'], 403);
            }
        }

        // Check if the caregiver is already linked to the patient
        $existingLink = CaregiverPatient::where([
            'caregiver_id' => $caregiver->id,
            'patient_id' => $invitation->patient_id,
        ])->exists();

        if ($existingLink) {
            return response()->json(['message' => 'Caregiver is already linked to this patient.'], 400);
        }

        // Link the caregiver to the patient
        CaregiverPatient::create([
            'caregiver_id' => $caregiver->id,
            'patient_id' => $invitation->patient_id,
        ]);

        // Delete the invitation
        $invitation->delete();

        return response()->json(['message' => 'Caregiver registered and linked successfully.'], 200);
    }

    /**
     * List all caregivers for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Load caregivers and their associated patients
        $caregivers = CaregiverPatient::with('caregiver', 'patient')
            ->where('patient_id', $user->id)
            ->get();

        return CaregiverResource::collection($caregivers);
    }
}
