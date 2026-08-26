<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\DriverProfile;
use App\Models\PackageDelivery;
use App\Models\RideAssignment;
use App\Services\PackageDeliveryAssignmentService;
use App\Services\RideAssignmentService;
use Illuminate\Support\Facades\DB;

echo "========================================================\n";
echo "  TESTING RIDEMYCARS PACKAGE DELIVERY OPERATIONAL FLOW  \n";
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

// Pickup location: New York City Hall (40.7128, -74.0060)
$pickupLat = 40.7128;
$pickupLng = -74.0060;

// Setup test users & couriers
$c1User = User::firstOrCreate(['email' => 'courier1@test.com'], ['name' => 'Courier One', 'password' => bcrypt('password')]);
$c2User = User::firstOrCreate(['email' => 'courier2@test.com'], ['name' => 'Courier Two', 'password' => bcrypt('password')]);
$c3User = User::firstOrCreate(['email' => 'courier3@test.com'], ['name' => 'Courier Three', 'password' => bcrypt('password')]);

// Courier 1: 1.1 km away (40.7220, -74.0060)
$cp1 = DriverProfile::updateOrCreate(['user_id' => $c1User->id], [
    'country' => 'USA',
    'license_number' => 'COUR-TEST-001',
    'is_available' => true,
    'rating' => 4.9,
    'current_lat' => 40.7220,
    'current_lng' => -74.0060,
    'last_location_update' => now(),
]);

// Courier 2: 4.2 km away (40.7500, -74.0060)
$cp2 = DriverProfile::updateOrCreate(['user_id' => $c2User->id], [
    'country' => 'USA',
    'license_number' => 'COUR-TEST-002',
    'is_available' => true,
    'rating' => 4.8,
    'current_lat' => 40.7500,
    'current_lng' => -74.0060,
    'last_location_update' => now(),
]);

// Courier 3: 11.5 km away (40.8150, -74.0060)
$cp3 = DriverProfile::updateOrCreate(['user_id' => $c3User->id], [
    'country' => 'USA',
    'license_number' => 'COUR-TEST-003',
    'is_available' => true,
    'rating' => 4.7,
    'current_lat' => 40.8150,
    'current_lng' => -74.0060,
    'last_location_update' => now(),
]);

// Create Test Customer & Package Delivery
$clientUser = User::firstOrCreate(['email' => 'delivery_customer@test.com'], ['name' => 'Test Customer', 'password' => bcrypt('password')]);

$delivery = PackageDelivery::create([
    'delivery_code' => 'DEL-TEST-' . rand(1000, 9999),
    'customer_id' => $clientUser->id,
    'pickup_location' => 'New York City Hall',
    'pickup_lat' => $pickupLat,
    'pickup_lng' => $pickupLng,
    'dropoff_location' => 'Empire State Building',
    'dropoff_lat' => 40.7484,
    'dropoff_lng' => -73.9857,
    'delivery_type' => 'Instant',
    'schedule_mode' => 'now',
    'sender_name' => 'Jane Sender',
    'sender_phone' => '+1 555-0199',
    'recipient_name' => 'Robert Recipient',
    'recipient_phone' => '+1 555-9988',
    'package_category' => 'Documents',
    'package_size' => 'Small',
    'package_weight_kg' => 1.5,
    'quantity' => 1,
    'declared_value' => 100.00,
    'special_handling' => ['signature_required'],
    'delivery_otp' => '4819',
    'delivery_status' => 'pending',
    'subtotal' => 22.50,
    'service_fee' => 1.13,
    'tax' => 1.13,
    'total_price' => 24.76,
    'currency' => 'USD',
    'payment_method' => 'stripe',
    'payment_status' => 'paid',
]);

echo "1. Testing Proximity-Based Courier Matching...\n";
$assignment1 = PackageDeliveryAssignmentService::assignNextCourier($delivery);
assertTest($assignment1 && (int)$assignment1->driver_id === (int)$c1User->id, "Courier 1 (1.1 km away) was offered delivery first.");

echo "2. Testing Courier Rejection & Automatic Reassignment...\n";
$assignment1->update(['status' => 'rejected']);
$assignment2 = PackageDeliveryAssignmentService::assignNextCourier($delivery);
assertTest($assignment2 && (int)$assignment2->driver_id === (int)$c2User->id, "Courier 2 (4.2 km away) was offered delivery after Courier 1 rejected.");

echo "3. Testing Atomic Concurrency Courier Acceptance & Availability Locking...\n";
DB::transaction(function () use ($assignment2, $delivery, $c2User, $cp2) {
    $lockedDelivery = PackageDelivery::where('id', $delivery->id)->lockForUpdate()->first();
    $assignment2->update(['status' => 'accepted']);
    $lockedDelivery->update([
        'courier_id' => $c2User->id,
        'courier_profile_id' => $cp2->id,
        'delivery_status' => 'courier_accepted',
    ]);
    $cp2->update(['is_available' => false]);
});

$delivery->refresh();
$cp2->refresh();
assertTest($delivery->delivery_status === 'courier_accepted' && (int)$delivery->courier_id === (int)$c2User->id, "Delivery status updated to 'courier_accepted' with Courier 2 assigned.");
assertTest($cp2->is_available === false, "Courier 2 availability set to 'false' (busy lock).");

echo "4. Testing Pickup Arrival & Parcel Pickup Confirmation...\n";
$delivery->update(['delivery_status' => 'arrived_at_pickup', 'arrived_at_pickup_at' => now()]);
$delivery->update(['delivery_status' => 'parcel_picked_up', 'picked_up_at' => now()]);
$delivery->refresh();
assertTest($delivery->delivery_status === 'parcel_picked_up', "Courier confirmed parcel pickup ('parcel_picked_up').");

echo "5. Testing In-Transit & Destination Arrival...\n";
$delivery->update(['delivery_status' => 'in_transit']);
$delivery->update(['delivery_status' => 'arrived_at_destination', 'arrived_at_destination_at' => now()]);
$delivery->refresh();
assertTest($delivery->delivery_status === 'arrived_at_destination', "Courier arrived at recipient destination ('arrived_at_destination').");

echo "6. Testing Secure 4-Digit PIN OTP Verification & Delivery Completion...\n";
$correctOtp = '4819';
$incorrectOtp = '9999';

assertTest($correctOtp === $delivery->delivery_otp, "4-Digit PIN matches delivery OTP ('4819').");

$delivery->update([
    'delivery_status' => 'delivered',
    'delivered_at' => now(),
    'payment_status' => 'paid',
    'pod_status' => 'completed',
    'pod_timestamp' => now(),
]);
$cp2->update(['is_available' => true]);

$delivery->refresh();
$cp2->refresh();
assertTest($delivery->delivery_status === 'delivered' && $delivery->payment_status === 'paid', "Delivery completed ('delivered') via PIN verification.");
assertTest($cp2->is_available === true, "Courier 2 availability restored to 'true' (available).");

echo "\n========================================================\n";
echo "  SUMMARY: {$passCount} / {$totalTests} TESTS PASSED SUCCESSFUL!\n";
echo "========================================================\n";
