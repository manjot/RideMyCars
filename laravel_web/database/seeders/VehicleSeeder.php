<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $vehicles = [
            [
                'make' => 'Toyota',
                'model' => 'Camry',
                'year' => '2023',
                'license_plate' => 'ABC-1234',
                'type' => 'Midsize',
                'daily_rate' => 45.00,
                'is_available' => true,
            ],
            [
                'make' => 'Honda',
                'model' => 'Civic',
                'year' => '2022',
                'license_plate' => 'XYZ-5678',
                'type' => 'Compact',
                'daily_rate' => 35.00,
                'is_available' => true,
            ],
            [
                'make' => 'Ford',
                'model' => 'Explorer',
                'year' => '2024',
                'license_plate' => 'LMN-9101',
                'type' => 'SUV',
                'daily_rate' => 75.00,
                'is_available' => true,
            ],
            [
                'make' => 'Mercedes-Benz',
                'model' => 'E-Class',
                'year' => '2023',
                'license_plate' => 'LUX-1111',
                'type' => 'Luxury',
                'daily_rate' => 120.00,
                'is_available' => true,
            ],
            [
                'make' => 'Chevrolet',
                'model' => 'Express',
                'year' => '2021',
                'license_plate' => 'VAN-2222',
                'type' => 'Van',
                'daily_rate' => 90.00,
                'is_available' => true,
            ],
            [
                'make' => 'Nissan',
                'model' => 'Versa',
                'year' => '2022',
                'license_plate' => 'ECO-3333',
                'type' => 'Economy',
                'daily_rate' => 25.00,
                'is_available' => true,
            ],
            [
                'make' => 'Toyota',
                'model' => 'RAV4',
                'year' => '2024',
                'license_plate' => 'SUV-4444',
                'type' => 'SUV',
                'daily_rate' => 65.00,
                'is_available' => true,
            ]
        ];

        foreach ($vehicles as $vehicle) {
            Vehicle::create($vehicle);
        }
    }
}
