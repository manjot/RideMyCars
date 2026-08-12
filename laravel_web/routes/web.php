<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DriverBookingController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/delivery', function (\Illuminate\Http\Request $request) {
    return view('delivery', [
        'pickup' => $request->query('pickup'),
        'dropoff' => $request->query('dropoff'),
    ]);
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
        \App\Models\DriverProfile::create([
            'user_id' => $user->id,
            'license_number' => 'DL-' . strtoupper(\Illuminate\Support\Str::random(6)),
            'hourly_rate' => 25.00,
            'daily_rate' => 170.00,
            'weekly_rate' => 950.00,
            'is_available' => true,
            'rating' => 5.00,
            'country' => 'USA',
        ]);
    }

    auth()->login($user);

    \App\Services\ActivityLogService::log('register', "User registered as {$user->role}", $user->id);

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

Route::get('/ride', function () {
    return view('ride');
});

Route::post('/ride/book', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'pickup_location' => 'required|string|max:255',
        'dropoff_location' => 'required|string|max:255',
        'vehicle_type' => 'nullable|string|max:255',
        'payment_method' => 'nullable|string|max:255',
        'notes' => 'nullable|string',
    ]);

    $riderId = auth()->id() ?? \App\Models\User::first()->id ?? 1;

    $ride = \App\Models\Ride::create([
        'rider_id' => $riderId,
        'pickup_location' => $request->pickup_location,
        'dropoff_location' => $request->dropoff_location,
        'vehicle_type' => $request->vehicle_type,
        'payment_method' => $request->payment_method,
        'notes' => $request->notes,
        'status' => 'pending',
    ]);

    \App\Services\ActivityLogService::log('booking_creation', "Created ride booking #{$ride->id}", $riderId);

    return redirect('/admin/rides')->with('success', 'Ride booked successfully!');
});

// Driver Dashboard
Route::prefix('driver')->middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        if (auth()->user()->role !== 'driver') abort(403);
        
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
            $user->driverProfile->update([
                'is_available' => $request->has('is_available'),
            ]);
            \App\Services\ActivityLogService::log('status_change', "Driver availability toggled to " . ($user->driverProfile->is_available ? 'Available' : 'Unavailable'), $user->id);
        }
        return back()->with('success', 'Availability updated.');
    });
});

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
