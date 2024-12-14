<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     *
     * @group Authentication
     *
     * **Login to the system**
     *
     * This endpoint allows users to log in with their email and password.
     * If successful, it returns an access token for mobile users and a session-based cookie for SPA users.
     *
     * @bodyParam email string required The user's email address. Example: user@example.com
     * @bodyParam password string required The user's password. Example: password123
     *
     * @response 200 {
     *   "message": "Login successful",
     *   "access_token": "1|e3PDeODU1v6Fw7zUb1DQcqNfXk7LJACXfiHVAmk2",
     *   "token_type": "Bearer",
     *   "user": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "user@example.com",
     *     "created_at": "2024-11-20T12:00:00.000000Z",
     *     "updated_at": "2024-11-20T12:00:00.000000Z"
     *   }
     * }
     *
     * @response 401 {
     *   "message": "Authentication failed. Please check your credentials."
     * }
     *
     * @response 500 {
     *   "message": "Internal server error."
     * }
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(LoginRequest $request): JsonResponse
    {
        try {
            // Authenticate the user
            $request->authenticate();

            // Get the authenticated user
            $user = Auth::user();

            // **Hybrid Approach**
            // Option 1: For SPA (Next.js) - Set session-based cookie
            $request->session()->regenerate();

            // Option 2: For Token-based Authentication (e.g., mobile apps)
            $token = $user->createToken('auth_token')->plainTextToken;

            // Log successful login
            Log::info('User logged in successfully', ['user_id' => $user->id]);

            // Return both access token (for mobile apps) and session cookie (for SPA)
            return response()->json([
                'message' => 'Login successful',
                'access_token' => $token, // Token for mobile apps
                'token_type' => 'Bearer',
                'user' => $user,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Login attempt failed', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Authentication failed. Please check your credentials.',
            ], 401);
        }
    }

    /**
     * Destroy an authenticated session.
     *
     * @group Authentication
     *
     * **Logout from the system**
     *
     * This endpoint allows the user to log out.
     * For mobile users, the token is revoked, and for SPA users, the session is invalidated.
     *
     * @authenticated
     *
     * @response 200 {
     *   "message": "Logout successful"
     * }
     *
     * @response 500 {
     *   "message": "Failed to log out."
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        try {
            // Revoke the current token for token-based users (mobile)
            $request->user()->currentAccessToken()->delete();

            // Logout and invalidate the session for SPA users
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'message' => 'Logout successful',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Logout attempt failed', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Failed to log out.',
            ], 500);
        }
    }
}
