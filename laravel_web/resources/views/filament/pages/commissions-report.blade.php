<x-filament-panels::page>
    <div wire:poll.10s x-data="{
        activeTab: 'overview',
        driverSearch: '',
        selectedType: 'all',
        selectedVertical: 'all'
    }" class="space-y-6">

        {{-- Top Header Banner (Issue 2.5: Explicit high-contrast dark gradient & white text) --}}
        <div class="relative overflow-hidden rounded-2xl p-6 md:p-8 shadow-xl"
             style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #0f172a 100%) !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.14);">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="px-3 py-1 text-xs font-bold uppercase tracking-wider rounded-full"
                              style="background: rgba(245, 158, 11, 0.22); color: #fcd34d; border: 1px solid rgba(245, 158, 11, 0.4);">
                            Platform Financials (GHS &amp; USD)
                        </span>
                        <span class="px-3 py-1 text-xs font-semibold rounded-full flex items-center gap-1.5"
                              style="background: rgba(16, 185, 129, 0.25); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.4);">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            Live Auto-Updating Ledger
                        </span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight" style="color: #ffffff !important;">
                        Platform Commissions &amp; Driver Ledger
                    </h1>
                    <p class="text-sm md:text-base max-w-2xl mt-1" style="color: #e2e8f0 !important;">
                        Comprehensive Daily, Weekly, Monthly &amp; Yearly platform commission earnings displayed in both Ghana Cedis (GH₵) and US Dollars ($), with automatic real-time updates on trip completion.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="rounded-xl p-4 text-right"
                         style="background: rgba(15, 23, 42, 0.72); border: 1px solid rgba(255, 255, 255, 0.18);">
                        <div class="text-xs uppercase font-bold tracking-wider" style="color: #fcd34d !important;">All-Time Platform Revenue</div>
                        <div class="text-2xl font-black mt-0.5" style="color: #ffffff !important;">
                            GH₵ {{ number_format($metrics['all_time']['total_commission_ghs'] ?? $metrics['all_time']['total_commission'] ?? 0, 0) }}
                            <span class="text-base font-bold" style="color: #93c5fd !important;">(${{ number_format($metrics['all_time']['total_commission_usd'] ?? 0, 0) }})</span>
                        </div>
                        <div class="text-xs mt-0.5" style="color: #cbd5e1 !important;">{{ $metrics['all_time']['trips_count'] ?? 0 }} total completed trips</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Metric Cards Grid: Daily, Weekly, Monthly, Yearly, Cash Deductions --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            {{-- Daily Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700/60 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Daily Commissions</span>
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">Today</span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-gray-900 dark:text-white">
                        GH₵ {{ number_format($metrics['daily']['total_commission_ghs'] ?? $metrics['daily']['total_commission'] ?? 0, 0) }}
                        <span class="text-sm font-bold text-blue-600 dark:text-blue-400">(${{ number_format($metrics['daily']['total_commission_usd'] ?? 0, 0) }})</span>
                    </div>
                    @php
                        $dayDiffGhs = ($metrics['daily']['total_commission_ghs'] ?? 0) - ($metrics['yesterday']['total_commission_ghs'] ?? 0);
                        $dayDiffUsd = ($metrics['daily']['total_commission_usd'] ?? 0) - ($metrics['yesterday']['total_commission_usd'] ?? 0);
                    @endphp
                    <div class="mt-1 text-xs font-bold {{ $dayDiffGhs >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                        {{ $dayDiffGhs >= 0 ? '+' : '-' }}GH₵ {{ number_format(abs($dayDiffGhs), 0) }} ({{ $dayDiffUsd >= 0 ? '+' : '-' }}${{ number_format(abs($dayDiffUsd), 0) }}) vs yesterday
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700/60 flex flex-wrap items-center justify-between gap-1 text-xs text-gray-500 dark:text-gray-400">
                    <span>{{ $metrics['daily']['trips_count'] ?? 0 }} trips today</span>
                    <span class="text-amber-600 dark:text-amber-400 font-semibold">GH₵ {{ number_format($metrics['daily']['cash_commission_ghs'] ?? 0, 0) }} (${{ number_format($metrics['daily']['cash_commission_usd'] ?? 0, 0) }}) cash commission</span>
                </div>
            </div>

            {{-- Weekly Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700/60 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Weekly Commissions</span>
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">This Week</span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-gray-900 dark:text-white">
                        GH₵ {{ number_format($metrics['weekly']['total_commission_ghs'] ?? $metrics['weekly']['total_commission'] ?? 0, 0) }}
                        <span class="text-sm font-bold text-blue-600 dark:text-blue-400">(${{ number_format($metrics['weekly']['total_commission_usd'] ?? 0, 0) }})</span>
                    </div>
                    @php
                        $weekDiffGhs = ($metrics['weekly']['total_commission_ghs'] ?? 0) - ($metrics['last_week']['total_commission_ghs'] ?? 0);
                        $weekDiffUsd = ($metrics['weekly']['total_commission_usd'] ?? 0) - ($metrics['last_week']['total_commission_usd'] ?? 0);
                    @endphp
                    <div class="mt-1 text-xs font-bold {{ $weekDiffGhs >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                        {{ $weekDiffGhs >= 0 ? '+' : '-' }}GH₵ {{ number_format(abs($weekDiffGhs), 0) }} ({{ $weekDiffUsd >= 0 ? '+' : '-' }}${{ number_format(abs($weekDiffUsd), 0) }}) vs last week
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700/60 flex flex-wrap items-center justify-between gap-1 text-xs text-gray-500 dark:text-gray-400">
                    <span>{{ $metrics['weekly']['trips_count'] ?? 0 }} trips this week</span>
                    <span class="font-semibold text-gray-700 dark:text-gray-300">GH₵ {{ number_format($metrics['weekly']['gross_volume_ghs'] ?? 0, 0) }} (${{ number_format($metrics['weekly']['gross_volume_usd'] ?? 0, 0) }}) gross volume</span>
                </div>
            </div>

            {{-- Monthly Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700/60 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Monthly Commissions</span>
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">{{ now()->format('M Y') }}</span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-gray-900 dark:text-white">
                        GH₵ {{ number_format($metrics['monthly']['total_commission_ghs'] ?? $metrics['monthly']['total_commission'] ?? 0, 0) }}
                        <span class="text-sm font-bold text-blue-600 dark:text-blue-400">(${{ number_format($metrics['monthly']['total_commission_usd'] ?? 0, 0) }})</span>
                    </div>
                    @php
                        $monthDiffGhs = ($metrics['monthly']['total_commission_ghs'] ?? 0) - ($metrics['last_month']['total_commission_ghs'] ?? 0);
                        $monthDiffUsd = ($metrics['monthly']['total_commission_usd'] ?? 0) - ($metrics['last_month']['total_commission_usd'] ?? 0);
                    @endphp
                    <div class="mt-1 text-xs font-bold {{ $monthDiffGhs >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                        {{ $monthDiffGhs >= 0 ? '+' : '-' }}GH₵ {{ number_format(abs($monthDiffGhs), 0) }} ({{ $monthDiffUsd >= 0 ? '+' : '-' }}${{ number_format(abs($monthDiffUsd), 0) }}) vs last month
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700/60 flex flex-wrap items-center justify-between gap-1 text-xs text-gray-500 dark:text-gray-400">
                    <span>{{ $metrics['monthly']['trips_count'] ?? 0 }} trips this month</span>
                    <span class="font-semibold text-gray-700 dark:text-gray-300">GH₵ {{ number_format($metrics['monthly']['gross_volume_ghs'] ?? 0, 0) }} (${{ number_format($metrics['monthly']['gross_volume_usd'] ?? 0, 0) }}) gross volume</span>
                </div>
            </div>

            {{-- Yearly Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700/60 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Yearly Commissions</span>
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-50 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">{{ now()->format('Y') }}</span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-gray-900 dark:text-white">
                        GH₵ {{ number_format($metrics['yearly']['total_commission_ghs'] ?? $metrics['yearly']['total_commission'] ?? 0, 0) }}
                        <span class="text-sm font-bold text-blue-600 dark:text-blue-400">(${{ number_format($metrics['yearly']['total_commission_usd'] ?? 0, 0) }})</span>
                    </div>
                    <div class="mt-1 text-xs font-semibold text-gray-400">
                        Annual total commission revenue
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700/60 flex flex-wrap items-center justify-between gap-1 text-xs text-gray-500 dark:text-gray-400">
                    <span>{{ $metrics['yearly']['trips_count'] ?? 0 }} trips in {{ now()->format('Y') }}</span>
                    <span class="font-semibold text-amber-600 dark:text-amber-400">GH₵ {{ number_format($metrics['yearly']['cash_commission_ghs'] ?? 0, 0) }} (${{ number_format($metrics['yearly']['cash_commission_usd'] ?? 0, 0) }}) cash commission</span>
                </div>
            </div>

        </div>

        {{-- Cash Commission Auto-Deduction Highlight Box --}}
        <div class="bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/50 rounded-2xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="p-2.5 bg-amber-500 text-white rounded-xl shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-amber-900 dark:text-amber-200">
                        Automated Main Wallet Deductions for Cash Trips
                    </h4>
                    <p class="text-xs text-amber-700 dark:text-amber-300/80 mt-0.5">
                        Platform commissions on trips paid in physical cash are deducted instantly and automatically from the driver's primary wallet balance and recorded in the commission wallet.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-4 text-xs font-semibold text-amber-900 dark:text-amber-200">
                <div class="text-right">
                    <div>Cash Deductions (Month): <span class="font-bold">GH₵ {{ number_format($metrics['monthly']['cash_commission_ghs'] ?? 0, 0) }} (${{ number_format($metrics['monthly']['cash_commission_usd'] ?? 0, 0) }})</span></div>
                    <div class="text-amber-600 dark:text-amber-400">{{ $metrics['monthly']['cash_trips_count'] ?? 0 }} cash transactions</div>
                </div>
            </div>
        </div>

        {{-- Tab Navigation Bar --}}
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex space-x-2 md:space-x-4 overflow-x-auto pb-2" aria-label="Tabs">
                <button @click="activeTab = 'overview'" 
                        :class="activeTab === 'overview' ? 'border-amber-500 text-amber-600 dark:text-amber-400 bg-amber-50/50 dark:bg-amber-950/20' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2.5 px-4 border-b-2 font-bold text-sm rounded-t-lg transition">
                    📊 Overview & Trends
                </button>
                <button @click="activeTab = 'daily'" 
                        :class="activeTab === 'daily' ? 'border-amber-500 text-amber-600 dark:text-amber-400 bg-amber-50/50 dark:bg-amber-950/20' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2.5 px-4 border-b-2 font-bold text-sm rounded-t-lg transition">
                    📅 Daily (Past 14 Days)
                </button>
                <button @click="activeTab = 'weekly'" 
                        :class="activeTab === 'weekly' ? 'border-amber-500 text-amber-600 dark:text-amber-400 bg-amber-50/50 dark:bg-amber-950/20' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2.5 px-4 border-b-2 font-bold text-sm rounded-t-lg transition">
                    🗓️ Weekly (Past 10 Weeks)
                </button>
                <button @click="activeTab = 'monthly'" 
                        :class="activeTab === 'monthly' ? 'border-amber-500 text-amber-600 dark:text-amber-400 bg-amber-50/50 dark:bg-amber-950/20' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2.5 px-4 border-b-2 font-bold text-sm rounded-t-lg transition">
                    📈 Monthly (Past 12 Months)
                </button>
                <button @click="activeTab = 'yearly'" 
                        :class="activeTab === 'yearly' ? 'border-amber-500 text-amber-600 dark:text-amber-400 bg-amber-50/50 dark:bg-amber-950/20' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2.5 px-4 border-b-2 font-bold text-sm rounded-t-lg transition">
                    🏛️ Yearly Breakdown
                </button>
                <button @click="activeTab = 'drivers'" 
                        :class="activeTab === 'drivers' ? 'border-amber-500 text-amber-600 dark:text-amber-400 bg-amber-50/50 dark:bg-amber-950/20' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2.5 px-4 border-b-2 font-bold text-sm rounded-t-lg transition flex items-center gap-2">
                    <span>🚗 Driver Commission History</span>
                    <span class="px-2 py-0.5 text-xs rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300">
                        {{ count($driverHistory) }}
                    </span>
                </button>
                <button @click="activeTab = 'overdue_drivers'" 
                        :class="activeTab === 'overdue_drivers' ? 'border-rose-500 text-rose-600 dark:text-rose-400 bg-rose-50/50 dark:bg-rose-950/20' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2.5 px-4 border-b-2 font-bold text-sm rounded-t-lg transition flex items-center gap-2">
                    <span>🚨 Exceeded 7-Day Payment Deadline</span>
                    <span class="px-2 py-0.5 text-xs rounded-full bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-300">
                        {{ count($overdueDrivers ?? []) }}
                    </span>
                </button>
            </nav>
        </div>

        {{-- TAB 1: OVERVIEW & TRENDS --}}
        <div x-show="activeTab === 'overview'" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Daily Table Summary --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700/60 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Recent Daily Commissions (Last 7 Days)</h3>
                        <button @click="activeTab = 'daily'" class="text-xs font-semibold text-amber-600 dark:text-amber-400 hover:underline">
                            View All 14 Days &rarr;
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-500 dark:text-gray-400 uppercase">
                                <tr>
                                    <th class="py-2.5 px-3">Date</th>
                                    <th class="py-2.5 px-3">Trips</th>
                                    <th class="py-2.5 px-3">Gross Volume</th>
                                    <th class="py-2.5 px-3 text-right">Commission</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium">
                                @foreach(array_slice($dailySeries, 0, 7) as $row)
                                <tr class="{{ $row['is_today'] ? 'bg-amber-50/40 dark:bg-amber-950/20 font-bold' : '' }}">
                                    <td class="py-2.5 px-3">
                                        {{ $row['date_label'] }}
                                        @if($row['is_today'])
                                            <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded bg-amber-500 text-white">Today</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 text-gray-600 dark:text-gray-300">{{ $row['trips_count'] }}</td>
                                    <td class="py-2.5 px-3 text-gray-600 dark:text-gray-300">
                                        GH₵ {{ number_format($row['gross_volume_ghs'] ?? $row['gross_volume'] ?? 0, 0) }}
                                        <span class="text-gray-400">(${{ number_format($row['gross_volume_usd'] ?? 0, 0) }})</span>
                                    </td>
                                    <td class="py-2.5 px-3 text-right text-emerald-600 dark:text-emerald-400 font-bold">
                                        GH₵ {{ number_format($row['total_commission_ghs'] ?? $row['total_commission'] ?? 0, 0) }}
                                        <span class="text-blue-600 dark:text-blue-400">(${{ number_format($row['total_commission_usd'] ?? 0, 0) }})</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Monthly Table Summary --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700/60 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Monthly Commission Trends (Last 6 Months)</h3>
                        <button @click="activeTab = 'monthly'" class="text-xs font-semibold text-amber-600 dark:text-amber-400 hover:underline">
                            View All 12 Months &rarr;
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-500 dark:text-gray-400 uppercase">
                                <tr>
                                    <th class="py-2.5 px-3">Month</th>
                                    <th class="py-2.5 px-3">Trips</th>
                                    <th class="py-2.5 px-3">Cash Auto-Deductions</th>
                                    <th class="py-2.5 px-3 text-right">Total Commission</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium">
                                @foreach(array_slice($monthlySeries, 0, 6) as $row)
                                <tr class="{{ $row['is_current'] ? 'bg-amber-50/40 dark:bg-amber-950/20 font-bold' : '' }}">
                                    <td class="py-2.5 px-3">
                                        {{ $row['month_label'] }}
                                        @if($row['is_current'])
                                            <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded bg-amber-500 text-white">Current</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 text-gray-600 dark:text-gray-300">{{ $row['trips_count'] }}</td>
                                    <td class="py-2.5 px-3 text-amber-600 dark:text-amber-400">
                                        GH₵ {{ number_format($row['cash_commission_ghs'] ?? $row['cash_commission'] ?? 0, 0) }}
                                        <span class="text-gray-400">(${{ number_format($row['cash_commission_usd'] ?? 0, 0) }})</span>
                                    </td>
                                    <td class="py-2.5 px-3 text-right text-emerald-600 dark:text-emerald-400 font-bold">
                                        GH₵ {{ number_format($row['total_commission_ghs'] ?? $row['total_commission'] ?? 0, 0) }}
                                        <span class="text-blue-600 dark:text-blue-400">(${{ number_format($row['total_commission_usd'] ?? 0, 0) }})</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 2: DAILY BREAKDOWN (14 DAYS) --}}
        <div x-show="activeTab === 'daily'" class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700/60 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Daily Platform Commissions (Past 14 Days)</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Day-by-day platform commission revenue, trip counts, and cash auto-deductions in GH₵ (GHS) and $ (USD).</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-500 dark:text-gray-400 text-xs uppercase">
                        <tr>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Day</th>
                            <th class="py-3 px-4">Completed Trips</th>
                            <th class="py-3 px-4">Cash Trips</th>
                            <th class="py-3 px-4">Gross Fare Volume</th>
                            <th class="py-3 px-4">Cash Deductions</th>
                            <th class="py-3 px-4">Online Commissions</th>
                            <th class="py-3 px-4 text-right">Total Commission</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 text-xs">
                        @foreach($dailySeries as $row)
                        <tr class="{{ $row['is_today'] ? 'bg-amber-50/50 dark:bg-amber-950/20 font-bold' : 'hover:bg-gray-50/50 dark:hover:bg-gray-700/30' }}">
                            <td class="py-3 px-4 font-semibold text-gray-900 dark:text-white">
                                {{ $row['date_label'] }}
                                @if($row['is_today'])
                                    <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded bg-amber-500 text-white">Today</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-gray-500 dark:text-gray-400">{{ $row['day_name'] }}</td>
                            <td class="py-3 px-4 text-gray-700 dark:text-gray-300 font-semibold">{{ $row['trips_count'] }}</td>
                            <td class="py-3 px-4 text-amber-600 dark:text-amber-400">{{ $row['cash_trips_count'] }}</td>
                            <td class="py-3 px-4 text-gray-700 dark:text-gray-300">
                                GH₵ {{ number_format($row['gross_volume_ghs'] ?? 0, 0) }} (${{ number_format($row['gross_volume_usd'] ?? 0, 0) }})
                            </td>
                            <td class="py-3 px-4 text-amber-600 dark:text-amber-400 font-semibold">
                                GH₵ {{ number_format($row['cash_commission_ghs'] ?? 0, 0) }} (${{ number_format($row['cash_commission_usd'] ?? 0, 0) }})
                            </td>
                            <td class="py-3 px-4 text-blue-600 dark:text-blue-400 font-semibold">
                                GH₵ {{ number_format($row['online_commission_ghs'] ?? 0, 0) }} (${{ number_format($row['online_commission_usd'] ?? 0, 0) }})
                            </td>
                            <td class="py-3 px-4 text-right text-emerald-600 dark:text-emerald-400 font-black text-sm">
                                GH₵ {{ number_format($row['total_commission_ghs'] ?? 0, 0) }} (${{ number_format($row['total_commission_usd'] ?? 0, 0) }})
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TAB 3: WEEKLY BREAKDOWN (10 WEEKS) --}}
        <div x-show="activeTab === 'weekly'" class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700/60 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Weekly Platform Commissions (Past 10 Weeks)</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Aggregated weekly commission performance in GH₵ (GHS) and $ (USD).</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-500 dark:text-gray-400 text-xs uppercase">
                        <tr>
                            <th class="py-3 px-4">Period</th>
                            <th class="py-3 px-4">Trips Count</th>
                            <th class="py-3 px-4">Gross Fare Volume</th>
                            <th class="py-3 px-4">Cash Deductions</th>
                            <th class="py-3 px-4">Online Commissions</th>
                            <th class="py-3 px-4 text-right">Total Commission</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 text-xs">
                        @foreach($weeklySeries as $row)
                        <tr class="{{ $row['is_current'] ? 'bg-amber-50/50 dark:bg-amber-950/20 font-bold' : 'hover:bg-gray-50/50 dark:hover:bg-gray-700/30' }}">
                            <td class="py-3 px-4 font-semibold text-gray-900 dark:text-white">
                                {{ $row['week_label'] }}
                                @if($row['is_current'])
                                    <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded bg-amber-500 text-white">Current</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-gray-700 dark:text-gray-300 font-semibold">{{ $row['trips_count'] }}</td>
                            <td class="py-3 px-4 text-gray-700 dark:text-gray-300">
                                GH₵ {{ number_format($row['gross_volume_ghs'] ?? 0, 0) }} (${{ number_format($row['gross_volume_usd'] ?? 0, 0) }})
                            </td>
                            <td class="py-3 px-4 text-amber-600 dark:text-amber-400 font-semibold">
                                GH₵ {{ number_format($row['cash_commission_ghs'] ?? 0, 0) }} (${{ number_format($row['cash_commission_usd'] ?? 0, 0) }})
                            </td>
                            <td class="py-3 px-4 text-blue-600 dark:text-blue-400 font-semibold">
                                GH₵ {{ number_format($row['online_commission_ghs'] ?? 0, 0) }} (${{ number_format($row['online_commission_usd'] ?? 0, 0) }})
                            </td>
                            <td class="py-3 px-4 text-right text-emerald-600 dark:text-emerald-400 font-black text-sm">
                                GH₵ {{ number_format($row['total_commission_ghs'] ?? 0, 0) }} (${{ number_format($row['total_commission_usd'] ?? 0, 0) }})
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TAB 4: MONTHLY BREAKDOWN (12 MONTHS) --}}
        <div x-show="activeTab === 'monthly'" class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700/60 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Monthly Platform Commissions (Past 12 Months)</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Month-by-month platform commission audit in GH₵ (GHS) and $ (USD).</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-500 dark:text-gray-400 text-xs uppercase">
                        <tr>
                            <th class="py-3 px-4">Month</th>
                            <th class="py-3 px-4">Completed Trips</th>
                            <th class="py-3 px-4">Gross Fare Volume</th>
                            <th class="py-3 px-4">Cash Deductions</th>
                            <th class="py-3 px-4">Online Commissions</th>
                            <th class="py-3 px-4 text-right">Total Commission</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 text-xs">
                        @foreach($monthlySeries as $row)
                        <tr class="{{ $row['is_current'] ? 'bg-amber-50/50 dark:bg-amber-950/20 font-bold' : 'hover:bg-gray-50/50 dark:hover:bg-gray-700/30' }}">
                            <td class="py-3 px-4 font-semibold text-gray-900 dark:text-white">
                                {{ $row['month_label'] }}
                                @if($row['is_current'])
                                    <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded bg-amber-500 text-white">Current</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-gray-700 dark:text-gray-300 font-semibold">{{ $row['trips_count'] }}</td>
                            <td class="py-3 px-4 text-gray-700 dark:text-gray-300">
                                GH₵ {{ number_format($row['gross_volume_ghs'] ?? 0, 0) }} (${{ number_format($row['gross_volume_usd'] ?? 0, 0) }})
                            </td>
                            <td class="py-3 px-4 text-amber-600 dark:text-amber-400 font-semibold">
                                GH₵ {{ number_format($row['cash_commission_ghs'] ?? 0, 0) }} (${{ number_format($row['cash_commission_usd'] ?? 0, 0) }})
                            </td>
                            <td class="py-3 px-4 text-blue-600 dark:text-blue-400 font-semibold">
                                GH₵ {{ number_format($row['online_commission_ghs'] ?? 0, 0) }} (${{ number_format($row['online_commission_usd'] ?? 0, 0) }})
                            </td>
                            <td class="py-3 px-4 text-right text-emerald-600 dark:text-emerald-400 font-black text-sm">
                                GH₵ {{ number_format($row['total_commission_ghs'] ?? 0, 0) }} (${{ number_format($row['total_commission_usd'] ?? 0, 0) }})
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TAB 5: YEARLY BREAKDOWN (5 YEARS) --}}
        <div x-show="activeTab === 'yearly'" class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700/60 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Yearly Platform Commissions (Past 5 Years)</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Annual platform revenue overview in GH₵ (GHS) and $ (USD).</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-500 dark:text-gray-400 text-xs uppercase">
                        <tr>
                            <th class="py-3 px-4">Year</th>
                            <th class="py-3 px-4">Total Trips</th>
                            <th class="py-3 px-4">Gross Fare Volume</th>
                            <th class="py-3 px-4">Cash Deductions</th>
                            <th class="py-3 px-4">Online Commissions</th>
                            <th class="py-3 px-4 text-right">Total Commission</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 text-xs">
                        @foreach($yearlySeries as $row)
                        <tr class="{{ $row['is_current'] ? 'bg-amber-50/50 dark:bg-amber-950/20 font-bold' : 'hover:bg-gray-50/50 dark:hover:bg-gray-700/30' }}">
                            <td class="py-3 px-4 font-semibold text-gray-900 dark:text-white">
                                {{ $row['year_label'] }}
                                @if($row['is_current'])
                                    <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded bg-amber-500 text-white">Current</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-gray-700 dark:text-gray-300 font-semibold">{{ $row['trips_count'] }}</td>
                            <td class="py-3 px-4 text-gray-700 dark:text-gray-300">
                                GH₵ {{ number_format($row['gross_volume_ghs'] ?? 0, 0) }} (${{ number_format($row['gross_volume_usd'] ?? 0, 0) }})
                            </td>
                            <td class="py-3 px-4 text-amber-600 dark:text-amber-400 font-semibold">
                                GH₵ {{ number_format($row['cash_commission_ghs'] ?? 0, 0) }} (${{ number_format($row['cash_commission_usd'] ?? 0, 0) }})
                            </td>
                            <td class="py-3 px-4 text-blue-600 dark:text-blue-400 font-semibold">
                                GH₵ {{ number_format($row['online_commission_ghs'] ?? 0, 0) }} (${{ number_format($row['online_commission_usd'] ?? 0, 0) }})
                            </td>
                            <td class="py-3 px-4 text-right text-emerald-600 dark:text-emerald-400 font-black text-sm">
                                GH₵ {{ number_format($row['total_commission_ghs'] ?? 0, 0) }} (${{ number_format($row['total_commission_usd'] ?? 0, 0) }})
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TAB 6: INDIVIDUAL DRIVER'S COMMISSIONS HISTORY --}}
        <div x-show="activeTab === 'drivers'" class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700/60 shadow-sm space-y-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Individual Driver's Commission History</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Detailed transaction ledger showing trip fares, commission rates, and main wallet deductions for each driver in GH₵ (GHS) and $ (USD).
                    </p>
                </div>
                
                {{-- Interactive Filters --}}
                <div class="flex flex-wrap items-center gap-2">
                    <input type="text" x-model="driverSearch" placeholder="Search driver or trip #..." 
                           class="text-xs px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-amber-500 w-44">

                    <select x-model="selectedType" class="text-xs px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-amber-500">
                        <option value="all">All Payment Types</option>
                        <option value="cash">Cash Trips (Auto-Deducted)</option>
                        <option value="online">Online / Split Payouts</option>
                    </select>

                    <select x-model="selectedVertical" class="text-xs px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-amber-500">
                        <option value="all">All Verticals</option>
                        <option value="ride">Ride Hailing</option>
                        <option value="chauffeur">Chauffeur / Driver Hire</option>
                        <option value="delivery">Package Delivery</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-500 dark:text-gray-400 uppercase">
                        <tr>
                            <th class="py-3 px-3">Date &amp; Time</th>
                            <th class="py-3 px-3">Driver</th>
                            <th class="py-3 px-3">Vertical</th>
                            <th class="py-3 px-3">Trip Reference</th>
                            <th class="py-3 px-3">Payment Mode</th>
                            <th class="py-3 px-3">Gross Fare</th>
                            <th class="py-3 px-3">Commission Rate</th>
                            <th class="py-3 px-3">Platform Commission</th>
                            <th class="py-3 px-3 text-right">Deduction Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium">
                        @forelse($driverHistory as $item)
                        <tr x-show="
                            (driverSearch === '' || '{{ strtolower($item['driver_name']) }}'.includes(driverSearch.toLowerCase()) || '{{ strtolower($item['trip_ref']) }}'.includes(driverSearch.toLowerCase())) &&
                            (selectedType === 'all' || (selectedType === 'cash' && {{ $item['is_cash'] ? 'true' : 'false' }}) || (selectedType === 'online' && !{{ $item['is_cash'] ? 'true' : 'false' }})) &&
                            (selectedVertical === 'all' || '{{ strtolower($item['vertical']) }}'.includes(selectedVertical.toLowerCase()))
                        " class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition">
                            <td class="py-3 px-3 whitespace-nowrap text-gray-500 dark:text-gray-400">
                                {{ $item['date'] }}
                            </td>
                            <td class="py-3 px-3">
                                <div class="font-bold text-gray-900 dark:text-white">{{ $item['driver_name'] }}</div>
                                @if(!empty($item['driver_phone']))
                                    <div class="text-[10px] text-gray-400">{{ $item['driver_phone'] }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                    @if(str_contains(strtolower($item['vertical']), 'chauffeur') || str_contains(strtolower($item['vertical']), 'hire'))
                                        bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300
                                    @elseif(str_contains(strtolower($item['vertical']), 'delivery'))
                                        bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300
                                    @else
                                        bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300
                                    @endif
                                ">
                                    {{ $item['vertical'] }}
                                </span>
                            </td>
                            <td class="py-3 px-3 font-mono text-[11px] text-gray-600 dark:text-gray-300">
                                {{ $item['trip_ref'] }}
                            </td>
                            <td class="py-3 px-3">
                                @if($item['is_cash'])
                                    <span class="inline-flex items-center gap-1 font-semibold text-amber-600 dark:text-amber-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Cash (Main Wallet Auto-Deduction)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 font-semibold text-blue-600 dark:text-blue-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                        Online Split
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-gray-800 dark:text-gray-200 font-semibold">
                                GH₵ {{ number_format($item['gross_fare_ghs'] ?? $item['gross_fare'] ?? 0, 0) }}
                                <span class="text-gray-400">(${{ number_format($item['gross_fare_usd'] ?? 0, 0) }})</span>
                            </td>
                            <td class="py-3 px-3 text-gray-500 dark:text-gray-400">
                                {{ number_format($item['commission_rate'], 0) }}%
                            </td>
                            <td class="py-3 px-3 font-bold text-emerald-600 dark:text-emerald-400">
                                GH₵ {{ number_format($item['commission_amount_ghs'] ?? $item['commission_amount'] ?? 0, 0) }}
                                <span class="text-blue-600 dark:text-blue-400">(${{ number_format($item['commission_amount_usd'] ?? 0, 0) }})</span>
                            </td>
                            <td class="py-3 px-3 text-right">
                                @if($item['is_cash'])
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                                        Deducted from Main Wallet
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                                        Gateway Disbursed
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-gray-400 dark:text-gray-500">
                                No commission transactions recorded yet. Completed trips with cash or online payments will appear here in real time.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TAB 7: DRIVERS WHO EXCEEDED 7-DAY PAYMENT DEADLINE --}}
        <div x-show="activeTab === 'overdue_drivers'" class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-rose-200 dark:border-rose-800/60 shadow-sm space-y-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold text-rose-700 dark:text-rose-400 flex items-center gap-2">
                        <span>🚨 Drivers Exceeding 7-Day Commission Payment Deadline</span>
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Drivers whose Cash Commission Wallet reached or exceeded GH₵100 and have exceeded the 7-day payment deadline. Their Driver Switch is automatically turned off and locked until payment is made. Once payment is made, their record is automatically removed from this tab and their Driver Switch is automatically turned on and unlocked.
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 uppercase">
                        <tr>
                            <th class="py-3 px-4">Driver</th>
                            <th class="py-3 px-4">Contact</th>
                            <th class="py-3 px-4">Unpaid Commission</th>
                            <th class="py-3 px-4">GH₵100 Reached On</th>
                            <th class="py-3 px-4">7-Day Deadline</th>
                            <th class="py-3 px-4">Overdue Duration</th>
                            <th class="py-3 px-4 text-right">Driver Switch Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium">
                        @forelse(($overdueDrivers ?? []) as $od)
                        <tr class="hover:bg-rose-50/30 dark:hover:bg-rose-950/20 transition">
                            <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">
                                {{ $od['driver_name'] }}
                            </td>
                            <td class="py-3 px-4 text-gray-600 dark:text-gray-300">
                                <div>{{ $od['driver_phone'] }}</div>
                                <div class="text-[10px] text-gray-400">{{ $od['driver_email'] }}</div>
                            </td>
                            <td class="py-3 px-4 font-black text-rose-600 dark:text-rose-400 text-sm">
                                GH₵ {{ number_format($od['commission_ghs'] ?? 0, 0) }}
                                <span class="text-xs text-gray-400 font-semibold">(${{ number_format($od['commission_usd'] ?? 0, 0) }})</span>
                            </td>
                            <td class="py-3 px-4 text-gray-600 dark:text-gray-300">
                                {{ $od['threshold_reached_at'] }}
                            </td>
                            <td class="py-3 px-4 text-rose-600 dark:text-rose-400 font-bold">
                                {{ $od['deadline_at'] }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-200">
                                    {{ $od['days_overdue'] }} {{ $od['days_overdue'] == 1 ? 'day' : 'days' }} overdue
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-600 text-white">
                                    🔒 OFF &amp; LOCKED
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-400 dark:text-gray-500">
                                ✅ No drivers have exceeded the 7-day commission payment deadline. Drivers who exceed the deadline appear here automatically and are removed automatically once they pay.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-filament-panels::page>
