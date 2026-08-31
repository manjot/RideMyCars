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
Route::post('/api/driver/verify-booking', [\App\Http\Controllers\StripeVerificationController::class, 'driverRespond']);
Route::get('/api/driver/pending-verifications', [\App\Http\Controllers\StripeVerificationController::class, 'getPendingVerifications']);
Route::get('/api/payment/verification-status/{serviceType}/{serviceId}', [\App\Http\Controllers\StripeVerificationController::class, 'getVerificationStatus']);

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
    $request->validate(['email' => 'required|email']);
    $email = $request->email;
    $otp = rand(1000, 9999);
    \Illuminate\Support\Facades\Cache::put('otp_' . $email, $otp, now()->addMinutes(10));
    
    $mailError = null;
    try {
        \Illuminate\Support\Facades\Mail::raw("Your RideMyCars login code is: {$otp}", function ($message) use ($email) {
            $message->to($email)->subject('Your RideMyCars Login Code');
        });
    } catch (\Throwable $e) {
        $mailError = $e->getMessage();
        \Illuminate\Support\Facades\Log::error('Mail error: ' . $mailError);
        \Illuminate\Support\Facades\Log::info("OTP for {$email} is {$otp}");
    }
    
    if ($mailError) {
        return response()->json(['message' => 'OTP generated but email failed', 'mail_error' => $mailError, 'debug_otp' => $otp]);
    }
    return response()->json(['message' => 'OTP sent successfully']);
});

Route::post('/api/otp/verify', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'email' => 'required|email',
        'otp' => 'required|numeric'
    ]);
    
    $cachedOtp = \Illuminate\Support\Facades\Cache::get('otp_' . $request->email);
    if ($cachedOtp && (string) $cachedOtp === (string) $request->otp) {
        \Illuminate\Support\Facades\Cache::forget('otp_' . $request->email);
        $user = \App\Models\User::firstOrCreate(
            ['email' => $request->email],
            [
                'name' => explode('@', $request->email)[0],
                'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(16)),
                'role' => 'customer'
            ]
        );
        auth()->login($user);
        $request->session()->regenerate();
        \App\Services\NotificationService::notifyLogin($user);
        return response()->json(['message' => 'Verified successfully', 'redirect' => session()->pull('url.intended', '/')]);
    }
    
    return response()->json(['error' => 'Invalid or expired OTP'], 422);
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
    ]);

    $user = \App\Models\User::create([
        'name' => $validated['first_name'] . ' ' . $validated['last_name'],
        'email' => $validated['email'],
        'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
        'role' => $validated['role'] ?? 'customer',
        'terms_accepted' => true,
        'terms_accepted_at' => now(),
        'terms_version' => '2026-08-23',
        'account_status' => 'active',
    ]);

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
            'stops.*' => 'nullable|string|max:255',
        ]);

        $riderId = auth()->id() ?? \App\Models\User::first()->id ?? 1;

        $digitalReceipt = 'REC-' . strtoupper(\Illuminate\Support\Str::random(8));
        $paymentMethod = $request->payment_method ?? 'Credit Card';
        $rawAmount = floatval($request->amount);
        $amount = $rawAmount > 0 ? $rawAmount : 28.50;

        $isForSomeoneElse = $request->boolean('is_for_someone_else');
        $passengerName = $request->input('passenger_name');
        $passengerPhone = $request->input('passenger_phone') ?? $request->input('phone_number');

        if (empty($passengerPhone)) {
            $user = auth()->user();
            $passengerPhone = $user?->phone ?? null;
        }

        if (empty($passengerPhone)) {
            return response()->json(['error' => 'Phone number is required to request a ride.'], 422);
        }

        $ride = \App\Models\Ride::create([
            'rider_id' => $riderId,
            'pickup_location' => $request->pickup_location,
            'pickup_lat' => $request->input('pickup_lat'),
            'pickup_lng' => $request->input('pickup_lng'),
            'dropoff_location' => $request->dropoff_location,
            'dropoff_lat' => $request->input('dropoff_lat'),
            'dropoff_lng' => $request->input('dropoff_lng'),
            'fare' => $amount,
            'vehicle_type' => $request->vehicle_type ?? 'Economy',
            'payment_method' => $paymentMethod,
            'is_for_someone_else' => $isForSomeoneElse,
            'passenger_name' => $passengerName,
            'passenger_phone' => $passengerPhone,
            'notes' => $request->notes,
            'digital_receipt_code' => $digitalReceipt,
            'status' => 'pending',
        ]);

        // Save stops if provided
        if ($request->has('stops') && is_array($request->stops)) {
            $order = 1;
            foreach ($request->stops as $stopLocation) {
                if (!empty(trim($stopLocation))) {
                    \App\Models\RideStop::create([
                        'ride_id' => $ride->id,
                        'stop_order' => $order++,
                        'location' => trim($stopLocation),
                    ]);
                }
            }
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

        if (strtolower($paymentMethod) === 'stripe') {
            try {
                $intentData = \App\Services\StripeService::createPaymentIntent('ride', $ride->id, auth()->id());

                return response()->json([
                    'success' => true,
                    'ride_id' => $ride->id,
                    'stripe_client_secret' => $intentData['client_secret'],
                    'stripe_publishable_key' => $intentData['publishable_key'],
                    'stripe_intent_id' => $intentData['payment_intent_id'],
                    'amount' => $intentData['amount'],
                    'currency' => $intentData['currency'],
                ]);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
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

    $response = [
        'status' => $ride->status,
        'fare' => ($ride->fare && floatval($ride->fare) > 0) ? floatval($ride->fare) : 28.50,
        'pickup' => $ride->pickup_location,
        'pickup_lat' => $ride->pickup_lat ? floatval($ride->pickup_lat) : null,
        'pickup_lng' => $ride->pickup_lng ? floatval($ride->pickup_lng) : null,
        'dropoff' => $ride->dropoff_location,
        'dropoff_lat' => $ride->dropoff_lat ? floatval($ride->dropoff_lat) : null,
        'dropoff_lng' => $ride->dropoff_lng ? floatval($ride->dropoff_lng) : null,
        'stops' => $ride->stops->map(fn($s) => ['order' => $s->stop_order, 'location' => $s->location, 'lat' => $s->lat ? floatval($s->lat) : null, 'lng' => $s->lng ? floatval($s->lng) : null]),
        'passenger_phone' => $ride->passenger_phone,
        'payment_method' => $ride->payment_method ?? 'cash',
        'vehicle_type' => $ride->vehicle_type ?? 'Sedan',
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
Route::get('/api/notifications', function () {
    $user = auth()->user();
    if (!$user) return response()->json(['notifications' => [], 'unread_count' => 0]);

    $cached = \Illuminate\Support\Facades\Cache::remember('user_notifications_' . $user->id, 2, function () use ($user) {
        $notifications = \App\Models\UserNotification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(20)
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
                    'time_ago' => $n->created_at->diffForHumans(),
                    'created_at' => $n->created_at->toIso8601String(),
                ];
            });

        $unreadCount = \App\Models\UserNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return [
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ];
    });

    return response()->json($cached);
})->middleware('auth');

// Mark Notifications as Read
Route::post('/api/notifications/mark-read', function (\Illuminate\Http\Request $request) {
    $user = auth()->user();
    if (!$user) return response()->json(['success' => false], 401);

    $id = $request->input('id');
    if ($id) {
        \App\Models\UserNotification::where('user_id', $user->id)->where('id', $id)->update(['is_read' => true]);
    } else {
        \App\Models\UserNotification::where('user_id', $user->id)->where('is_read', false)->update(['is_read' => true]);
    }

    return response()->json(['success' => true]);
})->middleware('auth');

// Clear All Notifications
Route::post('/api/notifications/clear', function () {
    $user = auth()->user();
    if (!$user) return response()->json(['success' => false], 401);

    \App\Models\UserNotification::where('user_id', $user->id)->delete();
    return response()->json(['success' => true]);
})->middleware('auth');

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

        $driverProfile = $ride->driver && $ride->driver->driverProfile ? $ride->driver->driverProfile : null;

        return ['ride' => [
            'id' => $ride->id,
            'status' => $ride->status,
            'pickup_location' => $ride->pickup_location,
            'dropoff_location' => $ride->dropoff_location,
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

    // Reassign to all active drivers
    $service = new \App\Services\RideAssignmentService();
    $service->assignToActiveDrivers($ride);

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
Route::get('/api/driver/active-rides', function () {
    $user = auth()->user();
    if (!$user) return response()->json([]);

    $rides = \App\Models\Ride::where('driver_id', $user->id)
        ->whereIn('status', ['accepted', 'en_route', 'arrived', 'in_progress'])
        ->where('created_at', '>=', now()->subHours(24)) // Only last 24h
        ->with(['rider', 'driverReview'])
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($ride) {
            return [
                'id' => $ride->id,
                'status' => $ride->status,
                'pickup_location' => $ride->pickup_location,
                'dropoff_location' => $ride->dropoff_location,
                'fare' => $ride->fare,
                'payment_method' => $ride->payment_method,
                'rider' => $ride->rider ? ['name' => $ride->rider->name] : null,
                'hasReview' => $ride->driverReview !== null,
                'created_at' => $ride->created_at->toIso8601String(),
            ];
        });

    return response()->json($rides);
})->middleware('auth');

// Polling endpoint for Driver to get incoming requests
Route::get('/api/driver/requests', function () {
    $user = auth()->user();
    if (!$user) return response()->json([]);

    $pending = \App\Models\RideAssignment::where('driver_id', $user->id)
        ->where('status', 'pending')
        ->where('expires_at', '>', now())
        ->with(['ride', 'driverBooking'])
        ->get();
        
    return response()->json($pending);
})->middleware('auth');

// Endpoint for Driver to Accept/Decline
Route::post('/api/driver/requests/{id}/respond', function (\Illuminate\Http\Request $request, $id) {
    $assignment = \App\Models\RideAssignment::find($id);
    if (!$assignment || (int)$assignment->driver_id !== (int)auth()->id()) {
        return response()->json(['error' => 'Unauthorized or request not found.'], 403);
    }

    $status = $request->input('status'); // 'accepted' or 'rejected'

    if ($status === 'accepted') {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($assignment) {
            if ($assignment->package_delivery_id) {
                $delivery = \App\Models\PackageDelivery::where('id', $assignment->package_delivery_id)->lockForUpdate()->first();
                if (!$delivery) {
                    return response()->json(['error' => 'Associated package delivery not found.'], 404);
                }

                if (in_array($delivery->delivery_status, ['courier_accepted', 'going_to_pickup', 'arrived_at_pickup', 'parcel_picked_up', 'in_transit', 'delivered']) && (int)$delivery->courier_id !== (int)auth()->id()) {
                    $assignment->update(['status' => 'expired']);
                    return response()->json(['error' => 'This package delivery was already accepted by another courier.'], 409);
                }

                $assignment->update(['status' => 'accepted']);
                $driverUser = auth()->user();
                $delivery->update([
                    'courier_id' => auth()->id(),
                    'courier_profile_id' => $driverUser?->driverProfile?->id,
                    'delivery_status' => 'courier_accepted',
                ]);

                if ($driverUser && $driverUser->driverProfile) {
                    $driverUser->driverProfile->update(['is_available' => false]);
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

                if ($booking->booking_status === 'accepted' && (int)$booking->driver_id !== (int)auth()->id()) {
                    $assignment->update(['status' => 'expired']);
                    return response()->json(['error' => 'This driver booking was already accepted by another driver.'], 409);
                }

                $assignment->update(['status' => 'accepted']);
                $booking->update([
                    'driver_id' => auth()->id(),
                    'booking_status' => 'accepted',
                ]);

                $driverUser = auth()->user();
                if ($driverUser && $driverUser->driverProfile) {
                    $driverUser->driverProfile->update(['is_available' => false]);
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

            if ($ride->status === 'accepted' && (int)$ride->driver_id !== (int)auth()->id()) {
                $assignment->update(['status' => 'expired']);
                return response()->json(['error' => 'This ride was already accepted by another driver.'], 409);
            }

            $assignment->update(['status' => 'accepted']);

            // Assign the ride to the driver and set status
            $ride->update([
                'driver_id' => auth()->id(),
                'status' => 'accepted',
            ]);

            // Set driver availability to false (busy) while on ride
            $driverUser = auth()->user();
            if ($driverUser && $driverUser->driverProfile) {
                $driverUser->driverProfile->update(['is_available' => false]);
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
})->middleware('auth');

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
        
        $vehicles = \App\Models\Vehicle::where('owner_id', $user->id)->get();
        $rides = \App\Models\Ride::where('driver_id', $user->id)->orderBy('created_at', 'desc')->get();
        
        $driverBookings = \App\Models\DriverBooking::where('driver_id', $user->id)
            ->with(['client', 'review'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        $activeDriverBookings = $driverBookings->whereIn('booking_status', ['accepted', 'in_progress']);
        $pendingDriverBookings = $driverBookings->where('booking_status', 'pending');
        $completedDriverBookings = $driverBookings->where('booking_status', 'completed');
        
        $activeRides = $rides->whereIn('status', ['accepted', 'in_progress']);
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
