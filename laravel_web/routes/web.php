<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
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

    auth()->login($user);

    return redirect('/');
});

Route::post('/logout', function (\Illuminate\Http\Request $request) {
    auth()->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
});

Route::get('/terms', function () {
    return view('terms');
});

Route::get('/privacy', function () {
    return view('privacy');
});

Route::get('/pricing', function () {
    return view('pricing');
});

Route::get('/about', function () {
    return view('about');
});

Route::redirect('/company', '/about');

Route::get('/safety', function () {
    return view('safety');
});

Route::get('/become-driver', function () {
    return view('become-driver');
});

Route::get('/become-owner', function () {
    return view('become-owner');
});

Route::get('/blogs', function () {
    return view('blogs');
});

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

    \App\Models\Ride::create([
        'rider_id' => $riderId,
        'pickup_location' => $request->pickup_location,
        'dropoff_location' => $request->dropoff_location,
        'vehicle_type' => $request->vehicle_type,
        'payment_method' => $request->payment_method,
        'notes' => $request->notes,
        'status' => 'pending',
    ]);

    return redirect('/admin/rides')->with('success', 'Ride booked successfully!');
});
Route::prefix('driver')->middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        if (auth()->user()->role !== 'driver') abort(403);
        
        $user = auth()->user();
        
        // Ensure driver profile exists
        $profile = $user->driverProfile ?? \App\Models\DriverProfile::create(['user_id' => $user->id, 'license_number' => 'PENDING-' . $user->id]);
        
        // Vehicles where driver is owner
        $vehicles = \App\Models\Vehicle::where('owner_id', $user->id)->get();
        
        // Jobs
        $rides = \App\Models\Ride::where('driver_id', $user->id)->orderBy('created_at', 'desc')->get();
        
        $activeRides = $rides->whereIn('status', ['accepted', 'in_progress']);
        $pendingRides = $rides->where('status', 'pending'); // actually pending rides wait for driver assignment, but let's assume they requested it or were assigned
        $completedRides = $rides->where('status', 'completed');
        
        // Earnings
        $today = now()->startOfDay();
        $startOfWeek = now()->startOfWeek();
        $startOfMonth = now()->startOfMonth();
        
        $dailyEarnings = $completedRides->where('updated_at', '>=', $today)->sum('fare');
        $weeklyEarnings = $completedRides->where('updated_at', '>=', $startOfWeek)->sum('fare');
        $monthlyEarnings = $completedRides->where('updated_at', '>=', $startOfMonth)->sum('fare');
        
        return view('driver.dashboard', compact(
            'user', 'profile', 'vehicles', 
            'activeRides', 'pendingRides', 'completedRides',
            'dailyEarnings', 'weeklyEarnings', 'monthlyEarnings'
        ));
    });
});

Route::get('/hire-driver', function () {
    $drivers = \App\Models\DriverProfile::with('user')->get();
    return view('hire-driver', compact('drivers'));
});

Route::get('/rent/{vehicle}', function (\App\Models\Vehicle $vehicle) {
    return view('vehicle-detail', compact('vehicle'));
});

Route::get('/hire-driver/{driverProfile}', function (\App\Models\DriverProfile $driverProfile) {
    $driverProfile->load('user');
    return view('driver-detail', compact('driverProfile'));
});

// Generic pages
$pages = ['safety', 'blog', 'careers', 'partner', 'help', 'contact', 'faq', 'support', 'refund', 'cookie', 'pricing', 'list-vehicle'];
foreach ($pages as $page) {
    Route::get('/' . $page, function () use ($page) {
        $title = ucwords(str_replace('-', ' ', $page));
        // If a specific view exists, it will use that, otherwise fallback to generic page
        if (view()->exists($page)) {
            return view($page);
        }
        return view('page', ['title' => $title]);
    });
}
