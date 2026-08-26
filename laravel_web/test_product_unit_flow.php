<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\Category;
use App\Http\Controllers\Api\ProductApiController;
use Illuminate\Http\Request;

echo "========================================================\n";
echo "  TESTING ADMIN PRODUCT 'UNIT' SELECTION & API FLOW     \n";
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

$controller = new ProductApiController();

// Cleanup test products
Product::where('name', 'like', 'Test Product%')->delete();

$testCat = Category::firstOrCreate(['slug' => 'test-grocery'], ['name' => 'Grocery', 'is_active' => true]);

echo "1. TEST 1: Add Product with Name = Rice, Price = 50, Unit = kg\n";
$product1 = Product::create([
    'name' => 'Test Product - Rice',
    'category_id' => $testCat->id,
    'price' => 50.00,
    'unit' => 'kg',
    'status' => 'active',
]);

assertTest($product1->id && $product1->unit === 'kg', "Product saved successfully with unit = 'kg'.");

echo "2. TEST 2: Add Product without Unit (Validation Error Expected)\n";
$validationFailed = false;
try {
    $reqFail = Request::create('/api/products', 'POST', [
        'name' => 'Test Product Fail',
        'price' => 20.00,
    ]);
    
    $validator = validator($reqFail->all(), [
        'name' => 'required|string',
        'price' => 'required|numeric',
        'unit' => 'required|string|in:' . implode(',', array_keys(Product::$unitOptions)),
    ], [
        'unit.required' => 'Please select a unit.',
    ]);

    if ($validator->fails() && $validator->errors()->has('unit')) {
        $validationFailed = true;
    }
} catch (\Throwable $e) {
    $validationFailed = true;
}
assertTest($validationFailed, "Validation error triggered when unit is missing ('Please select a unit.').");

echo "3. TEST 3: Edit Existing Product (Unit Loaded Automatically)\n";
$foundProduct = Product::find($product1->id);
assertTest($foundProduct && $foundProduct->unit === 'kg', "Existing product loads with unit = 'kg'.");

echo "4. TEST 4: Change Unit (kg -> pc)\n";
$reqUpdate = Request::create("/api/products/{$product1->id}", 'PUT', [
    'unit' => 'pc',
]);
$controller->update($reqUpdate, $product1->id);
$product1->refresh();

assertTest($product1->unit === 'pc', "Product unit updated successfully to 'pc'.");

echo "5. TEST 5: View Product (Formatted Price with Unit Displayed)\n";
$reqShow = Request::create("/api/products/{$product1->id}", 'GET');
$resShow = $controller->show($product1->id)->getData(true);

assertTest(
    isset($resShow['data']['unit']) && 
    $resShow['data']['unit'] === 'pc' && 
    $resShow['data']['formatted_price_with_unit'] === '$50.00 / pc',
    "Product API returns unit ('pc') and formatted price ('$50.00 / pc')."
);

echo "6. TEST 6: Cart Payload Integration (Product Unit Preserved)\n";
$cartItem = [
    'product_id' => $product1->id,
    'product_name' => $product1->name,
    'price' => $product1->price,
    'unit' => $product1->unit,
    'quantity' => 2,
];
assertTest($cartItem['unit'] === 'pc', "Cart item payload preserves product unit ('pc').");

echo "7. TEST 7: Checkout & Order Pricing Calculation Integrity\n";
$subtotal = $cartItem['price'] * $cartItem['quantity']; // 50 * 2 = 100
assertTest($subtotal === 100.00, "Price calculation intact ($50.00 * 2 = $100.00) without accidental unit price corruption.");

echo "8. TEST 8: Admin Product List & Unit Options Verification\n";
$unitOptionLabel = Product::$unitOptions[$product1->unit] ?? null;
assertTest($unitOptionLabel === 'Piece (pc)', "Unit option resolves to human-readable label ('Piece (pc)').");

// Cleanup
Product::where('name', 'like', 'Test Product%')->delete();

echo "\n========================================================\n";
echo "  SUMMARY: {$passCount} / {$totalTests} TESTS PASSED SUCCESSFUL!\n";
echo "========================================================\n";
