<x-layout>
    <x-slot:title>Book a Ride — RideMyCars</x-slot>

    <main class="flex-1 w-full max-w-7xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
        
        <div class="mb-10 text-center lg:text-left">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-3 tracking-tight">Book a ride</h1>
            <p class="text-gray-500 dark:text-gray-400 text-lg">Instant rides with verified, top-rated drivers.</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-12">
            
            <!-- Left Side: Form -->
            <div class="w-full lg:w-[55%]">
                <form action="#" method="POST" class="space-y-8 bg-white dark:bg-[#111] lg:bg-transparent">
                    
                    <!-- Locations -->
                    <div class="space-y-5">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Pickup location</label>
                                <button type="button" class="text-orange-500 hover:text-orange-600 text-sm font-medium flex items-center gap-1 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/></svg>
                                    Use my location
                                </button>
                            </div>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-green-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                </div>
                                <input type="text" placeholder="Where should the driver meet you?" class="w-full pl-12 pr-4 py-3.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Destination</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-red-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                </div>
                                <input type="text" placeholder="Where are you going?" class="w-full pl-12 pr-4 py-3.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- Vehicle Type -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Vehicle type</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Economy -->
                            <div class="border-2 border-orange-500 rounded-xl p-4 text-center cursor-pointer relative overflow-hidden bg-orange-50/30">
                                <div class="absolute top-2 right-2 w-4 h-4 bg-orange-500 rounded-full flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </div>
                                <div class="text-3xl mb-2">🚗</div>
                                <h3 class="font-bold text-gray-900 dark:text-white mb-1">Economy</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Affordable everyday rides</p>
                            </div>
                            
                            <!-- Comfort -->
                            <div class="border border-gray-200 dark:border-white/10 rounded-xl p-4 text-center cursor-pointer hover:border-orange-200 hover:bg-orange-50/10 transition-colors">
                                <div class="text-3xl mb-2">🚙</div>
                                <h3 class="font-bold text-gray-900 dark:text-white mb-1">Comfort</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Spacious, newer cars</p>
                            </div>

                            <!-- Premium -->
                            <div class="border border-gray-200 dark:border-white/10 rounded-xl p-4 text-center cursor-pointer hover:border-orange-200 hover:bg-orange-50/10 transition-colors">
                                <div class="text-3xl mb-2">🏎️</div>
                                <h3 class="font-bold text-gray-900 dark:text-white mb-1">Premium</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Luxury & top-rated drivers</p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Payment method</label>
                        <select class="w-full px-4 py-3.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all appearance-none cursor-pointer">
                            <option>Credit / debit card</option>
                            <option>Apple Pay</option>
                            <option>PayPal</option>
                            <option>Cash (Select regions)</option>
                        </select>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Notes for the driver <span class="font-normal text-gray-400 dark:text-gray-500">(optional)</span></label>
                        <textarea placeholder="Gate code, landmark, luggage..." rows="3" class="w-full px-4 py-3.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all resize-none"></textarea>
                    </div>

                    <button type="button" class="w-full py-4 mt-2 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl transition-all shadow-md shadow-orange-500/25 active:scale-[0.98]">
                        Confirm Ride
                    </button>
                    
                </form>
            </div>

            <!-- Right Side: Info & Map -->
            <div class="w-full lg:w-[45%] space-y-4">
                
                <!-- Map Placeholder -->
                <div class="bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl p-12 flex flex-col items-center justify-center text-center h-[300px]">
                    <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center text-gray-400 dark:text-gray-500 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Map unavailable</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm">Add NEXT_PUBLIC_GOOGLE_MAPS_API_KEY to show the route. Fares are still estimated from your addresses.</p>
                </div>

                <!-- Info Cards -->
                <div class="bg-white dark:bg-[#111] border border-gray-200 dark:border-white/10 rounded-2xl p-5 flex items-start gap-4 hover:border-gray-300 transition-colors">
                    <div class="mt-0.5 p-2 bg-orange-50 rounded-lg text-orange-500">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white mb-0.5">Fully insured</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Every ride covered by platform insurance.</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-[#111] border border-gray-200 dark:border-white/10 rounded-2xl p-5 flex items-start gap-4 hover:border-gray-300 transition-colors">
                    <div class="mt-0.5 p-2 bg-orange-50 rounded-lg text-orange-500">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white mb-0.5">Verified drivers</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Background-checked, rated 4.5 and above.</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-[#111] border border-gray-200 dark:border-white/10 rounded-2xl p-5 flex items-start gap-4 hover:border-gray-300 transition-colors">
                    <div class="mt-0.5 p-2 bg-orange-50 rounded-lg text-orange-500">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white mb-0.5">Support around the clock</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Reach a person any time, day or night.</p>
                    </div>
                </div>

                <div class="pt-4 text-center">
                    <a href="/rent" class="text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">Need a vehicle instead?</a>
                </div>

            </div>

        </div>
    </main>

</x-layout>