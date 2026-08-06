<x-layout theme="theme-delivery">
    <x-slot:title>Package Delivery — RideMyCars</x-slot>

    <main class="flex-1 w-full max-w-7xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
        
        <div class="mb-10 text-center lg:text-left">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-3 tracking-tight">Package Delivery</h1>
            <p class="text-gray-500 dark:text-gray-400 text-lg">Fast and secure delivery for packages and pharmaceuticals.</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-12">
            
            <!-- Left Side: Form -->
            <div class="w-full lg:w-[55%]">
                <form action="/delivery/book" method="POST" class="space-y-8 bg-white dark:bg-[#111] lg:bg-transparent" x-data="{ package_size: 'Small' }">
                    @csrf
                    
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
                                <input type="text" id="pickup_location" name="pickup_location" value="{{ $pickup ?? '' }}" required placeholder="Where should the driver pick up the package?" class="w-full pl-12 pr-4 py-3.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Destination</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-red-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                </div>
                                <input type="text" id="dropoff_location" name="dropoff_location" value="{{ $dropoff ?? '' }}" required placeholder="Where is the package going?" class="w-full pl-12 pr-4 py-3.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- Vehicle Type -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Package size</label>
                        <input type="hidden" name="package_size" x-model="package_size">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Small -->
                            <div @click="package_size = 'Small'" 
                                 :class="package_size === 'Small' ? 'border-brand-500 bg-brand-50/30' : 'border-gray-200 dark:border-white/10 hover:border-brand-200 hover:bg-brand-50/10'"
                                 class="border-2 rounded-xl p-4 text-center cursor-pointer relative overflow-hidden transition-colors">
                                <div x-show="package_size === 'Small'" class="absolute top-2 right-2 w-4 h-4 bg-brand-500 rounded-full flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </div>
                                <div class="text-3xl mb-2">📦</div>
                                <h3 class="font-bold text-gray-900 dark:text-white mb-1">Small</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Documents, small boxes</p>
                            </div>
                            
                            <!-- Medium -->
                            <div @click="package_size = 'Medium'" 
                                 :class="package_size === 'Medium' ? 'border-brand-500 bg-brand-50/30' : 'border-gray-200 dark:border-white/10 hover:border-brand-200 hover:bg-brand-50/10'"
                                 class="border-2 rounded-xl p-4 text-center cursor-pointer relative overflow-hidden transition-colors">
                                <div x-show="package_size === 'Medium'" class="absolute top-2 right-2 w-4 h-4 bg-brand-500 rounded-full flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </div>
                                <div class="text-3xl mb-2">🛒</div>
                                <h3 class="font-bold text-gray-900 dark:text-white mb-1">Medium</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Groceries, medium boxes</p>
                            </div>

                            <!-- Large -->
                            <div @click="package_size = 'Large'" 
                                 :class="package_size === 'Large' ? 'border-brand-500 bg-brand-50/30' : 'border-gray-200 dark:border-white/10 hover:border-brand-200 hover:bg-brand-50/10'"
                                 class="border-2 rounded-xl p-4 text-center cursor-pointer relative overflow-hidden transition-colors">
                                <div x-show="package_size === 'Large'" class="absolute top-2 right-2 w-4 h-4 bg-brand-500 rounded-full flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </div>
                                <div class="text-3xl mb-2">🚚</div>
                                <h3 class="font-bold text-gray-900 dark:text-white mb-1">Large</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Large packages, furniture</p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Payment method</label>
                        <select name="payment_method" class="w-full px-4 py-3.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all appearance-none cursor-pointer">
                            <option>Credit / debit card</option>
                            <option>Apple Pay</option>
                            <option>PayPal</option>
                            <option>Mobile Money</option>
                            <option>Cash (Select regions)</option>
                        </select>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Delivery instructions <span class="font-normal text-gray-400 dark:text-gray-500">(optional)</span></label>
                        <textarea name="notes" placeholder="Gate code, fragile items, leave at door..." rows="3" class="w-full px-4 py-3.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all resize-none"></textarea>
                    </div>

                    <button type="submit" class="w-full py-4 mt-2 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl transition-all shadow-md shadow-brand-500/25 active:scale-[0.98]">
                        Send Package
                    </button>
                    
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
                    <a href="/ride" class="text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">Need a ride instead?</a>
                </div>

            </div>

        </div>
    </main>

    <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof google === 'undefined') return;

            const map = new google.maps.Map(document.getElementById("map"), {
                center: { lat: 40.7128, lng: -74.0060 }, // Default to NY
                zoom: 12,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: false
            });
            const marker = new google.maps.Marker({ map: map });
            
            const pickupInput = document.getElementById("pickup_location");
            const dropoffInput = document.getElementById("dropoff_location");

            const pickupAutocomplete = new google.maps.places.Autocomplete(pickupInput);
            const dropoffAutocomplete = new google.maps.places.Autocomplete(dropoffInput);

            pickupAutocomplete.addListener("place_changed", () => {
                const place = pickupAutocomplete.getPlace();
                if (!place.geometry) return;
                map.setCenter(place.geometry.location);
                marker.setPosition(place.geometry.location);
                map.setZoom(15);
            });

            document.getElementById("use_my_location_btn").addEventListener("click", () => {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const pos = {
                                lat: position.coords.latitude,
                                lng: position.coords.longitude,
                            };
                            map.setCenter(pos);
                            marker.setPosition(pos);
                            map.setZoom(15);
                            
                            // Reverse geocoding to fill input
                            const geocoder = new google.maps.Geocoder();
                            geocoder.geocode({ location: pos }, (results, status) => {
                                if (status === "OK" && results[0]) {
                                    pickupInput.value = results[0].formatted_address;
                                }
                            });
                        },
                        () => {
                            alert("Error: The Geolocation service failed.");
                        }
                    );
                } else {
                    alert("Error: Your browser doesn't support geolocation.");
                }
            });
        });
    </script>

</x-layout>