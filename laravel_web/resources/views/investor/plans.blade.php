<x-layout title="Investment Plans & Tranches | RideMyCars NDFG LLC">
    <x-investor-nav />

    <div class="min-h-screen bg-slate-50 dark:bg-[#0b0f17] text-slate-900 dark:text-white relative overflow-hidden transition-colors selection:bg-brand-500 selection:text-black">
        <!-- Ambient glows -->
        <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[850px] h-[450px] bg-gradient-to-tr from-amber-500/10 via-brand-500/5 to-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl 2xl:max-w-[1600px] w-full mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 pt-12 sm:pt-16 pb-20 relative z-10"
             x-data="{ 
                 activeCountry: (new URLSearchParams(window.location.search).get('country') || (new URLSearchParams(window.location.search).get('state') || 'california')).toLowerCase(),
                 activeUsState: (new URLSearchParams(window.location.search).get('state') || 'california').toLowerCase(),
                 setCountry(c) {
                     this.activeCountry = c;
                     if (c === 'california' || c === 'atlanta' || c === 'georgia') {
                         this.activeUsState = c;
                     }
                     if (window.history.pushState) {
                         const newUrl = window.location.pathname + '?country=' + c;
                         window.history.replaceState({ path: newUrl }, '', newUrl);
                     }
                 },
                 setUsState(s) {
                     this.activeUsState = s;
                     this.activeCountry = s;
                     if (window.history.pushState) {
                         const newUrl = window.location.pathname + '?country=' + s;
                         window.history.replaceState({ path: newUrl }, '', newUrl);
                     }
                 }
             }">
            <!-- Header -->
            <div class="text-center max-w-3xl mx-auto mb-10">
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-300 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300 text-xs font-black uppercase tracking-wider mb-3 shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Active Capital Commitment Allocations</span>
                </div>
                <h1 class="text-3xl sm:text-5xl font-black text-slate-900 dark:text-white tracking-tight">Structured Investment Tranches</h1>
                <p class="text-base sm:text-lg text-slate-600 dark:text-slate-300 mt-4 leading-relaxed font-medium">
                    Investment opportunities are now officially open across <strong>California</strong>, <strong>Atlanta</strong>, and <strong>Georgia</strong> starting at $200,000 USD (4%), $500,000 USD (11%), and $1 Million USD (22%), alongside our active <strong>Ghana</strong> single cohort.
                </p>
            </div>

            <!-- Country & State Selection Tabs: California, Atlanta, Georgia, Ghana, USA Overview, South Africa, Other Countries -->
            <div class="flex justify-center mb-10">
                <div class="inline-flex flex-wrap justify-center p-1.5 rounded-2xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-md gap-1.5 sm:gap-2 max-w-full">
                    <!-- California Tab (Open Live Offering) -->
                    <button type="button" 
                            @click="setCountry('california')" 
                            :class="activeCountry === 'california' 
                                ? 'bg-amber-500 text-black shadow-md shadow-amber-500/20' 
                                : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800'"
                            class="flex items-center gap-2 px-3.5 sm:px-4 py-2.5 rounded-xl font-black text-xs sm:text-sm tracking-wide transition-all whitespace-nowrap cursor-pointer">
                        <span class="text-base sm:text-lg">🐻</span>
                        <span>California</span>
                        <span :class="activeCountry === 'california' ? 'bg-black/20 text-black' : 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30'" 
                              class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Live Offering
                        </span>
                    </button>

                    <!-- Atlanta Tab (Open Live Offering) -->
                    <button type="button" 
                            @click="setCountry('atlanta')" 
                            :class="activeCountry === 'atlanta' 
                                ? 'bg-amber-500 text-black shadow-md shadow-amber-500/20' 
                                : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800'"
                            class="flex items-center gap-2 px-3.5 sm:px-4 py-2.5 rounded-xl font-black text-xs sm:text-sm tracking-wide transition-all whitespace-nowrap cursor-pointer">
                        <span class="text-base sm:text-lg">🍑</span>
                        <span>Atlanta</span>
                        <span :class="activeCountry === 'atlanta' ? 'bg-black/20 text-black' : 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30'" 
                              class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Live Offering
                        </span>
                    </button>

                    <!-- Georgia Tab (Open Live Offering) -->
                    <button type="button" 
                            @click="setCountry('georgia')" 
                            :class="activeCountry === 'georgia' 
                                ? 'bg-amber-500 text-black shadow-md shadow-amber-500/20' 
                                : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800'"
                            class="flex items-center gap-2 px-3.5 sm:px-4 py-2.5 rounded-xl font-black text-xs sm:text-sm tracking-wide transition-all whitespace-nowrap cursor-pointer">
                        <span class="text-base sm:text-lg">🏛️</span>
                        <span>Georgia</span>
                        <span :class="activeCountry === 'georgia' ? 'bg-black/20 text-black' : 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30'" 
                              class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Live Offering
                        </span>
                    </button>

                    <!-- Ghana Tab (Currently Live & Offering) -->
                    <button type="button" 
                            @click="setCountry('ghana')" 
                            :class="activeCountry === 'ghana' 
                                ? 'bg-amber-500 text-black shadow-md shadow-amber-500/20' 
                                : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800'"
                            class="flex items-center gap-2 px-3.5 sm:px-4 py-2.5 rounded-xl font-black text-xs sm:text-sm tracking-wide transition-all whitespace-nowrap cursor-pointer">
                        <span class="text-base sm:text-lg">🇬🇭</span>
                        <span>Ghana</span>
                        <span :class="activeCountry === 'ghana' ? 'bg-black/20 text-black' : 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30'" 
                              class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Live Offering
                        </span>
                    </button>

                    <!-- All USA States Tab -->
                    <button type="button" 
                            @click="setCountry('usa')" 
                            :class="activeCountry === 'usa' 
                                ? 'bg-amber-500 text-black shadow-md shadow-amber-500/20' 
                                : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800'"
                            class="flex items-center gap-2 px-3.5 sm:px-4 py-2.5 rounded-xl font-black text-xs sm:text-sm tracking-wide transition-all whitespace-nowrap cursor-pointer">
                        <span class="text-base sm:text-lg">🇺🇸</span>
                        <span>All USA States</span>
                        <span :class="activeCountry === 'usa' ? 'bg-black/20 text-black' : 'bg-blue-500/15 text-blue-600 dark:text-blue-400 border border-blue-500/30'" 
                              class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full">
                            3 States Open
                        </span>
                    </button>

                    <!-- South Africa Tab -->
                    <button type="button" 
                            @click="setCountry('south-africa')" 
                            :class="activeCountry === 'south-africa' 
                                ? 'bg-amber-500 text-black shadow-md shadow-amber-500/20' 
                                : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800'"
                            class="flex items-center gap-2 px-3.5 sm:px-4 py-2.5 rounded-xl font-black text-xs sm:text-sm tracking-wide transition-all whitespace-nowrap cursor-pointer">
                        <span class="text-base sm:text-lg">🇿🇦</span>
                        <span>South Africa</span>
                        <span :class="activeCountry === 'south-africa' ? 'bg-black/20 text-black' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400'" 
                              class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full">
                            Pre-Reg
                        </span>
                    </button>

                    <!-- Other Countries Tab -->
                    <button type="button" 
                            @click="setCountry('other')" 
                            :class="activeCountry === 'other' 
                                ? 'bg-amber-500 text-black shadow-md shadow-amber-500/20' 
                                : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800'"
                            class="flex items-center gap-2 px-3.5 sm:px-4 py-2.5 rounded-xl font-black text-xs sm:text-sm tracking-wide transition-all whitespace-nowrap cursor-pointer">
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

                @php
                    $displayPlans = (isset($plans) && $plans->isNotEmpty()) ? $plans : collect([
                        (object) [
                            'tranche_code' => 'A',
                            'tier_name' => 'Seed Tier',
                            'capital_commitment_ghc' => 720000.00,
                            'capital_commitment_usd' => 60000.00,
                            'equity_percentage' => 7.00,
                            'description' => 'Ideal for private individual angels entering early-stage West African technology and mobility infrastructure.',
                            'perks' => [
                                'Quarterly multi-currency dividend distributions',
                                '24/7 Access to Live Micro-Transaction Ticker',
                                'Quarterly financial and fleet operational updates',
                                'Standard Year 3 structured buyout option',
                            ],
                        ],
                        (object) [
                            'tranche_code' => 'D',
                            'tier_name' => 'Wealth builder',
                            'capital_commitment_ghc' => 120000.00,
                            'capital_commitment_usd' => 10000.00,
                            'equity_percentage' => 1.20,
                            'description' => 'Accessible entry for emerging wealth builders seeking steady micro-fleet yields with verified 1.20% equity stake.',
                            'perks' => [
                                '1.20% fixed non-dilutive single cohort equity stake',
                                'Quarterly multi-currency dividend distributions',
                                '24/7 Access to Live Micro-Transaction Ticker',
                                'Standard Year 3 structured buyout option',
                            ],
                        ],
                        (object) [
                            'tranche_code' => 'B',
                            'tier_name' => 'Growth Tier',
                            'capital_commitment_ghc' => 1440000.00,
                            'capital_commitment_usd' => 120000.00,
                            'equity_percentage' => 14.00,
                            'description' => 'Engineered for family offices and sophisticated syndicate leads seeking prime yield velocity with enhanced liquidity provisions.',
                            'perks' => [
                                'Accelerated priority quarterly dividend wires',
                                'Full 3-Year Master Cash Ledger telemetry data',
                                'Direct line to Ride My Cars (Ghana) compliance desk',
                                'Enhanced Year 3 buyout formula with clawback reserve',
                            ],
                        ],
                        (object) [
                            'tranche_code' => 'C',
                            'tier_name' => 'Venture Tier',
                            'capital_commitment_ghc' => 2640000.00,
                            'capital_commitment_usd' => 220000.00,
                            'equity_percentage' => 22.00,
                            'description' => 'Designed for venture funds, institutional syndicates, and strategic sovereign entities looking for substantial equity leverage.',
                            'perks' => [
                                'Highest quarterly dividend pool participation',
                                'Advisory board observer seat consideration',
                                'Direct wire SWIFT & MoMo escrow routing priority',
                                'Institutional buyout multiple at Year 3 exit',
                            ],
                        ],
                    ]);
                    $plansGridClass = count($displayPlans) >= 4 ? 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4' : (count($displayPlans) === 2 ? 'grid-cols-1 md:grid-cols-2 max-w-4xl mx-auto' : 'grid-cols-1 md:grid-cols-3');
                @endphp

                <!-- Ghana Tranche Comparison Cards -->
                <div class="grid {{ $plansGridClass }} gap-8 mb-16">
                    @foreach($displayPlans as $plan)
                        @php
                            $isFeatured = ($plan->tranche_code === 'B' || ($loop->count <= 3 && $loop->iteration === 2));
                            $perks = is_array($plan->perks) ? $plan->perks : [
                                'Quarterly multi-currency dividend distributions',
                                '24/7 Access to Live Micro-Transaction Ticker',
                                'Ride My Cars (Ghana) escrow custody',
                                'Standard Year 3 structured buyout option',
                            ];
                        @endphp
                        <div class="p-8 rounded-3xl bg-white dark:bg-[#131926] border {{ $isFeatured ? 'border-2 border-amber-500 ring-4 ring-amber-500/15 shadow-xl shadow-amber-500/15' : 'border-slate-200 dark:border-slate-700 shadow-sm' }} hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between relative">
                            @if($isFeatured)
                                <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-4 py-0.5 rounded-full bg-amber-500 text-black font-black text-[10px] uppercase tracking-wider shadow-xs">
                                    ★ Most Popular Growth Tier
                                </div>
                            @endif

                            <div>
                                <div class="flex items-center justify-between mb-2 {{ $isFeatured ? 'mt-1' : '' }}">
                                    <span class="text-xs font-black uppercase tracking-wider {{ $isFeatured ? 'text-amber-600 dark:text-amber-400' : 'text-slate-600 dark:text-slate-400' }}">{{ $plan->tier_name }}</span>
                                    <span class="text-[10px] font-bold text-slate-500">Active Offering</span>
                                </div>
                                <div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden mb-4">
                                    <div class="bg-amber-500 h-full rounded-full" style="width: {{ $plan->tranche_code === 'A' ? '85%' : ($plan->tranche_code === 'B' ? '62%' : ($plan->tranche_code === 'D' ? '78%' : '44%')) }};"></div>
                                </div>

                                <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-2">Tranche {{ $plan->tranche_code }}</h2>
                                <p class="text-xs text-slate-600 dark:text-slate-300 mb-6 leading-relaxed">
                                    {{ $plan->description ?: ($plan->summary_headline ?: 'Participation in Ride My Cars fleet mobility infrastructure with verified equity allocation.') }}
                                </p>

                                <div class="p-4 rounded-2xl {{ $isFeatured ? 'bg-amber-50/50 dark:bg-slate-800/40 border border-amber-500/30' : 'bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700' }} mb-6">
                                    <div class="text-3xl font-black text-slate-900 dark:text-white">{{ number_format($plan->capital_commitment_ghc, 0) }} GHC</div>
                                    <div class="text-xs text-amber-600 dark:text-amber-400 font-bold mt-0.5">Approx. ${{ number_format($plan->capital_commitment_usd, 0) }} USD</div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Single upfront commitment</div>
                                </div>

                                <div class="p-3.5 rounded-2xl {{ $isFeatured ? 'bg-amber-500 text-black shadow-xs' : 'bg-amber-50 dark:bg-amber-500/15 border border-amber-200 dark:border-amber-500/30' }} text-center mb-6">
                                    <span class="text-xs {{ $isFeatured ? 'font-bold opacity-80' : 'text-slate-600 dark:text-slate-300' }}">Equity Allocation:</span>
                                    <div class="text-xl font-black {{ $isFeatured ? '' : 'text-amber-600 dark:text-amber-400' }}">{{ number_format($plan->equity_percentage, 2) }}% Fixed Stake</div>
                                </div>

                                <ul class="space-y-3 text-xs text-slate-600 dark:text-slate-300 mb-8">
                                    @foreach(array_slice($perks, 0, 4) as $perk)
                                        <li class="flex items-start gap-2">
                                            <span class="text-emerald-500 font-black">✓</span>
                                            <span>{{ $perk }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            <a href="/investor/register?tranche={{ $plan->tranche_code }}" class="w-full py-3.5 text-center rounded-xl {{ $isFeatured ? 'bg-amber-500 hover:bg-brand-500 text-black shadow-lg shadow-amber-500/25' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-900 dark:text-white' }} font-black text-xs uppercase tracking-wider transition-all">
                                Apply for Tranche {{ $plan->tranche_code }} →
                            </a>
                        </div>
                    @endforeach
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
            <!-- 3. CALIFORNIA VIEW (ACTIVE OFFERING - CPUC TNC AUTHORIZED)         -->
            <!-- ================================================================= -->
            <div x-show="activeCountry === 'california'" x-cloak x-transition:enter="transition ease-out duration-300 transform opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <!-- Regional Cohort Context Banner -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-5 rounded-2xl bg-blue-50/80 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 mb-8 shadow-xs">
                    <div class="flex items-start sm:items-center gap-3">
                        <span class="text-3xl">🐻</span>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white">Active US State Allocation: California Commercial Fleet Hub</h3>
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-500 text-black text-[10px] font-black uppercase tracking-wider inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-black animate-pulse"></span>
                                    Now Offering • CPUC Authorized
                                </span>
                            </div>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">
                                Direct capital deployment into California metropolitan corridors (Los Angeles, San Francisco, San Diego, Silicon Valley). Governed under Delaware NDFG LLC Operating Agreement and CPUC Decision 13-09-045.
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" @click="setCountry('atlanta')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition-all cursor-pointer">
                            <span>🍑 View Atlanta</span>
                        </button>
                        <button type="button" @click="setCountry('georgia')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition-all cursor-pointer">
                            <span>🏛️ View Georgia</span>
                        </button>
                    </div>
                </div>

                <!-- California Tranche Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
                    <!-- CA Tier 1: $200k / 4% -->
                    <div class="p-8 rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-black uppercase tracking-wider text-blue-600 dark:text-blue-400">Seed Tier (California)</span>
                                <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-600/30 px-2 py-0.5 rounded-full">Active Allocation</span>
                            </div>
                            <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-2">Tranche CA-1</h2>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mb-6 leading-relaxed">
                                Regional angel entry for California commercial mobility operations with steady quarterly USD dividend distributions and live fleet telemetry.
                            </p>

                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 mb-6">
                                <div class="text-3xl font-black text-slate-900 dark:text-white">$200,000 USD</div>
                                <div class="text-xs text-blue-600 dark:text-blue-400 font-bold mt-0.5">Approx. 2,400,000 GHC equivalent</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Single upfront commitment • Domestic Fedwire</div>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-blue-50 dark:bg-blue-500/15 border border-blue-200 dark:border-blue-500/30 text-center mb-6">
                                <span class="text-xs text-slate-600 dark:text-slate-300">Equity Allocation:</span>
                                <div class="text-xl font-black text-blue-600 dark:text-blue-400">4.0% Fixed Stake</div>
                            </div>

                            <ul class="space-y-3 text-xs text-slate-600 dark:text-slate-300 mb-8">
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>4.0% non-dilutive single cohort equity stake</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Quarterly USD dividend wires directly to US bank account</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>24/7 Access to Live California Micro-Transaction Ticker</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>CPUC regulatory fee & safety compliance oversight</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Standard Delaware LLC Year 3 structured buyout option</span>
                                </li>
                            </ul>
                        </div>

                        <a href="/investor/contact?country=California&interested_tranche=California+Tranche+CA-1+($200,000+USD+•+4%+Equity)" class="w-full py-3.5 text-center rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-900 dark:text-white font-black text-xs uppercase tracking-wider transition-all">
                            Apply for California Tier 1 ($200k) →
                        </a>
                    </div>

                    <!-- CA Tier 2: $500k / 11% (Featured) -->
                    <div class="p-8 rounded-3xl bg-white dark:bg-[#131926] border-2 border-blue-500 ring-4 ring-blue-500/15 shadow-xl shadow-blue-500/15 transition-all duration-300 flex flex-col justify-between relative hover:-translate-y-1">
                        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-4 py-0.5 rounded-full bg-blue-600 text-white font-black text-[10px] uppercase tracking-wider shadow-xs">
                            ★ Primary California Syndicate Choice
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2 mt-1">
                                <span class="text-xs font-black uppercase tracking-wider text-blue-600 dark:text-blue-400">Growth Tier (California)</span>
                                <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-2 py-0.5 rounded-full">Syndicate Lead</span>
                            </div>
                            <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-2">Tranche CA-2</h2>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mb-6 leading-relaxed">
                                Prime growth allocation engineered for US angel syndicates and family offices seeking high-yield commercial mobility infrastructure in Southern and Northern California.
                            </p>

                            <div class="p-4 rounded-2xl bg-blue-50/50 dark:bg-slate-800/40 border border-blue-500/30 mb-6">
                                <div class="text-3xl font-black text-slate-900 dark:text-white">$500,000 USD</div>
                                <div class="text-xs text-blue-600 dark:text-blue-400 font-bold mt-0.5">Approx. 6,000,000 GHC equivalent</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Priority single allocation • Direct domestic clearing</div>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-blue-600 text-white text-center mb-6 shadow-xs">
                                <span class="text-xs font-bold opacity-80">Equity Allocation:</span>
                                <div class="text-xl font-black">11.0% Fixed Stake</div>
                            </div>

                            <ul class="space-y-3 text-xs text-slate-600 dark:text-slate-300 mb-8">
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-600 dark:text-blue-400 font-black">✓</span>
                                    <span>11.0% non-dilutive single cohort equity stake</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-600 dark:text-blue-400 font-black">✓</span>
                                    <span>Accelerated priority quarterly USD dividend wire clearance</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-600 dark:text-blue-400 font-black">✓</span>
                                    <span>Full 3-Year Master Cash Ledger telemetry data access</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-600 dark:text-blue-400 font-black">✓</span>
                                    <span>Priority capital allocation rights across prime California metro hubs</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-600 dark:text-blue-400 font-black">✓</span>
                                    <span>Enhanced Year 3 buyout formula with clawback reserve</span>
                                </li>
                            </ul>
                        </div>

                        <a href="/investor/contact?country=California&interested_tranche=California+Tranche+CA-2+($500,000+USD+•+11%+Equity)" class="w-full py-3.5 text-center rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-black text-xs uppercase tracking-wider shadow-lg shadow-blue-500/25 transition-all">
                            Apply for California Tier 2 ($500k) →
                        </a>
                    </div>

                    <!-- CA Tier 3: $1M / 22% -->
                    <div class="p-8 rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Venture Tier (California)</span>
                                <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-full">Institutional Fund</span>
                            </div>
                            <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-2">Tranche CA-3</h2>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mb-6 leading-relaxed">
                                Flagship institutional allocation designed for venture capital funds and sovereign allocators seeking maximum equity leverage and strategic presence in California.
                            </p>

                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 mb-6">
                                <div class="text-3xl font-black text-slate-900 dark:text-white">$1,000,000 USD</div>
                                <div class="text-xs text-emerald-600 dark:text-emerald-400 font-bold mt-0.5">Approx. 12,000,000 GHC equivalent</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Maximum single commitment • Direct Fedwire clearance</div>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-200 dark:border-emerald-500/30 text-center mb-6">
                                <span class="text-xs text-slate-600 dark:text-slate-300">Equity Allocation:</span>
                                <div class="text-xl font-black text-emerald-600 dark:text-emerald-400">22.0% Fixed Stake</div>
                            </div>

                            <ul class="space-y-3 text-xs text-slate-600 dark:text-slate-300 mb-8">
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>22.0% non-dilutive single cohort equity stake</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Maximum quarterly dividend pool participation in USD</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Advisory Board observer seat consideration for California operations</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Dedicated domestic wire clearing desk with 5-day escrow settlement</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Institutional buyout multiple at Year 3 exit / secondary liquidation</span>
                                </li>
                            </ul>
                        </div>

                        <a href="/investor/contact?country=California&interested_tranche=California+Tranche+CA-3+($1,000,000+USD+•+22%+Equity)" class="w-full py-3.5 text-center rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-900 dark:text-white font-black text-xs uppercase tracking-wider transition-all">
                            Apply for California Tier 3 ($1M) →
                        </a>
                    </div>
                </div>

                <!-- California Notice -->
                <div class="rounded-2xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 p-6 text-center max-w-2xl mx-auto text-xs text-slate-600 dark:text-slate-400 shadow-sm">
                    🐻 <strong>California CPUC Regulatory Protocol:</strong> Allocations are governed under Delaware NDFG LLC Operating Agreement and California Public Utilities Commission (CPUC Decision 13-09-045). Capital commitments settle into designated Delaware LLC escrow custody within five (5) business days following accreditation approval.
                </div>
            </div>

            <!-- ================================================================= -->
            <!-- 4. ATLANTA VIEW (ACTIVE OFFERING - AIRPORT & METRO TRANSIT)        -->
            <!-- ================================================================= -->
            <div x-show="activeCountry === 'atlanta'" x-cloak x-transition:enter="transition ease-out duration-300 transform opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <!-- Regional Cohort Context Banner -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-5 rounded-2xl bg-amber-50/80 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 mb-8 shadow-xs">
                    <div class="flex items-start sm:items-center gap-3">
                        <span class="text-3xl">🍑</span>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white">Active US Market Allocation: Atlanta Commercial Fleet Hub</h3>
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-500 text-black text-[10px] font-black uppercase tracking-wider inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-black animate-pulse"></span>
                                    Now Offering • Airport Corridors Open
                                </span>
                            </div>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">
                                Targeted capital commitment into Atlanta's booming commercial mobility market, including Hartsfield-Jackson international transit corridors, Downtown, Midtown, and Buckhead executive dispatch channels.
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" @click="setCountry('california')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition-all cursor-pointer">
                            <span>🐻 View California</span>
                        </button>
                        <button type="button" @click="setCountry('georgia')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition-all cursor-pointer">
                            <span>🏛️ View Georgia</span>
                        </button>
                    </div>
                </div>

                <!-- Atlanta Tranche Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
                    <!-- ATL Tier 1: $200k / 4% -->
                    <div class="p-8 rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-black uppercase tracking-wider text-amber-600 dark:text-amber-400">Seed Tier (Atlanta)</span>
                                <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-600/30 px-2 py-0.5 rounded-full">Active Allocation</span>
                            </div>
                            <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-2">Tranche ATL-1</h2>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mb-6 leading-relaxed">
                                Regional angel entry for Atlanta metro operations with steady quarterly dividend yields and host-owned mobility agility.
                            </p>

                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 mb-6">
                                <div class="text-3xl font-black text-slate-900 dark:text-white">$200,000 USD</div>
                                <div class="text-xs text-amber-600 dark:text-amber-400 font-bold mt-0.5">Approx. 2,400,000 GHC equivalent</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Single upfront commitment • Domestic Fedwire</div>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-500/15 border border-amber-200 dark:border-amber-500/30 text-center mb-6">
                                <span class="text-xs text-slate-600 dark:text-slate-300">Equity Allocation:</span>
                                <div class="text-xl font-black text-amber-600 dark:text-amber-400">4.0% Fixed Stake</div>
                            </div>

                            <ul class="space-y-3 text-xs text-slate-600 dark:text-slate-300 mb-8">
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>4.0% non-dilutive single cohort equity stake</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Quarterly USD dividend distributions to US bank account</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>24/7 Access to Atlanta regional trip telemetry and micro-transaction feed</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Hartsfield-Jackson airport and metro corridor fleet data</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Standard Delaware LLC Year 3 structured buyout option</span>
                                </li>
                            </ul>
                        </div>

                        <a href="/investor/contact?country=Atlanta&interested_tranche=Atlanta+Tranche+ATL-1+($200,000+USD+•+4%+Equity)" class="w-full py-3.5 text-center rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-900 dark:text-white font-black text-xs uppercase tracking-wider transition-all">
                            Apply for Atlanta Tier 1 ($200k) →
                        </a>
                    </div>

                    <!-- ATL Tier 2: $500k / 11% (Featured) -->
                    <div class="p-8 rounded-3xl bg-white dark:bg-[#131926] border-2 border-amber-500 ring-4 ring-amber-500/15 shadow-xl shadow-amber-500/15 transition-all duration-300 flex flex-col justify-between relative hover:-translate-y-1">
                        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-4 py-0.5 rounded-full bg-amber-500 text-black font-black text-[10px] uppercase tracking-wider shadow-xs">
                            ★ Primary Atlanta Growth Allocation
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2 mt-1">
                                <span class="text-xs font-black uppercase tracking-wider text-amber-600 dark:text-amber-400">Growth Tier (Atlanta)</span>
                                <span class="text-[10px] font-bold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/30 px-2 py-0.5 rounded-full">Syndicate Lead</span>
                            </div>
                            <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-2">Tranche ATL-2</h2>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mb-6 leading-relaxed">
                                Engineered for regional investment syndicates and private wealth offices targeting high-margin Atlanta fleet operations across commercial districts and logistics hubs.
                            </p>

                            <div class="p-4 rounded-2xl bg-amber-50/50 dark:bg-slate-800/40 border border-amber-500/30 mb-6">
                                <div class="text-3xl font-black text-slate-900 dark:text-white">$500,000 USD</div>
                                <div class="text-xs text-amber-600 dark:text-amber-400 font-bold mt-0.5">Approx. 6,000,000 GHC equivalent</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Priority single allocation • Direct domestic wire</div>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-amber-500 text-black text-center mb-6 shadow-xs">
                                <span class="text-xs font-bold opacity-80">Equity Allocation:</span>
                                <div class="text-xl font-black">11.0% Fixed Stake</div>
                            </div>

                            <ul class="space-y-3 text-xs text-slate-600 dark:text-slate-300 mb-8">
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 dark:text-amber-400 font-black">✓</span>
                                    <span>11.0% non-dilutive single cohort equity stake</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 dark:text-amber-400 font-black">✓</span>
                                    <span>Accelerated priority quarterly USD wire clearance</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 dark:text-amber-400 font-black">✓</span>
                                    <span>Full Atlanta master cash ledger data access</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 dark:text-amber-400 font-black">✓</span>
                                    <span>Priority vehicle allocation on Hartsfield-Jackson airport fleet routes</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 dark:text-amber-400 font-black">✓</span>
                                    <span>Enhanced Year 3 structured buyout terms</span>
                                </li>
                            </ul>
                        </div>

                        <a href="/investor/contact?country=Atlanta&interested_tranche=Atlanta+Tranche+ATL-2+($500,000+USD+•+11%+Equity)" class="w-full py-3.5 text-center rounded-xl bg-amber-500 hover:bg-amber-400 text-black font-black text-xs uppercase tracking-wider shadow-lg shadow-amber-500/25 transition-all">
                            Apply for Atlanta Tier 2 ($500k) →
                        </a>
                    </div>

                    <!-- ATL Tier 3: $1M / 22% -->
                    <div class="p-8 rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Venture Tier (Atlanta)</span>
                                <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-full">Institutional Fund</span>
                            </div>
                            <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-2">Tranche ATL-3</h2>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mb-6 leading-relaxed">
                                Major institutional allocation for Southeast venture groups and private equity funds seeking governance presence and maximum returns in Atlanta mobility.
                            </p>

                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 mb-6">
                                <div class="text-3xl font-black text-slate-900 dark:text-white">$1,000,000 USD</div>
                                <div class="text-xs text-emerald-600 dark:text-emerald-400 font-bold mt-0.5">Approx. 12,000,000 GHC equivalent</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Maximum single commitment • Direct Fedwire clearance</div>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-200 dark:border-emerald-500/30 text-center mb-6">
                                <span class="text-xs text-slate-600 dark:text-slate-300">Equity Allocation:</span>
                                <div class="text-xl font-black text-emerald-600 dark:text-emerald-400">22.0% Fixed Stake</div>
                            </div>

                            <ul class="space-y-3 text-xs text-slate-600 dark:text-slate-300 mb-8">
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>22.0% non-dilutive single cohort equity stake</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Maximum quarterly dividend pool participation across Atlanta routes</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Atlanta Advisory Board observer seat consideration</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Dedicated escrow settlement desk with 5-day priority clearance</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Premium institutional buyout multiple at Year 3 exit</span>
                                </li>
                            </ul>
                        </div>

                        <a href="/investor/contact?country=Atlanta&interested_tranche=Atlanta+Tranche+ATL-3+($1,000,000+USD+•+22%+Equity)" class="w-full py-3.5 text-center rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-900 dark:text-white font-black text-xs uppercase tracking-wider transition-all">
                            Apply for Atlanta Tier 3 ($1M) →
                        </a>
                    </div>
                </div>

                <!-- Atlanta Notice -->
                <div class="rounded-2xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 p-6 text-center max-w-2xl mx-auto text-xs text-slate-600 dark:text-slate-400 shadow-sm">
                    🍑 <strong>Atlanta Protocol:</strong> Governed under NDFG LLC (Delaware) and City of Atlanta commercial transportation standards. Direct commitments execute under SEC Regulation D 506(c) private placement terms with 5-day domestic wire escrow settlement.
                </div>
            </div>

            <!-- ================================================================= -->
            <!-- 5. GEORGIA VIEW (ACTIVE OFFERING - STATEWIDE DPS COMPLIANT)         -->
            <!-- ================================================================= -->
            <div x-show="activeCountry === 'georgia'" x-cloak x-transition:enter="transition ease-out duration-300 transform opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <!-- Regional Cohort Context Banner -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-5 rounded-2xl bg-emerald-50/80 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 mb-8 shadow-xs">
                    <div class="flex items-start sm:items-center gap-3">
                        <span class="text-3xl">🏛️</span>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white">Active US State Allocation: State of Georgia Commercial Fleet Network</h3>
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-500 text-black text-[10px] font-black uppercase tracking-wider inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-black animate-pulse"></span>
                                    Now Offering • Georgia DPS Compliant
                                </span>
                            </div>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">
                                Statewide fleet mobility deployment under statutory authority of the Georgia Department of Public Safety (O.C.G.A. § 40-1-193), connecting Savannah, Augusta, Columbus, Athens, and statewide logistics corridors.
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" @click="setCountry('california')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition-all cursor-pointer">
                            <span>🐻 View California</span>
                        </button>
                        <button type="button" @click="setCountry('atlanta')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition-all cursor-pointer">
                            <span>🍑 View Atlanta</span>
                        </button>
                    </div>
                </div>

                <!-- Georgia Tranche Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
                    <!-- GA Tier 1: $200k / 4% -->
                    <div class="p-8 rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Seed Tier (Georgia)</span>
                                <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-600/30 px-2 py-0.5 rounded-full">Active Allocation</span>
                            </div>
                            <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-2">Tranche GA-1</h2>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mb-6 leading-relaxed">
                                Statewide angel allocation for Georgia commercial mobility network with verified quarterly dividend participation and host-owned agility.
                            </p>

                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 mb-6">
                                <div class="text-3xl font-black text-slate-900 dark:text-white">$200,000 USD</div>
                                <div class="text-xs text-emerald-600 dark:text-emerald-400 font-bold mt-0.5">Approx. 2,400,000 GHC equivalent</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Single upfront commitment • Domestic Fedwire</div>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-200 dark:border-emerald-500/30 text-center mb-6">
                                <span class="text-xs text-slate-600 dark:text-slate-300">Equity Allocation:</span>
                                <div class="text-xl font-black text-emerald-600 dark:text-emerald-400">4.0% Fixed Stake</div>
                            </div>

                            <ul class="space-y-3 text-xs text-slate-600 dark:text-slate-300 mb-8">
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>4.0% non-dilutive single cohort equity stake</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Quarterly USD dividend wire distributions to US bank</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Statewide telemetry data and real-time transaction ledger access</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Georgia DPS statutory compliance monitoring</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Standard Delaware LLC Year 3 structured buyout option</span>
                                </li>
                            </ul>
                        </div>

                        <a href="/investor/contact?country=Georgia&interested_tranche=Georgia+Tranche+GA-1+($200,000+USD+•+4%+Equity)" class="w-full py-3.5 text-center rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-900 dark:text-white font-black text-xs uppercase tracking-wider transition-all">
                            Apply for Georgia Tier 1 ($200k) →
                        </a>
                    </div>

                    <!-- GA Tier 2: $500k / 11% (Featured) -->
                    <div class="p-8 rounded-3xl bg-white dark:bg-[#131926] border-2 border-emerald-500 ring-4 ring-emerald-500/15 shadow-xl shadow-emerald-500/15 transition-all duration-300 flex flex-col justify-between relative hover:-translate-y-1">
                        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-4 py-0.5 rounded-full bg-emerald-600 text-white font-black text-[10px] uppercase tracking-wider shadow-xs">
                            ★ Primary Georgia Growth Allocation
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2 mt-1">
                                <span class="text-xs font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Growth Tier (Georgia)</span>
                                <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-full">Syndicate Lead</span>
                            </div>
                            <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-2">Tranche GA-2</h2>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mb-6 leading-relaxed">
                                Designed for syndicates and family offices seeking statewide transportation asset coverage and priority distribution across Georgia regional metros.
                            </p>

                            <div class="p-4 rounded-2xl bg-emerald-50/50 dark:bg-slate-800/40 border border-emerald-500/30 mb-6">
                                <div class="text-3xl font-black text-slate-900 dark:text-white">$500,000 USD</div>
                                <div class="text-xs text-emerald-600 dark:text-emerald-400 font-bold mt-0.5">Approx. 6,000,000 GHC equivalent</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Priority single allocation • Direct domestic wire</div>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-emerald-600 text-white text-center mb-6 shadow-xs">
                                <span class="text-xs font-bold opacity-80">Equity Allocation:</span>
                                <div class="text-xl font-black">11.0% Fixed Stake</div>
                            </div>

                            <ul class="space-y-3 text-xs text-slate-600 dark:text-slate-300 mb-8">
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-600 dark:text-emerald-400 font-black">✓</span>
                                    <span>11.0% non-dilutive single cohort equity stake</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-600 dark:text-emerald-400 font-black">✓</span>
                                    <span>Accelerated priority quarterly USD dividend payouts</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-600 dark:text-emerald-400 font-black">✓</span>
                                    <span>Full 3-Year Georgia Master Cash Ledger access</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-600 dark:text-emerald-400 font-black">✓</span>
                                    <span>Georgia statewide fleet deployment priority across key interstates</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-600 dark:text-emerald-400 font-black">✓</span>
                                    <span>Enhanced Year 3 buyout terms with clawback reserve</span>
                                </li>
                            </ul>
                        </div>

                        <a href="/investor/contact?country=Georgia&interested_tranche=Georgia+Tranche+GA-2+($500,000+USD+•+11%+Equity)" class="w-full py-3.5 text-center rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs uppercase tracking-wider shadow-lg shadow-emerald-500/25 transition-all">
                            Apply for Georgia Tier 2 ($500k) →
                        </a>
                    </div>

                    <!-- GA Tier 3: $1M / 22% -->
                    <div class="p-8 rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Venture Tier (Georgia)</span>
                                <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-full">Institutional Fund</span>
                            </div>
                            <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-2">Tranche GA-3</h2>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mb-6 leading-relaxed">
                                Institutional scale capital commitment for private equity and venture groups seeking dominant equity and advisory representation in Georgia mobility infrastructure.
                            </p>

                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 mb-6">
                                <div class="text-3xl font-black text-slate-900 dark:text-white">$1,000,000 USD</div>
                                <div class="text-xs text-emerald-600 dark:text-emerald-400 font-bold mt-0.5">Approx. 12,000,000 GHC equivalent</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Maximum single commitment • Direct Fedwire clearance</div>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-200 dark:border-emerald-500/30 text-center mb-6">
                                <span class="text-xs text-slate-600 dark:text-slate-300">Equity Allocation:</span>
                                <div class="text-xl font-black text-emerald-600 dark:text-emerald-400">22.0% Fixed Stake</div>
                            </div>

                            <ul class="space-y-3 text-xs text-slate-600 dark:text-slate-300 mb-8">
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>22.0% non-dilutive single cohort equity stake</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Maximum quarterly dividend pool participation across Georgia network</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Advisory board observer representation consideration</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Dedicated Delaware LLC escrow desk with SWIFT/Fedwire clearance</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-black">✓</span>
                                    <span>Institutional buyout multiple at Year 3 exit</span>
                                </li>
                            </ul>
                        </div>

                        <a href="/investor/contact?country=Georgia&interested_tranche=Georgia+Tranche+GA-3+($1,000,000+USD+•+22%+Equity)" class="w-full py-3.5 text-center rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-900 dark:text-white font-black text-xs uppercase tracking-wider transition-all">
                            Apply for Georgia Tier 3 ($1M) →
                        </a>
                    </div>
                </div>

                <!-- Georgia Notice -->
                <div class="rounded-2xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 p-6 text-center max-w-2xl mx-auto text-xs text-slate-600 dark:text-slate-400 shadow-sm">
                    🏛️ <strong>Georgia State Governance Protocol:</strong> Managed under Georgia Department of Public Safety (O.C.G.A. § 40-1-193) and Delaware NDFG LLC Operating Agreement. Following accreditation review, capital commitments settle into Delaware LLC escrow coordinates within five (5) business days.
                </div>
            </div>

            <!-- ================================================================= -->
            <!-- 6. ALL USA STATES OVERVIEW VIEW (CALIFORNIA, ATLANTA, GEORGIA)     -->
            <!-- ================================================================= -->
            <div x-show="activeCountry === 'usa'" x-cloak x-transition:enter="transition ease-out duration-300 transform opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <!-- Regional Cohort Context Banner -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-5 rounded-2xl bg-gradient-to-r from-blue-500/10 via-indigo-500/10 to-emerald-500/10 border border-blue-200 dark:border-blue-500/30 mb-8 shadow-xs">
                    <div class="flex items-start sm:items-center gap-3">
                        <span class="text-3xl">🇺🇸</span>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white">United States Multi-State Expansion: California, Atlanta & Georgia</h3>
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-500 text-black text-[10px] font-black uppercase tracking-wider inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-black animate-pulse"></span>
                                    3 State Allocations Open
                                </span>
                            </div>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">
                                Investment opportunities are now open in California, Atlanta, and Georgia under Delaware NDFG LLC Operating Agreement and SEC Regulation D Rule 506(c). Tier commitments: $200k (4%), $500k (11%), and $1M (22%).
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" @click="setCountry('california')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-black text-xs uppercase tracking-wider shadow-xs transition-all whitespace-nowrap cursor-pointer">
                            <span>🐻 California Details</span>
                            <span>→</span>
                        </button>
                        <button type="button" @click="setCountry('atlanta')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-black font-black text-xs uppercase tracking-wider shadow-xs transition-all whitespace-nowrap cursor-pointer">
                            <span>🍑 Atlanta Details</span>
                            <span>→</span>
                        </button>
                        <button type="button" @click="setCountry('georgia')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs uppercase tracking-wider shadow-xs transition-all whitespace-nowrap cursor-pointer">
                            <span>🏛️ Georgia Details</span>
                            <span>→</span>
                        </button>
                    </div>
                </div>

                <!-- 3 States Side-by-Side Comparison Matrix -->
                <div class="rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 p-8 sm:p-10 mb-16 shadow-md">
                    <div class="text-center max-w-2xl mx-auto mb-8">
                        <span class="text-xs font-black uppercase tracking-wider text-blue-600 dark:text-blue-400">Structured State Allocation Matrix</span>
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-1">Open Opportunities Across 3 US States / Corridors</h3>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-2">All three regions feature standardized single upfront tiers with locked non-dilutive equity stakes.</p>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-700">
                        <table class="w-full text-left text-xs sm:text-sm text-slate-700 dark:text-slate-300 border-collapse">
                            <thead>
                                <tr class="border-b-2 border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 text-[11px] font-black uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                    <th class="p-4 border-r border-slate-200 dark:border-slate-700">Investment Tier</th>
                                    <th class="p-4 border-r border-slate-200 dark:border-slate-700 text-center">Commitment (USD)</th>
                                    <th class="p-4 border-r border-slate-200 dark:border-slate-700 text-center">Fixed Equity</th>
                                    <th class="p-4 border-r border-slate-200 dark:border-slate-700">California Focus</th>
                                    <th class="p-4 border-r border-slate-200 dark:border-slate-700">Atlanta Focus</th>
                                    <th class="p-4">Georgia Focus</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-[#131926]">
                                <tr>
                                    <td class="p-4 font-bold text-slate-900 dark:text-white border-r border-slate-200 dark:border-slate-700">
                                        <div class="font-black text-sm">Tier 1</div>
                                        <div class="text-[10px] text-slate-500 uppercase tracking-wider">Seed Tier / Regional Angel</div>
                                    </td>
                                    <td class="p-4 font-mono font-bold text-slate-900 dark:text-white text-center border-r border-slate-200 dark:border-slate-700 text-base">
                                        $200,000 USD
                                    </td>
                                    <td class="p-4 font-black text-blue-600 dark:text-blue-400 text-center border-r border-slate-200 dark:border-slate-700 text-base bg-blue-50/30 dark:bg-blue-950/20">
                                        4.0%
                                    </td>
                                    <td class="p-4 text-xs border-r border-slate-200 dark:border-slate-700">
                                        Los Angeles & SF Bay transit corridors (CPUC TNC)
                                    </td>
                                    <td class="p-4 text-xs border-r border-slate-200 dark:border-slate-700">
                                        Hartsfield-Jackson airport & Midtown commercial runs
                                    </td>
                                    <td class="p-4 text-xs">
                                        Savannah & statewide key interstate logistics (DPS)
                                    </td>
                                </tr>
                                <tr class="bg-amber-50/30 dark:bg-amber-950/10">
                                    <td class="p-4 font-bold text-slate-900 dark:text-white border-r border-slate-200 dark:border-slate-700">
                                        <div class="font-black text-sm text-amber-700 dark:text-amber-400">Tier 2 ★</div>
                                        <div class="text-[10px] text-slate-500 uppercase tracking-wider">Growth Tier / Syndicate Lead</div>
                                    </td>
                                    <td class="p-4 font-mono font-bold text-slate-900 dark:text-white text-center border-r border-slate-200 dark:border-slate-700 text-base">
                                        $500,000 USD
                                    </td>
                                    <td class="p-4 font-black text-amber-600 dark:text-amber-400 text-center border-r border-slate-200 dark:border-slate-700 text-base bg-amber-50/50 dark:bg-amber-950/30">
                                        11.0%
                                    </td>
                                    <td class="p-4 text-xs border-r border-slate-200 dark:border-slate-700">
                                        Full California master cash ledger & priority fleet hubs
                                    </td>
                                    <td class="p-4 text-xs border-r border-slate-200 dark:border-slate-700">
                                        Priority allocation on airport & commercial fleet lines
                                    </td>
                                    <td class="p-4 text-xs">
                                        Statewide vehicle distribution across major Georgia metros
                                    </td>
                                </tr>
                                <tr>
                                    <td class="p-4 font-bold text-slate-900 dark:text-white border-r border-slate-200 dark:border-slate-700">
                                        <div class="font-black text-sm text-emerald-700 dark:text-emerald-400">Tier 3</div>
                                        <div class="text-[10px] text-slate-500 uppercase tracking-wider">Venture Tier / Institutional</div>
                                    </td>
                                    <td class="p-4 font-mono font-bold text-slate-900 dark:text-white text-center border-r border-slate-200 dark:border-slate-700 text-base">
                                        $1,000,000 USD
                                    </td>
                                    <td class="p-4 font-black text-emerald-600 dark:text-emerald-400 text-center border-r border-slate-200 dark:border-slate-700 text-base bg-emerald-50/30 dark:bg-emerald-950/20">
                                        22.0%
                                    </td>
                                    <td class="p-4 text-xs border-r border-slate-200 dark:border-slate-700">
                                        Observer representation on California regional advisory board
                                    </td>
                                    <td class="p-4 text-xs border-r border-slate-200 dark:border-slate-700">
                                        Dedicated escrow settlement & Atlanta Advisory seat
                                    </td>
                                    <td class="p-4 text-xs">
                                        Dominant equity stake with institutional exit multiple
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                        <button type="button" @click="setCountry('california')" class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-black text-xs uppercase tracking-wider shadow-md shadow-blue-500/25 transition-all">
                            🐻 Explore California Plans ($200k / $500k / $1M) →
                        </button>
                        <button type="button" @click="setCountry('atlanta')" class="px-6 py-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-black font-black text-xs uppercase tracking-wider shadow-md shadow-amber-500/25 transition-all">
                            🍑 Explore Atlanta Plans ($200k / $500k / $1M) →
                        </button>
                        <button type="button" @click="setCountry('georgia')" class="px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs uppercase tracking-wider shadow-md shadow-emerald-500/25 transition-all">
                            🏛️ Explore Georgia Plans ($200k / $500k / $1M) →
                        </button>
                    </div>
                </div>

                <!-- USA Notice -->
                <div class="rounded-2xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 p-6 text-center max-w-2xl mx-auto text-xs text-slate-600 dark:text-slate-400 shadow-sm">
                    🇺🇸 <strong>SEC Regulation D Notice:</strong> Investment allocations for California, Atlanta, and Georgia entities are offered under Rule 506(c) of Regulation D under the Securities Act of 1933. Direct commitments are governed under Delaware NDFG LLC Operating Agreement with domestic USD wire clearing.
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
