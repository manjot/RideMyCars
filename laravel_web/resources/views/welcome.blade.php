<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RideMyCars — Ride, Rent, Hire</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .bg-dots {
            background-image: radial-gradient(circle, #e5e7eb 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>
    <!-- AlpineJS for Tab Functionality -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-white text-gray-900 bg-dots relative overflow-x-hidden">
    <div class="flex min-h-screen flex-col relative z-10">
        
        <!-- Header -->
        <header class="top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md sticky border-b border-gray-100">
            <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-20 items-center justify-between">
                    <!-- Logo -->
                    <a class="flex items-center gap-2 group" href="/">
                        <div class="w-10 h-10 rounded-xl bg-orange-500 flex items-center justify-center shadow-lg shadow-orange-500/30 group-hover:scale-105 transition-transform">
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
                        <a class="text-sm font-medium transition-colors text-gray-500 hover:text-gray-900" href="/pricing">Pricing</a>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex items-center gap-6">
                        <button class="text-gray-400 hover:text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                        </button>
                        <a class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors" href="/login">Sign In</a>
                        <a class="px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-xl transition-all shadow-md shadow-orange-500/25 active:scale-95" href="/signup">Get Started</a>
                    </div>
                </div>
            </nav>
        </header>

        <main class="flex-1 w-full flex items-center">
            <section class="relative w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20 flex flex-col lg:flex-row items-center gap-16">
                
                <!-- Left Content -->
                <div class="flex-1 w-full z-10" x-data="{ tab: 'ride' }">
                    <!-- Trust Badge -->
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-orange-50 border border-orange-200 mb-8 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-orange-500"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        <span class="text-sm font-medium text-orange-600">Trusted by 50,000+ riders worldwide</span>
                    </div>
                    
                    <!-- Headline -->
                    <h1 class="text-6xl lg:text-[5rem] font-bold tracking-tight leading-[1.1] mb-6 text-gray-900">
                        One App.<br/>
                        <span class="text-orange-500">Three Ways</span><br/>
                        to Move.
                    </h1>
                    
                    <p class="text-lg text-gray-500 max-w-xl mb-12 leading-relaxed">
                        Book a ride, rent a vehicle, or hire a professional driver — all from a single platform built for the modern traveler.
                    </p>
                    
                    <!-- Tabbed Card -->
                    <div class="bg-white border border-gray-100 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden mb-8 max-w-xl p-2 relative">
                        <!-- Tabs -->
                        <div class="flex relative mb-4">
                            <button @click="tab = 'ride'" :class="tab === 'ride' ? 'text-orange-500' : 'text-gray-400 hover:text-gray-600'" class="flex-1 py-4 text-sm font-semibold transition-all relative z-10">
                                Ride
                                <div x-show="tab === 'ride'" class="absolute bottom-0 left-4 right-4 h-0.5 bg-orange-500 rounded-full" x-transition></div>
                            </button>
                            <button @click="tab = 'rent'" :class="tab === 'rent' ? 'text-orange-500' : 'text-gray-400 hover:text-gray-600'" class="flex-1 py-4 text-sm font-semibold transition-all relative z-10">
                                Rent Vehicle
                                <div x-show="tab === 'rent'" class="absolute bottom-0 left-4 right-4 h-0.5 bg-orange-500 rounded-full" style="display:none;" x-transition></div>
                            </button>
                            <button @click="tab = 'hire'" :class="tab === 'hire' ? 'text-orange-500' : 'text-gray-400 hover:text-gray-600'" class="flex-1 py-4 text-sm font-semibold transition-all relative z-10">
                                Hire Driver
                                <div x-show="tab === 'hire'" class="absolute bottom-0 left-4 right-4 h-0.5 bg-orange-500 rounded-full" style="display:none;" x-transition></div>
                            </button>
                            <div class="absolute bottom-0 left-0 right-0 h-[1px] bg-gray-100"></div>
                        </div>

                        <!-- Tab Content / Input -->
                        <div class="px-2 pb-2">
                            <div class="flex gap-2">
                                <div class="flex-1 relative flex items-center bg-gray-50 rounded-xl border border-gray-100 focus-within:border-orange-500 focus-within:ring-2 focus-within:ring-orange-500/20 transition-all">
                                    <div class="pl-4 text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                    </div>
                                    <!-- Dynamic Placeholder -->
                                    <input type="text" x-bind:placeholder="tab === 'ride' ? 'Where to?' : (tab === 'rent' ? 'Pickup location?' : 'City or Zip code?')" class="w-full pl-3 pr-4 py-3.5 bg-transparent text-sm focus:outline-none text-gray-900 font-medium placeholder-gray-400" />
                                </div>
                                <button class="flex items-center gap-2 px-6 py-3.5 bg-orange-500 hover:bg-orange-600 text-white font-semibold text-sm rounded-xl transition-all shadow-md shadow-orange-500/20 active:scale-95 whitespace-nowrap">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                    <span x-text="tab === 'ride' ? 'Book Ride' : (tab === 'rent' ? 'Find Vehicle' : 'Find Driver')">Book Ride</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Trust indicators bottom -->
                    <div class="flex flex-wrap items-center gap-8 text-sm font-medium text-gray-500">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                            Fully Insured
                        </div>
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-yellow-500"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            Verified Drivers
                        </div>
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-500"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            24/7 Support
                        </div>
                    </div>
                </div>
                
                <!-- Right Content Graphic -->
                <div class="flex-1 relative w-full h-[600px] hidden lg:block">
                    <!-- Soft gradient background element -->
                    <div class="absolute inset-0 right-0 bg-gradient-to-tr from-orange-50 to-orange-100/50 rounded-[3rem] transform -rotate-3 scale-105 z-0"></div>
                    
                    <!-- Main Orange Square -->
                    <div class="absolute inset-4 bg-orange-600 rounded-[2.5rem] shadow-2xl shadow-orange-600/30 flex items-center justify-center z-10 overflow-hidden">
                        <!-- Abstract shapes inside -->
                        <div class="absolute top-0 right-0 w-64 h-64 bg-orange-500 rounded-full blur-3xl opacity-50 transform translate-x-1/2 -translate-y-1/2"></div>
                        <div class="absolute bottom-0 left-0 w-80 h-80 bg-orange-700 rounded-full blur-3xl opacity-30 transform -translate-x-1/2 translate-y-1/2"></div>
                        
                        <!-- Car Outline Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-32 h-32 text-white relative z-20">
                            <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"></path>
                            <circle cx="7" cy="17" r="2"></circle>
                            <path d="M9 17h6"></path>
                            <circle cx="17" cy="17" r="2"></circle>
                        </svg>
                    </div>

                    <!-- Floating Badges -->
                    <!-- Top Left Badge -->
                    <div class="absolute top-16 -left-8 bg-white p-4 rounded-2xl shadow-xl border border-gray-100 z-30 flex items-center gap-4 animate-[bounce_4s_ease-in-out_infinite]">
                        <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 font-medium">Driver ETA</div>
                            <div class="font-bold text-gray-900">3 mins away</div>
                        </div>
                    </div>

                    <!-- Middle Right Badge -->
                    <div class="absolute top-1/2 -right-8 transform -translate-y-1/2 bg-white p-4 rounded-2xl shadow-xl border border-gray-100 z-30 flex items-center gap-4 animate-[bounce_5s_ease-in-out_infinite]">
                        <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-500"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 font-medium">Online Drivers</div>
                            <div class="font-bold text-gray-900">128 nearby</div>
                        </div>
                    </div>

                    <!-- Bottom Right Badge -->
                    <div class="absolute bottom-16 right-4 bg-white p-4 rounded-2xl shadow-xl border border-gray-100 z-30 flex items-center gap-4 animate-[bounce_6s_ease-in-out_infinite]">
                        <div class="w-10 h-10 bg-yellow-50 rounded-xl flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-yellow-500"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 font-medium">Avg Rating</div>
                            <div class="font-bold text-gray-900">4.9 / 5.0</div>
                        </div>
                    </div>
                </div>

            </section>
        </main>
    </div>
</body>
</html>
