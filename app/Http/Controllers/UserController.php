<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Update the authenticated user's information.
     *
     * @group User
     *
     * **Update User Information**
     *
     * This endpoint allows an authenticated user to update their profile information.
     *
     * @authenticated
     *
     * @bodyParam name string optional The user's new name. Example: John Doe
     * @bodyParam email string optional The user's new email address. Must be unique. Example: user@example.com
     * @bodyParam phone_number string optional The user's phone number. Example: +1234567890
     *
     * @response 200 {
     *   "message": "User information updated successfully",
     *   "user": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "user@example.com",
     *     "phone_number": "+1234567890",
     *     "updated_at": "2024-11-20T12:00:00.000000Z"
     *   }
     * }
     */
    public function update(Request $request)
    {
        try {
            $user = Auth::user();

            $validatedData = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $user->id,
                'phone_number' => 'sometimes|string|max:15',
            ]);

            $user->update($validatedData);

            Log::info('User information updated successfully', ['user_id' => $user->id]);

            return response()->json([
                'message' => 'User information updated successfully',
                'user' => $user->fresh(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('User information update failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'message' => 'Failed to update user information.',
            ], 500);
        }
    }

    /**
     * Update the authenticated user's password.
     *
     * @group User
     *
     * **Update User Password**
     *
     * This endpoint allows an authenticated user to update their password.
     * The user must provide their current password as an extra layer of security.
     *
     * @authenticated
     *
     * @bodyParam current_password string required The user's current password. Example: oldpassword123
     * @bodyParam new_password string required The new password. Must be at least 8 characters long. Example: newpassword123
     * @bodyParam new_password_confirmation string required The new password confirmation. Must match the new password. Example: newpassword123
     *
     * @response 200 {
     *   "message": "Password updated successfully"
     * }
     *
     * @response 401 {
     *   "message": "Invalid current password provided"
     * }
     *
     * @response 500 {
     *   "message": "Failed to update password."
     * }
     */
    public function updatePassword(Request $request)
    {
        try {
            $user = Auth::user();

            $validatedData = $request->validate([
                'current_password' => 'required|string|min:8',
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            // 🔐 Check if the current password is valid
            if (!Hash::check($validatedData['current_password'], $user->password)) {
                Log::warning('Password update failed due to incorrect current password', ['user_id' => $user->id]);

                return response()->json([
                    'message' => 'Invalid current password provided'
                ], 401);
            }

            // 🔐 Update the user's password
            $user->update([
                'password' => Hash::make($validatedData['new_password'])
            ]);

            Log::info('User password updated successfully', ['user_id' => $user->id]);

            return response()->json([
                'message' => 'Password updated successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Password update failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'message' => 'Failed to update password.',
            ], 500);
        }
    }

    /**
     * Show the authenticated user's information.
     *
     * @group User
     *
     * **Get User Information**
     *
     * This endpoint allows an authenticated user to retrieve their profile information.
     *
     * @authenticated
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "John Doe",
     *   "email": "user@example.com",
     *   "phone_number": "+1234567890",
     *   "created_at": "2024-11-20T12:00:00.000000Z",
     *   "updated_at": "2024-11-20T12:00:00.000000Z"
     * }
     */
    public function show()
    {
        try {
            $user = Auth::user();

            return response()->json($user, 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve user information', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'message' => 'Failed to retrieve user information.',
            ], 500);
        }
    }

    /**
     * Delete the authenticated user's account.
     *
     * @group User
     *
     * **Delete User Account**
     *
     * This endpoint allows an authenticated user to delete their account.
     * It removes the user and all associated resources if necessary.
     * The user must provide their password as an extra layer of security.
     *
     * @authenticated
     *
     * @bodyParam password string required The user's current password. Example: password123
     *
     * @response 200 {
     *   "message": "User account deleted successfully"
     * }
     *
     * @response 401 {
     *   "message": "Invalid password provided"
     * }
     *
     * @response 500 {
     *   "message": "Failed to delete user account."
     * }
     */
    public function destroy(Request $request)
    {
        try {
            $request->validate([
                'password' => 'required|string|min:8',
            ]);

            $user = Auth::user();

            if (!Hash::check($request->password, $user->password)) {
                Log::warning('Account deletion failed due to incorrect password', ['user_id' => $user->id]);

                return response()->json([
                    'message' => 'Invalid password provided'
                ], 401);
            }

            $user->delete();

            Log::info('User account deleted successfully', ['user_id' => $user->id]);

            return response()->json([
                'message' => 'User account deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to delete user account', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'message' => 'Failed to delete user account.',
            ], 500);
        }
    }
}
