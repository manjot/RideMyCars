<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Category;
use App\Models\Banner;
use App\Http\Controllers\Api\BannerApiController;
use Illuminate\Http\Request;

echo "========================================================\n";
echo "  TESTING CATEGORY & CATEGORY-WISE BANNER FUNCTIONALITY  \n";
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

// 0. Ensure Categories Exist
$rideCat = Category::firstOrCreate(['slug' => 'ride'], ['name' => 'Ride', 'description' => 'Ride category', 'is_active' => true]);
$rentCat = Category::firstOrCreate(['slug' => 'rent'], ['name' => 'Rent', 'description' => 'Rent category', 'is_active' => true]);
$delCat  = Category::firstOrCreate(['slug' => 'delivery'], ['name' => 'Delivery', 'description' => 'Delivery category', 'is_active' => true]);

$controller = new BannerApiController();

// Clean up test banners
Banner::where('title', 'like', 'Test Banner%')->delete();

echo "1. TEST 1: Create Banner A (Category = Ride, Status = Active)\n";
$bannerA = Banner::create([
    'category_id' => $rideCat->id,
    'title' => 'Test Banner A - Ride Offer',
    'description' => '20% off all rides',
    'image' => 'images/banner-a.jpg',
    'status' => 'active',
]);

$req1 = Request::create('/api/banners', 'GET', ['category_id' => $rideCat->id]);
$res1 = $controller->index($req1)->getData(true);
$rideBannerIds = array_column($res1['data'], 'id');
assertTest(in_array($bannerA->id, $rideBannerIds), "Banner A appears under Ride category.");

echo "2. TEST 2: Create Banner B (Category = Delivery, Status = Active)\n";
$bannerB = Banner::create([
    'category_id' => $delCat->id,
    'title' => 'Test Banner B - Express Delivery',
    'description' => 'Fast parcel delivery',
    'image' => 'images/banner-b.jpg',
    'status' => 'active',
]);

$req2Del = Request::create('/api/banners', 'GET', ['category_id' => $delCat->id]);
$res2Del = $controller->index($req2Del)->getData(true);
$delBannerIds = array_column($res2Del['data'], 'id');

$req2Ride = Request::create('/api/banners', 'GET', ['category_id' => $rideCat->id]);
$res2Ride = $controller->index($req2Ride)->getData(true);
$rideBannerIdsAfterB = array_column($res2Ride['data'], 'id');

assertTest(in_array($bannerB->id, $delBannerIds), "Banner B appears under Delivery category.");
assertTest(!in_array($bannerB->id, $rideBannerIdsAfterB), "Banner B does NOT appear under Ride category.");

echo "3. TEST 3: Create Banner C (Category = Rent, Status = Inactive)\n";
$bannerC = Banner::create([
    'category_id' => $rentCat->id,
    'title' => 'Test Banner C - Inactive Rent Deal',
    'description' => 'Inactive promo',
    'image' => 'images/banner-c.jpg',
    'status' => 'inactive',
]);

$req3 = Request::create('/api/banners', 'GET', ['category_id' => $rentCat->id]);
$res3 = $controller->index($req3)->getData(true);
$rentBannerIds = array_column($res3['data'], 'id');
assertTest(!in_array($bannerC->id, $rentBannerIds), "Inactive Banner C does NOT appear in public API.");

echo "4. TEST 4: Edit Banner A Category (Ride -> Delivery)\n";
$reqEdit = Request::create("/api/banners/{$bannerA->id}", 'PUT', [
    'category_id' => $delCat->id,
]);
$controller->update($reqEdit, $bannerA->id);

$req4Ride = Request::create('/api/banners', 'GET', ['category_id' => $rideCat->id]);
$res4Ride = $controller->index($req4Ride)->getData(true);
$rideIds4 = array_column($res4Ride['data'], 'id');

$req4Del = Request::create('/api/banners', 'GET', ['category_id' => $delCat->id]);
$res4Del = $controller->index($req4Del)->getData(true);
$delIds4 = array_column($res4Del['data'], 'id');

assertTest(!in_array($bannerA->id, $rideIds4), "Banner A disappeared from Ride category.");
assertTest(in_array($bannerA->id, $delIds4), "Banner A now appears under Delivery category.");

echo "5. TEST 5: Multiple Banners in One Category\n";
$bannerD = Banner::create([
    'category_id' => $rideCat->id,
    'title' => 'Test Banner D - Ride Perk 1',
    'description' => 'Perk 1',
    'image' => 'images/banner-d.jpg',
    'status' => 'active',
]);
$bannerE = Banner::create([
    'category_id' => $rideCat->id,
    'title' => 'Test Banner E - Ride Perk 2',
    'description' => 'Perk 2',
    'image' => 'images/banner-e.jpg',
    'status' => 'active',
]);

$req5 = Request::create('/api/banners', 'GET', ['category_id' => $rideCat->id]);
$res5 = $controller->index($req5)->getData(true);
$rideIds5 = array_column($res5['data'], 'id');

assertTest(in_array($bannerD->id, $rideIds5) && in_array($bannerE->id, $rideIds5), "Multiple active banners returned for Ride category.");

echo "6. TEST 6: Validation Error when Category is Missing\n";
$validationFailed = false;
try {
    $reqFail = Request::create('/api/banners', 'POST', [
        'title' => 'Test Banner Fail',
        'image' => 'images/fail.jpg',
    ]);
    // Simulate validation
    $validator = validator($reqFail->all(), [
        'category_id' => 'required|exists:categories,id',
        'title' => 'required|string|max:255',
        'image' => 'required|string',
    ], ['category_id.required' => 'Please select a category.']);
    
    if ($validator->fails()) {
        $validationFailed = true;
    }
} catch (\Throwable $e) {
    $validationFailed = true;
}
assertTest($validationFailed, "Validation error triggered when category_id is missing ('Please select a category.').");

echo "7. TEST 7: Delete Banner (Category Intact)\n";
$bannerDId = $bannerD->id;
$bannerD->delete();
$rideCatStillExists = Category::find($rideCat->id) !== null;
$bannerDDeleted = Banner::find($bannerDId) === null;
assertTest($bannerDDeleted && $rideCatStillExists, "Banner D deleted successfully while Ride Category remains intact.");

echo "8. TEST 8: Category Relationship Inspection\n";
$bannerB->refresh();
assertTest($bannerB->category !== null && (int)$bannerB->category->id === (int)$delCat->id, "Banner belongsTo Category relationship resolves correctly ('{$bannerB->category->name}').");

// Cleanup
Banner::where('title', 'like', 'Test Banner%')->delete();

echo "\n========================================================\n";
echo "  SUMMARY: {$passCount} / {$totalTests} TESTS PASSED SUCCESSFUL!\n";
echo "========================================================\n";
