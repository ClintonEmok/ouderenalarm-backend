<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\InviteResource;
use App\Mail\InviteCaregiverMail;
use App\Models\Invite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * @group Caregivers
 *
 * Manage caregiver-patient relationships.
 */
class CaregiverController extends Controller
{
    /**
     * Invite a user to be your caregiver by email
     *
     * @bodyParam email string required The email of the invited caregiver. Example: test@example.com
     *
     * @authenticated
     */
    public function invite(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $inviter = $request->user();
        $email = $request->input('email');
        $existingUser = User::where('email', $email)->first();
        $token = Str::uuid();

        if ($existingUser && $inviter->caregivers()->where('caregiver_id', $existingUser->id)->exists()) {
            return response()->json(['message' => 'User is already your caregiver.'], 409);
        }

        if (Invite::where('inviter_id', $inviter->id)->where('email', $email)->where('status', 'pending')->exists()) {
            return response()->json(['message' => 'Invite already sent.'], 409);
        }

        $invite = Invite::create([
            'inviter_id' => $inviter->id,
            'invited_user_id' => $existingUser?->id,
            'email' => $email,
            'token' => $token,
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($email)->send(new InviteCaregiverMail($invite, isNewUser: !$existingUser));

        return response()->json(['message' => 'Caregiver invite sent.']);
    }

    /**
     * Accept a caregiver invitation and register if needed
     *
     * @bodyParam token string required The invitation token. Example: abc-123
     * @bodyParam name string required Your name. Example: Jane Doe
     * @bodyParam password string required Your password. Example: secret123
     * @bodyParam password_confirmation string required Confirm password. Example: secret123
     *
     * @authenticated (optional, required if already logged in)
     */
    public function accept(Request $request)
    {
        $request->validate([
            'token' => 'required|string|exists:invites,token',
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $invite = Invite::where('token', $request->token)
            ->where('status', 'pending')
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->firstOrFail();

        $patient = User::findOrFail($invite->inviter_id);
        $user = $request->user();

        if (! $user) {
            if ($invite->invited_user_id) {
                $user = User::findOrFail($invite->invited_user_id);
            } else {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $invite->email,
                    'password' => Hash::make($request->password),
                ]);
                $invite->update(['invited_user_id' => $user->id]);
            }
        }

        if (! $user->patients()->where('patient_id', $patient->id)->exists()) {
            $user->patients()->attach($patient->id, ['priority' => 0]);
        }

        $invite->update(['status' => 'accepted']);

        return response()->json([
            'message' => 'You are now a caregiver.',
            'user' => $user,
        ]);
    }

    /**
     * Remove a caregiver-patient relationship
     *
     * @bodyParam user_id int required The ID of the other party. Example: 5
     *
     * @authenticated
     */
    public function remove(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $user = $request->user();
        $other = User::findOrFail($request->user_id);

        $user->caregivers()->detach($other->id);
        $user->patients()->detach($other->id);

        return response()->json(['message' => 'Caregiver relationship removed.']);
    }

    /**
     * List pending invites sent by the authenticated user
     *
     * @authenticated
     */
    public function pending(Request $request)
    {
        $invites = Invite::with('invitedUser')
            ->where('inviter_id', $request->user()->id)
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->get();

        return InviteResource::collection($invites);
    }

    /**
     * Validate an invite token (for public use in registration flows)
     *
     * @queryParam token string required The invite token. Example: abc-123
     */
    public function validateToken(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        $invite = Invite::with('inviter')
            ->where('token', $request->token)
            ->where('status', 'pending')
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->firstOrFail();

        return response()->json([
            'message' => 'Invite is valid.',
            'invite' => [
                'email' => $invite->email,
                'inviter_name' => $invite->inviter->name,
                'expires_at' => optional($invite->expires_at)->toDateTimeString(),
            ],
        ]);
    }
}