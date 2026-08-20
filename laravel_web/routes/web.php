<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DriverBookingController;

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
        $request->session()->regenerate();
        
        \App\Services\ActivityLogService::log('login', 'User logged in successfully', auth()->id());

        if (auth()->user()->role === 'driver') {
            return redirect('/driver/dashboard');
        }
        return redirect()->intended('/');
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ])->onlyInput('email');
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
    ]);

    $user = \App\Models\User::create([
        'name' => $validated['first_name'] . ' ' . $validated['last_name'],
        'email' => $validated['email'],
        'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
        'role' => $validated['role'] ?? 'customer',
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

Route::get('/rent', function () {
    $vehicles = \App\Models\Vehicle::all();
    return view('rent', compact('vehicles'));
});

Route::get('/rent/{vehicle}', function (\App\Models\Vehicle $vehicle) {
    return view('vehicle-detail', compact('vehicle'));
});

Route::post('/rent/{vehicle}/book', function (\Illuminate\Http\Request $request, \App\Models\Vehicle $vehicle) {
    $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'pickup_location' => 'required|string|max:255',
        'driver_license' => 'required|string|max:255',
        'payment_method' => 'nullable|string|max:255',
    ]);

    $riderId = auth()->id() ?? \App\Models\User::first()->id ?? 1;

    $startDate = \Carbon\Carbon::parse($request->start_date);
    $endDate = \Carbon\Carbon::parse($request->end_date);
    $days = max(1, $startDate->diffInDays($endDate));

    $totalPrice = $days * $vehicle->daily_rate;
    $rentalCode = 'RENT-' . strtoupper(\Illuminate\Support\Str::random(8));

    $ride = \App\Models\Ride::create([
        'rider_id' => $riderId,
        'pickup_location' => $request->pickup_location,
        'dropoff_location' => "Self-Drive Return to " . $request->pickup_location,
        'vehicle_type' => "Vehicle Rental ({$vehicle->make} {$vehicle->model})",
        'payment_method' => $request->payment_method ?? 'Credit Card',
        'notes' => "Rental Dates: {$request->start_date} to {$request->end_date} ({$days} days). License: {$request->driver_license}",
        'digital_receipt_code' => $rentalCode,
        'status' => 'confirmed',
        'fare' => $totalPrice,
    ]);

    \App\Services\ActivityLogService::log('rental_created', "Created vehicle rental booking #{$ride->id} for {$vehicle->make} {$vehicle->model} (Receipt: {$rentalCode})", $riderId);

    return redirect('/rent/' . $vehicle->id)->with('success', "Vehicle rental reservation confirmed! Confirmation Code: {$rentalCode}. Dates: {$request->start_date} to {$request->end_date} ({$days} days @ \${$vehicle->daily_rate}/day). Total: \${$totalPrice}.");
});

Route::get('/ride', function () {
    $vehicles = \App\Models\Vehicle::all();
    return view('ride', compact('vehicles'));
});

Route::post('/ride/book', function (\Illuminate\Http\Request $request) {
    try {
        $request->validate([
            'pickup_location' => 'required|string|max:255',
            'dropoff_location' => 'required|string|max:255',
            'vehicle_type' => 'nullable|string|max:255',
            'payment_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'amount' => 'nullable|numeric',
        ]);

        $riderId = auth()->id() ?? \App\Models\User::first()->id ?? 1;

        $digitalReceipt = 'REC-' . strtoupper(\Illuminate\Support\Str::random(8));
        
        $paymentMethod = $request->payment_method ?? 'Credit Card';
        $amount = $request->amount ?? 15.00;

        $ride = \App\Models\Ride::create([
            'rider_id' => $riderId,
            'pickup_location' => $request->pickup_location,
            'dropoff_location' => $request->dropoff_location,
            'vehicle_type' => $request->vehicle_type ?? 'Economy',
            'payment_method' => $paymentMethod,
            'notes' => $request->notes,
            'digital_receipt_code' => $digitalReceipt,
            'status' => 'pending',
        ]);

        try {
            \App\Services\ActivityLogService::log('booking_creation', "Created ride booking #{$ride->id}", $riderId);
        } catch (\Throwable $e) {
            // Activity log is non-critical, don't fail the booking
        }

        // Trigger the initial round-robin assignment
        \App\Services\RideAssignmentService::assignNextDriver($ride);

        if (strtolower($paymentMethod) === 'stripe') {
            try {
                \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
                
                $session = \Stripe\Checkout\Session::create([
                    'payment_method_types' => ['card'],
                    'line_items' => [[
                        'price_data' => [
                            'currency' => 'usd',
                            'product_data' => [
                                'name' => 'Ride with ' . ($request->vehicle_type ?? 'Economy'),
                                'description' => 'From ' . substr($request->pickup_location, 0, 100) . ' to ' . substr($request->dropoff_location, 0, 100),
                            ],
                            'unit_amount' => (int)($amount * 100),
                        ],
                        'quantity' => 1,
                    ]],
                    'mode' => 'payment',
                    'success_url' => url('/ride/success?session_id={CHECKOUT_SESSION_ID}&ride_id=' . $ride->id),
                    'cancel_url' => url('/ride'),
                ]);

                return response()->json([
                    'url' => $session->url,
                    'polling_url' => url('/api/ride/' . $ride->id . '/status')
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Stripe Checkout Error: ' . $e->getMessage());
                return response()->json(['error' => 'Payment gateway error: ' . $e->getMessage()], 500);
            }
        }

        return response()->json([
            'success' => true, 
            'ride_id' => $ride->id,
            'polling_url' => url('/api/ride/' . $ride->id . '/status')
        ]);

    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error('Ride booking error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// Polling endpoint for Rider to check ride lifecycle status
Route::get('/api/ride/{id}/status', function ($id) {
    $ride = \App\Models\Ride::with(['driver', 'riderReview'])->find($id);
    if (!$ride) return response()->json(['error' => 'Not found'], 404);

    $response = [
        'status' => $ride->status,
        'driver_name' => $ride->driver?->name,
        'arrived_at' => $ride->arrived_at?->toIso8601String(),
        'started_at' => $ride->started_at?->toIso8601String(),
        'completed_at' => $ride->completed_at?->toIso8601String(),
        'has_review' => $ride->riderReview !== null,
    ];

    // If still pending, check for expired assignments
    if ($ride->status === 'pending') {
        $activeAssignment = \App\Models\RideAssignment::where('ride_id', $ride->id)->where('status', 'pending')->first();
        if (!$activeAssignment) {
            $ride->update(['status' => 'failed']);
            $response['status'] = 'failed';
            $response['message'] = 'No drivers available';
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
    if ($newStatus === 'completed') $updates['completed_at'] = now();

    $ride->update($updates);

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

    return response()->json(['success' => true]);
})->middleware('auth');

// My Rides page
Route::get('/my-rides', function () {
    $user = auth()->user();
    $rides = \App\Models\Ride::where('rider_id', $user->id)
        ->with(['driver', 'riderReview', 'driverReview'])
        ->orderBy('created_at', 'desc')
        ->paginate(20);

    return view('my-rides', compact('user', 'rides'));
})->middleware('auth');



// Get ongoing ride for current user (rider or driver)
Route::get('/api/user/ongoing-ride', function () {
    $user = auth()->user();
    if (!$user) return response()->json(['ride' => null]);

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

    if (!$ride) return response()->json(['ride' => null]);

    return response()->json(['ride' => [
        'id' => $ride->id,
        'status' => $ride->status,
        'pickup_location' => $ride->pickup_location,
        'dropoff_location' => $ride->dropoff_location,
        'driver_name' => $ride->driver ? $ride->driver->name : null,
        'rider_name' => $ride->rider ? $ride->rider->name : null,
        'fare' => $ride->fare,
    ]]);
})->middleware('auth');

// Get active rides for driver
Route::get('/api/driver/active-rides', function () {
    $user = auth()->user();
    if (!$user) return response()->json([]);

    $rides = \App\Models\Ride::where('driver_id', $user->id)
        ->whereIn('status', ['accepted', 'en_route', 'arrived', 'in_progress', 'completed'])
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
        ->with('ride')
        ->get();
        
    return response()->json($pending);
})->middleware('auth');

// Endpoint for Driver to Accept/Decline
Route::post('/api/driver/requests/{id}/respond', function (\Illuminate\Http\Request $request, $id) {
    $assignment = \App\Models\RideAssignment::find($id);
    if (!$assignment || $assignment->driver_id !== auth()->id()) {
        return response()->json(['error' => 'Unauthorized or not found'], 403);
    }

    $status = $request->input('status'); // 'accepted' or 'rejected'
    $assignment->update(['status' => $status]);

    if ($status === 'accepted') {
        // Race condition: check if ride is already accepted by another driver
        $ride = $assignment->ride;
        if ($ride->status === 'accepted' && $ride->driver_id !== auth()->id()) {
            $assignment->update(['status' => 'expired']);
            return response()->json(['error' => 'This ride was already accepted by another driver'], 409);
        }
        // Assign the ride to the driver
        $ride->update([
            'driver_id' => auth()->id(),
            'status' => 'accepted'
        ]);
        // Expire all other pending assignments for this ride
        \App\Models\RideAssignment::where('ride_id', $assignment->ride_id)
            ->where('id', '!=', $assignment->id)
            ->where('status', 'pending')
            ->update(['status' => 'expired']);
    } elseif ($status === 'rejected') {
        // Try next driver
        \App\Services\RideAssignmentService::assignNextDriver($assignment->ride);
    }

    return response()->json(['success' => true]);
})->middleware('auth');

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
        
        return view('driver.dashboard', compact(
            'user', 'profile', 'vehicles', 
            'activeRides', 'pendingRides', 'completedRides',
            'driverBookings', 'activeDriverBookings', 'pendingDriverBookings', 'completedDriverBookings',
            'dailyEarnings', 'weeklyEarnings', 'monthlyEarnings'
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

// Generic pages
$pages = ['safety', 'blog', 'careers', 'partner', 'help', 'contact', 'faq', 'support', 'refund', 'cookie', 'pricing', 'list-vehicle'];
foreach ($pages as $page) {
    Route::get('/' . $page, function () use ($page) {
        $title = ucwords(str_replace('-', ' ', $page));
        if (view()->exists($page)) {
            return view($page);
        }
        return view('page', ['title' => $title]);
    });
}
