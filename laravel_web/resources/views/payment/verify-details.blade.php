<x-layout title="Payment Details & Driver Verification — RideMyCars">
    <div x-data="verificationPage('{{ $serviceType }}', {{ $serviceId }}, '{{ $verificationStatus }}', '{{ $paymentStatus }}')"
         x-init="initPolling()"
         class="min-h-screen bg-gray-50 dark:bg-[#09090b] py-12 px-4 sm:px-6 lg:px-8">

        <div class="max-w-3xl mx-auto space-y-8">
            
            <!-- Page Header & Title -->
            <div class="text-center space-y-2">
                <span class="px-3.5 py-1.5 rounded-full bg-brand-500/10 text-brand-600 dark:text-brand-400 font-extrabold text-xs uppercase tracking-widest border border-brand-500/20">
                    Stripe Checkout Verification
                </span>
                <h1 class="text-3xl md:text-4xl font-black text-gray-900 dark:text-white tracking-tight">
                    Payment Details & Driver Verification
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Review your booking information. Your assigned driver must verify details before payment processing.
                </p>
            </div>

            <!-- 4-Step Progress Indicator -->
            <div class="bg-white dark:bg-[#111] p-5 rounded-3xl border border-gray-200 dark:border-white/10 shadow-sm">
                <div class="grid grid-cols-4 gap-2 text-center text-xs font-extrabold">
                    
                    <!-- Step 1: Submit Details -->
                    <div class="p-2.5 rounded-2xl bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 flex flex-col items-center gap-1">
                        <span class="w-6 h-6 rounded-full bg-green-500 text-white flex items-center justify-center text-xs font-black">✓</span>
                        <span>1. Details</span>
                    </div>

                    <!-- Step 2: Driver Verification -->
                    <div :class="{
                            'bg-amber-500 text-white shadow-md animate-pulse': currentVerificationStatus === 'pending_verification',
                            'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300': currentVerificationStatus === 'driver_verified',
                            'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300': currentVerificationStatus === 'rejected',
                            'bg-gray-100 dark:bg-white/5 text-gray-400': currentVerificationStatus === 'unverified'
                        }"
                        class="p-2.5 rounded-2xl flex flex-col items-center gap-1 transition-all">
                        <span class="w-6 h-6 rounded-full bg-current text-white flex items-center justify-center text-xs font-black"
                              x-text="currentVerificationStatus === 'driver_verified' ? '✓' : (currentVerificationStatus === 'rejected' ? '✗' : '2')"></span>
                        <span>2. Driver Verification</span>
                    </div>

                    <!-- Step 3: Secure Payment -->
                    <div :class="{
                            'bg-brand-500 text-slate-950 shadow-md font-black': currentVerificationStatus === 'driver_verified' && currentPaymentStatus !== 'paid',
                            'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300': currentPaymentStatus === 'paid',
                            'bg-gray-100 dark:bg-white/5 text-gray-400': currentVerificationStatus !== 'driver_verified' && currentPaymentStatus !== 'paid'
                        }"
                        class="p-2.5 rounded-2xl flex flex-col items-center gap-1 transition-all">
                        <span class="w-6 h-6 rounded-full bg-current text-white flex items-center justify-center text-xs font-black"
                              x-text="currentPaymentStatus === 'paid' ? '✓' : '3'"></span>
                        <span>3. Payment</span>
                    </div>

                    <!-- Step 4: Confirmation -->
                    <div :class="{
                            'bg-green-500 text-white shadow-md': currentPaymentStatus === 'paid',
                            'bg-gray-100 dark:bg-white/5 text-gray-400': currentPaymentStatus !== 'paid'
                        }"
                        class="p-2.5 rounded-2xl flex flex-col items-center gap-1 transition-all">
                        <span class="w-6 h-6 rounded-full bg-current text-white flex items-center justify-center text-xs font-black">4</span>
                        <span>4. Confirmed</span>
                    </div>

                </div>
            </div>

            <!-- Dynamic Status Alert Box -->
            
            <!-- State 1: PENDING VERIFICATION -->
            <div x-show="currentVerificationStatus === 'pending_verification'" x-transition
                 class="bg-amber-50 dark:bg-amber-950/30 border-2 border-amber-300 dark:border-amber-700/50 rounded-3xl p-6 shadow-sm text-amber-900 dark:text-amber-200 space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center font-black animate-spin shrink-0">
                        🔄
                    </div>
                    <div>
                        <h3 class="font-extrabold text-base">Pending Driver Verification</h3>
                        <p class="text-xs text-amber-700 dark:text-amber-300 mt-0.5">
                            Your details have been submitted successfully. Please wait for the assigned driver to verify your booking.
                        </p>
                    </div>
                </div>
                <div class="text-[11px] font-bold text-amber-600 dark:text-amber-400 bg-amber-100/60 dark:bg-amber-900/40 p-2.5 rounded-xl flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                    <span>Live Driver Sync Active (Checking for driver response...)</span>
                </div>
            </div>

            <!-- State 0: PAYMENT ALREADY COMPLETED -->
            <template x-if="currentPaymentStatus === 'paid'">
                <div class="bg-emerald-50 dark:bg-emerald-950/30 border-2 border-emerald-400 dark:border-emerald-700/50 rounded-3xl p-6 shadow-sm text-emerald-900 dark:text-emerald-200 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center font-black text-2xl shrink-0 shadow-md">
                            ✓
                        </div>
                        <div>
                            <h3 class="font-black text-lg">✓ Payment Already Completed</h3>
                            <p class="text-xs text-emerald-700 dark:text-emerald-300 mt-0.5">
                                This booking has been fully paid. Transaction Reference: <strong class="font-mono">{{ $transactionRef ?? 'N/A' }}</strong>
                            </p>
                        </div>
                    </div>

                    <div class="bg-white/80 dark:bg-black/40 rounded-2xl p-4 border border-emerald-200 dark:border-emerald-800/30 grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                        <div>
                            <span class="text-gray-400 font-bold block uppercase tracking-wider text-[10px]">Payment Status</span>
                            <span class="font-black text-emerald-600 dark:text-emerald-400 uppercase">✓ Paid</span>
                        </div>
                        <div>
                            <span class="text-gray-400 font-bold block uppercase tracking-wider text-[10px]">Amount Paid</span>
                            <span class="font-black text-gray-900 dark:text-white">${{ number_format($totalAmount, 2) }} {{ $currency }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 font-bold block uppercase tracking-wider text-[10px]">Payment Method</span>
                            <span class="font-extrabold text-gray-900 dark:text-white">{{ $paidMethod ?? 'Stripe Secure Card' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 font-bold block uppercase tracking-wider text-[10px]">Payment Date</span>
                            <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $paidAt ?? date('M d, Y') }}</span>
                        </div>
                    </div>

                    <div class="pt-2 flex flex-col sm:flex-row gap-3">
                        <a href="/my-rides" class="px-6 py-3 bg-black hover:bg-gray-800 dark:bg-white dark:text-black dark:hover:bg-gray-100 text-white font-extrabold text-xs rounded-xl shadow-md transition-all text-center">
                            View My Bookings →
                        </a>
                        <button type="button" onclick="window.print()" class="px-5 py-3 border border-emerald-300 dark:border-emerald-700 text-emerald-800 dark:text-emerald-200 font-bold text-xs rounded-xl hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition text-center">
                            📄 Download Receipt
                        </button>
                    </div>
                </div>
            </template>

            <!-- State 2: DRIVER VERIFIED (APPROVED) OR DIRECT PAYMENT -->
            <template x-if="currentPaymentStatus !== 'paid'">
                <div class="bg-green-50 dark:bg-green-950/30 border-2 border-green-400 dark:border-green-700/50 rounded-3xl p-6 shadow-sm text-green-900 dark:text-green-200 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-green-500 text-white flex items-center justify-center font-black text-xl shrink-0">
                            ✓
                        </div>
                        <div>
                            <h3 class="font-extrabold text-base">🎉 Booking Ready for Checkout!</h3>
                            <p class="text-xs text-green-700 dark:text-green-300 mt-0.5">
                                Your booking request is confirmed. You may now proceed with secure Stripe PCI-DSS Payment.
                            </p>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="button" @click="triggerStripePayment()"
                                class="w-full py-4 px-6 bg-black hover:bg-gray-800 dark:bg-white dark:text-black dark:hover:bg-gray-100 text-white font-black text-base rounded-2xl shadow-xl transition-all flex items-center justify-center gap-2">
                            <span>💳 Pay ${{ number_format($totalAmount, 2) }} {{ $currency }} with Stripe</span>
                            <span>→</span>
                        </button>
                    </div>
                </div>
            </template>

            <!-- State 3: REJECTED -->
            <div x-show="currentVerificationStatus === 'rejected'" x-transition
                 class="bg-red-50 dark:bg-red-950/30 border-2 border-red-300 dark:border-red-700/50 rounded-3xl p-6 shadow-sm text-red-900 dark:text-red-200 space-y-3">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-red-500 text-white flex items-center justify-center font-black text-xl shrink-0 mt-0.5">
                        ⚠️
                    </div>
                    <div class="space-y-1">
                        <h3 class="font-extrabold text-base">Verification Rejected by Driver</h3>
                        <p class="text-xs text-red-700 dark:text-red-300">
                            <strong>Reason:</strong> <span x-text="rejectionReason || '{{ $rejectionReason ?? 'Driver declined requested pickup schedule or details.' }}'"></span>
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 pt-1">
                            Your payment has NOT been processed. You may modify your details and submit for re-verification.
                        </p>
                    </div>
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="button" onclick="window.history.back()" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-xs shadow-md">
                        🔄 Modify Details & Resubmit
                    </button>
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
                        <div class="text-sm font-extrabold text-brand-500 capitalize">{{ str_replace('_', ' ', $serviceType) }}</div>
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

                <!-- Assigned Driver Card -->
                @if(!empty($driver))
                    <div class="p-5 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-2xl flex items-center justify-between shadow-sm">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 rounded-full bg-brand-500 text-slate-950 flex items-center justify-center font-bold text-lg border-2 border-brand-300 shadow-sm">
                                👨‍✈️
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Assigned Driver</span>
                                <h4 class="font-extrabold text-base text-gray-900 dark:text-white">{{ $driver['name'] }}</h4>
                                <p class="text-xs text-gray-600 dark:text-gray-300">{{ $driver['vehicle'] ?? 'Executive Vehicle' }} • ⭐ {{ $driver['rating'] ?? 4.9 }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <a href="tel:{{ $driver['phone'] }}" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-slate-950 rounded-xl text-xs font-black shadow-sm transition">
                                📞 Call Driver
                            </a>
                        </div>
                    </div>
                @endif

            </div>

        </div>
    </div>

    <!-- Embedded Stripe Checkout Modal Component -->
    <x-stripe-modal :serviceType="$serviceType" :serviceId="$serviceId" :amount="$totalAmount" :currency="$currency" />

    <script>
    function verificationPage(serviceType, serviceId, initialVerificationStatus, initialPaymentStatus) {
        return {
            serviceType: serviceType,
            serviceId: serviceId,
            currentVerificationStatus: initialVerificationStatus,
            currentPaymentStatus: initialPaymentStatus,
            rejectionReason: '',
            pollingInterval: null,

            initPolling() {
                if (this.currentVerificationStatus === 'pending_verification') {
                    this.pollingInterval = setInterval(() => this.checkStatus(), 3000);
                }
            },

            async checkStatus() {
                try {
                    const res = await fetch(`/api/payment/verification-status/${this.serviceType}/${this.serviceId}`);
                    if (res.ok) {
                        const data = await res.json();
                        if (data.success) {
                            this.currentVerificationStatus = data.verification_status;
                            this.currentPaymentStatus = data.payment_status;
                            this.rejectionReason = data.rejection_reason || '';

                            if (this.currentVerificationStatus !== 'pending_verification' && this.pollingInterval) {
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
                        amount: {{ $totalAmount }},
                        currency: '{{ $currency }}'
                    }
                }));
            }
        }
    }
    </script>
</x-layout>
