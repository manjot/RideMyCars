<x-layout theme="theme-delivery">
    <x-slot:title>Package Delivery — RideMyCars Express Parcel Dispatch</x-slot>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10"
          x-data="{
              currentStep: 1,
              pickupLocation: '{{ $pickup ?? '' }}',
              dropoffLocation: '{{ $dropoff ?? '' }}',
              pickupLat: null,
              pickupLng: null,
              dropoffLat: null,
              dropoffLng: null,
              
              deliveryType: 'Hyperlocal', // Hyperlocal (Base), Scheduled, Same Day, Express, Instant
              scheduleMode: 'now', // now, later
              pickupDate: '{{ date('Y-m-d') }}',
              pickupTime: '09:00',

              senderName: '{{ auth()->user()->name ?? 'Jane Sender' }}',
              senderPhone: '{{ auth()->user()->phone ?? '+1 555 019 2831' }}',
              senderAddress: '',
              
              recipientName: 'Robert Johnson',
              recipientPhone: '+1 555 992 4810',
              recipientAddress: '',
              deliveryInstructions: '',

              packageCategory: 'Documents', // Documents, Clothing, Electronics, Household items, Office supplies, Personal belongings
              packageDescription: 'Important Legal Contracts & Office Supplies',
              packageSize: 'Small', // Small, Medium, Large
              packageWeight: 1.5,
              quantity: 1,
              declaredValue: 150,
              specialHandling: ['signature_required'],

              paymentMethod: 'stripe',

              priceBreakdown: {
                  subtotal: 0,
                  service_fee: 0,
                  tax: 0,
                  total_price: 0,
                  currency_symbol: '$'
              },

              async updatePrice() {
                  try {
                      const res = await fetch('/delivery/calculate-price', {
                          method: 'POST',
                          headers: {
                              'Content-Type': 'application/json',
                              'X-CSRF-TOKEN': '{{ csrf_token() }}'
                          },
                          body: JSON.stringify({
                              pickup_lat: this.pickupLat,
                              pickup_lng: this.pickupLng,
                              dropoff_lat: this.dropoffLat,
                              dropoff_lng: this.dropoffLng,
                              delivery_type: this.deliveryType,
                              package_size: this.packageSize,
                              package_weight_kg: this.packageWeight
                          })
                      });
                      if (res.ok) {
                          this.priceBreakdown = await res.json();
                      }
                  } catch (e) {
                      console.error(e);
                  }
              },
              toggleHandling(val) {
                  const idx = this.specialHandling.indexOf(val);
                  if (idx > -1) {
                      this.specialHandling.splice(idx, 1);
                  } else {
                      this.specialHandling.push(val);
                  }
              }
          }"
          x-init="updatePrice(); $watch('deliveryType', () => updatePrice()); $watch('packageSize', () => updatePrice()); $watch('packageWeight', () => updatePrice());">

        <!-- Category Banner Component -->
        <x-category-banner category="Delivery" />

        <!-- Page Header -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="px-3 py-1 rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 font-extrabold text-xs uppercase tracking-wider border border-amber-200 dark:border-amber-800/30">RideMyCars Parcel Dispatch</span>
                <h1 class="text-3xl md:text-4xl font-black text-gray-900 dark:text-white mt-1 tracking-tight">On-Demand & Scheduled Parcel Delivery</h1>
                <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Fast, secure door-to-door courier delivery for documents, electronics, supplies & personal items.</p>
            </div>
            <a href="/admin/package-delivery-tracker" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-extrabold text-xs rounded-2xl shadow-md transition-all shrink-0">
                <span>🚚</span>
                <span>Live Courier Tracker (Ops Dashboard)</span>
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800/30 text-emerald-800 dark:text-emerald-200 text-xs font-bold flex items-center gap-2">
                <span>📦 {{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800/30 text-rose-800 dark:text-rose-200 text-xs font-bold space-y-1">
                @foreach($errors->all() as $error)
                    <p>• {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- 6-Step Wizard Navigation Tabs -->
        <div class="mb-8 overflow-x-auto pb-2">
            <div class="flex items-center gap-2 min-w-max bg-white dark:bg-[#111] p-2 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm text-xs font-extrabold">
                <button type="button" @click="currentStep = 1" :class="currentStep === 1 ? 'bg-amber-500 text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'" class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-1.5">
                    <span>1.</span> 📍 Pickup & Drop
                </button>
                <span class="text-gray-300 dark:text-gray-700">→</span>
                <button type="button" @click="currentStep = 2" :class="currentStep === 2 ? 'bg-amber-500 text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'" class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-1.5">
                    <span>2.</span> ⏱️ Delivery Type
                </button>
                <span class="text-gray-300 dark:text-gray-700">→</span>
                <button type="button" @click="currentStep = 3" :class="currentStep === 3 ? 'bg-amber-500 text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'" class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-1.5">
                    <span>3.</span> 👤 Sender & Recipient
                </button>
                <span class="text-gray-300 dark:text-gray-700">→</span>
                <button type="button" @click="currentStep = 4" :class="currentStep === 4 ? 'bg-amber-500 text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'" class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-1.5">
                    <span>4.</span> 📦 Package Specs
                </button>
                <span class="text-gray-300 dark:text-gray-700">→</span>
                <button type="button" @click="currentStep = 5" :class="currentStep === 5 ? 'bg-amber-500 text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'" class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-1.5">
                    <span>5.</span> 💳 Price & Payment
                </button>
                <span class="text-gray-300 dark:text-gray-700">→</span>
                <button type="button" @click="currentStep = 6" :class="currentStep === 6 ? 'bg-amber-500 text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'" class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-1.5">
                    <span>6.</span> 🚀 Confirmation
                </button>
            </div>
        </div>

        <form action="/delivery/book" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            @csrf
            <input type="hidden" name="pickup_lat" x-model="pickupLat" id="pickup_lat_input">
            <input type="hidden" name="pickup_lng" x-model="pickupLng" id="pickup_lng_input">
            <input type="hidden" name="dropoff_lat" x-model="dropoffLat" id="dropoff_lat_input">
            <input type="hidden" name="dropoff_lng" x-model="dropoffLng" id="dropoff_lng_input">

            <!-- Left & Middle: Step Form Container -->
            <div class="lg:col-span-2 space-y-6">

                <!-- STEP 1: PICKUP & DROP-OFF -->
                <div x-show="currentStep === 1" class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 md:p-8 shadow-sm space-y-6">
                    <div>
                        <h2 class="text-xl font-black text-gray-900 dark:text-white">STEP 1: Pickup & Destination Locations</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Specify where your parcel should be picked up and delivered.</p>
                    </div>

                    <!-- Pickup Address -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Pickup Address *</label>
                            <button type="button" id="use_my_location_btn_delivery" class="text-amber-500 hover:text-amber-600 text-xs font-extrabold flex items-center gap-1 transition-colors">
                                📍 Use My Location
                            </button>
                        </div>
                        <div class="relative">
                            <input type="text" id="pickup_location_input" name="pickup_location" x-model="pickupLocation" required placeholder="Enter street address, building, or landmark..." class="w-full px-4 py-3.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-xs font-bold text-gray-900 dark:text-white">
                        </div>
                    </div>

                    <!-- Drop-off Address -->
                    <div class="space-y-2">
                        <label class="block text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Drop-off / Destination Address *</label>
                        <div class="relative">
                            <input type="text" id="dropoff_location_input" name="dropoff_location" x-model="dropoffLocation" required placeholder="Enter recipient delivery address..." class="w-full px-4 py-3.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-xs font-bold text-gray-900 dark:text-white">
                        </div>
                    </div>

                    <!-- Map Preview Box -->
                    <div class="bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl h-56 overflow-hidden relative">
                        <div id="map" class="w-full h-full"></div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="button" @click="currentStep = 2" class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs rounded-xl shadow-md transition-all">
                            Next: Delivery Type & Schedule →
                        </button>
                    </div>
                </div>

                <!-- STEP 2: DELIVERY TYPE & SCHEDULE -->
                <div x-show="currentStep === 2" class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 md:p-8 shadow-sm space-y-6">
                    <div>
                        <h2 class="text-xl font-black text-gray-900 dark:text-white">STEP 2: Delivery Type & Schedule</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Select dispatch speed and schedule window.</p>
                    </div>

                    <!-- Delivery Type Options (Three Parcel Style) -->
                    <div class="space-y-3">
                        <label class="block text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Delivery Speed Option *</label>
                        <input type="hidden" name="delivery_type" x-model="deliveryType">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs font-bold">
                            <template x-for="dt in [
                                { name: 'Hyperlocal', desc: '🛵 City Local Bike Courier', fee: '+$0.00' },
                                { name: 'Scheduled', desc: '🕒 Pick your exact time window', fee: '+$2.00' },
                                { name: 'Same Day', desc: '📅 Delivered by end of today', fee: '+$4.00' },
                                { name: 'Express', desc: '🚀 Priority Direct Route (< 2 hrs)', fee: '+$8.00' },
                                { name: 'Instant', desc: '⚡ Immediate Courier Pickup (~30 mins)', fee: '+$10.00' }
                            ]" :key="dt.name">
                                <div @click="deliveryType = dt.name"
                                     :class="deliveryType === dt.name ? 'border-amber-500 bg-amber-50/40 dark:bg-amber-950/20' : 'border-gray-200 dark:border-white/10 hover:border-amber-300'"
                                     class="border-2 rounded-2xl p-4 cursor-pointer transition-all flex items-start justify-between gap-2">
                                    <div>
                                        <h4 class="font-extrabold text-sm text-gray-900 dark:text-white" x-text="dt.name"></h4>
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 font-medium mt-0.5" x-text="dt.desc"></p>
                                    </div>
                                    <span class="text-xs font-black text-amber-600 dark:text-amber-400" x-text="dt.fee"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Now vs Schedule Later -->
                    <div class="space-y-3 pt-3 border-t border-gray-100 dark:border-white/10">
                        <label class="block text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Dispatch Schedule *</label>
                        <input type="hidden" name="schedule_mode" x-model="scheduleMode">

                        <div class="grid grid-cols-2 gap-3">
                            <button type="button" @click="scheduleMode = 'now'"
                                    :class="scheduleMode === 'now' ? 'bg-amber-500 text-white font-extrabold shadow-sm' : 'bg-gray-50 dark:bg-[#1a1a1a] text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-white/10 font-bold'"
                                    class="py-3 px-4 rounded-xl text-xs transition-all text-center">
                                ⚡ Deliver Now (Immediate)
                            </button>
                            <button type="button" @click="scheduleMode = 'later'"
                                    :class="scheduleMode === 'later' ? 'bg-amber-500 text-white font-extrabold shadow-sm' : 'bg-gray-50 dark:bg-[#1a1a1a] text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-white/10 font-bold'"
                                    class="py-3 px-4 rounded-xl text-xs transition-all text-center">
                                📅 Schedule for Later
                            </button>
                        </div>

                        <div x-show="scheduleMode === 'later'" class="grid grid-cols-2 gap-3 pt-2">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Pickup Date *</label>
                                <input type="date" name="pickup_date" x-model="pickupDate" min="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-bold text-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Pickup Time *</label>
                                <input type="time" name="pickup_time" x-model="pickupTime" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-bold text-gray-900 dark:text-white">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between pt-2">
                        <button type="button" @click="currentStep = 1" class="px-5 py-2.5 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 font-extrabold text-xs rounded-xl">
                            ← Back
                        </button>
                        <button type="button" @click="currentStep = 3" class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs rounded-xl shadow-md">
                            Next: Sender & Recipient →
                        </button>
                    </div>
                </div>

                <!-- STEP 3: SENDER & RECIPIENT -->
                <div x-show="currentStep === 3" class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 md:p-8 shadow-sm space-y-6">
                    <div>
                        <h2 class="text-xl font-black text-gray-900 dark:text-white">STEP 3: Sender & Recipient Details</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Provide contact details for parcel pickup & delivery notification.</p>
                    </div>

                    <!-- Sender Box -->
                    <div class="p-4 bg-gray-50 dark:bg-[#1a1a1a] rounded-2xl border border-gray-200 dark:border-white/10 space-y-3">
                        <h3 class="font-extrabold text-sm text-gray-900 dark:text-white uppercase tracking-wider">📤 Sender Information</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            <div>
                                <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Sender Full Name *</label>
                                <input type="text" name="sender_name" x-model="senderName" required class="w-full px-3.5 py-2.5 bg-white dark:bg-[#111] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Sender Phone Number *</label>
                                <input type="tel" name="sender_phone" x-model="senderPhone" required class="w-full px-3.5 py-2.5 bg-white dark:bg-[#111] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white">
                            </div>
                        </div>
                    </div>

                    <!-- Recipient Box -->
                    <div class="p-4 bg-amber-50/40 dark:bg-amber-950/20 rounded-2xl border border-amber-200 dark:border-amber-800/30 space-y-3">
                        <h3 class="font-extrabold text-sm text-amber-800 dark:text-amber-300 uppercase tracking-wider">📥 Recipient Information</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            <div>
                                <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Recipient Full Name *</label>
                                <input type="text" name="recipient_name" x-model="recipientName" required class="w-full px-3.5 py-2.5 bg-white dark:bg-[#111] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Recipient Phone Number (For PIN SMS) *</label>
                                <input type="tel" name="recipient_phone" x-model="recipientPhone" required class="w-full px-3.5 py-2.5 bg-white dark:bg-[#111] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white">
                            </div>
                        </div>

                        <div class="text-xs">
                            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Delivery Notes / Instructions (Optional)</label>
                            <textarea name="delivery_instructions" x-model="deliveryInstructions" rows="2" placeholder="Gate code, call before arrival, leave at reception..." class="w-full px-3.5 py-2 bg-white dark:bg-[#111] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white resize-none"></textarea>
                        </div>
                    </div>

                    <div class="flex justify-between pt-2">
                        <button type="button" @click="currentStep = 2" class="px-5 py-2.5 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 font-extrabold text-xs rounded-xl">
                            ← Back
                        </button>
                        <button type="button" @click="currentStep = 4" class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs rounded-xl shadow-md">
                            Next: Package Specifications →
                        </button>
                    </div>
                </div>

                <!-- STEP 4: PACKAGE DETAILS & SPECS -->
                <div x-show="currentStep === 4" class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 md:p-8 shadow-sm space-y-6">
                    <div>
                        <h2 class="text-xl font-black text-gray-900 dark:text-white">STEP 4: Package Category & Specifications</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Specify parcel category, size, weight, and handling rules.</p>
                    </div>

                    <!-- Category Pills -->
                    <div class="space-y-2">
                        <label class="block text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Package Category *</label>
                        <input type="hidden" name="package_category" x-model="packageCategory">

                        <div class="flex flex-wrap gap-2 text-xs font-bold">
                            <template x-for="cat in ['Documents', 'Clothing', 'Electronics', 'Household items', 'Office supplies', 'Personal belongings', 'Other']" :key="cat">
                                <button type="button" @click="packageCategory = cat"
                                        :class="packageCategory === cat ? 'bg-amber-500 text-white shadow-sm' : 'bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 hover:bg-gray-200'"
                                        class="py-2 px-3.5 rounded-xl transition-all">
                                    <span x-text="cat"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Description & Value -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Package Description</label>
                            <input type="text" name="package_description" x-model="packageDescription" placeholder="e.g. Legal documents & laptop" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Declared Value ($)</label>
                            <input type="number" name="declared_value" min="0" x-model="declaredValue" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white">
                        </div>
                    </div>

                    <!-- Package Size Selector -->
                    <div class="space-y-2 pt-2 border-t border-gray-100 dark:border-white/10">
                        <label class="block text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Package Size *</label>
                        <input type="hidden" name="package_size" x-model="packageSize">

                        <div class="grid grid-cols-3 gap-3 text-center text-xs">
                            <div @click="packageSize = 'Small'; packageWeight = 1.5;"
                                 :class="packageSize === 'Small' ? 'border-amber-500 bg-amber-50/40 dark:bg-amber-950/20 font-black' : 'border-gray-200 dark:border-white/10'"
                                 class="border-2 rounded-2xl p-4 cursor-pointer transition-all">
                                <div class="text-3xl mb-1">✉️</div>
                                <h4 class="font-extrabold text-sm text-gray-900 dark:text-white">Small</h4>
                                <p class="text-[10px] text-gray-400">Up to 2 kg (Envelopes / Small Box)</p>
                            </div>

                            <div @click="packageSize = 'Medium'; packageWeight = 5.0;"
                                 :class="packageSize === 'Medium' ? 'border-amber-500 bg-amber-50/40 dark:bg-amber-950/20 font-black' : 'border-gray-200 dark:border-white/10'"
                                 class="border-2 rounded-2xl p-4 cursor-pointer transition-all">
                                <div class="text-3xl mb-1">📦</div>
                                <h4 class="font-extrabold text-sm text-gray-900 dark:text-white">Medium</h4>
                                <p class="text-[10px] text-gray-400">Up to 8 kg (Shoebox / Groceries)</p>
                            </div>

                            <div @click="packageSize = 'Large'; packageWeight = 15.0;"
                                 :class="packageSize === 'Large' ? 'border-amber-500 bg-amber-50/40 dark:bg-amber-950/20 font-black' : 'border-gray-200 dark:border-white/10'"
                                 class="border-2 rounded-2xl p-4 cursor-pointer transition-all">
                                <div class="text-3xl mb-1">🚚</div>
                                <h4 class="font-extrabold text-sm text-gray-900 dark:text-white">Large</h4>
                                <p class="text-[10px] text-gray-400">Up to 25 kg (Cartons / Heavy Items)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Weight & Quantity -->
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Weight (kg) *</label>
                            <input type="number" step="0.1" name="package_weight_kg" x-model="packageWeight" required class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Quantity *</label>
                            <input type="number" min="1" name="quantity" x-model="quantity" required class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white">
                        </div>
                    </div>

                    <!-- Special Handling Checkboxes -->
                    <div class="p-4 bg-gray-50 dark:bg-[#1a1a1a] rounded-2xl border border-gray-200 dark:border-white/10 space-y-2 text-xs">
                        <label class="block font-extrabold text-gray-900 dark:text-white uppercase tracking-wider">Special Handling Options</label>
                        
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="special_handling[]" value="signature_required" @change="toggleHandling('signature_required')" checked class="w-4 h-4 text-amber-500 rounded border-gray-300">
                            <span class="font-bold text-gray-800 dark:text-gray-200">Signature required on delivery</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="special_handling[]" value="climate_control" @change="toggleHandling('climate_control')" class="w-4 h-4 text-amber-500 rounded border-gray-300">
                            <span class="font-bold text-gray-800 dark:text-gray-200">Climate-controlled transport (Temperature sensitive)</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="special_handling[]" value="discreet" @change="toggleHandling('discreet')" class="w-4 h-4 text-amber-500 rounded border-gray-300">
                            <span class="font-bold text-gray-800 dark:text-gray-200">Discreet white-glove packaging</span>
                        </label>
                    </div>

                    <div class="flex justify-between pt-2">
                        <button type="button" @click="currentStep = 3" class="px-5 py-2.5 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 font-extrabold text-xs rounded-xl">
                            ← Back
                        </button>
                        <button type="button" @click="currentStep = 5" class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs rounded-xl shadow-md">
                            Next: Price & Payment →
                        </button>
                    </div>
                </div>

                <!-- STEP 5: PRICE & PAYMENT -->
                <div x-show="currentStep === 5" class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 md:p-8 shadow-sm space-y-6">
                    <div>
                        <h2 class="text-xl font-black text-gray-900 dark:text-white">STEP 5: Payment Method</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Select payment method for parcel dispatch.</p>
                    </div>

                    <div class="space-y-3 text-xs">
                        <label class="block font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Payment Method *</label>
                        <select name="payment_method" x-model="paymentMethod" class="w-full px-4 py-3.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-xs font-bold text-gray-900 dark:text-white cursor-pointer">
                            <option value="stripe">💳 Stripe (Credit / Debit Card)</option>
                            <option value="momo">📱 Momo Pay</option>
                            <option value="cash">💵 Cash on Pickup / Delivery</option>
                            <option value="applepay">🍏 Apple Pay</option>
                        </select>
                    </div>

                    <div class="flex justify-between pt-2">
                        <button type="button" @click="currentStep = 4" class="px-5 py-2.5 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 font-extrabold text-xs rounded-xl">
                            ← Back
                        </button>
                        <button type="button" @click="currentStep = 6" class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs rounded-xl shadow-md">
                            Next: Summary & Confirmation →
                        </button>
                    </div>
                </div>

                <!-- STEP 6: CONFIRMATION SUMMARY -->
                <div x-show="currentStep === 6" class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 md:p-8 shadow-sm space-y-6">
                    <div>
                        <h2 class="text-xl font-black text-gray-900 dark:text-white">STEP 6: Confirm & Dispatch Parcel</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Review complete parcel order details before dispatching courier.</p>
                    </div>

                    <div class="space-y-4 text-xs font-semibold">
                        <div class="p-4 bg-gray-50 dark:bg-[#1a1a1a] rounded-2xl border border-gray-200 dark:border-white/10 space-y-2">
                            <h4 class="font-extrabold text-gray-900 dark:text-white uppercase">📍 Locations</h4>
                            <p><strong class="text-gray-900 dark:text-white">Pickup:</strong> <span x-text="pickupLocation || 'Pickup Address'"></span></p>
                            <p><strong class="text-gray-900 dark:text-white">Destination:</strong> <span x-text="dropoffLocation || 'Drop-off Address'"></span></p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="p-4 bg-gray-50 dark:bg-[#1a1a1a] rounded-2xl border border-gray-200 dark:border-white/10 space-y-1">
                                <h4 class="font-extrabold text-gray-900 dark:text-white uppercase">📤 Sender</h4>
                                <p x-text="senderName"></p>
                                <p x-text="senderPhone" class="text-gray-500"></p>
                            </div>
                            <div class="p-4 bg-gray-50 dark:bg-[#1a1a1a] rounded-2xl border border-gray-200 dark:border-white/10 space-y-1">
                                <h4 class="font-extrabold text-gray-900 dark:text-white uppercase">📥 Recipient</h4>
                                <p x-text="recipientName"></p>
                                <p x-text="recipientPhone" class="text-gray-500"></p>
                            </div>
                        </div>

                        <div class="p-4 bg-gray-50 dark:bg-[#1a1a1a] rounded-2xl border border-gray-200 dark:border-white/10 space-y-1">
                            <h4 class="font-extrabold text-gray-900 dark:text-white uppercase">📦 Package Details</h4>
                            <p><strong class="text-gray-900 dark:text-white">Category:</strong> <span x-text="packageCategory"></span> (<span x-text="packageSize"></span> Size, <span x-text="packageWeight"></span> kg)</p>
                            <p><strong class="text-gray-900 dark:text-white">Speed:</strong> <span x-text="deliveryType"></span> Delivery</p>
                        </div>

                        <!-- Prohibited Consignment Declaration Box -->
                        <div class="p-4 bg-red-50/70 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 rounded-2xl space-y-2 text-xs">
                            <h4 class="font-extrabold text-red-900 dark:text-red-200 uppercase tracking-wider flex items-center gap-1.5">
                                <span>🚫 Prohibited Consignment Declaration</span>
                            </h4>
                            <p class="text-gray-600 dark:text-gray-300 text-[11px] leading-relaxed">
                                Per Article VII of our Terms & Conditions, consignments must NOT contain illegal narcotics, weapons/firearms, explosives, flammable liquids, cash/bullion, biohazards, or stolen goods.
                            </p>
                            <label class="flex items-start gap-2.5 pt-1 cursor-pointer select-none">
                                <input type="checkbox" name="prohibited_items_acknowledged" value="1" required class="w-4 h-4 mt-0.5 rounded text-amber-500 border-gray-300 focus:ring-amber-500">
                                <span class="font-bold text-gray-900 dark:text-white text-xs">
                                    I confirm and declare that this parcel does NOT contain any prohibited, illegal, or hazardous items.
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-between pt-2">
                        <button type="button" @click="currentStep = 5" class="px-5 py-2.5 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 font-extrabold text-xs rounded-xl">
                            ← Back
                        </button>
                        <button type="submit" class="px-8 py-4 bg-amber-500 hover:bg-amber-600 text-white font-black text-sm rounded-2xl shadow-lg shadow-amber-500/25 uppercase tracking-wider">
                            🚀 Confirm & Dispatch Parcel (<span x-text="priceBreakdown.currency_symbol + Number(priceBreakdown.total_price || 0).toFixed(2)"></span>)
                        </button>
                    </div>
                </div>

            </div>

            <!-- Right Column: Sticky Fare Breakdown -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 shadow-xl sticky top-24 space-y-6">
                    
                    <div>
                        <span class="text-xs font-extrabold text-amber-500 uppercase tracking-widest block mb-1">Price Estimate</span>
                        <h2 class="text-2xl font-black text-gray-900 dark:text-white">Delivery Fare</h2>
                        <p class="text-xs text-gray-400 mt-1" x-text="deliveryType + ' Parcel Delivery'"></p>
                    </div>

                    <!-- Price Itemized List -->
                    <div class="space-y-3 text-xs border-t border-b border-gray-100 dark:border-white/10 py-4">
                        <div class="flex justify-between text-gray-600 dark:text-gray-400">
                            <span>Delivery Fee:</span>
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
                            <span class="text-gray-900 dark:text-white">Total Amount:</span>
                            <span class="text-2xl text-amber-500" x-text="priceBreakdown.currency_symbol + Number(priceBreakdown.total_price || 0).toFixed(2)"></span>
                        </div>
                    </div>

                    <div class="p-3 bg-amber-50 dark:bg-amber-950/20 rounded-xl text-amber-800 dark:text-amber-300 text-xs font-bold flex items-center gap-2">
                        <span>🔒</span>
                        <span>4-Digit Secure PIN Verification Included</span>
                    </div>

                </div>
            </div>

        </form>
    </main>

    <!-- Maps & Autocomplete Integration -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    @php
        $gmapsKey = config('services.google_maps.api_key');
        $hasValidKey = !empty($gmapsKey) && !str_contains($gmapsKey, 'AIzaSyDemoKey');
    @endphp

    @if($hasValidKey)
        <script src="https://maps.googleapis.com/maps/api/js?key={{ $gmapsKey }}&libraries=places" async defer></script>
    @endif

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const pInput = document.getElementById("pickup_location_input");
            const dInput = document.getElementById("dropoff_location_input");
            const locBtn = document.getElementById("use_my_location_btn_delivery");
            const pLatInput = document.getElementById("pickup_lat_input");
            const pLngInput = document.getElementById("pickup_lng_input");
            const dLatInput = document.getElementById("dropoff_lat_input");
            const dLngInput = document.getElementById("dropoff_lng_input");

            let mapInstance = null;
            let pickupMarker = null;
            let dropoffMarker = null;
            let routeLine = null;
            let defaultLat = 40.7128;
            let defaultLng = -74.0060;

            // Custom Leaflet Icons
            const createIcon = (emoji, bg) => L.divIcon({
                className: 'custom-map-marker',
                html: `<div style="background: ${bg}; color: white; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); border: 2px solid white;">${emoji}</div>`,
                iconSize: [34, 34],
                iconAnchor: [17, 17]
            });

            const pickupIcon = createIcon('📍', '#f59e0b');
            const dropoffIcon = createIcon('🏁', '#ef4444');
            const courierIcon = createIcon('🛵', '#10b981');

            function initMap() {
                const mapEl = document.getElementById('map');
                if (!mapEl || typeof L === 'undefined') return;

                try {
                    mapInstance = L.map('map', { zoomControl: true }).setView([defaultLat, defaultLng], 13);

                    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap &copy; CARTO'
                    }).addTo(mapInstance);

                    // Add initial pickup area marker
                    pickupMarker = L.marker([defaultLat, defaultLng], { icon: pickupIcon, draggable: true }).addTo(mapInstance)
                        .bindPopup('<b>📍 Pickup Location</b><br><span class="text-xs text-gray-500">Drag to refine location</span>');

                    pickupMarker.on('dragend', function(e) {
                        const pos = e.target.getLatLng();
                        setPickup(pos.lat, pos.lng, true);
                    });

                    // Add simulated active delivery couriers in the city
                    const offsets = [
                        [0.008, 0.006],
                        [-0.007, 0.009],
                        [0.005, -0.008],
                        [-0.006, -0.005]
                    ];
                    offsets.forEach((off, i) => {
                        L.marker([defaultLat + off[0], defaultLng + off[1]], { icon: courierIcon }).addTo(mapInstance)
                            .bindPopup(`<b>🛵 Active Courier #${i+1}</b><br><span class="text-xs text-emerald-600 font-bold">● Available (2-4 mins away)</span>`);
                    });

                    // Map Click Handler: alternates setting pickup / dropoff
                    mapInstance.on('click', function(e) {
                        const { lat, lng } = e.latlng;
                        if (!pLatInput.value || (pLatInput.value && dLatInput.value)) {
                            setPickup(lat, lng, true);
                        } else {
                            setDropoff(lat, lng, true);
                        }
                    });

                    setTimeout(() => mapInstance.invalidateSize(), 300);
                } catch (e) {
                    console.warn("Map initialization error:", e);
                }
            }

            function setPickup(lat, lng, reverseGeocode = false) {
                if (pLatInput) pLatInput.value = lat;
                if (pLngInput) pLngInput.value = lng;

                if (pickupMarker && mapInstance) {
                    pickupMarker.setLatLng([lat, lng]);
                } else if (mapInstance) {
                    pickupMarker = L.marker([lat, lng], { icon: pickupIcon, draggable: true }).addTo(mapInstance);
                }

                if (reverseGeocode && pInput) {
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                        .then(r => r.json())
                        .then(data => {
                            if (data && data.display_name) {
                                pInput.value = data.display_name;
                                pInput.dispatchEvent(new Event('input'));
                            }
                        }).catch(() => {});
                }

                updateRouteAndBounds();
            }

            function setDropoff(lat, lng, reverseGeocode = false) {
                if (dLatInput) dLatInput.value = lat;
                if (dLngInput) dLngInput.value = lng;

                if (dropoffMarker && mapInstance) {
                    dropoffMarker.setLatLng([lat, lng]);
                } else if (mapInstance) {
                    dropoffMarker = L.marker([lat, lng], { icon: dropoffIcon, draggable: true }).addTo(mapInstance);
                    dropoffMarker.on('dragend', function(e) {
                        const pos = e.target.getLatLng();
                        setDropoff(pos.lat, pos.lng, true);
                    });
                }

                if (reverseGeocode && dInput) {
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                        .then(r => r.json())
                        .then(data => {
                            if (data && data.display_name) {
                                dInput.value = data.display_name;
                                dInput.dispatchEvent(new Event('input'));
                            }
                        }).catch(() => {});
                }

                updateRouteAndBounds();
            }

            function updateRouteAndBounds() {
                if (!mapInstance) return;
                const pLat = parseFloat(pLatInput?.value);
                const pLng = parseFloat(pLngInput?.value);
                const dLat = parseFloat(dLatInput?.value);
                const dLng = parseFloat(dLngInput?.value);

                if (!isNaN(pLat) && !isNaN(pLng) && !isNaN(dLat) && !isNaN(dLng)) {
                    if (routeLine) mapInstance.removeLayer(routeLine);

                    // Draw delivery dispatch route
                    routeLine = L.polyline([[pLat, pLng], [dLat, dLng]], {
                        color: '#f59e0b',
                        weight: 4,
                        opacity: 0.85,
                        dashArray: '8, 8',
                        lineCap: 'round'
                    }).addTo(mapInstance);

                    const bounds = L.latLngBounds([[pLat, pLng], [dLat, dLng]]);
                    mapInstance.fitBounds(bounds, { padding: [40, 40] });
                } else if (!isNaN(pLat) && !isNaN(pLng)) {
                    mapInstance.setView([pLat, pLng], 14);
                }
            }

            initMap();

            // Google Places Autocomplete if available
            window.addEventListener('load', () => {
                if (window.google && google.maps && google.maps.places) {
                    try {
                        if (pInput) {
                            const acP = new google.maps.places.Autocomplete(pInput);
                            acP.addListener('place_changed', () => {
                                const place = acP.getPlace();
                                if (place.geometry && place.geometry.location) {
                                    setPickup(place.geometry.location.lat(), place.geometry.location.lng(), false);
                                }
                            });
                        }
                        if (dInput) {
                            const acD = new google.maps.places.Autocomplete(dInput);
                            acD.addListener('place_changed', () => {
                                const place = acD.getPlace();
                                if (place.geometry && place.geometry.location) {
                                    setDropoff(place.geometry.location.lat(), place.geometry.location.lng(), false);
                                }
                            });
                        }
                    } catch (e) {}
                }
            });

            // Geolocation Button
            if (locBtn) {
                locBtn.addEventListener("click", () => {
                    if (!navigator.geolocation) {
                        alert("Geolocation is not supported by your browser.");
                        return;
                    }
                    const orig = locBtn.innerHTML;
                    locBtn.disabled = true;
                    locBtn.innerText = "Locating...";

                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            setPickup(pos.coords.latitude, pos.coords.longitude, true);
                            locBtn.disabled = false;
                            locBtn.innerHTML = orig;
                        },
                        () => {
                            locBtn.disabled = false;
                            locBtn.innerHTML = orig;
                            alert("Unable to retrieve your location automatically.");
                        },
                        { timeout: 8000 }
                    );
                });
            }
        });
    </script>
</x-layout>