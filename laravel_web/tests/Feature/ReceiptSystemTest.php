<?php

namespace Tests\Feature;

use App\Models\DriverBooking;
use App\Models\PackageDelivery;
use App\Models\Receipt;
use App\Models\Ride;
use App\Models\User;
use App\Services\ReceiptService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReceiptSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Storage::fake('public');
    }

    public function test_can_generate_and_store_receipt_for_ride(): void
    {
        $rider = User::factory()->create(['name' => 'Shachiah Sneh', 'email' => 'shachiah@example.com']);
        $driver = User::factory()->create(['name' => 'Arun Pal Pal', 'role' => 'driver']);

        $ride = Ride::create([
            'rider_id' => $rider->id,
            'driver_id' => $driver->id,
            'pickup_location' => '3, opposite Aurobindo College, New Delhi',
            'dropoff_location' => '1/394, Block-A1, Nawada Majra, New Delhi',
            'distance_km' => 24.0,
            'duration_minutes' => 72,
            'fare' => 344.00,
            'total_amount' => 344.00,
            'subtotal' => 372.18,
            'discount' => 28.18,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'status' => 'completed',
            'ride_type' => 'ride',
        ]);

        $receipt = ReceiptService::generateReceiptForRide($ride, true);

        $this->assertNotNull($receipt);
        $this->assertDatabaseHas('receipts', [
            'id' => $receipt->id,
            'booking_type' => 'ride',
            'booking_id' => $ride->id,
            'user_id' => $rider->id,
            'total_amount' => 344.00,
            'payment_method' => 'cash',
        ]);

        $this->assertTrue($receipt->hasPdf());
        $this->assertNotNull($receipt->verification_token);
        $this->assertEquals($receipt->id, $ride->fresh()->receipt_id);
    }

    public function test_can_generate_receipt_for_driver_booking(): void
    {
        $client = User::factory()->create(['name' => 'Executive Client', 'email' => 'client@example.com']);
        $driver = User::factory()->create(['name' => 'John Chauffeur', 'role' => 'driver']);

        $booking = DriverBooking::create([
            'booking_code' => 'CHF-88991',
            'client_id' => $client->id,
            'driver_id' => $driver->id,
            'pickup_location' => 'Airport Terminal 3',
            'dropoff_location' => 'Financial District Plaza',
            'service_category' => 'hire-driver',
            'duration_type' => 'hourly',
            'duration_count' => 4,
            'start_date' => now()->toDateString(),
            'start_time' => '10:00',
            'total_price' => 180.00,
            'subtotal' => 160.00,
            'tax' => 20.00,
            'payment_method' => 'stripe',
            'payment_status' => 'paid',
            'booking_status' => 'completed',
        ]);

        $receipt = ReceiptService::generateReceiptForDriverBooking($booking, false);

        $this->assertNotNull($receipt);
        $this->assertEquals('driver_booking', $receipt->booking_type);
        $this->assertEquals(180.00, (float)$receipt->total_amount);
        $this->assertTrue($receipt->hasPdf());
    }

    public function test_can_generate_receipt_for_package_delivery(): void
    {
        $sender = User::factory()->create(['name' => 'Sender User', 'email' => 'sender@example.com']);

        $delivery = PackageDelivery::create([
            'delivery_code' => 'PKG-99211',
            'customer_id' => $sender->id,
            'sender_name' => 'Sender User',
            'sender_phone' => '+1 555-0123',
            'recipient_name' => 'Recipient Jane',
            'recipient_phone' => '+1 555-0199',
            'pickup_location' => 'Warehouse Bay 4',
            'dropoff_location' => '742 Evergreen Terrace',
            'package_category' => 'electronics',
            'package_size' => 'medium',
            'package_weight_kg' => 3.5,
            'quantity' => 1,
            'total_price' => 45.00,
            'subtotal' => 40.00,
            'service_fee' => 5.00,
            'payment_method' => 'card',
            'payment_status' => 'paid',
            'delivery_status' => 'delivered',
            'delivery_otp' => '4821',
        ]);

        $receipt = ReceiptService::generateReceiptForPackageDelivery($delivery, false);

        $this->assertNotNull($receipt);
        $this->assertEquals('delivery', $receipt->booking_type);
        $this->assertEquals(45.00, (float)$receipt->total_amount);
        $this->assertTrue($receipt->hasPdf());
    }

    public function test_customer_can_view_receipt_online(): void
    {
        $user = User::factory()->create(['name' => 'Alice Passenger', 'email' => 'alice@example.com']);

        $ride = Ride::create([
            'rider_id' => $user->id,
            'pickup_location' => 'Central Station',
            'dropoff_location' => 'Grand Hotel',
            'total_amount' => 55.00,
            'payment_method' => 'stripe',
            'payment_status' => 'paid',
            'status' => 'completed',
        ]);

        $receipt = ReceiptService::generateReceiptForRide($ride, false);

        $response = $this->get("/receipts/{$receipt->verification_token}");

        $response->assertStatus(200);
        $response->assertSee($receipt->receipt_number);
        $response->assertSee('Alice Passenger');
        $response->assertSee('Download PDF');
    }

    public function test_customer_can_download_receipt_pdf(): void
    {
        $user = User::factory()->create();

        $ride = Ride::create([
            'rider_id' => $user->id,
            'pickup_location' => 'Point A',
            'dropoff_location' => 'Point B',
            'total_amount' => 25.00,
            'payment_method' => 'stripe',
            'payment_status' => 'paid',
            'status' => 'completed',
        ]);

        $receipt = ReceiptService::generateReceiptForRide($ride, false);

        $response = $this->get("/receipts/{$receipt->verification_token}/download");

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_pdf_is_not_regenerated_if_already_present(): void
    {
        $user = User::factory()->create();

        $ride = Ride::create([
            'rider_id' => $user->id,
            'pickup_location' => 'Airport',
            'dropoff_location' => 'Downtown',
            'total_amount' => 80.00,
            'payment_method' => 'card',
            'payment_status' => 'paid',
            'status' => 'completed',
        ]);

        $receipt = ReceiptService::generateReceiptForRide($ride, false);
        $initialPdfPath = $receipt->pdf_path;
        $this->assertNotEmpty($initialPdfPath);

        // Call again without force:
        $freshReceipt = ReceiptService::generateReceiptForRide($ride, false);
        $this->assertEquals($receipt->id, $freshReceipt->id);
        $this->assertEquals($initialPdfPath, $freshReceipt->pdf_path);
    }
}
