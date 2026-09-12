<x-layout title="Payment Details & Driver Verification — RideMyCars">
    <div x-data="verificationPage({
            serviceType: '{{ $serviceType }}',
            serviceId: {{ $serviceId }},
            initialVerificationStatus: '{{ $verificationStatus }}',
            initialPaymentStatus: '{{ $paymentStatus }}',
            initialBookingStatus: '{{ $bookingStatus ?? 'pending' }}',
            initialIsPaymentConfirmed: {{ $isPaymentConfirmed ? 'true' : 'false' }},
            initialIsDriverConfirmed: {{ $isDriverConfirmed ? 'true' : 'false' }},
            totalAmount: {{ $totalAmount }},
            currency: '{{ $currency }}',
            driverData: {{ json_encode($driver) }},
            bookingCode: '{{ $bookingCode }}',
            initialPaymentMethod: '{{ $paidMethod ?? "stripe" }}',
            customerPhone: '{{ $customerPhone ?? "" }}'
         })"
         x-init="initPage()"
         class="min-h-screen bg-gray-50 dark:bg-[#09090b] py-12 px-4 sm:px-6 lg:px-8">

        <div class="max-w-3xl mx-auto space-y-8">
            
            <!-- Page Header & Title -->
            <div class="text-center space-y-2">
                <span class="px-3.5 py-1.5 rounded-full bg-brand-500/10 text-brand-600 dark:text-brand-400 font-extrabold text-xs uppercase tracking-widest border border-brand-500/20">
                    Escrow Protected Checkout
                </span>
                <h1 class="text-3xl md:text-4xl font-black text-gray-900 dark:text-white tracking-tight">
                    Payment Hold & Driver Confirmation
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 max-w-lg mx-auto">
                    Select your payment method (Stripe or MoMo Pay). Driver contacts unlock upon payment authorization & confirmation.
                </p>
            </div>

            <!-- 4-Step Progress Indicator -->
            <div class="bg-white dark:bg-[#111] p-5 rounded-3xl border border-gray-200 dark:border-white/10 shadow-sm">
                <div class="grid grid-cols-4 gap-2 text-center text-xs font-extrabold">
                    
                    <!-- Step 1: Details -->
                    <div class="p-2.5 rounded-2xl bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 flex flex-col items-center gap-1">
                        <span class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs font-black">✓</span>
                        <span>1. Details</span>
                    </div>

                    <!-- Step 2: Payment Hold -->
                    <div :class="{
                            'bg-brand-500 text-slate-950 shadow-md font-black ring-2 ring-brand-400': !isPaymentConfirmed,
                            'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300': isPaymentConfirmed
                        }"
                        class="p-2.5 rounded-2xl flex flex-col items-center gap-1 transition-all">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-black"
                              :class="isPaymentConfirmed ? 'bg-emerald-500 text-white' : 'bg-slate-950 text-white'"
                              x-text="isPaymentConfirmed ? '✓' : '2'"></span>
                        <span>2. Payment Hold</span>
                    </div>

                    <!-- Step 3: Driver Search & Confirm -->
                    <div :class="{
                            'bg-amber-500 text-white shadow-md animate-pulse': isPaymentConfirmed && !isDriverConfirmed,
                            'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300': isDriverConfirmed,
                            'bg-gray-100 dark:bg-white/5 text-gray-400': !isPaymentConfirmed
                        }"
                        class="p-2.5 rounded-2xl flex flex-col items-center gap-1 transition-all">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-black"
                              :class="isDriverConfirmed ? 'bg-emerald-500 text-white' : (isPaymentConfirmed ? 'bg-amber-600 text-white' : 'bg-gray-300 dark:bg-gray-700 text-gray-500')"
                              x-text="isDriverConfirmed ? '✓' : '3'"></span>
                        <span>3. Driver Match</span>
                    </div>

                    <!-- Step 4: Dispatched & Confirmed -->
                    <div :class="{
                            'bg-emerald-500 text-white shadow-md font-black': isPaymentConfirmed && isDriverConfirmed,
                            'bg-gray-100 dark:bg-white/5 text-gray-400': !isDriverConfirmed
                        }"
                        class="p-2.5 rounded-2xl flex flex-col items-center gap-1 transition-all">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-black"
                              :class="isPaymentConfirmed && isDriverConfirmed ? 'bg-white text-emerald-600' : 'bg-gray-300 dark:bg-gray-700 text-gray-500'">4</span>
                        <span>4. Confirmed</span>
                    </div>

                </div>
            </div>

            <!-- DYNAMIC STATUS PANELS -->

            <!-- STATE A: PAYMENT PENDING (Choose Stripe or MoMo Pay) -->
            <div x-show="!isPaymentConfirmed" x-transition class="space-y-6">
                <div class="bg-white dark:bg-[#111] border-2 border-brand-400/50 dark:border-brand-500/30 rounded-3xl p-6 md:p-8 shadow-lg space-y-6">
                    
                    <div class="flex items-start justify-between">
                        <div class="space-y-1">
                            <span class="px-3 py-1 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300 text-xs font-extrabold uppercase tracking-wide inline-flex items-center gap-1.5">
                                <span>🔒</span> Payment Pre-Authorization Required
                            </span>
                            <h2 class="text-xl md:text-2xl font-black text-gray-900 dark:text-white pt-1">
                                Secure Payment Escrow Hold
                            </h2>
                            <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">
                                Select your preferred payment method. Funds are held safely in escrow and only captured once service is rendered. Driver contact channels unlock immediately after confirmation.
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Total Fare</span>
                            <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono">${{ number_format($totalAmount, 2) }} {{ $currency }}</span>
                        </div>
                    </div>

                    <!-- Payment Method Selectors -->
                    <div class="space-y-3">
                        <label class="block text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                            Choose Payment Method
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            
                            <!-- Method 1: Stripe (Cards & Apple Pay via Stripe) -->
                            <button type="button" @click="paymentMethod = 'stripe'"
                                    :class="paymentMethod === 'stripe' ? 'border-[#635BFF] bg-[#635BFF]/10 ring-2 ring-[#635BFF] text-gray-900 dark:text-white shadow-md' : 'border-gray-200 dark:border-white/10 hover:border-gray-300 dark:hover:border-white/20 text-gray-600 dark:text-gray-400'"
                                    class="p-4 rounded-2xl border text-left transition-all relative flex flex-col justify-between space-y-3 cursor-pointer">
                                <div class="flex items-center justify-between">
                                    <img src="/images/stripe-icon.svg" alt="Stripe" class="w-10 h-10 rounded-xl shadow-xs shrink-0 object-contain">
                                    <span x-show="paymentMethod === 'stripe'" class="w-2.5 h-2.5 rounded-full bg-[#635BFF]"></span>
                                </div>
                                <div>
                                    <div class="font-extrabold text-sm text-gray-900 dark:text-white">Stripe Checkout</div>
                                    <div class="text-[11px] text-gray-500">Cards, Apple Pay & Google Pay</div>
                                </div>
                            </button>

                            <!-- Method 2: MoMo Pay (Mobile Money) -->
                            <button type="button" @click="paymentMethod = 'momo'"
                                    :class="paymentMethod === 'momo' ? 'border-amber-500 bg-amber-500/10 ring-2 ring-amber-500 text-gray-900 dark:text-white shadow-md' : 'border-gray-200 dark:border-white/10 hover:border-gray-300 dark:hover:border-white/20 text-gray-600 dark:text-gray-400'"
                                    class="p-4 rounded-2xl border text-left transition-all relative flex flex-col justify-between space-y-3 cursor-pointer">
                                <div class="flex items-center justify-between">
                                    <img src="/images/momo-icon.svg" alt="MoMo Pay" class="w-10 h-10 rounded-xl shadow-xs shrink-0 object-contain">
                                    <span x-show="paymentMethod === 'momo'" class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                </div>
                                <div>
                                    <div class="font-extrabold text-sm text-gray-900 dark:text-white">MoMo Pay</div>
                                    <div class="text-[11px] text-gray-500">MTN • Telecel • AirtelTigo</div>
                                </div>
                            </button>

                        </div>
                    </div>

                    <!-- MoMo Pay Details Section (Revealed when MoMo Pay selected) -->
                    <div x-show="paymentMethod === 'momo'" x-transition class="p-5 bg-amber-50/70 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/40 rounded-2xl space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-amber-900 dark:text-amber-300 uppercase tracking-wider">Select Mobile Money Network</span>
                            <span class="text-[10px] font-extrabold text-amber-600 dark:text-amber-400 bg-amber-100 dark:bg-amber-900/50 px-2.5 py-1 rounded-full">Ghana MoMo Direct</span>
                        </div>
                        
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" @click="momoNetwork = 'MTN'"
                                    :class="momoNetwork === 'MTN' ? 'bg-amber-400 text-slate-950 font-black shadow' : 'bg-white dark:bg-[#1a1a1a] text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-white/10'"
                                    class="py-2.5 px-3 rounded-xl text-xs font-bold transition text-center">
                                🟡 MTN MoMo
                            </button>
                            <button type="button" @click="momoNetwork = 'Telecel'"
                                    :class="momoNetwork === 'Telecel' ? 'bg-red-500 text-white font-black shadow' : 'bg-white dark:bg-[#1a1a1a] text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-white/10'"
                                    class="py-2.5 px-3 rounded-xl text-xs font-bold transition text-center">
                                🔴 Telecel (Vodafone)
                            </button>
                            <button type="button" @click="momoNetwork = 'AT'"
                                    :class="momoNetwork === 'AT' ? 'bg-blue-600 text-white font-black shadow' : 'bg-white dark:bg-[#1a1a1a] text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-white/10'"
                                    class="py-2.5 px-3 rounded-xl text-xs font-bold transition text-center">
                                🔵 AirtelTigo Money
                            </button>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Mobile Money Subscriber Phone Number <span class="text-red-500">*</span>
                            </label>
                            <div class="flex">
                                <span class="inline-flex items-center px-4 rounded-l-xl border border-r-0 border-gray-300 dark:border-white/15 bg-gray-100 dark:bg-[#222] text-gray-700 dark:text-gray-300 font-bold text-xs">
                                    🇬🇭 +233
                                </span>
                                <input type="tel" x-model="momoPhone" placeholder="024 123 4567"
                                       class="flex-1 px-4 py-3 bg-white dark:bg-[#1a1a1a] border border-gray-300 dark:border-white/15 rounded-r-xl text-sm font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            </div>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                A prompt will be triggered on your mobile handset to approve the ${{ number_format($totalAmount, 2) }} {{ $currency }} escrow authorization hold.
                            </p>
                        </div>
                    </div>

                    <!-- Checkout Trigger Button -->
                    <div class="pt-2">
                        <!-- Case 1: Stripe Checkout (Cards, Apple Pay & Google Pay) -->
                        <template x-if="paymentMethod === 'stripe'">
                            <button type="button" @click="triggerStripePayment()"
                                    class="w-full py-4 px-6 bg-[#635BFF] hover:bg-[#5349e0] text-white font-black text-base rounded-2xl shadow-xl shadow-[#635BFF]/25 transition-all flex items-center justify-center gap-2 cursor-pointer">
                                <span>🔒 Pay ${{ number_format($totalAmount, 2) }} {{ $currency }} with Stripe</span>
                                <span>→</span>
                            </button>
                        </template>

                        <!-- Case 3: MoMo Pay -->
                        <template x-if="paymentMethod === 'momo'">
                            <button type="button" @click="submitPaymentHold('momo')"
                                    :disabled="isProcessing || !momoPhone"
                                    class="w-full py-4 px-6 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-base rounded-2xl shadow-xl transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                                <span x-show="!isProcessing">📱 Authorize MoMo Hold (${{ number_format($totalAmount, 2) }} {{ $currency }})</span>
                                <span x-show="isProcessing" class="flex items-center gap-2">
                                    <svg class="animate-spin h-5 w-5 text-slate-950" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    Waiting for MoMo Handset Approval...
                                </span>
                            </button>
                        </template>
                    </div>

                </div>
            </div>

            <!-- STATE B: PAYMENT HELD & ACTIVELY SEARCHING FOR DRIVER -->
            <div x-show="isPaymentConfirmed && !isDriverConfirmed" x-transition class="space-y-6">
                
                <div class="bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-transparent border-2 border-amber-400 dark:border-amber-600/60 rounded-3xl p-6 md:p-8 shadow-xl text-center space-y-6 relative overflow-hidden">
                    
                    <!-- Pulsing Radar Animation Background -->
                    <div class="relative w-28 h-28 mx-auto flex items-center justify-center">
                        <div class="absolute inset-0 rounded-full bg-amber-400/20 animate-ping"></div>
                        <div class="absolute inset-2 rounded-full bg-amber-500/30 animate-pulse"></div>
                        <div class="relative w-16 h-16 rounded-full bg-amber-500 text-slate-950 flex items-center justify-center text-3xl font-black shadow-lg">
                            🚘
                        </div>
                    </div>

                    <div class="space-y-2 max-w-md mx-auto">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-300 font-extrabold text-xs">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            ✓ Escrow Payment Held (${{ number_format($totalAmount, 2) }} {{ $currency }})
                        </span>
                        <h3 class="text-2xl font-black text-gray-900 dark:text-white">
                            Searching for Available Driver...
                        </h3>
                        <p class="text-xs md:text-sm text-gray-600 dark:text-gray-300">
                            We are broadcasting your pickup request to vetted chauffeurs near <strong class="text-gray-900 dark:text-white">{{ $pickupLocation }}</strong>. 
                            Your payment is held safely in escrow and driver contact details will appear the moment a driver confirms.
                        </p>
                    </div>

                    <!-- Live Dispatch Steps Pulse -->
                    <div class="max-w-lg mx-auto bg-white/70 dark:bg-[#161616]/70 backdrop-blur rounded-2xl p-4 border border-amber-200/60 dark:border-white/10 grid grid-cols-3 gap-2 text-center text-[11px] font-bold">
                        <div class="p-2 rounded-xl bg-emerald-100/70 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300">
                            ✓ Payment Held
                        </div>
                        <div class="p-2 rounded-xl bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-200 flex items-center justify-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                            Radar Dispatch
                        </div>
                        <div class="p-2 rounded-xl bg-gray-100 dark:bg-white/5 text-gray-400">
                            Awaiting Accept
                        </div>
                    </div>

                    <!-- Instant Confirm Simulation Button for Testing/Demo -->
                    <div class="pt-2 flex justify-center">
                        <button type="button" @click="confirmDriverAssignment()"
                                :disabled="isSimulatingMatch"
                                class="px-6 py-3 bg-brand-500 hover:bg-brand-600 text-slate-950 font-black text-xs rounded-xl shadow-md transition flex items-center gap-2">
                            <span x-show="!isSimulatingMatch">⚡ Connect & Confirm Nearest Driver Now</span>
                            <span x-show="isSimulatingMatch" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-slate-950" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Matching Chauffeur...
                            </span>
                        </button>
                    </div>

                </div>

            </div>

            <!-- STATE C: DRIVER CONFIRMED & DISPATCHED (All Contacts Unlocked) -->
            <div x-show="isPaymentConfirmed && isDriverConfirmed" x-transition class="space-y-4">
                
                <div class="bg-emerald-50 dark:bg-emerald-950/30 border-2 border-emerald-400 dark:border-emerald-700/50 rounded-3xl p-6 shadow-sm text-emerald-900 dark:text-emerald-200 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center font-black text-2xl shrink-0 shadow-md">
                            ✓
                        </div>
                        <div>
                            <h3 class="font-black text-lg">🎉 Driver Confirmed & Assigned!</h3>
                            <p class="text-xs text-emerald-700 dark:text-emerald-300 mt-0.5">
                                Your assigned chauffeur has confirmed your booking. Payment is secured and all direct contact channels (Phone Call, WhatsApp, Email) are now live.
                            </p>
                        </div>
                    </div>

                    <div class="bg-white/80 dark:bg-black/40 rounded-2xl p-4 border border-emerald-200 dark:border-emerald-800/30 grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                        <div>
                            <span class="text-gray-400 font-bold block uppercase tracking-wider text-[10px]">Payment Status</span>
                            <span class="font-black text-emerald-600 dark:text-emerald-400 uppercase">✓ Escrow Held / Paid</span>
                        </div>
                        <div>
                            <span class="text-gray-400 font-bold block uppercase tracking-wider text-[10px]">Amount</span>
                            <span class="font-black text-gray-900 dark:text-white">${{ number_format($totalAmount, 2) }} {{ $currency }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 font-bold block uppercase tracking-wider text-[10px]">Payment Method</span>
                            <span class="font-extrabold text-gray-900 dark:text-white uppercase" x-text="paymentMethod || '{{ $paidMethod ?? 'Stripe Secure Card' }}'"></span>
                        </div>
                        <div>
                            <span class="text-gray-400 font-bold block uppercase tracking-wider text-[10px]">Driver Status</span>
                            <span class="font-black text-emerald-600 dark:text-emerald-400">✓ Confirmed</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Booking Overview Card -->
            <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 md:p-8 shadow-sm space-y-6">
                
                <div class="flex justify-between items-center pb-4 border-b border-gray-100 dark:border-white/10">
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Booking ID</span>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white font-mono">{{ $bookingCode }}</h3>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Service Type</span>
                        <div class="text-sm font-extrabold text-amber-700 dark:text-brand-400 font-black capitalize">{{ str_replace('_', ' ', $serviceType) }}</div>
                    </div>
                </div>

                <!-- Locations & Schedule Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div class="p-4 bg-gray-50 dark:bg-white/5 rounded-2xl space-y-1 border border-gray-100 dark:border-white/5">
                        <span class="font-extrabold text-gray-400 uppercase tracking-wider block">📍 Pickup Address</span>
                        <p class="font-bold text-gray-900 dark:text-white text-sm">{{ $pickupLocation }}</p>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-white/5 rounded-2xl space-y-1 border border-gray-100 dark:border-white/5">
                        <span class="font-extrabold text-gray-400 uppercase tracking-wider block">🏁 Destination Address</span>
                        <p class="font-bold text-gray-900 dark:text-white text-sm">{{ $dropoffLocation }}</p>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-white/5 rounded-2xl space-y-1 border border-gray-100 dark:border-white/5">
                        <span class="font-extrabold text-gray-400 uppercase tracking-wider block">📅 Pickup Date & Time</span>
                        <p class="font-bold text-gray-900 dark:text-white text-sm">{{ $pickupDate }} at {{ $pickupTime }}</p>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-white/5 rounded-2xl space-y-1 border border-gray-100 dark:border-white/5">
                        <span class="font-extrabold text-gray-400 uppercase tracking-wider block">💵 Total Approved Fare</span>
                        <p class="font-black text-emerald-600 dark:text-emerald-400 text-lg">${{ number_format($totalAmount, 2) }} {{ $currency }}</p>
                    </div>
                </div>

                <!-- ASSIGNED DRIVER CARD: LOCKED VS SEARCHING VS UNLOCKED -->
                <!-- Case 1: PAYMENT NOT CONFIRMED -> HIDE ALL DRIVER DETAILS & SHOW PRIVACY SHIELD -->
                <div x-show="!isPaymentConfirmed" class="border border-amber-300/80 dark:border-amber-600/30 rounded-2xl overflow-hidden bg-gradient-to-br from-amber-50/60 via-white to-amber-50/30 dark:from-amber-950/20 dark:via-[#141414] dark:to-black shadow-sm p-6 space-y-4">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-500/10 dark:bg-amber-500/20 border border-amber-500/30 text-amber-600 dark:text-amber-400 flex items-center justify-center text-2xl shrink-0 shadow-inner">
                            🛡️
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-wider">Driver Details Protected</span>
                                <span class="px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-300 font-extrabold text-[10px] uppercase tracking-wider">
                                    🔒 Escrow Payment Required
                                </span>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed font-medium">
                                Chauffeur identity, vehicle information, photo, and direct contact details are strictly hidden for privacy. They will unlock automatically after you authorize your payment (Stripe or MoMo Pay) and a verified driver confirms your booking.
                            </p>
                        </div>
                    </div>

                    <div class="p-3.5 bg-amber-500/10 border border-amber-500/20 rounded-xl flex items-center justify-between text-xs text-amber-900 dark:text-amber-200">
                        <span class="flex items-center gap-2 font-bold">
                            <span>💳</span> Select Stripe or MoMo Pay above to authorize escrow hold
                        </span>
                        <span class="text-[11px] font-black uppercase text-amber-700 dark:text-amber-400">Step 2 of 4</span>
                    </div>
                </div>

                <!-- Case 2: PAYMENT CONFIRMED BUT DRIVER STILL SEARCHING -> SEARCHING RADAR CARD -->
                <div x-show="isPaymentConfirmed && !isDriverConfirmed" class="border border-amber-300/80 dark:border-amber-600/30 rounded-2xl overflow-hidden bg-gray-50 dark:bg-white/5 shadow-sm p-6 text-center space-y-3">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300 text-xs font-black">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                        Radar Broadcast Active • Searching for Nearest Driver
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                        Payment is safely held in escrow. Driver contact, photo, and vehicle information will appear right here the moment a vetted driver confirms your pickup request.
                    </p>
                </div>

                <!-- Case 3: PAYMENT CONFIRMED AND DRIVER CONFIRMED -> UNLOCKED FULL DRIVER CARD -->
                <div x-show="isPaymentConfirmed && isDriverConfirmed && driver" class="border border-emerald-300 dark:border-emerald-700/50 rounded-2xl overflow-hidden bg-white dark:bg-[#161616] shadow-md">
                    <!-- Driver Profile Header -->
                    <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <img :src="driver?.photo_url || ('https://ui-avatars.com/api/?name=' + encodeURIComponent(driver?.name || 'Driver') + '&background=0F172A&color=FFFFFF&size=256&bold=true')" 
                                     alt="Driver Photo" 
                                     class="w-14 h-14 rounded-full object-cover border-2 border-emerald-500 shadow-sm">
                                <span class="absolute bottom-0 right-0 w-4 h-4 rounded-full border-2 border-white dark:border-[#111] bg-emerald-500"></span>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Assigned Driver</span>
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 font-extrabold text-[10px]">
                                        ✓ Confirmed & Dispatched
                                    </span>
                                </div>
                                <h4 class="font-extrabold text-base text-gray-900 dark:text-white" x-text="driver?.name"></h4>
                                <p class="text-xs text-gray-600 dark:text-gray-300">
                                    <span x-text="driver?.vehicle || 'Executive Vehicle'"></span> • ⭐ <span x-text="driver?.rating || '4.95'"></span>
                                </p>
                            </div>
                        </div>

                        <!-- Action State: UNLOCKED (Call, WhatsApp, Email) -->
                        <div class="shrink-0 flex flex-wrap sm:flex-nowrap items-center gap-2">
                            <!-- 1. Call Button -->
                            <template x-if="driver?.phone">
                                <a :href="'tel:' + driver.phone" 
                                   class="px-4 py-2.5 bg-brand-500 hover:bg-brand-600 text-slate-950 rounded-xl text-xs font-black shadow-sm transition flex items-center gap-1.5">
                                    <span>📞</span> Call Driver
                                </a>
                            </template>

                            <!-- 2. WhatsApp Button -->
                            <template x-if="driver?.whatsapp">
                                <a :href="'https://wa.me/' + driver.whatsapp + '?text=' + encodeURIComponent('Hello ' + (driver?.name || 'Driver') + ', I am your passenger for RideMyCars booking #' + bookingCode + '.')"
                                   target="_blank"
                                   class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black shadow-sm transition flex items-center gap-1.5">
                                    <span>💬</span> WhatsApp
                                </a>
                            </template>

                            <!-- 3. Email Button -->
                            <template x-if="driver?.email">
                                <a :href="'mailto:' + driver.email + '?subject=' + encodeURIComponent('RideMyCars Booking #' + bookingCode)"
                                   class="px-3.5 py-2.5 bg-gray-200 hover:bg-gray-300 dark:bg-white/10 dark:hover:bg-white/20 text-gray-900 dark:text-white rounded-xl text-xs font-black shadow-sm transition flex items-center gap-1.5">
                                    <span>✉️</span> Email
                                </a>
                            </template>
                        </div>
                    </div>

                    <!-- Unlocked Contact Bar Details -->
                    <div class="px-5 py-3 bg-emerald-500/10 border-t border-emerald-500/20 text-xs text-emerald-900 dark:text-emerald-200 flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-4 text-[11px]">
                            <span>📞 Phone: <strong class="font-mono text-gray-900 dark:text-white" x-text="driver?.phone"></strong></span>
                            <span>💬 WhatsApp: <strong class="font-mono text-emerald-600 dark:text-emerald-400" x-text="driver?.phone"></strong></span>
                            <span>✉️ Email: <strong class="text-gray-900 dark:text-white" x-text="driver?.email"></strong></span>
                        </div>
                        <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-300">
                            ✓ Direct Driver Communication Open
                        </span>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- Embedded Stripe Checkout Modal Component -->
    <x-stripe-modal :serviceType="$serviceType" :serviceId="$serviceId" :amount="$totalAmount" :currency="$currency" />

    <script>
    function verificationPage(config) {
        return {
            serviceType: config.serviceType,
            serviceId: config.serviceId,
            currentVerificationStatus: config.initialVerificationStatus,
            currentPaymentStatus: config.initialPaymentStatus,
            bookingStatus: config.initialBookingStatus,
            isPaymentConfirmed: config.initialIsPaymentConfirmed,
            isDriverConfirmed: config.initialIsDriverConfirmed,
            totalAmount: config.totalAmount,
            currency: config.currency,
            driver: (config.initialIsPaymentConfirmed && config.initialIsDriverConfirmed) ? config.driverData : null,
            bookingCode: config.bookingCode,
            paymentMethod: (config.initialPaymentMethod === 'momo' || config.initialPaymentMethod === 'mobile_money') ? 'momo' : 'stripe',
            momoNetwork: 'MTN',
            momoPhone: config.customerPhone || '',
            isProcessing: false,
            isSimulatingMatch: false,
            pollingInterval: null,

            initPage() {
                // If payment is held and driver not yet confirmed, poll for driver acceptance
                if (this.isPaymentConfirmed && !this.isDriverConfirmed) {
                    this.startPolling();
                }
            },

            startPolling() {
                if (this.pollingInterval) clearInterval(this.pollingInterval);
                this.pollingInterval = setInterval(() => this.checkStatus(), 2500);
            },

            async checkStatus() {
                try {
                    const res = await fetch(`/api/payment/verification-status/${this.serviceType}/${this.serviceId}`);
                    if (res.ok) {
                        const data = await res.json();
                        if (data.success) {
                            this.currentVerificationStatus = data.verification_status;
                            this.currentPaymentStatus = data.payment_status;
                            this.bookingStatus = data.booking_status;
                            this.isPaymentConfirmed = data.is_payment_confirmed;
                            this.isDriverConfirmed = data.is_driver_confirmed;

                            if (this.isPaymentConfirmed && this.isDriverConfirmed && data.driver) {
                                this.driver = Object.assign({}, this.driver || {}, data.driver);
                            } else if (!this.isPaymentConfirmed || !this.isDriverConfirmed) {
                                this.driver = null;
                            }

                            if (this.isDriverConfirmed && this.pollingInterval) {
                                clearInterval(this.pollingInterval);
                            }
                        }
                    }
                } catch (e) {
                    console.error("Status polling error:", e);
                }
            },

            triggerStripePayment() {
                window.dispatchEvent(new CustomEvent('open-stripe-modal', {
                    detail: {
                        serviceType: this.serviceType,
                        serviceId: this.serviceId,
                        amount: this.totalAmount,
                        currency: this.currency
                    }
                }));
            },

            async submitPaymentHold(method) {
                this.isProcessing = true;
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const res = await fetch('/api/payment/authorize-hold', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            service_type: this.serviceType,
                            service_id: this.serviceId,
                            payment_method: method,
                            momo_phone: this.momoPhone,
                            momo_network: this.momoNetwork
                        })
                    });

                    const data = await res.json();
                    if (data.success) {
                        if (data.checkout_url) {
                            window.location.href = data.checkout_url;
                            return;
                        }
                        this.isPaymentConfirmed = true;
                        this.currentPaymentStatus = 'hold';
                        this.startPolling();
                    } else {
                        alert(data.message || 'Payment hold authorization failed. Please try again.');
                    }
                } catch (e) {
                    console.error("Payment hold error:", e);
                    alert("Network error processing payment hold.");
                } finally {
                    this.isProcessing = false;
                }
            },

            async confirmDriverAssignment() {
                this.isSimulatingMatch = true;
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const res = await fetch('/api/payment/confirm-driver', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            service_type: this.serviceType,
                            service_id: this.serviceId
                        })
                    });

                    const data = await res.json();
                    if (data.success) {
                        this.isDriverConfirmed = true;
                        this.currentVerificationStatus = 'driver_verified';
                        this.bookingStatus = 'accepted';
                        if (data.driver) {
                            this.driver = Object.assign({}, this.driver, data.driver);
                        }
                    }
                } catch (e) {
                    console.error("Driver match error:", e);
                } finally {
                    this.isSimulatingMatch = false;
                }
            }
        }
    }
    </script>
</x-layout>
