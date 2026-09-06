<x-layout>
    <x-slot:title>Promotions & Exclusive Offers — RideMyCars</x-slot>

    <!-- Ambient Glow Background -->
    <div class="relative overflow-hidden bg-gray-50/60 dark:bg-[#0b0f17] text-gray-900 dark:text-white transition-colors"
         x-data="{
             activeFilter: 'all',
             toastMsg: '',
             showToast(msg) {
                 this.toastMsg = msg;
                 setTimeout(() => { if (this.toastMsg === msg) this.toastMsg = ''; }, 3000);
             },
             copyCode(code) {
                 if (!code) return;
                 navigator.clipboard.writeText(code).then(() => {
                     this.showToast('Promo code copied: ' + code);
                 }).catch(() => {
                     this.showToast('Code: ' + code);
                 });
             },
             copyLink(link) {
                 if (!link) return;
                 navigator.clipboard.writeText(link).then(() => {
                     this.showToast('Invite link copied to clipboard!');
                 }).catch(() => {
                     this.showToast('Invite link copied!');
                 });
             }
         }">
        
        <!-- Background Blur Spheres -->
        <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-gradient-to-tr from-amber-500/15 via-brand-500/10 to-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Toast Notification -->
        <template x-teleport="body">
            <div x-show="toastMsg" style="display: none;" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-[-20px]"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-[-20px]"
                 class="fixed top-24 right-4 sm:right-8 z-[999999] max-w-md w-full bg-emerald-600 text-white shadow-2xl rounded-2xl p-4 flex items-center justify-between font-bold text-sm">
                <div class="flex items-center gap-3">
                    <span class="text-xl">🎉</span>
                    <span x-text="toastMsg"></span>
                </div>
                <button @click="toastMsg = ''" class="text-white/80 hover:text-white font-bold ml-4 text-base">✕</button>
            </div>
        </template>

        <main class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-24 space-y-12">
            
            <!-- Hero Header -->
            <div class="text-center max-w-3xl mx-auto pt-4">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-brand-500/10 text-brand-600 dark:text-brand-400 font-extrabold text-xs uppercase tracking-wider border border-brand-500/20 mb-4 shadow-sm">
                    <span>🏷️</span> Limited-Time Deals & Travel Discounts
                </div>
                <h1 class="text-4xl sm:text-5xl font-black text-gray-900 dark:text-white tracking-tight leading-tight">
                    Ride More, Save More with <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-500 to-amber-300">RideMyCars</span>
                </h1>
                <p class="mt-4 text-base sm:text-lg text-gray-600 dark:text-gray-400 leading-relaxed">
                    Enjoy exclusive coupon codes, referral credits, airport special vouchers, and discount perks on every ride, car rental, chauffeur hire, and parcel delivery.
                </p>
            </div>

            <!-- Referral Rewards Banner (Personal Code) -->
            @auth
            <div class="bg-gradient-to-br from-[#102b54] via-[#15386b] to-[#0a1b35] text-white rounded-3xl p-6 sm:p-10 shadow-2xl border border-amber-400/30 relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-60 h-60 bg-amber-400/15 rounded-full blur-3xl pointer-events-none"></div>
                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-8">
                    <div class="max-w-2xl">
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-amber-400/20 border border-amber-400/40 rounded-full text-xs font-bold text-amber-300 uppercase tracking-wider mb-3">
                            <span>🎁</span> Your Exclusive Invite Code
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-white leading-tight">
                            Invite Friends & Earn Unlimited Ride Perks
                        </h2>
                        <p class="text-gray-300 text-sm sm:text-base mt-2">
                            Share your referral code with friends and colleagues. When they sign up and take their first trip or rental, you both earn special credits directly into your wallet!
                        </p>
                        
                        <div class="flex flex-wrap items-center gap-3 mt-6">
                            <a href="https://wa.me/?text={{ urlencode('Sign up on RideMyCars with my referral code ' . (auth()->user()->referral_code ?? '') . ' to unlock exclusive ride perks and discounts! ' . url('/signup?ref=' . (auth()->user()->referral_code ?? ''))) }}" 
                               target="_blank" 
                               class="inline-flex items-center gap-2 bg-[#25D366] hover:bg-[#20ba59] text-white font-bold py-3 px-5 rounded-2xl transition-all shadow-md active:scale-95 text-sm">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                <span>Share on WhatsApp</span>
                            </a>
                            <button type="button" 
                                    @click="copyLink('{{ url('/signup?ref=' . (auth()->user()->referral_code ?? '')) }}')"
                                    class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white font-bold py-3 px-5 rounded-2xl transition-all border border-white/20 active:scale-95 text-sm cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                <span>Copy Invite Link</span>
                            </button>
                        </div>
                    </div>

                    <!-- Promo Code Display Box -->
                    <div class="bg-black/50 border border-amber-400/50 rounded-3xl p-6 text-center sm:min-w-[280px] shadow-inner backdrop-blur-md">
                        <span class="text-xs text-amber-200/90 font-bold uppercase tracking-wider block mb-2">Your Personal Code</span>
                        <div class="text-3xl sm:text-4xl font-mono font-black text-amber-300 tracking-widest my-2">
                            {{ auth()->user()->referral_code ?? 'RMC000000' }}
                        </div>
                        <p class="text-[11px] text-gray-400 mb-4">No minimum booking required</p>
                        <button type="button" 
                                @click="copyCode('{{ auth()->user()->referral_code ?? '' }}')" 
                                class="w-full py-3 px-4 bg-gradient-to-r from-amber-400 to-amber-300 text-[#102b54] hover:brightness-110 font-black rounded-xl transition shadow-lg active:scale-95 flex items-center justify-center gap-2 text-sm cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                            <span>Copy Referral Code</span>
                        </button>
                    </div>
                </div>
            </div>
            @else
            <!-- Guest Welcome Banner -->
            <div class="bg-gradient-to-r from-[#102b54] to-[#1a4480] text-white rounded-3xl p-8 sm:p-10 shadow-xl border border-white/10 flex flex-col sm:flex-row items-center justify-between gap-6">
                <div>
                    <span class="px-3 py-1 bg-amber-400 text-black font-black text-xs uppercase tracking-wider rounded-full inline-block mb-3">Sign Up Special</span>
                    <h2 class="text-2xl sm:text-3xl font-black">Get $20 / ₹1,500 Off Your First Trip</h2>
                    <p class="text-gray-300 text-sm mt-1 max-w-xl">Create your free RideMyCars account today to claim your welcome voucher and unlock member-only promotional discounts.</p>
                </div>
                <div class="shrink-0 flex items-center gap-3">
                    <a href="/signup" class="px-6 py-3.5 bg-amber-400 hover:bg-amber-300 text-[#102b54] font-black rounded-2xl shadow-lg transition-all text-sm">
                        Claim Welcome Bonus →
                    </a>
                </div>
            </div>
            @endauth

            <!-- Filter Categories Pills -->
            <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
                <button type="button" @click="activeFilter = 'all'" 
                        :class="activeFilter === 'all' ? 'bg-amber-400 text-black font-black shadow-md' : 'bg-white dark:bg-white/5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/10 font-bold border border-gray-200 dark:border-white/10'"
                        class="px-5 py-2.5 rounded-full text-sm transition-all whitespace-nowrap cursor-pointer">
                    All Promotions (6)
                </button>
                <button type="button" @click="activeFilter = 'rides'" 
                        :class="activeFilter === 'rides' ? 'bg-amber-400 text-black font-black shadow-md' : 'bg-white dark:bg-white/5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/10 font-bold border border-gray-200 dark:border-white/10'"
                        class="px-5 py-2.5 rounded-full text-sm transition-all whitespace-nowrap cursor-pointer">
                    🚗 City & Airport Rides
                </button>
                <button type="button" @click="activeFilter = 'rentals'" 
                        :class="activeFilter === 'rentals' ? 'bg-amber-400 text-black font-black shadow-md' : 'bg-white dark:bg-white/5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/10 font-bold border border-gray-200 dark:border-white/10'"
                        class="px-5 py-2.5 rounded-full text-sm transition-all whitespace-nowrap cursor-pointer">
                    🚙 Car Rentals
                </button>
                <button type="button" @click="activeFilter = 'drivers'" 
                        :class="activeFilter === 'drivers' ? 'bg-amber-400 text-black font-black shadow-md' : 'bg-white dark:bg-white/5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/10 font-bold border border-gray-200 dark:border-white/10'"
                        class="px-5 py-2.5 rounded-full text-sm transition-all whitespace-nowrap cursor-pointer">
                    👨‍✈️ Private Chauffeur
                </button>
                <button type="button" @click="activeFilter = 'delivery'" 
                        :class="activeFilter === 'delivery' ? 'bg-amber-400 text-black font-black shadow-md' : 'bg-white dark:bg-white/5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/10 font-bold border border-gray-200 dark:border-white/10'"
                        class="px-5 py-2.5 rounded-full text-sm transition-all whitespace-nowrap cursor-pointer">
                    📦 Parcel Delivery
                </button>
            </div>

            <!-- Promotion Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- 1. WELCOME50 -->
                <div x-show="activeFilter === 'all' || activeFilter === 'rides'" 
                     class="bg-white dark:bg-[#141414] rounded-3xl p-6 sm:p-7 border border-gray-200/80 dark:border-white/10 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-amber-400/10 rounded-full blur-2xl group-hover:bg-amber-400/20 transition-all"></div>
                    <div>
                        <div class="flex items-center justify-between gap-3 mb-4">
                            <span class="px-3 py-1 bg-amber-400/15 border border-amber-400/40 text-amber-600 dark:text-amber-400 font-extrabold text-xs uppercase tracking-wider rounded-full">
                                50% OFF
                            </span>
                            <span class="text-xs font-bold text-gray-400">First Trip</span>
                        </div>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white mb-2">Welcome Discount</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed mb-6">
                            Enjoy 50% off on your first city ride, airport transfer, or vehicle rental up to $20 / ₹1,500.
                        </p>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-white/10">
                        <div class="flex items-center justify-between gap-3 mb-3 bg-gray-50 dark:bg-black/40 p-3 rounded-2xl border border-gray-200 dark:border-white/10">
                            <span class="font-mono font-black text-base text-gray-900 dark:text-amber-400 tracking-wider">WELCOME50</span>
                            <button type="button" 
                                    @click="copyCode('WELCOME50')"
                                    class="px-3 py-1.5 bg-black hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-200 text-white dark:text-black rounded-xl text-xs font-bold transition active:scale-90 cursor-pointer">
                                Copy
                            </button>
                        </div>
                        <a href="/ride" class="w-full block py-2.5 text-center text-xs font-black text-amber-600 dark:text-amber-400 hover:underline">
                            Book a Ride with this Code →
                        </a>
                    </div>
                </div>

                <!-- 2. RIDEINDIA -->
                <div x-show="activeFilter === 'all' || activeFilter === 'rides'" 
                     class="bg-white dark:bg-[#141414] rounded-3xl p-6 sm:p-7 border border-gray-200/80 dark:border-white/10 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/10 rounded-full blur-2xl group-hover:bg-blue-500/20 transition-all"></div>
                    <div>
                        <div class="flex items-center justify-between gap-3 mb-4">
                            <span class="px-3 py-1 bg-blue-500/15 border border-blue-500/40 text-blue-600 dark:text-blue-400 font-extrabold text-xs uppercase tracking-wider rounded-full">
                                20% OFF
                            </span>
                            <span class="text-xs font-bold text-gray-400">Airport & Intercity</span>
                        </div>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white mb-2">Airport & Intercity Saver</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed mb-6">
                            Flat 20% off on all pre-scheduled airport pickups, drop-offs, and intercity family routes.
                        </p>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-white/10">
                        <div class="flex items-center justify-between gap-3 mb-3 bg-gray-50 dark:bg-black/40 p-3 rounded-2xl border border-gray-200 dark:border-white/10">
                            <span class="font-mono font-black text-base text-gray-900 dark:text-blue-400 tracking-wider">RIDEINDIA</span>
                            <button type="button" 
                                    @click="copyCode('RIDEINDIA')"
                                    class="px-3 py-1.5 bg-black hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-200 text-white dark:text-black rounded-xl text-xs font-bold transition active:scale-90 cursor-pointer">
                                Copy
                            </button>
                        </div>
                        <a href="/ride" class="w-full block py-2.5 text-center text-xs font-black text-blue-600 dark:text-blue-400 hover:underline">
                            Schedule Airport Trip →
                        </a>
                    </div>
                </div>

                <!-- 3. WEEKENDESCAPE -->
                <div x-show="activeFilter === 'all' || activeFilter === 'rentals'" 
                     class="bg-white dark:bg-[#141414] rounded-3xl p-6 sm:p-7 border border-gray-200/80 dark:border-white/10 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-purple-500/10 rounded-full blur-2xl group-hover:bg-purple-500/20 transition-all"></div>
                    <div>
                        <div class="flex items-center justify-between gap-3 mb-4">
                            <span class="px-3 py-1 bg-purple-500/15 border border-purple-500/40 text-purple-600 dark:text-purple-400 font-extrabold text-xs uppercase tracking-wider rounded-full">
                                SAVE $30 / ₹2,500
                            </span>
                            <span class="text-xs font-bold text-gray-400">Car Rentals</span>
                        </div>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white mb-2">Weekend Car Rental Pass</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed mb-6">
                            Book any self-drive premium SUV or sedan for 2+ days and receive instant cashback & savings.
                        </p>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-white/10">
                        <div class="flex items-center justify-between gap-3 mb-3 bg-gray-50 dark:bg-black/40 p-3 rounded-2xl border border-gray-200 dark:border-white/10">
                            <span class="font-mono font-black text-base text-gray-900 dark:text-purple-400 tracking-wider">WEEKEND30</span>
                            <button type="button" 
                                    @click="copyCode('WEEKEND30')"
                                    class="px-3 py-1.5 bg-black hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-200 text-white dark:text-black rounded-xl text-xs font-bold transition active:scale-90 cursor-pointer">
                                Copy
                            </button>
                        </div>
                        <a href="/rent" class="w-full block py-2.5 text-center text-xs font-black text-purple-600 dark:text-purple-400 hover:underline">
                            Browse Rental Fleet →
                        </a>
                    </div>
                </div>

                <!-- 4. CHAUFFEUR15 -->
                <div x-show="activeFilter === 'all' || activeFilter === 'drivers'" 
                     class="bg-white dark:bg-[#141414] rounded-3xl p-6 sm:p-7 border border-gray-200/80 dark:border-white/10 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition-all"></div>
                    <div>
                        <div class="flex items-center justify-between gap-3 mb-4">
                            <span class="px-3 py-1 bg-emerald-500/15 border border-emerald-500/40 text-emerald-600 dark:text-emerald-400 font-extrabold text-xs uppercase tracking-wider rounded-full">
                                15% OFF
                            </span>
                            <span class="text-xs font-bold text-gray-400">Driver Hire</span>
                        </div>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white mb-2">Dedicated Chauffeur Special</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed mb-6">
                            Hire a verified professional driver for 4+ hours or multi-day executive trips at 15% off.
                        </p>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-white/10">
                        <div class="flex items-center justify-between gap-3 mb-3 bg-gray-50 dark:bg-black/40 p-3 rounded-2xl border border-gray-200 dark:border-white/10">
                            <span class="font-mono font-black text-base text-gray-900 dark:text-emerald-400 tracking-wider">CHAUFFEUR15</span>
                            <button type="button" 
                                    @click="copyCode('CHAUFFEUR15')"
                                    class="px-3 py-1.5 bg-black hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-200 text-white dark:text-black rounded-xl text-xs font-bold transition active:scale-90 cursor-pointer">
                                Copy
                            </button>
                        </div>
                        <a href="/hire-driver" class="w-full block py-2.5 text-center text-xs font-black text-emerald-600 dark:text-emerald-400 hover:underline">
                            Hire Verified Chauffeur →
                        </a>
                    </div>
                </div>

                <!-- 5. FREEDROP -->
                <div x-show="activeFilter === 'all' || activeFilter === 'delivery'" 
                     class="bg-white dark:bg-[#141414] rounded-3xl p-6 sm:p-7 border border-gray-200/80 dark:border-white/10 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-amber-500/10 rounded-full blur-2xl group-hover:bg-amber-500/20 transition-all"></div>
                    <div>
                        <div class="flex items-center justify-between gap-3 mb-4">
                            <span class="px-3 py-1 bg-amber-500/15 border border-amber-500/40 text-amber-600 dark:text-amber-400 font-extrabold text-xs uppercase tracking-wider rounded-full">
                                ZERO FEE
                            </span>
                            <span class="text-xs font-bold text-gray-400">Parcel Courier</span>
                        </div>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white mb-2">Free Delivery Booking</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed mb-6">
                            Zero platform dispatch fee on your next urgent documents or package delivery under 10 km.
                        </p>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-white/10">
                        <div class="flex items-center justify-between gap-3 mb-3 bg-gray-50 dark:bg-black/40 p-3 rounded-2xl border border-gray-200 dark:border-white/10">
                            <span class="font-mono font-black text-base text-gray-900 dark:text-amber-400 tracking-wider">FREEDROP</span>
                            <button type="button" 
                                    @click="copyCode('FREEDROP')"
                                    class="px-3 py-1.5 bg-black hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-200 text-white dark:text-black rounded-xl text-xs font-bold transition active:scale-90 cursor-pointer">
                                Copy
                            </button>
                        </div>
                        <a href="/delivery" class="w-full block py-2.5 text-center text-xs font-black text-amber-600 dark:text-amber-400 hover:underline">
                            Dispatch Package →
                        </a>
                    </div>
                </div>

                <!-- 6. CORPORATEVIP -->
                <div x-show="activeFilter === 'all' || activeFilter === 'rides' || activeFilter === 'rentals'" 
                     class="bg-white dark:bg-[#141414] rounded-3xl p-6 sm:p-7 border border-gray-200/80 dark:border-white/10 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/10 rounded-full blur-2xl group-hover:bg-indigo-500/20 transition-all"></div>
                    <div>
                        <div class="flex items-center justify-between gap-3 mb-4">
                            <span class="px-3 py-1 bg-indigo-500/15 border border-indigo-500/40 text-indigo-600 dark:text-indigo-400 font-extrabold text-xs uppercase tracking-wider rounded-full">
                                25% OFF
                            </span>
                            <span class="text-xs font-bold text-gray-400">Executive Travel</span>
                        </div>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white mb-2">Corporate & Business Pass</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed mb-6">
                            25% off monthly business travel packages and corporate account billing with priority dispatch.
                        </p>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-white/10">
                        <div class="flex items-center justify-between gap-3 mb-3 bg-gray-50 dark:bg-black/40 p-3 rounded-2xl border border-gray-200 dark:border-white/10">
                            <span class="font-mono font-black text-base text-gray-900 dark:text-indigo-400 tracking-wider">CORP25VIP</span>
                            <button type="button" 
                                    @click="copyCode('CORP25VIP')"
                                    class="px-3 py-1.5 bg-black hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-200 text-white dark:text-black rounded-xl text-xs font-bold transition active:scale-90 cursor-pointer">
                                Copy
                            </button>
                        </div>
                        <a href="/membership" class="w-full block py-2.5 text-center text-xs font-black text-indigo-600 dark:text-indigo-400 hover:underline">
                            View Corporate Memberships →
                        </a>
                    </div>
                </div>

            </div>

            <!-- How To Redeem Banner -->
            <div class="bg-gray-100 dark:bg-[#121212] rounded-3xl p-8 sm:p-12 border border-gray-200 dark:border-white/10">
                <div class="text-center max-w-2xl mx-auto mb-10">
                    <h2 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">How to Redeem a Promo Code</h2>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mt-2">Apply your promo code in 3 simple steps on website or mobile app.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                    <div class="flex flex-col items-center">
                        <div class="w-14 h-14 rounded-2xl bg-amber-400 text-black font-black text-xl flex items-center justify-center mb-4 shadow-md">
                            1
                        </div>
                        <h4 class="text-base font-extrabold text-gray-900 dark:text-white mb-1">Copy Any Promo Code</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">Click the "Copy" button on any active offer above or use your referral code.</p>
                    </div>
                    <div class="flex flex-col items-center">
                        <div class="w-14 h-14 rounded-2xl bg-[#102b54] text-white font-black text-xl flex items-center justify-center mb-4 shadow-md">
                            2
                        </div>
                        <h4 class="text-base font-extrabold text-gray-900 dark:text-white mb-1">Choose Your Ride or Rental</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">Select your pickup location, car rental period, or private driver package.</p>
                    </div>
                    <div class="flex flex-col items-center">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-500 text-white font-black text-xl flex items-center justify-center mb-4 shadow-md">
                            3
                        </div>
                        <h4 class="text-base font-extrabold text-gray-900 dark:text-white mb-1">Instant Discount Applied</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">Paste your code before payment or confirm booking with reduced fare automatically.</p>
                    </div>
                </div>
            </div>

            <!-- Promotion FAQs -->
            <div class="max-w-3xl mx-auto space-y-4">
                <h2 class="text-2xl font-black text-gray-900 dark:text-white text-center mb-6">Frequently Asked Questions</h2>
                
                <div class="bg-white dark:bg-[#141414] rounded-2xl p-5 border border-gray-200 dark:border-white/10">
                    <h4 class="font-bold text-gray-900 dark:text-white text-sm mb-1">Can I combine multiple promotion codes?</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">Only one promotional voucher can be applied per single ride, rental, or chauffeur hire booking. Referral wallet credits can be combined with any fare.</p>
                </div>
                <div class="bg-white dark:bg-[#141414] rounded-2xl p-5 border border-gray-200 dark:border-white/10">
                    <h4 class="font-bold text-gray-900 dark:text-white text-sm mb-1">When do the promotional codes expire?</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">Active promo vouchers are refreshed monthly. Personal referral codes never expire as long as your account remains active.</p>
                </div>
                <div class="bg-white dark:bg-[#141414] rounded-2xl p-5 border border-gray-200 dark:border-white/10">
                    <h4 class="font-bold text-gray-900 dark:text-white text-sm mb-1">Do promotions work on both the website and mobile app?</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">Yes! All promo codes and referral links work seamlessly across the RideMyCars web portal, iOS app, and Android app.</p>
                </div>
            </div>

        </main>
    </div>
</x-layout>
