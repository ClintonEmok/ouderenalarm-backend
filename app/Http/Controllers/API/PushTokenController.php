<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PushToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PushTokenController extends Controller
{
    /**
     * Register or update a push token for the authenticated user.
     *
     * This endpoint is called by the mobile app after receiving a push token from FCM.
     * It ensures that the token is stored and linked to the current user, allowing them to receive notifications.
     *
     * @group Push Notifications
     * @authenticated
     *
     * @bodyParam token string required The push token from FCM. Example: fcm_abc123
     * @bodyParam platform string The platform of the device. Example: android
     * @bodyParam app_version string The version of the app. Example: 1.0.0
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Push token registered successfully.",
     *   "data": {
     *     "id": 1,
     *     "user_id": 42,
     *     "token": "fcm_abc123",
     *     "platform": "android",
     *     "app_version": "1.0.0",
     *     "created_at": "2025-06-14T12:34:56.000000Z",
     *     "updated_at": "2025-06-14T12:34:56.000000Z"
     *   }
     * }
     * @response 422 {
     *   "message": "The token field is required.",
     *   "errors": {
     *     "token": ["The token field is required."]
     *   }
     * }
     * @response 500 {
     *   "status": "error",
     *   "message": "An error occurred while registering the push token."
     * }
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'platform' => 'nullable|string',
            'app_version' => 'nullable|string',
        ]);

        try {
            $user = $request->user();

            $pushToken = PushToken::updateOrCreate(
                ['token' => $validated['token']],
                [
                    'user_id' => $user->id,
                    'platform' => $validated['platform'] ?? null,
                    'app_version' => $validated['app_version'] ?? null,
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Push token registered successfully.',
                'data' => $pushToken,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to register push token', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
                'token' => $validated['token'],
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while registering the push token.',
            ], 500);
        }
    }

    /**
     * Delete a push token for the authenticated user.
     *
     * This is typically called when the user logs out or disables notifications on the device.
     *
     * @group Push Notifications
     * @authenticated
     *
     * @bodyParam token string required The push token to delete. Example: fcm_abc123
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Push token deleted successfully."
     * }
     * @response 404 {
     *   "status": "success",
     *   "message": "No matching token found for user."
     * }
     * @response 422 {
     *   "message": "The token field is required.",
     *   "errors": {
     *     "token": ["The token field is required."]
     *   }
     * }
     * @response 500 {
     *   "status": "error",
     *   "message": "An error occurred while deleting the push token."
     * }
     */
    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        try {
            $user = $request->user();

            $deleted = PushToken::where('token', $validated['token'])
                ->where('user_id', $user->id)
                ->delete();

            return response()->json([
                'status' => 'success',
                'message' => $deleted
                    ? 'Push token deleted successfully.'
                    : 'No matching token found for user.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to delete push token', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
                'token' => $validated['token'],
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while deleting the push token.',
            ], 500);
        }
    }
}