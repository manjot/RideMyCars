<x-layout>
    <x-slot:title>Download RideMyCars Apps — Rider & Driver Partner Editions | RideMyCars</x-slot>

@php
    $currentHost = request()->getHost();
    $isLocalHost = in_array($currentHost, ['localhost', '127.0.0.1', '::1']);
    $serverIp = $isLocalHost ? gethostbyname(gethostname()) : $currentHost;
    $port = request()->getPort() ? ':' . request()->getPort() : '';
    $scheme = request()->getScheme();
    
    $riderDownloadTargetUrl = $scheme . '://' . $serverIp . $port . route('download.rider', [], false);
    $driverDownloadTargetUrl = $scheme . '://' . $serverIp . $port . route('download.driver', [], false);
@endphp

    <!-- Hero Header -->
    <section class="relative pt-16 pb-20 lg:pt-24 lg:pb-28 overflow-hidden bg-gradient-to-b from-gray-50 via-white to-gray-50 dark:from-[#080808] dark:via-[#0f0f0f] dark:to-[#080808]">
        <div class="absolute inset-0 bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] dark:bg-[radial-gradient(#262626_1px,transparent_1px)] [background-size:24px_24px] opacity-60 -z-10"></div>
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-[700px] h-[350px] bg-brand-500/10 dark:bg-brand-500/10 blur-[140px] rounded-full -z-10 pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-brand-50 dark:bg-brand-950/60 border border-brand-200 dark:border-brand-800/40 text-brand-600 dark:text-brand-400 font-bold text-xs uppercase tracking-widest mb-6 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="animate-pulse"><rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><path d="M12 18h.01"/></svg>
                Mobile Ecosystem & Apps Hub
            </div>

            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-gray-900 dark:text-white tracking-tight leading-[1.1] mb-6">
                Two Dedicated Apps.<br class="hidden sm:inline">
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-500 via-amber-500 to-brand-600 dark:from-brand-400 dark:via-amber-400 dark:to-brand-500">
                    One Unified Mobility Platform.
                </span>
            </h1>

            <p class="text-lg sm:text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto font-medium leading-relaxed mb-10">
                Whether you are requesting luxury rides, booking chauffeurs, sending packages, or driving to earn 90% of every fare—download your dedicated app below.
            </p>

            <!-- Quick Filter / Anchor Pills -->
            <div class="inline-flex items-center p-1.5 rounded-2xl bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 shadow-inner gap-2">
                <a href="#rider-app" class="px-5 py-2.5 rounded-xl font-bold text-sm bg-white dark:bg-white/10 text-gray-900 dark:text-white shadow-sm hover:bg-gray-50 dark:hover:bg-white/15 transition-all flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-brand-500"></span>
                    RideMyCars (Rider)
                </a>
                <a href="#driver-app" class="px-5 py-2.5 rounded-xl font-bold text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white/5 transition-all flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                    RideMyCars Driver (Partner)
                </a>
            </div>
        </div>
    </section>

    <!-- Main Dual App Showcase -->
    <section class="py-12 pb-24 bg-white dark:bg-[#0c0c0c] border-t border-gray-100 dark:border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">

            <!-- =================================================================== -->
            <!-- APP 1: RIDER / CUSTOMER APP -->
            <!-- =================================================================== -->
            <div id="rider-app" class="scroll-mt-28 rounded-3xl p-8 lg:p-12 bg-gradient-to-br from-gray-50 via-white to-gray-50 dark:from-[#141414] dark:via-[#181818] dark:to-[#121212] border border-gray-200/80 dark:border-white/10 shadow-2xl relative overflow-hidden">
                <div class="absolute -right-24 -top-24 w-96 h-96 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                    <!-- Left Column: Details & Features -->
                    <div class="lg:col-span-7 space-y-6">
                        <div class="flex items-center gap-3">
                            <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-brand-500/15 text-brand-600 dark:text-brand-400 border border-brand-500/25">
                                For Passengers & Clients
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold">v1.0.0 • iOS & Android</span>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-brand-500 to-brand-600 flex items-center justify-center text-white shadow-xl shadow-brand-500/25 shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 2.8C2.1 10.9 2 11.1 2 11.4V16c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>
                            </div>
                            <div>
                                <h2 class="text-3xl sm:text-4xl font-black text-gray-900 dark:text-white tracking-tight">
                                    RideMyCars <span class="text-brand-500 dark:text-brand-400">Rider App</span>
                                </h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Request rides, book verified chauffeurs, rent vehicles & track deliveries.</p>
                            </div>
                        </div>

                        <p class="text-gray-600 dark:text-gray-300 leading-relaxed text-base font-normal">
                            Experience executive luxury and on-demand mobility at your fingertips. Book point-to-point rides, reserve executive rental cars, hire verified private drivers by the hour or day, and send time-critical packages with end-to-end security.
                        </p>

                        <!-- Key Features Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-2">
                            <div class="flex items-start gap-3 p-3 rounded-xl bg-white dark:bg-white/[0.03] border border-gray-200/60 dark:border-white/5">
                                <span class="w-6 h-6 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center shrink-0 mt-0.5 font-bold text-xs">✓</span>
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Instant On-Demand Ride & Chauffeur Dispatch</span>
                            </div>
                            <div class="flex items-start gap-3 p-3 rounded-xl bg-white dark:bg-white/[0.03] border border-gray-200/60 dark:border-white/5">
                                <span class="w-6 h-6 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center shrink-0 mt-0.5 font-bold text-xs">✓</span>
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Fleet Rentals & Luxury Car Bookings</span>
                            </div>
                            <div class="flex items-start gap-3 p-3 rounded-xl bg-white dark:bg-white/[0.03] border border-gray-200/60 dark:border-white/5">
                                <span class="w-6 h-6 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center shrink-0 mt-0.5 font-bold text-xs">✓</span>
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Real-Time GPS Driver & Delivery Live Tracking</span>
                            </div>
                            <div class="flex items-start gap-3 p-3 rounded-xl bg-white dark:bg-white/[0.03] border border-gray-200/60 dark:border-white/5">
                                <span class="w-6 h-6 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center shrink-0 mt-0.5 font-bold text-xs">✓</span>
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Secure Stripe Card Payments & Multi-Currency</span>
                            </div>
                        </div>

                        <!-- Download Badges Bar -->
                        <div class="pt-4 flex flex-wrap items-center gap-3">
                            <!-- App Store Button -->
                            <a href="{{ site_setting('rider.ios_url', site_setting('driver.ios_url', 'https://apps.apple.com/app/ridemycars/id123456789')) }}" target="_blank" rel="noopener" class="flex items-center gap-3 px-5 py-3 bg-gray-900 text-white dark:bg-white dark:text-gray-900 rounded-2xl hover:opacity-90 transition-all shadow-lg group">
                                <svg class="w-6 h-6 shrink-0 fill-current group-hover:scale-105 transition-transform" viewBox="0 0 24 24"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.62-.75 1.04-1.8 0.92-2.85-.9.04-1.99.6-2.61 1.35-.55.63-1.03 1.68-.9 2.7.99.08 2.01-.5 2.59-1.2z"/></svg>
                                <div class="flex flex-col text-left">
                                    <span class="text-[9px] uppercase tracking-wider opacity-70 leading-none">Download on</span>
                                    <span class="text-sm font-extrabold leading-tight mt-0.5">App Store</span>
                                </div>
                            </a>

                            <!-- Google Play / Direct APK -->
                            <a href="{{ route('download.rider') }}" download="RideMyCars-Rider.apk" class="flex items-center gap-3 px-5 py-3 bg-brand-500 hover:bg-brand-600 text-white rounded-2xl transition-all shadow-lg shadow-brand-500/25 group">
                                <svg class="w-6 h-6 shrink-0 fill-current group-hover:scale-105 transition-transform" viewBox="0 0 24 24"><path d="M3.609 1.814L13.792 12 3.61 22.186a1.994 1.994 0 0 1-.61-.954V2.768c.118-.363.33-.687.609-.954zm11.233 11.233l2.257 2.257-11.83 6.697 9.573-8.954zm2.257-2.094l2.845 1.611c.907.514.907 1.353 0 1.867l-2.845 1.611-2.09-2.09 2.09-2.999zm-2.257-2.093L5.27 0.906l11.83 6.697-2.258 2.257z"/></svg>
                                <div class="flex flex-col text-left">
                                    <span class="text-[9px] uppercase tracking-wider text-white/80 leading-none">Download APK /</span>
                                    <span class="text-sm font-extrabold leading-tight mt-0.5">Google Play</span>
                                </div>
                            </a>

                            <!-- Direct APK Direct Link -->
                            <a href="{{ route('download.rider') }}" download="RideMyCars-Rider.apk" class="flex items-center gap-2 px-4 py-3 bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:border-brand-500/50 text-gray-800 dark:text-gray-200 rounded-2xl text-xs font-bold transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-brand-500"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                Direct Rider APK (39 MB)
                            </a>
                        </div>
                    </div>

                    <!-- Right Column: Card Preview & QR Code -->
                    <div class="lg:col-span-5 flex flex-col items-center justify-center text-center">
                        <div class="p-6 rounded-3xl bg-white dark:bg-[#1a1a1a] border border-gray-200/80 dark:border-white/10 shadow-xl max-w-xs w-full">
                            <div class="p-4 rounded-2xl bg-white dark:bg-black/40 border border-gray-100 dark:border-white/5 flex items-center justify-center mb-4">
                                <!-- Real Scannable QR Code Generator -->
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=240x240&data={{ urlencode($riderDownloadTargetUrl) }}" 
                                     alt="Scan to Download Rider App" 
                                     class="w-44 h-44 rounded-xl object-contain p-1 bg-white border border-gray-200 dark:border-white/10 shadow-sm"
                                     loading="lazy">
                            </div>
                            <span class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider block">
                                Scan to Download Rider App
                            </span>
                            <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 block">
                                Point your camera to install on iOS / Android
                            </span>
                            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-white/5 flex items-center justify-between text-[11px] text-gray-500">
                                <span>Package: <code class="font-mono text-brand-500 font-bold">com.ridemycars.app</code></span>
                                <span>Free</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- =================================================================== -->
            <!-- APP 2: DRIVER / PARTNER APP -->
            <!-- =================================================================== -->
            <div id="driver-app" class="scroll-mt-28 rounded-3xl p-8 lg:p-12 bg-gradient-to-br from-gray-50 via-white to-gray-50 dark:from-[#141414] dark:via-[#191916] dark:to-[#121210] border border-amber-500/20 dark:border-amber-500/30 shadow-2xl relative overflow-hidden">
                <div class="absolute -right-24 -bottom-24 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                    <!-- Left Column: Details & Features -->
                    <div class="lg:col-span-7 space-y-6">
                        <div class="flex items-center gap-3">
                            <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30">
                                For Driver Partners & Chauffeurs
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold">v1.0.0 • 90% Belongs To You</span>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 flex items-center justify-center text-gray-950 shadow-xl shadow-amber-500/25 shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>
                            </div>
                            <div>
                                <h2 class="text-3xl sm:text-4xl font-black text-gray-900 dark:text-white tracking-tight">
                                    RideMyCars <span class="text-amber-500 dark:text-amber-400">Driver App</span>
                                </h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Keep 90% of every trip. Accept rides, turn-by-turn navigation & live payouts.</p>
                            </div>
                        </div>

                        <p class="text-gray-600 dark:text-gray-300 leading-relaxed text-base font-normal">
                            Built exclusively for professional drivers and vehicle owners. Receive real-time dispatch alerts, navigate directly to passenger pickups, monitor weekly earnings in real time, and enjoy the industry-lowest 10% platform commission.
                        </p>

                        <!-- Key Features Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-2">
                            <div class="flex items-start gap-3 p-3 rounded-xl bg-white dark:bg-white/[0.03] border border-gray-200/60 dark:border-white/5">
                                <span class="w-6 h-6 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center shrink-0 mt-0.5 font-bold text-xs">✓</span>
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Keep 90% of Revenue (Only 10% Commission)</span>
                            </div>
                            <div class="flex items-start gap-3 p-3 rounded-xl bg-white dark:bg-white/[0.03] border border-gray-200/60 dark:border-white/5">
                                <span class="w-6 h-6 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center shrink-0 mt-0.5 font-bold text-xs">✓</span>
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Instant Dispatch Notifications & Sound Alerts</span>
                            </div>
                            <div class="flex items-start gap-3 p-3 rounded-xl bg-white dark:bg-white/[0.03] border border-gray-200/60 dark:border-white/5">
                                <span class="w-6 h-6 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center shrink-0 mt-0.5 font-bold text-xs">✓</span>
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Integrated Turn-by-Turn GPS Navigation</span>
                            </div>
                            <div class="flex items-start gap-3 p-3 rounded-xl bg-white dark:bg-white/[0.03] border border-gray-200/60 dark:border-white/5">
                                <span class="w-6 h-6 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center shrink-0 mt-0.5 font-bold text-xs">✓</span>
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Daily & Weekly Earnings Analytics Dashboard</span>
                            </div>
                        </div>

                        <!-- Download Badges Bar -->
                        <div class="pt-4 flex flex-wrap items-center gap-3">
                            <!-- App Store Button -->
                            <a href="{{ site_setting('driver.ios_url', 'https://apps.apple.com/app/ridemycars-driver/id987654321') }}" target="_blank" rel="noopener" class="flex items-center gap-3 px-5 py-3 bg-gray-900 text-white dark:bg-white dark:text-gray-900 rounded-2xl hover:opacity-90 transition-all shadow-lg group">
                                <svg class="w-6 h-6 shrink-0 fill-current group-hover:scale-105 transition-transform" viewBox="0 0 24 24"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.62-.75 1.04-1.8 0.92-2.85-.9.04-1.99.6-2.61 1.35-.55.63-1.03 1.68-.9 2.7.99.08 2.01-.5 2.59-1.2z"/></svg>
                                <div class="flex flex-col text-left">
                                    <span class="text-[9px] uppercase tracking-wider opacity-70 leading-none">Download on</span>
                                    <span class="text-sm font-extrabold leading-tight mt-0.5">App Store</span>
                                </div>
                            </a>

                            <!-- Google Play / Direct APK -->
                            <a href="{{ route('download.driver') }}" download="RideMyCars-Driver.apk" class="flex items-center gap-3 px-5 py-3 bg-amber-500 hover:bg-amber-600 text-gray-950 font-black rounded-2xl transition-all shadow-lg shadow-amber-500/25 group">
                                <svg class="w-6 h-6 shrink-0 fill-current group-hover:scale-105 transition-transform" viewBox="0 0 24 24"><path d="M3.609 1.814L13.792 12 3.61 22.186a1.994 1.994 0 0 1-.61-.954V2.768c.118-.363.33-.687.609-.954zm11.233 11.233l2.257 2.257-11.83 6.697 9.573-8.954zm2.257-2.094l2.845 1.611c.907.514.907 1.353 0 1.867l-2.845 1.611-2.09-2.09 2.09-2.999zm-2.257-2.093L5.27 0.906l11.83 6.697-2.258 2.257z"/></svg>
                                <div class="flex flex-col text-left">
                                    <span class="text-[9px] uppercase tracking-wider text-gray-900/80 leading-none">Download APK /</span>
                                    <span class="text-sm font-black leading-tight mt-0.5">Google Play</span>
                                </div>
                            </a>

                            <!-- Direct APK Direct Link -->
                            <a href="{{ route('download.driver') }}" download="RideMyCars-Driver.apk" class="flex items-center gap-2 px-4 py-3 bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:border-amber-500/50 text-gray-800 dark:text-gray-200 rounded-2xl text-xs font-bold transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-500"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                Direct Driver APK (39 MB)
                            </a>

                            <a href="/become-driver" class="flex items-center gap-1.5 px-4 py-3 text-xs font-bold text-amber-500 hover:text-amber-400 transition-colors">
                                Apply to Drive Online →
                            </a>
                        </div>
                    </div>

                    <!-- Right Column: Card Preview & QR Code -->
                    <div class="lg:col-span-5 flex flex-col items-center justify-center text-center">
                        <div class="p-6 rounded-3xl bg-white dark:bg-[#1a1a1a] border border-gray-200/80 dark:border-amber-500/20 shadow-xl max-w-xs w-full">
                            <div class="p-4 rounded-2xl bg-white dark:bg-black/40 border border-gray-100 dark:border-white/5 flex items-center justify-center mb-4">
                                <!-- Real Scannable QR Code Generator -->
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=240x240&data={{ urlencode($driverDownloadTargetUrl) }}" 
                                     alt="Scan to Download Driver App" 
                                     class="w-44 h-44 rounded-xl object-contain p-1 bg-white border border-gray-200 dark:border-white/10 shadow-sm"
                                     loading="lazy">
                            </div>
                            <span class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider block">
                                Scan to Download Driver App
                            </span>
                            <span class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 block">
                                Point your camera to install Driver Console
                            </span>
                            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-white/5 flex items-center justify-between text-[11px] text-gray-500">
                                <span>Package: <code class="font-mono text-amber-500 font-bold">com.ridemycars.driver</code></span>
                                <span>Free</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Installation & Sideloading Instructions -->
    <section class="py-20 bg-gray-50 dark:bg-[#080808] border-t border-gray-100 dark:border-white/5">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-14">
                <h3 class="text-xs font-bold uppercase tracking-widest text-brand-500 mb-2">Simple Setup</h3>
                <h2 class="text-3xl sm:text-4xl font-black text-gray-900 dark:text-white tracking-tight">How to Install on Your Device</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Get up and running in less than 2 minutes on Android or iOS.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-6 rounded-3xl bg-white dark:bg-[#121212] border border-gray-200/80 dark:border-white/10 shadow-sm space-y-3">
                    <span class="w-10 h-10 rounded-2xl bg-brand-500/10 text-brand-500 flex items-center justify-center font-black text-base">1</span>
                    <h4 class="text-base font-bold text-gray-900 dark:text-white">Download APK or App</h4>
                    <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                        Tap either the <strong>Rider APK</strong> or <strong>Driver APK</strong> download button above to save the package directly to your phone.
                    </p>
                </div>

                <div class="p-6 rounded-3xl bg-white dark:bg-[#121212] border border-gray-200/80 dark:border-white/10 shadow-sm space-y-3">
                    <span class="w-10 h-10 rounded-2xl bg-brand-500/10 text-brand-500 flex items-center justify-center font-black text-base">2</span>
                    <h4 class="text-base font-bold text-gray-900 dark:text-white">Allow Installation</h4>
                    <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                        If prompted by Android, tap <em>"Allow from this source"</em> or <em>"Install anyway"</em> to complete the secure direct installation.
                    </p>
                </div>

                <div class="p-6 rounded-3xl bg-white dark:bg-[#121212] border border-gray-200/80 dark:border-white/10 shadow-sm space-y-3">
                    <span class="w-10 h-10 rounded-2xl bg-brand-500/10 text-brand-500 flex items-center justify-center font-black text-base">3</span>
                    <h4 class="text-base font-bold text-gray-900 dark:text-white">Sign In & Ride / Drive</h4>
                    <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                        Open the app and log in with your RideMyCars email and password. All rides, bookings, and earnings sync instantly!
                    </p>
                </div>
            </div>
        </div>
    </section>
</x-layout>
