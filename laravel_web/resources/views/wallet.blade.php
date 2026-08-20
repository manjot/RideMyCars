<x-layout>
    <x-slot:title>Wallet — RideMyCars</x-slot>

    <main class="flex-1 w-full max-w-4xl mx-auto px-4 py-12 sm:px-6 lg:px-8 bg-white dark:bg-[#0a0a0a]">
        
        <!-- Top Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-12">
            <!-- Balance Card -->
            <div class="bg-white dark:bg-[#111] border border-gray-100 dark:border-white/10 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Balance</h3>
                    <div class="text-3xl font-bold text-black dark:text-white mb-4">$0.00</div>
                    <p class="text-sm font-bold text-black dark:text-white mb-6">Add the bank account where you want to receive payouts</p>
                </div>
                <div>
                    <button class="bg-black hover:bg-gray-800 text-white text-sm font-bold py-2.5 px-4 rounded-full inline-flex items-center gap-1.5 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                        Add bank account
                    </button>
                </div>
            </div>

            <!-- RideMyCars Cash Card -->
            <div class="bg-gradient-to-br from-[#f6f6f6] to-[#e6e6e6] dark:from-[#1a1a1a] dark:to-[#222] rounded-2xl p-6 flex flex-col justify-between border border-gray-100 dark:border-white/5 min-h-[200px] relative overflow-hidden">
                <div class="absolute right-0 top-0 bottom-0 w-1/2 bg-gradient-to-bl from-white/40 to-transparent dark:from-white/5 dark:to-transparent skew-x-[-20deg] translate-x-1/4"></div>
                <div class="relative z-10">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">RideMyCars Cash</h3>
                    <div class="text-3xl font-bold text-black dark:text-white mb-6">$0.00</div>
                </div>
                <div class="relative z-10">
                    <button class="bg-black hover:bg-gray-800 text-white text-sm font-bold py-2.5 px-4 rounded-full inline-flex items-center gap-1.5 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                        Gift card
                    </button>
                </div>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="mb-12">
            <h2 class="text-xl font-bold text-black dark:text-white mb-6">Payment methods</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <!-- UPI -->
                <div class="bg-[#ebebeb] dark:bg-[#222] rounded-2xl p-5 min-h-[160px] relative border border-transparent hover:border-gray-300 transition-colors cursor-pointer">
                    <div class="flex justify-between items-start">
                        <span class="font-bold text-black dark:text-white text-[15px]">UPI Scan and Pay</span>
                        <div class="w-8 h-8 bg-black rounded flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h6v6H3z"/><path d="M15 3h6v6h-6z"/><path d="M3 15h6v6H3z"/><path d="M21 21v-6h-6"/><path d="M15 15v6h6"/><path d="M9 9h6v6H9z"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Cash (Preferred) -->
                <div class="bg-[#3c7840] hover:bg-[#346b38] rounded-2xl p-5 min-h-[160px] relative transition-colors cursor-pointer">
                    <div class="flex justify-between items-start">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-white text-[15px]">Cash</span>
                            <span class="bg-white/20 text-white text-[11px] font-bold px-2 py-0.5 rounded">Preferred</span>
                        </div>
                        <div class="w-8 h-8 bg-[#bedca9] text-[#2c582f] rounded flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm-8 12c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm6.5-6H17V8.5c0-.28.22-.5.5-.5h1c.28 0 .5.22.5.5V10zM5.5 10H7v1.5c0 .28-.22.5-.5.5h-1c-.28 0-.5-.22-.5-.5V10z"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            <button class="flex items-center gap-4 text-black dark:text-white font-bold text-base hover:bg-gray-50 dark:hover:bg-[#111] p-3 -ml-3 rounded-xl transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                Add payment method
            </button>
        </div>

        <hr class="border-gray-200 dark:border-white/10 mb-8">

        <!-- Profiles -->
        <div class="mb-12">
            <h2 class="text-xl font-bold text-black dark:text-white mb-4">Profiles</h2>
            <div class="space-y-1">
                <a href="#" class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-[#111] rounded-xl transition-colors">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-black text-white rounded-full flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M12 2a5 5 0 1 0 5 5 5 5 0 0 0-5-5Zm0 8a3 3 0 1 1 3-3 3 3 0 0 1-3 3Zm9 11v-1a7 7 0 0 0-7-7h-4a7 7 0 0 0-7 7v1h2v-1a5 5 0 0 1 5-5h4a5 5 0 0 1 5 5v1h2Z"/></svg>
                        </div>
                        <div>
                            <div class="font-bold text-[15px] text-black dark:text-white">Personal</div>
                            <div class="text-[13px] text-gray-500 font-medium">Cash</div>
                        </div>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-black dark:text-white"><path d="m9 18 6-6-6-6"/></svg>
                </a>
                <a href="#" class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-[#111] rounded-xl transition-colors">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-[#2d6def] text-white rounded-full flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M20 7h-4V5c0-1.1-.9-2-2-2h-4c-1.1 0-2 .9-2 2v2H4c-1.1 0-2 .9-2 2v11c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V9c0-1.1-.9-2-2-2zm-10-2h4v2h-4V5z"/></svg>
                        </div>
                        <div>
                            <div class="font-bold text-[15px] text-black dark:text-white">ajath Infotech private limited</div>
                        </div>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-black dark:text-white"><path d="m9 18 6-6-6-6"/></svg>
                </a>
            </div>
        </div>

        <hr class="border-gray-200 dark:border-white/10 mb-8">

        <!-- Shared with you -->
        <div class="mb-12">
            <h2 class="text-[15px] font-medium text-gray-600 dark:text-gray-400 mb-4">Shared with you</h2>
            <a href="#" class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-[#111] rounded-xl transition-colors">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-gray-300 dark:bg-gray-700 text-white rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                    </div>
                    <div>
                        <div class="font-bold text-[15px] text-black dark:text-white">Manage business rides for others</div>
                        <div class="text-[13px] text-gray-500 font-medium mt-0.5">Request access to their business profile</div>
                    </div>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-black dark:text-white"><path d="m9 18 6-6-6-6"/></svg>
            </a>
        </div>

        <hr class="border-gray-200 dark:border-white/10 mb-8">

        <!-- Vouchers -->
        <div class="mb-12">
            <h2 class="text-xl font-bold text-black dark:text-white mb-6">Vouchers</h2>
            <button class="flex items-center gap-4 text-black dark:text-white font-bold text-base hover:bg-gray-50 dark:hover:bg-[#111] p-3 -ml-3 rounded-xl transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                Add voucher
            </button>
        </div>

        <hr class="border-gray-200 dark:border-white/10 mb-8">

        <!-- In-store offers -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-black dark:text-white mb-6">In-store offers</h2>
            <button class="flex items-center gap-4 text-black dark:text-white font-bold text-base hover:bg-gray-50 dark:hover:bg-[#111] p-3 -ml-3 rounded-xl transition-colors">
                Offers
            </button>
        </div>
        
        <hr class="border-gray-200 dark:border-white/10 mb-8">

    </main>
</x-layout>
