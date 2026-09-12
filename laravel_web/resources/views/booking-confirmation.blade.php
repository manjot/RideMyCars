<x-layout>
    <x-slot:title>Driver Booking #{{ $booking->booking_code }} — Live Status</x-slot>

    <main class="flex-1 max-w-5xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10"
          x-data="{
              bookingStatus: '{{ $booking->booking_status }}',
              paymentStatus: '{{ strtolower($booking->payment_status ?? 'pending') }}',
              isPaymentConfirmed: {{ in_array(strtolower($booking->payment_status ?? ''), ['paid', 'hold', 'authorized']) ? 'true' : 'false' }},
              driverData: null,
              async pollStatus() {
                  try {
                      const res = await fetch(`/api/driver-booking/{{ $booking->id }}/status`);
                      if (res.ok) {
                          const data = await res.json();
                          if (data.status) {
                              this.bookingStatus = data.status;
                          }
                          if (data.payment_status) {
                              this.paymentStatus = data.payment_status;
                              this.isPaymentConfirmed = ['paid', 'hold', 'authorized'].includes(data.payment_status.toLowerCase());
                          }
                          if (data.driver) {
                              this.driverData = data.driver;
                          } else {
                              this.driverData = null;
                          }
                      }
                  } catch (e) {}
              }
          }"
          x-init="pollStatus(); setInterval(() => pollStatus(), 3500);">
        
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Driver Booking Live Status</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Booking Reference: <span class="font-mono font-bold text-brand-500">#{{ $booking->booking_code }}</span></p>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800/30 text-emerald-800 dark:text-emerald-200 text-xs font-bold flex items-center gap-2">
                <span>✓ {{ session('success') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Side: Radar / Map & Status Timeline -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Searching Radar Animation (When Pending) -->
                <div x-show="bookingStatus === 'pending'" class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-8 text-center space-y-4 shadow-sm relative overflow-hidden">
                    <div class="relative w-28 h-28 mx-auto flex items-center justify-center">
                        <div class="absolute inset-0 rounded-full bg-brand-500/20 animate-ping"></div>
                        <div class="absolute inset-2 rounded-full bg-brand-500/30 animate-pulse"></div>
                        <div class="w-16 h-16 rounded-full bg-brand-500 text-slate-950 flex items-center justify-center text-2xl font-bold shadow-lg z-10">
                            👨‍✈️
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white">Finding a Driver Near You</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Matching closest available drivers within 3 km $\rightarrow$ 5 km $\rightarrow$ 10 km...</p>
                    </div>
                </div>

                <!-- RideMyCars Booking Timeline Card -->
                <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 md:p-8 shadow-sm space-y-6">
                    <h3 class="font-black text-lg text-gray-900 dark:text-white border-b border-gray-100 dark:border-white/10 pb-3">Booking Progress Timeline</h3>

                    <div class="space-y-4 text-xs">
                        
                        <!-- Step 1: Request Created -->
                        <div class="flex items-start gap-4">
                            <div class="w-7 h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-sm">
                                ✓
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white">Driver Requested</h4>
                                <p class="text-gray-400">Request submitted for {{ $booking->service_type ?? 'Hire Driver' }}</p>
                            </div>
                        </div>

                        <!-- Step 2: Driver Assigned & Accepted -->
                        <div class="flex items-start gap-4">
                            <div :class="['accepted', 'en_route', 'arrived', 'in_progress', 'completed'].includes(bookingStatus) ? 'bg-emerald-500 text-white' : 'bg-gray-200 dark:bg-white/10 text-gray-400'"
                                 class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs shrink-0 shadow-sm">
                                <span x-text="['accepted', 'en_route', 'arrived', 'in_progress', 'completed'].includes(bookingStatus) ? '✓' : '2'"></span>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white">Driver Assigned & Accepted</h4>
                                <p class="text-gray-400" x-text="driverData ? (driverData.name + ' accepted your booking request.') : 'Waiting for driver response...'"></p>
                            </div>
                        </div>

                        <!-- Step 3: En Route -->
                        <div class="flex items-start gap-4">
                            <div :class="['en_route', 'arrived', 'in_progress', 'completed'].includes(bookingStatus) ? 'bg-emerald-500 text-white' : 'bg-gray-200 dark:bg-white/10 text-gray-400'"
                                 class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs shrink-0 shadow-sm">
                                <span x-text="['en_route', 'arrived', 'in_progress', 'completed'].includes(bookingStatus) ? '✓' : '3'"></span>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white">Driver En Route</h4>
                                <p class="text-gray-400">Driver navigating to pickup location</p>
                            </div>
                        </div>

                        <!-- Step 4: Arrived -->
                        <div class="flex items-start gap-4">
                            <div :class="['arrived', 'in_progress', 'completed'].includes(bookingStatus) ? 'bg-emerald-500 text-white' : 'bg-gray-200 dark:bg-white/10 text-gray-400'"
                                 class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs shrink-0 shadow-sm">
                                <span x-text="['arrived', 'in_progress', 'completed'].includes(bookingStatus) ? '✓' : '4'"></span>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white">Driver Arrived (100m Geofence)</h4>
                                <p class="text-gray-400">Driver reached pickup location</p>
                            </div>
                        </div>

                        <!-- Step 5: Service Completed -->
                        <div class="flex items-start gap-4">
                            <div :class="bookingStatus === 'completed' ? 'bg-emerald-500 text-white' : 'bg-gray-200 dark:bg-white/10 text-gray-400'"
                                 class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs shrink-0 shadow-sm">
                                <span x-text="bookingStatus === 'completed' ? '✓' : '5'"></span>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white">Trip Service Completed</h4>
                                <p class="text-gray-400">Final fare calculated and payment settled</p>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <!-- Right Side: Assigned Driver Card & Booking Summary -->
            <div class="lg:col-span-1 space-y-6">
                
                <!-- State 1: PAYMENT NOT CONFIRMED -> HIDE ALL DRIVER DETAILS & SHOW PRIVACY SHIELD -->
                <div x-show="!isPaymentConfirmed" class="bg-white dark:bg-[#111] rounded-3xl border border-amber-300 dark:border-amber-600/40 p-6 shadow-sm space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-wider">Assigned Chauffeur</span>
                        <span class="px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-300 font-extrabold text-[10px] uppercase">
                            🔒 Payment Required
                        </span>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed font-medium">
                        Driver identity and direct communication channels unlock immediately once your escrow payment is pre-authorized.
                    </p>
                    <a href="/payment/verify-details/driver_booking/{{ $booking->id }}" 
                       class="w-full py-3 px-4 bg-black dark:bg-white text-white dark:text-black font-black text-xs rounded-xl shadow-md flex items-center justify-center gap-2 hover:opacity-90 transition">
                        <span>💳 Authorize ${{ number_format($booking->total_price, 2) }} {{ $booking->currency }} →</span>
                    </a>
                </div>

                <!-- State 2: PAYMENT CONFIRMED BUT DRIVER NOT ACCEPTED YET -->
                <div x-show="isPaymentConfirmed && !driverData" class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 shadow-sm space-y-3 text-center">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300 text-xs font-black">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                        Broadcasting to Nearby Drivers...
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Payment safely held in escrow. Driver contact & vehicle details will appear immediately once a driver confirms.
                    </p>
                </div>

                <!-- State 3: PAYMENT CONFIRMED AND DRIVER CONFIRMED -->
                <div x-show="isPaymentConfirmed && driverData" class="bg-white dark:bg-[#111] rounded-3xl border border-emerald-300 dark:border-emerald-700/50 p-6 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Assigned Chauffeur</span>
                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 font-extrabold text-[10px]">
                            ✓ Confirmed
                        </span>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-[#222] overflow-hidden border-2 border-emerald-500 shrink-0">
                            <img :src="driverData?.photo_url || ('https://ui-avatars.com/api/?name=' + encodeURIComponent(driverData?.name || 'Driver') + '&background=0F172A&color=FFFFFF&size=256&bold=true')" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <h3 class="font-extrabold text-base text-gray-900 dark:text-white" x-text="driverData?.name"></h3>
                            <div class="flex items-center gap-2 text-xs text-gray-500 mt-0.5 font-semibold">
                                <span class="text-amber-500 font-bold">★ <span x-text="driverData?.rating || 4.95"></span></span>
                                <span>• Verified Driver</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg-gray-50 dark:bg-white/5 rounded-2xl text-xs space-y-1 border border-gray-100 dark:border-white/5">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Vehicle:</span>
                            <span class="font-bold text-gray-900 dark:text-white" x-text="driverData?.vehicle_model || '{{ $booking->car_make_model ?? 'Executive Vehicle' }}'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">License Plate:</span>
                            <span class="font-mono font-bold text-amber-600 dark:text-amber-400" x-text="driverData?.vehicle_plate || 'REG-8899'"></span>
                        </div>
                    </div>

                    <!-- Direct Contact Buttons -->
                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <template x-if="driverData?.phone">
                            <a :href="'tel:' + driverData.phone" class="py-2.5 bg-brand-500 hover:bg-brand-600 text-slate-950 rounded-xl text-xs font-black shadow-sm text-center flex items-center justify-center gap-1">
                                <span>📞 Call</span>
                            </a>
                        </template>
                        <template x-if="driverData?.phone">
                            <a :href="'https://wa.me/' + (driverData.phone ? driverData.phone.replace(/[^0-9]/g, '') : '')" target="_blank" class="py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black shadow-sm text-center flex items-center justify-center gap-1">
                                <span>💬 WhatsApp</span>
                            </a>
                        </template>
                    </div>

                    <div class="pt-3 border-t border-gray-100 dark:border-white/10 grid grid-cols-2 gap-2 text-xs font-semibold">
                        <div class="p-2.5 bg-gray-50 dark:bg-[#1a1a1a] rounded-xl text-center">
                            <span class="text-gray-400 block text-[10px]">Estimated ETA</span>
                            <span class="font-extrabold text-emerald-600 dark:text-emerald-400">~6 Mins</span>
                        </div>
                        <div class="p-2.5 bg-gray-50 dark:bg-[#1a1a1a] rounded-xl text-center">
                            <span class="text-gray-400 block text-[10px]">Status</span>
                            <span class="font-extrabold text-brand-500 uppercase" x-text="bookingStatus"></span>
                        </div>
                    </div>
                </div>

                <!-- Booking Summary Card -->
                <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 shadow-sm space-y-3 text-xs">
                    <h3 class="font-extrabold text-sm text-gray-900 dark:text-white border-b border-gray-100 dark:border-white/10 pb-2">Booking Details</h3>

                    <div class="space-y-2 text-gray-600 dark:text-gray-400">
                        <p><strong class="text-gray-900 dark:text-white">Service Type:</strong> {{ $booking->service_type ?? 'Hire Driver' }}</p>
                        <p><strong class="text-gray-900 dark:text-white">Pickup Location:</strong> {{ $booking->pickup_location }}</p>
                        @if($booking->dropoff_location)
                            <p><strong class="text-gray-900 dark:text-white">Destination:</strong> {{ $booking->dropoff_location }}</p>
                        @endif
                        <p><strong class="text-gray-900 dark:text-white">Schedule:</strong> {{ $booking->start_date }} at {{ $booking->start_time }}</p>
                        <p><strong class="text-gray-900 dark:text-white">Duration:</strong> {{ $booking->duration_count }} {{ $booking->duration_type }}(s)</p>
                    </div>

                    <div class="pt-3 border-t border-gray-100 dark:border-white/10 flex justify-between items-center text-sm font-extrabold">
                        <span class="text-gray-900 dark:text-white">Total Amount:</span>
                        <span class="text-brand-500">${{ number_format($booking->total_price, 2) }} {{ $booking->currency }}</span>
                    </div>
                </div>

            </div>

        </div>
    </main>
</x-layout>
