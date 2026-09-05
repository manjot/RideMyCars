<x-layout>
    <x-slot:title>{{ $ride ? "Ride #{$ride->id} — Live Tracker" : "Live Ride Tracker" }} | RideMyCars</x-slot>

    @if(!$ride)
    <div x-data="{
        isChecking: true,
        lookupRideId: '',
        lookupError: '',
        init() {
            try {
                const storedId = localStorage.getItem('rmc_active_ride_id');
                if (storedId) {
                    window.location.replace('/ride/track/' + encodeURIComponent(storedId));
                    return;
                }
            } catch(e) {}
            this.isChecking = false;
        },
        submitLookup() {
            const id = (this.lookupRideId || '').trim();
            if (!id) {
                this.lookupError = 'Please enter your Ride ID (e.g. 104)';
                return;
            }
            window.location.href = '/ride/track/' + encodeURIComponent(id);
        }
    }" x-init="init()" class="min-h-[65vh] flex flex-col items-center justify-center p-4 sm:p-8 text-center">
        
        <div x-show="isChecking" class="space-y-4">
            <div class="w-12 h-12 border-4 border-amber-500 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
            <h2 class="text-xl font-black text-gray-900 dark:text-white">Connecting to your ongoing ride...</h2>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Retrieving active session GPS status...</p>
        </div>

        <div x-show="!isChecking" style="display: none;" class="w-full max-w-md bg-white dark:bg-[#111] p-6 sm:p-8 rounded-3xl border border-gray-100 dark:border-white/10 shadow-2xl space-y-6 text-center">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-400 via-amber-500 to-amber-600 text-slate-950 flex items-center justify-center font-black text-2xl mx-auto shadow-lg shadow-amber-500/20">
                🛰️
            </div>

            <div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/20 mb-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    Guest Ongoing Ride Tracker
                </div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white">Track Your Ride</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Enter your Ride ID number to follow driver arrival, route updates, and live GPS without signing in.
                </p>
            </div>

            <form @submit.prevent="submitLookup()" class="space-y-3">
                <div>
                    <input type="text" x-model="lookupRideId" placeholder="Enter Ride ID (e.g. 104)" 
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-center text-base font-black text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                    <p x-show="lookupError" class="text-xs text-rose-500 font-bold mt-1.5 text-center" x-text="lookupError"></p>
                </div>

                <button type="submit" class="w-full py-3.5 bg-amber-400 hover:bg-amber-500 text-slate-950 font-black text-sm rounded-2xl shadow-md transition-all cursor-pointer">
                    Track Ride Live →
                </button>
            </form>

            <div class="pt-4 border-t border-gray-100 dark:border-white/10 flex items-center justify-between text-xs">
                <span class="text-gray-400">Need to request a ride?</span>
                <a href="/ride" class="font-extrabold text-brand-600 dark:text-brand-400 hover:underline">
                    Book a Ride Now →
                </a>
            </div>
        </div>
    </div>
    @else

    <style>
        .radar-pulse {
            position: relative;
        }
        .radar-pulse::before, .radar-pulse::after {
            content: '';
            position: absolute;
            inset: -8px;
            border-radius: 9999px;
            background: rgba(16, 185, 129, 0.25);
            animation: radarPing 2s cubic-bezier(0, 0, 0.2, 1) infinite;
        }
        .radar-pulse::after {
            animation-delay: 1s;
        }
        @keyframes radarPing {
            0% { transform: scale(0.9); opacity: 0.8; }
            100% { transform: scale(2.2); opacity: 0; }
        }
    </style>

    <main class="flex-1 max-w-5xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12"
          x-data="rideTrackerApp({{ $ride->id }}, '{{ $ride->status }}')"
          x-init="init()">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-gray-100 dark:border-white/10">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Live Trip Tracker
                    </span>
                    <span class="text-xs font-semibold text-gray-500 font-mono">Ride #{{ $ride->id }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                    <span x-text="statusHeading">Trip Status</span>
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    Real-time GPS tracking & driver connection. No login required.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <button type="button" @click="shareTrip()" 
                        class="px-4 py-2.5 bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:border-black dark:hover:border-white/30 text-gray-800 dark:text-white font-bold text-xs rounded-xl transition-all shadow-xs flex items-center gap-2 cursor-pointer">
                    <span x-text="shareSuccess ? '✓ Link Copied!' : '🔗 Share Trip'"></span>
                </button>
                <a href="/ride" 
                   class="px-4 py-2.5 bg-black hover:bg-gray-800 text-white dark:bg-white dark:hover:bg-gray-100 dark:text-black font-extrabold text-xs rounded-xl transition-all shadow-xs flex items-center gap-1.5">
                    <span>+ Book New Ride</span>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Left 2 Cols: Status Hero, Map & Timeline -->
            <div class="lg:col-span-2 space-y-6">

                <!-- 1. Live Radar Animation (When Searching / Pending) -->
                <div x-show="ride.status === 'pending'" 
                     class="bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-transparent border border-amber-500/20 rounded-3xl p-8 text-center space-y-4 shadow-sm relative overflow-hidden">
                    <div class="relative w-28 h-28 mx-auto flex items-center justify-center">
                        <div class="absolute inset-0 rounded-full bg-amber-500/20 animate-ping"></div>
                        <div class="absolute inset-2 rounded-full bg-amber-500/30 animate-pulse"></div>
                        <div class="w-16 h-16 rounded-full bg-amber-500 text-white flex items-center justify-center text-3xl font-bold shadow-lg z-10">
                            🚗
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white">Looking for Nearby Executive Drivers</h3>
                        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 max-w-md mx-auto mt-1">
                            Dispatched to top-rated professional drivers in your vicinity. You'll be notified immediately when a driver accepts.
                        </p>
                    </div>
                </div>

                <!-- 2. Interactive Map / Route Card -->
                <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 overflow-hidden shadow-sm">
                    <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
                        <span class="text-xs font-extrabold text-gray-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                            Live Route & Navigation
                        </span>
                        <a :href="googleMapsNavUrl" target="_blank" rel="noopener noreferrer"
                           class="text-xs font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                            <span>Open Google Maps</span>
                            <span>↗</span>
                        </a>
                    </div>
                    
                    <div class="relative w-full h-[280px] sm:h-[340px] bg-gray-100 dark:bg-black/40 overflow-hidden">
                        <!-- Static Map Image with fallback -->
                        <img :src="mapUrl" 
                             alt="Route map" 
                             class="w-full h-full object-cover"
                             onerror="this.style.display='none'">

                        <!-- Status Overlay Badge on Map -->
                        <div class="absolute top-4 left-4 z-10">
                            <span class="px-3.5 py-1.5 rounded-full text-xs font-extrabold uppercase shadow-lg backdrop-blur-md"
                                  :class="{
                                      'bg-amber-500 text-white': ride.status === 'pending',
                                      'bg-blue-600 text-white': ride.status === 'accepted',
                                      'bg-indigo-600 text-white': ride.status === 'en_route',
                                      'bg-purple-600 text-white': ride.status === 'arrived',
                                      'bg-emerald-600 text-white': ride.status === 'in_progress',
                                      'bg-gray-800 text-white': ride.status === 'completed'
                                  }"
                                  x-text="statusBadgeText">
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 3. Progress Lifecycle Steps -->
                <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 sm:p-8 shadow-sm space-y-6">
                    <h3 class="font-extrabold text-base text-gray-900 dark:text-white border-b border-gray-100 dark:border-white/10 pb-3">
                        Trip Progress Timeline
                    </h3>

                    <div class="space-y-4 text-xs">
                        <!-- Step 1: Request Placed -->
                        <div class="flex items-start gap-3.5">
                            <div class="w-7 h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                                ✓
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white text-sm">Ride Request Confirmed</h4>
                                <p class="text-gray-400">Order placed for <span x-text="ride.vehicle_type || 'Standard'"></span> ride.</p>
                            </div>
                        </div>

                        <!-- Step 2: Driver Assigned & Accepted -->
                        <div class="flex items-start gap-3.5">
                            <div :class="isStepActive(['accepted', 'en_route', 'arrived', 'in_progress', 'completed']) ? 'bg-emerald-500 text-white' : 'bg-gray-200 dark:bg-white/10 text-gray-400'"
                                 class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs shrink-0 shadow-xs transition-colors">
                                <span x-text="isStepActive(['accepted', 'en_route', 'arrived', 'in_progress', 'completed']) ? '✓' : '2'"></span>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white text-sm">Driver Assigned & Accepted</h4>
                                <p class="text-gray-400" x-text="driver ? (driver.name + ' accepted your trip.') : 'Awaiting driver acceptance...'"></p>
                            </div>
                        </div>

                        <!-- Step 3: Driver En Route -->
                        <div class="flex items-start gap-3.5">
                            <div :class="isStepActive(['en_route', 'arrived', 'in_progress', 'completed']) ? 'bg-emerald-500 text-white' : 'bg-gray-200 dark:bg-white/10 text-gray-400'"
                                 class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs shrink-0 shadow-xs transition-colors">
                                <span x-text="isStepActive(['en_route', 'arrived', 'in_progress', 'completed']) ? '✓' : '3'"></span>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white text-sm">Driver En Route to Pickup</h4>
                                <p class="text-gray-400" x-text="ride.status === 'en_route' ? (driver.name + ' is on the way to your pickup location.') : (isStepActive(['arrived', 'in_progress', 'completed']) ? 'Driver arrived at pickup.' : 'Driver will proceed once trip starts.')"></p>
                            </div>
                        </div>

                        <!-- Step 4: Arrived at Pickup & Trip In Progress -->
                        <div class="flex items-start gap-3.5">
                            <div :class="isStepActive(['arrived', 'in_progress', 'completed']) ? 'bg-emerald-500 text-white' : 'bg-gray-200 dark:bg-white/10 text-gray-400'"
                                 class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs shrink-0 shadow-xs transition-colors">
                                <span x-text="isStepActive(['arrived', 'in_progress', 'completed']) ? '✓' : '4'"></span>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white text-sm">Arrived & Trip in Progress</h4>
                                <p class="text-gray-400" x-text="ride.status === 'in_progress' ? 'Trip is actively in progress towards destination.' : (ride.status === 'completed' ? 'Destination reached.' : 'Passenger pickup pending.')"></p>
                            </div>
                        </div>

                        <!-- Step 5: Completed -->
                        <div class="flex items-start gap-3.5">
                            <div :class="ride.status === 'completed' ? 'bg-emerald-500 text-white' : 'bg-gray-200 dark:bg-white/10 text-gray-400'"
                                 class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs shrink-0 shadow-xs transition-colors">
                                <span x-text="ride.status === 'completed' ? '✓' : '5'"></span>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white text-sm">Trip Completed</h4>
                                <p class="text-gray-400" x-text="ride.status === 'completed' ? 'Thank you for riding with RideMyCars!' : 'Trip completion pending arrival.'"></p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Col: Driver Details & Trip Summary -->
            <div class="space-y-6">

                <!-- Driver Card -->
                <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 shadow-sm">
                    <h3 class="font-bold text-xs uppercase tracking-wider text-gray-400 mb-4">Assigned Driver</h3>
                    
                    <template x-if="driver">
                        <div class="space-y-4">
                            <div class="flex items-center gap-3.5">
                                <div class="w-14 h-14 rounded-2xl bg-amber-400 text-slate-900 font-black text-xl flex items-center justify-center overflow-hidden border border-black/10 shadow-sm shrink-0">
                                    <template x-if="driver.photo_url">
                                        <img :src="driver.photo_url" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!driver.photo_url">
                                        <span x-text="driver.name ? driver.name.charAt(0).toUpperCase() : 'D'"></span>
                                    </template>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="font-black text-base text-gray-900 dark:text-white truncate" x-text="driver.name"></h4>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-amber-500 text-xs font-black flex items-center gap-0.5">
                                            ★ <span x-text="driver.rating ? parseFloat(driver.rating).toFixed(1) : '4.9'"></span>
                                        </span>
                                        <span class="text-[11px] text-gray-400 font-medium" x-text="driver.total_trips ? ('· ' + driver.total_trips + ' trips') : ''"></span>
                                    </div>
                                    <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                        Verified Chauffeur
                                    </span>
                                </div>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/10 text-xs space-y-1.5">
                                <div class="flex justify-between">
                                    <span class="text-gray-400 font-medium">Vehicle:</span>
                                    <span class="font-bold text-gray-900 dark:text-white" x-text="driver.vehicle_model || 'Executive Sedan'"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400 font-medium">License Plate:</span>
                                    <span class="font-mono font-bold text-amber-600 dark:text-amber-400" x-text="driver.vehicle_plate || 'REG-8899'"></span>
                                </div>
                            </div>

                            <template x-if="driver.phone">
                                <a :href="'tel:' + driver.phone" 
                                   class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md flex items-center justify-center gap-2 transition-all">
                                    <span>📞 Call Driver</span>
                                    <span class="font-mono" x-text="'(' + driver.phone + ')'"></span>
                                </a>
                            </template>
                        </div>
                    </template>

                    <template x-if="!driver">
                        <div class="text-center py-6 text-gray-400 text-xs italic">
                            Driver details will be displayed here as soon as accepted.
                        </div>
                    </template>
                </div>

                <!-- Trip Details Summary -->
                <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 shadow-sm space-y-5">
                    <h3 class="font-bold text-xs uppercase tracking-wider text-gray-400">Trip Breakdown</h3>

                    <!-- Route -->
                    <div class="space-y-3 text-xs">
                        <div class="flex items-start gap-2.5">
                            <span class="w-3 h-3 rounded-full bg-emerald-500 mt-1 shrink-0"></span>
                            <div>
                                <span class="font-bold text-gray-400 uppercase text-[10px] tracking-wider block">Pickup</span>
                                <span class="font-semibold text-gray-900 dark:text-white leading-snug" x-text="ride.pickup || '{{ $ride->pickup_location }}'"></span>
                            </div>
                        </div>

                        <div class="flex items-start gap-2.5">
                            <span class="w-3 h-3 rounded-full bg-rose-500 mt-1 shrink-0"></span>
                            <div>
                                <span class="font-bold text-gray-400 uppercase text-[10px] tracking-wider block">Destination</span>
                                <span class="font-semibold text-gray-900 dark:text-white leading-snug" x-text="ride.dropoff || '{{ $ride->dropoff_location }}'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Fare & Payment -->
                    <div class="pt-4 border-t border-gray-100 dark:border-white/10 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider block">Trip Fare</span>
                            <span class="font-black text-2xl text-emerald-600 dark:text-emerald-400" x-text="'$' + parseFloat(ride.fare || {{ $ride->fare ?: 28.50 }}).toFixed(2)"></span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider block">Payment</span>
                            <span class="inline-block px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-white/10 font-extrabold text-xs uppercase text-gray-800 dark:text-gray-200" x-text="ride.payment_method || '{{ $ride->payment_method }}'"></span>
                        </div>
                    </div>

                    <!-- Digital Receipt -->
                    <div class="pt-3 border-t border-gray-100 dark:border-white/10 flex items-center justify-between text-xs">
                        <span class="text-gray-400">Digital Receipt:</span>
                        <span class="font-mono font-bold text-gray-700 dark:text-gray-300">{{ $ride->digital_receipt_code ?? 'REC-' . $ride->id }}</span>
                    </div>

                    <!-- Cancel Button -->
                    <template x-if="['pending', 'accepted'].includes(ride.status)">
                        <div class="pt-2">
                            <button type="button" @click="cancelRide()" :disabled="cancelling"
                                    class="w-full py-2.5 bg-red-50 hover:bg-red-100 dark:bg-red-950/40 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 font-bold text-xs rounded-xl transition-all border border-red-200 dark:border-red-800/30 flex items-center justify-center gap-1.5 cursor-pointer">
                                <span x-text="cancelling ? 'Cancelling...' : '✕ Cancel Ride Request'"></span>
                            </button>
                        </div>
                    </template>
                </div>

            </div>
        </div>

    </main>

    <script>
        function rideTrackerApp(rideId, initialStatus) {
            return {
                rideId: rideId,
                ride: {
                    status: initialStatus,
                    fare: '{{ $ride->fare }}',
                    pickup: @json($ride->pickup_location),
                    dropoff: @json($ride->dropoff_location),
                    vehicle_type: '{{ $ride->vehicle_type }}',
                    payment_method: '{{ $ride->payment_method }}',
                    pickup_lat: {{ $ride->pickup_lat ? floatval($ride->pickup_lat) : 'null' }},
                    pickup_lng: {{ $ride->pickup_lng ? floatval($ride->pickup_lng) : 'null' }},
                    dropoff_lat: {{ $ride->dropoff_lat ? floatval($ride->dropoff_lat) : 'null' }},
                    dropoff_lng: {{ $ride->dropoff_lng ? floatval($ride->dropoff_lng) : 'null' }}
                },
                driver: @json($driverData),
                mapKey: '{{ $mapKey }}',
                pollingTimer: null,
                shareSuccess: false,
                cancelling: false,

                init() {
                    // Remember this ride in localStorage for guest tracking continuity
                    localStorage.setItem('rmc_active_ride_id', this.rideId);
                    this.poll();
                    this.pollingTimer = setInterval(() => this.poll(), 3500);
                },

                async poll() {
                    try {
                        const res = await fetch(`/api/ride/${this.rideId}/status`);
                        if (res.ok) {
                            const data = await res.json();
                            this.ride.status = data.status;
                            this.ride.fare = data.fare;
                            this.ride.pickup = data.pickup;
                            this.ride.dropoff = data.dropoff;
                            if (data.driver) {
                                this.driver = data.driver;
                            }
                            if (data.status === 'completed' || data.status === 'cancelled') {
                                localStorage.removeItem('rmc_active_ride_id');
                            }
                        }
                    } catch(e) {}
                },

                get mapUrl() {
                    const p = encodeURIComponent(this.ride.pickup || '');
                    const d = encodeURIComponent(this.ride.dropoff || '');
                    return `https://maps.googleapis.com/maps/api/staticmap?size=800x380&scale=2&maptype=roadmap&markers=size:mid%7Ccolor:green%7Clabel:A%7C${p}&markers=size:mid%7Ccolor:red%7Clabel:B%7C${d}&path=color:0x10b981ff%7Cweight:5%7Cgeodesic:true%7C${p}%7C${d}&key=${this.mapKey}&style=feature:all%7Celement:labels%7Cvisibility:simplified`;
                },

                get googleMapsNavUrl() {
                    const p = encodeURIComponent(this.ride.pickup || '');
                    const d = encodeURIComponent(this.ride.dropoff || '');
                    return `https://www.google.com/maps/dir/?api=1&origin=${p}&destination=${d}&travelmode=driving`;
                },

                get statusHeading() {
                    const s = this.ride.status;
                    const d = this.driver ? this.driver.name : 'Driver';
                    if (s === 'pending') return 'Looking for nearest drivers...';
                    if (s === 'accepted') return `${d} accepted your trip`;
                    if (s === 'en_route') return `${d} is on the way to you`;
                    if (s === 'arrived') return `${d} has arrived at pickup!`;
                    if (s === 'in_progress') return 'Trip in Progress to destination';
                    if (s === 'completed') return 'Trip Completed';
                    if (s === 'cancelled') return 'Trip Cancelled';
                    return 'Live Trip Tracking';
                },

                get statusBadgeText() {
                    const s = this.ride.status;
                    if (s === 'pending') return '● Searching Drivers';
                    if (s === 'accepted') return '✓ Driver Accepted';
                    if (s === 'en_route') return '🚗 En Route to Pickup';
                    if (s === 'arrived') return '📍 Driver Arrived';
                    if (s === 'in_progress') return '⚡ Trip in Progress';
                    if (s === 'completed') return '✓ Completed';
                    if (s === 'cancelled') return '✕ Cancelled';
                    return s.toUpperCase();
                },

                isStepActive(statuses) {
                    return statuses.includes(this.ride.status);
                },

                shareTrip() {
                    const url = window.location.href;
                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(url);
                        this.shareSuccess = true;
                        setTimeout(() => this.shareSuccess = false, 3000);
                    } else {
                        alert('Tracking Link: ' + url);
                    }
                },

                async cancelRide() {
                    if (!confirm('Are you sure you want to cancel this ride request?')) return;
                    this.cancelling = true;
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]')?.content;
                        const res = await fetch(`/api/ride/${this.rideId}/cancel`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token
                            }
                        });
                        if (res.ok) {
                            this.ride.status = 'cancelled';
                            localStorage.removeItem('rmc_active_ride_id');
                            alert('Ride request has been cancelled.');
                        }
                    } catch(e) {
                        alert('Failed to cancel ride.');
                    }
                    this.cancelling = false;
                }
            };
        }
    </script>
    @endif
</x-layout>
