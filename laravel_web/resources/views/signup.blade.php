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
    <div class="flex min-h-screen" x-data="{ currentRole: '{{ $role ?? 'customer' }}' }">
        
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

                <form action="/signup" method="POST" enctype="multipart/form-data" class="space-y-5">
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
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Phone <span class="text-gray-400 dark:text-gray-500 font-normal">(optional)</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            </div>
                            <input type="tel" name="phone" value="{{ old('phone', request('phone')) }}" placeholder="+1 234 567 8900" class="w-full pl-12 pr-4 py-3.5 bg-gray-50/50 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
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

                    <button type="submit" class="w-full py-4 mt-4 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl transition-all shadow-md shadow-brand-500/25 active:scale-[0.98]">
                        <span x-text="currentRole === 'owner' ? 'Register & List Vehicle' : (currentRole === 'driver' ? 'Register as Driver' : 'Create Account')"></span>
                    </button>
                </form>
            </div>
        </div>

    </div>
</body>
</html>
