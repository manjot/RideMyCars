<x-layout theme="theme-hire">
    <x-slot:title>Book Driver — {{ $driverProfile->user->name }}</x-slot>

    <main class="flex-1 max-w-5xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-12"
          x-data="{
              serviceCategory: 'private',
              country: '{{ $selectedCountry }}',
              durationType: 'hourly',
              durationCount: 3,
              driverId: {{ $driverProfile->id }},
              paymentMethod: 'momo',
              priceBreakdown: {
                  subtotal: 0,
                  service_fee: 0,
                  tax: 0,
                  total_price: 0,
                  currency_symbol: '$',
                  applied_rate_text: ''
              },
              countriesConfig: {{ Js::from($countries) }},
              get currentPaymentMethods() {
                  return this.countriesConfig[this.country]?.payment_methods || [];
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
                              driver_profile_id: this.driverId,
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
          }"
          x-init="updatePrice(); $watch('country', () => { paymentMethod = currentPaymentMethods[1]?.id || 'momo'; updatePrice(); }); $watch('durationType', () => updatePrice()); $watch('durationCount', () => updatePrice());">

        <!-- Header -->
        <div class="mb-8 text-center md:text-left">
            <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white mb-2">Book Driver Service</h1>
            <p class="text-gray-500 dark:text-gray-400">Complete your details to hire {{ $driverProfile->user->name }}.</p>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800/30 text-rose-700 dark:text-rose-300 text-sm font-semibold flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="flex flex-col lg:flex-row gap-10">
            
            <!-- Left Side: Booking Form -->
            <div class="w-full lg:w-[60%]">
                <form action="/hire-driver/book" method="POST" class="space-y-8 bg-white dark:bg-[#111] p-6 md:p-8 rounded-3xl border border-gray-200 dark:border-white/10 shadow-sm">
                    @csrf
                    <input type="hidden" name="driver_profile_id" value="{{ $driverProfile->id }}">
                    <input type="hidden" name="vehicle_id" value="{{ request('vehicle_id') }}">
                    <input type="hidden" name="service_category" x-model="serviceCategory">
                    <input type="hidden" name="country" x-model="country">

                    <!-- Country Switcher -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Select Country / Region</label>
                        <select x-model="country" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white font-bold cursor-pointer">
                            <option value="USA">🇺🇸 United States (USD $)</option>
                            <option value="Ghana">🇬🇭 Ghana (GHS GH₵)</option>
                            <option value="Nigeria">🇳🇬 Nigeria (NGN ₦)</option>
                            <option value="South Africa">🇿🇦 South Africa (ZAR R)</option>
                        </select>
                    </div>

                    <!-- Category Selector Tabs -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Service Category</label>
                        <div class="grid grid-cols-2 gap-3 p-1.5 bg-gray-100 dark:bg-[#1a1a1a] rounded-2xl">
                            <button type="button" @click="serviceCategory = 'private'"
                                    :class="serviceCategory === 'private' ? 'bg-white dark:bg-[#111] text-brand-600 dark:text-brand-400 shadow-sm font-bold' : 'text-gray-500 font-medium'"
                                    class="py-3 px-4 rounded-xl text-sm transition-all text-center">
                                🚘 Private Driver Hiring
                            </button>
                            <button type="button" @click="serviceCategory = 'commercial'"
                                    :class="serviceCategory === 'commercial' ? 'bg-white dark:bg-[#111] text-brand-600 dark:text-brand-400 shadow-sm font-bold' : 'text-gray-500 font-medium'"
                                    class="py-3 px-4 rounded-xl text-sm transition-all text-center">
                                🚛 Commercial Service
                            </button>
                        </div>
                    </div>

                    <!-- Private Category Fields -->
                    <div x-show="serviceCategory === 'private'" class="space-y-5 pt-2 border-t border-gray-100 dark:border-white/10">
                        <h3 class="font-bold text-gray-900 dark:text-white text-base">Vehicle Information</h3>
                        
                        <!-- Vehicle Source (Requirement #4) -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Vehicle Being Driven *</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl cursor-pointer hover:border-brand-500 transition-colors">
                                    <input type="radio" name="vehicle_source" value="personal" class="w-4 h-4 text-brand-500 focus:ring-brand-500" checked>
                                    <span class="text-xs font-bold text-gray-900 dark:text-white">🚘 Personal Vehicle</span>
                                </label>
                                <label class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl cursor-pointer hover:border-brand-500 transition-colors">
                                    <input type="radio" name="vehicle_source" value="rental" class="w-4 h-4 text-brand-500 focus:ring-brand-500">
                                    <span class="text-xs font-bold text-gray-900 dark:text-white">🔑 Rental Fleet Vehicle</span>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Car Type *</label>
                                <select name="car_type" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white text-sm">
                                    <option value="Sedan">Sedan</option>
                                    <option value="SUV">SUV / Crossover</option>
                                    <option value="Luxury">Luxury Car</option>
                                    <option value="Van">Van / Minibus</option>
                                    <option value="Pickup Truck">Pickup Truck</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Transmission *</label>
                                <select name="transmission" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white text-sm">
                                    <option value="automatic">Automatic</option>
                                    <option value="manual">Manual</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Make & Model</label>
                                <input type="text" name="car_make_model" placeholder="e.g. Toyota Camry" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Mfg Year</label>
                                <input type="text" name="manufacturing_year" placeholder="e.g. 2022" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Registration No *</label>
                                <input type="text" name="registration_number" placeholder="e.g. ABC-1234" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white text-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Commercial Category Fields -->
                    <div x-show="serviceCategory === 'commercial'" class="space-y-5 pt-2 border-t border-gray-100 dark:border-white/10">
                        <h3 class="font-bold text-gray-900 dark:text-white text-base">Commercial Service Requirements</h3>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Service Type *</label>
                            <select name="commercial_service_type" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white text-sm">
                                <option value="Cargo Transport">Cargo & Freight Transport</option>
                                <option value="Passenger Shuttle">Corporate / Group Passenger Shuttle</option>
                                <option value="VIP Escort">VIP & Executive Chauffeur</option>
                                <option value="Heavy Vehicle">Heavy Machinery / Truck Driving</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Cargo / Job Specifications</label>
                            <textarea name="cargo_details" rows="3" placeholder="Describe cargo weight, commercial vehicle model, or special requirements..." class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white text-sm resize-none"></textarea>
                        </div>
                    </div>

                    <!-- Schedule & Duration -->
                    <div class="space-y-5 pt-4 border-t border-gray-100 dark:border-white/10">
                        <h3 class="font-bold text-gray-900 dark:text-white text-base">Schedule & Duration</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Start Date *</label>
                                <input type="date" name="start_date" required min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Start Time *</label>
                                <input type="time" name="start_time" required value="09:00" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white text-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Duration Unit *</label>
                                <select name="duration_type" x-model="durationType" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white text-sm font-semibold">
                                    <option value="hourly">Hourly (1–4 hrs @ standard, 4–8 hrs @ discount)</option>
                                    <option value="daily">Daily (Min 1 day for 8+ hrs)</option>
                                    <option value="weekly">Weekly (7+ days)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Duration Count *</label>
                                <input type="number" name="duration_count" min="1" max="100" x-model="durationCount" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white text-sm font-bold">
                            </div>
                        </div>
                    </div>

                    <!-- Pickup & Dropoff -->
                    <div class="space-y-4 pt-4 border-t border-gray-100 dark:border-white/10">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Pickup Address / Location *</label>
                                <button type="button" id="use_my_location_btn_book" class="text-brand-500 hover:text-brand-600 text-xs font-semibold flex items-center gap-1 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/></svg>
                                    Use my location
                                </button>
                            </div>
                            <input type="text" id="pickup_location_input" name="pickup_location" required placeholder="Enter pickup address..." class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Destination Address (Optional)</label>
                            <input type="text" id="dropoff_location_input" name="dropoff_location" placeholder="Enter destination..." class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white text-sm">
                        </div>
                    </div>

                    <!-- Country-Specific Payment Method Selection -->
                    <div class="space-y-4 pt-4 border-t border-gray-100 dark:border-white/10">
                        <h2 class="text-xl font-bold text-[#0F172A] dark:text-white mb-4" style="color: #0F172A;">Payment Method for <span x-text="country">USA</span></h2>
                        
                        <div class="grid grid-cols-1 gap-[14px]">
                            <template x-for="pm in currentPaymentMethods" :key="pm.id">
                                <label @click="paymentMethod = pm.id"
                                       :style="paymentMethod === pm.id ? 'border: 2px solid #FBBF24 !important; background-color: #FFFFFF !important;' : 'border: 2px solid #E5E7EB !important; background-color: #FFFFFF !important;'"
                                       class="py-4 px-4 rounded-xl cursor-pointer flex items-center gap-4 transition-all select-none shadow-sm">
                                    <input type="radio" name="payment_method" :value="pm.id" x-model="paymentMethod" class="sr-only">
                                    
                                    <!-- Universal Single Icon Container -->
                                    <div :style="pm.id === 'stripe' || pm.icon === 'stripe' || pm.id === 'card' || pm.icon === 'card' ? 'background-color: #635BFF !important;' : (pm.id === 'momo' || pm.icon === 'momo' ? 'background-color: #000000 !important;' : 'background-color: #F1F5F9 !important;')"
                                         class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 shadow-sm overflow-hidden">
                                        
                                        <!-- Stripe -->
                                        <span x-show="pm.id === 'stripe' || pm.icon === 'stripe' || pm.id === 'card' || pm.icon === 'card'" style="color: #FFFFFF !important;" class="text-white text-base font-black tracking-tighter">stripe</span>

                                        <!-- Momo Pay -->
                                        <div x-show="pm.id === 'momo' || pm.icon === 'momo'" class="flex flex-col items-center justify-center p-1">
                                            <svg viewBox="0 0 100 80" class="w-5 h-4.5 fill-white mb-0.5">
                                                <path d="M56 4 C70 18, 76 38, 70 54 C67 38, 62 20, 56 4 Z" />
                                                <path d="M26 46 C42 26, 56 16, 62 14 C66 40, 52 56, 26 46 Z" />
                                            </svg>
                                            <span style="color: #FFFFFF !important;" class="text-[6.5px] font-black tracking-tighter leading-none block uppercase">MoMo</span>
                                        </div>

                                        <!-- Cash -->
                                        <span x-show="pm.id === 'cash' || pm.icon === 'cash'" style="color: #475569 !important;" class="font-bold text-lg">$</span>

                                        <!-- Apple Pay -->
                                        <svg x-show="pm.id === 'applepay' || pm.icon === 'apple'" xmlns="http://www.w3.org/2000/svg" style="color: #0F172A !important;" class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 4.02c.62-.76 1.05-1.81.93-2.87-.9.04-2.01.6-2.65 1.34-.57.65-1.08 1.73-.94 2.77 1.01.08 2.04-.48 2.66-1.24z"/></svg>

                                        <!-- Fallback -->
                                        <span x-show="!['stripe', 'card', 'momo', 'cash', 'applepay'].includes(pm.id) && !['stripe', 'card', 'momo', 'cash', 'apple'].includes(pm.icon)" style="color: #475569 !important;" class="font-bold text-sm" x-text="pm.name.charAt(0)"></span>
                                    </div>

                                    <span style="color: #0F172A !important;" class="text-base font-semibold truncate" x-text="pm.name"></span>
                                </label>
                            </template>
                        </div>
                    </div>

                    <button type="submit" style="background-color: #FBBF24 !important; color: #FFFFFF !important;" class="w-full py-4 bg-amber-400 hover:bg-amber-500 text-white font-bold text-base rounded-xl transition-all shadow-md shadow-amber-500/20 cursor-pointer">
                        Confirm & Complete Booking
                    </button>

                </form>
            </div>

            <!-- Right Side: Driver Summary & Price Breakdown -->
            <div class="w-full lg:w-[40%] space-y-6">
                
                <!-- Driver Info Card -->
                <div class="bg-white dark:bg-[#111] p-6 rounded-3xl border border-gray-200 dark:border-white/10 shadow-sm flex items-center gap-4 relative overflow-hidden">
                    <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-[#222] shrink-0 overflow-hidden relative border-2 border-brand-500">
                        <img src="{{ $driverProfile->photo_url }}" alt="{{ $driverProfile->user->name }}" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-lg text-gray-900 dark:text-white">{{ $driverProfile->user->name }}</h3>
                            @if($driverProfile->verification_status === 'verified')
                                <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400 border border-green-200 dark:border-green-800/30">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                    Verified Driver
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-500 mt-1">
                            <span class="text-amber-500 font-bold">★ {{ $driverProfile->rating }}</span>
                            <span>• {{ $driverProfile->experience_years ?? 3 }} yrs exp</span>
                            <span>• {{ $driverProfile->country ?? 'USA' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Price Breakdown Card -->
                <div class="bg-white dark:bg-[#111] p-6 rounded-3xl border border-gray-200 dark:border-white/10 shadow-sm space-y-4">
                    <h3 class="font-bold text-gray-900 dark:text-white text-lg border-b border-gray-100 dark:border-white/10 pb-3">Price Breakdown</h3>

                    <div class="text-xs font-medium text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/20 p-3 rounded-xl" x-text="priceBreakdown.applied_rate_text"></div>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-gray-600 dark:text-gray-400">
                            <span>Driver Charge</span>
                            <span class="font-semibold text-gray-900 dark:text-white" x-text="priceBreakdown.currency_symbol + Number(priceBreakdown.subtotal || 0).toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between text-gray-600 dark:text-gray-400">
                            <span>Service Fee (5%)</span>
                            <span class="font-semibold text-gray-900 dark:text-white" x-text="priceBreakdown.currency_symbol + Number(priceBreakdown.service_fee || 0).toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between text-gray-600 dark:text-gray-400">
                            <span>Taxes (5%)</span>
                            <span class="font-semibold text-gray-900 dark:text-white" x-text="priceBreakdown.currency_symbol + Number(priceBreakdown.tax || 0).toFixed(2)"></span>
                        </div>

                        <div class="pt-3 border-t border-gray-100 dark:border-white/10 flex justify-between items-center text-base">
                            <span class="font-bold text-gray-900 dark:text-white">Total Amount</span>
                            <span class="font-extrabold text-2xl text-brand-500" x-text="priceBreakdown.currency_symbol + Number(priceBreakdown.total_price || 0).toFixed(2)"></span>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <!-- Google Maps Places Autocomplete & Geolocation Integration -->
    @php
        $gmapsKey = env('GOOGLE_MAPS_API_KEY');
        $hasValidKey = !empty($gmapsKey) && !str_contains($gmapsKey, 'AIzaSyDemoKey');
    @endphp

    @if($hasValidKey)
        <script src="https://maps.googleapis.com/maps/api/js?key={{ $gmapsKey }}&libraries=places"></script>
    @endif

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const pickupInput = document.getElementById("pickup_location_input");
            const dropoffInput = document.getElementById("dropoff_location_input");

            @if($hasValidKey)
                if (window.google && google.maps && google.maps.places) {
                    try {
                        if (pickupInput) new google.maps.places.Autocomplete(pickupInput);
                        if (dropoffInput) new google.maps.places.Autocomplete(dropoffInput);
                    } catch (e) {
                        console.warn("Google Places Autocomplete initialization bypassed:", e);
                    }
                }
            @endif

            const locBtn = document.getElementById("use_my_location_btn_book");
            if (locBtn && pickupInput) {
                locBtn.addEventListener("click", () => {
                    if (!navigator.geolocation) {
                        alert("Geolocation is not supported by your browser.");
                        return;
                    }

                    const originalHTML = locBtn.innerHTML;
                    locBtn.disabled = true;
                    locBtn.innerHTML = `
                        <svg class="animate-spin h-3 w-3 text-brand-500 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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
