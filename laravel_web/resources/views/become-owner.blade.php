<x-layout>
    <x-slot:title>Become a Vehicle Owner — Monetize Your Idle Car | RideMyCars</x-slot>

    <main class="flex-1 pb-24 overflow-hidden" x-data="{
        carCategory: 'suv',
        daysRented: 14,
        rates: {
            economy: { name: 'Economy Sedan', daily: 45, desc: 'Toyota Corolla, Honda Civic, Hyundai Elantra' },
            suv: { name: 'Compact / Midsize SUV', daily: 85, desc: 'Toyota RAV4, Honda CR-V, Hyundai Tucson' },
            luxury: { name: 'Executive Luxury Sedan', daily: 160, desc: 'Mercedes C/E-Class, BMW 3/5-Series, Audi A6' },
            premium_suv: { name: 'Premium SUV / Luxury Van', daily: 240, desc: 'Range Rover, Mercedes GLE/V-Class, BMW X5' }
        },
        get dailyRate() { return this.rates[this.carCategory].daily; },
        get monthlyEarnings() { return Math.round(this.dailyRate * this.daysRented * 0.85); },
        get annualEarnings() { return this.monthlyEarnings * 12; }
    }">

        <!-- 1. Hero Section -->
        <section class="relative pt-16 pb-20 lg:pt-24 lg:pb-28 overflow-hidden bg-gradient-to-b from-blue-500/5 via-white to-gray-50 dark:from-[#0a0a0a] dark:via-[#111] dark:to-[#0a0a0a]">
            <div class="absolute top-10 left-1/2 -translate-x-1/2 w-[700px] h-[350px] bg-blue-500/15 blur-[120px] rounded-full -z-10"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                    
                    <!-- Left Hero Copy -->
                    <div class="lg:col-span-7 text-center lg:text-left">
                        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-100 dark:bg-blue-950/60 border border-blue-300 dark:border-blue-800/40 text-blue-800 dark:text-blue-300 font-extrabold text-xs uppercase tracking-widest mb-6 shadow-sm">
                            <span>🔑</span> Vehicle Owner Host Marketplace
                        </div>

                        <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-gray-900 dark:text-white tracking-tight leading-[1.05] mb-6">
                            Your Car Earns <br>
                            <span class="text-blue-600 dark:text-blue-400 font-black">
                                While It Parks.
                            </span>
                        </h1>

                        <p class="text-lg sm:text-xl text-gray-600 dark:text-gray-300 leading-relaxed font-normal mb-8 max-w-xl">
                            The average private vehicle sits parked for 95% of the day. Turn your idle asset into a high-yield passive income stream with comprehensive $1M insurance coverage, verified renters, and 24/7 telematics.
                        </p>

                        <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 mb-8">
                            <a href="/owner-signup" class="w-full sm:w-auto inline-flex items-center justify-center gap-3 px-8 py-4 text-white font-black text-lg rounded-2xl transition-all shadow-xl shadow-blue-500/30 hover:scale-105 hover:shadow-2xl"
                               style="background: linear-gradient(135deg, #2563eb 0%, #4f46e5 50%, #1d4ed8 100%) !important; color: #ffffff !important;">
                                List Your Vehicle Now
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                            </a>
                            <a href="#estimator" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 bg-white dark:bg-white/10 hover:bg-gray-100 dark:hover:bg-white/20 text-gray-900 dark:text-white font-bold text-lg rounded-2xl border-2 border-gray-200 dark:border-white/15 transition-all">
                                Estimate Monthly Yield
                            </a>
                        </div>

                        <div class="flex flex-wrap items-center justify-center lg:justify-start gap-6 text-xs text-gray-600 dark:text-gray-400 font-bold">
                            <span class="flex items-center gap-1.5"><span class="text-blue-500">✓</span> $1,000,000 Insurance Policy</span>
                            <span class="flex items-center gap-1.5"><span class="text-blue-500">✓</span> 100% ID-Screened Renters</span>
                            <span class="flex items-center gap-1.5"><span class="text-blue-500">✓</span> You Control Pricing & Dates</span>
                        </div>
                    </div>

                    <!-- Right Hero: Switcher between Official Fleet Flyer and Host Photo -->
                    <div class="lg:col-span-5 relative" x-data="{ viewMode: 'flyer', showFleetModal: false }">
                        <!-- Switcher Pills -->
                        <div class="flex items-center justify-center lg:justify-end gap-2 mb-3">
                            <button type="button" 
                                    @click="viewMode = 'flyer'"
                                    :class="viewMode === 'flyer' ? 'bg-blue-600 text-white font-black shadow-md' : 'bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 font-bold'"
                                    class="px-4 py-1.5 rounded-full text-xs transition-all flex items-center gap-1.5 cursor-pointer">
                                <span>📄</span> Official Fleet Flyer
                            </button>
                            <button type="button" 
                                    @click="viewMode = 'photo'"
                                    :class="viewMode === 'photo' ? 'bg-blue-600 text-white font-black shadow-md' : 'bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 font-bold'"
                                    class="px-4 py-1.5 rounded-full text-xs transition-all flex items-center gap-1.5 cursor-pointer">
                                <span>👤</span> Verified Host
                            </button>
                        </div>

                        <!-- Flyer View -->
                        <div x-show="viewMode === 'flyer'" class="relative rounded-3xl overflow-hidden shadow-2xl border-2 border-blue-400 dark:border-blue-500/40 group bg-gradient-to-b from-gray-50 via-gray-100/50 to-gray-50 dark:from-[#1a1a1a] dark:to-[#121212] p-3 sm:p-4 flex flex-col items-center">
                            <div class="relative w-full cursor-pointer overflow-hidden rounded-2xl" @click="showFleetModal = true">
                                <img src="{{ asset('images/promo-fleet-owner.jpg') }}" alt="Own Cars? Turn Them Into Monthly Income - Official Fleet Flyer" class="w-full h-auto max-h-[480px] sm:max-h-[520px] object-contain rounded-2xl shadow-md transition-transform duration-700 group-hover:scale-[1.01]">
                                <div class="absolute inset-0 bg-black/10 group-hover:bg-transparent transition-colors flex items-center justify-center">
                                    <span class="px-3 py-1.5 rounded-full bg-black/75 backdrop-blur-sm text-white font-bold text-xs opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1 shadow-lg">
                                        🔍 Click to Enlarge
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Clean Info Bar Below Flyer -->
                            <div class="w-full mt-3 p-3.5 rounded-2xl bg-white dark:bg-[#181818] border border-gray-200 dark:border-white/10 shadow-md flex items-center justify-between">
                                <div>
                                    <div class="text-xs font-black text-blue-600 dark:text-blue-400">Own Cars? Turn Into Monthly Income</div>
                                    <div class="text-[11px] text-gray-600 dark:text-gray-400 font-semibold">Weekly Bank + Instant MoMo Cash Outs</div>
                                </div>
                                <a href="/owner-signup" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-black rounded-xl shadow-xs transition">
                                    List Fleet →
                                </a>
                            </div>
                        </div>

                        <!-- Photo View -->
                        <div x-show="viewMode === 'photo'" style="display: none;" class="relative rounded-3xl overflow-hidden shadow-2xl border-2 border-blue-200 dark:border-blue-500/30 group">
                            <img src="{{ asset('images/owner-hero.jpg') }}" alt="Proud RideMyCars Fleet Host" class="w-full h-[450px] sm:h-[520px] object-cover transition-transform duration-700 group-hover:scale-[1.02]">
                            
                            <!-- Floating Glass Badge -->
                            <div class="absolute bottom-6 left-6 right-6 p-5 rounded-2xl bg-white/95 dark:bg-[#141414]/95 backdrop-blur-md border border-gray-200 dark:border-white/10 shadow-xl flex items-center justify-between">
                                <div>
                                    <div class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-0.5">Top Fleet Host</div>
                                    <div class="text-xs font-bold text-gray-800 dark:text-gray-200">Sarah M. • 2 Vehicles Listed</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xl font-black text-blue-600 dark:text-blue-400">$2,450 / mo</div>
                                    <div class="text-[10px] uppercase font-bold text-gray-400">Average Net Payout</div>
                                </div>
                            </div>
                        </div>

                        <!-- Lightbox Modal for Official Fleet Flyer -->
                        <template x-teleport="body">
                            <div x-show="showFleetModal" 
                                 style="display: none;"
                                 class="fixed inset-0 z-[999999] flex items-center justify-center p-4 bg-black/85 backdrop-blur-md"
                                 @click.self="showFleetModal = false"
                                 @keydown.escape.window="showFleetModal = false">
                                <div class="relative max-w-xl w-full bg-white dark:bg-[#181818] rounded-3xl p-5 shadow-2xl border border-blue-400 flex flex-col items-center">
                                    <button type="button" 
                                            @click="showFleetModal = false"
                                            class="absolute -top-3 -right-3 w-9 h-9 rounded-full bg-black text-white flex items-center justify-center text-sm font-bold shadow-lg">
                                        ✕
                                    </button>
                                    <img src="{{ asset('images/promo-fleet-owner.jpg') }}" 
                                         alt="Own Cars? Turn Them Into Monthly Income Flyer" 
                                         class="w-full max-h-[75vh] rounded-2xl object-contain shadow-md mb-4">
                                    <div class="w-full flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-gray-100 dark:border-white/10">
                                        <div>
                                            <h4 class="text-sm font-black text-gray-900 dark:text-white">List Your Fleet on RideMyCars</h4>
                                            <p class="text-xs text-blue-600 dark:text-blue-400 font-semibold">Safe • Reliable • Profitable</p>
                                        </div>
                                        <a href="/owner-signup" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-black text-xs rounded-xl transition shadow-md">
                                            List Fleet Now →
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                </div>
            </div>
        </section>

        <!-- 2. Interactive Host Revenue Estimator -->
        <section id="estimator" class="py-24 bg-white dark:bg-[#111]">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <span class="px-4 py-1.5 rounded-full bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 font-extrabold text-xs uppercase tracking-wider border border-blue-300 dark:border-blue-800/40 inline-block mb-3">
                        Yield Calculator
                    </span>
                    <h2 class="text-4xl sm:text-5xl font-black text-gray-900 dark:text-white tracking-tight mb-4">
                        Estimate Your Vehicle's Earnings
                    </h2>
                    <p class="text-base sm:text-lg text-gray-600 dark:text-gray-300 font-normal">
                        Select your vehicle tier and how many days you want to rent it out each month to see projected passive cash flow.
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
                    <!-- Controls Column -->
                    <div class="lg:col-span-6 bg-gray-50 dark:bg-[#161616] p-8 sm:p-10 rounded-3xl border-2 border-gray-200 dark:border-white/10 shadow-lg flex flex-col justify-between">
                        <div class="space-y-8">
                            <!-- Category Buttons -->
                            <div>
                                <label class="font-extrabold text-gray-900 dark:text-white text-base block mb-3">Select Vehicle Class</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <button type="button" @click="carCategory = 'economy'" :class="carCategory === 'economy' ? 'border-blue-600 bg-blue-50 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300' : 'border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-gray-700 dark:text-gray-300'" class="p-3.5 rounded-2xl border-2 text-left transition-all">
                                        <div class="text-xs font-black uppercase">Economy Sedan</div>
                                        <div class="text-sm font-extrabold mt-0.5">$45 / day avg</div>
                                    </button>
                                    <button type="button" @click="carCategory = 'suv'" :class="carCategory === 'suv' ? 'border-blue-600 bg-blue-50 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300' : 'border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-gray-700 dark:text-gray-300'" class="p-3.5 rounded-2xl border-2 text-left transition-all">
                                        <div class="text-xs font-black uppercase">Compact / SUV</div>
                                        <div class="text-sm font-extrabold mt-0.5">$85 / day avg</div>
                                    </button>
                                    <button type="button" @click="carCategory = 'luxury'" :class="carCategory === 'luxury' ? 'border-blue-600 bg-blue-50 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300' : 'border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-gray-700 dark:text-gray-300'" class="p-3.5 rounded-2xl border-2 text-left transition-all">
                                        <div class="text-xs font-black uppercase">Executive Luxury</div>
                                        <div class="text-sm font-extrabold mt-0.5">$160 / day avg</div>
                                    </button>
                                    <button type="button" @click="carCategory = 'premium_suv'" :class="carCategory === 'premium_suv' ? 'border-blue-600 bg-blue-50 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300' : 'border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-gray-700 dark:text-gray-300'" class="p-3.5 rounded-2xl border-2 text-left transition-all">
                                        <div class="text-xs font-black uppercase">Premium SUV / Van</div>
                                        <div class="text-sm font-extrabold mt-0.5">$240 / day avg</div>
                                    </button>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-2 font-medium" x-text="'Typical models: ' + rates[carCategory].desc"></div>
                            </div>

                            <!-- Rental Days Slider -->
                            <div>
                                <div class="flex justify-between items-center mb-3">
                                    <label class="font-extrabold text-gray-900 dark:text-white text-base">Days Rented Per Month</label>
                                    <span class="text-2xl font-black text-blue-600 dark:text-blue-400" x-text="daysRented + ' Days'"></span>
                                </div>
                                <input type="range" min="5" max="28" step="1" x-model="daysRented" class="w-full h-3 bg-gray-200 dark:bg-white/10 rounded-lg appearance-none cursor-pointer accent-blue-600">
                                <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-2 font-bold">
                                    <span>5 days (Weekends)</span>
                                    <span>14 days (Half-month)</span>
                                    <span>28 days (Full availability)</span>
                                </div>
                            </div>

                            <div class="p-4 rounded-2xl bg-blue-500/10 border border-blue-500/30 text-xs text-blue-800 dark:text-blue-300 font-medium">
                                🛡️ <strong>Zero Risk Guarantee:</strong> All rental hours are insured up to $1,000,000 for physical damage, theft, and third-party liability with verified renters.
                            </div>
                        </div>

                        <div class="mt-8 pt-6 border-t border-gray-200 dark:border-white/10 flex items-center justify-between text-xs text-gray-500">
                            <span>Host Payout Cycle:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200">Disbursed Within 24h of Trip End</span>
                        </div>
                    </div>

                    <!-- Output Column -->
                    <div class="lg:col-span-6 bg-gradient-to-br from-blue-500/10 via-indigo-500/10 to-blue-500/5 dark:from-[#131b26] dark:via-[#161d2b] dark:to-[#161616] p-8 sm:p-10 rounded-3xl border-2 border-blue-500 shadow-2xl flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-200 dark:border-white/10">
                                <span class="font-black text-lg text-gray-900 dark:text-white">Host Net Payout Projection</span>
                                <span class="px-3.5 py-1 bg-blue-600 text-white font-extrabold text-xs rounded-full">Host Keeps 85%</span>
                            </div>

                            <div class="space-y-4 mb-8">
                                <div class="flex justify-between items-center p-3.5 rounded-xl bg-white dark:bg-white/5 shadow-sm">
                                    <span class="text-sm font-semibold text-gray-600 dark:text-gray-300">Vehicle Tier:</span>
                                    <span class="text-base font-black text-gray-900 dark:text-white" x-text="rates[carCategory].name"></span>
                                </div>
                                <div class="flex justify-between items-center p-3.5 rounded-xl bg-white dark:bg-white/5 shadow-sm">
                                    <span class="text-sm font-semibold text-gray-600 dark:text-gray-300">Gross Monthly Booking Value:</span>
                                    <span class="text-base font-extrabold text-gray-800 dark:text-gray-200" x-text="'$' + (dailyRate * daysRented)"></span>
                                </div>
                                <div class="flex justify-between items-center p-3.5 rounded-xl bg-blue-50 dark:bg-blue-950/20 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/40">
                                    <span class="text-sm font-semibold">Platform Fee (15% covers $1M Insurance):</span>
                                    <span class="text-base font-extrabold" x-text="'-$' + Math.round(dailyRate * daysRented * 0.15)"></span>
                                </div>
                            </div>

                            <!-- Big Profit Highlight Box -->
                            <div class="p-6 rounded-2xl text-white shadow-xl mb-6 relative overflow-hidden"
                                 style="background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%) !important; color: #ffffff !important;">
                                <div class="text-xs uppercase font-extrabold tracking-widest mb-1" style="color: #dbeafe !important;">Estimated Net Host Profit</div>
                                <div class="text-4xl sm:text-5xl font-black tracking-tight mb-2 text-white" style="color: #ffffff !important;" x-text="'$' + monthlyEarnings + ' / month'"></div>
                                <p class="text-xs font-medium" style="color: #eff6ff !important;">
                                    That equals <strong class="font-extrabold" style="color: #ffffff !important;" x-text="'$' + annualEarnings.toLocaleString() + ' / year'"></strong> in recurring passive cash flow from a single vehicle.
                                </p>
                            </div>
                        </div>

                        <a href="/owner-signup" class="w-full inline-flex items-center justify-center gap-2 py-4 px-6 rounded-2xl bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-black text-base hover:opacity-90 transition-all shadow-lg">
                            List Your Car & Start Earning →
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3. Host Options: Self-Managed vs Concierge Managed -->
        <section class="py-24 bg-gray-50 dark:bg-[#161616] border-y border-gray-100 dark:border-white/10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <span class="px-4 py-1.5 rounded-full bg-blue-100 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 font-extrabold text-xs uppercase tracking-wider border border-blue-200 dark:border-blue-800/40 inline-block mb-3">
                        Flexible Hosting
                    </span>
                    <h2 class="text-4xl sm:text-5xl font-black text-gray-900 dark:text-white tracking-tight">
                        Two Ways to Host on RideMyCars
                    </h2>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Option 1: Self-Managed -->
                    <div class="bg-white dark:bg-[#111] rounded-3xl p-8 sm:p-10 border-2 border-gray-200 dark:border-white/10 shadow-sm flex flex-col justify-between">
                        <div>
                            <span class="px-3.5 py-1.5 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 font-bold text-xs uppercase tracking-wider inline-block mb-4">
                                Maximum Control
                            </span>
                            <h3 class="text-3xl font-black text-gray-900 dark:text-white mb-3">Self-Managed Host</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed font-normal mb-6">
                                You store your vehicle at your residence or business, handle in-person or lockbox key handoffs, and keep the highest net return.
                            </p>

                            <ul class="space-y-3 text-xs sm:text-sm text-gray-700 dark:text-gray-300 font-medium">
                                <li class="flex items-center gap-2.5">
                                    <span class="text-blue-500 font-black">✓</span>
                                    <span>Keep 85% of total gross rental booking revenue</span>
                                </li>
                                <li class="flex items-center gap-2.5">
                                    <span class="text-blue-500 font-black">✓</span>
                                    <span>You approve or decline each rental reservation</span>
                                </li>
                                <li class="flex items-center gap-2.5">
                                    <span class="text-blue-500 font-black">✓</span>
                                    <span>Set your own custom daily and weekend pricing</span>
                                </li>
                                <li class="flex items-center gap-2.5">
                                    <span class="text-blue-500 font-black">✓</span>
                                    <span>Block out dates whenever you need personal car use</span>
                                </li>
                            </ul>
                        </div>
                        <div class="mt-8 pt-6 border-t border-gray-100 dark:border-white/5">
                            <a href="/owner-signup?mode=self" class="w-full inline-flex items-center justify-center py-3.5 px-6 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm transition-all shadow-md">
                                Choose Self-Managed Hosting
                            </a>
                        </div>
                    </div>

                    <!-- Option 2: Concierge Managed -->
                    <div class="bg-gradient-to-br from-indigo-500/10 via-purple-500/5 to-blue-500/10 dark:from-[#171524] dark:via-[#131b26] dark:to-[#111] rounded-3xl p-8 sm:p-10 border-2 border-indigo-500 shadow-xl flex flex-col justify-between relative overflow-hidden">
                        <div class="absolute top-0 right-0 bg-indigo-600 text-white font-extrabold text-xs uppercase tracking-widest px-4 py-2 rounded-bl-2xl">
                            100% Hands-Free
                        </div>
                        <div>
                            <span class="px-3.5 py-1.5 rounded-xl bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 font-bold text-xs uppercase tracking-wider inline-block mb-4">
                                Passive Wealth
                            </span>
                            <h3 class="text-3xl font-black text-gray-900 dark:text-white mb-3">Concierge Fleet Management</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed font-normal mb-6">
                                Deliver your car to our secure regional hub. We detail it, inspect it, hand off keys to verified renters, manage maintenance, and deposit net profits to you.
                            </p>

                            <ul class="space-y-3 text-xs sm:text-sm text-gray-700 dark:text-gray-300 font-medium">
                                <li class="flex items-center gap-2.5">
                                    <span class="text-indigo-500 font-black">✓</span>
                                    <span>Zero time required — 100% turnkey passive income</span>
                                </li>
                                <li class="flex items-center gap-2.5">
                                    <span class="text-indigo-500 font-black">✓</span>
                                    <span>Stored in 24/7 monitored, gated executive facility</span>
                                </li>
                                <li class="flex items-center gap-2.5">
                                    <span class="text-indigo-500 font-black">✓</span>
                                    <span>Complimentary car washes and pre-trip mechanical checks</span>
                                </li>
                                <li class="flex items-center gap-2.5">
                                    <span class="text-indigo-500 font-black">✓</span>
                                    <span>Dedicated account manager for multi-car fleet owners</span>
                                </li>
                            </ul>
                        </div>
                        <div class="mt-8 pt-6 border-t border-indigo-200 dark:border-white/10">
                            <a href="/owner-signup?mode=concierge" class="w-full inline-flex items-center justify-center py-3.5 px-6 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm transition-all shadow-md">
                                Inquire About Concierge Fleet
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Official Fleet Partner Guarantees Showcase (Directly from Campaign Flyer) -->
        <section class="py-24 bg-gradient-to-b from-blue-50/50 via-white to-gray-50 dark:from-[#0d131d] dark:via-[#111] dark:to-[#0f0f0f] border-b border-gray-100 dark:border-white/10 relative overflow-hidden" x-data="{ showFleetSectionModal: false }">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <span class="px-4 py-1.5 rounded-full bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 font-extrabold text-xs uppercase tracking-wider border border-blue-300 dark:border-blue-800/40 inline-block mb-3">
                        ⭐ Official Fleet Campaign
                    </span>
                    <h2 class="text-3xl sm:text-5xl font-black text-gray-950 dark:text-white tracking-tight">
                        Own Cars? <span class="text-blue-600 dark:text-blue-400">Turn Them Into Monthly Income</span>
                    </h2>
                    <p class="text-base text-gray-600 dark:text-gray-300 font-medium mt-3 max-w-2xl mx-auto">
                        List your single vehicle or full fleet on RideMyCars. Built to deliver maximum vehicle utilization with complete host peace of mind.
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                    <!-- Left: Official Flyer Showcase Card -->
                    <div class="lg:col-span-5 flex justify-center">
                        <div class="p-3 sm:p-4 rounded-3xl bg-white dark:bg-[#181818] border-2 border-blue-400 dark:border-blue-500/40 shadow-2xl max-w-md w-full group cursor-pointer"
                             @click="showFleetSectionModal = true">
                            <div class="relative rounded-2xl overflow-hidden shadow-lg bg-gray-950">
                                <img src="{{ asset('images/promo-fleet-owner.jpg') }}" 
                                     alt="Own Cars? Turn Them Into Monthly Income - Official Campaign Flyer" 
                                     class="w-full h-auto object-contain group-hover:scale-[1.02] transition-transform duration-500">
                                <div class="absolute inset-0 bg-black/20 group-hover:bg-transparent transition-colors flex items-center justify-center">
                                    <span class="px-3.5 py-1.5 rounded-full bg-black/80 backdrop-blur-sm text-white font-bold text-xs opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1.5 shadow-lg">
                                        🔍 Click to Enlarge Flyer
                                    </span>
                                </div>
                            </div>
                            <div class="mt-4 px-2 flex items-center justify-between text-xs">
                                <div>
                                    <span class="font-black text-blue-600 dark:text-blue-400 block">Official Fleet Host Poster</span>
                                    <span class="text-[11px] text-gray-500 dark:text-gray-400">Safe • Reliable • Profitable</span>
                                </div>
                                <span class="px-2.5 py-1 rounded-lg bg-blue-50 dark:bg-blue-950 text-blue-700 dark:text-blue-300 font-bold text-[11px] border border-blue-200 dark:border-blue-800">
                                    ridemycars.com
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: The 4 Core Pillars from Flyer -->
                    <div class="lg:col-span-7 space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            
                            <!-- Pillar 1: Earn From Every Trip -->
                            <div class="p-5 rounded-2xl bg-white dark:bg-[#161616] border-2 border-gray-100 dark:border-white/10 hover:border-blue-400 dark:hover:border-blue-500/40 shadow-sm transition-all">
                                <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 flex items-center justify-center mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>
                                </div>
                                <h3 class="text-base font-black text-gray-950 dark:text-white mb-1">Earn From Every Trip</h3>
                                <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed font-medium">
                                    Maximize revenue on every completed ride with daily trip accruals and detailed booking breakdowns on your portal.
                                </p>
                            </div>

                            <!-- Pillar 2: We Provide Verified Drivers -->
                            <div class="p-5 rounded-2xl bg-white dark:bg-[#161616] border-2 border-gray-100 dark:border-white/10 hover:border-blue-400 dark:hover:border-blue-500/40 shadow-sm transition-all">
                                <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 flex items-center justify-center mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
                                </div>
                                <h3 class="text-base font-black text-gray-950 dark:text-white mb-1">We Provide Verified Drivers</h3>
                                <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed font-medium">
                                    Thoroughly vetted, background-checked, and driving-record approved drivers assigned directly to keep your fleet active.
                                </p>
                            </div>

                            <!-- Pillar 3: Real-Time Fleet Tracking -->
                            <div class="p-5 rounded-2xl bg-white dark:bg-[#161616] border-2 border-gray-100 dark:border-white/10 hover:border-blue-400 dark:hover:border-blue-500/40 shadow-sm transition-all">
                                <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-400 flex items-center justify-center mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                                </div>
                                <h3 class="text-base font-black text-gray-950 dark:text-white mb-1">Real-Time Fleet Tracking</h3>
                                <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed font-medium">
                                    Monitor your cars 24/7 via dashboard & mobile app with turn-by-turn live GPS telemetry, geofencing, and trip logs.
                                </p>
                            </div>

                            <!-- Pillar 4: Weekly Bank Transfer + Instant MoMo -->
                            <div class="p-5 rounded-2xl bg-white dark:bg-[#161616] border-2 border-gray-100 dark:border-white/10 hover:border-blue-400 dark:hover:border-blue-500/40 shadow-sm transition-all">
                                <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-400 flex items-center justify-center mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                                </div>
                                <h3 class="text-base font-black text-gray-950 dark:text-white mb-1">Weekly Bank + Instant MoMo</h3>
                                <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed font-medium">
                                    Get paid weekly directly to your bank account or cash out your net earnings instantly to Mobile Money (MTN / Vodafone) 24/7.
                                </p>
                            </div>

                        </div>

                        <!-- Action Bar -->
                        <div class="p-5 rounded-2xl bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800/40 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="text-left">
                                <div class="text-xs font-black text-blue-900 dark:text-blue-300">Ready to activate your vehicle?</div>
                                <div class="text-[11px] text-gray-600 dark:text-gray-400">List in 2 minutes or contact our fleet coordinator</div>
                            </div>
                            <div class="flex items-center gap-2.5 w-full sm:w-auto">
                                <button type="button" 
                                        @click="showFleetSectionModal = true"
                                        class="px-4 py-2.5 bg-white dark:bg-white/10 text-gray-900 dark:text-white font-bold text-xs rounded-xl border border-gray-200 dark:border-white/10 transition cursor-pointer">
                                    🔍 Enlarge Flyer
                                </button>
                                <a href="/owner-signup" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-black text-xs rounded-xl shadow-md transition">
                                    List Your Fleet Now →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lightbox Modal -->
                <template x-teleport="body">
                    <div x-show="showFleetSectionModal" 
                         style="display: none;"
                         class="fixed inset-0 z-[999999] flex items-center justify-center p-4 bg-black/85 backdrop-blur-md"
                         @click.self="showFleetSectionModal = false"
                         @keydown.escape.window="showFleetSectionModal = false">
                        <div class="relative max-w-xl w-full bg-white dark:bg-[#181818] rounded-3xl p-5 shadow-2xl border border-blue-400 flex flex-col items-center">
                            <button type="button" 
                                    @click="showFleetSectionModal = false"
                                    class="absolute -top-3 -right-3 w-9 h-9 rounded-full bg-black text-white flex items-center justify-center text-sm font-bold shadow-lg">
                                ✕
                            </button>
                            <img src="{{ asset('images/promo-fleet-owner.jpg') }}" 
                                 alt="Own Cars? Turn Them Into Monthly Income Flyer" 
                                 class="w-full max-h-[75vh] rounded-2xl object-contain shadow-md mb-4">
                            <div class="w-full flex items-center justify-between gap-4 pt-2 border-t border-gray-100 dark:border-white/10">
                                <div>
                                    <h4 class="text-sm font-black text-gray-900 dark:text-white">Turn Cars Into Monthly Income</h4>
                                    <span class="text-xs text-blue-600 dark:text-blue-400 font-bold">List Your Fleet on RideMyCars</span>
                                </div>
                                <a href="/owner-signup" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-black text-xs rounded-xl shadow-md transition">
                                    List Fleet Now →
                                </a>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </section>

        <!-- 4. Four-Step Listing Roadmap -->
        <section class="py-24 bg-white dark:bg-[#111]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <span class="px-4 py-1.5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-extrabold text-xs uppercase tracking-wider border border-emerald-200 dark:border-emerald-800/40 inline-block mb-3">
                        Simple Process
                    </span>
                    <h2 class="text-4xl sm:text-5xl font-black text-gray-900 dark:text-white tracking-tight">
                        How to List Your Car in 4 Steps
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="p-8 rounded-3xl bg-gray-50 dark:bg-[#161616] border-2 border-gray-200 dark:border-white/10 text-center shadow-sm">
                        <span class="text-4xl font-black text-blue-500 block mb-4">01</span>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white mb-2">Create Your Listing</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400 font-medium leading-relaxed">Submit vehicle VIN, high-res photos, features, and your preferred daily rental rate.</p>
                    </div>

                    <div class="p-8 rounded-3xl bg-gray-50 dark:bg-[#161616] border-2 border-gray-200 dark:border-white/10 text-center shadow-sm">
                        <span class="text-4xl font-black text-indigo-500 block mb-4">02</span>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white mb-2">24h Verification</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400 font-medium leading-relaxed">Our trust team validates title ownership, active registration, and roadworthiness pass.</p>
                    </div>

                    <div class="p-8 rounded-3xl bg-gray-50 dark:bg-[#161616] border-2 border-gray-200 dark:border-white/10 text-center shadow-sm">
                        <span class="text-4xl font-black text-emerald-500 block mb-4">03</span>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white mb-2">Accept Bookings</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400 font-medium leading-relaxed">Receive reservation requests from ID-screened renters with pre-authorized damage deposits.</p>
                    </div>

                    <div class="p-8 rounded-3xl bg-gray-50 dark:bg-[#161616] border-2 border-gray-200 dark:border-white/10 text-center shadow-sm">
                        <span class="text-4xl font-black text-purple-500 block mb-4">04</span>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white mb-2">Get Paid Fast</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400 font-medium leading-relaxed">Payouts are deposited directly to your bank or mobile wallet within 24 hours of trip end.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 5. Owner FAQs -->
        <section class="py-24 bg-gray-50 dark:bg-[#161616] border-t border-gray-100 dark:border-white/10">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <span class="px-4 py-1.5 rounded-full bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 font-extrabold text-xs uppercase tracking-wider border border-blue-200 dark:border-blue-800/40 inline-block mb-3">
                        Owner FAQs
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-black text-gray-900 dark:text-white tracking-tight">
                        Frequently Asked Questions by Vehicle Hosts
                    </h2>
                </div>

                <div class="space-y-4" x-data="{ openFaq: null }">
                    <div class="p-6 rounded-2xl bg-white dark:bg-[#141414] border-2 border-gray-200 dark:border-white/10 cursor-pointer" @click="openFaq = (openFaq === 1 ? null : 1)">
                        <div class="flex justify-between items-center">
                            <h4 class="font-extrabold text-base text-gray-900 dark:text-white">What happens if a renter damages my car?</h4>
                            <span class="text-xl font-bold" x-text="openFaq === 1 ? '−' : '+'"></span>
                        </div>
                        <div x-show="openFaq === 1" class="mt-3 text-xs sm:text-sm text-gray-600 dark:text-gray-400 leading-relaxed font-normal">
                            All bookings are protected under our platform's $1,000,000 comprehensive liability and collision policy. Renters undergo digital check-in photo audits before taking the vehicle. If physical damage occurs, our claims adjusters triage the repair immediately and handle reimbursement from the security deposit and insurer.
                        </div>
                    </div>

                    <div class="p-6 rounded-2xl bg-white dark:bg-[#141414] border-2 border-gray-200 dark:border-white/10 cursor-pointer" @click="openFaq = (openFaq === 2 ? null : 2)">
                        <div class="flex justify-between items-center">
                            <h4 class="font-extrabold text-base text-gray-900 dark:text-white">Who is allowed to drive my car?</h4>
                            <span class="text-xl font-bold" x-text="openFaq === 2 ? '−' : '+'"></span>
                        </div>
                        <div x-show="openFaq === 2" class="mt-3 text-xs sm:text-sm text-gray-600 dark:text-gray-400 leading-relaxed font-normal">
                            Only the verified primary renter who completed ID biometric screening, holds a valid unrestricted driver's license, and was approved for the booking is permitted to operate the vehicle. Unauthorized secondary drivers invalidate coverage and constitute breach of contract.
                        </div>
                    </div>

                    <div class="p-6 rounded-2xl bg-white dark:bg-[#141414] border-2 border-gray-200 dark:border-white/10 cursor-pointer" @click="openFaq = (openFaq === 3 ? null : 3)">
                        <div class="flex justify-between items-center">
                            <h4 class="font-extrabold text-base text-gray-900 dark:text-white">Can I still use my vehicle for personal errands?</h4>
                            <span class="text-xl font-bold" x-text="openFaq === 3 ? '−' : '+'"></span>
                        </div>
                        <div x-show="openFaq === 3" class="mt-3 text-xs sm:text-sm text-gray-600 dark:text-gray-400 leading-relaxed font-normal">
                            Absolutely. Through the host portal calendar, you can block out hours, weekends, or entire months whenever you wish to use your car. You only accept bookings when your vehicle is genuinely free.
                        </div>
                    </div>

                    <div class="p-6 rounded-2xl bg-white dark:bg-[#141414] border-2 border-gray-200 dark:border-white/10 cursor-pointer" @click="openFaq = (openFaq === 4 ? null : 4)">
                        <div class="flex justify-between items-center">
                            <h4 class="font-extrabold text-base text-gray-900 dark:text-white">How do mileage and fuel policies work?</h4>
                            <span class="text-xl font-bold" x-text="openFaq === 4 ? '−' : '+'"></span>
                        </div>
                        <div x-show="openFaq === 4" class="mt-3 text-xs sm:text-sm text-gray-600 dark:text-gray-400 leading-relaxed font-normal">
                            You set the daily mileage limit (standard is 150-200 km/day). Excess kilometers are billed automatically at your preset rate (e.g. $0.35/km). Cars must be returned with the same fuel level as dispatched or the difference is deducted from the renter's deposit.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 6. Strong CTA Section -->
        <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-[2.5rem] p-8 sm:p-14 text-center text-white shadow-2xl relative overflow-hidden"
                 style="background: linear-gradient(135deg, #1d4ed8 0%, #4f46e5 50%, #2563eb 100%) !important; color: #ffffff !important;">
                <div class="relative z-10">
                    <span class="px-4 py-1.5 rounded-full bg-white/20 text-white font-extrabold text-xs uppercase tracking-widest inline-block mb-4">
                        Unlock High-Yield Passive Cash Flow
                    </span>
                    <h2 class="text-3xl sm:text-5xl font-black mb-4 tracking-tight">Ready to Put Your Vehicle to Work?</h2>
                    <p class="text-white/90 text-sm sm:text-lg max-w-xl mx-auto mb-8 font-medium">
                        Join over 1,400 verified vehicle hosts earning thousands of dollars monthly under complete $1,000,000 insurance protection.
                    </p>
                    <div class="flex flex-wrap items-center justify-center gap-4">
                        <a href="/owner-signup" class="px-9 py-4 rounded-2xl bg-white text-blue-700 font-black text-lg shadow-xl hover:bg-gray-50 transition-all hover:scale-105">
                            List Your Car (Free)
                        </a>
                        <a href="/rent" class="px-8 py-4 rounded-2xl bg-gray-950 text-white font-bold text-base shadow-xl hover:bg-black transition-all hover:scale-105">
                            Browse Rented Vehicles
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </main>
</x-layout>
