<x-layout>
    <x-slot:title>My Rides & Bookings — RideMyCars</x-slot:title>

    <main class="w-full mx-auto px-4 py-8 sm:px-6 lg:px-8" style="max-width: 1100px;" x-data="{ activeTab: new URLSearchParams(window.location.search).get('tab') || 'rides' }">
        <!-- Header -->
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">My Bookings & Trips</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">Manage rides, car rentals, private chauffeurs, and package deliveries</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="/ride" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-bold rounded-xl transition-colors shadow-sm">
                    + Book a Ride
                </a>
                <a href="/rent" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-white/10 dark:hover:bg-white/20 text-gray-800 dark:text-white text-sm font-bold rounded-xl transition-colors">
                    Rent Car
                </a>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex items-center gap-2 border-b border-gray-200 dark:border-white/10 mb-8 overflow-x-auto pb-2">
            <button @click="activeTab = 'rides'"
                :class="activeTab === 'rides' ? 'border-brand-500 text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-950/30' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                class="px-4 py-2.5 rounded-xl font-bold text-sm border flex items-center gap-2 shrink-0 transition-all">
                <span>🚗 Rides & Rentals</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-200 dark:bg-white/10 text-gray-700 dark:text-gray-300">
                    {{ $rides->total() ?? $rides->count() }}
                </span>
            </button>

            <button @click="activeTab = 'chauffeurs'"
                :class="activeTab === 'chauffeurs' ? 'border-brand-500 text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-950/30' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                class="px-4 py-2.5 rounded-xl font-bold text-sm border flex items-center gap-2 shrink-0 transition-all">
                <span>👔 Hire a Driver</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-200 dark:bg-white/10 text-gray-700 dark:text-gray-300">
                    {{ count($driverBookings ?? []) }}
                </span>
            </button>

            <button @click="activeTab = 'deliveries'"
                :class="activeTab === 'deliveries' ? 'border-brand-500 text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-950/30' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                class="px-4 py-2.5 rounded-xl font-bold text-sm border flex items-center gap-2 shrink-0 transition-all">
                <span>📦 Deliveries</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-200 dark:bg-white/10 text-gray-700 dark:text-gray-300">
                    {{ count($packageDeliveries ?? []) }}
                </span>
            </button>
        </div>

        <!-- ========================================== -->
        <!-- TAB 1: RIDES & CAR RENTALS                 -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'rides'" class="space-y-6">
            @if($rides->isEmpty())
                <div class="text-center py-16 bg-white dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-3xl">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-white/10 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M5 17H3v-6l2-5h9l4 5h1a2 2 0 0 1 2 2v4h-2m-4 0H9"/></svg>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 font-semibold text-lg">No rides or car rentals yet</p>
                    <p class="text-gray-400 text-sm mt-1">Book your first ride or reserve a vehicle to get started!</p>
                    <div class="mt-4 flex justify-center gap-3">
                        <a href="/ride" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl transition-colors text-sm">Book a Ride</a>
                        <a href="/rent" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-white/10 text-gray-800 dark:text-white font-bold rounded-xl transition-colors text-sm">Rent a Car</a>
                    </div>
                </div>
            @else
                <div class="space-y-6">
                    @foreach($rides as $ride)
                        @php
                            $isRental = str_starts_with($ride->digital_receipt_code ?? '', 'RENT-');
                            $serviceType = $isRental ? 'car_rental' : 'ride';
                            $paymentStatus = strtolower($ride->payment_status ?? 'pending');
                            $isPaymentConfirmed = in_array($paymentStatus, ['paid', 'hold', 'authorized', 'completed', 'deposit_paid', 'partially_paid']);
                            $isDriverConfirmed = $ride->driver && in_array(strtolower($ride->status), ['accepted', 'en_route', 'arrived', 'in_progress', 'completed']);
                            $mapKey = config('services.google_maps.api_key', env('GOOGLE_MAPS_API_KEY'));
                            $pickup = urlencode($ride->pickup_location);
                            $dropoff = urlencode($ride->dropoff_location);
                            $mapUrl = "https://maps.googleapis.com/maps/api/staticmap?size=800x220&scale=2&maptype=roadmap&markers=color:green%7Clabel:A%7C{$pickup}&markers=color:red%7Clabel:B%7C{$dropoff}&path=color:0x4f46e5%7Cweight:5%7Cgeodesic:true%7C{$pickup}%7C{$dropoff}&key={$mapKey}&style=feature:all%7Celement:labels%7Cvisibility:simplified";
                            $displayFare = ($ride->fare && floatval($ride->fare) > 0) ? floatval($ride->fare) : 28.50;
                        @endphp

                        <div class="bg-white dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                            <!-- Map + Status Header -->
                            <div class="relative">
                                <img src="{{ $mapUrl }}" alt="Route map" class="w-full h-[140px] sm:h-[160px] object-cover" loading="lazy" onerror="this.style.display='none'">
                                
                                <!-- Status Badge on Map -->
                                <div class="absolute top-3 left-3 flex items-center gap-2">
                                    <span class="text-xs font-extrabold uppercase px-3 py-1.5 rounded-lg shadow-md backdrop-blur-sm
                                        @if($ride->status === 'completed') bg-green-500 text-white
                                        @elseif($ride->status === 'failed' || $ride->status === 'cancelled') bg-red-500 text-white
                                        @elseif(in_array($ride->status, ['accepted','en_route','arrived','in_progress'])) bg-blue-500 text-white
                                        @else bg-gray-700 text-white
                                        @endif">
                                        {{ strtoupper(str_replace('_', ' ', $ride->status)) }}
                                    </span>

                                    @if($isRental)
                                        <span class="text-xs font-extrabold px-2.5 py-1.5 rounded-lg shadow-md bg-amber-500 text-white">
                                            🚘 CAR RENTAL
                                        </span>
                                    @endif
                                </div>
                                
                                <!-- Date on Map -->
                                <div class="absolute top-3 right-3">
                                    <span class="text-xs font-semibold text-white bg-black/60 backdrop-blur-sm px-3 py-1.5 rounded-lg">
                                        {{ $ride->created_at->timezone(config('app.timezone', 'Asia/Kolkata'))->format('M d, Y · h:i A') }}
                                    </span>
                                </div>
                            </div>

                            <!-- Ride Details -->
                            <div class="p-5">
                                <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto] gap-4">
                                    <!-- Locations -->
                                    <div class="min-w-0">
                                        <div class="flex gap-3">
                                            <div class="flex flex-col items-center pt-1.5 shrink-0">
                                                <div class="w-3 h-3 rounded-full bg-green-500 border-2 border-green-200"></div>
                                                <div class="w-0.5 h-6 bg-gray-200 dark:bg-white/10 my-0.5"></div>
                                                <div class="w-3 h-3 rounded-full bg-red-500 border-2 border-red-200"></div>
                                            </div>
                                            <div class="flex-1 min-w-0 space-y-2">
                                                <div>
                                                    <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Pickup</p>
                                                    <p class="text-sm font-semibold text-gray-900 dark:text-white leading-snug break-words">{{ $ride->pickup_location }}</p>
                                                </div>

                                                @if($ride->stops && $ride->stops->count() > 0)
                                                    @foreach($ride->stops as $stop)
                                                        <div class="pl-2 border-l-2 border-amber-400">
                                                            <p class="text-[10px] uppercase font-bold text-amber-600 dark:text-amber-400 tracking-wider">Stop {{ $stop->stop_order }}</p>
                                                            <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 leading-snug break-words">{{ $stop->location }}</p>
                                                        </div>
                                                    @endforeach
                                                @endif

                                                <div>
                                                    <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Dropoff</p>
                                                    <p class="text-sm font-semibold text-gray-900 dark:text-white leading-snug break-words">{{ $ride->dropoff_location }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pricing & Payment Status -->
                                    <div class="sm:text-right sm:pl-4 sm:border-l sm:border-gray-100 sm:dark:border-white/10 flex sm:flex-col items-center sm:items-end gap-3 sm:gap-1 shrink-0">
                                        <p class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white">${{ number_format($ride->total_amount ?? $displayFare, 2) }}</p>
                                        
                                        @if($ride->total_amount > 0 && $ride->paid_amount !== null)
                                            <div class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                                                Paid: ${{ number_format($ride->paid_amount, 2) }}
                                            </div>
                                            @if($ride->remaining_balance > 0)
                                                <div class="text-[11px] font-semibold text-amber-600 dark:text-amber-400">
                                                    Due: ${{ number_format($ride->remaining_balance, 2) }}
                                                </div>
                                            @endif
                                        @endif

                                        <div class="flex flex-wrap gap-1 mt-1 justify-end">
                                            @if($isPaymentConfirmed)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300">
                                                    ✓ Escrow Paid
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300">
                                                    ⚠️ Payment Pending
                                                </span>
                                            @endif

                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-bold uppercase bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300">
                                                {{ ucfirst($ride->payment_method ?? 'Card') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Rental & Extra Meta -->
                                @if($ride->pickup_time || $ride->fuel_policy || $ride->passenger_phone || $isRental)
                                    <div class="mt-3 pt-3 border-t border-gray-100 dark:border-white/10 flex flex-wrap items-center justify-between gap-2 text-xs">
                                        <div class="flex flex-wrap items-center gap-2">
                                            @if($ride->pickup_date && $ride->pickup_time)
                                                <span class="px-2.5 py-1 rounded-md bg-gray-100 dark:bg-white/10 text-gray-800 dark:text-gray-200 font-medium">
                                                    ⏰ Pickup: {{ $ride->pickup_date->format('M d, Y') }} at {{ $ride->pickup_time }}
                                                </span>
                                            @endif
                                            @if($ride->fuel_policy)
                                                <span class="px-2.5 py-1 rounded-md bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 font-medium">
                                                    ⛽ Fuel: {{ $ride->fuel_policy }}
                                                </span>
                                            @endif
                                            @if($ride->insurance_accepted)
                                                <span class="px-2.5 py-1 rounded-md bg-green-50 dark:bg-green-950/40 text-green-700 dark:text-green-300 font-medium">
                                                    🛡️ Full Protection
                                                </span>
                                            @endif
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0">
                                            @if($isRental)
                                                <a href="/rent/booking/{{ $ride->id }}/voucher" class="px-3 py-1.5 bg-brand-500 hover:bg-brand-600 text-white rounded-lg font-bold text-xs shadow-sm transition-colors">
                                                    📄 Rental Voucher
                                                </a>

                                                @if(!in_array($ride->status, ['completed', 'cancelled']))
                                                    <form action="/rent/booking/{{ $ride->id }}/cancel" method="POST" onsubmit="return confirm('Are you sure you want to cancel this car rental? Free cancellation terms apply.');">
                                                        @csrf
                                                        <button type="submit" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg font-bold text-xs shadow-sm transition-colors">
                                                            ❌ Cancel
                                                        </button>
                                                    </form>
                                                @endif
                                            @else
                                                <a href="/ride/track/{{ $ride->id }}" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold text-xs shadow-sm transition-colors flex items-center gap-1">
                                                    <span>📍 Live Radar & Track</span>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                <!-- ======================================================== -->
                                <!-- DRIVER DETAILS SECTION (STRICTLY GATED BY ESCROW PAYMENT)-->
                                <!-- ======================================================== -->
                                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-white/10">
                                    @if(!$isPaymentConfirmed)
                                        <!-- State 1: Payment Not Confirmed (Privacy Protected) -->
                                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/20">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center text-amber-500 shrink-0 text-lg font-black">
                                                    🔒
                                                </div>
                                                <div>
                                                    <p class="text-xs font-bold text-gray-900 dark:text-white">
                                                        {{ $isRental ? 'Chauffeur / Agent Details Hidden' : 'Driver Contact & Details Protected' }}
                                                    </p>
                                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                                        Escrow payment required via Stripe Card, Apple Pay, or MoMo Pay to reveal driver identity.
                                                    </p>
                                                </div>
                                            </div>
                                            <a href="/payment/verify-details/{{ $serviceType }}/{{ $ride->id }}" class="w-full sm:w-auto text-center px-4 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold text-xs rounded-xl shadow-sm transition-all whitespace-nowrap">
                                                Pay & Unlock Driver →
                                            </a>
                                        </div>

                                    @elseif(!$isDriverConfirmed)
                                        <!-- State 2: Payment Confirmed, Searching for Driver -->
                                        <div class="flex items-center justify-between gap-3 p-3.5 rounded-xl bg-blue-500/10 border border-blue-500/20">
                                            <div class="flex items-center gap-3">
                                                <div class="relative flex items-center justify-center w-10 h-10 shrink-0">
                                                    <div class="absolute inset-0 rounded-full bg-blue-500/30 animate-ping"></div>
                                                    <div class="w-9 h-9 rounded-full bg-blue-500 text-white flex items-center justify-center text-sm font-bold shadow-md">
                                                        📡
                                                    </div>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                                        <span>Escrow Confirmed</span>
                                                        <span class="text-emerald-500 font-black">✓</span>
                                                        <span class="text-gray-400 font-normal">• Searching for Available Driver</span>
                                                    </p>
                                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                                        Broadcasting to verified drivers nearby. Contact details and live GPS tracking will unlock immediately upon driver acceptance.
                                                    </p>
                                                </div>
                                            </div>
                                            <a href="/ride/track/{{ $ride->id }}" class="hidden sm:inline-block px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-colors whitespace-nowrap">
                                                View Live Radar
                                            </a>
                                        </div>

                                    @else
                                        <!-- State 3: Payment Confirmed AND Driver Confirmed (Unlocked) -->
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
                                            <div class="flex items-center gap-3">
                                                @if($ride->driver->avatar_url)
                                                    <img src="{{ $ride->driver->avatar_url }}" alt="{{ $ride->driver->name }}" class="w-10 h-10 rounded-full object-cover border-2 border-emerald-500 shrink-0" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                    <div style="display: none;" class="w-10 h-10 rounded-full bg-emerald-500 text-white items-center justify-center text-sm font-bold shrink-0">
                                                        {{ strtoupper(substr($ride->driver->name ?? 'D', 0, 1)) }}
                                                    </div>
                                                @else
                                                    <div class="w-10 h-10 rounded-full bg-emerald-500 text-white flex items-center justify-center text-sm font-bold shrink-0">
                                                        {{ strtoupper(substr($ride->driver->name ?? 'D', 0, 1)) }}
                                                    </div>
                                                @endif
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-1.5">
                                                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $ride->driver->name }}</p>
                                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 font-bold uppercase">Confirmed Driver</span>
                                                    </div>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $ride->vehicle ? ($ride->vehicle->make . ' ' . $ride->vehicle->model . ' • ' . $ride->vehicle->plate_number) : 'Verified Professional Driver' }}
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- Direct Contact Buttons -->
                                            <div class="flex items-center gap-2 shrink-0">
                                                @if($ride->driver->phone)
                                                    <a href="tel:{{ $ride->driver->phone }}" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-xs shadow-sm transition-colors flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                                        <span>Call</span>
                                                    </a>
                                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $ride->driver->phone) }}" target="_blank" class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-lg font-bold text-xs shadow-sm transition-colors flex items-center gap-1">
                                                        <span>WhatsApp</span>
                                                    </a>
                                                @endif
                                                @if(!$isRental)
                                                    <a href="/ride/track/{{ $ride->id }}" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold text-xs shadow-sm transition-colors">
                                                        Track GPS
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Reviews Section -->
                            @if($ride->status === 'completed' && ($ride->riderReview || $ride->driverReview))
                                <div class="px-5 pb-5">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        @if($ride->riderReview)
                                            <div class="bg-indigo-50/50 dark:bg-indigo-900/10 border border-indigo-100 dark:border-indigo-800/30 rounded-xl p-3">
                                                <p class="text-xs font-bold text-indigo-700 dark:text-indigo-300 mb-1">Your Review</p>
                                                <div class="flex items-center gap-1 mb-1">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <span class="text-sm {{ $i <= $ride->riderReview->rating ? 'text-yellow-400' : 'text-gray-300' }}">★</span>
                                                    @endfor
                                                    <span class="text-xs text-gray-500 ml-1">({{ $ride->riderReview->rating }}/5)</span>
                                                </div>
                                                @if($ride->riderReview->comment)
                                                    <p class="text-xs text-gray-600 dark:text-gray-400 italic break-words">"{{ $ride->riderReview->comment }}"</p>
                                                @endif
                                            </div>
                                        @endif

                                        @if($ride->driverReview)
                                            <div class="bg-emerald-50/50 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-800/30 rounded-xl p-3">
                                                <p class="text-xs font-bold text-emerald-700 dark:text-emerald-300 mb-1">Driver's Review</p>
                                                <div class="flex items-center gap-1 mb-1">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <span class="text-sm {{ $i <= $ride->driverReview->rating ? 'text-yellow-400' : 'text-gray-300' }}">★</span>
                                                    @endfor
                                                    <span class="text-xs text-gray-500 ml-1">({{ $ride->driverReview->rating }}/5)</span>
                                                </div>
                                                @if($ride->driverReview->comment)
                                                    <p class="text-xs text-gray-600 dark:text-gray-400 italic break-words">"{{ $ride->driverReview->comment }}"</p>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $rides->links() }}
                </div>
            @endif
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: HIRE A DRIVER (CHAUFFEUR BOOKINGS)  -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'chauffeurs'" class="space-y-6" style="display: none;">
            @if(empty($driverBookings) || count($driverBookings) === 0)
                <div class="text-center py-16 bg-white dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-3xl">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-white/10 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 font-semibold text-lg">No chauffeur bookings yet</p>
                    <p class="text-gray-400 text-sm mt-1">Hire a verified professional driver by the hour, day, or week.</p>
                    <div class="mt-4">
                        <a href="/driver-hire" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl transition-colors text-sm inline-block">Hire a Driver</a>
                    </div>
                </div>
            @else
                <div class="space-y-6">
                    @foreach($driverBookings as $booking)
                        @php
                            $bookingPayment = strtolower($booking->payment_status ?? 'pending');
                            $isBookingPaid = in_array($bookingPayment, ['paid', 'hold', 'authorized', 'completed', 'deposit_paid', 'partially_paid']);
                            $isChauffeurConfirmed = $booking->driver && in_array(strtolower($booking->booking_status ?? ''), ['confirmed', 'assigned', 'started', 'completed']);
                        @endphp

                        <div class="bg-white dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow p-5">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-gray-100 dark:border-white/10 gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-1 rounded-md text-xs font-black uppercase bg-indigo-100 text-indigo-800 dark:bg-indigo-950/50 dark:text-indigo-300">
                                            👔 CHAUFFEUR BOOKING
                                        </span>
                                        <span class="text-xs font-mono font-bold text-gray-500">
                                            #{{ $booking->booking_code ?? 'DRV-'.$booking->id }}
                                        </span>
                                    </div>
                                    <h3 class="text-base font-bold text-gray-900 dark:text-white mt-1">
                                        {{ ucfirst($booking->service_type ?? 'Dedicated Chauffeur') }} ({{ ucfirst($booking->duration_type ?? 'Daily') }})
                                    </h3>
                                </div>
                                <div class="text-right">
                                    <p class="text-2xl font-black text-gray-900 dark:text-white">${{ number_format($booking->total_price ?? 0, 2) }}</p>
                                    <span class="text-xs font-bold uppercase px-2 py-0.5 rounded-full
                                        @if($isBookingPaid) bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300
                                        @else bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300
                                        @endif">
                                        {{ $isBookingPaid ? 'Escrow Confirmed' : 'Payment Pending' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Booking Locations & Dates -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 my-4 text-xs">
                                <div class="space-y-1.5">
                                    <p class="text-gray-400 font-bold uppercase tracking-wider">Pickup Location</p>
                                    <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $booking->pickup_location }}</p>
                                    @if($booking->dropoff_location)
                                        <p class="text-gray-400 font-bold uppercase tracking-wider mt-2">Destination / Route</p>
                                        <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $booking->dropoff_location }}</p>
                                    @endif
                                </div>
                                <div class="space-y-1.5">
                                    <p class="text-gray-400 font-bold uppercase tracking-wider">Schedule</p>
                                    <p class="font-semibold text-gray-800 dark:text-gray-200">
                                        📅 {{ $booking->start_date ? $booking->start_date->format('M d, Y') : 'Immediate' }}
                                        @if($booking->start_time) at {{ $booking->start_time }} @endif
                                    </p>
                                    @if($booking->car_make_model)
                                        <p class="text-gray-400 font-bold uppercase tracking-wider mt-2">Customer Vehicle</p>
                                        <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $booking->car_make_model }} ({{ $booking->registration_number ?? 'Client Car' }})</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Gated Driver Profile Card -->
                            <div class="pt-4 border-t border-gray-100 dark:border-white/10">
                                @if(!$isBookingPaid)
                                    <!-- Unpaid Privacy Lock -->
                                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/20">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center text-amber-500 shrink-0 text-lg font-black">
                                                🔒
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-gray-900 dark:text-white">Chauffeur Identity & Contact Locked</p>
                                                <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                                    Escrow payment authorization required via Stripe, Apple Pay, or MoMo Pay to reveal chauffeur details.
                                                </p>
                                            </div>
                                        </div>
                                        <a href="/payment/verify-details/driver_booking/{{ $booking->id }}" class="w-full sm:w-auto text-center px-4 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold text-xs rounded-xl shadow-sm transition-all whitespace-nowrap">
                                            Authorize Payment (${{ number_format($booking->total_price ?? 0, 2) }}) →
                                        </a>
                                    </div>

                                @elseif(!$isChauffeurConfirmed)
                                    <!-- Paid, Matching Chauffeur -->
                                    <div class="flex items-center justify-between gap-3 p-3.5 rounded-xl bg-blue-500/10 border border-blue-500/20">
                                        <div class="flex items-center gap-3">
                                            <div class="relative flex items-center justify-center w-10 h-10 shrink-0">
                                                <div class="absolute inset-0 rounded-full bg-blue-500/30 animate-ping"></div>
                                                <div class="w-9 h-9 rounded-full bg-blue-500 text-white flex items-center justify-center text-sm font-bold shadow-md">
                                                    👔
                                                </div>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                                    <span>Escrow Confirmed</span>
                                                    <span class="text-emerald-500 font-black">✓</span>
                                                    <span class="text-gray-400 font-normal">• Assigning Verified Chauffeur</span>
                                                </p>
                                                <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                                    We are dispatching a top-rated vetted chauffeur. You will receive an instant notification and full contact card upon confirmation.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                @else
                                    <!-- Paid & Confirmed Chauffeur -->
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
                                        <div class="flex items-center gap-3">
                                            @if($booking->driver->avatar_url)
                                                <img src="{{ $booking->driver->avatar_url }}" alt="{{ $booking->driver->name }}" class="w-10 h-10 rounded-full object-cover border-2 border-emerald-500 shrink-0">
                                            @else
                                                <div class="w-10 h-10 rounded-full bg-emerald-500 text-white flex items-center justify-center text-sm font-bold shrink-0">
                                                    {{ strtoupper(substr($booking->driver->name ?? 'D', 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $booking->driver->name }}</p>
                                                    <span class="text-[10px] px-2 py-0.5 rounded bg-emerald-500 text-white font-bold uppercase">Assigned Chauffeur</span>
                                                </div>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    Phone: {{ $booking->driver->phone ?? 'Contact available below' }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0">
                                            @if($booking->driver->phone)
                                                <a href="tel:{{ $booking->driver->phone }}" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-xs shadow-sm transition-colors flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                                    <span>Call Chauffeur</span>
                                                </a>
                                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $booking->driver->phone) }}" target="_blank" class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-lg font-bold text-xs shadow-sm transition-colors">
                                                    <span>WhatsApp</span>
                                                </a>
                                            @endif
                                            <a href="/driver-hire/confirmation/{{ $booking->id }}" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-white/10 dark:hover:bg-white/20 text-gray-800 dark:text-white rounded-lg font-bold text-xs transition-colors">
                                                Booking Receipt
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- ========================================== -->
        <!-- TAB 3: PACKAGE DELIVERIES                  -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'deliveries'" class="space-y-6" style="display: none;">
            @if(empty($packageDeliveries) || count($packageDeliveries) === 0)
                <div class="text-center py-16 bg-white dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-3xl">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-white/10 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 font-semibold text-lg">No package deliveries yet</p>
                    <p class="text-gray-400 text-sm mt-1">Send parcels, groceries, documents, and cargo across town with live GPS.</p>
                    <div class="mt-4">
                        <a href="/delivery" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl transition-colors text-sm inline-block">Send a Package</a>
                    </div>
                </div>
            @else
                <div class="space-y-6">
                    @foreach($packageDeliveries as $delivery)
                        @php
                            $deliveryPayment = strtolower($delivery->payment_status ?? 'pending');
                            $isDeliveryPaid = in_array($deliveryPayment, ['paid', 'hold', 'authorized', 'completed', 'deposit_paid', 'partially_paid']);
                            $isCourierConfirmed = $delivery->courier && in_array(strtolower($delivery->delivery_status ?? ''), ['accepted', 'arrived_pickup', 'picked_up', 'in_transit', 'arrived_destination', 'delivered']);
                        @endphp

                        <div class="bg-white dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow p-5">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-gray-100 dark:border-white/10 gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-1 rounded-md text-xs font-black uppercase bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300">
                                            📦 PACKAGE DELIVERY
                                        </span>
                                        <span class="text-xs font-mono font-bold text-gray-500">
                                            #{{ $delivery->delivery_code }}
                                        </span>
                                    </div>
                                    <h3 class="text-base font-bold text-gray-900 dark:text-white mt-1">
                                        {{ ucfirst($delivery->package_category ?? 'General Parcel') }} • {{ ucfirst($delivery->delivery_type ?? 'Express') }}
                                    </h3>
                                </div>
                                <div class="text-right">
                                    <p class="text-2xl font-black text-gray-900 dark:text-white">${{ number_format($delivery->total_price ?? 0, 2) }}</p>
                                    <span class="text-xs font-bold uppercase px-2 py-0.5 rounded-full
                                        @if($isDeliveryPaid) bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300
                                        @else bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300
                                        @endif">
                                        {{ $isDeliveryPaid ? 'Escrow Confirmed' : 'Payment Pending' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Delivery Route & Recipients -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 my-4 text-xs">
                                <div class="space-y-1.5">
                                    <p class="text-gray-400 font-bold uppercase tracking-wider">Pickup</p>
                                    <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $delivery->pickup_location }}</p>
                                    <p class="text-gray-500">Sender: {{ $delivery->sender_name }} ({{ $delivery->sender_phone }})</p>
                                </div>
                                <div class="space-y-1.5">
                                    <p class="text-gray-400 font-bold uppercase tracking-wider">Dropoff</p>
                                    <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $delivery->dropoff_location }}</p>
                                    <p class="text-gray-500">Recipient: {{ $delivery->recipient_name }} ({{ $delivery->recipient_phone }})</p>
                                </div>
                            </div>

                            <!-- Gated Courier Profile Card -->
                            <div class="pt-4 border-t border-gray-100 dark:border-white/10">
                                @if(!$isDeliveryPaid)
                                    <!-- Unpaid Privacy Lock -->
                                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/20">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center text-amber-500 shrink-0 text-lg font-black">
                                                🔒
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-gray-900 dark:text-white">Courier Identity & Live Dispatch Protected</p>
                                                <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                                    Escrow payment authorization required via Stripe Card, Apple Pay, or MoMo Pay to dispatch courier.
                                                </p>
                                            </div>
                                        </div>
                                        <a href="/payment/verify-details/package_delivery/{{ $delivery->id }}" class="w-full sm:w-auto text-center px-4 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold text-xs rounded-xl shadow-sm transition-all whitespace-nowrap">
                                            Pay & Dispatch Courier (${{ number_format($delivery->total_price ?? 0, 2) }}) →
                                        </a>
                                    </div>

                                @elseif(!$isCourierConfirmed)
                                    <!-- Paid, Dispatching Courier -->
                                    <div class="flex items-center justify-between gap-3 p-3.5 rounded-xl bg-blue-500/10 border border-blue-500/20">
                                        <div class="flex items-center gap-3">
                                            <div class="relative flex items-center justify-center w-10 h-10 shrink-0">
                                                <div class="absolute inset-0 rounded-full bg-blue-500/30 animate-ping"></div>
                                                <div class="w-9 h-9 rounded-full bg-blue-500 text-white flex items-center justify-center text-sm font-bold shadow-md">
                                                    📦
                                                </div>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                                    <span>Escrow Confirmed</span>
                                                    <span class="text-emerald-500 font-black">✓</span>
                                                    <span class="text-gray-400 font-normal">• Dispatching Nearest Courier</span>
                                                </p>
                                                <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                                    Broadcast active. Courier phone number, photo, and live GPS route will unlock as soon as accepted.
                                                </p>
                                            </div>
                                        </div>
                                        <a href="/delivery/tracker?tracking_code={{ $delivery->delivery_code }}" class="hidden sm:inline-block px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-colors whitespace-nowrap">
                                            Live Tracker
                                        </a>
                                    </div>

                                @else
                                    <!-- Paid & Confirmed Courier -->
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
                                        <div class="flex items-center gap-3">
                                            @if($delivery->courier->avatar_url)
                                                <img src="{{ $delivery->courier->avatar_url }}" alt="{{ $delivery->courier->name }}" class="w-10 h-10 rounded-full object-cover border-2 border-emerald-500 shrink-0">
                                            @else
                                                <div class="w-10 h-10 rounded-full bg-emerald-500 text-white flex items-center justify-center text-sm font-bold shrink-0">
                                                    {{ strtoupper(substr($delivery->courier->name ?? 'C', 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $delivery->courier->name }}</p>
                                                    <span class="text-[10px] px-2 py-0.5 rounded bg-emerald-500 text-white font-bold uppercase">Assigned Courier</span>
                                                </div>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    Phone: {{ $delivery->courier->phone ?? 'Contact available below' }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0">
                                            @if($delivery->courier->phone)
                                                <a href="tel:{{ $delivery->courier->phone }}" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-xs shadow-sm transition-colors flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                                    <span>Call Courier</span>
                                                </a>
                                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $delivery->courier->phone) }}" target="_blank" class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-lg font-bold text-xs shadow-sm transition-colors">
                                                    <span>WhatsApp</span>
                                                </a>
                                            @endif
                                            <a href="/delivery/tracker?tracking_code={{ $delivery->delivery_code }}" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold text-xs shadow-sm transition-colors">
                                                Live Tracker
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </main>
</x-layout>
