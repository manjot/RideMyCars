<x-layout title="Driver Incentive Program | RideMyCars">
    <div class="min-h-screen bg-gray-50 dark:bg-[#080808] py-8 sm:py-12 transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Breadcrumb Navigation -->
            <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400 mb-6">
                <a href="/" class="hover:text-gray-900 dark:hover:text-white transition-colors">Home</a>
                <span>/</span>
                <a href="/wallet" class="hover:text-gray-900 dark:hover:text-white transition-colors">Earnings</a>
                <span>/</span>
                <span class="text-amber-600 dark:text-amber-400 font-bold">Incentive Program</span>
            </nav>

            <!-- Incentive Program Container -->
            <div x-data="driverIncentiveProgram(@js($incentivesData ?? []))" class="bg-white dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-3xl p-6 sm:p-8 shadow-sm">
                <!-- Header & Location Detection Badge -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-100 dark:border-white/10">
                    <div>
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-amber-500/15 text-amber-500 flex items-center justify-center text-2xl shadow-sm">
                                🎁
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">Driver Incentive Program</h1>
                                    <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-800/40">
                                        Live Rewards
                                    </span>
                                </div>
                                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">Complete ride milestones in your registered area to earn automatic wallet cash bonuses.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <!-- Location & Vehicle Badge -->
                        <div class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-gray-100 dark:bg-white/10 text-xs font-semibold text-gray-700 dark:text-gray-300">
                            <span>📍</span>
                            <span x-text="data.driver_location?.display_location || 'India'"></span>
                            <span class="text-gray-400">•</span>
                            <span x-text="data.driver_location?.vehicle_type || 'Car'"></span>
                        </div>
                        <button type="button" @click="fetchIncentives()" :disabled="loading" class="p-2.5 rounded-xl bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-white/20 transition-all text-xs font-bold" title="Refresh Incentives">
                            <span :class="loading ? 'inline-block animate-spin' : ''">🔄</span>
                        </button>
                    </div>
                </div>

                <!-- Tabs: Daily, Weekly, Monthly -->
                <div class="flex gap-2 p-1.5 bg-gray-100 dark:bg-[#111] rounded-2xl mt-6 max-w-md">
                    <button type="button" @click="activeTab = 'daily'" 
                        :class="activeTab === 'daily' ? 'bg-amber-400 text-gray-900 font-extrabold shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white font-medium'"
                        class="flex-1 py-2 rounded-xl text-xs sm:text-sm transition-all text-center">
                        ⚡ Daily
                    </button>
                    <button type="button" @click="activeTab = 'weekly'" 
                        :class="activeTab === 'weekly' ? 'bg-amber-400 text-gray-900 font-extrabold shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white font-medium'"
                        class="flex-1 py-2 rounded-xl text-xs sm:text-sm transition-all text-center">
                        📅 Weekly
                    </button>
                    <button type="button" @click="activeTab = 'monthly'" 
                        :class="activeTab === 'monthly' ? 'bg-amber-400 text-gray-900 font-extrabold shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white font-medium'"
                        class="flex-1 py-2 rounded-xl text-xs sm:text-sm transition-all text-center">
                        🏆 Monthly
                    </button>
                </div>

                <!-- Tab Content -->
                <div class="mt-6">
                    <template x-for="tabKey in ['daily', 'weekly', 'monthly']" :key="tabKey">
                        <div x-show="activeTab === tabKey" class="space-y-6">
                            <!-- If tab has active incentive -->
                            <template x-if="data.tabs && data.tabs[tabKey] && data.tabs[tabKey].has_incentive">
                                <div class="space-y-6">
                                    <!-- Hero Incentive Card -->
                                    <div class="relative overflow-hidden p-6 sm:p-8 rounded-3xl bg-gradient-to-br from-[#0F172A] via-[#1E293B] to-[#0A0F1D] text-white border border-amber-400/20 shadow-xl">
                                        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-amber-400/10 rounded-full blur-3xl pointer-events-none"></div>

                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                            <div>
                                                <span class="text-[11px] uppercase tracking-wider font-extrabold text-amber-400 bg-amber-400/10 px-3 py-1 rounded-full border border-amber-400/20" x-text="data.tabs[tabKey].title"></span>
                                                <h3 class="text-2xl sm:text-3xl font-black mt-3 text-white" x-text="data.tabs[tabKey].hero_reward_text"></h3>
                                                <p class="text-sm text-gray-300 mt-1 font-medium" x-text="data.tabs[tabKey].hero_target_text"></p>
                                            </div>

                                            <div class="text-left sm:text-right">
                                                <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Progress</div>
                                                <div class="text-2xl sm:text-3xl font-black text-amber-400" x-text="data.tabs[tabKey].progress_fraction"></div>
                                                <div class="text-xs font-bold text-gray-300 mt-0.5" x-text="data.tabs[tabKey].remaining_text"></div>
                                            </div>
                                        </div>

                                        <!-- Progress Bar -->
                                        <div class="mt-6">
                                            <div class="w-full bg-white/10 rounded-full h-3 overflow-hidden p-0.5">
                                                <div class="bg-gradient-to-r from-amber-400 to-yellow-300 h-2 rounded-full transition-all duration-700 shadow-sm"
                                                     :style="'width: ' + Math.max(3, data.tabs[tabKey].progress_percentage) + '%'"></div>
                                            </div>
                                            <div class="flex justify-between items-center text-[11px] text-gray-400 mt-2 font-medium">
                                                <span>0 Rides</span>
                                                <span x-text="data.tabs[tabKey].progress_percentage + '% Completed'"></span>
                                                <span x-text="data.tabs[tabKey].hero_target_rides + ' Rides'"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Completed Green Celebration Card -->
                                    <template x-if="data.tabs[tabKey].is_completed">
                                        <div class="p-5 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-900 dark:text-emerald-200 flex items-center justify-between gap-4 shadow-sm">
                                            <div class="flex items-center gap-3">
                                                <span class="text-3xl">🎉</span>
                                                <div>
                                                    <h4 class="font-extrabold text-base" x-text="data.tabs[tabKey].completed_card.title"></h4>
                                                    <p class="text-xs text-emerald-700 dark:text-emerald-300" x-text="data.tabs[tabKey].completed_card.message + ' to your wallet.'"></p>
                                                </div>
                                            </div>
                                            <span class="px-3 py-1.5 bg-emerald-500 text-white rounded-xl text-xs font-black uppercase tracking-wider">
                                                Credited
                                            </span>
                                        </div>
                                    </template>

                                    <!-- Milestone Cards Grid -->
                                    <div>
                                        <h4 class="text-xs font-extrabold uppercase text-gray-400 tracking-wider mb-3">Milestone Targets</h4>
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                            <template x-for="m in data.tabs[tabKey].milestones" :key="m.rides">
                                                <div :class="m.is_completed 
                                                    ? 'bg-emerald-50 dark:bg-emerald-950/20 border-emerald-300 dark:border-emerald-800/40' 
                                                    : (m.is_current ? 'bg-amber-50 dark:bg-amber-950/20 border-amber-300 dark:border-amber-700/50 shadow-sm' : 'bg-gray-50 dark:bg-white/5 border-gray-200 dark:border-white/10')"
                                                     class="p-4 rounded-2xl border transition-all">
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex items-center gap-2">
                                                            <template x-if="m.is_completed">
                                                                <span class="w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs font-bold">✔</span>
                                                            </template>
                                                            <template x-if="!m.is_completed">
                                                                <span class="w-5 h-5 rounded-full border border-gray-300 dark:border-white/20 flex items-center justify-center text-[10px] text-gray-400">○</span>
                                                            </template>
                                                            <span class="text-xs font-bold text-gray-900 dark:text-white" x-text="m.title"></span>
                                                        </div>
                                                        <span class="font-black text-sm text-emerald-600 dark:text-emerald-400" x-text="m.reward_formatted"></span>
                                                    </div>

                                                    <div class="mt-3 flex items-center justify-between text-xs">
                                                        <span class="font-extrabold" :class="m.is_completed ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-500 dark:text-gray-400'" x-text="m.progress_text"></span>
                                                        <span x-show="m.remaining_text" class="text-[11px] text-gray-400" x-text="m.remaining_text"></span>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- If no active incentive / expired missed state -->
                            <template x-if="!data.tabs || !data.tabs[tabKey] || !data.tabs[tabKey].has_incentive">
                                <div class="p-8 rounded-3xl border text-center space-y-3"
                                     :class="data.tabs && data.tabs[tabKey]?.is_missed 
                                        ? 'bg-rose-50 dark:bg-rose-950/20 border-rose-200 dark:border-rose-900/30' 
                                        : 'bg-gray-50 dark:bg-white/5 border-gray-200 dark:border-white/10'">
                                    <span class="text-4xl" x-text="data.tabs && data.tabs[tabKey]?.is_missed ? '⏰' : '🏁'"></span>
                                    <h4 class="text-base font-bold text-gray-900 dark:text-white" 
                                        x-text="data.tabs && data.tabs[tabKey]?.is_missed ? data.tabs[tabKey].missed_card.title : 'No Active ' + tabKey.toUpperCase() + ' Incentive'"></h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 max-w-md mx-auto" 
                                       x-text="data.tabs && data.tabs[tabKey]?.is_missed ? data.tabs[tabKey].missed_card.message : 'Check back tomorrow or look at other incentive tabs for new earning goals.'"></p>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- Reward History & Wallet Summary Footer -->
                <div class="mt-8 pt-6 border-t border-gray-100 dark:border-white/10 grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Reward History -->
                    <div class="lg:col-span-2">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-xs font-extrabold uppercase text-gray-400 tracking-wider">Incentive Reward History</h4>
                            <span class="text-xs font-bold text-gray-500" x-text="(data.reward_history ? data.reward_history.length : 0) + ' Rewards Credited'"></span>
                        </div>

                        <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                            <template x-if="!data.reward_history || data.reward_history.length === 0">
                                <div class="py-8 text-center text-xs text-gray-400 bg-gray-50 dark:bg-white/5 rounded-2xl">
                                    No incentive rewards credited yet. Complete your first milestone to earn!
                                </div>
                            </template>

                            <template x-for="r in (data.reward_history || [])" :key="r.id">
                                <div class="flex items-center justify-between p-3.5 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/10 text-xs">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-emerald-500/15 text-emerald-500 flex items-center justify-center font-bold text-base">
                                            🎁
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900 dark:text-white text-sm" x-text="r.incentive"></div>
                                            <div class="text-[11px] text-gray-400 mt-0.5" x-text="r.date + ' • ' + r.trips"></div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-black text-sm text-emerald-600 dark:text-emerald-400" x-text="'+' + r.reward"></div>
                                        <span class="inline-block text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider" x-text="r.status"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Wallet Credits Box -->
                    <div class="p-6 rounded-2xl bg-gradient-to-br from-amber-500/10 to-yellow-500/5 border border-amber-400/30 text-gray-900 dark:text-white flex flex-col justify-between">
                        <div>
                            <span class="text-[11px] uppercase tracking-wider font-extrabold text-amber-600 dark:text-amber-400">Total Incentive Bonuses</span>
                            <div class="text-3xl sm:text-4xl font-black text-gray-900 dark:text-white mt-1">
                                <span x-text="(data.wallet_summary?.currency || '₹') + Number(data.wallet_summary?.total_incentive_earned || 0).toLocaleString()"></span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">All rewards are instantly deposited into your driver wallet upon achieving ride targets.</p>
                        </div>

                        <div class="mt-6 pt-4 border-t border-amber-400/20 flex items-center justify-between text-xs">
                            <span class="text-gray-500 dark:text-gray-400">Current Balance:</span>
                            <span class="font-extrabold text-emerald-600 dark:text-emerald-400 text-sm" x-text="(data.wallet_summary?.currency || '₹') + Number(data.wallet_summary?.balance || 0).toFixed(2)"></span>
                        </div>
                    </div>
                </div>
            </div>

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
