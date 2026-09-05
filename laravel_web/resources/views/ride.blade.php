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

    <main class="w-full mx-auto px-4 py-6 sm:px-6 lg:px-8 max-w-[1500px]" x-data="rideBooking()">
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
                        <div class="flex items-center gap-2 relative">
                            <!-- Schedule Dropdown -->
                            <div class="relative flex-1" @click.away="showScheduleDropdown = false">
                                <button type="button" 
                                        @click="toggleScheduleDropdown()" 
                                        class="w-full py-2.5 px-3.5 bg-gray-100 dark:bg-[#222] hover:bg-gray-200 dark:hover:bg-[#333] rounded-full text-xs font-extrabold text-gray-900 dark:text-white flex items-center justify-between transition-all select-none cursor-pointer"
                                        :class="showScheduleDropdown ? 'ring-2 ring-black/10 dark:ring-white/20' : ''">
                                    <div class="flex items-center gap-2 min-w-0 pr-1">
                                        <span class="shrink-0">🕒</span>
                                        <span class="truncate" x-text="schedule_type === 'now' ? 'Pickup now' : getFormattedScheduledDate()">Pickup now</span>
                                    </div>
                                    <span class="text-gray-400 text-[10px] shrink-0 transition-transform duration-200" :class="showScheduleDropdown ? 'rotate-180' : ''">▼</span>
                                </button>

                                <!-- Schedule Dropdown Popover Card -->
                                <div x-show="showScheduleDropdown" 
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                     class="absolute left-0 top-full mt-2 w-[310px] sm:w-[340px] max-w-[calc(100vw-2.5rem)] bg-white dark:bg-[#181818] border border-gray-200 dark:border-white/10 rounded-2xl shadow-2xl p-3.5 z-[99999] space-y-3"
                                     x-cloak
                                     style="display: none;">
                                    
                                    <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-white/5">
                                        <span class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider">When do you need a ride?</span>
                                        <button type="button" @click="showScheduleDropdown = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs p-1">✕</button>
                                    </div>

                                    <!-- Option 1: Pickup Now -->
                                    <button type="button" 
                                            @click="selectPickupNow()"
                                            class="w-full p-2.5 rounded-xl border text-left flex items-center justify-between transition-all cursor-pointer"
                                            :class="schedule_type === 'now' ? 'bg-emerald-50/70 dark:bg-emerald-950/30 border-emerald-500/50 text-emerald-950 dark:text-emerald-300' : 'bg-gray-50 dark:bg-white/5 border-transparent hover:border-gray-200 dark:hover:border-white/10 text-gray-700 dark:text-gray-300'">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-8 h-8 rounded-lg bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm font-bold shrink-0">⚡</span>
                                            <div>
                                                <div class="text-xs font-bold text-gray-900 dark:text-white">Pickup now</div>
                                                <div class="text-[11px] text-gray-500 dark:text-gray-400">Driver arrives in ~3–5 mins</div>
                                            </div>
                                        </div>
                                        <span x-show="schedule_type === 'now'" class="text-emerald-600 dark:text-emerald-400 text-sm font-black">✓</span>
                                    </button>

                                    <!-- Option 2: Schedule for Later -->
                                    <div class="p-2.5 rounded-xl border transition-all"
                                         :class="schedule_type === 'later' ? 'bg-brand-500/5 border-brand-500/40' : 'bg-gray-50 dark:bg-white/5 border-transparent'">
                                        <button type="button" 
                                                @click="schedule_type = 'later'"
                                                class="w-full text-left flex items-center justify-between cursor-pointer">
                                            <div class="flex items-center gap-2.5">
                                                <span class="w-8 h-8 rounded-lg bg-brand-500/15 text-brand-600 dark:text-brand-400 flex items-center justify-center text-sm font-bold shrink-0">📅</span>
                                                <div>
                                                    <div class="text-xs font-bold text-gray-900 dark:text-white">Schedule for later</div>
                                                    <div class="text-[11px] text-gray-500 dark:text-gray-400">Plan up to 30 days ahead</div>
                                                </div>
                                            </div>
                                            <span x-show="schedule_type === 'later'" class="text-brand-500 text-sm font-black">✓</span>
                                        </button>

                                        <!-- Date & Time Picker Controls (Visible when later is selected) -->
                                        <div x-show="schedule_type === 'later'" class="mt-3 pt-3 border-t border-gray-200/60 dark:border-white/10 space-y-2.5">
                                            <div class="grid grid-cols-2 gap-2">
                                                <div>
                                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">Date</label>
                                                    <input type="date" 
                                                           x-model="scheduledDate" 
                                                           :min="minScheduleDate" 
                                                           class="w-full px-2.5 py-2 text-xs font-bold bg-white dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">Time</label>
                                                    <input type="time" 
                                                           x-model="scheduledTime" 
                                                           class="w-full px-2.5 py-2 text-xs font-bold bg-white dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                                                </div>
                                            </div>

                                            <button type="button" 
                                                    @click="confirmSchedule()" 
                                                    class="w-full py-2 px-3 bg-black hover:bg-gray-900 dark:bg-brand-500 dark:hover:bg-brand-400 text-white dark:text-black text-xs font-extrabold rounded-xl transition-all shadow-sm flex items-center justify-center gap-1.5 cursor-pointer">
                                                <span>Set Pickup Time</span>
                                                <span>→</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Passenger Dropdown -->
                            <div class="relative flex-1" @click.away="showRiderDropdown = false">
                                <button type="button" 
                                        @click="toggleRiderDropdown()" 
                                        class="w-full py-2.5 px-3.5 bg-gray-100 dark:bg-[#222] hover:bg-gray-200 dark:hover:bg-[#333] rounded-full text-xs font-extrabold text-gray-900 dark:text-white flex items-center justify-between transition-all select-none cursor-pointer"
                                        :class="showRiderDropdown ? 'ring-2 ring-black/10 dark:ring-white/20' : ''">
                                    <div class="flex items-center gap-2 min-w-0 pr-1">
                                        <span class="shrink-0">👤</span>
                                        <span class="truncate" x-text="riderType === 'me' ? 'For me' : (riderName ? 'For ' + riderName.split(' ')[0] : 'Someone else')">For me</span>
                                    </div>
                                    <span class="text-gray-400 text-[10px] shrink-0 transition-transform duration-200" :class="showRiderDropdown ? 'rotate-180' : ''">▼</span>
                                </button>

                                <!-- Rider Dropdown Popover Card -->
                                <div x-show="showRiderDropdown" 
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                     class="absolute right-0 top-full mt-2 w-[310px] sm:w-[340px] max-w-[calc(100vw-2.5rem)] bg-white dark:bg-[#181818] border border-gray-200 dark:border-white/10 rounded-2xl shadow-2xl p-3.5 z-[99999] space-y-3"
                                     x-cloak
                                     style="display: none;">
                                    
                                    <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-white/5">
                                        <span class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider">Who is riding?</span>
                                        <button type="button" @click="showRiderDropdown = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs p-1">✕</button>
                                    </div>

                                    <!-- Option 1: For Me -->
                                    <button type="button" 
                                            @click="selectRiderMe()"
                                            class="w-full p-2.5 rounded-xl border text-left flex items-center justify-between transition-all cursor-pointer"
                                            :class="riderType === 'me' ? 'bg-emerald-50/70 dark:bg-emerald-950/30 border-emerald-500/50 text-emerald-950 dark:text-emerald-300' : 'bg-gray-50 dark:bg-white/5 border-transparent hover:border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300'">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-8 h-8 rounded-lg bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm font-bold shrink-0">👤</span>
                                            <div>
                                                <div class="text-xs font-bold text-gray-900 dark:text-white">For me</div>
                                                <div class="text-[11px] text-gray-500 dark:text-gray-400">{{ auth()->user()->name ?? 'Account owner' }} (You)</div>
                                            </div>
                                        </div>
                                        <span x-show="riderType === 'me'" class="text-emerald-600 dark:text-emerald-400 text-sm font-black">✓</span>
                                    </button>

                                    <!-- Option 2: Someone Else -->
                                    <div class="p-2.5 rounded-xl border transition-all"
                                         :class="riderType === 'someone_else' ? 'bg-brand-500/5 border-brand-500/40' : 'bg-gray-50 dark:bg-white/5 border-transparent'">
                                        <button type="button" 
                                                @click="riderType = 'someone_else'"
                                                class="w-full text-left flex items-center justify-between cursor-pointer">
                                            <div class="flex items-center gap-2.5">
                                                <span class="w-8 h-8 rounded-lg bg-brand-500/15 text-brand-600 dark:text-brand-400 flex items-center justify-center text-sm font-bold shrink-0">👥</span>
                                                <div>
                                                    <div class="text-xs font-bold text-gray-900 dark:text-white">Someone else</div>
                                                    <div class="text-[11px] text-gray-500 dark:text-gray-400">Driver contacts the passenger directly</div>
                                                </div>
                                            </div>
                                            <span x-show="riderType === 'someone_else'" class="text-brand-500 text-sm font-black">✓</span>
                                        </button>

                                        <!-- Someone Else Inputs (Visible when selected) -->
                                        <div x-show="riderType === 'someone_else'" class="mt-3 pt-3 border-t border-gray-200/60 dark:border-white/10 space-y-2.5">
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">Rider Full Name <span class="text-red-500">*</span></label>
                                                <input type="text" 
                                                       x-model="riderName" 
                                                       placeholder="e.g. Sarah Jenkins" 
                                                       class="w-full px-3 py-2 text-xs font-semibold bg-white dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                                            </div>

                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">Rider Phone Number <span class="text-red-500">*</span></label>
                                                <input type="tel" 
                                                       x-model="riderPhone" 
                                                       placeholder="e.g. +1 555-0199" 
                                                       class="w-full px-3 py-2 text-xs font-semibold bg-white dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                                                <p class="text-[10px] text-gray-400 mt-1">We'll send driver arrival alerts via SMS to this phone.</p>
                                            </div>

                                            <button type="button" 
                                                    @click="confirmRiderSomeoneElse()" 
                                                    class="w-full py-2 px-3 bg-black hover:bg-gray-900 dark:bg-brand-500 dark:hover:bg-brand-400 text-white dark:text-black text-xs font-extrabold rounded-xl transition-all shadow-sm flex items-center justify-center gap-1.5 cursor-pointer">
                                                <span>Save Passenger Details</span>
                                                <span>✓</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Location Inputs Section -->
                        <div class="space-y-3 relative">
                            <!-- Pickup Location Input -->
                            <div class="relative">
                                <div class="absolute left-3.5 top-3.5 text-emerald-500 font-black text-sm">●</div>
                                <input type="text" id="pickup_location" name="pickup_location" x-model="pickup" required
                                       @input.debounce.250ms="onPickupInput()"
                                       @focus="if(pickupSuggestions.length > 0) showPickupSuggestions = true"
                                       @keydown.enter.prevent="autoSelectOrGeocodePickup()"
                                       @blur="setTimeout(() => autoSelectOrGeocodePickup(), 300)"
                                       placeholder="Search pickup location (address, hotel, airport...)" 
                                       class="w-full pl-10 pr-10 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 text-xs font-extrabold focus:outline-none focus:ring-2 focus:ring-black dark:focus:ring-white transition-all">
                                
                                <button type="button" 
                                        id="use_my_location_btn" 
                                        @click="useCurrentLocation()" 
                                        :disabled="isDetectingLocation"
                                        :title="isDetectingLocation ? 'Detecting high-accuracy GPS...' : 'Use exact current location'" 
                                        class="absolute right-3 top-3 p-1 text-gray-400 hover:text-emerald-500 font-bold text-xs transition-colors cursor-pointer disabled:opacity-50">
                                    <span x-show="!isDetectingLocation" class="text-sm">📍</span>
                                    <svg x-show="isDetectingLocation" class="animate-spin h-4 w-4 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                </button>

                                <!-- Pickup Suggestions Dropdown -->
                                <div x-show="showPickupSuggestions && pickupSuggestions.length > 0" 
                                     @click.away="showPickupSuggestions = false"
                                     style="display: none;"
                                     class="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl shadow-2xl z-[9999] max-h-56 overflow-y-auto divide-y divide-gray-100 dark:divide-white/5">
                                    <template x-for="item in pickupSuggestions" :key="item.place_id || item.description">
                                        <button type="button" @click="selectPickupSuggestion(item)" class="w-full text-left px-3.5 py-2.5 hover:bg-gray-50 dark:hover:bg-[#222] transition-colors flex items-start gap-2.5 cursor-pointer">
                                            <span class="text-emerald-500 text-xs shrink-0 mt-0.5">●</span>
                                            <div class="min-w-0 flex-1">
                                                <span class="font-extrabold block text-xs text-gray-900 dark:text-white truncate" x-text="item.main_text || item.description"></span>
                                                <span class="block text-[10px] text-gray-500 dark:text-gray-400 truncate" x-text="item.secondary_text || item.description"></span>
                                            </div>
                                        </button>
                                    </template>
                                </div>

                                <!-- Real-Time Accuracy / Mode Badge -->
                                <div x-show="locationAccuracyText" x-cloak class="mt-1 flex items-center justify-between text-[11px] text-emerald-600 dark:text-emerald-400 font-bold px-1">
                                    <div class="flex items-center gap-1">
                                        <span>🎯</span>
                                        <span x-text="locationAccuracyText"></span>
                                    </div>
                                    <span class="text-gray-400 font-medium text-[10px]">Click map or drag pin to adjust</span>
                                </div>
                            </div>

                            <!-- Dynamic Intermediate Stops (Positioned strictly between Pickup and Destination) -->
                            <template x-for="(stop, index) in stops" :key="stop.id">
                                <div class="relative space-y-1">
                                    <div class="relative flex items-center gap-2">
                                        <div class="absolute left-3.5 top-3.5 text-amber-500 font-bold text-xs">📍</div>
                                        <input type="text" x-model="stop.location" 
                                               @input.debounce.250ms="searchStopLocation(stop)"
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
                                         class="absolute left-0 right-12 top-full mt-1 bg-white dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl shadow-2xl z-[9999] max-h-56 overflow-y-auto divide-y divide-gray-100 dark:divide-white/5">
                                        <template x-for="item in stop.suggestions" :key="item.place_id || item.description">
                                            <button type="button" @click="selectStopSuggestion(stop, item)" class="w-full text-left px-3.5 py-2.5 hover:bg-gray-50 dark:hover:bg-[#222] transition-colors flex items-start gap-2.5 cursor-pointer">
                                                <span class="text-amber-500 text-xs shrink-0 mt-0.5">📍</span>
                                                <div class="min-w-0 flex-1">
                                                    <span class="font-extrabold block text-xs text-gray-900 dark:text-white truncate" x-text="item.main_text || item.description"></span>
                                                    <span class="block text-[10px] text-gray-500 dark:text-gray-400 truncate" x-text="item.secondary_text || item.description"></span>
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
                                       @input.debounce.250ms="onDropoffInput()"
                                       @focus="if(dropoffSuggestions.length > 0) showDropoffSuggestions = true"
                                       @keydown.enter.prevent="autoSelectOrGeocodeDropoff()"
                                       @blur="setTimeout(() => autoSelectOrGeocodeDropoff(), 300)"
                                       placeholder="Where to? (Search destination)" 
                                       class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 text-xs font-extrabold focus:outline-none focus:ring-2 focus:ring-black dark:focus:ring-white transition-all">

                                <!-- Dropoff Suggestions Dropdown -->
                                <div x-show="showDropoffSuggestions && dropoffSuggestions.length > 0" 
                                     @click.away="showDropoffSuggestions = false"
                                     style="display: none;"
                                     class="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl shadow-2xl z-[9999] max-h-56 overflow-y-auto divide-y divide-gray-100 dark:divide-white/5">
                                    <template x-for="item in dropoffSuggestions" :key="item.place_id || item.description">
                                        <button type="button" @click="selectDropoffSuggestion(item)" class="w-full text-left px-3.5 py-2.5 hover:bg-gray-50 dark:hover:bg-[#222] transition-colors flex items-start gap-2.5 cursor-pointer">
                                            <span class="text-black dark:text-white text-xs shrink-0 mt-0.5">□</span>
                                            <div class="min-w-0 flex-1">
                                                <span class="font-extrabold block text-xs text-gray-900 dark:text-white truncate" x-text="item.main_text || item.description"></span>
                                                <span class="block text-[10px] text-gray-500 dark:text-gray-400 truncate" x-text="item.secondary_text || item.description"></span>
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

                        <template x-if="currentRideId">
                            <a :href="'/ride/track/' + currentRideId" 
                               class="block w-full py-3 bg-amber-400 hover:bg-amber-500 text-slate-950 font-black text-xs rounded-2xl shadow-md transition-all text-center">
                               📍 Open Live GPS Tracker Page →
                            </a>
                        </template>

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

                        <!-- Dedicated GPS Tracker Link -->
                        <template x-if="currentRideId">
                            <a :href="'/ride/track/' + currentRideId" 
                               class="block w-full py-3 bg-amber-400 hover:bg-amber-500 text-slate-950 font-black text-xs rounded-2xl shadow-md transition-all text-center">
                               📍 Open Live GPS Tracker Page →
                            </a>
                        </template>

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

                    <!-- Floating Map Control: Locate Me Button -->
                    <div class="absolute bottom-6 right-6 z-[1000] flex flex-col gap-2">
                        <button type="button" 
                                @click="useCurrentLocation()" 
                                :disabled="isDetectingLocation"
                                :title="isDetectingLocation ? 'Locating exact GPS...' : 'Locate my position accurately'"
                                class="w-12 h-12 bg-white dark:bg-[#1f1f1f] hover:bg-gray-50 dark:hover:bg-[#2a2a2a] text-gray-800 dark:text-white rounded-2xl shadow-xl border border-gray-200 dark:border-white/10 flex items-center justify-center transition-all hover:scale-105 active:scale-95 cursor-pointer disabled:opacity-50">
                            <span x-show="!isDetectingLocation" class="text-xl">🎯</span>
                            <svg x-show="isDetectingLocation" class="animate-spin h-5 w-5 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Floating Map Guide / Hint Badge -->
                    <div class="absolute top-4 left-4 z-[1000] pointer-events-none">
                        <div class="bg-white/95 dark:bg-black/85 backdrop-blur-md px-3 py-1.5 rounded-full border border-gray-200/60 dark:border-white/10 shadow-lg text-[11px] font-extrabold text-gray-800 dark:text-gray-200 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Click map or drag pin to adjust pickup</span>
                        </div>
                    </div>
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
                            <template x-for="item in modalSuggestions" :key="item.place_id || item.description">
                                <button type="button" @click="selectModalSuggestion(item)" class="w-full text-left px-3.5 py-2.5 hover:bg-gray-50 dark:hover:bg-[#222] transition-colors flex items-start gap-2.5">
                                    <span class="text-amber-500 text-xs shrink-0 mt-0.5">📍</span>
                                    <div class="min-w-0 flex-1">
                                        <span class="font-extrabold block text-xs truncate" x-text="item.main_text || item.description"></span>
                                        <span class="block text-[10px] text-gray-500 truncate" x-text="item.secondary_text || item.description"></span>
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
                showScheduleDropdown: false,
                showRiderDropdown: false,
                scheduledDate: '',
                minScheduleDate: '',
                scheduledTime: '',
                riderName: '',
                riderPhone: '',
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
                currentRideId: null,
                pollTimer: null,
                driverName: '',
                driverPlate: '',
                driverModel: '',
                driverPhone: '',
                userLat: null,
                userLng: null,
                userAccuracy: null,
                isDetectingLocation: false,
                locationAccuracyText: '',

                init() {
                    this.fetchSavedLocations();
                    this.fetchSavedCards();
                    this.initScheduleDefaults();
                    this.initUserLocation();
                    this.checkForActiveRide();
                },

                async checkForActiveRide() {
                    const urlParams = new URLSearchParams(window.location.search);
                    const resumeId = urlParams.get('resume') || localStorage.getItem('rmc_active_ride_id');
                    if (!resumeId) return;

                    try {
                        const res = await fetch(`/api/ride/${resumeId}/status`);
                        if (res.ok) {
                            const data = await res.json();
                            if (['pending', 'accepted', 'en_route', 'arrived', 'in_progress'].includes(data.status)) {
                                this.currentRideId = resumeId;
                                localStorage.setItem('rmc_active_ride_id', resumeId);
                                if (data.pickup) this.pickup = data.pickup;
                                if (data.dropoff) this.dropoff = data.dropoff;
                                if (data.driver) {
                                    this.driverName = data.driver.name || 'Driver';
                                    this.driverPlate = data.driver.vehicle_plate || '';
                                    this.driverModel = data.driver.vehicle_model || 'Executive Sedan';
                                    this.driverPhone = data.driver.phone || '';
                                    this.bookingStep = 'driver_assigned';
                                } else {
                                    this.bookingStep = (data.status === 'pending') ? 'finding_driver' : 'driver_assigned';
                                }
                                this.startRideStatusPolling(resumeId);
                            } else if (['completed', 'cancelled'].includes(data.status)) {
                                localStorage.removeItem('rmc_active_ride_id');
                            }
                        }
                    } catch(e) {}
                },

                startRideStatusPolling(rideId) {
                    if (this.pollTimer) clearInterval(this.pollTimer);
                    this.pollTimer = setInterval(async () => {
                        try {
                            const sRes = await fetch(`/api/ride/${rideId}/status`);
                            if (sRes.ok) {
                                const sData = await sRes.json();
                                if (['accepted', 'en_route', 'arrived', 'in_progress'].includes(sData.status)) {
                                    this.driverName = (sData.driver && sData.driver.name) || sData.driver_name || 'Driver';
                                    this.driverPlate = (sData.driver && sData.driver.vehicle_plate) || 'REG-8899';
                                    this.driverModel = (sData.driver && sData.driver.vehicle_model) || 'Executive Sedan';
                                    this.driverPhone = (sData.driver && sData.driver.phone) || '';
                                    this.bookingStep = 'driver_assigned';
                                } else if (sData.status === 'completed') {
                                    clearInterval(this.pollTimer);
                                    localStorage.removeItem('rmc_active_ride_id');
                                    this.bookingStep = 'completed';
                                } else if (sData.status === 'cancelled') {
                                    clearInterval(this.pollTimer);
                                    localStorage.removeItem('rmc_active_ride_id');
                                    alert('Ride request was cancelled.');
                                    this.bookingStep = 'find_trip';
                                }
                            }
                        } catch(err) {}
                    }, 3000);
                },

                initScheduleDefaults() {
                    const now = new Date();
                    const yyyy = now.getFullYear();
                    const mm = String(now.getMonth() + 1).padStart(2, '0');
                    const dd = String(now.getDate()).padStart(2, '0');
                    this.scheduledDate = `${yyyy}-${mm}-${dd}`;
                    this.minScheduleDate = `${yyyy}-${mm}-${dd}`;

                    now.setMinutes(now.getMinutes() + 30);
                    const hh = String(now.getHours()).padStart(2, '0');
                    const min = String(now.getMinutes()).padStart(2, '0');
                    this.scheduledTime = `${hh}:${min}`;
                },

                getFormattedScheduledDate() {
                    if (!this.scheduledDate || !this.scheduledTime) return 'Scheduled';
                    try {
                        const parts = this.scheduledDate.split('-');
                        if (parts.length < 3) return 'Scheduled';
                        const y = parseInt(parts[0], 10);
                        const m = parseInt(parts[1], 10);
                        const d = parseInt(parts[2], 10);
                        const tParts = this.scheduledTime.split(':');
                        const h = parseInt(tParts[0], 10) || 0;
                        const min = parseInt(tParts[1], 10) || 0;
                        const dt = new Date(y, m - 1, d, h, min);
                        const now = new Date();
                        const isToday = dt.toDateString() === now.toDateString();
                        const timeStr = dt.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
                        if (isToday) return `Today, ${timeStr}`;
                        return `${dt.toLocaleDateString([], { month: 'short', day: 'numeric' })}, ${timeStr}`;
                    } catch (e) {
                        return 'Scheduled';
                    }
                },

                toggleScheduleDropdown() {
                    this.showScheduleDropdown = !this.showScheduleDropdown;
                    if (this.showScheduleDropdown) this.showRiderDropdown = false;
                },

                toggleRiderDropdown() {
                    this.showRiderDropdown = !this.showRiderDropdown;
                    if (this.showRiderDropdown) this.showScheduleDropdown = false;
                },

                selectPickupNow() {
                    this.schedule_type = 'now';
                    this.showScheduleDropdown = false;
                },

                confirmSchedule() {
                    if (!this.scheduledDate || !this.scheduledTime) {
                        alert('Please select both a date and time.');
                        return;
                    }
                    this.schedule_type = 'later';
                    this.showScheduleDropdown = false;
                },

                selectRiderMe() {
                    this.riderType = 'me';
                    this.showRiderDropdown = false;
                },

                confirmRiderSomeoneElse() {
                    if (!this.riderName || !this.riderName.trim()) {
                        alert('Please enter the passenger\'s name.');
                        return;
                    }
                    if (!this.riderPhone || !this.riderPhone.trim()) {
                        alert('Please enter the passenger\'s phone number.');
                        return;
                    }
                    this.riderType = 'someone_else';
                    this.showRiderDropdown = false;
                },

                initUserLocation() {
                    window.addEventListener('map-clicked', async (e) => {
                        await this.setPickupFromCoordinates(e.detail.lat, e.detail.lng, 'Pinned on map');
                    });

                    window.addEventListener('map-pickup-dragged', async (e) => {
                        await this.setPickupFromCoordinates(e.detail.lat, e.detail.lng, 'Pin adjusted');
                    });

                    // Detect high-accuracy location automatically on load
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            async (pos) => {
                                const lat = pos.coords.latitude;
                                const lng = pos.coords.longitude;
                                const accuracy = Math.round(pos.coords.accuracy || 15);
                                this.userLat = lat;
                                this.userLng = lng;
                                this.userAccuracy = accuracy;

                                window.dispatchEvent(new CustomEvent('map-user-located', {
                                    detail: { lat, lng, accuracy, flyTo: false }
                                }));

                                // If pickup is blank on load, auto-populate with reverse-geocoded GPS address
                                if (!this.pickup || this.pickup.trim() === '') {
                                    await this.setPickupFromCoordinates(lat, lng, `GPS Accurate (±${accuracy}m)`);
                                }
                            },
                            async (err) => {
                                console.warn('Browser GPS not available on load, using IP fallback:', err);
                                try {
                                    const ipRes = await fetch('https://get.geojs.io/v1/ip/geo.json');
                                    if (ipRes.ok) {
                                        const ipData = await ipRes.json();
                                        const lat = parseFloat(ipData.latitude);
                                        const lng = parseFloat(ipData.longitude);
                                        this.userLat = lat;
                                        this.userLng = lng;
                                        window.dispatchEvent(new CustomEvent('map-user-located', {
                                            detail: { lat, lng, accuracy: 5000, flyTo: false }
                                        }));
                                    }
                                } catch (e) {}
                            },
                            { enableHighAccuracy: true, timeout: 8000, maximumAge: 60000 }
                        );
                    }
                },

                async useCurrentLocation() {
                    if (!navigator.geolocation) {
                        alert("Geolocation is not supported by your browser.");
                        return;
                    }
                    this.isDetectingLocation = true;
                    this.locationAccuracyText = 'Locating exact GPS...';

                    const getPos = () => new Promise((resolve, reject) => {
                        navigator.geolocation.getCurrentPosition(resolve, reject, {
                            enableHighAccuracy: true,
                            timeout: 12000,
                            maximumAge: 0
                        });
                    });

                    try {
                        let lat, lng, accuracy;
                        try {
                            const pos = await getPos();
                            lat = pos.coords.latitude;
                            lng = pos.coords.longitude;
                            accuracy = Math.round(pos.coords.accuracy || 10);
                            this.userLat = lat;
                            this.userLng = lng;
                            this.userAccuracy = accuracy;
                            this.locationAccuracyText = `GPS Accurate (±${accuracy}m)`;
                        } catch (gpsErr) {
                            console.warn('High accuracy GPS error, trying network IP:', gpsErr);
                            const ipRes = await fetch('https://get.geojs.io/v1/ip/geo.json');
                            if (ipRes.ok) {
                                const ipData = await ipRes.json();
                                lat = parseFloat(ipData.latitude);
                                lng = parseFloat(ipData.longitude);
                                accuracy = 3000;
                                this.userLat = lat;
                                this.userLng = lng;
                                this.userAccuracy = accuracy;
                                this.locationAccuracyText = `Approximate location (${ipData.city || 'Network'})`;
                            } else {
                                throw gpsErr;
                            }
                        }

                        window.dispatchEvent(new CustomEvent('map-user-located', {
                            detail: { lat, lng, accuracy, flyTo: true }
                        }));

                        await this.setPickupFromCoordinates(lat, lng, this.locationAccuracyText);
                    } catch (err) {
                        console.error('Locate error:', err);
                        alert('Could not determine your exact location. Please allow browser location permissions or click on the map to place your pin.');
                        this.locationAccuracyText = '';
                    } finally {
                        this.isDetectingLocation = false;
                    }
                },

                async setPickupFromCoordinates(lat, lng, accuracyText = 'Selected on map') {
                    this.pickupLat = lat;
                    this.pickupLng = lng;
                    this.locationAccuracyText = accuracyText;

                    try {
                        const res = await fetch(`/api/places/reverse?lat=${lat}&lng=${lng}`);
                        if (res.ok) {
                            const data = await res.json();
                            if (data.success && data.place) {
                                this.pickup = data.place.formatted_address || data.place.name || `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                            } else {
                                this.pickup = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                            }
                        } else {
                            this.pickup = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                        }
                    } catch (e) {
                        this.pickup = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                    }
                    this.showPickupSuggestions = false;
                    this.updateMapRoute();
                },

                onPickupInput() {
                    if (this.pickupLat !== null || this.pickupLng !== null) {
                        this.pickupLat = null;
                        this.pickupLng = null;
                        this.locationAccuracyText = '';
                        this.updateMapRoute();
                    }
                    this.searchPickupLocation();
                },

                async searchPickupLocation() {
                    this.pickupLat = null;
                    this.pickupLng = null;
                    this.locationAccuracyText = '';
                    if (!this.pickup || this.pickup.trim().length < 2) {
                        this.pickupSuggestions = [];
                        this.showPickupSuggestions = false;
                        return;
                    }
                    try {
                        const biasLat = this.userLat || '';
                        const biasLng = this.userLng || '';
                        const res = await fetch(`/api/places/autocomplete?input=${encodeURIComponent(this.pickup.trim())}&lat=${biasLat}&lng=${biasLng}`);
                        if (res.ok) {
                            const data = await res.json();
                            this.pickupSuggestions = data.predictions || [];
                            this.showPickupSuggestions = this.pickupSuggestions.length > 0;
                        }
                    } catch (e) {
                        console.warn('Pickup search error:', e);
                    }
                },

                async autoSelectOrGeocodePickup() {
                    if (this.pickupLat && this.pickupLng) return;
                    if (this.pickupSuggestions && this.pickupSuggestions.length > 0) {
                        await this.selectPickupSuggestion(this.pickupSuggestions[0]);
                        return;
                    }
                    if (this.pickup && this.pickup.trim().length >= 2) {
                        try {
                            const res = await fetch(`/api/places/geocode?query=${encodeURIComponent(this.pickup.trim())}`);
                            if (res.ok) {
                                const data = await res.json();
                                if (data.success && data.place) {
                                    this.pickupLat = parseFloat(data.place.lat);
                                    this.pickupLng = parseFloat(data.place.lng);
                                    this.locationAccuracyText = 'Matched address';
                                    this.showPickupSuggestions = false;
                                    this.updateMapRoute();
                                }
                            }
                        } catch (e) {}
                    }
                },

                async selectPickupSuggestion(item) {
                    this.pickup = item.main_text || item.description;
                    this.showPickupSuggestions = false;
                    this.locationAccuracyText = 'Selected from search';

                    if (item.lat && item.lng) {
                        this.pickupLat = parseFloat(item.lat);
                        this.pickupLng = parseFloat(item.lng);
                        this.updateMapRoute();
                        return;
                    }

                    if (item.place_id) {
                        try {
                            const res = await fetch(`/api/places/details?place_id=${encodeURIComponent(item.place_id)}`);
                            if (res.ok) {
                                const data = await res.json();
                                if (data.success && data.place) {
                                    this.pickupLat = parseFloat(data.place.lat);
                                    this.pickupLng = parseFloat(data.place.lng);
                                    this.pickup = data.place.formatted_address || data.place.name || this.pickup;
                                    this.updateMapRoute();
                                }
                            }
                        } catch (e) {
                            console.warn('Place details error:', e);
                        }
                    }
                },

                onDropoffInput() {
                    // Stale coordinates cleared immediately so previous destination is erased from map
                    if (this.dropoffLat !== null || this.dropoffLng !== null) {
                        this.dropoffLat = null;
                        this.dropoffLng = null;
                        this.updateMapRoute();
                    }
                    this.searchDropoffLocation();
                },

                async searchDropoffLocation() {
                    this.dropoffLat = null;
                    this.dropoffLng = null;
                    if (!this.dropoff || this.dropoff.trim().length < 2) {
                        this.dropoffSuggestions = [];
                        this.showDropoffSuggestions = false;
                        return;
                    }
                    try {
                        const biasLat = this.pickupLat || this.userLat || '';
                        const biasLng = this.pickupLng || this.userLng || '';
                        const res = await fetch(`/api/places/autocomplete?input=${encodeURIComponent(this.dropoff.trim())}&lat=${biasLat}&lng=${biasLng}`);
                        if (res.ok) {
                            const data = await res.json();
                            this.dropoffSuggestions = data.predictions || [];
                            this.showDropoffSuggestions = this.dropoffSuggestions.length > 0;
                        }
                    } catch (e) {
                        console.warn('Dropoff search error:', e);
                    }
                },

                async autoSelectOrGeocodeDropoff() {
                    if (this.dropoffLat && this.dropoffLng) return;
                    if (this.dropoffSuggestions && this.dropoffSuggestions.length > 0) {
                        await this.selectDropoffSuggestion(this.dropoffSuggestions[0]);
                        return;
                    }
                    if (this.dropoff && this.dropoff.trim().length >= 2) {
                        try {
                            const res = await fetch(`/api/places/geocode?query=${encodeURIComponent(this.dropoff.trim())}`);
                            if (res.ok) {
                                const data = await res.json();
                                if (data.success && data.place) {
                                    this.dropoffLat = parseFloat(data.place.lat);
                                    this.dropoffLng = parseFloat(data.place.lng);
                                    this.showDropoffSuggestions = false;
                                    this.updateMapRoute();
                                }
                            }
                        } catch (e) {}
                    }
                },

                async selectDropoffSuggestion(item) {
                    this.dropoff = item.main_text || item.description;
                    this.showDropoffSuggestions = false;

                    if (item.lat && item.lng) {
                        this.dropoffLat = parseFloat(item.lat);
                        this.dropoffLng = parseFloat(item.lng);
                        this.updateMapRoute();
                        return;
                    }

                    if (item.place_id) {
                        try {
                            const res = await fetch(`/api/places/details?place_id=${encodeURIComponent(item.place_id)}`);
                            if (res.ok) {
                                const data = await res.json();
                                if (data.success && data.place) {
                                    this.dropoffLat = parseFloat(data.place.lat);
                                    this.dropoffLng = parseFloat(data.place.lng);
                                    this.dropoff = data.place.formatted_address || data.place.name || this.dropoff;
                                    this.updateMapRoute();
                                }
                            }
                        } catch (e) {
                            console.warn('Place details error:', e);
                        }
                    }
                },

                async searchStopLocation(stop) {
                    stop.isSelected = false;
                    if (stop.lat !== null || stop.lng !== null) {
                        stop.lat = null;
                        stop.lng = null;
                        this.updateMapRoute();
                    }
                    if (!stop.location || stop.location.trim().length < 2) {
                        stop.suggestions = [];
                        stop.showSuggestions = false;
                        return;
                    }
                    try {
                        const biasLat = this.pickupLat || this.userLat || '';
                        const biasLng = this.pickupLng || this.userLng || '';
                        const res = await fetch(`/api/places/autocomplete?input=${encodeURIComponent(stop.location.trim())}&lat=${biasLat}&lng=${biasLng}`);
                        if (res.ok) {
                            const data = await res.json();
                            stop.suggestions = data.predictions || [];
                            stop.showSuggestions = stop.suggestions.length > 0;
                        }
                    } catch (e) {
                        console.warn('Stop search error:', e);
                    }
                },

                async selectStopSuggestion(stop, item) {
                    stop.location = item.main_text || item.description;
                    stop.showSuggestions = false;

                    if (item.lat && item.lng) {
                        stop.lat = parseFloat(item.lat);
                        stop.lng = parseFloat(item.lng);
                        stop.isSelected = true;
                        this.updateMapRoute();
                        return;
                    }

                    if (item.place_id) {
                        try {
                            const res = await fetch(`/api/places/details?place_id=${encodeURIComponent(item.place_id)}`);
                            if (res.ok) {
                                const data = await res.json();
                                if (data.success && data.place) {
                                    stop.lat = parseFloat(data.place.lat);
                                    stop.lng = parseFloat(data.place.lng);
                                    stop.location = data.place.formatted_address || data.place.name || stop.location;
                                    stop.isSelected = true;
                                    this.updateMapRoute();
                                }
                            }
                        } catch (e) {}
                    }
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
                    if (!this.modalTempLocation.location || this.modalTempLocation.location.trim().length < 2) {
                        this.modalSuggestions = [];
                        this.showModalSuggestions = false;
                        return;
                    }
                    try {
                        const biasLat = this.userLat || '';
                        const biasLng = this.userLng || '';
                        const res = await fetch(`/api/places/autocomplete?input=${encodeURIComponent(this.modalTempLocation.location.trim())}&lat=${biasLat}&lng=${biasLng}`);
                        if (res.ok) {
                            const data = await res.json();
                            this.modalSuggestions = data.predictions || [];
                            this.showModalSuggestions = this.modalSuggestions.length > 0;
                        }
                    } catch (e) {
                        console.warn('Modal map search error:', e);
                    }
                },

                async selectModalSuggestion(item) {
                    this.modalTempLocation.location = item.description || item.main_text;
                    this.modalTempLocation.place_id = item.place_id ? String(item.place_id) : null;
                    this.showModalSuggestions = false;

                    if (item.lat && item.lng) {
                        this.modalTempLocation.lat = parseFloat(item.lat);
                        this.modalTempLocation.lng = parseFloat(item.lng);
                        this.modalTempLocation.isSelected = true;
                        return;
                    }

                    if (item.place_id) {
                        try {
                            const res = await fetch(`/api/places/details?place_id=${encodeURIComponent(item.place_id)}`);
                            if (res.ok) {
                                const data = await res.json();
                                if (data.success && data.place) {
                                    this.modalTempLocation.lat = parseFloat(data.place.lat);
                                    this.modalTempLocation.lng = parseFloat(data.place.lng);
                                    this.modalTempLocation.location = data.place.formatted_address || data.place.name || this.modalTempLocation.location;
                                    this.modalTempLocation.isSelected = true;
                                }
                            }
                        } catch (e) {
                            console.warn('Modal details error:', e);
                        }
                    }
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
                        const csrfToken = document.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        const res = await fetch('/ride/book', {
                            method: 'POST',
                            headers: { 
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken 
                            },
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
                                phone_number: this.phone || 'N/A',
                                is_for_someone_else: this.riderType === 'someone_else',
                                passenger_phone: this.riderType === 'me' ? (this.phone || 'N/A') : (this.riderPhone || this.phone || 'N/A'),
                                passenger_name: this.riderType === 'me' ? '{{ auth()->user()->name ?? "Rider" }}' : (this.riderName || 'Someone else'),
                                schedule_type: this.schedule_type,
                                scheduled_time: this.schedule_type === 'later' ? (this.scheduledDate + ' ' + this.scheduledTime) : null,
                                pickup_date: this.schedule_type === 'later' ? this.scheduledDate : null,
                                pickup_time: this.schedule_type === 'later' ? this.scheduledTime : null,
                                amount: parseFloat(this.selectedFare.replace('$', '')) || (this.fareBreakdown ? this.fareBreakdown.grand_total : 28.50)
                            })
                        });

                        const data = await res.json();

                        if (!res.ok) {
                            alert(data.error || 'Could not place ride request. Please check your details.');
                            this.bookingStep = 'confirm_ride';
                            return;
                        }

                        // Start polling for real driver acceptance
                        if (data.ride_id) {
                            this.currentRideId = data.ride_id;
                            localStorage.setItem('rmc_active_ride_id', data.ride_id);
                            this.startRideStatusPolling(data.ride_id);
                        }
                    } catch (e) {
                        alert('Network error while booking ride. Please try again.');
                        this.bookingStep = 'confirm_ride';
                    }
                },
                cancelRide() {
                    if (this.pollTimer) clearInterval(this.pollTimer);
                    localStorage.removeItem('rmc_active_ride_id');
                    if (this.currentRideId) {
                        const csrfToken = document.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        fetch(`/api/ride/${this.currentRideId}/cancel`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrfToken }
                        }).catch(() => {});
                    }
                    this.bookingStep = 'find_trip';
                }
            }));
        });

        document.addEventListener("DOMContentLoaded", function() {
            const mapEl = document.getElementById('map');
            if (mapEl) {
                // Initialize Leaflet map with OpenStreetMap tiles
                const map = L.map('map', {
                    center: [5.6037, -0.1870], // Default center
                    zoom: 13,
                    zoomControl: true
                });

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                }).addTo(map);

                let driverMarkers = [];
                function updateNearbyDrivers(centerLat, centerLng) {
                    driverMarkers.forEach(m => map.removeLayer(m));
                    driverMarkers = [];
                    const carIcon = L.divIcon({
                        html: '<div class="w-8 h-8 rounded-full bg-black text-white dark:bg-white dark:text-black font-black text-sm flex items-center justify-center shadow-lg border-2 border-white dark:border-black hover:scale-110 transition-transform select-none">🚗</div>',
                        className: 'car-marker-icon',
                        iconSize: [32, 32],
                        iconAnchor: [16, 16]
                    });
                    for (let i = 0; i < 5; i++) {
                        const offsetLat = (Math.random() - 0.5) * 0.025;
                        const offsetLng = (Math.random() - 0.5) * 0.025;
                        const m = L.marker([centerLat + offsetLat, centerLng + offsetLng], { icon: carIcon }).addTo(map);
                        driverMarkers.push(m);
                    }
                }

                // Initial drivers near center
                updateNearbyDrivers(5.6037, -0.1870);

                let userAccuracyCircle = null;
                let userBeaconMarker = null;

                // Handle user location detection event
                window.addEventListener('map-user-located', function(e) {
                    const { lat, lng, accuracy, flyTo } = e.detail;
                    if (!map) return;

                    if (flyTo) {
                        map.flyTo([lat, lng], 16, { duration: 1.2 });
                    } else {
                        map.setView([lat, lng], 15);
                    }

                    if (userAccuracyCircle) map.removeLayer(userAccuracyCircle);
                    if (accuracy && accuracy < 10000) {
                        userAccuracyCircle = L.circle([lat, lng], {
                            radius: Math.max(accuracy, 25),
                            color: '#10b981',
                            fillColor: '#10b981',
                            fillOpacity: 0.12,
                            weight: 1.5,
                            dashArray: '4, 4'
                        }).addTo(map);
                    }

                    if (userBeaconMarker) map.removeLayer(userBeaconMarker);
                    const beaconIcon = L.divIcon({
                        html: `
                            <div class="relative flex items-center justify-center w-8 h-8">
                                <span class="absolute w-7 h-7 rounded-full bg-emerald-500/35 animate-ping"></span>
                                <span class="relative w-4 h-4 rounded-full bg-emerald-600 border-2 border-white shadow-lg ring-2 ring-emerald-300"></span>
                            </div>
                        `,
                        className: 'user-beacon-icon',
                        iconSize: [32, 32],
                        iconAnchor: [16, 16]
                    });
                    userBeaconMarker = L.marker([lat, lng], { icon: beaconIcon, zIndexOffset: 1000 })
                        .addTo(map)
                        .bindPopup("<b>Your GPS Location</b>");

                    updateNearbyDrivers(lat, lng);
                });

                // Click anywhere on map to set/adjust pickup location
                map.on('click', function(e) {
                    window.dispatchEvent(new CustomEvent('map-clicked', {
                        detail: { lat: e.latlng.lat, lng: e.latlng.lng }
                    }));
                });

                // Global event listener for updating route & waypoints on map
                window.addEventListener('update-ride-route', function(e) {
                    if (!map) return;

                    if (window.rideRoutePolyline) map.removeLayer(window.rideRoutePolyline);
                    if (window.rideWaypointsMarkers) {
                        window.rideWaypointsMarkers.forEach(m => map.removeLayer(m));
                    }
                    window.rideWaypointsMarkers = [];

                    const waypoints = e.detail.waypoints || [];
                    if (waypoints.length === 0) return;

                    const latLngs = [];

                    waypoints.forEach((wp, idx) => {
                        latLngs.push([wp.lat, wp.lng]);

                        const isPickup = wp.type === 'pickup';
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
                            html: `<div class="px-3 py-1.5 rounded-full ${bgClass} text-xs font-extrabold shadow-2xl border-2 border-white flex items-center justify-center gap-1.5 select-none ${isPickup ? 'cursor-grab active:cursor-grabbing hover:scale-105 transition-transform' : ''}">
                                <span>${isPickup ? '📍' : (wp.type === 'dropoff' ? '🏁' : '🛑')}</span>
                                <span>${wp.label}</span>
                                ${isPickup ? '<span class="text-[10px] opacity-80">(Drag)</span>' : ''}
                            </div>`,
                            className: 'waypoint-marker-icon',
                            iconSize: [120, 32],
                            iconAnchor: [60, 16]
                        });

                        const marker = L.marker([wp.lat, wp.lng], { 
                            icon,
                            draggable: isPickup,
                            zIndexOffset: isPickup ? 900 : 500
                        }).addTo(map);

                        if (isPickup) {
                            marker.bindPopup(`<b>Pickup Location</b><br><span style="font-size:11px;color:#666;">Drag pin to fine-tune exact pickup spot</span>`);
                            marker.on('dragend', function(evt) {
                                const pos = evt.target.getLatLng();
                                window.dispatchEvent(new CustomEvent('map-pickup-dragged', {
                                    detail: { lat: pos.lat, lng: pos.lng }
                                }));
                            });
                        } else {
                            marker.bindPopup(`<b>${wp.label}</b>`);
                        }

                        window.rideWaypointsMarkers.push(marker);
                    });

                    if (latLngs.length >= 2) {
                        window.rideRoutePolyline = L.polyline(latLngs, {
                            color: '#10b981',
                            weight: 5,
                            opacity: 0.85,
                            dashArray: '8, 8'
                        }).addTo(map);

                        map.fitBounds(window.rideRoutePolyline.getBounds(), { padding: [60, 60], maxZoom: 16 });
                    } else if (latLngs.length === 1) {
                        map.setView(latLngs[0], 16);
                    }
                });
            }
        });
    </script>
</x-layout>