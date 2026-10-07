<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Ride;
use App\Models\CountryPricing;
use App\Services\ActivityLogService;
use App\Services\CountryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OwnerPortalController extends Controller
{
    /**
     * Owner Dashboard View with Fleet Management & Rental Requests.
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        if (!$user || ($user->role !== 'owner' && $user->role !== 'admin')) {
            return redirect('/')->with('error', 'Access restricted to registered Vehicle Owners.');
        }

        $countryCode = CountryService::getCurrentCountryCode($request);
        $pricing = CountryPricing::forCountry($countryCode);

        if ($user->role === 'admin') {
            $vehicles = Vehicle::with('owner')->latest()->get();
            $vehicleIds = $vehicles->pluck('id')->toArray();
            $allBookings = Ride::with(['rider', 'vehicle'])
                ->where(function($q) use ($vehicleIds) {
                    $q->whereIn('vehicle_id', $vehicleIds)
                      ->orWhere('vehicle_type', 'like', 'Car Rental%');
                })
                ->latest()
                ->get();
        } else {
            // Fetch owner vehicles
            $vehicles = Vehicle::where('owner_id', $user->id)
                ->latest()
                ->get();

            $vehicleIds = $vehicles->pluck('id')->toArray();

            // If no vehicles yet and this is the default owner, link seeded cars or demo cars
            if (empty($vehicleIds) && $user->email === 'owner@ridemycars.com') {
                Vehicle::whereNull('owner_id')->update(['owner_id' => $user->id]);
                $vehicles = Vehicle::where('owner_id', $user->id)->latest()->get();
                $vehicleIds = $vehicles->pluck('id')->toArray();
            }

            // Rental Bookings for Owner's Vehicles
            $allBookings = Ride::with(['rider', 'vehicle'])
                ->whereIn('vehicle_id', $vehicleIds)
                ->latest()
                ->get();
        }

        $pendingRequests = $allBookings->where('status', 'pending');
        $activeRentals = $allBookings->whereIn('status', ['confirmed', 'accepted', 'in_progress']);
        $historyRentals = $allBookings->whereIn('status', ['completed', 'rejected', 'cancelled']);

        $totalEarned = $allBookings->whereIn('status', ['confirmed', 'accepted', 'in_progress', 'completed'])->sum('fare');

        $categories = ['Economy', 'Compact', 'Sedan', 'SUV', 'Luxury', 'Van'];

        return view('owner.dashboard', compact(
            'user',
            'vehicles',
            'pendingRequests',
            'activeRentals',
            'historyRentals',
            'allBookings',
            'totalEarned',
            'pricing',
            'categories'
        ));
    }

    /**
     * Approve incoming rental request.
     */
    public function approveRental(Ride $ride, Request $request)
    {
        $user = Auth::user();
        $this->authorizeOwnerOrAdmin($ride, $user);

        $ride->update([
            'status' => 'confirmed',
        ]);

        ActivityLogService::log(
            'rental_approved',
            "Vehicle owner {$user->name} approved rental request #{$ride->id} for {$ride->vehicle?->make} {$ride->vehicle?->model}",
            $user->id
        );

        return back()->with('success', "🎉 Rental Request #{$ride->id} approved successfully! The customer has been confirmed.");
    }

    /**
     * Reject incoming rental request.
     */
    public function rejectRental(Ride $ride, Request $request)
    {
        $user = Auth::user();
        $this->authorizeOwnerOrAdmin($ride, $user);

        $reason = $request->input('reason', 'Vehicle not available at the requested dates.');

        $ride->update([
            'status' => 'rejected',
            'cancellation_reason' => $reason,
        ]);

        ActivityLogService::log(
            'rental_rejected',
            "Vehicle owner {$user->name} rejected rental request #{$ride->id}. Reason: {$reason}",
            $user->id
        );

        return back()->with('info', "Rental Request #{$ride->id} has been rejected.");
    }

    /**
     * Add new vehicle to owner's fleet.
     */
    public function storeVehicle(Request $request)
    {
        $user = Auth::user();
        if (!$user || ($user->role !== 'owner' && $user->role !== 'admin')) {
            abort(403);
        }

        $validated = $request->validate([
            'make' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year' => 'required|integer|min:1990|max:' . (date('Y') + 2),
            'license_plate' => 'required|string|max:50|unique:vehicles,license_plate',
            'category' => 'required|string|in:Economy,Compact,Sedan,SUV,Luxury,Van',
            'type' => 'nullable|string|max:100',
            'daily_rate' => 'required|numeric|min:5|max:10000',
            'security_deposit_amount' => 'nullable|numeric|min:0',
            'seats' => 'required|integer|min:1|max:50',
            'transmission' => 'required|string|in:automatic,manual',
            'fuel_type' => 'required|string|in:petrol,diesel,hybrid,electric',
            'doors' => 'nullable|integer|min:2|max:8',
            'luggage' => 'nullable|integer|min:0|max:20',
            'min_driver_age' => 'nullable|integer|min:18|max:100',
            'vehicle_image' => 'nullable|image|max:10240',
            'image_url' => 'nullable|url|max:500',
        ]);

        $imagePath = $validated['image_url'] ?? null;
        if ($request->hasFile('vehicle_image')) {
            $imagePath = $request->file('vehicle_image')->store('vehicles', 'public');
        }

        $vehicle = Vehicle::create([
            'owner_id' => $user->id,
            'make' => $validated['make'],
            'model' => $validated['model'],
            'year' => (string) $validated['year'],
            'license_plate' => strtoupper(trim($validated['license_plate'])),
            'category' => $validated['category'],
            'type' => $validated['type'] ?? $validated['category'],
            'daily_rate' => $validated['daily_rate'],
            'security_deposit_amount' => $validated['security_deposit_amount'] ?? 200.00,
            'seats' => $validated['seats'],
            'transmission' => $validated['transmission'],
            'fuel_type' => $validated['fuel_type'],
            'doors' => $validated['doors'] ?? 4,
            'luggage' => $validated['luggage'] ?? 2,
            'min_driver_age' => $validated['min_driver_age'] ?? 18,
            'image_url' => $imagePath,
            'is_available' => true,
            'approval_status' => 'approved',
            'fuel_policy' => 'Full-to-Full',
            'daily_mileage_limit' => 300,
        ]);

        ActivityLogService::log(
            'vehicle_added',
            "Vehicle owner {$user->name} added {$vehicle->make} {$vehicle->model} ({$vehicle->license_plate}) to fleet",
            $user->id
        );

        return back()->with('success', "🚗 {$vehicle->year} {$vehicle->make} {$vehicle->model} successfully added to your rental fleet!");
    }

    /**
     * Update existing vehicle.
     */
    public function updateVehicle(Vehicle $vehicle, Request $request)
    {
        $user = Auth::user();
        if ($vehicle->owner_id !== $user->id && $user->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'make' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year' => 'required|integer|min:1990|max:' . (date('Y') + 2),
            'license_plate' => 'required|string|max:50|unique:vehicles,license_plate,' . $vehicle->id,
            'category' => 'required|string|in:Economy,Compact,Sedan,SUV,Luxury,Van',
            'type' => 'nullable|string|max:100',
            'daily_rate' => 'required|numeric|min:5|max:10000',
            'security_deposit_amount' => 'nullable|numeric|min:0',
            'seats' => 'required|integer|min:1|max:50',
            'transmission' => 'required|string|in:automatic,manual',
            'fuel_type' => 'required|string|in:petrol,diesel,hybrid,electric',
            'doors' => 'nullable|integer|min:2|max:8',
            'luggage' => 'nullable|integer|min:0|max:20',
            'min_driver_age' => 'nullable|integer|min:18|max:100',
            'vehicle_image' => 'nullable|image|max:10240',
            'image_url' => 'nullable|url|max:500',
            'is_available' => 'nullable|boolean',
        ]);

        $updates = [
            'make' => $validated['make'],
            'model' => $validated['model'],
            'year' => (string) $validated['year'],
            'license_plate' => strtoupper(trim($validated['license_plate'])),
            'category' => $validated['category'],
            'type' => $validated['type'] ?? $validated['category'],
            'daily_rate' => $validated['daily_rate'],
            'security_deposit_amount' => $validated['security_deposit_amount'] ?? $vehicle->security_deposit_amount,
            'seats' => $validated['seats'],
            'transmission' => $validated['transmission'],
            'fuel_type' => $validated['fuel_type'],
            'doors' => $validated['doors'] ?? $vehicle->doors,
            'luggage' => $validated['luggage'] ?? $vehicle->luggage,
            'min_driver_age' => $validated['min_driver_age'] ?? $vehicle->min_driver_age,
            'is_available' => $request->has('is_available'),
        ];

        if ($request->hasFile('vehicle_image')) {
            $updates['image_url'] = $request->file('vehicle_image')->store('vehicles', 'public');
        } elseif (!empty($validated['image_url'])) {
            $updates['image_url'] = $validated['image_url'];
        }

        $vehicle->update($updates);

        ActivityLogService::log(
            'vehicle_updated',
            "Vehicle owner {$user->name} updated {$vehicle->make} {$vehicle->model} ({$vehicle->license_plate})",
            $user->id
        );

        return back()->with('success', "✅ {$vehicle->make} {$vehicle->model} details updated successfully!");
    }

    /**
     * Delete vehicle from fleet.
     */
    public function deleteVehicle(Vehicle $vehicle, Request $request)
    {
        $user = Auth::user();
        if ($vehicle->owner_id !== $user->id && $user->role !== 'admin') {
            abort(403);
        }

        // Prevent delete if active bookings exist
        $hasActiveBookings = Ride::where('vehicle_id', $vehicle->id)
            ->whereIn('status', ['confirmed', 'accepted', 'in_progress'])
            ->exists();

        if ($hasActiveBookings) {
            return back()->with('error', "Cannot delete {$vehicle->make} {$vehicle->model} because it currently has active confirmed rentals. Please complete or cancel them first.");
        }

        $name = "{$vehicle->year} {$vehicle->make} {$vehicle->model} ({$vehicle->license_plate})";
        $vehicle->delete();

        ActivityLogService::log(
            'vehicle_deleted',
            "Vehicle owner {$user->name} deleted {$name} from fleet",
            $user->id
        );

        return back()->with('info', "Vehicle {$name} removed from your fleet.");
    }

    /**
     * Toggle availability online/offline.
     */
    public function toggleAvailability(Vehicle $vehicle, Request $request)
    {
        $user = Auth::user();
        if ($vehicle->owner_id !== $user->id && $user->role !== 'admin') {
            abort(403);
        }

        $vehicle->update(['is_available' => !$vehicle->is_available]);
        $statusStr = $vehicle->is_available ? 'Available for Rent' : 'Unavailable (Offline)';

        return back()->with('success', "{$vehicle->make} {$vehicle->model} is now {$statusStr}.");
    }

    /**
     * Helper to verify authorization.
     */
    protected function authorizeOwnerOrAdmin(Ride $ride, $user): void
    {
        if (!$user) {
            abort(403, 'Unauthorized.');
        }

        if ($user->role === 'admin') {
            return;
        }

        if (!$ride->vehicle || $ride->vehicle->owner_id !== $user->id) {
            abort(403, 'You do not have permission to manage this rental request.');
        }
    }
}
