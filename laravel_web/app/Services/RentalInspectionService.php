<?php

namespace App\Services;

use App\Models\DriverBooking;
use App\Models\RentalInspection;
use App\Models\Vehicle;

class RentalInspectionService
{
    /**
     * Required photo keys for vehicle release.
     */
    public const REQUIRED_PHOTOS = [
        'front_photo_url' => 'Front Photo',
        'back_photo_url' => 'Back Photo',
        'left_photo_url' => 'Left Side Photo',
        'right_photo_url' => 'Right Side Photo',
        'dashboard_photo_url' => 'Dashboard / Odometer Photo',
        'fuel_gauge_photo_url' => 'Fuel Gauge Photo',
    ];

    /**
     * Verify inspection completeness for a booking.
     */
    public static function checkInspectionStatus(DriverBooking $booking, string $type = 'pre_rental'): array
    {
        $inspection = RentalInspection::where('driver_booking_id', $booking->id)
            ->where('inspection_type', $type)
            ->first();

        if (!$inspection) {
            return [
                'is_complete' => false,
                'inspection_id' => null,
                'missing_photos' => array_values(self::REQUIRED_PHOTOS),
                'present_photos' => [],
                'message' => "Inspection incomplete: No {$type} inspection record found for Booking #{$booking->booking_code}.",
            ];
        }

        $missing = [];
        $present = [];

        foreach (self::REQUIRED_PHOTOS as $column => $label) {
            if (empty($inspection->$column)) {
                $missing[] = $label;
            } else {
                $present[] = $label;
            }
        }

        $isComplete = empty($missing);

        return [
            'is_complete' => $isComplete,
            'inspection_id' => $inspection->id,
            'missing_photos' => $missing,
            'present_photos' => $present,
            'message' => $isComplete 
                ? "All 6 mandatory inspection photos verified for {$type} inspection." 
                : "Vehicle release blocked! Missing " . count($missing) . " photo(s): " . implode(', ', $missing),
        ];
    }

    /**
     * Attempt to mark booking/vehicle as released/in_progress.
     * Throws exception or returns false if inspection is incomplete.
     */
    public static function validateVehicleRelease(DriverBooking $booking): bool
    {
        $check = static::checkInspectionStatus($booking, 'pre_rental');

        if (!$check['is_complete']) {
            throw new \InvalidArgumentException($check['message']);
        }

        return true;
    }
}
