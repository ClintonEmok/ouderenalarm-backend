<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

/**
 * @group Authentication
 *
 * Endpoints for mobile/API users using token-based login via Laravel Sanctum.
 */
class AuthSessionController extends Controller
{
    /**
     * Login for Mobile/API Users (Token-based)
     *
     * This endpoint logs in users using Laravel Sanctum and returns an access token.
     *
     * @unauthenticated
     *
     * @bodyParam email string required The user's email. Example: user@example.com
     * @bodyParam password string required The user's password. Example: password123
     *
     * @response 200 {
     *   "message": "Login successful",
     *   "access_token": "1|e3PDeODU1v6Fw7zUb1DQcqNfXk7LJACXfiHVAmk2",
     *   "user": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "user@example.com"
     *   }
     * }
     */
    public function loginToken(LoginRequest $request): JsonResponse
    {
        try {
            $request->authenticate();
            $user = Auth::user();
            $token = $user->createToken('mobile')->plainTextToken;

            Log::info('Token login successful', ['user_id' => $user->id]);

            return response()->json([
                'message' => 'Login successful',
                'access_token' => $token,
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            Log::error('Token login failed', [
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'message' => 'Authentication failed',
            ], 401);
        }
    }

    /**
     * Logout for Mobile/API Users (Token-based)
     *
     * This endpoint revokes the current access token for the authenticated user.
     *
     * @authenticated
     *
     * @response 200 {
     *   "message": "Logout successful"
     * }
     */
    public function logoutToken(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($request->bearerToken()) {
                $accessToken = $user->currentAccessToken();
                if ($accessToken) {
                    $accessToken->delete();
                    Log::info('Token revoked', ['user_id' => $user->id, 'token_id' => $accessToken->id]);
                }
            }

            return response()->json([
                'message' => 'Logout successful',
            ]);
        } catch (\Exception $e) {
            Log::error('Token logout failed', [
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'message' => 'Logout failed',
            ], 500);
        }
    }
}