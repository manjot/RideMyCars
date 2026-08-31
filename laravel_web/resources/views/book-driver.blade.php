<x-layout theme="theme-hire">
    <x-slot:title>Hire a Driver — Professional Driver Hiring | RideMyCars</x-slot>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10"
          x-data="{
              serviceType: 'Hire Driver', // Hire Driver, Hourly Driver, Daily Driver, Outstation, Package
              scheduleMode: 'now', // now, later
              startDate: '{{ date('Y-m-d') }}',
              startTime: '09:00',
              durationType: 'hourly', // hourly, daily, weekly
              durationCount: 4,
              country: '{{ $selectedCountry ?? 'USA' }}',
              driverProfileId: '{{ $driverProfile->id ?? '' }}',
              paymentMethod: 'stripe',
              stops: [],
              vehicleSource: 'personal',
              carType: 'Sedan',
              transmission: 'automatic',
              carMakeModel: 'Toyota Camry',
              mfgYear: '2023',
              regNumber: 'REG-8899',
              preferredGender: 'any',
              preferredLanguage: 'English',
              
              priceBreakdown: {
                  subtotal: 0,
                  service_fee: 0,
                  tax: 0,
                  total_price: 0,
                  currency_symbol: '$',
                  applied_rate_text: ''
              },

              addStop() {
                  if (this.stops.length < 3) {
                      this.stops.push({ location: '' });
                  }
              },
              removeStop(index) {
                  this.stops.splice(index, 1);
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
          }"
          x-init="updatePrice(); $watch('country', () => updatePrice()); $watch('durationType', () => updatePrice()); $watch('durationCount', () => updatePrice());">

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

        <form action="/hire-driver/book" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
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
                        <template x-for="(stop, index) in stops" :key="index">
                            <div class="relative flex items-center gap-2">
                                <div class="w-full relative">
                                    <input type="text" :name="`additional_stops[${index}]`" x-model="stop.location" placeholder="Additional Stop Location..." class="w-full pl-9 pr-4 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-amber-200 dark:border-amber-900/30 rounded-xl text-xs font-bold text-gray-900 dark:text-white">
                                    <span class="absolute left-3 top-2.5 text-amber-500">📍</span>
                                </div>
                                <button type="button" @click="removeStop(index)" class="p-2 text-rose-500 hover:bg-rose-50 rounded-xl transition-colors shrink-0" title="Remove Stop">
                                    ✕
                                </button>
                            </div>
                        </template>

                        <button type="button" @click="addStop()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 font-extrabold text-xs transition-colors border border-amber-200 dark:border-amber-800/30">
                            + Add Additional Stop
                        </button>
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
