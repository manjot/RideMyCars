<x-layout theme="theme-ride">
    <x-slot:title>Book a Ride — RideMyCars</x-slot>

    <main class="flex-1 w-full max-w-7xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
        
        @if(session('success'))
            <div class="mb-8 p-5 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800/30 text-emerald-800 dark:text-emerald-200 font-semibold flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-brand-500 text-white flex items-center justify-center font-bold text-xl shrink-0 shadow-md">
                        🚘
                    </div>
                    <div>
                        <h4 class="font-extrabold text-lg text-gray-900 dark:text-white">Ride Booked Successfully!</h4>
                        <p class="text-sm text-emerald-700 dark:text-emerald-300 font-medium mt-0.5">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-8 p-4 rounded-2xl bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800/30 text-rose-800 dark:text-rose-200 font-semibold flex items-center gap-3 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="mb-10 text-center lg:text-left">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-3 tracking-tight">Book a ride</h1>
            <p class="text-gray-500 dark:text-gray-400 text-lg">Instant rides with verified, top-rated drivers.</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-12">
            
            <!-- Left Side: Form -->
            <div class="w-full lg:w-[55%]">
                <form action="/ride/book" method="POST" class="space-y-8 bg-white dark:bg-[#111] lg:bg-transparent" x-data="{ vehicle_type: '{{ request('type', 'Economy') }}', schedule_type: 'now' }">
                    @csrf
                    
                    <!-- Schedule Time -->
                    <div class="flex">
                        <div class="relative inline-flex items-center">
                            <div class="absolute left-3.5 pointer-events-none text-gray-900 dark:text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8Zm1-8h4a1 1 0 0 1 0 2h-5a1 1 0 0 1-1-1V7a1 1 0 0 1 2 0Z"/></svg>
                            </div>
                            <select name="schedule_type" x-model="schedule_type" class="pl-9 pr-8 py-2 bg-gray-200/70 dark:bg-[#222] hover:bg-gray-200 dark:hover:bg-[#333] text-gray-900 dark:text-white font-semibold rounded-full appearance-none cursor-pointer border-none focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-colors text-sm">
                                <option value="now">Pickup now</option>
                                <option value="later">Schedule later</option>
                            </select>
                            <div class="absolute right-3.5 pointer-events-none text-gray-900 dark:text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Date/Time Picker (Shown only if schedule later) -->
                    <div x-show="schedule_type === 'later'" style="display: none;" class="grid grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Pickup Date</label>
                            <input type="date" name="schedule_date" :required="schedule_type === 'later'" class="w-full px-4 py-3.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Pickup Time</label>
                            <input type="time" name="schedule_time" :required="schedule_type === 'later'" class="w-full px-4 py-3.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                        </div>
                    </div>

                    <!-- Locations -->
                    <div class="space-y-5">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Pickup location</label>
                                <button type="button" id="use_my_location_btn" class="text-brand-500 hover:text-brand-600 text-sm font-medium flex items-center gap-1 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/></svg>
                                    Use my location
                                </button>
                            </div>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-green-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                </div>
                                <input type="text" id="pickup_location" name="pickup_location" required placeholder="Where should the driver meet you?" class="w-full pl-12 pr-4 py-3.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Destination</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-red-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                </div>
                                <input type="text" id="dropoff_location" name="dropoff_location" required placeholder="Where are you going?" class="w-full pl-12 pr-4 py-3.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- Protected Options -->
                    <div class="relative">
                        @guest
                            <!-- Login Overlay Modal (Uber Style) -->
                            <div class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-white/70 dark:bg-[#111]/80 backdrop-blur-[2px] rounded-2xl">
                                <div class="bg-white dark:bg-[#1a1a1a] p-6 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.4)] max-w-md w-[90%] mx-auto border border-gray-100 dark:border-white/10 text-center relative mt-[-20px]">
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Log in to see trip options</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Please take a moment to quickly log in or sign up so we can show you your trip options.</p>
                                    <a href="/login" class="block w-full py-3.5 bg-black dark:bg-white hover:bg-gray-800 dark:hover:bg-gray-200 text-white dark:text-black font-bold rounded-xl transition-all shadow-md active:scale-[0.98]">
                                        Continue
                                    </a>
                                </div>
                            </div>
                        @endguest

                        <div class="space-y-8 @guest opacity-50 pointer-events-none select-none blur-[1px] @endguest transition-all duration-300 rounded-2xl">
                            <!-- Vehicle Tier Selection (Requirement #2) -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Vehicle Tier</label>
                                <input type="hidden" name="vehicle_type" x-model="vehicle_type">
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    <!-- Comfort -->
                                    <div @click="vehicle_type = 'Comfort'" 
                                         :class="vehicle_type === 'Comfort' || vehicle_type === 'Executive Sedan' ? 'border-brand-500 bg-brand-50/30' : 'border-gray-200 dark:border-white/10 hover:border-brand-200 hover:bg-brand-50/10'"
                                         class="border-2 rounded-xl p-3.5 text-center cursor-pointer relative overflow-hidden transition-colors">
                                        <div x-show="vehicle_type === 'Comfort' || vehicle_type === 'Executive Sedan'" class="absolute top-2 right-2 w-4 h-4 bg-brand-500 rounded-full flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                        </div>
                                        <div class="text-2xl mb-1">🚘</div>
                                        <h3 class="font-bold text-gray-900 dark:text-white text-xs mb-0.5">Comfort</h3>
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400">Class-leading comfort</p>
                                    </div>

                                    <!-- Ultra-SUV -->
                                    <div @click="vehicle_type = 'Ultra-SUV'" 
                                         :class="vehicle_type === 'Ultra-SUV' ? 'border-brand-500 bg-brand-50/30' : 'border-gray-200 dark:border-white/10 hover:border-brand-200 hover:bg-brand-50/10'"
                                         class="border-2 rounded-xl p-3.5 text-center cursor-pointer relative overflow-hidden transition-colors">
                                        <div x-show="vehicle_type === 'Ultra-SUV'" class="absolute top-2 right-2 w-4 h-4 bg-brand-500 rounded-full flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                        </div>
                                        <div class="text-2xl mb-1">🚙</div>
                                        <h3 class="font-bold text-gray-900 dark:text-white text-xs mb-0.5">Ultra-SUV</h3>
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400">Flagship luxury space</p>
                                    </div>

                                    <!-- Economy -->
                                    <div @click="vehicle_type = 'Economy'" 
                                         :class="vehicle_type === 'Economy' ? 'border-brand-500 bg-brand-50/30' : 'border-gray-200 dark:border-white/10 hover:border-brand-200 hover:bg-brand-50/10'"
                                         class="border-2 rounded-xl p-3.5 text-center cursor-pointer relative overflow-hidden transition-colors">
                                        <div x-show="vehicle_type === 'Economy'" class="absolute top-2 right-2 w-4 h-4 bg-brand-500 rounded-full flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                        </div>
                                        <div class="text-2xl mb-1">🚗</div>
                                        <h3 class="font-bold text-gray-900 dark:text-white text-xs mb-0.5">Economy</h3>
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400">Everyday rides</p>
                                    </div>
                                    
                                    <!-- Premium -->
                                    <div @click="vehicle_type = 'Premium'" 
                                         :class="vehicle_type === 'Premium' ? 'border-brand-500 bg-brand-50/30' : 'border-gray-200 dark:border-white/10 hover:border-brand-200 hover:bg-brand-50/10'"
                                         class="border-2 rounded-xl p-3.5 text-center cursor-pointer relative overflow-hidden transition-colors">
                                        <div x-show="vehicle_type === 'Premium'" class="absolute top-2 right-2 w-4 h-4 bg-brand-500 rounded-full flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                        </div>
                                        <div class="text-2xl mb-1">🏎️</div>
                                        <h3 class="font-bold text-gray-900 dark:text-white text-xs mb-0.5">Premium</h3>
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400">Luxury fleet</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Payment method</label>
                                <div class="relative">
                                    <select name="payment_method" class="w-full px-4 py-3.5 pr-10 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all appearance-none cursor-pointer font-semibold">
                                        <option value="stripe">💳 Stripe</option>
                                        <option value="momo" selected>📱 Momo Pay</option>
                                        <option value="cash">💵 Cash</option>
                                        <option value="applepay">🍏 Apple Pay</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-gray-500 dark:text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Notes for the driver <span class="font-normal text-gray-400 dark:text-gray-500">(optional)</span></label>
                                <textarea name="notes" placeholder="Gate code, landmark, luggage..." rows="3" class="w-full px-4 py-3.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all resize-none"></textarea>
                            </div>

                            <button type="submit" class="w-full py-4 mt-2 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl transition-all shadow-md shadow-brand-500/25 active:scale-[0.98]">
                                Confirm Ride
                            </button>
                        </div>
                    </div>
                    
                </form>
            </div>

            <!-- Right Side: Info & Map -->
            <div class="w-full lg:w-[45%] space-y-4">
                
                <!-- Map Container -->
                <div class="bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl flex flex-col items-center justify-center text-center h-[300px] overflow-hidden">
                    <div id="map" class="w-full h-full"></div>
                </div>

                <!-- Info Cards -->
                <div class="bg-white dark:bg-[#111] border border-gray-200 dark:border-white/10 rounded-2xl p-5 flex items-start gap-4 hover:border-gray-300 transition-colors">
                    <div class="mt-0.5 p-2 bg-brand-50 rounded-lg text-brand-500">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white mb-0.5">Fully insured</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Every ride covered by platform insurance.</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-[#111] border border-gray-200 dark:border-white/10 rounded-2xl p-5 flex items-start gap-4 hover:border-gray-300 transition-colors">
                    <div class="mt-0.5 p-2 bg-brand-50 rounded-lg text-brand-500">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white mb-0.5">Verified drivers</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Background-checked, rated 4.5 and above.</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-[#111] border border-gray-200 dark:border-white/10 rounded-2xl p-5 flex items-start gap-4 hover:border-gray-300 transition-colors">
                    <div class="mt-0.5 p-2 bg-brand-50 rounded-lg text-brand-500">
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

    @php
        $gmapsKey = config('services.google_maps.api_key');
        $hasValidKey = !empty($gmapsKey) && !str_contains($gmapsKey, 'AIzaSyDemoKey');
    @endphp

    @if($hasValidKey)
        <script src="https://maps.googleapis.com/maps/api/js?key={{ $gmapsKey }}&libraries=places"></script>
    @endif
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let map = null;
            let marker = null;
            const pickupInput = document.getElementById("pickup_location");
            const dropoffInput = document.getElementById("dropoff_location");
            const locBtn = document.getElementById("use_my_location_btn");

            if (typeof google !== 'undefined' && google.maps) {
                try {
                    map = new google.maps.Map(document.getElementById("map"), {
                        center: { lat: 40.7128, lng: -74.0060 }, // Default to NY
                        zoom: 12,
                        mapTypeControl: false,
                        streetViewControl: false,
                        fullscreenControl: false
                    });
                    marker = new google.maps.Marker({ map: map });
                    
                    if (pickupInput && google.maps.places) {
                        const pickupAutocomplete = new google.maps.places.Autocomplete(pickupInput);
                        pickupAutocomplete.addListener("place_changed", () => {
                            const place = pickupAutocomplete.getPlace();
                            if (!place.geometry) return;
                            map.setCenter(place.geometry.location);
                            marker.setPosition(place.geometry.location);
                            map.setZoom(15);
                        });
                    }

                    if (dropoffInput && google.maps.places) {
                        new google.maps.places.Autocomplete(dropoffInput);
                    }
                } catch (e) {
                    console.warn("Google Maps init skipped or failed:", e);
                }
            }

            if (locBtn && pickupInput) {
                locBtn.addEventListener("click", () => {
                    if (!navigator.geolocation) {
                        alert("Error: Your browser doesn't support geolocation.");
                        return;
                    }

                    const originalHTML = locBtn.innerHTML;
                    locBtn.disabled = true;
                    locBtn.innerHTML = `
                        <svg class="animate-spin h-3.5 w-3.5 text-brand-500 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Locating...</span>
                    `;

                    navigator.geolocation.getCurrentPosition(
                        async (position) => {
                            const pos = {
                                lat: position.coords.latitude,
                                lng: position.coords.longitude,
                            };

                            if (map && marker) {
                                map.setCenter(pos);
                                marker.setPosition(pos);
                                map.setZoom(15);
                            }

                            let addressSet = false;

                            // 1. Try Google Maps Geocoder if loaded
                            if (typeof google !== 'undefined' && google.maps && google.maps.Geocoder) {
                                try {
                                    const geocoder = new google.maps.Geocoder();
                                    const res = await new Promise((resolve) => {
                                        geocoder.geocode({ location: pos }, (results, status) => {
                                            if (status === "OK" && results && results[0]) {
                                                resolve(results[0].formatted_address);
                                            } else {
                                                resolve(null);
                                            }
                                        });
                                    });
                                    if (res) {
                                        pickupInput.value = res;
                                        addressSet = true;
                                    }
                                } catch (e) {
                                    console.warn("Google Geocoder error:", e);
                                }
                            }

                            // 2. Fallback to OpenStreetMap Nominatim API
                            if (!addressSet) {
                                try {
                                    const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${pos.lat}&lon=${pos.lng}`);
                                    if (response.ok) {
                                        const data = await response.json();
                                        if (data && data.display_name) {
                                            pickupInput.value = data.display_name;
                                            addressSet = true;
                                        }
                                    }
                                } catch (e) {
                                    console.warn("OSM Nominatim reverse geocode error:", e);
                                }
                            }

                            // 3. Fallback to lat/lng text if reverse geocode failed
                            if (!addressSet) {
                                pickupInput.value = `Current Location (${pos.lat.toFixed(4)}, ${pos.lng.toFixed(4)})`;
                            }

                            locBtn.disabled = false;
                            locBtn.innerHTML = originalHTML;
                        },
                        (error) => {
                            locBtn.disabled = false;
                            locBtn.innerHTML = originalHTML;

                            if (error.code === error.PERMISSION_DENIED) {
                                alert("Location permission was denied. Please allow location access in your browser settings or enter your address manually.");
                            } else {
                                alert("Unable to retrieve your location automatically. Please enter your address manually.");
                            }
                        },
                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                    );
                });
            }
        });
    </script>

</x-layout>