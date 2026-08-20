<x-layout theme="theme-ride">
    <x-slot:title>Book a Ride — RideMyCars</x-slot>

    <main class="w-full mx-auto px-4 py-8 sm:px-6 lg:px-8" style="max-width: 1500px;">
        
        @if(session('success'))
            <div class="mb-6 p-5 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800/30 text-emerald-800 dark:text-emerald-200 font-semibold flex items-center justify-between shadow-sm">
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
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800/30 text-rose-800 dark:text-rose-200 font-semibold flex items-center gap-3 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="flex flex-col lg:flex-row gap-6 lg:gap-8 items-start relative" x-data="{ 
            vehicle_type: '{{ request('type', '') }}', 
            schedule_type: 'now',
            pickup: '',
            dropoff: '',
            get showRides() { return this.pickup.trim().length > 0 && this.dropoff.trim().length > 0; }
        }">
            
            <form action="/ride/book" method="POST" class="flex flex-col lg:flex-row gap-6 lg:gap-8 w-full lg:w-auto z-10 shrink-0">
                @csrf
                
                <div class="w-full lg:w-96 bg-white dark:bg-[#111] lg:p-6 lg:shadow-[0_8px_30px_rgb(0,0,0,0.08)] lg:dark:shadow-[0_8px_30px_rgb(0,0,0,0.3)] lg:rounded-[24px] border-0 lg:border border-gray-100 dark:border-white/10 shrink-0 h-fit" style="max-width: 100%;">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6 tracking-tight">Find a trip</h1>
                    
                    <input type="hidden" name="schedule_type" x-model="schedule_type">

                    <div class="flex mb-4" x-data="{ open: false }">
                        <div class="relative inline-block w-full">
                            <button type="button" @click="open = !open" @click.away="open = false" 
                                    class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] hover:bg-gray-100 dark:hover:bg-[#222] text-gray-900 dark:text-white font-bold rounded-xl cursor-pointer focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-colors text-sm shadow-sm border border-gray-200 dark:border-white/10">
                                <div class="flex items-center gap-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8Zm1-8h4a1 1 0 0 1 0 2h-5a1 1 0 0 1-1-1V7a1 1 0 0 1 2 0Z"/></svg>
                                    <span x-text="schedule_type === 'now' ? 'Pickup now' : 'Schedule for later'"></span>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                            </button>

                            <div x-show="open" style="display: none;" 
                                 x-transition.opacity.duration.200ms
                                 class="absolute left-0 mt-2 w-full bg-white dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] z-50 py-1 overflow-hidden">
                                <button type="button" @click="schedule_type = 'now'; open = false;" class="w-full text-left px-4 py-3 text-sm font-semibold flex items-center gap-3 transition-colors hover:bg-gray-50 dark:hover:bg-[#222]" :class="schedule_type === 'now' ? 'text-brand-500 bg-brand-50/50 dark:bg-brand-900/20' : 'text-gray-700 dark:text-gray-300'">
                                    <svg x-show="schedule_type === 'now'" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    <span :class="schedule_type !== 'now' ? 'ml-7' : ''">Pickup now</span>
                                </button>
                                <button type="button" @click="schedule_type = 'later'; open = false;" class="w-full text-left px-4 py-3 text-sm font-semibold flex items-center gap-3 transition-colors hover:bg-gray-50 dark:hover:bg-[#222]" :class="schedule_type === 'later' ? 'text-brand-500 bg-brand-50/50 dark:bg-brand-900/20' : 'text-gray-700 dark:text-gray-300'">
                                    <svg x-show="schedule_type === 'later'" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    <span :class="schedule_type !== 'later' ? 'ml-7' : ''">Schedule for later</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div x-show="schedule_type === 'later'" style="display: none;" class="grid grid-cols-2 gap-4 mb-4">
                        <input type="date" name="schedule_date" :required="schedule_type === 'later'" class="w-full px-4 py-3.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all text-sm">
                        <input type="time" name="schedule_time" :required="schedule_type === 'later'" class="w-full px-4 py-3.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all text-sm">
                    </div>

                    <div class="space-y-4">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <div class="w-2.5 h-2.5 rounded-full bg-gray-900 dark:bg-white"></div>
                            </div>
                            <input type="text" id="pickup_location" name="pickup_location" x-model="pickup" required placeholder="Pickup location" class="w-full pl-10 pr-10 py-3.5 bg-gray-100 dark:bg-[#222] border-none rounded-xl text-gray-900 dark:text-white placeholder-gray-500 font-medium focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all">
                            <button type="button" id="use_my_location_btn" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors" title="Use my location">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            </button>
                        </div>

                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <div class="w-2.5 h-2.5 bg-gray-900 dark:bg-white"></div>
                            </div>
                            <input type="text" id="dropoff_location" name="dropoff_location" x-model="dropoff" required placeholder="Dropoff location" class="w-full pl-10 pr-4 py-3.5 bg-gray-100 dark:bg-[#222] border-none rounded-xl text-gray-900 dark:text-white placeholder-gray-500 font-medium focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all">
                        </div>
                    </div>

                    <div class="mt-8 space-y-4 hidden lg:block" x-show="!showRides" x-transition.opacity.duration.300ms>
                        <div class="flex items-start gap-4 p-4 rounded-xl hover:bg-gray-50 dark:hover:bg-[#1a1a1a] transition-colors cursor-default">
                            <div class="p-2 bg-brand-50 rounded-lg text-brand-500 shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white text-sm">Fully insured</h4>
                                <p class="text-xs text-gray-500 mt-0.5">Every ride covered by platform insurance.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 p-4 rounded-xl hover:bg-gray-50 dark:hover:bg-[#1a1a1a] transition-colors cursor-default">
                            <div class="p-2 bg-brand-50 rounded-lg text-brand-500 shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white text-sm">Verified drivers</h4>
                                <p class="text-xs text-gray-500 mt-0.5">Background-checked, rated 4.5 and above.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full bg-white dark:bg-[#111] lg:p-6 lg:shadow-[0_8px_30px_rgb(0,0,0,0.08)] lg:dark:shadow-[0_8px_30px_rgb(0,0,0,0.3)] lg:rounded-[24px] border-0 lg:border border-gray-100 dark:border-white/10 shrink-0 h-fit relative pb-28 lg:pb-24" x-show="showRides" style="display: none; max-width: 440px;" x-transition.opacity.duration.300ms>
                    
                    @guest
                        <div class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-white/70 dark:bg-[#111]/80 backdrop-blur-[2px] rounded-[24px]">
                            <div class="bg-white dark:bg-[#1a1a1a] p-6 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.4)] max-w-sm w-[90%] mx-auto border border-gray-100 dark:border-white/10 text-center relative mt-[-20px]">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Log in to see trip options</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Please take a moment to log in so we can show you your trip options.</p>
                                <a href="/login" class="block w-full py-3.5 bg-black dark:bg-white hover:bg-gray-800 dark:hover:bg-gray-200 text-white dark:text-black font-bold rounded-xl transition-all shadow-md active:scale-[0.98]">
                                    Continue
                                </a>
                            </div>
                        </div>
                    @endguest

                    <div class="@guest opacity-30 pointer-events-none select-none blur-[1px] @endguest transition-all duration-300" x-data="{ paymentModal: false, paymentMethod: 'Cash', profileType: 'Personal' }">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-1 tracking-tight">Choose a ride</h2>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Recommended</h3>
                        
                        <input type="hidden" name="vehicle_type" x-model="vehicle_type">
                        <input type="hidden" name="payment_method" x-model="paymentMethod">

                        <div class="space-y-3 pr-1" style="max-height: calc(100vh - 300px); overflow-y: auto;">
                            @forelse($vehicles as $vehicle)
                            <div @click="vehicle_type = '{{ $vehicle->make }} {{ $vehicle->model }}'" 
                                 :class="vehicle_type === '{{ $vehicle->make }} {{ $vehicle->model }}' ? 'border-gray-900 ring-[1.5px] ring-gray-900 dark:border-white dark:ring-white bg-gray-50 dark:bg-[#222]' : 'border-transparent hover:bg-gray-50 dark:hover:bg-[#222]'"
                                 class="flex items-center justify-between p-4 rounded-[14px] border-[1.5px] cursor-pointer transition-colors bg-white dark:bg-[#1a1a1a] shadow-sm">
                                <div class="flex items-center gap-4">
                                    @if($vehicle->image_url)
                                        <img src="{{ Storage::url($vehicle->image_url) }}" alt="{{ $vehicle->make }}" class="w-[72px] h-14 object-contain">
                                    @else
                                        <div class="text-[52px] leading-none">
                                            @if(str_contains(strtolower($vehicle->type), 'sedan') || str_contains(strtolower($vehicle->type), 'luxury'))
                                                🚘
                                            @elseif(str_contains(strtolower($vehicle->type), 'suv') || str_contains(strtolower($vehicle->type), 'van'))
                                                🚙
                                            @elseif(str_contains(strtolower($vehicle->type), 'bike') || str_contains(strtolower($vehicle->type), 'moto'))
                                                🛵
                                            @else
                                                🚗
                                            @endif
                                        </div>
                                    @endif
                                    <div>
                                        <div class="flex items-center gap-1.5 mb-0.5">
                                            <h4 class="font-bold text-gray-900 dark:text-white text-lg leading-none">{{ $vehicle->make }} {{ $vehicle->model }}</h4>
                                            <div class="flex items-center text-xs font-bold text-gray-900 dark:text-white gap-0.5 mt-0.5">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                                {{ str_contains(strtolower($vehicle->type), 'bike') ? '1' : '4' }}
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">3 mins away • 11:27 PM</p>
                                        @if(str_contains(strtolower($vehicle->type), 'bike'))
                                            <p class="text-[11px] text-gray-500 dark:text-gray-500 mt-0.5">Affordable 2 wheeler rides</p>
                                        @else
                                            <p class="text-[11px] text-gray-500 dark:text-gray-500 mt-0.5">Affordable rides</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right shrink-0 ml-2">
                                    <div class="dynamic-price font-bold text-xl text-gray-900 dark:text-white" data-daily-rate="{{ $vehicle->daily_rate }}">${{ number_format($vehicle->daily_rate, 2) }}</div>
                                    @if(str_contains(strtolower($vehicle->type), 'bike'))
                                        <div class="dynamic-price-strike text-xs text-gray-500 line-through" data-daily-rate="{{ $vehicle->daily_rate }}">${{ number_format($vehicle->daily_rate * 1.05, 2) }}</div>
                                    @endif
                                </div>
                            </div>
                            @empty
                                <div class="text-center p-6 bg-gray-50 dark:bg-[#1a1a1a] rounded-xl border border-gray-200 dark:border-white/10">
                                    <p class="text-gray-500 dark:text-gray-400 font-medium">No vehicles are currently available in your area.</p>
                                </div>
                            @endforelse
                        </div>

                        <div class="absolute bottom-0 left-0 w-full bg-white dark:bg-[#111] border-t border-gray-100 dark:border-white/10 p-4 lg:rounded-b-[24px] z-10">
                            <!-- Payment Method -->
                            <div class="mb-3 relative">
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

                            <button type="submit" class="w-full bg-black hover:bg-gray-800 text-white font-bold py-4 rounded-xl text-lg transition-colors flex items-center justify-center shadow-md active:scale-[0.98]">
                                Request <span x-text="vehicle_type || 'Ride'" class="ml-1 truncate max-w-[150px]"></span>
                            </button>
                        </div>
                    </div>
                </div> <!-- Closes w-[440px] panel -->
            </form>
        
            <!-- Right Side: Map -->
            <div class="w-full lg:flex-1 h-96 lg:h-auto bg-gray-50 dark:bg-[#1a1a1a] rounded-[24px] border border-gray-200 dark:border-white/10 overflow-hidden relative shadow-sm shrink-0 lg:shrink" style="min-width: 300px; min-height: 500px;">
                <div id="map" class="w-full h-full absolute inset-0"></div>
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
            let directionsService = null;
            let directionsRenderer = null;
            let pickupLoc = null;
            let dropoffLoc = null;
            let vehicleMarkers = [];

            const pickupInput = document.getElementById("pickup_location");
            const dropoffInput = document.getElementById("dropoff_location");
            const locBtn = document.getElementById("use_my_location_btn");

            function drawVehicles(location) {
                // Clear existing
                vehicleMarkers.forEach(m => m.setMap(null));
                vehicleMarkers = [];
                
                const lat = typeof location.lat === 'function' ? location.lat() : location.lat;
                const lng = typeof location.lng === 'function' ? location.lng() : location.lng;

                // Spawn 4-5 vehicles around pickup
                for(let i=0; i<5; i++) {
                    let offsetLat = (Math.random() - 0.5) * 0.015;
                    let offsetLng = (Math.random() - 0.5) * 0.015;
                    let carMarker = new google.maps.Marker({
                        position: { lat: lat + offsetLat, lng: lng + offsetLng },
                        map: map,
                        icon: {
                            // A simple top-down car SVG path
                            path: 'M17.4 0H5.6C2.5 0 0 3.5 0 6.6v34.8C0 44.5 2.5 47 5.6 47h11.8c3.1 0 5.6-2.5 5.6-5.6V6.6C23 3.5 20.5 0 17.4 0z',
                            fillColor: "white",
                            fillOpacity: 1,
                            strokeWeight: 2,
                            strokeColor: "black",
                            rotation: Math.random() * 360,
                            scale: 0.4,
                            anchor: new google.maps.Point(11.5, 23.5)
                        }
                    });
                    vehicleMarkers.push(carMarker);
                }
            }

            function calculateRoute() {
                if (pickupLoc && dropoffLoc && directionsService && directionsRenderer) {
                    directionsService.route({
                        origin: pickupLoc,
                        destination: dropoffLoc,
                        travelMode: google.maps.TravelMode.DRIVING
                    }, (response, status) => {
                        if (status === 'OK') {
                            directionsRenderer.setDirections(response);
                            if (marker) marker.setMap(null); // Hide default single marker
                            drawVehicles(pickupLoc);

                            // Calculate dynamic prices based on route
                            const route = response.routes[0].legs[0];
                            const distanceKm = route.distance.value / 1000;
                            const durationMin = route.duration.value / 60;
                            
                            // Adjust traffic multiplier (simulate traffic based on avg speed)
                            // avg speed in km/h
                            const avgSpeed = distanceKm / (durationMin / 60);
                            const trafficMultiplier = avgSpeed < 20 ? 1.2 : (avgSpeed > 40 ? 0.9 : 1.0);

                            document.querySelectorAll('.dynamic-price').forEach(el => {
                                const dailyRate = parseFloat(el.getAttribute('data-daily-rate'));
                                // Split daily rate into base, per km, and per min
                                const basePrice = dailyRate / 10;
                                const perKm = dailyRate / 50;
                                const perMin = dailyRate / 100;
                                
                                const fare = (basePrice + (distanceKm * perKm) + (durationMin * perMin)) * trafficMultiplier;
                                el.innerText = '$' + fare.toFixed(2);
                            });
                            
                            document.querySelectorAll('.dynamic-price-strike').forEach(el => {
                                const dailyRate = parseFloat(el.getAttribute('data-daily-rate'));
                                const basePrice = dailyRate / 10;
                                const perKm = dailyRate / 50;
                                const perMin = dailyRate / 100;
                                
                                const fare = (basePrice + (distanceKm * perKm) + (durationMin * perMin)) * trafficMultiplier;
                                el.innerText = '$' + (fare * 1.05).toFixed(2);
                            });

                        } else {
                            console.warn("Directions request failed: " + status);
                        }
                    });
                }
            }

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
                    
                    directionsService = new google.maps.DirectionsService();
                    directionsRenderer = new google.maps.DirectionsRenderer({
                        map: map,
                        polylineOptions: { strokeColor: '#000000', strokeWeight: 4 }
                    });
                    
                    if (pickupInput && google.maps.places) {
                        const pickupAutocomplete = new google.maps.places.Autocomplete(pickupInput);
                        pickupAutocomplete.addListener("place_changed", () => {
                            const place = pickupAutocomplete.getPlace();
                            if (place.geometry) {
                                pickupLoc = place.geometry.location;
                                map.setCenter(pickupLoc);
                                if (marker) marker.setPosition(pickupLoc);
                                map.setZoom(15);
                                calculateRoute();
                            }
                            if (place.formatted_address) {
                                pickupInput.value = place.formatted_address;
                            } else {
                                pickupInput.value = place.name;
                            }
                            pickupInput.dispatchEvent(new Event('input'));
                            pickupInput.blur();
                            setTimeout(() => { document.querySelectorAll('.pac-container').forEach(c => c.style.display = 'none'); }, 10);
                        });
                    }

                    if (dropoffInput && google.maps.places) {
                        const dropoffAutocomplete = new google.maps.places.Autocomplete(dropoffInput);
                        dropoffAutocomplete.addListener("place_changed", () => {
                            const place = dropoffAutocomplete.getPlace();
                            if (place.geometry) {
                                dropoffLoc = place.geometry.location;
                                calculateRoute();
                            }
                            if (place.formatted_address) {
                                dropoffInput.value = place.formatted_address;
                            } else {
                                dropoffInput.value = place.name;
                            }
                            dropoffInput.dispatchEvent(new Event('input'));
                            dropoffInput.blur();
                            setTimeout(() => { document.querySelectorAll('.pac-container').forEach(c => c.style.display = 'none'); }, 10);
                        });
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
                            
                            pickupLoc = pos;
                            calculateRoute();

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
                            
                            pickupInput.dispatchEvent(new Event('input'));

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