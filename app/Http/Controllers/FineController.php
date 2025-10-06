<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Fine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class FineController extends Controller
{
    public function createFine(Request $request): JsonResponse
    {
        $request->validate([
            'driver_id' => 'required',
            'violation' => 'required|string|max:255',
            'amount' => 'required|numeric|min:160',
            'date' => 'required|date',
            'officer_id' => 'required|exists:users,id',
            'role' => 'required|in:1,2',
        ]);

        $fine = Fine::create([
            'driver_id' => $request->driver_id,
            'officer_id' => $request->officer_id,
            'violation' => $request->violation,
            'amount' => $request->amount,
            'date' => $request->date,
            'status' => 'unpaid',
        ]);

        return response()->json([
            'message' => 'Fine issued successfully',
            'fine' => $fine
        ], 201);
    }

    public function viewAllFines(): JsonResponse
    {
        $fines = Fine::with(['driver.user', 'officer'])->get();
        return response()->json($fines, 200);
    }

    public function viewDriverFines(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $driver = Driver::where('user_id', $request->user_id)->first();

        if (!$driver) {
            return response()->json(['message' => 'Driver not found'], 404);
        }

        $fines = Fine::with(['driver.user', 'officer'])
            ->where('driver_id', $driver->id)
            ->get();

        return response()->json([
            'driver_id' => $driver->id,
            'fines' => $fines
        ], 200);
    }

    public function payFine($id): JsonResponse
    {
        $fine = Fine::where('id', $id)->where('status', 'unpaid')->first();

        if (!$fine) {
            return response()->json(['message' => 'Fine not found or already paid'], 404);
        }

        $fine->update(['status' => 'paid']);

        return response()->json([
            'message' => 'Fine paid successfully',
            'fine' => $fine
        ], 200);
    }

    public function createCheckoutSession(Request $request): JsonResponse
    {
        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'LKR',
                        'product_data' => [
                            'name' => "Fine Payment - ID: " . $request->fine_id,
                        ],
                        'unit_amount' => $request->amount * 100,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => env('FRONTEND_URL') . "/payment-success?fine_id=" . $request->fine_id,
                'cancel_url' => env('FRONTEND_URL') . "/payment-failed",
            ]);

            return response()->json(['sessionId' => $session->id]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
