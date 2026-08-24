<x-layout>
    <x-slot:title>{{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }} — Rent | RideMyCars</x-slot>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
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

        @if(session('error'))
            <div class="mb-8 p-4 rounded-2xl bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800/30 text-rose-800 dark:text-rose-200 font-semibold flex items-center gap-3 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Breadcrumb -->
        <nav class="mb-8">
            <ol class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                <li><a href="/rent" class="hover:text-brand-500 transition-colors">Rent</a></li>
                <li><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg></li>
                <li class="font-medium text-gray-900 dark:text-white">{{ $vehicle->make }} {{ $vehicle->model }}</li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <!-- Left Column: Images & Details -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Main Image -->
                <div class="bg-gray-100 dark:bg-[#111] rounded-3xl overflow-hidden aspect-video relative flex items-center justify-center border border-gray-200 dark:border-white/10 p-4">
                    <img src="{{ $vehicle->image_src }}" alt="{{ $vehicle->make }} {{ $vehicle->model }}" class="w-full h-full object-contain" onError="this.onerror=null;this.src='/images/hero-rent.png';">
                    <div class="absolute top-4 left-4 bg-white/90 dark:bg-black/80 backdrop-blur-sm px-4 py-1.5 rounded-full text-sm font-bold text-gray-900 dark:text-white shadow-sm">
                        {{ $vehicle->type }}
                    </div>
                </div>

                <!-- Inspection & Verification Badges -->
                <div class="p-5 rounded-2xl bg-gray-50 dark:bg-[#111] border border-gray-200 dark:border-white/10 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-green-500/10 text-green-500 flex items-center justify-center font-bold">
                            ✓
                        </div>
                        <div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold block">Vehicle Inspection Status</span>
                            <span class="text-sm font-bold text-gray-900 dark:text-white">Passed 150-Point Safety & Condition Check</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-xs font-bold text-gray-700 dark:text-gray-300">
                        <span class="px-3 py-1 bg-white dark:bg-[#1a1a1a] rounded-lg border border-gray-200 dark:border-white/10">Plate: {{ $vehicle->license_plate ?? 'REG-9912' }}</span>
                        <span class="px-3 py-1 bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400 rounded-lg border border-green-200 dark:border-green-800/30">Verified Listing</span>
                    </div>
                </div>

                <!-- Features -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Features & Specifications</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-gray-50 dark:bg-[#111] p-4 rounded-2xl border border-gray-100 dark:border-white/5 flex flex-col items-center justify-center text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-brand-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $vehicle->year }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Year</span>
                        </div>
                        <div class="bg-gray-50 dark:bg-[#111] p-4 rounded-2xl border border-gray-100 dark:border-white/5 flex flex-col items-center justify-center text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-brand-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">5 Seats</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Capacity</span>
                        </div>
                        <div class="bg-gray-50 dark:bg-[#111] p-4 rounded-2xl border border-gray-100 dark:border-white/5 flex flex-col items-center justify-center text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-brand-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">Auto</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Transmission</span>
                        </div>
                        <div class="bg-gray-50 dark:bg-[#111] p-4 rounded-2xl border border-gray-100 dark:border-white/5 flex flex-col items-center justify-center text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-brand-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">AC</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Climate</span>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Description</h2>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed text-lg">
                        The {{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }} is a premium {{ strtolower($vehicle->type) }} that offers a perfect blend of comfort, style, and efficiency. Whether you're navigating city streets or heading out on a road trip, this vehicle provides a smooth and reliable experience. It is fully inspected and maintained to the highest standards.
                    </p>
                </div>
            </div>

            <!-- Right Column: Pricing & Booking -->
            <div class="lg:col-span-1" x-data="{ driverRequired: 'no' }">
                <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 shadow-sm sticky top-24">
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-2">
                            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $vehicle->make }}</h1>
                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $vehicle->is_available ? 'bg-green-100 text-green-700 dark:bg-green-950/60 dark:text-green-400' : 'bg-red-100 text-red-700' }}">
                                {{ $vehicle->is_available ? 'Available Now' : 'Booked' }}
                            </span>
                        </div>
                        <p class="text-xl text-gray-500 dark:text-gray-400 font-medium">{{ $vehicle->model }}</p>
                        <p class="text-xs text-gray-400 mt-1">Listed by: <strong class="text-gray-700 dark:text-gray-300">{{ $vehicle->owner->name ?? 'Verified Fleet Partner' }}</strong></p>
                    </div>

                    <div class="flex items-end justify-between mb-6 pb-6 border-b border-gray-100 dark:border-white/10">
                        <div>
                            <span class="text-4xl font-extrabold text-gray-900 dark:text-white">${{ $vehicle->daily_rate }}</span>
                            <span class="text-lg text-gray-500 dark:text-gray-400 font-medium pb-1">/ day</span>
                        </div>
                        <span class="text-xs font-semibold text-gray-400 bg-gray-50 dark:bg-white/5 px-2.5 py-1 rounded-lg">80% Owner Payout Model</span>
                    </div>

                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Free cancellation up to 24h</span>
                            <span class="text-green-500 font-semibold flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Included</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Basic insurance</span>
                            <span class="text-green-500 font-semibold flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Included</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Unlimited mileage</span>
                            <span class="text-green-500 font-semibold flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Included</span>
                        </div>
                    </div>

                    <!-- Security Deposit Information (Section 7 Requirement) -->
                    <div class="mb-6 p-4 rounded-2xl bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/30 text-amber-800 dark:text-amber-300 space-y-1">
                        <div class="flex items-center gap-2 font-bold text-xs">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <span>Security Deposit Information</span>
                        </div>
                        <p class="text-xs leading-relaxed text-amber-700 dark:text-amber-400">Refundable Security Deposit: <strong>$200</strong> (Pre-authorization hold released upon vehicle return check).</p>
                    </div>

                    <!-- Driver Required Toggle (Client Requirement #2) -->
                    <div class="mb-6 p-4 rounded-2xl bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 space-y-3">
                        <label class="block text-sm font-bold text-gray-900 dark:text-white">Driver Required?</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" 
                                     @click="driverRequired = 'no'" 
                                     :class="driverRequired === 'no' ? 'bg-white dark:bg-[#111] text-brand-600 dark:text-brand-400 border-brand-500 shadow-sm font-bold' : 'bg-transparent text-gray-500 border-transparent hover:text-gray-700'" 
                                     class="py-2.5 px-3 border rounded-xl text-xs transition-all text-center">
                                ❌ No (Car Only)
                            </button>
                            <button type="button" 
                                     @click="driverRequired = 'yes'" 
                                     :class="driverRequired === 'yes' ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm font-bold' : 'bg-transparent text-gray-500 border-transparent hover:text-gray-700'" 
                                     class="py-2.5 px-3 border rounded-xl text-xs transition-all text-center">
                                👨‍✈️ Yes (Hire Driver)
                            </button>
                        </div>
                    </div>

                    <!-- Booking Actions / Form -->
                    <div class="space-y-4">
                        
                        <!-- Self-Drive Rental Form (driverRequired === 'no') -->
                        <template x-if="driverRequired === 'no'">
                            <form action="/rent/{{ $vehicle->id }}/book" method="POST" class="space-y-4 pt-2 border-t border-gray-100 dark:border-white/10" 
                                  x-data="{ 
                                      startDate: '{{ date('Y-m-d') }}', 
                                      endDate: '{{ date('Y-m-d', strtotime('+3 days')) }}',
                                      dailyRate: {{ $vehicle->daily_rate }},
                                      get totalDays() {
                                          if (!this.startDate || !this.endDate) return 1;
                                          const start = new Date(this.startDate);
                                          const end = new Date(this.endDate);
                                          const diffTime = end - start;
                                          const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                                          return diffDays > 0 ? diffDays : 1;
                                      },
                                      get totalPrice() {
                                          return (this.totalDays * this.dailyRate).toFixed(2);
                                      }
                                  }">
                                @csrf

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Pick-up Date *</label>
                                        <input type="date" name="start_date" required min="{{ date('Y-m-d') }}" x-model="startDate" class="w-full px-3 py-2 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-xs text-gray-900 dark:text-white font-semibold">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Return Date *</label>
                                        <input type="date" name="end_date" required min="{{ date('Y-m-d') }}" x-model="endDate" class="w-full px-3 py-2 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-xs text-gray-900 dark:text-white font-semibold">
                                    </div>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Pick-up Location *</label>
                                        <button type="button" id="use_my_location_btn_rent" class="text-brand-500 hover:text-brand-600 text-xs font-semibold flex items-center gap-1 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/></svg>
                                            Use my location
                                        </button>
                                    </div>
                                    <input type="text" id="pickup_location_rent" name="pickup_location" required placeholder="Airport, hotel, or address..." class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-xs text-gray-900 dark:text-white">
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Driver License Number *</label>
                                    <input type="text" name="driver_license" required placeholder="e.g. DL-88997766" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-xs text-gray-900 dark:text-white">
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Payment Method</label>
                                    <div class="relative">
                                        <select name="payment_method" class="w-full px-3.5 py-2.5 pr-9 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-xs text-gray-900 dark:text-white font-medium cursor-pointer appearance-none">
                                            <option value="stripe">💳 Stripe</option>
                                            <option value="momo">📱 Momo Pay</option>
                                            <option value="cash">💵 Cash</option>
                                            <option value="applepay">🍏 Apple Pay</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500 dark:text-gray-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- Total Price Summary Box -->
                                <div class="bg-gray-50 dark:bg-[#1a1a1a] p-3.5 rounded-2xl border border-gray-200 dark:border-white/10 space-y-1">
                                    <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                                        <span>Rental Duration</span>
                                        <span class="font-bold text-gray-900 dark:text-white" x-text="`${totalDays} Day(s) @ $${dailyRate}/day`"></span>
                                    </div>
                                    <div class="flex justify-between items-center pt-1 border-t border-gray-200 dark:border-white/10">
                                        <span class="font-bold text-xs text-gray-900 dark:text-white">Total Amount</span>
                                        <span class="font-extrabold text-xl text-brand-500" x-text="`$${totalPrice}`"></span>
                                    </div>
                                </div>

                                <button type="submit" class="w-full py-4 bg-brand-500 hover:bg-brand-600 text-white rounded-2xl font-extrabold text-sm transition-all shadow-md shadow-brand-500/25">
                                    🔑 Confirm Self-Drive Rental (<span x-text="`$${totalPrice}`"></span>)
                                </button>
                            </form>
                        </template>

                        <!-- Hire Driver Link (driverRequired === 'yes') -->
                        <template x-if="driverRequired === 'yes'">
                            <div class="pt-2 border-t border-gray-100 dark:border-white/10">
                                <a href="/hire-driver?vehicle_id={{ $vehicle->id }}" class="w-full block text-center py-4 px-6 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-bold text-sm transition-colors shadow-sm shadow-emerald-600/20">
                                    👨‍✈️ Choose Driver for Vehicle
                                </a>
                                <p class="text-xs text-center text-emerald-600 dark:text-emerald-400 mt-2 font-medium">Browse verified drivers with ratings and reviews.</p>
                            </div>
                        </template>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const locBtn = document.getElementById("use_my_location_btn_rent");
            const pickupInput = document.getElementById("pickup_location_rent");

            if (locBtn && pickupInput) {
                locBtn.addEventListener("click", () => {
                    if (!navigator.geolocation) {
                        alert("Error: Your browser doesn't support geolocation.");
                        return;
                    }

                    const originalHTML = locBtn.innerHTML;
                    locBtn.disabled = true;
                    locBtn.innerHTML = `<span>Locating...</span>`;

                    navigator.geolocation.getCurrentPosition(
                        async (position) => {
                            const pos = { lat: position.coords.latitude, lng: position.coords.longitude };
                            let addressSet = false;

                            try {
                                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${pos.lat}&lon=${pos.lng}`);
                                if (response.ok) {
                                    const data = await response.json();
                                    if (data && data.display_name) {
                                        pickupInput.value = data.display_name;
                                        addressSet = true;
                                    }
                                }
                            } catch (e) {}

                            if (!addressSet) {
                                pickupInput.value = `Current Location (${pos.lat.toFixed(4)}, ${pos.lng.toFixed(4)})`;
                            }

                            locBtn.disabled = false;
                            locBtn.innerHTML = originalHTML;
                        },
                        (error) => {
                            locBtn.disabled = false;
                            locBtn.innerHTML = originalHTML;
                            alert("Unable to retrieve location. Please enter address manually.");
                        },
                        { enableHighAccuracy: true, timeout: 10000 }
                    );
                });
            }
        });
    </script>
</x-layout>
