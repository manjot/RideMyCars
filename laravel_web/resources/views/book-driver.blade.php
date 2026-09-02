<x-layout theme="theme-hire">
    <x-slot:title>Hire a Driver — Professional Driver Hiring | RideMyCars</x-slot>

    <style>
        /* Force Google Places Autocomplete to appear on top of modals and maps */
        .pac-container {
            z-index: 9999999 !important;
            pointer-events: auto !important;
            border-radius: 1rem !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.1) !important;
            border: 1px solid rgba(245, 158, 11, 0.4) !important;
            background-color: #ffffff !important;
            font-family: inherit !important;
            margin-top: 4px !important;
            overflow: hidden !important;
        }
        .dark .pac-container {
            background-color: #1f1f1f !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            color: #ffffff !important;
        }
        .pac-item {
            padding: 10px 14px !important;
            cursor: pointer !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            color: #111827 !important;
            border-top: 1px solid rgba(243, 244, 246, 1) !important;
        }
        .dark .pac-item {
            color: #f3f4f6 !important;
            border-top: 1px solid rgba(255, 255, 255, 0.05) !important;
        }
        .pac-item:hover, .pac-item-selected {
            background-color: #fef3c7 !important;
        }
        .dark .pac-item:hover, .dark .pac-item-selected {
            background-color: rgba(180, 83, 9, 0.4) !important;
        }
        .pac-item-query {
            font-size: 13px !important;
            font-weight: 700 !important;
            color: #000000 !important;
        }
        .dark .pac-item-query {
            color: #ffffff !important;
        }
        .pac-matched {
            font-weight: 800 !important;
            color: #d97706 !important;
        }
    </style>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10" x-data="bookDriverBooking">

        <!-- Category Banner Component -->
        <x-category-banner category="Hire a Driver" />

        <!-- Page Header -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="px-3 py-1 rounded-full bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400 font-extrabold text-xs uppercase tracking-wider border border-brand-200 dark:border-brand-800/30">RideMyCars Driver Hiring</span>
                <h1 class="text-3xl md:text-4xl font-black text-gray-900 dark:text-white mt-1 tracking-tight">Hire a Personal or Commercial Driver</h1>
                <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">On-demand professional drivers for your vehicle. Verified, insured, and background-checked.</p>
            </div>
            @if(isset($driverProfile) && $driverProfile->user)
                <div class="flex items-center gap-3 bg-white dark:bg-[#111] p-3 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm shrink-0">
                    <img src="{{ $driverProfile->photo_url }}" class="w-12 h-12 rounded-full object-cover border-2 border-brand-500" alt="Driver">
                    <div>
                        <span class="text-xs text-gray-400 font-bold block uppercase">Pre-selected Driver</span>
                        <h4 class="font-extrabold text-sm text-gray-900 dark:text-white">{{ $driverProfile->user->name }}</h4>
                    </div>
                </div>
            @endif
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800/30 text-rose-800 dark:text-rose-200 text-xs font-bold space-y-1">
                @foreach($errors->all() as $error)
                    <p>• {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="/hire-driver/book" method="POST" @submit="validateForm($event)" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            @csrf
            <input type="hidden" name="driver_profile_id" :value="driverProfileId">
            <input type="hidden" name="service_category" value="private">
            <input type="hidden" name="service_type" x-model="serviceType">
            <input type="hidden" name="country" x-model="country">
            <input type="hidden" id="pickup_lat_input" name="pickup_lat">
            <input type="hidden" id="pickup_lng_input" name="pickup_lng">
            <input type="hidden" id="dropoff_lat_input" name="dropoff_lat">
            <input type="hidden" id="dropoff_lng_input" name="dropoff_lng">

            <!-- Left & Middle: Comprehensive Booking Card -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- 1. Service Type Selector Tabs (RideMyCars Style) -->
                <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 shadow-sm space-y-4">
                    <label class="block text-xs font-extrabold text-gray-900 dark:text-white uppercase tracking-wider">Select Driver Service Type *</label>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 p-1 bg-gray-100 dark:bg-[#1a1a1a] rounded-2xl text-xs font-bold">
                        <template x-for="st in ['Hire Driver', 'Hourly Driver', 'Daily Driver', 'Outstation', 'Package']" :key="st">
                            <button type="button" @click="serviceType = st; if(st==='Daily Driver') { durationType='daily'; durationCount=1; } else if(st==='Hourly Driver') { durationType='hourly'; durationCount=4; }"
                                    :class="serviceType === st ? 'bg-white dark:bg-[#111] text-brand-600 dark:text-brand-400 shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'"
                                    class="py-2.5 px-3 rounded-xl transition-all text-center">
                                <span x-text="st"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- 2. Locations & Additional Destinations / Stops -->
                <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 shadow-sm space-y-4">
                    <h3 class="font-extrabold text-base text-gray-900 dark:text-white flex items-center gap-2">
                        <span>📍 Location & Routing</span>
                    </h3>

                    <!-- Pickup Address -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Pick-up Location *</label>
                            <button type="button" id="use_my_location_btn_rmc" class="text-brand-500 hover:text-brand-600 text-xs font-bold flex items-center gap-1 transition-colors">
                                📍 Use My Location
                            </button>
                        </div>
                        <input type="text" id="pickup_location_rmc" name="pickup_location" required placeholder="Enter pickup address, city, or airport..." class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-bold text-gray-900 dark:text-white">
                    </div>

                    <!-- Additional Stops -->
                    <div class="space-y-3">
                        <div x-show="validationErrorMessage" class="p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/40 text-rose-700 dark:text-rose-300 text-xs font-bold flex items-start gap-2">
                            <span class="text-rose-500">⚠️</span>
                            <span x-text="validationErrorMessage"></span>
                        </div>

                        <template x-for="(stop, index) in stops" :key="stop.id">
                            <div class="space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-extrabold text-amber-600 dark:text-amber-400 uppercase tracking-wider flex items-center gap-1">
                                        <span>🚩 Stop</span> <span x-text="index + 1"></span>
                                    </span>
                                </div>

                                <div class="relative flex items-center gap-2">
                                    <div class="w-full relative">
                                        <input type="text" 
                                               x-model="stop.location" 
                                               @input.debounce.300ms="onStopInputChange(stop)"
                                               @focus="if(stop.suggestions && stop.suggestions.length > 0) stop.showSuggestions = true"
                                               @click.outside="stop.showSuggestions = false"
                                               x-init="initStopAutocomplete($el, stop)"
                                               placeholder="Search additional stop location (address, city, airport, landmark)..." 
                                               :class="stop.validationError ? 'border-rose-500 ring-1 ring-rose-500 bg-rose-50/20' : (stop.isSelected ? 'border-emerald-500 ring-1 ring-emerald-500/30' : 'border-amber-200 dark:border-amber-900/30')"
                                               class="w-full pl-9 pr-8 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border rounded-xl text-xs font-bold text-gray-900 dark:text-white transition-colors">
                                        
                                        <span class="absolute left-3 top-2.5 text-amber-500">📍</span>
                                        
                                        <span x-show="stop.isSelected" class="absolute right-3 top-2.5 text-emerald-500 font-bold text-xs" title="Map location verified">✓</span>

                                        <!-- Map Search Suggestions Dropdown -->
                                        <div x-show="stop.showSuggestions && stop.suggestions && stop.suggestions.length > 0"
                                             x-transition.opacity
                                             class="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-[#1f1f1f] border border-amber-200 dark:border-white/10 rounded-xl shadow-xl z-50 overflow-hidden divide-y divide-gray-100 dark:divide-white/5">
                                            <template x-for="item in stop.suggestions" :key="item.place_id || item.osm_id">
                                                <button type="button" 
                                                        @click="selectStopSuggestion(stop, item)" 
                                                        class="w-full px-3.5 py-2.5 text-left text-xs text-gray-800 dark:text-gray-200 hover:bg-amber-50 dark:hover:bg-amber-950/40 flex items-start gap-2 transition-colors">
                                                    <span class="text-amber-500 shrink-0 mt-0.5">📍</span>
                                                    <div>
                                                        <span class="font-bold block" x-text="item.display_name"></span>
                                                        <span class="text-[10px] text-gray-400 font-normal block" x-text="item.type ? (item.type.toUpperCase() + ' • ' + (item.class || 'location')) : 'Map Location'"></span>
                                                    </div>
                                                </button>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Hidden Form Payload Inputs -->
                                    <input type="hidden" :name="`additional_stops[${index}][location]`" :value="stop.location">
                                    <input type="hidden" :name="`additional_stops[${index}][lat]`" :value="stop.lat">
                                    <input type="hidden" :name="`additional_stops[${index}][lng]`" :value="stop.lng">
                                    <input type="hidden" :name="`additional_stops[${index}][place_id]`" :value="stop.place_id">

                                    <button type="button" @click="removeStop(index)" class="p-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition-colors shrink-0" title="Remove Stop">
                                        ✕
                                    </button>
                                </div>

                                <p x-show="stop.validationError" class="text-[11px] font-bold text-rose-600 dark:text-rose-400 pl-1" x-text="stop.validationError"></p>
                            </div>
                        </template>

                        <div class="flex flex-wrap items-center gap-2 pt-1">
                            <button type="button" @click="addStop()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 font-extrabold text-xs transition-colors border border-amber-200 dark:border-amber-800/30 cursor-pointer hover:bg-amber-100">
                                + Add Additional Stop
                            </button>

                            <!-- Quick Home Button -->
                            <div class="relative inline-flex items-center gap-1">
                                <button type="button" 
                                        @click="useSavedLocation('home')" 
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl font-extrabold text-xs transition-all border cursor-pointer"
                                        :class="savedLocations.home ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/40 shadow-sm' : 'bg-gray-50 dark:bg-[#1a1a1a] text-gray-700 dark:text-gray-300 border-gray-200 dark:border-white/10 hover:border-amber-300'">
                                    <span>🏠</span>
                                    <span x-text="savedLocations.home ? 'Home' : '+ Save Home'"></span>
                                </button>
                                <template x-if="savedLocations.home">
                                    <button type="button" @click.stop="openSavedLocationModal('home')" class="p-1 text-gray-400 hover:text-amber-500 font-bold text-xs rounded transition-colors" title="Edit Home Address">
                                        ✏️
                                    </button>
                                </template>
                            </div>

                            <!-- Quick Office Button -->
                            <div class="relative inline-flex items-center gap-1">
                                <button type="button" 
                                        @click="useSavedLocation('office')" 
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl font-extrabold text-xs transition-all border cursor-pointer"
                                        :class="savedLocations.office ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/40 shadow-sm' : 'bg-gray-50 dark:bg-[#1a1a1a] text-gray-700 dark:text-gray-300 border-gray-200 dark:border-white/10 hover:border-amber-300'">
                                    <span>🏢</span>
                                    <span x-text="savedLocations.office ? 'Office' : '+ Save Office'"></span>
                                </button>
                                <template x-if="savedLocations.office">
                                    <button type="button" @click.stop="openSavedLocationModal('office')" class="p-1 text-gray-400 hover:text-amber-500 font-bold text-xs rounded transition-colors" title="Edit Office Address">
                                        ✏️
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>



                    <!-- Drop-off Address -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Final Destination (Optional)</label>
                        <input type="text" id="dropoff_location_rmc" name="dropoff_location" placeholder="Enter final destination location..." class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-bold text-gray-900 dark:text-white">
                    </div>
                </div>

                <!-- 3. Schedule (Book Now vs Schedule for Later) & Duration -->
                <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 shadow-sm space-y-4">
                    <h3 class="font-extrabold text-base text-gray-900 dark:text-white">⏰ Schedule & Duration</h3>

                    <!-- Book Now vs Schedule Later -->
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" @click="scheduleMode = 'now'"
                                :class="scheduleMode === 'now' ? 'bg-brand-500 text-white font-extrabold shadow-sm' : 'bg-gray-50 dark:bg-[#1a1a1a] text-gray-700 dark:text-gray-300 font-bold border border-gray-200 dark:border-white/10'"
                                class="py-3 px-4 rounded-xl text-xs transition-all text-center">
                            ⚡ Book Now (Immediate Dispatch)
                        </button>
                        <button type="button" @click="scheduleMode = 'later'"
                                :class="scheduleMode === 'later' ? 'bg-brand-500 text-white font-extrabold shadow-sm' : 'bg-gray-50 dark:bg-[#1a1a1a] text-gray-700 dark:text-gray-300 font-bold border border-gray-200 dark:border-white/10'"
                                class="py-3 px-4 rounded-xl text-xs transition-all text-center">
                            📅 Schedule for Later
                        </button>
                    </div>

                    <div x-show="scheduleMode === 'later'" class="grid grid-cols-2 gap-3 pt-2">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Date *</label>
                            <input type="date" name="start_date" x-model="startDate" min="{{ date('Y-m-d') }}" class="w-full px-3 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-bold text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Time *</label>
                            <input type="time" name="start_time" x-model="startTime" class="w-full px-3 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-bold text-gray-900 dark:text-white">
                        </div>
                    </div>

                    <!-- Duration Selection (DriveU Style Options) -->
                    <div class="pt-2 border-t border-gray-100 dark:border-white/10">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">Duration Selection *</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            <div>
                                <label class="block text-[11px] text-gray-500 mb-1 font-semibold">Duration Unit</label>
                                <select name="duration_type" x-model="durationType" class="w-full px-3 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white">
                                    <option value="hourly">Hourly (1–4 hrs standard, 4–8 hrs discount)</option>
                                    <option value="daily">Daily Package (Full Day 8+ hrs)</option>
                                    <option value="weekly">Weekly Service (7+ Days)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] text-gray-500 mb-1 font-semibold">Duration Count</label>
                                <input type="number" name="duration_count" min="1" max="100" x-model="durationCount" class="w-full px-3 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Vehicle Details Card -->
                <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 shadow-sm space-y-4">
                    <h3 class="font-extrabold text-base text-gray-900 dark:text-white">🚘 Vehicle Information</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Make & Model *</label>
                            <input type="text" name="car_make_model" x-model="carMakeModel" required placeholder="e.g. Toyota Camry" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Registration No *</label>
                            <input type="text" name="registration_number" x-model="regNumber" required placeholder="e.g. REG-8899" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Transmission *</label>
                            <select name="transmission" x-model="transmission" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white">
                                <option value="automatic">Automatic</option>
                                <option value="manual">Manual</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 5. Driver Preferences -->
                <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 shadow-sm space-y-4">
                    <h3 class="font-extrabold text-base text-gray-900 dark:text-white">👨‍✈️ Driver Preferences</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Preferred Driver Gender</label>
                            <select name="preferred_gender" x-model="preferredGender" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white">
                                <option value="any">Any Gender</option>
                                <option value="male">Male Driver</option>
                                <option value="female">Female Driver</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Preferred Language</label>
                            <select name="preferred_language" x-model="preferredLanguage" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white">
                                <option value="English">English</option>
                                <option value="Spanish">Spanish</option>
                                <option value="French">French</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Sticky Fare Breakdown & CTA -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 shadow-xl sticky top-24 space-y-6">
                    
                    <div>
                        <span class="text-xs font-extrabold text-brand-500 uppercase tracking-widest block mb-1">Price Estimate</span>
                        <h2 class="text-2xl font-black text-gray-900 dark:text-white">Estimated Fare</h2>
                        <p class="text-xs text-gray-400 mt-1" x-text="priceBreakdown.applied_rate_text || 'Standard Driver Hiring Rates'"></p>
                    </div>

                    <!-- Itemized Price Summary -->
                    <div class="space-y-3 text-xs border-t border-b border-gray-100 dark:border-white/10 py-4">
                        <div class="flex justify-between text-gray-600 dark:text-gray-400">
                            <span>Driver Service Fee:</span>
                            <span class="font-bold text-gray-900 dark:text-white" x-text="priceBreakdown.currency_symbol + Number(priceBreakdown.subtotal || 0).toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between text-gray-600 dark:text-gray-400">
                            <span>Service Fee (5%):</span>
                            <span class="font-bold text-gray-900 dark:text-white" x-text="priceBreakdown.currency_symbol + Number(priceBreakdown.service_fee || 0).toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between text-gray-600 dark:text-gray-400">
                            <span>Taxes (5%):</span>
                            <span class="font-bold text-gray-900 dark:text-white" x-text="priceBreakdown.currency_symbol + Number(priceBreakdown.tax || 0).toFixed(2)"></span>
                        </div>

                        <div class="pt-2 border-t border-gray-100 dark:border-white/10 flex justify-between items-center text-sm font-black">
                            <span class="text-gray-900 dark:text-white">Estimated Total:</span>
                            <span class="text-2xl text-brand-500" x-text="priceBreakdown.currency_symbol + Number(priceBreakdown.total_price || 0).toFixed(2)"></span>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Payment Method *</label>
                        <select name="payment_method" x-model="paymentMethod" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-bold text-gray-900 dark:text-white cursor-pointer">
                            <option value="stripe">💳 Stripe (Credit Card)</option>
                            <option value="momo">📱 Momo Pay</option>
                            <option value="cash">💵 Cash</option>
                            <option value="applepay">🍏 Apple Pay</option>
                        </select>
                    </div>

                    <!-- Card Fillup Information for Stripe -->
                    <x-stripe-card-input modelName="paymentMethod" value="stripe" />

                    <!-- Primary Request CTA -->
                    <button type="submit" class="w-full py-4 bg-brand-500 hover:bg-brand-600 text-white rounded-2xl font-black text-sm transition-all shadow-lg shadow-brand-500/25 cursor-pointer uppercase tracking-wider">
                        🚀 Request Driver Now (<span x-text="priceBreakdown.currency_symbol + Number(priceBreakdown.total_price || 0).toFixed(2)"></span>)
                    </button>

                </div>
            </div>

        </form>

        <!-- Saved Location Setup / Edit Modal (Top-Level Container for Absolute Stacking Order) -->
        <div x-show="showSavedLocationModal" 
             x-transition.opacity
             style="display: none;"
             class="fixed inset-0 z-[9999999] flex items-center justify-center p-4 bg-black/70 backdrop-blur-md">
            <div @click.outside="showSavedLocationModal = false" class="bg-white dark:bg-[#181818] rounded-3xl border border-gray-200 dark:border-white/10 shadow-2xl max-w-md w-full p-6 space-y-4 relative z-[99999999]">
                <div class="flex items-center justify-between">
                    <h3 class="font-extrabold text-base text-gray-900 dark:text-white flex items-center gap-2">
                        <span x-text="editingLabel === 'home' ? '🏠' : '🏢'"></span>
                        <span x-text="(savedLocations[editingLabel] ? 'Edit ' : 'Set ') + (editingLabel === 'home' ? 'Home' : 'Office') + ' Address'"></span>
                    </h3>
                    <button type="button" @click="showSavedLocationModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-sm">✕</button>
                </div>

                <p class="text-xs text-gray-500 dark:text-gray-400">Search and select your <span x-text="editingLabel"></span> address from the map. It will be saved for one-tap booking.</p>

                <div class="relative space-y-2">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Map Address Search</label>
                    <div class="relative">
                        <input type="text" 
                               x-model="modalTempLocation.location"
                               @input.debounce.300ms="searchModalLocation()"
                               @focus="if(modalSuggestions.length > 0) showModalSuggestions = true"
                               @click.outside="showModalSuggestions = false"
                               x-init="initModalAutocomplete($el)"
                               placeholder="Search address, city, landmark..." 
                               class="w-full pl-9 pr-8 py-3 bg-gray-50 dark:bg-[#222] border rounded-xl text-xs font-bold text-gray-900 dark:text-white transition-colors"
                               :class="modalTempLocation.isSelected ? 'border-emerald-500 ring-1 ring-emerald-500/30' : 'border-gray-200 dark:border-white/10'">
                        <span class="absolute left-3 top-3.5 text-amber-500">📍</span>
                        <span x-show="modalTempLocation.isSelected" class="absolute right-3 top-3.5 text-emerald-500 font-bold text-xs" title="Verified Location">✓</span>
                    </div>

                    <!-- Selection Confirmation Badge -->
                    <div x-show="modalTempLocation.isSelected" class="flex items-center gap-1.5 p-2 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/40 rounded-xl text-[11px] font-extrabold text-emerald-700 dark:text-emerald-300">
                        <span class="text-emerald-500 shrink-0">✓ Verified Location:</span>
                        <span class="truncate" x-text="modalTempLocation.location"></span>
                    </div>

                    <!-- Suggestions Dropdown -->
                    <div x-show="showModalSuggestions && modalSuggestions.length > 0"
                         x-transition.opacity
                         class="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-[#1f1f1f] border border-amber-300 dark:border-white/20 rounded-2xl shadow-2xl z-[999999999] overflow-hidden divide-y divide-gray-100 dark:divide-white/5 max-h-56 overflow-y-auto">
                        <template x-for="item in modalSuggestions" :key="item.place_id || item.osm_id">
                            <button type="button" 
                                    @click="selectModalSuggestion(item)" 
                                    class="w-full px-3.5 py-3 text-left text-xs text-gray-800 dark:text-gray-200 hover:bg-amber-50 dark:hover:bg-amber-950/40 flex items-start gap-2.5 transition-colors cursor-pointer">
                                <span class="text-amber-500 shrink-0 mt-0.5">📍</span>
                                <div>
                                    <span class="font-bold block text-xs" x-text="item.display_name"></span>
                                    <span class="text-[10px] text-gray-400 font-normal block" x-text="item.type ? (item.type.toUpperCase() + ' • ' + (item.class || 'location')) : 'Map Location'"></span>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-gray-100 dark:border-white/10">
                    <button type="button" @click="showSavedLocationModal = false" class="px-4 py-2.5 rounded-xl bg-gray-100 dark:bg-[#222] text-gray-700 dark:text-gray-300 font-bold text-xs">Cancel</button>
                    <button type="button" @click="saveLocationFromModal()" :disabled="!modalTempLocation.isSelected" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 disabled:opacity-40 text-white font-extrabold text-xs shadow-md transition-all cursor-pointer">Save & Apply</button>
                </div>
            </div>
        </div>
    </main>

    <!-- Google Places Autocomplete Script for Hire Driver Page -->
    @php
        $gmapsKey = config('services.google_maps.api_key');
        $hasValidKey = !empty($gmapsKey) && !str_contains($gmapsKey, 'AIzaSyDemoKey');
    @endphp

    @if($hasValidKey)
        <script src="https://maps.googleapis.com/maps/api/js?key={{ $gmapsKey }}&libraries=places"></script>
    @endif

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('bookDriverBooking', () => ({
                serviceType: 'Hire Driver',
                scheduleMode: 'now',
                startDate: '{{ date("Y-m-d") }}',
                startTime: '09:00',
                durationType: 'hourly',
                durationCount: 4,
                country: '{{ $selectedCountry ?? "USA" }}',
                driverProfileId: '{{ $driverProfile->id ?? "" }}',
                paymentMethod: 'stripe',
                vehicleSource: 'personal',
                carType: 'Sedan',
                transmission: 'automatic',
                carMakeModel: 'Toyota Camry',
                mfgYear: '2023',
                regNumber: 'REG-8899',
                preferredGender: 'any',
                preferredLanguage: 'English',
                stops: [],
                validationErrorMessage: null,
                savedLocations: { home: null, office: null },
                showSavedLocationModal: false,
                editingLabel: 'home',
                modalTempLocation: { location: '', lat: null, lng: null, place_id: null, isSelected: false },
                modalSuggestions: [],
                showModalSuggestions: false,

                priceBreakdown: {
                    subtotal: 0,
                    service_fee: 0,
                    tax: 0,
                    total_price: 0,
                    currency_symbol: '$',
                    applied_rate_text: ''
                },

                init() {
                    this.updatePrice();
                    this.fetchSavedLocations();
                    this.$watch('country', () => this.updatePrice());
                    this.$watch('durationType', () => this.updatePrice());
                    this.$watch('durationCount', () => this.updatePrice());
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
                        if (this.stops.length < 5) {
                            this.stops.push({
                                id: 'stop_' + Date.now() + '_' + Math.floor(Math.random() * 10000),
                                location: saved.address,
                                lat: saved.latitude ? parseFloat(saved.latitude) : null,
                                lng: saved.longitude ? parseFloat(saved.longitude) : null,
                                place_id: saved.place_id || null,
                                isSelected: true,
                                suggestions: [],
                                showSuggestions: false,
                                validationError: null
                            });
                            this.validationErrorMessage = null;
                        } else {
                            alert('Maximum 5 additional stops allowed.');
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
                        alert('Please select a valid location suggestion from the map dropdown.');
                        return;
                    }
                    try {
                        const csrfToken = '{{ csrf_token() }}';
                        const res = await fetch('/api/user/saved-locations', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
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
                                
                                // Automatically insert the saved location as an additional stop
                                if (this.stops.length < 5) {
                                    this.stops.push({
                                        id: 'stop_' + Date.now() + '_' + Math.floor(Math.random() * 10000),
                                        location: data.location.address,
                                        lat: data.location.latitude ? parseFloat(data.location.latitude) : null,
                                        lng: data.location.longitude ? parseFloat(data.location.longitude) : null,
                                        place_id: data.location.place_id || null,
                                        isSelected: true,
                                        suggestions: [],
                                        showSuggestions: false,
                                        validationError: null
                                    });
                                }
                            }
                        } else {
                            alert('Failed to save location. Please try again.');
                        }
                    } catch (e) {
                        console.error('Error saving location:', e);
                        alert('An error occurred while saving the location.');
                    }
                },

                addStop() {
                    if (this.stops.length < 5) {
                        this.stops.push({
                            id: 'stop_' + Date.now() + '_' + Math.floor(Math.random() * 10000),
                            location: '',
                            lat: null,
                            lng: null,
                            place_id: null,
                            isSelected: false,
                            suggestions: [],
                            showSuggestions: false,
                            validationError: null
                        });
                        this.validationErrorMessage = null;
                    }
                },

                removeStop(index) {
                    this.stops.splice(index, 1);
                    this.validationErrorMessage = null;
                },

                onStopInputChange(stop) {
                    stop.lat = null;
                    stop.lng = null;
                    stop.place_id = null;
                    stop.isSelected = false;
                    stop.validationError = null;
                    this.validationErrorMessage = null;
                    this.searchStopLocation(stop);
                },

                selectStopSuggestion(stop, item) {
                    stop.location = item.display_name;
                    stop.lat = parseFloat(item.lat);
                    stop.lng = parseFloat(item.lon);
                    stop.place_id = item.place_id ? String(item.place_id) : null;
                    stop.isSelected = true;
                    stop.validationError = null;
                    stop.showSuggestions = false;
                    this.validationErrorMessage = null;
                },

                async searchStopLocation(stop) {
                    if (!stop.location || stop.location.trim().length < 3) {
                        stop.suggestions = [];
                        stop.showSuggestions = false;
                        return;
                    }
                    try {
                        const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(stop.location)}&limit=5`);
                        if (res.ok) {
                            const data = await res.json();
                            stop.suggestions = data;
                            stop.showSuggestions = data.length > 0;
                        }
                    } catch (e) {
                        console.warn('Map search error:', e);
                    }
                },

                validateForm(e) {
                    this.validationErrorMessage = null;
                    for (let i = 0; i < this.stops.length; i++) {
                        const s = this.stops[i];
                        if (!s.location || s.location.trim().length === 0) {
                            s.validationError = `Additional Stop #${i + 1} location is required.`;
                            this.validationErrorMessage = `Additional Stop #${i + 1} location cannot be empty. Please search and select a valid map location.`;
                            e.preventDefault();
                            return false;
                        }
                        if (!s.isSelected) {
                            s.validationError = `Please select a location suggestion from the map dropdown.`;
                            this.validationErrorMessage = `Additional Stop #${i + 1} ("${s.location}") was not selected from map suggestions. Please pick a location from the dropdown list.`;
                            e.preventDefault();
                            return false;
                        }
                    }
                    return true;
                },

                async updatePrice() {
                    try {
                        const res = await fetch('/hire-driver/calculate-price', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                driver_profile_id: this.driverProfileId || 1,
                                duration_type: this.durationType,
                                duration_count: this.durationCount,
                                country: this.country
                            })
                        });
                        if (res.ok) {
                            this.priceBreakdown = await res.json();
                        }
                    } catch (e) {
                        console.error(e);
                    }
                }
            }));
        });
        function initModalAutocomplete(el) {
            if (window.google && google.maps && google.maps.places) {
                try {
                    const ac = new google.maps.places.Autocomplete(el);
                    ac.addListener('place_changed', () => {
                        const place = ac.getPlace();
                        const component = Alpine.$data(el.closest('[x-data]'));
                        if (component) {
                            if (place.geometry && place.geometry.location) {
                                component.modalTempLocation.lat = place.geometry.location.lat();
                                component.modalTempLocation.lng = place.geometry.location.lng();
                            }
                            if (place.place_id) component.modalTempLocation.place_id = place.place_id;
                            const addr = place.formatted_address || place.name;
                            if (addr) {
                                component.modalTempLocation.location = addr;
                                component.modalTempLocation.isSelected = true;
                                el.value = addr;
                            }
                        }
                    });
                } catch (e) {}
            }
        }

        function initStopAutocomplete(el, stopObj) {
            if (window.google && google.maps && google.maps.places) {
                try {
                    const ac = new google.maps.places.Autocomplete(el);
                    ac.addListener('place_changed', () => {
                        const place = ac.getPlace();
                        if (place.geometry && place.geometry.location) {
                            stopObj.lat = place.geometry.location.lat();
                            stopObj.lng = place.geometry.location.lng();
                        }
                        if (place.place_id) {
                            stopObj.place_id = place.place_id;
                        }
                        const addr = place.formatted_address || place.name;
                        if (addr) {
                            stopObj.location = addr;
                            stopObj.isSelected = true;
                            stopObj.validationError = null;
                            el.value = addr;
                            el.dispatchEvent(new Event('input'));
                        }
                    });
                } catch (e) {}
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
            const pInput = document.getElementById("pickup_location_rmc");
            const dInput = document.getElementById("dropoff_location_rmc");
            const locBtn = document.getElementById("use_my_location_btn_rmc");
            const pLatInput = document.getElementById("pickup_lat_input");
            const pLngInput = document.getElementById("pickup_lng_input");

            @if($hasValidKey)
                if (window.google && google.maps && google.maps.places) {
                    try {
                        if (pInput) {
                            const acP = new google.maps.places.Autocomplete(pInput);
                            acP.addListener('place_changed', () => {
                                const place = acP.getPlace();
                                if (place.geometry && place.geometry.location) {
                                    if (pLatInput) pLatInput.value = place.geometry.location.lat();
                                    if (pLngInput) pLngInput.value = place.geometry.location.lng();
                                }
                            });
                        }
                        if (dInput) new google.maps.places.Autocomplete(dInput);
                    } catch (e) {}
                }
            @endif

            if (locBtn && pInput) {
                locBtn.addEventListener("click", () => {
                    if (!navigator.geolocation) {
                        alert("Geolocation is not supported by your browser.");
                        return;
                    }
                    locBtn.disabled = true;
                    locBtn.innerText = "Locating...";

                    navigator.geolocation.getCurrentPosition(
                        async (pos) => {
                            if (pLatInput) pLatInput.value = pos.coords.latitude;
                            if (pLngInput) pLngInput.value = pos.coords.longitude;

                            try {
                                const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${pos.coords.latitude}&lon=${pos.coords.longitude}`);
                                if (res.ok) {
                                    const data = await res.json();
                                    if (data && data.display_name) pInput.value = data.display_name;
                                }
                            } catch (e) {}
                            locBtn.disabled = false;
                            locBtn.innerText = "📍 Use My Location";
                        },
                        () => {
                            locBtn.disabled = false;
                            locBtn.innerText = "📍 Use My Location";
                            alert("Unable to detect location automatically.");
                        }
                    );
                });
            }
        });
    </script>
    <x-stripe-modal serviceType="driver_booking" />
</x-layout>
