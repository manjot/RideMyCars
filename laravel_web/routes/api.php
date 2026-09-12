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
use App\Http\Controllers\Api\PlacesApiController;
use App\Http\Controllers\Api\AppConfigApiController;

// Public App Settings & Dynamic Ride Categories
Route::get('/app-settings', [AppConfigApiController::class, 'getPublicSettings']);
Route::get('/ride-categories', [AppConfigApiController::class, 'getRideCategories']);

// Places Autocomplete, Geocoding & Reverse Geocoding
Route::get('/places/autocomplete', [PlacesApiController::class, 'autocomplete']);
Route::get('/places/details', [PlacesApiController::class, 'details']);
Route::get('/places/geocode', [PlacesApiController::class, 'geocode']);
Route::get('/places/reverse', [PlacesApiController::class, 'reverse']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/otp/send', [AuthController::class, 'sendOtp']);
Route::post('/otp/verify', [AuthController::class, 'verifyOtp']);
Route::post('/auth/phone/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/auth/phone/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/auth/social/google', [\App\Http\Controllers\Auth\SocialAuthController::class, 'apiGoogleAuth']);
Route::post('/auth/social/apple', [\App\Http\Controllers\Auth\SocialAuthController::class, 'apiAppleAuth']);
Route::post('/auth/google', [\App\Http\Controllers\Auth\SocialAuthController::class, 'apiGoogleAuth']);
Route::post('/auth/apple', [\App\Http\Controllers\Auth\SocialAuthController::class, 'apiAppleAuth']);
Route::post('/auth/email/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/auth/email/verify-otp', [AuthController::class, 'verifyOtp']);

use App\Http\Controllers\StripeVerificationController;

// Stripe Payment Gateway & Webhooks
Route::post('/create-payment-intent', [StripePaymentController::class, 'createPaymentIntent']);
Route::post('/stripe/create-payment-intent', [StripePaymentController::class, 'createPaymentIntent']);
Route::post('/stripe/confirm-payment', [StripePaymentController::class, 'confirmPayment']);
Route::get('/stripe/payment-status/{identifier}', [StripePaymentController::class, 'getPaymentStatus']);
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook']);

// ExpressPay Ghana Gateway & Webhooks
Route::post('/expresspay/initiate', [\App\Http\Controllers\ExpressPayController::class, 'initiate']);
Route::post('/payment/expresspay/ipn', [\App\Http\Controllers\ExpressPayController::class, 'handleIpn']);
Route::get('/payment/expresspay/status/{token}', [\App\Http\Controllers\ExpressPayController::class, 'checkStatus']);

// Stripe Driver Verification & Multi-Payment Flow
Route::post('/payment/submit-verification', [StripeVerificationController::class, 'submitForVerification']);
Route::post('/payment/authorize-hold', [StripeVerificationController::class, 'authorizePaymentHold']);
Route::post('/payment/confirm-driver', [StripeVerificationController::class, 'confirmDriverAssignment']);
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

// Public Rental Vehicles Listing, Details & Booking
Route::get('/rent/search', [\App\Http\Controllers\VehicleRentalController::class, 'searchApi']);
Route::get('/vehicles', [\App\Http\Controllers\VehicleRentalController::class, 'searchApi']);
Route::get('/rent/{vehicle}', [\App\Http\Controllers\VehicleRentalController::class, 'detailApi']);
Route::post('/rent/{vehicle}/book', [\App\Http\Controllers\VehicleRentalController::class, 'bookRentalApi']);
Route::post('/rent/book', [\App\Http\Controllers\VehicleRentalController::class, 'bookRentalApi']);

// Public driver listing & country info
Route::get('/drivers', [DriverApiController::class, 'drivers']);
Route::get('/drivers/{id}', [DriverApiController::class, 'driverDetail']);
Route::get('/countries', function (\Illuminate\Http\Request $request) {
    return response()->json([
        'status' => 'success',
        'data' => \App\Services\CountryService::getAll($request),
        'visitor_location' => \App\Services\CountryService::getVisitorLocationInfo($request),
        'is_unsupported_region' => \App\Services\CountryService::isUnsupportedRegion($request),
    ]);
});
Route::get('/country-pricing', function (Request $request) {
    $country = $request->query('country');
    $pricing = $country ? \App\Models\CountryPricing::forCountry($country) : \App\Services\CountryService::getCurrentPricing($request);
    return response()->json([
        'status' => 'success',
        'data' => $pricing,
    ]);
});
Route::post('/drivers/calculate-price', [DriverApiController::class, 'calculatePrice']);
Route::post('/drivers/book', [DriverApiController::class, 'bookDriver']);
Route::post('/hire-driver/book', [DriverApiController::class, 'bookDriver']);

// Public Package Delivery API Routes
Route::post('/delivery/calculate-price', [\App\Http\Controllers\PackageDeliveryController::class, 'calculatePrice']);
Route::post('/delivery/book', [\App\Http\Controllers\PackageDeliveryController::class, 'storeBooking']);
Route::get('/delivery/{id}/status', [\App\Http\Controllers\PackageDeliveryController::class, 'statusApi']);
Route::post('/delivery/{id}/verify-otp', [\App\Http\Controllers\PackageDeliveryController::class, 'verifyOtp']);
Route::post('/delivery/{id}/update-status', [\App\Http\Controllers\PackageDeliveryController::class, 'updateDeliveryStatus']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Auth & User Profile
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Rides Lifecycle
    Route::get('/rides/active', [RideController::class, 'active']);
    Route::post('/rides/{id}/status', [RideController::class, 'updateStatus']);
    Route::post('/rides/{id}/confirm-payment', [RideController::class, 'confirmPayment']);
    Route::post('/rides/{id}/cancel', [RideController::class, 'cancel']);
    Route::apiResource('rides', RideController::class);
    Route::apiResource('vehicles', VehicleController::class);

    // Driver Dispatch & Operations
    Route::post('/driver/location', [DriverApiController::class, 'updateLocation']);
    Route::post('/driver/toggle-availability', [DriverApiController::class, 'toggleAvailability']);
    Route::get('/driver/requests', [DriverApiController::class, 'pendingRequests']);
    Route::post('/driver/respond', [DriverApiController::class, 'respondToAssignment']);
    Route::post('/driver/verify-booking', [StripeVerificationController::class, 'driverRespond']);
    Route::get('/driver/pending-verifications', [StripeVerificationController::class, 'getPendingVerifications']);
    Route::get('/driver/active-rides', [DriverApiController::class, 'activeRides']);
    Route::get('/driver/earnings', [DriverApiController::class, 'earnings']);
    Route::post('/driver/profile', [DriverApiController::class, 'updateProfile']);
    Route::post('/driver/photo', [DriverApiController::class, 'uploadPhoto']);

    // Driver Bookings & Reviews
    Route::post('/drivers/book', [DriverApiController::class, 'bookDriver']);
    Route::post('/driver-bookings/{id}/review', [DriverApiController::class, 'submitReview']);

    // Notifications
    Route::get('/notifications', function (Request $request) {
        $user = $request->user();
        $notifications = \App\Models\UserNotification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(30)
            ->get()
            ->map(function ($n) {
                return [
                    'id' => $n->id,
                    'type' => $n->type,
                    'title' => $n->title,
                    'message' => $n->message,
                    'link' => $n->link,
                    'data' => $n->data,
                    'is_read' => (bool)$n->is_read,
                    'time_ago' => $n->created_at->diffForHumans(),
                    'created_at' => $n->created_at->toIso8601String(),
                ];
            });

        $unreadCount = \App\Models\UserNotification::where('user_id', $user->id)->where('is_read', false)->count();

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    });

    Route::post('/notifications/mark-read', function (Request $request) {
        $user = $request->user();
        $id = $request->input('id');
        if ($id) {
            \App\Models\UserNotification::where('user_id', $user->id)->where('id', $id)->update(['is_read' => true]);
        } else {
            \App\Models\UserNotification::where('user_id', $user->id)->update(['is_read' => true]);
        }
        return response()->json(['success' => true]);
    });

    // Banners & Categories Management
    Route::post('/banners', [BannerApiController::class, 'store']);
    Route::put('/banners/{id}', [BannerApiController::class, 'update']);
    Route::delete('/banners/{id}', [BannerApiController::class, 'destroy']);
    Route::post('/categories', [CategoryApiController::class, 'store']);
    Route::put('/categories/{id}', [CategoryApiController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryApiController::class, 'destroy']);
    Route::post('/products', [ProductApiController::class, 'store']);
    Route::put('/products/{id}', [ProductApiController::class, 'update']);
    Route::delete('/products/{id}', [ProductApiController::class, 'destroy']);

    // Activity Logs
    Route::get('/activity-logs', [DriverApiController::class, 'activityLogs']);
});
