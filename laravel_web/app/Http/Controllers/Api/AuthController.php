<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DriverProfile;
use App\Models\Ride;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Ensure personal_access_tokens table exists
     */
    private function ensureTokensTable(): void
    {
        try {
            if (!Schema::hasTable('personal_access_tokens')) {
                Schema::create('personal_access_tokens', function (Blueprint $table) {
                    $table->id();
                    $table->morphs('tokenable');
                    $table->text('name');
                    $table->string('token', 64)->unique();
                    $table->text('abilities')->nullable();
                    $table->timestamp('last_used_at')->nullable();
                    $table->timestamp('expires_at')->nullable()->index();
                    $table->timestamps();
                });
            }
        } catch (\Throwable $e) {
            Log::warning('Personal access tokens table check warning: ' . $e->getMessage());
        }
    }

    /**
     * Safely generate API access token
     */
    private function issueToken(User $user): string
    {
        $this->ensureTokensTable();

        try {
            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }
            return $user->createToken('flutter_app')->plainTextToken;
        } catch (\Throwable $e) {
            Log::warning('Sanctum token generation fallback: ' . $e->getMessage());
            // Fallback unique token string
            return 'rmc_' . bin2hex(random_bytes(24)) . '_' . $user->id;
        }
    }

    /**
     * User registration (Rider or Driver)
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:6|confirmed',
                'role' => 'nullable|string|in:customer,rider,driver',
                'phone' => 'nullable|string|max:50',
            ]);

            $role = in_array($validated['role'] ?? 'customer', ['rider', 'customer']) ? 'customer' : 'driver';

            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $role,
            ];

            if (Schema::hasColumn('users', 'account_status')) {
                $userData['account_status'] = 'active';
            }
            if (Schema::hasColumn('users', 'membership_type')) {
                $userData['membership_type'] = 'free';
            }
            if (Schema::hasColumn('users', 'membership_status')) {
                $userData['membership_status'] = 'active';
            }
            if (!empty($validated['phone']) && Schema::hasColumn('users', 'phone')) {
                $userData['phone'] = $validated['phone'];
            }

            $user = User::create($userData);

            // If registering as driver, initialize driver profile
            if ($role === 'driver' && Schema::hasTable('driver_profiles')) {
                DriverProfile::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'license_number' => 'DL-' . strtoupper(bin2hex(random_bytes(4))),
                        'hourly_rate' => 35.00,
                        'country' => 'USA',
                        'is_available' => true,
                        'verification_status' => 'pending',
                        'rating' => 5.0,
                        'total_trips' => 0,
                    ]
                );
            }

            $token = $this->issueToken($user);

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
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('API Register Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * User login
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', trim($request->email))->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid email address or password.',
                ], 401);
            }

            if (isset($user->account_status) && in_array($user->account_status, ['suspended', 'deactivated'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been suspended or deactivated. Please contact support.',
                ], 403);
            }

            $token = $this->issueToken($user);

            $driverProfile = null;
            try {
                if ($user->role === 'driver' || $user->driverProfile) {
                    $driverProfile = $user->driverProfile;
                }
            } catch (\Throwable $e) {
                // Driver profile relationship optional
            }

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
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('API Login Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Login error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get authenticated user profile & current state
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $driverProfile = null;
            try {
                $driverProfile = $user->driverProfile;
            } catch (\Throwable $e) {
                // optional
            }

            // Check for active ongoing ride
            $activeRide = null;
            try {
                $activeRide = Ride::where(function ($q) use ($user) {
                    $q->where('rider_id', $user->id)
                      ->orWhere('driver_id', $user->id);
                })
                ->whereIn('status', ['pending', 'accepted', 'en_route', 'arrived', 'in_progress'])
                ->latest()
                ->first();
            } catch (\Throwable $e) {
                // optional
            }

            $unreadCount = 0;
            try {
                if (Schema::hasTable('user_notifications')) {
                    $unreadCount = UserNotification::where('user_id', $user->id)
                        ->where('is_read', false)
                        ->count();
                }
            } catch (\Throwable $e) {
                // optional
            }

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
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Profile retrieval failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * User logout
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            if ($request->user() && $request->user()->currentAccessToken()) {
                $request->user()->currentAccessToken()->delete();
            }
        } catch (\Throwable $e) {
            // Ignore logout token deletion issues
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }
}
