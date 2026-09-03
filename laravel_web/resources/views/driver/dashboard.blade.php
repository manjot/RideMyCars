<x-layout>
    <x-slot:title>Driver Dashboard — RideMyCars</x-slot>
    <div class="pt-24 pb-12 bg-gray-50 dark:bg-[#09090b] min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-100 border border-green-200 text-green-800 rounded-2xl text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Welcome back, {{ $user->name }}</h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">Manage your driver hiring requests, active jobs, and verification.</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold uppercase
                        {{ $profile->verification_status === 'verified' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 border border-green-300 dark:border-green-800/30' : 
                          ($profile->verification_status === 'submitted' || $profile->verification_status === 'under_review' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400') }}">
                        <span class="w-2 h-2 rounded-full bg-current"></span>
                        License: {{ str_replace('_', ' ', $profile->verification_status) }}
                    </span>
                </div>
            </div>

            <!-- Earnings Overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-2xl p-6 shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Today's Earnings</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">${{ number_format($dailyEarnings, 2) }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $todayTrips }} trip{{ $todayTrips !== 1 ? 's' : '' }} completed</p>
                </div>
                <div class="bg-white dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-2xl p-6 shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">This Week</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">${{ number_format($weeklyEarnings, 2) }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $weekTrips }} trip{{ $weekTrips !== 1 ? 's' : '' }} completed</p>
                </div>
                <div class="bg-white dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-2xl p-6 shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">This Month</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">${{ number_format($monthlyEarnings, 2) }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $monthTrips }} trip{{ $monthTrips !== 1 ? 's' : '' }} completed</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content (Driver Hiring Jobs & Rides) -->
                <div class="lg:col-span-2 space-y-8">
                    @php
                        $mapKey = config('services.google_maps.api_key', env('GOOGLE_MAPS_API_KEY'));
                    @endphp

                    <!-- Pending Payment & Booking Verification Requests -->
                    <div x-data="driverVerificationRequests()" x-init="fetchRequests()" class="bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/30 rounded-3xl p-6 shadow-sm mb-8">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-bold text-amber-900 dark:text-amber-200 flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-amber-500 animate-pulse"></span>
                                Pending Payment & Booking Verification Requests
                            </h2>
                            <button type="button" @click="fetchRequests()" class="text-xs font-bold text-amber-600 hover:text-amber-700 dark:text-amber-400 flex items-center gap-1">
                                🔄 Refresh
                            </button>
                        </div>

                        <div x-show="loading" class="py-4 text-center text-xs text-amber-700 dark:text-amber-300">
                            Loading pending verification requests...
                        </div>

                        <div x-show="!loading && items.length === 0" class="py-4 text-center text-xs text-gray-500 dark:text-gray-400">
                            No pending payment verification requests at the moment.
                        </div>

                        <div x-show="!loading && items.length > 0" class="space-y-4">
                            <template x-for="item in items" :key="item.type + '-' + item.id">
                                <div class="border border-amber-300 dark:border-amber-800/50 rounded-2xl bg-white dark:bg-[#111] p-5 shadow-sm space-y-3">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <span class="text-xs font-extrabold uppercase px-2.5 py-1 bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300 rounded-lg" x-text="item.type_label"></span>
                                            <h4 class="font-bold text-gray-900 dark:text-white text-base mt-2" x-text="'Booking ID: #' + item.code"></h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400" x-text="'Customer: ' + item.customer_name"></p>
                                        </div>
                                        <div class="text-right">
                                            <span class="font-black text-xl text-emerald-600 dark:text-emerald-400" x-text="'$' + item.amount.toFixed(2) + ' ' + item.currency"></span>
                                            <div class="text-[11px] font-bold text-amber-600 dark:text-amber-400 mt-0.5">💳 Stripe Verification Pending</div>
                                        </div>
                                    </div>

                                    <div class="text-xs text-gray-600 dark:text-gray-300 grid grid-cols-1 sm:grid-cols-2 gap-2 bg-gray-50 dark:bg-white/5 p-3 rounded-xl">
                                        <p><strong>📍 Pickup:</strong> <span x-text="item.pickup"></span></p>
                                        <p><strong>🏁 Dropoff:</strong> <span x-text="item.dropoff"></span></p>
                                        <p><strong>📅 Date & Time:</strong> <span x-text="item.schedule"></span></p>
                                        <p><strong>🚘 Vehicle Info:</strong> <span x-text="item.vehicle"></span></p>
                                    </div>

                                    <div class="flex items-center justify-end gap-3 pt-2">
                                        <button type="button" @click="respond(item, 'reject')" :disabled="processing" class="px-4 py-2 bg-red-100 hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-900/50 text-red-700 dark:text-red-300 font-bold rounded-xl text-xs transition">
                                            ✗ Reject Verification
                                        </button>
                                        <button type="button" @click="respond(item, 'approve')" :disabled="processing" class="px-5 py-2 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl text-xs shadow-md transition flex items-center gap-1.5">
                                            <span x-show="processing" class="animate-spin w-3 h-3 border-2 border-white border-t-transparent rounded-full"></span>
                                            <span>✓ Approve & Verify Details</span>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <script>
                    function driverVerificationRequests() {
                        return {
                            loading: true,
                            processing: false,
                            items: [],

                            async fetchRequests() {
                                this.loading = true;
                                try {
                                    const res = await fetch('/api/driver/pending-verifications');
                                    if (res.ok) {
                                        const data = await res.json();
                                        this.items = data.items || [];
                                    }
                                } catch (e) {
                                    console.error("Error fetching driver verification requests:", e);
                                } finally {
                                    this.loading = false;
                                }
                            },

                            async respond(item, action) {
                                let reason = '';
                                if (action === 'reject') {
                                    reason = prompt("Please enter rejection reason for customer:", "Schedule mismatch or vehicle unavailable");
                                    if (reason === null) return;
                                }

                                this.processing = true;
                                try {
                                    const res = await fetch('/api/driver/verify-booking', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'Accept': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                                        },
                                        body: JSON.stringify({
                                            service_type: item.type,
                                            service_id: item.id,
                                            action: action,
                                            rejection_reason: reason
                                        })
                                    });

                                    const data = await res.json();
                                    alert(data.message || 'Verification updated successfully.');
                                    await this.fetchRequests();
                                } catch (e) {
                                    alert('Failed to submit response: ' + e.message);
                                } finally {
                                    this.processing = false;
                                }
                            }
                        }
                    }
                    </script>

                    <!-- Incoming Ride Requests -->
                    <div x-data="driverPolling()" x-init="initPolling()" x-show="requests.length > 0" x-cloak class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800/30 rounded-3xl p-6 shadow-sm mb-8 relative overflow-hidden">
                        <div class="absolute inset-0 bg-indigo-500/10 animate-pulse"></div>
                        <h2 class="text-xl font-bold text-indigo-900 dark:text-indigo-200 mb-4 relative z-10 flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-indigo-500 animate-ping"></span>
                            Incoming Ride Requests
                        </h2>
                        
                        <div class="space-y-4 relative z-10">
                            <template x-for="req in requests" :key="req.id">
                                <div class="border border-indigo-300 dark:border-indigo-700 rounded-2xl bg-white/90 dark:bg-black/60 backdrop-blur-sm shadow-md overflow-hidden">
                                    <!-- Map Preview with Route Path -->
                                    <img :src="getMapUrl(req.ride ? req.ride.pickup_location : (req.driver_booking ? req.driver_booking.pickup_location : ''), req.ride ? req.ride.dropoff_location : (req.driver_booking ? req.driver_booking.dropoff_location : ''), '0x6366f1ff')" alt="Route map" class="w-full h-[120px] object-cover" loading="lazy" onerror="this.style.display='none'">
                                    
                                    <div class="p-5">
                                        <div class="flex justify-between items-start mb-3">
                                            <div>
                                                <span class="text-xs font-extrabold uppercase px-2.5 py-1 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded-lg" x-text="req.driver_booking ? 'New Driver Hiring Request' : 'New Ride Request'">
                                                </span>
                                                <h4 class="font-bold text-gray-900 dark:text-white text-base mt-2" x-text="req.driver_booking ? 'Booking #' + req.driver_booking.booking_code : 'Ride #' + req.ride.id"></h4>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-black text-2xl text-emerald-600 dark:text-emerald-400" x-text="req.driver_booking ? (req.driver_booking.currency + ' ' + parseFloat(req.driver_booking.total_price).toFixed(2)) : (req.ride && req.ride.fare && parseFloat(req.ride.fare) > 0 ? '$' + parseFloat(req.ride.fare).toFixed(2) : '$35.00')"></p>
                                                <span class="inline-flex items-center gap-1 text-[11px] font-bold uppercase text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-white/10 px-2 py-0.5 rounded" x-text="(req.driver_booking ? req.driver_booking.payment_method : req.ride?.payment_method) || 'Cash'"></span>
                                            </div>
                                        </div>
                                        <div class="text-sm text-gray-600 dark:text-gray-300 space-y-1.5 mb-4">
                                            <p><strong>Pickup:</strong> <span x-text="req.ride ? req.ride.pickup_location : (req.driver_booking ? req.driver_booking.pickup_location : '')"></span></p>
                                            <p x-show="(req.ride && req.ride.dropoff_location) || (req.driver_booking && req.driver_booking.dropoff_location)"><strong>Dropoff:</strong> <span x-text="req.ride ? req.ride.dropoff_location : (req.driver_booking ? req.driver_booking.dropoff_location : '')"></span></p>
                                            <p x-show="req.driver_booking"><strong>Schedule & Duration:</strong> <span x-text="req.driver_booking ? (req.driver_booking.start_date + ' (' + req.driver_booking.duration_count + ' ' + req.driver_booking.duration_type + ')') : ''"></span></p>
                                            <p x-show="req.ride && req.ride.vehicle_type"><strong>Vehicle:</strong> <span x-text="req.ride ? req.ride.vehicle_type : ''"></span></p>
                                            <p><strong>Expires In:</strong> <span class="text-red-500 font-bold" x-text="Math.max(0, Math.floor((new Date(req.expires_at) - new Date()) / 1000)) + 's'"></span></p>
                                        </div>
                                        <div class="flex gap-3 pt-3 border-t border-indigo-100 dark:border-indigo-800/30">
                                            <button type="button" @click.stop.prevent="respondToRequest(req.id, 'accepted')" :disabled="responding" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-bold rounded-xl text-xs shadow-sm flex items-center gap-1.5 cursor-pointer">
                                                <svg x-show="responding" class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-dasharray="32" stroke-dashoffset="12"/></svg>
                                                <span>✓ Accept Request & Earn</span> <span x-text="req.driver_booking ? (req.driver_booking.currency + ' ' + parseFloat(req.driver_booking.total_price).toFixed(2)) : (req.ride && req.ride.fare && parseFloat(req.ride.fare) > 0 ? '$' + parseFloat(req.ride.fare).toFixed(2) : '$35.00')"></span>
                                            </button>
                                            <button type="button" @click.stop.prevent="respondToRequest(req.id, 'rejected')" :disabled="responding" class="px-5 py-2.5 border border-gray-300 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/10 font-bold rounded-xl text-xs cursor-pointer">
                                                Decline
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Active Rides (Accepted rides with lifecycle controls) -->
                    <div x-data="activeRides()" x-init="init()" class="bg-white dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-3xl p-6 shadow-sm">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse"></span>
                            Active Rides
                        </h2>
                        
                        <template x-if="rides.length === 0">
                            <p class="text-gray-500 dark:text-gray-400 text-sm italic">No active rides at the moment.</p>
                        </template>
                        
                        <div class="space-y-4">
                            <template x-for="ride in rides" :key="ride.id">
                                <div class="border border-emerald-200 dark:border-emerald-800/30 bg-emerald-50/50 dark:bg-emerald-900/10 rounded-2xl overflow-hidden">
                                    <!-- Map Preview with Route Path -->
                                    <img :src="getMapUrl(ride.pickup_location, ride.dropoff_location, '0x10b981ff')" alt="Route map" class="w-full h-[120px] object-cover" loading="lazy" onerror="this.style.display='none'">

                                    <div class="p-5">
                                        <div class="flex justify-between items-start mb-3">
                                            <div>
                                                <span class="text-xs font-extrabold uppercase px-2.5 py-1 rounded-lg" 
                                                    :class="{
                                                        'bg-yellow-100 text-yellow-700': ride.status === 'accepted',
                                                        'bg-blue-100 text-blue-700': ride.status === 'en_route',
                                                        'bg-amber-100 text-amber-700': ride.status === 'arrived',
                                                        'bg-emerald-100 text-emerald-700': ride.status === 'in_progress',
                                                        'bg-green-100 text-green-700': ride.status === 'completed'
                                                    }" x-text="ride.status.replace('_',' ').toUpperCase()"></span>
                                                <h4 class="font-bold text-gray-900 dark:text-white text-base mt-2" x-text="'Ride #' + ride.id"></h4>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-black text-2xl text-emerald-600 dark:text-emerald-400" x-text="ride.fare && parseFloat(ride.fare) > 0 ? '$' + parseFloat(ride.fare).toFixed(2) : '$35.00'"></p>
                                                <span class="inline-flex items-center gap-1 text-[11px] font-bold uppercase text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-white/10 px-2 py-0.5 rounded" x-text="ride.payment_method || 'Cash'"></span>
                                            </div>
                                        </div>
                                        <div class="text-sm text-gray-600 dark:text-gray-300 space-y-1 mb-4">
                                            <p><strong>Rider:</strong> <span x-text="ride.rider?.name || 'Rider'"></span></p>
                                            <p><strong>📍 Pickup:</strong> <span x-text="ride.pickup_location"></span></p>
                                            <template x-if="ride.stops && ride.stops.length > 0">
                                                <div class="my-1.5 pl-3 border-l-2 border-dashed border-amber-400 space-y-1">
                                                    <template x-for="st in ride.stops" :key="st.order">
                                                        <p class="text-xs font-semibold text-amber-700 dark:text-amber-300">
                                                            <span class="font-extrabold uppercase text-[10px]" x-text="'📍 Stop ' + st.order + ':'"></span>
                                                            <span x-text="st.location"></span>
                                                        </p>
                                                    </template>
                                                </div>
                                            </template>
                                            <p><strong>🏁 Dropoff:</strong> <span x-text="ride.dropoff_location"></span></p>
                                        </div>
                                        
                                        <!-- Lifecycle action buttons -->
                                        <div class="flex gap-3 pt-3 border-t border-emerald-100 dark:border-emerald-800/30">
                                            <template x-if="ride.status === 'accepted'">
                                                <button @click="updateRideStatus(ride.id, 'en_route')" 
                                                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs shadow-md flex items-center gap-1.5 transition-colors"
                                                        style="background-color: #2563eb !important; color: #ffffff !important; font-weight: 700 !important; font-size: 12px !important;">
                                                    🚗 En Route to Pickup
                                                </button>
                                            </template>
                                            <template x-if="ride.status === 'en_route'">
                                                <button @click="updateRideStatus(ride.id, 'arrived')" 
                                                        class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs shadow-md flex items-center gap-1.5 transition-colors"
                                                        style="background-color: #d97706 !important; color: #ffffff !important; font-weight: 700 !important; font-size: 12px !important;">
                                                    📍 Arrived at Pickup
                                                </button>
                                            </template>
                                            <template x-if="ride.status === 'arrived'">
                                                <button @click="updateRideStatus(ride.id, 'in_progress')" 
                                                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md flex items-center gap-1.5 transition-colors"
                                                        style="background-color: #059669 !important; color: #ffffff !important; font-weight: 700 !important; font-size: 12px !important;">
                                                    ▶ Start Trip
                                                </button>
                                            </template>
                                            <template x-if="ride.status === 'in_progress'">
                                                <button @click="updateRideStatus(ride.id, 'completed')" 
                                                        class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl text-xs shadow-md flex items-center gap-1.5 transition-colors"
                                                        style="background-color: #16a34a !important; color: #ffffff !important; font-weight: 700 !important; font-size: 12px !important;">
                                                    ✓ Complete Trip
                                                </button>
                                            </template>

                                            <!-- Turn-by-Turn Google Maps Navigation -->
                                            <template x-if="ride.status !== 'completed' && ride.status !== 'cancelled'">
                                                <a :href="getDriverNavUrl(ride)" target="_blank" rel="noopener noreferrer"
                                                   class="px-4 py-2.5 bg-slate-900 hover:bg-black text-white font-bold rounded-xl text-xs shadow-md flex items-center gap-1.5 transition-all border border-slate-700"
                                                   style="background-color: #0f172a !important; color: #ffffff !important; font-weight: 700 !important; font-size: 12px !important;">
                                                    <span>🧭</span>
                                                    <span x-text="ride.status === 'in_progress' ? 'Navigate to Destination' : 'Navigate to Pickup'"></span>
                                                </a>
                                            </template>

                                            <template x-if="ride.status === 'completed' && !ride.hasReview">
                                                <div class="w-full" x-data="{ rating: 0, comment: '', submitted: false }">
                                                    <p class="text-sm font-bold text-gray-900 dark:text-white mb-2">Rate this rider:</p>
                                                    <div class="flex gap-1 mb-2">
                                                        <template x-for="s in [1,2,3,4,5]" :key="s">
                                                            <button @click="rating = s" class="text-2xl" :class="s <= rating ? 'text-yellow-400' : 'text-gray-300'">★</button>
                                                        </template>
                                                    </div>
                                                    <input x-model="comment" placeholder="Comment (optional)" class="w-full bg-gray-50 dark:bg-[#111] border border-gray-200 dark:border-white/10 rounded-lg px-3 py-2 text-sm mb-2">
                                                    <button @click="if(rating>0){ submitDriverReview(ride.id, rating, comment); ride.hasReview=true; submitted=true; }" :disabled="rating<1" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-bold rounded-lg text-xs">Submit Review</button>
                                                    <p x-show="submitted" class="text-green-600 text-xs font-bold mt-1">✓ Review submitted</p>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Completed Past Trips -->
                    <div class="bg-white dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-3xl p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Completed Trips ({{ $completedRides->count() + $completedDriverBookings->count() }})</h2>
                            <a href="/my-rides" class="text-sm text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">View all →</a>
                        </div>
                        
                        @php
                            $recentTrips = $completedRides->sortByDesc('updated_at')->take(5);
                        @endphp

                        @if($recentTrips->isEmpty() && $completedDriverBookings->isEmpty())
                            <p class="text-gray-500 dark:text-gray-400 text-sm italic">No completed trips yet.</p>
                        @else
                            <div class="space-y-4">
                                @foreach($recentTrips as $trip)
                                    @php
                                        $pickup = urlencode($trip->pickup_location);
                                        $dropoff = urlencode($trip->dropoff_location);
                                        $tripFare = ($trip->fare && $trip->fare > 0) ? $trip->fare : 24.50;
                                        $staticMap = "https://maps.googleapis.com/maps/api/staticmap?size=600x130&scale=2&maptype=roadmap&markers=size:small%7Ccolor:green%7Clabel:A%7C{$pickup}&markers=size:small%7Ccolor:red%7Clabel:B%7C{$dropoff}&path=color:0x10b981%7Cweight:4%7Cgeodesic:true%7C{$pickup}%7C{$dropoff}&key={$mapKey}&style=feature:all%7Celement:labels%7Cvisibility:simplified";
                                    @endphp
                                    <div class="border border-gray-100 dark:border-white/10 rounded-2xl overflow-hidden hover:shadow-md transition-shadow">
                                        <!-- Mini Map with Route Path -->
                                        <img src="{{ $staticMap }}" alt="Route" class="w-full h-[110px] object-cover" loading="lazy" onerror="this.style.display='none'">
                                        
                                        <!-- Details -->
                                        <div class="p-4">
                                            <div class="flex items-start gap-3">
                                                <!-- Route dots -->
                                                <div class="flex flex-col items-center pt-1 shrink-0">
                                                    <div class="w-2.5 h-2.5 rounded-full bg-green-500"></div>
                                                    <div class="w-0.5 h-5 bg-gray-200 dark:bg-white/10 my-0.5"></div>
                                                    <div class="w-2.5 h-2.5 rounded-full bg-red-500"></div>
                                                </div>
                                                <!-- Locations -->
                                                <div class="flex-1 min-w-0 space-y-1">
                                                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ Str::limit($trip->pickup_location, 45) }}</p>
                                                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ Str::limit($trip->dropoff_location, 45) }}</p>
                                                </div>
                                                <!-- Fare -->
                                                <div class="text-right shrink-0">
                                                    <p class="font-black text-lg text-green-600 dark:text-green-400">${{ number_format($tripFare, 2) }}</p>
                                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase text-gray-400">
                                                        {{ ucfirst($trip->payment_method ?? 'Cash') }}
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <!-- Footer: Date + Rider -->
                                            <div class="flex items-center gap-3 mt-3 pt-3 border-t border-gray-100 dark:border-white/10 text-xs text-gray-400">
                                                <span>{{ $trip->updated_at->format('M d, Y · h:i A') }}</span>
                                                @if($trip->rider)
                                                    <span>· Rider: <span class="text-gray-600 dark:text-gray-300 font-medium">{{ $trip->rider->name }}</span></span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                @foreach($completedDriverBookings->sortByDesc('updated_at')->take(3) as $bk)
                                    <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-white/5 rounded-xl">
                                        <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-bold text-sm text-gray-900 dark:text-white">Hire: {{ $bk->client->name ?? 'Client' }}</p>
                                            <p class="text-xs text-gray-500">{{ $bk->start_date }} — {{ $bk->duration_days ?? 1 }} day(s)</p>
                                            <span class="text-xs text-gray-400">{{ $bk->updated_at->format('M d, h:i A') }}</span>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <p class="font-extrabold text-base text-green-600 dark:text-green-400">${{ number_format($bk->total_price, 2) }}</p>
                                            <p class="text-xs text-gray-400">Hiring</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Available Ride Requests (Pending / Unassigned Rides) -->
                    <div class="bg-white dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-3xl p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-indigo-500 {{ $pendingRides->isNotEmpty() ? 'animate-ping' : '' }}"></span>
                                Available Ride Requests ({{ $pendingRides->count() }})
                            </h2>
                            <span class="text-xs text-gray-400 font-bold uppercase tracking-wider">On-Demand Rides</span>
                        </div>

                        @if($pendingRides->isEmpty())
                            <p class="text-gray-500 dark:text-gray-400 text-sm italic">No pending ride requests right now.</p>
                        @else
                            <div class="space-y-4">
                                @foreach($pendingRides as $pr)
                                    <div class="p-5 border border-indigo-200 dark:border-indigo-800/40 rounded-2xl bg-indigo-50/40 dark:bg-indigo-950/20">
                                        <div class="flex justify-between items-start mb-3">
                                            <div>
                                                <span class="text-xs font-extrabold uppercase px-2.5 py-1 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded-lg">
                                                    {{ $pr->vehicle_type ?? 'Standard' }} Ride #{{ $pr->id }}
                                                </span>
                                                <h4 class="font-bold text-gray-900 dark:text-white text-base mt-2">Rider: {{ $pr->rider->name ?? $pr->passenger_name ?? 'Guest Passenger' }}</h4>
                                            </div>
                                            <div class="text-right">
                                                <span class="font-black text-2xl text-emerald-600 dark:text-emerald-400">${{ number_format($pr->fare ?: $pr->total_amount, 2) }}</span>
                                                <span class="text-xs text-gray-400 block font-bold uppercase">{{ $pr->payment_method ?? 'Cash' }}</span>
                                            </div>
                                        </div>

                                        <div class="text-sm text-gray-600 dark:text-gray-300 space-y-1.5 mb-4">
                                            <p><strong>📍 Pickup:</strong> {{ $pr->pickup_location }}</p>
                                            @if($pr->dropoff_location)
                                                <p><strong>🏁 Destination:</strong> {{ $pr->dropoff_location }}</p>
                                            @endif
                                            @if($pr->distance_km)
                                                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Distance: ~{{ number_format($pr->distance_km, 1) }} km ({{ $pr->duration_minutes ?? 15 }} mins)</p>
                                            @endif
                                        </div>

                                        <div class="flex gap-3 pt-3 border-t border-indigo-100 dark:border-indigo-800/30">
                                            <form action="/driver/ride/{{ $pr->id }}/accept" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-sm flex items-center gap-1.5 cursor-pointer">
                                                    <span>✓ Accept Ride & Earn ${{ number_format($pr->fare ?: $pr->total_amount, 2) }}</span>
                                                </button>
                                            </form>
                                            <form action="/driver/ride/{{ $pr->id }}/decline" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="px-5 py-2.5 border border-gray-300 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/10 font-bold rounded-xl text-xs cursor-pointer">
                                                    Decline
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="bg-white dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-3xl p-6 shadow-sm">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Pending Hiring Requests ({{ $pendingDriverBookings->count() }})</h2>
                        @if($pendingDriverBookings->isEmpty())
                            <p class="text-gray-500 dark:text-gray-400 text-sm italic">No pending driver hiring requests.</p>
                        @else
                            <div class="space-y-4">
                                @foreach($pendingDriverBookings as $bk)
                                    <div class="p-5 border border-gray-200 dark:border-white/10 rounded-2xl bg-gray-50/50 dark:bg-white/5">
                                        <div class="flex justify-between items-start mb-3">
                                            <div>
                                                <span class="text-xs font-extrabold uppercase px-2.5 py-1 bg-brand-100 dark:bg-brand-900/30 text-brand-700 dark:text-brand-400 rounded-lg">
                                                    {{ ucfirst($bk->service_category) }} Driver Booking
                                                </span>
                                                <h4 class="font-bold text-gray-900 dark:text-white text-base mt-2">Client: {{ $bk->client->name ?? 'Client' }}</h4>
                                            </div>
                                            <div class="text-right">
                                                <span class="font-extrabold text-lg text-gray-900 dark:text-white">{{ $bk->currency }} {{ number_format($bk->total_price, 2) }}</span>
                                                <span class="text-xs text-gray-400 block">Method: {{ strtoupper($bk->payment_method) }}</span>
                                            </div>
                                        </div>

                                        <div class="text-sm text-gray-600 dark:text-gray-300 space-y-1 mb-4">
                                            <p><strong>Schedule:</strong> {{ $bk->start_date }} at {{ $bk->start_time }} ({{ $bk->duration_count }} {{ $bk->duration_type }})</p>
                                            <p><strong>Pickup:</strong> {{ $bk->pickup_location }}</p>
                                            @if($bk->service_category === 'private')
                                                <p><strong>Vehicle:</strong> {{ $bk->car_type }} | Reg: {{ $bk->registration_number }} ({{ $bk->transmission }})</p>
                                            @else
                                                <p><strong>Commercial Job:</strong> {{ $bk->commercial_service_type }}</p>
                                                <p><strong>Cargo Details:</strong> {{ $bk->cargo_details ?? 'N/A' }}</p>
                                            @endif
                                        </div>

                                        <div class="flex gap-3 pt-3 border-t border-gray-100 dark:border-white/10">
                                            <form action="/driver-booking/{{ $bk->id }}/update-status" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="status" value="accepted">
                                                <button type="submit" class="px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl text-xs shadow-sm">
                                                    Accept Booking
                                                </button>
                                            </form>
                                            <form action="/driver-booking/{{ $bk->id }}/update-status" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="px-5 py-2 border border-gray-300 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/10 font-bold rounded-xl text-xs">
                                                    Decline
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- License Verification Upload Section -->
                    <div class="bg-white dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-3xl p-6 shadow-sm">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Driver License Verification</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Upload your driver's license to get verified and increase client booking trust.</p>

                        <form action="/driver/verify-license" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">License Number *</label>
                                    <input type="text" name="license_number" required value="{{ $profile->license_number }}" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-sm font-semibold text-gray-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Country / State *</label>
                                    <input type="text" name="license_country" required value="{{ $profile->license_country ?? $profile->country }}" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-sm text-gray-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Expiry Date *</label>
                                    <input type="date" name="license_expiry" required value="{{ $profile->license_expiry }}" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-sm text-gray-900 dark:text-white">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">License Front Image</label>
                                    <input type="file" name="license_front" accept="image/*" class="w-full text-xs text-gray-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">License Back Image</label>
                                    <input type="file" name="license_back" accept="image/*" class="w-full text-xs text-gray-500">
                                </div>
                            </div>

                            <button type="submit" class="px-6 py-3 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl text-sm shadow-sm transition-all">
                                Submit Verification Documents
                            </button>
                        </form>
                    </div>

                    <!-- Guarantor Information & Document Submission Section -->
                    <div class="bg-white dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-3xl p-6 shadow-sm">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Guarantor Verification Information</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Submit your guarantor's identity and signed liability agreement to unlock vehicle hiring opportunities.</p>

                        <form action="/driver/submit-guarantor" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Guarantor Legal Full Name *</label>
                                    <input type="text" name="full_name" required placeholder="e.g. Kwame Mensah" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-sm font-semibold text-gray-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Ghana Card Number (NIA) *</label>
                                    <input type="text" name="ghana_card_number" required placeholder="GHA-712345678-9" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-sm font-semibold text-gray-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Relationship to Driver *</label>
                                    <input type="text" name="relationship" required placeholder="e.g. Sibling / Employer / Parent" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-sm text-gray-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Primary Phone Number *</label>
                                    <input type="text" name="primary_phone" required placeholder="+233 24 123 4567" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-sm font-semibold text-gray-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Alternative Phone Number</label>
                                    <input type="text" name="alt_phone" placeholder="+233 20 987 6543" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-sm text-gray-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Ghana Post GPS Address</label>
                                    <input type="text" name="digital_address" placeholder="GA-123-4567" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-sm text-gray-900 dark:text-white">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Physical Residential Address</label>
                                    <input type="text" name="physical_address" placeholder="House No 14, East Legon, Accra" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-sm text-gray-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Employer / Business Name & Occupation</label>
                                    <input type="text" name="employer_business" placeholder="Business / Employer Name" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-sm text-gray-900 dark:text-white">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Ghana Card Front Image</label>
                                    <input type="file" name="ghana_card_front" accept="image/*,.pdf" class="w-full text-xs text-gray-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Ghana Card Back Image</label>
                                    <input type="file" name="ghana_card_back" accept="image/*,.pdf" class="w-full text-xs text-gray-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Signed Liability Agreement</label>
                                    <input type="file" name="signed_liability_agreement" accept="image/*,.pdf" class="w-full text-xs text-gray-500">
                                </div>
                            </div>

                            <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm shadow-sm transition-all">
                                Submit Guarantor Details
                            </button>
                        </form>
                    </div>

                </div>

                <!-- Sidebar (Profile & Rates) -->
                <div class="space-y-8">
                    <!-- Driver Profile Details -->
                    <div class="bg-white dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-3xl p-6 shadow-sm">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Profile & Status</h2>
                        <ul class="space-y-3 text-sm">
                            <li class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">License Number</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $profile->masked_license }}</span>
                            </li>
                            <li class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Rating</span>
                                <span class="font-bold text-gray-900 dark:text-white flex items-center gap-1 text-amber-500">
                                    ★ {{ $profile->rating }}
                                </span>
                            </li>
                            <li class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Total Completed Trips</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ ($completedRides->count() + $completedDriverBookings->count()) }}</span>
                            </li>
                            <li class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Total Earnings</span>
                                <span class="font-extrabold text-emerald-600 dark:text-emerald-400">${{ number_format($monthlyEarnings, 2) }}</span>
                            </li>
                            <li class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Hourly Rate</span>
                                <span class="font-bold text-gray-900 dark:text-white">${{ number_format($profile->hourly_rate ?? 25.00, 2) }}</span>
                            </li>
                            <li class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Daily Rate</span>
                                <span class="font-bold text-gray-900 dark:text-white">${{ number_format($profile->daily_rate ?? (($profile->hourly_rate ?? 25) * 8 * 0.85), 2) }}</span>
                            </li>

                            <li class="pt-4 border-t border-gray-100 dark:border-white/10">
                                <form action="/driver/toggle-availability" method="POST">
                                    @csrf
                                    <label class="flex justify-between items-center cursor-pointer">
                                        <span class="font-bold text-gray-900 dark:text-white">Available for Booking</span>
                                        <input type="checkbox" name="is_available" value="1" onchange="this.form.submit()" {{ $profile->is_available ? 'checked' : '' }} class="w-5 h-5 accent-brand-500">
                                    </label>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('driverPolling', () => ({
                requests: [],
                responding: false,
                pollingInterval: null,
                countdownInterval: null,
                mapKey: '{{ $mapKey }}',
                
                getMapUrl(pickup, dropoff, color = '0x6366f1') {
                    const p = encodeURIComponent(pickup || '');
                    const d = encodeURIComponent(dropoff || '');
                    return `https://maps.googleapis.com/maps/api/staticmap?size=600x130&scale=2&maptype=roadmap&markers=size:small%7Ccolor:green%7Clabel:A%7C${p}&markers=size:small%7Ccolor:red%7Clabel:B%7C${d}&path=color:${color}%7Cweight:4%7Cgeodesic:true%7C${p}%7C${d}&key=${this.mapKey}&style=feature:all%7Celement:labels%7Cvisibility:simplified`;
                },
                
                initPolling() {
                    this.fetchRequests();
                    this.pollingInterval = setInterval(() => this.fetchRequests(), 5000); // Check every 5s
                    this.countdownInterval = setInterval(() => {
                        // Force reactivity update for the countdown timer
                        this.requests = [...this.requests];
                    }, 1000);
                },
                
                async fetchRequests() {
                    if (document.hidden) return;
                    try {
                        const res = await fetch('/api/driver/requests');
                        if (res.ok) {
                            this.requests = await res.json();
                        }
                    } catch (e) {
                        console.error('Error fetching requests', e);
                    }
                },
                
                async respondToRequest(id, status) {
                    if (this.responding) return;
                    this.responding = true;
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;
                        const res = await fetch(`/api/driver/requests/${id}/respond`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken || ''
                            },
                            body: JSON.stringify({ status })
                        });
                        
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.requests = this.requests.filter(r => r.id !== id);
                            if (status === 'accepted') {
                                window.location.reload(); // Reload to show active job
                            }
                        } else {
                            alert(data.error || 'Failed to process ride response.');
                        }
                    } catch (e) {
                        console.error('Error responding', e);
                        alert('Network error while processing response.');
                    }
                    this.responding = false;
                }
            }));

            Alpine.data('activeRides', () => ({
                rides: [],
                pollingTimer: null,
                mapKey: '{{ $mapKey }}',
                
                getMapUrl(pickup, dropoff, color = '0x10b981') {
                    const p = encodeURIComponent(pickup || '');
                    const d = encodeURIComponent(dropoff || '');
                    return `https://maps.googleapis.com/maps/api/staticmap?size=600x130&scale=2&maptype=roadmap&markers=size:small%7Ccolor:green%7Clabel:A%7C${p}&markers=size:small%7Ccolor:red%7Clabel:B%7C${d}&path=color:${color}%7Cweight:4%7Cgeodesic:true%7C${p}%7C${d}&key=${this.mapKey}&style=feature:all%7Celement:labels%7Cvisibility:simplified`;
                },

                getDriverNavUrl(ride) {
                    if (!ride) return '#';
                    const target = ride.status === 'in_progress' ? (ride.dropoff_location || '') : (ride.pickup_location || '');
                    return `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(target)}&travelmode=driving`;
                },
                
                init() {
                    this.fetchRides();
                    this.pollingTimer = setInterval(() => this.fetchRides(), 5000);
                },
                
                async fetchRides() {
                    try {
                        const res = await fetch('/api/driver/active-rides');
                        if (res.ok) {
                            this.rides = await res.json();
                        }
                    } catch (e) {
                        console.error('Error fetching active rides', e);
                    }
                },
                
                async updateRideStatus(rideId, newStatus) {
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;
                        const res = await fetch(`/api/ride/${rideId}/update-status`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({ status: newStatus })
                        });
                        if (res.ok) {
                            // Update local state
                            const ride = this.rides.find(r => r.id === rideId);
                            if (ride) ride.status = newStatus;
                        } else {
                            const data = await res.json();
                            alert(data.error || 'Failed to update status');
                        }
                    } catch (e) {
                        console.error('Error updating ride status', e);
                    }
                },
                
                async submitDriverReview(rideId, rating, comment) {
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;
                        await fetch(`/api/ride/${rideId}/review`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({ rating, comment })
                        });
                    } catch (e) {
                        console.error('Error submitting review', e);
                    }
                }
            }));

            // Background Driver GPS Location Pinger
            if (navigator.geolocation) {
                const sendGpsPing = (pos) => {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;
                    fetch('/api/driver/location', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || ''
                        },
                        body: JSON.stringify({
                            latitude: pos.coords.latitude,
                            longitude: pos.coords.longitude
                        })
                    }).catch(e => console.error('GPS ping error:', e));
                };

                navigator.geolocation.getCurrentPosition(sendGpsPing, null, { enableHighAccuracy: true });
                navigator.geolocation.watchPosition(sendGpsPing, null, { enableHighAccuracy: true, maximumAge: 5000, timeout: 10000 });
            }
        });
    </script>
</x-layout>
