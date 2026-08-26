<x-layout>
    <x-slot:title>Parcel Delivery #{{ $delivery->delivery_code }} — Live Tracker</x-slot>

    <main class="flex-1 max-w-5xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10"
          x-data="{
              deliveryStatus: '{{ $delivery->delivery_status }}',
              courierData: null,
              otpInput: '',
              async pollStatus() {
                  try {
                      const res = await fetch(`/api/package-delivery/{{ $delivery->id }}/status`);
                      if (res.ok) {
                          const data = await res.json();
                          if (data.status) {
                              this.deliveryStatus = data.status;
                          }
                          if (data.courier) {
                              this.courierData = data.courier;
                          }
                      }
                  } catch (e) {}
              }
          }"
          x-init="pollStatus(); setInterval(() => pollStatus(), 4000);">

        <!-- Header -->
        <div class="text-center mb-8">
            <span class="px-3 py-1 rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 font-extrabold text-xs uppercase tracking-wider border border-amber-200 dark:border-amber-800/30">RideMyCars Parcel Tracker</span>
            <h1 class="text-3xl font-black text-gray-900 dark:text-white mt-1 tracking-tight">Parcel Live Tracking & Verification</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Delivery Reference: <span class="font-mono font-bold text-amber-500">#{{ $delivery->delivery_code }}</span></p>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Left Side: Searching Radar & Progress Timeline -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Searching Courier Radar Animation (When Pending) -->
                <div x-show="deliveryStatus === 'pending' || deliveryStatus === 'searching'" class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-8 text-center space-y-4 shadow-sm relative overflow-hidden">
                    <div class="relative w-28 h-28 mx-auto flex items-center justify-center">
                        <div class="absolute inset-0 rounded-full bg-amber-500/20 animate-ping"></div>
                        <div class="absolute inset-2 rounded-full bg-amber-500/30 animate-pulse"></div>
                        <div class="w-16 h-16 rounded-full bg-amber-500 text-white flex items-center justify-center text-2xl font-bold shadow-lg z-10">
                            🛵
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white">Finding Nearest Available Courier</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Matching couriers within 3 km $\rightarrow$ 5 km $\rightarrow$ 10 km from pickup address...</p>
                    </div>
                </div>

                <!-- RideMyCars Progress Timeline Card -->
                <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 md:p-8 shadow-sm space-y-6">
                    <h3 class="font-black text-lg text-gray-900 dark:text-white border-b border-gray-100 dark:border-white/10 pb-3">Delivery Progress Timeline</h3>

                    <div class="space-y-4 text-xs">
                        
                        <!-- Step 1: Order Confirmed -->
                        <div class="flex items-start gap-4">
                            <div class="w-7 h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-sm">
                                ✓
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white">Order Confirmed</h4>
                                <p class="text-gray-400">Parcel dispatch created for {{ $delivery->package_category }}</p>
                            </div>
                        </div>

                        <!-- Step 2: Courier Assigned & Accepted -->
                        <div class="flex items-start gap-4">
                            <div :class="['courier_assigned', 'courier_accepted', 'going_to_pickup', 'arrived_at_pickup', 'parcel_picked_up', 'in_transit', 'arrived_at_destination', 'delivered'].includes(deliveryStatus) ? 'bg-emerald-500 text-white' : 'bg-gray-200 dark:bg-white/10 text-gray-400'"
                                 class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs shrink-0 shadow-sm">
                                <span x-text="['courier_assigned', 'courier_accepted', 'going_to_pickup', 'arrived_at_pickup', 'parcel_picked_up', 'in_transit', 'arrived_at_destination', 'delivered'].includes(deliveryStatus) ? '✓' : '2'"></span>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white">Courier Assigned & Accepted</h4>
                                <p class="text-gray-400" x-text="courierData ? (courierData.name + ' accepted your parcel delivery request.') : 'Searching nearby couriers...'"></p>
                            </div>
                        </div>

                        <!-- Step 3: Courier Arrived at Pickup & Parcel Picked Up -->
                        <div class="flex items-start gap-4">
                            <div :class="['arrived_at_pickup', 'parcel_picked_up', 'in_transit', 'arrived_at_destination', 'delivered'].includes(deliveryStatus) ? 'bg-emerald-500 text-white' : 'bg-gray-200 dark:bg-white/10 text-gray-400'"
                                 class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs shrink-0 shadow-sm">
                                <span x-text="['arrived_at_pickup', 'parcel_picked_up', 'in_transit', 'arrived_at_destination', 'delivered'].includes(deliveryStatus) ? '✓' : '3'"></span>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white">Parcel Picked Up</h4>
                                <p class="text-gray-400">Courier reached pickup location and collected package</p>
                            </div>
                        </div>

                        <!-- Step 4: In Transit -->
                        <div class="flex items-start gap-4">
                            <div :class="['in_transit', 'arrived_at_destination', 'delivered'].includes(deliveryStatus) ? 'bg-emerald-500 text-white' : 'bg-gray-200 dark:bg-white/10 text-gray-400'"
                                 class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs shrink-0 shadow-sm">
                                <span x-text="['in_transit', 'arrived_at_destination', 'delivered'].includes(deliveryStatus) ? '✓' : '4'"></span>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white">In Transit to Destination</h4>
                                <p class="text-gray-400">Courier navigating to recipient address</p>
                            </div>
                        </div>

                        <!-- Step 5: PIN Verification & Delivered -->
                        <div class="flex items-start gap-4">
                            <div :class="deliveryStatus === 'delivered' ? 'bg-emerald-500 text-white' : 'bg-gray-200 dark:bg-white/10 text-gray-400'"
                                 class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs shrink-0 shadow-sm">
                                <span x-text="deliveryStatus === 'delivered' ? '✓' : '5'"></span>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white">PIN Verified & Delivered</h4>
                                <p class="text-gray-400">4-Digit PIN verified and package handed to recipient</p>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- 4-Digit PIN Verification Card for Courier / Recipient -->
                <div class="bg-amber-50 dark:bg-amber-950/20 rounded-3xl border border-amber-200 dark:border-amber-800/30 p-6 md:p-8 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-black text-amber-600 dark:text-amber-400 uppercase tracking-widest block">Secure Delivery Verification</span>
                            <h3 class="text-xl font-black text-gray-900 dark:text-white mt-0.5">4-Digit Recipient Delivery PIN</h3>
                        </div>
                        <div class="px-4 py-2 bg-amber-500 text-white font-mono font-black text-2xl rounded-2xl shadow-sm tracking-widest">
                            {{ $delivery->delivery_otp }}
                        </div>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-400 font-medium leading-relaxed">
                        Share this 4-digit PIN with recipient <strong>{{ $delivery->recipient_name }}</strong> (<span class="font-bold">{{ $delivery->recipient_phone }}</span>). Courier must enter this PIN to complete delivery.
                    </p>

                    <!-- PIN Input Form for Courier/Admin Validation -->
                    @if($delivery->delivery_status !== 'delivered')
                        <form action="/api/package-delivery/{{ $delivery->id }}/verify-otp" method="POST" class="pt-3 flex gap-3 border-t border-amber-200/60 dark:border-amber-800/30">
                            @csrf
                            <input type="text" name="otp" maxLength="4" required placeholder="Enter 4-digit PIN" class="px-4 py-3 bg-white dark:bg-[#111] border border-amber-300 dark:border-amber-700/50 rounded-xl font-mono font-bold text-center text-lg text-gray-900 dark:text-white w-44">
                            <button type="submit" class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white font-extrabold rounded-xl text-xs shadow-md transition-all">
                                ✓ Verify PIN & Complete Delivery
                            </button>
                        </form>
                    @endif
                </div>

            </div>

            <!-- Right Side: Assigned Courier Profile Card & Details -->
            <div class="lg:col-span-1 space-y-6">
                
                <!-- Courier Profile Card (When Assigned) -->
                <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 shadow-sm space-y-4">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Assigned Courier</span>
                    
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-[#222] overflow-hidden border-2 border-amber-500 shrink-0">
                            <img :src="courierData ? courierData.photo_url : '{{ $delivery->courierProfile->photo_url ?? '' }}'" class="w-full h-full object-cover" onError="this.onerror=null;this.src='/images/hero-rent.png';">
                        </div>
                        <div>
                            <h3 class="font-extrabold text-base text-gray-900 dark:text-white" x-text="courierData ? courierData.name : '{{ $delivery->courier->name ?? 'Matching Courier...' }}'"></h3>
                            <div class="flex items-center gap-2 text-xs text-gray-500 mt-0.5 font-semibold">
                                <span class="text-amber-500 font-bold">★ <span x-text="courierData ? courierData.rating : '{{ $delivery->courierProfile->rating ?? 4.9 }}'"></span></span>
                                <span>• Express Courier</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-gray-100 dark:border-white/10 grid grid-cols-2 gap-2 text-xs font-semibold">
                        <div class="p-2.5 bg-gray-50 dark:bg-[#1a1a1a] rounded-xl text-center">
                            <span class="text-gray-400 block text-[10px]">Estimated ETA</span>
                            <span class="font-extrabold text-emerald-600 dark:text-emerald-400">~12 Mins</span>
                        </div>
                        <div class="p-2.5 bg-gray-50 dark:bg-[#1a1a1a] rounded-xl text-center">
                            <span class="text-gray-400 block text-[10px]">Status</span>
                            <span class="font-extrabold text-amber-500 uppercase" x-text="deliveryStatus"></span>
                        </div>
                    </div>
                </div>

                <!-- Parcel & Contact Summary Card -->
                <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 shadow-sm space-y-3 text-xs">
                    <h3 class="font-extrabold text-sm text-gray-900 dark:text-white border-b border-gray-100 dark:border-white/10 pb-2">Parcel & Contact Summary</h3>

                    <div class="space-y-2 text-gray-600 dark:text-gray-400">
                        <p><strong class="text-gray-900 dark:text-white">Sender:</strong> {{ $delivery->sender_name }} ({{ $delivery->sender_phone }})</p>
                        <p><strong class="text-gray-900 dark:text-white">Recipient:</strong> {{ $delivery->recipient_name }} ({{ $delivery->recipient_phone }})</p>
                        <p><strong class="text-gray-900 dark:text-white">Pickup:</strong> {{ $delivery->pickup_location }}</p>
                        <p><strong class="text-gray-900 dark:text-white">Destination:</strong> {{ $delivery->dropoff_location }}</p>
                        <p><strong class="text-gray-900 dark:text-white">Category:</strong> {{ $delivery->package_category }} ({{ $delivery->package_size }} Size)</p>
                    </div>

                    <div class="pt-3 border-t border-gray-100 dark:border-white/10 flex justify-between items-center text-sm font-extrabold">
                        <span class="text-gray-900 dark:text-white">Total Amount:</span>
                        <span class="text-amber-500">${{ number_format($delivery->total_price, 2) }} {{ $delivery->currency }}</span>
                    </div>
                </div>

            </div>

        </div>
    </main>
</x-layout>
