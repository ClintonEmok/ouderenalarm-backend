<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Address;
use App\Models\City;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Events\Registered;
use Exception;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @group Authentication
     *
     * **User Registration**
     *
     * This endpoint allows new users to register an account. The user must provide personal information and address details.
     * The system will create or link the user, city, country, and address.
     *
     * **Requirements:**
     * - Passwords must be at least 8 characters and match the password confirmation.
     * - Email must be unique in the system.
     *
     * **Note:**
     * - The user's address is linked to the user as a "shipping" address.
     * - All operations are performed within a database transaction for data integrity.
     *
     * @bodyParam name string required The first name of the user. Example: John
     * @bodyParam last_name string required The last name of the user. Example: Doe
     * @bodyParam email string required The email address of the user. Must be unique. Example: john.doe@example.com
     * @bodyParam password string required The password for the user. Must be at least 8 characters. Example: Password123!
     * @bodyParam password_confirmation string required Confirmation of the password. Must match the `password` field. Example: Password123!
     * @bodyParam phone_number string optional The user's phone number. Example: +1234567890
     * @bodyParam street string required The street name of the user's address. Example: Main Street
     * @bodyParam house_number string required The house number of the user's address. Example: 42A
     * @bodyParam postal_code string required The postal code of the user's address. Example: 12345
     * @bodyParam city string required The city where the user resides. Example: Amsterdam
     * @bodyParam country string required The country where the user resides. Example: Netherlands
     *
     * @response 201 {
     *   "success": true,
     *   "message": "User registered successfully",
     *   "user": {
     *     "id": 1,
     *     "name": "John",
     *     "last_name": "Doe",
     *     "email": "john.doe@example.com",
     *     "phone_number": "+1234567890",
     *     "created_at": "2024-12-12T08:00:00.000000Z",
     *     "updated_at": "2024-12-12T08:00:00.000000Z",
     *     "addresses": [
     *       {
     *         "id": 1,
     *         "street_name": "Main Street",
     *         "house_number": "42A",
     *         "postal_code": "12345",
     *         "city_id": 1,
     *         "country_id": 1,
     *         "created_at": "2024-12-12T08:00:00.000000Z",
     *         "updated_at": "2024-12-12T08:00:00.000000Z"
     *       }
     *     ]
     *   }
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "message": "Registration failed. Please try again.",
     *   "error": "Failed to create the user."
     * }
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // 1️⃣ Validate the user and address input
            $validatedData = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'unique:users,email'],
                'password' => ['required', 'min:8', 'confirmed'],
                'phone_number' =>['nullable', 'string'],
            ]);

            DB::beginTransaction();

            $user = User::create([
                'name' => $validatedData['name'],
                'last_name' => $validatedData['last_name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'phone_number' => optional($validatedData)['phone_number'],
            ]);

            if (!$user) {
                throw new Exception('Failed to create the user.');
            }

            DB::commit();

            event(new Registered($user));

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'user' => $user
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('User registration failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
