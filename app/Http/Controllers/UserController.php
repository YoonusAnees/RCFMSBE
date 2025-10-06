<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function createAdmin(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        try {
            $admin = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_as' => 1,
            ]);

            return response()->json([
                'message' => 'Admin user created successfully',
                'user' => $admin
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating admin:', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Failed to create admin user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllUsers(): JsonResponse
    {
        $users = User::with(['driver', 'trafficOfficer'])->get();
        return response()->json($users, 200);
    }


    public function getUser($id): JsonResponse
    {
        $user = User::findOrFail($id);
        return response()->json($user, 200);
    }

    public function deleteUser($id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ], 200);
    }

    public function updateUser(Request $request, $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'role_as' => 'required|integer|in:1,2,3',
        ]);

        $dataToUpdate = [
            'name' => $request->name,
            'email' => $request->email,
            'role_as' => $request->role_as,
        ];

        try {
            $user->update($dataToUpdate);

            if ($request->role_as == 3 && $user->driver) {
                $user->driver->update([
                    'license_number' => $request->license_number,
                    'phone_number' => $request->phone_number,
                    'address' => $request->address,
                ]);
            } elseif ($request->role_as == 2 && $user->trafficOfficer) {
                $user->trafficOfficer->update([
                    'badge_number' => $request->badge_number,
                    'station' => $request->station,
                ]);
            }

            return response()->json([
                'message' => 'User updated successfully',
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating user:', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function resetPassword($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Password must be at least 8 characters'], 400);
        }

        try {
            $user = User::findOrFail($id);

            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json(['message' => 'Password updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'User not found'], 404);
        }
    }
}
