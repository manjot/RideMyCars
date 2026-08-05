<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('login');
});

Route::get('/signup', function () {
    return view('signup');
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

Route::get('/rent', function () {
    $vehicles = \App\Models\Vehicle::all();
    return view('rent', compact('vehicles'));
});

Route::get('/ride', function () {
    return view('ride');
});

Route::get('/hire-driver', function () {
    $drivers = \App\Models\DriverProfile::with('user')->get();
    return view('hire-driver', compact('drivers'));
});
