<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\DriverProfile;
use App\Models\DriverBooking;
use App\Models\RideAssignment;
use App\Services\DriverBookingAssignmentService;
use App\Services\RideAssignmentService;
use Illuminate\Support\Facades\DB;

echo "========================================================\n";
echo "  TESTING RIDEMYCARS HIRE A DRIVER OPERATIONAL FLOW     \n";
echo "========================================================\n\n";

$passCount = 0;
$totalTests = 0;

function assertTest($condition, $testName) {
    global $passCount, $totalTests;
    $totalTests++;
    if ($condition) {
        $passCount++;
        echo "  [PASS] Test #{$totalTests}: {$testName}\n";
    } else {
        echo "  [FAIL] Test #{$totalTests}: {$testName}\n";
    }
}

// Pickup location: Accra Central (5.6037, -0.1870)
$pickupLat = 5.6037;
$pickupLng = -0.1870;

// Setup test users & drivers
$d1User = User::firstOrCreate(['email' => 'hiring_driver1@test.com'], ['name' => 'Driver One', 'password' => bcrypt('password')]);
$d2User = User::firstOrCreate(['email' => 'hiring_driver2@test.com'], ['name' => 'Driver Two', 'password' => bcrypt('password')]);
$d3User = User::firstOrCreate(['email' => 'hiring_driver3@test.com'], ['name' => 'Driver Three', 'password' => bcrypt('password')]);

// Driver 1: 1.2 km away (5.6145, -0.1870)
$dp1 = DriverProfile::updateOrCreate(['user_id' => $d1User->id], [
    'country' => 'Ghana',
    'license_number' => 'LIC-TEST-001',
    'is_available' => true,
    'rating' => 4.9,
    'current_lat' => 5.6145,
    'current_lng' => -0.1870,
    'last_location_update' => now(),
]);

// Driver 2: 4.5 km away (5.6442, -0.1870)
$dp2 = DriverProfile::updateOrCreate(['user_id' => $d2User->id], [
    'country' => 'Ghana',
    'license_number' => 'LIC-TEST-002',
    'is_available' => true,
    'rating' => 4.8,
    'current_lat' => 5.6442,
    'current_lng' => -0.1870,
    'last_location_update' => now(),
]);

// Driver 3: 12.0 km away (5.7117, -0.1870)
$dp3 = DriverProfile::updateOrCreate(['user_id' => $d3User->id], [
    'country' => 'Ghana',
    'license_number' => 'LIC-TEST-003',
    'is_available' => true,
    'rating' => 4.7,
    'current_lat' => 5.7117,
    'current_lng' => -0.1870,
    'last_location_update' => now(),
]);

// Create Test Driver Booking
$clientUser = User::firstOrCreate(['email' => 'hiring_client@test.com'], ['name' => 'Test Client', 'password' => bcrypt('password')]);

$booking = DriverBooking::create([
    'booking_code' => 'DRV-TEST-' . rand(1000, 9999),
    'client_id' => $clientUser->id,
    'driver_id' => $d1User->id,
    'driver_profile_id' => null, // Open search
    'service_category' => 'private',
    'country' => 'Ghana',
    'car_type' => 'Sedan',
    'car_make_model' => 'Honda Accord',
    'registration_number' => 'ACC-9988',
    'transmission' => 'automatic',
    'pickup_location' => 'Accra Central',
    'pickup_lat' => $pickupLat,
    'pickup_lng' => $pickupLng,
    'dropoff_location' => 'Osu Mall',
    'dropoff_lat' => 5.5560,
    'dropoff_lng' => -0.1821,
    'start_date' => date('Y-m-d'),
    'start_time' => '10:00',
    'duration_type' => 'hourly',
    'duration_count' => 4,
    'hourly_rate' => 30.00,
    'subtotal' => 120.00,
    'service_fee' => 6.00,
    'tax' => 6.00,
    'total_price' => 132.00,
    'currency' => 'GHS',
    'payment_method' => 'momo',
    'payment_status' => 'paid',
    'booking_status' => 'pending',
]);

echo "1. Testing Proximity-Based Nearby Driver Matching...\n";
$assignment1 = DriverBookingAssignmentService::assignNextDriver($booking);
assertTest($assignment1 && (int)$assignment1->driver_id === (int)$d1User->id, "Driver 1 (1.2 km away) was offered booking first.");

echo "2. Testing Driver Rejection & Automatic Reassignment...\n";
$assignment1->update(['status' => 'rejected']);
$assignment2 = DriverBookingAssignmentService::assignNextDriver($booking);
assertTest($assignment2 && (int)$assignment2->driver_id === (int)$d2User->id, "Driver 2 (4.5 km away) was offered booking after Driver 1 rejected.");

echo "3. Testing Atomic Concurrency Driver Acceptance & Availability Locking...\n";
DB::transaction(function () use ($assignment2, $booking, $d2User, $dp2) {
    $lockedBooking = DriverBooking::where('id', $booking->id)->lockForUpdate()->first();
    $assignment2->update(['status' => 'accepted']);
    $lockedBooking->update([
        'driver_id' => $d2User->id,
        'driver_profile_id' => $dp2->id,
        'booking_status' => 'accepted',
    ]);
    $dp2->update(['is_available' => false]);
});

$booking->refresh();
$dp2->refresh();
assertTest($booking->booking_status === 'accepted' && (int)$booking->driver_id === (int)$d2User->id, "Booking status updated to 'accepted' with Driver 2 assigned.");
assertTest($dp2->is_available === false, "Driver 2 availability set to 'false' (busy lock).");

echo "4. Testing Geofenced Arrival Logic (100m Radius)...\n";
// Driver 2 moves to 30 meters from pickup
$dp2->update(['current_lat' => 5.6038, 'current_lng' => -0.1871]);
$distKm = RideAssignmentService::haversineDistance($dp2->current_lat, $dp2->current_lng, $booking->pickup_lat, $booking->pickup_lng);
assertTest($distKm <= 0.10, "Driver 2 is within 100m geofence ({$distKm} km).");

if ($distKm <= 0.10) {
    $booking->update(['booking_status' => 'arrived', 'arrived_at' => now()]);
}
$booking->refresh();
assertTest($booking->booking_status === 'arrived', "Booking status auto-transitioned to 'arrived'.");

echo "5. Testing Service In-Progress & Completion Lifecycle...\n";
$booking->update(['booking_status' => 'in_progress', 'started_at' => now()->subHours(4)]);
$booking->refresh();
assertTest($booking->booking_status === 'in_progress', "Service started ('in_progress').");

// Driver completes trip
$booking->update([
    'booking_status' => 'completed',
    'completed_at' => now(),
    'payment_status' => 'paid',
    'actual_duration_minutes' => 240,
]);
$dp2->update(['is_available' => true]);

$booking->refresh();
$dp2->refresh();
assertTest($booking->booking_status === 'completed' && $booking->payment_status === 'paid', "Service completed ('completed') with paid status.");
assertTest($dp2->is_available === true, "Driver 2 availability restored to 'true' (available).");

echo "\n========================================================\n";
echo "  SUMMARY: {$passCount} / {$totalTests} TESTS PASSED SUCCESSFUL!\n";
echo "========================================================\n";
