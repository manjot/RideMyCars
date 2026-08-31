<?php

namespace App\Http\Controllers;

use App\Models\DriverBooking;
use App\Models\PackageDelivery;
use App\Models\PaymentTransaction;
use App\Models\Ride;

use App\Services\ActivityLogService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StripeVerificationController extends Controller
{
    /**
     * Show dedicated Payment Details & Driver Verification page.
     */
    public function showDetails(string $serviceType, int $serviceId)
    {
        $details = $this->resolveBookingDetails($serviceType, $serviceId);
        return view('payment.verify-details', $details);
    }

    /**
     * Customer submits booking & payment details for driver verification.
     */
    public function submitForVerification(Request $request): JsonResponse
    {
        $request->validate([
            'service_type' => 'required|string|in:ride,rental,driver_booking,hire-driver,package_delivery,delivery',
            'service_id' => 'required|integer',
        ]);

        $serviceType = $request->input('service_type');
        $serviceId = (int) $request->input('service_id');

        $booking = $this->getBookingModel($serviceType, $serviceId);
        if (!$booking) {
            return response()->json(['success' => false, 'message' => 'Booking record not found.'], 404);
        }

        $booking->update([
            'verification_status' => 'pending_verification',
            'rejection_reason' => null,
            'payment_method' => 'stripe',
        ]);

        // Send notification to assigned driver if available
        $driverId = $booking->driver_id ?? $booking->courier_id ?? null;
        if ($driverId) {
            try {
                NotificationService::notifyDriverVerificationRequested($booking, $driverId);
            } catch (\Throwable $e) {
                Log::warning("Notification failed: " . $e->getMessage());
            }
        }

        ActivityLogService::log(
            'verification_requested',
            "Submitted {$serviceType} #{$serviceId} details for driver verification",
            Auth::id() ?? 1
        );

        return response()->json([
            'success' => true,
            'verification_status' => 'pending_verification',
            'message' => 'Your booking details have been submitted for driver verification.',
        ]);
    }

    /**
     * Driver Approves or Rejects the booking details.
     */
    public function driverRespond(Request $request): JsonResponse
    {
        $request->validate([
            'service_type' => 'required|string|in:ride,rental,driver_booking,hire-driver,package_delivery,delivery',
            'service_id' => 'required|integer',
            'action' => 'required|string|in:approve,reject',
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $serviceType = $request->input('service_type');
        $serviceId = (int) $request->input('service_id');
        $action = $request->input('action');
        $reason = $request->input('rejection_reason');
        $driverUser = Auth::user();

        $booking = $this->getBookingModel($serviceType, $serviceId);
        if (!$booking) {
            return response()->json(['success' => false, 'message' => 'Booking not found.'], 404);
        }

        if ($action === 'approve') {
            $booking->update([
                'verification_status' => 'driver_verified',
                'verified_by_driver_id' => $driverUser->id ?? null,
                'verified_at' => now(),
                'rejection_reason' => null,
            ]);

            ActivityLogService::log(
                'driver_verified_booking',
                "Driver #{$driverUser->id} approved verification for {$serviceType} #{$serviceId}",
                $driverUser->id ?? 1
            );

            return response()->json([
                'success' => true,
                'verification_status' => 'driver_verified',
                'message' => 'Booking verification approved. Customer can now proceed with Stripe Payment.',
            ]);
        } else {
            $booking->update([
                'verification_status' => 'rejected',
                'rejection_reason' => $reason ?? 'Driver declined details.',
            ]);

            ActivityLogService::log(
                'driver_rejected_booking',
                "Driver #{$driverUser->id} rejected verification for {$serviceType} #{$serviceId}: {$reason}",
                $driverUser->id ?? 1
            );

            return response()->json([
                'success' => true,
                'verification_status' => 'rejected',
                'message' => 'Booking verification rejected.',
            ]);
        }
    }

    /**
     * Poll API status for customer view.
     */
    public function getVerificationStatus(string $serviceType, int $serviceId): JsonResponse
    {
        $booking = $this->getBookingModel($serviceType, $serviceId);
        if (!$booking) {
            return response()->json(['success' => false, 'message' => 'Booking not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'verification_status' => $booking->verification_status ?? 'pending_verification',
            'payment_status' => $booking->payment_status ?? 'pending',
            'rejection_reason' => $booking->rejection_reason,
            'is_verified' => ($booking->verification_status === 'driver_verified'),
        ]);
    }

    /**
     * Driver Dashboard API: Fetch pending verification requests.
     */
    public function getPendingVerifications(Request $request): JsonResponse
    {
        $driverId = Auth::id();
        $items = [];

        // Driver Bookings
        $driverBookings = DriverBooking::with(['client'])
            ->where('verification_status', 'pending_verification')
            ->when($driverId, function ($q) use ($driverId) {
                $q->where(function ($sub) use ($driverId) {
                    $sub->where('driver_id', $driverId)->orWhereNull('driver_id');
                });
            })
            ->latest()
            ->take(10)
            ->get();

        foreach ($driverBookings as $db) {
            $items[] = [
                'type' => 'driver_booking',
                'type_label' => 'Chauffeur Booking',
                'id' => $db->id,
                'code' => $db->booking_code,
                'customer_name' => $db->client->name ?? 'Customer',
                'pickup' => $db->pickup_location,
                'dropoff' => $db->dropoff_location ?? 'N/A',
                'schedule' => ($db->start_date ? $db->start_date->format('Y-m-d') : date('Y-m-d')) . ' ' . $db->start_time,
                'vehicle' => $db->car_make_model ?? 'Executive Vehicle',
                'amount' => (float)$db->total_price,
                'currency' => $db->currency ?? 'USD',
            ];
        }

        // Rides
        $rides = Ride::with(['rider', 'vehicle'])
            ->where('verification_status', 'pending_verification')
            ->when($driverId, function ($q) use ($driverId) {
                $q->where(function ($sub) use ($driverId) {
                    $sub->where('driver_id', $driverId)->orWhereNull('driver_id');
                });
            })
            ->latest()
            ->take(10)
            ->get();

        foreach ($rides as $r) {
            $items[] = [
                'type' => 'ride',
                'type_label' => 'Ride Service',
                'id' => $r->id,
                'code' => 'RIDE-' . $r->id,
                'customer_name' => $r->rider->name ?? $r->passenger_name ?? 'Rider',
                'pickup' => $r->pickup_location,
                'dropoff' => $r->dropoff_location,
                'schedule' => ($r->pickup_date ? $r->pickup_date->format('Y-m-d') : date('Y-m-d')) . ' ' . ($r->pickup_time ?? 'Immediate'),
                'vehicle' => $r->vehicle ? ($r->vehicle->make . ' ' . $r->vehicle->model) : ($r->vehicle_type ?? 'Standard Sedan'),
                'amount' => (float)($r->total_amount ?? $r->fare ?? 0),
                'currency' => 'USD',
            ];
        }

        // Package Deliveries
        $deliveries = PackageDelivery::with(['customer'])
            ->where('verification_status', 'pending_verification')
            ->when($driverId, function ($q) use ($driverId) {
                $q->where(function ($sub) use ($driverId) {
                    $sub->where('courier_id', $driverId)->orWhereNull('courier_id');
                });
            })
            ->latest()
            ->take(10)
            ->get();

        foreach ($deliveries as $pd) {
            $items[] = [
                'type' => 'package_delivery',
                'type_label' => 'Parcel Dispatch',
                'id' => $pd->id,
                'code' => $pd->delivery_code,
                'customer_name' => $pd->customer->name ?? $pd->sender_name ?? 'Sender',
                'pickup' => $pd->pickup_location,
                'dropoff' => $pd->dropoff_location,
                'schedule' => ($pd->pickup_date ? $pd->pickup_date->format('Y-m-d') : date('Y-m-d')) . ' ' . $pd->pickup_time,
                'vehicle' => 'Courier Vehicle',
                'amount' => (float)$pd->total_price,
                'currency' => $pd->currency ?? 'USD',
            ];
        }

        return response()->json([
            'success' => true,
            'items' => $items,
        ]);
    }

    /**
     * Helper to retrieve booking model.
     */
    protected function getBookingModel(string $serviceType, int $serviceId)
    {
        switch ($serviceType) {
            case 'ride':
            case 'rental':
                return Ride::find($serviceId);
            case 'driver_booking':
            case 'hire-driver':
                return DriverBooking::find($serviceId);
            case 'package_delivery':
            case 'delivery':
                return PackageDelivery::find($serviceId);
            default:
                return null;
        }
    }

    /**
     * Helper to build booking array for view.
     */
    protected function resolveBookingDetails(string $serviceType, int $serviceId): array
    {
        $booking = $this->getBookingModel($serviceType, $serviceId);
        if (!$booking) {
            abort(404, 'Booking not found.');
        }

        $code = $booking->booking_code ?? $booking->delivery_code ?? ('BOOK-' . $booking->id);
        $pickup = $booking->pickup_location ?? 'Default Pickup Address';
        $dropoff = $booking->dropoff_location ?? 'Default Destination';
        $amount = (float) ($booking->total_price ?? $booking->fare ?? 0);
        $currency = $booking->currency ?? 'USD';
        $date = $booking->start_date ? $booking->start_date->format('Y-m-d') : ($booking->pickup_date ? $booking->pickup_date->format('Y-m-d') : date('Y-m-d'));
        $time = $booking->start_time ?? $booking->pickup_time ?? '09:00 AM';

        $driver = null;
        if ($serviceType === 'driver_booking' || $serviceType === 'hire-driver') {
            $booking->load(['driver', 'driverProfile']);
            $driver = [
                'name' => $booking->driver->name ?? 'Assigned Professional Chauffeur',
                'phone' => $booking->driver->phone ?? '+1 888 570 0008',
                'rating' => $booking->driverProfile->rating ?? 4.9,
                'vehicle' => ($booking->car_make_model ?? 'Executive Vehicle'),
                'photo_url' => $booking->driverProfile->photo_url ?? null,
            ];
        } elseif ($serviceType === 'ride' || $serviceType === 'rental') {
            $booking->load(['driver', 'vehicle']);
            $driver = [
                'name' => $booking->driver->name ?? 'Assigned Driver',
                'phone' => $booking->driver->phone ?? '+1 888 570 0008',
                'vehicle' => $booking->vehicle ? ($booking->vehicle->make . ' ' . $booking->vehicle->model) : ($booking->vehicle_type ?? 'Standard Sedan'),
                'photo_url' => null,
            ];
        } else {
            $booking->load(['courier', 'courierProfile']);
            $driver = [
                'name' => $booking->courier->name ?? 'Assigned Express Courier',
                'phone' => $booking->courier->phone ?? '+1 888 570 0008',
                'vehicle' => 'Dispatch Courier Vehicle',
                'photo_url' => null,
            ];
        }

        return [
            'serviceType' => $serviceType,
            'serviceId' => $serviceId,
            'bookingCode' => $code,
            'pickupLocation' => $pickup,
            'dropoffLocation' => $dropoff,
            'pickupDate' => $date,
            'pickupTime' => $time,
            'totalAmount' => $amount,
            'currency' => $currency,
            'driver' => $driver,
            'verificationStatus' => $booking->verification_status ?? 'pending_verification',
            'paymentStatus' => $booking->payment_status ?? 'pending',
            'rejectionReason' => $booking->rejection_reason,
            'publishableKey' => config('services.stripe.key'),
        ];
    }
}
