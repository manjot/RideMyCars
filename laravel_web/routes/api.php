<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RideController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\DriverApiController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public driver listing & country info
Route::get('/drivers', [DriverApiController::class, 'drivers']);
Route::get('/drivers/{id}', [DriverApiController::class, 'driverDetail']);
Route::get('/countries', [DriverApiController::class, 'countries']);
Route::post('/drivers/calculate-price', [DriverApiController::class, 'calculatePrice']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::post('/logout', [AuthController::class, 'logout']);

    // Rides & Vehicles
    Route::apiResource('rides', RideController::class);
    Route::apiResource('vehicles', VehicleController::class);

    // Driver Bookings & Reviews
    Route::post('/drivers/book', [DriverApiController::class, 'bookDriver']);
    Route::post('/driver-bookings/{id}/review', [DriverApiController::class, 'submitReview']);

    // Activity Logs
    Route::get('/activity-logs', [DriverApiController::class, 'activityLogs']);
});
