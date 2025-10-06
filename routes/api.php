<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FineController;
use App\Http\Controllers\TrafficOfficerController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DriverController;

    Route::post('/login', [AuthController::class, 'login']);


    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user-profile', [AuthController::class, 'userProfile']);
    });


    Route::middleware(['auth:sanctum', 'role:1'])->group(function () {
        Route::get('/admin-dashboard', function () {
            return response()->json(['message' => 'Welcome Admin']);
        });
    });

    Route::get('/view-all-fines', [FineController::class, 'viewAllFines']);

    Route::middleware(['auth:sanctum', 'role:2'])->group(function () {
        Route::get('/officer-dashboard', function () {
            return response()->json(['message' => 'Welcome Traffic Officer']);
        });
    });
     Route::post('/create-fine', [FineController::class, 'createFine']);

    Route::middleware(['auth:sanctum', 'role:3'])->group(function () {
        Route::get('/driver-dashboard', function () {
            return response()->json(['message' => 'Welcome Driver']);
        });
    });
        Route::get('/view-driver-fines', [FineController::class, 'viewDriverFines']);
        Route::post('/pay-fine/{id}', [FineController::class, 'payFine']);
        Route::post('/create-checkout-session', [FineController::class, 'createCheckoutSession']);


    Route::post('/driver-create', [DriverController::class, 'driverCreate']);
    Route::get('/get-all-drivers', [DriverController::class, 'getAllDrivers']);
    Route::get('/get-driver/{id}', [DriverController::class, 'getDriver']);
    Route::put('/update-driver/{id}', [DriverController::class, 'updateDriver']);
    Route::delete('/delete-driver/{id}', [DriverController::class, 'deleteDriver']);

    Route::post('/admin-create', [UserController::class, 'createAdmin']);
    Route::get('/get-all-users', [UserController::class, 'getAllUsers']);
    Route::get('/get-user/{id}', [UserController::class, 'getUser']);
    Route::put('/update-user/{id}', [UserController::class, 'updateUser']);
    Route::delete('/delete-user/{id}', [UserController::class, 'deleteUser']);
    Route::put('/reset-password/{id}', [UserController::class, 'resetPassword']);


    Route::post('/traffic-officer-create', [TrafficOfficerController::class, 'createTrafficOfficer']);
    Route::get('/get-all-traffic-officers', [TrafficOfficerController::class, 'getAllTrafficOfficers']);
    Route::get('/get-traffic-officer/{id}', [TrafficOfficerController::class, 'getTrafficOfficer']);
    Route::put('/update-traffic-officer/{id}', [TrafficOfficerController::class, 'updateTrafficOfficer']);
    Route::delete('/delete-traffic-officer/{id}', [TrafficOfficerController::class, 'deleteTrafficOfficer']);
