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
use App\Http\Controllers\Api\RideController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

echo "========================================================\n";
echo "  TESTING 4-DIGIT SECURE PIN RIDE VERIFICATION FLOW     \n";
echo "========================================================\n\n";

// Use SQLite in-memory for lightning-fast, offline testing
Config::set('database.default', 'sqlite');
Config::set('database.connections.sqlite', [
    'driver' => 'sqlite',
    'database' => ':memory:',
    'prefix' => '',
]);
DB::purge('sqlite');
DB::reconnect('sqlite');

// Build minimal schema
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name')->default('Test Rider');
    $table->string('email')->unique();
    $table->string('role')->default('rider');
    $table->string('referral_code')->nullable();
    $table->string('password')->nullable();
    $table->string('phone')->nullable();
    $table->timestamps();
});

Schema::create('drivers', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable();
    $table->string('status')->default('active');
    $table->timestamps();
});

Schema::create('driver_profiles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable();
    $table->boolean('is_available')->default(true);
    $table->boolean('is_live')->default(true);
    $table->integer('total_trips')->default(0);
    $table->timestamp('last_location_update')->nullable();
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

Schema::create('package_deliveries', function (Blueprint $table) {
    $table->id();
    $table->foreignId('courier_id')->nullable();
    $table->string('delivery_status')->default('pending');
    $table->timestamps();
});

Schema::create('driver_bookings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('driver_id')->nullable();
    $table->string('booking_status')->default('pending');
    $table->timestamps();
});

Schema::create('rides', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable();
    $table->foreignId('driver_id')->nullable();
    $table->string('pickup_location')->default('Central Park, NYC');
    $table->string('dropoff_location')->default('Times Square, NYC');
    $table->decimal('pickup_lat', 10, 7)->default(40.785091);
    $table->decimal('pickup_lng', 10, 7)->default(-73.968285);
    $table->decimal('dropoff_lat', 10, 7)->default(40.758896);
    $table->decimal('dropoff_lng', 10, 7)->default(-73.985130);
    $table->decimal('fare', 8, 2)->default(25.50);
    $table->string('status')->default('pending');
    $table->string('start_pin', 6)->nullable();
    $table->timestamp('started_at')->nullable();
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();
});

Schema::create('ride_stops', function (Blueprint $table) {
    $table->id();
    $table->foreignId('ride_id')->nullable();
    $table->integer('stop_order')->default(1);
    $table->string('location')->nullable();
    $table->timestamps();
});

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

// 1. Create a user and driver
$rider = User::create(['name' => 'John Customer', 'email' => 'customer@test.com', 'role' => 'rider']);
$driverUser = User::create(['name' => 'Speedy Driver', 'email' => 'driver@test.com', 'role' => 'driver']);
$driver = DB::table('drivers')->insertGetId(['user_id' => $driverUser->id, 'status' => 'active']);
DB::table('driver_profiles')->insert([
    'user_id' => $driverUser->id,
    'is_available' => true,
    'is_live' => true,
    'last_location_update' => now(),
]);

assertTest($rider->id && $driver, 'Rider and Driver initialized in test database');

// 2. Test PIN Generation in Store / Ride Model
$controller = new RideController();

$ride = Ride::create([
    'user_id' => $rider->id,
    'pickup_location' => 'Central Park',
    'dropoff_location' => 'Times Square',
    'fare' => 28.00,
    'status' => 'pending',
    'start_pin' => '5432',
]);

assertTest($ride->start_pin === '5432', "Ride correctly persisted with 4-digit PIN '5432'");

// 3. Test Accessor Fallback for legacy rides where start_pin is null in DB
$legacyRide = new Ride();
$legacyRide->id = 99;
$legacyRide->user_id = $rider->id;
$legacyRide->status = 'pending';
$legacyRide->start_pin = null; // simulate null in DB
$fallbackPin = $legacyRide->start_pin;
assertTest(strlen($fallbackPin) === 4 && ctype_digit($fallbackPin), "Legacy null start_pin automatically resolves to a 4-digit PIN: '{$fallbackPin}'");

// 4. Update ride to accepted & arrived
$ride->update(['driver_id' => $driver, 'status' => 'arrived']);
assertTest($ride->status === 'arrived', "Driver arrives at pickup; status is 'arrived'");

// 5. Driver attempts to start trip (in_progress) WITHOUT PIN
$noPinRequest = Request::create("/api/driver/rides/{$ride->id}/status", 'POST', [
    'status' => 'in_progress',
]);
$res1 = $controller->updateStatus($noPinRequest, $ride->id);
$json1 = json_decode($res1->getContent(), true);

assertTest($res1->getStatusCode() === 422, "Starting trip without PIN returns HTTP 422 Unprocessable Entity");
assertTest(isset($json1['requires_pin']) && $json1['requires_pin'] === true, "Response payload flags 'requires_pin: true'");
$ride->refresh();
assertTest($ride->status === 'arrived', "Trip status remains 'arrived' (prevented from starting)");

// 6. Driver attempts to start trip with WRONG PIN
$wrongPinRequest = Request::create("/api/driver/rides/{$ride->id}/status", 'POST', [
    'status' => 'in_progress',
    'pin' => '9999',
]);
$res2 = $controller->updateStatus($wrongPinRequest, $ride->id);
$json2 = json_decode($res2->getContent(), true);

assertTest($res2->getStatusCode() === 422, "Starting trip with wrong PIN ('9999') returns HTTP 422");
assertTest(isset($json2['requires_pin']) && $json2['requires_pin'] === true, "Response payload indicates invalid PIN and requires correct PIN");
$ride->refresh();
assertTest($ride->status === 'arrived', "Trip status still guarded at 'arrived'");

// 7. Driver attempts to start trip with CORRECT PIN ('5432')
$correctPinRequest = Request::create("/api/driver/rides/{$ride->id}/status", 'POST', [
    'status' => 'in_progress',
    'pin' => '5432',
]);
$res3 = $controller->updateStatus($correctPinRequest, $ride->id);
$json3 = json_decode($res3->getContent(), true);

assertTest($res3->getStatusCode() === 200, "Starting trip with correct PIN ('5432') returns HTTP 200 OK");
$ride->refresh();
assertTest($ride->status === 'in_progress', "Trip status successfully transitioned to 'in_progress'");

// 8. Test Dedicated Verify PIN Endpoint (/rides/{id}/verify-pin)
$ride2 = Ride::create([
    'user_id' => $rider->id,
    'driver_id' => $driver,
    'status' => 'arrived',
    'start_pin' => '7890',
]);

$dedicatedWrong = Request::create("/api/rides/{$ride2->id}/verify-pin", 'POST', ['pin' => '1234']);
$res4 = $controller->verifyPin($dedicatedWrong, $ride2->id);
assertTest($res4->getStatusCode() === 422, "Dedicated verifyPin with incorrect PIN returns HTTP 422");

$dedicatedCorrect = Request::create("/api/rides/{$ride2->id}/verify-pin", 'POST', ['pin' => '7890']);
$res5 = $controller->verifyPin($dedicatedCorrect, $ride2->id);
$json5 = json_decode($res5->getContent(), true);

assertTest($res5->getStatusCode() === 200, "Dedicated verifyPin with correct PIN returns HTTP 200");
$ride2->refresh();
assertTest($ride2->status === 'in_progress', "Dedicated verifyPin advanced ride to 'in_progress'");

echo "\n--------------------------------------------------------\n";
echo "SUMMARY: Passed {$passCount} of {$totalTests} tests.\n";
if ($passCount === $totalTests) {
    echo "STATUS: ALL 4-DIGIT SECURE PIN FLOW TESTS PASSED SUCCESSFULLY! ✓\n";
} else {
    echo "STATUS: SOME TESTS FAILED.\n";
}
echo "========================================================\n";
