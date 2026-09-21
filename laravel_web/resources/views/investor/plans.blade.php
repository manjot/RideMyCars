<x-layout title="Investment Plans & Tranches | RideMyCars NDFG LLC">
    <x-investor-nav />

    <div class="min-h-screen bg-slate-50 dark:bg-[#0b0f17] text-slate-900 dark:text-white relative overflow-hidden transition-colors selection:bg-brand-500 selection:text-black">
        <!-- Ambient glows -->
        <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[850px] h-[450px] bg-gradient-to-tr from-amber-500/10 via-brand-500/5 to-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl 2xl:max-w-[1600px] w-full mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 pt-12 sm:pt-16 pb-20 relative z-10"
             x-data="{ 
                 activeCountry: (new URLSearchParams(window.location.search).get('country') || 'ghana').toLowerCase(),
                 setCountry(c) {
                     this.activeCountry = c;
                     if (window.history.pushState) {
                         const newUrl = window.location.pathname + '?country=' + c;
                         window.history.replaceState({ path: newUrl }, '', newUrl);
                     }
                 }
             }">
            <!-- Header -->
            <div class="text-center max-w-3xl mx-auto mb-10">
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-amber-50 dark:bg-amber-500/15 border border-amber-200 dark:border-amber-500/30 text-amber-800 dark:text-amber-300 text-xs font-black uppercase tracking-wider mb-3">
                    Capital Commitment Structures
                </div>
                <h1 class="text-3xl sm:text-5xl font-black text-slate-900 dark:text-white tracking-tight">Structured Investment Tranches</h1>
                <p class="text-base sm:text-lg text-slate-600 dark:text-slate-300 mt-4 leading-relaxed font-medium">
                    Investment tranches are organized per sovereign jurisdiction under the NDFG LLC Operating Agreement. Active capital allocation is currently open for the <strong>Ride My Cars (Ghana)</strong> single cohort.
                </p>
            </div>

            <!-- Country Selection Tabs: Ghana, South Africa, USA, Other Countries -->
            <div class="flex justify-center mb-10">
                <div class="inline-flex flex-wrap justify-center p-1.5 rounded-2xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-md gap-1.5 sm:gap-2 max-w-full">
                    <!-- Ghana Tab (Currently Live & Offering) -->
                    <button type="button" 
                            @click="setCountry('ghana')" 
                            :class="activeCountry === 'ghana' 
                                ? 'bg-amber-500 text-black shadow-md shadow-amber-500/20' 
                                : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800'"
                            class="flex items-center gap-2 px-4 sm:px-5 py-2.5 rounded-xl font-black text-xs sm:text-sm tracking-wide transition-all whitespace-nowrap cursor-pointer">
                        <span class="text-base sm:text-lg">🇬🇭</span>
                        <span>Ghana</span>
                        <span :class="activeCountry === 'ghana' ? 'bg-black/20 text-black' : 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30'" 
                              class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Live Offering
                        </span>
                    </button>

                    <!-- South Africa Tab -->
                    <button type="button" 
                            @click="setCountry('south-africa')" 
                            :class="activeCountry === 'south-africa' 
                                ? 'bg-amber-500 text-black shadow-md shadow-amber-500/20' 
                                : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800'"
                            class="flex items-center gap-2 px-4 sm:px-5 py-2.5 rounded-xl font-black text-xs sm:text-sm tracking-wide transition-all whitespace-nowrap cursor-pointer">
                        <span class="text-base sm:text-lg">🇿🇦</span>
                        <span>South Africa</span>
                        <span :class="activeCountry === 'south-africa' ? 'bg-black/20 text-black' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400'" 
                              class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full">
                            Pre-Reg
                        </span>
                    </button>

                    <!-- USA Tab -->
                    <button type="button" 
                            @click="setCountry('usa')" 
                            :class="activeCountry === 'usa' 
                                ? 'bg-amber-500 text-black shadow-md shadow-amber-500/20' 
                                : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800'"
                            class="flex items-center gap-2 px-4 sm:px-5 py-2.5 rounded-xl font-black text-xs sm:text-sm tracking-wide transition-all whitespace-nowrap cursor-pointer">
                        <span class="text-base sm:text-lg">🇺🇸</span>
                        <span>USA</span>
                        <span :class="activeCountry === 'usa' ? 'bg-black/20 text-black' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400'" 
                              class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full">
                            Reg D / Waitlist
                        </span>
                    </button>

                    <!-- Other Countries Tab -->
                    <button type="button" 
                            @click="setCountry('other')" 
                            :class="activeCountry === 'other' 
                                ? 'bg-amber-500 text-black shadow-md shadow-amber-500/20' 
                                : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800'"
                            class="flex items-center gap-2 px-4 sm:px-5 py-2.5 rounded-xl font-black text-xs sm:text-sm tracking-wide transition-all whitespace-nowrap cursor-pointer">
                        <span class="text-base sm:text-lg">🌍</span>
                        <span>Other Countries</span>
                        <span :class="activeCountry === 'other' ? 'bg-black/20 text-black' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400'" 
                              class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full">
                            Pipeline
                        </span>
                    </button>
                </div>
            </div>

            <!-- ================================================================= -->
            <!-- 1. GHANA VIEW (ACTIVE OFFERING - DEFAULT SELECTED)                 -->
            <!-- ================================================================= -->
            <div x-show="activeCountry === 'ghana'" x-transition:enter="transition ease-out duration-300 transform opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <!-- Regional Cohort Context Banner -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-5 rounded-2xl bg-amber-50/80 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 mb-8 shadow-xs">
                    <div class="flex items-start sm:items-center gap-3">
                        <span class="text-3xl">🇬🇭</span>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white">Active Single-Cohort Allocation: Ride My Cars (Ghana)</h3>
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-500 text-black text-[10px] font-black uppercase tracking-wider">Now Offering</span>
                            </div>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">
                                Direct capital commitment under the NDFG LLC Operating Agreement. Capital intake is open across three discrete tiers.
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-xs font-bold text-amber-800 dark:text-amber-300 bg-white dark:bg-[#131926] px-3.5 py-2 rounded-xl border border-amber-200 dark:border-amber-500/30 shadow-xs">
                        <span>Basic Platform Revenue: 50 GHC/day</span>
                        <span class="text-slate-400">•</span>
                        <span>Targeted Net Margin: 85–92%</span>
                    </div>
                </div>

                <!-- Ghana Tranche Comparison Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
                    
                    <!-- Tranche A: Seed Tier -->
                    <div class="p-8 rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-black uppercase tracking-wider text-amber-600 dark:text-amber-400">Seed Tier</span>
                                <span class="text-[10px] font-bold text-slate-500">85% Subscribed</span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden mb-4">
                                <div class="bg-amber-500 h-full rounded-full" style="width: 85%;"></div>
                            </div>

                            <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-2">Tranche A</h2>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mb-6 leading-relaxed">
                                Ideal for private individual angels entering early-stage West African technology and mobility infrastructure.
                            </p>

                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 mb-6">
                                <div class="text-3xl font-black text-slate-900 dark:text-white">720,000 GHC</div>
                                <div class="text-xs text-amber-600 dark:text-amber-400 font-bold mt-0.5">Approx. $60,000 USD</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Single upfront commitment</div>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-500/15 border border-amber-200 dark:border-amber-500/30 text-center mb-6">
                                <span class="text-xs text-slate-600 dark:text-slate-300">Equity Allocation:</span>
                                <div class="text-xl font-black text-amber-600 dark:text-amber-400">10.0% Fixed Stake</div>
                            </div>

                            <ul class="space-y-3 text-xs text-slate-600 dark:text-slate-300 mb-8">
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Quarterly multi-currency dividend distributions</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>24/7 Access to Live Micro-Transaction Ticker</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Quarterly financial and fleet operational updates</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Standard Year 3 structured buyout option</span>
                                </li>
                            </ul>
                        </div>

                        <a href="/investor/register?tranche=A" class="w-full py-3.5 text-center rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-900 dark:text-white font-black text-xs uppercase tracking-wider transition-all">
                            Apply for Tranche A →
                        </a>
                    </div>

                    <!-- Tranche B: Growth Tier (Flagship) -->
                    <div class="p-8 rounded-3xl bg-white dark:bg-[#131926] border-2 border-amber-500 ring-4 ring-amber-500/15 shadow-xl shadow-amber-500/15 transition-all duration-300 flex flex-col justify-between relative hover:-translate-y-1">
                        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-4 py-0.5 rounded-full bg-amber-500 text-black font-black text-[10px] uppercase tracking-wider shadow-xs">
                            ★ Most Popular Growth Tier
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2 mt-1">
                                <span class="text-xs font-black uppercase tracking-wider text-amber-600 dark:text-amber-400">Growth Tier</span>
                                <span class="text-[10px] font-bold text-slate-500">62% Subscribed</span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden mb-4">
                                <div class="bg-amber-500 h-full rounded-full" style="width: 62%;"></div>
                            </div>

                            <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-2">Tranche B</h2>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mb-6 leading-relaxed">
                                Engineered for family offices and sophisticated syndicate leads seeking prime yield velocity with enhanced liquidity provisions.
                            </p>

                            <div class="p-4 rounded-2xl bg-amber-50/50 dark:bg-slate-800/40 border border-amber-500/30 mb-6">
                                <div class="text-3xl font-black text-slate-900 dark:text-white">1,440,000 GHC</div>
                                <div class="text-xs text-amber-600 dark:text-amber-400 font-bold mt-0.5">Approx. $120,000 USD</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Priority single allocation</div>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-amber-500 text-black text-center mb-6 shadow-xs">
                                <span class="text-xs font-bold opacity-80">Equity Allocation:</span>
                                <div class="text-xl font-black">14.0% Fixed Stake</div>
                            </div>

                            <ul class="space-y-3 text-xs text-slate-600 dark:text-slate-300 mb-8">
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 dark:text-amber-400 font-black">✓</span>
                                    <span>Accelerated priority quarterly dividend wires</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 dark:text-amber-400 font-black">✓</span>
                                    <span>Full 3-Year Master Cash Ledger telemetry data</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 dark:text-amber-400 font-black">✓</span>
                                    <span>Direct line to Ride My Cars (Ghana) compliance desk</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 dark:text-amber-400 font-black">✓</span>
                                    <span>Enhanced Year 3 buyout formula with clawback reserve</span>
                                </li>
                            </ul>
                        </div>

                        <a href="/investor/register?tranche=B" class="w-full py-3.5 text-center rounded-xl bg-amber-500 hover:bg-brand-500 text-black font-black text-xs uppercase tracking-wider shadow-lg shadow-amber-500/25 transition-all">
                            Apply for Tranche B →
                        </a>
                    </div>

                    <!-- Tranche C: Venture Tier -->
                    <div class="p-8 rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Venture Tier</span>
                                <span class="text-[10px] font-bold text-slate-500">44% Subscribed</span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden mb-4">
                                <div class="bg-emerald-500 h-full rounded-full" style="width: 44%;"></div>
                            </div>

                            <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-2">Tranche C</h2>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mb-6 leading-relaxed">
                                Designed for venture funds, institutional syndicates, and strategic sovereign entities looking for substantial equity leverage.
                            </p>

                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 mb-6">
                                <div class="text-3xl font-black text-slate-900 dark:text-white">2,640,000 GHC</div>
                                <div class="text-xs text-emerald-600 dark:text-emerald-400 font-bold mt-0.5">Approx. $220,000 USD</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Maximum single allocation</div>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-200 dark:border-emerald-500/30 text-center mb-6">
                                <span class="text-xs text-slate-600 dark:text-slate-300">Equity Allocation:</span>
                                <div class="text-xl font-black text-emerald-600 dark:text-emerald-400">22.0% Fixed Stake</div>
                            </div>

                            <ul class="space-y-3 text-xs text-slate-600 dark:text-slate-300 mb-8">
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Highest quarterly dividend pool participation</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Advisory board observer seat consideration</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Direct wire SWIFT & MoMo escrow routing priority</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Institutional buyout multiple at Year 3 exit</span>
                                </li>
                            </ul>
                        </div>

                        <a href="/investor/register?tranche=C" class="w-full py-3.5 text-center rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-900 dark:text-white font-black text-xs uppercase tracking-wider transition-all">
                            Apply for Tranche C →
                        </a>
                    </div>
                </div>

                <!-- Ghana Settlement Protocol -->
                <div class="rounded-2xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 p-6 text-center max-w-2xl mx-auto text-xs text-slate-600 dark:text-slate-400 shadow-sm">
                    🔒 <strong>Settlement Protocol:</strong> In compliance with NDFG LLC governance rules, following administrative approval, all capital commitments must settle into the designated Ride My Cars (Ghana) escrow accounts within <strong>five (5) business days</strong>.
                </div>
            </div>

            <!-- ================================================================= -->
            <!-- 2. SOUTH AFRICA VIEW (UPCOMING EXPANSION / PRE-REGISTRATION)       -->
            <!-- ================================================================= -->
            <div x-show="activeCountry === 'south-africa'" x-cloak x-transition:enter="transition ease-out duration-300 transform opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <!-- Regional Cohort Context Banner -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-5 rounded-2xl bg-blue-50/80 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 mb-8 shadow-xs">
                    <div class="flex items-start sm:items-center gap-3">
                        <span class="text-3xl">🇿🇦</span>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white">South Africa Expansion Cohort (Johannesburg & Cape Town)</h3>
                                <span class="px-2.5 py-0.5 rounded-full bg-blue-500 text-white text-[10px] font-black uppercase tracking-wider">Structuring Phase • Pre-Registration Open</span>
                            </div>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">
                                Ride My Cars is currently accepting capital allocations exclusively for Ghana. Pre-register below to lock in early allocation queue for the South Africa launch.
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="setCountry('ghana')" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-amber-500 hover:bg-brand-500 text-black font-black text-xs uppercase tracking-wider shadow-sm transition-all whitespace-nowrap cursor-pointer">
                        <span>🇬🇭 View Live Ghana Offering</span>
                        <span>→</span>
                    </button>
                </div>

                <!-- South Africa Tranche Preview Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
                    <!-- SA Tranche A -->
                    <div class="p-8 rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-black uppercase tracking-wider text-blue-600 dark:text-blue-400">Seed Tier (ZAF)</span>
                                <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-2 py-0.5 rounded-full">Pre-Registration Open</span>
                            </div>
                            <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-2">Tranche A</h2>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mb-6 leading-relaxed">
                                Regional entry for private angel investors targeting commercial fleet mobility in Gauteng & Western Cape corridors.
                            </p>

                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 mb-6">
                                <div class="text-3xl font-black text-slate-900 dark:text-white">1,100,000 ZAR</div>
                                <div class="text-xs text-blue-600 dark:text-blue-400 font-bold mt-0.5">Approx. $60,000 USD</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Single upfront commitment</div>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-blue-50 dark:bg-blue-500/15 border border-blue-200 dark:border-blue-500/30 text-center mb-6">
                                <span class="text-xs text-slate-600 dark:text-slate-300">Equity Allocation:</span>
                                <div class="text-xl font-black text-blue-600 dark:text-blue-400">8.0% Fixed Stake</div>
                            </div>

                            <ul class="space-y-3 text-xs text-slate-600 dark:text-slate-300 mb-8">
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Quarterly dividend wires in ZAR or USD</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Access to South Africa regional fleet telemetry</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Johannesburg metro operational reports</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Standard Year 3 structured buyout option</span>
                                </li>
                            </ul>
                        </div>

                        <a href="/investor/contact?country=South+Africa&interested_tranche=Tranche+A+(Seed+Tier)" class="w-full py-3.5 text-center rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-900 dark:text-white font-black text-xs uppercase tracking-wider transition-all">
                            Pre-Register for SA Tranche A →
                        </a>
                    </div>

                    <!-- SA Tranche B (Featured) -->
                    <div class="p-8 rounded-3xl bg-white dark:bg-[#131926] border-2 border-blue-500 ring-4 ring-blue-500/15 shadow-xl shadow-blue-500/15 transition-all duration-300 flex flex-col justify-between relative hover:-translate-y-1">
                        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-4 py-0.5 rounded-full bg-blue-500 text-white font-black text-[10px] uppercase tracking-wider shadow-xs">
                            ★ High Pre-Registration Demand
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2 mt-1">
                                <span class="text-xs font-black uppercase tracking-wider text-blue-600 dark:text-blue-400">Growth Tier (ZAF)</span>
                                <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-2 py-0.5 rounded-full">Priority Allocation</span>
                            </div>
                            <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-2">Tranche B</h2>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mb-6 leading-relaxed">
                                Tailored for Southern African family offices and syndicated vehicle asset groups requiring prioritized dividend returns.
                            </p>

                            <div class="p-4 rounded-2xl bg-blue-50/50 dark:bg-slate-800/40 border border-blue-500/30 mb-6">
                                <div class="text-3xl font-black text-slate-900 dark:text-white">2,200,000 ZAR</div>
                                <div class="text-xs text-blue-600 dark:text-blue-400 font-bold mt-0.5">Approx. $120,000 USD</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Priority single allocation</div>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-blue-600 text-white text-center mb-6 shadow-xs">
                                <span class="text-xs font-bold opacity-80">Equity Allocation:</span>
                                <div class="text-xl font-black">12.0% Fixed Stake</div>
                            </div>

                            <ul class="space-y-3 text-xs text-slate-600 dark:text-slate-300 mb-8">
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-600 dark:text-blue-400 font-black">✓</span>
                                    <span>Accelerated quarterly dividend wire payouts</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-600 dark:text-blue-400 font-black">✓</span>
                                    <span>Priority allocation rights on initial 100 vehicle units</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-600 dark:text-blue-400 font-black">✓</span>
                                    <span>Direct contact with Southern Africa expansion lead</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-600 dark:text-blue-400 font-black">✓</span>
                                    <span>Enhanced Year 3 structured buyout formula</span>
                                </li>
                            </ul>
                        </div>

                        <a href="/investor/contact?country=South+Africa&interested_tranche=Tranche+B+(Growth+Tier)" class="w-full py-3.5 text-center rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-black text-xs uppercase tracking-wider shadow-lg shadow-blue-500/25 transition-all">
                            Pre-Register for SA Tranche B →
                        </a>
                    </div>

                    <!-- SA Tranche C -->
                    <div class="p-8 rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Venture Tier (ZAF)</span>
                                <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-full">Syndicate Reserved</span>
                            </div>
                            <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-2">Tranche C</h2>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mb-6 leading-relaxed">
                                Strategic institutional allocation for major regional funds and vehicle asset syndicates seeking governance presence.
                            </p>

                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 mb-6">
                                <div class="text-3xl font-black text-slate-900 dark:text-white">4,000,000 ZAR</div>
                                <div class="text-xs text-emerald-600 dark:text-emerald-400 font-bold mt-0.5">Approx. $220,000 USD</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Maximum single allocation</div>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-200 dark:border-emerald-500/30 text-center mb-6">
                                <span class="text-xs text-slate-600 dark:text-slate-300">Equity Allocation:</span>
                                <div class="text-xl font-black text-emerald-600 dark:text-emerald-400">20.0% Fixed Stake</div>
                            </div>

                            <ul class="space-y-3 text-xs text-slate-600 dark:text-slate-300 mb-8">
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Highest quarterly dividend pool participation across SA</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Regional Advisory Board observer seat consideration</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Direct SARB & cross-border SWIFT escrow clearance</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Institutional buyout multiple at Year 3 exit</span>
                                </li>
                            </ul>
                        </div>

                        <a href="/investor/contact?country=South+Africa&interested_tranche=Tranche+C+(Venture+Tier)" class="w-full py-3.5 text-center rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-900 dark:text-white font-black text-xs uppercase tracking-wider transition-all">
                            Pre-Register for SA Tranche C →
                        </a>
                    </div>
                </div>

                <!-- South Africa Notice -->
                <div class="rounded-2xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 p-6 text-center max-w-2xl mx-auto text-xs text-slate-600 dark:text-slate-400 shadow-sm">
                    🇿🇦 <strong>South Africa Governance Protocol:</strong> Structuring is governed under NDFG LLC in consultation with South African cross-border regulatory standards. Submitting early pre-registration establishes formal tranche queue priority prior to cohort activation.
                </div>
            </div>

            <!-- ================================================================= -->
            <!-- 3. USA VIEW (REG D 506(c) ACCREDITED INVESTORS & SYNDICATES)      -->
            <!-- ================================================================= -->
            <div x-show="activeCountry === 'usa'" x-cloak x-transition:enter="transition ease-out duration-300 transform opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <!-- Regional Cohort Context Banner -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-5 rounded-2xl bg-indigo-50/80 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/30 mb-8 shadow-xs">
                    <div class="flex items-start sm:items-center gap-3">
                        <span class="text-3xl">🇺🇸</span>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white">United States Institutional & Syndicate Allocation (NDFG LLC Delaware)</h3>
                                <span class="px-2.5 py-0.5 rounded-full bg-indigo-600 text-white text-[10px] font-black uppercase tracking-wider">SEC Reg D 506(c) Accredited</span>
                            </div>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">
                                Structured under Delaware LLC governance for US Accredited Investors and Angel Syndicates. Domestic USD wire settlement.
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="setCountry('ghana')" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-amber-500 hover:bg-brand-500 text-black font-black text-xs uppercase tracking-wider shadow-sm transition-all whitespace-nowrap cursor-pointer">
                        <span>🇬🇭 View Live Ghana Offering</span>
                        <span>→</span>
                    </button>
                </div>

                <!-- USA Tranche Preview Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
                    <!-- US Tranche A -->
                    <div class="p-8 rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-black uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Seed Tier (USD)</span>
                                <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 rounded-full">Accredited Individual</span>
                            </div>
                            <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-2">Tranche A</h2>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mb-6 leading-relaxed">
                                Structured for individual US accredited angel investors seeking direct high-yield African mobility fintech exposure.
                            </p>

                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 mb-6">
                                <div class="text-3xl font-black text-slate-900 dark:text-white">$60,000 USD</div>
                                <div class="text-xs text-indigo-600 dark:text-indigo-400 font-bold mt-0.5">Direct domestic USD wire / ACH</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Single upfront commitment</div>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-indigo-50 dark:bg-indigo-500/15 border border-indigo-200 dark:border-indigo-500/30 text-center mb-6">
                                <span class="text-xs text-slate-600 dark:text-slate-300">Equity Allocation:</span>
                                <div class="text-xl font-black text-indigo-600 dark:text-indigo-400">6.0% Fixed Stake</div>
                            </div>

                            <ul class="space-y-3 text-xs text-slate-600 dark:text-slate-300 mb-8">
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Quarterly USD dividend wires to US bank account</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>SEC Rule 506(c) accredited third-party verification</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>24/7 Access to Live Micro-Transaction Ticker</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Standard Delaware LLC Year 3 structured buyout</span>
                                </li>
                            </ul>
                        </div>

                        <a href="/investor/contact?country=USA&interested_tranche=Tranche+A+(Seed+Tier)" class="w-full py-3.5 text-center rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-900 dark:text-white font-black text-xs uppercase tracking-wider transition-all">
                            Pre-Register for US Tranche A →
                        </a>
                    </div>

                    <!-- US Tranche B (Featured) -->
                    <div class="p-8 rounded-3xl bg-white dark:bg-[#131926] border-2 border-indigo-500 ring-4 ring-indigo-500/15 shadow-xl shadow-indigo-500/15 transition-all duration-300 flex flex-col justify-between relative hover:-translate-y-1">
                        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-4 py-0.5 rounded-full bg-indigo-600 text-white font-black text-[10px] uppercase tracking-wider shadow-xs">
                            ★ Primary US Syndicate Choice
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2 mt-1">
                                <span class="text-xs font-black uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Growth Tier (USD)</span>
                                <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 rounded-full">Syndicate Lead Tier</span>
                            </div>
                            <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-2">Tranche B</h2>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mb-6 leading-relaxed">
                                Engineered for US single-family offices and multi-member angel syndicates requiring expedited liquidity and pass-through accounting.
                            </p>

                            <div class="p-4 rounded-2xl bg-indigo-50/50 dark:bg-slate-800/40 border border-indigo-500/30 mb-6">
                                <div class="text-3xl font-black text-slate-900 dark:text-white">$120,000 USD</div>
                                <div class="text-xs text-indigo-600 dark:text-indigo-400 font-bold mt-0.5">Direct domestic USD wire / ACH</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Priority single allocation</div>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-indigo-600 text-white text-center mb-6 shadow-xs">
                                <span class="text-xs font-bold opacity-80">Equity Allocation:</span>
                                <div class="text-xl font-black">10.0% Fixed Stake</div>
                            </div>

                            <ul class="space-y-3 text-xs text-slate-600 dark:text-slate-300 mb-8">
                                <li class="flex items-start gap-2">
                                    <span class="text-indigo-600 dark:text-indigo-400 font-black">✓</span>
                                    <span>Priority accelerated quarterly USD dividend wires</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-indigo-600 dark:text-indigo-400 font-black">✓</span>
                                    <span>Full 3-Year Master Cash Ledger telemetry data</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-indigo-600 dark:text-indigo-400 font-black">✓</span>
                                    <span>Delaware LLC pass-through tax schedule reporting</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-indigo-600 dark:text-indigo-400 font-black">✓</span>
                                    <span>Enhanced Year 3 buyout formula with clawback reserve</span>
                                </li>
                            </ul>
                        </div>

                        <a href="/investor/contact?country=USA&interested_tranche=Tranche+B+(Growth+Tier)" class="w-full py-3.5 text-center rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs uppercase tracking-wider shadow-lg shadow-indigo-500/25 transition-all">
                            Pre-Register for US Tranche B →
                        </a>
                    </div>

                    <!-- US Tranche C -->
                    <div class="p-8 rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Venture Tier (USD)</span>
                                <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-full">Institutional Fund</span>
                            </div>
                            <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-2">Tranche C</h2>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mb-6 leading-relaxed">
                                Formatted for US venture capital funds and institutional allocators looking for significant equity stake and observer rights.
                            </p>

                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 mb-6">
                                <div class="text-3xl font-black text-slate-900 dark:text-white">$220,000 USD</div>
                                <div class="text-xs text-emerald-600 dark:text-emerald-400 font-bold mt-0.5">Maximum single commitment</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Direct wire clearance</div>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-200 dark:border-emerald-500/30 text-center mb-6">
                                <span class="text-xs text-slate-600 dark:text-slate-300">Equity Allocation:</span>
                                <div class="text-xl font-black text-emerald-600 dark:text-emerald-400">18.0% Fixed Stake</div>
                            </div>

                            <ul class="space-y-3 text-xs text-slate-600 dark:text-slate-300 mb-8">
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Highest quarterly dividend pool participation in USD</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>NDFG LLC Advisory Board observer seat consideration</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Dedicated institutional legal counsel liaison</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Institutional multiple at Year 3 exit / secondary liquidation</span>
                                </li>
                            </ul>
                        </div>

                        <a href="/investor/contact?country=USA&interested_tranche=Tranche+C+(Venture+Tier)" class="w-full py-3.5 text-center rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-900 dark:text-white font-black text-xs uppercase tracking-wider transition-all">
                            Pre-Register for US Tranche C →
                        </a>
                    </div>
                </div>

                <!-- USA Notice -->
                <div class="rounded-2xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 p-6 text-center max-w-2xl mx-auto text-xs text-slate-600 dark:text-slate-400 shadow-sm">
                    🇺🇸 <strong>SEC Regulation D Notice:</strong> Investment allocations for US entities are offered exclusively under Rule 506(c) of Regulation D under the Securities Act of 1933. Direct commitments are governed under NDFG LLC (Delaware).
                </div>
            </div>

            <!-- ================================================================= -->
            <!-- 4. OTHER COUNTRIES (INTERNATIONAL & PAN-AFRICAN EXPANSION PIPELINE)-->
            <!-- ================================================================= -->
            <div x-show="activeCountry === 'other'" x-cloak x-transition:enter="transition ease-out duration-300 transform opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <!-- Regional Cohort Context Banner -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-5 rounded-2xl bg-teal-50/80 dark:bg-teal-500/10 border border-teal-200 dark:border-teal-500/30 mb-8 shadow-xs">
                    <div class="flex items-start sm:items-center gap-3">
                        <span class="text-3xl">🌍</span>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white">Global & Pan-African Expansion Pipeline</h3>
                                <span class="px-2.5 py-0.5 rounded-full bg-teal-600 text-white text-[10px] font-black uppercase tracking-wider">Regional Inquiries Open</span>
                            </div>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">
                                For capital allocators and strategic partners in the UK, Europe, UAE, and other African nations.
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="setCountry('ghana')" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-amber-500 hover:bg-brand-500 text-black font-black text-xs uppercase tracking-wider shadow-sm transition-all whitespace-nowrap cursor-pointer">
                        <span>🇬🇭 View Live Ghana Offering</span>
                        <span>→</span>
                    </button>
                </div>

                <!-- International Hubs Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
                    <!-- UK & Europe -->
                    <div class="p-8 rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-black uppercase tracking-wider text-teal-600 dark:text-teal-400">UK & Europe Hub</span>
                                <span class="text-[10px] font-bold text-teal-600 dark:text-teal-400 bg-teal-50 dark:bg-teal-900/30 px-2 py-0.5 rounded-full">GBP / EUR</span>
                            </div>
                            <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-2">🇬🇧 United Kingdom & EU</h2>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mb-6 leading-relaxed">
                                Formatted for London family offices and African diaspora syndicate allocators seeking tax-efficient cross-border returns.
                            </p>

                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 mb-6">
                                <div class="text-2xl font-black text-slate-900 dark:text-white">From £50,000 GBP</div>
                                <div class="text-xs text-teal-600 dark:text-teal-400 font-bold mt-0.5">Approx. €60,000 EUR / $65,000 USD</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Multi-currency wire routing</div>
                            </div>

                            <ul class="space-y-3 text-xs text-slate-600 dark:text-slate-300 mb-8">
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Direct GBP / EUR / USD dividend settlement</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Diaspora syndicate co-investment framework</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Semi-annual in-person London investor briefings</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Cross-border tax-efficient SPV scheduling</span>
                                </li>
                            </ul>
                        </div>

                        <a href="/investor/contact?country=United+Kingdom&interested_tranche=General+Inquiry" class="w-full py-3.5 text-center rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-900 dark:text-white font-black text-xs uppercase tracking-wider transition-all">
                            Register UK/EU Interest →
                        </a>
                    </div>

                    <!-- UAE & Gulf Region -->
                    <div class="p-8 rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-black uppercase tracking-wider text-teal-600 dark:text-teal-400">Gulf / MENA Hub</span>
                                <span class="text-[10px] font-bold text-teal-600 dark:text-teal-400 bg-teal-50 dark:bg-teal-900/30 px-2 py-0.5 rounded-full">AED / USD</span>
                            </div>
                            <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-2">🇦🇪 UAE & Gulf Region</h2>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mb-6 leading-relaxed">
                                Tailored for Dubai & Abu Dhabi venture studios, sovereign allocators, and family offices seeking high-yield frontier tech.
                            </p>

                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 mb-6">
                                <div class="text-2xl font-black text-slate-900 dark:text-white">From $100,000 USD</div>
                                <div class="text-xs text-teal-600 dark:text-teal-400 font-bold mt-0.5">Approx. 367,000 AED</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">DIFC / ADGM co-structuring</div>
                            </div>

                            <ul class="space-y-3 text-xs text-slate-600 dark:text-slate-300 mb-8">
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Direct SWIFT wire via UAE banking institutions</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Frontier mobility infrastructure yield diversification</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Sharia-compliant asset leaseback structuring option</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Direct access to NDFG LLC executive management</span>
                                </li>
                            </ul>
                        </div>

                        <a href="/investor/contact?country=Other&interested_tranche=General+Inquiry" class="w-full py-3.5 text-center rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-900 dark:text-white font-black text-xs uppercase tracking-wider transition-all">
                            Register UAE/Gulf Interest →
                        </a>
                    </div>

                    <!-- Pan-African Frontier -->
                    <div class="p-8 rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-black uppercase tracking-wider text-teal-600 dark:text-teal-400">Pan-African Hub</span>
                                <span class="text-[10px] font-bold text-teal-600 dark:text-teal-400 bg-teal-50 dark:bg-teal-900/30 px-2 py-0.5 rounded-full">Frontier Markets</span>
                            </div>
                            <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-2">🇳🇬 🇰🇪 Nigeria & Kenya</h2>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mb-6 leading-relaxed">
                                Strategic regional co-investment for future multi-city expansion cohorts in Lagos, Abuja, Nairobi, and regional mega-corridors.
                            </p>

                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 mb-6">
                                <div class="text-2xl font-black text-slate-900 dark:text-white">Regional Syndicates</div>
                                <div class="text-xs text-teal-600 dark:text-teal-400 font-bold mt-0.5">Flexible sovereign / private tiers</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Multi-market expansion pipeline</div>
                            </div>

                            <ul class="space-y-3 text-xs text-slate-600 dark:text-slate-300 mb-8">
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Continental telemetry and multi-currency payouts</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Sovereign mobility operating partner consideration</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Priority expansion cohort equity rights</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Unified Year 3 sovereign liquidity framework</span>
                                </li>
                            </ul>
                        </div>

                        <a href="/investor/contact?country=Other+Africa&interested_tranche=General+Inquiry" class="w-full py-3.5 text-center rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-900 dark:text-white font-black text-xs uppercase tracking-wider transition-all">
                            Register Pan-African Interest →
                        </a>
                    </div>
                </div>

                <!-- Global Notice -->
                <div class="rounded-2xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 p-6 text-center max-w-2xl mx-auto text-xs text-slate-600 dark:text-slate-400 shadow-sm">
                    🌍 <strong>International Placement Note:</strong> All cross-border inquiries outside Ghana are handled through NDFG LLC legal and compliance counsel. Our team will review your jurisdictional requirements and respond within 12 business hours.
                </div>
            </div>

        </div>
    </div>
</x-layout>
