<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign Up — RideMyCars</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white dark:bg-[#111] text-gray-900 dark:text-white overflow-x-hidden">
    <div class="flex min-h-screen">
        
        <!-- Left Banner (Dark) -->
        <div class="hidden lg:flex w-[45%] bg-[#1a1a1a] flex-col justify-between p-12 fixed h-screen left-0 top-0 overflow-hidden">
            <!-- Logo -->
            <a class="flex items-center gap-2 group z-10" href="/">
                <img src="{{ asset('images/logo.png') }}" alt="RideMyCars Logo" class="h-16 md:h-[72px] w-auto mix-blend-multiply dark:mix-blend-normal dark:bg-white dark:rounded-xl dark:p-1">
            </a>

            <!-- Text Content -->
            <div class="z-10 max-w-lg mt-20">
                <h1 class="text-5xl font-bold text-white mb-6 leading-tight">
                    Ride. Rent. Hire.<br/>
                    All in one place.
                </h1>
                <p class="text-gray-400 dark:text-gray-500 text-lg leading-relaxed">
                    Join 50,000+ riders and drivers on the platform that combines everything you need to move.
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
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-2 tracking-tight">{{ $title ?? 'Create your account' }}</h2>
                    <p class="text-gray-500 dark:text-gray-400 text-lg">{{ $subtitle ?? "Join RideMyCars — it's free forever." }}</p>
                </div>

                <form action="/signup" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    <input type="hidden" name="role" value="{{ $role ?? 'customer' }}">

                    @if(($role ?? '') === 'driver')
                        <div class="mb-6 p-4 rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/30 text-amber-800 dark:text-amber-200 text-xs space-y-1">
                            <p class="font-bold text-sm mb-1 flex items-center gap-1.5">
                                <span>👔</span> Formal Profile Picture Requirement
                            </p>
                            <p>Driver profile photos must be formal. Acceptable attire includes a formal suit, long-sleeve formal shirt, or tie. Non-formal or casual photos will be flagged for review.</p>
                        </div>
                    @endif
                    
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
                            <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="+1 234 567 8900" class="w-full pl-12 pr-4 py-3.5 bg-gray-50/50 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
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

                    @if(($role ?? '') === 'driver')
                        <div class="pt-6 border-t border-gray-200 dark:border-white/10 space-y-4">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-xl">👨‍✈️</span>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Driver Credentials & Rates</h3>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">License Number</label>
                                    <input type="text" name="license_number" required value="{{ old('license_number') }}" placeholder="e.g. DL-99887766" class="w-full px-4 py-3.5 bg-gray-50/50 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">License Expiry Date</label>
                                    <input type="date" name="license_expiry" required value="{{ old('license_expiry') }}" class="w-full px-4 py-3.5 bg-gray-50/50 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Country</label>
                                    <select name="country" required class="w-full px-4 py-3.5 bg-gray-50/50 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                                        <option value="USA">USA</option>
                                        <option value="Ghana">Ghana</option>
                                        <option value="Nigeria">Nigeria</option>
                                        <option value="South Africa">South Africa</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Driving Experience (Years)</label>
                                    <input type="number" name="experience_years" required value="{{ old('experience_years', 5) }}" min="1" placeholder="5" class="w-full px-4 py-3.5 bg-gray-50/50 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Hourly Rate ($)</label>
                                    <input type="number" step="0.01" name="hourly_rate" required value="{{ old('hourly_rate', 25.00) }}" placeholder="25.00" class="w-full px-3 py-3 bg-gray-50/50 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Daily Rate ($)</label>
                                    <input type="number" step="0.01" name="daily_rate" required value="{{ old('daily_rate', 170.00) }}" placeholder="170.00" class="w-full px-3 py-3 bg-gray-50/50 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Weekly Rate ($)</label>
                                    <input type="number" step="0.01" name="weekly_rate" required value="{{ old('weekly_rate', 950.00) }}" placeholder="950.00" class="w-full px-3 py-3 bg-gray-50/50 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Formal Profile Photo <span class="text-xs text-amber-600 font-normal">(Suit, Long-sleeve shirt, or Tie required)</span></label>
                                <input type="file" name="driver_photo" accept="image/*" class="w-full px-4 py-2 bg-gray-50/50 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-500 file:text-white">
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

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Short Bio / Chauffeur Overview <span class="text-gray-400 font-normal">(optional)</span></label>
                                <textarea name="bio" rows="2" placeholder="Tell clients about your driving background and experience..." class="w-full px-4 py-3 bg-gray-50/50 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all"></textarea>
                            </div>
                        </div>
                    @endif

                    <button type="submit" class="w-full py-4 mt-4 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl transition-all shadow-md shadow-brand-500/25 active:scale-[0.98]">
                        Create Account
                    </button>
                    
                    <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-4">
                        By signing up you agree to our <a href="/terms" class="text-brand-500 hover:underline">Terms</a> and <a href="/privacy" class="text-brand-500 hover:underline">Privacy Policy</a>.
                    </p>
                </form>
            </div>
        </div>

    </div>
</body>
</html>
