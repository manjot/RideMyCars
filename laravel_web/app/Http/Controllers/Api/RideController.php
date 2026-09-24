<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ride;
use App\Models\RideAssignment;
use App\Models\RideStop;
use App\Services\NotificationService;
use App\Services\PricingService;
use App\Services\RideAssignmentService;
use App\Services\BackupChauffeurService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RideController extends Controller
{
    /**
     * List rides for the current authenticated user (rider or driver)
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Include all driver IDs associated with this driver's identity
        $driverUserIds = [$user->id];
        if ($user->role === 'driver') {
            $matchingIds = \App\Models\User::where('name', $user->name)
                ->orWhere('email', 'like', explode('@', $user->email)[0] . '%')
                ->pluck('id')
                ->toArray();
            $driverUserIds = array_unique(array_merge($driverUserIds, $matchingIds));
        }

        $rides = Ride::with(['driver.driverProfile', 'rider', 'vehicle'])
            ->where(function ($q) use ($user, $driverUserIds) {
                $q->where('rider_id', $user->id)
                  ->orWhereIn('driver_id', $driverUserIds)
                  ->orWhereIn('verified_by_driver_id', $driverUserIds);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Also fetch chauffeur bookings for this user/driver
        $driverBookings = \App\Models\DriverBooking::with(['client', 'driver'])
            ->where(function ($q) use ($user, $driverUserIds) {
                $q->where('client_id', $user->id)
                  ->orWhereIn('driver_id', $driverUserIds)
                  ->orWhereIn('verified_by_driver_id', $driverUserIds);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Also fetch package deliveries for this user/driver
        $deliveries = \App\Models\PackageDelivery::with(['customer', 'courier'])
            ->where(function ($q) use ($user, $driverUserIds) {
                $q->where('customer_id', $user->id)
                  ->orWhereIn('courier_id', $driverUserIds);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $items = [];

        foreach ($rides as $r) {
            $rawVehicleType = (string)($r->vehicle_type ?? 'Standard');
            $isRental = $r->ride_type === 'rental' || $r->vehicle_id !== null || str_starts_with($rawVehicleType, 'RENTAL_');
            $isChauffeur = str_starts_with($rawVehicleType, 'CHAUFFEUR_');
            $isDelivery = str_starts_with($rawVehicleType, 'DELIVERY_');

            $cleanVehicleType = $rawVehicleType;
            $type = 'ride';

            if ($isRental) {
                $type = 'rental';
                if ($r->vehicle) {
                    $cleanVehicleType = $r->vehicle->year . ' ' . $r->vehicle->make . ' ' . $r->vehicle->model;
                } else {
                    $cleanVehicleType = preg_replace('/^RENTAL_\d+_/', '', $rawVehicleType);
                }
            } elseif ($isChauffeur) {
                $type = 'chauffeur';
                $cleanVehicleType = preg_replace('/^CHAUFFEUR_\d+_/', '', $rawVehicleType);
            } elseif ($isDelivery) {
                $type = 'delivery';
                $cleanVehicleType = preg_replace('/^DELIVERY_/', '', $rawVehicleType);
            }

            $items[] = [
                'id' => $r->id,
                'type' => $type,
                'booking_code' => $isRental 
                    ? ($r->digital_receipt_code ?: ('RNT-' . $r->id)) 
                    : ($isDelivery ? ('DEL-' . $r->id) : ('RIDE-' . $r->id)),
                'status' => $r->status,
                'receipt_id' => $r->receipt_id,
                'receipt_url' => $r->receipt ? $r->receipt->view_url : ($r->receipt_id ? url('/receipts/' . $r->receipt_id) : null),
                'receipt_download_url' => $r->receipt ? $r->receipt->download_url : ($r->receipt_id ? url('/receipts/' . $r->receipt_id . '/download') : null),
                'fare' => (float)($r->total_amount ?? $r->fare ?? 0),
                'paid_amount' => (float)($r->paid_amount ?? 0),
                'remaining_balance' => (float)($r->remaining_balance ?? 0),
                'payment_status' => $r->payment_status ?? ($r->status === 'completed' ? 'paid' : 'pending'),
                'pickup_location' => $r->pickup_location,
                'dropoff_location' => $r->dropoff_location,
                'vehicle_type' => $cleanVehicleType,
                'raw_vehicle_type' => $rawVehicleType,
                'pickup_date' => $r->pickup_date ? $r->pickup_date->format('Y-m-d') : null,
                'pickup_time' => $r->pickup_time,
                'return_date' => $r->return_date ? $r->return_date->format('Y-m-d') : null,
                'return_time' => $r->return_time,
                'protection_option' => $r->protection_option,
                'fuel_policy' => $r->fuel_policy,
                'vehicle' => $r->vehicle ? [
                    'id' => $r->vehicle->id,
                    'make' => $r->vehicle->make,
                    'model' => $r->vehicle->model,
                    'year' => $r->vehicle->year,
                    'category' => $r->vehicle->category,
                    'image_url' => $r->vehicle->image_url,
                    'daily_rate' => (float)$r->vehicle->daily_rate,
                ] : null,
                'created_at' => $r->created_at ? $r->created_at->toIso8601String() : null,
                'driver' => $r->driver ? [
                    'id' => $r->driver->id,
                    'name' => $r->driver->name,
                    'email' => $r->driver->email,
                ] : null,
                'rider' => $r->rider ? [
                    'id' => $r->rider->id,
                    'name' => $r->rider->name,
                    'email' => $r->rider->email,
                ] : null,
                'passenger_name' => $r->passenger_name ?? ($r->rider->name ?? 'Passenger'),
            ];
        }

        foreach ($driverBookings as $db) {
            $cleanName = $db->driver ? $db->driver->name : ($db->car_make_model ?? 'Executive Chauffeur');
            $items[] = [
                'id' => $db->id,
                'type' => 'chauffeur',
                'booking_code' => $db->booking_code ?? ('BK-' . $db->id),
                'status' => $db->booking_status ?? ($db->verification_status === 'driver_verified' ? 'completed' : 'pending'),
                'receipt_id' => $db->receipt_id,
                'receipt_url' => $db->receipt ? $db->receipt->view_url : ($db->receipt_id ? url('/receipts/' . $db->receipt_id) : null),
                'receipt_download_url' => $db->receipt ? $db->receipt->download_url : ($db->receipt_id ? url('/receipts/' . $db->receipt_id . '/download') : null),
                'fare' => (float)($db->total_price ?? 0),
                'pickup_location' => $db->pickup_location,
                'dropoff_location' => $db->dropoff_location ?? 'As Directed',
                'vehicle_type' => 'Chauffeur: ' . $cleanName,
                'created_at' => $db->created_at ? $db->created_at->toIso8601String() : null,
                'driver' => $db->driver ? [
                    'id' => $db->driver->id,
                    'name' => $db->driver->name,
                    'email' => $db->driver->email,
                ] : null,
                'rider' => $db->client ? [
                    'id' => $db->client->id,
                    'name' => $db->client->name,
                    'email' => $db->client->email,
                ] : null,
                'passenger_name' => $db->client->name ?? 'Client',
            ];
        }

        foreach ($deliveries as $del) {
            $items[] = [
                'id' => $del->id,
                'type' => 'delivery',
                'booking_code' => $del->delivery_code ?: ('DEL-' . $del->id),
                'status' => $del->delivery_status ?: 'pending',
                'receipt_id' => $del->receipt_id,
                'receipt_url' => $del->receipt ? $del->receipt->view_url : ($del->receipt_id ? url('/receipts/' . $del->receipt_id) : null),
                'receipt_download_url' => $del->receipt ? $del->receipt->download_url : ($del->receipt_id ? url('/receipts/' . $del->receipt_id . '/download') : null),
                'fare' => (float)($del->total_price ?? 0),
                'pickup_location' => $del->pickup_location,
                'dropoff_location' => $del->dropoff_location,
                'vehicle_type' => ($del->package_category ?: 'Parcel') . ' (' . ($del->delivery_type ?: 'Standard') . ')',
                'delivery_otp' => $del->delivery_otp,
                'sender_name' => $del->sender_name,
                'recipient_name' => $del->recipient_name,
                'package_size' => $del->package_size,
                'package_weight_kg' => $del->package_weight_kg,
                'created_at' => $del->created_at ? $del->created_at->toIso8601String() : null,
                'courier' => $del->courier ? [
                    'id' => $del->courier->id,
                    'name' => $del->courier->name,
                    'email' => $del->courier->email,
                ] : null,
                'rider' => $del->customer ? [
                    'id' => $del->customer->id,
                    'name' => $del->customer->name,
                    'email' => $del->customer->email,
                ] : null,
                'passenger_name' => $del->sender_name ?: ($del->customer->name ?? 'Sender'),
            ];
        }

        // Sort all trips descending by created_at
        usort($items, function ($a, $b) {
            return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
        });

        return response()->json([
            'success' => true,
            'data' => $items,
            'rides' => $items,
        ]);
    }

    /**
     * Book a new ride
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'pickup_location' => 'required|string|max:255',
            'dropoff_location' => 'required|string|max:255',
            'pickup_lat' => 'nullable|numeric',
            'pickup_lng' => 'nullable|numeric',
            'dropoff_lat' => 'nullable|numeric',
            'dropoff_lng' => 'nullable|numeric',
            'vehicle_type' => 'nullable|string|max:100',
            'payment_method' => 'nullable|string|max:50',
            'distance_km' => 'nullable|numeric',
            'duration_minutes' => 'nullable|integer',
            'stops' => 'nullable|array',
            'notes' => 'nullable|string',
            'country' => 'nullable|string|max:50',
        ]);

        $user = $request->user();
        $distanceKm = floatval($request->input('distance_km', 10.0));
        $durationMin = intval($request->input('duration_minutes', 15));
        $vehicleType = $request->input('vehicle_type', 'Standard');
        $stopsInput = $request->input('stops', []);
        $stopsCount = is_array($stopsInput) ? count($stopsInput) : 0;

        $country = $request->input('country') ?? CountryService::getCurrentCountryCode($request);
        $breakdown = PricingService::calculateTripFareWithBreakdown($distanceKm, $durationMin, $vehicleType, $stopsCount, $country);
        $amount = $breakdown['total_fare'];

        $digitalReceipt = 'REC-' . strtoupper(Str::random(8));

        $ride = Ride::create([
            'rider_id' => $user->id,
            'pickup_location' => $request->pickup_location,
            'pickup_lat' => $request->pickup_lat,
            'pickup_lng' => $request->pickup_lng,
            'dropoff_location' => $request->dropoff_location,
            'dropoff_lat' => $request->dropoff_lat,
            'dropoff_lng' => $request->dropoff_lng,
            'distance_km' => $distanceKm,
            'duration_minutes' => $durationMin,
            'fare' => $amount,
            'total_amount' => $amount,
            'vehicle_type' => $vehicleType,
            'payment_method' => $request->input('payment_method', 'stripe'),
            'passenger_name' => $user->name,
            'passenger_phone' => $user->phone ?? 'N/A',
            'notes' => $request->notes,
            'digital_receipt_code' => $digitalReceipt,
            'status' => 'pending',
            'payment_status' => 'pending',
            'backup_chauffeur_enabled' => $request->boolean('backup_chauffeur_enabled', false),
            'driver_assignment_type' => 'primary',
        ]);

        // Save stops if provided
        if (is_array($stopsInput)) {
            $order = 1;
            foreach ($stopsInput as $s) {
                if (!empty($s['location'])) {
                    RideStop::create([
                        'ride_id' => $ride->id,
                        'stop_order' => $order++,
                        'location' => $s['location'],
                        'lat' => $s['lat'] ?? null,
                        'lng' => $s['lng'] ?? null,
                    ]);
                }
            }
        }

        // Initialize payment authorization hold
        $rawMethod = strtolower($request->input('payment_method', 'stripe'));
        $paymentData = [];

        if (in_array($rawMethod, ['momo', 'mobile_money', 'momo_pay', 'mtn_momo'], true)) {
            $momoPhone = $request->input('momo_phone', $user->phone ?? '0240000000');
            $momoNet = $request->input('momo_network', 'MTN');
            $momoRes = \App\Services\MomoPaymentService::requestToPay($ride, $momoPhone, $momoNet);
            $paymentData = [
                'requires_payment_hold' => true,
                'payment_method' => 'momo',
                'transaction_ref' => $momoRes['transaction_ref'] ?? null,
                'message' => $momoRes['message'] ?? 'Please confirm USSD prompt on your phone.',
            ];
        } else {
            // Stripe or Apple Pay pre-authorization hold
            $intentData = \App\Services\StripeService::createPaymentIntent('ride', $ride->id, $user->id);
            $paymentData = [
                'requires_payment_hold' => true,
                'payment_method' => str_contains($rawMethod, 'apple') ? 'apple_pay' : 'stripe',
                'stripe_client_secret' => $intentData['client_secret'] ?? null,
                'stripe_publishable_key' => $intentData['publishable_key'] ?? null,
                'payment_intent_id' => $intentData['payment_intent_id'] ?? null,
            ];
        }

        return response()->json(array_merge([
            'success' => true,
            'message' => 'Ride booking created. Please complete payment hold to search for drivers.',
            'ride' => $ride->fresh(['stops']),
            'country_code' => $breakdown['country_code'] ?? 'USA',
            'currency' => $breakdown['currency'] ?? 'USD',
            'currency_symbol' => $breakdown['currency_symbol'] ?? '$',
            'breakdown' => $breakdown,
        ], $paymentData), 201);
    }

    /**
     * Get active ongoing ride for authenticated user
     */
    public function active(Request $request): JsonResponse
    {
        $user = $request->user();

        $ride = Ride::with(['driver.driverProfile', 'rider', 'stops', 'backupDriver.driverProfile'])
            ->where(function ($q) use ($user) {
                $q->where('rider_id', $user->id)
                  ->orWhere('driver_id', $user->id)
                  ->orWhere('backup_driver_id', $user->id);
            })
            ->whereIn('status', ['pending', 'accepted', 'en_route', 'arrived', 'in_progress'])
            ->latest()
            ->first();

        if (!$ride) {
            return response()->json([
                'success' => true,
                'ride' => null,
            ]);
        }

        // Live check: If pending and primary driver timed out or went offline, trigger backup if enabled
        if ($ride->status === 'pending') {
            $activeOffer = RideAssignment::where('ride_id', $ride->id)
                ->where('status', 'pending')
                ->first();

            if ($activeOffer) {
                $driverProfile = $activeOffer->driver?->driverProfile;
                $isDriverOffline = $driverProfile && $driverProfile->last_location_update && $driverProfile->last_location_update->lt(now()->subMinutes(5));
                $isExpired = $activeOffer->expires_at && $activeOffer->expires_at->lt(now());

                if ($isExpired || $isDriverOffline) {
                    $activeOffer->update(['status' => 'expired']);
                    if ($activeOffer->assignment_type === 'backup') {
                        BackupChauffeurService::dispatchBackupOffer($ride);
                    } else {
                        BackupChauffeurService::handlePrimaryDriverUnavailable($ride, $isExpired ? 'timeout' : 'driver_offline');
                    }
                    $ride->refresh();
                }
            }

            // Also check customer confirmation timeout for reserved backup driver
            if ($ride->backup_status === 'waiting' && $ride->backup_reserved_at) {
                $customerTimeout = (int) BackupChauffeurService::getConfig('backup.customer_timeout_sec', 60);
                if ($ride->backup_reserved_at->addSeconds($customerTimeout)->lt(now())) {
                    if ($ride->backup_driver_id) {
                        $dp = \App\Models\DriverProfile::where('user_id', $ride->backup_driver_id)->first();
                        if ($dp) $dp->update(['is_available' => true]);
                    }
                    $ride->update(['backup_driver_id' => null, 'backup_status' => 'expired']);
                    BackupChauffeurService::dispatchBackupOffer($ride);
                    $ride->refresh();
                }
            }
        }

        $isDriverAccepted = !empty($ride->driver_id) && in_array($ride->status, ['accepted', 'en_route', 'arrived', 'in_progress', 'completed'], true);
        $driverProfile = $ride->driver?->driverProfile;

        $driverData = null;
        if ($isDriverAccepted && $ride->driver) {
            $rawPhone = $ride->driver->phone ?: '';
            $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
            if (str_starts_with($cleanPhone, '0')) {
                $cleanPhone = '233' . substr($cleanPhone, 1);
            } elseif (!str_starts_with($cleanPhone, '233') && strlen($cleanPhone) === 9) {
                $cleanPhone = '233' . $cleanPhone;
            }
            $whatsappUrl = !empty($cleanPhone) ? "https://wa.me/{$cleanPhone}" : null;

            $driverData = [
                'id' => $ride->driver->id,
                'name' => $ride->driver->name,
                'phone' => $ride->driver->phone,
                'email' => $ride->driver->email,
                'whatsapp' => $cleanPhone,
                'whatsapp_url' => $whatsappUrl,
                'photo_url' => $driverProfile?->photo_url,
                'rating' => $driverProfile ? floatval($driverProfile->rating) : 4.9,
                'total_trips' => $driverProfile ? $driverProfile->total_completed_trips : 40,
                'vehicle' => $driverProfile ? trim($driverProfile->vehicle_make . ' ' . $driverProfile->vehicle_model) : ($ride->vehicle_type ?? 'Executive Sedan'),
                'plate' => $driverProfile?->license_number ?? 'REG-8899',
                'current_lat' => $ride->current_lat ? floatval($ride->current_lat) : ($driverProfile?->current_lat ? floatval($driverProfile->current_lat) : null),
                'current_lng' => $ride->current_lng ? floatval($ride->current_lng) : ($driverProfile?->current_lng ? floatval($driverProfile->current_lng) : null),
            ];
        }

        // Prepare backup driver preview if reserved
        $backupDriverData = null;
        if ($ride->backup_driver_id && $ride->backupDriver) {
            $bp = $ride->backupDriver->driverProfile;
            $rawBPhone = $ride->backupDriver->phone ?: '';
            $cleanBPhone = preg_replace('/[^0-9]/', '', $rawBPhone);
            $backupDriverData = [
                'id' => $ride->backupDriver->id,
                'name' => $ride->backupDriver->name,
                'phone' => $ride->backupDriver->phone,
                'email' => $ride->backupDriver->email,
                'photo_url' => $bp?->photo_url,
                'rating' => $bp ? floatval($bp->rating) : 4.9,
                'total_trips' => $bp ? $bp->total_completed_trips : 35,
                'vehicle' => $bp ? trim($bp->vehicle_make . ' ' . $bp->vehicle_model) : ($ride->vehicle_type ?? 'Executive Sedan'),
                'plate' => $bp?->license_number ?? 'REG-8899',
                'current_lat' => $bp?->current_lat ? floatval($bp->current_lat) : null,
                'current_lng' => $bp?->current_lng ? floatval($bp->current_lng) : null,
            ];
        }

        return response()->json([
            'success' => true,
            'ride' => [
                'id' => $ride->id,
                'status' => $ride->status,
                'payment_status' => $ride->payment_status,
                'pickup_location' => $ride->pickup_location,
                'pickup_lat' => $ride->pickup_lat ? floatval($ride->pickup_lat) : null,
                'pickup_lng' => $ride->pickup_lng ? floatval($ride->pickup_lng) : null,
                'dropoff_location' => $ride->dropoff_location,
                'dropoff_lat' => $ride->dropoff_lat ? floatval($ride->dropoff_lat) : null,
                'dropoff_lng' => $ride->dropoff_lng ? floatval($ride->dropoff_lng) : null,
                'fare' => floatval($ride->fare ?: $ride->total_amount),
                'vehicle_type' => $ride->vehicle_type ?? 'Standard',
                'payment_method' => $ride->payment_method ?? 'stripe',
                'distance_km' => $ride->distance_km ? floatval($ride->distance_km) : null,
                'duration_minutes' => $ride->duration_minutes,
                'created_at' => $ride->created_at->toIso8601String(),
                'driver' => $driverData,
                'backup_chauffeur_enabled' => (bool)$ride->backup_chauffeur_enabled,
                'backup_status' => $ride->backup_status,
                'backup_driver_id' => $ride->backup_driver_id,
                'driver_assignment_type' => $ride->driver_assignment_type ?? 'primary',
                'backup_driver' => $backupDriverData,
                'backup_reserved_at' => $ride->backup_reserved_at?->toIso8601String(),
                'rider' => [
                    'id' => $ride->rider?->id,
                    'name' => $ride->rider?->name ?? $ride->passenger_name ?? 'Rider',
                    'phone' => $ride->passenger_phone ?? $ride->rider?->phone,
                    'customer_name' => $ride->rider?->name ?? 'Customer',
                    'customer_phone' => $ride->rider?->phone,
                    'poc_name' => $ride->passenger_name,
                    'poc_phone' => $ride->passenger_phone,
                    'is_for_someone_else' => (bool)$ride->is_for_someone_else,
                ],
                'stops' => $ride->stops,
            ],
        ]);
    }

    /**
     * Show single ride details with IDOR protection
     */
    public function show(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $ride = Ride::with(['driver.driverProfile', 'rider', 'stops'])->find($id);

        if (!$ride) {
            return response()->json(['success' => false, 'message' => 'Ride not found'], 404);
        }

        // Fix IDOR: only allow rider, assigned driver, or admin
        if ($user && $user->id !== $ride->rider_id && $user->id !== $ride->driver_id && ($user->role ?? null) !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized to view this ride details'], 403);
        }

        // Gate driver contacts unless confirmed/accepted
        if (!in_array($ride->status, ['accepted', 'en_route', 'arrived', 'in_progress', 'completed'], true)) {
            $ride->setRelation('driver', null);
        }

        return response()->json(['success' => true, 'ride' => $ride]);
    }

    /**
     * Verify payment hold / confirmation for API rides and start driver search
     */
    public function confirmPayment(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $ride = Ride::find($id);

        if (!$ride) {
            return response()->json(['success' => false, 'message' => 'Ride not found'], 404);
        }

        if ($user && $user->id !== $ride->rider_id && ($user->role ?? null) !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $paymentIntentId = $request->input('payment_intent_id') ?: $ride->hold_payment_intent_id;
        $transactionRef = $request->input('transaction_ref') ?: $ride->hold_authorization_code;

        if ($paymentIntentId) {
            $result = \App\Services\StripeService::confirmPayment($paymentIntentId);
            if (!empty($result['success'])) {
                $ride->refresh();
                return response()->json([
                    'success' => true,
                    'status' => $ride->payment_status,
                    'message' => 'Payment hold verified. Driver search is now active.',
                    'ride' => $ride->fresh(['stops', 'driver.driverProfile']),
                ]);
            }
            return response()->json(['success' => false, 'error' => $result['error'] ?? 'Card authorization failed.'], 400);
        }

        if ($transactionRef) {
            $result = \App\Services\MomoPaymentService::confirmPayment($transactionRef);
            if (!empty($result['success'])) {
                $ride->refresh();
                return response()->json([
                    'success' => true,
                    'status' => $ride->payment_status,
                    'message' => 'Mobile Money payment confirmed. Driver search is now active.',
                    'ride' => $ride->fresh(['stops', 'driver.driverProfile']),
                ]);
            }
            return response()->json(['success' => false, 'error' => $result['message'] ?? 'MoMo confirmation failed.'], 400);
        }

        return response()->json(['success' => false, 'error' => 'Missing payment identifier.'], 422);
    }

    /**
     * Driver updates ride status
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:en_route,arrived,in_progress,completed,cancelled',
        ]);

        $user = $request->user();
        $ride = Ride::find($id);

        if (!$ride) {
            return response()->json(['success' => false, 'message' => 'Ride not found'], 404);
        }

        $userIds = [$user->id];
        $matchingIds = \App\Models\User::where('name', $user->name)
            ->orWhere('email', 'like', explode('@', $user->email)[0] . '%')
            ->pluck('id')
            ->toArray();
        $userIds = array_unique(array_merge($userIds, $matchingIds));

        // Must be the assigned driver or admin
        if (!in_array((int)$ride->driver_id, $userIds) && $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $newStatus = $request->input('status');
        $updates = ['status' => $newStatus];

        if ($newStatus === 'en_route') $updates['en_route_at'] = now();
        if ($newStatus === 'arrived') $updates['arrived_at'] = now();
        if ($newStatus === 'in_progress') $updates['started_at'] = now();
        if ($newStatus === 'completed') {
            $updates['completed_at'] = now();
            \App\Services\StripeService::captureRideHold($ride);
            $updates['payment_status'] = 'paid';

            if ($user->driverProfile) {
                $user->driverProfile->update(['is_available' => true]);
                if (\Illuminate\Support\Facades\Schema::hasColumn('driver_profiles', 'total_trips')) {
                    $user->driverProfile->increment('total_trips');
                }
            }
        }
        if ($newStatus === 'cancelled') {
            $updates['cancelled_at'] = now();
            \App\Services\StripeService::releaseRideHold($ride);
            $updates['payment_status'] = 'released';
        }

        $ride->update($updates);

        if ($newStatus === 'completed') {
            try {
                \App\Services\IncentiveService::handleRideCompleted($ride);
            } catch (\Throwable $e) {}

            try {
                \App\Services\ReceiptService::generateReceiptForRide($ride, true);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Auto receipt generation for ride error: ' . $e->getMessage());
            }
        }

        // Notifications
        try {
            if ($newStatus === 'en_route') {
                NotificationService::notifyEnRoute($ride);
            } elseif ($newStatus === 'arrived') {
                NotificationService::notifyArrived($ride);
            } elseif ($newStatus === 'in_progress') {
                NotificationService::notifyTripStarted($ride);
            } elseif ($newStatus === 'completed') {
                NotificationService::notifyTripCompleted($ride);
            }
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'message' => "Ride status updated to {$newStatus}",
            'ride' => $ride->fresh(),
        ]);
    }

    /**
     * Cancel a ride
     */
    public function cancel(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $ride = Ride::find($id);

        if (!$ride) {
            return response()->json(['success' => false, 'message' => 'Ride not found'], 404);
        }

        if ($ride->rider_id !== $user->id && $ride->driver_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $ride->update(['status' => 'cancelled']);
        RideAssignment::where('ride_id', $ride->id)->update(['status' => 'expired']);

        if ($ride->driver?->driverProfile) {
            $ride->driver->driverProfile->update(['is_available' => true]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ride cancelled successfully.',
        ]);
    }

    /**
     * Enable or disable Backup Chauffeur option for a ride
     */
    public function toggleBackupChauffeur(Request $request, $id): JsonResponse
    {
        $ride = Ride::find($id);
        if (!$ride) {
            return response()->json(['success' => false, 'message' => 'Ride not found'], 404);
        }

        $user = $request->user();
        if ($user && $user->id !== $ride->rider_id && ($user->role ?? null) !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $enabled = $request->has('enabled') ? $request->boolean('enabled') : !$ride->backup_chauffeur_enabled;
        $ride->update(['backup_chauffeur_enabled' => $enabled]);

        return response()->json([
            'success' => true,
            'message' => 'Backup chauffeur ' . ($enabled ? 'enabled' : 'disabled'),
            'backup_chauffeur_enabled' => $enabled,
        ]);
    }

    /**
     * Find nearby backup drivers for a ride
     */
    public function getNearbyBackupDrivers(Request $request, $id): JsonResponse
    {
        $ride = Ride::find($id);
        if (!$ride) {
            return response()->json(['success' => false, 'message' => 'Ride not found'], 404);
        }

        $radius = $request->has('radius_km') ? floatval($request->radius_km) : null;
        $drivers = BackupChauffeurService::findNearbyBackupDrivers($ride, $radius);

        return response()->json([
            'success' => true,
            'count' => $drivers->count(),
            'drivers' => $drivers->map(fn($d) => [
                'id' => $d->user_id,
                'name' => $d->user?->name ?? 'Chauffeur',
                'distance_km' => round($d->distance_km ?? 0, 2),
                'rating' => floatval($d->rating ?? 4.9),
                'photo_url' => $d->photo_url,
                'vehicle' => trim($d->vehicle_make . ' ' . $d->vehicle_model),
            ]),
        ]);
    }

    /**
     * Send backup ride request to nearest drivers
     */
    public function sendBackupRequest(Request $request, $id): JsonResponse
    {
        $ride = Ride::find($id);
        if (!$ride) {
            return response()->json(['success' => false, 'message' => 'Ride not found'], 404);
        }

        $assignment = BackupChauffeurService::dispatchBackupOffer($ride);

        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => 'No nearby chauffeur is currently available.',
                'status' => 'cancelled',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Backup ride offer dispatched.',
            'assignment_id' => $assignment->id,
            'driver_id' => $assignment->driver_id,
            'expires_at' => $assignment->expires_at?->toIso8601String(),
        ]);
    }

    /**
     * Driver accepts backup ride offer (temporarily reserves driver)
     */
    public function driverAcceptBackup(Request $request, $assignmentId): JsonResponse
    {
        $assignment = RideAssignment::find($assignmentId);
        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'Assignment not found'], 404);
        }

        $user = $request->user();
        if ($user && $user->id !== $assignment->driver_id && ($user->role ?? null) !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $reserved = BackupChauffeurService::reserveBackupDriver($assignment);

        if (!$reserved) {
            return response()->json([
                'success' => false,
                'message' => 'Ride is no longer available for reservation.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Backup ride reserved. Customer waiting for confirmation.',
            'status' => 'waiting_confirmation',
        ]);
    }

    /**
     * Driver declines backup ride offer
     */
    public function driverDeclineBackup(Request $request, $assignmentId): JsonResponse
    {
        $assignment = RideAssignment::find($assignmentId);
        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'Assignment not found'], 404);
        }

        BackupChauffeurService::handleDriverDeclinedBackup($assignment);

        return response()->json([
            'success' => true,
            'message' => 'Backup ride offer declined.',
        ]);
    }

    /**
     * Customer confirms backup driver from the in-app modal
     */
    public function customerConfirmBackup(Request $request, $id): JsonResponse
    {
        $ride = Ride::find($id);
        if (!$ride) {
            return response()->json(['success' => false, 'message' => 'Ride not found'], 404);
        }

        $result = BackupChauffeurService::customerConfirmBackupDriver($ride, $request->user());

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Customer declines backup driver from the in-app modal
     */
    public function customerDeclineBackup(Request $request, $id): JsonResponse
    {
        $ride = Ride::find($id);
        if (!$ride) {
            return response()->json(['success' => false, 'message' => 'Ride not found'], 404);
        }

        $result = BackupChauffeurService::customerDeclineBackupDriver($ride, $request->user());

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Cancel active backup assignment
     */
    public function cancelBackupAssignment(Request $request, $id): JsonResponse
    {
        $ride = Ride::find($id);
        if (!$ride) {
            return response()->json(['success' => false, 'message' => 'Ride not found'], 404);
        }

        BackupChauffeurService::cancelBackup($ride);

        return response()->json([
            'success' => true,
            'message' => 'Backup assignment cancelled.',
        ]);
    }

    /**
     * Cancel a ride
     */
    public function cancel(Request $request, $id): JsonResponse
    {
        $ride = Ride::find($id);
        if (!$ride) {
            session()->forget('active_guest_ride_id');
            return response()->json([
                'success' => true,
                'message' => 'Ride not found or already cancelled.',
                'status' => 'not_found',
            ], 200);
        }

        if ($ride->status === 'cancelled') {
            session()->forget('active_guest_ride_id');
            return response()->json([
                'success' => true,
                'message' => 'Ride is already cancelled.',
                'status' => 'cancelled',
                'ride_id' => $ride->id,
            ], 200);
        }

        if ($ride->status === 'completed') {
            return response()->json([
                'success' => false,
                'error' => 'Ride has already been completed and cannot be cancelled.',
                'status' => 'completed',
            ], 400);
        }

        $reason = $request->input('reason', 'Cancelled by user');

        $ride->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
        ]);

        session()->forget('active_guest_ride_id');

        \App\Models\RideAssignment::where('ride_id', $ride->id)
            ->whereNotIn('status', ['completed'])
            ->update(['status' => 'cancelled']);

        try {
            if (class_exists(\App\Services\StripeService::class)) {
                \App\Services\StripeService::releaseRideHold($ride, $reason);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Ride cancel hold release notice for #{$ride->id}: " . $e->getMessage());
        }

        try {
            if ($ride->rider_id && class_exists(\App\Services\NotificationService::class)) {
                \App\Services\NotificationService::send(
                    $ride->rider_id,
                    'cancelled',
                    'Ride Cancelled',
                    "Ride #{$ride->id} to {$ride->dropoff_location} has been cancelled.",
                    $ride->id,
                    '/'
                );
            }
            if ($ride->driver_id && class_exists(\App\Services\NotificationService::class)) {
                \App\Services\NotificationService::send(
                    $ride->driver_id,
                    'cancelled',
                    'Ride Cancelled',
                    "Ride #{$ride->id} was cancelled.",
                    $ride->id,
                    '/driver/dashboard'
                );
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Ride cancel notification notice for #{$ride->id}: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Ride cancelled successfully.',
            'status' => 'cancelled',
            'ride_id' => $ride->id,
        ], 200);
    }
}
