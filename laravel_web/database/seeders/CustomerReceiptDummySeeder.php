<?php

namespace Database\Seeders;

use App\Models\DriverBooking;
use App\Models\DriverProfile;
use App\Models\PackageDelivery;
use App\Models\Receipt;
use App\Models\Ride;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\ReceiptService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CustomerReceiptDummySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure Target Customer User exists with specified password
        $customer = User::updateOrCreate(
            ['email' => 'shachisheh@gmail.com'],
            [
                'name' => 'Shachi Sheh',
                'password' => Hash::make('shachish21'),
                'phone' => '+1 202-555-0143',
                'country' => 'United States',
                'city' => 'New York',
                'role' => 'user',
                'account_status' => 'active',
                'membership_type' => 'Gold VIP Member',
                'membership_status' => 'active',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ]
        );

        // 2. Ensure Professional Driver / Courier User exists
        $driver = User::updateOrCreate(
            ['email' => 'kwame.driver@ridemycars.com'],
            [
                'name' => 'Kwame Mensah',
                'password' => Hash::make('DriverSecure2026!'),
                'phone' => '+233 24 555 7890',
                'country' => 'Ghana',
                'city' => 'Accra',
                'role' => 'driver',
                'account_status' => 'active',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ]
        );

        $driverProfile = DriverProfile::updateOrCreate(
            ['user_id' => $driver->id],
            [
                'rating' => 4.95,
                'total_trips' => 420,
                'license_number' => 'GHA-DL-883921',
                'is_available' => true,
                'is_live' => true,
                'kyc_status' => 'approved',
                'verification_status' => 'approved',
                'experience_years' => 7,
                'bio' => 'Certified executive VIP chauffeur and express delivery specialist with over 7 years of incident-free luxury driving across Greater Accra.',
            ]
        );

        // 3. Ensure Vehicles Exist
        $vehicleRide = Vehicle::updateOrCreate(
            ['license_plate' => 'GS-4527-26'],
            [
                'user_id' => $driver->id,
                'driver_id' => $driver->id,
                'make' => 'Toyota',
                'model' => 'Camry XSE',
                'year' => 2024,
                'color' => 'Silver Metallic',
                'type' => 'Sedan',
                'category' => 'Sedan',
                'status' => 'active',
                'image_url' => 'https://images.unsplash.com/photo-1621007947382-bb3c3994e3fb?auto=format&fit=crop&w=1200&q=80',
            ]
        );

        $vehicleRental = Vehicle::updateOrCreate(
            ['license_plate' => 'LUX-1111'],
            [
                'user_id' => $driver->id,
                'make' => 'Mercedes-Benz',
                'model' => 'GLC 300 4MATIC',
                'year' => 2024,
                'color' => 'Obsidian Black',
                'type' => 'Luxury SUV',
                'category' => 'SUV',
                'status' => 'active',
                'daily_rate' => 120.00,
                'image_url' => 'https://images.unsplash.com/photo-1617531653332-bd46c24f2068?auto=format&fit=crop&w=1200&q=80',
            ]
        );

        // ========================================================
        // FEATURE 1: RIDE (Standard Ride Hailing - Completed)
        // ========================================================
        $ride = Ride::where('rider_id', $customer->id)
            ->where('ride_type', 'ride')
            ->first();

        if (!$ride) {
            $ride = new Ride();
        }

        $ride->fill([
            'rider_id' => $customer->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicleRide->id,
            'ride_type' => 'ride',
            'status' => 'completed',
            'vehicle_type' => 'Sedan Comfort',
            'pickup_location' => 'Accra Mall, Tetteh Quarshie Interchange, Accra',
            'dropoff_location' => 'Kotoka International Airport (ACC), Terminal 3',
            'pickup_lat' => 5.6225,
            'pickup_lng' => -0.1748,
            'dropoff_lat' => 5.6052,
            'dropoff_lng' => -0.1668,
            'fare' => 48.50,
            'total_amount' => 48.50,
            'paid_amount' => 48.50,
            'payment_method' => 'Credit Card (Stripe)',
            'payment_status' => 'paid',
            'pickup_date' => now()->subDays(2)->toDateString(),
            'pickup_time' => '14:15',
            'started_at' => now()->subDays(2)->setTime(14, 15),
            'completed_at' => now()->subDays(2)->setTime(14, 45),
            'distance_km' => 12.8,
            'duration_minutes' => 30,
        ]);
        $ride->save();

        $receiptRide = ReceiptService::generateReceiptForRide($ride, false);

        // ========================================================
        // FEATURE 2: RENTAL (Car Rental - Completed)
        // ========================================================
        $rental = Ride::where('rider_id', $customer->id)
            ->where('ride_type', 'rental')
            ->first();

        if (!$rental) {
            $rental = new Ride();
        }

        $rental->fill([
            'rider_id' => $customer->id,
            'vehicle_id' => $vehicleRental->id,
            'ride_type' => 'rental',
            'status' => 'completed',
            'vehicle_type' => 'RENTAL_SUV_Luxury',
            'pickup_location' => 'RideMyCars Airport Hub, Kotoka International Airport',
            'dropoff_location' => 'RideMyCars Downtown Hub, Ring Road Central, Accra',
            'pickup_lat' => 5.6052,
            'pickup_lng' => -0.1668,
            'dropoff_lat' => 5.5780,
            'dropoff_lng' => -0.2012,
            'pickup_date' => now()->subDays(5)->toDateString(),
            'pickup_time' => '09:00',
            'return_date' => now()->subDays(2)->toDateString(),
            'return_time' => '18:00',
            'fare' => 280.00,
            'protection_fee' => 45.00,
            'extras_fee' => 15.00,
            'total_amount' => 340.00,
            'paid_amount' => 340.00,
            'fuel_policy' => 'Full to Full',
            'payment_method' => 'Credit Card (Mastercard ending 8812)',
            'payment_status' => 'paid',
            'started_at' => now()->subDays(5)->setTime(9, 0),
            'completed_at' => now()->subDays(2)->setTime(18, 0),
            'distance_km' => 320.0,
            'duration_minutes' => 4320, // 3 days
        ]);
        $rental->save();

        $receiptRental = ReceiptService::generateReceiptForRide($rental, false);

        // ========================================================
        // FEATURE 3: DRIVER BOOKING (Chauffeur - Completed)
        // ========================================================
        $chauffeur = DriverBooking::where('client_id', $customer->id)->first();
        if (!$chauffeur) {
            $chauffeur = new DriverBooking();
        }

        $chauffeur->fill([
            'booking_code' => $chauffeur->booking_code ?: ('CHF-' . rand(20000, 99999)),
            'client_id' => $customer->id,
            'driver_id' => $driver->id,
            'driver_profile_id' => $driverProfile->id,
            'vehicle_id' => $vehicleRide->id,
            'service_category' => 'daily',
            'service_type' => 'executive',
            'country' => 'Ghana',
            'car_type' => 'Luxury Executive Sedan',
            'car_make_model' => 'Mercedes-Benz E-Class 2024 Executive',
            'registration_number' => 'GS-4527-26',
            'transmission' => 'Automatic',
            'preferred_language' => 'English',
            'pickup_location' => 'Kempinski Hotel Gold Coast City, Accra',
            'dropoff_location' => 'Labadi Beach Resort & As Directed across Greater Accra',
            'start_date' => now()->subDays(6)->toDateString(),
            'start_time' => '08:30',
            'end_date' => now()->subDays(6)->toDateString(),
            'end_time' => '17:30',
            'duration_type' => 'day',
            'duration_count' => 1,
            'hourly_rate' => 25.00,
            'daily_rate' => 160.00,
            'subtotal' => 160.00,
            'service_fee' => 15.00,
            'tax' => 10.00,
            'total_price' => 185.00,
            'currency' => 'USD',
            'payment_method' => 'Credit Card (Stripe)',
            'payment_status' => 'paid',
            'booking_status' => 'completed',
            'escrow_status' => 'released',
            'actual_distance_km' => 84.5,
            'actual_duration_minutes' => 540,
            'completed_at' => now()->subDays(6)->setTime(17, 30),
        ]);
        $chauffeur->save();

        $receiptChauffeur = ReceiptService::generateReceiptForDriverBooking($chauffeur, false);

        // ========================================================
        // FEATURE 4: PACKAGE DELIVERY (Express Parcel - Delivered)
        // ========================================================
        $delivery = PackageDelivery::where('customer_id', $customer->id)->first();
        if (!$delivery) {
            $delivery = new PackageDelivery();
        }

        $delivery->fill([
            'delivery_code' => $delivery->delivery_code ?: ('DLV-' . rand(20000, 99999)),
            'customer_id' => $customer->id,
            'courier_id' => $driver->id,
            'courier_profile_id' => $driverProfile->id,
            'delivery_status' => 'delivered',
            'delivery_type' => 'express',
            'schedule_mode' => 'instant',
            'pickup_date' => now()->subDay()->toDateString(),
            'pickup_time' => '11:15',
            'pickup_location' => 'Ridge Ambassadorial Enclave, House 12, Accra',
            'pickup_lat' => 5.5780,
            'pickup_lng' => -0.1980,
            'dropoff_location' => 'East Legon Business District, Suite 402, Accra',
            'dropoff_lat' => 5.6350,
            'dropoff_lng' => -0.1550,
            'sender_name' => 'Shachi Sheh',
            'sender_phone' => '+1 202-555-0143',
            'sender_address' => 'Ridge Ambassadorial Enclave, House 12, Accra',
            'recipient_name' => 'Dr. Kwesi Appiah',
            'recipient_phone' => '+233 20 888 1234',
            'recipient_address' => 'East Legon Business District, Suite 402, Accra',
            'delivery_instructions' => 'Please hand over directly to Dr. Appiah and obtain signature.',
            'package_category' => 'Electronics & Legal Documents',
            'package_description' => 'Sealed Corporate Legal Contracts & Hardware Auth Token',
            'package_size' => 'medium',
            'package_weight_kg' => 2.4,
            'quantity' => 1,
            'declared_value' => 500.00,
            'subtotal' => 38.00,
            'service_fee' => 4.50,
            'tax' => 2.50,
            'total_price' => 45.00,
            'currency' => 'USD',
            'payment_method' => 'Credit Card',
            'payment_status' => 'paid',
            'pod_status' => 'signed',
            'pod_timestamp' => now()->subDay()->setTime(12, 10),
            'delivered_at' => now()->subDay()->setTime(12, 10),
        ]);
        $delivery->save();

        $receiptDelivery = ReceiptService::generateReceiptForPackageDelivery($delivery, false);
    }
}
