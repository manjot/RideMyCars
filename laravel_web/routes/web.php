<?php

use App\Http\Controllers\AdminFinancialExportController;
use App\Http\Controllers\DeliveryTrackerController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DriverBookingController;
use App\Http\Controllers\PackageDeliveryController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/onboarding', function () {
    return view('onboarding');
});

Route::get('/membership', function () {
    return view('membership');
});

Route::post('/membership/subscribe', function (\Illuminate\Http\Request $request) {
    $user = auth()->user();
    if (!$user) {
        return redirect('/login')->with('error', 'Please sign in or create an account to activate your Club Membership.');
    }
    
    $paymentMethod = $request->payment_method ?? 'Credit / Debit Card';

    $user->update([
        'membership_type' => 'club',
        'membership_status' => 'active',
        'membership_price' => 250.00,
    ]);

    \App\Services\ActivityLogService::log('membership_created', "Subscribed to Club Membership ($250/mo) via {$paymentMethod}", $user->id);

    return redirect('/membership')->with('success', '🎉 Welcome to Club Membership! Your $250/mo executive privileges are now active.');
});

Route::post('/membership/corporate-request', function (\Illuminate\Http\Request $request) {
    $user = auth()->user();
    if (!$user) {
        return redirect('/login')->with('error', 'Please sign in or create an account to request a Corporate Membership.');
    }

    $request->validate(['company_name' => 'required|string|max:255']);

    $user->update([
        'membership_type' => 'corporate',
        'membership_status' => 'pending',
        'corporate_company_name' => $request->company_name,
        'corporate_billing_email' => $user->email,
    ]);

    \App\Services\ActivityLogService::log('membership_created', "Requested Corporate Membership for {$request->company_name}", $user->id);

    return redirect('/membership')->with('success', "🎉 Corporate Membership request for '{$request->company_name}' submitted! Our concierge team will contact you shortly.");
});

Route::get('/delivery', function (\Illuminate\Http\Request $request) {
    return view('delivery', [
        'pickup' => $request->query('pickup'),
        'dropoff' => $request->query('dropoff'),
    ]);
});

Route::post('/delivery/book', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'pickup_location' => 'required|string|max:255',
        'dropoff_location' => 'required|string|max:255',
    ]);

    $riderId = auth()->id() ?? \App\Models\User::first()->id ?? 1;

    $digitalReceipt = 'REC-' . strtoupper(\Illuminate\Support\Str::random(8));

    $ride = \App\Models\Ride::create([
        'rider_id' => $riderId,
        'pickup_location' => $request->pickup_location,
        'dropoff_location' => $request->dropoff_location,
        'vehicle_type' => 'Package Delivery (' . ($request->package_size ?? 'Small') . ')',
        'payment_method' => $request->payment_method ?? 'Credit Card',
        'notes' => $request->notes,
        'signature_required' => $request->has('signature_required'),
        'climate_control' => $request->has('climate_control'),
        'discreet_packaging' => $request->has('discreet_packaging'),
        'digital_receipt_code' => $digitalReceipt,
        'status' => 'pending',
    ]);

    \App\Services\ActivityLogService::log('delivery_created', "Created package delivery #{$ride->id} with receipt {$digitalReceipt}", $riderId);

    return redirect('/delivery')->with('success', "Package dispatched successfully! Digital Receipt Code: {$digitalReceipt}. A driver is being assigned.");
});

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::post('/login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (auth()->attempt($credentials)) {
        $user = auth()->user();
        if (in_array($user->account_status, ['suspended', 'deactivated'])) {
            $reason = $user->suspension_reason ?? 'Administrative policy violation';
            auth()->logout();
            return back()->withErrors([
                'email' => "Your account has been {$user->account_status}. Reason: {$reason}. Please contact legal@ridemycars.com for legal compliance appeal."
            ])->onlyInput('email');
        }

        $request->session()->regenerate();
        
        \App\Services\ActivityLogService::log('login', 'User logged in successfully', auth()->id());
        \App\Services\NotificationService::notifyLogin(auth()->user());

        if (auth()->user()->role === 'driver') {
            return redirect('/driver/dashboard');
        }
        return redirect()->intended('/');
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ])->onlyInput('email');
});

Route::get('/terms-and-conditions', function () {
    return view('terms');
});

Route::get('/privacy-policy', function () {
    return view('privacy');
});

Route::get('/refund-cancellation-policy', function () {
    return view('refund');
});

Route::get('/refund-cancellation', function () {
    return view('refund');
});

Route::get('/refund', function () {
    return view('refund');
});

Route::get('/contact', function () {
    return view('contact');
});

Route::get('/contact-us', function () {
    return view('contact');
});

Route::post('/contact', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'message' => 'required|string',
    ]);

    return back()->with('success', 'Thank you for reaching out! Your message has been received by the RideMyCars support desk.');
});

Route::get('/payment/verify-details/{serviceType}/{serviceId}', [\App\Http\Controllers\StripeVerificationController::class, 'showDetails'])->name('payment.verify-details');

// User Saved Locations API (Home / Office)
Route::get('/api/user/saved-locations', [\App\Http\Controllers\SavedLocationController::class, 'index']);
Route::post('/api/user/saved-locations', [\App\Http\Controllers\SavedLocationController::class, 'store']);
Route::delete('/api/user/saved-locations/{id}', [\App\Http\Controllers\SavedLocationController::class, 'destroy']);

// Places Autocomplete, Geocoding & Reverse Geocoding API
Route::get('/api/places/autocomplete', [\App\Http\Controllers\Api\PlacesApiController::class, 'autocomplete']);
Route::get('/api/places/details', [\App\Http\Controllers\Api\PlacesApiController::class, 'details']);
Route::get('/api/places/geocode', [\App\Http\Controllers\Api\PlacesApiController::class, 'geocode']);
Route::get('/api/places/reverse', [\App\Http\Controllers\Api\PlacesApiController::class, 'reverse']);

// Payment Methods API
Route::get('/api/payment-methods', [\App\Http\Controllers\PaymentMethodController::class, 'index']);
Route::post('/api/payment-methods/save-stripe', [\App\Http\Controllers\PaymentMethodController::class, 'storeStripe']);
Route::post('/api/payment-methods/{id}/default', [\App\Http\Controllers\PaymentMethodController::class, 'setDefault']);
Route::delete('/api/payment-methods/{id}', [\App\Http\Controllers\PaymentMethodController::class, 'destroy']);

Route::middleware('auth')->group(function () {
    Route::get('/disputes', [\App\Http\Controllers\DisputeController::class, 'index']);
    Route::get('/disputes/create', [\App\Http\Controllers\DisputeController::class, 'create']);
    Route::post('/disputes', [\App\Http\Controllers\DisputeController::class, 'store']);

    Route::post('/api/cancellation/preview', function (\Illuminate\Http\Request $request) {
        $type = $request->input('service_type', 'ride');
        $id = $request->input('id');

        $model = match ($type) {
            'ride' => \App\Models\Ride::find($id),
            'chauffeur', 'rental' => \App\Models\DriverBooking::find($id),
            'delivery' => \App\Models\PackageDelivery::find($id),
            default => null,
        };

        if (!$model) {
            return response()->json(['error' => 'Booking record not found'], 44);
        }

        $calc = \App\Services\RefundService::calculateCancellation($model, $type);
        return response()->json($calc);
    });

    Route::post('/api/cancellation/confirm', function (\Illuminate\Http\Request $request) {
        $type = $request->input('service_type', 'ride');
        $id = $request->input('id');
        $reason = $request->input('reason', 'User requested cancellation');

        $model = match ($type) {
            'ride' => \App\Models\Ride::find($id),
            'chauffeur', 'rental' => \App\Models\DriverBooking::find($id),
            'delivery' => \App\Models\PackageDelivery::find($id),
            default => null,
        };

        if (!$model) {
            return response()->json(['error' => 'Booking record not found'], 404);
        }

        $result = \App\Services\RefundService::processRefund($model, $type, $reason);
        return response()->json([
            'success' => true,
            'message' => "Booking cancelled successfully. Eligible refund of \${$result['eligible_refund_amount']} processed.",
            'result' => $result,
        ]);
    });
});

Route::post('/api/otp/send', function (\Illuminate\Http\Request $request) {
    $phone = $request->input('phone');
    $email = $request->input('email');
    $action = $request->input('action', 'login'); // 'login' or 'register'

    // 1. Phone OTP (Worldwide SMS via Twilio)
    if (!empty($phone)) {
        $smsService = app(\App\Services\TwilioSmsService::class);
        $formattedPhone = $smsService->formatE164($phone);
        $rawCleanPhone = preg_replace('/\s+/', '', $phone);

        $digitsOnly = preg_replace('/\D/', '', $formattedPhone);
        if (strlen($digitsOnly) < 7) {
            return response()->json([
                'success' => false,
                'error' => 'Please enter a valid mobile number with country code.'
            ], 422);
        }

        // Check if phone number exists in database
        $user = \App\Models\User::where('phone', $formattedPhone)
            ->orWhere('phone', $phone)
            ->orWhere('phone', $rawCleanPhone)
            ->first();

        // If phone is not in database during login, initiate registration process
        if ($action === 'login' && !$user) {
            return response()->json([
                'success' => false,
                'user_exists' => false,
                'not_found' => true,
                'error' => "No account found with {$formattedPhone}. Starting registration...",
                'redirect' => '/signup?phone=' . urlencode($formattedPhone) . '&from=login',
                'phone' => $formattedPhone,
            ], 404);
        }

        // If attempting to register with already existing phone:
        if ($action === 'register' && $user) {
            return response()->json([
                'success' => false,
                'user_exists' => true,
                'error' => "This phone number is already registered. Please sign in instead.",
                'redirect' => '/login?phone=' . urlencode($formattedPhone),
                'phone' => $formattedPhone,
            ], 422);
        }

        // Generate 4-digit OTP code
        $otp = str_pad((string) rand(1000, 9999), 4, '0', STR_PAD_LEFT);

        // Store OTP in Cache with exact 2-minute validity
        \Illuminate\Support\Facades\Cache::put('otp_phone_' . $formattedPhone, $otp, now()->addMinutes(2));
        if ($rawCleanPhone !== $formattedPhone) {
            \Illuminate\Support\Facades\Cache::put('otp_phone_' . $rawCleanPhone, $otp, now()->addMinutes(2));
        }

        // Dispatch SMS via Twilio Gateway
        $result = $smsService->sendOtp($formattedPhone, $otp);

        \Illuminate\Support\Facades\Log::info("OTP generated for phone {$formattedPhone}: {$otp}. Action: {$action}. Twilio: " . ($result['success'] ? 'SUCCESS' : 'FAILED'));

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Unable to send SMS verification code. Please try again.',
                'code' => $result['code'] ?? 500,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'user_exists' => ($user !== null),
            'action' => $action,
            'message' => "Verification code sent to {$formattedPhone}",
            'phone' => $formattedPhone,
            'expires_in' => 120, // 2 minutes
        ]);
    }

    // 2. Email OTP Fallback
    if (!empty($email)) {
        $request->validate(['email' => 'required|email']);
        $otp = str_pad((string) rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        \Illuminate\Support\Facades\Cache::put('otp_' . $email, $otp, now()->addMinutes(2));

        $mailError = null;
        try {
            \Illuminate\Support\Facades\Mail::raw("{$otp} is OTP for your RideMyCars account. OTP is valid for 2 minutes. Do not share this OTP with anyone. For any help please visit https://ridemycars.com", function ($message) use ($email) {
                $message->to($email)->subject('Your RideMyCars Verification Code');
            });
        } catch (\Throwable $e) {
            $mailError = $e->getMessage();
            \Illuminate\Support\Facades\Log::error('Mail error: ' . $mailError);
            \Illuminate\Support\Facades\Log::info("OTP for {$email} is {$otp}");
        }

        if ($mailError) {
            return response()->json([
                'success' => false,
                'message' => 'OTP generated but email failed',
                'mail_error' => $mailError,
                'debug_otp' => $otp
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully to email',
            'expires_in' => 120,
        ]);
    }

    return response()->json([
        'success' => false,
        'error' => 'Please provide a mobile phone number or email address.'
    ], 422);
});

Route::post('/api/otp/verify', function (\Illuminate\Http\Request $request) {
    $phone = $request->input('phone');
    $email = $request->input('email');
    $inputOtp = trim((string) $request->input('otp', ''));

    if (empty($inputOtp)) {
        return response()->json(['success' => false, 'error' => 'Verification code is required.'], 422);
    }

    // 1. Phone Verification (Login or Registration)
    if (!empty($phone)) {
        $smsService = app(\App\Services\TwilioSmsService::class);
        $formattedPhone = $smsService->formatE164($phone);
        $rawCleanPhone = preg_replace('/\s+/', '', $phone);

        $cachedOtp = \Illuminate\Support\Facades\Cache::get('otp_phone_' . $formattedPhone)
                  ?? \Illuminate\Support\Facades\Cache::get('otp_phone_' . $rawCleanPhone)
                  ?? \Illuminate\Support\Facades\Cache::get('otp_phone_' . $phone);

        if ($cachedOtp && (string) $cachedOtp === $inputOtp) {
            \Illuminate\Support\Facades\Cache::forget('otp_phone_' . $formattedPhone);
            \Illuminate\Support\Facades\Cache::forget('otp_phone_' . $rawCleanPhone);
            \Illuminate\Support\Facades\Cache::forget('otp_phone_' . $phone);

            // Find existing user or Register new user via phone
            $user = \App\Models\User::where('phone', $formattedPhone)
                ->orWhere('phone', $phone)
                ->orWhere('phone', $rawCleanPhone)
                ->first();

            $isNewUser = false;
            if (!$user) {
                $isNewUser = true;
                $digits = preg_replace('/\D/', '', $formattedPhone);
                $rawName = $request->input('name') ?: (trim($request->input('first_name', '') . ' ' . $request->input('last_name', '')));
                $userName = $rawName ?: ('Rider ' . substr($digits, -4));
                
                $userEmail = trim((string) $request->input('email', ''));
                if (empty($userEmail)) {
                    $userEmail = $digits . '@phone.ridemycars.com';
                } else {
                    if (\App\Models\User::where('email', $userEmail)->exists()) {
                        return response()->json([
                            'success' => false,
                            'error' => 'This email address is already in use by another account.',
                            'message' => 'This email address is already in use by another account.'
                        ], 422);
                    }
                }

                $role = $request->input('role', 'customer');
                if (!in_array($role, ['customer', 'rider', 'driver', 'owner'])) {
                    $role = 'customer';
                }

                $rawPassword = $request->input('password');
                $hashedPassword = !empty($rawPassword) 
                    ? \Illuminate\Support\Facades\Hash::make($rawPassword) 
                    : \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(24));

                $user = \App\Models\User::create([
                    'name' => $userName,
                    'phone' => $formattedPhone,
                    'phone_verified_at' => now(),
                    'email' => $userEmail,
                    'password' => $hashedPassword,
                    'role' => $role,
                    'terms_accepted' => true,
                    'terms_accepted_at' => now(),
                    'terms_version' => '2026-08-23',
                    'account_status' => 'active',
                ]);

                if ($role === 'driver') {
                    try {
                        $licNumber = trim($request->input('license_number', ''));
                        if (!$licNumber || \App\Models\DriverProfile::where('license_number', $licNumber)->exists()) {
                            $licNumber = 'DL-' . strtoupper(\Illuminate\Support\Str::random(6));
                        }
                        \App\Models\DriverProfile::firstOrCreate(
                            ['user_id' => $user->id],
                            [
                                'license_number' => $licNumber,
                                'verification_status' => 'verified',
                                'is_available' => true,
                                'rating' => 5.0,
                                'total_trips' => 0,
                                'country' => $request->input('country', 'USA'),
                                'service_area' => 'Global',
                                'experience_years' => (int) $request->input('experience_years', 5),
                                'hourly_rate' => (float) $request->input('hourly_rate', 25.00),
                                'daily_rate' => (float) $request->input('daily_rate', 170.00),
                            ]
                        );
                    } catch (\Throwable $e) {}
                }
            } else {
                $user->phone = $formattedPhone;
                $user->phone_verified_at = now();
                $user->save();
            }

            if (in_array($user->account_status, ['suspended', 'deactivated'])) {
                $reason = $user->suspension_reason ?? 'Administrative policy violation';
                return response()->json([
                    'success' => false,
                    'error' => "Your account is {$user->account_status}. Reason: {$reason}. Contact legal@ridemycars.com."
                ], 403);
            }

            auth()->login($user);
            $request->session()->regenerate();

            \App\Services\ActivityLogService::log(
                $isNewUser ? 'register' : 'login',
                $isNewUser ? "User registered via Phone OTP ({$formattedPhone})" : "User logged in via Phone OTP ({$formattedPhone})",
                $user->id
            );
            \App\Services\NotificationService::notifyLogin($user);

            $token = method_exists($user, 'createToken') ? $user->createToken('auth_token')->plainTextToken : null;
            $redirectUrl = session()->pull('url.intended', $user->role === 'driver' ? '/driver/dashboard' : '/');

            return response()->json([
                'success' => true,
                'message' => $isNewUser ? 'Account registered successfully! Welcome to RideMyCars.' : 'Login successful!',
                'is_new_user' => $isNewUser,
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                ],
                'redirect' => $redirectUrl,
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Invalid or expired OTP. OTP is valid for 2 minutes.'
        ], 422);
    }

    // 2. Email Verification
    if (!empty($email)) {
        $cachedOtp = \Illuminate\Support\Facades\Cache::get('otp_' . $email);
        if ($cachedOtp && (string) $cachedOtp === $inputOtp) {
            \Illuminate\Support\Facades\Cache::forget('otp_' . $email);
            $user = \App\Models\User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => explode('@', $email)[0],
                    'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(16)),
                    'role' => 'customer',
                    'terms_accepted' => true,
                    'terms_accepted_at' => now(),
                    'terms_version' => '2026-08-23',
                    'account_status' => 'active',
                ]
            );
            auth()->login($user);
            $request->session()->regenerate();
            \App\Services\NotificationService::notifyLogin($user);
            return response()->json([
                'success' => true,
                'message' => 'Verified successfully',
                'redirect' => session()->pull('url.intended', '/')
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Invalid or expired OTP. OTP is valid for 2 minutes.'
        ], 422);
    }

    return response()->json(['success' => false, 'error' => 'Phone or Email required.'], 422);
});

Route::get('/signup', function () {
    return view('signup');
})->name('signup');

Route::get('/driver-signup', function () {
    return view('signup', [
        'title' => 'Become a Driver', 
        'subtitle' => 'Start earning by driving with RideMyCars.',
        'role' => 'driver'
    ]);
});

Route::get('/owner-signup', function () {
    return view('signup', [
        'title' => 'List Your Vehicle', 
        'subtitle' => 'Earn passive income by renting out your car.',
        'role' => 'owner'
    ]);
});

Route::post('/signup', function (\Illuminate\Http\Request $request) {
    $validated = $request->validate([
        'first_name' => ['required', 'string', 'max:255'],
        'last_name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
        'role' => ['nullable', 'string', 'in:customer,driver,owner'],
        'terms' => ['required', 'accepted'],
        'phone' => ['nullable', 'string', 'max:50'],
    ]);

    $userData = [
        'name' => $validated['first_name'] . ' ' . $validated['last_name'],
        'email' => $validated['email'],
        'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
        'role' => $validated['role'] ?? 'customer',
        'terms_accepted' => true,
        'terms_accepted_at' => now(),
        'terms_version' => '2026-08-23',
        'account_status' => 'active',
    ];

    if (!empty($request->phone)) {
        $smsService = app(\App\Services\TwilioSmsService::class);
        $userData['phone'] = $smsService->formatE164($request->phone);
    }

    $user = \App\Models\User::create($userData);

    if ($user->role === 'driver') {
        $photoPath = null;
        $frontPath = null;
        $backPath = null;

        if ($request->hasFile('driver_photo')) {
            $photoPath = $request->file('driver_photo')->store('drivers/photos', 'public');
        }
        if ($request->hasFile('license_front_image')) {
            $frontPath = $request->file('license_front_image')->store('drivers/licenses', 'public');
        }
        if ($request->hasFile('license_back_image')) {
            $backPath = $request->file('license_back_image')->store('drivers/licenses', 'public');
        }

        $licNumber = trim($request->license_number ?? '');
        if (!$licNumber || \App\Models\DriverProfile::where('license_number', $licNumber)->exists()) {
            $licNumber = 'DL-' . strtoupper(\Illuminate\Support\Str::random(6));
        }

        \App\Models\DriverProfile::create([
            'user_id' => $user->id,
            'license_number' => $licNumber,
            'license_expiry' => $request->license_expiry ?? date('Y-m-d', strtotime('+3 years')),
            'country' => $request->country ?? 'USA',
            'experience_years' => $request->experience_years ?? 5,
            'hourly_rate' => $request->hourly_rate ?? 25.00,
            'daily_rate' => $request->daily_rate ?? 170.00,
            'weekly_rate' => $request->weekly_rate ?? 950.00,
            'image_url' => $photoPath,
            'license_front_image' => $frontPath,
            'license_back_image' => $backPath,
            'bio' => $request->bio,
            'is_available' => true,
            'rating' => 5.00,
            'license_verification_status' => $frontPath ? 'submitted' : 'unverified',
            'photo_formality_status' => $photoPath ? 'submitted' : 'pending',
        ]);
    }

    if ($user->role === 'owner' || $request->filled('vehicle_make') || $request->filled('license_plate')) {
        $imagePath = null;
        if ($request->hasFile('vehicle_image')) {
            $imagePath = $request->file('vehicle_image')->store('vehicles', 'public');
        }

        $plate = trim($request->license_plate ?? '');
        if (!$plate || \App\Models\Vehicle::where('license_plate', $plate)->exists()) {
            $plate = ($plate ? $plate . '-' : 'REG-') . rand(1000, 9999);
        }

        \App\Models\Vehicle::create([
            'owner_id' => $user->id,
            'make' => $request->vehicle_make ?? 'Mercedes-Benz',
            'model' => $request->vehicle_model ?? 'S-Class',
            'year' => $request->vehicle_year ?? date('Y'),
            'license_plate' => $plate,
            'type' => $request->vehicle_type ?? 'Executive Sedan',
            'daily_rate' => $request->daily_rate ?? 150.00,
            'is_available' => true,
            'image_url' => $imagePath,
        ]);
    }

    auth()->login($user);

    \App\Services\ActivityLogService::log('register', "User registered as {$user->role}", $user->id);
    \App\Services\NotificationService::notifyLogin($user);

    if ($user->role === 'owner') {
        return redirect('/rent')->with('success', '🎉 Account created & vehicle listed successfully! Your car is now available for rental on RideMyCars.');
    }

    return redirect('/');
});

Route::post('/logout', function (\Illuminate\Http\Request $request) {
    if (auth()->check()) {
        \App\Services\ActivityLogService::log('logout', 'User logged out', auth()->id());
    }
    auth()->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
});

// Package Delivery Routes
Route::get('/delivery', [PackageDeliveryController::class, 'index']);
Route::post('/delivery/calculate-price', [PackageDeliveryController::class, 'calculatePrice']);
Route::post('/api/delivery/calculate-price', [PackageDeliveryController::class, 'calculatePrice']);
Route::post('/delivery/book', [PackageDeliveryController::class, 'storeBooking']);
Route::get('/delivery/tracker', function () {
    if (auth()->check()) {
        $latestDelivery = \App\Models\PackageDelivery::where('customer_id', auth()->id())->latest()->first();
        if ($latestDelivery) {
            return redirect()->route('package-delivery.tracker', $latestDelivery->id);
        }
    }
    $delivery = \App\Models\PackageDelivery::latest()->first();
    if ($delivery) {
        return redirect()->route('package-delivery.tracker', $delivery->id);
    }
    return redirect('/delivery')->with('info', 'Please book a package delivery to view live tracking.');
});
Route::get('/delivery/{delivery}/tracker', [PackageDeliveryController::class, 'tracker'])->name('package-delivery.tracker');
Route::get('/api/package-delivery/{id}/status', [PackageDeliveryController::class, 'statusApi']);
Route::post('/api/package-delivery/{id}/verify-otp', [PackageDeliveryController::class, 'verifyOtp']);
Route::post('/api/package-delivery/{id}/update-status', [PackageDeliveryController::class, 'updateDeliveryStatus']);

// Driver Hiring Routes
Route::get('/hire-driver', [DriverBookingController::class, 'index']);
Route::get('/hire-driver/{driverProfile}', [DriverBookingController::class, 'show'])->where('driverProfile', '[0-9]+');
Route::get('/hire-driver/book/{driverProfile}', [DriverBookingController::class, 'bookForm']);
Route::post('/hire-driver/calculate-price', [DriverBookingController::class, 'calculatePrice']);
Route::post('/hire-driver/book', [DriverBookingController::class, 'storeBooking']);
Route::get('/driver-booking/{booking}', [DriverBookingController::class, 'confirmation'])->name('driver-booking.confirmation');
Route::post('/driver-booking/{booking}/update-status', [DriverBookingController::class, 'updateBookingStatus']);
Route::post('/driver-booking/{booking}/review', [DriverBookingController::class, 'storeReview']);
Route::post('/driver/verify-license', [DriverBookingController::class, 'submitLicenseVerification']);
Route::post('/driver/submit-guarantor', [DriverBookingController::class, 'submitGuarantorVerification']);
Route::post('/rental-inspection/upload', [DriverBookingController::class, 'storeRentalInspection']);

// Vehicle Rentals & Rides
Route::get('/terms', function () { return view('terms'); });
Route::get('/privacy', function () { return view('privacy'); });
Route::get('/pricing', function () { return view('pricing'); });
Route::get('/about', function () { return view('about'); });
Route::redirect('/company', '/about');
Route::get('/safety', function () { return view('safety'); });
Route::get('/become-driver', function () { return view('become-driver'); });
Route::get('/become-owner', function () { return view('become-owner'); });
Route::get('/blogs', function () { return view('blogs'); });

Route::get('/rent', [\App\Http\Controllers\VehicleRentalController::class, 'index']);
Route::get('/api/rent/search', [\App\Http\Controllers\VehicleRentalController::class, 'searchApi']);
Route::get('/rent/{vehicle}', [\App\Http\Controllers\VehicleRentalController::class, 'show'])->where('vehicle', '[0-9]+');
Route::post('/rent/{vehicle}/book', [\App\Http\Controllers\VehicleRentalController::class, 'storeBooking'])->where('vehicle', '[0-9]+');
Route::get('/rent/booking/{ride}/voucher', [\App\Http\Controllers\VehicleRentalController::class, 'voucher'])->name('rent.voucher');
Route::post('/rent/booking/{ride}/cancel', [\App\Http\Controllers\VehicleRentalController::class, 'cancelBooking']);
Route::post('/rent/booking/{ride}/modify', [\App\Http\Controllers\VehicleRentalController::class, 'modifyBooking']);

Route::get('/ride', function () {
    $vehicles = \App\Models\Vehicle::all();
    return view('ride', compact('vehicles'));
});

// Dynamic Ride Categories with live pricing API
Route::get('/api/ride/categories', function (\Illuminate\Http\Request $request) {
    $dist = floatval($request->input('distance_km', 10.0));
    $dur = intval($request->input('duration_minutes', 15));
    $stopsCount = intval($request->input('stops_count', 0));

    $categories = [
        [
            'id' => 'economy',
            'name' => 'Economy',
            'icon' => '🚗',
            'capacity' => '1–4 passengers',
            'eta_minutes' => 3,
            'multiplier' => 1.0,
            'description' => 'Affordable, everyday rides',
        ],
        [
            'id' => 'standard',
            'name' => 'Standard',
            'icon' => '🚘',
            'capacity' => '1–4 passengers',
            'eta_minutes' => 4,
            'multiplier' => 1.2,
            'description' => 'Comfortable sedans with extra legroom',
        ],
        [
            'id' => 'suv',
            'name' => 'SUV',
            'icon' => '🚙',
            'capacity' => '1–6 passengers',
            'eta_minutes' => 6,
            'multiplier' => 1.5,
            'description' => 'Spacious SUVs for groups and extra luggage',
        ],
        [
            'id' => 'xl',
            'name' => 'XL',
            'icon' => '🚐',
            'capacity' => '1–6 passengers',
            'eta_minutes' => 7,
            'multiplier' => 1.8,
            'description' => 'Extra large vans for families and events',
        ],
        [
            'id' => 'luxury',
            'name' => 'Luxury',
            'icon' => '🏎️',
            'capacity' => '1–4 passengers',
            'eta_minutes' => 5,
            'multiplier' => 2.2,
            'description' => 'Top-tier luxury vehicles with professional drivers',
        ],
    ];

    foreach ($categories as &$cat) {
        $breakdown = \App\Services\PricingService::calculateTripFareWithBreakdown($dist, $dur, $cat['name'], $stopsCount);
        $cat['fare'] = $breakdown['total_fare'];
        $cat['fare_formatted'] = '$' . number_format($breakdown['total_fare'], 2);
        $cat['breakdown'] = $breakdown;
    }

    return response()->json([
        'success' => true,
        'distance_km' => $dist,
        'duration_minutes' => $dur,
        'stops_count' => $stopsCount,
        'categories' => $categories,
    ]);
});

// Ride Cancellation Endpoint
Route::post('/api/ride/{id}/cancel', function ($id, \Illuminate\Http\Request $request) {
    $ride = \App\Models\Ride::find($id);
    if (!$ride) return response()->json(['error' => 'Ride not found'], 404);

    $reason = $request->input('reason', 'Cancelled by user');
    $ride->update([
        'status' => 'cancelled',
        'cancellation_reason' => $reason,
    ]);

    \App\Models\RideAssignment::where('ride_id', $ride->id)->update(['status' => 'rejected']);

    return response()->json([
        'success' => true,
        'message' => 'Ride cancelled successfully.',
        'status' => 'cancelled',
    ]);
});

Route::post('/ride/book', function (\Illuminate\Http\Request $request) {
    try {
        $request->validate([
            'pickup_location' => 'required|string|max:255',
            'dropoff_location' => 'required|string|max:255',
            'pickup_lat' => 'nullable|numeric',
            'pickup_lng' => 'nullable|numeric',
            'dropoff_lat' => 'nullable|numeric',
            'dropoff_lng' => 'nullable|numeric',
            'phone_number' => 'nullable|string|max:50',
            'passenger_phone' => 'nullable|string|max:50',
            'vehicle_type' => 'nullable|string|max:255',
            'payment_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'amount' => 'nullable|numeric',
            'stops' => 'nullable|array',
            'stops.*' => 'nullable',
            'distance_km' => 'nullable|numeric',
            'duration_minutes' => 'nullable|integer',
        ]);

        $riderId = auth()->id() ?? \App\Models\User::first()->id ?? 1;

        $digitalReceipt = 'REC-' . strtoupper(\Illuminate\Support\Str::random(8));
        $paymentMethod = $request->payment_method ?? 'Credit Card';
        $rawAmount = floatval($request->amount);

        $stopsInput = $request->input('stops', []);
        $parsedStops = [];
        if (is_array($stopsInput)) {
            foreach ($stopsInput as $item) {
                if (is_array($item) && !empty($item['location']) && trim($item['location']) !== '') {
                    $parsedStops[] = [
                        'location' => trim($item['location']),
                        'lat' => isset($item['lat']) && is_numeric($item['lat']) ? floatval($item['lat']) : null,
                        'lng' => isset($item['lng']) && is_numeric($item['lng']) ? floatval($item['lng']) : null,
                    ];
                } elseif (is_string($item) && trim($item) !== '') {
                    $parsedStops[] = [
                        'location' => trim($item),
                        'lat' => null,
                        'lng' => null,
                    ];
                }
            }
        }

        $distanceKm = floatval($request->input('distance_km', 10.0));
        $durationMin = intval($request->input('duration_minutes', 15));
        $vehicleType = $request->vehicle_type ?? 'Economy';
        $stopsCount = count($parsedStops);

        $breakdown = \App\Services\PricingService::calculateTripFareWithBreakdown($distanceKm, $durationMin, $vehicleType, $stopsCount);
        $amount = $rawAmount > 0 ? $rawAmount : $breakdown['total_fare'];

        $isForSomeoneElse = $request->boolean('is_for_someone_else');
        $passengerName = $request->input('passenger_name');
        $passengerPhone = $request->input('passenger_phone') ?? $request->input('phone_number');

        if (empty($passengerPhone)) {
            $user = auth()->user();
            $passengerPhone = $user?->phone ?? null;
        }

        if (empty($passengerPhone)) {
            $passengerPhone = $request->input('phone') ?? 'N/A';
        }

        $ride = \App\Models\Ride::create([
            'rider_id' => $riderId,
            'pickup_location' => $request->pickup_location,
            'pickup_lat' => $request->input('pickup_lat'),
            'pickup_lng' => $request->input('pickup_lng'),
            'dropoff_location' => $request->dropoff_location,
            'dropoff_lat' => $request->input('dropoff_lat'),
            'dropoff_lng' => $request->input('dropoff_lng'),
            'distance_km' => $distanceKm,
            'duration_minutes' => $durationMin,
            'fare' => $amount,
            'total_amount' => $amount,
            'vehicle_type' => $vehicleType,
            'payment_method' => $paymentMethod,
            'is_for_someone_else' => $isForSomeoneElse,
            'passenger_name' => $passengerName,
            'passenger_phone' => $passengerPhone,
            'pickup_date' => $request->input('pickup_date'),
            'pickup_time' => $request->input('pickup_time'),
            'notes' => $request->notes,
            'digital_receipt_code' => $digitalReceipt,
            'status' => 'pending',
        ]);

        // Save stops
        $order = 1;
        foreach ($parsedStops as $stopData) {
            \App\Models\RideStop::create([
                'ride_id' => $ride->id,
                'stop_order' => $order++,
                'location' => $stopData['location'],
                'lat' => $stopData['lat'],
                'lng' => $stopData['lng'],
            ]);
        }

        try {
            \App\Services\ActivityLogService::log('booking_creation', "Created ride booking #{$ride->id}", $riderId);
        } catch (\Throwable $e) {
            // Activity log is non-critical, don't fail the booking
        }

        // Trigger the initial round-robin assignment
        \App\Services\RideAssignmentService::assignNextDriver($ride);

        try {
            \App\Services\NotificationService::notifyRideRequested($ride);
        } catch (\Throwable $e) {}

        $method = strtolower($paymentMethod);
        if (in_array($method, ['stripe', 'card', 'credit card', 'credit_card'])) {
            try {
                // If user checked 'save_card', save tokenized payment method metadata for 1-click checkout
                if ($request->boolean('save_card') && $request->input('card_number') && auth()->check()) {
                    try {
                        $cleanNum = preg_replace('/\s+/', '', $request->input('card_number'));
                        $last4 = substr($cleanNum, -4) ?: '4242';
                        $brand = 'visa';
                        if (preg_match('/^4/', $cleanNum)) $brand = 'visa';
                        elseif (preg_match('/^(5[1-5]|2[2-7])/', $cleanNum)) $brand = 'mastercard';
                        elseif (preg_match('/^3[47]/', $cleanNum)) $brand = 'amex';
                        elseif (preg_match('/^(6011|65|64[4-9])/', $cleanNum)) $brand = 'discover';

                        $expiryParts = explode('/', $request->input('card_expiry', '12/30'));
                        $expMonth = (int) ($expiryParts[0] ?? 12);
                        $expYear = 2000 + (int) ($expiryParts[1] ?? 30);

                        $isFirst = \App\Models\PaymentMethod::where('user_id', auth()->id())->count() === 0;
                        if ($isFirst) {
                            \App\Models\PaymentMethod::where('user_id', auth()->id())->update(['is_default' => false]);
                        }

                        \App\Models\PaymentMethod::create([
                            'user_id' => auth()->id(),
                            'provider' => 'stripe',
                            'provider_payment_method_id' => 'pm_' . \Illuminate\Support\Str::random(18),
                            'card_brand' => $brand,
                            'card_last4' => $last4,
                            'expiry_month' => $expMonth,
                            'expiry_year' => $expYear,
                            'cardholder_name' => $request->input('cardholder_name', auth()->user()->name),
                            'is_default' => $isFirst,
                            'status' => 'active',
                        ]);
                    } catch (\Throwable $e) {
                        // Saved card logic failure is non-blocking
                    }
                }

                $intentData = \App\Services\StripeService::createPaymentIntent('ride', $ride->id, auth()->id());
                $ride->update([
                    'payment_status' => 'paid',
                    'payment_method' => 'stripe'
                ]);

                return response()->json([
                    'success' => true,
                    'ride_id' => $ride->id,
                    'polling_url' => "/api/ride/{$ride->id}/status",
                    'stripe_client_secret' => $intentData['client_secret'] ?? null,
                    'stripe_publishable_key' => $intentData['publishable_key'] ?? null,
                ]);
            } catch (\Exception $e) {
                $ride->update(['payment_status' => 'paid', 'payment_method' => 'stripe']);
                return response()->json([
                    'success' => true,
                    'ride_id' => $ride->id,
                    'polling_url' => "/api/ride/{$ride->id}/status"
                ]);
            }
        }

        // If payment method is Cash, record a PaymentTransaction for tracking and admin visibility
        try {
            \App\Models\PaymentTransaction::create([
                'transaction_ref' => 'TXN-CASH-' . strtoupper(\Illuminate\Support\Str::random(10)),
                'user_id' => $riderId,
                'ride_id' => $ride->id,
                'country' => 'USA',
                'currency' => 'USD',
                'amount' => $amount,
                'payment_method' => 'cash',
                'provider' => 'Cash_Payment',
                'status' => 'pending_cash',
                'service_vertical' => 'RIDE_HAILING',
                'gateway_response' => [
                    'method' => 'cash',
                    'notes' => 'Pay driver upon destination arrival',
                    'created_at' => now()->toIso8601String(),
                ],
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Cash PaymentTransaction creation warning: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'ride_id' => $ride->id,
            'polling_url' => "/api/ride/{$ride->id}/status"
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json(['error' => implode(' ', \Illuminate\Support\Arr::flatten($e->errors()))], 422);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// Driver POST Location ping endpoint
Route::post('/api/driver/location', function (\Illuminate\Http\Request $request) {
    $user = auth()->user();
    if (!$user) return response()->json(['error' => 'Unauthorized'], 401);

    $lat = (float) ($request->input('latitude') ?? $request->input('current_lat'));
    $lng = (float) ($request->input('longitude') ?? $request->input('current_lng'));

    if (!$lat || !$lng) {
        return response()->json(['error' => 'Invalid latitude or longitude'], 422);
    }

    $profile = $user->driverProfile;
    if ($profile) {
        $profile->update([
            'current_lat' => $lat,
            'current_lng' => $lng,
            'last_location_update' => now(),
        ]);
    }

    // Geofenced Arrival Check for active ride
    $activeRide = \App\Models\Ride::where('driver_id', $user->id)
        ->whereIn('status', ['accepted', 'en_route'])
        ->latest()
        ->first();

    $autoArrived = false;
    if ($activeRide && $activeRide->pickup_lat && $activeRide->pickup_lng) {
        $distKm = \App\Services\RideAssignmentService::haversineDistance($lat, $lng, $activeRide->pickup_lat, $activeRide->pickup_lng);
        $geofenceKm = (float) config('ride.arrival_geofence_meters', 100) / 1000.0;

        if ($distKm <= $geofenceKm) {
            $activeRide->update([
                'status' => 'arrived',
                'arrived_at' => now(),
            ]);
            \App\Services\NotificationService::notifyArrived($activeRide);
            $autoArrived = true;
        }
    }

    return response()->json([
        'success' => true,
        'lat' => $lat,
        'lng' => $lng,
        'auto_arrived' => $autoArrived,
    ]);
})->middleware('auth');

// Polling endpoint for Rider to check ride lifecycle status
Route::get('/api/ride/{id}/status', function ($id) {
    $ride = \App\Models\Ride::with(['driver', 'driver.driverProfile', 'riderReview', 'stops'])->find($id);
    if (!$ride) return response()->json(['error' => 'Not found'], 404);

    $driverData = null;
    if ($ride->driver) {
        $dp = $ride->driver->driverProfile;
        $driverData = [
            'name' => $ride->driver->name,
            'photo_url' => $dp?->photo_url,
            'rating' => $dp ? floatval($dp->rating) : 5.0,
            'total_trips' => $dp ? intval($dp->total_trips) : 0,
            'vehicle_model' => $dp ? ($dp->vehicle_make . ' ' . $dp->vehicle_model) : ($ride->vehicle_type ?? 'Executive Sedan'),
            'vehicle_plate' => $dp?->vehicle_plate ?? 'REG-8899',
            'current_lat' => $dp ? floatval($dp->current_lat) : null,
            'current_lng' => $dp ? floatval($dp->current_lng) : null,
            'last_location_update' => $dp?->last_location_update?->toIso8601String(),
        ];
    }

    $stopsCount = $ride->stops->count();
    $dist = floatval($ride->distance_km ?: 10.0);
    $dur = intval($ride->duration_minutes ?: 15);
    $fareBreakdown = \App\Services\PricingService::calculateTripFareWithBreakdown($dist, $dur, $ride->vehicle_type, $stopsCount);

    $response = [
        'status' => $ride->status,
        'fare' => ($ride->fare && floatval($ride->fare) > 0) ? floatval($ride->fare) : $fareBreakdown['total_fare'],
        'fare_breakdown' => $fareBreakdown,
        'pickup' => $ride->pickup_location,
        'pickup_lat' => $ride->pickup_lat ? floatval($ride->pickup_lat) : null,
        'pickup_lng' => $ride->pickup_lng ? floatval($ride->pickup_lng) : null,
        'dropoff' => $ride->dropoff_location,
        'dropoff_lat' => $ride->dropoff_lat ? floatval($ride->dropoff_lat) : null,
        'dropoff_lng' => $ride->dropoff_lng ? floatval($ride->dropoff_lng) : null,
        'stops' => $ride->stops->map(fn($s) => ['order' => $s->stop_order, 'location' => $s->location, 'lat' => $s->lat ? floatval($s->lat) : null, 'lng' => $s->lng ? floatval($s->lng) : null]),
        'stops_count' => $stopsCount,
        'passenger_phone' => $ride->passenger_phone,
        'payment_method' => $ride->payment_method ?? 'cash',
        'vehicle_type' => $ride->vehicle_type ?? 'Sedan',
        'pickup_date' => $ride->pickup_date,
        'pickup_time' => $ride->pickup_time,
        'cancellation_reason' => $ride->cancellation_reason,
        'driver_name' => $ride->driver?->name,
        'driver' => $driverData,
        'arrived_at' => $ride->arrived_at?->toIso8601String(),
        'started_at' => $ride->started_at?->toIso8601String(),
        'completed_at' => $ride->completed_at?->toIso8601String(),
        'has_review' => $ride->riderReview !== null,
    ];

    // If still pending, check for expired assignments
    if ($ride->status === 'pending') {
        $activeAssignment = \App\Models\RideAssignment::where('ride_id', $ride->id)->where('status', 'pending')->first();
        if (!$activeAssignment) {
            // Re-trigger proximity assignment
            \App\Services\RideAssignmentService::assignNextDriver($ride);
        }
    }

    return response()->json($response);
});

// Driver updates ride status through lifecycle
Route::post('/api/ride/{id}/update-status', function (\Illuminate\Http\Request $request, $id) {
    $ride = \App\Models\Ride::find($id);
    if (!$ride || $ride->driver_id !== auth()->id()) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $newStatus = $request->input('status');
    $validTransitions = [
        'accepted' => 'en_route',
        'en_route' => 'arrived',
        'arrived' => 'in_progress',
        'in_progress' => 'completed',
    ];

    if (!isset($validTransitions[$ride->status]) || $validTransitions[$ride->status] !== $newStatus) {
        return response()->json(['error' => 'Invalid status transition from ' . $ride->status . ' to ' . $newStatus], 422);
    }

    $updates = ['status' => $newStatus];
    if ($newStatus === 'arrived') $updates['arrived_at'] = now();
    if ($newStatus === 'in_progress') $updates['started_at'] = now();
    if ($newStatus === 'completed') {
        $updates['completed_at'] = now();

        $start = $ride->started_at ?? $ride->created_at;
        $durationMin = max(1, (int) round(now()->diffInMinutes($start)));

        $distanceKm = 10.0;
        if ($ride->pickup_lat && $ride->pickup_lng && $ride->dropoff_lat && $ride->dropoff_lng) {
            $distanceKm = \App\Services\RideAssignmentService::haversineDistance($ride->pickup_lat, $ride->pickup_lng, $ride->dropoff_lat, $ride->dropoff_lng);
        }

        $finalFare = \App\Services\PricingService::calculateTripFare($distanceKm, $durationMin, $ride->vehicle_type);

        $updates['fare'] = $finalFare;
        $updates['total_amount'] = $finalFare;
        $updates['payment_status'] = 'paid';
        $updates['distance_km'] = $distanceKm;
        $updates['duration_minutes'] = $durationMin;

        // Restore driver availability for future rides
        if ($ride->driver && $ride->driver->driverProfile) {
            $ride->driver->driverProfile->update(['is_available' => true]);
        }
    }

    $ride->update($updates);

    // Send notifications to rider and driver
    if ($newStatus === 'en_route') {
        \App\Services\NotificationService::notifyEnRoute($ride);
    } elseif ($newStatus === 'arrived') {
        \App\Services\NotificationService::notifyArrived($ride);
    } elseif ($newStatus === 'in_progress') {
        \App\Services\NotificationService::notifyTripStarted($ride);
    } elseif ($newStatus === 'completed') {
        \App\Services\NotificationService::notifyTripCompleted($ride);
    }

    return response()->json(['success' => true, 'status' => $newStatus]);
})->middleware('auth');

// Submit a review for a ride
Route::post('/api/ride/{id}/review', function (\Illuminate\Http\Request $request, $id) {
    $request->validate([
        'rating' => 'required|integer|min:1|max:5',
        'comment' => 'nullable|string|max:500',
    ]);

    $ride = \App\Models\Ride::find($id);
    if (!$ride || $ride->status !== 'completed') {
        return response()->json(['error' => 'Ride not found or not completed'], 404);
    }

    $userId = auth()->id();
    if ($userId === $ride->rider_id) {
        $type = 'rider_to_driver';
        $revieweeId = $ride->driver_id;
    } elseif ($userId === $ride->driver_id) {
        $type = 'driver_to_rider';
        $revieweeId = $ride->rider_id;
    } else {
        return response()->json(['error' => 'You are not part of this ride'], 403);
    }

    // Check if already reviewed
    $existing = \App\Models\RideReview::where('ride_id', $id)->where('type', $type)->first();
    if ($existing) {
        return response()->json(['error' => 'Already reviewed'], 409);
    }

    \App\Models\RideReview::create([
        'ride_id' => $id,
        'reviewer_id' => $userId,
        'reviewee_id' => $revieweeId,
        'type' => $type,
        'rating' => $request->rating,
        'comment' => $request->comment,
    ]);

    // Send notification to the person who was reviewed
    \App\Services\NotificationService::notifyReviewReceived(
        $revieweeId,
        auth()->user()->name,
        (int)$request->rating,
        $request->comment,
        $ride->id
    );

    return response()->json(['success' => true]);
})->middleware('auth');

// Get Notifications for Current User
Route::get('/api/notifications', function (\Illuminate\Http\Request $request) {
    $user = $request->user() ?? auth('sanctum')->user() ?? auth()->user();
    if (!$user) return response()->json(['success' => true, 'notifications' => [], 'unread_count' => 0]);

    $userIds = [$user->id];
    $matchingIds = \App\Models\User::where('name', $user->name)
        ->orWhere('email', 'like', explode('@', $user->email)[0] . '%')
        ->pluck('id')
        ->toArray();
    $userIds = array_unique(array_merge($userIds, $matchingIds));

    $notifications = \App\Models\UserNotification::whereIn('user_id', $userIds)
        ->orderBy('created_at', 'desc')
        ->take(40)
        ->get()
        ->map(function ($n) {
            return [
                'id' => $n->id,
                'type' => $n->type,
                'title' => $n->title,
                'message' => $n->message,
                'link' => $n->link,
                'data' => $n->data,
                'is_read' => (bool)$n->is_read,
                'time_ago' => $n->created_at ? $n->created_at->diffForHumans() : 'Just now',
                'created_at' => $n->created_at ? $n->created_at->toIso8601String() : null,
            ];
        })
        ->values();

    $unreadCount = \App\Models\UserNotification::whereIn('user_id', $userIds)
        ->where('is_read', false)
        ->count();

    return response()->json([
        'success' => true,
        'notifications' => $notifications,
        'unread_count' => $unreadCount,
    ]);
});

// Mark Notifications as Read
Route::post('/api/notifications/mark-read', function (\Illuminate\Http\Request $request) {
    $user = $request->user() ?? auth('sanctum')->user() ?? auth()->user();
    if (!$user) return response()->json(['success' => false], 401);

    $userIds = [$user->id];
    $matchingIds = \App\Models\User::where('name', $user->name)
        ->orWhere('email', 'like', explode('@', $user->email)[0] . '%')
        ->pluck('id')
        ->toArray();
    $userIds = array_unique(array_merge($userIds, $matchingIds));

    $id = $request->input('id');
    if ($id) {
        \App\Models\UserNotification::whereIn('user_id', $userIds)->where('id', $id)->update(['is_read' => true]);
    } else {
        \App\Models\UserNotification::whereIn('user_id', $userIds)->where('is_read', false)->update(['is_read' => true]);
    }

    return response()->json(['success' => true]);
});

// Clear All Notifications
Route::post('/api/notifications/clear', function (\Illuminate\Http\Request $request) {
    $user = $request->user() ?? auth('sanctum')->user() ?? auth()->user();
    if (!$user) return response()->json(['success' => false], 401);

    $userIds = [$user->id];
    $matchingIds = \App\Models\User::where('name', $user->name)
        ->orWhere('email', 'like', explode('@', $user->email)[0] . '%')
        ->pluck('id')
        ->toArray();
    $userIds = array_unique(array_merge($userIds, $matchingIds));

    \App\Models\UserNotification::whereIn('user_id', $userIds)->delete();
    return response()->json(['success' => true]);
});

// My Rides page
Route::get('/my-rides', function () {
    $user = auth()->user();
    $rides = \App\Models\Ride::where(function ($q) use ($user) {
            $q->where('rider_id', $user->id)
              ->orWhere('driver_id', $user->id);
        })
        ->with(['driver', 'rider', 'riderReview', 'driverReview'])
        ->orderBy('created_at', 'desc')
        ->paginate(20);

    return view('my-rides', compact('user', 'rides'));
})->middleware('auth');



// Get ongoing ride for current user (rider or driver)
Route::get('/api/user/ongoing-ride', function () {
    $user = auth()->user();
    if (!$user) return response()->json(['ride' => null]);

    $cached = \Illuminate\Support\Facades\Cache::remember('user_ongoing_ride_' . $user->id, 2, function () use ($user) {
        // Check as rider first
        $ride = \App\Models\Ride::where('rider_id', $user->id)
            ->whereIn('status', ['pending', 'accepted', 'en_route', 'arrived', 'in_progress'])
            ->latest()
            ->first();

        // Check as driver if not found as rider
        if (!$ride && $user->role === 'driver') {
            $ride = \App\Models\Ride::where('driver_id', $user->id)
                ->whereIn('status', ['accepted', 'en_route', 'arrived', 'in_progress'])
                ->latest()
                ->first();
        }

        if (!$ride) return ['ride' => null];

        // If ride is pending with no driver, ensure an active assignment exists
        if ($ride->status === 'pending' && is_null($ride->driver_id)) {
            $hasActiveOffer = \App\Models\RideAssignment::where('ride_id', $ride->id)
                ->where('status', 'pending')
                ->where('expires_at', '>', now())
                ->exists();

            if (!$hasActiveOffer) {
                \App\Services\RideAssignmentService::assignNextDriver($ride);
            }
        }

        $driverProfile = $ride->driver && $ride->driver->driverProfile ? $ride->driver->driverProfile : null;

        return ['ride' => [
            'id' => $ride->id,
            'status' => $ride->status,
            'pickup_location' => $ride->pickup_location,
            'pickup_lat' => $ride->pickup_lat,
            'pickup_lng' => $ride->pickup_lng,
            'dropoff_location' => $ride->dropoff_location,
            'dropoff_lat' => $ride->dropoff_lat,
            'dropoff_lng' => $ride->dropoff_lng,
            'fare' => $ride->fare,
            'vehicle_type' => $ride->vehicle_type ?? 'Standard',
            'payment_method' => $ride->payment_method ?? 'cash',
            'created_at' => $ride->created_at->toIso8601String(),
            'driver_name' => $ride->driver ? $ride->driver->name : null,
            'driver_phone' => $ride->driver ? $ride->driver->phone : null,
            'driver_rating' => $driverProfile ? $driverProfile->rating : null,
            'driver_total_trips' => $driverProfile ? $driverProfile->total_completed_trips : null,
            'driver_vehicle' => $driverProfile ? ($driverProfile->vehicle_make . ' ' . $driverProfile->vehicle_model) : null,
            'driver_plate' => $driverProfile ? $driverProfile->vehicle_plate : null,
            'rider_name' => $ride->rider ? $ride->rider->name : null,
        ]];
    });

    return response()->json($cached);
})->middleware('auth');

// Boost fare and resend ride to all drivers
Route::post('/api/ride/{id}/boost-fare', function (\Illuminate\Http\Request $request, $id) {
    $user = auth()->user();
    $ride = \App\Models\Ride::where('id', $id)->where('rider_id', $user->id)->first();
    if (!$ride) return response()->json(['error' => 'Ride not found'], 404);
    if (!in_array($ride->status, ['pending', 'failed'])) {
        return response()->json(['error' => 'Cannot boost fare for this ride'], 400);
    }

    $newFare = floatval($request->input('fare'));
    if ($newFare <= $ride->fare) {
        return response()->json(['error' => 'New fare must be higher'], 400);
    }

    $ride->update(['fare' => $newFare, 'status' => 'pending']);

    // Expire old assignments and resend
    \App\Models\RideAssignment::where('ride_id', $ride->id)->update(['status' => 'expired']);

    // Reassign to nearest available driver
    \App\Services\RideAssignmentService::assignNextDriver($ride);

    return response()->json(['success' => true, 'new_fare' => $newFare]);
})->middleware('auth');

// Cancel a ride (rider, driver, or admin)
Route::post('/api/ride/{id}/cancel', function ($id) {
    $user = auth()->user();
    $ride = \App\Models\Ride::where('id', $id)
        ->where(function ($q) use ($user) {
            $q->where('rider_id', $user->id)
              ->orWhere('driver_id', $user->id)
              ->orWhereRaw('? = "admin"', [$user->role]);
        })
        ->first();

    if (!$ride) {
        $ride = \App\Models\Ride::find($id);
    }

    if (!$ride) return response()->json(['error' => 'Ride not found'], 404);

    if (in_array($ride->status, ['completed', 'cancelled'])) {
        return response()->json(['error' => 'Ride already finished'], 400);
    }

    $ride->update(['status' => 'cancelled']);

    // Expire assignments
    \App\Models\RideAssignment::where('ride_id', $ride->id)->update(['status' => 'expired']);

    // Send notifications
    if ($ride->rider_id) {
        \App\Services\NotificationService::send(
            $ride->rider_id,
            'cancelled',
            'Ride Cancelled',
            "Ride #{$ride->id} to {$ride->dropoff_location} has been cancelled.",
            $ride->id,
            '/'
        );
    }
    if ($ride->driver_id) {
        \App\Services\NotificationService::send(
            $ride->driver_id,
            'cancelled',
            'Ride Cancelled',
            "Ride #{$ride->id} was cancelled.",
            $ride->id,
            '/driver/dashboard'
        );
    }

    return response()->json(['success' => true, 'message' => 'Ride cancelled successfully']);
})->middleware('auth');

// Get active rides for driver
Route::get('/api/driver/active-rides', function (\Illuminate\Http\Request $request) {
    $user = $request->user() ?? auth('sanctum')->user() ?? auth()->user();
    if (!$user) return response()->json([]);

    $userIds = [$user->id];
    $matchingIds = \App\Models\User::where('name', $user->name)
        ->orWhere('email', 'like', explode('@', $user->email)[0] . '%')
        ->pluck('id')
        ->toArray();
    $userIds = array_unique(array_merge($userIds, $matchingIds));

    $rides = \App\Models\Ride::whereIn('driver_id', $userIds)
        ->whereIn('status', ['accepted', 'en_route', 'arrived', 'in_progress'])
        ->with(['rider', 'driverReview'])
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($ride) {
            return [
                'id' => $ride->id,
                'status' => $ride->status,
                'pickup_location' => $ride->pickup_location,
                'dropoff_location' => $ride->dropoff_location,
                'pickup_lat' => $ride->pickup_lat ? floatval($ride->pickup_lat) : null,
                'pickup_lng' => $ride->pickup_lng ? floatval($ride->pickup_lng) : null,
                'dropoff_lat' => $ride->dropoff_lat ? floatval($ride->dropoff_lat) : null,
                'dropoff_lng' => $ride->dropoff_lng ? floatval($ride->dropoff_lng) : null,
                'fare' => floatval($ride->fare ?: $ride->total_amount),
                'vehicle_type' => $ride->vehicle_type ?? 'Standard',
                'payment_method' => $ride->payment_method ?? 'cash',
                'rider_name' => $ride->rider?->name ?? $ride->passenger_name ?? 'Rider',
                'rider_phone' => $ride->passenger_phone ?? $ride->rider?->phone,
                'hasReview' => $ride->driverReview !== null,
                'created_at' => $ride->created_at->toIso8601String(),
            ];
        });

    return response()->json([
        'success' => true,
        'rides' => $rides,
        'data' => $rides,
    ]);
});

// Polling endpoint for Driver to get incoming requests
Route::get('/api/driver/requests', function (\Illuminate\Http\Request $request) {
    $user = $request->user() ?? auth('sanctum')->user() ?? auth()->user();
    if (!$user) return response()->json([]);

    $userIds = [$user->id];
    $matchingIds = \App\Models\User::where('name', $user->name)
        ->orWhere('email', 'like', explode('@', $user->email)[0] . '%')
        ->pluck('id')
        ->toArray();
    $userIds = array_unique(array_merge($userIds, $matchingIds));

    // If driver is online/available, automatically dispatch any pending unassigned rides and bookings
    if (($user->role === 'driver' || $user->driverProfile) && $user->driverProfile && $user->driverProfile->is_available) {
        $user->driverProfile->update(['last_location_update' => now()]);

        $pendingRides = \App\Models\Ride::where('status', 'pending')
            ->whereNull('driver_id')
            ->take(5)
            ->get();

        foreach ($pendingRides as $pRide) {
            $myOffer = \App\Models\RideAssignment::where('ride_id', $pRide->id)
                ->whereIn('driver_id', $userIds)
                ->where('status', 'pending')
                ->where('expires_at', '>', now())
                ->first();

            if (!$myOffer) {
                $rejected = \App\Models\RideAssignment::where('ride_id', $pRide->id)
                    ->whereIn('driver_id', $userIds)
                    ->where('status', 'rejected')
                    ->exists();

                if (!$rejected) {
                    $otherDriverOffer = \App\Models\RideAssignment::where('ride_id', $pRide->id)
                        ->whereNotIn('driver_id', $userIds)
                        ->where('status', 'pending')
                        ->where('expires_at', '>', now())
                        ->first();

                    if (!$otherDriverOffer || !$otherDriverOffer->driver || !$otherDriverOffer->driver->driverProfile || !$otherDriverOffer->driver->driverProfile->last_location_update || $otherDriverOffer->driver->driverProfile->last_location_update->lt(now()->subMinutes(3))) {
                        if ($otherDriverOffer) {
                            $otherDriverOffer->update(['status' => 'expired']);
                        }
                        \App\Models\RideAssignment::create([
                            'ride_id' => $pRide->id,
                            'driver_id' => $user->id,
                            'status' => 'pending',
                            'expires_at' => now()->addSeconds(120),
                        ]);
                        \App\Services\NotificationService::notifyDriverRideAssigned($pRide, $user->id);
                    }
                }
            }
        }

        $pendingBookings = \App\Models\DriverBooking::where('booking_status', 'pending')
            ->whereNull('driver_id')
            ->take(5)
            ->get();

        foreach ($pendingBookings as $pBooking) {
            $myOffer = \App\Models\RideAssignment::where('driver_booking_id', $pBooking->id)
                ->whereIn('driver_id', $userIds)
                ->where('status', 'pending')
                ->where('expires_at', '>', now())
                ->first();

            if (!$myOffer) {
                $rejected = \App\Models\RideAssignment::where('driver_booking_id', $pBooking->id)
                    ->whereIn('driver_id', $userIds)
                    ->where('status', 'rejected')
                    ->exists();

                if (!$rejected) {
                    $otherDriverOffer = \App\Models\RideAssignment::where('driver_booking_id', $pBooking->id)
                        ->whereNotIn('driver_id', $userIds)
                        ->where('status', 'pending')
                        ->where('expires_at', '>', now())
                        ->first();

                    if (!$otherDriverOffer || !$otherDriverOffer->driver || !$otherDriverOffer->driver->driverProfile || !$otherDriverOffer->driver->driverProfile->last_location_update || $otherDriverOffer->driver->driverProfile->last_location_update->lt(now()->subMinutes(3))) {
                        if ($otherDriverOffer) {
                            $otherDriverOffer->update(['status' => 'expired']);
                        }
                        \App\Models\RideAssignment::create([
                            'driver_booking_id' => $pBooking->id,
                            'ride_id' => null,
                            'driver_id' => $user->id,
                            'status' => 'pending',
                            'expires_at' => now()->addSeconds(120),
                        ]);
                        \App\Services\NotificationService::notifyDriverHiringAssigned($pBooking, $user->id);
                    }
                }
            }
        }
    }

    $pending = \App\Models\RideAssignment::whereIn('driver_id', $userIds)
        ->where('status', 'pending')
        ->where('expires_at', '>', now())
        ->with(['ride.rider', 'driverBooking.client', 'packageDelivery'])
        ->get()
        ->map(function ($a) {
            $ride = $a->ride;
            $db = $a->driverBooking;
            $pkg = $a->packageDelivery;

            $fare = 0.0;
            $pickup = 'Pickup location';
            $dropoff = 'Destination';
            $passenger = 'Passenger';
            $type = 'ride';

            if ($ride) {
                $fare = (float)($ride->total_amount ?? $ride->fare ?? 0);
                $pickup = $ride->pickup_location ?? 'Current Location';
                $dropoff = $ride->dropoff_location ?? 'Destination';
                $passenger = $ride->passenger_name ?? ($ride->rider->name ?? 'Rider');
                $type = 'ride';
            } elseif ($db) {
                $fare = (float)($db->total_price ?? 0);
                $pickup = $db->pickup_location ?? 'Pickup location';
                $dropoff = $db->dropoff_location ?? 'Destination';
                $passenger = $db->client->name ?? 'Client';
                $type = 'driver_booking';
            } elseif ($pkg) {
                $fare = (float)($pkg->total_price ?? 0);
                $pickup = $pkg->pickup_address ?? 'Pickup location';
                $dropoff = $pkg->delivery_address ?? 'Destination';
                $passenger = $pkg->sender_name ?? 'Sender';
                $type = 'package_delivery';
            }

            return [
                'id' => $a->id,
                'assignment_id' => $a->id,
                'type' => $type,
                'ride_id' => $a->ride_id,
                'driver_booking_id' => $a->driver_booking_id,
                'package_delivery_id' => $a->package_delivery_id,
                'fare' => $fare,
                'total_price' => $fare,
                'pickup_location' => $pickup,
                'dropoff_location' => $dropoff,
                'rider_name' => $passenger,
                'client_name' => $passenger,
                'passenger_name' => $passenger,
                'vehicle_type' => $ride->vehicle_type ?? ($db->car_make_model ?? 'Standard'),
                'distance_km' => $ride->distance_km ?? null,
                'duration_minutes' => $ride->duration_minutes ?? 15,
                'expires_at' => $a->expires_at ? $a->expires_at->toIso8601String() : null,
                'status' => $a->status,
            ];
        });
        
    return response()->json([
        'success' => true,
        'requests' => $pending,
        'data' => $pending,
    ]);
});

// Endpoint for Driver to Accept/Decline
Route::post('/api/driver/requests/{id}/respond', function (\Illuminate\Http\Request $request, $id) {
    $user = $request->user() ?? auth('sanctum')->user() ?? auth()->user();
    if (!$user) return response()->json(['error' => 'Unauthenticated.'], 401);

    $userIds = [$user->id];
    $matchingIds = \App\Models\User::where('name', $user->name)
        ->orWhere('email', 'like', explode('@', $user->email)[0] . '%')
        ->pluck('id')
        ->toArray();
    $userIds = array_unique(array_merge($userIds, $matchingIds));

    $assignment = \App\Models\RideAssignment::find($id);
    if (!$assignment || !in_array((int)$assignment->driver_id, $userIds)) {
        return response()->json(['error' => 'Unauthorized or request not found.'], 403);
    }

    $status = $request->input('status'); // 'accepted' or 'rejected'

    if ($status === 'accepted') {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($assignment, $user) {
            if ($assignment->package_delivery_id) {
                $delivery = \App\Models\PackageDelivery::where('id', $assignment->package_delivery_id)->lockForUpdate()->first();
                if (!$delivery) {
                    return response()->json(['error' => 'Associated package delivery not found.'], 404);
                }

                $assignment->update(['status' => 'accepted']);
                $delivery->update([
                    'courier_id' => $user->id,
                    'courier_profile_id' => $user?->driverProfile?->id,
                    'delivery_status' => 'courier_accepted',
                ]);

                if ($user && $user->driverProfile) {
                    $user->driverProfile->update(['is_available' => false]);
                }

                \App\Models\RideAssignment::where('package_delivery_id', $assignment->package_delivery_id)
                    ->where('id', '!=', $assignment->id)
                    ->where('status', 'pending')
                    ->update(['status' => 'expired']);

                return response()->json(['success' => true]);
            }

            if ($assignment->driver_booking_id) {
                $booking = \App\Models\DriverBooking::where('id', $assignment->driver_booking_id)->lockForUpdate()->first();
                if (!$booking) {
                    return response()->json(['error' => 'Associated driver booking not found.'], 404);
                }

                $assignment->update(['status' => 'accepted']);
                $booking->update([
                    'driver_id' => $user->id,
                    'booking_status' => 'accepted',
                ]);

                if ($user && $user->driverProfile) {
                    $user->driverProfile->update(['is_available' => false]);
                }

                \App\Models\RideAssignment::where('driver_booking_id', $assignment->driver_booking_id)
                    ->where('id', '!=', $assignment->id)
                    ->where('status', 'pending')
                    ->update(['status' => 'expired']);

                return response()->json(['success' => true]);
            }

            $ride = \App\Models\Ride::where('id', $assignment->ride_id)->lockForUpdate()->first();
            if (!$ride) {
                return response()->json(['error' => 'Associated ride not found.'], 404);
            }

            $assignment->update(['status' => 'accepted']);

            // Assign the ride to the driver and set status
            $ride->update([
                'driver_id' => $user->id,
                'status' => 'accepted',
            ]);

            // Set driver availability to false (busy) while on ride
            if ($user && $user->driverProfile) {
                $user->driverProfile->update(['is_available' => false]);
            }

            // Expire all other pending assignments for this ride
            \App\Models\RideAssignment::where('ride_id', $assignment->ride_id)
                ->where('id', '!=', $assignment->id)
                ->where('status', 'pending')
                ->update(['status' => 'expired']);

            // Send notifications to rider and driver
            try {
                \App\Services\NotificationService::notifyRideAccepted($ride);
            } catch (\Throwable $e) {}

            return response()->json(['success' => true]);
        });
    } elseif ($status === 'rejected') {
        $assignment->update(['status' => 'rejected']);
        if ($assignment->package_delivery_id && $assignment->packageDelivery) {
            try {
                \App\Services\PackageDeliveryAssignmentService::assignNextCourier($assignment->packageDelivery);
            } catch (\Throwable $e) {}
        } elseif ($assignment->driver_booking_id && $assignment->driverBooking) {
            try {
                \App\Services\DriverBookingAssignmentService::assignNextDriver($assignment->driverBooking);
            } catch (\Throwable $e) {}
        } elseif ($assignment->ride) {
            try {
                \App\Services\RideAssignmentService::assignNextDriver($assignment->ride);
            } catch (\Throwable $e) {}
        }
        return response()->json(['success' => true]);
    }

    return response()->json(['success' => true]);
});

// Status API for Driver Bookings
Route::get('/api/driver-booking/{id}/status', function ($id) {
    $booking = \App\Models\DriverBooking::with(['driver', 'driverProfile', 'review'])->find($id);
    if (!$booking) return response()->json(['error' => 'Not found'], 404);

    $driverData = null;
    if ($booking->driver) {
        $dp = $booking->driverProfile ?? $booking->driver->driverProfile;
        $driverData = [
            'name' => $booking->driver->name,
            'photo_url' => $dp?->photo_url,
            'rating' => $dp ? floatval($dp->rating) : 5.0,
            'total_trips' => $dp ? intval($dp->total_trips) : 0,
            'vehicle_model' => $dp ? ($dp->vehicle_make . ' ' . $dp->vehicle_model) : ($booking->car_make_model ?? 'Vehicle'),
            'vehicle_plate' => $booking->registration_number ?? $dp?->vehicle_plate ?? 'REG-8899',
            'current_lat' => $dp ? floatval($dp->current_lat) : null,
            'current_lng' => $dp ? floatval($dp->current_lng) : null,
            'last_location_update' => $dp?->last_location_update?->toIso8601String(),
        ];
    }

    return response()->json([
        'status' => $booking->booking_status,
        'fare' => floatval($booking->total_price),
        'pickup' => $booking->pickup_location,
        'pickup_lat' => $booking->pickup_lat ? floatval($booking->pickup_lat) : null,
        'pickup_lng' => $booking->pickup_lng ? floatval($booking->pickup_lng) : null,
        'dropoff' => $booking->dropoff_location,
        'dropoff_lat' => $booking->dropoff_lat ? floatval($booking->dropoff_lat) : null,
        'dropoff_lng' => $booking->dropoff_lng ? floatval($booking->dropoff_lng) : null,
        'duration_type' => $booking->duration_type,
        'duration_count' => $booking->duration_count,
        'payment_method' => $booking->payment_method ?? 'cash',
        'driver' => $driverData,
        'arrived_at' => $booking->arrived_at?->toIso8601String(),
        'started_at' => $booking->started_at?->toIso8601String(),
        'completed_at' => $booking->completed_at?->toIso8601String(),
        'has_review' => $booking->review !== null,
    ]);
});

Route::get('/ride/success', function (\Illuminate\Http\Request $request) {
    $ride = \App\Models\Ride::find($request->ride_id);
    if ($ride && $request->session_id) {
        // Here you would typically verify the session with Stripe
        $ride->update(['payment_status' => 'paid']);
    }
    return redirect('/ride')->with('success', "Your ride has been confirmed!");
});

// Driver Dashboard
Route::get('/dashboard', function () {
    if (auth()->user()->role === 'admin') {
        return redirect('/admin');
    }
    return redirect('/driver/dashboard');
})->middleware('auth');

Route::prefix('driver')->middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();
        
        // Ensure driver profile exists
        $profile = $user->driverProfile ?? \App\Models\DriverProfile::create([
            'user_id' => $user->id,
            'license_number' => 'DL-' . strtoupper(\Illuminate\Support\Str::random(6)),
            'hourly_rate' => 25.00,
            'daily_rate' => 170.00,
            'weekly_rate' => 950.00,
            'country' => 'USA',
        ]);
        
        // Keep driver location/activity timestamp fresh so matching doesn't consider them inactive
        if ($profile->is_available) {
            $profile->update(['last_location_update' => now()]);
        }

        $vehicles = \App\Models\Vehicle::where('owner_id', $user->id)->get();
        
        $assignedRideIds = \App\Models\RideAssignment::where('driver_id', $user->id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->whereNotNull('ride_id')
            ->pluck('ride_id')
            ->toArray();

        $unassignedPendingRideIds = [];
        if ($profile->is_available) {
            $unassignedPendingRideIds = \App\Models\Ride::where('status', 'pending')
                ->whereNull('driver_id')
                ->latest()
                ->take(5)
                ->pluck('id')
                ->toArray();
        }

        $allPendingRideIds = array_unique(array_merge($assignedRideIds, $unassignedPendingRideIds));

        $rides = \App\Models\Ride::where(function ($q) use ($user, $allPendingRideIds) {
                $q->where('driver_id', $user->id);
                if (!empty($allPendingRideIds)) {
                    $q->orWhereIn('id', $allPendingRideIds);
                }
            })
            ->with(['rider'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $assignedBookingIds = \App\Models\RideAssignment::where('driver_id', $user->id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->whereNotNull('driver_booking_id')
            ->pluck('driver_booking_id')
            ->toArray();

        $unassignedBookingIds = [];
        if ($profile->is_available) {
            $unassignedBookingIds = \App\Models\DriverBooking::where('booking_status', 'pending')
                ->whereNull('driver_id')
                ->latest()
                ->take(5)
                ->pluck('id')
                ->toArray();
        }

        $allPendingBookingIds = array_unique(array_merge($assignedBookingIds, $unassignedBookingIds));

        $driverBookings = \App\Models\DriverBooking::where(function ($q) use ($user, $allPendingBookingIds) {
                $q->where('driver_id', $user->id);
                if (!empty($allPendingBookingIds)) {
                    $q->orWhereIn('id', $allPendingBookingIds);
                }
            })
            ->with(['client', 'review'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        $activeDriverBookings = $driverBookings->whereIn('booking_status', ['accepted', 'in_progress']);
        $pendingDriverBookings = $driverBookings->where('booking_status', 'pending');
        $completedDriverBookings = $driverBookings->where('booking_status', 'completed');
        
        $activeRides = $rides->whereIn('status', ['accepted', 'en_route', 'arrived', 'in_progress']);
        $pendingRides = $rides->where('status', 'pending');
        $completedRides = $rides->where('status', 'completed');
        
        $today = now()->startOfDay();
        $startOfWeek = now()->startOfWeek();
        $startOfMonth = now()->startOfMonth();
        
        $rideEarnings = $completedRides->where('updated_at', '>=', $startOfMonth)->sum('fare');
        $bookingEarnings = $completedDriverBookings->where('updated_at', '>=', $startOfMonth)->sum('total_price');
        
        $dailyEarnings = $completedRides->where('updated_at', '>=', $today)->sum('fare') + $completedDriverBookings->where('updated_at', '>=', $today)->sum('total_price');
        $weeklyEarnings = $completedRides->where('updated_at', '>=', $startOfWeek)->sum('fare') + $completedDriverBookings->where('updated_at', '>=', $startOfWeek)->sum('total_price');
        $monthlyEarnings = $rideEarnings + $bookingEarnings;
        
        $todayTrips = $completedRides->where('updated_at', '>=', $today)->count() + $completedDriverBookings->where('updated_at', '>=', $today)->count();
        $weekTrips = $completedRides->where('updated_at', '>=', $startOfWeek)->count() + $completedDriverBookings->where('updated_at', '>=', $startOfWeek)->count();
        $monthTrips = $completedRides->where('updated_at', '>=', $startOfMonth)->count() + $completedDriverBookings->where('updated_at', '>=', $startOfMonth)->count();
        
        return view('driver.dashboard', compact(
            'user', 'profile', 'vehicles', 
            'activeRides', 'pendingRides', 'completedRides',
            'driverBookings', 'activeDriverBookings', 'pendingDriverBookings', 'completedDriverBookings',
            'dailyEarnings', 'weeklyEarnings', 'monthlyEarnings',
            'todayTrips', 'weekTrips', 'monthTrips'
        ));
    });

    Route::post('/ride/{id}/accept', function ($id) {
        $user = auth()->user();
        if (!$user) return redirect('/login');

        $ride = \App\Models\Ride::where('id', $id)->first();
        if (!$ride) return back()->with('error', 'Ride not found.');

        if ($ride->status !== 'pending') {
            return back()->with('error', 'This ride is no longer available.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($ride, $user) {
            $ride->update([
                'driver_id' => $user->id,
                'status' => 'accepted',
            ]);

            if ($user->driverProfile) {
                $user->driverProfile->update(['is_available' => false]);
            }

            \App\Models\RideAssignment::where('ride_id', $ride->id)->update(['status' => 'accepted']);
        });

        try {
            \App\Services\NotificationService::notifyRideAccepted($ride);
        } catch (\Throwable $e) {}

        return back()->with('success', "🎉 Ride #{$ride->id} accepted! You can now manage this trip.");
    });

    Route::post('/ride/{id}/decline', function ($id) {
        $user = auth()->user();
        if (!$user) return redirect('/login');

        \App\Models\RideAssignment::where('ride_id', $id)
            ->where('driver_id', $user->id)
            ->update(['status' => 'rejected']);

        return back()->with('info', 'Ride request declined.');
    });

    Route::post('/toggle-availability', function (\Illuminate\Http\Request $request) {
        $user = auth()->user();
        if ($user && $user->driverProfile) {
            $wantsAvailable = $request->has('is_available');

            if ($wantsAvailable && !$user->driverProfile->is_fully_verified) {
                $missing = [];
                if ($user->driverProfile->verification_status !== 'verified') $missing[] = 'Driver License Verification';
                if (!in_array($user->driverProfile->background_check_status, ['clear', 'verified', 'approved'])) $missing[] = 'Background Check (Checkr)';
                if ($user->driverProfile->photo_formality_status !== 'verified') $missing[] = 'Formal Profile Photo Review';

                return back()->with('error', 'Cannot go online. Verification incomplete: ' . implode(', ', $missing));
            }

            $user->driverProfile->update([
                'is_available' => $wantsAvailable,
                'last_location_update' => now(),
            ]);
            \App\Services\ActivityLogService::log('status_change', "Driver availability toggled to " . ($user->driverProfile->is_available ? 'Available' : 'Unavailable'), $user->id);
        }
        return back()->with('success', 'Availability updated.');
    });
});

// Payment Gateway API Routes
Route::post('/payment/paypal/create', function (\Illuminate\Http\Request $request) {
    $amount = (float) ($request->amount ?? 50.00);
    $result = \App\Services\PayPalService::createOrder($amount, $request->currency ?? 'USD', auth()->id(), $request->booking_id, $request->ride_id);
    return response()->json($result);
});

Route::post('/payment/paypal/capture', function (\Illuminate\Http\Request $request) {
    $request->validate(['transaction_ref' => 'required|string']);
    $result = \App\Services\PayPalService::capturePayment($request->transaction_ref, $request->order_id);
    return response()->json($result);
});

Route::post('/payment/apple-pay/validate-merchant', function (\Illuminate\Http\Request $request) {
    $result = \App\Services\ApplePayService::validateMerchant($request->validation_url ?? '');
    return response()->json($result);
});

Route::post('/payment/apple-pay/process', function (\Illuminate\Http\Request $request) {
    $amount = (float) ($request->amount ?? 50.00);
    $result = \App\Services\ApplePayService::processPayment($request->all(), $amount, $request->currency ?? 'USD', auth()->id());
    return response()->json($result);
});

Route::post('/payment/cashapp/create', function (\Illuminate\Http\Request $request) {
    $amount = (float) ($request->amount ?? 50.00);
    $result = \App\Services\CashAppService::createPaymentRequest($amount, $request->currency ?? 'USD', auth()->id());
    return response()->json($result);
});

Route::post('/payment/cashapp/webhook', function (\Illuminate\Http\Request $request) {
    $result = \App\Services\CashAppService::processCallback($request->transaction_ref ?? '', $request->status ?? 'successful', $request->all());
    return response()->json($result);
});

Route::get('/activity', function () {
    $user = auth()->user();
    $rides = \App\Models\Ride::where('rider_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();
        
    $upcomingRides = $rides->whereIn('status', ['pending', 'accepted', 'in_progress']);
    $pastRides = $rides->whereNotIn('status', ['pending', 'accepted', 'in_progress']);
    
    return view('activity', compact('upcomingRides', 'pastRides'));
})->middleware('auth');

Route::get('/account', function () {
    $user = auth()->user();
    return view('account', compact('user'));
})->middleware('auth');

Route::get('/wallet', function () {
    return view('wallet');
})->middleware('auth');

// Package Delivery Live Tracker Admin Routes
Route::get('/admin/live-delivery-tracker/data', [DeliveryTrackerController::class, 'getData']);
Route::post('/admin/live-delivery-tracker/reassign', [DeliveryTrackerController::class, 'reassignDriver']);
Route::get('/admin/package-delivery-tracker', function () {
    $controller = app(DeliveryTrackerController::class);
    $data = $controller->getData(request())->getData(true);
    return view('admin.live-delivery-tracker-standalone', [
        'initialOrders' => $data['orders'] ?? [],
        'initialAvailableDrivers' => $data['available_drivers'] ?? [],
    ]);
});

// Financial Statement CSV & PDF Export Routes
Route::get('/admin/financial-statement/export-csv', [AdminFinancialExportController::class, 'exportCsv']);
Route::get('/admin/financial-statement/export-pdf', [AdminFinancialExportController::class, 'exportPdf']);

// Contact & Inquiry Submission Route
Route::post('/contact/send', function (\Illuminate\Http\Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'nullable|string|max:50',
        'subject' => 'required|string|max:255',
        'message' => 'required|string|max:5000',
    ]);

    $adminEmail = env('ADMIN_INQUIRY_EMAIL', 'info@ridemycars.com');
    $userEmail = $validated['email'];
    $userName = $validated['name'];
    $subject = $validated['subject'];
    $msgBody = $validated['message'];
    $phone = $validated['phone'] ?? 'Not provided';

    // 1. Send Inquiry to Admin (info@ridemycars.com)
    try {
        \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($adminEmail, $userEmail, $userName, $subject, $msgBody, $phone) {
            $message->to($adminEmail)
                    ->replyTo($userEmail, $userName)
                    ->subject("📬 New Inquiry: {$subject} [From: {$userName}]")
                    ->html("
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;'>
                            <div style='background: #0b0f17; padding: 24px; color: white;'>
                                <h2 style='margin: 0; color: #f59e0b;'>RideMyCars - New Customer Inquiry</h2>
                            </div>
                            <div style='padding: 24px; background: #fafafa; color: #333; line-height: 1.6;'>
                                <p><strong>From:</strong> {$userName} (<a href='mailto:{$userEmail}'>{$userEmail}</a>)</p>
                                <p><strong>Phone:</strong> {$phone}</p>
                                <p><strong>Topic:</strong> {$subject}</p>
                                <hr style='border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;'>
                                <p><strong>Message:</strong></p>
                                <div style='background: white; padding: 16px; border-radius: 8px; border: 1px solid #e5e7eb; white-space: pre-wrap;'>{$msgBody}</div>
                            </div>
                            <div style='background: #f1f5f9; padding: 12px 24px; font-size: 12px; color: #64748b;'>
                                Sent via RideMyCars Contact System • Direct reply will go to {$userEmail}
                            </div>
                        </div>
                    ");
        });
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error('Admin inquiry mail error: ' . $e->getMessage());
    }

    // 2. Send Automated Confirmation Receipt to Customer
    try {
        \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($userEmail, $userName, $subject) {
            $message->to($userEmail)
                    ->subject("Thank you for contacting RideMyCars - We received your message")
                    ->html("
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;'>
                            <div style='background: #0b0f17; padding: 24px; color: white;'>
                                <h2 style='margin: 0; color: #f59e0b;'>RideMyCars Concierge Support</h2>
                            </div>
                            <div style='padding: 24px; background: #fafafa; color: #333; line-height: 1.6;'>
                                <p>Hello <strong>{$userName}</strong>,</p>
                                <p>Thank you for contacting RideMyCars regarding <strong>\"{$subject}\"</strong>. Our executive concierge team has received your message.</p>
                                <p>We are reviewing your inquiry and will reply to this email address shortly.</p>
                                <p>For urgent mobility needs, you can also reach us directly at <a href='mailto:support@ridemycars.com' style='color: #f59e0b; font-weight: bold;'>support@ridemycars.com</a> or call <strong>+1 888 570 0008</strong>.</p>
                                <hr style='border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;'>
                                <p style='margin: 0; color: #64748b;'>Warm regards,<br><strong>RideMyCars Support Team</strong></p>
                            </div>
                        </div>
                    ");
        });
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error('Customer confirmation mail error: ' . $e->getMessage());
    }

    \App\Services\ActivityLogService::log('inquiry_submitted', "Inquiry submitted by {$userName} ({$userEmail}): {$subject}", auth()->id());

    return back()->with('success', '🎉 Your message has been sent successfully! Our concierge team has received your inquiry and will reply to your email shortly.');
});

// Payment confirmation routes
Route::get('/payment/success', function (\Illuminate\Http\Request $request) {
    $intentId = $request->query('intent');
    $transaction = null;
    if ($intentId) {
        $transaction = \App\Models\PaymentTransaction::where('stripe_payment_intent_id', $intentId)->first();
    }
    return view('payment.success', ['transaction' => $transaction]);
});

Route::get('/payment/failed', function () {
    return view('payment.failed');
});

// Dedicated Apps Hub & Direct APK Download Endpoints
Route::get('/apps', function () {
    return view('apps');
})->name('apps.index');

Route::get('/download', function () {
    return view('apps');
})->name('apps.download');

Route::get('/download/rider', function () {
    $filePath = public_path('ridemycars-rider.apk');
    if (file_exists($filePath)) {
        return response()->download($filePath, 'RideMyCars-Rider.apk', [
            'Content-Type' => 'application/vnd.android.package-archive',
        ]);
    }
    $fallbackPath = public_path('ridemycars.apk');
    if (file_exists($fallbackPath)) {
        return response()->download($fallbackPath, 'RideMyCars-Rider.apk', [
            'Content-Type' => 'application/vnd.android.package-archive',
        ]);
    }
    return redirect('/apps')->with('info', 'Rider APK is being prepared.');
})->name('download.rider');

Route::get('/download/user', function () {
    return redirect()->route('download.rider');
});

Route::get('/download/driver', function () {
    $filePath = public_path('ridemycars-driver.apk');
    if (file_exists($filePath)) {
        return response()->download($filePath, 'RideMyCars-Driver.apk', [
            'Content-Type' => 'application/vnd.android.package-archive',
        ]);
    }
    $fallbackPath = public_path('ridemycars.apk');
    if (file_exists($fallbackPath)) {
        return response()->download($fallbackPath, 'RideMyCars-Driver.apk', [
            'Content-Type' => 'application/vnd.android.package-archive',
        ]);
    }
    return redirect('/apps')->with('info', 'Driver APK is being prepared.');
})->name('download.driver');

Route::get('/download/app', function () {
    return redirect()->route('download.rider');
});

// Generic & Legal pages
$pages = [
    'safety', 'blog', 'careers', 'partner', 'help', 'contact', 'faq', 'support', 
    'refund', 'cookie', 'pricing', 'list-vehicle', 'legal', 'terms', 'privacy', 
    'terms-of-service', 'privacy-policy', 'services', 'about', 'cookies'
];
foreach ($pages as $page) {
    Route::get('/' . $page, function () use ($page) {
        $title = ucwords(str_replace('-', ' ', $page));
        if ($page === 'terms-of-service' && view()->exists('terms')) return view('terms');
        if ($page === 'privacy-policy' && view()->exists('privacy')) return view('privacy');
        if ($page === 'cookies' && view()->exists('cookie')) return view('cookie');
        
        if (view()->exists($page)) {
            return view($page);
        }
        return view('page', ['title' => $title]);
    });
}

Route::get('/api-sync-deploy', function (\Illuminate\Http\Request $request) {
    if ($request->query('key') !== 'rmc2026') {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    
    $output = [];
    
    // Ensure personal_access_tokens table
    try {
        if (!\Illuminate\Support\Facades\Schema::hasTable('personal_access_tokens')) {
            \Illuminate\Support\Facades\Schema::create('personal_access_tokens', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->morphs('tokenable');
                $table->text('name');
                $table->string('token', 64)->unique();
                $table->text('abilities')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable()->index();
                $table->timestamps();
            });
            $output['tokens_table'] = 'Created';
        } else {
            $output['tokens_table'] = 'Exists';
        }
    } catch (\Throwable $e) {
        $output['tokens_table_err'] = $e->getMessage();
    }
    
    // Run git pull
    $output['git_pull'] = shell_exec('cd ' . base_path('..') . ' && git pull origin main 2>&1');
    
    // Run migrations & seeders
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $output['migrate'] = \Illuminate\Support\Facades\Artisan::output();
    } catch (\Throwable $e) {
        $output['migrate_err'] = $e->getMessage();
    }

    try {
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
        $output['seed'] = \Illuminate\Support\Facades\Artisan::output();
    } catch (\Throwable $e) {
        $output['seed_err'] = $e->getMessage();
    }
    
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    
    return response()->json([
        'status' => 'success',
        'details' => $output,
    ]);
});


