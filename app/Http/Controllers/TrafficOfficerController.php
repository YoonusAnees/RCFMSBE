<?php

namespace App\Http\Controllers;

use App\Models\TrafficOfficer;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

class TrafficOfficerController extends Controller
{
    public function createTrafficOfficer(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6',
            'badge_number' => 'required|string|unique:traffic_officers,badge_number',
            'station' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_as' => 2,
            ]);

            $trafficOfficer = TrafficOfficer::create([
                'user_id' => $user->id,
                'badge_number' => $request->badge_number,
                'station' => $request->station,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Traffic Officer created successfully',
                'user' => $user,
                'traffic_officer' => $trafficOfficer
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error creating traffic officer:', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Failed to create traffic officer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllTrafficOfficers(): JsonResponse
    {
        $officers = TrafficOfficer::with('user')->get();
        return response()->json($officers, 200);
    }

    public function getTrafficOfficer($id): JsonResponse
    {
        $officer = TrafficOfficer::with('user')->findOrFail($id);
        return response()->json($officer, 200);
    }

    public function updateTrafficOfficer(Request $request, $id): JsonResponse
    {
        $officer = TrafficOfficer::findOrFail($id);

        $request->validate([
            'badge_number' => 'sometimes|string|unique:traffic_officers,badge_number,' . $officer->id,
            'station' => 'nullable|string',
        ]);

        $officer->update($request->all());

        return response()->json([
            'message' => 'Traffic Officer updated successfully',
            'traffic_officer' => $officer
        ], 200);
    }

    public function deleteTrafficOfficer($id): JsonResponse
    {
        $officer = TrafficOfficer::findOrFail($id);
        $officer->delete();

        return response()->json([
            'message' => 'Traffic Officer deleted successfully'
        ], 200);
    }
}

