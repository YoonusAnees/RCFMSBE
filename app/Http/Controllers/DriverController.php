<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DriverController extends Controller
{
    public function driverCreate(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6',
            'license_number' => 'required|string|unique:drivers,license_number',
            'phone_number' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_as' => 3,
            ]);

            $driver = Driver::create([
                'user_id' => $user->id,
                'license_number' => $request->license_number,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Driver created successfully',
                'user' => $user,
                'driver' => $driver
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating driver:', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Failed to create driver',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllDrivers(): JsonResponse
    {
        $drivers = Driver::with('user')->get();
        return response()->json($drivers, 200);
    }

    public function getDriver($id): JsonResponse
    {
        $driver = Driver::with('user')->findOrFail($id);
        return response()->json($driver, 200);
    }

    public function updateDriver(Request $request, $id): JsonResponse
    {
        $driver = Driver::findOrFail($id);

        $request->validate([
            'license_number' => 'sometimes|string|unique:drivers,license_number,' . $driver->id,
            'phone_number' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $driver->update($request->all());

        return response()->json([
            'message' => 'Driver updated successfully',
            'driver' => $driver
        ], 200);
    }

    public function deleteDriver($id): JsonResponse
    {
        $driver = Driver::findOrFail($id);
        $driver->delete();

        return response()->json([
            'message' => 'Driver deleted successfully'
        ], 200);
    }
}
