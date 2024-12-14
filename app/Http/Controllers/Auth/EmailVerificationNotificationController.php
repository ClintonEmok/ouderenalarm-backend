<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     *
     * @group Authentication
     *
     * **Resend Email Verification Link**
     *
     * This endpoint sends a new email verification link to the user's registered email.
     * If the user's email is already verified, it redirects them to the dashboard.
     *
     * **Requirements:**
     * - The user must be authenticated.
     * - The email must not be verified to receive a new link.
     *
     * @authenticated
     *
     * @response 200 {
     *   "status": "verification-link-sent"
     * }
     *
     * @response 302 Redirects to /dashboard if the email is already verified.
     *
     * @response 500 {
     *   "message": "Internal server error."
     * }
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended('/dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['status' => 'verification-link-sent']);
    }
}
