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
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <!-- Alpine.js & Instant Page Prefetching -->
    <style>[x-cloak] { display: none !important; }</style>
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
</head>
<body class="font-sans antialiased bg-[#fafafa] dark:bg-[#0a0a0a] text-gray-900 dark:text-white min-h-screen flex flex-col transition-colors duration-200 {{ $theme ?? '' }}">
    
    <!-- Header -->
    <header class="top-0 left-0 right-0 z-50 bg-white dark:bg-[#111] dark:bg-[#0a0a0a] border-b border-gray-100 dark:border-white/10 transition-colors duration-200">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex h-20 items-center justify-between">
                <!-- Logo -->
                <a class="flex items-center gap-2 group" href="/">
                    <img src="{{ asset('images/logo.png') }}" alt="RideMyCars Logo" class="h-16 md:h-[72px] w-auto object-contain dark:bg-white dark:rounded-xl dark:p-1">
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
                            <a class="text-sm font-medium transition-colors {{ request()->is('admin/package-delivery-tracker*') ? 'text-brand-500 font-bold dark:text-brand-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}" href="/admin/package-delivery-tracker">🚚 Track Deliveries</a>
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
                             class="absolute top-full right-0 mt-4 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-2xl rounded-2xl z-50 overflow-hidden" 
                             style="display: none; width: 360px; max-width: 90vw;">
                            
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
                                            <template x-if="item.type === 'login'"><span>👋</span></template>
                                            <template x-if="item.type === 'ride_accepted'"><span>🚗</span></template>
                                            <template x-if="item.type === 'en_route'"><span>🚗</span></template>
                                            <template x-if="item.type === 'arrived'"><span>📍</span></template>
                                            <template x-if="item.type === 'in_progress'"><span>🟢</span></template>
                                            <template x-if="item.type === 'completed'"><span>🏁</span></template>
                                            <template x-if="item.type === 'review'"><span>★</span></template>
                                            <template x-if="!['login','ride_accepted','en_route','arrived','in_progress','completed','review'].includes(item.type)"><span>🔔</span></template>
                                        </div>

                                        <!-- Content -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-2 mb-0.5">
                                                <h4 class="text-xs font-bold text-gray-900 dark:text-white" x-text="item.title.replace(/^[\p{So}\p{Sk}\p{Sm}\p{Sc}\p{P}\s]+/u, '').trim()"></h4>
                                                <span class="text-[11px] font-medium text-gray-400 whitespace-nowrap shrink-0" x-text="item.time_ago"></span>
                                            </div>
                                            <p class="text-xs text-gray-600 dark:text-gray-300 leading-snug break-words" x-text="item.message"></p>
                                        </div>

                                        <!-- Unread Dot -->
                                        <div class="shrink-0 flex items-center pt-1.5">
                                            <template x-if="!item.is_read">
                                                <span class="w-2 h-2 rounded-full bg-indigo-600 shadow-sm"></span>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- In-App Floating Toast Notification (When a new event happens live) -->
                        <template x-if="latestToast">
                            <div x-show="toastVisible"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 translate-y-[-20px] scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-200"
                                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave-end="opacity-0 translate-y-[-20px] scale-95"
                                 class="fixed top-24 right-4 sm:right-8 z-50 max-w-sm w-full bg-white dark:bg-gray-900 border border-indigo-200 dark:border-indigo-800 shadow-2xl rounded-2xl p-4 flex items-start gap-3 backdrop-blur-md">
                                <div class="w-9 h-9 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 text-base shadow-sm">
                                    <span x-text="latestToast.type === 'login' ? '👋' : (latestToast.type === 'completed' ? '🏁' : (latestToast.type === 'arrived' ? '📍' : (latestToast.type === 'in_progress' ? '🟢' : (latestToast.type === 'review' ? '★' : '🚗'))))"></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-xs font-bold text-gray-900 dark:text-white" x-text="latestToast.title"></h4>
                                    <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5 line-clamp-2" x-text="latestToast.message"></p>
                                    <div class="flex items-center gap-2 mt-2">
                                        <template x-if="latestToast.link">
                                            <a :href="latestToast.link" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">View details →</a>
                                        </template>
                                        <button @click="toastVisible = false" class="text-[11px] font-medium text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">Dismiss</button>
                                    </div>
                                </div>
                                <button @click="toastVisible = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs">✕</button>
                            </div>
                        </template>
                    </div>

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

    {{ $slot }}

    <!-- Footer -->
    <footer class="bg-[#0b0f17] text-white pt-16 pb-12 border-t border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Main Grid: 12-Column Responsive Layout -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-8 mb-14">
                
                <!-- Brand Column (Span 4) -->
                <div class="lg:col-span-4 space-y-5">
                    <a class="inline-flex items-center gap-2 group" href="/">
                        <img src="{{ asset('images/logo.png') }}" alt="RideMyCars Logo" class="h-14 w-auto bg-white rounded-xl p-1 shadow-md object-contain transition-transform group-hover:scale-105">
                    </a>
                    <p class="text-gray-300 text-sm leading-relaxed max-w-sm">
                        Your unified mobility platform. Book rides, rent vehicles, hire verified private chauffeurs, and track parcel deliveries in real time.
                    </p>
                    <div class="flex items-center gap-3 pt-2">
                        <a href="https://twitter.com" target="_blank" rel="noopener" class="w-9 h-9 rounded-lg bg-white/5 hover:bg-brand-500 hover:text-black text-gray-300 flex items-center justify-center transition-all duration-200 border border-white/5 hover:border-brand-400" title="Twitter / X">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </a>
                        <a href="https://instagram.com" target="_blank" rel="noopener" class="w-9 h-9 rounded-lg bg-white/5 hover:bg-brand-500 hover:text-black text-gray-300 flex items-center justify-center transition-all duration-200 border border-white/5 hover:border-brand-400" title="Instagram">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>
                        </a>
                        <a href="https://facebook.com" target="_blank" rel="noopener" class="w-9 h-9 rounded-lg bg-white/5 hover:bg-brand-500 hover:text-black text-gray-300 flex items-center justify-center transition-all duration-200 border border-white/5 hover:border-brand-400" title="Facebook">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M9 8H6v4h3v12h5V12h3.642L18 8h-4V6.333C14 5.374 14.5 5 15.778 5H18V0h-3.808C10.593 0 9 1.583 9 4.615z"/></svg>
                        </a>
                        <a href="https://linkedin.com" target="_blank" rel="noopener" class="w-9 h-9 rounded-lg bg-white/5 hover:bg-brand-500 hover:text-black text-gray-300 flex items-center justify-center transition-all duration-200 border border-white/5 hover:border-brand-400" title="LinkedIn">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
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
                        <li><a href="/become-driver" class="text-gray-300 hover:text-white hover:translate-x-1 inline-block transition-all">Become a Driver</a></li>
                        <li><a href="/become-owner" class="text-gray-300 hover:text-white hover:translate-x-1 inline-block transition-all">List Your Vehicle</a></li>
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
                    </ul>
                </div>

                <!-- Column 4: Legal (Span 2) -->
                <div class="lg:col-span-2 space-y-4">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-white flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                        Legal
                    </h4>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="/terms" class="text-gray-300 hover:text-white hover:translate-x-1 inline-block transition-all">Terms of Service</a></li>
                        <li><a href="/privacy" class="text-gray-300 hover:text-white hover:translate-x-1 inline-block transition-all">Privacy Policy</a></li>
                        <li><a href="/refund" class="text-gray-300 hover:text-white hover:translate-x-1 inline-block transition-all">Refund Policy</a></li>
                        <li><a href="/cookie" class="text-gray-300 hover:text-white hover:translate-x-1 inline-block transition-all">Cookie Policy</a></li>
                        <li><a href="/legal" class="text-gray-300 hover:text-white hover:translate-x-1 inline-block transition-all">Compliance & Trust</a></li>
                    </ul>
                </div>
            </div>

            <!-- Middle Bar: Contact Info & App Download Buttons -->
            <div class="py-8 border-y border-white/10 flex flex-col lg:flex-row items-center justify-between gap-6">
                <!-- Direct Contacts -->
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-y-3 gap-x-8 text-sm text-gray-300">
                    <a href="mailto:{{ site_setting('footer.support_email', 'support@ridemycars.com') }}" class="flex items-center gap-2.5 hover:text-brand-400 transition-colors">
                        <span class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-brand-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        </span>
                        <span>{{ site_setting('footer.support_email', 'support@ridemycars.com') }}</span>
                    </a>
                    
                    <a href="tel:{{ site_setting('footer.support_phone', '+1 800 123 4567') }}" class="flex items-center gap-2.5 hover:text-brand-400 transition-colors">
                        <span class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-brand-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        </span>
                        <span>{{ site_setting('footer.support_phone', '+1 800 123 4567') }}</span>
                    </a>

                    <span class="flex items-center gap-2.5 text-gray-400">
                        <span class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-brand-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </span>
                        <span>{{ site_setting('footer.location', 'San Francisco, CA') }}</span>
                    </span>
                </div>

                <!-- App Buttons -->
                <div class="flex items-center gap-3">
                    <!-- App Store -->
                    <a href="{{ site_setting('driver.ios_url', '#') }}" class="flex items-center gap-3 px-4 py-2.5 bg-white/5 border border-white/10 text-white rounded-xl hover:bg-white/10 hover:border-white/20 transition-all group">
                        <svg class="w-5 h-5 shrink-0 group-hover:scale-110 transition-transform" viewBox="0 0 24 24" fill="currentColor"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.62-.75 1.04-1.8 0.92-2.85-.9.04-1.99.6-2.61 1.35-.55.63-1.03 1.68-.9 2.7.99.08 2.01-.5 2.59-1.2z"/></svg>
                        <div class="flex flex-col text-left">
                            <span class="text-[9px] uppercase tracking-wider text-gray-400 leading-none">Download on</span>
                            <span class="text-xs font-semibold leading-tight mt-0.5">App Store</span>
                        </div>
                    </a>
                    
                    <!-- Google Play / Direct APK Download -->
                    <a href="{{ asset('ridemycars.apk') }}" download="RideMyCars.apk" class="flex items-center gap-3 px-4 py-2.5 bg-white/5 border border-white/10 text-white rounded-xl hover:bg-white/10 hover:border-white/20 transition-all group" title="Download RideMyCars Android APK">
                        <svg class="w-5 h-5 shrink-0 text-brand-500 group-hover:scale-110 transition-transform" viewBox="0 0 24 24" fill="currentColor"><path d="M3.609 1.814L13.792 12 3.61 22.186a1.994 1.994 0 0 1-.61-.954V2.768c.118-.363.33-.687.609-.954zm11.233 11.233l2.257 2.257-11.83 6.697 9.573-8.954zm2.257-2.094l2.845 1.611c.907.514.907 1.353 0 1.867l-2.845 1.611-2.09-2.09 2.09-2.999zm-2.257-2.093L5.27 0.906l11.83 6.697-2.258 2.257z"/></svg>
                        <div class="flex flex-col text-left">
                            <span class="text-[9px] uppercase tracking-wider text-gray-400 leading-none">Get it on</span>
                            <span class="text-xs font-semibold leading-tight mt-0.5">Google Play</span>
                        </div>
                    </a>
                </div>
            </div>
            
            <!-- Bottom Footer Bar -->
            <div class="pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-gray-400">
                <p class="text-center md:text-left">{{ site_setting('footer.copyright', '© 2026 RideMyCars. All rights reserved.') }}</p>
                
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
                
                init() {
                    this.fetchNotifications(true);
                    this.pollingTimer = setInterval(() => this.fetchNotifications(false), 12000);
                },

                toggleOpen() {
                    this.open = !this.open;
                },

                async fetchNotifications(isInitial = false) {
                    if (!isInitial && document.hidden) return;
                    try {
                        const res = await fetch('/api/notifications');
                        if (res.ok) {
                            const data = await res.json();
                            const newNotifications = data.notifications || [];
                            const newUnread = data.unread_count || 0;

                            // Detect newly arrived notifications to trigger live toast pop-up
                            if (!isInitial && newNotifications.length > 0) {
                                const newItems = newNotifications.filter(n => !this.lastKnownIds.has(n.id) && !n.is_read);
                                if (newItems.length > 0) {
                                    this.latestToast = newItems[0];
                                    this.toastVisible = true;
                                    this.playChime();
                                    setTimeout(() => { this.toastVisible = false; }, 6000);
                                }
                            }

                            this.notifications = newNotifications;
                            this.unreadCount = newUnread;
                            this.lastKnownIds = new Set(newNotifications.map(n => n.id));
                        }
                    } catch (e) {
                        // Silent catch
                    }
                },

                async handleItemClick(item) {
                    if (!item.is_read) {
                        await this.markAsRead(item.id);
                    }
                    if (item.link) {
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
                        const item = this.notifications.find(n => n.id === id);
                        if (item) {
                            item.is_read = true;
                            this.unreadCount = Math.max(0, this.unreadCount - 1);
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
    @auth
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
                    
                    <!-- Actions: Track & Cancel -->
                    <div class="space-y-2.5 pt-2">
                        <a :href="'/ride?resume=' + (ride ? ride.id : '')" 
                           class="block w-full text-center py-3.5 bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-extrabold text-sm rounded-2xl hover:opacity-90 shadow-lg transition-all flex items-center justify-center gap-2">
                            <span>🗺</span>
                            <span>Track on Live Map</span>
                            <span>→</span>
                        </a>
                        <button type="button" @click="cancelRide()" :disabled="cancelling"
                                class="block w-full text-center py-3 bg-red-50 hover:bg-red-100 dark:bg-red-950/40 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 font-bold text-xs rounded-2xl transition-colors flex items-center justify-center gap-1.5 border border-red-200 dark:border-red-800/30">
                            <svg x-show="cancelling" class="w-4 h-4 animate-spin text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-dasharray="32" stroke-dashoffset="12"/></svg>
                            <span x-text="cancelling ? 'Cancelling ride...' : '✕ Cancel Ride Request'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Collapsed Floating Bottom Pill -->
        <div x-show="ride && !dismissed && !expanded" 
             x-transition.opacity.duration.300ms
             class="fixed bottom-6 right-4 sm:right-8 max-w-[calc(100vw-32px)] sm:max-w-md"
             style="display: none; z-index: 999999 !important; position: fixed !important; bottom: 28px !important; right: 24px !important;"
             x-cloak>
            
            <div @click="expanded = true"
                 class="flex items-center gap-3 p-3.5 sm:p-4 rounded-2xl sm:rounded-3xl transition-all hover:scale-[1.02] active:scale-[0.98] cursor-pointer select-none"
                 style="background-color: #0f172a !important; color: #ffffff !important; border: 1.5px solid rgba(255, 255, 255, 0.22) !important; box-shadow: 0 20px 35px -5px rgba(0, 0, 0, 0.5), 0 4px 12px rgba(0,0,0,0.3) !important;">
                
                <!-- Status Icon Badge -->
                <div class="relative shrink-0">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg" style="background-color: rgba(255, 255, 255, 0.12) !important;">
                        <span x-show="ride && ride.status === 'pending'" class="animate-pulse">🔍</span>
                        <span x-show="ride && ride.status === 'accepted'">✓</span>
                        <span x-show="ride && ride.status === 'en_route'" class="animate-bounce">🚗</span>
                        <span x-show="ride && ride.status === 'arrived'">📍</span>
                        <span x-show="ride && ride.status === 'in_progress'">⚡</span>
                    </div>
                </div>
                <!-- Info Text -->
                <div class="flex-1 min-w-0 pr-1">
                    <p class="font-black text-sm truncate leading-snug" style="color: #ffffff !important;" x-text="statusText"></p>
                    <p class="text-xs truncate font-medium mt-0.5" style="color: #94a3b8 !important;" x-text="ride ? (ride.dropoff_location || ride.pickup_location || '') : ''"></p>
                </div>
                <!-- Action Buttons -->
                <div class="flex items-center gap-1.5 shrink-0">
                    <button type="button" @click.stop="expanded = true" 
                            class="text-xs font-bold px-3 py-1.5 rounded-xl transition-all flex items-center gap-1 hover:brightness-110 shadow-sm"
                            style="background-color: #3b82f6 !important; color: #ffffff !important; font-weight: 700 !important;">
                        <span>Details</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <button type="button" @click.stop="dismissed = true" title="Dismiss Banner" 
                            class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-colors hover:bg-white/20"
                            style="background-color: rgba(255, 255, 255, 0.14) !important; color: #ffffff !important;">
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
            init() {
                this.check();
                this.timer = setInterval(() => this.check(), 12000);
            },
            async check() {
                if (document.hidden) return;
                try {
                    const res = await fetch('/api/user/ongoing-ride');
                    if (res.ok) {
                        const data = await res.json();
                        const newRide = data.ride || null;
                        if (newRide) {
                            if (this.lastStatus && this.lastStatus !== newRide.status) {
                                this.dismissed = false; // re-show banner on new status
                            }
                            this.lastStatus = newRide.status;
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
    @endauth

</body>
</html>
