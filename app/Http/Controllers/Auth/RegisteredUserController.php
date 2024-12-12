<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'], // fixed 'lowercase' issue
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'last_name' => $request->last_name,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->string('password')),
        ]);

        // Fire the Registered event (can be used for sending welcome emails, etc.)
        event(new Registered($user));

        // Log in the user to create a session (for SPA)
        Auth::login($user);
        $request->session()->regenerate(); // Regenerate the session ID

        // **For Mobile Apps or Token-Based Authentication** - Create an access token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return a hybrid response that works for both SPA (cookie) and API (token)
        return response()->json([
            'message' => 'Registration successful',
            'user' => $user,
            'access_token' => $token,
        ], 201);
    }
}
