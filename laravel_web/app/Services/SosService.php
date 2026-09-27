<?php

namespace App\Services;

use App\Models\EmergencyContact;
use App\Models\Ride;
use App\Models\SosAlert;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Log;

class SosService
{
    /**
     * Get all emergency contacts for a user.
     */
    public static function getContacts(User $user)
    {
        return $user->emergencyContacts()->get();
    }

    /**
     * Add a new emergency contact (limit to 5 contacts per user).
     */
    public static function addContact(User $user, array $data): EmergencyContact
    {
        $count = $user->emergencyContacts()->count();
        if ($count >= 5) {
            throw new \InvalidArgumentException('You can add up to 5 emergency contacts.');
        }

        $phone = preg_replace('/[^\d+]/', '', trim($data['phone'] ?? ''));
        if (empty($phone)) {
            throw new \InvalidArgumentException('A valid phone number is required.');
        }

        $isPrimary = !empty($data['is_primary']) || $count === 0;

        // If setting this contact as primary, unset other primaries
        if ($isPrimary) {
            $user->emergencyContacts()->update(['is_primary' => false]);
        }

        return $user->emergencyContacts()->create([
            'name' => trim($data['name'] ?? 'Emergency Contact'),
            'phone' => $phone,
            'relationship' => trim($data['relationship'] ?? 'Contact'),
            'is_primary' => $isPrimary,
            'notify_sms' => $data['notify_sms'] ?? true,
            'notify_whatsapp' => $data['notify_whatsapp'] ?? true,
        ]);
    }

    /**
     * Update an existing emergency contact.
     */
    public static function updateContact(User $user, int $contactId, array $data): EmergencyContact
    {
        $contact = $user->emergencyContacts()->findOrFail($contactId);

        if (isset($data['phone'])) {
            $phone = preg_replace('/[^\d+]/', '', trim($data['phone']));
            if (empty($phone)) {
                throw new \InvalidArgumentException('Phone number cannot be empty.');
            }
            $contact->phone = $phone;
        }

        if (isset($data['name'])) {
            $contact->name = trim($data['name']);
        }

        if (isset($data['relationship'])) {
            $contact->relationship = trim($data['relationship']);
        }

        if (isset($data['is_primary']) && $data['is_primary']) {
            $user->emergencyContacts()->where('id', '!=', $contact->id)->update(['is_primary' => false]);
            $contact->is_primary = true;
        } elseif (isset($data['is_primary']) && !$data['is_primary']) {
            $contact->is_primary = false;
        }

        if (isset($data['notify_sms'])) {
            $contact->notify_sms = (bool) $data['notify_sms'];
        }

        if (isset($data['notify_whatsapp'])) {
            $contact->notify_whatsapp = (bool) $data['notify_whatsapp'];
        }

        $contact->save();

        return $contact;
    }

    /**
     * Delete an emergency contact.
     */
    public static function deleteContact(User $user, int $contactId): bool
    {
        $contact = $user->emergencyContacts()->find($contactId);
        if (!$contact) {
            return false;
        }

        $wasPrimary = $contact->is_primary;
        $deleted = $contact->delete();

        // If the primary was deleted, promote another contact
        if ($wasPrimary) {
            $next = $user->emergencyContacts()->first();
            if ($next) {
                $next->update(['is_primary' => true]);
            }
        }

        return $deleted;
    }

    /**
     * Trigger an emergency SOS alert.
     */
    public static function triggerAlert(User $user, array $data): array
    {
        $lat = isset($data['latitude']) ? (float) $data['latitude'] : null;
        $lng = isset($data['longitude']) ? (float) $data['longitude'] : null;
        $rideId = isset($data['ride_id']) ? (int) $data['ride_id'] : null;
        $role = $data['role'] ?? ($user->role === 'driver' ? 'driver' : 'rider');
        $address = $data['location_address'] ?? null;
        $serviceDialed = $data['emergency_service_dialed'] ?? null;
        $notes = $data['notes'] ?? null;

        $contacts = $user->emergencyContacts()->get();
        $notifiedCount = $contacts->count();

        // Create the SOS alert record
        $alert = SosAlert::create([
            'user_id' => $user->id,
            'ride_id' => $rideId,
            'role' => $role,
            'latitude' => $lat,
            'longitude' => $lng,
            'location_address' => $address,
            'status' => 'active',
            'contacts_notified_count' => $notifiedCount,
            'emergency_service_dialed' => $serviceDialed,
            'notes' => $notes,
        ]);

        // Resolve emergency hotline by country
        $country = strtoupper($user->country ?? 'USA');
        $policeNumber = match ($country) {
            'GHA', 'GH' => '112',
            'ZAF', 'ZA' => '10111',
            'NGA', 'NG' => '112',
            'IND', 'IN' => '112',
            'GBR', 'GB' => '999',
            default => '911',
        };
        $safetyDeskPhone = '+18007433692';

        $mapsUrl = ($lat && $lng) ? "https://www.google.com/maps?q={$lat},{$lng}" : null;

        // Build active ride summary if in ride
        $rideInfo = null;
        if ($rideId) {
            $ride = Ride::find($rideId);
            if ($ride) {
                $rideInfo = [
                    'ride_id' => $ride->id,
                    'status' => $ride->status,
                    'driver_name' => $ride->driver?->name ?? 'Assigned Driver',
                    'driver_phone' => $ride->driver?->phone ?? null,
                    'vehicle' => $ride->vehicle ? "{$ride->vehicle->make} {$ride->vehicle->model} ({$ride->vehicle->license_plate})" : 'RideMyCars Vehicle',
                    'pickup' => $ride->pickup_address,
                    'dropoff' => $ride->destination_address,
                ];
            }
        }

        // Notification message for in-app and emergency contacts
        $sosMessage = "EMERGENCY SOS: {$user->name} has triggered an emergency alert!";
        if ($mapsUrl) {
            $sosMessage .= " Live Location: {$mapsUrl}";
        }
        if ($rideInfo) {
            $sosMessage .= " Vehicle: {$rideInfo['vehicle']}";
        }

        // Create in-app notification for the user
        try {
            UserNotification::create([
                'user_id' => $user->id,
                'title' => '🚨 Emergency SOS Dispatched',
                'message' => "Your emergency alert has been broadcast. Safety desk (+18007433692) and {$notifiedCount} trusted contacts have been notified.",
                'type' => 'sos_alert',
                'is_read' => false,
            ]);
        } catch (\Throwable $e) {
            Log::warning("Could not create user notification for SOS: {$e->getMessage()}");
        }

        return [
            'alert_id' => $alert->id,
            'status' => 'active',
            'contacts_notified' => $contacts->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'phone' => $c->phone,
                    'relationship' => $c->relationship,
                ];
            }),
            'emergency_numbers' => [
                'police' => $policeNumber,
                'safety_desk' => $safetyDeskPhone,
                'ambulance' => ($country === 'USA') ? '911' : '112',
            ],
            'live_maps_url' => $mapsUrl,
            'sos_message' => $sosMessage,
            'ride_info' => $rideInfo,
        ];
    }
}
