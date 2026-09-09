<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PrivacyRequest;
use App\Models\DriverProfile;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AccountDeletionController extends Controller
{
    /**
     * Show the public account deletion request page.
     */
    public function show()
    {
        $currentUser = Auth::user();
        return view('delete-account', compact('currentUser'));
    }

    /**
     * Process the account deletion request by Email or Phone.
     */
    public function process(Request $request)
    {
        $validated = $request->validate([
            'identifier' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'role' => 'nullable|string|in:all,rider,customer,driver,owner,host',
            'reason' => 'required|string|max:255',
            'comments' => 'nullable|string|max:2000',
            'confirm_deletion' => 'accepted',
        ], [
            'confirm_deletion.accepted' => 'You must check the confirmation box acknowledging that account deletion is permanent and irreversible.',
            'reason.required' => 'Please select a reason for your deletion request.',
        ]);

        $email = trim($validated['email'] ?? '');
        $phone = trim($validated['phone'] ?? '');
        $rawIdentifier = trim($validated['identifier'] ?? '');

        // If user filled the single identifier field instead of separate fields
        if (empty($email) && empty($phone) && !empty($rawIdentifier)) {
            if (filter_var($rawIdentifier, FILTER_VALIDATE_EMAIL)) {
                $email = $rawIdentifier;
            } else {
                $phone = $rawIdentifier;
            }
        }

        if (empty($email) && empty($phone)) {
            return back()
                ->withInput()
                ->withErrors(['identifier' => 'Please provide at least your registered Email Address or Mobile Number.']);
        }

        $cleanedPhone = !empty($phone) ? preg_replace('/[^\d+]/', '', $phone) : '';
        $phoneDigitsOnly = !empty($phone) ? preg_replace('/\D/', '', $phone) : '';
        $role = strtolower($validated['role'] ?? 'all');
        $reason = $validated['reason'] ?? 'User requested deletion';
        $comments = $validated['comments'] ?? '';

        $deletedCount = 0;
        $matchedUsersInfo = [];

        try {
            DB::beginTransaction();

            // Find matching users by email or phone
            $query = User::query();

            $query->where(function ($q) use ($email, $cleanedPhone, $phoneDigitsOnly) {
                $hasCondition = false;
                if (!empty($email)) {
                    $q->where('email', $email);
                    $hasCondition = true;
                }
                if (!empty($cleanedPhone)) {
                    if ($hasCondition) {
                        $q->orWhere('phone', $cleanedPhone);
                        if (strlen($phoneDigitsOnly) >= 7) {
                            $q->orWhere('phone', 'like', '%' . substr($phoneDigitsOnly, -8));
                        }
                    } else {
                        $q->where('phone', $cleanedPhone);
                        if (strlen($phoneDigitsOnly) >= 7) {
                            $q->orWhere('phone', 'like', '%' . substr($phoneDigitsOnly, -8));
                        }
                    }
                }
            });

            if ($role && $role !== 'all') {
                if ($role === 'rider' || $role === 'customer') {
                    $query->whereIn('role', ['customer', 'rider', 'user']);
                } elseif ($role === 'driver') {
                    $query->where('role', 'driver');
                } elseif ($role === 'owner' || $role === 'host') {
                    $query->whereIn('role', ['host', 'owner', 'partner']);
                }
            }

            $users = $query->get();

            $isCurrentUserDeleted = false;

            foreach ($users as $user) {
                $matchedUsersInfo[] = "ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Role: {$user->role}";

                // 1. Delete or disassociate driver profile
                if (Schema::hasTable('driver_profiles')) {
                    DriverProfile::where('user_id', $user->id)->delete();
                }

                // 2. Mark any owner vehicles as unlisted or disassociate
                if (Schema::hasTable('vehicles')) {
                    Vehicle::where('owner_id', $user->id)->update([
                        'is_available' => false,
                        'is_approved' => false,
                    ]);
                }

                // 3. Revoke Sanctum tokens
                if (method_exists($user, 'tokens')) {
                    $user->tokens()->delete();
                }

                // 4. Cancel active user rides or deliveries if any
                if (Schema::hasTable('rides')) {
                    DB::table('rides')
                        ->where('rider_id', $user->id)
                        ->whereIn('status', ['pending', 'accepted'])
                        ->update(['status' => 'cancelled', 'updated_at' => now()]);
                }

                if (Schema::hasTable('driver_bookings')) {
                    DB::table('driver_bookings')
                        ->where(function ($b) use ($user) {
                            $b->where('client_id', $user->id)->orWhere('driver_id', $user->id);
                        })
                        ->whereIn('status', ['pending', 'accepted'])
                        ->update(['status' => 'cancelled', 'updated_at' => now()]);
                }

                // 5. Check if currently authenticated
                if (Auth::check() && Auth::id() === $user->id) {
                    $isCurrentUserDeleted = true;
                }

                // 6. Delete user record
                $user->delete();
                $deletedCount++;
            }

            // Record statutory GDPR / App Store erasure log in privacy_requests
            $requestCode = 'DEL-' . date('Y') . '-' . rand(10000, 99999);
            if (Schema::hasTable('privacy_requests')) {
                PrivacyRequest::create([
                    'request_code' => $requestCode,
                    'user_id' => null,
                    'name' => $email ? explode('@', $email)[0] : ($phone ?: 'Account User'),
                    'email' => $email ?: 'phone-account@ridemycars.com',
                    'phone' => $phone ?: null,
                    'request_type' => 'erasure',
                    'details' => "Account deletion self-service request. Role filter: {$role}. Reason: {$reason}. Additional comments: {$comments}. Accounts purged: {$deletedCount}. Info: " . implode('; ', $matchedUsersInfo),
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
            }

            DB::commit();

            if ($isCurrentUserDeleted) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            $msgIdentifier = !empty($email) ? $email : $phone;

            return redirect('/delete-account')
                ->with('deletion_success', true)
                ->with('request_code', $requestCode)
                ->with('deleted_identifier', $msgIdentifier)
                ->with('deleted_count', $deletedCount);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Account deletion error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return back()
                ->withInput()
                ->withErrors(['identifier' => 'An unexpected error occurred while processing your account deletion. Please try again or contact support at support@ridemycars.com.']);
        }
    }
}
