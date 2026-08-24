<x-layout>
    <x-slot:title>Wallet & Earnings — RideMyCars</x-slot>

    <main class="flex-1 w-full max-w-4xl mx-auto px-4 py-12 sm:px-6 lg:px-8 bg-white dark:bg-[#0a0a0a]"
          x-data="walletManager()">
        
        <!-- Success Toast Notification -->
        <template x-teleport="body">
            <div x-show="toast" style="display: none;" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-[-20px]"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-[-20px]"
                 class="fixed top-24 right-4 sm:right-8 z-[999999] max-w-md w-full bg-emerald-600 text-white shadow-2xl rounded-2xl p-4 flex items-center justify-between font-bold text-sm">
                <div class="flex items-center gap-3">
                    <span class="text-xl">🎉</span>
                    <span x-text="toast"></span>
                </div>
                <button @click="toast = ''" class="text-white/80 hover:text-white font-bold ml-4 text-base">✕</button>
            </div>
        </template>

        <!-- Top Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-12">
            <!-- Balance Card -->
            <div class="bg-white dark:bg-[#111] border border-gray-100 dark:border-white/10 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Balance</h3>
                    <div class="text-3xl font-bold text-black dark:text-white mb-4" x-text="'$' + balance.toFixed(2)"></div>
                    <template x-if="!bankAccount">
                        <p class="text-sm font-bold text-black dark:text-white mb-6">Add the bank account where you want to receive payouts</p>
                    </template>
                    <template x-if="bankAccount">
                        <div class="p-3.5 bg-gray-50 dark:bg-[#1a1a1a] rounded-xl mb-6 border border-gray-200 dark:border-white/10">
                            <p class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">✓ Linked Bank Account</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white mt-1" x-text="bankAccount.bank_name + ' (•••• ' + (bankAccount.account_number ? bankAccount.account_number.slice(-4) : '4821') + ')'"></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5" x-text="'Holder: ' + bankAccount.holder_name"></p>
                        </div>
                    </template>
                </div>
                <div>
                    <button type="button" @click="showBankModal = true" class="bg-black hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-black text-sm font-bold py-2.5 px-5 rounded-full inline-flex items-center gap-2 transition-all cursor-pointer shadow-sm active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                        <span x-text="bankAccount ? 'Update bank account' : 'Add bank account'"></span>
                    </button>
                </div>
            </div>

            <!-- RideMyCars Cash Card -->
            <div class="bg-gradient-to-br from-[#f6f6f6] to-[#e6e6e6] dark:from-[#1a1a1a] dark:to-[#222] rounded-2xl p-6 flex flex-col justify-between border border-gray-100 dark:border-white/5 min-h-[200px] relative overflow-hidden">
                <div class="absolute right-0 top-0 bottom-0 w-1/2 bg-gradient-to-bl from-white/40 to-transparent dark:from-white/5 dark:to-transparent skew-x-[-20deg] translate-x-1/4"></div>
                <div class="relative z-10">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">RideMyCars Cash</h3>
                    <div class="text-3xl font-bold text-black dark:text-white mb-6" x-text="'$' + giftCash.toFixed(2)"></div>
                </div>
                <div class="relative z-10">
                    <button type="button" @click="showGiftModal = true" class="bg-black hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-black text-sm font-bold py-2.5 px-5 rounded-full inline-flex items-center gap-2 transition-all cursor-pointer shadow-sm active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                        + Gift card
                    </button>
                </div>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="mb-12">
            <h2 class="text-xl font-bold text-black dark:text-white mb-6">Payment methods</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <!-- Dynamic List of Payment Methods -->
                <template x-for="(pm, index) in paymentMethods" :key="index">
                    <div class="rounded-2xl p-5 min-h-[160px] relative border transition-all cursor-pointer shadow-sm"
                         :class="pm.preferred ? 'bg-[#3c7840] hover:bg-[#346b38] border-transparent text-white' : 'bg-[#ebebeb] dark:bg-[#222] border-transparent hover:border-gray-300 dark:hover:border-white/20 text-black dark:text-white'">
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-[15px]" x-text="pm.name"></span>
                                <template x-if="pm.preferred">
                                    <span class="bg-white/20 text-white text-[11px] font-bold px-2 py-0.5 rounded">Preferred</span>
                                </template>
                            </div>
                            <div class="w-8 h-8 rounded flex items-center justify-center"
                                 :class="pm.preferred ? 'bg-[#bedca9] text-[#2c582f]' : 'bg-black text-white dark:bg-white dark:text-black'">
                                <template x-if="pm.type === 'upi'">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h6v6H3z"/><path d="M15 3h6v6h-6z"/><path d="M3 15h6v6H3z"/><path d="M21 21v-6h-6"/><path d="M15 15v6h6"/><path d="M9 9h6v6H9z"/></svg>
                                </template>
                                <template x-if="pm.type === 'cash'">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm-8 12c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm6.5-6H17V8.5c0-.28.22-.5.5-.5h1c.28 0 .5.22.5.5V10zM5.5 10H7v1.5c0 .28-.22.5-.5.5h-1c-.28 0-.5-.22-.5-.5V10z"/></svg>
                                </template>
                                <template x-if="pm.type === 'card'">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                                </template>
                            </div>
                        </div>
                        <template x-if="pm.details">
                            <p class="text-xs mt-8 opacity-80 font-medium" x-text="pm.details"></p>
                        </template>
                    </div>
                </template>
            </div>

            <button type="button" @click="showPaymentModal = true" class="flex items-center gap-4 text-black dark:text-white font-bold text-base hover:bg-gray-50 dark:hover:bg-[#111] p-3 -ml-3 rounded-xl transition-colors cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                Add payment method
            </button>
        </div>

        <hr class="border-gray-200 dark:border-white/10 mb-8">

        <!-- Profiles -->
        <div class="mb-12">
            <h2 class="text-xl font-bold text-black dark:text-white mb-4">Profiles</h2>
            <div class="space-y-1">
                <button type="button" @click="openProfileModal('personal')" class="w-full flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-[#111] rounded-xl transition-colors text-left cursor-pointer">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-black text-white rounded-full flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M12 2a5 5 0 1 0 5 5 5 5 0 0 0-5-5Zm0 8a3 3 0 1 1 3-3 3 3 0 0 1-3 3Zm9 11v-1a7 7 0 0 0-7-7h-4a7 7 0 0 0-7 7v1h2v-1a5 5 0 0 1 5-5h4a5 5 0 0 1 5 5v1h2Z"/></svg>
                        </div>
                        <div>
                            <div class="font-bold text-[15px] text-black dark:text-white">Personal</div>
                            <div class="text-[13px] text-gray-500 font-medium">Default • Cash</div>
                        </div>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-black dark:text-white"><path d="m9 18 6-6-6-6"/></svg>
                </button>
                <button type="button" @click="openProfileModal('business')" class="w-full flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-[#111] rounded-xl transition-colors text-left cursor-pointer">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-[#2d6def] text-white rounded-full flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M20 7h-4V5c0-1.1-.9-2-2-2h-4c-1.1 0-2 .9-2 2v2H4c-1.1 0-2 .9-2 2v11c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V9c0-1.1-.9-2-2-2zm-10-2h4v2h-4V5z"/></svg>
                        </div>
                        <div>
                            <div class="font-bold text-[15px] text-black dark:text-white">ajath Infotech private limited</div>
                            <div class="text-[13px] text-gray-500 font-medium">Business Account • Expense Code Active</div>
                        </div>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-black dark:text-white"><path d="m9 18 6-6-6-6"/></svg>
                </button>
            </div>
        </div>

        <hr class="border-gray-200 dark:border-white/10 mb-8">

        <!-- Shared with you -->
        <div class="mb-12">
            <h2 class="text-[15px] font-medium text-gray-600 dark:text-gray-400 mb-4">Shared with you</h2>
            <button type="button" @click="showBusinessModal = true" class="w-full flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-[#111] rounded-xl transition-colors text-left cursor-pointer">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-gray-300 dark:bg-gray-700 text-white rounded-full flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                    </div>
                    <div>
                        <div class="font-bold text-[15px] text-black dark:text-white flex items-center gap-2">
                            <span>Manage business rides for others</span>
                            <template x-if="businessRequestSent">
                                <span class="bg-amber-100 text-amber-800 text-[11px] font-bold px-2 py-0.5 rounded-full">Pending Approval</span>
                            </template>
                        </div>
                        <div class="text-[13px] text-gray-500 font-medium mt-0.5" x-text="businessRequestSent ? ('Access requested to ' + (businessForm.email || 'admin')) : 'Request access to their business profile'"></div>
                    </div>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-black dark:text-white"><path d="m9 18 6-6-6-6"/></svg>
            </button>
        </div>

        <hr class="border-gray-200 dark:border-white/10 mb-8">

        <!-- Vouchers -->
        <div class="mb-12">
            <h2 class="text-xl font-bold text-black dark:text-white mb-6">Vouchers</h2>
            <template x-if="vouchers.length > 0">
                <div class="space-y-2 mb-4">
                    <template x-for="(v, index) in vouchers" :key="index">
                        <div class="p-4 bg-gray-50 dark:bg-[#181818] rounded-xl border border-gray-200 dark:border-white/10 flex justify-between items-center">
                            <div>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400" x-text="v.code"></span>
                                <p class="text-xs text-gray-500 mt-0.5" x-text="v.discount"></p>
                            </div>
                            <span class="text-xs bg-emerald-100 text-emerald-800 font-bold px-2.5 py-1 rounded-full">Active</span>
                        </div>
                    </template>
                </div>
            </template>
            <button type="button" @click="showVoucherModal = true" class="flex items-center gap-4 text-black dark:text-white font-bold text-base hover:bg-gray-50 dark:hover:bg-[#111] p-3 -ml-3 rounded-xl transition-colors cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                Add voucher
            </button>
        </div>

        <!-- MODAL 1: ADD BANK ACCOUNT -->
        <template x-teleport="body">
            <div x-show="showBankModal" style="display: none;" 
                 class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                
                <div class="bg-white dark:bg-[#181818] w-full max-w-md rounded-2xl shadow-2xl p-6 relative border border-gray-100 dark:border-white/10" @click.away="showBankModal = false">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-white/10 mb-4">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Add Bank Account</h3>
                        <button type="button" @click="showBankModal = false" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center text-gray-500 hover:text-gray-900 dark:hover:text-white">✕</button>
                    </div>
                    
                    <form @submit.prevent="saveBankAccount" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Bank Name</label>
                            <input type="text" x-model="bankForm.bank_name" required placeholder="e.g. Chase, Bank of America, HDFC" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-black text-sm font-medium">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Account Holder Name</label>
                            <input type="text" x-model="bankForm.holder_name" required placeholder="Full name on account" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-black text-sm font-medium">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Account Number / IBAN</label>
                            <input type="text" x-model="bankForm.account_number" required placeholder="Enter account number" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-black text-sm font-medium">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Routing / IFSC / SWIFT Code</label>
                            <input type="text" x-model="bankForm.routing_number" required placeholder="e.g. 122000661" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-black text-sm font-medium">
                        </div>
                        
                        <div class="pt-2">
                            <button type="submit" class="w-full bg-black hover:bg-gray-900 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-black font-bold py-3.5 rounded-xl text-sm transition-all shadow-md">
                                Save Bank Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- MODAL 2: ADD GIFT CARD -->
        <template x-teleport="body">
            <div x-show="showGiftModal" style="display: none;" 
                 class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                
                <div class="bg-white dark:bg-[#181818] w-full max-w-md rounded-2xl shadow-2xl p-6 relative border border-gray-100 dark:border-white/10" @click.away="showGiftModal = false">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-white/10 mb-4">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Redeem Gift Card</h3>
                        <button type="button" @click="showGiftModal = false" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center text-gray-500 hover:text-gray-900 dark:hover:text-white">✕</button>
                    </div>
                    
                    <form @submit.prevent="redeemGiftCard" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Gift Card / Claim Code</label>
                            <input type="text" x-model="giftForm.code" required placeholder="e.g. RIDE-GIFT-50" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white uppercase font-mono tracking-wider focus:outline-none focus:ring-2 focus:ring-black text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Amount ($)</label>
                            <input type="number" step="5" min="5" x-model="giftForm.amount" required placeholder="50.00" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-black text-sm font-medium">
                        </div>
                        
                        <div class="pt-2">
                            <button type="submit" class="w-full bg-black hover:bg-gray-900 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-black font-bold py-3.5 rounded-xl text-sm transition-all shadow-md">
                                Redeem Gift Card
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- MODAL 3: ADD PAYMENT METHOD -->
        <template x-teleport="body">
            <div x-show="showPaymentModal" style="display: none;" 
                 class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                
                <div class="bg-white dark:bg-[#181818] w-full max-w-md rounded-2xl shadow-2xl p-6 relative border border-gray-100 dark:border-white/10" @click.away="showPaymentModal = false">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-white/10 mb-4">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Add Payment Method</h3>
                        <button type="button" @click="showPaymentModal = false" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center text-gray-500 hover:text-gray-900 dark:hover:text-white">✕</button>
                    </div>
                    
                    <form @submit.prevent="addPaymentMethod" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Method Type</label>
                            <select x-model="pmForm.type" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-black text-sm font-medium">
                                <option value="card">Credit or Debit Card</option>
                                <option value="upi">UPI Scan & Pay</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1" x-text="pmForm.type === 'card' ? 'Card Number' : 'UPI ID / VPA'"></label>
                            <input type="text" x-model="pmForm.number" required :placeholder="pmForm.type === 'card' ? '4532 •••• •••• 8892' : 'username@upi'" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-black text-sm font-medium">
                        </div>
                        <template x-if="pmForm.type === 'card'">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Expiry (MM/YY)</label>
                                    <input type="text" x-model="pmForm.expiry" placeholder="12/28" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-black text-sm font-medium">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">CVV</label>
                                    <input type="password" maxlength="4" placeholder="123" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-black text-sm font-medium">
                                </div>
                            </div>
                        </template>

                        <div class="pt-2">
                            <button type="submit" class="w-full bg-black hover:bg-gray-900 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-black font-bold py-3.5 rounded-xl text-sm transition-all shadow-md">
                                Save Payment Method
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- MODAL 4: ADD VOUCHER -->
        <template x-teleport="body">
            <div x-show="showVoucherModal" style="display: none;" 
                 class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                
                <div class="bg-white dark:bg-[#181818] w-full max-w-md rounded-2xl shadow-2xl p-6 relative border border-gray-100 dark:border-white/10" @click.away="showVoucherModal = false">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-white/10 mb-4">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Add Voucher</h3>
                        <button type="button" @click="showVoucherModal = false" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center text-gray-500 hover:text-gray-900 dark:hover:text-white">✕</button>
                    </div>
                    
                    <form @submit.prevent="addVoucher" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Voucher Code</label>
                            <input type="text" x-model="voucherCode" required placeholder="e.g. SUMMER2026" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white uppercase font-mono tracking-wider focus:outline-none focus:ring-2 focus:ring-black text-sm">
                        </div>
                        
                        <div class="pt-2">
                            <button type="submit" class="w-full bg-black hover:bg-gray-900 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-black font-bold py-3.5 rounded-xl text-sm transition-all shadow-md">
                                Apply Voucher
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- MODAL 5: MANAGE BUSINESS RIDES FOR OTHERS (UBER BUSINESS STYLE) -->
        <template x-teleport="body">
            <div x-show="showBusinessModal" style="display: none;" 
                 class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                
                <div class="bg-white dark:bg-[#181818] w-full max-w-md rounded-2xl shadow-2xl p-6 relative border border-gray-100 dark:border-white/10" @click.away="showBusinessModal = false">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-white/10 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-[#2d6def] text-white flex items-center justify-center font-bold">
                                💼
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Business Delegate Access</h3>
                                <p class="text-xs text-gray-500">RideMyCars Business Mode</p>
                            </div>
                        </div>
                        <button type="button" @click="showBusinessModal = false" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center text-gray-500 hover:text-gray-900 dark:hover:text-white">✕</button>
                    </div>

                    <p class="text-xs text-gray-600 dark:text-gray-300 mb-4 leading-relaxed">
                        Request permission from a company administrator or executive to book and arrange business rides directly under their organization account.
                    </p>
                    
                    <form @submit.prevent="requestBusinessAccess" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Company / Organization</label>
                            <input type="text" x-model="businessForm.company" required placeholder="Company Name" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-black text-sm font-medium">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Business Admin Email</label>
                            <input type="email" x-model="businessForm.email" required placeholder="admin@company.com" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-black text-sm font-medium">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Your Role / Request Note</label>
                            <textarea x-model="businessForm.notes" rows="2" placeholder="e.g. Executive Assistant arranging travel for team members" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-black text-sm font-medium resize-none"></textarea>
                        </div>
                        
                        <div class="pt-2">
                            <button type="submit" class="w-full bg-[#2d6def] hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl text-sm transition-all shadow-md">
                                Send Access Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- MODAL 6: PROFILE DETAILS MODAL -->
        <template x-teleport="body">
            <div x-show="showProfileModalState" style="display: none;" 
                 class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                
                <div class="bg-white dark:bg-[#181818] w-full max-w-md rounded-2xl shadow-2xl p-6 relative border border-gray-100 dark:border-white/10" @click.away="showProfileModalState = false">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-white/10 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full font-bold flex items-center justify-center text-white"
                                 :class="selectedProfile === 'personal' ? 'bg-black' : 'bg-[#2d6def]'">
                                <span x-text="selectedProfile === 'personal' ? '👤' : '💼'"></span>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white" x-text="selectedProfile === 'personal' ? 'Personal Profile' : 'ajath Infotech private limited'"></h3>
                                <p class="text-xs text-gray-500" x-text="selectedProfile === 'personal' ? 'Standard personal riding profile' : 'Corporate Business Account'"></p>
                            </div>
                        </div>
                        <button type="button" @click="showProfileModalState = false" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center text-gray-500 hover:text-gray-900 dark:hover:text-white">✕</button>
                    </div>

                    <div class="space-y-4 text-sm">
                        <div class="p-3.5 bg-gray-50 dark:bg-[#222] rounded-xl border border-gray-200 dark:border-white/10">
                            <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Default Payment Method</p>
                            <p class="font-bold text-gray-900 dark:text-white mt-0.5" x-text="selectedProfile === 'personal' ? 'Cash' : 'Corporate Account Card (•••• 9012)'"></p>
                        </div>
                        <div class="p-3.5 bg-gray-50 dark:bg-[#222] rounded-xl border border-gray-200 dark:border-white/10">
                            <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Weekly / Monthly Travel Receipts Email</p>
                            <p class="font-bold text-gray-900 dark:text-white mt-0.5" x-text="'{{ auth()->user()->email ?? 'user@example.com' }}'"></p>
                        </div>
                        <div class="p-3.5 bg-gray-50 dark:bg-[#222] rounded-xl border border-gray-200 dark:border-white/10">
                            <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Expense Code Enforcement</p>
                            <p class="font-bold text-gray-900 dark:text-white mt-0.5" x-text="selectedProfile === 'personal' ? 'Disabled' : 'Enabled (Prompts for Cost Center)'"></p>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-100 dark:border-white/10">
                        <button type="button" @click="showProfileModalState = false; showToast('Profile settings saved!')" class="w-full bg-black hover:bg-gray-900 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-black font-bold py-3.5 rounded-xl text-sm transition-all shadow-md">
                            Done
                        </button>
                    </div>
                </div>
            </div>
        </template>

    </main>

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('walletManager', () => ({
            balance: 0.00,
            giftCash: 0.00,
            toast: '',
            showBankModal: false,
            showGiftModal: false,
            showPaymentModal: false,
            showVoucherModal: false,
            showBusinessModal: false,
            showProfileModalState: false,
            selectedProfile: 'personal',
            
            businessRequestSent: false,
            businessForm: {
                company: 'ajath Infotech private limited',
                email: '',
                notes: ''
            },
            
            bankAccount: null,
            bankForm: {
                bank_name: '',
                holder_name: '{{ auth()->user()->name ?? 'Account Holder' }}',
                account_number: '',
                routing_number: ''
            },
            
            giftForm: {
                code: 'RIDE-GIFT-50',
                amount: 50
            },
            
            paymentMethods: [
                { type: 'upi', name: 'UPI Scan and Pay', details: 'Scanned at checkout', preferred: false },
                { type: 'cash', name: 'Cash', details: 'Paid directly to driver', preferred: true }
            ],
            
            pmForm: {
                type: 'card',
                number: '',
                expiry: ''
            },
            
            vouchers: [],
            voucherCode: '',
            
            init() {
                const savedBank = localStorage.getItem('rmc_bank_account');
                if (savedBank) {
                    try { this.bankAccount = JSON.parse(savedBank); } catch(e){}
                }
                const savedGift = localStorage.getItem('rmc_gift_cash');
                if (savedGift) {
                    this.giftCash = parseFloat(savedGift) || 0.00;
                }
                const savedBiz = localStorage.getItem('rmc_biz_request');
                if (savedBiz) {
                    this.businessRequestSent = true;
                    try { this.businessForm = JSON.parse(savedBiz); } catch(e){}
                }
            },
            
            saveBankAccount() {
                this.bankAccount = { ...this.bankForm };
                localStorage.setItem('rmc_bank_account', JSON.stringify(this.bankAccount));
                this.showBankModal = false;
                this.showToast(`Bank account '${this.bankAccount.bank_name}' linked successfully!`);
            },
            
            redeemGiftCard() {
                const added = parseFloat(this.giftForm.amount) || 50;
                this.giftCash += added;
                localStorage.setItem('rmc_gift_cash', this.giftCash.toString());
                this.showGiftModal = false;
                this.showToast(`Gift card redeemed! $${added.toFixed(2)} added to RideMyCars Cash.`);
            },
            
            addPaymentMethod() {
                const name = this.pmForm.type === 'card' 
                    ? `Card (•••• ${this.pmForm.number.slice(-4) || '8892'})`
                    : `UPI (${this.pmForm.number})`;
                this.paymentMethods.push({
                    type: this.pmForm.type,
                    name: name,
                    details: 'Added payment method',
                    preferred: false
                });
                this.showPaymentModal = false;
                this.showToast(`Payment method '${name}' added!`);
            },
            
            addVoucher() {
                if (!this.voucherCode.trim()) return;
                this.vouchers.push({
                    code: this.voucherCode.toUpperCase(),
                    discount: '15% Off Next 3 Rides'
                });
                this.showVoucherModal = false;
                this.showToast(`Voucher '${this.voucherCode.toUpperCase()}' activated!`);
                this.voucherCode = '';
            },
            
            requestBusinessAccess() {
                if (!this.businessForm.email.trim()) return;
                this.businessRequestSent = true;
                localStorage.setItem('rmc_biz_request', JSON.stringify(this.businessForm));
                this.showBusinessModal = false;
                this.showToast(`Delegate access request sent to ${this.businessForm.email}!`);
            },
            
            openProfileModal(type) {
                this.selectedProfile = type;
                this.showProfileModalState = true;
            },
            
            showToast(msg) {
                this.toast = msg;
                setTimeout(() => { this.toast = ''; }, 4500);
            }
        }));
    });
    </script>
</x-layout>
