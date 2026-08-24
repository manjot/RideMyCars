<?php

namespace App\Services;

use App\Models\DriverBooking;
use App\Models\Vehicle;
use Carbon\Carbon;

class RentalBufferService
{
    /**
     * Buffer duration in hours.
     */
    public const BUFFER_HOURS = 4;

    /**
     * Calculate buffer end time for a completed or active booking.
     */
    public static function calculateBufferEndTime(Carbon|string $rentalEndTime): Carbon
    {
        $end = is_string($rentalEndTime) ? Carbon::parse($rentalEndTime) : $rentalEndTime->copy();
        return $end->addHours(self::BUFFER_HOURS);
    }

    /**
     * Check if a proposed booking interval for a vehicle conflicts with existing bookings or 4-hour buffers.
     * Works strictly at the backend database query level.
     */
    public static function checkBufferConflict(
        int $vehicleId,
        string|Carbon $proposedStartTime,
        string|Carbon $proposedEndTime,
        ?int $ignoreBookingId = null
    ): array {
        $start = is_string($proposedStartTime) ? Carbon::parse($proposedStartTime) : $proposedStartTime;
        $end = is_string($proposedEndTime) ? Carbon::parse($proposedEndTime) : $proposedEndTime;

        // Query active or confirmed bookings for this vehicle
        $query = DriverBooking::where('vehicle_id', $vehicleId)
            ->whereIn('booking_status', ['accepted', 'in_progress', 'completed'])
            ->whereIn('payment_status', ['paid', 'confirmed', 'pending']);

        if ($ignoreBookingId) {
            $query->where('id', '!=', $ignoreBookingId);
        }

        $existingBookings = $query->get();
        // echo "FOUND " . $existingBookings->count() . " BOOKINGS FOR VEHICLE " . $vehicleId . "\n";

        foreach ($existingBookings as $b) {
            $bStart = Carbon::parse($b->start_date . ' ' . ($b->start_time ?? '09:00:00'));
            
            // Calculate rental end time and buffer end time (+ 4 hours)
            if ($b->buffer_end_time) {
                $bBufferEnd = Carbon::parse($b->buffer_end_time);
                $bEnd = $bBufferEnd->copy()->subHours(self::BUFFER_HOURS);
            } else {
                $bEnd = $b->end_date ? Carbon::parse($b->end_date . ' ' . ($b->end_time ?? $b->start_time ?? '09:00:00')) : $bStart->copy()->addDays($b->duration_count ?? 1);
                $bBufferEnd = static::calculateBufferEndTime($bEnd);
            }

            // Check if proposed booking starts before existing booking buffer ends AND proposed booking ends after existing booking starts
            if ($start->lt($bBufferEnd) && $end->gt($bStart)) {
                $isBufferConflict = $start->gte($bEnd) && $start->lt($bBufferEnd);

                return [
                    'has_conflict' => true,
                    'conflict_booking_code' => $b->booking_code,
                    'rental_end_time' => $bEnd->toDateTimeString(),
                    'buffer_end_time' => $bBufferEnd->toDateTimeString(),
                    'reason' => $isBufferConflict 
                        ? "Vehicle unavailable: Overlaps 4-hour post-rental maintenance buffer of Booking #{$b->booking_code} (Buffer ends at {$bBufferEnd->format('Y-m-d H:i')})." 
                        : "Vehicle unavailable: Directly overlaps existing Booking #{$b->booking_code}.",
                ];
            }
        }

        return [
            'has_conflict' => false,
            'reason' => null,
        ];
    }

    /**
     * Validate and throw exception if booking violates 4-hour buffer.
     */
    public static function validateBookingSlot(int $vehicleId, string|Carbon $startTime, string|Carbon $endTime): bool
    {
        $check = static::checkBufferConflict($vehicleId, $startTime, $endTime);

        if ($check['has_conflict']) {
            throw new \InvalidArgumentException($check['reason']);
        }

        return true;
    }
}
