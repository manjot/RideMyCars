<x-layout>
    <x-slot:title>Driver Dashboard — RideMyCars</x-slot>
    <div class="pt-24 pb-12 bg-gray-50 dark:bg-[#09090b] min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-100 border border-green-200 text-green-800 rounded-2xl text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-100 border border-red-200 text-red-800 rounded-2xl text-sm font-semibold">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('info'))
                <div class="mb-6 p-4 bg-blue-100 border border-blue-200 text-blue-800 rounded-2xl text-sm font-semibold">
                    {{ session('info') }}
                </div>
            @endif

            {{-- Inactive Account Warning Banner --}}
            @if(!$profile->is_live)
                <div class="mb-8 p-6 rounded-3xl bg-gradient-to-r from-amber-500/15 via-red-500/10 to-amber-500/5 border-2 border-amber-500/40 shadow-lg relative overflow-hidden">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-amber-500 text-gray-950 flex items-center justify-center font-black text-2xl shrink-0 shadow-md">
                                ⚠️
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-black uppercase bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400 border border-red-200 dark:border-red-800">
                                        Account Inactive
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold">
                                        Verification Action Required
                                    </span>
                                </div>
                                <h3 class="text-lg font-extrabold text-gray-900 dark:text-white">
                                    Your account is inactive
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1 max-w-2xl">
                                    Your account is inactive please connect to admin or check your profile section and make nessesory action.
                                    Upload your <strong>Vehicle Insurance</strong> and <strong>Vehicle Fitness (Roadworthy) certificate</strong> picture scans so admin can verify and make your account live.
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0 w-full md:w-auto">
                            <a href="#vehicle-certificates-section" class="flex-1 md:flex-none px-5 py-3 rounded-xl bg-amber-500 hover:bg-amber-600 text-gray-950 font-bold text-xs shadow-md transition-all flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                <span>Check Profile & Upload Certificates</span>
                            </a>
                            <a href="mailto:admin@ridemycars.com?subject=Driver%20Account%20Activation%20Request%20-%20{{ urlencode($user->name) }}" class="px-4 py-3 rounded-xl bg-white dark:bg-white/10 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/15 font-bold text-xs transition-all flex items-center justify-center">
                                Connect to Admin
                            </a>
                        </div>
                    </div>

                    <!-- Quick Status Summary -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-5 pt-4 border-t border-amber-500/20">
                        <div class="flex items-center gap-2.5 text-xs font-semibold">
                            <span class="w-2.5 h-2.5 rounded-full {{ $profile->vehicle_insurance_status === 'approved' ? 'bg-emerald-500' : ($profile->vehicle_insurance_status === 'submitted' || $profile->vehicle_insurance_status === 'under_review' ? 'bg-amber-500 animate-pulse' : 'bg-red-500') }}"></span>
                            <span class="text-gray-600 dark:text-gray-300">Vehicle Insurance:</span>
                            <span class="font-bold {{ $profile->vehicle_insurance_status === 'approved' ? 'text-emerald-600 dark:text-emerald-400' : ($profile->vehicle_insurance_status === 'submitted' || $profile->vehicle_insurance_status === 'under_review' ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400') }}">
                                {{ ucfirst(str_replace('_', ' ', $profile->vehicle_insurance_status ?? 'not submitted')) }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs font-semibold">
                            <span class="w-2.5 h-2.5 rounded-full {{ $profile->vehicle_fitness_status === 'approved' ? 'bg-emerald-500' : ($profile->vehicle_fitness_status === 'submitted' || $profile->vehicle_fitness_status === 'under_review' ? 'bg-amber-500 animate-pulse' : 'bg-red-500') }}"></span>
                            <span class="text-gray-600 dark:text-gray-300">Vehicle Fitness:</span>
                            <span class="font-bold {{ $profile->vehicle_fitness_status === 'approved' ? 'text-emerald-600 dark:text-emerald-400' : ($profile->vehicle_fitness_status === 'submitted' || $profile->vehicle_fitness_status === 'under_review' ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400') }}">
                                {{ ucfirst(str_replace('_', ' ', $profile->vehicle_fitness_status ?? 'not submitted')) }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs font-semibold">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                            <span class="text-gray-600 dark:text-gray-300">Dispatch Status:</span>
                            <span class="font-bold text-red-600 dark:text-red-400">Blocked (Inactive Account)</span>
                        </div>
                    </div>
                </div>
            @else
                <div class="mb-8 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                        </span>
                        <span class="text-sm font-bold text-emerald-800 dark:text-emerald-300">
                            Account is Live & Active — You are eligible to receive and accept trip requests from customers!
                        </span>
                    </div>
                    <span class="hidden sm:inline-flex px-3 py-1 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 rounded-lg text-xs font-black uppercase">
                        LIVE DRIVER
                    </span>
                </div>
            @endif

            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Welcome back, {{ $user->name }}</h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">Manage your driver hiring requests, active jobs, and verification.</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold uppercase
                        {{ $profile->is_live ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-800/30' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 border border-red-300 dark:border-red-800/30' }}">
                        <span class="w-2 h-2 rounded-full {{ $profile->is_live ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                        {{ $profile->is_live ? 'Account: Live (Active)' : 'Account: Inactive' }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold uppercase
                        {{ $profile->verification_status === 'verified' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 border border-green-300 dark:border-green-800/30' : 
                          ($profile->verification_status === 'submitted' || $profile->verification_status === 'under_review' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400') }}">
                        <span class="w-2 h-2 rounded-full bg-current"></span>
                        License: {{ str_replace('_', ' ', $profile->verification_status) }}
                    </span>
                    <a href="{{ route('download.driver') }}" download="RideMyCars-Driver.apk" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-500 hover:bg-amber-600 text-gray-950 font-bold text-xs shadow-md transition-all">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M3.609 1.814L13.792 12 3.61 22.186a1.994 1.994 0 0 1-.61-.954V2.768c.118-.363.33-.687.609-.954zm11.233 11.233l2.257 2.257-11.83 6.697 9.573-8.954zm2.257-2.094l2.845 1.611c.907.514.907 1.353 0 1.867l-2.845 1.611-2.09-2.09 2.09-2.999zm-2.257-2.093L5.27 0.906l11.83 6.697-2.258 2.257z"/></svg>
                        <span>Download Driver App</span>
                    </a>
                </div>
            </div>

            <!-- Mobile App Companion Banner -->
            <div class="mb-8 p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-amber-500/10 via-brand-500/5 to-transparent border border-amber-500/20 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-amber-500 text-gray-950 flex items-center justify-center font-black text-lg shrink-0">
                        🚕
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">Get real-time trip alerts on the go</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Install the RideMyCars Driver Mobile App for instant audio alerts, background GPS tracking, and turn-by-turn navigation.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0 w-full sm:w-auto justify-end">
                    <a href="{{ route('download.driver') }}" download="RideMyCars-Driver.apk" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-gray-950 rounded-xl text-xs font-black transition-all shadow-sm flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        <span>Download Driver APK</span>
                    </a>
                    <a href="/apps#driver-app" class="px-3.5 py-2 bg-white dark:bg-white/10 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-white/10 rounded-xl text-xs font-semibold hover:bg-gray-50 dark:hover:bg-white/15 transition-all">
                        Store / QR
                    </a>
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
                            <template x-for="req in requests" :key="req.assignment_id || req.id">
                                <div class="border border-indigo-300 dark:border-indigo-700 rounded-2xl bg-white/90 dark:bg-black/60 backdrop-blur-sm shadow-md overflow-hidden">
                                    <!-- Map Preview with Route Path -->
                                    <img :src="getMapUrl(req.pickup_location || (req.ride ? req.ride.pickup_location : ''), req.dropoff_location || (req.ride ? req.ride.dropoff_location : ''), '0x6366f1ff')" alt="Route map" class="w-full h-[120px] object-cover" loading="lazy" onerror="this.style.display='none'">
                                    
                                    <div class="p-5">
                                        <div class="flex justify-between items-start mb-3">
                                            <div>
                                                <span class="text-xs font-extrabold uppercase px-2.5 py-1 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded-lg" x-text="req.type === 'driver_booking' ? 'New Driver Hiring Request' : (req.type === 'package_delivery' ? 'New Delivery Request' : 'New Ride Request')">
                                                </span>
                                                <h4 class="font-bold text-gray-900 dark:text-white text-base mt-2" x-text="req.booking_id ? ('Booking #' + req.booking_id) : (req.ride_id ? ('Ride #' + req.ride_id) : (req.delivery_id ? ('Delivery #' + req.delivery_id) : 'New Request'))"></h4>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-black text-2xl text-emerald-600 dark:text-emerald-400" x-text="'$' + parseFloat(req.fare || req.total_price || 35.00).toFixed(2)"></p>
                                                <span class="inline-flex items-center gap-1 text-[11px] font-bold uppercase text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-white/10 px-2 py-0.5 rounded" x-text="req.payment_method || 'Stripe / MoMo'"></span>
                                            </div>
                                        </div>

                                        <!-- Customer & POC Contact Details -->
                                        <div class="p-3 bg-gray-50 dark:bg-white/5 rounded-xl text-xs space-y-1.5 mb-3 border border-gray-100 dark:border-white/10">
                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-500 font-medium">Customer:</span>
                                                <span class="font-bold text-gray-900 dark:text-white" x-text="req.customer_name || req.rider_name || 'Customer'"></span>
                                            </div>
                                            <template x-if="req.poc_name && req.poc_name !== (req.customer_name || req.rider_name)">
                                                <div class="flex justify-between items-center">
                                                    <span class="text-amber-600 font-bold">POC / Passenger:</span>
                                                    <span class="font-extrabold text-amber-600 dark:text-amber-400" x-text="req.poc_name"></span>
                                                </div>
                                            </template>
                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-500 font-medium">Contact Phone:</span>
                                                <span class="font-mono font-bold text-gray-800 dark:text-gray-200" x-text="req.poc_phone || req.customer_phone || req.rider_phone || 'Available on accept'"></span>
                                            </div>
                                        </div>

                                        <div class="text-sm text-gray-600 dark:text-gray-300 space-y-1.5 mb-4">
                                            <p><strong>📍 Pickup:</strong> <span x-text="req.pickup_location || (req.ride ? req.ride.pickup_location : '')"></span></p>
                                            <p x-show="req.dropoff_location || (req.ride && req.ride.dropoff_location)"><strong>🏁 Dropoff:</strong> <span x-text="req.dropoff_location || (req.ride ? req.ride.dropoff_location : '')"></span></p>
                                            <p x-show="req.duration_type"><strong>Schedule:</strong> <span x-text="req.start_date + ' (' + req.duration_count + ' ' + req.duration_type + ')'"></span></p>
                                            <p x-show="req.vehicle_type"><strong>Vehicle:</strong> <span x-text="req.vehicle_type"></span></p>
                                            <p x-show="req.expires_at"><strong>Expires In:</strong> <span class="text-red-500 font-bold" x-text="Math.max(0, Math.floor((new Date(req.expires_at) - new Date()) / 1000)) + 's'"></span></p>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-3 pt-3 border-t border-indigo-100 dark:border-indigo-800/30">
                                            <button type="button" @click.stop.prevent="respondToRequest(req.assignment_id || req.id, 'accepted')" :disabled="responding" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-bold rounded-xl text-xs shadow-sm flex items-center gap-1.5 cursor-pointer">
                                                <svg x-show="responding" class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-dasharray="32" stroke-dashoffset="12"/></svg>
                                                <span>✓ Accept Request & Earn</span> <span x-text="'$' + parseFloat(req.fare || req.total_price || 35.00).toFixed(2)"></span>
                                            </button>
                                            <button type="button" @click.stop.prevent="respondToRequest(req.assignment_id || req.id, 'rejected')" :disabled="responding" class="px-4 py-2.5 border border-gray-300 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/10 font-bold rounded-xl text-xs cursor-pointer">
                                                Decline
                                            </button>
                                            <template x-if="req.poc_phone || req.customer_phone || req.client_phone || req.rider_phone">
                                                <a :href="'tel:' + (req.poc_phone || req.customer_phone || req.client_phone || req.rider_phone)" 
                                                   class="px-4 py-2.5 bg-green-50 hover:bg-green-100 dark:bg-green-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800/40 font-bold rounded-xl text-xs flex items-center gap-1.5 transition-all">
                                                    <span>📞 Call Customer</span>
                                                </a>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Active Rides (Accepted rides with lifecycle controls) -->
                    <div x-data="activeRides()" x-init="init()" class="bg-white dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-3xl p-6 shadow-sm">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse"></span>
                                <span>Active Rides</span>
                                <span class="px-2 py-0.5 text-xs font-extrabold rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300" x-text="rides.length"></span>
                            </div>
                            <button type="button" @click="fetchRides()" class="text-xs font-semibold text-gray-500 hover:text-black dark:hover:text-white transition-colors">
                                ↻ Refresh
                            </button>
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
                                                <span class="inline-flex items-center gap-1 text-[11px] font-bold uppercase text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-white/10 px-2 py-0.5 rounded" x-text="ride.payment_method || 'Stripe / MoMo'"></span>
                                            </div>
                                        </div>
                                        <div class="text-sm text-gray-600 dark:text-gray-300 space-y-2 mb-4">
                                            <div class="flex items-center justify-between">
                                                <p><strong>Customer:</strong> <span class="font-bold text-gray-900 dark:text-white" x-text="ride.customer_name || ride.rider?.name || ride.rider_name || 'Customer'"></span></p>
                                                <template x-if="ride.customer_phone || ride.rider_phone">
                                                    <a :href="'tel:' + (ride.customer_phone || ride.rider_phone)" class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-sm transition-all" style="color: #ffffff !important;">
                                                        <span>📞 Call</span>
                                                        <span x-text="ride.customer_phone || ride.rider_phone"></span>
                                                    </a>
                                                </template>
                                            </div>
                                            <template x-if="ride.poc_name && ride.poc_name !== (ride.customer_name || ride.rider?.name || ride.rider_name)">
                                                <div class="flex items-center justify-between bg-amber-50 dark:bg-amber-950/30 p-2.5 rounded-xl border border-amber-200 dark:border-amber-800/40">
                                                    <div>
                                                        <span class="text-[10px] font-extrabold uppercase text-amber-700 dark:text-amber-400 block">Passenger / POC</span>
                                                        <p class="font-bold text-gray-900 dark:text-white text-xs" x-text="ride.poc_name"></p>
                                                    </div>
                                                    <template x-if="ride.poc_phone">
                                                        <a :href="'tel:' + ride.poc_phone" class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold shadow-sm" style="color: #ffffff !important;">
                                                            <span>📞 Call POC</span>
                                                            <span x-text="ride.poc_phone"></span>
                                                        </a>
                                                    </template>
                                                </div>
                                            </template>
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
                                                        {{ ucfirst($trip->payment_method ?? 'Stripe') }}
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
                                                <h4 class="font-bold text-gray-900 dark:text-white text-base mt-2">
                                                    Customer: {{ $pr->customer_name ?? $pr->rider->name ?? $pr->passenger_name ?? 'Guest Passenger' }}
                                                </h4>
                                                @php
                                                    $custPhone = $pr->customer_phone ?? $pr->rider->phone ?? $pr->passenger_phone;
                                                    $pocName = $pr->poc_name ?? $pr->passenger_name;
                                                    $pocPhone = $pr->poc_phone ?? $pr->passenger_phone;
                                                    $isPocDifferent = $pocName && $pocName !== ($pr->customer_name ?? $pr->rider->name);
                                                @endphp
                                                @if($custPhone)
                                                    <div class="mt-1">
                                                        <a href="tel:{{ $custPhone }}" class="inline-flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400 font-bold hover:underline">
                                                            📞 Customer Phone: {{ $custPhone }}
                                                        </a>
                                                    </div>
                                                @endif
                                                @if($isPocDifferent)
                                                    <div class="mt-1.5 p-2 bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/30 rounded-xl">
                                                        <span class="text-[10px] font-extrabold uppercase text-amber-700 dark:text-amber-300 block">Passenger / POC</span>
                                                        <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $pocName }}</span>
                                                        @if($pocPhone)
                                                            <a href="tel:{{ $pocPhone }}" class="block text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline mt-0.5">📞 {{ $pocPhone }}</a>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="text-right">
                                                <span class="font-black text-2xl text-emerald-600 dark:text-emerald-400">${{ number_format($pr->fare ?: $pr->total_amount, 2) }}</span>
                                                <span class="text-xs text-gray-400 block font-bold uppercase">{{ $pr->payment_method ?? 'Stripe' }}</span>
                                                @if($custPhone)
                                                    <a href="tel:{{ $custPhone }}" class="mt-2 inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all" style="color: #ffffff !important;">
                                                        📞 Call Rider
                                                    </a>
                                                @endif
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
                                                @php
                                                    $clientPhone = $bk->client->phone ?? $bk->contact_phone;
                                                    $pocName = $bk->contact_person_name;
                                                    $pocPhone = $bk->contact_phone;
                                                @endphp
                                                @if($clientPhone)
                                                    <div class="mt-1">
                                                        <a href="tel:{{ $clientPhone }}" class="inline-flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400 font-bold hover:underline">
                                                            📞 Client Phone: {{ $clientPhone }}
                                                        </a>
                                                    </div>
                                                @endif
                                                @if($pocName && $pocName !== ($bk->client->name ?? ''))
                                                    <div class="mt-1.5 p-2 bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/30 rounded-xl">
                                                        <span class="text-[10px] font-extrabold uppercase text-amber-700 dark:text-amber-300 block">Person of Contact</span>
                                                        <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $pocName }}</span>
                                                        @if($pocPhone)
                                                            <a href="tel:{{ $pocPhone }}" class="block text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline mt-0.5">📞 {{ $pocPhone }}</a>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="text-right">
                                                <span class="font-extrabold text-lg text-gray-900 dark:text-white">{{ $bk->currency }} {{ number_format($bk->total_price, 2) }}</span>
                                                <span class="text-xs text-gray-400 block font-bold">Method: {{ strtoupper($bk->payment_method) }}</span>
                                                @if($clientPhone)
                                                    <a href="tel:{{ $clientPhone }}" class="mt-2 inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all" style="color: #ffffff !important;">
                                                        📞 Call Client
                                                    </a>
                                                @endif
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

                    <!-- Vehicle Insurance & Vehicle Fitness (Roadworthy) Certificate Upload Section -->
                    <div id="vehicle-certificates-section" class="bg-white dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-3xl p-6 shadow-sm mb-8">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-5 pb-4 border-b border-gray-100 dark:border-white/10">
                            <div>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <span>🛡️</span>
                                    <span>Vehicle Certificates Verification</span>
                                </h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    Upload picture scans of your Vehicle Insurance and Roadworthy (Vehicle Fitness) certificates. Admin will review and approve these documents to activate your account.
                                </p>
                            </div>
                            <div>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-black uppercase
                                    {{ $profile->is_certificates_approved ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' }}">
                                    <span class="w-2 h-2 rounded-full {{ $profile->is_certificates_approved ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                    {{ $profile->is_certificates_approved ? 'Certificates Approved' : 'Certificates Pending' }}
                                </span>
                            </div>
                        </div>

                        <form action="/driver/upload-vehicle-certificates" method="POST" enctype="multipart/form-data" class="space-y-6">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Vehicle Insurance Card -->
                                <div class="p-5 rounded-2xl border {{ $profile->vehicle_insurance_status === 'rejected' ? 'border-red-300 bg-red-50/40 dark:bg-red-950/20 dark:border-red-900/40' : ($profile->vehicle_insurance_status === 'approved' ? 'border-emerald-300 bg-emerald-50/40 dark:bg-emerald-950/20 dark:border-emerald-900/40' : 'border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-white/5') }} flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between mb-3">
                                            <h3 class="font-extrabold text-sm text-gray-900 dark:text-white flex items-center gap-2">
                                                <span>📄</span>
                                                <span>Vehicle Insurance Certificate</span>
                                            </h3>
                                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black uppercase
                                                {{ $profile->vehicle_insurance_status === 'approved' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400' :
                                                  ($profile->vehicle_insurance_status === 'submitted' || $profile->vehicle_insurance_status === 'under_review' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400' :
                                                  ($profile->vehicle_insurance_status === 'rejected' ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300')) }}">
                                                {{ ucfirst(str_replace('_', ' ', $profile->vehicle_insurance_status ?? 'not submitted')) }}
                                            </span>
                                        </div>

                                        @if($profile->vehicle_insurance_status === 'rejected')
                                            <div class="mb-4 p-3 bg-red-100/70 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl text-left">
                                                <p class="text-xs font-bold text-red-800 dark:text-red-300">
                                                    ⚠️ Rejected by Admin:
                                                </p>
                                                <p class="text-xs text-red-700 dark:text-red-300 mt-0.5">
                                                    {{ $profile->vehicle_insurance_rejection_reason ?? 'Your certificate did not meet requirements. Please re-upload a clear copy.' }}
                                                </p>
                                                <p class="text-[10px] font-bold text-red-600 dark:text-red-400 mt-1">
                                                    Action Required: Please upload a clear picture scan of your valid insurance below.
                                                </p>
                                            </div>
                                        @elseif($profile->vehicle_insurance_status === 'approved')
                                            <div class="mb-4 p-3 bg-emerald-100/70 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-xl">
                                                <p class="text-xs font-bold text-emerald-800 dark:text-emerald-300">
                                                    ✓ Verified & Approved
                                                </p>
                                                <p class="text-[11px] text-emerald-700 dark:text-emerald-400 mt-0.5">
                                                    Your vehicle insurance is approved by admin.
                                                </p>
                                            </div>
                                        @elseif($profile->vehicle_insurance_status === 'submitted' || $profile->vehicle_insurance_status === 'under_review')
                                            <div class="mb-4 p-3 bg-amber-100/70 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 rounded-xl">
                                                <p class="text-xs font-bold text-amber-800 dark:text-amber-300">
                                                    ⏳ Under Review
                                                </p>
                                                <p class="text-[11px] text-amber-700 dark:text-amber-400 mt-0.5">
                                                    Admin is currently reviewing your uploaded insurance scan.
                                                </p>
                                            </div>
                                        @endif

                                        @if($profile->insurance_certificate_url)
                                            <div class="mb-4 p-3 bg-white dark:bg-black/40 rounded-xl border border-gray-200 dark:border-white/10 flex items-center justify-between">
                                                <div class="flex items-center gap-2.5 overflow-hidden">
                                                    <span class="text-lg">📷</span>
                                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300 truncate">Current Insurance Scan</span>
                                                </div>
                                                <a href="{{ $profile->insurance_certificate_url }}" target="_blank" class="px-2.5 py-1 bg-brand-500 hover:bg-brand-600 text-white rounded-lg text-[11px] font-bold shrink-0 shadow-sm">
                                                    View Scan ↗
                                                </a>
                                            </div>
                                        @endif

                                        <div class="space-y-3">
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                                    {{ $profile->vehicle_insurance_image ? 'Upload New / Replace Insurance Picture Scan' : 'Upload Insurance Picture Scan *' }}
                                                </label>
                                                <input type="file" name="vehicle_insurance" accept="image/*,.pdf" class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-500 file:text-white hover:file:bg-brand-600 cursor-pointer">
                                                <p class="text-[10px] text-gray-400 mt-1">Photo scan (JPEG, PNG, WEBP) or PDF up to 10MB.</p>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Insurance Expiry Date</label>
                                                <input type="date" name="vehicle_insurance_expiry" value="{{ $profile->vehicle_insurance_expiry ? \Carbon\Carbon::parse($profile->vehicle_insurance_expiry)->format('Y-m-d') : '' }}" class="w-full px-3 py-2 bg-white dark:bg-black/30 border border-gray-200 dark:border-white/10 rounded-xl text-xs text-gray-900 dark:text-white">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Vehicle Fitness (Roadworthy) Card -->
                                <div class="p-5 rounded-2xl border {{ $profile->vehicle_fitness_status === 'rejected' ? 'border-red-300 bg-red-50/40 dark:bg-red-950/20 dark:border-red-900/40' : ($profile->vehicle_fitness_status === 'approved' ? 'border-emerald-300 bg-emerald-50/40 dark:bg-emerald-950/20 dark:border-emerald-900/40' : 'border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-white/5') }} flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between mb-3">
                                            <h3 class="font-extrabold text-sm text-gray-900 dark:text-white flex items-center gap-2">
                                                <span>🚗</span>
                                                <span>Vehicle Fitness (Roadworthy)</span>
                                            </h3>
                                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black uppercase
                                                {{ $profile->vehicle_fitness_status === 'approved' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400' :
                                                  ($profile->vehicle_fitness_status === 'submitted' || $profile->vehicle_fitness_status === 'under_review' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400' :
                                                  ($profile->vehicle_fitness_status === 'rejected' ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300')) }}">
                                                {{ ucfirst(str_replace('_', ' ', $profile->vehicle_fitness_status ?? 'not submitted')) }}
                                            </span>
                                        </div>

                                        @if($profile->vehicle_fitness_status === 'rejected')
                                            <div class="mb-4 p-3 bg-red-100/70 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl text-left">
                                                <p class="text-xs font-bold text-red-800 dark:text-red-300">
                                                    ⚠️ Rejected by Admin:
                                                </p>
                                                <p class="text-xs text-red-700 dark:text-red-300 mt-0.5">
                                                    {{ $profile->vehicle_fitness_rejection_reason ?? 'Your certificate did not meet requirements. Please re-upload a clear copy.' }}
                                                </p>
                                                <p class="text-[10px] font-bold text-red-600 dark:text-red-400 mt-1">
                                                    Action Required: Please upload a clear picture scan of your valid roadworthy certificate below.
                                                </p>
                                            </div>
                                        @elseif($profile->vehicle_fitness_status === 'approved')
                                            <div class="mb-4 p-3 bg-emerald-100/70 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-xl">
                                                <p class="text-xs font-bold text-emerald-800 dark:text-emerald-300">
                                                    ✓ Verified & Approved
                                                </p>
                                                <p class="text-[11px] text-emerald-700 dark:text-emerald-400 mt-0.5">
                                                    Your vehicle fitness (roadworthy) certificate is approved.
                                                </p>
                                            </div>
                                        @elseif($profile->vehicle_fitness_status === 'submitted' || $profile->vehicle_fitness_status === 'under_review')
                                            <div class="mb-4 p-3 bg-amber-100/70 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 rounded-xl">
                                                <p class="text-xs font-bold text-amber-800 dark:text-amber-300">
                                                    ⏳ Under Review
                                                </p>
                                                <p class="text-[11px] text-amber-700 dark:text-amber-400 mt-0.5">
                                                    Admin is currently reviewing your uploaded roadworthy scan.
                                                </p>
                                            </div>
                                        @endif

                                        @if($profile->fitness_certificate_url)
                                            <div class="mb-4 p-3 bg-white dark:bg-black/40 rounded-xl border border-gray-200 dark:border-white/10 flex items-center justify-between">
                                                <div class="flex items-center gap-2.5 overflow-hidden">
                                                    <span class="text-lg">📷</span>
                                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300 truncate">Current Roadworthy Scan</span>
                                                </div>
                                                <a href="{{ $profile->fitness_certificate_url }}" target="_blank" class="px-2.5 py-1 bg-brand-500 hover:bg-brand-600 text-white rounded-lg text-[11px] font-bold shrink-0 shadow-sm">
                                                    View Scan ↗
                                                </a>
                                            </div>
                                        @endif

                                        <div class="space-y-3">
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                                    {{ $profile->vehicle_fitness_image ? 'Upload New / Replace Roadworthy Picture Scan' : 'Upload Roadworthy Picture Scan *' }}
                                                </label>
                                                <input type="file" name="vehicle_fitness" accept="image/*,.pdf" class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-500 file:text-white hover:file:bg-brand-600 cursor-pointer">
                                                <p class="text-[10px] text-gray-400 mt-1">Photo scan (JPEG, PNG, WEBP) or PDF up to 10MB.</p>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Roadworthiness Expiry Date</label>
                                                <input type="date" name="vehicle_fitness_expiry" value="{{ $profile->vehicle_fitness_expiry ? \Carbon\Carbon::parse($profile->vehicle_fitness_expiry)->format('Y-m-d') : '' }}" class="w-full px-3 py-2 bg-white dark:bg-black/30 border border-gray-200 dark:border-white/10 rounded-xl text-xs text-gray-900 dark:text-white">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-4">
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    💡 <em>Once submitted, the admin panel will inspect your certificates. When approved, admin will activate your driver live status.</em>
                                </p>
                                <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl text-sm shadow-md transition-all flex items-center justify-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    <span>Upload & Submit Certificates</span>
                                </button>
                            </div>
                        </form>
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
                    <!-- Driver Profile Details & Edit Profile Feature -->
                    <div class="bg-white dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-3xl p-6 shadow-sm" x-data="driverProfileModal()">
                        <div class="flex items-center gap-4 mb-5 pb-5 border-b border-gray-100 dark:border-white/10">
                            <div class="relative w-16 h-16 rounded-2xl overflow-hidden bg-gray-100 dark:bg-white/10 flex-shrink-0 border-2 border-brand-500 shadow-md group">
                                @if($profile->image_url)
                                    <img src="{{ str_starts_with($profile->image_url, 'http') ? $profile->image_url : asset('storage/' . $profile->image_url) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                @elseif($user->profile_photo_path)
                                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                @elseif($user->avatar_url)
                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-brand-500/10 text-brand-500 font-extrabold text-2xl">
                                        {{ strtoupper(substr($user->name ?? 'D', 0, 1)) }}
                                    </div>
                                @endif
                                <button type="button" @click="showEditModal = true" class="absolute bottom-0 right-0 left-0 bg-black/80 hover:bg-amber-400 hover:text-black text-[10px] text-white py-0.5 text-center font-bold transition-colors cursor-pointer">
                                    Edit
                                </button>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <h3 class="font-black text-gray-900 dark:text-white text-base truncate">{{ $user->name }}</h3>
                                    @if(($profile->verification_status ?? '') === 'verified')
                                        <span class="text-amber-500 inline-flex items-center" title="Verified Chauffeur">
                                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2m-2 15-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $user->phone ?? 'No phone added' }}</p>
                                <button type="button" @click="showEditModal = true" class="mt-2 inline-flex items-center gap-1.5 px-3 py-1 bg-amber-400/15 hover:bg-amber-400/25 text-amber-700 dark:text-amber-300 rounded-xl text-xs font-bold transition-all cursor-pointer">
                                    <span>⚙️ Edit Profile & Documents</span>
                                </button>
                            </div>
                        </div>

                        <!-- REDESIGNED FULL EDIT DRIVER PROFILE MODAL (Teleported directly to Body to eliminate container clipping) -->
                        <template x-teleport="body">
                            <div x-show="showEditModal" 
                                 x-cloak 
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 x-transition:leave="transition ease-in duration-200"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0"
                                 class="fixed inset-0 z-[999999] overflow-y-auto bg-black/80 backdrop-blur-md flex items-center justify-center p-3 sm:p-6" 
                                 @keydown.escape.window="showEditModal = false">
                                
                                <div class="bg-white dark:bg-[#151515] text-gray-900 dark:text-white rounded-3xl max-w-3xl w-full shadow-2xl border border-gray-200 dark:border-white/10 flex flex-col my-auto max-h-[92vh] overflow-hidden" 
                                     @click.away="showEditModal = false">
                                    
                                    <!-- Modal Header (Sticky) -->
                                    <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10 flex items-center justify-between bg-gray-50/80 dark:bg-[#1a1a1a] sticky top-0 z-20">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-2xl bg-amber-400/20 text-amber-500 flex items-center justify-center text-xl font-bold">
                                                👨‍✈️
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2">
                                                    <span>Edit Driver Profile</span>
                                                </h3>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Manage all registration information, chauffeur rates, license documents, and service area.</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[11px] font-black uppercase px-2.5 py-1 rounded-full {{ $profile->is_live ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30' : 'bg-red-100 dark:bg-red-950/60 text-red-600 dark:text-red-400 border border-red-500/30' }}">
                                                {{ $profile->is_live ? '● Live' : '○ Inactive' }}
                                            </span>
                                            <button type="button" @click="showEditModal = false" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 dark:bg-white/10 dark:hover:bg-white/20 text-gray-500 dark:text-gray-300 flex items-center justify-center font-bold text-sm transition-colors cursor-pointer">
                                                ✕
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Navigation Tabs -->
                                    <div class="flex items-center gap-1 sm:gap-2 px-6 pt-3 pb-2 border-b border-gray-100 dark:border-white/10 bg-white dark:bg-[#151515] overflow-x-auto text-xs font-bold">
                                        <button type="button" @click="activeTab = 'personal'" 
                                                class="px-3.5 py-2 rounded-xl transition-all whitespace-nowrap cursor-pointer flex items-center gap-1.5"
                                                :class="activeTab === 'personal' ? 'bg-[#102b54] text-white dark:bg-amber-400 dark:text-[#102b54] shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5'">
                                            <span>👤</span> Personal & Contact
                                        </button>
                                        <button type="button" @click="activeTab = 'chauffeur'" 
                                                class="px-3.5 py-2 rounded-xl transition-all whitespace-nowrap cursor-pointer flex items-center gap-1.5"
                                                :class="activeTab === 'chauffeur' ? 'bg-[#102b54] text-white dark:bg-amber-400 dark:text-[#102b54] shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5'">
                                            <span>👨‍✈️</span> Chauffeur & Rates
                                        </button>
                                        <button type="button" @click="activeTab = 'license'" 
                                                class="px-3.5 py-2 rounded-xl transition-all whitespace-nowrap cursor-pointer flex items-center gap-1.5"
                                                :class="activeTab === 'license' ? 'bg-[#102b54] text-white dark:bg-amber-400 dark:text-[#102b54] shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5'">
                                            <span>🪪</span> License & Documents
                                        </button>
                                        <button type="button" @click="activeTab = 'compliance'" 
                                                class="px-3.5 py-2 rounded-xl transition-all whitespace-nowrap cursor-pointer flex items-center gap-1.5"
                                                :class="activeTab === 'compliance' ? 'bg-[#102b54] text-white dark:bg-amber-400 dark:text-[#102b54] shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5'">
                                            <span>🛡️</span> Vehicle Compliance
                                        </button>
                                    </div>

                                    <!-- Modal Body (Scrollable Form) -->
                                    <form action="/driver/profile/update" method="POST" enctype="multipart/form-data" class="flex-1 overflow-y-auto px-6 py-5 space-y-6 text-left" @submit="isSaving = true">
                                        @csrf
                                        
                                        <!-- TAB 1: PERSONAL & CONTACT -->
                                        <div x-show="activeTab === 'personal'" class="space-y-4">
                                            <div class="p-3 bg-amber-500/10 border border-amber-500/20 rounded-2xl flex items-center gap-3 text-xs text-amber-800 dark:text-amber-200">
                                                <span class="text-base">ℹ️</span>
                                                <span>Personal and contact details entered during registration. These help passengers identify and contact their assigned chauffeur.</span>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Full Name *</label>
                                                    <input type="text" name="name" value="{{ $user->name }}" required class="w-full px-4 py-3 bg-gray-50 dark:bg-[#202020] border border-gray-200 dark:border-white/10 rounded-xl text-sm font-semibold text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Registered Email Address</label>
                                                    <div class="relative">
                                                        <input type="email" name="email" value="{{ $user->email }}" required class="w-full px-4 py-3 bg-gray-50 dark:bg-[#202020] border border-gray-200 dark:border-white/10 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400">Verified</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Mobile Phone Number</label>
                                                    <input type="tel" name="phone" value="{{ $user->phone }}" placeholder="+1 555-0199" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#202020] border border-gray-200 dark:border-white/10 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                                                </div>

                                                <!-- Country Selector with Flag Dropdown -->
                                                <div class="relative">
                                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Operating Country *</label>
                                                    <input type="hidden" name="country" :value="selectedCountry ? selectedCountry.name : '{{ $profile->country ?? $user->country ?? 'United States' }}'">
                                                    
                                                    <button type="button" @click="countryOpen = !countryOpen" 
                                                            class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-[#202020] border border-gray-200 dark:border-white/10 rounded-xl text-sm font-medium text-gray-900 dark:text-white text-left cursor-pointer hover:border-amber-400 transition-colors">
                                                        <div class="flex items-center gap-2.5 min-w-0">
                                                            <template x-if="selectedCountry">
                                                                <img :src="selectedCountry.flagUrl || `https://flagcdn.com/w40/${(selectedCountry.code || 'us').toLowerCase()}.png`" 
                                                                     :alt="selectedCountry.name" 
                                                                     class="w-5 h-3.5 object-cover rounded-sm shadow-xs shrink-0">
                                                            </template>
                                                            <span class="truncate" x-text="selectedCountry ? selectedCountry.name : '{{ $profile->country ?? 'United States' }}'"></span>
                                                        </div>
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200" :class="countryOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                                    </button>
                                                    
                                                    <div x-show="countryOpen" @click.away="countryOpen = false" style="display: none;"
                                                         class="absolute left-0 right-0 top-full mt-1.5 bg-white dark:bg-[#1c1c1c] rounded-2xl shadow-2xl border border-gray-200 dark:border-white/10 z-50 overflow-hidden">
                                                        <div class="p-2 border-b border-gray-100 dark:border-white/10 sticky top-0 bg-gray-50 dark:bg-[#222]">
                                                            <input type="text" x-model="countrySearch" placeholder="Search country..."
                                                                   class="w-full px-3 py-2 bg-white dark:bg-[#181818] border border-gray-200 dark:border-white/10 rounded-lg text-xs font-semibold text-gray-900 dark:text-white focus:outline-none focus:border-amber-400">
                                                        </div>
                                                        <div class="max-h-48 overflow-y-auto p-1 text-xs space-y-0.5">
                                                            <template x-for="c in countryList" :key="c.code">
                                                                <button type="button" @click="selectedCountry = c; countryOpen = false; countrySearch = ''"
                                                                        class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-left transition-colors hover:bg-gray-100 dark:hover:bg-white/10 cursor-pointer"
                                                                        :class="selectedCountry && selectedCountry.code === c.code ? 'bg-amber-400 text-black font-bold' : 'text-gray-800 dark:text-gray-200'">
                                                                    <div class="flex items-center gap-2.5 min-w-0">
                                                                        <img :src="c.flagUrl || `https://flagcdn.com/w40/${(c.code || 'us').toLowerCase()}.png`" 
                                                                             :alt="c.name" 
                                                                             loading="lazy"
                                                                             class="w-5 h-3.5 object-cover rounded-sm shadow-xs shrink-0">
                                                                        <span class="truncate" x-text="c.name"></span>
                                                                    </div>
                                                                    <span class="font-mono text-[10px] opacity-70" x-text="c.code"></span>
                                                                </button>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Primary Service Area / Operating City</label>
                                                <input type="text" name="service_area" value="{{ $profile->service_area ?? $user->city ?? '' }}" placeholder="e.g. Greater London, Johannesburg & Sandton, Miami Metro" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#202020] border border-gray-200 dark:border-white/10 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                                                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Specify cities or regions where you are ready to receive ride requests and chauffeur bookings.</p>
                                            </div>
                                        </div>

                                        <!-- TAB 2: CHAUFFEUR & RATES -->
                                        <div x-show="activeTab === 'chauffeur'" class="space-y-5" style="display: none;">
                                            <!-- Chauffeur Formal Photo -->
                                            <div>
                                                <div class="flex items-center justify-between mb-2">
                                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                        Formal Chauffeur Photo <span class="text-amber-500 font-semibold normal-case">(Professional Attire)</span>
                                                    </label>
                                                    <span class="text-[11px] text-gray-400">Max 10MB</span>
                                                </div>
                                                
                                                <input type="file" name="driver_photo" id="driverPhotoEditInput" x-ref="driverPhotoInput" accept="image/*" class="hidden" @change="handleDriverPhoto($event.target.files[0])">
                                                
                                                <div class="flex items-center gap-4 p-4 rounded-2xl border-2 border-dashed border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-[#202020]">
                                                    <div class="relative w-20 h-20 rounded-2xl overflow-hidden bg-gray-100 dark:bg-black/40 border-2 border-amber-400 shadow-md shrink-0">
                                                        <template x-if="driverPhotoPreview">
                                                            <img :src="driverPhotoPreview" alt="Preview" class="w-full h-full object-cover">
                                                        </template>
                                                        <template x-if="!driverPhotoPreview">
                                                            @if($profile->image_url)
                                                                <img src="{{ str_starts_with($profile->image_url, 'http') ? $profile->image_url : asset('storage/' . $profile->image_url) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                                            @elseif($user->avatar_url)
                                                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                                            @else
                                                                <div class="w-full h-full flex items-center justify-center text-3xl">👨‍✈️</div>
                                                            @endif
                                                        </template>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white" x-text="driverFileName ? driverFileName : 'Formal Chauffeur Profile Picture'"></h4>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Formal suit, shirt or tie recommended. Visible to customers on booking.</p>
                                                        <div class="mt-2.5 flex items-center gap-2">
                                                            <button type="button" @click="$refs.driverPhotoInput.click()" class="px-3 py-1.5 bg-amber-400 hover:bg-amber-300 text-[#102b54] font-black text-xs rounded-xl transition cursor-pointer shadow-xs">
                                                                Browse / Change Photo
                                                            </button>
                                                            <template x-if="driverPhotoPreview">
                                                                <button type="button" @click="driverPhotoPreview = null; driverFileName = ''; $refs.driverPhotoInput.value = ''" class="px-3 py-1.5 bg-red-100 dark:bg-red-950/40 text-red-600 dark:text-red-400 font-bold text-xs rounded-xl hover:bg-red-200 transition cursor-pointer">
                                                                    Revert
                                                                </button>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Commercial Driving Experience (Years)</label>
                                                    <input type="number" name="experience_years" min="1" max="60" value="{{ $profile->experience_years ?? 5 }}" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#202020] border border-gray-200 dark:border-white/10 rounded-xl text-sm font-semibold text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Hourly Chauffeur Rate ($)</label>
                                                    <input type="number" step="0.50" name="hourly_rate" min="1" value="{{ $profile->hourly_rate ?? 25.00 }}" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#202020] border border-gray-200 dark:border-white/10 rounded-xl text-sm font-semibold text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Daily Chauffeur Rate ($)</label>
                                                    <input type="number" step="1.00" name="daily_rate" min="10" value="{{ $profile->daily_rate ?? 170.00 }}" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#202020] border border-gray-200 dark:border-white/10 rounded-xl text-sm font-semibold text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Weekly Chauffeur Rate ($)</label>
                                                    <input type="number" step="5.00" name="weekly_rate" min="50" value="{{ $profile->weekly_rate ?? 950.00 }}" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#202020] border border-gray-200 dark:border-white/10 rounded-xl text-sm font-semibold text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                                                </div>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Professional Bio & Experience Summary</label>
                                                <textarea name="bio" rows="3" placeholder="Describe your executive chauffeuring background, vehicle safety habits, and notable service highlights..." class="w-full px-4 py-3 bg-gray-50 dark:bg-[#202020] border border-gray-200 dark:border-white/10 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-400">{{ $profile->bio }}</textarea>
                                            </div>
                                        </div>

                                        <!-- TAB 3: LICENSE & DOCUMENTS -->
                                        <div x-show="activeTab === 'license'" class="space-y-5" style="display: none;">
                                            <div class="p-3 bg-blue-500/10 border border-blue-500/20 rounded-2xl flex items-center justify-between text-xs">
                                                <div class="flex items-center gap-2.5">
                                                    <span class="text-base">🪪</span>
                                                    <div>
                                                        <span class="font-bold text-blue-900 dark:text-blue-200">License Status:</span>
                                                        <span class="font-black uppercase tracking-wider ml-1 text-amber-600 dark:text-amber-400">{{ ucfirst($profile->verification_status ?? 'unverified') }}</span>
                                                    </div>
                                                </div>
                                                <span class="text-[11px] text-gray-500 font-mono">{{ $profile->masked_license }}</span>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Driver License Number *</label>
                                                    <input type="text" name="license_number" value="{{ $profile->license_number }}" required placeholder="e.g. DL-99887766" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#202020] border border-gray-200 dark:border-white/10 rounded-xl text-sm font-mono font-bold text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">License Expiry Date</label>
                                                    <input type="date" name="license_expiry" value="{{ $profile->license_expiry ? \Carbon\Carbon::parse($profile->license_expiry)->format('Y-m-d') : '' }}" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#202020] border border-gray-200 dark:border-white/10 rounded-xl text-sm font-medium text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                                                </div>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">License Issuing Country / Jurisdiction</label>
                                                <input type="text" name="license_country" value="{{ $profile->license_country ?? $profile->country ?? 'USA' }}" placeholder="e.g. United States, United Kingdom, South Africa" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#202020] border border-gray-200 dark:border-white/10 rounded-xl text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                                            </div>

                                            <!-- Document Upload Previews -->
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                <!-- Front Document -->
                                                <div class="p-4 bg-gray-50 dark:bg-[#202020] border border-gray-200 dark:border-white/10 rounded-2xl space-y-2">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">License Front Image</span>
                                                        @if($profile->license_front_image)
                                                            <span class="text-[10px] font-bold text-emerald-600 bg-emerald-100 dark:bg-emerald-950/60 px-2 py-0.5 rounded-full">Uploaded</span>
                                                        @endif
                                                    </div>
                                                    
                                                    @if($profile->license_front_image)
                                                        <div class="h-28 rounded-xl overflow-hidden bg-black/5 dark:bg-white/5 border border-gray-200 dark:border-white/10">
                                                            <img src="{{ asset('storage/' . $profile->license_front_image) }}" alt="License Front" class="w-full h-full object-cover">
                                                        </div>
                                                    @endif
                                                    
                                                    <input type="file" name="license_front_image" accept="image/*" class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#102b54] file:text-white dark:file:bg-amber-400 dark:file:text-black hover:file:opacity-90 cursor-pointer">
                                                    <p class="text-[10px] text-gray-400">Select file to replace current front copy.</p>
                                                </div>

                                                <!-- Back Document -->
                                                <div class="p-4 bg-gray-50 dark:bg-[#202020] border border-gray-200 dark:border-white/10 rounded-2xl space-y-2">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">License Back Image</span>
                                                        @if($profile->license_back_image)
                                                            <span class="text-[10px] font-bold text-emerald-600 bg-emerald-100 dark:bg-emerald-950/60 px-2 py-0.5 rounded-full">Uploaded</span>
                                                        @endif
                                                    </div>
                                                    
                                                    @if($profile->license_back_image)
                                                        <div class="h-28 rounded-xl overflow-hidden bg-black/5 dark:bg-white/5 border border-gray-200 dark:border-white/10">
                                                            <img src="{{ asset('storage/' . $profile->license_back_image) }}" alt="License Back" class="w-full h-full object-cover">
                                                        </div>
                                                    @endif
                                                    
                                                    <input type="file" name="license_back_image" accept="image/*" class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#102b54] file:text-white dark:file:bg-amber-400 dark:file:text-black hover:file:opacity-90 cursor-pointer">
                                                    <p class="text-[10px] text-gray-400">Select file to replace current back copy.</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- TAB 4: COMPLIANCE & CERTIFICATES -->
                                        <div x-show="activeTab === 'compliance'" class="space-y-4" style="display: none;">
                                            <div class="p-4 bg-gray-50 dark:bg-[#202020] rounded-2xl border border-gray-200 dark:border-white/10">
                                                <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-2">Compliance Status Summary</h4>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Both Vehicle Insurance and Roadworthy Fitness certificates must be approved by admin to activate your driver live status.</p>
                                                
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4">
                                                    <div class="p-3 rounded-xl bg-white dark:bg-[#181818] border border-gray-200 dark:border-white/10">
                                                        <div class="flex items-center justify-between text-xs mb-1">
                                                            <span class="font-semibold text-gray-600 dark:text-gray-400">Vehicle Insurance</span>
                                                            <span class="font-bold {{ $profile->vehicle_insurance_status === 'approved' ? 'text-emerald-500' : ($profile->vehicle_insurance_status === 'submitted' ? 'text-amber-500' : 'text-red-500') }}">
                                                                {{ ucfirst(str_replace('_', ' ', $profile->vehicle_insurance_status ?? 'Not submitted')) }}
                                                            </span>
                                                        </div>
                                                        <p class="text-[11px] text-gray-400 font-mono">
                                                            Expiry: {{ $profile->vehicle_insurance_expiry ? \Carbon\Carbon::parse($profile->vehicle_insurance_expiry)->format('d M Y') : 'Not set' }}
                                                        </p>
                                                    </div>

                                                    <div class="p-3 rounded-xl bg-white dark:bg-[#181818] border border-gray-200 dark:border-white/10">
                                                        <div class="flex items-center justify-between text-xs mb-1">
                                                            <span class="font-semibold text-gray-600 dark:text-gray-400">Roadworthy Fitness</span>
                                                            <span class="font-bold {{ $profile->vehicle_fitness_status === 'approved' ? 'text-emerald-500' : ($profile->vehicle_fitness_status === 'submitted' ? 'text-amber-500' : 'text-red-500') }}">
                                                                {{ ucfirst(str_replace('_', ' ', $profile->vehicle_fitness_status ?? 'Not submitted')) }}
                                                            </span>
                                                        </div>
                                                        <p class="text-[11px] text-gray-400 font-mono">
                                                            Expiry: {{ $profile->vehicle_fitness_expiry ? \Carbon\Carbon::parse($profile->vehicle_fitness_expiry)->format('d M Y') : 'Not set' }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="mt-4 pt-3 border-t border-gray-200 dark:border-white/10 flex items-center justify-between">
                                                    <span class="text-xs text-gray-500">Need to upload new certificate scans?</span>
                                                    <button type="button" @click="showEditModal = false; const el = document.getElementById('vehicleCertificatesSection'); if(el) el.scrollIntoView({behavior: 'smooth'})" class="px-3 py-1.5 bg-[#102b54] text-white hover:bg-black font-bold text-xs rounded-xl transition cursor-pointer">
                                                        Go to Certificate Scanner →
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal Footer (Sticky) -->
                                        <div class="pt-4 border-t border-gray-100 dark:border-white/10 flex flex-col sm:flex-row items-center justify-between gap-3 sticky bottom-0 bg-white dark:bg-[#151515] z-20">
                                            <div class="text-xs text-gray-500">
                                                <span x-show="activeTab === 'personal'">Section 1 of 4: Personal Details</span>
                                                <span x-show="activeTab === 'chauffeur'">Section 2 of 4: Chauffeur & Rates</span>
                                                <span x-show="activeTab === 'license'">Section 3 of 4: License & Docs</span>
                                                <span x-show="activeTab === 'compliance'">Section 4 of 4: Compliance</span>
                                            </div>
                                            <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end">
                                                <button type="button" @click="showEditModal = false" class="px-5 py-2.5 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/10 rounded-xl transition cursor-pointer">
                                                    Cancel
                                                </button>
                                                <button type="submit" :disabled="isSaving" class="px-6 py-2.5 text-xs font-black bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-[#102b54] rounded-xl shadow-lg shadow-amber-500/20 transition cursor-pointer flex items-center gap-2">
                                                    <span x-show="!isSaving">Save Profile Changes</span>
                                                    <span x-show="isSaving" style="display: none;" class="flex items-center gap-1.5">
                                                        <svg class="animate-spin h-3.5 w-3.5 text-[#102b54]" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                        <span>Saving...</span>
                                                    </span>
                                                </button>
                                            </div>
                                        </div>

                                    </form>
                                </div>
                            </div>
                        </template>

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

                            <li class="flex justify-between items-center pt-2 border-t border-gray-100 dark:border-white/10">
                                <span class="text-gray-500 dark:text-gray-400 font-medium">Account Live Status</span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-black uppercase
                                    {{ $profile->is_live ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $profile->is_live ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                    {{ $profile->is_live ? 'Live (Active)' : 'Inactive' }}
                                </span>
                            </li>
                            <li class="flex justify-between items-center text-xs">
                                <span class="text-gray-500 dark:text-gray-400">Vehicle Insurance</span>
                                <span class="font-bold {{ $profile->vehicle_insurance_status === 'approved' ? 'text-emerald-600 dark:text-emerald-400' : ($profile->vehicle_insurance_status === 'submitted' || $profile->vehicle_insurance_status === 'under_review' ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400') }}">
                                    {{ ucfirst(str_replace('_', ' ', $profile->vehicle_insurance_status ?? 'not submitted')) }}
                                </span>
                            </li>
                            <li class="flex justify-between items-center text-xs">
                                <span class="text-gray-500 dark:text-gray-400">Vehicle Fitness</span>
                                <span class="font-bold {{ $profile->vehicle_fitness_status === 'approved' ? 'text-emerald-600 dark:text-emerald-400' : ($profile->vehicle_fitness_status === 'submitted' || $profile->vehicle_fitness_status === 'under_review' ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400') }}">
                                    {{ ucfirst(str_replace('_', ' ', $profile->vehicle_fitness_status ?? 'not submitted')) }}
                                </span>
                            </li>

                            <li class="pt-4 border-t border-gray-100 dark:border-white/10">
                                @if(!$profile->is_live)
                                    <div class="mb-3 p-3 bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/30 rounded-xl text-left">
                                        <p class="text-[11px] text-amber-800 dark:text-amber-300 font-semibold leading-relaxed">
                                            ⚠️ <strong>Account Inactive:</strong> You cannot go online until your Vehicle Insurance and Roadworthy certificates are approved and made Live by admin.
                                        </p>
                                    </div>
                                @endif
                                <form action="/driver/toggle-availability" method="POST">
                                    @csrf
                                    <label class="flex justify-between items-center {{ $profile->is_live ? 'cursor-pointer' : 'cursor-not-allowed opacity-60' }}">
                                        <div>
                                            <span class="font-bold text-gray-900 dark:text-white block">Online for Booking</span>
                                            <span class="text-[11px] text-gray-400">{{ $profile->is_live ? ($profile->is_available ? 'Online (Receiving Trips)' : 'Offline') : 'Locked (Account Inactive)' }}</span>
                                        </div>
                                        <input type="checkbox" name="is_available" value="1" onchange="this.form.submit()" {{ $profile->is_available && $profile->is_live ? 'checked' : '' }} {{ !$profile->is_live ? 'disabled' : '' }} class="w-5 h-5 accent-brand-500">
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
            Alpine.data('driverProfileModal', () => ({
                showEditModal: false,
                activeTab: 'personal',
                isSaving: false,
                countryOpen: false,
                countrySearch: '',
                selectedCountry: null,
                driverPhotoPreview: null,
                driverFileName: '',

                init() {
                    const countries = window.WORLD_COUNTRIES || [];
                    const currentCountry = '{{ addslashes($profile->country ?? $user->country ?? 'United States') }}';
                    const found = countries.find(c => 
                        (c.name && c.name.toLowerCase() === currentCountry.toLowerCase()) || 
                        (c.cca3 && c.cca3.toLowerCase() === currentCountry.toLowerCase()) ||
                        (c.code && c.code.toLowerCase() === currentCountry.toLowerCase())
                    );
                    this.selectedCountry = found || (countries.length ? countries[0] : { name: 'United States', code: 'US', flagUrl: 'https://flagcdn.com/w40/us.png' });
                },

                get countryList() {
                    const all = window.WORLD_COUNTRIES || [];
                    if (!this.countrySearch) return all;
                    const q = this.countrySearch.toLowerCase().trim();
                    return all.filter(c => (c.name && c.name.toLowerCase().includes(q)) || (c.code && c.code.toLowerCase().includes(q)));
                },

                handleDriverPhoto(file) {
                    if (!file || !file.type.startsWith('image/')) return;
                    this.driverFileName = file.name;
                    const reader = new FileReader();
                    reader.onload = (e) => { this.driverPhotoPreview = e.target.result; };
                    reader.readAsDataURL(file);
                }
            }));

            Alpine.data('driverPolling', () => ({
                isLive: {{ $profile->is_live ? 'true' : 'false' }},
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
                    // Only poll for customer requests if driver account is active/live
                    if (!this.isLive) {
                        return;
                    }
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
                rides: @json($activeRides->values()),
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
                    this.pollingTimer = setInterval(() => this.fetchRides(), 4000);
                },
                
                async fetchRides() {
                    try {
                        let res = await fetch('/driver/active-rides-data');
                        if (!res.ok) {
                            res = await fetch('/api/driver/active-rides');
                        }
                        if (res.ok) {
                            const data = await res.json();
                            this.rides = Array.isArray(data) ? data : (data.rides || data.data || []);
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
                            lat: pos.coords.latitude,
                            lng: pos.coords.longitude,
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
