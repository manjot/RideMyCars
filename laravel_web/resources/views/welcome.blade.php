<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RideMyCars — Your Journey, Your Way</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white text-gray-900 min-h-screen flex flex-col">
    
    <!-- Header -->
    <header class="top-0 left-0 right-0 z-50 bg-white border-b border-gray-100 sticky">
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
                    <a class="text-sm font-medium transition-colors text-gray-500 hover:text-gray-900 flex items-center gap-1" href="#">Company <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg></a>
                    <a class="text-sm font-medium transition-colors text-gray-500 hover:text-gray-900" href="#">Pricing</a>
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
    <main class="flex-1">
        
        <!-- Hero Section -->
        <section class="relative pt-20 pb-24 lg:pt-32 lg:pb-32 overflow-hidden bg-[#fafafa]">
            <div class="absolute inset-0 bg-gradient-to-br from-orange-50 to-white -z-10"></div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
                <h1 class="text-5xl md:text-7xl font-extrabold text-gray-900 tracking-tight mb-6">
                    Move your way. <br/>
                    <span class="text-orange-500">Every single day.</span>
                </h1>
                <p class="text-lg md:text-xl text-gray-500 mb-10 max-w-2xl mx-auto">
                    Whether you need a quick ride across town, a vehicle for the weekend, or a professional driver for your event — we've got you covered.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="/ride" class="px-8 py-4 bg-gray-900 hover:bg-black text-white font-bold rounded-xl transition-all text-lg w-full sm:w-auto">
                        Book a Ride
                    </a>
                    <a href="/rent" class="px-8 py-4 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl transition-all shadow-lg shadow-orange-500/25 text-lg w-full sm:w-auto">
                        Rent a Vehicle
                    </a>
                </div>
            </div>
        </section>

        <!-- Stats Section (Screenshot 1) -->
        <section class="border-y border-gray-100 bg-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center divide-x divide-gray-100">
                    <div>
                        <div class="text-4xl font-black text-gray-900 mb-1">50K+</div>
                        <div class="text-sm font-medium text-gray-500">Happy Riders</div>
                    </div>
                    <div>
                        <div class="text-4xl font-black text-gray-900 mb-1">3.2K+</div>
                        <div class="text-sm font-medium text-gray-500">Verified Drivers</div>
                    </div>
                    <div>
                        <div class="text-4xl font-black text-gray-900 mb-1">1.5K+</div>
                        <div class="text-sm font-medium text-gray-500">Vehicles Listed</div>
                    </div>
                    <div>
                        <div class="text-4xl font-black text-gray-900 mb-1 flex items-center justify-center gap-1">4.9<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" stroke="none" class="text-gray-900"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></div>
                        <div class="text-sm font-medium text-gray-500">Average Rating</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section (Screenshot 2) -->
        <section class="py-24 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center mb-16">
                    <h3 class="text-orange-500 font-bold text-sm tracking-widest uppercase mb-3">Our Services</h3>
                    <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4 tracking-tight">Everything you need to move</h2>
                    <p class="text-lg text-gray-500 max-w-2xl mx-auto">Three marketplace modules, one seamless platform. Choose how you want to travel.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Ride Hailing -->
                    <div class="rounded-3xl border border-gray-100 p-8 hover:shadow-xl transition-shadow bg-white relative overflow-hidden group">
                        <div class="absolute top-6 right-6 bg-orange-50 text-orange-600 text-xs font-bold px-3 py-1 rounded-full">Most Popular</div>
                        <div class="w-14 h-14 bg-blue-50 text-blue-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Ride Hailing</h3>
                        <p class="text-gray-500 mb-8 leading-relaxed">Book instant rides with verified drivers. Real-time tracking, fare estimates, and cashless payments. Your safety is our priority.</p>
                        
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center gap-2 text-sm text-gray-600 font-medium">
                                <div class="w-1.5 h-1.5 rounded-full bg-orange-500"></div> Instant booking
                            </li>
                            <li class="flex items-center gap-2 text-sm text-gray-600 font-medium">
                                <div class="w-1.5 h-1.5 rounded-full bg-orange-500"></div> Live GPS tracking
                            </li>
                            <li class="flex items-center gap-2 text-sm text-gray-600 font-medium">
                                <div class="w-1.5 h-1.5 rounded-full bg-orange-500"></div> Fare estimate upfront
                            </li>
                            <li class="flex items-center gap-2 text-sm text-gray-600 font-medium">
                                <div class="w-1.5 h-1.5 rounded-full bg-orange-500"></div> Rated drivers
                            </li>
                        </ul>

                        <a href="/ride" class="inline-flex items-center gap-2 text-orange-500 font-bold hover:text-orange-600 transition-colors">
                            Explore Ride Hailing <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>

                    <!-- Vehicle Rentals -->
                    <div class="rounded-3xl border border-orange-200 p-8 shadow-xl shadow-orange-100/50 bg-white relative overflow-hidden group transform md:-translate-y-4">
                        <div class="absolute top-6 right-6 bg-orange-100 text-orange-600 text-xs font-bold px-3 py-1 rounded-full">Best Value</div>
                        <div class="w-14 h-14 bg-orange-50 text-orange-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15.5 7.5 2.3 2.3a1 1 0 0 0 1.4 0l2.1-2.1a1 1 0 0 0 0-1.4L19 4"/><path d="m21 2-9.6 9.6"/><circle cx="7.5" cy="15.5" r="5.5"/></svg>
                        </div>
                        <h3 class="text-2xl font-bold text-orange-500 mb-3">Vehicle Rentals</h3>
                        <p class="text-gray-500 mb-8 leading-relaxed">Rent from a curated fleet of economy, luxury, and specialty vehicles. Daily or weekly rates with full insurance coverage.</p>
                        
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center gap-2 text-sm text-gray-600 font-medium">
                                <div class="w-1.5 h-1.5 rounded-full bg-orange-500"></div> 1000+ vehicles
                            </li>
                            <li class="flex items-center gap-2 text-sm text-gray-600 font-medium">
                                <div class="w-1.5 h-1.5 rounded-full bg-orange-500"></div> Flexible rental periods
                            </li>
                            <li class="flex items-center gap-2 text-sm text-gray-600 font-medium">
                                <div class="w-1.5 h-1.5 rounded-full bg-orange-500"></div> Doorstep delivery
                            </li>
                            <li class="flex items-center gap-2 text-sm text-gray-600 font-medium">
                                <div class="w-1.5 h-1.5 rounded-full bg-orange-500"></div> Full insurance
                            </li>
                        </ul>

                        <a href="/rent" class="inline-flex items-center gap-2 text-orange-500 font-bold hover:text-orange-600 transition-colors">
                            Explore Vehicle Rentals <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>

                    <!-- Hire a Driver -->
                    <div class="rounded-3xl border border-gray-100 p-8 hover:shadow-xl transition-shadow bg-white relative overflow-hidden group">
                        <div class="absolute top-6 right-6 bg-purple-50 text-purple-600 text-xs font-bold px-3 py-1 rounded-full">Premium</div>
                        <div class="w-14 h-14 bg-purple-50 text-purple-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Hire a Driver</h3>
                        <p class="text-gray-500 mb-8 leading-relaxed">Professional, background-verified drivers for a day, week, or longer. Perfect for business travel, weddings, and special occasions.</p>
                        
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center gap-2 text-sm text-gray-600 font-medium">
                                <div class="w-1.5 h-1.5 rounded-full bg-orange-500"></div> Background verified
                            </li>
                            <li class="flex items-center gap-2 text-sm text-gray-600 font-medium">
                                <div class="w-1.5 h-1.5 rounded-full bg-orange-500"></div> Experienced & multilingual
                            </li>
                            <li class="flex items-center gap-2 text-sm text-gray-600 font-medium">
                                <div class="w-1.5 h-1.5 rounded-full bg-orange-500"></div> Flexible hire periods
                            </li>
                            <li class="flex items-center gap-2 text-sm text-gray-600 font-medium">
                                <div class="w-1.5 h-1.5 rounded-full bg-orange-500"></div> Corporate packages
                            </li>
                        </ul>

                        <a href="/hire-driver" class="inline-flex items-center gap-2 text-orange-500 font-bold hover:text-orange-600 transition-colors">
                            Explore Hire a Driver <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>

            </div>
        </section>

        <!-- How it Works (Screenshot 3) -->
        <section class="py-24 bg-white border-t border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center mb-20">
                    <h3 class="text-orange-500 font-bold text-sm tracking-widest uppercase mb-3">How It Works</h3>
                    <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4 tracking-tight">Ready in four steps</h2>
                    <p class="text-lg text-gray-500 max-w-2xl mx-auto">From search to destination, we've made every step simple and transparent.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-12 relative">
                    <!-- Line connecting steps (desktop) -->
                    <div class="hidden md:block absolute top-12 left-1/8 right-1/8 h-px bg-gray-200 w-3/4 mx-auto z-0"></div>

                    <!-- Step 1 -->
                    <div class="text-center relative z-10">
                        <div class="w-24 h-24 mx-auto bg-blue-50 rounded-3xl flex items-center justify-center mb-6 relative">
                            <div class="absolute -top-3 -right-3 w-8 h-8 bg-orange-500 text-white font-bold rounded-full flex items-center justify-center border-4 border-white shadow-sm">1</div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-500"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 mb-3">Search & Choose</h4>
                        <p class="text-gray-500 text-sm leading-relaxed">Search for rides, vehicles, or drivers. Filter by price, rating, distance, and availability.</p>
                    </div>

                    <!-- Step 2 -->
                    <div class="text-center relative z-10">
                        <div class="w-24 h-24 mx-auto bg-orange-50 rounded-3xl flex items-center justify-center mb-6 relative">
                            <div class="absolute -top-3 -right-3 w-8 h-8 bg-orange-500 text-white font-bold rounded-full flex items-center justify-center border-4 border-white shadow-sm">2</div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-orange-500"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 mb-3">Book & Pay</h4>
                        <p class="text-gray-500 text-sm leading-relaxed">Instant booking with secure payments via card or wallet. Get a full receipt and invoice.</p>
                    </div>

                    <!-- Step 3 -->
                    <div class="text-center relative z-10">
                        <div class="w-24 h-24 mx-auto bg-green-50 rounded-3xl flex items-center justify-center mb-6 relative">
                            <div class="absolute -top-3 -right-3 w-8 h-8 bg-orange-500 text-white font-bold rounded-full flex items-center justify-center border-4 border-white shadow-sm">3</div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 mb-3">Track in Real Time</h4>
                        <p class="text-gray-500 text-sm leading-relaxed">Watch your driver arrive on a live map. Get SMS and push updates at every step.</p>
                    </div>

                    <!-- Step 4 -->
                    <div class="text-center relative z-10">
                        <div class="w-24 h-24 mx-auto bg-purple-50 rounded-3xl flex items-center justify-center mb-6 relative">
                            <div class="absolute -top-3 -right-3 w-8 h-8 bg-orange-500 text-white font-bold rounded-full flex items-center justify-center border-4 border-white shadow-sm">4</div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-purple-500"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 mb-3">Rate Your Experience</h4>
                        <p class="text-gray-500 text-sm leading-relaxed">Leave a review after your trip. Your feedback keeps our community safe and high quality.</p>
                    </div>
                </div>

            </div>
        </section>

        <!-- Why RideMyCars (Screenshot 4) -->
        <section class="py-24 bg-white border-t border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center mb-16">
                    <h3 class="text-orange-500 font-bold text-sm tracking-widest uppercase mb-3">Why RideMyCars</h3>
                    <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4 tracking-tight">Built different. For good reason.</h2>
                    <p class="text-lg text-gray-500 max-w-2xl mx-auto">We combined three separate markets into one platform and raised the bar on every dimension.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    
                    <div class="border border-gray-100 rounded-3xl p-8 hover:border-gray-200 transition-colors">
                        <div class="w-12 h-12 bg-green-50 text-green-500 rounded-2xl flex items-center justify-center mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 mb-3">Safety First</h4>
                        <p class="text-gray-500 text-sm leading-relaxed">Every driver is background-verified. Vehicles are insured. Your safety is our foundation.</p>
                    </div>

                    <div class="border border-orange-200 rounded-3xl p-8 bg-white shadow-xl shadow-orange-50/50">
                        <div class="w-12 h-12 bg-yellow-50 text-yellow-500 rounded-2xl flex items-center justify-center mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 mb-3">Lightning Fast</h4>
                        <p class="text-gray-500 text-sm leading-relaxed">Average driver assignment in under 2 minutes. Real-time matching powered by smart algorithms.</p>
                    </div>

                    <div class="border border-gray-100 rounded-3xl p-8 hover:border-gray-200 transition-colors">
                        <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-2xl flex items-center justify-center mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 mb-3">24/7 Support</h4>
                        <p class="text-gray-500 text-sm leading-relaxed">Our support team is always on. Chat, email, or call — we respond in minutes, not days.</p>
                    </div>

                    <div class="border border-gray-100 rounded-3xl p-8 hover:border-gray-200 transition-colors">
                        <div class="w-12 h-12 bg-orange-50 text-orange-500 rounded-2xl flex items-center justify-center mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 mb-3">Transparent Pricing</h4>
                        <p class="text-gray-500 text-sm leading-relaxed">No hidden fees. See your full fare before you book. Pay via card, wallet, or cash.</p>
                    </div>

                    <div class="border border-gray-100 rounded-3xl p-8 hover:border-gray-200 transition-colors">
                        <div class="w-12 h-12 bg-purple-50 text-purple-500 rounded-2xl flex items-center justify-center mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 mb-3">Live Tracking</h4>
                        <p class="text-gray-500 text-sm leading-relaxed">Track your driver or vehicle in real time on Google Maps. Share your trip for extra safety.</p>
                    </div>

                    <div class="border border-gray-100 rounded-3xl p-8 hover:border-gray-200 transition-colors">
                        <div class="w-12 h-12 bg-pink-50 text-pink-500 rounded-2xl flex items-center justify-center mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 mb-3">Trusted Community</h4>
                        <p class="text-gray-500 text-sm leading-relaxed">50,000+ reviews from real riders. Our dual rating system keeps quality high on both sides.</p>
                    </div>

                </div>

            </div>
        </section>

        <!-- Platform Features (Screenshot 5) -->
        <section class="py-24 bg-gray-50 border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center mb-16">
                    <h3 class="text-orange-500 font-bold text-sm tracking-widest uppercase mb-3">Platform Features</h3>
                    <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4 tracking-tight">Everything built in</h2>
                    <p class="text-lg text-gray-500 max-w-2xl mx-auto">No add-ons needed. Every feature you need comes standard.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    
                    <!-- Row 1 -->
                    <div class="bg-white border border-gray-100 rounded-2xl p-6 hover:shadow-md transition-shadow">
                        <div class="w-10 h-10 bg-orange-50 text-orange-500 rounded-xl flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Smart Wallet</h4>
                        <p class="text-gray-500 text-sm leading-relaxed">Top up once, pay for everything. Instant refunds. Full transaction history.</p>
                    </div>

                    <div class="bg-white border border-gray-100 rounded-2xl p-6 hover:shadow-md transition-shadow">
                        <div class="w-10 h-10 bg-orange-50 text-orange-500 rounded-xl flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Real-time Notifications</h4>
                        <p class="text-gray-500 text-sm leading-relaxed">Email, SMS, and in-app alerts at every step of your booking journey.</p>
                    </div>

                    <div class="bg-white border border-gray-100 rounded-2xl p-6 hover:shadow-md transition-shadow">
                        <div class="w-10 h-10 bg-orange-50 text-orange-500 rounded-xl flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Instant Invoices</h4>
                        <p class="text-gray-500 text-sm leading-relaxed">Download detailed PDF invoices for every booking. Perfect for expense reports.</p>
                    </div>

                    <div class="bg-white border border-gray-100 rounded-2xl p-6 hover:shadow-md transition-shadow">
                        <div class="w-10 h-10 bg-orange-50 text-orange-500 rounded-xl flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l8.29-8.29c.94-.94.94-2.48 0-3.42L12 2Z"/><path d="M7 7h.01"/></svg>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Coupons & Referrals</h4>
                        <p class="text-gray-500 text-sm leading-relaxed">Share your code, earn credits. Use coupons to save on every booking.</p>
                    </div>

                    <!-- Row 2 -->
                    <div class="bg-white border border-gray-100 rounded-2xl p-6 hover:shadow-md transition-shadow">
                        <div class="w-10 h-10 bg-orange-50 text-orange-500 rounded-xl flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Saved Locations</h4>
                        <p class="text-gray-500 text-sm leading-relaxed">Save home, work, and frequent destinations. Book in one tap.</p>
                    </div>

                    <div class="bg-white border border-gray-100 rounded-2xl p-6 hover:shadow-md transition-shadow">
                        <div class="w-10 h-10 bg-orange-50 text-orange-500 rounded-xl flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Ratings & Reviews</h4>
                        <p class="text-gray-500 text-sm leading-relaxed">Two-way reviews build trust. Only book highly-rated drivers and vehicles.</p>
                    </div>

                    <div class="bg-white border-2 border-orange-200 rounded-2xl p-6 shadow-lg shadow-orange-50">
                        <div class="w-10 h-10 bg-orange-500 text-white rounded-xl flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Identity Verification</h4>
                        <p class="text-gray-500 text-sm leading-relaxed">Verified profiles mean safer trips. We verify drivers, owners, and renters.</p>
                    </div>

                    <div class="bg-white border border-gray-100 rounded-2xl p-6 hover:shadow-md transition-shadow">
                        <div class="w-10 h-10 bg-orange-50 text-orange-500 rounded-xl flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Easy Refunds</h4>
                        <p class="text-gray-500 text-sm leading-relaxed">Automatic refunds for cancellations. Wallet credits appear instantly.</p>
                    </div>

                </div>

            </div>
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
                        The ultimate mobility platform. Ride, rent, or hire a driver with just a few taps.
                    </p>
                </div>
                <div>
                    <h4 class="font-bold text-lg mb-6">Services</h4>
                    <ul class="space-y-4">
                        <li><a href="/ride" class="text-gray-400 hover:text-white transition-colors">Ride Hailing</a></li>
                        <li><a href="/rent" class="text-gray-400 hover:text-white transition-colors">Vehicle Rentals</a></li>
                        <li><a href="/hire-driver" class="text-gray-400 hover:text-white transition-colors">Hire a Driver</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-lg mb-6">Company</h4>
                    <ul class="space-y-4">
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">About Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Careers</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Blog</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Press</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-lg mb-6">Legal</h4>
                    <ul class="space-y-4">
                        <li><a href="/terms" class="text-gray-400 hover:text-white transition-colors">Terms of Service</a></li>
                        <li><a href="/privacy" class="text-gray-400 hover:text-white transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Cookie Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-gray-500 text-sm">© 2025 RideMyCars. All rights reserved.</p>
                <div class="flex gap-4">
                    <a href="#" class="text-gray-500 hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                    </a>
                    <a href="#" class="text-gray-500 hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"/></svg>
                    </a>
                    <a href="#" class="text-gray-500 hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
