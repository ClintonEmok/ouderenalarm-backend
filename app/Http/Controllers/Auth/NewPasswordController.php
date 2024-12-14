<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class NewPasswordController extends Controller
{
    /**
     * Handle an incoming new password request.
     *
     * @group Authentication
     *
     * **Reset Password**
     *
     * This endpoint allows users to reset their password using a token sent to their email.
     * It requires the email, token, and new password (with confirmation).
     *
     * **Requirements:**
     * - The password must meet the application's password policy.
     * - The token must be valid and not expired.
     *
     * @bodyParam token string required The password reset token received via email. Example: AbC123XyZ
     * @bodyParam email string required The user's email address. Example: user@example.com
     * @bodyParam password string required The new password for the user. Example: MyN3wP@ssword!
     * @bodyParam password_confirmation string required Must match the `password` field. Example: MyN3wP@ssword!
     *
     * @response 200 {
     *   "status": "Your password has been reset!"
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "email": ["This password reset token is invalid."]
     *   }
     * }
     *
     * @response 500 {
     *   "message": "Internal server error."
     * }
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Attempt to reset the user's password
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->string('password')),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password reset attempt was unsuccessful, throw a validation error
        if ($status != Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        // Return a success message
        return response()->json(['status' => __($status)]);
    }
}
