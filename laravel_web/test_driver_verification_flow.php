<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DriverBooking;
use App\Models\User;
use App\Services\StripeService;
use App\Http\Controllers\StripeVerificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

echo "========================================================\n";
echo "  TESTING DRIVER VERIFICATION & STRIPE PAYMENT FLOW    \n";
echo "========================================================\n\n";

$passCount = 0;
$totalTests = 0;

function assertTest(bool $condition, string $description) {
    global $passCount, $totalTests;
    $totalTests++;
    if ($condition) {
        $passCount++;
        echo "  [PASS] Test #{$totalTests}: {$description}\n";
    } else {
        echo "  [FAIL] Test #{$totalTests}: {$description}\n";
    }
}

try {
    $client = User::first() ?? User::create(['name' => 'Test Client', 'email' => 'client@test.com', 'password' => bcrypt('password')]);
    $driver = User::where('id', '!=', $client->id)->first() ?? User::create(['name' => 'Test Driver', 'email' => 'driver@test.com', 'password' => bcrypt('password')]);

    // 1. Create a DriverBooking with payment_method = 'stripe'
    $booking = DriverBooking::create([
        'booking_code' => 'TEST-VERIF-' . strtoupper(Str::random(6)),
        'client_id' => $client->id,
        'driver_id' => $driver->id,
        'service_category' => 'private',
        'country' => 'USA',
        'pickup_location' => '100 Broadway, NY',
        'dropoff_location' => 'JFK International Airport',
        'start_date' => date('Y-m-d'),
        'start_time' => '10:00',
        'duration_type' => 'hourly',
        'duration_count' => 2,
        'total_price' => 75.00,
        'currency' => 'USD',
        'payment_method' => 'stripe',
        'payment_status' => 'pending',
        'verification_status' => 'pending_verification',
        'booking_status' => 'pending',
    ]);

    assertTest($booking->verification_status === 'pending_verification', "New booking initialized with verification_status = 'pending_verification'");
    assertTest($booking->payment_status === 'pending', "New booking initialized with payment_status = 'pending'");

    // 2. Test Stripe PaymentIntent creation before Driver Verification (Must fail as per security rule)
    $intentBlocked = false;
    try {
        StripeService::createPaymentIntent('driver_booking', $booking->id, $client->id);
    } catch (\InvalidArgumentException $e) {
        if (str_contains($e->getMessage(), 'Driver verification is required')) {
            $intentBlocked = true;
        }
    }
    assertTest($intentBlocked, "Stripe PaymentIntent creation is blocked while verification_status = 'pending_verification'");

    // 3. Driver Approves Booking
    Auth::login($driver);
    $controller = new StripeVerificationController();
    $reqApprove = new Request([
        'service_type' => 'driver_booking',
        'service_id' => $booking->id,
        'action' => 'approve',
    ]);

    $resApprove = $controller->driverRespond($reqApprove);
    $booking->refresh();

    assertTest($booking->verification_status === 'driver_verified', "Driver approval updates verification_status to 'driver_verified'");
    assertTest($booking->verified_by_driver_id === $driver->id, "Driver ID #{$driver->id} recorded in verified_by_driver_id");
    assertTest($booking->verified_at !== null, "Verification timestamp recorded in verified_at");

    // 4. Test Stripe PaymentIntent creation AFTER Driver Approval (Must succeed)
    $intentData = StripeService::createPaymentIntent('driver_booking', $booking->id, $client->id);
    assertTest(!empty($intentData['client_secret']), "Stripe PaymentIntent created successfully after driver approval");

    // 5. Test Driver Rejection Flow
    $bookingReject = DriverBooking::create([
        'booking_code' => 'TEST-REJ-' . strtoupper(Str::random(6)),
        'client_id' => $client->id,
        'driver_id' => $driver->id,
        'service_category' => 'private',
        'country' => 'USA',
        'pickup_location' => '500 5th Ave, NY',
        'dropoff_location' => 'LaGuardia Airport',
        'start_date' => date('Y-m-d'),
        'start_time' => '14:00',
        'duration_type' => 'hourly',
        'duration_count' => 1,
        'total_price' => 50.00,
        'currency' => 'USD',
        'payment_method' => 'stripe',
        'payment_status' => 'pending',
        'verification_status' => 'pending_verification',
        'booking_status' => 'pending',
    ]);

    $reqReject = new Request([
        'service_type' => 'driver_booking',
        'service_id' => $bookingReject->id,
        'action' => 'reject',
        'rejection_reason' => 'Schedule conflict with existing ride',
    ]);

    $resReject = $controller->driverRespond($reqReject);
    $bookingReject->refresh();

    assertTest($bookingReject->verification_status === 'rejected', "Driver rejection updates verification_status to 'rejected'");
    assertTest($bookingReject->rejection_reason === 'Schedule conflict with existing ride', "Rejection reason saved correctly");

    // 6. Test PaymentIntent creation on Rejected Booking (Must fail)
    $intentBlockedReject = false;
    try {
        StripeService::createPaymentIntent('driver_booking', $bookingReject->id, $client->id);
    } catch (\InvalidArgumentException $e) {
        if (str_contains($e->getMessage(), 'Driver verification is required')) {
            $intentBlockedReject = true;
        }
    }
    assertTest($intentBlockedReject, "Stripe PaymentIntent creation blocked on rejected booking");

} catch (\Throwable $e) {
    echo "ERROR EXCEPTION: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

echo "\n========================================================\n";
echo "  SUMMARY: {$passCount} / {$totalTests} TESTS PASSED SUCCESSFUL!\n";
echo "========================================================\n";
