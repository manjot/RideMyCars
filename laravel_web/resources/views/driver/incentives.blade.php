<x-layout title="Driver Incentive Program | RideMyCars">
    <div class="min-h-screen bg-[#f8fafc] dark:bg-[#070b14] py-8 sm:py-12 transition-colors duration-200 relative overflow-hidden">
        <!-- Ambient Decorative Lighting Effects -->
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none -z-10"></div>
        <div class="absolute top-40 right-10 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none -z-10"></div>
        <div class="absolute top-96 left-10 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl pointer-events-none -z-10"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Breadcrumb Navigation -->
            <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400 mb-6">
                <a href="/" class="hover:text-gray-900 dark:hover:text-white transition-colors flex items-center gap-1">
                    <span>🏠</span> Home
                </a>
                <span class="text-gray-300 dark:text-gray-600">/</span>
                <a href="/wallet" class="hover:text-gray-900 dark:hover:text-white transition-colors">Earnings</a>
                <span class="text-gray-300 dark:text-gray-600">/</span>
                <span class="px-2.5 py-0.5 rounded-full bg-amber-500/15 text-amber-700 dark:text-amber-400 font-bold border border-amber-500/20">
                    🎁 Incentive Program
                </span>
            </nav>

            <!-- Main App Container -->
            <div x-data="driverIncentiveProgram(@js($incentivesData ?? []))" class="space-y-8">

                <!-- Header Banner Card -->
                <div class="relative overflow-hidden rounded-3xl bg-white dark:bg-[#0e1626] border border-gray-200/80 dark:border-white/10 p-6 sm:p-8 shadow-sm">
                    <div class="absolute top-0 right-0 w-80 h-full bg-gradient-to-l from-amber-500/10 via-yellow-500/5 to-transparent pointer-events-none"></div>

                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
                        <!-- Title & Description -->
                        <div class="flex items-start sm:items-center gap-4">
                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-tr from-amber-500 via-amber-400 to-yellow-300 text-gray-950 flex items-center justify-center text-3xl shrink-0 shadow-lg shadow-amber-500/25 border-2 border-amber-300/40">
                                🎁
                            </div>
                            <div>
                                <div class="flex flex-wrap items-center gap-2.5">
                                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-gray-950 dark:text-white tracking-tight">
                                        Driver Incentive Program
                                    </h1>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-md shadow-emerald-500/25">
                                        <span class="w-2 h-2 rounded-full bg-white animate-ping"></span>
                                        LIVE REWARDS
                                    </span>
                                </div>
                                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 mt-1.5 font-medium max-w-2xl">
                                    Unlock tiered cash bonuses on top of your standard 90% ride fares by hitting daily, weekly, and monthly trip milestones in your registered area.
                                </p>
                            </div>
                        </div>

                        <!-- Location Tag & Live Refresh Button -->
                        <div class="flex items-center gap-2.5 self-start lg:self-center shrink-0">
                            <!-- Location Badge -->
                            <div class="flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-gray-100/90 dark:bg-white/5 border border-gray-200 dark:border-white/10 text-xs font-bold text-gray-800 dark:text-gray-200 shadow-xs">
                                <span class="text-sm">📍</span>
                                <div>
                                    <div class="text-[10px] text-gray-400 uppercase font-extrabold tracking-wider leading-none">Registered Area</div>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="text-gray-900 dark:text-white font-bold" x-text="data.driver_location?.display_location || 'USA'"></span>
                                        <span class="text-gray-300 dark:text-gray-600">•</span>
                                        <span class="text-amber-600 dark:text-amber-400 font-extrabold" x-text="data.driver_location?.vehicle_type || 'All Vehicles'"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Refresh Button -->
                            <button type="button" @click="fetchIncentives()" :disabled="loading" 
                                class="p-3 rounded-2xl bg-gray-100/90 dark:bg-white/5 hover:bg-amber-400 hover:text-gray-950 dark:hover:bg-amber-400 dark:hover:text-gray-950 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 transition-all text-xs font-bold shadow-xs hover:scale-105 active:scale-95" 
                                title="Sync Latest Milestone Progress">
                                <span :class="loading ? 'inline-block animate-spin' : ''" class="text-sm">🔄</span>
                            </button>
                        </div>
                    </div>

                    <!-- Highlight Feature Chips Row -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-6 pt-6 border-t border-gray-100 dark:border-white/5 text-xs">
                        <div class="flex items-center gap-2.5 text-gray-600 dark:text-gray-300">
                            <span class="w-6 h-6 rounded-full bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs">✓</span>
                            <span><strong>Instant Credit:</strong> Bonuses land directly in your wallet</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-gray-600 dark:text-gray-300">
                            <span class="w-6 h-6 rounded-full bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xs">⚡</span>
                            <span><strong>No Cap:</strong> Earn across Daily, Weekly & Monthly tiers</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-gray-600 dark:text-gray-300">
                            <span class="w-6 h-6 rounded-full bg-blue-500/15 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs">🛡️</span>
                            <span><strong>Zero Commission:</strong> 100% bonus is yours to keep</span>
                        </div>
                    </div>
                </div>

                <!-- Tabs: Daily, Weekly, Monthly (Colorful & Interactive) -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="inline-flex p-1.5 rounded-2xl bg-gray-200/70 dark:bg-white/5 border border-gray-200 dark:border-white/10 w-full sm:w-auto shadow-inner">
                        <!-- Daily Tab -->
                        <button type="button" @click="activeTab = 'daily'" 
                            :class="activeTab === 'daily' 
                                ? 'bg-gradient-to-r from-amber-500 via-amber-400 to-yellow-400 text-gray-950 font-black shadow-lg shadow-amber-500/30 scale-102' 
                                : 'text-gray-700 dark:text-gray-300 hover:text-gray-950 dark:hover:text-white font-bold'"
                            class="flex-1 sm:flex-none px-5 sm:px-7 py-3 rounded-xl text-xs sm:text-sm transition-all duration-200 flex items-center justify-center gap-2">
                            <span>⚡</span>
                            <span>Daily Sprint</span>
                        </button>

                        <!-- Weekly Tab -->
                        <button type="button" @click="activeTab = 'weekly'" 
                            :class="activeTab === 'weekly' 
                                ? 'bg-gradient-to-r from-blue-600 via-indigo-500 to-cyan-400 text-white font-black shadow-lg shadow-indigo-500/30 scale-102' 
                                : 'text-gray-700 dark:text-gray-300 hover:text-gray-950 dark:hover:text-white font-bold'"
                            class="flex-1 sm:flex-none px-5 sm:px-7 py-3 rounded-xl text-xs sm:text-sm transition-all duration-200 flex items-center justify-center gap-2">
                            <span>📅</span>
                            <span>Weekly Quest</span>
                        </button>

                        <!-- Monthly Tab -->
                        <button type="button" @click="activeTab = 'monthly'" 
                            :class="activeTab === 'monthly' 
                                ? 'bg-gradient-to-r from-emerald-600 via-teal-500 to-green-400 text-white font-black shadow-lg shadow-emerald-500/30 scale-102' 
                                : 'text-gray-700 dark:text-gray-300 hover:text-gray-950 dark:hover:text-white font-bold'"
                            class="flex-1 sm:flex-none px-5 sm:px-7 py-3 rounded-xl text-xs sm:text-sm transition-all duration-200 flex items-center justify-center gap-2">
                            <span>🏆</span>
                            <span>Monthly Championship</span>
                        </button>
                    </div>

                    <!-- Program Time & Countdown Indicator -->
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 flex items-center gap-2">
                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span x-text="activeTab === 'daily' ? 'Resets daily at 11:59 PM' : (activeTab === 'weekly' ? 'Active Monday to Sunday 11:59 PM' : 'Active Calendar Month Challenge')"></span>
                    </div>
                </div>

                <!-- Tab Panels Container -->
                <div>
                    <template x-for="tabKey in ['daily', 'weekly', 'monthly']" :key="tabKey">
                        <div x-show="activeTab === tabKey" class="space-y-8" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0">
                            
                            <!-- Active Incentive View -->
                            <template x-if="data.tabs && data.tabs[tabKey] && data.tabs[tabKey].has_incentive">
                                <div class="space-y-8">
                                    
                                    <!-- Hero Incentive Spotlight Card -->
                                    <div class="relative overflow-hidden rounded-3xl text-white shadow-2xl border transition-all duration-300"
                                         :class="tabKey === 'daily' 
                                            ? 'bg-gradient-to-br from-[#0c1222] via-[#0f172a] to-[#1e1b4b] border-amber-400/30' 
                                            : (tabKey === 'weekly' 
                                                ? 'bg-gradient-to-br from-[#0a1128] via-[#001f54] to-[#034078] border-indigo-400/30' 
                                                : 'bg-gradient-to-br from-[#05201c] via-[#0b3c35] to-[#134e4a] border-emerald-400/30')">
                                        
                                        <!-- Glowing Decorative Ambient Orbs -->
                                        <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full blur-3xl opacity-40 pointer-events-none"
                                             :class="tabKey === 'daily' ? 'bg-amber-400' : (tabKey === 'weekly' ? 'bg-cyan-400' : 'bg-emerald-400')"></div>
                                        <div class="absolute -left-16 -bottom-16 w-64 h-64 rounded-full blur-3xl opacity-30 pointer-events-none"
                                             :class="tabKey === 'daily' ? 'bg-yellow-500' : (tabKey === 'weekly' ? 'bg-indigo-500' : 'bg-teal-500')"></div>

                                        <div class="relative z-10 p-6 sm:p-10">
                                            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                                                
                                                <!-- Left Spotlight Info -->
                                                <div class="space-y-3 max-w-xl">
                                                    <!-- Badge -->
                                                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-black tracking-wider uppercase border shadow-sm"
                                                         :class="tabKey === 'daily' 
                                                            ? 'bg-amber-400/15 text-amber-300 border-amber-400/30' 
                                                            : (tabKey === 'weekly' ? 'bg-cyan-400/15 text-cyan-300 border-cyan-400/30' : 'bg-emerald-400/15 text-emerald-300 border-emerald-400/30')">
                                                        <span x-text="tabKey === 'daily' ? '⚡ TODAY\'S REWARD GOAL' : (tabKey === 'weekly' ? '📅 WEEKLY CHALLENGE' : '🏆 MONTHLY GRAND QUEST')"></span>
                                                    </div>

                                                    <!-- Reward Text with Glowing Gradient -->
                                                    <div class="flex items-baseline gap-3">
                                                        <h2 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight bg-gradient-to-r from-white via-amber-200 to-yellow-400 bg-clip-text text-transparent"
                                                            x-text="data.tabs[tabKey].hero_reward_text"></h2>
                                                        <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-emerald-400 px-2.5 py-1 rounded-lg bg-emerald-950/60 border border-emerald-800/40">
                                                            Cash Bonus
                                                        </span>
                                                    </div>

                                                    <h3 class="text-lg sm:text-xl font-bold text-gray-200" x-text="data.tabs[tabKey].name"></h3>
                                                    <p class="text-xs sm:text-sm text-gray-300/90 font-medium" x-text="data.tabs[tabKey].hero_target_text + ' in ' + (data.tabs[tabKey].city || data.tabs[tabKey].country || 'your area') + ' to claim this reward tier.'"></p>
                                                </div>

                                                <!-- Right Big Progress Counter -->
                                                <div class="p-6 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md flex flex-col items-center sm:items-end justify-center text-center sm:text-right shrink-0 min-w-[220px]">
                                                    <span class="text-xs font-extrabold uppercase tracking-wider text-gray-400">Target Progress</span>
                                                    <div class="text-4xl sm:text-5xl font-black mt-1"
                                                         :class="tabKey === 'daily' ? 'text-amber-400' : (tabKey === 'weekly' ? 'text-cyan-300' : 'text-emerald-400')"
                                                         x-text="data.tabs[tabKey].progress_fraction"></div>
                                                    <div class="mt-2 text-xs font-black px-3 py-1 rounded-full bg-white/10 border border-white/10"
                                                         x-text="data.tabs[tabKey].remaining_text"></div>
                                                </div>
                                            </div>

                                            <!-- High-Visibility Shimmer Progress Bar -->
                                            <div class="mt-8 pt-6 border-t border-white/10 space-y-3">
                                                <div class="flex justify-between items-center text-xs font-bold">
                                                    <span class="text-gray-300">0 Rides</span>
                                                    <span class="text-amber-300 font-extrabold tracking-wider" x-text="data.tabs[tabKey].progress_percentage + '% Completed'"></span>
                                                    <span class="text-gray-300" x-text="data.tabs[tabKey].hero_target_rides + ' Target Rides'"></span>
                                                </div>

                                                <!-- Progress Track -->
                                                <div class="w-full bg-black/40 rounded-full h-4 p-0.5 border border-white/15 overflow-hidden">
                                                    <div class="h-full rounded-full transition-all duration-700 ease-out shadow-lg"
                                                         :class="tabKey === 'daily' 
                                                            ? 'bg-gradient-to-r from-amber-500 via-yellow-400 to-amber-300 shadow-amber-400/50' 
                                                            : (tabKey === 'weekly' 
                                                                ? 'bg-gradient-to-r from-blue-500 via-indigo-400 to-cyan-300 shadow-cyan-400/50' 
                                                                : 'bg-gradient-to-r from-emerald-500 via-teal-400 to-green-300 shadow-emerald-400/50')"
                                                         :style="'width: ' + Math.max(3, data.tabs[tabKey].progress_percentage) + '%'">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Completed Celebration Banner (Shows when target is achieved) -->
                                    <template x-if="data.tabs[tabKey].is_completed">
                                        <div class="p-6 rounded-3xl bg-gradient-to-r from-emerald-500/20 via-teal-500/15 to-emerald-500/10 border-2 border-emerald-500/40 text-emerald-950 dark:text-emerald-100 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-lg shadow-emerald-500/10">
                                            <div class="flex items-center gap-4 text-center sm:text-left">
                                                <span class="text-4xl animate-bounce">🎉</span>
                                                <div>
                                                    <h4 class="font-black text-lg sm:text-xl" x-text="data.tabs[tabKey].completed_card.title"></h4>
                                                    <p class="text-xs sm:text-sm text-emerald-800 dark:text-emerald-300 mt-0.5" x-text="data.tabs[tabKey].completed_card.message + ' automatically into your driver wallet.'"></p>
                                                </div>
                                            </div>
                                            <a href="/wallet" class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-black text-xs uppercase tracking-wider shadow-md transition-all">
                                                View in Wallet →
                                            </a>
                                        </div>
                                    </template>

                                    <!-- Tiered Milestone Cards Section -->
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <h3 class="text-base sm:text-lg font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                                                    <span>🎯</span> Milestone Reward Tiers
                                                </h3>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Reach progressive ride milestones to stack automatic cash bonuses.</p>
                                            </div>
                                            <span class="text-xs font-bold px-3 py-1 rounded-full bg-gray-100 dark:bg-white/5 text-gray-600 dark:text-gray-300" 
                                                  x-text="(data.tabs[tabKey].milestones ? data.tabs[tabKey].milestones.length : 0) + ' Tiers Available'"></span>
                                        </div>

                                        <!-- Milestone Cards Grid (Tier 1, Tier 2, Tier 3) -->
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                                            <template x-for="(m, index) in data.tabs[tabKey].milestones" :key="m.rides">
                                                <div class="relative overflow-hidden rounded-3xl p-6 border transition-all duration-300 flex flex-col justify-between group hover:-translate-y-1 hover:shadow-xl"
                                                     :class="m.is_completed 
                                                        ? 'bg-gradient-to-b from-emerald-50/80 to-white dark:from-emerald-950/20 dark:to-[#0f172a] border-emerald-400 dark:border-emerald-500/40 shadow-emerald-500/10' 
                                                        : (m.is_current 
                                                            ? 'bg-gradient-to-b from-amber-50/90 to-white dark:from-amber-950/25 dark:to-[#0f172a] border-amber-400 dark:border-amber-500/50 shadow-lg shadow-amber-500/10 ring-2 ring-amber-400/20' 
                                                            : 'bg-white dark:bg-[#0e1626] border-gray-200 dark:border-white/10 hover:border-gray-300 dark:hover:border-white/20 shadow-xs')">
                                                    
                                                    <!-- Top Tier Badge & Reward Amount -->
                                                    <div>
                                                        <div class="flex items-center justify-between gap-2 mb-4">
                                                            <!-- Tier Pill -->
                                                            <span class="text-[11px] font-black uppercase px-2.5 py-1 rounded-lg tracking-wider"
                                                                  :class="m.is_completed 
                                                                    ? 'bg-emerald-500 text-white shadow-xs' 
                                                                    : (m.is_current 
                                                                        ? 'bg-amber-400 text-gray-950 shadow-xs' 
                                                                        : 'bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-gray-300')"
                                                                  x-text="index === 0 ? '🥉 Tier 1 • Starter' : (index === 1 ? '🥈 Tier 2 • Pro Driver' : '👑 Tier 3 • Champion')">
                                                            </span>

                                                            <!-- Cash Bonus Callout -->
                                                            <div class="text-right">
                                                                <span class="text-[10px] uppercase font-bold text-gray-400 block leading-none">Bonus</span>
                                                                <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight" x-text="m.reward_formatted"></span>
                                                            </div>
                                                        </div>

                                                        <!-- Target Ride Goal -->
                                                        <div class="space-y-1">
                                                            <h4 class="text-lg font-black text-gray-900 dark:text-white" x-text="m.rides + ' Completed Rides'"></h4>
                                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                                <span x-text="'Receive ' + m.reward_formatted + ' cash reward once you complete ' + m.rides + ' trips.'"></span>
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <!-- Bottom Progress Indicator & Status Pill -->
                                                    <div class="mt-6 pt-4 border-t border-gray-100 dark:border-white/5 space-y-3">
                                                        <div class="flex items-center justify-between text-xs">
                                                            <span class="font-extrabold"
                                                                  :class="m.is_completed ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-700 dark:text-gray-300'"
                                                                  x-text="m.is_completed ? '✔ Target Achieved' : m.progress_text"></span>
                                                            <span class="text-[11px] font-bold text-gray-400" x-show="m.remaining_text" x-text="m.remaining_text"></span>
                                                        </div>

                                                        <!-- Mini Progress Bar Inside Card -->
                                                        <div class="w-full bg-gray-100 dark:bg-white/10 rounded-full h-2 overflow-hidden">
                                                            <div class="h-full rounded-full transition-all duration-500"
                                                                 :class="m.is_completed 
                                                                    ? 'bg-emerald-500 w-full' 
                                                                    : (m.is_current ? 'bg-amber-400' : 'bg-gray-300 dark:bg-white/20')"
                                                                 :style="'width: ' + (m.is_completed ? 100 : Math.min(100, Math.round(((data.tabs[tabKey].completed_rides || 0) / (m.rides || 1)) * 100))) + '%'">
                                                            </div>
                                                        </div>

                                                        <!-- Status Label Button -->
                                                        <div class="text-center pt-1">
                                                            <span class="inline-block w-full py-1.5 text-center text-xs font-black rounded-xl transition-all"
                                                                  :class="m.is_completed 
                                                                    ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-800/40' 
                                                                    : (m.is_current 
                                                                        ? 'bg-amber-400 text-gray-950 shadow-sm' 
                                                                        : 'bg-gray-100 dark:bg-white/5 text-gray-500 dark:text-gray-400')"
                                                                  x-text="m.is_completed ? '✔ Bonus Credited' : (m.is_current ? '⚡ Active Target' : 'Locked Tier')">
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Empty / Missed State -->
                            <template x-if="!data.tabs || !data.tabs[tabKey] || !data.tabs[tabKey].has_incentive">
                                <div class="p-12 text-center rounded-3xl bg-white dark:bg-[#0e1626] border border-dashed border-gray-200 dark:border-white/10 space-y-3">
                                    <span class="text-5xl block">🏁</span>
                                    <h4 class="text-lg font-black text-gray-900 dark:text-white" x-text="'No Active ' + tabKey.toUpperCase() + ' Incentive'"></h4>
                                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                                        Check back shortly or view other incentive tabs. New earning challenges are added frequently by the operations team.
                                    </p>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- Footer Summary Grid: Reward History & VIP Wallet Card -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Reward History Timeline (2 Cols) -->
                    <div class="lg:col-span-2 rounded-3xl bg-white dark:bg-[#0e1626] border border-gray-200/80 dark:border-white/10 p-6 sm:p-7 shadow-sm">
                        <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100 dark:border-white/5">
                            <div class="flex items-center gap-2">
                                <span class="text-lg">📜</span>
                                <h4 class="text-sm font-black uppercase text-gray-900 dark:text-white tracking-wider">Incentive Reward History</h4>
                            </div>
                            <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-800/40"
                                  x-text="(data.reward_history ? data.reward_history.length : 0) + ' Rewards Credited'"></span>
                        </div>

                        <!-- Empty History State -->
                        <template x-if="!data.reward_history || data.reward_history.length === 0">
                            <div class="py-12 px-4 text-center rounded-2xl bg-gray-50/50 dark:bg-white/[0.02] border border-dashed border-gray-200 dark:border-white/5">
                                <span class="text-3xl block mb-2">🎁</span>
                                <h5 class="text-sm font-bold text-gray-800 dark:text-gray-200">No milestone rewards credited yet</h5>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-sm mx-auto">
                                    Hit your first ride milestone above to unlock automatic instant cash deposits into your driver wallet.
                                </p>
                            </div>
                        </template>

                        <!-- Populated Reward Items -->
                        <div class="space-y-2.5 max-h-72 overflow-y-auto pr-1">
                            <template x-for="r in (data.reward_history || [])" :key="r.id">
                                <div class="flex items-center justify-between p-3.5 rounded-2xl bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/5 hover:border-emerald-300 dark:hover:border-emerald-500/30 transition-all text-xs">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                                            🎁
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900 dark:text-white text-sm" x-text="r.incentive"></div>
                                            <div class="text-[11px] text-gray-400 mt-0.5" x-text="r.full_date + ' • ' + r.trips"></div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-black text-base text-emerald-600 dark:text-emerald-400" x-text="'+' + r.reward"></div>
                                        <span class="inline-block text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800/30">
                                            ✓ Credited
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- VIP Driver Wallet Card (1 Col) -->
                    <div class="rounded-3xl bg-gradient-to-br from-[#0c1222] via-[#0f172a] to-[#1e1b4b] border-2 border-amber-400/40 p-6 sm:p-7 text-white shadow-xl shadow-amber-500/10 flex flex-col justify-between relative overflow-hidden">
                        <!-- Decorative glow -->
                        <div class="absolute -right-12 -bottom-12 w-48 h-48 bg-amber-500/20 rounded-full blur-2xl pointer-events-none"></div>

                        <div class="relative z-10">
                            <!-- Card Header -->
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-[11px] uppercase font-black tracking-wider text-amber-400 bg-amber-400/10 px-3 py-1 rounded-full border border-amber-400/20">
                                    Driver Wallet
                                </span>
                                <span class="text-xl">💳</span>
                            </div>

                            <!-- Total Incentive Cash -->
                            <div class="space-y-1">
                                <span class="text-xs text-gray-400 font-bold uppercase tracking-wider">Total Incentive Bonuses Earned</span>
                                <div class="text-4xl sm:text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-amber-300 via-yellow-200 to-amber-400 tracking-tight">
                                    <span x-text="(data.wallet_summary?.currency || '$') + Number(data.wallet_summary?.total_incentive_earned || 0).toLocaleString()"></span>
                                </div>
                                <p class="text-xs text-gray-300/80 mt-2 font-medium">
                                    All incentive rewards are credited directly to your driver wallet with zero delays.
                                </p>
                            </div>
                        </div>

                        <!-- Card Balance & Quick Action Button -->
                        <div class="mt-8 pt-5 border-t border-white/10 relative z-10 space-y-4">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-400 font-medium">Available Balance:</span>
                                <span class="font-black text-emerald-400 text-lg" 
                                      x-text="(data.wallet_summary?.currency || '$') + Number(data.wallet_summary?.balance || 0).toFixed(2)"></span>
                            </div>

                            <a href="/wallet" class="w-full py-3 rounded-xl bg-gradient-to-r from-amber-400 via-amber-500 to-yellow-400 hover:from-amber-300 hover:to-yellow-300 text-gray-950 font-black text-xs text-center uppercase tracking-wider shadow-lg shadow-amber-500/25 transition-all flex items-center justify-center gap-2 hover:scale-102 active:scale-98">
                                <span>Go to Wallet & Payouts</span>
                                <span>→</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 3-Step Driver How-It-Works Visual Roadmap -->
                <div class="rounded-3xl bg-white dark:bg-[#0e1626] border border-gray-200/80 dark:border-white/10 p-6 sm:p-8 shadow-sm">
                    <h4 class="text-sm font-black uppercase text-gray-900 dark:text-white tracking-wider mb-6 flex items-center gap-2">
                        <span>💡</span> How Milestone Rewards Work
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative">
                        <!-- Step 1 -->
                        <div class="p-5 rounded-2xl bg-gray-50/70 dark:bg-white/5 border border-gray-100 dark:border-white/5 space-y-2">
                            <div class="w-10 h-10 rounded-xl bg-amber-500/15 text-amber-500 flex items-center justify-center font-black text-base">
                                1
                            </div>
                            <h5 class="font-bold text-gray-900 dark:text-white text-sm">Drive & Accept Trips</h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                Every ride you complete in your registered area automatically counts towards your Daily, Weekly, and Monthly targets.
                            </p>
                        </div>

                        <!-- Step 2 -->
                        <div class="p-5 rounded-2xl bg-gray-50/70 dark:bg-white/5 border border-gray-100 dark:border-white/5 space-y-2">
                            <div class="w-10 h-10 rounded-xl bg-blue-500/15 text-blue-500 flex items-center justify-center font-black text-base">
                                2
                            </div>
                            <h5 class="font-bold text-gray-900 dark:text-white text-sm">Hit Tier Targets</h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                Progress through Tier 1, Tier 2, and Tier 3. Higher tiers unlock larger cash reward bonuses.
                            </p>
                        </div>

                        <!-- Step 3 -->
                        <div class="p-5 rounded-2xl bg-gray-50/70 dark:bg-white/5 border border-gray-100 dark:border-white/5 space-y-2">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500/15 text-emerald-500 flex items-center justify-center font-black text-base">
                                3
                            </div>
                            <h5 class="font-bold text-gray-900 dark:text-white text-sm">Instant Cash In Wallet</h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                Once achieved, the cash bonus is immediately credited to your driver wallet for payout or transfer.
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Alpine Component Logic -->
            <script>
                function driverIncentiveProgram(initialData) {
                    return {
                        data: initialData || {},
                        activeTab: 'daily',
                        loading: false,
                        async fetchIncentives() {
                            this.loading = true;
                            try {
                                const res = await fetch('/api/driver/incentives', {
                                    headers: {
                                        'Accept': 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                });
                                if (res.ok) {
                                    const json = await res.json();
                                    this.data = json;
                                }
                            } catch (e) {
                                console.error('Failed to load driver incentives:', e);
                            } finally {
                                this.loading = false;
                            }
                        }
                    }
                }
            </script>
        </div>
    </div>
</x-layout>
