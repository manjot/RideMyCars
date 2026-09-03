<x-layout theme="theme-ride">
    <x-slot:title>Book a Ride — RideMyCars</x-slot>
    <x-slot:head>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <style>
            .pac-container {
                border-radius: 16px;
                box-shadow: 0 12px 40px rgba(0,0,0,0.18);
                border: 1px solid rgba(0,0,0,0.08);
                margin-top: 6px;
                z-index: 9999999 !important;
            }
            .pac-item {
                white-space: normal !important;
                word-wrap: break-word !important;
                padding: 12px 16px !important;
                font-size: 14px !important;
            }
            @keyframes pulse-radar {
                0% { transform: scale(0.95); opacity: 0.8; }
                50% { transform: scale(1.15); opacity: 0.3; }
                100% { transform: scale(0.95); opacity: 0.8; }
            }
            .animate-pulse-radar {
                animation: pulse-radar 2s infinite ease-in-out;
            }
        </style>
    </x-slot>

    <main class="w-full mx-auto px-4 py-6 sm:px-6 lg:px-8 max-w-[1500px]" x-data="rideBooking">
        <!-- Category Banner Component -->
        <x-category-banner category="Ride" />
        
        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800/30 text-emerald-800 dark:text-emerald-200 font-semibold flex items-center gap-3 shadow-sm">
                <span class="text-2xl">🚘</span>
                <div>
                    <h4 class="font-extrabold text-base text-gray-900 dark:text-white">Ride Booked Successfully!</h4>
                    <p class="text-xs text-emerald-700 dark:text-emerald-300 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800/30 text-rose-800 dark:text-rose-200 font-semibold flex items-center gap-3 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <form @submit.prevent="submitBooking" action="/ride/book" method="POST" class="w-full">
            @csrf
            
            <!-- DESKTOP SPLIT LAYOUT: LEFT DYNAMIC PANEL (~40%) / RIGHT LIVE MAP (~60%) -->
            <div class="flex flex-col md:flex-row items-start gap-6 w-full relative">
                
                <!-- LEFT SIDE: DYNAMIC BOOKING PANEL (40% width) -->
                <div class="w-full md:w-[420px] lg:w-[450px] shrink-0 space-y-6 z-10">
                    
                    <!-- STEP 1: FIND A TRIP (bookingStep === 'find_trip') -->
                    <div x-show="bookingStep === 'find_trip'" class="w-full bg-white dark:bg-[#111] p-6 shadow-[0_8px_30px_rgb(0,0,0,0.08)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.3)] rounded-[24px] border border-gray-100 dark:border-white/10 space-y-5">
                        <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Find a trip</h1>
                        
                        <!-- Promo Offer Badge -->
                        <div class="p-3.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/60 dark:border-emerald-800/40 rounded-2xl flex items-center justify-between text-xs font-extrabold text-emerald-800 dark:text-emerald-300 shadow-sm">
                            <div class="flex items-center gap-2">
                                <span>🏷️</span>
                                <span>100% off your next ride. Up to $10.00</span>
                            </div>
                            <span class="text-emerald-500 font-extrabold cursor-pointer" title="Offer details">ⓘ</span>
                        </div>

                        <!-- Control Pills: Pickup Now & Rider Selection -->
                        <div class="flex items-center gap-2">
                            <!-- Schedule Pill -->
                            <button type="button" @click="showScheduleModal = true" class="flex-1 py-2.5 px-3.5 bg-gray-100 dark:bg-[#222] hover:bg-gray-200 dark:hover:bg-[#333] rounded-full text-xs font-extrabold text-gray-900 dark:text-white flex items-center justify-between transition-all">
                                <div class="flex items-center gap-2">
                                    <span>🕒</span>
                                    <span x-text="schedule_type === 'now' ? 'Pickup now' : 'Schedule for later'"></span>
                                </div>
                                <span class="text-gray-400">▼</span>
                            </button>

                            <!-- Passenger Pill -->
                            <button type="button" @click="showRiderModal = true" class="flex-1 py-2.5 px-3.5 bg-gray-100 dark:bg-[#222] hover:bg-gray-200 dark:hover:bg-[#333] rounded-full text-xs font-extrabold text-gray-900 dark:text-white flex items-center justify-between transition-all">
                                <div class="flex items-center gap-2">
                                    <span>👤</span>
                                    <span x-text="riderType === 'me' ? 'For me' : 'Someone else'"></span>
                                </div>
                                <span class="text-gray-400">▼</span>
                            </button>
                        </div>

                        <!-- Location Inputs Section -->
                        <div class="space-y-3 relative">
                            <!-- Pickup Location Input -->
                            <div class="relative">
                                <div class="absolute left-3.5 top-3.5 text-emerald-500 font-black text-sm">●</div>
                                <input type="text" id="pickup_location" name="pickup_location" x-model="pickup" required
                                       @input.debounce.300ms="searchPickupLocation()"
                                       @focus="if(pickupSuggestions.length > 0) showPickupSuggestions = true"
                                       placeholder="Search pickup location (address, hotel, airport...)" 
                                       class="w-full pl-10 pr-10 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 text-xs font-extrabold focus:outline-none focus:ring-2 focus:ring-black dark:focus:ring-white transition-all">
                                <button type="button" id="use_my_location_btn" title="Use current location" class="absolute right-3 top-3 text-gray-400 hover:text-emerald-500 font-bold text-xs">📍</button>

                                <!-- Pickup Suggestions Dropdown -->
                                <div x-show="showPickupSuggestions && pickupSuggestions.length > 0" 
                                     @click.away="showPickupSuggestions = false"
                                     style="display: none;"
                                     class="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl shadow-2xl z-[9999] max-h-52 overflow-y-auto divide-y divide-gray-100 dark:divide-white/5">
                                    <template x-for="item in pickupSuggestions" :key="item.place_id || item.osm_id">
                                        <button type="button" @click="selectPickupSuggestion(item)" class="w-full text-left px-3.5 py-2.5 hover:bg-gray-50 dark:hover:bg-[#222] transition-colors flex items-start gap-2.5 cursor-pointer">
                                            <span class="text-emerald-500 text-xs shrink-0 mt-0.5">●</span>
                                            <div class="min-w-0 flex-1">
                                                <span class="font-extrabold block text-xs text-gray-900 dark:text-white truncate" x-text="item.display_name"></span>
                                            </div>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- Dynamic Intermediate Stops (Positioned strictly between Pickup and Destination) -->
                            <template x-for="(stop, index) in stops" :key="stop.id">
                                <div class="relative space-y-1">
                                    <div class="relative flex items-center gap-2">
                                        <div class="absolute left-3.5 top-3.5 text-amber-500 font-bold text-xs">📍</div>
                                        <input type="text" x-model="stop.location" 
                                               @input.debounce.300ms="searchStopLocation(stop)"
                                               @focus="if(stop.suggestions && stop.suggestions.length > 0) stop.showSuggestions = true"
                                               :placeholder="'Search Stop ' + (index + 1) + ' location (hotel, airport, landmark...)'"
                                               class="w-full pl-10 pr-10 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 text-xs font-extrabold focus:outline-none focus:ring-2 focus:ring-black dark:focus:ring-white transition-all"
                                               :class="stop.isSelected ? 'border-emerald-500 ring-1 ring-emerald-500/30' : ''">
                                        <button type="button" @click="removeStop(index)" class="p-2.5 text-rose-500 hover:text-rose-700 bg-gray-50 dark:bg-[#1a1a1a] hover:bg-rose-50 dark:hover:bg-rose-950/40 border border-gray-200 dark:border-white/10 rounded-2xl font-bold text-xs transition-colors shrink-0" title="Remove stop">✕</button>
                                    </div>

                                    <!-- Stop Suggestions Dropdown -->
                                    <div x-show="stop.showSuggestions && stop.suggestions && stop.suggestions.length > 0" 
                                         @click.away="stop.showSuggestions = false"
                                         style="display: none;"
                                         class="absolute left-0 right-12 top-full mt-1 bg-white dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl shadow-2xl z-[9999] max-h-52 overflow-y-auto divide-y divide-gray-100 dark:divide-white/5">
                                        <template x-for="item in stop.suggestions" :key="item.place_id || item.osm_id">
                                            <button type="button" @click="selectStopSuggestion(stop, item)" class="w-full text-left px-3.5 py-2.5 hover:bg-gray-50 dark:hover:bg-[#222] transition-colors flex items-start gap-2.5 cursor-pointer">
                                                <span class="text-amber-500 text-xs shrink-0 mt-0.5">📍</span>
                                                <div class="min-w-0 flex-1">
                                                    <span class="font-extrabold block text-xs text-gray-900 dark:text-white truncate" x-text="item.display_name"></span>
                                                </div>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <!-- Destination Input ("Where to?") -->
                            <div class="relative">
                                <div class="absolute left-3.5 top-3.5 text-black dark:text-white font-black text-sm">□</div>
                                <input type="text" id="dropoff_location" name="dropoff_location" x-model="dropoff" required
                                       @input.debounce.300ms="searchDropoffLocation()"
                                       @focus="if(dropoffSuggestions.length > 0) showDropoffSuggestions = true"
                                       placeholder="Where to? (Search destination)" 
                                       class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 text-xs font-extrabold focus:outline-none focus:ring-2 focus:ring-black dark:focus:ring-white transition-all">

                                <!-- Dropoff Suggestions Dropdown -->
                                <div x-show="showDropoffSuggestions && dropoffSuggestions.length > 0" 
                                     @click.away="showDropoffSuggestions = false"
                                     style="display: none;"
                                     class="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl shadow-2xl z-[9999] max-h-52 overflow-y-auto divide-y divide-gray-100 dark:divide-white/5">
                                    <template x-for="item in dropoffSuggestions" :key="item.place_id || item.osm_id">
                                        <button type="button" @click="selectDropoffSuggestion(item)" class="w-full text-left px-3.5 py-2.5 hover:bg-gray-50 dark:hover:bg-[#222] transition-colors flex items-start gap-2.5 cursor-pointer">
                                            <span class="text-black dark:text-white text-xs shrink-0 mt-0.5">□</span>
                                            <div class="min-w-0 flex-1">
                                                <span class="font-extrabold block text-xs text-gray-900 dark:text-white truncate" x-text="item.display_name"></span>
                                            </div>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- + Add Stop Button & Quick Buttons -->
                        <div class="flex items-center justify-between pt-1">
                            <button type="button" @click="addStop()" class="text-xs font-extrabold text-black dark:text-white hover:underline flex items-center gap-1">
                                <span>+</span>
                                <span>Add stop</span>
                            </button>

                            <div class="flex items-center gap-2">
                                <!-- Home Button & Edit Icon -->
                                <div class="inline-flex items-center gap-1 bg-gray-100 dark:bg-[#222] p-1 rounded-xl border transition-all" :class="savedLocations.home ? 'border-emerald-300 dark:border-emerald-800 bg-emerald-50/50 dark:bg-emerald-950/30' : 'border-transparent'">
                                    <button type="button" 
                                            @click="useSavedLocation('home')" 
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-extrabold transition-colors"
                                            :class="savedLocations.home ? 'text-emerald-700 dark:text-emerald-300' : 'text-gray-700 dark:text-gray-300 hover:text-black dark:hover:text-white'">
                                        <span>🏠</span>
                                        <span x-text="savedLocations.home ? 'Home' : '+ Home'"></span>
                                    </button>
                                    <button type="button" 
                                            @click="openSavedLocationModal('home')" 
                                            class="p-1 text-gray-400 hover:text-amber-500 font-bold text-xs rounded-lg hover:bg-white dark:hover:bg-white/10 transition-colors cursor-pointer" 
                                            title="Edit Home Address">
                                        ✏️
                                    </button>
                                </div>

                                <!-- Office Button & Edit Icon -->
                                <div class="inline-flex items-center gap-1 bg-gray-100 dark:bg-[#222] p-1 rounded-xl border transition-all" :class="savedLocations.office ? 'border-emerald-300 dark:border-emerald-800 bg-emerald-50/50 dark:bg-emerald-950/30' : 'border-transparent'">
                                    <button type="button" 
                                            @click="useSavedLocation('office')" 
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-extrabold transition-colors"
                                            :class="savedLocations.office ? 'text-emerald-700 dark:text-emerald-300' : 'text-gray-700 dark:text-gray-300 hover:text-black dark:hover:text-white'">
                                        <span>🏢</span>
                                        <span x-text="savedLocations.office ? 'Office' : '+ Office'"></span>
                                    </button>
                                    <button type="button" 
                                            @click="openSavedLocationModal('office')" 
                                            class="p-1 text-gray-400 hover:text-amber-500 font-bold text-xs rounded-lg hover:bg-white dark:hover:bg-white/10 transition-colors cursor-pointer" 
                                            title="Edit Office Address">
                                        ✏️
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Mandatory Phone Number -->
                        <div class="relative pt-2 border-t border-gray-100 dark:border-white/10">
                            <input type="tel" name="phone_number" x-model="phone" required placeholder="Mobile Phone Number (Required) *" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 text-xs font-extrabold focus:outline-none focus:ring-2 focus:ring-black dark:focus:ring-white">
                        </div>

                        <!-- Primary CTA: Search Rides -->
                        <button type="button" @click="goToChooseRide()" :disabled="!pickup.trim() || !dropoff.trim()" class="w-full py-4 bg-black dark:bg-white text-white dark:text-black font-black text-base rounded-2xl shadow-xl hover:opacity-90 transition-all disabled:opacity-40 flex items-center justify-center gap-2 active:scale-[0.99]">
                            <span>Search Rides</span>
                            <span>→</span>
                        </button>
                    </div>

                    <!-- STEP 2: CHOOSE A RIDE (bookingStep === 'choose_ride') -->
                    <div x-show="bookingStep === 'choose_ride'" style="display: none;" class="w-full bg-white dark:bg-[#111] p-6 shadow-[0_8px_30px_rgb(0,0,0,0.08)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.3)] rounded-[24px] border border-gray-100 dark:border-white/10 space-y-5">
                        
                        <!-- Header with Back Button -->
                        <div class="flex items-center justify-between">
                            <button type="button" @click="bookingStep = 'find_trip'" class="px-3 py-1.5 bg-gray-100 dark:bg-white/10 hover:bg-gray-200 rounded-full text-xs font-extrabold text-gray-800 dark:text-gray-200 flex items-center gap-1 transition-all">
                                ← Back
                            </button>
                            <h2 class="text-base font-black text-gray-900 dark:text-white">Choose a ride</h2>
                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-1 rounded-full">✓ Route Ready</span>
                        </div>

                        <!-- Route & Itemized Fare Breakdown -->
                        <div class="bg-gray-50 dark:bg-[#1a1a1a] p-4 rounded-2xl border border-gray-200 dark:border-white/10 space-y-2 text-xs">
                            <div class="flex justify-between text-gray-500 dark:text-gray-400">
                                <span>Estimated Distance</span>
                                <span class="font-bold text-gray-900 dark:text-white" x-text="estimatedDistanceKm.toFixed(1) + ' km (' + estimatedDurationMin + ' mins)'"></span>
                            </div>
                            <div class="flex justify-between text-gray-500 dark:text-gray-400">
                                <span>Base & Distance Fare</span>
                                <span class="font-bold text-gray-900 dark:text-white" x-text="'$' + (fareBreakdown.base_fare + fareBreakdown.distance_fare).toFixed(2)"></span>
                            </div>
                            <div class="flex justify-between font-extrabold text-sm text-gray-900 dark:text-white pt-1 border-t border-gray-200 dark:border-white/10">
                                <span>Total Estimated Fare</span>
                                <span x-text="'$' + fareBreakdown.grand_total.toFixed(2)"></span>
                            </div>
                        </div>

                        <!-- Vehicle Categories -->
                        <div class="space-y-3 max-h-[380px] overflow-y-auto pr-1">
                            <template x-for="cat in categories" :key="cat.id">
                                <div @click="vehicle_type = cat.name; selectedFare = cat.fare_formatted" 
                                     :class="vehicle_type === cat.name ? 'border-black dark:border-white ring-2 ring-black dark:ring-white bg-gray-50 dark:bg-[#222]' : 'border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-[#222]'"
                                     class="flex items-center justify-between p-4 rounded-2xl border-[1.5px] cursor-pointer transition-all bg-white dark:bg-[#1a1a1a] shadow-sm">
                                    <div class="flex items-center gap-3.5">
                                        <div class="text-3xl shrink-0" x-text="cat.icon"></div>
                                        <div>
                                            <div class="flex items-center gap-2 mb-0.5">
                                                <h4 class="font-black text-gray-900 dark:text-white text-sm" x-text="cat.name"></h4>
                                                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300" x-text="cat.capacity"></span>
                                            </div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                                                Arrives in <span class="text-emerald-600 dark:text-emerald-400 font-extrabold" x-text="cat.eta_minutes + ' mins'"></span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0 ml-2">
                                        <div class="font-black text-lg text-gray-900 dark:text-white" x-text="cat.fare_formatted"></div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Payment Method Selector Pill -->
                        <div class="pt-2 border-t border-gray-100 dark:border-white/10 flex items-center justify-between">
                            <button type="button" @click="paymentModal = true; paymentStep = 'select';" class="w-full flex items-center justify-between p-3.5 bg-gray-50 dark:bg-[#1a1a1a] hover:bg-gray-100 dark:hover:bg-[#222] rounded-2xl border border-gray-200 dark:border-white/10 transition-all cursor-pointer">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl" x-text="paymentMethod === 'cash' ? '💵' : '💳'"></span>
                                    <div class="text-left">
                                        <div class="text-xs font-black text-gray-900 dark:text-white" 
                                             x-text="paymentMethod === 'cash' ? 'Cash on Arrival' : (selectedCard ? (selectedCard.brand_name + ' •••• ' + selectedCard.card_last4) : 'Visa •••• 4242')"></div>
                                        <div class="text-[10px] font-bold text-gray-500 dark:text-gray-400" 
                                             x-text="paymentMethod === 'cash' ? 'Pay directly to driver' : (selectedCard ? ('Exp ' + selectedCard.expiry_formatted) : 'Stripe Secured')"></div>
                                    </div>
                                </div>
                                <span class="text-gray-400 font-bold text-xs">></span>
                            </button>
                        </div>

                        <!-- Primary CTA: Continue to Confirm -->
                        <button type="button" @click="bookingStep = 'confirm_ride'" class="w-full py-4 bg-black dark:bg-white text-white dark:text-black font-black text-base rounded-2xl shadow-xl hover:opacity-90 transition-all active:scale-[0.99]">
                            Continue to Confirm →
                        </button>
                    </div>

                    <!-- STEP 3: CONFIRM RIDE (bookingStep === 'confirm_ride') -->
                    <div x-show="bookingStep === 'confirm_ride'" style="display: none;" class="w-full bg-white dark:bg-[#111] p-6 shadow-[0_8px_30px_rgb(0,0,0,0.08)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.3)] rounded-[24px] border border-gray-100 dark:border-white/10 space-y-5">
                        
                        <div class="flex items-center justify-between">
                            <button type="button" @click="bookingStep = 'choose_ride'" class="px-3 py-1.5 bg-gray-100 dark:bg-white/10 hover:bg-gray-200 rounded-full text-xs font-extrabold text-gray-800 dark:text-gray-200 flex items-center gap-1 transition-all">
                                ← Back
                            </button>
                            <h2 class="text-base font-black text-gray-900 dark:text-white">Confirm your ride</h2>
                            <span class="text-xs font-bold text-gray-400">Step 3 of 3</span>
                        </div>

                        <!-- Summary Details -->
                        <div class="bg-gray-50 dark:bg-[#1a1a1a] p-5 rounded-2xl border border-gray-200 dark:border-white/10 space-y-4">
                            <!-- Pickup -->
                            <div>
                                <span class="text-[10px] font-black uppercase text-gray-400 tracking-wider">Pickup</span>
                                <p class="text-xs font-black text-gray-900 dark:text-white" x-text="pickup"></p>
                            </div>
                            
                            <!-- Destination -->
                            <div>
                                <span class="text-[10px] font-black uppercase text-gray-400 tracking-wider">Destination</span>
                                <p class="text-xs font-black text-gray-900 dark:text-white" x-text="dropoff"></p>
                            </div>

                            <!-- Selected Vehicle & Fare -->
                            <div class="flex items-center justify-between pt-3 border-t border-gray-200 dark:border-white/10">
                                <div>
                                    <span class="text-[10px] font-black uppercase text-gray-400 tracking-wider">Vehicle</span>
                                    <p class="text-xs font-black text-gray-900 dark:text-white" x-text="vehicle_type"></p>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] font-black uppercase text-gray-400 tracking-wider">Total Fare</span>
                                    <p class="text-base font-black text-emerald-600 dark:text-emerald-400" x-text="getFormattedFare()"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Final CTA Button: Confirm Ride -->
                        <button type="submit" class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-lg rounded-2xl shadow-xl transition-all active:scale-[0.99]">
                            Confirm Ride →
                        </button>
                    </div>

                    <!-- STEP 4: FINDING DRIVER (bookingStep === 'finding_driver') -->
                    <div x-show="bookingStep === 'finding_driver'" style="display: none;" class="w-full bg-white dark:bg-[#111] p-6 shadow-[0_8px_30px_rgb(0,0,0,0.08)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.3)] rounded-[24px] border border-gray-100 dark:border-white/10 space-y-6 text-center">
                        
                        <div class="relative w-24 h-24 mx-auto flex items-center justify-center">
                            <div class="absolute inset-0 rounded-full bg-indigo-500/20 animate-pulse-radar"></div>
                            <div class="w-16 h-16 rounded-full bg-black dark:bg-white text-white dark:text-black font-black text-2xl flex items-center justify-center shadow-xl">
                                🚗
                            </div>
                        </div>

                        <div>
                            <h2 class="text-xl font-black text-gray-900 dark:text-white">Finding your driver...</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Connecting with top-rated nearby drivers</p>
                        </div>

                        <button type="button" @click="cancelRide()" class="w-full py-3.5 bg-gray-100 dark:bg-[#222] hover:bg-gray-200 text-rose-600 font-extrabold text-sm rounded-2xl transition-all">
                            Cancel Request
                        </button>
                    </div>

                    <!-- STEP 5-8: DRIVER MATCHED & TRIP PROGRESS -->
                    <div x-show="['driver_assigned', 'en_route', 'arrived', 'in_progress'].includes(bookingStep)" style="display: none;" class="w-full bg-white dark:bg-[#111] p-6 shadow-[0_8px_30px_rgb(0,0,0,0.08)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.3)] rounded-[24px] border border-gray-100 dark:border-white/10 space-y-5">
                        
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-3 py-1 rounded-full">✓ Driver Matched</span>
                            <span class="text-xs font-bold text-gray-400" x-text="driverPlate || 'ABC-1234'"></span>
                        </div>

                        <!-- Driver Info Card -->
                        <div class="flex items-center gap-4 bg-gray-50 dark:bg-[#1a1a1a] p-4 rounded-2xl border border-gray-200 dark:border-white/10">
                            <div class="w-14 h-14 rounded-full bg-black text-white dark:bg-white dark:text-black font-black text-xl flex items-center justify-center shrink-0 shadow-md">
                                👨‍✈️
                            </div>
                            <div class="flex-1">
                                <h3 class="font-black text-base text-gray-900 dark:text-white" x-text="driverName || 'Michael Vance'"></h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold" x-text="(driverModel || 'Toyota Camry') + ' • ⭐ 4.9'"></p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="grid grid-cols-2 gap-3">
                            <button type="button" @click="showContactModal = true" class="py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs rounded-xl shadow-md transition-all">📞 Call Driver</button>
                            <button type="button" @click="showContactModal = true" class="py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs rounded-xl shadow-md transition-all">💬 Message</button>
                        </div>

                        <button type="button" @click="cancelRide()" class="w-full py-3 text-rose-600 font-extrabold text-xs hover:underline">Cancel Ride</button>
                    </div>

                    <!-- STEP 9: RIDE COMPLETED -->
                    <div x-show="bookingStep === 'completed'" style="display: none;" class="w-full bg-white dark:bg-[#111] p-6 shadow-[0_8px_30px_rgb(0,0,0,0.08)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.3)] rounded-[24px] border border-gray-100 dark:border-white/10 space-y-5 text-center">
                        <div class="text-4xl">🎉</div>
                        <h2 class="text-2xl font-black text-gray-900 dark:text-white">Ride Completed!</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Thank you for riding with RideMyCars</p>

                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="showReviewModal = true" class="flex-1 py-3.5 bg-black dark:bg-white text-white dark:text-black font-black text-sm rounded-2xl shadow-xl">⭐ Rate Driver</button>
                            <button type="button" @click="bookingStep = 'find_trip'; isConfirming = false;" class="px-5 py-3.5 bg-gray-100 dark:bg-[#222] font-black text-sm rounded-2xl">Done</button>
                        </div>
                    </div>

                </div>

                <!-- RIGHT SIDE: LARGE LIVE INTERACTIVE MAP (~60% width) -->
                <div class="w-full md:flex-1 h-[550px] md:h-[680px] lg:h-[720px] bg-gray-100 dark:bg-[#181818] rounded-[24px] border border-gray-200 dark:border-white/10 overflow-hidden relative shadow-md md:sticky md:top-24">
                    <div id="map" style="width: 100%; height: 100%; min-height: 450px; display: block;" class="relative z-0"></div>
                </div>

            </div>
        </form>

        <!-- Payment Method Modal -->
        <div x-show="paymentModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto">
            <div @click.away="paymentModal = false" class="bg-white dark:bg-[#1a1a1a] rounded-3xl w-full max-w-md max-h-[90vh] flex flex-col shadow-2xl border border-gray-200 dark:border-white/10 overflow-hidden my-auto">
                <div class="p-4 flex items-center justify-between border-b border-gray-100 dark:border-white/10 shrink-0">
                    <template x-if="paymentStep === 'select'">
                        <button type="button" @click="paymentModal = false" class="p-2 hover:bg-gray-100 dark:hover:bg-[#333] rounded-full text-gray-500">✕</button>
                    </template>
                    <template x-if="paymentStep !== 'select'">
                        <button type="button" @click="paymentStep = 'select'" class="px-2.5 py-1 bg-gray-100 dark:bg-white/10 rounded-full text-xs font-bold">← Back</button>
                    </template>
                    <h2 class="text-base font-extrabold text-gray-900 dark:text-white" x-text="paymentStep === 'select' ? 'Payment method' : 'Add Card'"></h2>
                    <span class="text-[11px] font-bold text-gray-400">🔒 SSL Encrypted</span>
                </div>
                <div class="p-5 overflow-y-auto flex-1 space-y-4">
                    <div x-show="paymentStep === 'select'" class="space-y-4">
                        <!-- Option 1: Cash on Arrival -->
                        <div @click="paymentMethod = 'cash'; selectedCard = null;" 
                             :class="paymentMethod === 'cash' ? 'border-black dark:border-white ring-2 ring-black dark:ring-white bg-gray-50 dark:bg-[#222]' : 'border-gray-200 dark:border-white/10 bg-white dark:bg-[#1a1a1a] hover:bg-gray-50 dark:hover:bg-[#222]'"
                             class="p-4 rounded-2xl border flex items-center justify-between cursor-pointer transition-all shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-300 font-bold text-xl flex items-center justify-center">
                                    💵
                                </div>
                                <div>
                                    <h4 class="font-black text-sm text-gray-900 dark:text-white">Cash on Arrival</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Pay the driver directly at your destination</p>
                                </div>
                            </div>
                            <span x-show="paymentMethod === 'cash'" class="text-emerald-500 font-extrabold text-sm">✓</span>
                        </div>

                        <!-- Option 2: Credit / Debit Cards Header & List -->
                        <div class="pt-2 border-t border-gray-100 dark:border-white/10 space-y-2.5">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black uppercase text-gray-400 tracking-wider">Credit / Debit Cards</span>
                                <span class="text-[11px] font-bold text-gray-400">Stripe Secured</span>
                            </div>

                            <template x-for="card in savedCards" :key="card.id">
                                <div @click="selectSavedCard(card)" 
                                     :class="paymentMethod === 'stripe' && selectedCard && selectedCard.id === card.id ? 'border-black dark:border-white ring-2 ring-black dark:ring-white bg-gray-50 dark:bg-[#222]' : 'border-gray-200 dark:border-white/10 bg-white dark:bg-[#1a1a1a] hover:bg-gray-50 dark:hover:bg-[#222]'"
                                     class="p-3.5 rounded-2xl border flex items-center justify-between cursor-pointer transition-all shadow-sm">
                                    <div class="flex items-center gap-3">
                                        <span class="text-2xl">💳</span>
                                        <div>
                                            <div class="font-black text-sm text-gray-900 dark:text-white flex items-center gap-2">
                                                <span x-text="card.brand_name + ' •••• ' + card.card_last4"></span>
                                                <template x-if="card.is_default">
                                                    <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300">Default</span>
                                                </template>
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium" x-text="'Expires ' + card.expiry_formatted"></div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2" @click.stop>
                                        <span x-show="paymentMethod === 'stripe' && selectedCard && selectedCard.id === card.id" class="text-emerald-500 font-extrabold text-sm mr-1">✓</span>
                                        <button type="button" 
                                                @click.stop="removeSavedCard(card.id)" 
                                                class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition-all font-bold text-xs flex items-center gap-1 cursor-pointer"
                                                title="Remove Card">
                                            <span>🗑️</span>
                                            <span class="text-[11px] font-extrabold">Remove</span>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <template x-if="savedCards.length === 0">
                                <div @click="paymentMethod = 'stripe'; selectedCard = { brand_name: 'Visa', card_last4: '4242', expiry_formatted: '08/29' };" 
                                     :class="paymentMethod === 'stripe' ? 'border-black dark:border-white ring-2 ring-black dark:ring-white bg-gray-50 dark:bg-[#222]' : 'border-gray-200 dark:border-white/10 bg-white dark:bg-[#1a1a1a] hover:bg-gray-50 dark:hover:bg-[#222]'"
                                     class="p-3.5 rounded-2xl border flex items-center justify-between cursor-pointer transition-all">
                                    <div class="flex items-center gap-3">
                                        <span class="text-xl">💳</span>
                                        <div>
                                            <div class="font-black text-sm text-gray-900 dark:text-white">Visa •••• 4242</div>
                                            <div class="text-xs text-gray-500">Expires 08/29</div>
                                        </div>
                                    </div>
                                    <span x-show="paymentMethod === 'stripe'" class="text-emerald-500 font-bold">✓</span>
                                </div>
                            </template>

                            <!-- + Add Payment Method Button -->
                            <button type="button" @click="paymentStep = 'card_form'" class="w-full py-3 px-4 bg-gray-100 hover:bg-gray-200 dark:bg-[#222] dark:hover:bg-[#333] font-extrabold text-xs text-gray-900 dark:text-white rounded-2xl flex items-center justify-between transition-all cursor-pointer">
                                <span class="flex items-center gap-2">
                                    <span>+</span>
                                    <span>Add new card</span>
                                </span>
                                <span class="text-gray-400 font-bold">></span>
                            </button>
                        </div>
                    </div>
                    <div x-show="paymentStep === 'card_form'" class="space-y-4">
                        <x-stripe-card-input modelName="paymentMethod" value="stripe" />
                    </div>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-[#111] border-t border-gray-100 dark:border-white/10 shrink-0">
                    <template x-if="paymentStep === 'card_form'">
                        <button type="button" 
                                @click="paymentMethod = 'stripe'; paymentStep = 'select'; paymentModal = false; saveCardFromForm();" 
                                class="w-full py-4 bg-black dark:bg-white text-white dark:text-black font-black text-base rounded-2xl shadow-xl hover:opacity-90 transition-all flex items-center justify-center gap-2 active:scale-[0.99] cursor-pointer">
                            <span>Add Card</span>
                        </button>
                    </template>

                    <template x-if="paymentStep !== 'card_form'">
                        <button type="button" 
                                @click="paymentModal = false; paymentStep = 'select';" 
                                class="w-full py-4 bg-black dark:bg-white text-white dark:text-black font-black text-base rounded-2xl shadow-xl hover:opacity-90 transition-all active:scale-[0.99] cursor-pointer">
                            Done
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <!-- Saved Location Setup / Edit Modal (Teleported to Body) -->
        <template x-teleport="body">
            <div x-show="showSavedLocationModal" 
                 x-transition.opacity
                 style="display: none;"
                 class="fixed inset-0 z-[9999999] flex items-center justify-center p-4 bg-black/70 backdrop-blur-md">
                <div @click.outside="showSavedLocationModal = false" class="bg-white dark:bg-[#181818] rounded-3xl border border-gray-200 dark:border-white/10 shadow-2xl max-w-md w-full p-6 space-y-4 relative z-[99999999]">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-white/10 pb-3">
                        <h3 class="font-extrabold text-base text-gray-900 dark:text-white flex items-center gap-2">
                            <span x-text="editingLabel === 'home' ? '🏠' : '🏢'"></span>
                            <span x-text="(savedLocations[editingLabel] ? 'Edit ' : 'Set ') + (editingLabel === 'home' ? 'Home' : 'Office') + ' Address'"></span>
                        </h3>
                        <button type="button" @click="showSavedLocationModal = false" class="p-1 text-gray-400 hover:text-gray-600 font-bold text-sm">✕</button>
                    </div>

                    <p class="text-xs text-gray-500 dark:text-gray-400">Search and select your <span x-text="editingLabel"></span> address. It will be saved to your profile for 1-tap booking.</p>

                    <div class="relative space-y-2">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Address Search</label>
                        <div class="relative">
                            <input type="text" 
                                   x-model="modalTempLocation.location"
                                   @input.debounce.300ms="searchModalLocation()"
                                   @focus="if(modalSuggestions.length > 0) showModalSuggestions = true"
                                   placeholder="Search address, city, landmark..." 
                                   class="w-full pl-9 pr-8 py-3 bg-gray-50 dark:bg-[#222] border rounded-xl text-xs font-bold text-gray-900 dark:text-white transition-colors"
                                   :class="modalTempLocation.isSelected ? 'border-emerald-500 ring-1 ring-emerald-500/30' : 'border-gray-200 dark:border-white/10'">
                            <span class="absolute left-3 top-3.5 text-amber-500">📍</span>
                            <span x-show="modalTempLocation.isSelected" class="absolute right-3 top-3.5 text-emerald-500 font-bold text-xs" title="Verified Location">✓</span>
                        </div>

                        <!-- Suggestions Dropdown -->
                        <div x-show="showModalSuggestions && modalSuggestions.length > 0" 
                             @click.away="showModalSuggestions = false"
                             class="bg-white dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl shadow-xl max-h-48 overflow-y-auto divide-y divide-gray-100 dark:divide-white/5">
                            <template x-for="item in modalSuggestions" :key="item.place_id || item.osm_id">
                                <button type="button" @click="selectModalSuggestion(item)" class="w-full text-left px-3.5 py-2.5 hover:bg-gray-50 dark:hover:bg-[#222] transition-colors flex items-start gap-2.5">
                                    <span class="text-amber-500 text-xs shrink-0 mt-0.5">📍</span>
                                    <div class="min-w-0 flex-1">
                                        <span class="font-bold block text-xs truncate" x-text="item.display_name"></span>
                                    </div>
                                </button>
                            </template>
                        </div>

                        <!-- Verified Badge -->
                        <div x-show="modalTempLocation.isSelected" class="flex items-center gap-1.5 p-2.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/40 rounded-xl text-xs font-extrabold text-emerald-700 dark:text-emerald-300">
                            <span class="text-emerald-500 shrink-0">✓ Verified:</span>
                            <span class="truncate" x-text="modalTempLocation.location"></span>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-gray-100 dark:border-white/10">
                        <button type="button" @click="showSavedLocationModal = false" class="px-4 py-2.5 rounded-xl bg-gray-100 dark:bg-[#222] text-gray-700 dark:text-gray-300 font-bold text-xs">Cancel</button>
                        <button type="button" @click="saveLocationFromModal()" :disabled="!modalTempLocation.isSelected" class="px-5 py-2.5 rounded-xl bg-black dark:bg-white text-white dark:text-black hover:opacity-90 disabled:opacity-40 font-extrabold text-xs shadow-md transition-all cursor-pointer">Save & Apply</button>
                    </div>
                </div>
            </div>
        </template>
    </main>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('rideBooking', () => ({
                bookingStep: 'find_trip', // 'find_trip', 'choose_ride', 'confirm_ride', 'finding_driver', 'driver_assigned', 'completed'
                vehicle_type: 'Economy',
                schedule_type: 'now',
                pickup: '',
                pickupLat: null,
                pickupLng: null,
                pickupSuggestions: [],
                showPickupSuggestions: false,

                dropoff: '',
                dropoffLat: null,
                dropoffLng: null,
                dropoffSuggestions: [],
                showDropoffSuggestions: false,

                phone: '{{ auth()->user()->phone ?? "" }}',
                stops: [],
                estimatedDistanceKm: 10.0,
                estimatedDurationMin: 15,
                categories: [
                    { id: 'economy', name: 'Economy', icon: '🚗', capacity: '1–4 seats', eta_minutes: 3, fare_formatted: '$28.50', description: 'Affordable everyday rides' },
                    { id: 'standard', name: 'Standard', icon: '🚘', capacity: '1–4 seats', eta_minutes: 4, fare_formatted: '$34.20', description: 'Comfortable sedans' },
                    { id: 'suv', name: 'SUV', icon: '🚙', capacity: '1–6 seats', eta_minutes: 6, fare_formatted: '$42.75', description: 'Spacious SUVs' },
                    { id: 'xl', name: 'XL', icon: '🚐', capacity: '1–6 seats', eta_minutes: 7, fare_formatted: '$51.30', description: 'Large vans for groups' },
                    { id: 'luxury', name: 'Luxury', icon: '🏎️', capacity: '1–4 seats', eta_minutes: 5, fare_formatted: '$62.70', description: 'Premium luxury vehicles' },
                ],
                fareBreakdown: { base_fare: 5.00, distance_fare: 15.00, stops_fee: 0.00, tax: 1.19, grand_total: 29.69 },
                paymentModal: false,
                paymentStep: 'select',
                paymentMethod: 'stripe',
                riderType: 'me',
                savedCards: [],
                selectedCard: null,
                selectedFare: '$28.50',
                isSavingCard: false,
                savedLocations: { home: null, office: null },
                showSavedLocationModal: false,
                editingLabel: 'home',
                modalTempLocation: { location: '', lat: null, lng: null, place_id: null, isSelected: false },
                modalSuggestions: [],
                showModalSuggestions: false,

                init() {
                    this.fetchSavedLocations();
                    this.fetchSavedCards();
                },

                async searchPickupLocation() {
                    this.pickupLat = null;
                    this.pickupLng = null;
                    if (!this.pickup || this.pickup.trim().length < 3) {
                        this.pickupSuggestions = [];
                        this.showPickupSuggestions = false;
                        return;
                    }
                    try {
                        const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.pickup)}&limit=5`);
                        if (res.ok) {
                            this.pickupSuggestions = await res.json();
                            this.showPickupSuggestions = this.pickupSuggestions.length > 0;
                        }
                    } catch (e) {
                        console.warn('Pickup search error:', e);
                    }
                },

                selectPickupSuggestion(item) {
                    this.pickup = item.display_name;
                    this.pickupLat = parseFloat(item.lat);
                    this.pickupLng = parseFloat(item.lon);
                    this.showPickupSuggestions = false;
                    this.updateMapRoute();
                },

                async searchDropoffLocation() {
                    this.dropoffLat = null;
                    this.dropoffLng = null;
                    if (!this.dropoff || this.dropoff.trim().length < 3) {
                        this.dropoffSuggestions = [];
                        this.showDropoffSuggestions = false;
                        return;
                    }
                    try {
                        const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.dropoff)}&limit=5`);
                        if (res.ok) {
                            this.dropoffSuggestions = await res.json();
                            this.showDropoffSuggestions = this.dropoffSuggestions.length > 0;
                        }
                    } catch (e) {
                        console.warn('Dropoff search error:', e);
                    }
                },

                selectDropoffSuggestion(item) {
                    this.dropoff = item.display_name;
                    this.dropoffLat = parseFloat(item.lat);
                    this.dropoffLng = parseFloat(item.lon);
                    this.showDropoffSuggestions = false;
                    this.updateMapRoute();
                },

                async searchStopLocation(stop) {
                    stop.isSelected = false;
                    stop.lat = null;
                    stop.lng = null;
                    if (!stop.location || stop.location.trim().length < 3) {
                        stop.suggestions = [];
                        stop.showSuggestions = false;
                        return;
                    }
                    try {
                        const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(stop.location)}&limit=5`);
                        if (res.ok) {
                            stop.suggestions = await res.json();
                            stop.showSuggestions = stop.suggestions.length > 0;
                        }
                    } catch (e) {
                        console.warn('Stop search error:', e);
                    }
                },

                selectStopSuggestion(stop, item) {
                    stop.location = item.display_name;
                    stop.lat = parseFloat(item.lat);
                    stop.lng = parseFloat(item.lon);
                    stop.isSelected = true;
                    stop.showSuggestions = false;
                    this.updateMapRoute();
                },

                addStop() {
                    if (this.stops.length < 5) {
                        this.stops.push({
                            id: Date.now() + Math.random(),
                            location: '',
                            lat: null,
                            lng: null,
                            isSelected: false,
                            suggestions: [],
                            showSuggestions: false
                        });
                    }
                },

                removeStop(idx) {
                    this.stops.splice(idx, 1);
                    this.updateMapRoute();
                },

                updateMapRoute() {
                    const waypoints = [];
                    if (this.pickupLat && this.pickupLng) {
                        waypoints.push({ label: 'Pickup', lat: this.pickupLat, lng: this.pickupLng, type: 'pickup' });
                    }
                    this.stops.forEach((s, i) => {
                        if (s.lat && s.lng) {
                            waypoints.push({ label: `Stop ${i + 1}`, lat: s.lat, lng: s.lng, type: 'stop' });
                        }
                    });
                    if (this.dropoffLat && this.dropoffLng) {
                        waypoints.push({ label: 'Destination', lat: this.dropoffLat, lng: this.dropoffLng, type: 'dropoff' });
                    }

                    window.dispatchEvent(new CustomEvent('update-ride-route', { detail: { waypoints } }));

                    let totalDist = 0;
                    for (let i = 0; i < waypoints.length - 1; i++) {
                        totalDist += this.calculateDistanceKm(waypoints[i].lat, waypoints[i].lng, waypoints[i+1].lat, waypoints[i+1].lng);
                    }

                    if (totalDist > 0) {
                        this.estimatedDistanceKm = totalDist;
                        this.estimatedDurationMin = Math.round((totalDist / 30) * 60) + (this.stops.length * 5);
                        
                        const stopsFee = this.stops.length * 3.50;
                        const baseFare = 5.00;
                        const distFare = totalDist * 1.50;
                        const grandTotal = baseFare + distFare + stopsFee;

                        this.fareBreakdown = {
                            base_fare: baseFare,
                            distance_fare: distFare,
                            stops_fee: stopsFee,
                            tax: grandTotal * 0.05,
                            grand_total: grandTotal
                        };

                        const baseMults = { economy: 1.0, standard: 1.2, suv: 1.5, xl: 1.8, luxury: 2.2 };
                        this.categories.forEach(cat => {
                            const mult = baseMults[cat.id] || 1.0;
                            const fare = (grandTotal * mult).toFixed(2);
                            cat.fare_formatted = '$' + fare;
                        });
                        this.selectedFare = this.categories.find(c => c.name === this.vehicle_type)?.fare_formatted || '$' + grandTotal.toFixed(2);
                    }
                },

                calculateDistanceKm(lat1, lon1, lat2, lon2) {
                    const R = 6371;
                    const dLat = (lat2 - lat1) * Math.PI / 180;
                    const dLon = (lon2 - lon1) * Math.PI / 180;
                    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                              Math.sin(dLon/2) * Math.sin(dLon/2);
                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                    return R * c;
                },

                async fetchSavedCards() {
                    try {
                        const res = await fetch('/api/payment-methods');
                        if (res.ok) {
                            const data = await res.json();
                            if (data.success && Array.isArray(data.payment_methods)) {
                                this.savedCards = data.payment_methods;
                                if (this.savedCards.length > 0 && !this.selectedCard) {
                                    this.selectedCard = this.savedCards.find(c => c.is_default) || this.savedCards[0];
                                }
                            }
                        }
                    } catch (e) {
                        console.warn('Error fetching saved cards:', e);
                    }
                },

                selectSavedCard(card) {
                    this.selectedCard = card;
                    this.paymentMethod = 'stripe';
                },

                removeSavedCard(cardId) {
                    // Instant optimistic UI removal (0ms delay)
                    this.savedCards = (this.savedCards || []).filter(c => c && c.id !== cardId);
                    if (this.selectedCard && this.selectedCard.id === cardId) {
                        this.selectedCard = this.savedCards.length > 0 ? this.savedCards[0] : null;
                    }

                    // Background API deletion
                    fetch(`/api/payment-methods/${cardId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    }).catch(e => console.warn('Background card removal notice:', e));
                },

                async fetchSavedLocations() {
                    try {
                        const res = await fetch('/api/user/saved-locations');
                        if (res.ok) {
                            const data = await res.json();
                            if (data.success && data.locations) {
                                this.savedLocations.home = data.locations.home || null;
                                this.savedLocations.office = data.locations.office || null;
                            }
                        }
                    } catch (e) {
                        console.warn('Error fetching saved locations:', e);
                    }
                },

                useSavedLocation(label) {
                    const saved = this.savedLocations[label];
                    if (saved && saved.address) {
                        if (!this.pickup) {
                            this.pickup = saved.address;
                            if (saved.latitude && saved.longitude) {
                                this.pickupLat = parseFloat(saved.latitude);
                                this.pickupLng = parseFloat(saved.longitude);
                                this.updateMapRoute();
                            }
                        } else {
                            this.dropoff = saved.address;
                            if (saved.latitude && saved.longitude) {
                                this.dropoffLat = parseFloat(saved.latitude);
                                this.dropoffLng = parseFloat(saved.longitude);
                                this.updateMapRoute();
                            }
                        }
                    } else {
                        this.openSavedLocationModal(label);
                    }
                },

                openSavedLocationModal(label) {
                    this.editingLabel = label;
                    const existing = this.savedLocations[label];
                    if (existing) {
                        this.modalTempLocation = {
                            location: existing.address || '',
                            lat: existing.latitude ? parseFloat(existing.latitude) : null,
                            lng: existing.longitude ? parseFloat(existing.longitude) : null,
                            place_id: existing.place_id || null,
                            isSelected: true
                        };
                    } else {
                        this.modalTempLocation = { location: '', lat: null, lng: null, place_id: null, isSelected: false };
                    }
                    this.modalSuggestions = [];
                    this.showModalSuggestions = false;
                    this.showSavedLocationModal = true;
                },

                async searchModalLocation() {
                    this.modalTempLocation.isSelected = false;
                    if (!this.modalTempLocation.location || this.modalTempLocation.location.trim().length < 3) {
                        this.modalSuggestions = [];
                        this.showModalSuggestions = false;
                        return;
                    }
                    try {
                        const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.modalTempLocation.location)}&limit=5`);
                        if (res.ok) {
                            const data = await res.json();
                            this.modalSuggestions = data;
                            this.showModalSuggestions = data.length > 0;
                        }
                    } catch (e) {
                        console.warn('Modal map search error:', e);
                    }
                },

                selectModalSuggestion(item) {
                    this.modalTempLocation.location = item.display_name;
                    this.modalTempLocation.lat = parseFloat(item.lat);
                    this.modalTempLocation.lng = parseFloat(item.lon);
                    this.modalTempLocation.place_id = item.place_id ? String(item.place_id) : null;
                    this.modalTempLocation.isSelected = true;
                    this.showModalSuggestions = false;
                },

                async saveLocationFromModal() {
                    if (!this.modalTempLocation.location || !this.modalTempLocation.isSelected) {
                        alert('Please select a valid location suggestion from the dropdown.');
                        return;
                    }
                    try {
                        const csrfToken = '{{ csrf_token() }}';
                        const res = await fetch('/api/user/saved-locations', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                label: this.editingLabel,
                                address: this.modalTempLocation.location,
                                latitude: this.modalTempLocation.lat,
                                longitude: this.modalTempLocation.lng,
                                place_id: this.modalTempLocation.place_id
                            })
                        });
                        if (res.ok) {
                            const data = await res.json();
                            if (data.success && data.location) {
                                this.savedLocations[this.editingLabel] = data.location;
                                this.showSavedLocationModal = false;
                                if (!this.pickup) {
                                    this.pickup = data.location.address;
                                    this.pickupLat = data.location.latitude ? parseFloat(data.location.latitude) : null;
                                    this.pickupLng = data.location.longitude ? parseFloat(data.location.longitude) : null;
                                    this.updateMapRoute();
                                } else {
                                    this.dropoff = data.location.address;
                                    this.dropoffLat = data.location.latitude ? parseFloat(data.location.latitude) : null;
                                    this.dropoffLng = data.location.longitude ? parseFloat(data.location.longitude) : null;
                                    this.updateMapRoute();
                                }
                            }
                        }
                    } catch (e) {
                        console.error('Error saving location:', e);
                    }
                },

                saveCardFromForm() {
                    const cardNum = document.getElementById('stripe_card_number_input')?.value || '4242 4242 4242 4242';
                    const cardHolder = document.getElementById('stripe_cardholder_name_input')?.value || '{{ auth()->user()->name ?? "Valued Customer" }}';
                    const cardExpiry = document.getElementById('stripe_card_expiry_input')?.value || '12/29';

                    const cleanNum = cardNum.replace(/\s+/g, '');
                    const last4 = cleanNum.slice(-4) || '4242';
                    let brand = 'visa';
                    if (/^4/.test(cleanNum)) brand = 'visa';
                    else if (/^(5[1-5]|2[2-7])/.test(cleanNum)) brand = 'mastercard';
                    else if (/^3[47]/.test(cleanNum)) brand = 'amex';
                    else if (/^(6011|65|64[4-9])/.test(cleanNum)) brand = 'discover';

                    const parts = cardExpiry.split('/');
                    const month = parseInt(parts[0], 10) || 12;
                    const year = 2000 + (parseInt(parts[1], 10) || 29);

                    // Instant UI update & modal close (0ms delay)
                    this.paymentMethod = 'stripe';
                    this.paymentStep = 'select';
                    this.paymentModal = false;
                    this.isSavingCard = false;

                    // Save tokenized card preference in background
                    fetch('/api/payment-methods/save-stripe', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            card_brand: brand,
                            card_last4: last4,
                            expiry_month: month,
                            expiry_year: year,
                            cardholder_name: cardHolder,
                            set_default: true
                        })
                    }).then(r => r.json()).then(data => {
                        if (data && data.success && data.payment_method) {
                            if (Array.isArray(this.savedCards)) {
                                this.savedCards.unshift(data.payment_method);
                            }
                            this.selectedCard = data.payment_method;
                        }
                    }).catch(e => console.warn('Background card save notice:', e));
                },

                getFormattedFare() {
                    if (this.selectedFare && this.selectedFare !== '') return this.selectedFare;
                    if (this.fareBreakdown && this.fareBreakdown.grand_total) {
                        return '$' + parseFloat(this.fareBreakdown.grand_total).toFixed(2);
                    }
                    return '$28.50';
                },

                goToChooseRide() {
                    if (this.pickup.trim() && this.dropoff.trim()) {
                        this.bookingStep = 'choose_ride';
                    }
                },
                async submitBooking() {
                    this.bookingStep = 'finding_driver';
                    try {
                        const csrfToken = document.querySelector('input[name="_token"]').value;
                        const res = await fetch('/ride/book', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                            body: JSON.stringify({ 
                                pickup_location: this.pickup, 
                                pickup_lat: this.pickupLat,
                                pickup_lng: this.pickupLng,
                                dropoff_location: this.dropoff, 
                                dropoff_lat: this.dropoffLat,
                                dropoff_lng: this.dropoffLng,
                                stops: this.stops.map(s => ({ location: s.location, lat: s.lat, lng: s.lng })),
                                vehicle_type: this.vehicle_type, 
                                payment_method: this.paymentMethod,
                                amount: parseFloat(this.selectedFare.replace('$', '')) || this.fareBreakdown.grand_total
                            })
                        });
                        if (res.ok) {
                            setTimeout(() => {
                                this.bookingStep = 'driver_assigned';
                            }, 4000);
                        }
                    } catch (e) {
                        this.bookingStep = 'choose_ride';
                    }
                },
                cancelRide() {
                    this.bookingStep = 'find_trip';
                }
            }));
        });

        document.addEventListener("DOMContentLoaded", function() {
            const mapEl = document.getElementById('map');
            if (mapEl) {
                // Initialize Leaflet map with OpenStreetMap tiles
                const map = L.map('map', {
                    center: [28.6139, 77.2090], // Default center
                    zoom: 13,
                    zoomControl: true
                });

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                }).addTo(map);

                // Nearby driver markers
                const carIcon = L.divIcon({
                    html: '<div class="w-8 h-8 rounded-full bg-black text-white dark:bg-white dark:text-black font-black text-sm flex items-center justify-center shadow-lg border-2 border-white dark:border-black">🚗</div>',
                    className: 'car-marker-icon',
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                });

                const centerLat = 28.6139;
                const centerLng = 77.2090;
                for (let i = 0; i < 5; i++) {
                    const offsetLat = (Math.random() - 0.5) * 0.02;
                    const offsetLng = (Math.random() - 0.5) * 0.02;
                    L.marker([centerLat + offsetLat, centerLng + offsetLng], { icon: carIcon }).addTo(map);
                }

                // Global event listener for updating route & waypoints on map
                window.addEventListener('update-ride-route', function(e) {
                    const waypoints = e.detail.waypoints || [];
                    if (!map || waypoints.length === 0) return;

                    if (window.rideRoutePolyline) map.removeLayer(window.rideRoutePolyline);
                    if (window.rideWaypointsMarkers) {
                        window.rideWaypointsMarkers.forEach(m => map.removeLayer(m));
                    }
                    window.rideWaypointsMarkers = [];

                    const latLngs = [];

                    waypoints.forEach((wp, idx) => {
                        latLngs.push([wp.lat, wp.lng]);

                        let iconHtml = '●';
                        let bgClass = 'bg-emerald-500 text-white';
                        if (wp.type === 'stop') {
                            iconHtml = `Stop ${idx}`;
                            bgClass = 'bg-amber-500 text-white font-black';
                        } else if (wp.type === 'dropoff') {
                            iconHtml = '■';
                            bgClass = 'bg-black text-white dark:bg-white dark:text-black';
                        }

                        const icon = L.divIcon({
                            html: `<div class="px-2.5 py-1 rounded-full ${bgClass} text-xs font-bold shadow-lg border-2 border-white flex items-center justify-center gap-1">${wp.label}</div>`,
                            className: 'waypoint-marker-icon',
                            iconSize: [110, 28],
                            iconAnchor: [55, 14]
                        });

                        const marker = L.marker([wp.lat, wp.lng], { icon }).addTo(map).bindPopup(`<b>${wp.label}</b>`);
                        window.rideWaypointsMarkers.push(marker);
                    });

                    if (latLngs.length >= 2) {
                        window.rideRoutePolyline = L.polyline(latLngs, {
                            color: '#10b981',
                            weight: 5,
                            opacity: 0.8,
                            dashArray: '8, 8'
                        }).addTo(map);

                        map.fitBounds(window.rideRoutePolyline.getBounds(), { padding: [50, 50] });
                    } else if (latLngs.length === 1) {
                        map.setView(latLngs[0], 14);
                    }
                });

                // Geolocation detection
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition((pos) => {
                        const lat = pos.coords.latitude;
                        const lng = pos.coords.longitude;
                        map.setView([lat, lng], 14);

                        const userIcon = L.divIcon({
                            html: '<div class="w-4 h-4 rounded-full bg-emerald-500 ring-4 ring-emerald-200 shadow-md"></div>',
                            className: 'user-marker-icon',
                            iconSize: [16, 16],
                            iconAnchor: [8, 8]
                        });
                        L.marker([lat, lng], { icon: userIcon }).addTo(map).bindPopup("Your Location");
                    }, (err) => {}, { enableHighAccuracy: true });
                }
            }
        });
    </script>
</x-layout>