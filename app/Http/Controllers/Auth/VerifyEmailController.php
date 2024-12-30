<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     *
     * @group Authentication
     *
     * **Email Verification**
     *
     * This endpoint is used to verify the user's email address after they click the link in the email.
     *
     * **Requirements:**
     * - The user must be authenticated.
     * - The email verification link must be valid and not expired.
     *
     * **Notes:**
     * - If the email is already verified, the user is redirected to the dashboard.
     * - If the email is successfully verified, the user is redirected to the dashboard with a success query parameter (`?verified=1`).
     *
     * @authenticated
     *
     * @response 302 Redirects to /dashboard with the query parameter `?verified=1` if successful.
     *
     * @response 302 Redirects to /dashboard with the query parameter `?verified=1` if the email is already verified.
     *
     * @response 500 {
     *   "message": "Internal server error."
     * }
     *
     * @param  \Illuminate\Foundation\Auth\EmailVerificationRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(
                config('app.frontend_url').'/dashboard?verified=1'
            );
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended(
            config('app.frontend_url').'/dashboard?verified=1'
        );
    }
}
