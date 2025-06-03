<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * @group Authentication
 *
 * User registration for mobile/API clients.
 */
class RegisterController extends Controller
{
    /**
     * Register a new user and return an access token
     *
     * @unauthenticated
     *
     * @bodyParam name string required The user's name. Example: John Doe
     * @bodyParam email string required The user's email. Must be unique. Example: john@example.com
     * @bodyParam password string required The password (min: 8). Example: password123
     * @bodyParam password_confirmation string required Must match password. Example: password123
     *
     * @response 201 {
     *   "message": "Registration successful",
     *   "access_token": "1|xyz...",
     *   "user": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com"
     *   }
     * }
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken('mobile')->plainTextToken;

        Log::info('User registered successfully', ['user_id' => $user->id]);

        return response()->json([
            'message' => 'Registration successful',
            'access_token' => $token,
            'user' => $user,
        ], 201);
    }
}