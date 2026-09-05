<x-layout>
    <x-slot:title>Become a Driver — 90% Belongs to You | RideMyCars</x-slot>

    <main class="flex-1 pb-24 overflow-hidden" x-data="{
        dailyFares: 500,
        daysPerMonth: 26,
        currencySymbol: 'GH₵',
        get dailyTakeHome() { return Math.round(this.dailyFares * 0.90); },
        get dailyCommission() { return Math.round(this.dailyFares * 0.10); },
        get competitorCommission() { return Math.round(this.dailyFares * 0.25); },
        get dailyExtra() { return Math.round(this.dailyFares * 0.15); },
        get weeklyExtra() { return Math.round(this.dailyExtra * 6); },
        get monthlyExtra() { return Math.round(this.dailyExtra * this.daysPerMonth); },
        get monthlyTakeHome() { return Math.round(this.dailyTakeHome * this.daysPerMonth); }
    }">

        <!-- 1. Main Driver Hero Section -->
        <section class="relative pt-16 pb-20 lg:pt-24 lg:pb-28 overflow-hidden bg-gradient-to-b from-amber-500/5 via-white to-gray-50 dark:from-[#0a0a0a] dark:via-[#111] dark:to-[#0a0a0a]">
            <div class="absolute top-10 left-1/2 -translate-x-1/2 w-[700px] h-[350px] bg-amber-500/15 blur-[120px] rounded-full -z-10"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                    
                    <!-- Left Hero Copy -->
                    <div class="lg:col-span-7 text-center lg:text-left">
                        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-amber-100 dark:bg-amber-950/60 border border-amber-300 dark:border-amber-800/40 text-amber-800 dark:text-amber-300 font-extrabold text-xs uppercase tracking-widest mb-6 shadow-sm">
                            <span class="animate-pulse">⚡</span> Industry-Leading 10% Commission Model
                        </div>

                        <h1 class="text-5xl sm:text-7xl lg:text-8xl font-black text-gray-900 dark:text-white tracking-tight leading-[0.98] mb-6">
                            <span class="text-orange-600 dark:text-orange-400 font-black">
                                90%
                            </span><br>
                            BELONGS TO YOU
                        </h1>

                        <div class="text-lg sm:text-2xl text-gray-700 dark:text-gray-200 font-bold leading-relaxed mb-6 space-y-1">
                            <p class="text-gray-600 dark:text-gray-400 font-normal">You bought the car. You pay for the fuel.</p>
                            <p class="text-amber-600 dark:text-amber-400 font-extrabold">You keep 90% of what you earn on every single trip.</p>
                        </div>

                        <p class="text-base sm:text-lg text-gray-600 dark:text-gray-400 max-w-xl mb-8 leading-relaxed font-normal">
                            Why give away 25% or more to foreign ride-hailing conglomerates? Keep an extra <strong class="text-gray-900 dark:text-white font-extrabold">GH₵1,950+ every month</strong> directly in your pocket.
                        </p>

                        <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 mb-6">
                            <a href="/driver-signup" class="w-full sm:w-auto inline-flex items-center justify-center gap-3 px-8 py-4 text-white font-black text-lg rounded-2xl transition-all shadow-xl shadow-amber-500/30 hover:scale-105 hover:shadow-2xl"
                               style="background: linear-gradient(135deg, #f97316 0%, #f59e0b 50%, #ea580c 100%) !important; color: #ffffff !important;">
                                Join RideMyCars (Keep 90%)
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                            </a>
                            <a href="#calculator" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 bg-white dark:bg-white/10 hover:bg-gray-100 dark:hover:bg-white/20 text-gray-900 dark:text-white font-bold text-lg rounded-2xl border-2 border-gray-200 dark:border-white/15 transition-all">
                                Calculate Your Profit
                            </a>
                        </div>

                        <div class="flex flex-wrap items-center justify-center lg:justify-start gap-6 text-xs text-gray-600 dark:text-gray-400 font-bold">
                            <span class="flex items-center gap-1.5"><span class="text-emerald-500">✓</span> Free Driver Sign-Up</span>
                            <span class="flex items-center gap-1.5"><span class="text-emerald-500">✓</span> Instant MoMo & Bank Payouts</span>
                            <span class="flex items-center gap-1.5"><span class="text-emerald-500">✓</span> Zero Hidden Deductions</span>
                        </div>
                    </div>

                    <!-- Right Hero Real Driver Photo -->
                    <div class="lg:col-span-5 relative">
                        <div class="relative rounded-3xl overflow-hidden shadow-2xl border-2 border-amber-300 dark:border-amber-500/30 group">
                            <img src="{{ asset('images/driver-hero.jpg') }}" alt="Proud RideMyCars Verified Driver Partner" class="w-full h-[450px] sm:h-[520px] object-cover transition-transform duration-700 group-hover:scale-[1.02]">
                            
                            <!-- Floating Glass Badge -->
                            <div class="absolute bottom-6 left-6 right-6 p-5 rounded-2xl bg-white/95 dark:bg-[#141414]/95 backdrop-blur-md border border-gray-200 dark:border-white/10 shadow-xl flex items-center justify-between">
                                <div>
                                    <div class="flex items-center gap-1 text-amber-500 text-sm mb-0.5">
                                        ★★★★★ <span class="text-xs font-bold text-gray-800 dark:text-gray-200 ml-1">4.96 Rating</span>
                                    </div>
                                    <div class="text-xs font-bold text-gray-600 dark:text-gray-400">Verified Driver Partner</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xl font-black text-emerald-600 dark:text-emerald-400">90% Payout</div>
                                    <div class="text-[10px] uppercase font-bold text-gray-400">Every Single Trip</div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- 2. Interactive Earnings & Profit Calculator -->
        <section id="calculator" class="py-24 bg-white dark:bg-[#111]">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <span class="px-4 py-1.5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-extrabold text-xs uppercase tracking-wider border border-emerald-300 dark:border-emerald-800/40 inline-block mb-3">
                        Interactive Calculator
                    </span>
                    <h2 class="text-4xl sm:text-5xl font-black text-gray-900 dark:text-white tracking-tight mb-4">
                        Calculate How Much More You Keep
                    </h2>
                    <p class="text-base sm:text-lg text-gray-600 dark:text-gray-300 font-normal">
                        Drag the sliders to see the direct financial difference between the standard 25% commission and our 10% model.
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
                    <!-- Controls Column -->
                    <div class="lg:col-span-6 bg-gray-50 dark:bg-[#161616] p-8 sm:p-10 rounded-3xl border-2 border-gray-200 dark:border-white/10 shadow-lg flex flex-col justify-between">
                        <div class="space-y-8">
                            <!-- Daily Fares Slider -->
                            <div>
                                <div class="flex justify-between items-center mb-3">
                                    <label class="font-extrabold text-gray-900 dark:text-white text-base">Estimated Daily Fares</label>
                                    <span class="text-2xl font-black text-amber-600 dark:text-amber-400" x-text="currencySymbol + dailyFares"></span>
                                </div>
                                <input type="range" min="200" max="1500" step="50" x-model="dailyFares" class="w-full h-3 bg-gray-200 dark:bg-white/10 rounded-lg appearance-none cursor-pointer accent-amber-500">
                                <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-2 font-bold">
                                    <span>GH₵200 / day</span>
                                    <span>GH₵850 / day</span>
                                    <span>GH₵1,500 / day</span>
                                </div>
                            </div>

                            <!-- Driving Days Per Month -->
                            <div>
                                <div class="flex justify-between items-center mb-3">
                                    <label class="font-extrabold text-gray-900 dark:text-white text-base">Days Driven Per Month</label>
                                    <span class="text-2xl font-black text-blue-600 dark:text-blue-400" x-text="daysPerMonth + ' Days'"></span>
                                </div>
                                <input type="range" min="15" max="30" step="1" x-model="daysPerMonth" class="w-full h-3 bg-gray-200 dark:bg-white/10 rounded-lg appearance-none cursor-pointer accent-blue-600">
                                <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-2 font-bold">
                                    <span>15 days (Part-time)</span>
                                    <span>22 days (Standard)</span>
                                    <span>26+ days (Full-time)</span>
                                </div>
                            </div>

                            <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-xs text-amber-800 dark:text-amber-300 font-medium">
                                💡 <strong>The RideMyCars Advantage:</strong> While other apps deduct up to 25% plus hidden tech fees, RideMyCars caps platform commission at a flat 10%.
                            </div>
                        </div>

                        <div class="mt-8 pt-6 border-t border-gray-200 dark:border-white/10 flex items-center justify-between text-xs text-gray-500">
                            <span>Instant Payout Channels:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200">MTN MoMo • Telecel Cash • Bank Transfer</span>
                        </div>
                    </div>

                    <!-- Output Column -->
                    <div class="lg:col-span-6 bg-gradient-to-br from-amber-500/10 via-emerald-500/10 to-amber-500/5 dark:from-[#1e1b15] dark:via-[#131c17] dark:to-[#161616] p-8 sm:p-10 rounded-3xl border-2 border-amber-500 shadow-2xl flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-200 dark:border-white/10">
                                <span class="font-black text-lg text-gray-900 dark:text-white">Your Earnings Breakdown</span>
                                <span class="px-3.5 py-1 bg-emerald-600 text-white font-extrabold text-xs rounded-full">10% Fair Share</span>
                            </div>

                            <div class="space-y-4 mb-8">
                                <div class="flex justify-between items-center p-3.5 rounded-xl bg-white dark:bg-white/5 shadow-sm">
                                    <span class="text-sm font-semibold text-gray-600 dark:text-gray-300">Daily 90% Take-Home:</span>
                                    <span class="text-xl font-black text-emerald-600 dark:text-emerald-400" x-text="currencySymbol + dailyTakeHome"></span>
                                </div>
                                <div class="flex justify-between items-center p-3.5 rounded-xl bg-white dark:bg-white/5 shadow-sm">
                                    <span class="text-sm font-semibold text-gray-600 dark:text-gray-300">RideMyCars Fee (10%):</span>
                                    <span class="text-base font-extrabold text-gray-800 dark:text-gray-200" x-text="currencySymbol + dailyCommission"></span>
                                </div>
                                <div class="flex justify-between items-center p-3.5 rounded-xl bg-red-50 dark:bg-red-950/20 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800/40">
                                    <span class="text-sm font-semibold">Competitor Fee (25%):</span>
                                    <span class="text-base font-extrabold" x-text="currencySymbol + competitorCommission"></span>
                                </div>
                            </div>

                            <!-- Big Profit Highlight Box -->
                            <div class="p-6 rounded-2xl text-white shadow-xl mb-6 relative overflow-hidden"
                                 style="background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%) !important; color: #ffffff !important;">
                                <div class="text-xs uppercase font-extrabold tracking-widest mb-1" style="color: #fef3c7 !important;">Extra Profit in Your Pocket</div>
                                <div class="text-4xl sm:text-5xl font-black tracking-tight mb-2 text-white" style="color: #ffffff !important;" x-text="'+' + currencySymbol + monthlyExtra + ' / mo'"></div>
                                <p class="text-xs font-medium" style="color: #fffbeb !important;">
                                    That's <span class="font-extrabold" style="color: #ffffff !important;" x-text="'+' + currencySymbol + dailyExtra"></span> extra every single day, or <span class="font-extrabold" style="color: #ffffff !important;" x-text="'+' + currencySymbol + weeklyExtra"></span> more each week.
                                </p>
                            </div>
                        </div>

                        <a href="/driver-signup" class="w-full inline-flex items-center justify-center gap-2 py-4 px-6 rounded-2xl bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-black text-base hover:opacity-90 transition-all shadow-lg">
                            Claim Your 90% Driver Profile →
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3. Real Impact: What Could You Do with the Extra Income? -->
        <section class="py-24 bg-gray-50 dark:bg-[#161616] border-y border-gray-100 dark:border-white/10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <span class="px-4 py-1.5 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 font-extrabold text-xs uppercase tracking-wider border border-amber-200 dark:border-amber-800/40 inline-block mb-3">
                        Tangible Value
                    </span>
                    <h2 class="text-4xl sm:text-5xl font-black text-gray-900 dark:text-white tracking-tight">
                        What An Extra GH₵1,950+ Every Month Means
                    </h2>
                    <p class="text-base sm:text-lg text-gray-600 dark:text-gray-300 mt-2">
                        Money kept by drivers transforms lives, maintains vehicles, and empowers local communities.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                    <div class="bg-white dark:bg-[#111] rounded-3xl p-6 border-2 border-gray-200 dark:border-white/10 text-center hover:border-amber-500 transition-all hover:-translate-y-1 shadow-sm">
                        <div class="w-14 h-14 bg-amber-100 dark:bg-amber-950/60 text-amber-600 rounded-2xl flex items-center justify-center mx-auto mb-5 text-2xl font-bold">⛽</div>
                        <h3 class="font-extrabold text-lg text-gray-900 dark:text-white mb-2">Fuel Covered</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">Offset the rising costs of fuel and keep your car on the road with higher net margins.</p>
                    </div>

                    <div class="bg-white dark:bg-[#111] rounded-3xl p-6 border-2 border-gray-200 dark:border-white/10 text-center hover:border-blue-500 transition-all hover:-translate-y-1 shadow-sm">
                        <div class="w-14 h-14 bg-blue-100 dark:bg-blue-950/60 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-5 text-2xl font-bold">🎓</div>
                        <h3 class="font-extrabold text-lg text-gray-900 dark:text-white mb-2">Children's Education</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">Pay school fees and learning materials comfortably without taking high-interest loans.</p>
                    </div>

                    <div class="bg-white dark:bg-[#111] rounded-3xl p-6 border-2 border-gray-200 dark:border-white/10 text-center hover:border-emerald-500 transition-all hover:-translate-y-1 shadow-sm">
                        <div class="w-14 h-14 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-5 text-2xl font-bold">🔧</div>
                        <h3 class="font-extrabold text-lg text-gray-900 dark:text-white mb-2">Regular Maintenance</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">Replace tires, service brake pads, and preserve your vehicle's long-term resale value.</p>
                    </div>

                    <div class="bg-white dark:bg-[#111] rounded-3xl p-6 border-2 border-gray-200 dark:border-white/10 text-center hover:border-purple-500 transition-all hover:-translate-y-1 shadow-sm">
                        <div class="w-14 h-14 bg-purple-100 dark:bg-purple-950/60 text-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-5 text-2xl font-bold">🚗</div>
                        <h3 class="font-extrabold text-lg text-gray-900 dark:text-white mb-2">Next Car Savings</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">Save money every month toward upgrading to a newer or higher-tier executive vehicle.</p>
                    </div>

                    <div class="bg-white dark:bg-[#111] rounded-3xl p-6 border-2 border-gray-200 dark:border-white/10 text-center hover:border-rose-500 transition-all hover:-translate-y-1 shadow-sm sm:col-span-2 lg:col-span-1">
                        <div class="w-14 h-14 bg-rose-100 dark:bg-rose-950/60 text-rose-600 rounded-2xl flex items-center justify-center mx-auto mb-5 text-2xl font-bold">🏡</div>
                        <h3 class="font-extrabold text-lg text-gray-900 dark:text-white mb-2">Family Security</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">Put your hard-earned wages toward building emergency savings and home security.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. How to Get Started: 4 Simple Steps -->
        <section class="py-24 bg-white dark:bg-[#111]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <span class="px-4 py-1.5 rounded-full bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 font-extrabold text-xs uppercase tracking-wider border border-blue-200 dark:border-blue-800/40 inline-block mb-3">
                        Simple Onboarding
                    </span>
                    <h2 class="text-4xl sm:text-5xl font-black text-gray-900 dark:text-white tracking-tight">
                        Four Simple Steps to Your First Trip
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="p-8 rounded-3xl bg-gray-50 dark:bg-[#161616] border-2 border-gray-200 dark:border-white/10 text-center shadow-sm">
                        <span class="text-4xl font-black text-orange-500 block mb-4">01</span>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white mb-2">Sign Up in 2 Mins</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400 font-medium leading-relaxed">Enter your mobile phone number, city, and vehicle information to create your partner profile.</p>
                    </div>

                    <div class="p-8 rounded-3xl bg-gray-50 dark:bg-[#161616] border-2 border-gray-200 dark:border-white/10 text-center shadow-sm">
                        <span class="text-4xl font-black text-blue-500 block mb-4">02</span>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white mb-2">Upload Credentials</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400 font-medium leading-relaxed">Snap photos of your valid driver's license, vehicle registration, and insurance certificate.</p>
                    </div>

                    <div class="p-8 rounded-3xl bg-gray-50 dark:bg-[#161616] border-2 border-gray-200 dark:border-white/10 text-center shadow-sm">
                        <span class="text-4xl font-black text-emerald-500 block mb-4">03</span>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white mb-2">Fast Verification</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400 font-medium leading-relaxed">Our safety team verifies your documents within 24 hours to clear you for road dispatch.</p>
                    </div>

                    <div class="p-8 rounded-3xl bg-gray-50 dark:bg-[#161616] border-2 border-gray-200 dark:border-white/10 text-center shadow-sm">
                        <span class="text-4xl font-black text-purple-500 block mb-4">04</span>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white mb-2">Go Online & Keep 90%</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400 font-medium leading-relaxed">Accept on-demand rides, chauffeur bookings, or parcel dispatches and withdraw earnings anytime.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 5. Driver Mobile App Download Strip -->
        <section class="py-20 bg-gray-50 dark:bg-[#0d0d0d] border-t border-gray-200 dark:border-white/10">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="p-8 sm:p-12 rounded-3xl bg-gradient-to-br from-amber-500/15 via-white to-orange-500/10 dark:from-[#181712] dark:via-[#161510] dark:to-[#121210] border-2 border-amber-500/30 shadow-2xl flex flex-col md:flex-row items-center justify-between gap-8">
                    <div class="space-y-4 max-w-xl">
                        <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-500/30">
                            Partner Mobile Console
                        </span>
                        <h3 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                            Download the RideMyCars <span class="text-amber-500">Driver App</span>
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed font-normal">
                            Get instant dispatch alerts, navigate turn-by-turn with live GPS, and track your daily and weekly earnings with zero latency directly on iOS or Android.
                        </p>
                        <div class="flex flex-wrap items-center gap-4 pt-2 text-xs font-bold text-gray-700 dark:text-gray-300">
                            <span class="flex items-center gap-1.5"><span class="text-amber-500">⚡</span> Sub-Second Dispatch</span>
                            <span class="flex items-center gap-1.5"><span class="text-amber-500">🗺️</span> In-App Navigation</span>
                            <span class="flex items-center gap-1.5"><span class="text-amber-500">💰</span> Real-Time MoMo Payouts</span>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row md:flex-col gap-3 shrink-0 w-full sm:w-auto">
                        <a href="{{ site_setting('driver.ios_url', 'https://apps.apple.com/app/ridemycars-driver/id987654321') }}" target="_blank" rel="noopener" class="flex items-center justify-center gap-3 px-7 py-3.5 bg-gray-900 text-white dark:bg-white dark:text-gray-900 rounded-2xl hover:opacity-95 transition-all shadow-md">
                            <svg class="w-5 h-5 shrink-0 fill-current" viewBox="0 0 24 24"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.62-.75 1.04-1.8 0.92-2.85-.9.04-1.99.6-2.61 1.35-.55.63-1.03 1.68-.9 2.7.99.08 2.01-.5 2.59-1.2z"/></svg>
                            <div class="flex flex-col text-left">
                                <span class="text-[8px] uppercase tracking-wider opacity-70 leading-none">Download on</span>
                                <span class="text-xs font-black leading-tight mt-0.5">App Store</span>
                            </div>
                        </a>

                        <a href="{{ route('download.driver') }}" download="RideMyCars-Driver.apk" class="flex items-center justify-center gap-3 px-7 py-3.5 bg-amber-500 hover:bg-amber-600 text-gray-950 font-black rounded-2xl transition-all shadow-lg shadow-amber-500/25">
                            <svg class="w-5 h-5 shrink-0 fill-current" viewBox="0 0 24 24"><path d="M3.609 1.814L13.792 12 3.61 22.186a1.994 1.994 0 0 1-.61-.954V2.768c.118-.363.33-.687.609-.954zm11.233 11.233l2.257 2.257-11.83 6.697 9.573-8.954zm2.257-2.094l2.845 1.611c.907.514.907 1.353 0 1.867l-2.845 1.611-2.09-2.09 2.09-2.999zm-2.257-2.093L5.27 0.906l11.83 6.697-2.258 2.257z"/></svg>
                            <div class="flex flex-col text-left">
                                <span class="text-[8px] uppercase tracking-wider text-gray-950/80 leading-none">Download APK /</span>
                                <span class="text-xs font-black leading-tight mt-0.5">Google Play</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- 6. Driver FAQs -->
        <section class="py-24 bg-white dark:bg-[#111]">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <span class="px-4 py-1.5 rounded-full bg-orange-100 dark:bg-orange-950/60 text-orange-700 dark:text-orange-300 font-extrabold text-xs uppercase tracking-wider border border-orange-200 dark:border-orange-800/40 inline-block mb-3">
                        Driver FAQs
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-black text-gray-900 dark:text-white tracking-tight">
                        Frequently Asked Questions
                    </h2>
                </div>

                <div class="space-y-4" x-data="{ openFaq: null }">
                    <div class="p-6 rounded-2xl bg-gray-50 dark:bg-[#161616] border-2 border-gray-200 dark:border-white/10 cursor-pointer" @click="openFaq = (openFaq === 1 ? null : 1)">
                        <div class="flex justify-between items-center">
                            <h4 class="font-extrabold text-base text-gray-900 dark:text-white">How does the 10% commission work?</h4>
                            <span class="text-xl font-bold" x-text="openFaq === 1 ? '−' : '+'"></span>
                        </div>
                        <div x-show="openFaq === 1" class="mt-3 text-xs sm:text-sm text-gray-600 dark:text-gray-400 leading-relaxed font-normal">
                            On every completed trip or delivery, RideMyCars deducts exactly 10% to cover server infrastructure, live GPS telemetry, insurance, and 24/7 security. The remaining 90% is credited directly to your partner wallet immediately upon trip completion.
                        </div>
                    </div>

                    <div class="p-6 rounded-2xl bg-gray-50 dark:bg-[#161616] border-2 border-gray-200 dark:border-white/10 cursor-pointer" @click="openFaq = (openFaq === 2 ? null : 2)">
                        <div class="flex justify-between items-center">
                            <h4 class="font-extrabold text-base text-gray-900 dark:text-white">How and when do I get paid?</h4>
                            <span class="text-xl font-bold" x-text="openFaq === 2 ? '−' : '+'"></span>
                        </div>
                        <div x-show="openFaq === 2" class="mt-3 text-xs sm:text-sm text-gray-600 dark:text-gray-400 leading-relaxed font-normal">
                            You have complete control over your earnings. You can trigger on-demand withdrawals directly to your Mobile Money wallet (MTN, Telecel) or commercial bank account at any time, or allow weekly automatic disbursements with zero withdrawal fees.
                        </div>
                    </div>

                    <div class="p-6 rounded-2xl bg-gray-50 dark:bg-[#161616] border-2 border-gray-200 dark:border-white/10 cursor-pointer" @click="openFaq = (openFaq === 3 ? null : 3)">
                        <div class="flex justify-between items-center">
                            <h4 class="font-extrabold text-base text-gray-900 dark:text-white">What are the vehicle age and condition requirements?</h4>
                            <span class="text-xl font-bold" x-text="openFaq === 3 ? '−' : '+'"></span>
                        </div>
                        <div x-show="openFaq === 3" class="mt-3 text-xs sm:text-sm text-gray-600 dark:text-gray-400 leading-relaxed font-normal">
                            Vehicles must be 2012 or newer, possess 4 working doors, operational air conditioning, intact seatbelts, clean upholstery, and pass our 15-point roadworthiness audit. Two-door coupés are not eligible for ride-hailing services.
                        </div>
                    </div>

                    <div class="p-6 rounded-2xl bg-gray-50 dark:bg-[#161616] border-2 border-gray-200 dark:border-white/10 cursor-pointer" @click="openFaq = (openFaq === 4 ? null : 4)">
                        <div class="flex justify-between items-center">
                            <h4 class="font-extrabold text-base text-gray-900 dark:text-white">Can I drive without owning a car?</h4>
                            <span class="text-xl font-bold" x-text="openFaq === 4 ? '−' : '+'"></span>
                        </div>
                        <div x-show="openFaq === 4" class="mt-3 text-xs sm:text-sm text-gray-600 dark:text-gray-400 leading-relaxed font-normal">
                            Yes! Through our Chauffeur Hire service, verified drivers can accept bookings to drive vehicle owners and corporate clients in their private luxury cars, earning hourly and daily guaranteed wages without needing your own vehicle.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 7. Strong Driver CTA Section -->
        <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-[2.5rem] p-8 sm:p-14 text-center text-white shadow-2xl relative overflow-hidden"
                 style="background: linear-gradient(135deg, #ea580c 0%, #f59e0b 50%, #ea580c 100%) !important; color: #ffffff !important;">
                <div class="relative z-10">
                    <span class="px-4 py-1.5 rounded-full bg-white/20 text-white font-extrabold text-xs uppercase tracking-widest inline-block mb-4">
                        Join Over 3,200+ Verified Driver Partners
                    </span>
                    <h2 class="text-3xl sm:text-5xl font-black mb-4 tracking-tight">Ready to Keep 90% of Your Hard Work?</h2>
                    <p class="text-white/90 text-sm sm:text-lg max-w-xl mx-auto mb-8 font-medium">
                        Don't work hard just to surrender 25% to apps that don't care about your livelihood. Sign up now and keep 90% from your first trip.
                    </p>
                    <div class="flex flex-wrap items-center justify-center gap-4">
                        <a href="/driver-signup" class="px-9 py-4 rounded-2xl bg-white text-orange-600 font-black text-lg shadow-xl hover:bg-gray-50 transition-all hover:scale-105">
                            Sign Up Now (Free)
                        </a>
                        <a href="/login" class="px-8 py-4 rounded-2xl bg-gray-900 text-white font-bold text-base shadow-xl hover:bg-black transition-all hover:scale-105">
                            Driver Partner Sign In
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </main>
</x-layout>
