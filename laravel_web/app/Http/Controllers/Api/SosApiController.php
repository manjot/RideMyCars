<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SosService;
use Illuminate\Http\Request;

class SosApiController extends Controller
{
    /**
     * Get user emergency contacts.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $contacts = SosService::getContacts($user);

        return response()->json([
            'success' => true,
            'data' => $contacts,
            'count' => $contacts->count(),
            'max_allowed' => 5,
        ]);
    }

    /**
     * Add a new emergency contact.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:25',
            'relationship' => 'nullable|string|max:50',
            'is_primary' => 'nullable|boolean',
            'notify_sms' => 'nullable|boolean',
            'notify_whatsapp' => 'nullable|boolean',
        ]);

        try {
            $contact = SosService::addContact($request->user(), $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Emergency contact added successfully.',
                'data' => $contact,
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not save emergency contact: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an emergency contact.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'sometimes|string|max:100',
            'phone' => 'sometimes|string|max:25',
            'relationship' => 'nullable|string|max:50',
            'is_primary' => 'nullable|boolean',
            'notify_sms' => 'nullable|boolean',
            'notify_whatsapp' => 'nullable|boolean',
        ]);

        try {
            $contact = SosService::updateContact($request->user(), (int) $id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Emergency contact updated.',
                'data' => $contact,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not update contact.',
            ], 404);
        }
    }

    /**
     * Delete an emergency contact.
     */
    public function destroy(Request $request, $id)
    {
        $deleted = SosService::deleteContact($request->user(), (int) $id);

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Emergency contact removed.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Contact not found.',
        ], 404);
    }

    /**
     * Trigger Emergency SOS Alert.
     */
    public function trigger(Request $request)
    {
        $request->validate([
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'ride_id' => 'nullable|integer',
            'role' => 'nullable|string|in:rider,driver',
            'location_address' => 'nullable|string',
            'emergency_service_dialed' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $payload = SosService::triggerAlert($request->user(), $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Emergency SOS alert dispatched immediately.',
                'data' => $payload,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to trigger SOS alert: ' . $e->getMessage(),
            ], 500);
        }
    }
}
