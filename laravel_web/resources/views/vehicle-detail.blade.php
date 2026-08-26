<x-layout>
    <x-slot:title>{{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }} — Rent | RideMyCars</x-slot>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10"
          x-data="{
              startDate: '{{ $startDate }}',
              pickupTime: '{{ $pickupTime }}',
              returnDate: '{{ $returnDate }}',
              returnTime: '{{ $returnTime }}',
              driverAge: {{ $driverAge }},
              driverCountry: '{{ $driverCountry }}',
              protectionOption: 'basic', // basic, full_cover
              selectedExtras: [], // additional_driver, child_seat, gps
              paymentOption: 'part', // part (20%), full (100%)
              dailyRate: {{ $vehicle->daily_rate }},

              get daysCount() {
                  try {
                      const s = new Date(this.startDate + 'T' + this.pickupTime);
                      const e = new Date(this.returnDate + 'T' + this.returnTime);
                      const diffHrs = (e - s) / (1000 * 60 * 60);
                      const d = Math.ceil(diffHrs / 24);
                      return d > 0 ? d : 1;
                  } catch (err) {
                      return 1;
                  }
              },
              get baseTotal() {
                  return (this.daysCount * this.dailyRate).toFixed(2);
              },
              get protectionTotal() {
                  return this.protectionOption === 'full_cover' ? (this.daysCount * 12.00).toFixed(2) : (0).toFixed(2);
              },
              get extrasTotal() {
                  let fee = 0;
                  if (this.selectedExtras.includes('additional_driver')) fee += this.daysCount * 10.00;
                  if (this.selectedExtras.includes('child_seat')) fee += this.daysCount * 8.00;
                  if (this.selectedExtras.includes('gps')) fee += this.daysCount * 5.00;
                  return fee.toFixed(2);
              },
              get totalAmount() {
                  return (parseFloat(this.baseTotal) + parseFloat(this.protectionTotal) + parseFloat(this.extrasTotal)).toFixed(2);
              },
              get payNowDeposit() {
                  if (this.paymentOption === 'full') {
                      return this.totalAmount;
                  }
                  return (this.totalAmount * 0.20).toFixed(2);
              },
              get balanceAtPickup() {
                  if (this.paymentOption === 'full') {
                      return (0).toFixed(2);
                  }
                  return (this.totalAmount - this.payNowDeposit).toFixed(2);
              },
              toggleExtra(item) {
                  if (this.selectedExtras.includes(item)) {
                      this.selectedExtras = this.selectedExtras.filter(i => i !== item);
                  } else {
                      this.selectedExtras.push(item);
                  }
              }
          }">
        
        <!-- Flash Alerts -->
        @if(session('success'))
            <div class="mb-8 p-5 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800/30 text-emerald-800 dark:text-emerald-200 font-semibold flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center font-bold text-xl shrink-0 shadow-md">
                        🔑
                    </div>
                    <div>
                        <h4 class="font-extrabold text-lg text-gray-900 dark:text-white">Rental Reservation Confirmed!</h4>
                        <p class="text-sm text-emerald-700 dark:text-emerald-300 font-medium mt-0.5">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-8 p-5 rounded-2xl bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800/30 text-rose-800 dark:text-rose-200 font-semibold space-y-1 shadow-sm">
                <div class="flex items-center gap-2 text-rose-700 dark:text-rose-300 font-bold text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>Please fix the following validation errors:</span>
                </div>
                <ul class="list-disc list-inside text-xs space-y-0.5 pl-6">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Breadcrumb -->
        <nav class="mb-6">
            <ol class="flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400">
                <li><a href="/rent" class="hover:text-brand-500 font-bold transition-colors">Rent Cars</a></li>
                <li>&rarr;</li>
                <li class="font-bold text-gray-900 dark:text-white">{{ $vehicle->make }} {{ $vehicle->model }}</li>
            </ol>
        </nav>

        <form action="/rent/{{ $vehicle->id }}/book" method="POST">
            @csrf
            <input type="hidden" name="protection_option" x-model="protectionOption">
            <template x-for="extra in selectedExtras" :key="extra">
                <input type="hidden" name="selected_extras[]" :value="extra">
            </template>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left Column: Specs, Protection & Extras -->
                <div class="lg:col-span-2 space-y-8">
                    
                    <!-- Main Showcase Card -->
                    <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 shadow-sm overflow-hidden">
                        <div class="flex flex-col md:flex-row items-center gap-6 mb-6">
                            <div class="w-full md:w-1/2 aspect-video bg-gray-50 dark:bg-[#181818] rounded-2xl overflow-hidden p-4 flex items-center justify-center border border-gray-100 dark:border-white/5">
                                <img src="{{ $vehicle->image_src }}" alt="{{ $vehicle->make }} {{ $vehicle->model }}" class="w-full h-full object-contain" onError="this.onerror=null;this.src='/images/hero-rent.png';">
                            </div>
                            <div class="w-full md:w-1/2 space-y-3">
                                <span class="px-3 py-1 rounded-full bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300 font-extrabold text-xs uppercase tracking-wider border border-brand-200 dark:border-brand-800/30" x-text="'Category: ' + (vehicle.type || vehicle.category || 'Sedan')"></span>
                                <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">{{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}</h1>
                                <p class="text-xs text-gray-400">Owner: <strong class="text-gray-700 dark:text-gray-300">{{ $vehicle->owner->name ?? 'RideMyCars Fleet Partner' }}</strong></p>

                                <div class="p-3 bg-emerald-50 dark:bg-emerald-950/30 rounded-xl border border-emerald-200 dark:border-emerald-800/30 text-xs font-bold text-emerald-800 dark:text-emerald-300 flex items-center gap-2">
                                    <span>✓ Passed 150-Point Safety & Mechanical Check</span>
                                </div>
                            </div>
                        </div>

                        <!-- Specs Grid -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-4 border-t border-gray-100 dark:border-white/10 text-xs">
                            <div class="p-3 bg-gray-50 dark:bg-[#1a1a1a] rounded-xl border border-gray-100 dark:border-white/5">
                                <span class="text-gray-400 block mb-0.5">Transmission</span>
                                <span class="font-extrabold text-gray-900 dark:text-white">⚙️ {{ ucfirst($vehicle->transmission ?? 'Automatic') }}</span>
                            </div>
                            <div class="p-3 bg-gray-50 dark:bg-[#1a1a1a] rounded-xl border border-gray-100 dark:border-white/5">
                                <span class="text-gray-400 block mb-0.5">Fuel Type</span>
                                <span class="font-extrabold text-gray-900 dark:text-white">⛽ {{ ucfirst($vehicle->fuel_type ?? 'Petrol') }}</span>
                            </div>
                            <div class="p-3 bg-gray-50 dark:bg-[#1a1a1a] rounded-xl border border-gray-100 dark:border-white/5">
                                <span class="text-gray-400 block mb-0.5">Capacity</span>
                                <span class="font-extrabold text-gray-900 dark:text-white">👤 {{ $vehicle->seats ?? 5 }} Seats / {{ $vehicle->luggage ?? 2 }} Bags</span>
                            </div>
                            <div class="p-3 bg-gray-50 dark:bg-[#1a1a1a] rounded-xl border border-gray-100 dark:border-white/5">
                                <span class="text-gray-400 block mb-0.5">Fuel Policy</span>
                                <span class="font-extrabold text-gray-900 dark:text-white">⛽ {{ $vehicle->fuel_policy ?? 'Full-to-Full' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Protection & Insurance Selection (RideMyCars Protection Section) -->
                    <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 md:p-8 shadow-sm space-y-6">
                        <div>
                            <span class="px-3 py-1 rounded-full bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 text-xs font-extrabold uppercase tracking-wider border border-blue-200 dark:border-blue-800/30">RideMyCars Protection & Coverage</span>
                            <h2 class="text-2xl font-black text-gray-900 dark:text-white mt-1">Select Protection & Insurance</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Choose between included standard protection or full excess cover for peace of mind.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            
                            <!-- Basic Protection Option -->
                            <div @click="protectionOption = 'basic'"
                                 :class="protectionOption === 'basic' ? 'border-brand-500 bg-brand-50/40 dark:bg-brand-950/20 shadow-md' : 'border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#1a1a1a]'"
                                 class="p-5 rounded-2xl border-2 cursor-pointer transition-all space-y-3 relative">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h3 class="font-extrabold text-gray-900 dark:text-white text-base">Basic Protection</h3>
                                        <span class="text-xs text-emerald-600 font-bold">Included FREE in Rental</span>
                                    </div>
                                    <input type="radio" name="protection_choice" value="basic" :checked="protectionOption === 'basic'" class="w-5 h-5 text-brand-500 mt-1">
                                </div>
                                <ul class="text-xs space-y-1.5 text-gray-600 dark:text-gray-400">
                                    <li>✓ Third-party liability insurance</li>
                                    <li>✓ Collision Damage Waiver (CDW)</li>
                                    <li>⚠️ Standard Excess / Deductible applies ($500 hold)</li>
                                </ul>
                            </div>

                            <!-- Full Protection Option -->
                            <div @click="protectionOption = 'full_cover'"
                                 :class="protectionOption === 'full_cover' ? 'border-brand-500 bg-brand-50/40 dark:bg-brand-950/20 shadow-md' : 'border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#1a1a1a]'"
                                 class="p-5 rounded-2xl border-2 cursor-pointer transition-all space-y-3 relative">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <span class="px-2 py-0.5 bg-amber-500 text-white font-extrabold text-[10px] uppercase rounded">Recommended</span>
                                        <h3 class="font-extrabold text-gray-900 dark:text-white text-base mt-1">Full Protection Cover</h3>
                                        <span class="text-xs text-brand-600 dark:text-brand-400 font-bold">+$12.00 / day</span>
                                    </div>
                                    <input type="radio" name="protection_choice" value="full_cover" :checked="protectionOption === 'full_cover'" class="w-5 h-5 text-brand-500 mt-1">
                                </div>
                                <ul class="text-xs space-y-1.5 text-gray-600 dark:text-gray-400">
                                    <li>✓ $0 Excess / Zero Deductible on Collision & Theft</li>
                                    <li>✓ Full Glass, Wheels, Tires & Bodywork Coverage</li>
                                    <li>✓ 24/7 Priority Emergency Roadside Assistance</li>
                                </ul>
                            </div>

                        </div>
                    </div>

                    <!-- Optional Extras Section -->
                    <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 md:p-8 shadow-sm space-y-4">
                        <h2 class="text-2xl font-black text-gray-900 dark:text-white">Optional Extras</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Add equipment or options to your rental reservation.</p>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                            
                            <!-- Additional Driver -->
                            <div @click="toggleExtra('additional_driver')"
                                 :class="selectedExtras.includes('additional_driver') ? 'border-brand-500 bg-brand-50/40 dark:bg-brand-950/20' : 'border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#1a1a1a]'"
                                 class="p-4 rounded-2xl border-2 cursor-pointer transition-all flex items-center justify-between">
                                <div>
                                    <span class="font-extrabold text-gray-900 dark:text-white block">👨‍✈️ Additional Driver</span>
                                    <span class="text-[11px] text-gray-500">+$10.00 / day</span>
                                </div>
                                <input type="checkbox" :checked="selectedExtras.includes('additional_driver')" class="w-4 h-4 text-brand-500 rounded">
                            </div>

                            <!-- Child Safety Seat -->
                            <div @click="toggleExtra('child_seat')"
                                 :class="selectedExtras.includes('child_seat') ? 'border-brand-500 bg-brand-50/40 dark:bg-brand-950/20' : 'border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#1a1a1a]'"
                                 class="p-4 rounded-2xl border-2 cursor-pointer transition-all flex items-center justify-between">
                                <div>
                                    <span class="font-extrabold text-gray-900 dark:text-white block">👶 Child Safety Seat</span>
                                    <span class="text-[11px] text-gray-500">+$8.00 / day</span>
                                </div>
                                <input type="checkbox" :checked="selectedExtras.includes('child_seat')" class="w-4 h-4 text-brand-500 rounded">
                            </div>

                            <!-- GPS Unit -->
                            <div @click="toggleExtra('gps')"
                                 :class="selectedExtras.includes('gps') ? 'border-brand-500 bg-brand-50/40 dark:bg-brand-950/20' : 'border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#1a1a1a]'"
                                 class="p-4 rounded-2xl border-2 cursor-pointer transition-all flex items-center justify-between">
                                <div>
                                    <span class="font-extrabold text-gray-900 dark:text-white block">🗺️ GPS Navigation</span>
                                    <span class="text-[11px] text-gray-500">+$5.00 / day</span>
                                </div>
                                <input type="checkbox" :checked="selectedExtras.includes('gps')" class="w-4 h-4 text-brand-500 rounded">
                            </div>

                        </div>
                    </div>

                    <!-- Driver & Booking Information Form -->
                    <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 md:p-8 shadow-sm space-y-6">
                        <h2 class="text-2xl font-black text-gray-900 dark:text-white">Driver & Contact Information</h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div>
                                <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Pick-up Location *</label>
                                <input type="text" name="pickup_location" value="{{ $pickupLocation }}" required placeholder="Enter pickup address or city..." class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Drop-off Location *</label>
                                <input type="text" name="dropoff_location" value="{{ $dropoffLocation ?: $pickupLocation }}" required placeholder="Enter return address..." class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div>
                                <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Pick-up Date & Time *</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="date" name="start_date" x-model="startDate" required min="{{ date('Y-m-d') }}" class="w-full px-3 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white">
                                    <input type="time" name="pickup_time" x-model="pickupTime" required class="w-full px-3 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white">
                                </div>
                            </div>
                            <div>
                                <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Return Date & Time *</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="date" name="end_date" x-model="returnDate" required min="{{ date('Y-m-d') }}" class="w-full px-3 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white">
                                    <input type="time" name="return_time" x-model="returnTime" required class="w-full px-3 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                            <div>
                                <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Driver License Number *</label>
                                <input type="text" name="driver_license" required placeholder="e.g. DL-88997766" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Driver Age (Min {{ $vehicle->min_driver_age ?? 18 }}+) *</label>
                                <input type="number" name="customer_age" x-model="driverAge" required min="{{ $vehicle->min_driver_age ?? 18 }}" max="120" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Residence Country *</label>
                                <input type="text" name="driver_country" x-model="driverCountry" required class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div>
                                <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Contact Email</label>
                                <input type="email" name="driver_email" placeholder="email@domain.com" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Phone Number</label>
                                <input type="text" name="driver_phone" placeholder="+1 (555) 000-0000" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white">
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Sticky Itemized Price Summary & Payment Card -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 shadow-xl sticky top-24 space-y-6">
                        
                        <div>
                            <span class="text-xs font-extrabold text-brand-500 uppercase tracking-widest block mb-1">Rental Summary</span>
                            <h2 class="text-2xl font-black text-gray-900 dark:text-white">{{ $vehicle->make }} {{ $vehicle->model }}</h2>
                            <p class="text-xs text-gray-400 mt-1" x-text="`${daysCount} Day(s) Rental Duration`"></p>
                        </div>

                        <!-- Itemized Price Breakdown -->
                        <div class="space-y-2.5 text-xs border-t border-b border-gray-100 dark:border-white/10 py-4">
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>Base Rental (<span x-text="daysCount"></span> Days @ $<span x-text="dailyRate"></span>):</span>
                                <span class="font-bold text-gray-900 dark:text-white" x-text="`$${baseTotal}`"></span>
                            </div>
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>Protection Cover:</span>
                                <span class="font-bold text-gray-900 dark:text-white" x-text="`+$${protectionTotal}`"></span>
                            </div>
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>Optional Extras:</span>
                                <span class="font-bold text-gray-900 dark:text-white" x-text="`+$${extrasTotal}`"></span>
                            </div>

                            <div class="pt-2 border-t border-gray-100 dark:border-white/10 flex justify-between items-center text-sm font-black">
                                <span class="text-gray-900 dark:text-white">Estimated Total:</span>
                                <span class="text-xl text-brand-500" x-text="`$${totalAmount}`"></span>
                            </div>
                        </div>

                        <!-- 20% Online Deposit vs 80% Balance Structure -->
                        <div class="space-y-3">
                            <label class="block text-xs font-extrabold text-gray-900 dark:text-white">Payment Option *</label>
                            
                            <div class="space-y-2 text-xs">
                                <label class="flex items-center justify-between p-3 rounded-xl border cursor-pointer transition-all"
                                       :class="paymentOption === 'part' ? 'bg-brand-50/50 dark:bg-brand-950/20 border-brand-500 font-bold' : 'bg-gray-50 dark:bg-[#1a1a1a] border-gray-200 dark:border-white/10'">
                                    <div class="flex items-center gap-2">
                                        <input type="radio" name="payment_option" value="part" x-model="paymentOption" class="text-brand-500">
                                        <span class="text-gray-900 dark:text-white">20% Deposit Online</span>
                                    </div>
                                    <span class="text-emerald-600 dark:text-emerald-400 font-extrabold" x-text="`$${(totalAmount * 0.20).toFixed(2)}`"></span>
                                </label>

                                <label class="flex items-center justify-between p-3 rounded-xl border cursor-pointer transition-all"
                                       :class="paymentOption === 'full' ? 'bg-brand-50/50 dark:bg-brand-950/20 border-brand-500 font-bold' : 'bg-gray-50 dark:bg-[#1a1a1a] border-gray-200 dark:border-white/10'">
                                    <div class="flex items-center gap-2">
                                        <input type="radio" name="payment_option" value="full" x-model="paymentOption" class="text-brand-500">
                                        <span class="text-gray-900 dark:text-white">Full Payment (100%)</span>
                                    </div>
                                    <span class="text-emerald-600 dark:text-emerald-400 font-extrabold" x-text="`$${totalAmount}`"></span>
                                </label>
                            </div>
                        </div>

                        <!-- Pay Now vs Pickup Balance Summary -->
                        <div class="bg-gray-50 dark:bg-[#1a1a1a] p-4 rounded-2xl border border-gray-100 dark:border-white/5 space-y-2 text-xs">
                            <div class="flex justify-between text-emerald-600 dark:text-emerald-400 font-bold">
                                <span>Payable Online Today:</span>
                                <span class="text-base font-black" x-text="`$${payNowDeposit}`"></span>
                            </div>
                            <div class="flex justify-between text-amber-600 dark:text-amber-400 font-bold" x-show="paymentOption === 'part'">
                                <span>Remaining Balance at Pickup:</span>
                                <span class="text-base font-black" x-text="`$${balanceAtPickup}`"></span>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Payment Method</label>
                            <select name="payment_method" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-bold text-gray-900 dark:text-white cursor-pointer">
                                <option value="stripe">💳 Stripe (Credit / Debit Card)</option>
                                <option value="momo">📱 Momo Pay</option>
                                <option value="cash">💵 Cash</option>
                                <option value="applepay">🍏 Apple Pay</option>
                            </select>
                        </div>

                        <!-- Terms & Agreement Checkbox -->
                        <div class="space-y-2 text-xs">
                            <label class="flex items-start gap-2 cursor-pointer">
                                <input type="checkbox" name="insurance_accepted" value="1" required class="mt-0.5 rounded text-brand-500">
                                <span class="text-gray-700 dark:text-gray-300 font-semibold leading-tight">I agree to the Rental Terms, Fuel Policy ({{ $vehicle->fuel_policy ?? 'Full-to-Full' }}), and Cancellation Policy. *</span>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="w-full py-4 bg-brand-500 hover:bg-brand-600 text-white rounded-2xl font-black text-sm transition-all shadow-lg shadow-brand-500/25 cursor-pointer uppercase tracking-wider">
                            🔑 Confirm & Pay Deposit (<span x-text="`$${payNowDeposit}`"></span>)
                        </button>

                    </div>
                </div>

            </div>
        </form>
    </main>
</x-layout>
