<x-layout title="Investment Opportunity | RideMyCars Ghana Super-App Expansion">
    <x-investor-nav />

    <div class="min-h-screen bg-slate-50 dark:bg-[#0b0f17] text-slate-900 dark:text-white relative overflow-hidden transition-colors selection:bg-brand-500 selection:text-black">
        <!-- Ambient glows -->
        <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[850px] h-[450px] bg-gradient-to-tr from-amber-500/10 via-brand-500/5 to-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl 2xl:max-w-[1600px] w-full mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 pt-12 sm:pt-16 pb-20 relative z-10"
             x-data="{
                activeScale: 'y1',
                scaleData: {
                    'y1': { drivers: '500 UPD (Users Per Day)', rev: '50 GHC', dailyGmv: '25,000 GHC', annualGross: '9,125,000 GHC', netMargin: '8,395,000 GHC', qPool: '2,098,750 GHC' },
                    'y2': { drivers: '1,250 UPD (Users Per Day)', rev: '50 GHC', dailyGmv: '62,500 GHC', annualGross: '22,812,500 GHC', netMargin: '20,987,500 GHC', qPool: '5,246,875 GHC' },
                    'y3': { drivers: '2,500 UPD (Users Per Day)', rev: '50 GHC', dailyGmv: '125,000 GHC', annualGross: '45,625,000 GHC', netMargin: '41,975,000 GHC', qPool: '10,493,750 GHC' }
                }
             }">
            <!-- Header -->
            <div class="text-center max-w-3xl mx-auto mb-14">
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-300 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300 text-xs font-black uppercase tracking-wider mb-3">
                    📈 Institutional Offering Details
                </div>
                <h1 class="text-3xl sm:text-5xl font-black text-slate-900 dark:text-white tracking-tight">Ride My Cars Ghana Super-App Offering</h1>
                <p class="text-base sm:text-lg text-slate-600 dark:text-slate-300 mt-4 leading-relaxed font-medium">
                    A high-velocity, asset-light mobility and logistics infrastructure platform deploying a single-cohort expansion across West Africa with institutional oversight by NDFG LLC and Ride My Cars (Ghana).
                </p>

                <!-- Interactive Cohort Horizon Switcher -->
                <div class="mt-6 inline-flex items-center gap-1.5 p-1.5 rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                    <button type="button" @click="activeScale = 'y1'"
                            :class="activeScale === 'y1' ? 'bg-slate-900 text-white dark:bg-amber-500 dark:text-slate-950 font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 font-bold'"
                            class="px-4 py-2 rounded-xl text-xs transition-all cursor-pointer">
                        Year 1 (500 UPD)
                    </button>
                    <button type="button" @click="activeScale = 'y2'"
                            :class="activeScale === 'y2' ? 'bg-slate-900 text-white dark:bg-amber-500 dark:text-slate-950 font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 font-bold'"
                            class="px-4 py-2 rounded-xl text-xs transition-all cursor-pointer">
                        Year 2 (1,250 UPD)
                    </button>
                    <button type="button" @click="activeScale = 'y3'"
                            :class="activeScale === 'y3' ? 'bg-slate-900 text-white dark:bg-amber-500 dark:text-slate-950 font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 font-bold'"
                            class="px-4 py-2 rounded-xl text-xs transition-all cursor-pointer">
                        Year 3 (2,500 UPD)
                    </button>
                </div>
            </div>

            <!-- Pro Forma Cohort Metrics Breakdown -->
            <div class="rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 p-8 sm:p-10 mb-16 shadow-md">
                <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 pb-8 border-b border-slate-200 dark:border-slate-800">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-300 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300 text-xs font-bold mb-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Cohort Unit Economics Engine</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">
                            <span x-text="scaleData[activeScale].drivers">500 UPD (Users Per Day)</span> Expansion Model
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-1 max-w-xl">
                            Modeled upon a baseline of 50 GHC daily basic platform revenue per active user (UPD) with host-owned agility.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-4">
                        <div class="px-5 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-center">
                            <div class="text-[10px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Basic Platform Revenue</div>
                            <div class="text-xl font-black text-amber-600 dark:text-amber-400" x-text="scaleData[activeScale].rev">50 GHC</div>
                        </div>
                        <div class="px-5 py-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-center">
                            <div class="text-[10px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Daily Gross Volume</div>
                            <div class="text-xl font-black text-slate-900 dark:text-white" x-text="scaleData[activeScale].dailyGmv">25,000 GHC</div>
                        </div>
                        <div class="px-5 py-3 rounded-2xl bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-300 dark:border-emerald-500/30 text-center">
                            <div class="text-[10px] font-black uppercase tracking-wider text-emerald-800 dark:text-emerald-300 font-bold">Targeted Net Margin</div>
                            <div class="text-xl font-black text-emerald-600 dark:text-emerald-400">85–92%</div>
                        </div>
                    </div>
                </div>

                <!-- 3-Year Master Cash Ledger Projections Table with High Contrast Borders -->
                <div class="mt-8 overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-700">
                    <table class="w-full text-left text-xs sm:text-sm text-slate-700 dark:text-slate-300 border-collapse">
                        <thead>
                            <tr class="border-b-2 border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 text-[11px] font-black uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                <th class="p-4 border-r border-slate-200 dark:border-slate-700">Metric / Performance Line</th>
                                <th class="p-4 border-r border-slate-200 dark:border-slate-700" :class="{ 'bg-amber-500/10 text-amber-900 dark:text-amber-300 font-black': activeScale === 'y1' }">Year 1 (Deployment)</th>
                                <th class="p-4 border-r border-slate-200 dark:border-slate-700" :class="{ 'bg-amber-500/10 text-amber-900 dark:text-amber-300 font-black': activeScale === 'y2' }">Year 2 (Acceleration)</th>
                                <th class="p-4" :class="{ 'bg-amber-500/10 text-amber-900 dark:text-amber-300 font-black': activeScale === 'y3' }">Year 3 (Maturity & Buyout)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-[#131926]">
                            <tr>
                                <td class="p-4 font-bold text-slate-900 dark:text-white border-r border-slate-200 dark:border-slate-700">Daily Active Users / Units (UPD)</td>
                                <td class="p-4 font-medium border-r border-slate-200 dark:border-slate-700" :class="{ 'bg-amber-50/50 dark:bg-amber-500/5 font-bold': activeScale === 'y1' }">500 UPD</td>
                                <td class="p-4 font-medium border-r border-slate-200 dark:border-slate-700" :class="{ 'bg-amber-50/50 dark:bg-amber-500/5 font-bold': activeScale === 'y2' }">1,250 UPD</td>
                                <td class="p-4 font-medium" :class="{ 'bg-amber-50/50 dark:bg-amber-500/5 font-bold': activeScale === 'y3' }">2,500 UPD</td>
                            </tr>
                            <tr class="bg-slate-50/50 dark:bg-slate-850/40">
                                <td class="p-4 font-bold text-slate-900 dark:text-white border-r border-slate-200 dark:border-slate-700">Daily Platform Revenue Baseline (50 GHC/day)</td>
                                <td class="p-4 font-mono font-bold text-amber-600 dark:text-amber-400 border-r border-slate-200 dark:border-slate-700" :class="{ 'bg-amber-50/50 dark:bg-amber-500/5': activeScale === 'y1' }">25,000 GHC</td>
                                <td class="p-4 font-mono font-bold text-amber-600 dark:text-amber-400 border-r border-slate-200 dark:border-slate-700" :class="{ 'bg-amber-50/50 dark:bg-amber-500/5': activeScale === 'y2' }">62,500 GHC</td>
                                <td class="p-4 font-mono font-bold text-amber-600 dark:text-amber-400" :class="{ 'bg-amber-50/50 dark:bg-amber-500/5': activeScale === 'y3' }">125,000 GHC</td>
                            </tr>
                            <tr>
                                <td class="p-4 font-bold text-slate-900 dark:text-white border-r border-slate-200 dark:border-slate-700">Annual Gross Collection Volume</td>
                                <td class="p-4 font-mono border-r border-slate-200 dark:border-slate-700" :class="{ 'bg-amber-50/50 dark:bg-amber-500/5 font-bold': activeScale === 'y1' }">9,125,000 GHC</td>
                                <td class="p-4 font-mono border-r border-slate-200 dark:border-slate-700" :class="{ 'bg-amber-50/50 dark:bg-amber-500/5 font-bold': activeScale === 'y2' }">22,812,500 GHC</td>
                                <td class="p-4 font-mono" :class="{ 'bg-amber-50/50 dark:bg-amber-500/5 font-bold': activeScale === 'y3' }">45,625,000 GHC</td>
                            </tr>
                            <tr class="bg-emerald-50/80 dark:bg-emerald-950/20 font-semibold border-t-2 border-b-2 border-emerald-300 dark:border-emerald-700/60">
                                <td class="p-4 font-black text-emerald-800 dark:text-emerald-300 border-r border-slate-200 dark:border-slate-700">Targeted Net Platform Margin (85–92%)</td>
                                <td class="p-4 font-bold text-emerald-700 dark:text-emerald-400 font-mono border-r border-slate-200 dark:border-slate-700">8,395,000 GHC</td>
                                <td class="p-4 font-bold text-emerald-700 dark:text-emerald-400 font-mono border-r border-slate-200 dark:border-slate-700">20,987,500 GHC</td>
                                <td class="p-4 font-bold text-emerald-700 dark:text-emerald-400 font-mono">41,975,000 GHC</td>
                            </tr>
                            <tr>
                                <td class="p-4 font-bold text-slate-900 dark:text-white border-r border-slate-200 dark:border-slate-700">Target Quarterly Dividend Pool</td>
                                <td class="p-4 border-r border-slate-200 dark:border-slate-700 font-semibold text-emerald-600 dark:text-emerald-400">2,098,750 GHC / Qtr</td>
                                <td class="p-4 border-r border-slate-200 dark:border-slate-700 font-semibold text-emerald-600 dark:text-emerald-400">5,246,875 GHC / Qtr</td>
                                <td class="p-4 text-emerald-600 dark:text-emerald-400 font-bold">10,493,750 GHC / Qtr + Buyout</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                    * Pro forma metrics and financial projections are estimates based on baseline operating data and do not constitute a guarantee of future exact yields. Prospective partners should carefully analyze the NDFG LLC Operating Agreement and buyout provisions prior to commitment.
                </div>
            </div>

            <!-- Capital Remittance Channels Grid with Top Accent Stripes -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-16">
                <div class="p-6 rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 border-t-4 border-t-indigo-500 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="text-3xl mb-3">💳</div>
                    <h3 class="text-base font-black text-slate-900 dark:text-white mb-2">Stripe Gateway</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                        Instant, secure card settlement supporting Visa, Mastercard, and Apple Pay integrated through the Ride My Cars Stripe portal.
                    </p>
                </div>
                <div class="p-6 rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 border-t-4 border-t-emerald-500 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="text-3xl mb-3">📱</div>
                    <h3 class="text-base font-black text-slate-900 dark:text-white mb-2">MoMo Pay Gateway</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                        Instant push collections via MTN MoMo and Telecel Cash merchant rails for domestic West African capital deployment.
                    </p>
                </div>
                <div class="p-6 rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 border-t-4 border-t-amber-500 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="text-3xl mb-3">🇬🇭</div>
                    <h3 class="text-base font-black text-slate-900 dark:text-white mb-2">Ghanaian Local Banking Rails</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                        Direct local clearing (ACH / GHIPSS) in Ghana Cedis (GHC) to Ride My Cars (Ghana) corporate custody accounts in Accra.
                    </p>
                </div>
                <div class="p-6 rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 border-t-4 border-t-blue-500 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="text-3xl mb-3">🏦</div>
                    <h3 class="text-base font-black text-slate-900 dark:text-white mb-2">Multi-Currency Bank SWIFT Wire</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                        International wires accepted in USD, EUR, GBP, and CAD routed into an institutional escrow account managed under Ride My Cars LLC.
                    </p>
                </div>
            </div>

            <!-- Action Banner -->
            <div class="text-center">
                <a href="/investor/register" class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl bg-brand-500 hover:bg-brand-400 text-black font-black text-sm tracking-wide shadow-md shadow-brand-500/25 transition-all hover:scale-105">
                    <span>Lock in Tranche Allocation</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </a>
            </div>
        </div>
    </div>
</x-layout>
