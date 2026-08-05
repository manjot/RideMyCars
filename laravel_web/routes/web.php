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
        return redirect()->intended('/');
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ])->onlyInput('email');
});

Route::get('/signup', function () {
    return view('signup');
})->name('signup');

Route::post('/signup', function (\Illuminate\Http\Request $request) {
    $validated = $request->validate([
        'first_name' => ['required', 'string', 'max:255'],
        'last_name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ]);

    $user = \App\Models\User::create([
        'name' => $validated['first_name'] . ' ' . $validated['last_name'],
        'email' => $validated['email'],
        'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
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
