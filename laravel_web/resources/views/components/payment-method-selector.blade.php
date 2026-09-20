@props([
    'modelName' => 'paymentMethod',
    'phoneModel' => 'momoPhone',
    'networkModel' => 'momoNetwork',
    'showMomoDetails' => true,
    'showSecurityBadge' => true,
    'inputName' => 'payment_method',
    'momoInputName' => 'momo_phone',
    'allowCash' => false,
])

<div class="space-y-3" x-cloak>
    <label class="block text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">
        Select Payment Method *
    </label>

    <!-- Hidden form input for standard form submission -->
    <input type="hidden" name="{{ $inputName }}" :value="{{ $modelName }}">

    <!-- Payment Method Cards (Compact, Sleek, Professional 2-Tier Layout) -->
    <div class="space-y-2.5">
        
        <!-- BUTTON 1: STRIPE -->
        <button type="button" 
                @click="{{ $modelName }} = 'stripe'"
                :class="{{ $modelName }} === 'stripe' 
                    ? 'border-[#635BFF] bg-gradient-to-r from-[#635BFF]/[0.06] via-white to-transparent dark:from-[#635BFF]/20 dark:via-[#141416] dark:to-transparent ring-2 ring-[#635BFF] text-gray-900 dark:text-white shadow-sm' 
                    : 'border-gray-200 dark:border-white/10 bg-white dark:bg-[#141416] hover:border-gray-300 dark:hover:border-white/20 text-gray-700 dark:text-gray-300'"
                class="w-full p-3 sm:px-4 sm:py-3 rounded-2xl border text-left transition-all duration-150 flex flex-col gap-2.5 cursor-pointer group">
            
            <!-- TOP ROW: Icon + Title + Pill Badge + Radio Checkmark -->
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                    <!-- Stripe Icon -->
                    <div class="w-8 h-8 rounded-xl bg-[#635BFF]/10 dark:bg-[#635BFF]/20 p-1 border border-[#635BFF]/20 flex items-center justify-center shrink-0 shadow-2xs">
                        <img src="/images/stripe-icon.svg" alt="Stripe" class="w-full h-full object-contain">
                    </div>

                    <!-- Title & Badge (Always on one line, never wraps vertically) -->
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="font-black text-sm text-gray-900 dark:text-white whitespace-nowrap">Stripe</span>
                        <span class="inline-flex items-center text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-[#635BFF]/10 text-[#635BFF] dark:bg-[#635BFF]/25 dark:text-[#a29bfe] whitespace-nowrap">
                            Cards &amp; Apple Pay
                        </span>
                    </div>
                </div>

                <!-- Radio Checkmark Indicator -->
                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 transition-colors ml-2"
                     :class="{{ $modelName }} === 'stripe' ? 'border-[#635BFF] bg-[#635BFF]' : 'border-gray-300 dark:border-gray-600 bg-transparent'">
                    <div x-show="{{ $modelName }} === 'stripe'" class="w-2 h-2 rounded-full bg-white"></div>
                </div>
            </div>

            <!-- BOTTOM ROW: Logos Strip + Description -->
            <div class="flex items-center justify-between gap-2 pt-2 border-t border-gray-100 dark:border-white/5 w-full">
                <!-- 5 Logos in single crisp row -->
                <div class="flex items-center gap-1.5 shrink-0 flex-nowrap">
                    <img src="/images/payment-icons/visa.svg" alt="Visa" class="h-4.5 sm:h-5 w-auto rounded shadow-2xs transition-transform group-hover:scale-105" loading="lazy">
                    <img src="/images/payment-icons/mastercard.svg" alt="Mastercard" class="h-4.5 sm:h-5 w-auto rounded shadow-2xs transition-transform group-hover:scale-105" loading="lazy">
                    <img src="/images/payment-icons/discover.svg" alt="Discover" class="h-4.5 sm:h-5 w-auto rounded shadow-2xs transition-transform group-hover:scale-105" loading="lazy">
                    <img src="/images/payment-icons/amex.svg" alt="AMEX" class="h-4.5 sm:h-5 w-auto rounded shadow-2xs transition-transform group-hover:scale-105" loading="lazy">
                    <img src="/images/payment-icons/apple-pay.svg" alt="Apple Pay" class="h-4.5 sm:h-5 w-auto rounded shadow-2xs transition-transform group-hover:scale-105" loading="lazy">
                </div>

                <!-- Subtitle on the right -->
                <span class="text-[11px] text-gray-500 dark:text-gray-400 font-medium whitespace-nowrap">
                    Credit / Debit, Apple Pay
                </span>
            </div>
        </button>

        <!-- BUTTON 2: MOMO PAY -->
        <button type="button" 
                @click="{{ $modelName }} = 'momo'"
                :class="{{ $modelName }} === 'momo' 
                    ? 'border-amber-500 bg-gradient-to-r from-amber-500/[0.06] via-white to-transparent dark:from-amber-500/20 dark:via-[#141416] dark:to-transparent ring-2 ring-amber-500 text-gray-900 dark:text-white shadow-sm' 
                    : 'border-gray-200 dark:border-white/10 bg-white dark:bg-[#141416] hover:border-gray-300 dark:hover:border-white/20 text-gray-700 dark:text-gray-300'"
                class="w-full p-3 sm:px-4 sm:py-3 rounded-2xl border text-left transition-all duration-150 flex flex-col gap-2.5 cursor-pointer group">
            
            <!-- TOP ROW: Icon + Title + Pill Badge + Radio Checkmark -->
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                    <!-- MoMo Icon -->
                    <div class="w-8 h-8 rounded-lg bg-amber-400 p-0.5 border border-amber-300/60 flex items-center justify-center shrink-0 shadow-2xs">
                        <img src="/images/momo-icon.svg" alt="MoMo Pay" class="w-full h-full object-contain">
                    </div>

                    <!-- Title & Badge -->
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="font-black text-sm text-gray-900 dark:text-white whitespace-nowrap">MoMo Pay</span>
                        <span class="inline-flex items-center text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-amber-200 text-amber-900 dark:bg-amber-900/60 dark:text-amber-300 whitespace-nowrap">
                            Mobile Money
                        </span>
                    </div>
                </div>

                <!-- Radio Checkmark Indicator -->
                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 transition-colors ml-2"
                     :class="{{ $modelName }} === 'momo' ? 'border-amber-500 bg-amber-500' : 'border-gray-300 dark:border-gray-600 bg-transparent'">
                    <div x-show="{{ $modelName }} === 'momo'" class="w-2 h-2 rounded-full bg-slate-950"></div>
                </div>
            </div>

            <!-- BOTTOM ROW: Logos Strip + Description -->
            <div class="flex items-center justify-between gap-2 pt-2 border-t border-gray-100 dark:border-white/5 w-full">
                <!-- 3 Network Logos in single crisp row -->
                <div class="flex items-center gap-1.5 shrink-0 flex-nowrap">
                    <img src="/images/payment-icons/mtn-momo.svg" alt="MTN MoMo" class="h-4.5 sm:h-5 w-auto rounded shadow-2xs transition-transform group-hover:scale-105" loading="lazy">
                    <img src="/images/payment-icons/telecel.svg" alt="Telecel" class="h-4.5 sm:h-5 w-auto rounded shadow-2xs transition-transform group-hover:scale-105" loading="lazy">
                    <img src="/images/payment-icons/airteltigo.svg" alt="AirtelTigo" class="h-4.5 sm:h-5 w-auto rounded shadow-2xs transition-transform group-hover:scale-105" loading="lazy">
                </div>

                <!-- Subtitle on the right -->
                <span class="text-[11px] text-gray-500 dark:text-gray-400 font-medium whitespace-nowrap">
                    Instant USSD Push
                </span>
            </div>
        </button>

        @if($allowCash)
        <!-- BUTTON 3: CASH (Direct Pay on Drop-off) -->
        <button type="button" 
                @click="{{ $modelName }} = 'cash'"
                :class="{{ $modelName }} === 'cash' 
                    ? 'border-emerald-500 bg-gradient-to-r from-emerald-500/[0.06] via-white to-transparent dark:from-emerald-500/20 dark:via-[#141416] dark:to-transparent ring-2 ring-emerald-500 text-gray-900 dark:text-white shadow-sm' 
                    : 'border-gray-200 dark:border-white/10 bg-white dark:bg-[#141416] hover:border-gray-300 dark:hover:border-white/20 text-gray-700 dark:text-gray-300'"
                class="w-full p-3 sm:px-4 sm:py-3 rounded-2xl border text-left transition-all duration-150 flex flex-col gap-2.5 cursor-pointer group">
            
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 border border-emerald-300/40 flex items-center justify-center text-base shrink-0 shadow-2xs">
                        💵
                    </div>
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="font-black text-sm text-gray-900 dark:text-white whitespace-nowrap">Cash</span>
                        <span class="inline-flex items-center text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300 whitespace-nowrap">
                            Direct Pay
                        </span>
                    </div>
                </div>

                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 transition-colors ml-2"
                     :class="{{ $modelName }} === 'cash' ? 'border-emerald-500 bg-emerald-500' : 'border-gray-300 dark:border-gray-600 bg-transparent'">
                    <div x-show="{{ $modelName }} === 'cash'" class="w-2 h-2 rounded-full bg-white"></div>
                </div>
            </div>

            <div class="flex items-center justify-between gap-2 pt-2 border-t border-gray-100 dark:border-white/5 w-full">
                <div class="flex items-center gap-1.5 shrink-0">
                    <span class="px-2 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300 border border-emerald-300/40 text-[10px] font-bold">✓ No Hold</span>
                    <span class="px-2 py-0.5 rounded-md bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-gray-400 text-[10px] font-bold">Exact Fare</span>
                </div>
                <span class="text-[11px] text-gray-500 dark:text-gray-400 font-medium whitespace-nowrap">
                    Pay upon drop-off
                </span>
            </div>
        </button>
        @endif

    </div>

    @if($allowCash)
    <!-- CASH NOTICE (When Cash selected) -->
    <div x-show="{{ $modelName }} === 'cash'" 
         x-transition.opacity 
         style="display: none;"
         class="p-3 bg-emerald-50/70 dark:bg-emerald-950/20 rounded-xl border border-emerald-200 dark:border-emerald-800/40 flex items-start gap-2.5 text-xs shadow-2xs">
        <div class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5">
            💵
        </div>
        <div class="min-w-0 flex-1">
            <div class="font-bold text-gray-900 dark:text-white text-xs">
                Cash on Delivery / Drop-off
            </div>
            <p class="text-[10.5px] text-gray-600 dark:text-gray-400 mt-0.5 leading-relaxed">
                Zero upfront charge. Hand physical cash directly to your dispatch courier or chauffeur once your delivery or trip is safely completed.
            </p>
        </div>
    </div>
    @endif

    @if($showSecurityBadge)
    <!-- STRIPE SECURITY NOTICE (Compact Bank-Grade Badge) -->
    <div x-show="{{ $modelName }} === 'stripe'" 
         x-transition.opacity 
         class="px-3.5 py-2.5 bg-gradient-to-r from-[#635BFF]/[0.05] via-white to-transparent dark:from-[#635BFF]/15 dark:via-[#141416] dark:to-transparent rounded-xl border border-[#635BFF]/20 flex items-center justify-between gap-2.5 text-xs shadow-2xs">
        <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-6 h-6 rounded-lg bg-[#635BFF]/10 text-[#635BFF] dark:bg-[#635BFF]/20 flex items-center justify-center shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-extrabold text-gray-900 dark:text-white">
                    Stripe PCI-DSS Level 1 Secure Authorization
                </span>
                <span class="text-[10px] text-gray-500 dark:text-gray-400 hidden sm:inline">· Bank-grade tokenized encryption</span>
            </div>
        </div>
        <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 shrink-0 whitespace-nowrap">
            🔒 256-Bit SSL
        </span>
    </div>
    @endif

    @if($showMomoDetails)
    <!-- MOMO PAY PHONE & NETWORK DETAILS (Revealed when MoMo is chosen) -->
    <div x-show="{{ $modelName }} === 'momo'" 
         x-transition.opacity 
         style="display: none;"
         class="p-3 sm:p-3.5 bg-amber-50/70 dark:bg-amber-950/20 rounded-xl border border-amber-200 dark:border-amber-800/40 space-y-2.5 text-xs">
        
        <div class="flex items-center justify-between">
            <span class="font-extrabold text-amber-950 dark:text-amber-300 uppercase tracking-wider text-[11px]">
                Select Mobile Money Network
            </span>
            <span class="text-[10px] font-black text-amber-700 dark:text-amber-400 bg-amber-200/60 dark:bg-amber-900/60 px-2 py-0.5 rounded-full">
                Instant USSD Push
            </span>
        </div>

        <!-- Network Selector Buttons -->
        <div class="grid grid-cols-3 gap-2">
            <button type="button" 
                    @click="{{ $networkModel }} = 'MTN'" 
                    :class="{{ $networkModel }} === 'MTN' ? 'bg-amber-400 text-slate-950 font-black shadow-sm ring-2 ring-amber-500' : 'bg-white dark:bg-[#1c1c1f] text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-white/10 hover:border-gray-300'"
                    class="py-2 px-1.5 rounded-xl text-xs font-extrabold flex items-center justify-center gap-1.5 transition cursor-pointer">
                <img src="/images/payment-icons/mtn-momo.svg" alt="MTN MoMo" class="h-4 w-auto rounded shrink-0">
                <span class="hidden sm:inline">MTN</span>
            </button>
            <button type="button" 
                    @click="{{ $networkModel }} = 'Telecel'" 
                    :class="{{ $networkModel }} === 'Telecel' ? 'bg-rose-500 text-white font-black shadow-sm ring-2 ring-rose-500' : 'bg-white dark:bg-[#1c1c1f] text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-white/10 hover:border-gray-300'"
                    class="py-2 px-1.5 rounded-xl text-xs font-extrabold flex items-center justify-center gap-1.5 transition cursor-pointer">
                <img src="/images/payment-icons/telecel.svg" alt="Telecel" class="h-4 w-auto rounded shrink-0">
                <span class="hidden sm:inline">Telecel</span>
            </button>
            <button type="button" 
                    @click="{{ $networkModel }} = 'AT'" 
                    :class="{{ $networkModel }} === 'AT' ? 'bg-blue-600 text-white font-black shadow-sm ring-2 ring-blue-500' : 'bg-white dark:bg-[#1c1c1f] text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-white/10 hover:border-gray-300'"
                    class="py-2 px-1.5 rounded-xl text-xs font-extrabold flex items-center justify-center gap-1.5 transition cursor-pointer">
                <img src="/images/payment-icons/airteltigo.svg" alt="AirtelTigo" class="h-4 w-auto rounded shrink-0">
                <span class="hidden sm:inline">AirtelTigo</span>
            </button>
        </div>

        <!-- Phone Number Input -->
        <div>
            <label class="block text-[11px] font-extrabold text-gray-800 dark:text-gray-200 mb-1 uppercase tracking-wider">
                Mobile Money Phone Number *
            </label>
            <input type="tel" 
                   name="{{ $momoInputName }}"
                   x-model="{{ $phoneModel }}" 
                   placeholder="e.g. 024 123 4567" 
                   class="w-full px-3 py-2 bg-white dark:bg-[#161618] border border-amber-300/70 dark:border-amber-700/40 rounded-xl text-xs font-mono font-bold text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-amber-500 focus:outline-none transition">
            <p class="text-[10px] text-amber-800 dark:text-amber-400 mt-1 font-medium">
                📲 A payment authorization prompt will be pushed to this mobile device to enter your secret PIN.
            </p>
        </div>
    </div>
    @endif
</div>
