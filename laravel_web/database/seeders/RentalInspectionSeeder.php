<?php

namespace Database\Seeders;

use App\Models\DriverBooking;
use App\Models\RentalInspection;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class RentalInspectionSeeder extends Seeder
{
    public function run(): void
    {
        $vehicle1 = Vehicle::firstOrCreate(
            ['license_plate' => 'ABC-1234'],
            ['make' => 'Toyota', 'model' => 'Camry', 'year' => '2023', 'type' => 'Midsize', 'daily_rate' => 45.00, 'is_available' => true]
        );

        $vehicle2 = Vehicle::firstOrCreate(
            ['license_plate' => 'XYZ-5678'],
            ['make' => 'Honda', 'model' => 'Civic', 'year' => '2022', 'type' => 'Compact', 'daily_rate' => 35.00, 'is_available' => true]
        );

        $vehicle3 = Vehicle::firstOrCreate(
            ['license_plate' => 'LMN-9101'],
            ['make' => 'Ford', 'model' => 'Explorer', 'year' => '2024', 'type' => 'SUV', 'daily_rate' => 75.00, 'is_available' => true]
        );

        $vehicle4 = Vehicle::firstOrCreate(
            ['license_plate' => 'LUX-1111'],
            ['make' => 'Mercedes-Benz', 'model' => 'E-Class', 'year' => '2023', 'type' => 'Luxury', 'daily_rate' => 120.00, 'is_available' => true]
        );

        $booking1 = DriverBooking::firstOrCreate(
            ['booking_code' => 'RMC-BK-9011'],
            [
                'client_id' => 1,
                'driver_id' => 1,
                'country' => 'Ghana',
                'service_category' => 'private',
                'car_type' => 'Toyota Camry',
                'registration_number' => 'ABC-1234',
                'transmission' => 'automatic',
                'start_date' => now()->subDays(3)->toDateString(),
                'start_time' => '09:00',
                'duration_type' => 'day',
                'duration_count' => 3,
                'pickup_location' => 'Airport Residential Area, Accra',
                'booking_status' => 'completed',
                'payment_status' => 'paid',
                'payment_method' => 'momo',
                'subtotal' => 360.00,
                'service_fee' => 40.00,
                'tax' => 10.00,
                'total_price' => 410.00,
                'currency' => 'GHS',
                'vehicle_id' => $vehicle1->id,
            ]
        );

        $booking2 = DriverBooking::firstOrCreate(
            ['booking_code' => 'RMC-BK-9022'],
            [
                'client_id' => 1,
                'driver_id' => 2,
                'country' => 'Ghana',
                'service_category' => 'private',
                'car_type' => 'Honda Civic',
                'registration_number' => 'XYZ-5678',
                'transmission' => 'automatic',
                'start_date' => now()->subDays(1)->toDateString(),
                'start_time' => '10:00',
                'duration_type' => 'day',
                'duration_count' => 2,
                'pickup_location' => 'East Legon, Accra',
                'booking_status' => 'accepted',
                'payment_status' => 'paid',
                'payment_method' => 'card',
                'subtotal' => 240.00,
                'service_fee' => 30.00,
                'tax' => 5.00,
                'total_price' => 275.00,
                'currency' => 'GHS',
                'vehicle_id' => $vehicle2->id,
            ]
        );

        $inspections = [
            // 1. Pre-Rental Booking 1
            [
                'driver_booking_id' => $booking1->id,
                'vehicle_id' => $vehicle1->id,
                'inspection_type' => 'pre_rental',
                'front_photo_url' => 'inspections/front_pre.jpg',
                'back_photo_url' => 'inspections/back_pre.jpg',
                'left_photo_url' => 'inspections/left_pre.jpg',
                'right_photo_url' => 'inspections/right_pre.jpg',
                'dashboard_photo_url' => 'inspections/dashboard_pre.jpg',
                'fuel_gauge_photo_url' => 'inspections/fuel_pre.jpg',
                'odometer_reading' => 45200,
                'fuel_level' => '100% (Full)',
                'notes' => 'Pre-rental inspection clear. All 6 mandatory photos verified. Vehicle clean, no pre-existing scratches.',
                'created_at' => now()->subDays(3),
            ],
            // 2. Post-Rental Booking 1
            [
                'driver_booking_id' => $booking1->id,
                'vehicle_id' => $vehicle1->id,
                'inspection_type' => 'post_rental',
                'front_photo_url' => 'inspections/front_post.jpg',
                'back_photo_url' => 'inspections/back_post.jpg',
                'left_photo_url' => 'inspections/left_post.jpg',
                'right_photo_url' => 'inspections/right_post.jpg',
                'dashboard_photo_url' => 'inspections/dashboard_post.jpg',
                'fuel_gauge_photo_url' => 'inspections/fuel_post.jpg',
                'odometer_reading' => 45680,
                'fuel_level' => '75% (3/4)',
                'notes' => 'Post-rental inspection completed. Driven 480km total (Within daily allowance limit). Fuel level returned at 75%.',
                'created_at' => now()->subHours(6),
            ],
            // 3. Pre-Rental Booking 2
            [
                'driver_booking_id' => $booking2->id,
                'vehicle_id' => $vehicle2->id,
                'inspection_type' => 'pre_rental',
                'front_photo_url' => 'inspections/front_pre2.jpg',
                'back_photo_url' => 'inspections/back_pre2.jpg',
                'left_photo_url' => 'inspections/left_pre2.jpg',
                'right_photo_url' => 'inspections/right_pre2.jpg',
                'dashboard_photo_url' => 'inspections/dashboard_pre2.jpg',
                'fuel_gauge_photo_url' => 'inspections/fuel_pre2.jpg',
                'odometer_reading' => 12400,
                'fuel_level' => '100% (Full)',
                'notes' => 'All 6 inspection photos uploaded and approved by support before releasing key to renter.',
                'created_at' => now()->subDays(1),
            ],
            // 4. Post-Rental Booking 2
            [
                'driver_booking_id' => $booking2->id,
                'vehicle_id' => $vehicle2->id,
                'inspection_type' => 'post_rental',
                'front_photo_url' => 'inspections/front_post2.jpg',
                'back_photo_url' => 'inspections/back_post2.jpg',
                'left_photo_url' => 'inspections/left_post2.jpg',
                'right_photo_url' => 'inspections/right_post2.jpg',
                'dashboard_photo_url' => 'inspections/dashboard_post2.jpg',
                'fuel_gauge_photo_url' => 'inspections/fuel_post2.jpg',
                'odometer_reading' => 12620,
                'fuel_level' => '100% (Full)',
                'notes' => 'Post-rental inspection verified. Vehicle returned in pristine condition with full tank.',
                'created_at' => now()->subHours(2),
            ],
            // 5. Pre-Rental Vehicle 3
            [
                'driver_booking_id' => $booking1->id,
                'vehicle_id' => $vehicle3->id,
                'inspection_type' => 'pre_rental',
                'front_photo_url' => 'inspections/front_pre3.jpg',
                'back_photo_url' => 'inspections/back_pre3.jpg',
                'left_photo_url' => 'inspections/left_pre3.jpg',
                'right_photo_url' => 'inspections/right_pre3.jpg',
                'dashboard_photo_url' => 'inspections/dashboard_pre3.jpg',
                'fuel_gauge_photo_url' => 'inspections/fuel_pre3.jpg',
                'odometer_reading' => 8900,
                'fuel_level' => '100% (Full)',
                'notes' => 'Pre-rental SUV inspection. Minor scuff noted on front bumper in photo 1.',
                'created_at' => now()->subDays(4),
            ],
            // 6. Post-Rental Vehicle 3
            [
                'driver_booking_id' => $booking1->id,
                'vehicle_id' => $vehicle3->id,
                'inspection_type' => 'post_rental',
                'front_photo_url' => 'inspections/front_post3.jpg',
                'back_photo_url' => 'inspections/back_post3.jpg',
                'left_photo_url' => 'inspections/left_post3.jpg',
                'right_photo_url' => 'inspections/right_post3.jpg',
                'dashboard_photo_url' => 'inspections/dashboard_post3.jpg',
                'fuel_gauge_photo_url' => 'inspections/fuel_post3.jpg',
                'odometer_reading' => 9340,
                'fuel_level' => '50% (1/2)',
                'notes' => 'Returned with 50% fuel deficit. GH₵ 150 fuel deduction processed from expressPay escrow deposit.',
                'created_at' => now()->subDays(2),
            ],
            // 7. Pre-Rental Luxury E-Class
            [
                'driver_booking_id' => $booking2->id,
                'vehicle_id' => $vehicle4->id,
                'inspection_type' => 'pre_rental',
                'front_photo_url' => 'inspections/front_pre4.jpg',
                'back_photo_url' => 'inspections/back_pre4.jpg',
                'left_photo_url' => 'inspections/left_pre4.jpg',
                'right_photo_url' => 'inspections/right_pre4.jpg',
                'dashboard_photo_url' => 'inspections/dashboard_pre4.jpg',
                'fuel_gauge_photo_url' => 'inspections/fuel_pre4.jpg',
                'odometer_reading' => 3100,
                'fuel_level' => '100% (Full)',
                'notes' => 'Executive Mercedes E-Class pre-rental check. GH₵ 1,000 escrow hold confirmed active.',
                'created_at' => now()->subDays(5),
            ],
            // 8. Post-Rental Luxury E-Class
            [
                'driver_booking_id' => $booking2->id,
                'vehicle_id' => $vehicle4->id,
                'inspection_type' => 'post_rental',
                'front_photo_url' => 'inspections/front_post4.jpg',
                'back_photo_url' => 'inspections/back_post4.jpg',
                'left_photo_url' => 'inspections/left_post4.jpg',
                'right_photo_url' => 'inspections/right_post4.jpg',
                'dashboard_photo_url' => 'inspections/dashboard_post4.jpg',
                'fuel_gauge_photo_url' => 'inspections/fuel_post4.jpg',
                'odometer_reading' => 3450,
                'fuel_level' => '100% (Full)',
                'notes' => 'Post-rental luxury inspection passed with zero damage. GH₵ 1,000 escrow deposit released in full.',
                'created_at' => now()->subDays(3),
            ],
        ];

        foreach ($inspections as $insp) {
            RentalInspection::firstOrCreate(
                [
                    'driver_booking_id' => $insp['driver_booking_id'],
                    'vehicle_id' => $insp['vehicle_id'],
                    'inspection_type' => $insp['inspection_type'],
                ],
                $insp
            );
        }
    }
}
