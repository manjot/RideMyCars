<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Sign Up' }} — RideMyCars</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <!-- Alpine.js & Country Dataset -->
    <script src="{{ asset('js/countries-data.js') }}"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white dark:bg-[#111] text-gray-900 dark:text-white overflow-x-hidden">
    <div class="flex min-h-screen" x-data="signupApp()">
        
        <!-- Left Banner (Dark) -->
        <div class="hidden lg:flex w-[45%] bg-[#1a1a1a] flex-col justify-between p-12 fixed h-screen left-0 top-0 overflow-hidden">
            <!-- Logo -->
            <a class="flex items-center gap-2 group z-10" href="/">
                <img src="{{ asset('images/logo.png') }}" alt="RideMyCars Logo" class="h-14 w-auto object-contain">
            </a>

            <!-- Text Content -->
            <div class="z-10 max-w-lg mt-20">
                <h1 class="text-5xl font-bold text-white mb-6 leading-tight">
                    Ride. Rent. Hire.<br/>
                    All in one place.
                </h1>
                <p class="text-gray-400 dark:text-gray-500 text-lg leading-relaxed">
                    Join 50,000+ riders, drivers, and vehicle owners on the platform that combines everything you need to move.
                </p>
            </div>

            <!-- Stats Bottom -->
            <div class="flex gap-4 z-10 mt-20">
                <div class="bg-[#242424] rounded-2xl p-6 flex-1 text-center border border-gray-800">
                    <div class="text-3xl font-bold text-brand-500 mb-1">50K+</div>
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Riders</div>
                </div>
                <div class="bg-[#242424] rounded-2xl p-6 flex-1 text-center border border-gray-800">
                    <div class="text-3xl font-bold text-brand-500 mb-1">3.2K+</div>
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Drivers</div>
                </div>
                <div class="bg-[#242424] rounded-2xl p-6 flex-1 text-center border border-gray-800">
                    <div class="text-3xl font-bold text-brand-500 mb-1 flex items-center justify-center gap-1">4.9<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" stroke="none" class="text-brand-500"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg></div>
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Rating</div>
                </div>
            </div>
        </div>

        <!-- Right Form (White) -->
        <div class="w-full lg:w-[55%] lg:ml-[45%] flex items-center justify-center p-8 lg:py-16 lg:px-24">
            <div class="w-full max-w-lg">
                <div class="mb-8">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-2 tracking-tight" x-text="currentRole === 'driver' ? 'Become a Driver' : (currentRole === 'owner' ? 'List Your Vehicle' : 'Create your account')"></h2>
                    <p class="text-gray-500 dark:text-gray-400 text-lg" x-text="currentRole === 'driver' ? 'Start earning by driving with RideMyCars.' : (currentRole === 'owner' ? 'Earn passive income by renting out your car.' : 'Join RideMyCars — it\'s free forever.')"></p>
                </div>

                @if(request('phone'))
                    <div class="mb-6 p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-700/50 text-amber-900 dark:text-amber-200 text-sm shadow-sm flex items-start gap-3">
                        <span class="text-2xl shrink-0">📝</span>
                        <div>
                            <p class="font-bold text-gray-900 dark:text-white">Complete Your Registration</p>
                            <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5">
                                No existing account was found for <span class="font-bold underline text-black dark:text-white">{{ request('phone') }}</span>. Please complete your registration below to get started.
                            </p>
                        </div>
                    </div>
                @endif

                <form action="/signup" method="POST" enctype="multipart/form-data" @submit.prevent="initiateSignup($event)" class="space-y-5" x-ref="signupForm">
                    @csrf
                    <input type="hidden" name="role" :value="currentRole">
                    
                    <!-- Driver Section Header -->
                    <div x-show="currentRole === 'driver'" class="pb-2 border-b border-gray-200 dark:border-white/10 mb-1">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">👨‍✈️</span>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Driver Credentials & Rates</h3>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">First Name</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                </div>
                                <input type="text" name="first_name" required value="{{ old('first_name') }}" placeholder="Alex" class="w-full pl-12 pr-4 py-3.5 bg-gray-50/50 border @error('first_name') border-red-500 @else border-gray-200 dark:border-white/10 @enderror rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                            </div>
                            @error('first_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Last Name</label>
                            <input type="text" name="last_name" required value="{{ old('last_name') }}" placeholder="Rivera" class="w-full px-4 py-3.5 bg-gray-50/50 border @error('last_name') border-red-500 @else border-gray-200 dark:border-white/10 @enderror rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                            @error('last_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                            </div>
                            <input type="email" name="email" required value="{{ old('email') }}" placeholder="you@example.com" class="w-full pl-12 pr-4 py-3.5 bg-gray-50/50 border @error('email') border-red-500 @else border-gray-200 dark:border-white/10 @enderror rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                        </div>
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Mobile Phone <span class="text-brand-500 font-bold">*</span> <span class="text-gray-400 dark:text-gray-500 font-normal text-xs">(Twilio SMS OTP verification)</span></label>
                        <div class="relative" @click.away="countryDropdownOpen = false">
                            <div class="flex items-center h-[52px] bg-gray-50/50 dark:bg-white/5 rounded-xl border border-gray-200 dark:border-white/10 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 transition-all overflow-hidden shadow-sm">
                                <!-- Country Dropdown Trigger with Flag -->
                                <button type="button" 
                                        @click.prevent.stop="toggleCountryDropdown()" 
                                        class="flex items-center gap-2 h-full px-3.5 bg-gray-100/80 dark:bg-white/5 hover:bg-gray-200/80 dark:hover:bg-white/10 border-r border-gray-200 dark:border-white/10 transition-colors shrink-0 cursor-pointer select-none">
                                    <img :src="selectedCountry.flagUrl || `https://flagcdn.com/w40/${(selectedCountry.code || 'us').toLowerCase()}.png`" 
                                         :alt="selectedCountry.name"
                                         class="w-6 h-4 object-cover rounded-sm shadow-sm border border-black/10 dark:border-white/15 shrink-0">
                                    <span class="text-sm font-bold text-gray-900 dark:text-white" x-text="selectedCountry.dial">+1</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500 dark:text-gray-400 transition-transform duration-200 shrink-0" :class="countryDropdownOpen ? 'rotate-180 text-black dark:text-white' : ''" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>

                                <!-- Mobile Input -->
                                <input type="tel" 
                                       x-model="mobileNumber"
                                       @input="updatePhone()"
                                       placeholder="Mobile number" 
                                       required
                                       class="flex-1 h-full bg-transparent px-4 text-gray-900 dark:text-white placeholder-gray-400 font-medium focus:outline-none border-none text-base">
                                
                                <div class="pr-3 text-brand-500" title="Verified via Twilio SMS OTP">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                </div>
                            </div>

                            <input type="hidden" name="phone" :value="phone">

                            <!-- Country Dropdown Floating Card -->
                            <div x-show="countryDropdownOpen" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 translate-y-2 scale-98"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave-end="opacity-0 translate-y-2 scale-98"
                                 class="absolute left-0 right-0 top-full mt-2 bg-white dark:bg-[#181818] rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.18)] border border-gray-200 dark:border-white/15 z-50 overflow-hidden ring-1 ring-black/5"
                                 style="display: none;">
                                
                                <!-- Search Bar Header -->
                                <div class="p-3 bg-gray-50/90 dark:bg-white/5 border-b border-gray-100 dark:border-white/10 sticky top-0 z-10 backdrop-blur-sm">
                                    <div class="relative flex items-center bg-white dark:bg-[#121212] rounded-xl border border-gray-200 dark:border-white/10 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20 transition-all shadow-inner px-3 py-2">
                                        <svg class="w-4 h-4 text-gray-400 shrink-0 mr-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                        <input type="text" 
                                               x-ref="countrySearchInput"
                                               x-model="countrySearch" 
                                               placeholder="Search country or code (e.g. +1, USA)..." 
                                               class="w-full bg-transparent text-xs font-semibold text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none border-none p-0">
                                        <button type="button" 
                                                x-show="countrySearch" 
                                                @click.prevent.stop="countrySearch = ''; $refs.countrySearchInput?.focus({ preventScroll: true })" 
                                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 shrink-0 ml-1 p-0.5"
                                                style="display: none;">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Country List -->
                                <div class="max-h-52 sm:max-h-60 overflow-y-auto country-scroll p-1.5 space-y-0.5 text-sm">
                                    <template x-for="country in filteredCountries" :key="country.code + country.dial">
                                        <button type="button" 
                                                @click="selectCountry(country)"
                                                class="w-full px-3 py-2 rounded-xl flex items-center justify-between hover:bg-gray-100/90 dark:hover:bg-white/10 active:bg-gray-200/80 transition-all text-left group cursor-pointer"
                                                :class="selectedCountry.code === country.code ? 'bg-brand-500 text-white hover:bg-brand-600' : 'text-gray-800 dark:text-gray-200'">
                                            <div class="flex items-center gap-3 min-w-0 pr-2">
                                                <img :src="country.flagUrl || `https://flagcdn.com/w40/${(country.code || 'us').toLowerCase()}.png`" 
                                                     :alt="country.name" 
                                                     loading="lazy"
                                                     class="w-6 h-4 object-cover rounded-sm shadow-sm shrink-0 border border-black/10 dark:border-white/15">
                                                <span class="text-xs font-semibold truncate" 
                                                      :class="selectedCountry.code === country.code ? 'text-white' : 'text-gray-900 dark:text-gray-100 group-hover:text-black dark:group-hover:text-white'" 
                                                      x-text="country.name"></span>
                                            </div>
                                            <div class="flex items-center gap-2 shrink-0">
                                                <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-md" 
                                                      :class="selectedCountry.code === country.code ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-gray-400 group-hover:text-black dark:group-hover:text-white group-hover:bg-gray-200'" 
                                                      x-text="country.dial"></span>
                                                <span x-show="selectedCountry.code === country.code" class="text-xs font-bold text-white">✓</span>
                                            </div>
                                        </button>
                                    </template>
                                    
                                    <div x-show="filteredCountries.length === 0" class="py-8 text-center" style="display: none;">
                                        <div class="text-2xl mb-1">🌍</div>
                                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">No countries found</p>
                                        <p class="text-[11px] text-gray-400 mt-0.5">Try searching with a different name or dial code</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Password</label>
                        <div class="relative" x-data="{ show: false }">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </div>
                            <input :type="show ? 'text' : 'password'" name="password" required placeholder="Min. 8 chars" class="w-full pl-12 pr-12 py-3.5 bg-gray-50/50 border @error('password') border-red-500 @else border-gray-200 dark:border-white/10 @enderror rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                            <div @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 dark:text-gray-500 cursor-pointer hover:text-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </div>
                        </div>
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Confirm Password</label>
                        <div class="relative" x-data="{ show: false }">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </div>
                            <input :type="show ? 'text' : 'password'" name="password_confirmation" required placeholder="Repeat password" class="w-full pl-12 pr-12 py-3.5 bg-gray-50/50 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Referral Code <span class="text-gray-400 dark:text-gray-500 font-normal">(optional)</span></label>
                        <input type="text" name="referral_code" value="{{ old('referral_code') }}" placeholder="Enter referral code" class="w-full px-4 py-3.5 bg-gray-50/50 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                    </div>

                    <!-- Driver Credentials & Rates Section -->
                    <div x-show="currentRole === 'driver'" class="pt-4 border-t border-gray-200 dark:border-white/10 space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Formal Profile Photo <span class="text-xs text-amber-600 font-normal">(Suit, Long-sleeve shirt, or Tie required)</span></label>
                            <input type="file" name="driver_photo" accept="image/*" class="w-full px-4 py-2 bg-gray-50/50 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-500 file:text-white">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">License Number</label>
                                <input type="text" name="license_number" :required="currentRole === 'driver'" value="{{ old('license_number') }}" placeholder="e.g. DL-99887766" class="w-full px-4 py-3.5 bg-gray-50/50 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">License Expiry Date</label>
                                <input type="date" name="license_expiry" :required="currentRole === 'driver'" value="{{ old('license_expiry') }}" class="w-full px-4 py-3.5 bg-gray-50/50 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">License Front Image</label>
                                <input type="file" name="license_front_image" accept="image/*" class="w-full px-3 py-2 bg-gray-50/50 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white text-xs file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-gray-700 file:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">License Back Image</label>
                                <input type="file" name="license_back_image" accept="image/*" class="w-full px-3 py-2 bg-gray-50/50 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white text-xs file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-gray-700 file:text-white">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div x-data="{
                                open: false,
                                search: '',
                                selected: (window.WORLD_COUNTRIES && window.WORLD_COUNTRIES[0]) || { name: 'United States', code: 'US', flagUrl: 'https://flagcdn.com/w40/us.png' },
                                get list() {
                                    const all = window.WORLD_COUNTRIES || [];
                                    if (!this.search) return all;
                                    const q = this.search.toLowerCase().trim();
                                    return all.filter(c => c.name.toLowerCase().includes(q) || c.code.toLowerCase().includes(q));
                                }
                            }" class="relative">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Country</label>
                                <input type="hidden" name="country" :value="selected.name">
                                
                                <button type="button" @click="open = !open; if(open) $nextTick(() => $refs.signupCountrySearch?.focus({ preventScroll: true }))"
                                        class="w-full flex items-center justify-between px-3.5 py-3 bg-gray-50/50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white transition-all text-left">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <img :src="selected.flagUrl || `https://flagcdn.com/w40/${(selected.code || 'us').toLowerCase()}.png`" 
                                             :alt="selected.name" 
                                             class="w-5 h-3.5 object-cover rounded-sm shadow-sm border border-black/10 dark:border-white/15 shrink-0">
                                        <span class="text-sm font-medium truncate" x-text="selected.name">United States</span>
                                    </div>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                
                                <div x-show="open" @click.away="open = false" style="display: none;"
                                     class="absolute left-0 right-0 top-full mt-1.5 bg-white dark:bg-[#1a1a1a] rounded-xl shadow-2xl border border-gray-200 dark:border-white/10 z-50 overflow-hidden">
                                    <div class="p-2 border-b border-gray-100 dark:border-white/10 sticky top-0 bg-gray-50 dark:bg-[#181818]">
                                        <input type="text" x-ref="signupCountrySearch" x-model="search" placeholder="Search country..."
                                               class="w-full px-3 py-1.5 bg-white dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-lg text-xs font-semibold text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:border-brand-500">
                                    </div>
                                    <div class="max-h-56 overflow-y-auto p-1 text-sm space-y-0.5">
                                        <template x-for="c in list" :key="c.code">
                                            <button type="button" @click="selected = c; open = false; search = ''"
                                                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-left text-xs transition-colors hover:bg-gray-100 dark:hover:bg-white/10 cursor-pointer"
                                                    :class="selected.code === c.code ? 'bg-brand-500 text-white font-bold hover:bg-brand-600' : 'text-gray-800 dark:text-gray-200'">
                                                <div class="flex items-center gap-2.5 min-w-0">
                                                    <img :src="c.flagUrl || `https://flagcdn.com/w40/${(c.code || 'us').toLowerCase()}.png`" 
                                                         :alt="c.name" 
                                                         loading="lazy"
                                                         class="w-5 h-3.5 object-cover rounded-sm shadow-sm border border-black/10 shrink-0">
                                                    <span class="truncate" x-text="c.name"></span>
                                                </div>
                                                <span class="font-mono text-[10px] opacity-70 shrink-0" x-text="c.code"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Driving Experience (Years)</label>
                                <input type="number" name="experience_years" :required="currentRole === 'driver'" value="{{ old('experience_years', 5) }}" min="1" placeholder="5" class="w-full px-4 py-3.5 bg-gray-50/50 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Hourly Rate ($)</label>
                                <input type="number" step="0.01" name="hourly_rate" :required="currentRole === 'driver'" value="{{ old('hourly_rate', 25.00) }}" placeholder="25.00" class="w-full px-3 py-3 bg-gray-50/50 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Daily Rate ($)</label>
                                <input type="number" step="0.01" name="daily_rate" :required="currentRole === 'driver'" value="{{ old('daily_rate', 170.00) }}" placeholder="170.00" class="w-full px-3 py-3 bg-gray-50/50 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Weekly Rate ($)</label>
                                <input type="number" step="0.01" name="weekly_rate" :required="currentRole === 'driver'" value="{{ old('weekly_rate', 950.00) }}" placeholder="950.00" class="w-full px-3 py-3 bg-gray-50/50 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Short Bio / Chauffeur Overview <span class="text-gray-400 font-normal">(optional)</span></label>
                            <textarea name="bio" rows="2" placeholder="Tell clients about your driving background and experience..." class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all"></textarea>
                        </div>
                    </div>

                    <!-- Vehicle Owner Section -->
                    <div x-show="currentRole === 'owner'" class="pt-6 border-t border-gray-200 dark:border-white/10 space-y-4">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xl">🏎️</span>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Vehicle Listing Details</h3>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Vehicle Make</label>
                                <input type="text" name="vehicle_make" :required="currentRole === 'owner'" value="{{ old('vehicle_make') }}" placeholder="e.g. Mercedes-Benz, Toyota, Tesla" class="w-full px-4 py-3.5 bg-gray-50/50 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Vehicle Model</label>
                                <input type="text" name="vehicle_model" :required="currentRole === 'owner'" value="{{ old('vehicle_model') }}" placeholder="e.g. S-Class, Camry, Model 3" class="w-full px-4 py-3.5 bg-gray-50/50 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Manufacturing Year</label>
                                <input type="number" name="vehicle_year" :required="currentRole === 'owner'" value="{{ old('vehicle_year', date('Y')) }}" min="2000" max="{{ date('Y') + 1 }}" placeholder="2024" class="w-full px-4 py-3.5 bg-gray-50/50 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">License Plate Number</label>
                                <input type="text" name="license_plate" :required="currentRole === 'owner'" value="{{ old('license_plate') }}" placeholder="e.g. REG-9876" class="w-full px-4 py-3.5 bg-gray-50/50 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Vehicle Category / Type</label>
                                <select name="vehicle_type" :required="currentRole === 'owner'" class="w-full px-4 py-3.5 bg-gray-50/50 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                                    <option value="Executive Sedan">Executive Sedan</option>
                                    <option value="Luxury SUV">Luxury SUV</option>
                                    <option value="Sports Car">Sports Car</option>
                                    <option value="Economy Compact">Economy Compact</option>
                                    <option value="Electric Vehicle (EV)">Electric Vehicle (EV)</option>
                                    <option value="Van / Minivan">Van / Minivan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Daily Rental Rate ($)</label>
                                <input type="number" step="0.01" name="daily_rate" :required="currentRole === 'owner'" value="{{ old('daily_rate', 150.00) }}" placeholder="150.00" class="w-full px-4 py-3.5 bg-gray-50/50 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Vehicle Photo <span class="text-gray-400 font-normal">(optional)</span></label>
                            <input type="file" name="vehicle_image" accept="image/*" class="w-full px-4 py-2 bg-gray-50/50 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-500 file:text-white">
                        </div>
                    </div>

                    <!-- Terms & Conditions Acceptance Checkbox -->
                    <div class="mt-4 p-4 bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-xl">
                        <label class="flex items-start gap-3 cursor-pointer select-none">
                            <input type="checkbox" name="terms" value="1" required class="w-4 h-4 mt-0.5 rounded text-brand-500 border-gray-300 focus:ring-brand-500">
                            <span class="text-xs text-gray-600 dark:text-gray-300 leading-normal">
                                I explicitly agree to the <a href="/terms-and-conditions" target="_blank" class="text-brand-500 dark:text-brand-400 font-bold hover:underline">Ride My Cars Terms & Conditions</a> and <a href="/privacy" target="_blank" class="text-brand-500 dark:text-brand-400 font-bold hover:underline">Privacy Policy</a>.
                            </span>
                        </label>
                        @error('terms')
                            <p class="text-xs text-red-500 font-bold mt-1">You must agree to the Terms & Conditions to create an account.</p>
                        @enderror
                    </div>

                    <button type="submit" :disabled="isLoading" class="w-full py-4 mt-4 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl transition-all shadow-md shadow-brand-500/25 active:scale-[0.98] flex items-center justify-center gap-2 cursor-pointer" :class="isLoading ? 'opacity-70 cursor-wait' : ''">
                        <svg x-show="isLoading" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display: none;"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="isLoading ? 'Sending SMS Code...' : (currentRole === 'owner' ? 'Verify & List Vehicle' : (currentRole === 'driver' ? 'Verify & Register as Driver' : 'Create Account'))"></span>
                    </button>
                </form>
            </div>
        </div>

    </div>

    <!-- OTP Verification Modal Overlay -->
    <div x-show="otpModalOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 backdrop-blur-none"
         x-transition:enter-end="opacity-100 backdrop-blur-md"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 backdrop-blur-md"
         x-transition:leave-end="opacity-0 backdrop-blur-none"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
         style="display: none;">
        
        <div class="relative w-full max-w-md bg-white dark:bg-[#181818] rounded-3xl p-7 sm:p-8 shadow-2xl border border-gray-200 dark:border-white/10 overflow-hidden"
             @click.away="otpModalOpen = false">
            
            <div class="flex items-center justify-between mb-4">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/40">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 0 0-2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2zm10-10V7a4 4 0 0 0-8 0v4h8z"/></svg>
                    <span>Phone Verification</span>
                </span>
                <button type="button" @click="otpModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <h3 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight mb-2">Verify your mobile</h3>
            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 leading-relaxed mb-4">
                We sent a 4-digit code to <span class="font-bold text-gray-900 dark:text-white" x-text="phone"></span>. Please enter it below to complete your registration.
            </p>

            <div class="flex items-center gap-2 mb-6">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Valid for 2 minutes</span>
                </span>
                <span x-show="timer > 0" class="text-xs font-mono font-bold text-gray-500 dark:text-gray-400" x-text="'(' + formattedTimer + ')'"></span>
            </div>

            <!-- 4 Digit Input Boxes -->
            <div class="flex items-center justify-center gap-3 mb-3">
                <input type="text" maxlength="1" inputmode="numeric" x-ref="sc1" x-model="c1" 
                       @input="handleDigit($event, 'c1', 'sc2')" 
                       @keydown.backspace="handleBackspace($event, 'c1', null)" 
                       class="w-12 h-14 bg-gray-100 dark:bg-white/5 rounded-xl text-center text-xl font-bold text-gray-900 dark:text-white border-2 border-transparent focus:border-brand-500 focus:bg-white dark:focus:bg-[#121212] transition-all shadow-inner">
                <input type="text" maxlength="1" inputmode="numeric" x-ref="sc2" x-model="c2" 
                       @input="handleDigit($event, 'c2', 'sc3')" 
                       @keydown.backspace="handleBackspace($event, 'c2', 'sc1')" 
                       class="w-12 h-14 bg-gray-100 dark:bg-white/5 rounded-xl text-center text-xl font-bold text-gray-900 dark:text-white border-2 border-transparent focus:border-brand-500 focus:bg-white dark:focus:bg-[#121212] transition-all shadow-inner">
                <input type="text" maxlength="1" inputmode="numeric" x-ref="sc3" x-model="c3" 
                       @input="handleDigit($event, 'c3', 'sc4')" 
                       @keydown.backspace="handleBackspace($event, 'c3', 'sc2')" 
                       class="w-12 h-14 bg-gray-100 dark:bg-white/5 rounded-xl text-center text-xl font-bold text-gray-900 dark:text-white border-2 border-transparent focus:border-brand-500 focus:bg-white dark:focus:bg-[#121212] transition-all shadow-inner">
                <input type="text" maxlength="1" inputmode="numeric" x-ref="sc4" x-model="c4" 
                       @input="handleDigit($event, 'c4', null)" 
                       @keydown.backspace="handleBackspace($event, 'c4', 'sc3')" 
                       class="w-12 h-14 bg-gray-100 dark:bg-white/5 rounded-xl text-center text-xl font-bold text-gray-900 dark:text-white border-2 border-transparent focus:border-brand-500 focus:bg-white dark:focus:bg-[#121212] transition-all shadow-inner">
            </div>

            <p class="text-red-500 text-xs font-semibold mb-2 h-4 text-center" x-text="otpError"></p>
            <p class="text-emerald-600 dark:text-emerald-400 text-xs font-semibold mb-2 h-4 text-center" x-text="otpSuccess"></p>

            <div class="text-center mb-6">
                <button type="button" 
                        x-show="timer === 0" 
                        @click="resendOtp()" 
                        class="text-xs font-bold text-brand-500 hover:underline cursor-pointer"
                        style="display: none;">
                    Didn't receive code? Resend SMS OTP
                </button>
                <span x-show="timer > 0" class="text-xs text-gray-400">
                    Resend code in <span class="font-mono font-bold" x-text="formattedTimer"></span>
                </span>
            </div>

            <button type="button" 
                    @click="verifyAndRegister()"
                    class="w-full py-3.5 rounded-xl font-bold flex items-center justify-center gap-2 transition-all"
                    :class="(c1 && c2 && c3 && c4 && !isLoading) ? 'bg-brand-500 hover:bg-brand-600 text-white cursor-pointer shadow-lg shadow-brand-500/25' : 'bg-gray-100 dark:bg-white/5 text-gray-400 cursor-not-allowed'"
                    :disabled="isLoading || !(c1 && c2 && c3 && c4)">
                <svg x-show="isLoading" class="animate-spin h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display: none;"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span x-text="isLoading ? 'Verifying...' : 'Verify & Complete Registration'">Verify & Complete Registration</span>
            </button>
        </div>
    </div>

    <script>
        function signupApp() {
            return {
                currentRole: '{{ $role ?? "customer" }}',
                phone: '{{ old("phone", request("phone")) }}',
                mobileNumber: '',
                countryDropdownOpen: false,
                countrySearch: '',
                selectedCountry: (window.WORLD_COUNTRIES && window.WORLD_COUNTRIES.length) ? window.WORLD_COUNTRIES[0] : { name: 'United States', code: 'US', dial: '+1', flagUrl: 'https://flagcdn.com/w40/us.png' },
                countries: (window.WORLD_COUNTRIES && window.WORLD_COUNTRIES.length) ? window.WORLD_COUNTRIES : [],

                otpModalOpen: false,
                isLoading: false,
                otpError: '',
                otpSuccess: '',
                timer: 120,
                timerInterval: null,
                c1: '', c2: '', c3: '', c4: '',

                init() {
                    if (window.WORLD_COUNTRIES && window.WORLD_COUNTRIES.length) {
                        this.countries = window.WORLD_COUNTRIES;
                        if (!this.selectedCountry || !this.selectedCountry.flagUrl) {
                            this.selectedCountry = this.countries[0];
                        }
                    }
                    const prefill = '{{ old("phone", request("phone")) }}';
                    if (prefill) {
                        const matched = this.countries.find(c => prefill.startsWith(c.dial));
                        if (matched) {
                            this.selectedCountry = matched;
                            this.mobileNumber = prefill.substring(matched.dial.length).trim();
                        } else {
                            this.mobileNumber = prefill.replace(/^\+?\d{1,4}/, '').trim();
                        }
                    }
                    this.updatePhone();
                },

                get filteredCountries() {
                    if (!this.countrySearch) return this.countries;
                    const q = this.countrySearch.toLowerCase().trim();
                    return this.countries.filter(c => 
                        c.name.toLowerCase().includes(q) || 
                        c.dial.includes(q) || 
                        c.code.toLowerCase().includes(q)
                    );
                },

                toggleCountryDropdown() {
                    this.countryDropdownOpen = !this.countryDropdownOpen;
                    if (this.countryDropdownOpen) {
                        this.countrySearch = '';
                        this.$nextTick(() => {
                            this.$refs.countrySearchInput?.focus({ preventScroll: true });
                        });
                    }
                },

                selectCountry(country) {
                    this.selectedCountry = country;
                    this.countryDropdownOpen = false;
                    this.countrySearch = '';
                    this.updatePhone();
                },

                updatePhone() {
                    const raw = (this.mobileNumber || '').replace(/[^\d]/g, '');
                    this.phone = this.selectedCountry.dial + (raw ? ' ' + raw : '');
                },

                startTimer(seconds = 120) {
                    this.timer = seconds;
                    if (this.timerInterval) clearInterval(this.timerInterval);
                    this.timerInterval = setInterval(() => {
                        if (this.timer > 0) {
                            this.timer--;
                        } else {
                            clearInterval(this.timerInterval);
                        }
                    }, 1000);
                },

                get formattedTimer() {
                    const m = Math.floor(this.timer / 60);
                    const s = this.timer % 60;
                    return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
                },

                handleDigit(e, currentKey, nextRef) {
                    const val = e.target.value.replace(/\D/g, '');
                    this[currentKey] = val.slice(-1);
                    if (this[currentKey] && nextRef && this.$refs[nextRef]) {
                        this.$refs[nextRef].focus({ preventScroll: true });
                    }
                    if (this.c1 && this.c2 && this.c3 && this.c4) {
                        this.verifyAndRegister();
                    }
                },

                handleBackspace(e, currentKey, prevRef) {
                    if (!this[currentKey] && prevRef && this.$refs[prevRef]) {
                        this.$refs[prevRef].focus({ preventScroll: true });
                    }
                },

                async initiateSignup(e) {
                    const form = this.$refs.signupForm;
                    if (!form.checkValidity()) {
                        form.reportValidity();
                        return;
                    }

                    if (!this.phone) {
                        alert('Please enter your mobile phone number to receive the verification OTP.');
                        return;
                    }

                    this.isLoading = true;
                    this.otpError = '';
                    this.otpSuccess = '';

                    try {
                        const res = await fetch('/api/otp/send', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ phone: this.phone, action: 'register' })
                        });
                        const data = await res.json();
                        this.isLoading = false;

                        if (data.success) {
                            this.otpModalOpen = true;
                            this.c1 = this.c2 = this.c3 = this.c4 = '';
                            this.startTimer(120);
                            this.$nextTick(() => { this.$refs.sc1?.focus({ preventScroll: true }); });
                        } else {
                            alert(data.error || data.message || 'Failed to send SMS OTP. Please check your phone number.');
                        }
                    } catch (err) {
                        this.isLoading = false;
                        alert('Network error while requesting verification code.');
                    }
                },

                async resendOtp() {
                    if (this.timer > 0 || this.isLoading) return;
                    this.isLoading = true;
                    this.otpError = '';
                    this.otpSuccess = '';

                    try {
                        const res = await fetch('/api/otp/send', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ phone: this.phone, action: 'register' })
                        });
                        const data = await res.json();
                        this.isLoading = false;
                        if (data.success) {
                            this.startTimer(120);
                            this.otpSuccess = 'New 4-digit code sent!';
                            setTimeout(() => { this.otpSuccess = ''; }, 3500);
                        } else {
                            this.otpError = data.error || 'Failed to resend code.';
                        }
                    } catch (e) {
                        this.isLoading = false;
                        this.otpError = 'Network error.';
                    }
                },

                async verifyAndRegister() {
                    if (!(this.c1 && this.c2 && this.c3 && this.c4) || this.isLoading) return;
                    this.isLoading = true;
                    this.otpError = '';
                    this.otpSuccess = '';

                    const code = (this.c1 + this.c2 + this.c3 + this.c4).trim();
                    const form = this.$refs.signupForm;
                    const formData = new FormData(form);
                    formData.append('otp', code);

                    try {
                        const res = await fetch('/api/otp/verify', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: formData
                        });
                        const data = await res.json();
                        this.isLoading = false;

                        if (data.success) {
                            window.location.href = data.redirect || '/';
                        } else {
                            this.otpError = data.error || data.message || 'Invalid code. Please try again.';
                            this.c1 = this.c2 = this.c3 = this.c4 = '';
                            this.$nextTick(() => { this.$refs.sc1?.focus({ preventScroll: true }); });
                        }
                    } catch (e) {
                        this.isLoading = false;
                        this.otpError = 'Network error during verification.';
                    }
                }
            };
        }
    </script>
</body>
</html>
