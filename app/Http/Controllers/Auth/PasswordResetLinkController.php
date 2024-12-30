<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetLinkController extends Controller
{
    /**
     * Handle an incoming password reset link request.
     *
     * @group Authentication
     *
     * **Request Password Reset Link**
     *
     * This endpoint allows users to request a password reset link to be sent to their email.
     *
     * **Requirements:**
     * - The email must be registered in the system.
     * - The email must be in a valid email format.
     *
     * **Notes:**
     * - If the email does not exist, the user will still receive a response as if it was successful.
     * - This behavior prevents attackers from determining which emails are registered.
     *
     * @bodyParam email string required The email address associated with the user's account. Example: user@example.com
     *
     * @response 200 {
     *   "status": "We have emailed your password reset link!"
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "email": ["We can't find a user with that email address."]
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
            'email' => ['required', 'email'],
        ]);

        // Attempt to send the password reset link
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // If the reset link could not be sent, throw a validation error
        if ($status != Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        // Return a success message
        return response()->json(['status' => __($status)]);
    }
}
