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
     * **Login for SPA Users (Session-based)**
     *
     * This endpoint logs in users via a session and sets a CSRF-protected cookie.
     *
     * @bodyParam email string required The user's email address. Example: user@example.com
     * @bodyParam password string required The user's password. Example: password123
     *
     * @response 200 {
     *   "message": "Login successful",
     *   "user": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "user@example.com",
     *     "created_at": "2024-11-20T12:00:00.000000Z",
     *     "updated_at": "2024-11-20T12:00:00.000000Z"
     *   }
     * }
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginSession(LoginRequest $request): JsonResponse
    {
        try {
            // 🛠️ Authenticate the user
            $request->authenticate();

            // 🛠️ Get the authenticated user
            $user = Auth::user();

            // 🛠️ Set session-based cookie
            $request->session()->regenerate();

            // 🛠️ Log successful login
            Log::info('Session login successful', ['user_id' => $user->id]);

            return response()->json([
                'message' => 'Login successful (session)',
                'user' => $user,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Session login failed', [
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'message' => 'Authentication failed. Please check your credentials.',
            ], 401);
        }
    }

    /**
     * **Login for Mobile/API Users (Token-based)**
     *
     * This endpoint logs in users via token-based authentication.
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
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginToken(LoginRequest $request): JsonResponse
    {
        try {
            // 🛠️ Authenticate the user
            $request->authenticate();

            // 🛠️ Get the authenticated user
            $user = Auth::user();

            // 🛠️ Generate a token
            $token = $user->createToken('auth_token')->plainTextToken;

            // 🛠️ Log successful login
            Log::info('Token login successful', ['user_id' => $user->id]);

            return response()->json([
                'message' => 'Login successful (token)',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Token login failed', [
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'message' => 'Authentication failed. Please check your credentials.',
            ], 401);
        }
    }

    /**
     * **Logout for SPA users (Session-based)**
     *
     * This endpoint logs out a user from the SPA.
     * It invalidates the session and regenerates the CSRF token.
     *
     * @authenticated
     *
     * @response 200 {
     *   "message": "Logout successful"
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logoutSession(Request $request)
    {
        try {
            // 🛠️ Logout and invalidate session
            if (Auth::check()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                Log::info('Session logout successful', ['user_id' => Auth::id()]);
            }

            return response()->json([
                'message' => 'Logout successful (session)',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Session logout failed', [
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'message' => 'Failed to log out (session)',
            ], 500);
        }
    }

    /**
     * **Logout for Mobile/API users (Token-based)**
     *
     * This endpoint logs out a user by revoking the current access token.
     *
     * @authenticated
     *
     * @response 200 {
     *   "message": "Logout successful"
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logoutToken(Request $request)
    {
        try {
            $user = $request->user();

            // 🛠️ Revoke the current access token for the API user
            if ($request->bearerToken()) {
                $accessToken = $request->user()->currentAccessToken();

                if ($accessToken) {
                    $user->tokens()->where('id', $accessToken->id)->delete();
                    Log::info('Token revoked for API user', [
                        'user_id' => $user->id,
                        'token_id' => $accessToken->id
                    ]);
                }
            }

            return response()->json([
                'message' => 'Logout successful (token)',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Token logout failed', [
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'message' => 'Failed to log out (token)',
            ], 500);
        }
    }
}
