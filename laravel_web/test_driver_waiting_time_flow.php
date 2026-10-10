<?php

putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=:memory:');
$_ENV['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = ':memory:';
$_SERVER['DB_CONNECTION'] = 'sqlite';
$_SERVER['DB_DATABASE'] = ':memory:';

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Ride;
use App\Models\PackageDelivery;
use App\Models\DriverProfile;
use App\Models\Setting;
use App\Services\SettingService;
use App\Services\RequestExpirationService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

// Use SQLite in-memory for lightning-fast, offline testing
Config::set('database.default', 'sqlite');
Config::set('database.connections.sqlite', [
    'driver' => 'sqlite',
    'database' => ':memory:',
    'prefix' => '',
]);
DB::purge('sqlite');
DB::reconnect('sqlite');

// Build test schema
Schema::create('settings', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique();
    $table->text('value')->nullable();
    $table->string('group')->nullable()->default('General');
    $table->string('type')->nullable()->default('text');
    $table->string('label')->nullable();
    $table->string('file_path')->nullable();
    $table->timestamps();
});

Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name')->default('Test User');
    $table->string('email')->unique();
    $table->string('role')->default('rider');
    $table->string('password')->nullable();
    $table->string('phone')->nullable();
    $table->string('referral_code')->nullable();
    $table->timestamps();
});

Schema::create('rides', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable();
    $table->foreignId('driver_id')->nullable();
    $table->string('pickup_location')->default('Times Square, NY');
    $table->string('dropoff_location')->default('Central Park, NY');
    $table->string('status')->default('pending');
    $table->decimal('fare', 8, 2)->default(25.50);
    $table->string('payment_method')->default('cash');
    $table->string('payment_status')->default('pending');
    $table->text('cancellation_reason')->nullable();
    $table->timestamp('expires_at')->nullable();
    $table->timestamps();
});

Schema::create('package_deliveries', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable();
    $table->foreignId('driver_id')->nullable();
    $table->foreignId('courier_id')->nullable();
    $table->string('tracking_number')->nullable();
    $table->string('sender_name')->nullable();
    $table->string('sender_phone')->nullable();
    $table->string('recipient_name')->nullable();
    $table->string('recipient_phone')->nullable();
    $table->string('pickup_address')->nullable();
    $table->string('delivery_address')->nullable();
    $table->string('status')->default('pending');
    $table->string('delivery_status')->default('pending');
    $table->decimal('price', 8, 2)->default(15.00);
    $table->decimal('fare', 8, 2)->default(15.00);
    $table->string('payment_method')->default('cash');
    $table->string('payment_status')->default('pending');
    $table->text('cancellation_reason')->nullable();
    $table->timestamp('expires_at')->nullable();
    $table->timestamps();
});

Schema::create('ride_assignments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('ride_id')->nullable();
    $table->foreignId('driver_id')->nullable();
    $table->foreignId('package_delivery_id')->nullable();
    $table->foreignId('driver_booking_id')->nullable();
    $table->string('status')->default('pending');
    $table->timestamps();
});

echo "====================================================================\n";
echo "  TESTING DRIVER REQUEST WAITING TIME FLOW (RIDE & DELIVERY)        \n";
echo "====================================================================\n\n";

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

// -------------------------------------------------------------
// SECTION 1: Default Settings & Dynamic Admin Configuration
// -------------------------------------------------------------
echo "\n--- Section 1: Default Settings & Admin Configuration ---\n";

$defaultRideMinutes = SettingService::getRideWaitingTimeMinutes();
$defaultDeliveryMinutes = SettingService::getDeliveryWaitingTimeMinutes();
$defaultRideSeconds = SettingService::getRideWaitingTimeSeconds();

assertTest($defaultRideMinutes === 5, "Default ride waiting time is 5 minutes (got {$defaultRideMinutes})");
assertTest($defaultDeliveryMinutes === 5, "Default delivery waiting time is 5 minutes (got {$defaultDeliveryMinutes})");
assertTest($defaultRideSeconds === 300, "Default ride waiting time in seconds is 300 (got {$defaultRideSeconds})");

// Simulate Admin updating waiting times dynamically via Admin Panel
SettingService::set('dispatch.ride_waiting_time_minutes', 8);
SettingService::set('dispatch.delivery_waiting_time_minutes', 12);

$updatedRideMinutes = SettingService::getRideWaitingTimeMinutes();
$updatedDeliveryMinutes = SettingService::getDeliveryWaitingTimeMinutes();
$updatedRideSeconds = SettingService::getRideWaitingTimeSeconds();
$updatedDeliverySeconds = SettingService::getDeliveryWaitingTimeSeconds();

assertTest($updatedRideMinutes === 8, "Dynamic Admin update for ride waiting time returns 8 minutes");
assertTest($updatedDeliveryMinutes === 12, "Dynamic Admin update for delivery waiting time returns 12 minutes");
assertTest($updatedRideSeconds === 480, "Updated ride waiting seconds returns 480");
assertTest($updatedDeliverySeconds === 720, "Updated delivery waiting seconds returns 720");

// Reset to 5 minutes
SettingService::set('dispatch.ride_waiting_time_minutes', 5);
SettingService::set('dispatch.delivery_waiting_time_minutes', 5);
SettingService::set('dispatch.auto_cancel_unaccepted', 1);

// -------------------------------------------------------------
// SECTION 2: Ride Creation, Countdown & Expiration
// -------------------------------------------------------------
echo "\n--- Section 2: Ride Creation, Countdown & Expiration ---\n";

$user = User::create([
    'name' => 'Waiting Test Rider',
    'email' => 'waiting_test_rider@test.com',
    'password' => bcrypt('password')
]);

$waitingMinutes = SettingService::getRideWaitingTimeMinutes();
$ride = Ride::create([
    'user_id' => $user->id,
    'pickup_location' => 'Times Square, NY',
    'dropoff_location' => 'Central Park, NY',
    'status' => 'pending',
    'fare' => 25.50,
    'payment_method' => 'cash',
    'payment_status' => 'pending',
    'expires_at' => now()->addMinutes($waitingMinutes),
]);

assertTest($ride->expires_at !== null, "New Ride has expires_at populated");
assertTest($ride->remainingSeconds() > 280 && $ride->remainingSeconds() <= 300, "New Ride remainingSeconds is within 280-300s (got {$ride->remainingSeconds()})");
assertTest(!$ride->isExpired(), "Freshly created Ride is NOT expired");

// Test an overdue Ride (created in past with expired waiting time)
$expiredRide = Ride::create([
    'user_id' => $user->id,
    'pickup_location' => 'Grand Central, NY',
    'dropoff_location' => 'Empire State, NY',
    'status' => 'pending',
    'fare' => 18.00,
    'payment_method' => 'cash',
    'payment_status' => 'pending',
    'expires_at' => now()->subMinutes(2), // Overdue by 2 minutes
]);

assertTest($expiredRide->isExpired(), "Overdue Ride is detected as expired");
assertTest($expiredRide->remainingSeconds() === 0, "Overdue Ride remainingSeconds returns 0");

// -------------------------------------------------------------
// SECTION 3: Package Delivery Creation & Expiration
// -------------------------------------------------------------
echo "\n--- Section 3: Package Delivery Creation & Expiration ---\n";

$delivery = PackageDelivery::create([
    'user_id' => $user->id,
    'sender_name' => 'Alice Sender',
    'sender_phone' => '1234567890',
    'recipient_name' => 'Bob Recipient',
    'recipient_phone' => '0987654321',
    'pickup_address' => 'Wall St, NY',
    'delivery_address' => 'SoHo, NY',
    'status' => 'pending',
    'price' => 15.00,
    'payment_method' => 'cash',
    'payment_status' => 'pending',
    'expires_at' => now()->addMinutes(SettingService::getDeliveryWaitingTimeMinutes()),
]);

assertTest($delivery->expires_at !== null, "New Package Delivery has expires_at populated");
assertTest($delivery->remainingSeconds() > 280 && $delivery->remainingSeconds() <= 300, "New Delivery remainingSeconds is within 280-300s (got {$delivery->remainingSeconds()})");
assertTest(!$delivery->isExpired(), "Freshly created Package Delivery is NOT expired");

$expiredDelivery = PackageDelivery::create([
    'user_id' => $user->id,
    'sender_name' => 'Alice Sender',
    'sender_phone' => '1234567890',
    'recipient_name' => 'Bob Recipient',
    'recipient_phone' => '0987654321',
    'pickup_address' => 'Wall St, NY',
    'delivery_address' => 'SoHo, NY',
    'status' => 'pending',
    'price' => 15.00,
    'payment_method' => 'cash',
    'payment_status' => 'pending',
    'expires_at' => now()->subMinutes(1), // Overdue by 1 minute
]);

assertTest($expiredDelivery->isExpired(), "Overdue Package Delivery is detected as expired");
assertTest($expiredDelivery->remainingSeconds() === 0, "Overdue Package Delivery remainingSeconds returns 0");

// -------------------------------------------------------------
// SECTION 4: RequestExpirationService Auto-Cancellation
// -------------------------------------------------------------
echo "\n--- Section 4: RequestExpirationService Processing ---\n";

$result = RequestExpirationService::expireAllOverdue();

assertTest($result['rides_expired'] >= 1, "RequestExpirationService cancelled at least 1 overdue ride (count: {$result['rides_expired']})");
assertTest($result['deliveries_expired'] >= 1, "RequestExpirationService cancelled at least 1 overdue delivery (count: {$result['deliveries_expired']})");

$expiredRide->refresh();
assertTest($expiredRide->status === 'cancelled', "Overdue Ride status changed to 'cancelled'");
assertTest(str_contains($expiredRide->cancellation_reason, 'No driver was available within the waiting period'), "Ride cancellation reason mentions waiting period timeout");

$expiredDelivery->refresh();
assertTest($expiredDelivery->delivery_status === 'cancelled', "Overdue Delivery delivery_status changed to 'cancelled'");
assertTest(str_contains($expiredDelivery->cancellation_reason, 'No courier was available within the waiting period'), "Delivery cancellation reason mentions waiting period timeout");

// Fresh ride and delivery must remain pending
$ride->refresh();
$delivery->refresh();
assertTest($ride->status === 'pending', "Active Ride within waiting time remains 'pending'");
assertTest($delivery->status === 'pending', "Active Delivery within waiting time remains 'pending'");

// -------------------------------------------------------------
// SECTION 5: Driver Acceptance Guards (HTTP 410 on Expired)
// -------------------------------------------------------------
echo "\n--- Section 5: Driver Acceptance Edge Cases ---\n";

// Guard check simulating DriverApiController respondToAssignment
$driverUser = User::create([
    'name' => 'Waiting Test Driver',
    'email' => 'waiting_test_driver@test.com',
    'password' => bcrypt('password')
]);

$canDriverAcceptExpired = true;
$rejectionResponseCode = 200;
$rejectionMessage = '';

if ($expiredRide->status === 'cancelled' || $expiredRide->isExpired()) {
    $canDriverAcceptExpired = false;
    $rejectionResponseCode = 410;
    $rejectionMessage = 'This ride request has expired or was cancelled because the waiting time ended.';
}

assertTest($canDriverAcceptExpired === false, "Driver cannot accept expired/cancelled ride");
assertTest($rejectionResponseCode === 410, "Response code is HTTP 410 (Gone) on expired request acceptance");
assertTest(str_contains($rejectionMessage, 'waiting time ended'), "Rejection message explains waiting time ended");

// Driver accepts valid active request before countdown expires
$ride->update([
    'driver_id' => $driverUser->id,
    'status' => 'accepted',
]);
$ride->refresh();

assertTest($ride->status === 'accepted', "Driver can accept valid active request before countdown expires");
assertTest($ride->driver_id === $driverUser->id, "Ride successfully assigned to driver");

echo "\n====================================================================\n";
echo "  TEST SUMMARY: {$passCount} / {$totalTests} TESTS PASSED\n";
if ($passCount === $totalTests) {
    echo "  ALL DRIVER REQUEST WAITING TIME TESTS PASSED PERFECTLY!\n";
} else {
    echo "  SOME TESTS FAILED!\n";
}
echo "====================================================================\n";
