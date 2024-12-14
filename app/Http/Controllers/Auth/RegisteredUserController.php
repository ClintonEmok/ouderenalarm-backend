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
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        // Start a try-catch block to capture and handle any errors
        try {
            // 1️⃣ Validate the user and address input
            $validatedData = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'unique:users,email'],
                'password' => ['required', 'min:8', 'confirmed'],
                'phone_number' =>['nullable', 'string'],
                'street' => ['required', 'string', 'max:255'],
                'house_number' => ['required', 'string', 'max:255'],
                'postal_code' => ['required', 'string', 'max:20'],
                'city' => ['required', 'string', 'max:100'],
                'country' => ['required', 'string', 'max:100'],
            ]);

            // 2️⃣ Use a database transaction to ensure atomicity
            DB::beginTransaction();

            // 3️⃣ Create the user and hash the password
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

            // 4️⃣ Create or get the country
            $country = Country::firstOrCreate(['name' => $validatedData['country']]);

            if (!$country) {
                throw new Exception('Failed to create or fetch the country.');
            }

            // 5️⃣ Create or get the city and link it to the country
            $city = City::firstOrCreate([
                'name' => $validatedData['city'],
                'country_id' => $country->id
            ]);

            if (!$city) {
                throw new Exception('Failed to create or fetch the city.');
            }

            // 6️⃣ Create or get the address
            $address = Address::firstOrCreate([
                'street_name' => $validatedData['street'],
                'house_number' => $validatedData['house_number'],
                'postal_code' => $validatedData['postal_code'],
                'city_id' => $city->id,
                'country_id' => $country->id
            ]);

            if (!$address) {
                throw new Exception('Failed to create or fetch the address.');
            }

            // 7️⃣ Attach the address to the user with the type (billing or shipping)
            $user->addresses()->attach($address->id, ['type' => "shipping"]);

            // If everything is successful, commit the transaction
            DB::commit();

            // 8️⃣ Fire the user registered event
            event(new Registered($user));

            // 9️⃣ Return success response
            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'user' => $user->load('addresses')
            ], 201);

        } catch (Exception $e) {
            // Rollback any changes to maintain data integrity
            DB::rollBack();

            // Log the error message for debugging purposes
            Log::error('User registration failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            // Return an error response to the client
            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
