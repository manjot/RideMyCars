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
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 font-extrabold text-xs uppercase tracking-wider border border-amber-300 dark:border-amber-700/60 mb-4 shadow-sm">
                    <span>🏷️</span> Limited-Time Deals & Travel Discounts
                </div>
                <h1 class="text-4xl sm:text-5xl font-black text-gray-900 dark:text-white tracking-tight leading-tight">
                    Ride More, Save More with <span class="text-amber-600 dark:text-amber-400 font-black">RideMyCars</span>
                </h1>
                <p class="mt-4 text-base sm:text-lg text-gray-700 dark:text-gray-200 font-medium leading-relaxed">
                    Enjoy exclusive coupon codes, referral credits, airport special vouchers, and discount perks on every ride, car rental, chauffeur hire, and parcel delivery.
                </p>
            </div>

            <!-- Referral Rewards Banner (Personal Code) -->
            @auth
            <div class="rounded-3xl p-6 sm:p-10 shadow-2xl border-2 border-amber-400/40 relative overflow-hidden"
                 style="background: linear-gradient(135deg, #091a33 0%, #102b54 50%, #163666 100%) !important; color: #ffffff !important;">
                <div class="absolute -right-10 -bottom-10 w-60 h-60 bg-amber-400/15 rounded-full blur-3xl pointer-events-none"></div>
                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-8">
                    <div class="max-w-2xl">
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-amber-400/20 border border-amber-400/40 rounded-full text-xs font-bold text-amber-300 uppercase tracking-wider mb-3">
                            <span>🎁</span> Your Exclusive Invite Code
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black leading-tight" style="color: #ffffff !important;">
                            Invite Friends & Earn Unlimited Ride Perks
                        </h2>
                        <p class="text-sm sm:text-base font-semibold mt-2 leading-relaxed" style="color: #fef3c7 !important;">
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
                    <div class="border border-amber-400/50 rounded-3xl p-6 text-center sm:min-w-[280px] shadow-inner backdrop-blur-md"
                         style="background: rgba(0, 0, 0, 0.55) !important;">
                        <span class="text-xs text-amber-200/90 font-bold uppercase tracking-wider block mb-2">Your Personal Code</span>
                        <div class="text-3xl sm:text-4xl font-mono font-black text-amber-300 tracking-widest my-2">
                            {{ auth()->user()->referral_code ?? 'RMC000000' }}
                        </div>
                        <p class="text-[11px] text-gray-300 mb-4 font-medium">No minimum booking required</p>
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
            <div class="rounded-3xl p-8 sm:p-10 shadow-2xl border-2 border-amber-400/40 flex flex-col sm:flex-row items-center justify-between gap-6 relative overflow-hidden"
                 style="background: linear-gradient(135deg, #091a33 0%, #102b54 50%, #163666 100%) !important; color: #ffffff !important;">
                <!-- Decorative ambient glow -->
                <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-amber-400/15 rounded-full blur-3xl pointer-events-none"></div>
                <div class="relative z-10 max-w-2xl">
                    <span class="px-3.5 py-1.5 font-black text-xs uppercase tracking-wider rounded-full inline-flex items-center gap-1.5 mb-3 shadow-md"
                          style="background-color: #f59e0b !important; color: #0b0f17 !important;">
                        <span>⚡</span> Sign Up Special
                    </span>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight leading-snug"
                        style="color: #ffffff !important;">
                        Get $20 / ₹1,500 Off Your First Trip
                    </h2>
                    <p class="text-sm sm:text-base font-semibold mt-2 leading-relaxed"
                       style="color: #fef3c7 !important;">
                        Create your free RideMyCars account today to claim your welcome voucher and unlock member-only promotional discounts.
                    </p>
                </div>
                <div class="relative z-10 shrink-0">
                    <a href="/signup" 
                       class="inline-flex items-center gap-2 px-7 py-4 font-black rounded-2xl shadow-xl hover:scale-105 transition-all text-sm hover:brightness-105 active:scale-95"
                       style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%) !important; color: #0b0f17 !important;">
                        <span>Claim Welcome Bonus</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
            @endauth

            <!-- Official Campaign Flyers Showcase Section -->
            <div class="space-y-6" x-data="{ activeFlyerModal: null }">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                    <div>
                        <span class="px-3 py-1 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 font-extrabold text-xs uppercase tracking-wider border border-amber-300 dark:border-amber-700/60 inline-block mb-2">
                            ⭐ Official Campaign Materials
                        </span>
                        <h2 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                            Live Promotional Campaigns & Program Posters
                        </h2>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Click any flyer to view high-resolution details, scannable QR codes, and instant redeem links.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-6">
                    
                    <!-- Flyer 1: 50% Off First 3 Rides (Hatchback Edition) -->
                    <div class="bg-white dark:bg-[#141414] rounded-3xl border-2 border-amber-300 dark:border-amber-500/30 overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 group flex flex-col justify-between hover:-translate-y-1">
                        <div class="relative cursor-pointer h-72 sm:h-80 p-3 bg-gradient-to-b from-gray-50 via-gray-100/50 to-gray-50 dark:from-[#1c1c1c] dark:via-[#161616] dark:to-[#1c1c1c] border-b border-gray-100 dark:border-white/5 flex items-center justify-center overflow-hidden"
                             @click="activeFlyerModal = { title: 'Ride More, Pay Less — 50% Off First 3 Rides (Hatchback)', img: '{{ asset('images/promo-ride-payless.jpg') }}', link: '/ride', cta: 'Book Ride (50% Off)' }">
                            <img src="{{ asset('images/promo-ride-payless.jpg') }}" alt="Ride More, Pay Less - 50% Off First 3 Rides" class="max-w-full max-h-full w-auto h-auto object-contain rounded-xl shadow-md group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute top-3 left-3 px-2 py-0.5 rounded-full bg-amber-500 text-gray-950 font-black text-[10px] uppercase tracking-wider shadow">
                                50% OFF
                            </div>
                            <div class="absolute bottom-3 right-3 px-2.5 py-1 rounded-full bg-black/80 backdrop-blur-sm text-white font-bold text-xs opacity-0 group-hover:opacity-100 transition-opacity shadow-lg">
                                <span>🔍 Click to Enlarge</span>
                            </div>
                        </div>
                        <div class="p-5 space-y-3">
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-300">
                                    New Riders • 50% Off
                                </span>
                                <h3 class="text-base font-black text-gray-900 dark:text-white mt-1.5 leading-snug">Ride More, Pay Less (Hatchback)</h3>
                                <p class="text-xs text-gray-700 dark:text-gray-300 font-medium mt-1">Download app to redeem 50% off automatically on your first 3 trips. Fast rides, 24/7 available, and low fares.</p>
                                <ul class="text-[11px] text-gray-800 dark:text-gray-200 space-y-1 pt-2 font-medium">
                                    <li class="flex items-center gap-1.5"><span class="text-amber-500 font-bold">✓</span> Fast rides & quick pickup in minutes</li>
                                    <li class="flex items-center gap-1.5"><span class="text-amber-500 font-bold">✓</span> 24/7 availability city-wide</li>
                                    <li class="flex items-center gap-1.5"><span class="text-amber-500 font-bold">✓</span> Low fares & transparent pricing</li>
                                </ul>
                            </div>
                            <div class="pt-2 border-t border-gray-100 dark:border-white/10 flex items-center gap-2">
                                <a href="/ride" class="flex-1 py-2.5 px-3 bg-amber-500 hover:bg-amber-600 text-gray-950 text-center font-black text-xs rounded-xl shadow-xs transition">
                                    Book Ride (50% Off) →
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Flyer 2: 50% Off First 3 Rides (Sedan Edition) -->
                    <div class="bg-white dark:bg-[#141414] rounded-3xl border-2 border-amber-400 dark:border-amber-500/40 overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 group flex flex-col justify-between hover:-translate-y-1">
                        <div class="relative cursor-pointer h-72 sm:h-80 p-3 bg-gradient-to-b from-gray-50 via-gray-100/50 to-gray-50 dark:from-[#1c1c1c] dark:via-[#161616] dark:to-[#1c1c1c] border-b border-gray-100 dark:border-white/5 flex items-center justify-center overflow-hidden"
                             @click="activeFlyerModal = { title: 'Ride More, Pay Less — 50% Off (Executive Sedan Edition)', img: '{{ asset('images/promo-ride-payless-sedan.jpg') }}', link: '/apps#rider-app', cta: 'Download Rider App' }">
                            <img src="{{ asset('images/promo-ride-payless-sedan.jpg') }}" alt="Ride More, Pay Less - Executive Sedan Edition" class="max-w-full max-h-full w-auto h-auto object-contain rounded-xl shadow-md group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute top-3 left-3 px-2 py-0.5 rounded-full bg-amber-500 text-gray-950 font-black text-[10px] uppercase tracking-wider shadow">
                                SEDAN EDITION
                            </div>
                            <div class="absolute bottom-3 right-3 px-2.5 py-1 rounded-full bg-black/80 backdrop-blur-sm text-white font-bold text-xs opacity-0 group-hover:opacity-100 transition-opacity shadow-lg">
                                <span>🔍 Click to Enlarge</span>
                            </div>
                        </div>
                        <div class="p-5 space-y-3">
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-300">
                                    iOS & Android • QR Scan
                                </span>
                                <h3 class="text-base font-black text-gray-900 dark:text-white mt-1.5 leading-snug">Ride More, Pay Less (Sedan)</h3>
                                <p class="text-xs text-gray-700 dark:text-gray-300 font-medium mt-1">Scan the QR code to install RideMyCars on your smartphone. Premium sedan comfort with 50% off for new users.</p>
                                <ul class="text-[11px] text-gray-800 dark:text-gray-200 space-y-1 pt-2 font-medium">
                                    <li class="flex items-center gap-1.5"><span class="text-amber-500 font-bold">✓</span> Scannable QR code for instant install</li>
                                    <li class="flex items-center gap-1.5"><span class="text-amber-500 font-bold">✓</span> Available on iOS App Store & Android</li>
                                    <li class="flex items-center gap-1.5"><span class="text-amber-500 font-bold">✓</span> Safe, reliable, and affordable mobility</li>
                                </ul>
                            </div>
                            <div class="pt-2 border-t border-gray-100 dark:border-white/10 flex items-center gap-2">
                                <a href="/apps#rider-app" class="flex-1 py-2.5 px-3 bg-amber-500 hover:bg-amber-600 text-gray-950 text-center font-black text-xs rounded-xl shadow-xs transition">
                                    Scan & Download →
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Flyer 3: Own Cars? Turn Them Into Monthly Income (Fleet Hosts) -->
                    <div class="bg-white dark:bg-[#141414] rounded-3xl border-2 border-blue-400 dark:border-blue-500/40 overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 group flex flex-col justify-between hover:-translate-y-1">
                        <div class="relative cursor-pointer h-72 sm:h-80 p-3 bg-gradient-to-b from-gray-50 via-gray-100/50 to-gray-50 dark:from-[#1c1c1c] dark:via-[#161616] dark:to-[#1c1c1c] border-b border-gray-100 dark:border-white/5 flex items-center justify-center overflow-hidden"
                             @click="activeFlyerModal = { title: 'Own Cars? Turn Them Into Monthly Income — List Your Fleet', img: '{{ asset('images/promo-fleet-owner.jpg') }}', link: '/become-owner', cta: 'List Your Fleet Now' }">
                            <img src="{{ asset('images/promo-fleet-owner.jpg') }}" alt="Turn Cars Into Monthly Income - List Your Fleet on RideMyCars" class="max-w-full max-h-full w-auto h-auto object-contain rounded-xl shadow-md group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute top-3 left-3 px-2 py-0.5 rounded-full bg-blue-600 text-white font-black text-[10px] uppercase tracking-wider shadow">
                                FLEET HOSTS
                            </div>
                            <div class="absolute bottom-3 right-3 px-2.5 py-1 rounded-full bg-black/80 backdrop-blur-sm text-white font-bold text-xs opacity-0 group-hover:opacity-100 transition-opacity shadow-lg">
                                <span>🔍 Click to Enlarge</span>
                            </div>
                        </div>
                        <div class="p-5 space-y-3">
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 border border-blue-300">
                                    Fleet Partnership • Passive Yield
                                </span>
                                <h3 class="text-base font-black text-gray-900 dark:text-white mt-1.5 leading-snug">Turn Cars Into Monthly Income</h3>
                                <p class="text-xs text-gray-700 dark:text-gray-300 font-medium mt-1">List your cars on RideMyCars. We provide verified drivers, 24/7 GPS fleet tracking, and weekly bank + MoMo payouts.</p>
                                <ul class="text-[11px] text-gray-800 dark:text-gray-200 space-y-1 pt-2 font-medium">
                                    <li class="flex items-center gap-1.5"><span class="text-blue-500 font-bold">✓</span> Earn from every completed trip</li>
                                    <li class="flex items-center gap-1.5"><span class="text-blue-500 font-bold">✓</span> We provide thoroughly vetted drivers</li>
                                    <li class="flex items-center gap-1.5"><span class="text-blue-500 font-bold">✓</span> 24/7 real-time fleet GPS tracking</li>
                                    <li class="flex items-center gap-1.5"><span class="text-blue-500 font-bold">✓</span> Weekly bank transfer + Instant MoMo cash outs</li>
                                </ul>
                            </div>
                            <div class="pt-2 border-t border-gray-100 dark:border-white/10 flex items-center gap-2">
                                <a href="/become-owner" class="flex-1 py-2.5 px-3 bg-blue-600 hover:bg-blue-700 text-white text-center font-black text-xs rounded-xl shadow-xs transition">
                                    List Fleet Now →
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Flyer 4: Become a RideMyCars Chauffeur — Earn 85% -->
                    <div class="bg-white dark:bg-[#141414] rounded-3xl border-2 border-indigo-400 dark:border-indigo-500/40 overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 group flex flex-col justify-between hover:-translate-y-1">
                        <div class="relative cursor-pointer h-72 sm:h-80 p-3 bg-gradient-to-b from-gray-50 via-gray-100/50 to-gray-50 dark:from-[#1c1c1c] dark:via-[#161616] dark:to-[#1c1c1c] border-b border-gray-100 dark:border-white/5 flex items-center justify-center overflow-hidden"
                             @click="activeFlyerModal = { title: 'Become a RideMyCars Chauffeur — Professional Chauffeurs Wanted (85%)', img: '{{ asset('images/promo-chauffeur-wanted.jpg') }}', link: '/driver-signup', cta: 'Register as Chauffeur' }">
                            <img src="{{ asset('images/promo-chauffeur-wanted.jpg') }}" alt="Become a RideMyCars Chauffeur - Professional Chauffeurs Wanted Earn 85%" class="max-w-full max-h-full w-auto h-auto object-contain rounded-xl shadow-md group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute top-3 left-3 px-2 py-0.5 rounded-full bg-indigo-600 text-white font-black text-[10px] uppercase tracking-wider shadow">
                                EARN 85%
                            </div>
                            <div class="absolute bottom-3 right-3 px-2.5 py-1 rounded-full bg-black/80 backdrop-blur-sm text-white font-bold text-xs opacity-0 group-hover:opacity-100 transition-opacity shadow-lg">
                                <span>🔍 Click to Enlarge</span>
                            </div>
                        </div>
                        <div class="p-5 space-y-3">
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-950/60 text-indigo-800 dark:text-indigo-300 border border-indigo-300">
                                    Chauffeur Tier • 3-Step Setup
                                </span>
                                <h3 class="text-base font-black text-gray-900 dark:text-white mt-1.5 leading-snug">Become a RideMyCars Chauffeur</h3>
                                <p class="text-xs text-gray-700 dark:text-gray-300 font-medium mt-1">Professional chauffeurs wanted. Keep 85% from Trip #1. 3 simple steps: Register online, verify documents, and start driving.</p>
                                <ul class="text-[11px] text-gray-800 dark:text-gray-200 space-y-1 pt-2 font-medium">
                                    <li class="flex items-center gap-1.5"><span class="text-indigo-500 font-bold">✓</span> Keep 85% guaranteed from Trip #1</li>
                                    <li class="flex items-center gap-1.5"><span class="text-indigo-500 font-bold">✓</span> Verified riders & executive concierge</li>
                                    <li class="flex items-center gap-1.5"><span class="text-indigo-500 font-bold">✓</span> Weekly Bank Transfer + Smart Wallet</li>
                                    <li class="flex items-center gap-1.5"><span class="text-indigo-500 font-bold">✓</span> NEW: Instant MoMo cash outs 24/7</li>
                                </ul>
                            </div>
                            <div class="pt-2 border-t border-gray-100 dark:border-white/10 flex items-center gap-2">
                                <a href="/driver-signup" class="flex-1 py-2.5 px-3 bg-indigo-600 hover:bg-indigo-700 text-white text-center font-black text-xs rounded-xl shadow-xs transition">
                                    Register as Chauffeur →
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Flyer 5: Drive with Us, Earn 90% (Standard Partner) -->
                    <div class="bg-white dark:bg-[#141414] rounded-3xl border-2 border-emerald-300 dark:border-emerald-500/30 overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 group flex flex-col justify-between hover:-translate-y-1">
                        <div class="relative cursor-pointer h-72 sm:h-80 p-3 bg-gradient-to-b from-gray-50 via-gray-100/50 to-gray-50 dark:from-[#1c1c1c] dark:via-[#161616] dark:to-[#1c1c1c] border-b border-gray-100 dark:border-white/5 flex items-center justify-center overflow-hidden"
                             @click="activeFlyerModal = { title: 'Drive With Us, Earn 90% — Only 10% Platform Fee', img: '{{ asset('images/promo-driver-earn90.jpg') }}', link: '/become-driver', cta: 'Join as Driver Partner' }">
                            <img src="{{ asset('images/promo-driver-earn90.jpg') }}" alt="Drive With Us, Earn 90%" class="max-w-full max-h-full w-auto h-auto object-contain rounded-xl shadow-md group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute top-3 left-3 px-2 py-0.5 rounded-full bg-emerald-600 text-white font-black text-[10px] uppercase tracking-wider shadow">
                                KEEP 90%
                            </div>
                            <div class="absolute bottom-3 right-3 px-2.5 py-1 rounded-full bg-black/80 backdrop-blur-sm text-white font-bold text-xs opacity-0 group-hover:opacity-100 transition-opacity shadow-lg">
                                <span>🔍 Click to Enlarge</span>
                            </div>
                        </div>
                        <div class="p-5 space-y-3">
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-300">
                                    Drivers • 10% Fee
                                </span>
                                <h3 class="text-base font-black text-gray-900 dark:text-white mt-1.5 leading-snug">Drive & Earn 90%</h3>
                                <p class="text-xs text-gray-700 dark:text-gray-300 font-medium mt-1">Keep 90% from Trip #1 with weekly bank transfers and instant MoMo payouts. Only 10% platform commission.</p>
                                <ul class="text-[11px] text-gray-800 dark:text-gray-200 space-y-1 pt-2 font-medium">
                                    <li class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> Industry lowest 10% commission</li>
                                    <li class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> Instant trip dispatch alerts</li>
                                    <li class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> Weekly Friday bank deposits + MoMo</li>
                                </ul>
                            </div>
                            <div class="pt-2 border-t border-gray-100 dark:border-white/10 flex items-center gap-2">
                                <a href="/become-driver" class="flex-1 py-2.5 px-3 bg-emerald-600 hover:bg-emerald-700 text-white text-center font-black text-xs rounded-xl shadow-xs transition">
                                    Join Partner →
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Flyer 6: Official Mobile App & Instant Registration QR Poster -->
                    <div class="bg-white dark:bg-[#141414] rounded-3xl border-2 border-yellow-400 dark:border-yellow-500/40 overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 group flex flex-col justify-between hover:-translate-y-1">
                        <div class="relative cursor-pointer h-72 sm:h-80 p-3 bg-gradient-to-b from-gray-50 via-gray-100/50 to-gray-50 dark:from-[#1c1c1c] dark:via-[#161616] dark:to-[#1c1c1c] border-b border-gray-100 dark:border-white/5 flex items-center justify-center overflow-hidden"
                             @click="activeFlyerModal = { title: 'RideMyCars Official App & Instant Registration QR Poster', img: '{{ asset('images/promo-app-qr-register.jpg') }}', link: '/apps', cta: 'Download Mobile App' }">
                            <img src="{{ asset('images/promo-app-qr-register.jpg') }}" alt="RideMyCars Official App & Registration QR Poster" class="max-w-full max-h-full w-auto h-auto object-contain rounded-xl shadow-md group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute top-3 left-3 px-2 py-0.5 rounded-full bg-yellow-500 text-gray-950 font-black text-[10px] uppercase tracking-wider shadow">
                                SCAN & REGISTER
                            </div>
                            <div class="absolute bottom-3 right-3 px-2.5 py-1 rounded-full bg-black/80 backdrop-blur-sm text-white font-bold text-xs opacity-0 group-hover:opacity-100 transition-opacity shadow-lg">
                                <span>🔍 Click to Enlarge</span>
                            </div>
                        </div>
                        <div class="p-5 space-y-3">
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-yellow-100 dark:bg-yellow-950/60 text-yellow-800 dark:text-yellow-300 border border-yellow-300">
                                    Universal App • Quick QR
                                </span>
                                <h3 class="text-base font-black text-gray-900 dark:text-white mt-1.5 leading-snug">Official App & Register Poster</h3>
                                <p class="text-xs text-gray-700 dark:text-gray-300 font-medium mt-1">Scan the official QR code to download from App Store or Google Play. Safe, reliable, and affordable rides anytime.</p>
                                <ul class="text-[11px] text-gray-800 dark:text-gray-200 space-y-1 pt-2 font-medium">
                                    <li class="flex items-center gap-1.5"><span class="text-yellow-600 font-bold">✓</span> Large scannable official QR code</li>
                                    <li class="flex items-center gap-1.5"><span class="text-yellow-600 font-bold">✓</span> Direct Apple App Store & Google Play links</li>
                                    <li class="flex items-center gap-1.5"><span class="text-yellow-600 font-bold">✓</span> Quick 2-minute account signup</li>
                                </ul>
                            </div>
                            <div class="pt-2 border-t border-gray-100 dark:border-white/10 flex items-center gap-2">
                                <a href="/apps" class="flex-1 py-2.5 px-3 bg-yellow-500 hover:bg-yellow-600 text-gray-950 text-center font-black text-xs rounded-xl shadow-xs transition">
                                    Get Mobile App →
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Flyer 7: Accra & Tema Package Delivery -->
                    <div class="bg-white dark:bg-[#141414] rounded-3xl border-2 border-purple-300 dark:border-purple-500/30 overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 group flex flex-col justify-between hover:-translate-y-1">
                        <div class="relative cursor-pointer h-72 sm:h-80 p-3 bg-gradient-to-b from-gray-50 via-gray-100/50 to-gray-50 dark:from-[#1c1c1c] dark:via-[#161616] dark:to-[#1c1c1c] border-b border-gray-100 dark:border-white/5 flex items-center justify-center overflow-hidden"
                             @click="activeFlyerModal = { title: 'Fast Package Delivery — Accra & Tema', img: '{{ asset('images/promo-delivery-accra.jpg') }}', link: '/delivery', cta: 'Send a Package' }">
                            <img src="{{ asset('images/promo-delivery-accra.jpg') }}" alt="Fast Package Delivery Accra & Tema" class="max-w-full max-h-full w-auto h-auto object-contain rounded-xl shadow-md group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute top-3 left-3 px-2 py-0.5 rounded-full bg-purple-600 text-white font-black text-[10px] uppercase tracking-wider shadow">
                                ACCRA & TEMA
                            </div>
                            <div class="absolute bottom-3 right-3 px-2.5 py-1 rounded-full bg-black/80 backdrop-blur-sm text-white font-bold text-xs opacity-0 group-hover:opacity-100 transition-opacity shadow-lg">
                                <span>🔍 Click to Enlarge</span>
                            </div>
                        </div>
                        <div class="p-5 space-y-3">
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-purple-100 dark:bg-purple-950/60 text-purple-800 dark:text-purple-300 border border-purple-300">
                                    Accra & Tema Express
                                </span>
                                <h3 class="text-base font-black text-gray-900 dark:text-white mt-1.5 leading-snug">Express Courier Dispatch</h3>
                                <p class="text-xs text-gray-700 dark:text-gray-300 font-medium mt-1">Same day parcel delivery with live GPS tracking, smart wallet, and MoMo.</p>
                                <ul class="text-[11px] text-gray-800 dark:text-gray-200 space-y-1 pt-2 font-medium">
                                    <li class="flex items-center gap-1.5"><span class="text-purple-500 font-bold">✓</span> Same day express delivery</li>
                                    <li class="flex items-center gap-1.5"><span class="text-purple-500 font-bold">✓</span> Turn-by-turn live GPS tracking</li>
                                    <li class="flex items-center gap-1.5"><span class="text-purple-500 font-bold">✓</span> Instant MoMo & wallet payments</li>
                                </ul>
                            </div>
                            <div class="pt-2 border-t border-gray-100 dark:border-white/10 flex items-center gap-2">
                                <a href="/delivery" class="flex-1 py-2.5 px-3 bg-purple-600 hover:bg-purple-700 text-white text-center font-black text-xs rounded-xl shadow-xs transition">
                                    Dispatch Parcel →
                                </a>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Flyer Lightbox Modal -->
                <template x-teleport="body">
                    <div x-show="activeFlyerModal" 
                         style="display: none;"
                         class="fixed inset-0 z-[999999] flex items-center justify-center p-4 bg-black/85 backdrop-blur-md"
                         @click.self="activeFlyerModal = null"
                         @keydown.escape.window="activeFlyerModal = null">
                        <div class="relative max-w-xl w-full bg-white dark:bg-[#181818] rounded-3xl p-5 shadow-2xl border border-white/20 flex flex-col items-center">
                            <button type="button" 
                                    @click="activeFlyerModal = null"
                                    class="absolute -top-3 -right-3 w-9 h-9 rounded-full bg-black text-white flex items-center justify-center text-sm font-bold shadow-lg">
                                ✕
                            </button>
                            <img :src="activeFlyerModal?.img" 
                                 :alt="activeFlyerModal?.title"
                                 class="w-full max-h-[75vh] rounded-2xl object-contain shadow-md mb-4">
                            <div class="w-full flex items-center justify-between gap-4 pt-2 border-t border-gray-100 dark:border-white/10">
                                <h4 class="text-sm font-black text-gray-900 dark:text-white" x-text="activeFlyerModal?.title"></h4>
                                <a :href="activeFlyerModal?.link" 
                                   class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-gray-950 font-black text-xs rounded-xl shrink-0 transition"
                                   x-text="activeFlyerModal?.cta"></a>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

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
                        <p class="text-xs text-gray-700 dark:text-gray-300 font-medium leading-relaxed mb-6">
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
                        <p class="text-xs text-gray-700 dark:text-gray-300 font-medium leading-relaxed mb-6">
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
                        <p class="text-xs text-gray-700 dark:text-gray-300 font-medium leading-relaxed mb-6">
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
                        <p class="text-xs text-gray-700 dark:text-gray-300 font-medium leading-relaxed mb-6">
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
                        <p class="text-xs text-gray-700 dark:text-gray-300 font-medium leading-relaxed mb-6">
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
                        <p class="text-xs text-gray-700 dark:text-gray-300 font-medium leading-relaxed mb-6">
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

            <!-- Official Video Reels & Campaign Spotlights -->
            <section class="rounded-3xl p-6 sm:p-10 bg-[#0a0d14] text-white border-2 border-amber-400/30 shadow-2xl relative overflow-hidden"
                     x-data="{
                         activeVideoPromo: null,
                         promoVideos: [
                             {
                                 id: 1,
                                 title: 'Executive Ride Experience',
                                 badge: '50% OFF FIRST 3 RIDES',
                                 badgeColor: 'amber',
                                 duration: '0:25',
                                 videoSrc: '{{ asset('videos/video-1.mp4') }}',
                                 poster: '{{ asset('videos/poster-1.jpg') }}',
                                 tagline: 'Ride More, Pay Less — Transparent Pricing & Zero Surge',
                                 desc: 'Enjoy 50% discount on your first 3 trips. High-end executive comfort with verified chauffeurs across Greater Accra.',
                                 ctaText: 'Claim 50% Off & Book Ride',
                                 ctaLink: '/ride'
                             },
                             {
                                 id: 2,
                                 title: 'Chauffeur & Driver Career',
                                 badge: 'KEEP 85-90% REVENUE',
                                 badgeColor: 'indigo',
                                 duration: '0:33',
                                 videoSrc: '{{ asset('videos/video-2.mp4') }}',
                                 poster: '{{ asset('videos/poster-2.jpg') }}',
                                 tagline: 'Only 10% Platform Fee — You Keep 85% to 90%',
                                 desc: 'Why give away 25% or more to foreign apps? Keep 90% from your first trip with guaranteed weekly deposits and instant 24/7 MoMo.',
                                 ctaText: 'Register as Driver',
                                 ctaLink: '/become-driver'
                             },
                             {
                                 id: 3,
                                 title: 'Earnings & Instant Payouts',
                                 badge: 'INSTANT 24/7 MOMO',
                                 badgeColor: 'emerald',
                                 duration: '0:36',
                                 videoSrc: '{{ asset('videos/video-3.mp4') }}',
                                 poster: '{{ asset('videos/poster-3.jpg') }}',
                                 tagline: 'Fast Mobile Money & Commercial Bank Transfers',
                                 desc: 'Withdraw your trip earnings with zero waiting period. Direct to your MTN, Telecel, or AT Mobile Money wallet anytime.',
                                 ctaText: 'Calculate Driver Profit',
                                 ctaLink: '/become-driver#calculator'
                             },
                             {
                                 id: 4,
                                 title: 'Smart City Mobility',
                                 badge: 'MULTI-MODAL TRANSIT',
                                 badgeColor: 'blue',
                                 duration: '0:25',
                                 videoSrc: '{{ asset('videos/video-4.mp4') }}',
                                 poster: '{{ asset('videos/poster-4.jpg') }}',
                                 tagline: 'Cars, Bikes & Express Delivery at Your Fingertips',
                                 desc: 'Book on-demand city trips, rent executive luxury cars, or send fast parcel couriers across Greater Accra and Tema.',
                                 ctaText: 'Explore Services',
                                 ctaLink: '/ride'
                             }
                         ],
                         openPromoModal(v) {
                             this.activeVideoPromo = v;
                             this.$nextTick(() => {
                                 const el = document.getElementById('promoModalPlayer');
                                 if (el) {
                                     el.load();
                                     el.currentTime = 0;
                                     el.play().catch(() => {});
                                 }
                             });
                         },
                         closePromoModal() {
                             const el = document.getElementById('promoModalPlayer');
                             if (el) el.pause();
                             this.activeVideoPromo = null;
                         }
                     }">
                <div class="relative z-10">
                    <div class="text-center max-w-2xl mx-auto mb-10">
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-amber-400/20 border border-amber-400/40 rounded-full text-xs font-bold text-amber-300 uppercase tracking-wider mb-3">
                            <span>🎬</span> Watch Brand Reels & Stories
                        </div>
                        <h2 class="text-2xl sm:text-4xl font-black text-white tracking-tight">
                            See Our Offers & Experience in Motion
                        </h2>
                        <p class="text-sm text-gray-300 mt-2 font-medium">
                            Watch our official video reels highlighting passenger discounts, chauffeur careers, and instant MoMo driver cashouts.
                        </p>
                    </div>

                    <!-- 4 Video Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <template x-for="v in promoVideos" :key="v.id">
                            <div class="group relative rounded-2xl overflow-hidden border border-white/20 hover:border-amber-400 transition-all duration-300 cursor-pointer shadow-lg hover:shadow-2xl hover:shadow-amber-500/20 hover:-translate-y-1 aspect-[9/16] min-h-[420px] bg-black flex flex-col justify-between"
                                 @click="openPromoModal(v)">
                                <img :src="v.poster" :alt="v.title" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-black/20 group-hover:from-black/95 transition-all"></div>
                                
                                <div class="relative z-10 p-3.5 flex items-center justify-between">
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider backdrop-blur-md bg-black/75 border border-white/20 text-amber-300"
                                          x-text="v.badge"></span>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-black/80 text-white text-[10px] font-bold">
                                        <svg class="w-3 h-3 fill-current text-amber-400" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                        <span x-text="v.duration"></span>
                                    </span>
                                </div>

                                <div class="relative z-10 my-auto flex items-center justify-center">
                                    <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-md border border-white/60 flex items-center justify-center text-white shadow-xl transition-all duration-300 group-hover:scale-115 group-hover:bg-amber-500 group-hover:text-black">
                                        <svg class="w-5 h-5 fill-current ml-0.5" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                    </div>
                                </div>

                                <div class="relative z-10 p-4 space-y-1 bg-gradient-to-t from-black via-black/85 to-transparent">
                                    <h3 class="text-base font-black text-white leading-tight group-hover:text-amber-300 transition-colors" x-text="v.title"></h3>
                                    <p class="text-[11px] text-gray-300 font-medium leading-snug line-clamp-2" x-text="v.tagline"></p>
                                    <div class="pt-1 flex items-center justify-between text-[11px] font-bold text-amber-400">
                                        <span>Watch Reel ▶</span>
                                        <span class="text-[9px] text-gray-400">🔊 Sound On</span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Lightbox Modal -->
                <template x-teleport="body">
                    <div x-show="activeVideoPromo" 
                         style="display: none;"
                         class="fixed inset-0 z-[999999] flex items-center justify-center p-3 sm:p-6 bg-black/90 backdrop-blur-xl"
                         @click.self="closePromoModal()"
                         @keydown.escape.window="closePromoModal()">
                        <div class="relative bg-[#11141c] text-white rounded-3xl max-w-3xl w-full max-h-[92vh] shadow-2xl border border-white/20 flex flex-col md:flex-row overflow-hidden"
                             @click.stop>
                            <button type="button" 
                                    @click="closePromoModal()"
                                    class="absolute top-4 right-4 z-30 w-9 h-9 rounded-full bg-red-600/80 hover:bg-red-600 text-white flex items-center justify-center text-sm font-bold shadow-lg cursor-pointer">
                                ✕
                            </button>
                            <div class="md:w-[360px] shrink-0 bg-black flex items-center justify-center p-3">
                                <div class="relative w-full aspect-[9/16] max-h-[60vh] md:max-h-[78vh] rounded-2xl overflow-hidden bg-black">
                                    <video id="promoModalPlayer" 
                                           :src="activeVideoPromo?.videoSrc" 
                                           :poster="activeVideoPromo?.poster"
                                           controls 
                                           autoplay 
                                           playsinline 
                                           class="w-full h-full object-contain bg-black">
                                    </video>
                                </div>
                            </div>
                            <div class="flex-1 p-6 flex flex-col justify-between space-y-4">
                                <div>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-amber-500/20 text-amber-300 border border-amber-400/40"
                                          x-text="activeVideoPromo?.badge"></span>
                                    <h3 class="text-2xl font-black text-white mt-3" x-text="activeVideoPromo?.title"></h3>
                                    <p class="text-xs font-bold text-amber-400 mt-1" x-text="activeVideoPromo?.tagline"></p>
                                    <p class="text-xs text-gray-300 mt-3 leading-relaxed" x-text="activeVideoPromo?.desc"></p>
                                </div>
                                <div class="pt-4 border-t border-white/10">
                                    <a :href="activeVideoPromo?.ctaLink" 
                                       class="w-full inline-flex items-center justify-center gap-2 px-5 py-3.5 bg-amber-500 hover:bg-amber-600 text-gray-950 font-black text-sm rounded-xl shadow-lg transition">
                                        <span x-text="activeVideoPromo?.ctaText"></span>
                                        <span>→</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </section>

            <!-- How To Redeem Banner -->
            <div class="bg-gray-100 dark:bg-[#121212] rounded-3xl p-8 sm:p-12 border border-gray-200 dark:border-white/10">
                <div class="text-center max-w-2xl mx-auto mb-10">
                    <h2 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">How to Redeem a Promo Code</h2>
                    <p class="text-gray-700 dark:text-gray-300 text-sm font-medium mt-2">Apply your promo code in 3 simple steps on website or mobile app.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                    <div class="flex flex-col items-center">
                        <div class="w-14 h-14 rounded-2xl bg-amber-400 text-black font-black text-xl flex items-center justify-center mb-4 shadow-md">
                            1
                        </div>
                        <h4 class="text-base font-extrabold text-gray-900 dark:text-white mb-1">Copy Any Promo Code</h4>
                        <p class="text-xs text-gray-700 dark:text-gray-300 font-medium leading-relaxed">Click the "Copy" button on any active offer above or use your referral code.</p>
                    </div>
                    <div class="flex flex-col items-center">
                        <div class="w-14 h-14 rounded-2xl text-white font-black text-xl flex items-center justify-center mb-4 shadow-md"
                             style="background-color: #102b54 !important;">
                            2
                        </div>
                        <h4 class="text-base font-extrabold text-gray-900 dark:text-white mb-1">Choose Your Ride or Rental</h4>
                        <p class="text-xs text-gray-700 dark:text-gray-300 font-medium leading-relaxed">Select your pickup location, car rental period, or private driver package.</p>
                    </div>
                    <div class="flex flex-col items-center">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-500 text-white font-black text-xl flex items-center justify-center mb-4 shadow-md">
                            3
                        </div>
                        <h4 class="text-base font-extrabold text-gray-900 dark:text-white mb-1">Instant Discount Applied</h4>
                        <p class="text-xs text-gray-700 dark:text-gray-300 font-medium leading-relaxed">Paste your code before payment or confirm booking with reduced fare automatically.</p>
                    </div>
                </div>
            </div>

            <!-- Promotion FAQs -->
            <div class="max-w-3xl mx-auto space-y-4">
                <h2 class="text-2xl font-black text-gray-900 dark:text-white text-center mb-6">Frequently Asked Questions</h2>
                
                <div class="bg-white dark:bg-[#141414] rounded-2xl p-5 border border-gray-200 dark:border-white/10">
                    <h4 class="font-bold text-gray-900 dark:text-white text-sm mb-1">Can I combine multiple promotion codes?</h4>
                    <p class="text-xs text-gray-700 dark:text-gray-300 font-medium leading-relaxed">Only one promotional voucher can be applied per single ride, rental, or chauffeur hire booking. Referral wallet credits can be combined with any fare.</p>
                </div>
                <div class="bg-white dark:bg-[#141414] rounded-2xl p-5 border border-gray-200 dark:border-white/10">
                    <h4 class="font-bold text-gray-900 dark:text-white text-sm mb-1">When do the promotional codes expire?</h4>
                    <p class="text-xs text-gray-700 dark:text-gray-300 font-medium leading-relaxed">Active promo vouchers are refreshed monthly. Personal referral codes never expire as long as your account remains active.</p>
                </div>
                <div class="bg-white dark:bg-[#141414] rounded-2xl p-5 border border-gray-200 dark:border-white/10">
                    <h4 class="font-bold text-gray-900 dark:text-white text-sm mb-1">Do promotions work on both the website and mobile app?</h4>
                    <p class="text-xs text-gray-700 dark:text-gray-300 font-medium leading-relaxed">Yes! All promo codes and referral links work seamlessly across the RideMyCars web portal, iOS app, and Android app.</p>
                </div>
            </div>

        </main>
    </div>
</x-layout>
