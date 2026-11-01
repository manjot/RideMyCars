<?php

namespace App\Http\Controllers;

use App\Models\DriverBooking;
use App\Models\PackageDelivery;
use App\Models\Ride;
use App\Models\TripMessage;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TripChatController extends Controller
{
    protected function normalizeType(string $type): string
    {
        $t = strtolower(trim($type));
        if (in_array($t, ['driver_booking', 'booking', 'chauffeur', 'hire'])) {
            return 'driver_booking';
        }
        if (in_array($t, ['delivery', 'package_delivery', 'package'])) {
            return 'delivery';
        }
        return 'ride';
    }

    /**
     * Fetch in-app chat messages for a trip (ride, driver_booking, or delivery).
     */
    public function index(Request $request, string $type, int $id): JsonResponse
    {
        TripMessage::ensureTableExists();
        $tripType = $this->normalizeType($type);

        $messages = TripMessage::where('trip_type', $tripType)
            ->where('trip_id', $id)
            ->orderBy('created_at', 'asc')
            ->limit(200)
            ->get()
            ->map(function (TripMessage $m) {
                return [
                    'id' => $m->id,
                    'trip_type' => $m->trip_type,
                    'trip_id' => $m->trip_id,
                    'sender_id' => $m->sender_id,
                    'sender_role' => $m->sender_role,
                    'sender_name' => $m->sender_name ?: ucfirst($m->sender_role),
                    'message' => $m->message,
                    'is_read' => (bool) $m->is_read,
                    'time' => $m->created_at ? $m->created_at->format('H:i') : now()->format('H:i'),
                    'created_at' => $m->created_at ? $m->created_at->toIso8601String() : now()->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'trip_type' => $tripType,
            'trip_id' => $id,
            'messages' => $messages,
        ]);
    }

    /**
     * Store a new in-app chat message for a trip.
     */
    public function store(Request $request, string $type, int $id): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'sender_role' => 'nullable|string|max:30',
            'sender_name' => 'nullable|string|max:120',
        ]);

        TripMessage::ensureTableExists();
        $tripType = $this->normalizeType($type);

        $user = $request->user() ?? auth()->user();
        $senderRole = strtolower(trim($request->input('sender_role', $user && $user->role === 'driver' ? 'driver' : 'rider')));
        if (!in_array($senderRole, ['driver', 'rider', 'customer'])) {
            $senderRole = 'rider';
        }
        if ($senderRole === 'customer') {
            $senderRole = 'rider';
        }

        $senderName = trim((string) ($request->input('sender_name') ?: ($user?->name ?? ($senderRole === 'driver' ? 'Driver' : 'Rider'))));
        $text = trim((string) $request->input('message'));

        $msg = TripMessage::create([
            'trip_type' => $tripType,
            'trip_id' => $id,
            'sender_id' => $user?->id,
            'sender_role' => $senderRole,
            'sender_name' => $senderName,
            'message' => $text,
            'is_read' => false,
        ]);

        // Notify the other party in-app
        try {
            $recipientId = null;
            if ($tripType === 'ride') {
                $ride = Ride::find($id);
                if ($ride) {
                    $recipientId = $senderRole === 'driver' ? $ride->rider_id : $ride->driver_id;
                }
            } elseif ($tripType === 'driver_booking') {
                $booking = DriverBooking::with('driver')->find($id);
                if ($booking) {
                    $driverUserId = $booking->driver?->user_id ?? $booking->driver_id;
                    $recipientId = $senderRole === 'driver' ? $booking->user_id : $driverUserId;
                }
            } elseif ($tripType === 'delivery') {
                $delivery = PackageDelivery::find($id);
                if ($delivery) {
                    $recipientId = $senderRole === 'driver' ? $delivery->user_id : $delivery->driver_id;
                }
            }

            if ($recipientId) {
                NotificationService::send(
                    (int) $recipientId,
                    'chat_message',
                    "💬 New Message from {$senderName}",
                    $text,
                    $tripType === 'ride' ? $id : null,
                    ['trip_type' => $tripType, 'trip_id' => $id]
                );
            }
        } catch (\Throwable $e) {
            // Non-blocking notification
        }

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $msg->id,
                'trip_type' => $msg->trip_type,
                'trip_id' => $msg->trip_id,
                'sender_id' => $msg->sender_id,
                'sender_role' => $msg->sender_role,
                'sender_name' => $msg->sender_name,
                'message' => $msg->message,
                'is_read' => false,
                'time' => $msg->created_at ? $msg->created_at->format('H:i') : now()->format('H:i'),
                'created_at' => $msg->created_at ? $msg->created_at->toIso8601String() : now()->toIso8601String(),
            ],
        ]);
    }
}

