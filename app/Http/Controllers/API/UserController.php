<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\UserResource;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group User
 *
 * Endpoints for managing the authenticated user's profile, caregivers, and patients.
 */
class UserController extends Controller
{
    /**
     * Show the authenticated user's information.
     *
     * @authenticated
     */
    public function show()
    {
        try {
            return response()->json(Auth::user(), 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve user information', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json(['message' => 'Failed to retrieve user information.'], 500);
        }
    }

    /**
     * Update the authenticated user's profile information.
     *
     * @bodyParam name string optional The user's name. Example: John
     * @bodyParam email string optional Unique email. Example: john@example.com
     * @bodyParam phone_number string optional Phone number. Example: +31612345678
     *
     * @authenticated
     */
    public function update(Request $request)
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $user->id,
                'phone_number' => 'sometimes|string|max:15',
            ]);

            $user->update($validated);

            return response()->json([
                'message' => 'User information updated successfully',
                'user' => $user->fresh(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update user info', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json(['message' => 'Failed to update user information.'], 500);
        }
    }

    /**
     * Update the authenticated user's password.
     *
     * @bodyParam current_password string required. Example: oldpass123
     * @bodyParam new_password string required. Example: newpass456
     * @bodyParam new_password_confirmation string required. Example: newpass456
     *
     * @authenticated
     */
    public function updatePassword(Request $request)
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'current_password' => 'required|string|min:8',
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json(['message' => 'Invalid current password provided'], 401);
            }

            $user->update(['password' => Hash::make($validated['new_password'])]);

            return response()->json(['message' => 'Password updated successfully']);
        } catch (\Exception $e) {
            Log::error('Password update failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json(['message' => 'Failed to update password.'], 500);
        }
    }

    /**
     * Delete the authenticated user's account.
     *
     * @bodyParam password string required The user's password. Example: pass123
     *
     * @authenticated
     */
    public function destroy(Request $request)
    {
        try {
            $request->validate(['password' => 'required|string|min:8']);
            $user = Auth::user();

            if (!Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Invalid password provided'], 401);
            }

            $user->delete();

            return response()->json(['message' => 'User account deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Account deletion failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json(['message' => 'Failed to delete user account.'], 500);
        }
    }

    /**
     * Get all caregivers for the authenticated user.
     *
     * @authenticated
     */
    public function caregivers(Request $request)
    {
        $caregivers = $request->user()
            ->caregivers()
            ->withPivot('priority')
            ->orderBy('pivot_priority')
            ->get();

        return UserResource::collection($caregivers);
    }

    /**
     * Get all patients the authenticated user cares for.
     *
     * @authenticated
     */
    public function patients(Request $request)
    {
        $patients = $request->user()->patients()->get();

        return UserResource::collection($patients);
    }

    /**
     * Update caregiver priorities.
     *
     * @bodyParam caregivers array required
     * @bodyParam caregivers[].user_id int required
     * @bodyParam caregivers[].priority int required
     *
     * @authenticated
     */
    public function updateCaregiverPriorities(Request $request)
    {
        $data = $request->validate([
            'caregivers' => 'required|array',
            'caregivers.*.user_id' => 'required|integer|exists:users,id',
            'caregivers.*.priority' => 'required|integer|min:0',
        ]);

        $user = $request->user();

        foreach ($data['caregivers'] as $item) {
            $user->caregivers()->updateExistingPivot($item['user_id'], [
                'priority' => $item['priority'],
            ]);
        }

        return response()->json(['message' => 'Caregiver priorities updated.']);
    }


    /**
     * Get notes for the authenticated user.
     *
     * @group Notes
     * @authenticated
     *
     * @response 200 scenario="Success" {
     *   "status": "success",
     *   "message": "Notes retrieved successfully.",
     *   "count": 1,
     *   "data": [
     *     {
     *       "id": 1,
     *       "type": "medical",
     *       "content": "Feeling better.",
     *       "author_id": 5,
     *       "noteable_id": 5,
     *       "noteable_type": "App\\Models\\User",
     *       "created_at": "2025-05-19T12:00:00Z",
     *       "updated_at": "2025-05-19T12:00:00Z"
     *     }
     *   ]
     * }
     */
    public function userNotes(): JsonResponse
    {
        try {
            $notes = auth()->user()->notes()->latest()->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Notes retrieved successfully.',
                'count' => $notes->count(),
                'data' => $notes,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to retrieve notes', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong while retrieving notes.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a new note for the authenticated user.
     *
     * @group Notes
     * @authenticated
     *
     * @bodyParam type string Example: medical
     * @bodyParam content string required Example: I feel dizzy today.
     * @bodyParam anonymous boolean optional Example: true
     *
     * @response 201 {
     *   "status": "success",
     *   "message": "Note created successfully.",
     *   "data": { "id": 2, "type": "medical", "content": "I feel dizzy today.", ... }
     * }
     */
    public function storeUserNote(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'type' => 'nullable|string|max:255',
                'content' => 'required|string',
                'anonymous' => 'sometimes|boolean',
            ]);

            $note = auth()->user()->notes()->create([
                'type' => $validated['type'] ?? null,
                'content' => $validated['content'],
                'author_id' => $validated['anonymous'] ?? false ? null : auth()->id(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Note created successfully.',
                'data' => $note,
            ], Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            Log::error('Failed to create note', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong while creating the note.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update an existing note of the authenticated user.
     *
     * @group Notes
     * @authenticated
     *
     * @urlParam note int required The ID of the note. Example: 5
     * @bodyParam type string Example: general
     * @bodyParam content string required Example: Updated note content.
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Note updated successfully.",
     *   "data": { "id": 5, "type": "general", "content": "Updated note content.", ... }
     * }
     */
    public function updateUserNote(Request $request, Note $note): JsonResponse
    {
        try {
            if (! $note->noteable->is(auth()->user())) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not authorized to update this note.',
                ], Response::HTTP_FORBIDDEN);
            }

            $validated = $request->validate([
                'type' => 'nullable|string|max:255',
                'content' => 'required|string',
            ]);

            $note->update([
                'type' => $validated['type'] ?? null,
                'content' => $validated['content'],
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Note updated successfully.',
                'data' => $note,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to update note', [
                'error' => $e->getMessage(),
                'note_id' => $note->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong while updating the note.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete a note owned by the authenticated user.
     *
     * @group Notes
     * @authenticated
     *
     * @urlParam note int required The ID of the note. Example: 5
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Note deleted successfully."
     * }
     */
    public function deleteUserNote(Note $note): JsonResponse
    {
        try {
            if (! $note->noteable->is(auth()->user())) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not authorized to delete this note.',
                ], Response::HTTP_FORBIDDEN);
            }

            $note->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Note deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to delete note', [
                'error' => $e->getMessage(),
                'note_id' => $note->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong while deleting the note.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}