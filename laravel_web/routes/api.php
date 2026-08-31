<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RideController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\DriverApiController;
use App\Http\Controllers\Api\BannerApiController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\StripePaymentController;
use App\Http\Controllers\Api\StripeWebhookController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

use App\Http\Controllers\StripeVerificationController;

// Stripe Payment Gateway & Webhooks
Route::post('/stripe/create-payment-intent', [StripePaymentController::class, 'createPaymentIntent']);
Route::post('/stripe/confirm-payment', [StripePaymentController::class, 'confirmPayment']);
Route::get('/stripe/payment-status/{identifier}', [StripePaymentController::class, 'getPaymentStatus']);
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook']);

// Stripe Driver Verification Flow
Route::post('/payment/submit-verification', [StripeVerificationController::class, 'submitForVerification']);
Route::post('/driver/verify-booking', [StripeVerificationController::class, 'driverRespond']);
Route::get('/payment/verification-status/{serviceType}/{serviceId}', [StripeVerificationController::class, 'getVerificationStatus']);
Route::get('/driver/pending-verifications', [StripeVerificationController::class, 'getPendingVerifications']);

// Public Banners, Categories & Products
Route::get('/banners', [BannerApiController::class, 'index']);
Route::get('/banners/{id}', [BannerApiController::class, 'show']);
Route::get('/categories', [CategoryApiController::class, 'index']);
Route::get('/categories/{id}', [CategoryApiController::class, 'show']);
Route::get('/products', [ProductApiController::class, 'index']);
Route::get('/products/{id}', [ProductApiController::class, 'show']);

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

    // Banners & Categories Management
    Route::post('/banners', [BannerApiController::class, 'store']);
    Route::put('/banners/{id}', [BannerApiController::class, 'update']);
    Route::delete('/banners/{id}', [BannerApiController::class, 'destroy']);

    Route::post('/categories', [CategoryApiController::class, 'store']);
    Route::put('/categories/{id}', [CategoryApiController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryApiController::class, 'destroy']);

    // Products Management
    Route::post('/products', [ProductApiController::class, 'store']);
    Route::put('/products/{id}', [ProductApiController::class, 'update']);
    Route::delete('/products/{id}', [ProductApiController::class, 'destroy']);

    // Rides & Vehicles
    Route::apiResource('rides', RideController::class);
    Route::apiResource('vehicles', VehicleController::class);

    // Driver Bookings & Reviews
    Route::post('/drivers/book', [DriverApiController::class, 'bookDriver']);
    Route::post('/driver-bookings/{id}/review', [DriverApiController::class, 'submitReview']);

    // Activity Logs
    Route::get('/activity-logs', [DriverApiController::class, 'activityLogs']);
});
