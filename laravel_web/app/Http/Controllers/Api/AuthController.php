<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DriverProfile;
use App\Models\Ride;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * User registration (Rider or Driver)
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'nullable|string|in:customer,driver',
            'phone' => 'nullable|string|max:50',
        ]);

        $role = $validated['role'] ?? 'customer';

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $role,
            'account_status' => 'active',
            'membership_type' => 'free',
            'membership_status' => 'active',
        ]);

        // If registering as driver, initialize driver profile
        if ($role === 'driver') {
            DriverProfile::create([
                'user_id' => $user->id,
                'is_available' => true,
                'verification_status' => 'pending',
                'rating' => 5.0,
                'total_completed_trips' => 0,
            ]);
        }

        $token = $user->createToken('flutter_app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'role' => $user->role,
        ], 201);
    }

    /**
     * User login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        if (in_array($user->account_status, ['suspended', 'deactivated'])) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been suspended or deactivated. Please contact support.',
            ], 403);
        }

        // Revoke older tokens for clean session
        $user->tokens()->delete();

        $token = $user->createToken('flutter_app')->plainTextToken;

        $driverProfile = $user->role === 'driver' || $user->driverProfile ? $user->driverProfile : null;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'role' => $user->role,
            'driver_profile' => $driverProfile,
        ]);
    }

    /**
     * Get authenticated user profile & current state
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $driverProfile = $user->driverProfile;

        // Check for active ongoing ride
        $activeRide = Ride::where(function ($q) use ($user) {
            $q->where('rider_id', $user->id)
              ->orWhere('driver_id', $user->id);
        })
        ->whereIn('status', ['pending', 'accepted', 'en_route', 'arrived', 'in_progress'])
        ->latest()
        ->first();

        $unreadCount = UserNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'role' => $user->role,
            'driver_profile' => $driverProfile,
            'active_ride' => $activeRide,
            'unread_notifications_count' => $unreadCount,
        ]);
    }

    /**
     * User logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }
}
