@props([
    'modelName' => 'paymentMethod',
    'value' => 'stripe'
])

<div x-show="{{ $modelName }} === '{{ $value }}'"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 transform -translate-y-2"
     x-transition:enter-end="opacity-100 transform translate-y-0"
     class="mt-4 p-5 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-[#161618] dark:to-[#1f1f23] rounded-2xl border-2 border-gray-200 dark:border-white/10 shadow-md space-y-4">
    
    <!-- Card Header & Badges -->
    <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-white/10">
        <div class="flex items-center space-x-2">
            <div class="p-2 bg-black dark:bg-white text-white dark:text-black rounded-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <h4 class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-wider">Credit / Debit Card Information</h4>
                <p class="text-[10px] text-gray-500 dark:text-gray-400">Required for secure Stripe checkout</p>
            </div>
        </div>
        <div class="flex items-center space-x-1.5 bg-white dark:bg-black/40 px-2.5 py-1 rounded-full border border-gray-200 dark:border-white/10 text-[10px] font-bold text-gray-600 dark:text-gray-300">
            <span>🔒 256-bit SSL</span>
        </div>
    </div>

    <!-- Fillup Form Inputs -->
    <div class="space-y-3">
        <!-- Cardholder Name -->
        <div>
            <label class="block text-[11px] font-extrabold text-gray-700 dark:text-gray-300 mb-1 uppercase tracking-wider">
                Cardholder Name <span class="text-red-500">*</span>
            </label>
            <input type="text" 
                   name="cardholder_name" 
                   placeholder="e.g. Johnathan Doe" 
                   required
                   class="w-full px-3.5 py-2.5 bg-white dark:bg-[#0d0d0f] border border-gray-300 dark:border-white/15 rounded-xl text-xs font-semibold text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-black dark:focus:ring-white focus:outline-none transition">
        </div>

        <!-- Card Number -->
        <div>
            <div class="flex justify-between items-center mb-1 flex-wrap gap-1">
                <label class="block text-[11px] font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                    Card Number <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center space-x-1 text-[9px] font-bold text-gray-400">
                    <span class="px-1 py-0.5 bg-gray-200 dark:bg-white/10 rounded text-gray-700 dark:text-gray-300">VISA</span>
                    <span class="px-1 py-0.5 bg-gray-200 dark:bg-white/10 rounded text-gray-700 dark:text-gray-300">MC</span>
                    <span class="px-1 py-0.5 bg-gray-200 dark:bg-white/10 rounded text-gray-700 dark:text-gray-300">AMEX</span>
                    <span class="px-1 py-0.5 bg-gray-200 dark:bg-white/10 rounded text-gray-700 dark:text-gray-300">DISCOVER</span>
                </div>
            </div>
            <div class="relative w-full">
                <input type="text" 
                       name="card_number" 
                       maxlength="19" 
                       placeholder="4242 4242 4242 4242" 
                       required
                       oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(.{4})/g, '$1 ').trim()"
                       class="w-full pl-8 pr-2.5 py-2.5 bg-white dark:bg-[#0d0d0f] border border-gray-300 dark:border-white/15 rounded-xl text-[13px] font-mono font-bold text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-black dark:focus:ring-white focus:outline-none transition tracking-wide shadow-inner">
                <div class="absolute left-2.5 top-2.5 text-gray-400 text-xs">
                    💳
                </div>
            </div>
        </div>

        <!-- Expiry, CVC & ZIP Grid -->
        <div class="grid grid-cols-3 gap-2">
            <!-- Expiration Date -->
            <div>
                <label class="block text-[10px] font-extrabold text-gray-700 dark:text-gray-300 mb-1 uppercase tracking-wider">
                    Expiry <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="card_expiry" 
                       maxlength="5" 
                       placeholder="MM/YY" 
                       required
                       oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^([2-9])/, '0$1').replace(/^(1[3-9])/, '12').replace(/^([0-9]{2})([0-9]+)/, '$1/$2')"
                       class="w-full px-3 py-2.5 bg-white dark:bg-[#0d0d0f] border border-gray-300 dark:border-white/15 rounded-xl text-xs font-mono font-bold text-gray-900 dark:text-white placeholder-gray-400 text-center focus:ring-2 focus:ring-black dark:focus:ring-white focus:outline-none transition">
            </div>

            <!-- CVC / CVV -->
            <div>
                <label class="block text-[10px] font-extrabold text-gray-700 dark:text-gray-300 mb-1 uppercase tracking-wider">
                    CVC <span class="text-red-500">*</span>
                </label>
                <input type="password" 
                       name="card_cvc" 
                       maxlength="4" 
                       placeholder="123" 
                       required
                       oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                       class="w-full px-3 py-2.5 bg-white dark:bg-[#0d0d0f] border border-gray-300 dark:border-white/15 rounded-xl text-xs font-mono font-bold text-gray-900 dark:text-white placeholder-gray-400 text-center focus:ring-2 focus:ring-black dark:focus:ring-white focus:outline-none transition">
            </div>

            <!-- Postal / ZIP Code -->
            <div>
                <label class="block text-[10px] font-extrabold text-gray-700 dark:text-gray-300 mb-1 uppercase tracking-wider">
                    ZIP / Postal <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="card_zip" 
                       maxlength="10" 
                       placeholder="10001" 
                       required
                       class="w-full px-3 py-2.5 bg-white dark:bg-[#0d0d0f] border border-gray-300 dark:border-white/15 rounded-xl text-xs font-bold text-gray-900 dark:text-white placeholder-gray-400 text-center focus:ring-2 focus:ring-black dark:focus:ring-white focus:outline-none transition">
            </div>
        </div>
    </div>

    <!-- Security & Guarantee Footer -->
    <div class="pt-2 flex items-center justify-between text-[10px] text-gray-500 dark:text-gray-400 border-t border-gray-200/60 dark:border-white/5">
        <span class="flex items-center gap-1">
            <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span>PCI-DSS Level 1 Compliant</span>
        </span>
        <span class="font-bold text-gray-700 dark:text-gray-300">Powered by Stripe</span>
    </div>
</div>
