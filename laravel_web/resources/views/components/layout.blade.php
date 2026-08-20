<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ darkMode: localStorage.getItem('theme') === 'dark' }" 
      x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))" 
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'RideMyCars' }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#fafafa] dark:bg-[#0a0a0a] text-gray-900 dark:text-white min-h-screen flex flex-col transition-colors duration-200 {{ $theme ?? '' }}">
    
    <!-- Header -->
    <header class="top-0 left-0 right-0 z-50 bg-white dark:bg-[#111] dark:bg-[#0a0a0a] border-b border-gray-100 dark:border-white/10 transition-colors duration-200">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex h-20 items-center justify-between">
                <!-- Logo -->
                <a class="flex items-center gap-2 group" href="/">
                    <img src="{{ asset('images/logo.png') }}" alt="RideMyCars Logo" class="h-16 md:h-[72px] w-auto mix-blend-multiply dark:mix-blend-normal dark:bg-white dark:rounded-xl dark:p-1">
                </a>
                
                <div class="hidden lg:flex items-center gap-6">
                    @auth
                        @if(auth()->user()->role === 'driver')
                            <a class="text-sm font-medium transition-colors {{ request()->is('driver/dashboard*') ? 'text-brand-500 font-bold dark:text-brand-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}" href="/driver/dashboard">Dashboard</a>
                            <a class="text-sm font-medium transition-colors {{ request()->is('ride*') ? 'text-brand-500 font-bold dark:text-brand-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}" href="/ride">Ride</a>
                            <a class="text-sm font-medium transition-colors {{ request()->is('wallet*') ? 'text-brand-500 font-bold dark:text-brand-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}" href="/wallet">Earnings</a>
                            <a class="text-sm font-medium transition-colors {{ request()->is('pricing*') ? 'text-brand-500 font-bold dark:text-brand-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}" href="/pricing">Pricing</a>
                        @else
                            <a class="text-sm font-medium transition-colors {{ request()->is('ride*') ? 'text-brand-500 font-bold dark:text-brand-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}" href="/ride">Ride</a>
                            <a class="text-sm font-medium transition-colors {{ request()->is('rent*') ? 'text-brand-500 font-bold dark:text-brand-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}" href="/rent">Rent Vehicle</a>
                            <a class="text-sm font-medium transition-colors {{ request()->is('hire-driver*') || request()->is('driver-booking*') ? 'text-brand-500 font-bold dark:text-brand-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}" href="/hire-driver">Hire Driver</a>
                            <a class="text-sm font-medium transition-colors {{ request()->is('delivery*') ? 'text-brand-500 font-bold dark:text-brand-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}" href="/delivery">Package Delivery</a>
                            <a class="text-sm font-medium transition-colors {{ request()->is('membership*') ? 'text-brand-500 font-bold dark:text-brand-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}" href="/membership">Memberships</a>
                            
                            <!-- Company Dropdown -->
                            <div x-data="{ open: false }" class="relative" @click.away="open = false">
                                <button @click="open = !open" class="text-sm font-medium transition-colors flex items-center gap-1 {{ request()->is('about*') || request()->is('safety*') || request()->is('become-*') || request()->is('blogs*') ? 'text-brand-500 font-bold dark:text-brand-400' : 'text-gray-900 dark:text-white' }} hover:bg-gray-100 dark:hover:bg-white/10 px-4 py-2 rounded-full">
                                    Company 
                                    <svg :class="{'rotate-180': open}" class="transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                </button>
                                <div x-show="open" x-transition class="absolute top-full left-0 mt-2 w-48 bg-white dark:bg-[#111] border border-gray-100 dark:border-white/10 shadow-xl rounded-xl py-2 z-50" style="display: none;">
                                    <a href="/about" class="block px-4 py-2.5 text-sm {{ request()->is('about*') ? 'text-brand-500 font-bold dark:text-brand-400' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }} hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">About Us</a>
                                    <a href="/safety" class="block px-4 py-2.5 text-sm {{ request()->is('safety*') ? 'text-brand-500 font-bold dark:text-brand-400' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }} hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">Safety</a>
                                    <a href="/become-driver" class="block px-4 py-2.5 text-sm {{ request()->is('become-driver*') ? 'text-brand-500 font-bold dark:text-brand-400' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }} hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">Become a Driver</a>
                                    <a href="/become-owner" class="block px-4 py-2.5 text-sm {{ request()->is('become-owner*') ? 'text-brand-500 font-bold dark:text-brand-400' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }} hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">List Your Vehicle</a>
                                    <a href="/blogs" class="block px-4 py-2.5 text-sm {{ request()->is('blogs*') ? 'text-brand-500 font-bold dark:text-brand-400' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }} hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">Blogs</a>
                                </div>
                            </div>

                            <a class="text-sm font-medium transition-colors {{ request()->is('pricing*') ? 'text-brand-500 font-bold dark:text-brand-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}" href="/pricing">Pricing</a>
                        @endif
                    @else
                        <a class="text-sm font-medium transition-colors {{ request()->is('ride*') ? 'text-brand-500 font-bold dark:text-brand-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}" href="/ride">Ride</a>
                        <a class="text-sm font-medium transition-colors {{ request()->is('rent*') ? 'text-brand-500 font-bold dark:text-brand-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}" href="/rent">Rent Vehicle</a>
                        <a class="text-sm font-medium transition-colors {{ request()->is('hire-driver*') || request()->is('driver-booking*') ? 'text-brand-500 font-bold dark:text-brand-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}" href="/hire-driver">Hire Driver</a>
                        <a class="text-sm font-medium transition-colors {{ request()->is('delivery*') ? 'text-brand-500 font-bold dark:text-brand-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}" href="/delivery">Package Delivery</a>
                        <a class="text-sm font-medium transition-colors {{ request()->is('membership*') ? 'text-brand-500 font-bold dark:text-brand-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}" href="/membership">Memberships</a>
                        
                        <!-- Company Dropdown -->
                        <div x-data="{ open: false }" class="relative" @click.away="open = false">
                            <button @click="open = !open" class="text-sm font-medium transition-colors flex items-center gap-1 {{ request()->is('about*') || request()->is('safety*') || request()->is('become-*') || request()->is('blogs*') ? 'text-brand-500 font-bold dark:text-brand-400' : 'text-gray-900 dark:text-white' }} hover:bg-gray-100 dark:hover:bg-white/10 px-4 py-2 rounded-full">
                                Company 
                                <svg :class="{'rotate-180': open}" class="transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                            </button>
                            <div x-show="open" x-transition class="absolute top-full left-0 mt-2 w-48 bg-white dark:bg-[#111] border border-gray-100 dark:border-white/10 shadow-xl rounded-xl py-2 z-50" style="display: none;">
                                <a href="/about" class="block px-4 py-2.5 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">About Us</a>
                                <a href="/safety" class="block px-4 py-2.5 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">Safety</a>
                                <a href="/become-driver" class="block px-4 py-2.5 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">Become a Driver</a>
                                <a href="/become-owner" class="block px-4 py-2.5 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">List Your Vehicle</a>
                                <a href="/blogs" class="block px-4 py-2.5 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">Blogs</a>
                            </div>
                        </div>

                        <a class="text-sm font-medium transition-colors {{ request()->is('pricing*') ? 'text-brand-500 font-bold dark:text-brand-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}" href="/pricing">Pricing</a>
                    @endauth
                </div>
                
                <!-- Actions -->
                <div class="flex items-center gap-3 lg:gap-6 shrink-0">
                    <button @click="darkMode = !darkMode" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 w-8 h-8 lg:w-9 lg:h-9 rounded-full flex items-center justify-center transition-colors shrink-0" :class="darkMode ? 'border border-gray-700 bg-gray-800' : ''">
                        <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                        <svg x-show="darkMode" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                    </button>
                    @auth
                    <div x-data="{ userMenuOpen: false }" class="relative" @click.away="userMenuOpen = false">
                        <button @click="userMenuOpen = !userMenuOpen" class="flex items-center gap-2 focus:outline-none">
                            <div class="w-10 h-10 rounded-full bg-gray-200 text-gray-700 flex items-center justify-center font-bold text-lg border border-gray-300 shadow-sm">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                        </button>
                        
                        <div x-show="userMenuOpen" x-transition class="absolute top-full right-0 mt-4 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-2xl rounded-2xl p-4 sm:p-5 z-50" style="display: none; width: 340px; max-width: 90vw;">
                            
                            <!-- Header -->
                            <div class="flex items-center justify-between mb-6">
                                <div class="overflow-hidden pr-3">
                                    <h2 class="text-xl font-bold text-black dark:text-white leading-tight truncate">{{ auth()->user()->name }}</h2>
                                    <div class="inline-flex items-center gap-1 bg-[#f0f0f0] dark:bg-gray-800 px-2 py-0.5 rounded-full mt-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                        <span class="text-xs font-bold text-gray-900 dark:text-white">5.00</span>
                                    </div>
                                </div>
                                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-gray-100 text-gray-900 flex items-center justify-center font-bold text-xl sm:text-2xl shrink-0">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                            </div>

                            @if(auth()->user()->role === 'driver')
                            <!-- Driver Action Buttons -->
                            <div class="flex items-stretch gap-2 mb-4">
                                <a href="/driver/dashboard" class="flex-1 flex flex-col items-center justify-center gap-2 py-3 px-1 bg-[#f8f8f8] dark:bg-gray-800 rounded-xl hover:bg-[#eaeaea] dark:hover:bg-gray-700 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-black dark:text-white"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                                    <span class="text-xs font-bold text-black dark:text-white">Dashboard</span>
                                </a>
                                <a href="/wallet" class="flex-1 flex flex-col items-center justify-center gap-2 py-3 px-1 bg-[#f8f8f8] dark:bg-gray-800 rounded-xl hover:bg-[#eaeaea] dark:hover:bg-gray-700 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-black dark:text-white"><line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                    <span class="text-xs font-bold text-black dark:text-white">Earnings</span>
                                </a>
                                <a href="/help" class="flex-1 flex flex-col items-center justify-center gap-2 py-3 px-1 bg-[#f8f8f8] dark:bg-gray-800 rounded-xl hover:bg-[#eaeaea] dark:hover:bg-gray-700 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-black dark:text-white"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>
                                    <span class="text-xs font-bold text-black dark:text-white">Help</span>
                                </a>
                            </div>

                            <!-- Driver List Links -->
                            <div class="flex flex-col mb-4">
                                <a href="/driver/dashboard" class="flex items-center gap-3 py-3 px-2 hover:bg-[#f8f8f8] dark:hover:bg-gray-800 rounded-lg transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-900 dark:text-gray-300"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                                    <span class="font-medium text-black dark:text-white text-[15px]">Driver Dashboard</span>
                                </a>
                                <a href="/my-rides" class="flex items-center gap-3 py-3 px-2 hover:bg-[#f8f8f8] dark:hover:bg-gray-800 rounded-lg transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-900 dark:text-gray-300"><path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M5 17H3v-6l2-5h9l4 5h1a2 2 0 0 1 2 2v4h-2m-4 0H9"/></svg>
                                    <span class="font-medium text-black dark:text-white text-[15px]">Ride History</span>
                                </a>
                                <a href="/account" class="flex items-center gap-3 py-3 px-2 hover:bg-[#f8f8f8] dark:hover:bg-gray-800 rounded-lg transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="none" class="text-gray-900 dark:text-gray-300"><path d="M12 2a5 5 0 1 0 5 5 5 5 0 0 0-5-5Zm0 8a3 3 0 1 1 3-3 3 3 0 0 1-3 3Zm9 11v-1a7 7 0 0 0-7-7h-4a7 7 0 0 0-7 7v1h2v-1a5 5 0 0 1 5-5h4a5 5 0 0 1 5 5v1h2Z"/></svg>
                                    <span class="font-medium text-black dark:text-white text-[15px]">Manage account</span>
                                </a>
                                <a href="/legal" class="flex items-center gap-3 py-3 px-2 hover:bg-[#f8f8f8] dark:hover:bg-gray-800 rounded-lg transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="none" class="text-gray-900 dark:text-gray-300"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                                    <span class="font-medium text-black dark:text-white text-[15px]">Legal</span>
                                </a>
                            </div>
                            @else
                            <!-- Rider Action Buttons -->
                            <div class="flex items-stretch gap-2 mb-4">
                                <a href="/help" class="flex-1 flex flex-col items-center justify-center gap-2 py-3 px-1 bg-[#f8f8f8] dark:bg-gray-800 rounded-xl hover:bg-[#eaeaea] dark:hover:bg-gray-700 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-black dark:text-white"><circle cx="12" cy="12" r="10"/><path d="m4.93 4.93 4.24 4.24"/><path d="m14.83 9.17 4.24-4.24"/><path d="m14.83 14.83 4.24 4.24"/><path d="m9.17 14.83-4.24 4.24"/><circle cx="12" cy="12" r="4"/></svg>
                                    <span class="text-xs font-bold text-black dark:text-white">Help</span>
                                </a>
                                <a href="/wallet" class="flex-1 flex flex-col items-center justify-center gap-2 py-3 px-1 bg-[#f8f8f8] dark:bg-gray-800 rounded-xl hover:bg-[#eaeaea] dark:hover:bg-gray-700 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-black dark:text-white"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>
                                    <span class="text-xs font-bold text-black dark:text-white">Wallet</span>
                                </a>
                                <a href="/activity" class="flex-1 flex flex-col items-center justify-center gap-2 py-3 px-1 bg-[#f8f8f8] dark:bg-gray-800 rounded-xl hover:bg-[#eaeaea] dark:hover:bg-gray-700 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="currentColor" stroke="none" class="text-black dark:text-white"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"/></svg>
                                    <span class="text-xs font-bold text-black dark:text-white">Activity</span>
                                </a>
                            </div>

                            <!-- RideMyCars Cash -->
                            <a href="/wallet" class="flex items-center justify-between p-4 bg-[#f8f8f8] dark:bg-gray-800 rounded-xl mb-4 hover:bg-[#eaeaea] dark:hover:bg-gray-700 transition-colors">
                                <span class="font-bold text-black dark:text-white text-sm">RideMyCars Cash</span>
                                <span class="font-bold text-black dark:text-white text-lg">$0.00</span>
                            </a>

                            <!-- Rider List Links -->
                            <div class="flex flex-col mb-4">
                                <a href="/account" class="flex items-center gap-3 py-3 px-2 hover:bg-[#f8f8f8] dark:hover:bg-gray-800 rounded-lg transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="none" class="text-gray-900 dark:text-gray-300"><path d="M12 2a5 5 0 1 0 5 5 5 5 0 0 0-5-5Zm0 8a3 3 0 1 1 3-3 3 3 0 0 1-3 3Zm9 11v-1a7 7 0 0 0-7-7h-4a7 7 0 0 0-7 7v1h2v-1a5 5 0 0 1 5-5h4a5 5 0 0 1 5 5v1h2Z"/></svg>
                                    <span class="font-medium text-black dark:text-white text-[15px]">Manage account</span>
                                </a>
                                <a href="/my-rides" class="flex items-center gap-3 py-3 px-2 hover:bg-[#f8f8f8] dark:hover:bg-gray-800 rounded-lg transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-900 dark:text-gray-300"><path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M5 17H3v-6l2-5h9l4 5h1a2 2 0 0 1 2 2v4h-2m-4 0H9"/></svg>
                                    <span class="font-medium text-black dark:text-white text-[15px]">My Rides</span>
                                </a>
                                <a href="/promotions" class="flex items-center gap-3 py-3 px-2 hover:bg-[#f8f8f8] dark:hover:bg-gray-800 rounded-lg transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="none" class="text-gray-900 dark:text-gray-300"><path d="M21.41 11.58l-9-9C12.05 2.22 11.55 2 11 2H4c-1.1 0-2 .9-2 2v7c0 .55.22 1.05.59 1.41l9 9c.36.36.86.58 1.41.58.55 0 1.05-.22 1.41-.59l7-7c.37-.36.59-.86.59-1.41 0-.55-.23-1.06-.59-1.41zM5.5 7C4.67 7 4 6.33 4 5.5S4.67 4 5.5 4 7 4.67 7 5.5 6.33 7 5.5 7z"/></svg>
                                    <span class="font-medium text-black dark:text-white text-[15px]">Promotions</span>
                                </a>
                                <a href="/legal" class="flex items-center gap-3 py-3 px-2 hover:bg-[#f8f8f8] dark:hover:bg-gray-800 rounded-lg transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="none" class="text-gray-900 dark:text-gray-300"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                                    <span class="font-medium text-black dark:text-white text-[15px]">Legal</span>
                                </a>
                            </div>
                            @endif

                            <!-- Sign Out Button -->
                            <form method="POST" action="/logout">
                                @csrf
                                <button type="submit" class="w-full py-3 hover:bg-[#f8f8f8] dark:hover:bg-gray-800 rounded-lg transition-colors text-left px-2 font-medium text-red-600 text-[15px]">
                                    Sign out
                                </button>
                            </form>
                        </div>
                    </div>
                    @else
                    <a class="whitespace-nowrap text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white transition-colors" href="/login">Sign In</a>
                    <a class="whitespace-nowrap px-4 py-2 lg:px-6 lg:py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-xl transition-all shadow-md shadow-brand-500/25" href="/signup">Get Started</a>
                    @endauth
                </div>
            </div>
        </nav>
    </header>

    <!-- Ongoing Ride Banner -->
    @auth
    <div x-data="ongoingRide()" x-init="init()" x-show="ride" x-transition x-cloak
         class="fixed bottom-0 left-0 right-0 z-40 px-4 pb-4 pointer-events-none" style="display:none;">
        
        <!-- Expanded Detail Panel -->
        <div x-show="expanded" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-8"
             class="pointer-events-auto max-w-lg mx-auto bg-white dark:bg-[#111] rounded-2xl shadow-2xl border border-gray-200 dark:border-white/10 mb-2 overflow-hidden" style="display:none;">
            
            <!-- Header bar -->
            <div class="p-4 border-b border-gray-100 dark:border-white/10 flex items-center justify-between"
                 :class="{
                     'bg-indigo-50 dark:bg-indigo-900/20': ride && ride.status === 'pending',
                     'bg-yellow-50 dark:bg-yellow-900/20': ride && ride.status === 'accepted',
                     'bg-blue-50 dark:bg-blue-900/20': ride && ride.status === 'en_route',
                     'bg-amber-50 dark:bg-amber-900/20': ride && ride.status === 'arrived',
                     'bg-green-50 dark:bg-green-900/20': ride && ride.status === 'in_progress',
                 }">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full animate-pulse"
                          :class="{
                              'bg-indigo-500': ride && ride.status === 'pending',
                              'bg-yellow-500': ride && ride.status === 'accepted',
                              'bg-blue-500': ride && ride.status === 'en_route',
                              'bg-amber-500': ride && ride.status === 'arrived',
                              'bg-green-500': ride && ride.status === 'in_progress',
                          }"></span>
                    <span class="font-bold text-sm text-gray-900 dark:text-white" x-text="statusText"></span>
                </div>
                <button @click="expanded = false" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center hover:bg-gray-200 dark:hover:bg-white/20 transition-colors">
                    <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg>
                </button>
            </div>
            
            <!-- Ride Details -->
            <div class="p-4 space-y-3">
                <!-- Locations -->
                <div class="flex gap-3">
                    <div class="flex flex-col items-center pt-1 shrink-0">
                        <div class="w-3 h-3 rounded-full bg-green-500 border-2 border-green-200"></div>
                        <div class="w-0.5 h-8 bg-gray-200 dark:bg-white/10 my-1"></div>
                        <div class="w-3 h-3 rounded-full bg-red-500 border-2 border-red-200"></div>
                    </div>
                    <div class="flex-1 min-w-0 space-y-3">
                        <div>
                            <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Pickup</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white leading-tight break-words" x-text="ride ? ride.pickup_location : ''"></p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Dropoff</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white leading-tight break-words" x-text="ride ? ride.dropoff_location : ''"></p>
                        </div>
                    </div>
                </div>

                <!-- Driver Details (when assigned) -->
                <div x-show="ride && ride.driver_name" class="pt-3 border-t border-gray-100 dark:border-white/10">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-lg font-bold text-indigo-700 dark:text-indigo-300 shrink-0" x-text="ride && ride.driver_name ? ride.driver_name.charAt(0) : ''"></div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-base text-gray-900 dark:text-white" x-text="ride ? ride.driver_name : ''"></p>
                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                <span x-show="ride && ride.driver_rating" class="flex items-center gap-0.5">
                                    <span class="text-yellow-400">★</span>
                                    <span x-text="ride && ride.driver_rating ? parseFloat(ride.driver_rating).toFixed(1) : ''"></span>
                                </span>
                                <span x-show="ride && ride.driver_total_trips">·</span>
                                <span x-show="ride && ride.driver_total_trips" x-text="(ride ? ride.driver_total_trips : 0) + ' trips'"></span>
                            </div>
                        </div>
                        <a x-show="ride && ride.driver_phone" :href="'tel:' + (ride ? ride.driver_phone : '')" class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center hover:bg-green-200 transition-colors shrink-0">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        </a>
                    </div>
                    <!-- Vehicle Info -->
                    <div x-show="ride && ride.driver_vehicle" class="flex items-center gap-3 p-2.5 bg-gray-50 dark:bg-white/5 rounded-xl text-xs">
                        <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M5 17H3v-6l2-5h9l4 5h1a2 2 0 0 1 2 2v4h-2m-4 0H9"/></svg>
                        <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="ride ? ride.driver_vehicle : ''"></span>
                        <span x-show="ride && ride.driver_plate" class="ml-auto px-2 py-0.5 bg-gray-200 dark:bg-white/10 rounded font-bold text-gray-600 dark:text-gray-300" x-text="ride ? ride.driver_plate : ''"></span>
                    </div>
                </div>

                <!-- Searching for drivers (when pending) -->
                <div x-show="ride && !ride.driver_name" class="pt-3 border-t border-gray-100 dark:border-white/10">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center shrink-0">
                            <div class="w-5 h-5 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
                        </div>
                        <div>
                            <p class="font-bold text-sm text-gray-900 dark:text-white">Looking for drivers...</p>
                            <p class="text-xs text-gray-400">We'll notify you when a driver accepts</p>
                        </div>
                    </div>
                </div>

                <!-- Fare & Payment -->
                <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-white/10">
                    <div>
                        <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Fare</p>
                        <p class="font-black text-2xl text-gray-900 dark:text-white" x-text="ride && ride.fare ? '$' + parseFloat(ride.fare).toFixed(2) : '$0.00'"></p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Payment</p>
                        <p class="font-semibold text-sm text-gray-700 dark:text-gray-300 capitalize" x-text="ride ? ride.payment_method : 'cash'"></p>
                    </div>
                </div>
                
                <!-- Boost Fare (visible only when pending / no driver accepted) -->
                <div x-show="ride && ride.status === 'pending'" class="pt-3 border-t border-gray-100 dark:border-white/10">
                    <p class="text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">💰 No drivers yet? Increase fare to attract drivers</p>
                    <div class="flex gap-2">
                        <div class="flex-1 relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold">$</span>
                            <input type="number" step="0.50" min="0" x-model="boostFare" 
                                   class="w-full pl-7 pr-3 py-2.5 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-sm font-bold text-gray-900 dark:text-white"
                                   placeholder="Enter new fare">
                        </div>
                        <button @click="submitBoost()" :disabled="boosting" 
                                class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-bold text-xs rounded-xl transition-colors whitespace-nowrap flex items-center gap-1.5">
                            <svg x-show="boosting" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-dasharray="32" stroke-dashoffset="12"/></svg>
                            <span x-text="boosting ? 'Sending...' : '🚀 Boost & Resend'"></span>
                        </button>
                    </div>
                    <p x-show="boostError" class="text-xs text-red-500 mt-1 font-medium" x-text="boostError"></p>
                    <p x-show="boostSuccess" class="text-xs text-green-600 mt-1 font-medium">✓ Fare boosted! Resent to all drivers.</p>
                </div>
                
                <!-- Actions: Track & Cancel -->
                <div class="space-y-2">
                    <a :href="'/ride?resume=' + (ride ? ride.id : '')" 
                       class="block w-full text-center py-3 bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-bold text-sm rounded-xl hover:opacity-90 transition-opacity">
                        Track Ride →
                    </a>
                    <button @click="cancelRide()" :disabled="cancelling"
                            class="block w-full text-center py-2.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 font-bold text-xs rounded-xl transition-colors flex items-center justify-center gap-1.5">
                        <svg x-show="cancelling" class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-dasharray="32" stroke-dashoffset="12"/></svg>
                        <span x-text="cancelling ? 'Cancelling ride...' : '✕ Cancel Ride'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Collapsed Banner (click to expand) -->
        <div @click="expanded = !expanded"
           class="pointer-events-auto max-w-lg mx-auto flex items-center gap-3 p-3.5 sm:p-4 rounded-2xl shadow-2xl border transition-all hover:scale-[1.01] cursor-pointer"
           :class="{
               'bg-indigo-600 border-indigo-500 text-white': ride && ride.status === 'pending',
               'bg-blue-600 border-blue-500 text-white': ride && ride.status === 'en_route',
               'bg-amber-500 border-amber-400 text-white': ride && ride.status === 'arrived',
               'bg-emerald-600 border-emerald-500 text-white': ride && ride.status === 'in_progress',
               'bg-yellow-500 border-yellow-400 text-white': ride && ride.status === 'accepted',
           }">
            <!-- Pulsing dot -->
            <div class="relative shrink-0">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white/20 flex items-center justify-center">
                    <div class="w-3 h-3 rounded-full bg-white animate-pulse"></div>
                </div>
            </div>
            <!-- Info -->
            <div class="flex-1 min-w-0">
                <p class="font-bold text-sm truncate" x-text="statusText"></p>
                <p class="text-xs opacity-80 truncate" x-text="ride ? ride.dropoff_location : ''"></p>
            </div>
            <!-- Quick Actions -->
            <div class="flex items-center gap-2 shrink-0">
                <span class="text-[11px] font-bold bg-white/20 px-2 py-1 rounded-lg hidden sm:inline" x-text="expanded ? 'Hide' : 'Details'"></span>
                <svg class="w-5 h-5 opacity-80 transition-transform duration-200" :class="expanded ? 'rotate-90' : ''" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg>
                <button @click.stop="cancelRide()" title="Cancel Ride" class="w-7 h-7 rounded-full bg-black/20 hover:bg-black/40 flex items-center justify-center text-xs font-bold transition-colors">
                    ✕
                </button>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('ongoingRide', () => ({
            ride: null,
            expanded: false,
            timer: null,
            boostFare: '',
            boosting: false,
            boostError: '',
            boostSuccess: false,
            cancelling: false,
            get statusText() {
                if (!this.ride) return '';
                const s = this.ride.status;
                const d = this.ride.driver_name || 'Driver';
                if (s === 'pending') return '🔍 Looking for drivers...';
                if (s === 'accepted') return `✓ ${d} accepted your ride`;
                if (s === 'en_route') return `🚗 ${d} is on the way`;
                if (s === 'arrived') return `📍 ${d} has arrived`;
                if (s === 'in_progress') return '⚡ Trip in progress';
                return '';
            },
            init() {
                this.check();
                this.timer = setInterval(() => this.check(), 8000);
            },
            async check() {
                try {
                    const res = await fetch('/api/user/ongoing-ride');
                    if (res.ok) {
                        const data = await res.json();
                        this.ride = data.ride || null;
                        if (this.ride && !this.boostFare) {
                            this.boostFare = (parseFloat(this.ride.fare) + 5).toFixed(2);
                        }
                    }
                } catch(e) {}
            },
            async submitBoost() {
                if (!this.ride) return;
                this.boosting = true;
                this.boostError = '';
                this.boostSuccess = false;
                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.content;
                    const res = await fetch(`/api/ride/${this.ride.id}/boost-fare`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                        body: JSON.stringify({ fare: parseFloat(this.boostFare) })
                    });
                    const data = await res.json();
                    if (res.ok) {
                        this.boostSuccess = true;
                        this.ride.fare = data.new_fare;
                        this.boostFare = (parseFloat(data.new_fare) + 5).toFixed(2);
                        setTimeout(() => this.boostSuccess = false, 4000);
                    } else {
                        this.boostError = data.error || 'Failed to boost fare';
                    }
                } catch(e) { this.boostError = 'Network error'; }
                this.boosting = false;
            },
            async cancelRide() {
                if (!this.ride) return;
                if (!confirm('Cancel this ride request?')) return;
                this.cancelling = true;
                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.content;
                    const res = await fetch(`/api/ride/${this.ride.id}/cancel`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token }
                    });
                    if (res.ok) {
                        this.ride = null;
                        this.expanded = false;
                    }
                } catch(e) {}
                this.cancelling = false;
            }
        }));
    });
    </script>
    @endauth

    {{ $slot }}

    <!-- Footer -->
    <footer class="bg-[#0a0a0a] text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                <div class="col-span-1 md:col-span-1">
                    <a class="flex items-center gap-2 mb-6" href="/">
                        <img src="{{ asset('images/logo.png') }}" alt="RideMyCars Logo" class="h-16 md:h-[72px] w-auto mix-blend-multiply dark:mix-blend-normal dark:bg-white dark:rounded-xl dark:p-1">
                    </a>
                    <p class="text-gray-400 dark:text-gray-500 text-sm leading-relaxed">
                        Your unified mobility platform. Book rides, rent vehicles, and hire professional drivers — all in one place.
                    </p>
                    <div class="flex gap-4 mt-6">
                        <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-gray-400 dark:text-gray-500 hover:text-white transition-colors cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"/></svg>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-gray-400 dark:text-gray-500 hover:text-white transition-colors cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-gray-400 dark:text-gray-500 hover:text-white transition-colors cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-gray-400 dark:text-gray-500 hover:text-white transition-colors cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/></svg>
                        </div>
                    </div>
                </div>
                <div>
                    <h4 class="font-bold text-xs tracking-widest text-gray-500 dark:text-gray-400 uppercase mb-6">Services</h4>
                    <ul class="space-y-4">
                        <li><a href="/ride" class="text-sm text-gray-400 dark:text-gray-500 hover:text-white transition-colors">Book a Ride</a></li>
                        <li><a href="/rent" class="text-sm text-gray-400 dark:text-gray-500 hover:text-white transition-colors">Rent a Vehicle</a></li>
                        <li><a href="/hire-driver" class="text-sm text-gray-400 dark:text-gray-500 hover:text-white transition-colors">Hire a Driver</a></li>
                        <li><a href="/pricing" class="text-sm text-gray-400 dark:text-gray-500 hover:text-white transition-colors">Pricing</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-xs tracking-widest text-gray-500 dark:text-gray-400 uppercase mb-6">Company</h4>
                    <ul class="space-y-4">
                        <li><a href="/about" class="text-sm text-gray-400 dark:text-gray-500 hover:text-white transition-colors">About Us</a></li>
                        <li><a href="/safety" class="text-sm text-gray-400 dark:text-gray-500 hover:text-white transition-colors">Safety</a></li>
                        <li><a href="/blog" class="text-sm text-gray-400 dark:text-gray-500 hover:text-white transition-colors">Blog</a></li>
                        <li><a href="/careers" class="text-sm text-gray-400 dark:text-gray-500 hover:text-white transition-colors">Careers</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-xs tracking-widest text-gray-500 dark:text-gray-400 uppercase mb-6">Partners</h4>
                    <ul class="space-y-4">
                        <li><a href="/become-driver" class="text-sm text-gray-400 dark:text-gray-500 hover:text-white transition-colors">Become a Driver</a></li>
                        <li><a href="/list-vehicle" class="text-sm text-gray-400 dark:text-gray-500 hover:text-white transition-colors">List Your Vehicle</a></li>
                        <li><a href="/partner" class="text-sm text-gray-400 dark:text-gray-500 hover:text-white transition-colors">Partner Portal</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-xs tracking-widest text-gray-500 dark:text-gray-400 uppercase mb-6">Support</h4>
                    <ul class="space-y-4">
                        <li><a href="/help" class="text-sm text-gray-400 dark:text-gray-500 hover:text-white transition-colors">Help Center</a></li>
                        <li><a href="/contact" class="text-sm text-gray-400 dark:text-gray-500 hover:text-white transition-colors">Contact Us</a></li>
                        <li><a href="/faq" class="text-sm text-gray-400 dark:text-gray-500 hover:text-white transition-colors">FAQ</a></li>
                        <li><a href="/support" class="text-sm text-gray-400 dark:text-gray-500 hover:text-white transition-colors">Support Tickets</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-xs tracking-widest text-gray-500 dark:text-gray-400 uppercase mb-6">Legal</h4>
                    <ul class="space-y-4">
                        <li><a href="/terms" class="text-sm text-gray-400 dark:text-gray-500 hover:text-white transition-colors">Terms of Service</a></li>
                        <li><a href="/privacy" class="text-sm text-gray-400 dark:text-gray-500 hover:text-white transition-colors">Privacy Policy</a></li>
                        <li><a href="/refund" class="text-sm text-gray-400 dark:text-gray-500 hover:text-white transition-colors">Refund Policy</a></li>
                        <li><a href="/cookie" class="text-sm text-gray-400 dark:text-gray-500 hover:text-white transition-colors">Cookie Policy</a></li>
                    </ul>
                </div>
            </div>

            <!-- App Downloads -->
            <div class="py-12 flex flex-col md:flex-row items-center justify-center gap-6 border-t border-gray-100 dark:border-white/10 mt-12">
                <span class="text-gray-500 dark:text-gray-400 font-medium">Available on</span>
                <div class="flex items-center gap-4">
                    <!-- App Store -->
                    <a href="{{ site_setting('driver.ios_url', '#') }}" class="flex items-center gap-3 px-5 py-2.5 bg-white/5 border border-white/10 text-white rounded-xl hover:bg-white/10 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M16.6 14c-.1-.1-1.5-2.5-1.5-5.1 0-3.2 2.7-4.8 2.8-4.9-1.5-2.2-3.8-2.5-4.6-2.5-1.9-.2-3.8 1.1-4.8 1.1-1 0-2.6-1-4.1-1-2 0-3.9 1.1-4.9 2.9-2 3.6-.5 8.9 1.5 11.8 1 1.4 2.1 3 3.6 2.9 1.5-.1 2.1-1 3.9-1s2.3.9 3.9 1c1.6.1 2.6-1.5 3.5-2.9 1.1-1.7 1.6-3.3 1.6-3.4-.1-.1-2.4-.9-2.5-3.4zM11.9 4.8c.8-1 1.4-2.4 1.3-3.8-1.2.1-2.7.8-3.5 1.8-.7.9-1.4 2.3-1.2 3.8 1.3.1 2.6-.7 3.4-1.8z"/></svg>
                        <div class="flex flex-col">
                            <span class="text-[10px] leading-tight text-gray-400">Download on the</span>
                            <span class="text-sm font-semibold leading-tight">App Store</span>
                        </div>
                    </a>
                    
                    <!-- Google Play -->
                    <a href="{{ asset('ridemycars.apk') }}" download class="flex items-center gap-3 px-5 py-2.5 bg-transparent border border-white/20 text-white rounded-xl hover:bg-white/5 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M17.5 13.9l-9-5.1c-.5-.3-1.1-.3-1.6 0-.5.3-.8.8-.8 1.4v10.1c0 .6.3 1.1.8 1.4.3.1.6.2.8.2s.6-.1.8-.2l9-5.1c.5-.3.8-.8.8-1.4s-.3-1-.8-1.3zm-8.8-3.4l6.1 3.5-3.1 1.8-3-3.5v-1.8zm0 7.8v-1.8l3 3.5-3 1.8v-3.5zm4.7-.9l-1.1-.6 1.1-1.3 2 2.3-2-.4z"/></svg>
                        <div class="flex flex-col">
                            <span class="text-[10px] leading-tight text-gray-400">GET IT ON</span>
                            <span class="text-sm font-semibold leading-tight">Google Play</span>
                        </div>
                    </a>
                </div>
            </div>
            
            <!-- Bottom Footer -->
            <div class="pt-8 border-t border-gray-100 dark:border-white/10 flex flex-col md:flex-row justify-between items-center gap-6">
                
                <div class="flex items-center gap-6">
                    <a href="mailto:{{ site_setting('footer.support_email', 'support@ridemycars.com') }}" class="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        {{ site_setting('footer.support_email', 'support@ridemycars.com') }}
                    </a>
                    <a href="tel:{{ site_setting('footer.support_phone', '+1 800 123 4567') }}" class="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        {{ site_setting('footer.support_phone', '+1 800 123 4567') }}
                    </a>
                    <span class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        {{ site_setting('footer.location', 'San Francisco, CA') }}
                    </span>
                </div>
            </div>
            
            <div class="mt-8 pt-8 border-t border-gray-100 dark:border-white/10 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-sm text-gray-400 dark:text-gray-500 text-center md:text-left">{{ site_setting('footer.copyright', '© 2026 RideMyCars. All rights reserved.') }}</p>
            </div>
        </div>
    </footer>

</body>
</html>
