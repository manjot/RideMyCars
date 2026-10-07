<x-layout theme="theme-rent">
    <x-slot:title>Vehicle Owner Portal & Rental Management — RideMyCars</x-slot>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8"
          x-data="{
              activeTab: '{{ request('tab', 'requests') }}',
              showAddModal: false,
              editingVehicle: null,
              rejectingRideId: null,
              rejectReason: '',
              searchVehicle: '',
          }">

        <!-- Header Spotlight -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-gradient-to-r from-slate-900 via-slate-800 to-blue-950 p-6 sm:p-8 rounded-3xl border border-slate-800 shadow-xl text-white">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-blue-600/20 border border-blue-500/40 flex items-center justify-center text-3xl shadow-inner shrink-0">
                    🔑
                </div>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-2xl sm:text-3xl font-black tracking-tight">Owner Portal</h1>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-black uppercase tracking-wider bg-blue-500 text-white shadow-sm">Verified Fleet</span>
                    </div>
                    <p class="text-sm text-slate-300 mt-1 font-medium">Manage your vehicles, review rental booking requests, and approve customer trips.</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" 
                        @click="showAddModal = true; editingVehicle = null;"
                        class="px-5 py-3 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-extrabold text-xs sm:text-sm tracking-wide shadow-lg shadow-blue-500/25 transition-all flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Add New Vehicle</span>
                </button>
            </div>
        </div>

        <!-- Success & Error Alerts -->
        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 font-bold text-sm flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-xl">✨</span>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white">✕</button>
            </div>
        @endif

        @if(session('info'))
            <div class="mb-6 p-4 rounded-2xl bg-blue-500/10 border border-blue-500/30 text-blue-400 font-bold text-sm flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-xl">ℹ️</span>
                    <span>{{ session('info') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-blue-400 hover:text-white">✕</button>
            </div>
        @endif

        @if(session('error') || $errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-400 font-bold text-sm flex flex-col gap-1">
                <div class="flex items-center gap-2">
                    <span class="text-xl">⚠️</span>
                    <span>{{ session('error') ?? 'Please check the form inputs below:' }}</span>
                </div>
                @if($errors->any())
                    <ul class="list-disc list-inside text-xs mt-1 text-rose-300">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        <!-- Quick Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xl shrink-0 font-black">
                    🚘
                </div>
                <div>
                    <div class="text-2xl font-black text-slate-900 dark:text-white">{{ $vehicles->count() }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider">Total Vehicles</div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4 relative overflow-hidden">
                @if($pendingRequests->count() > 0)
                    <div class="absolute -top-1 -right-1 w-3 h-3 bg-amber-500 rounded-full animate-ping"></div>
                @endif
                <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl shrink-0 font-black">
                    ⏳
                </div>
                <div>
                    <div class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ $pendingRequests->count() }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider">Pending Requests</div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl shrink-0 font-black">
                    🟢
                </div>
                <div>
                    <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $activeRentals->count() }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider">Active Bookings</div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl shrink-0 font-black">
                    💳
                </div>
                <div>
                    <div class="text-2xl font-black text-slate-900 dark:text-white">{{ $pricing->currency_symbol }}{{ number_format($totalEarned, 2) }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider">Gross Bookings</div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-4 mb-8 overflow-x-auto">
            <button type="button" 
                    @click="activeTab = 'requests'"
                    :class="activeTab === 'requests' ? 'bg-blue-600 text-white font-black shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold hover:bg-slate-200 dark:hover:bg-slate-700'"
                    class="px-5 py-2.5 rounded-xl text-xs sm:text-sm tracking-wide transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                <span>Rental Requests</span>
                @if($pendingRequests->count() > 0)
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-black bg-amber-400 text-slate-950">{{ $pendingRequests->count() }}</span>
                @endif
            </button>

            <button type="button" 
                    @click="activeTab = 'fleet'"
                    :class="activeTab === 'fleet' ? 'bg-blue-600 text-white font-black shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold hover:bg-slate-200 dark:hover:bg-slate-700'"
                    class="px-5 py-2.5 rounded-xl text-xs sm:text-sm tracking-wide transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                <span>My Vehicles Fleet</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-black bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200">{{ $vehicles->count() }}</span>
            </button>

            <button type="button" 
                    @click="activeTab = 'history'"
                    :class="activeTab === 'history' ? 'bg-blue-600 text-white font-black shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold hover:bg-slate-200 dark:hover:bg-slate-700'"
                    class="px-5 py-2.5 rounded-xl text-xs sm:text-sm tracking-wide transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                <span>Completed & History</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-black bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200">{{ $historyRentals->count() }}</span>
            </button>
        </div>

        <!-- ========================================== -->
        <!-- TAB 1: RENTAL REQUESTS (PENDING & ACTIVE) -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'requests'" class="space-y-8">
            <!-- PENDING REQUESTS SECTION -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <span>Pending Customer Requests</span>
                        <span class="text-xs font-bold px-2 py-0.5 rounded-lg bg-amber-500/20 text-amber-500 border border-amber-500/30">Action Required</span>
                    </h2>
                </div>

                @if($pendingRequests->isEmpty())
                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-8 border border-slate-200 dark:border-slate-800 text-center text-slate-400">
                        <div class="text-4xl mb-2">🎉</div>
                        <h3 class="text-base font-bold text-slate-700 dark:text-slate-200">No pending rental requests!</h3>
                        <p class="text-xs mt-1 text-slate-500">All customer requests have been processed. New bookings will appear here instantly.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($pendingRequests as $ride)
                            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border-2 border-amber-400/40 shadow-xl relative overflow-hidden flex flex-col justify-between">
                                <div class="absolute top-0 right-0 px-4 py-1.5 rounded-bl-2xl bg-amber-500 text-slate-950 font-black text-[11px] uppercase tracking-wider">
                                    Pending Approval
                                </div>

                                <div>
                                    <!-- Vehicle Header -->
                                    <div class="flex items-start gap-4 mb-4">
                                        <div class="w-20 h-16 rounded-xl overflow-hidden bg-slate-800 shrink-0 border border-slate-700">
                                            <img src="{{ $ride->vehicle?->image_src }}" alt="Vehicle" class="w-full h-full object-cover">
                                        </div>
                                        <div>
                                            <h3 class="text-base font-black text-slate-900 dark:text-white">{{ $ride->vehicle?->year }} {{ $ride->vehicle?->make }} {{ $ride->vehicle?->model }}</h3>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="text-xs font-mono font-bold px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-md border border-slate-200 dark:border-slate-700">{{ $ride->vehicle?->license_plate }}</span>
                                                <span class="text-xs font-semibold text-blue-500">{{ $ride->vehicle?->category }}</span>
                                            </div>
                                            <div class="text-xs text-slate-400 mt-1">Receipt: <strong class="text-slate-200 font-mono">{{ $ride->digital_receipt_code ?? ('#'.$ride->id) }}</strong></div>
                                        </div>
                                    </div>

                                    <!-- Customer & Booking Specs -->
                                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-2xl p-4 border border-slate-200/80 dark:border-slate-800 mb-5 space-y-2.5 text-xs">
                                        <div class="flex items-center justify-between pb-2 border-b border-slate-200/60 dark:border-slate-700/60">
                                            <span class="text-slate-500 font-medium">Customer:</span>
                                            <span class="font-bold text-slate-900 dark:text-white">{{ $ride->rider?->name ?? 'Guest Customer' }} ({{ $ride->passenger_phone ?? ($ride->rider?->phone ?? 'No phone') }})</span>
                                        </div>
                                        <div class="flex items-center justify-between pb-2 border-b border-slate-200/60 dark:border-slate-700/60">
                                            <span class="text-slate-500 font-medium">Driver Age:</span>
                                            <span class="font-bold text-slate-900 dark:text-white">{{ $ride->customer_age ?? '25+' }} yrs</span>
                                        </div>
                                        <div class="flex items-center justify-between pb-2 border-b border-slate-200/60 dark:border-slate-700/60">
                                            <span class="text-slate-500 font-medium">Dates:</span>
                                            <span class="font-bold text-blue-600 dark:text-blue-400">{{ $ride->pickup_date ? \Carbon\Carbon::parse($ride->pickup_date)->format('M d, Y') : 'Start' }} → {{ $ride->return_date ? \Carbon\Carbon::parse($ride->return_date)->format('M d, Y') : 'End' }}</span>
                                        </div>
                                        <div class="flex items-center justify-between pb-2 border-b border-slate-200/60 dark:border-slate-700/60">
                                            <span class="text-slate-500 font-medium">Pickup:</span>
                                            <span class="font-semibold text-slate-900 dark:text-slate-200 text-right truncate max-w-[220px]" title="{{ $ride->pickup_location }}">{{ $ride->pickup_location }}</span>
                                        </div>
                                        <div class="flex items-center justify-between pt-1">
                                            <div>
                                                <span class="text-slate-500 font-medium">Total Rental Fare:</span>
                                                <div class="text-lg font-black text-slate-900 dark:text-white">{{ $pricing->currency_symbol }}{{ number_format($ride->total_amount ?: $ride->fare, 2) }}</div>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-slate-500 font-medium">20% Deposit:</span>
                                                <div class="text-sm font-black text-emerald-500">{{ $pricing->currency_symbol }}{{ number_format($ride->paid_amount ?: ($ride->fare * 0.2), 2) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons: Approve & Reject -->
                                <div class="flex items-center gap-3 pt-2">
                                    <form action="/owner/rentals/{{ $ride->id }}/approve" method="POST" class="flex-1">
                                        @csrf
                                        <button type="submit" 
                                                onclick="return confirm('Approve rental request #{{ $ride->id }} for {{ $ride->vehicle?->make }} {{ $ride->vehicle?->model }}?')"
                                                class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs uppercase tracking-wider shadow-lg shadow-emerald-600/30 transition-all flex items-center justify-center gap-2 cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            <span>Approve Rental</span>
                                        </button>
                                    </form>

                                    <button type="button" 
                                            @click="rejectingRideId = {{ $ride->id }}"
                                            class="px-4 py-3 rounded-xl bg-slate-100 hover:bg-rose-500/10 text-slate-700 dark:text-slate-300 hover:text-rose-500 border border-slate-200 dark:border-slate-700 hover:border-rose-500/30 font-bold text-xs uppercase tracking-wider transition-all cursor-pointer">
                                        <span>Reject</span>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- ACTIVE RENTALS SECTION -->
            <div class="pt-6 border-t border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <span>Active & Confirmed Rentals</span>
                        <span class="text-xs font-bold px-2 py-0.5 rounded-lg bg-emerald-500/20 text-emerald-500 border border-emerald-500/30">{{ $activeRentals->count() }} Active</span>
                    </h2>
                </div>

                @if($activeRentals->isEmpty())
                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 text-center text-slate-400 text-xs">
                        No active trips currently in progress.
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($activeRentals as $ride)
                            <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                            {{ ucfirst($ride->status) }}
                                        </span>
                                        <span class="text-xs font-mono font-bold text-slate-400">{{ $ride->digital_receipt_code ?? ('#'.$ride->id) }}</span>
                                    </div>
                                    <h4 class="font-extrabold text-sm text-slate-900 dark:text-white">{{ $ride->vehicle?->year }} {{ $ride->vehicle?->make }} {{ $ride->vehicle?->model }}</h4>
                                    <p class="text-xs text-slate-400 mt-1">Customer: <strong class="text-slate-200">{{ $ride->rider?->name ?? 'Guest' }}</strong></p>
                                    <p class="text-xs text-blue-500 font-semibold mt-1">{{ $ride->pickup_date ? \Carbon\Carbon::parse($ride->pickup_date)->format('M d') : 'Now' }} → {{ $ride->return_date ? \Carbon\Carbon::parse($ride->return_date)->format('M d') : 'End' }}</p>
                                </div>
                                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                                    <span class="text-slate-400">Total:</span>
                                    <span class="font-black text-slate-900 dark:text-white">{{ $pricing->currency_symbol }}{{ number_format($ride->fare, 2) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: MY VEHICLES FLEET (FULL CRUD)      -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'fleet'" class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white">Your Listed Vehicles</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Add, edit, change pricing, or toggle availability for your rental fleet.</p>
                </div>
                <button type="button" 
                        @click="showAddModal = true; editingVehicle = null;"
                        class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-extrabold text-xs uppercase tracking-wider transition-all flex items-center gap-2 cursor-pointer self-start sm:self-auto">
                    <span>+ Add Vehicle</span>
                </button>
            </div>

            @if($vehicles->isEmpty())
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-12 border border-slate-200 dark:border-slate-800 text-center">
                    <div class="text-5xl mb-3">🚗</div>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white">No vehicles listed yet</h3>
                    <p class="text-xs text-slate-400 mt-1 max-w-md mx-auto">Start listing your vehicles to earn rental income. You can set daily rates, security deposits, and upload photos.</p>
                    <button type="button" 
                            @click="showAddModal = true; editingVehicle = null;"
                            class="mt-6 px-6 py-3 rounded-2xl bg-blue-600 text-white font-bold text-xs uppercase tracking-wider shadow-lg shadow-blue-500/25">
                        Add Your First Vehicle
                    </button>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($vehicles as $v)
                        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm flex flex-col justify-between group hover:border-blue-500/50 transition-all">
                            <div>
                                <!-- Vehicle Image -->
                                <div class="relative h-48 bg-slate-950 overflow-hidden">
                                    <img src="{{ $v->image_src }}" alt="{{ $v->make }} {{ $v->model }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    <div class="absolute top-3 left-3 flex items-center gap-2">
                                        <span class="px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-slate-950/80 backdrop-blur text-white border border-white/10">
                                            {{ $v->category }}
                                        </span>
                                    </div>
                                    <div class="absolute top-3 right-3">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider {{ $v->is_available ? 'bg-emerald-500 text-slate-950' : 'bg-slate-700 text-slate-300' }}">
                                            {{ $v->is_available ? 'Active' : 'Offline' }}
                                        </span>
                                    </div>
                                    <div class="absolute bottom-3 right-3 px-3 py-1.5 rounded-xl bg-slate-950/90 backdrop-blur border border-white/10 text-white text-right">
                                        <span class="text-xs text-slate-400">Daily: </span>
                                        <strong class="text-sm font-black text-amber-400">{{ $pricing->currency_symbol }}{{ number_format($v->daily_rate, 2) }}</strong>
                                    </div>
                                </div>

                                <!-- Details -->
                                <div class="p-5">
                                    <div class="flex items-start justify-between gap-2 mb-2">
                                        <div>
                                            <h3 class="font-black text-base text-slate-900 dark:text-white leading-tight">{{ $v->year }} {{ $v->make }} {{ $v->model }}</h3>
                                            <p class="text-xs text-slate-400 mt-0.5">{{ $v->type }}</p>
                                        </div>
                                        <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-mono font-bold rounded-lg border border-slate-200 dark:border-slate-700 shrink-0">
                                            {{ $v->license_plate }}
                                        </span>
                                    </div>

                                    <!-- Specs Pills -->
                                    <div class="grid grid-cols-3 gap-2 mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 text-center">
                                        <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                                            <div class="text-[10px] text-slate-400 font-semibold uppercase">Seats</div>
                                            <div class="font-extrabold text-xs text-slate-800 dark:text-slate-200 mt-0.5">{{ $v->seats }} Seats</div>
                                        </div>
                                        <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                                            <div class="text-[10px] text-slate-400 font-semibold uppercase">Trans</div>
                                            <div class="font-extrabold text-xs text-slate-800 dark:text-slate-200 mt-0.5 capitalize">{{ $v->transmission }}</div>
                                        </div>
                                        <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                                            <div class="text-[10px] text-slate-400 font-semibold uppercase">Fuel</div>
                                            <div class="font-extrabold text-xs text-slate-800 dark:text-slate-200 mt-0.5 capitalize">{{ $v->fuel_type }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions Footer -->
                            <div class="p-5 pt-0 flex items-center gap-2">
                                <!-- Quick Toggle Online/Offline -->
                                <form action="/owner/vehicles/{{ $v->id }}/toggle-availability" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full py-2.5 rounded-xl border {{ $v->is_available ? 'border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' : 'border-emerald-500/40 text-emerald-500 bg-emerald-500/10 hover:bg-emerald-500/20' }} font-bold text-xs transition-all cursor-pointer">
                                        {{ $v->is_available ? 'Pause' : 'Make Active' }}
                                    </button>
                                </form>

                                <!-- Edit Button -->
                                <button type="button" 
                                        @click="editingVehicle = {{ Js::from($v) }}; showAddModal = true;"
                                        class="px-3 py-2.5 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/50 font-bold text-xs transition-all cursor-pointer">
                                    Edit
                                </button>

                                <!-- Delete Button -->
                                <form action="/owner/vehicles/{{ $v->id }}/delete" method="POST" onsubmit="return confirm('Are you sure you want to delete this vehicle from your fleet?')">
                                    @csrf
                                    <button type="submit" 
                                            class="px-3 py-2.5 rounded-xl bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/40 font-bold text-xs transition-all cursor-pointer">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- ========================================== -->
        <!-- TAB 3: COMPLETED & HISTORY               -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'history'" class="space-y-6">
            <h2 class="text-xl font-black text-slate-900 dark:text-white">Rental History</h2>

            @if($historyRentals->isEmpty())
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-8 border border-slate-200 dark:border-slate-800 text-center text-slate-400 text-xs">
                    No past completed or rejected bookings yet.
                </div>
            @else
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 font-extrabold uppercase border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="p-4">Booking Ref</th>
                                    <th class="p-4">Vehicle</th>
                                    <th class="p-4">Customer</th>
                                    <th class="p-4">Dates</th>
                                    <th class="p-4">Amount</th>
                                    <th class="p-4">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach($historyRentals as $ride)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                                        <td class="p-4 font-mono font-bold text-slate-700 dark:text-slate-300">{{ $ride->digital_receipt_code ?? ('#'.$ride->id) }}</td>
                                        <td class="p-4 font-extrabold text-slate-900 dark:text-white">{{ $ride->vehicle?->year }} {{ $ride->vehicle?->make }} {{ $ride->vehicle?->model }}</td>
                                        <td class="p-4 text-slate-600 dark:text-slate-400">{{ $ride->rider?->name ?? 'Customer' }}</td>
                                        <td class="p-4 text-slate-500">{{ $ride->pickup_date ? \Carbon\Carbon::parse($ride->pickup_date)->format('M d') : '-' }} → {{ $ride->return_date ? \Carbon\Carbon::parse($ride->return_date)->format('M d') : '-' }}</td>
                                        <td class="p-4 font-black text-slate-900 dark:text-white">{{ $pricing->currency_symbol }}{{ number_format($ride->fare, 2) }}</td>
                                        <td class="p-4">
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider {{ $ride->status === 'completed' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' }}">
                                                {{ ucfirst($ride->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <!-- ========================================== -->
        <!-- MODAL: ADD / EDIT VEHICLE (CRUD)           -->
        <!-- ========================================== -->
        <template x-teleport="body">
            <div x-show="showAddModal" style="display: none;" 
                 class="fixed inset-0 z-[99999] overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
                <div @click.away="showAddModal = false" 
                     class="bg-white dark:bg-slate-900 rounded-3xl max-w-2xl w-full border border-slate-200 dark:border-slate-800 shadow-2xl p-6 sm:p-8 max-h-[90vh] overflow-y-auto">
                    
                    <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-100 dark:border-slate-800">
                        <h3 class="text-xl font-black text-slate-900 dark:text-white" x-text="editingVehicle ? 'Edit Vehicle Details' : 'Add New Rental Vehicle'"></h3>
                        <button type="button" @click="showAddModal = false" class="text-slate-400 hover:text-white text-lg font-bold">✕</button>
                    </div>

                    <form :action="editingVehicle ? '/owner/vehicles/' + editingVehicle.id + '/update' : '/owner/vehicles/create'" 
                          method="POST" 
                          enctype="multipart/form-data" 
                          class="space-y-4">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-extrabold uppercase text-slate-700 dark:text-slate-300 mb-1">Make *</label>
                                <input type="text" name="make" required placeholder="e.g. Toyota, BMW, Tesla" 
                                       :value="editingVehicle ? editingVehicle.make : ''"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-900 dark:text-white outline-none focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-xs font-extrabold uppercase text-slate-700 dark:text-slate-300 mb-1">Model *</label>
                                <input type="text" name="model" required placeholder="e.g. Camry, M4, Model S" 
                                       :value="editingVehicle ? editingVehicle.model : ''"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-900 dark:text-white outline-none focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-xs font-extrabold uppercase text-slate-700 dark:text-slate-300 mb-1">Year *</label>
                                <input type="number" name="year" required min="1990" max="{{ date('Y') + 2 }}" placeholder="{{ date('Y') }}" 
                                       :value="editingVehicle ? editingVehicle.year : '{{ date('Y') }}'"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-900 dark:text-white outline-none focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-xs font-extrabold uppercase text-slate-700 dark:text-slate-300 mb-1">License Plate *</label>
                                <input type="text" name="license_plate" required placeholder="e.g. ABC-1234" 
                                       :value="editingVehicle ? editingVehicle.license_plate : ''"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold text-slate-900 dark:text-white outline-none focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-xs font-extrabold uppercase text-slate-700 dark:text-slate-300 mb-1">Category *</label>
                                <select name="category" required 
                                        :value="editingVehicle ? editingVehicle.category : 'Sedan'"
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-900 dark:text-white outline-none focus:border-blue-500">
                                    <option value="Economy">Economy</option>
                                    <option value="Compact">Compact</option>
                                    <option value="Sedan">Sedan</option>
                                    <option value="SUV">SUV</option>
                                    <option value="Luxury">Luxury</option>
                                    <option value="Van">Van</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-extrabold uppercase text-slate-700 dark:text-slate-300 mb-1">Body Type</label>
                                <input type="text" name="type" placeholder="e.g. Sedan, SUV, Coupe" 
                                       :value="editingVehicle ? editingVehicle.type : 'Sedan'"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-900 dark:text-white outline-none focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-xs font-extrabold uppercase text-slate-700 dark:text-slate-300 mb-1">Daily Rate ({{ $pricing->currency_symbol }}) *</label>
                                <input type="number" step="0.01" name="daily_rate" required placeholder="50.00" 
                                       :value="editingVehicle ? editingVehicle.daily_rate : '65.00'"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-900 dark:text-white outline-none focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-xs font-extrabold uppercase text-slate-700 dark:text-slate-300 mb-1">Security Deposit ({{ $pricing->currency_symbol }})</label>
                                <input type="number" step="0.01" name="security_deposit_amount" placeholder="200.00" 
                                       :value="editingVehicle ? editingVehicle.security_deposit_amount : '200.00'"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-900 dark:text-white outline-none focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-xs font-extrabold uppercase text-slate-700 dark:text-slate-300 mb-1">Transmission *</label>
                                <select name="transmission" required 
                                        :value="editingVehicle ? editingVehicle.transmission : 'automatic'"
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-900 dark:text-white outline-none focus:border-blue-500">
                                    <option value="automatic">Automatic</option>
                                    <option value="manual">Manual</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-extrabold uppercase text-slate-700 dark:text-slate-300 mb-1">Fuel Type *</label>
                                <select name="fuel_type" required 
                                        :value="editingVehicle ? editingVehicle.fuel_type : 'petrol'"
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-900 dark:text-white outline-none focus:border-blue-500">
                                    <option value="petrol">Petrol</option>
                                    <option value="diesel">Diesel</option>
                                    <option value="hybrid">Hybrid</option>
                                    <option value="electric">Electric</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-extrabold uppercase text-slate-700 dark:text-slate-300 mb-1">Seats *</label>
                                <input type="number" name="seats" required min="1" max="50" placeholder="5" 
                                       :value="editingVehicle ? editingVehicle.seats : 5"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-900 dark:text-white outline-none focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-xs font-extrabold uppercase text-slate-700 dark:text-slate-300 mb-1">Min Driver Age</label>
                                <input type="number" name="min_driver_age" min="18" max="100" placeholder="18" 
                                       :value="editingVehicle ? editingVehicle.min_driver_age : 18"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-900 dark:text-white outline-none focus:border-blue-500">
                            </div>
                        </div>

                        <!-- Image Section -->
                        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 space-y-3">
                            <div>
                                <label class="block text-xs font-extrabold uppercase text-slate-700 dark:text-slate-300 mb-1">Upload Vehicle Photo</label>
                                <input type="file" name="vehicle_image" accept="image/*" 
                                       class="w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-500 cursor-pointer">
                            </div>
                            <div>
                                <label class="block text-xs font-extrabold uppercase text-slate-700 dark:text-slate-300 mb-1">Or Direct Photo Image URL</label>
                                <input type="url" name="image_url" placeholder="https://images.unsplash.com/..." 
                                       :value="editingVehicle ? editingVehicle.image_url : ''"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono text-slate-900 dark:text-white outline-none focus:border-blue-500">
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                            <button type="button" @click="showAddModal = false" class="px-5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                                Cancel
                            </button>
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-black text-xs uppercase tracking-wider shadow-lg shadow-blue-500/25">
                                <span x-text="editingVehicle ? 'Save Changes' : 'Add Vehicle to Fleet'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- ========================================== -->
        <!-- MODAL: REJECT REASON MODAL                 -->
        <!-- ========================================== -->
        <template x-teleport="body">
            <div x-show="rejectingRideId !== null" style="display: none;" 
                 class="fixed inset-0 z-[99999] overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
                <div @click.away="rejectingRideId = null" 
                     class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full border border-slate-200 dark:border-slate-800 shadow-2xl p-6">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white mb-2">Reject Rental Request</h3>
                    <p class="text-xs text-slate-400 mb-4">Please provide a reason for declining this request. The customer will be informed.</p>

                    <form :action="'/owner/rentals/' + rejectingRideId + '/reject'" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-extrabold uppercase text-slate-700 dark:text-slate-300 mb-1">Reason for Rejection *</label>
                            <textarea name="reason" required rows="3" placeholder="e.g. Car is scheduled for maintenance on these dates." 
                                      class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white outline-none focus:border-rose-500"></textarea>
                        </div>
                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button type="button" @click="rejectingRideId = null" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-400 hover:text-white">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-black text-xs uppercase tracking-wider shadow-lg shadow-rose-600/30">
                                Confirm Rejection
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </main>
</x-layout>
