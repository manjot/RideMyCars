@props([
    'modelName' => 'paymentMethod',
    'phoneModel' => 'momoPhone',
    'networkModel' => 'momoNetwork',
    'showMomoDetails' => true,
    'showSecurityBadge' => true,
    'inputName' => 'payment_method',
    'momoInputName' => 'momo_phone'
])

<div class="space-y-4" x-cloak>
    <label class="block text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
        Select Payment Method *
    </label>

    <!-- Hidden form input for standard form submission -->
    <input type="hidden" name="{{ $inputName }}" :value="{{ $modelName }}">

    <!-- 2 Professional Payment Method Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
        
        <!-- BUTTON 1: STRIPE -->
        <button type="button" 
                @click="{{ $modelName }} = 'stripe'"
                :class="{{ $modelName }} === 'stripe' 
                    ? 'border-brand-500 bg-gradient-to-br from-brand-50/70 to-brand-100/40 dark:from-brand-950/40 dark:to-brand-900/20 ring-2 ring-brand-500 text-gray-900 dark:text-white shadow-lg shadow-brand-500/10' 
                    : 'border-gray-200 dark:border-white/10 bg-white dark:bg-[#141416] hover:border-gray-300 dark:hover:border-white/20 text-gray-700 dark:text-gray-300 hover:shadow-sm'"
                class="p-4 rounded-2xl border text-left transition-all duration-200 relative flex flex-col justify-between space-y-3 cursor-pointer group">
            
            <div class="flex items-center justify-between w-full">
                <!-- Stripe Brand Logo / Badge -->
                <div class="flex items-center space-x-2">
                    <div class="w-10 h-10 rounded-xl bg-[#635BFF]/10 dark:bg-[#635BFF]/20 flex items-center justify-center text-[#635BFF] shrink-0 border border-[#635BFF]/20 shadow-inner">
                        <!-- Stripe Iconic S SVG -->
                        <svg class="w-6 h-6" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="40" height="40" rx="8" fill="#635BFF"/>
                            <path d="M19.8 16.5C19.8 15.6 20.6 15 21.9 15C23.6 15 25.8 15.6 27.5 16.5V11.8C25.6 11 23.6 10.7 21.6 10.7C16.7 10.7 13.4 13.3 13.4 17.5C13.4 23.9 22.3 22.9 22.3 26.1C22.3 27.2 21.3 27.8 19.8 27.8C17.9 27.8 15.3 27 13.4 25.9V30.7C15.6 31.7 17.8 32.2 20.1 32.2C25.2 32.2 28.7 29.7 28.7 25.3C28.7 18.5 19.8 19.7 19.8 16.5Z" fill="white"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-black text-sm text-gray-900 dark:text-white flex items-center gap-1.5">
                            <span>Stripe</span>
                            <span class="text-[9px] font-extrabold uppercase px-1.5 py-0.5 rounded bg-[#635BFF]/10 text-[#635BFF] dark:bg-[#635BFF]/30 dark:text-[#a29bfe]">Cards</span>
                        </div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400">Credit / Debit Card</div>
                    </div>
                </div>

                <!-- Active Checkmark Indicator -->
                <div class="w-5 h-5 rounded-full border flex items-center justify-center transition-all"
                     :class="{{ $modelName }} === 'stripe' ? 'border-brand-500 bg-brand-500 text-white' : 'border-gray-300 dark:border-white/20 bg-transparent'">
                    <svg x-show="{{ $modelName }} === 'stripe'" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>

            <!-- Card Badges Row -->
            <div class="flex items-center gap-1 text-[9px] font-extrabold text-gray-500 dark:text-gray-400 flex-wrap pt-1 border-t border-gray-100 dark:border-white/5">
                <span class="px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-200/50">VISA</span>
                <span class="px-1.5 py-0.5 rounded bg-orange-50 dark:bg-orange-950/40 text-orange-700 dark:text-orange-300 border border-orange-200/50">Mastercard</span>
                <span class="px-1.5 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200/50">AMEX</span>
                <span class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300">Apple Pay</span>
            </div>
        </button>

        <!-- BUTTON 2: MOMO PAY -->
        <button type="button" 
                @click="{{ $modelName }} = 'momo'"
                :class="{{ $modelName }} === 'momo' 
                    ? 'border-amber-500 bg-gradient-to-br from-amber-50/70 to-amber-100/40 dark:from-amber-950/40 dark:to-amber-900/20 ring-2 ring-amber-500 text-gray-900 dark:text-white shadow-lg shadow-amber-500/10' 
                    : 'border-gray-200 dark:border-white/10 bg-white dark:bg-[#141416] hover:border-gray-300 dark:hover:border-white/20 text-gray-700 dark:text-gray-300 hover:shadow-sm'"
                class="p-4 rounded-2xl border text-left transition-all duration-200 relative flex flex-col justify-between space-y-3 cursor-pointer group">
            
            <div class="flex items-center justify-between w-full">
                <!-- MoMo Pay Brand Logo / Badge -->
                <div class="flex items-center space-x-2">
                    <div class="w-10 h-10 rounded-xl bg-amber-400 text-slate-950 font-black flex items-center justify-center shrink-0 border border-amber-500/30 shadow-inner">
                        <!-- MoMo Iconic Phone/Card SVG -->
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17 1H7C5.34 1 4 2.34 4 4v16c0 1.66 1.34 3 3 3h10c1.66 0 3-1.34 3-3V4c0-1.66-1.34-3-3-3zm-5 20c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm6.5-4H5.5V4h13v13z"/>
                            <circle cx="12" cy="10" r="2.5" fill="#111"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-black text-sm text-gray-900 dark:text-white flex items-center gap-1.5">
                            <span>MoMo Pay</span>
                            <span class="text-[9px] font-extrabold uppercase px-1.5 py-0.5 rounded bg-amber-200 text-amber-900 dark:bg-amber-900/60 dark:text-amber-300">Mobile Money</span>
                        </div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400">Instant Mobile Prompt</div>
                    </div>
                </div>

                <!-- Active Checkmark Indicator -->
                <div class="w-5 h-5 rounded-full border flex items-center justify-center transition-all"
                     :class="{{ $modelName }} === 'momo' ? 'border-amber-500 bg-amber-500 text-slate-950' : 'border-gray-300 dark:border-white/20 bg-transparent'">
                    <svg x-show="{{ $modelName }} === 'momo'" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>

            <!-- MoMo Network Badges Row -->
            <div class="flex items-center gap-1 text-[9px] font-extrabold text-gray-500 dark:text-gray-400 flex-wrap pt-1 border-t border-gray-100 dark:border-white/5">
                <span class="px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-950/50 text-amber-900 dark:text-amber-300 border border-amber-300/40">MTN MoMo</span>
                <span class="px-1.5 py-0.5 rounded bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200/50">Telecel</span>
                <span class="px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-200/50">AirtelTigo</span>
            </div>
        </button>

    </div>

    <!-- STRIPE SECURITY NOTICE (Clean, no direct raw card inputs) -->
    <div x-show="{{ $modelName }} === 'stripe'" 
         x-transition.opacity 
         class="p-4 bg-gradient-to-r from-brand-50/50 to-white dark:from-brand-950/30 dark:to-[#121214] rounded-2xl border border-brand-200/80 dark:border-brand-800/30 flex items-start gap-3 text-xs">
        <div class="p-2 rounded-xl bg-[#635BFF]/10 text-[#635BFF] dark:bg-[#635BFF]/20 shrink-0 mt-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>
        <div>
            <div class="font-extrabold text-gray-900 dark:text-white flex items-center gap-1.5">
                <span>Stripe PCI-DSS Level 1 Secure Authorization</span>
                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">256-Bit SSL</span>
            </div>
            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 leading-relaxed">
                Your payment will be securely pre-authorized via Stripe. Card details are tokenized with bank-grade encryption without exposed form fields.
            </p>
        </div>
    </div>

    @if($showMomoDetails)
    <!-- MOMO PAY PHONE & NETWORK DETAILS (Revealed when MoMo is chosen) -->
    <div x-show="{{ $modelName }} === 'momo'" 
         x-transition.opacity 
         style="display: none;"
         class="p-4 bg-amber-50/70 dark:bg-amber-950/20 rounded-2xl border border-amber-200 dark:border-amber-800/40 space-y-3 text-xs">
        
        <div class="flex items-center justify-between">
            <span class="font-extrabold text-amber-900 dark:text-amber-300 uppercase tracking-wider text-[11px]">
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
                    :class="{{ $networkModel }} === 'MTN' ? 'bg-amber-400 text-slate-950 font-black shadow-sm ring-1 ring-amber-500' : 'bg-white dark:bg-[#1c1c1f] text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-white/10'"
                    class="py-2.5 px-2 rounded-xl text-xs font-extrabold text-center transition cursor-pointer">
                MTN MoMo
            </button>
            <button type="button" 
                    @click="{{ $networkModel }} = 'Telecel'" 
                    :class="{{ $networkModel }} === 'Telecel' ? 'bg-rose-500 text-white font-black shadow-sm ring-1 ring-rose-500' : 'bg-white dark:bg-[#1c1c1f] text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-white/10'"
                    class="py-2.5 px-2 rounded-xl text-xs font-extrabold text-center transition cursor-pointer">
                Telecel
            </button>
            <button type="button" 
                    @click="{{ $networkModel }} = 'AT'" 
                    :class="{{ $networkModel }} === 'AT' ? 'bg-blue-600 text-white font-black shadow-sm ring-1 ring-blue-500' : 'bg-white dark:bg-[#1c1c1f] text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-white/10'"
                    class="py-2.5 px-2 rounded-xl text-xs font-extrabold text-center transition cursor-pointer">
                AirtelTigo
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
                   class="w-full px-3.5 py-2.5 bg-white dark:bg-[#161618] border border-amber-300/70 dark:border-amber-700/40 rounded-xl text-xs font-mono font-bold text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-amber-500 focus:outline-none transition">
            <p class="text-[10px] text-amber-800 dark:text-amber-400 mt-1 font-medium">
                📲 A payment authorization prompt will be pushed to this mobile device to enter your secret PIN.
            </p>
        </div>
    </div>
    @endif
</div>
