<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pricing — RideMyCars</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#fafafa] text-gray-900 min-h-screen flex flex-col">
    
    <!-- Header -->
    <header class="top-0 left-0 right-0 z-50 bg-white border-b border-gray-100">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex h-20 items-center justify-between">
                <!-- Logo -->
                <a class="flex items-center gap-2 group" href="/">
                    <div class="w-10 h-10 rounded-xl bg-orange-500 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-white"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"></path><circle cx="7" cy="17" r="2"></circle><path d="M9 17h6"></path><circle cx="17" cy="17" r="2"></circle></svg>
                    </div>
                    <span class="font-bold text-2xl tracking-tight text-gray-900">Ride<span class="text-orange-500">MyCars</span></span>
                </a>
                
                <!-- Desktop Nav -->
                <div class="hidden lg:flex items-center gap-6">
                    <a class="text-sm font-medium transition-colors text-gray-500 hover:text-gray-900" href="/ride">Ride</a>
                    <a class="text-sm font-medium transition-colors text-gray-500 hover:text-gray-900" href="/rent">Rent Vehicle</a>
                    <a class="text-sm font-medium transition-colors text-gray-500 hover:text-gray-900" href="/hire-driver">Hire Driver</a>
                    <a class="text-sm font-medium transition-colors text-gray-500 hover:text-gray-900 flex items-center gap-1" href="/company">Company <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg></a>
                    <a class="text-sm font-medium transition-colors text-gray-900 bg-gray-100 px-4 py-2 rounded-full" href="/pricing">Pricing</a>
                </div>
                
                <!-- Actions -->
                <div class="flex items-center gap-6">
                    <button class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                    </button>
                    <a class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors" href="/login">Sign In</a>
                    <a class="px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-xl transition-all shadow-md shadow-orange-500/25" href="/signup">Get Started</a>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="flex-1 pb-24">
        
        <!-- Header -->
        <div class="max-w-4xl mx-auto text-center px-4 pt-20 pb-16">
            <h3 class="text-orange-500 font-bold text-sm tracking-widest uppercase mb-4">Pricing</h3>
            <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4 tracking-tight">Simple, transparent pricing</h1>
            <p class="text-lg text-gray-500">No surge surprises, no hidden fees. What you see is what you pay.</p>
        </div>

        <!-- Section 1: Ride Hailing -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-24">
            <h2 class="text-3xl font-bold text-gray-900 mb-8">Ride Hailing</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start">
                
                <!-- Economy -->
                <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                    <h3 class="text-xl font-bold text-gray-900 mb-1">Economy</h3>
                    <p class="text-sm text-gray-500 mb-8">Affordable daily rides</p>
                    
                    <div class="space-y-4 mb-8">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Base fare</span>
                            <span class="font-bold text-gray-900">$2.50</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Per km</span>
                            <span class="font-bold text-gray-900">$0.90</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Per minute</span>
                            <span class="font-bold text-gray-900">$0.15</span>
                        </div>
                        <div class="pt-4 mt-4 border-t border-gray-100 flex justify-between items-center text-sm">
                            <span class="text-gray-500">Minimum fare</span>
                            <span class="font-bold text-orange-500">$4.00</span>
                        </div>
                    </div>
                    
                    <button class="w-full py-3 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl transition-colors">Book Economy</button>
                </div>

                <!-- Comfort (Popular) -->
                <div class="bg-white rounded-3xl p-8 border-2 border-orange-500 shadow-lg shadow-orange-50 relative transform md:-translate-y-4">
                    <div class="absolute -top-3 inset-x-0 flex justify-center">
                        <span class="bg-orange-500 text-white text-xs font-bold px-3 py-1 rounded-full">Most Popular</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-1">Comfort</h3>
                    <p class="text-sm text-gray-500 mb-8">Spacious, newer vehicles</p>
                    
                    <div class="space-y-4 mb-8">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Base fare</span>
                            <span class="font-bold text-gray-900">$3.00</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Per km</span>
                            <span class="font-bold text-gray-900">$1.20</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Per minute</span>
                            <span class="font-bold text-gray-900">$0.18</span>
                        </div>
                        <div class="pt-4 mt-4 border-t border-gray-100 flex justify-between items-center text-sm">
                            <span class="text-gray-500">Minimum fare</span>
                            <span class="font-bold text-orange-500">$5.00</span>
                        </div>
                    </div>
                    
                    <button class="w-full py-3 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl transition-colors shadow-md shadow-orange-500/25">Book Comfort</button>
                </div>

                <!-- Premium -->
                <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                    <h3 class="text-xl font-bold text-gray-900 mb-1">Premium</h3>
                    <p class="text-sm text-gray-500 mb-8">Luxury vehicles & top-rated drivers</p>
                    
                    <div class="space-y-4 mb-8">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Base fare</span>
                            <span class="font-bold text-gray-900">$4.50</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Per km</span>
                            <span class="font-bold text-gray-900">$1.80</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Per minute</span>
                            <span class="font-bold text-gray-900">$0.25</span>
                        </div>
                        <div class="pt-4 mt-4 border-t border-gray-100 flex justify-between items-center text-sm">
                            <span class="text-gray-500">Minimum fare</span>
                            <span class="font-bold text-orange-500">$8.00</span>
                        </div>
                    </div>
                    
                    <button class="w-full py-3 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl transition-colors">Book Premium</button>
                </div>

            </div>

            <p class="mt-6 flex items-center gap-2 text-xs text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                A 15% platform fee is included in all displayed fares. Prices may vary by city.
            </p>
        </section>


        <!-- Section 2: Vehicle Rentals -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-24">
            <h2 class="text-3xl font-bold text-gray-900 mb-8">Vehicle Rentals — From (per day)</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- Economy Rental -->
                <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                    <h3 class="text-xl font-bold text-gray-900 mb-1">Economy</h3>
                    <p class="text-sm text-gray-500 mb-6">Toyota Corolla, Honda Civic</p>
                    
                    <div class="mb-8">
                        <span class="text-4xl font-extrabold text-orange-500">$35</span><span class="text-gray-500 font-medium">/day</span>
                    </div>

                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center gap-2 text-sm text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><polyline points="20 6 9 17 4 12"/></svg>
                            Unlimited mileage
                        </li>
                        <li class="flex items-center gap-2 text-sm text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><polyline points="20 6 9 17 4 12"/></svg>
                            Basic insurance
                        </li>
                        <li class="flex items-center gap-2 text-sm text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><polyline points="20 6 9 17 4 12"/></svg>
                            Free cancellation 24h
                        </li>
                    </ul>

                    <button class="w-full py-3 bg-white border border-orange-200 text-orange-500 hover:bg-orange-50 font-bold rounded-xl transition-colors">Browse Economy</button>
                </div>

                <!-- SUV Rental -->
                <div class="bg-white rounded-3xl p-8 border border-orange-100 shadow-lg shadow-orange-50">
                    <h3 class="text-xl font-bold text-gray-900 mb-1">SUV / Midsize</h3>
                    <p class="text-sm text-gray-500 mb-6">Toyota RAV4, Honda CR-V</p>
                    
                    <div class="mb-8">
                        <span class="text-4xl font-extrabold text-orange-500">$65</span><span class="text-gray-500 font-medium">/day</span>
                    </div>

                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center gap-2 text-sm text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><polyline points="20 6 9 17 4 12"/></svg>
                            Unlimited mileage
                        </li>
                        <li class="flex items-center gap-2 text-sm text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><polyline points="20 6 9 17 4 12"/></svg>
                            Full insurance
                        </li>
                        <li class="flex items-center gap-2 text-sm text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><polyline points="20 6 9 17 4 12"/></svg>
                            Free cancellation 24h
                        </li>
                        <li class="flex items-center gap-2 text-sm text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><polyline points="20 6 9 17 4 12"/></svg>
                            GPS included
                        </li>
                    </ul>

                    <button class="w-full py-3 bg-white border border-orange-200 text-orange-500 hover:bg-orange-50 font-bold rounded-xl transition-colors">Browse SUV / Midsize</button>
                </div>

                <!-- Luxury Rental -->
                <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                    <h3 class="text-xl font-bold text-gray-900 mb-1">Luxury</h3>
                    <p class="text-sm text-gray-500 mb-6">BMW 5 Series, Mercedes E-Class</p>
                    
                    <div class="mb-8">
                        <span class="text-4xl font-extrabold text-orange-500">$120</span><span class="text-gray-500 font-medium">/day</span>
                    </div>

                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center gap-2 text-sm text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><polyline points="20 6 9 17 4 12"/></svg>
                            Unlimited mileage
                        </li>
                        <li class="flex items-center gap-2 text-sm text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><polyline points="20 6 9 17 4 12"/></svg>
                            Premium insurance
                        </li>
                        <li class="flex items-center gap-2 text-sm text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><polyline points="20 6 9 17 4 12"/></svg>
                            Priority support
                        </li>
                        <li class="flex items-center gap-2 text-sm text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><polyline points="20 6 9 17 4 12"/></svg>
                            Doorstep delivery
                        </li>
                        <li class="flex items-center gap-2 text-sm text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><polyline points="20 6 9 17 4 12"/></svg>
                            GPS included
                        </li>
                    </ul>

                    <button class="w-full py-3 bg-white border border-orange-200 text-orange-500 hover:bg-orange-50 font-bold rounded-xl transition-colors">Browse Luxury</button>
                </div>
            </div>
        </section>


        <!-- Section 3: Driver Hire -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Professional Driver Hire</h2>
            <p class="text-gray-500 mb-8 max-w-2xl">Driver rates are set by each professional and vary based on experience, languages, and availability. Typical ranges:</p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-3xl p-8 text-center border border-gray-100 shadow-sm">
                    <h4 class="text-sm font-medium text-gray-500 mb-2">Half Day (4h)</h4>
                    <span class="text-3xl font-extrabold text-orange-500">$60 - $120</span>
                </div>
                <div class="bg-white rounded-3xl p-8 text-center border border-gray-100 shadow-sm">
                    <h4 class="text-sm font-medium text-gray-500 mb-2">Full Day (8h)</h4>
                    <span class="text-3xl font-extrabold text-orange-500">$100 - $200</span>
                </div>
                <div class="bg-white rounded-3xl p-8 text-center border border-gray-100 shadow-sm">
                    <h4 class="text-sm font-medium text-gray-500 mb-2">Weekly</h4>
                    <span class="text-3xl font-extrabold text-orange-500">$500 - $1,000</span>
                </div>
            </div>

            <button class="px-8 py-3 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl transition-colors shadow-md shadow-orange-500/25">Browse Drivers & Compare</button>

        </section>

    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                <div class="col-span-1 md:col-span-1">
                    <a class="flex items-center gap-2 mb-6" href="/">
                        <div class="w-10 h-10 rounded-xl bg-orange-500 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"></path><circle cx="7" cy="17" r="2"></circle><path d="M9 17h6"></path><circle cx="17" cy="17" r="2"></circle></svg>
                        </div>
                        <span class="font-bold text-2xl tracking-tight text-white">Ride<span class="text-orange-500">MyCars</span></span>
                    </a>
                    <p class="text-gray-400 text-sm leading-relaxed">
                        Your unified mobility platform. Book rides, rent vehicles, and hire professional drivers — all in one place.
                    </p>
                    <div class="flex gap-4 mt-6">
                        <!-- Social placeholders -->
                        <div class="w-8 h-8 rounded-full bg-gray-800 flex items-center justify-center text-gray-400 hover:text-white transition-colors cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"/></svg>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-gray-800 flex items-center justify-center text-gray-400 hover:text-white transition-colors cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-gray-800 flex items-center justify-center text-gray-400 hover:text-white transition-colors cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-gray-800 flex items-center justify-center text-gray-400 hover:text-white transition-colors cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/></svg>
                        </div>
                    </div>
                </div>
                <div>
                    <h4 class="font-bold text-xs tracking-widest text-gray-500 uppercase mb-6">Services</h4>
                    <ul class="space-y-4">
                        <li><a href="/ride" class="text-sm text-gray-400 hover:text-white transition-colors">Book a Ride</a></li>
                        <li><a href="/rent" class="text-sm text-gray-400 hover:text-white transition-colors">Rent a Vehicle</a></li>
                        <li><a href="/hire-driver" class="text-sm text-gray-400 hover:text-white transition-colors">Hire a Driver</a></li>
                        <li><a href="/pricing" class="text-sm text-gray-400 hover:text-white transition-colors">Pricing</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-xs tracking-widest text-gray-500 uppercase mb-6">Company</h4>
                    <ul class="space-y-4">
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">About Us</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">Safety</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">Blog</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">Careers</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-xs tracking-widest text-gray-500 uppercase mb-6">Partners</h4>
                    <ul class="space-y-4">
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">Become a Driver</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">List Your Vehicle</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">Partner Portal</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-xs tracking-widest text-gray-500 uppercase mb-6">Support</h4>
                    <ul class="space-y-4">
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">Help Center</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">Contact Us</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">FAQ</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">Support Tickets</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-xs tracking-widest text-gray-500 uppercase mb-6">Legal</h4>
                    <ul class="space-y-4">
                        <li><a href="/terms" class="text-sm text-gray-400 hover:text-white transition-colors">Terms of Service</a></li>
                        <li><a href="/privacy" class="text-sm text-gray-400 hover:text-white transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">Refund Policy</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">Cookie Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-gray-500 text-sm">© 2025 RideMyCars. All rights reserved.</p>
            </div>
        </div>
    </footer>

</body>
</html>
