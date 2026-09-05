<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ darkMode: localStorage.getItem('theme') === 'dark' }" 
      x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))" 
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'RideMyCars' }}</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <!-- Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <!-- Alpine.js & Instant Page Prefetching -->
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        header.sticky {
            position: sticky !important;
            top: 0 !important;
            z-index: 100 !important;
        }
        .dropdown-menu-card {
            background-color: #ffffff !important;
            opacity: 1 !important;
            z-index: 110 !important;
        }
        .dark .dropdown-menu-card {
            background-color: #121212 !important;
        }
        .glow-ambient-amber {
            background: radial-gradient(circle, rgba(249, 197, 42, 0.22) 0%, rgba(249, 115, 22, 0.12) 45%, transparent 70%);
        }
        .glow-ambient-blue {
            background: radial-gradient(circle, rgba(59, 130, 246, 0.22) 0%, rgba(37, 99, 235, 0.10) 45%, transparent 70%);
        }
        .glow-ambient-emerald {
            background: radial-gradient(circle, rgba(34, 197, 94, 0.20) 0%, rgba(16, 185, 129, 0.10) 45%, transparent 70%);
        }
        .glow-ambient-purple {
            background: radial-gradient(circle, rgba(168, 85, 247, 0.22) 0%, rgba(147, 51, 234, 0.10) 45%, transparent 70%);
        }
        .glass-colorful {
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
    </style>
    <script src="{{ asset('js/countries-data.js') }}"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/instant.page@5.2.0/instantpage.min.js" type="module"></script>
    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Tailwind CSS Fallback CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        darkMode: 'class',
        theme: {
          extend: {
            fontFamily: {
              sans: ['"Plus Jakarta Sans"', 'Inter', 'system-ui', '-apple-system', 'sans-serif'],
            },
            colors: {
              brand: {
                50: '#fff8df',
                100: '#ffecb0',
                200: '#ffe183',
                300: '#ffd453',
                400: '#fbc933',
                500: '#f9c52a',
                600: '#dbab21',
                700: '#a78117',
                800: '#886711',
                900: '#71540a',
                950: '#451a03',
              }
            }
          }
        }
      }
    </script>


    @if(config('services.firebase.api_key'))
    <!-- Firebase SDK (Web) -->
    <script src="https://www.gstatic.com/firebasejs/10.9.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.9.0/firebase-messaging-compat.js"></script>
    <script>
        window.firebaseConfig = {
            apiKey: "{{ config('services.firebase.api_key') }}",
            authDomain: "{{ config('services.firebase.auth_domain') }}",
            projectId: "{{ config('services.firebase.project_id') }}",
            storageBucket: "{{ config('services.firebase.storage_bucket') }}",
            messagingSenderId: "{{ config('services.firebase.messaging_sender_id') }}",
            appId: "{{ config('services.firebase.app_id') }}",
            measurementId: "{{ config('services.firebase.measurement_id') }}"
        };
        try {
            if (!firebase.apps.length) {
                firebase.initializeApp(window.firebaseConfig);
            }
        } catch(e) { console.warn('Firebase Web init:', e); }
    </script>
    @endif
    {{ $head ?? '' }}
</head>
<body class="font-sans antialiased bg-[#fafafa] dark:bg-[#0a0a0a] text-gray-900 dark:text-white min-h-screen flex flex-col transition-colors duration-200 {{ $theme ?? '' }}">
    
    <!-- Header -->
    <header x-data="{ mobileMenuOpen: false }" class="sticky top-0 left-0 right-0 z-[100] bg-white dark:bg-[#0a0a0a] border-b border-gray-100 dark:border-white/10 transition-colors duration-200">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex h-20 items-center justify-between">
                <!-- Logo -->
                <a class="flex items-center gap-2 group shrink-0" href="/">
                    <img src="{{ asset('images/logo.png') }}" alt="RideMyCars Logo" class="h-14 w-auto object-contain">
                </a>
                
                <!-- Desktop Navigation Menu (Perfect Alignment) -->
                <div class="hidden lg:flex items-center gap-1 xl:gap-2">
                    @auth
                        @if(auth()->user()->role === 'driver')
                            <a class="text-sm font-semibold transition-all whitespace-nowrap px-3.5 py-2 rounded-full {{ request()->is('/') ? 'text-brand-500 dark:text-brand-400 bg-brand-500/10 font-bold' : 'text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/10' }}" href="/">Home</a>
                            <a class="text-sm font-semibold transition-all whitespace-nowrap px-3.5 py-2 rounded-full {{ request()->is('driver/dashboard*') ? 'text-brand-500 dark:text-brand-400 bg-brand-500/10 font-bold' : 'text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/10' }}" href="/driver/dashboard">Dashboard</a>
                            <a class="text-sm font-semibold transition-all whitespace-nowrap px-3.5 py-2 rounded-full {{ request()->is('ride*') ? 'text-brand-500 dark:text-brand-400 bg-brand-500/10 font-bold' : 'text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/10' }}" href="/ride">Ride</a>
                            <a class="text-sm font-semibold transition-all whitespace-nowrap px-3.5 py-2 rounded-full {{ request()->is('wallet*') ? 'text-brand-500 dark:text-brand-400 bg-brand-500/10 font-bold' : 'text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/10' }}" href="/wallet">Earnings</a>
                            <a class="text-sm font-semibold transition-all whitespace-nowrap px-3.5 py-2 rounded-full {{ request()->is('pricing*') ? 'text-brand-500 dark:text-brand-400 bg-brand-500/10 font-bold' : 'text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/10' }}" href="/pricing">Pricing</a>
                        @else
                            <!-- Home -->
                            <a class="text-sm font-semibold transition-all whitespace-nowrap px-3.5 py-2 rounded-full {{ request()->is('/') ? 'text-brand-500 dark:text-brand-400 bg-brand-500/10 font-bold' : 'text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/10' }}" href="/">Home</a>
                            
                            <!-- Services Dropdown with Rich Icons -->
                            <div x-data="{ open: false }" class="relative" @click.away="open = false" @keydown.escape="open = false">
                                <button @click="open = !open" 
                                        class="text-sm font-semibold transition-all flex items-center gap-1.5 px-3.5 py-2 rounded-full whitespace-nowrap {{ request()->is('ride*') || request()->is('rent*') || request()->is('hire-driver*') || request()->is('driver-booking*') || request()->is('delivery*') ? 'text-brand-500 dark:text-brand-400 bg-brand-500/10 font-bold' : 'text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/10' }}">
                                    <span>Services</span>
                                    <svg :class="{'rotate-180': open}" class="transition-transform duration-200 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m6 9 6 6 6-6"/>
                                    </svg>
                                </button>
                                
                                <div x-show="open" 
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                     class="dropdown-menu-card absolute top-full left-0 mt-2 w-[340px] bg-white dark:bg-[#121212] border border-gray-100 dark:border-white/10 shadow-2xl rounded-2xl p-2.5 z-[110] divide-y divide-gray-100 dark:divide-white/5" 
                                     style="display: none;">
                                    
                                    <div class="space-y-1 pb-2">
                                        <!-- Ride -->
                                        <a href="/ride" class="group flex items-center gap-3.5 p-2.5 rounded-xl transition-all {{ request()->is('ride*') ? 'bg-brand-500/10 text-brand-600 dark:text-brand-400' : 'text-gray-700 dark:text-gray-300 hover:bg-brand-500/10 hover:text-brand-600 dark:hover:text-brand-400' }}">
                                            <div class="w-10 h-10 rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 shadow-sm transition-transform group-hover:scale-105">
                                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/>
                                                    <circle cx="7" cy="17" r="2"/>
                                                    <path d="M9 17h6"/>
                                                    <circle cx="17" cy="17" r="2"/>
                                                </svg>
                                            </div>
                                            <div class="flex flex-col text-left min-w-0">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-bold text-sm text-gray-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-400">Ride</span>
                                                    <span class="text-[9px] font-black uppercase px-1.5 py-0.2 rounded bg-amber-500/15 text-amber-700 dark:text-amber-300">Instant</span>
                                                </div>
                                                <span class="text-[11px] text-gray-500 dark:text-gray-400 truncate">Book city rides & on-demand trips</span>
                                            </div>
                                        </a>

                                        <!-- Rent Vehicle -->
                                        <a href="/rent" class="group flex items-center gap-3.5 p-2.5 rounded-xl transition-all {{ request()->is('rent*') ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:bg-blue-500/10 hover:text-blue-600 dark:hover:text-blue-400' }}">
                                            <div class="w-10 h-10 rounded-xl bg-blue-500/15 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 shadow-sm transition-transform group-hover:scale-105">
                                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="8" cy="15" r="5"/>
                                                    <path d="m11.5 11.5 7-7"/>
                                                    <path d="m16 4 2 2"/>
                                                    <path d="m19 7 2 2"/>
                                                    <circle cx="8" cy="15" r="2"/>
                                                </svg>
                                            </div>
                                            <div class="flex flex-col text-left min-w-0">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-bold text-sm text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400">Rent Vehicle</span>
                                                    <span class="text-[9px] font-black uppercase px-1.5 py-0.2 rounded bg-blue-500/15 text-blue-700 dark:text-blue-300">Fleet</span>
                                                </div>
                                                <span class="text-[11px] text-gray-500 dark:text-gray-400 truncate">Self-drive & luxury fleet rentals</span>
                                            </div>
                                        </a>

                                        <!-- Hire Driver -->
                                        <a href="/hire-driver" class="group flex items-center gap-3.5 p-2.5 rounded-xl transition-all {{ request()->is('hire-driver*') || request()->is('driver-booking*') ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'text-gray-700 dark:text-gray-300 hover:bg-emerald-500/10 hover:text-emerald-600 dark:hover:text-emerald-400' }}">
                                            <div class="w-10 h-10 rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 shadow-sm transition-transform group-hover:scale-105">
                                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                                                    <circle cx="12" cy="7" r="4"/>
                                                    <path d="m9 11 2 2 4-4"/>
                                                </svg>
                                            </div>
                                            <div class="flex flex-col text-left min-w-0">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-bold text-sm text-gray-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400">Hire Driver</span>
                                                    <span class="text-[9px] font-black uppercase px-1.5 py-0.2 rounded bg-emerald-500/15 text-emerald-700 dark:text-emerald-300">Verified</span>
                                                </div>
                                                <span class="text-[11px] text-gray-500 dark:text-gray-400 truncate">Private chauffeurs for your car</span>
                                            </div>
                                        </a>

                                        <!-- Package Delivery -->
                                        <a href="/delivery" class="group flex items-center gap-3.5 p-2.5 rounded-xl transition-all {{ request()->is('delivery*') ? 'bg-purple-500/10 text-purple-600 dark:text-purple-400' : 'text-gray-700 dark:text-gray-300 hover:bg-purple-500/10 hover:text-purple-600 dark:hover:text-purple-400' }}">
                                            <div class="w-10 h-10 rounded-xl bg-purple-500/15 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0 shadow-sm transition-transform group-hover:scale-105">
                                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="m7.5 4.27 9 5.15"/>
                                                    <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                                                    <path d="m3.3 7 8.7 5 8.7-5"/>
                                                    <path d="M12 22V12"/>
                                                </svg>
                                            </div>
                                            <div class="flex flex-col text-left min-w-0">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-bold text-sm text-gray-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400">Package Delivery</span>
                                                    <span class="text-[9px] font-black uppercase px-1.5 py-0.2 rounded bg-purple-500/15 text-purple-700 dark:text-purple-300">Express</span>
                                                </div>
                                                <span class="text-[11px] text-gray-500 dark:text-gray-400 truncate">Door-to-door courier dispatch</span>
                                            </div>
                                        </a>

                                        <!-- Track My Ride -->
                                        <a href="/ride/track" class="group flex items-center gap-3.5 p-2.5 rounded-xl transition-all {{ request()->is('ride/track*') ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'text-gray-700 dark:text-gray-300 hover:bg-emerald-500/10 hover:text-emerald-600 dark:hover:text-emerald-400' }}">
                                            <div class="w-10 h-10 rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 shadow-sm transition-transform group-hover:scale-105">
                                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10"/>
                                                    <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/>
                                                </svg>
                                            </div>
                                            <div class="flex flex-col text-left min-w-0">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-bold text-sm text-gray-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400">Track My Ride</span>
                                                    <span class="text-[9px] font-black uppercase px-1.5 py-0.2 rounded bg-emerald-500/15 text-emerald-700 dark:text-emerald-300">GPS Live</span>
                                                </div>
                                                <span class="text-[11px] text-gray-500 dark:text-gray-400 truncate">Live GPS ride tracking for riders & guests</span>
                                            </div>
                                        </a>
                                    </div>
                                    
                                    <div class="pt-2 px-1 flex items-center justify-between">
                                        <a href="/pricing" class="text-xs font-bold text-brand-500 hover:text-brand-600 dark:hover:text-brand-400 transition-colors flex items-center gap-1">
                                            <span>Rates & Calculator</span>
                                            <span>→</span>
                                        </a>
                                        <a href="/onboarding" class="text-[11px] font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors">
                                            How It Works
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Memberships -->
                            <a class="text-sm font-semibold transition-all whitespace-nowrap px-3.5 py-2 rounded-full {{ request()->is('membership*') ? 'text-brand-500 dark:text-brand-400 bg-brand-500/10 font-bold' : 'text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/10' }}" href="/membership">Memberships</a>
                            
                            <!-- Apps Dropdown -->
                            <div x-data="{ open: false }" class="relative" @click.away="open = false" @keydown.escape="open = false">
                                <button @click="open = !open" class="text-sm font-semibold transition-all flex items-center gap-1.5 px-3.5 py-2 rounded-full whitespace-nowrap {{ request()->is('apps*') || request()->is('download*') ? 'text-brand-500 dark:text-brand-400 bg-brand-500/10 font-bold' : 'text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/10' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-brand-500"><rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><path d="M12 18h.01"/></svg>
                                    <span>Apps</span> 
                                    <svg :class="{'rotate-180': open}" class="transition-transform duration-200 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                </button>
                                <div x-show="open" 
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                     class="dropdown-menu-card absolute top-full left-0 mt-2 w-64 bg-white dark:bg-[#121212] border border-gray-100 dark:border-white/10 shadow-2xl rounded-2xl p-2 z-[110] divide-y divide-gray-100 dark:divide-white/5" 
                                     style="display: none;">
                                    <div class="space-y-1 pb-2">
                                        <a href="/apps#rider-app" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-brand-500/10 hover:text-brand-600 dark:hover:text-brand-400 transition-colors">
                                            <span class="w-8 h-8 rounded-lg bg-brand-500/15 text-brand-500 flex items-center justify-center shrink-0 text-base">🚗</span>
                                            <div class="flex flex-col text-left min-w-0">
                                                <span class="font-bold text-gray-900 dark:text-white">Rider App</span>
                                                <span class="text-[10px] text-gray-500 dark:text-gray-400 truncate">Book rides & chauffeurs</span>
                                            </div>
                                        </a>
                                        <a href="/apps#driver-app" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 transition-colors">
                                            <span class="w-8 h-8 rounded-lg bg-amber-500/15 text-amber-500 flex items-center justify-center shrink-0 text-base">🚕</span>
                                            <div class="flex flex-col text-left min-w-0">
                                                <span class="font-bold text-gray-900 dark:text-white">Driver App</span>
                                                <span class="text-[10px] text-gray-500 dark:text-gray-400 truncate">Earn & keep 90%</span>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="pt-2">
                                        <a href="/apps" class="block px-3 py-1.5 text-center text-xs font-bold text-brand-500 hover:text-brand-600 dark:hover:text-brand-400">
                                            Downloads & QR Hub →
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Company Dropdown -->
                            <div x-data="{ open: false }" class="relative" @click.away="open = false" @keydown.escape="open = false">
                                <button @click="open = !open" class="text-sm font-semibold transition-all flex items-center gap-1.5 px-3.5 py-2 rounded-full whitespace-nowrap {{ request()->is('about*') || request()->is('safety*') || request()->is('become-*') || request()->is('blogs*') ? 'text-brand-500 dark:text-brand-400 bg-brand-500/10 font-bold' : 'text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/10' }}">
                                    <span>Company</span> 
                                    <svg :class="{'rotate-180': open}" class="transition-transform duration-200 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                </button>
                                <div x-show="open" 
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                     class="dropdown-menu-card absolute top-full left-0 mt-2 w-60 bg-white dark:bg-[#121212] border border-gray-100 dark:border-white/10 shadow-2xl rounded-2xl p-2 z-[110]" 
                                     style="display: none;">
                                    <div class="space-y-1">
                                        <a href="/about" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl {{ request()->is('about*') ? 'bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white' }} transition-colors">
                                            <span class="w-6 h-6 rounded-lg bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-gray-300 flex items-center justify-center shrink-0 text-xs">🏢</span>
                                            <span class="flex-1 truncate">About Us</span>
                                        </a>
                                        <a href="/safety" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl {{ request()->is('safety*') ? 'bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white' }} transition-colors">
                                            <span class="w-6 h-6 rounded-lg bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 text-xs">🛡️</span>
                                            <span class="flex-1 truncate">Safety & Trust</span>
                                        </a>
                                        <a href="/become-driver" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl {{ request()->is('become-driver*') ? 'bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white' }} transition-colors">
                                            <span class="w-6 h-6 rounded-lg bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 text-sm">👨‍✈️</span>
                                            <span class="flex-1 truncate">Become a Driver</span>
                                            <span class="text-[9px] font-black uppercase px-1.5 py-0.2 rounded bg-amber-500/15 text-amber-700 dark:text-amber-300">Earn</span>
                                        </a>
                                        <a href="/become-owner" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl {{ request()->is('become-owner*') ? 'bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white' }} transition-colors">
                                            <span class="w-6 h-6 rounded-lg bg-blue-500/15 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 text-sm">🔑</span>
                                            <span class="flex-1 truncate">List Your Vehicle</span>
                                            <span class="text-[9px] font-black uppercase px-1.5 py-0.2 rounded bg-blue-500/15 text-blue-700 dark:text-blue-300">Host</span>
                                        </a>
                                        <a href="/blogs" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl {{ request()->is('blogs*') ? 'bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white' }} transition-colors">
                                            <span class="w-6 h-6 rounded-lg bg-purple-500/15 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0 text-xs">📰</span>
                                            <span class="flex-1 truncate">Blogs & News</span>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Pricing -->
                            <a class="text-sm font-semibold transition-all whitespace-nowrap px-3.5 py-2 rounded-full {{ request()->is('pricing*') ? 'text-brand-500 dark:text-brand-400 bg-brand-500/10 font-bold' : 'text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/10' }}" href="/pricing">Pricing</a>
                        @endif
                    @else
                        <!-- Guest Navigation: Home, Services Dropdown, Memberships, Apps, Company, Pricing -->
                        <a class="text-sm font-semibold transition-all whitespace-nowrap px-3.5 py-2 rounded-full {{ request()->is('/') ? 'text-brand-500 dark:text-brand-400 bg-brand-500/10 font-bold' : 'text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/10' }}" href="/">Home</a>
                        
                        <!-- Services Dropdown with Rich Icons -->
                        <div x-data="{ open: false }" class="relative" @click.away="open = false" @keydown.escape="open = false">
                            <button @click="open = !open" 
                                    class="text-sm font-semibold transition-all flex items-center gap-1.5 px-3.5 py-2 rounded-full whitespace-nowrap {{ request()->is('ride*') || request()->is('rent*') || request()->is('hire-driver*') || request()->is('driver-booking*') || request()->is('delivery*') ? 'text-brand-500 dark:text-brand-400 bg-brand-500/10 font-bold' : 'text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/10' }}">
                                <span>Services</span>
                                <svg :class="{'rotate-180': open}" class="transition-transform duration-200 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m6 9 6 6 6-6"/>
                                </svg>
                            </button>
                            
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                 class="dropdown-menu-card absolute top-full left-0 mt-2 w-[340px] bg-white dark:bg-[#121212] border border-gray-100 dark:border-white/10 shadow-2xl rounded-2xl p-2.5 z-[110] divide-y divide-gray-100 dark:divide-white/5" 
                                 style="display: none;">
                                
                                <div class="space-y-1 pb-2">
                                    <!-- Ride -->
                                    <a href="/ride" class="group flex items-center gap-3.5 p-2.5 rounded-xl transition-all {{ request()->is('ride*') ? 'bg-brand-500/10 text-brand-600 dark:text-brand-400' : 'text-gray-700 dark:text-gray-300 hover:bg-brand-500/10 hover:text-brand-600 dark:hover:text-brand-400' }}">
                                        <div class="w-10 h-10 rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 shadow-sm transition-transform group-hover:scale-105">
                                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/>
                                                <circle cx="7" cy="17" r="2"/>
                                                <path d="M9 17h6"/>
                                                <circle cx="17" cy="17" r="2"/>
                                            </svg>
                                        </div>
                                        <div class="flex flex-col text-left min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-bold text-sm text-gray-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-400">Ride</span>
                                                <span class="text-[9px] font-black uppercase px-1.5 py-0.2 rounded bg-amber-500/15 text-amber-700 dark:text-amber-300">Instant</span>
                                            </div>
                                            <span class="text-[11px] text-gray-500 dark:text-gray-400 truncate">Book city rides & on-demand trips</span>
                                        </div>
                                    </a>

                                    <!-- Rent Vehicle -->
                                    <a href="/rent" class="group flex items-center gap-3.5 p-2.5 rounded-xl transition-all {{ request()->is('rent*') ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:bg-blue-500/10 hover:text-blue-600 dark:hover:text-blue-400' }}">
                                        <div class="w-10 h-10 rounded-xl bg-blue-500/15 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 shadow-sm transition-transform group-hover:scale-105">
                                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="8" cy="15" r="5"/>
                                                <path d="m11.5 11.5 7-7"/>
                                                <path d="m16 4 2 2"/>
                                                <path d="m19 7 2 2"/>
                                                <circle cx="8" cy="15" r="2"/>
                                            </svg>
                                        </div>
                                        <div class="flex flex-col text-left min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-bold text-sm text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400">Rent Vehicle</span>
                                                <span class="text-[9px] font-black uppercase px-1.5 py-0.2 rounded bg-blue-500/15 text-blue-700 dark:text-blue-300">Fleet</span>
                                            </div>
                                            <span class="text-[11px] text-gray-500 dark:text-gray-400 truncate">Self-drive & luxury fleet rentals</span>
                                        </div>
                                    </a>

                                    <!-- Hire Driver -->
                                    <a href="/hire-driver" class="group flex items-center gap-3.5 p-2.5 rounded-xl transition-all {{ request()->is('hire-driver*') || request()->is('driver-booking*') ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'text-gray-700 dark:text-gray-300 hover:bg-emerald-500/10 hover:text-emerald-600 dark:hover:text-emerald-400' }}">
                                        <div class="w-10 h-10 rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 shadow-sm transition-transform group-hover:scale-105">
                                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                                                <circle cx="12" cy="7" r="4"/>
                                                <path d="m9 11 2 2 4-4"/>
                                            </svg>
                                        </div>
                                        <div class="flex flex-col text-left min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-bold text-sm text-gray-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400">Hire Driver</span>
                                                <span class="text-[9px] font-black uppercase px-1.5 py-0.2 rounded bg-emerald-500/15 text-emerald-700 dark:text-emerald-300">Verified</span>
                                            </div>
                                            <span class="text-[11px] text-gray-500 dark:text-gray-400 truncate">Private chauffeurs for your car</span>
                                        </div>
                                    </a>

                                    <!-- Package Delivery -->
                                    <a href="/delivery" class="group flex items-center gap-3.5 p-2.5 rounded-xl transition-all {{ request()->is('delivery*') ? 'bg-purple-500/10 text-purple-600 dark:text-purple-400' : 'text-gray-700 dark:text-gray-300 hover:bg-purple-500/10 hover:text-purple-600 dark:hover:text-purple-400' }}">
                                        <div class="w-10 h-10 rounded-xl bg-purple-500/15 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0 shadow-sm transition-transform group-hover:scale-105">
                                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="m7.5 4.27 9 5.15"/>
                                                <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                                                <path d="m3.3 7 8.7 5 8.7-5"/>
                                                <path d="M12 22V12"/>
                                            </svg>
                                        </div>
                                        <div class="flex flex-col text-left min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-bold text-sm text-gray-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400">Package Delivery</span>
                                                <span class="text-[9px] font-black uppercase px-1.5 py-0.2 rounded bg-purple-500/15 text-purple-700 dark:text-purple-300">Express</span>
                                            </div>
                                            <span class="text-[11px] text-gray-500 dark:text-gray-400 truncate">Door-to-door courier dispatch</span>
                                        </div>
                                    </a>

                                    <!-- Track My Ride -->
                                    <a href="/ride/track" class="group flex items-center gap-3.5 p-2.5 rounded-xl transition-all {{ request()->is('ride/track*') ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'text-gray-700 dark:text-gray-300 hover:bg-emerald-500/10 hover:text-emerald-600 dark:hover:text-emerald-400' }}">
                                        <div class="w-10 h-10 rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 shadow-sm transition-transform group-hover:scale-105">
                                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"/>
                                                <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/>
                                            </svg>
                                        </div>
                                        <div class="flex flex-col text-left min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-bold text-sm text-gray-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400">Track My Ride</span>
                                                <span class="text-[9px] font-black uppercase px-1.5 py-0.2 rounded bg-emerald-500/15 text-emerald-700 dark:text-emerald-300">GPS Live</span>
                                            </div>
                                            <span class="text-[11px] text-gray-500 dark:text-gray-400 truncate">Live GPS ride tracking for riders & guests</span>
                                        </div>
                                    </a>
                                </div>
                                
                                <div class="pt-2 px-1 flex items-center justify-between">
                                    <a href="/pricing" class="text-xs font-bold text-brand-500 hover:text-brand-600 dark:hover:text-brand-400 transition-colors flex items-center gap-1">
                                        <span>Rates & Calculator</span>
                                        <span>→</span>
                                    </a>
                                    <a href="/onboarding" class="text-[11px] font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors">
                                        How It Works
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Memberships -->
                        <a class="text-sm font-semibold transition-all whitespace-nowrap px-3.5 py-2 rounded-full {{ request()->is('membership*') ? 'text-brand-500 dark:text-brand-400 bg-brand-500/10 font-bold' : 'text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/10' }}" href="/membership">Memberships</a>
                        
                        <!-- Apps Dropdown -->
                        <div x-data="{ open: false }" class="relative" @click.away="open = false" @keydown.escape="open = false">
                            <button @click="open = !open" class="text-sm font-semibold transition-all flex items-center gap-1.5 px-3.5 py-2 rounded-full whitespace-nowrap {{ request()->is('apps*') || request()->is('download*') ? 'text-brand-500 dark:text-brand-400 bg-brand-500/10 font-bold' : 'text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/10' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-brand-500"><rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><path d="M12 18h.01"/></svg>
                                <span>Apps</span> 
                                <svg :class="{'rotate-180': open}" class="transition-transform duration-200 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                            </button>
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                 class="dropdown-menu-card absolute top-full left-0 mt-2 w-64 bg-white dark:bg-[#121212] border border-gray-100 dark:border-white/10 shadow-2xl rounded-2xl p-2 z-[110] divide-y divide-gray-100 dark:divide-white/5" 
                                 style="display: none;">
                                <div class="space-y-1 pb-2">
                                    <a href="/apps#rider-app" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-brand-500/10 hover:text-brand-600 dark:hover:text-brand-400 transition-colors">
                                        <span class="w-8 h-8 rounded-lg bg-brand-500/15 text-brand-500 flex items-center justify-center shrink-0 text-base">🚗</span>
                                        <div class="flex flex-col text-left min-w-0">
                                            <span class="font-bold text-gray-900 dark:text-white">Rider App</span>
                                            <span class="text-[10px] text-gray-500 dark:text-gray-400 truncate">Book rides & chauffeurs</span>
                                        </div>
                                    </a>
                                    <a href="/apps#driver-app" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 transition-colors">
                                        <span class="w-8 h-8 rounded-lg bg-amber-500/15 text-amber-500 flex items-center justify-center shrink-0 text-base">🚕</span>
                                        <div class="flex flex-col text-left min-w-0">
                                            <span class="font-bold text-gray-900 dark:text-white">Driver App</span>
                                            <span class="text-[10px] text-gray-500 dark:text-gray-400 truncate">Earn & keep 90%</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="pt-2">
                                    <a href="/apps" class="block px-3 py-1.5 text-center text-xs font-bold text-brand-500 hover:text-brand-600 dark:hover:text-brand-400">
                                        Downloads & QR Hub →
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Company Dropdown -->
                        <div x-data="{ open: false }" class="relative" @click.away="open = false" @keydown.escape="open = false">
                            <button @click="open = !open" class="text-sm font-semibold transition-all flex items-center gap-1.5 px-3.5 py-2 rounded-full whitespace-nowrap {{ request()->is('about*') || request()->is('safety*') || request()->is('become-*') || request()->is('blogs*') ? 'text-brand-500 dark:text-brand-400 bg-brand-500/10 font-bold' : 'text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/10' }}">
                                <span>Company</span> 
                                <svg :class="{'rotate-180': open}" class="transition-transform duration-200 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                            </button>
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                 class="dropdown-menu-card absolute top-full left-0 mt-2 w-60 bg-white dark:bg-[#121212] border border-gray-100 dark:border-white/10 shadow-2xl rounded-2xl p-2 z-[110]" 
                                 style="display: none;">
                                <div class="space-y-1">
                                    <a href="/about" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl {{ request()->is('about*') ? 'bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white' }} transition-colors">
                                        <span class="w-6 h-6 rounded-lg bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-gray-300 flex items-center justify-center shrink-0 text-xs">🏢</span>
                                        <span class="flex-1 truncate">About Us</span>
                                    </a>
                                    <a href="/safety" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl {{ request()->is('safety*') ? 'bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white' }} transition-colors">
                                        <span class="w-6 h-6 rounded-lg bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 text-xs">🛡️</span>
                                        <span class="flex-1 truncate">Safety & Trust</span>
                                    </a>
                                    <a href="/become-driver" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl {{ request()->is('become-driver*') ? 'bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white' }} transition-colors">
                                        <span class="w-6 h-6 rounded-lg bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 text-sm">👨‍✈️</span>
                                        <span class="flex-1 truncate">Become a Driver</span>
                                        <span class="text-[9px] font-black uppercase px-1.5 py-0.2 rounded bg-amber-500/15 text-amber-700 dark:text-amber-300">Earn</span>
                                    </a>
                                    <a href="/become-owner" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl {{ request()->is('become-owner*') ? 'bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white' }} transition-colors">
                                        <span class="w-6 h-6 rounded-lg bg-blue-500/15 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 text-sm">🔑</span>
                                        <span class="flex-1 truncate">List Your Vehicle</span>
                                        <span class="text-[9px] font-black uppercase px-1.5 py-0.2 rounded bg-blue-500/15 text-blue-700 dark:text-blue-300">Host</span>
                                    </a>
                                    <a href="/blogs" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-xl {{ request()->is('blogs*') ? 'bg-brand-500/10 text-brand-600 dark:text-brand-400 font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white' }} transition-colors">
                                        <span class="w-6 h-6 rounded-lg bg-purple-500/15 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0 text-xs">📰</span>
                                        <span class="flex-1 truncate">Blogs & News</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Pricing -->
                        <a class="text-sm font-semibold transition-all whitespace-nowrap px-3.5 py-2 rounded-full {{ request()->is('pricing*') ? 'text-brand-500 dark:text-brand-400 bg-brand-500/10 font-bold' : 'text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/10' }}" href="/pricing">Pricing</a>
                    @endauth
                </div>
                
                <!-- Actions -->
                <div class="flex items-center gap-2 sm:gap-3 lg:gap-4 shrink-0">
                    <button @click="darkMode = !darkMode" class="w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 dark:bg-white/5 dark:hover:bg-white/10 flex items-center justify-center transition-all text-gray-600 dark:text-gray-300 border border-gray-200/60 dark:border-white/10 shrink-0" aria-label="Toggle theme">
                        <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                        <svg x-show="darkMode" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                    </button>
                    @auth
                    <!-- Notification Bell -->
                    <div x-data="notificationCenter()" x-init="init()" class="relative" @click.away="open = false">
                        <!-- Bell Button with Proper Badge -->
                        <button @click="toggleOpen()" class="relative w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 dark:bg-white/5 dark:hover:bg-white/10 flex items-center justify-center transition-all text-gray-600 dark:text-gray-300 border border-gray-200/60 dark:border-white/10 focus:outline-none shrink-0" aria-label="Notifications">
                            <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                                <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
                            </svg>
                            
                            <!-- Unread Badge with Bold Red Text -->
                            <template x-if="unreadCount > 0">
                                <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 bg-white dark:bg-gray-900 border-2 border-red-500 text-red-600 dark:text-red-400 text-[10px] font-black rounded-full flex items-center justify-center shadow-md pointer-events-none" 
                                      style="color: #dc2626 !important; font-weight: 800 !important; border-color: #ef4444 !important;"
                                      x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
                            </template>
                        </button>

                        <!-- Notification Dropdown Panel (Styled exactly like User Menu) -->
                        <div x-show="open" 
                             x-transition
                             class="dropdown-menu-card absolute top-full -right-[52px] sm:right-0 mt-2.5 w-[calc(100vw-32px)] sm:w-[380px] max-w-[380px] bg-white dark:bg-[#121212] border border-gray-100 dark:border-gray-800 shadow-2xl rounded-2xl z-[110] overflow-hidden" 
                             style="display: none;">
                            
                            <!-- Header -->
                            <div class="px-4 py-3.5 bg-gray-50/80 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                                <div class="flex items-center gap-2 min-w-0">
                                    <h3 class="font-bold text-sm text-gray-900 dark:text-white shrink-0">Notifications</h3>
                                    <template x-if="unreadCount > 0">
                                        <span class="text-[11px] font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 px-2 py-0.5 rounded-full whitespace-nowrap" x-text="unreadCount + ' new'"></span>
                                    </template>
                                </div>
                                <div class="flex items-center gap-2.5 shrink-0">
                                    <template x-if="unreadCount > 0">
                                        <button @click="markAllAsRead()" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline whitespace-nowrap">Mark all read</button>
                                    </template>
                                    <template x-if="notifications.length > 0">
                                        <button @click="clearAll()" title="Clear all" class="text-gray-400 hover:text-red-500 transition-colors p-1 rounded hover:bg-gray-100 dark:hover:bg-white/10">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- Notification List -->
                            <div class="max-h-[390px] overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800">
                                <template x-if="notifications.length === 0">
                                    <div class="py-10 px-6 text-center">
                                        <div class="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-white/5 flex items-center justify-center mx-auto mb-3 text-indigo-500 dark:text-indigo-400 shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                                        </div>
                                        <p class="text-sm font-bold text-gray-900 dark:text-white">No notifications right now</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-[280px] mx-auto leading-relaxed">You're all caught up! Updates on rides, jobs, and logins will appear here.</p>
                                    </div>
                                </template>

                                <template x-for="item in notifications" :key="item.id">
                                    <div @click="handleItemClick(item)" 
                                         class="p-3.5 hover:bg-gray-50 dark:hover:bg-white/[0.04] cursor-pointer transition-colors flex items-start gap-3 relative"
                                         :class="{'bg-indigo-50/30 dark:bg-indigo-950/20': !item.is_read}">
                                        
                                        <!-- Icon Avatar -->
                                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 text-base shadow-sm font-bold"
                                             :class="{
                                                 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/60 dark:text-indigo-300': item.type === 'login' || item.type === 'ride_accepted',
                                                 'bg-blue-100 text-blue-600 dark:bg-blue-900/60 dark:text-blue-300': item.type === 'en_route',
                                                 'bg-amber-100 text-amber-600 dark:bg-amber-900/60 dark:text-amber-300': item.type === 'arrived',
                                                 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/60 dark:text-emerald-300': item.type === 'in_progress',
                                                 'bg-green-100 text-green-700 dark:bg-green-900/60 dark:text-green-300': item.type === 'completed',
                                                 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/60 dark:text-yellow-300': item.type === 'review',
                                                 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300': !['login','ride_accepted','en_route','arrived','in_progress','completed','review'].includes(item.type)
                                             }">
                                            <span x-text="getIcon(item.type)"></span>
                                        </div>

                                        <!-- Content -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-2 mb-0.5">
                                                <h4 class="text-xs font-bold text-gray-900 dark:text-white" x-text="item && item.title ? item.title.replace(/^[\p{So}\p{Sk}\p{Sm}\p{Sc}\p{P}\s]+/u, '').trim() : ''"></h4>
                                                <span class="text-[11px] font-medium text-gray-400 whitespace-nowrap shrink-0" x-text="item && item.time_ago ? item.time_ago : ''"></span>
                                            </div>
                                            <p class="text-xs text-gray-600 dark:text-gray-300 leading-snug break-words" x-text="item && item.message ? item.message : ''"></p>
                                        </div>

                                        <!-- Unread Dot -->
                                        <div class="shrink-0 flex items-center pt-1.5" x-show="item && !item.is_read">
                                            <span class="w-2 h-2 rounded-full bg-indigo-600 shadow-sm"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- In-App Floating Toast Notification (When a new event happens live) -->
                        <template x-if="latestToast && typeof latestToast === 'object'">
                            <div x-show="toastVisible"
                                 @click="handleItemClick(latestToast)"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 translate-y-[-20px] scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-200"
                                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave-end="opacity-0 translate-y-[-20px] scale-95"
                                 class="fixed top-24 right-4 sm:right-8 z-50 max-w-sm w-full bg-white dark:bg-gray-900 border border-indigo-200 dark:border-indigo-800 shadow-2xl rounded-2xl p-4 flex items-start gap-3 backdrop-blur-md cursor-pointer hover:shadow-indigo-500/10 transition-shadow">
                                <div class="w-9 h-9 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 text-base shadow-sm">
                                    <span x-text="latestToast.type === 'login' ? '👋' : (latestToast.type === 'completed' ? '🏁' : (latestToast.type === 'arrived' ? '📍' : (latestToast.type === 'in_progress' ? '🟢' : (latestToast.type === 'review' ? '★' : '🚗'))))"></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-xs font-bold text-gray-900 dark:text-white" x-text="latestToast.title || ''"></h4>
                                    <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5 line-clamp-2" x-text="latestToast.message || ''"></p>
                                    <div class="flex items-center gap-2 mt-2">
                                        <template x-if="typeof latestToast.link === 'string' && latestToast.link.trim() !== '' && !latestToast.link.includes('[native code]')">
                                            <span class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">View details →</span>
                                        </template>
                                        <button @click.stop="toastVisible = false" class="text-[11px] font-medium text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">Dismiss</button>
                                    </div>
                                </div>
                                <button @click.stop="toastVisible = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs p-1">✕</button>
                            </div>
                        </template>
                    </div>

                    <div x-data="{ userMenuOpen: false }" class="relative" @click.away="userMenuOpen = false">
                        <button @click="userMenuOpen = !userMenuOpen" class="flex items-center gap-2 focus:outline-none">
                            <div class="w-10 h-10 rounded-full bg-gray-200 text-gray-700 flex items-center justify-center font-bold text-lg border border-gray-300 shadow-sm">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                        </button>
                        
                        <div x-show="userMenuOpen" x-transition class="dropdown-menu-card absolute top-full right-0 mt-2.5 sm:mt-4 w-[calc(100vw-32px)] sm:w-[340px] max-w-[340px] bg-white dark:bg-[#121212] border border-gray-100 dark:border-gray-800 shadow-2xl rounded-2xl p-4 sm:p-5 z-[110]" style="display: none;">
                            
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
                    <a class="hidden sm:inline-block whitespace-nowrap text-sm font-semibold px-3.5 py-2 rounded-full transition-colors {{ request()->is('login*') ? 'text-brand-600 dark:text-brand-400 bg-brand-500/10 font-bold' : 'text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/10' }}" href="/login">Sign In</a>
                    <a class="whitespace-nowrap px-4 py-2 lg:px-5 lg:py-2.5 bg-brand-500 hover:bg-brand-600 text-black font-bold text-sm rounded-xl transition-all shadow-md shadow-brand-500/25 hover:shadow-brand-500/40" href="/signup">Get Started</a>
                    @endauth

                    <!-- Mobile Menu Hamburger Button (Visible on < lg) -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" 
                            class="lg:hidden w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-white/5 dark:hover:bg-white/10 flex items-center justify-center transition-all text-gray-700 dark:text-gray-200 border border-gray-200/60 dark:border-white/10 focus:outline-none shrink-0" 
                            aria-label="Toggle navigation menu">
                        <svg x-show="!mobileMenuOpen" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg x-show="mobileMenuOpen" style="display: none;" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </nav>

        <!-- Mobile Navigation Drawer -->
        <div x-show="mobileMenuOpen" 
             x-cloak
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-4"
             @click.away="mobileMenuOpen = false"
             class="lg:hidden border-t border-gray-100 dark:border-white/10 bg-white dark:bg-[#0c0c0c] px-4 pt-3 pb-6 space-y-4 max-h-[calc(100vh-5rem)] overflow-y-auto shadow-2xl" 
             style="display: none;">
            
            <!-- Mobile Links -->
            <div class="space-y-1">
                <a href="/" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-bold {{ request()->is('/') ? 'bg-brand-500/15 text-brand-600 dark:text-brand-400' : 'text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5' }} transition-colors">
                    <span class="text-base">🏠</span>
                    <span>Home</span>
                </a>
            </div>

            <!-- Services Mobile Section -->
            <div class="space-y-1.5 pt-1">
                <div class="text-[11px] font-extrabold uppercase tracking-wider text-gray-400 dark:text-gray-500 px-3.5 flex items-center justify-between">
                    <span>Services</span>
                    <a href="/pricing" class="text-[10px] text-brand-500 lowercase tracking-normal font-bold">rates & pricing →</a>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                    <!-- Ride -->
                    <a href="/ride" class="flex items-center gap-3 p-2.5 rounded-xl transition-all {{ request()->is('ride*') ? 'bg-brand-500/10 text-brand-600 dark:text-brand-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5' }}">
                        <div class="w-9 h-9 rounded-lg bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                            <svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/>
                                <circle cx="7" cy="17" r="2"/>
                                <path d="M9 17h6"/>
                                <circle cx="17" cy="17" r="2"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="font-bold text-sm text-gray-900 dark:text-white leading-tight">Ride</div>
                            <div class="text-[11px] text-gray-500 dark:text-gray-400 truncate">Book on-demand city rides</div>
                        </div>
                    </a>

                    <!-- Rent Vehicle -->
                    <a href="/rent" class="flex items-center gap-3 p-2.5 rounded-xl transition-all {{ request()->is('rent*') ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5' }}">
                        <div class="w-9 h-9 rounded-lg bg-blue-500/15 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                            <svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="8" cy="15" r="5"/>
                                <path d="m11.5 11.5 7-7"/>
                                <path d="m16 4 2 2"/>
                                <path d="m19 7 2 2"/>
                                <circle cx="8" cy="15" r="2"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="font-bold text-sm text-gray-900 dark:text-white leading-tight">Rent Vehicle</div>
                            <div class="text-[11px] text-gray-500 dark:text-gray-400 truncate">Self-drive & car rentals</div>
                        </div>
                    </a>

                    <!-- Hire Driver -->
                    <a href="/hire-driver" class="flex items-center gap-3 p-2.5 rounded-xl transition-all {{ request()->is('hire-driver*') || request()->is('driver-booking*') ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5' }}">
                        <div class="w-9 h-9 rounded-lg bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                                <path d="m9 11 2 2 4-4"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="font-bold text-sm text-gray-900 dark:text-white leading-tight">Hire Driver</div>
                            <div class="text-[11px] text-gray-500 dark:text-gray-400 truncate">Verified private chauffeurs</div>
                        </div>
                    </a>

                    <!-- Package Delivery -->
                    <a href="/delivery" class="flex items-center gap-3 p-2.5 rounded-xl transition-all {{ request()->is('delivery*') ? 'bg-purple-500/10 text-purple-600 dark:text-purple-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5' }}">
                        <div class="w-9 h-9 rounded-lg bg-purple-500/15 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                            <svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m7.5 4.27 9 5.15"/>
                                <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                                <path d="m3.3 7 8.7 5 8.7-5"/>
                                <path d="M12 22V12"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="font-bold text-sm text-gray-900 dark:text-white leading-tight">Package Delivery</div>
                            <div class="text-[11px] text-gray-500 dark:text-gray-400 truncate">Door-to-door courier dispatch</div>
                        </div>
                    </a>

                    <!-- Track My Ride -->
                    <a href="/ride/track" class="flex items-center gap-3 p-2.5 rounded-xl transition-all {{ request()->is('ride/track*') ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5' }}">
                        <div class="w-9 h-9 rounded-lg bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="font-bold text-sm text-gray-900 dark:text-white leading-tight">Track My Ride</div>
                            <div class="text-[11px] text-gray-500 dark:text-gray-400 truncate">Live GPS trip tracker</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Memberships, Pricing & Apps -->
            <div class="space-y-1 pt-1 border-t border-gray-100 dark:border-white/10">
                <a href="/membership" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold {{ request()->is('membership*') ? 'bg-brand-500/15 text-brand-600 dark:text-brand-400 font-bold' : 'text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5' }} transition-colors">
                    <span>Memberships</span>
                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded bg-brand-500/20 text-brand-600 dark:text-brand-400 border border-brand-500/30">VIP</span>
                </a>
                <a href="/pricing" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold {{ request()->is('pricing*') ? 'bg-brand-500/15 text-brand-600 dark:text-brand-400 font-bold' : 'text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5' }} transition-colors">
                    <span>Pricing & Rates</span>
                    <span class="text-xs text-gray-400">→</span>
                </a>
            </div>

            <!-- Apps Hub Mobile -->
            <div class="space-y-1.5 pt-1 border-t border-gray-100 dark:border-white/10">
                <div class="text-[11px] font-extrabold uppercase tracking-wider text-gray-400 dark:text-gray-500 px-3.5">Apps</div>
                <div class="grid grid-cols-2 gap-2">
                    <a href="/apps#rider-app" class="p-2.5 rounded-xl bg-gray-50 dark:bg-white/5 flex items-center gap-2.5 hover:bg-brand-500/10 transition-colors">
                        <span class="text-base">🚗</span>
                        <div class="min-w-0">
                            <span class="block text-xs font-bold text-gray-900 dark:text-white truncate">Rider App</span>
                            <span class="block text-[10px] text-gray-500 truncate">Book rides</span>
                        </div>
                    </a>
                    <a href="/apps#driver-app" class="p-2.5 rounded-xl bg-gray-50 dark:bg-white/5 flex items-center gap-2.5 hover:bg-amber-500/10 transition-colors">
                        <span class="text-base">🚕</span>
                        <div class="min-w-0">
                            <span class="block text-xs font-bold text-gray-900 dark:text-white truncate">Driver App</span>
                            <span class="block text-[10px] text-gray-500 truncate">Keep 90%</span>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Company Links Mobile -->
            <div class="space-y-1 pt-1 border-t border-gray-100 dark:border-white/10">
                <div class="text-[11px] font-extrabold uppercase tracking-wider text-gray-400 dark:text-gray-500 px-3.5">Company</div>
                <div class="grid grid-cols-2 gap-1 text-xs font-medium text-gray-600 dark:text-gray-400 px-1">
                    <a href="/about" class="px-2.5 py-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors flex items-center gap-1.5">
                        <span>🏢</span> <span>About Us</span>
                    </a>
                    <a href="/safety" class="px-2.5 py-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors flex items-center gap-1.5">
                        <span>🛡️</span> <span>Safety & Trust</span>
                    </a>
                    <a href="/become-driver" class="px-2.5 py-1.5 rounded-lg hover:bg-amber-500/10 hover:text-amber-600 dark:hover:text-amber-400 transition-colors flex items-center gap-1.5 font-semibold text-amber-600 dark:text-amber-400">
                        <span>👨‍✈️</span> <span>Become Driver</span>
                    </a>
                    <a href="/become-owner" class="px-2.5 py-1.5 rounded-lg hover:bg-blue-500/10 hover:text-blue-600 dark:hover:text-blue-400 transition-colors flex items-center gap-1.5 font-semibold text-blue-600 dark:text-blue-400">
                        <span>🔑</span> <span>List Vehicle</span>
                    </a>
                    <a href="/signup" class="px-2.5 py-1.5 rounded-lg hover:bg-brand-500/10 hover:text-brand-600 dark:hover:text-brand-400 transition-colors flex items-center gap-1.5 font-semibold text-brand-600 dark:text-brand-400">
                        <span>👤</span> <span>Rider Signup</span>
                    </a>
                    <a href="/blogs" class="px-2.5 py-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors flex items-center gap-1.5">
                        <span>📰</span> <span>Blogs & News</span>
                    </a>
                    <a href="/contact" class="col-span-2 px-2.5 py-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-colors flex items-center gap-1.5">
                        <span>💬</span> <span>Contact Concierge</span>
                    </a>
                </div>
            </div>

            <!-- Mobile Auth Action Buttons -->
            <div class="pt-3 border-t border-gray-100 dark:border-white/10">
                @auth
                    <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5 mb-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-full bg-brand-500 text-black font-black flex items-center justify-center text-sm shrink-0">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <div class="truncate">
                                <div class="text-xs font-bold text-gray-900 dark:text-white truncate">{{ auth()->user()->name }}</div>
                                <div class="text-[10px] text-gray-500 dark:text-gray-400 capitalize">{{ auth()->user()->role ?? 'Member' }}</div>
                            </div>
                        </div>
                        <a href="/account" class="text-xs font-bold text-brand-500 hover:underline shrink-0">Profile →</a>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="/my-rides" class="py-2.5 text-center text-xs font-bold rounded-xl bg-gray-100 dark:bg-white/5 text-gray-800 dark:text-gray-200">My Rides</a>
                        <a href="/wallet" class="py-2.5 text-center text-xs font-bold rounded-xl bg-gray-100 dark:bg-white/5 text-gray-800 dark:text-gray-200">Wallet</a>
                    </div>
                    <form method="POST" action="/logout" class="mt-2">
                        @csrf
                        <button type="submit" class="w-full py-2 text-center text-xs font-bold text-red-500 hover:text-red-600">Sign Out</button>
                    </form>
                @else
                    <div class="flex flex-col gap-2">
                        <a href="/login" class="w-full py-2.5 text-center text-sm font-bold text-gray-800 dark:text-white bg-gray-100 dark:bg-white/5 rounded-xl hover:bg-gray-200 dark:hover:bg-white/10 transition-colors">Sign In</a>
                        <a href="/signup" class="w-full py-2.5 text-center text-sm font-black text-black bg-brand-500 hover:bg-brand-600 rounded-xl shadow-md shadow-brand-500/25 transition-all">Get Started</a>
                    </div>
                @endauth
            </div>
        </div>
    </header>

    {{ $slot }}

    <!-- Footer -->
    <footer class="bg-[#0b0f17] text-white pt-16 pb-12 border-t border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Main Grid: 12-Column Responsive Layout -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-8 mb-14">
                
                <!-- Brand Column (Span 4) -->
                <div class="lg:col-span-4 space-y-5">
                    <a class="inline-flex items-center gap-2 group" href="/">
                        <img src="{{ asset('images/logo.png') }}" alt="RideMyCars Logo" class="h-14 w-auto object-contain transition-transform group-hover:scale-105">
                    </a>
                    <p class="text-gray-300 text-sm leading-relaxed max-w-sm">
                        Your unified mobility platform. Book rides, rent vehicles, hire verified private chauffeurs, and track parcel deliveries in real time.
                    </p>
                    <div class="flex items-center gap-3 pt-2">
                        <a href="https://x.com/ridemycars" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-lg bg-white/5 hover:bg-brand-500 hover:text-black text-gray-300 flex items-center justify-center transition-all duration-200 border border-white/5 hover:border-brand-400" title="Follow RideMyCars on X (Twitter)">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </a>
                        <a href="https://www.instagram.com/ridemycars1?igsi=ZHc2ZjltdHdiaDNj&utm_source=qr" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-lg bg-white/5 hover:bg-brand-500 hover:text-black text-gray-300 flex items-center justify-center transition-all duration-200 border border-white/5 hover:border-brand-400" title="Follow RideMyCars on Instagram">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>
                        </a>
                        <a href="https://www.facebook.com/profile.php?id=61594184214102" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-lg bg-white/5 hover:bg-brand-500 hover:text-black text-gray-300 flex items-center justify-center transition-all duration-200 border border-white/5 hover:border-brand-400" title="Follow RideMyCars on Facebook">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M9 8H6v4h3v12h5V12h3.642L18 8h-4V6.333C14 5.374 14.5 5 15.778 5H18V0h-3.808C10.593 0 9 1.583 9 4.615z"/></svg>
                        </a>
                        <a href="https://www.linkedin.com/in/ride-mycars-587b03432" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-lg bg-white/5 hover:bg-brand-500 hover:text-black text-gray-300 flex items-center justify-center transition-all duration-200 border border-white/5 hover:border-brand-400" title="Follow RideMyCars on LinkedIn">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                        </a>
                        <a href="{{ site_setting('social.tiktok_url', 'https://www.tiktok.com/@ridemycars') }}" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-lg bg-white/5 hover:bg-brand-500 hover:text-black text-gray-300 flex items-center justify-center transition-all duration-200 border border-white/5 hover:border-brand-400" title="Follow RideMyCars on TikTok">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 3 15.68 6.34 6.34 0 0 0 9.34 22a6.34 6.34 0 0 0 6.33-6.33V9.05a8.3 8.3 0 0 0 4.92 1.6V7.21a4.85 4.85 0 0 1-1-.52z"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Column 1: Services (Span 2) -->
                <div class="lg:col-span-2 space-y-4">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-white flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                        Services
                    </h4>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="/ride" class="text-gray-300 hover:text-white hover:translate-x-1 inline-block transition-all">Book a Ride</a></li>
                        <li><a href="/rent" class="text-gray-300 hover:text-white hover:translate-x-1 inline-block transition-all">Rent a Vehicle</a></li>
                        <li><a href="/hire-driver" class="text-gray-300 hover:text-white hover:translate-x-1 inline-block transition-all">Hire a Driver</a></li>
                        <li><a href="/delivery" class="text-gray-300 hover:text-white hover:translate-x-1 inline-block transition-all">Package Delivery</a></li>
                        <li><a href="/pricing" class="text-gray-300 hover:text-white hover:translate-x-1 inline-block transition-all">Pricing & Rates</a></li>
                        <li><a href="/membership" class="text-gray-300 hover:text-white hover:translate-x-1 inline-block transition-all flex items-center gap-1.5">
                            Club Membership
                            <span class="text-[10px] font-extrabold uppercase px-1.5 py-0.5 rounded bg-brand-500/20 text-brand-400 border border-brand-500/30">VIP</span>
                        </a></li>
                        <li><a href="/apps#rider-app" class="text-brand-400 hover:text-brand-300 hover:translate-x-1 inline-block transition-all font-semibold flex items-center gap-1">
                            <span>Download Rider App</span>
                            <span class="text-[9px] px-1 py-0.2 rounded bg-brand-500/20 uppercase font-black">App</span>
                        </a></li>
                    </ul>
                </div>

                <!-- Column 2: Company (Span 2) -->
                <div class="lg:col-span-2 space-y-4">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-white flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                        Company
                    </h4>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="/about" class="text-gray-300 hover:text-white hover:translate-x-1 inline-block transition-all">About Us</a></li>
                        <li><a href="/safety" class="text-gray-300 hover:text-white hover:translate-x-1 inline-block transition-all">Safety & Trust</a></li>
                        <li><a href="/blogs" class="text-gray-300 hover:text-white hover:translate-x-1 inline-block transition-all">News & Insights</a></li>
                        <li>
                            <a href="/signup" class="text-gray-300 hover:text-brand-400 hover:translate-x-1 inline-flex items-center gap-2 transition-all group">
                                <span class="text-sm shrink-0">👤</span>
                                <span>Rider Signup</span>
                                <span class="text-[9px] font-extrabold uppercase px-1.5 py-0.5 rounded bg-brand-500/20 text-brand-400 border border-brand-500/30">Free</span>
                            </a>
                        </li>
                        <li>
                            <a href="/become-driver" class="text-gray-300 hover:text-amber-400 hover:translate-x-1 inline-flex items-center gap-2 transition-all group">
                                <span class="text-sm shrink-0">👨‍✈️</span>
                                <span>Become a Driver</span>
                                <span class="text-[9px] font-extrabold uppercase px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-400 border border-amber-500/30">Earn</span>
                            </a>
                        </li>
                        <li>
                            <a href="/become-owner" class="text-gray-300 hover:text-blue-400 hover:translate-x-1 inline-flex items-center gap-2 transition-all group">
                                <span class="text-sm shrink-0">🔑</span>
                                <span>List Your Vehicle</span>
                                <span class="text-[9px] font-extrabold uppercase px-1.5 py-0.5 rounded bg-blue-500/20 text-blue-400 border border-blue-500/30">Host</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Column 3: Support (Span 2) -->
                <div class="lg:col-span-2 space-y-4">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-white flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                        Support
                    </h4>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="/about" class="text-gray-300 hover:text-white hover:translate-x-1 inline-block transition-all">Help Center</a></li>
                        <li><a href="mailto:{{ site_setting('footer.support_email', 'support@ridemycars.com') }}" class="text-gray-300 hover:text-white hover:translate-x-1 inline-block transition-all">Concierge Support</a></li>
                        <li><a href="/onboarding" class="text-gray-300 hover:text-white hover:translate-x-1 inline-block transition-all">How It Works</a></li>
                        <li><a href="/delivery/tracker" class="text-gray-300 hover:text-white hover:translate-x-1 inline-block transition-all">Delivery Tracker</a></li>
                        <li><a href="/driver/dashboard" class="text-gray-300 hover:text-white hover:translate-x-1 inline-block transition-all">Driver Portal</a></li>
                        <li><a href="/apps#driver-app" class="text-amber-400 hover:text-amber-300 hover:translate-x-1 inline-block transition-all font-semibold flex items-center gap-1">
                            <span>Download Driver App</span>
                            <span class="text-[9px] px-1 py-0.2 rounded bg-amber-500/20 uppercase font-black">90%</span>
                        </a></li>
                    </ul>
                </div>
                <!-- Column 4: Legal (Span 2) -->
                <div class="lg:col-span-2 space-y-4">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-white flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                        Legal & Compliance
                    </h4>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="/terms-and-conditions" class="text-gray-300 hover:text-white hover:translate-x-1 inline-block transition-all">Terms & Conditions</a></li>
                        <li><a href="/privacy-policy" class="text-gray-300 hover:text-white hover:translate-x-1 inline-block transition-all">Privacy Policy</a></li>
                        <li><a href="/refund-cancellation-policy" class="text-gray-300 hover:text-white hover:translate-x-1 inline-block transition-all">Refund & Cancellation Policy</a></li>
                        <li><a href="/privacy-requests" class="text-gray-300 hover:text-white hover:translate-x-1 inline-block transition-all">Privacy Data Rights Portal</a></li>
                        <li><a href="/disputes" class="text-gray-300 hover:text-white hover:translate-x-1 inline-block transition-all">Disputes & Claims (72h)</a></li>
                        <li><a href="/contact" class="text-gray-300 hover:text-white hover:translate-x-1 inline-block transition-all">Contact Us</a></li>
                        <li><a href="/legal" class="text-gray-300 hover:text-white hover:translate-x-1 inline-block transition-all">Compliance & Trust</a></li>
                    </ul>
                </div>
            </div>

            <!-- Corporate Regional Offices Strip (New Development Finance Group) -->
            <div class="pt-10 pb-8 border-t border-white/10">
                <div class="flex items-center justify-between gap-4 mb-5 flex-wrap">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                        <span class="text-xs font-black uppercase tracking-wider text-white">
                            New Development Finance Group — Global Corporate & Regional Offices
                        </span>
                    </div>
                    <a href="/contact" class="text-xs font-bold text-brand-400 hover:text-brand-300 transition-colors flex items-center gap-1">
                        <span>View Office Directory</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- USA Global HQ -->
                    <a href="https://maps.google.com/?q=4301+Saddle+River+Drive,+Bowie,+MD+20720" target="_blank" rel="noopener" class="p-4 rounded-2xl bg-white/[0.03] hover:bg-white/[0.07] border border-white/10 hover:border-brand-500/40 transition-all duration-200 group flex items-start gap-3.5">
                        <span class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-400 border border-blue-500/20 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform overflow-hidden">
                            <img src="https://flagcdn.com/w40/us.png" alt="USA Flag" class="w-6 h-4 object-cover rounded-sm shadow-sm">
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs font-bold text-white group-hover:text-brand-400 transition-colors">United States</span>
                                <span class="text-[10px] font-black px-1.5 py-0.5 rounded bg-blue-500/20 text-blue-300 border border-blue-500/30 uppercase tracking-wider">Global HQ</span>
                            </div>
                            <p class="text-xs text-gray-300 font-medium leading-snug mt-1" title="4301 Saddle River Drive, Bowie, MD 20720">
                                4301 Saddle River Drive, Bowie, MD 20720
                            </p>
                        </div>
                    </a>

                    <!-- RSA Regional Hub -->
                    <a href="https://maps.google.com/?q=11+Corona+Road,+Sandhurst,+Sandton,+Gauteng+2196" target="_blank" rel="noopener" class="p-4 rounded-2xl bg-white/[0.03] hover:bg-white/[0.07] border border-white/10 hover:border-brand-500/40 transition-all duration-200 group flex items-start gap-3.5">
                        <span class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform overflow-hidden">
                            <img src="https://flagcdn.com/w40/za.png" alt="South Africa Flag" class="w-6 h-4 object-cover rounded-sm shadow-sm">
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs font-bold text-white group-hover:text-brand-400 transition-colors">South Africa</span>
                                <span class="text-[10px] font-black px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 uppercase tracking-wider">RSA Hub</span>
                            </div>
                            <p class="text-xs text-gray-300 font-medium leading-snug mt-1" title="11 Corona Road, Sandhurst, Sandton, Gauteng 2196">
                                11 Corona Rd, Sandhurst, Sandton 2196
                            </p>
                        </div>
                    </a>

                    <!-- Ghana Regional Hub -->
                    <a href="https://maps.google.com/?q=No+1+Airport+Square,+Airport+City,+Accra,+Ghana" target="_blank" rel="noopener" class="p-4 rounded-2xl bg-white/[0.03] hover:bg-white/[0.07] border border-white/10 hover:border-brand-500/40 transition-all duration-200 group flex items-start gap-3.5">
                        <span class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform overflow-hidden">
                            <img src="https://flagcdn.com/w40/gh.png" alt="Ghana Flag" class="w-6 h-4 object-cover rounded-sm shadow-sm">
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs font-bold text-white group-hover:text-brand-400 transition-colors">Ghana</span>
                                <span class="text-[10px] font-black px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-300 border border-amber-500/30 uppercase tracking-wider">GHA Hub</span>
                            </div>
                            <p class="text-xs text-gray-300 font-medium leading-snug mt-1" title="No 1 Airport Square, 8th Floor, Airport City, Accra, Ghana">
                                No 1 Airport Square, 8th FL, Airport City, Accra
                            </p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Middle Bar: Contact Info & Separate App Download Badges (Rider vs Driver) -->
            <div class="py-8 border-y border-white/10 flex flex-col xl:flex-row items-center justify-between gap-6">
                <!-- Direct Contacts -->
                <div class="flex flex-wrap items-center justify-center xl:justify-start gap-y-3 gap-x-8 text-sm text-gray-300">
                    <a href="mailto:{{ site_setting('footer.support_email', 'support@ridemycars.com') }}" class="flex items-center gap-2.5 hover:text-brand-400 transition-colors">
                        <span class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-brand-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        </span>
                        <span>{{ site_setting('footer.support_email', 'support@ridemycars.com') }}</span>
                    </a>
                    
                    <a href="tel:+18885700008" class="flex items-center gap-2.5 hover:text-brand-400 transition-colors">
                        <span class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-brand-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        </span>
                        <span>{{ site_setting('footer.support_phone', '+1 888 570 0008') }}</span>
                    </a>
                </div>

                <!-- Separate Rider & Driver App Download Badges -->
                <div class="flex flex-col lg:flex-row items-center gap-3">
                    <!-- Rider App Section -->
                    <div class="flex items-center gap-2 bg-white/[0.03] p-1.5 rounded-2xl border border-white/10">
                        <a href="/apps#rider-app" class="px-2.5 py-1.5 text-[10px] font-black uppercase tracking-wider text-brand-400 bg-brand-500/10 hover:bg-brand-500/20 rounded-lg border border-brand-500/25 transition-colors" title="View Rider App Details">
                            🚗 Rider App
                        </a>
                        <!-- App Store -->
                        <a href="{{ site_setting('rider.ios_url', site_setting('driver.ios_url', 'https://apps.apple.com/app/ridemycars/id123456789')) }}" target="_blank" rel="noopener" class="flex items-center gap-2 px-3 py-1.5 bg-white/5 border border-white/10 text-white rounded-xl hover:bg-white/10 hover:border-white/20 transition-all group" title="Download RideMyCars Rider App on iOS">
                            <svg class="w-4 h-4 shrink-0 group-hover:scale-110 transition-transform" viewBox="0 0 24 24" fill="currentColor"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.62-.75 1.04-1.8 0.92-2.85-.9.04-1.99.6-2.61 1.35-.55.63-1.03 1.68-.9 2.7.99.08 2.01-.5 2.59-1.2z"/></svg>
                            <div class="flex flex-col text-left">
                                <span class="text-[7px] uppercase tracking-wider text-gray-400 leading-none">Download on</span>
                                <span class="text-[10px] font-bold leading-tight mt-0.5">App Store</span>
                            </div>
                        </a>
                        
                        <!-- Google Play / Direct APK -->
                        <a href="{{ route('download.rider') }}" download="RideMyCars-Rider.apk" class="flex items-center gap-2 px-3 py-1.5 bg-white/5 border border-white/10 text-white rounded-xl hover:bg-white/10 hover:border-white/20 transition-all group" title="Download RideMyCars Rider Android APK">
                            <svg class="w-4 h-4 shrink-0 text-brand-500 group-hover:scale-110 transition-transform" viewBox="0 0 24 24" fill="currentColor"><path d="M3.609 1.814L13.792 12 3.61 22.186a1.994 1.994 0 0 1-.61-.954V2.768c.118-.363.33-.687.609-.954zm11.233 11.233l2.257 2.257-11.83 6.697 9.573-8.954zm2.257-2.094l2.845 1.611c.907.514.907 1.353 0 1.867l-2.845 1.611-2.09-2.09 2.09-2.999zm-2.257-2.093L5.27 0.906l11.83 6.697-2.258 2.257z"/></svg>
                            <div class="flex flex-col text-left">
                                <span class="text-[7px] uppercase tracking-wider text-gray-400 leading-none">Get it on</span>
                                <span class="text-[10px] font-bold leading-tight mt-0.5">Google Play</span>
                            </div>
                        </a>
                    </div>

                    <!-- Driver App Section -->
                    <div class="flex items-center gap-2 bg-white/[0.03] p-1.5 rounded-2xl border border-amber-500/20">
                        <a href="/apps#driver-app" class="px-2.5 py-1.5 text-[10px] font-black uppercase tracking-wider text-amber-400 bg-amber-500/10 hover:bg-amber-500/20 rounded-lg border border-amber-500/30 transition-colors" title="View Driver App Details">
                            🚕 Driver App
                        </a>
                        <!-- App Store -->
                        <a href="{{ site_setting('driver.ios_url', 'https://apps.apple.com/app/ridemycars-driver/id987654321') }}" target="_blank" rel="noopener" class="flex items-center gap-2 px-3 py-1.5 bg-white/5 border border-white/10 text-white rounded-xl hover:bg-white/10 hover:border-amber-500/30 transition-all group" title="Download RideMyCars Driver App on iOS">
                            <svg class="w-4 h-4 shrink-0 group-hover:scale-110 transition-transform" viewBox="0 0 24 24" fill="currentColor"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.62-.75 1.04-1.8 0.92-2.85-.9.04-1.99.6-2.61 1.35-.55.63-1.03 1.68-.9 2.7.99.08 2.01-.5 2.59-1.2z"/></svg>
                            <div class="flex flex-col text-left">
                                <span class="text-[7px] uppercase tracking-wider text-gray-400 leading-none">Download on</span>
                                <span class="text-[10px] font-bold leading-tight mt-0.5">App Store</span>
                            </div>
                        </a>
                        
                        <!-- Google Play / Direct APK -->
                        <a href="{{ route('download.driver') }}" download="RideMyCars-Driver.apk" class="flex items-center gap-2 px-3 py-1.5 bg-white/5 border border-white/10 text-white rounded-xl hover:bg-white/10 hover:border-amber-500/30 transition-all group" title="Download RideMyCars Driver Android APK">
                            <svg class="w-4 h-4 shrink-0 text-amber-400 group-hover:scale-110 transition-transform" viewBox="0 0 24 24" fill="currentColor"><path d="M3.609 1.814L13.792 12 3.61 22.186a1.994 1.994 0 0 1-.61-.954V2.768c.118-.363.33-.687.609-.954zm11.233 11.233l2.257 2.257-11.83 6.697 9.573-8.954zm2.257-2.094l2.845 1.611c.907.514.907 1.353 0 1.867l-2.845 1.611-2.09-2.09 2.09-2.999zm-2.257-2.093L5.27 0.906l11.83 6.697-2.258 2.257z"/></svg>
                            <div class="flex flex-col text-left">
                                <span class="text-[7px] uppercase tracking-wider text-gray-400 leading-none">Get it on</span>
                                <span class="text-[10px] font-bold leading-tight mt-0.5">Google Play</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Bottom Footer Bar -->
            <div class="pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-gray-400">
                <p class="text-center md:text-left">{{ site_setting('footer.copyright', '© 2026 RideMyCars • A New Development Finance Group Company. All rights reserved.') }}</p>
                
                <div class="flex flex-wrap items-center justify-center gap-4 text-gray-400">
                    <span class="flex items-center gap-1.5 text-gray-300">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Systems Operational
                    </span>
                    <span class="text-gray-600 hidden sm:inline">•</span>
                    <span class="flex items-center gap-1.5 text-gray-300">
                        <svg class="w-3.5 h-3.5 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        256-Bit SSL Encrypted
                    </span>
                </div>
            </div>

        </div>
    </footer>

    @auth
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('notificationCenter', () => ({
                open: false,
                notifications: [],
                unreadCount: 0,
                pollingTimer: null,
                latestToast: null,
                toastVisible: false,
                lastKnownIds: new Set(),
                
                getIcon(type) {
                    switch (type) {
                        case 'login': return '👋';
                        case 'ride_accepted':
                        case 'en_route': return '🚗';
                        case 'arrived': return '📍';
                        case 'in_progress': return '🟢';
                        case 'completed': return '🏁';
                        case 'review': return '★';
                        default: return '🔔';
                    }
                },

                init() {
                    this.fetchNotifications(true);
                    this.pollingTimer = setInterval(() => {
                        if (this.open) return; // Do not re-poll and disturb user while dropdown is open
                        this.fetchNotifications(false);
                    }, 5000);
                },

                toggleOpen() {
                    this.open = !this.open;
                    if (this.open) {
                        this.fetchNotifications(false);
                    }
                },

                async fetchNotifications(isInitial = false) {
                    if (!isInitial && document.hidden) return;
                    try {
                        const res = await fetch('/api/notifications', {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        if (res.ok) {
                            const data = await res.json();
                            let rawNotifications = data.notifications;
                            if (rawNotifications !== undefined && rawNotifications !== null) {
                                if (!Array.isArray(rawNotifications) && typeof rawNotifications === 'object') {
                                    rawNotifications = Object.values(rawNotifications);
                                }
                                const newNotifications = Array.isArray(rawNotifications) 
                                    ? rawNotifications.filter(n => n && typeof n === 'object') 
                                    : [];
                                const newUnread = typeof data.unread_count === 'number' ? data.unread_count : 0;

                                // Detect newly arrived notifications to trigger live toast pop-up
                                if (!isInitial && newNotifications.length > 0) {
                                    const newItems = newNotifications.filter(n => !this.lastKnownIds.has(n.id) && !n.is_read);
                                    if (newItems.length > 0) {
                                        this.latestToast = newItems[0];
                                        this.toastVisible = true;
                                        this.playChime();
                                        setTimeout(() => { this.toastVisible = false; }, 10000);
                                    }
                                }

                                // Compare signatures so we NEVER re-render or flicker unchanged lists
                                const currentSig = this.notifications.map(n => n.id + ':' + (n.is_read ? 1 : 0)).join(',');
                                const newSig = newNotifications.map(n => n.id + ':' + (n.is_read ? 1 : 0)).join(',');

                                if (currentSig !== newSig) {
                                    this.notifications = newNotifications;
                                }
                                if (this.unreadCount !== newUnread) {
                                    this.unreadCount = newUnread;
                                }
                                this.lastKnownIds = new Set(newNotifications.map(n => n.id));
                            }
                        }
                    } catch (e) {
                        // Silent catch
                    }
                },

                async handleItemClick(item) {
                    if (!item || typeof item !== 'object') return;
                    if (!item.is_read && item.id) {
                        await this.markAsRead(item.id);
                    }
                    if (typeof item.link === 'string' && item.link.trim() !== '' && !item.link.includes('[native code]')) {
                        window.location.href = item.link;
                    }
                },

                async markAsRead(id) {
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                        await fetch('/api/notifications/mark-read', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken || ''
                            },
                            body: JSON.stringify({ id })
                        });
                        if (Array.isArray(this.notifications)) {
                            const item = this.notifications.find(n => n && n.id === id);
                            if (item) {
                                item.is_read = true;
                                this.unreadCount = Math.max(0, this.unreadCount - 1);
                            }
                        }
                    } catch (e) {
                        console.error('Error marking as read', e);
                    }
                },

                async markAllAsRead() {
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                        await fetch('/api/notifications/mark-read', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken || ''
                            },
                            body: JSON.stringify({})
                        });
                        this.notifications.forEach(n => n.is_read = true);
                        this.unreadCount = 0;
                    } catch (e) {
                        console.error('Error marking all as read', e);
                    }
                },

                async clearAll() {
                    if (!confirm('Are you sure you want to clear all notifications?')) return;
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                        await fetch('/api/notifications/clear', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken || ''
                            },
                            body: JSON.stringify({})
                        });
                        this.notifications = [];
                        this.unreadCount = 0;
                    } catch (e) {
                        console.error('Error clearing notifications', e);
                    }
                },

                playChime() {
                    try {
                        const ctx = new (window.AudioContext || window.webkitAudioContext)();
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(587.33, ctx.currentTime);
                        osc.frequency.setValueAtTime(880, ctx.currentTime + 0.1);
                        gain.gain.setValueAtTime(0.12, ctx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.35);
                        osc.start();
                        osc.stop(ctx.currentTime + 0.35);
                    } catch (e) {}
                }
            }));
        });
    </script>
    @endauth

    <!-- Ongoing Ride Banner & Details Modal (Placed at bottom of body for topmost stacking) -->
    @if(!request()->is('driver*') && (!auth()->check() || auth()->user()->role !== 'driver'))
    <div x-data="ongoingRide()" x-init="init()" x-cloak>
        
        <!-- Expanded Fullscreen / Centered Detail Modal (Topmost z-index above Google Maps) -->
        <div x-show="expanded && ride" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
             style="display: none; z-index: 9999999 !important; position: fixed !important;"
             @click.self="expanded = false">
            
            <div class="w-full max-w-lg bg-white dark:bg-[#141414] rounded-3xl shadow-2xl border border-gray-200 dark:border-white/10 overflow-hidden max-h-[85vh] flex flex-col relative"
                 style="z-index: 9999999 !important;"
                 x-show="expanded"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4">
                
                <!-- Header bar -->
                <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-white/10 flex items-center justify-between shrink-0"
                     :class="{
                         'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-950 dark:text-indigo-200': ride && ride.status === 'pending',
                         'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-950 dark:text-emerald-200': ride && (ride.status === 'accepted' || ride.status === 'in_progress'),
                         'bg-blue-50 dark:bg-blue-900/30 text-blue-950 dark:text-blue-200': ride && ride.status === 'en_route',
                         'bg-amber-50 dark:bg-amber-900/30 text-amber-950 dark:text-amber-200': ride && ride.status === 'arrived',
                     }">
                    <div class="flex items-center gap-2.5">
                        <span class="w-3 h-3 rounded-full animate-ping shrink-0"
                              :class="{
                                  'bg-indigo-500': ride && ride.status === 'pending',
                                  'bg-emerald-500': ride && (ride.status === 'accepted' || ride.status === 'in_progress'),
                                  'bg-blue-500': ride && ride.status === 'en_route',
                                  'bg-amber-500': ride && ride.status === 'arrived',
                              }"></span>
                        <span class="font-extrabold text-base text-gray-900 dark:text-white" x-text="statusText"></span>
                    </div>
                    <button type="button" @click="expanded = false" class="w-8 h-8 rounded-full bg-gray-200/80 dark:bg-white/10 hover:bg-gray-300 dark:hover:bg-white/20 flex items-center justify-center text-gray-600 dark:text-gray-300 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <!-- Ride Details Content (Scrollable) -->
                <div class="p-5 sm:p-6 overflow-y-auto space-y-4 flex-1">
                    
                    <!-- Driver Details (when assigned) -->
                    <div x-show="ride && ride.driver_name" class="p-4 bg-gray-50 dark:bg-white/5 rounded-2xl border border-gray-100 dark:border-white/10">
                        <div class="flex items-center gap-3.5">
                            <!-- Avatar with explicit solid styling -->
                            <div class="w-12 h-12 rounded-full flex items-center justify-center text-lg font-black shadow-md shrink-0" 
                                 style="background-color: #4f46e5 !important; color: #ffffff !important;"
                                 x-text="ride && ride.driver_name ? ride.driver_name.charAt(0).toUpperCase() : 'D'"></div>
                            
                            <div class="flex-1 min-w-0">
                                <p class="font-extrabold text-base text-gray-900 dark:text-white truncate" x-text="ride ? ride.driver_name : ''"></p>
                                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    <span class="flex items-center gap-0.5 text-amber-500 font-bold">
                                        ★ <span x-text="ride && ride.driver_rating ? parseFloat(ride.driver_rating).toFixed(1) : '4.9'"></span>
                                    </span>
                                    <span>·</span>
                                    <span class="font-medium" x-text="(ride && ride.driver_total_trips ? ride.driver_total_trips : '40+') + ' trips'"></span>
                                </div>
                            </div>
                            
                            <!-- Call Button -->
                            <a x-show="ride && ride.driver_phone" :href="'tel:' + (ride ? ride.driver_phone : '')" class="w-10 h-10 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white flex items-center justify-center shadow-md transition-colors shrink-0" title="Call Driver">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            </a>
                        </div>
                        
                        <!-- Vehicle Info (Only shown if data exists) -->
                        <template x-if="ride && (ride.driver_vehicle || ride.driver_plate)">
                            <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-200/60 dark:border-white/10 text-xs">
                                <span class="font-bold text-gray-700 dark:text-gray-300" x-text="ride.driver_vehicle || 'Vehicle'"></span>
                                <span x-show="ride.driver_plate" class="px-2.5 py-0.5 bg-gray-200 dark:bg-white/10 font-mono font-bold text-gray-800 dark:text-gray-200 rounded" x-text="ride.driver_plate"></span>
                            </div>
                        </template>
                    </div>

                    <!-- Searching state (when pending) -->
                    <div x-show="ride && !ride.driver_name" class="p-4 bg-indigo-50/70 dark:bg-indigo-900/20 rounded-2xl border border-indigo-100 dark:border-indigo-800/30 flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center shrink-0">
                            <div class="w-5 h-5 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                        </div>
                        <div>
                            <p class="font-bold text-sm text-indigo-950 dark:text-indigo-200">Contacting nearby drivers...</p>
                            <p class="text-xs text-indigo-600 dark:text-indigo-400">Request sent to all active drivers</p>
                        </div>
                    </div>

                    <!-- Locations -->
                    <div class="flex gap-3 px-1 py-1">
                        <div class="flex flex-col items-center pt-1 shrink-0">
                            <div class="w-3.5 h-3.5 rounded-full bg-emerald-500 border-2 border-emerald-200 dark:border-emerald-800"></div>
                            <div class="w-0.5 h-8 bg-gray-200 dark:bg-white/10 my-1"></div>
                            <div class="w-3.5 h-3.5 rounded-full bg-rose-500 border-2 border-rose-200 dark:border-rose-800"></div>
                        </div>
                        <div class="flex-1 min-w-0 space-y-3">
                            <div>
                                <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Pickup Location</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white leading-tight break-words mt-0.5" x-text="ride ? ride.pickup_location : ''"></p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Dropoff Destination</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white leading-tight break-words mt-0.5" x-text="ride ? ride.dropoff_location : ''"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Fare & Payment Method -->
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-white/5 rounded-2xl border border-gray-100 dark:border-white/10">
                        <div>
                            <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Estimated Fare</p>
                            <p class="font-black text-2xl text-emerald-600 dark:text-emerald-400" x-text="ride && ride.fare ? '$' + parseFloat(ride.fare).toFixed(2) : '$28.50'"></p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Payment</p>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-white dark:bg-white/10 border border-gray-200 dark:border-white/10 rounded-lg text-xs font-bold uppercase text-gray-800 dark:text-gray-200" x-text="ride ? ride.payment_method : 'Cash'"></span>
                        </div>
                    </div>
                    
                    <!-- Boost Fare (visible only when pending / no driver accepted) -->
                    <div x-show="ride && ride.status === 'pending'" class="p-4 bg-indigo-50/60 dark:bg-indigo-900/10 rounded-2xl border border-indigo-100 dark:border-indigo-800/30">
                        <p class="text-xs font-bold text-gray-800 dark:text-gray-200 mb-2">💰 Need a ride faster? Increase fare to attract drivers</p>
                        <div class="flex gap-2">
                            <div class="flex-1 relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold">$</span>
                                <input type="number" step="1.00" min="0" x-model="boostFare" 
                                       class="w-full pl-7 pr-3 py-2.5 bg-white dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-sm font-bold text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                                       placeholder="Enter new fare">
                            </div>
                            <button type="button" @click="submitBoost()" :disabled="boosting" 
                                    class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-bold text-xs rounded-xl transition-colors whitespace-nowrap flex items-center gap-1.5 shadow-md">
                                <svg x-show="boosting" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-dasharray="32" stroke-dashoffset="12"/></svg>
                                <span x-text="boosting ? 'Sending...' : '🚀 Boost Fare'"></span>
                            </button>
                        </div>
                        <p x-show="boostError" class="text-xs text-red-500 mt-1.5 font-medium" x-text="boostError"></p>
                        <p x-show="boostSuccess" class="text-xs text-green-600 mt-1.5 font-medium">✓ Fare boosted! Resent to all drivers.</p>
                    </div>
                    
                    <!-- Actions: Navigate, Track & Cancel -->
                    <div class="space-y-2.5 pt-2">
                        <!-- Open Google Maps Turn-by-Turn Navigation -->
                        <a :href="googleMapsUrl" target="_blank" rel="noopener noreferrer"
                           @click="openNavigation()"
                           class="block w-full text-center py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-sm rounded-2xl shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer hover:scale-[1.01] active:scale-[0.99]"
                           style="background-color: #059669 !important; color: #ffffff !important;">
                            <span class="text-base">🧭</span>
                            <span>Navigate in Google Maps</span>
                            <span class="text-[10px] bg-white/20 font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">Turn-by-Turn</span>
                        </a>

                        <!-- In-App Track on Live Map -->
                        <a :href="'/ride?resume=' + (ride ? ride.id : '')" 
                           class="block w-full text-center py-3 bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-extrabold text-xs rounded-2xl hover:opacity-90 transition-all flex items-center justify-center gap-2">
                            <span>🗺️</span>
                            <span>View in RideMyCars Map</span>
                            <span>→</span>
                        </a>

                        <!-- Dedicated Full Live GPS Tracking Page -->
                        <a :href="'/ride/track/' + (ride ? ride.id : '')" 
                           class="block w-full text-center py-3 bg-amber-400 hover:bg-amber-500 text-slate-950 font-extrabold text-xs rounded-2xl shadow-md transition-all flex items-center justify-center gap-2">
                            <span>📍</span>
                            <span>Open Dedicated Live GPS Tracker</span>
                            <span>→</span>
                        </a>

                        <button type="button" @click="cancelRide()" :disabled="cancelling"
                                class="block w-full text-center py-2.5 bg-red-50 hover:bg-red-100 dark:bg-red-950/40 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 font-bold text-xs rounded-2xl transition-colors flex items-center justify-center gap-1.5 border border-red-200 dark:border-red-800/30">
                            <svg x-show="cancelling" class="w-4 h-4 animate-spin text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-dasharray="32" stroke-dashoffset="12"/></svg>
                            <span x-text="cancelling ? 'Cancelling ride...' : '✕ Cancel Ride Request'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Uber-Style Floating App Bubble Widget -->
        <div x-show="ride && !dismissed && !expanded" 
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-8 scale-75"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-8 scale-75"
             class="fixed bottom-6 right-4 sm:right-8 flex items-center gap-2 group"
             style="display: none; z-index: 999999 !important; position: fixed !important; bottom: 28px !important; right: 20px !important;"
             x-cloak>

            <!-- Floating Trip Status Capsule (Like Uber) -->
            <div @click="expanded = true"
                 class="flex items-center gap-3 pl-2 pr-3.5 py-2 rounded-full cursor-pointer select-none transition-all duration-300 hover:scale-105 active:scale-95 shadow-2xl backdrop-blur-xl border border-white/20"
                 style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important; color: #ffffff !important; box-shadow: 0 16px 32px -4px rgba(0, 0, 0, 0.45), 0 0 0 1px rgba(255, 255, 255, 0.15) !important;">
                
                <!-- Circular App Badge with Animated Pulse Radar -->
                <div class="relative flex items-center justify-center shrink-0">
                    <span class="absolute inline-flex h-12 w-12 rounded-full bg-emerald-400/40 opacity-75 animate-ping"></span>
                    
                    <div class="relative w-11 h-11 rounded-full flex items-center justify-center font-black text-xl shadow-lg border-2 border-white/30"
                         style="background: linear-gradient(135deg, #ffdc00 0%, #eab308 100%); color: #0f172a !important;">
                        <span x-show="ride && ride.status === 'pending'" class="animate-spin text-sm">🔍</span>
                        <span x-show="ride && ride.status === 'accepted'">🚗</span>
                        <span x-show="ride && ride.status === 'en_route'" class="animate-bounce text-base">🚘</span>
                        <span x-show="ride && ride.status === 'arrived'" class="text-base">📍</span>
                        <span x-show="ride && ride.status === 'in_progress'" class="text-base">⚡</span>
                    </div>

                    <span class="absolute -top-0.5 -right-0.5 flex h-3.5 w-3.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-emerald-500 border-2 border-slate-900"></span>
                    </span>
                </div>

                <!-- Info Preview -->
                <div class="flex flex-col min-w-0 max-w-[170px] sm:max-w-[220px]">
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] font-black uppercase tracking-wider text-amber-400" x-text="ride && ride.driver_name ? (ride.driver_name + ' · ' + (ride.driver_rating ? '★ ' + parseFloat(ride.driver_rating).toFixed(1) : 'Driver')) : 'Trip in Progress'"></span>
                    </div>
                    <p class="font-extrabold text-xs truncate text-white leading-tight" x-text="statusText"></p>
                </div>

                <!-- Quick Action Buttons -->
                <div class="flex items-center gap-1 shrink-0 ml-1">
                    <a :href="googleMapsUrl" target="_blank" rel="noopener noreferrer"
                       @click.stop="openNavigation()"
                       title="Navigate in Google Maps"
                       class="w-7 h-7 rounded-full bg-emerald-500/20 hover:bg-emerald-500/40 text-emerald-400 flex items-center justify-center text-xs transition-colors border border-emerald-500/30">
                        🧭
                    </a>
                    <button type="button" @click.stop="expanded = true" 
                            title="Expand Details"
                            class="w-7 h-7 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center text-xs font-bold transition-colors">
                        ⤢
                    </button>
                    <button type="button" @click.stop="dismissed = true" title="Dismiss"
                            class="w-6 h-6 rounded-full text-slate-400 hover:text-white flex items-center justify-center text-[10px] transition-colors">
                        ✕
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('ongoingRide', () => ({
            ride: null,
            expanded: false,
            dismissed: false,
            lastStatus: null,
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
                if (s === 'pending') return 'Looking for drivers...';
                if (s === 'accepted') return `${d} accepted your ride`;
                if (s === 'en_route') return `${d} is on the way`;
                if (s === 'arrived') return `${d} has arrived at pickup`;
                if (s === 'in_progress') return 'Trip in progress';
                return '';
            },
            get googleMapsUrl() {
                if (!this.ride) return '#';
                let origin = '';
                let destination = '';

                if (this.ride.pickup_lat && this.ride.pickup_lng) {
                    origin = `${this.ride.pickup_lat},${this.ride.pickup_lng}`;
                } else {
                    origin = encodeURIComponent(this.ride.pickup_location || '');
                }

                if (this.ride.dropoff_lat && this.ride.dropoff_lng) {
                    destination = `${this.ride.dropoff_lat},${this.ride.dropoff_lng}`;
                } else {
                    destination = encodeURIComponent(this.ride.dropoff_location || '');
                }

                return `https://www.google.com/maps/dir/?api=1&origin=${origin}&destination=${destination}&travelmode=driving`;
            },
            openNavigation() {
                setTimeout(() => {
                    this.expanded = false;
                    this.dismissed = false;
                }, 500);
            },
            init() {
                this.check();
                this.timer = setInterval(() => this.check(), 12000);
            },
            async check() {
                if (document.hidden) return;
                try {
                    const guestRideId = localStorage.getItem('rmc_active_ride_id') || '';
                    const url = '/api/user/ongoing-ride' + (guestRideId ? `?guest_ride_id=${encodeURIComponent(guestRideId)}` : '');
                    const res = await fetch(url);
                    if (res.ok) {
                        const data = await res.json();
                        const newRide = data.ride || null;
                        if (newRide) {
                            if (this.lastStatus && this.lastStatus !== newRide.status) {
                                this.dismissed = false; // re-show banner on new status
                            }
                            this.lastStatus = newRide.status;
                            localStorage.setItem('rmc_active_ride_id', newRide.id);
                        } else if (guestRideId && !data.ride) {
                            localStorage.removeItem('rmc_active_ride_id');
                        }
                        this.ride = newRide;
                        if (this.ride && !this.boostFare) {
                            this.boostFare = (parseFloat(this.ride.fare || 0) + 5).toFixed(2);
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
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const res = await fetch(`/api/ride/${this.ride.id}/boost-fare`, {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token 
                        },
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
                if (!confirm('Are you sure you want to cancel this ride request?')) return;
                this.cancelling = true;
                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const res = await fetch(`/api/ride/${this.ride.id}/cancel`, {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token 
                        }
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        this.ride = null;
                        this.expanded = false;
                        this.dismissed = true;
                        alert('Ride cancelled successfully.');
                        window.location.reload();
                    } else {
                        alert(data.error || 'Failed to cancel ride');
                    }
                } catch(e) { 
                    alert('Network error while cancelling ride'); 
                }
                this.cancelling = false;
            }
        }));
    });
    </script>
    @endif

</body>
</html>
