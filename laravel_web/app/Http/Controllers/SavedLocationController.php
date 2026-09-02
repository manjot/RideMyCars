<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSavedLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavedLocationController extends Controller
{
    /**
     * Get saved locations for current user.
     */
    public function index()
    {
        $userId = Auth::id();
        if (!$userId) {
            $user = User::where('email', 'customer@ridemycars.com')->first() ?? User::first();
            $userId = $user ? $user->id : 1;
        }

        $locations = UserSavedLocation::where('user_id', $userId)->get()->keyBy('label');

        return response()->json([
            'success' => true,
            'locations' => $locations,
        ]);
    }

    /**
     * Store or update a saved location (Home / Office).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|in:home,office',
            'address' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'place_id' => 'nullable|string|max:255',
        ]);

        $userId = Auth::id();
        if (!$userId) {
            $user = User::where('email', 'customer@ridemycars.com')->first() ?? User::first();
            $userId = $user ? $user->id : 1;
        }

        $savedLocation = UserSavedLocation::updateOrCreate(
            [
                'user_id' => $userId,
                'label' => strtolower($validated['label']),
            ],
            [
                'address' => $validated['address'],
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'place_id' => $validated['place_id'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => ucfirst($savedLocation->label) . ' address saved successfully!',
            'location' => $savedLocation,
        ]);
    }

    /**
     * Remove a saved location.
     */
    public function destroy($id)
    {
        $userId = Auth::id();
        if (!$userId) {
            $user = User::where('email', 'customer@ridemycars.com')->first() ?? User::first();
            $userId = $user ? $user->id : 1;
        }

        $location = UserSavedLocation::where('user_id', $userId)->where('id', $id)->first();
        if ($location) {
            $location->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Saved location deleted successfully.',
        ]);
    }
}
