<!-- Interactive Investor Portal Sub-Navigation Bar -->
<div class="sticky top-20 z-40 bg-white/95 dark:bg-[#0f172a]/95 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 transition-colors shadow-xs">
    
    <!-- 1. Institutional Micro-Ticker: Live Cohort Status & Sovereign Gateway -->
    <div class="bg-slate-900 dark:bg-slate-950 text-white text-[11px] py-1 border-b border-slate-800/80 transition-colors">
        <div class="max-w-7xl 2xl:max-w-[1600px] w-full mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2 overflow-hidden">
                <span class="relative flex h-2 w-2 shrink-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span class="font-black uppercase tracking-wider text-slate-200 shrink-0">
                    Cohort 1: <span class="text-amber-400 font-bold">500 UPD (Users Per Day)</span>
                </span>
                <span class="text-slate-600 shrink-0 hidden sm:inline">•</span>
                <span class="text-emerald-400 font-bold shrink-0 hidden sm:inline">
                    85–92% Targeted Margin
                </span>
                <span class="text-slate-600 shrink-0 hidden md:inline">•</span>
                <span class="text-slate-300 font-mono font-bold shrink-0 hidden md:inline">
                    25,000 GHC/day Target
                </span>
            </div>
            <div class="flex items-center gap-3 shrink-0 text-[10px] text-slate-400">
                <span class="hidden sm:inline font-medium">⚖️ SEC Ghana & Global Sovereign Escrow</span>
                <a href="/investor/contact" class="text-amber-400 hover:text-amber-300 font-bold transition-colors">Contact IR →</a>
            </div>
        </div>
    </div>

    <!-- 2. Main Portal Navigation & Quick Actions -->
    <div class="max-w-7xl 2xl:max-w-[1600px] w-full mx-auto px-4 sm:px-6 lg:px-8 xl:px-12">
        <div class="flex items-center justify-between py-2 gap-3">
            
            <!-- Left: Portal Identity -->
            <a href="/investor" class="flex items-center gap-2 group shrink-0">
                <span class="w-6 h-6 rounded-md bg-amber-400 text-slate-950 flex items-center justify-center font-black text-xs shadow-xs group-hover:scale-105 transition-transform">
                    ⚡
                </span>
                <span class="text-xs font-black tracking-tight text-slate-900 dark:text-white uppercase hidden xl:inline">
                    Investor Portal
                </span>
            </a>

            <!-- Center (Desktop lg+): Clean Navigation Tabs without Overflow -->
            <nav class="hidden lg:flex items-center gap-1 sm:gap-1.5 justify-center flex-wrap">
                <a href="/investor" 
                   class="px-2.5 xl:px-3 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap {{ request()->is('investor') ? 'bg-slate-900 text-white dark:bg-amber-500 dark:text-slate-950 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/70' }}">
                    Overview
                </a>
                <a href="/investor/why-invest" 
                   class="px-2.5 xl:px-3 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap {{ request()->is('investor/why-invest*') ? 'bg-slate-900 text-white dark:bg-amber-500 dark:text-slate-950 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/70' }}">
                    Why Invest
                </a>
                <a href="/investor/opportunity" 
                   class="px-2.5 xl:px-3 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap {{ request()->is('investor/opportunity*') ? 'bg-slate-900 text-white dark:bg-amber-500 dark:text-slate-950 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/70' }}">
                    Financial Model
                </a>
                <a href="/investor/plans" 
                   class="px-2.5 xl:px-3 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap {{ request()->is('investor/plans*') ? 'bg-slate-900 text-white dark:bg-amber-500 dark:text-slate-950 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/70' }}">
                    Tranches & Plans
                </a>
                <a href="/investor/faq" 
                   class="px-2.5 xl:px-3 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap {{ request()->is('investor/faq*') ? 'bg-slate-900 text-white dark:bg-amber-500 dark:text-slate-950 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/70' }}">
                    Compliance FAQ
                </a>
                <a href="/investor/contact" 
                   class="px-2.5 xl:px-3 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap {{ request()->is('investor/contact*') ? 'bg-slate-900 text-white dark:bg-amber-500 dark:text-slate-950 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/70' }}">
                    Contact IR
                </a>
                <a href="/investor/regulatory-notices" 
                   class="px-2.5 xl:px-3 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap {{ request()->is('investor/regulatory-notices*') ? 'bg-slate-900 text-white dark:bg-amber-500 dark:text-slate-950 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/70' }}">
                    Disclosures
                </a>
                @auth
                <a href="/investor/dashboard" 
                   class="px-2.5 xl:px-3 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap {{ request()->is('investor/dashboard*') ? 'bg-slate-900 text-white dark:bg-amber-500 dark:text-slate-950 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/70' }}">
                    Dashboard
                </a>
                @endauth
            </nav>

            <!-- Responsive Mobile/Tablet Alternative: Clean Section Selector (lg:hidden) -->
            <div class="lg:hidden flex-1 max-w-[220px] sm:max-w-[280px]">
                <div class="relative">
                    <select onchange="window.location.href=this.value"
                            class="w-full appearance-none bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-xs font-bold py-1.5 pl-3 pr-8 rounded-full border border-slate-200 dark:border-slate-700 shadow-xs focus:outline-none focus:ring-2 focus:ring-amber-400">
                        <option value="/investor" {{ request()->is('investor') ? 'selected' : '' }}>📑 Section: Overview</option>
                        <option value="/investor/why-invest" {{ request()->is('investor/why-invest*') ? 'selected' : '' }}>💡 Section: Why Invest</option>
                        <option value="/investor/opportunity" {{ request()->is('investor/opportunity*') ? 'selected' : '' }}>📊 Section: Financial Model</option>
                        <option value="/investor/plans" {{ request()->is('investor/plans*') ? 'selected' : '' }}>💎 Section: Tranches & Plans</option>
                        <option value="/investor/faq" {{ request()->is('investor/faq*') ? 'selected' : '' }}>⚖️ Section: Compliance FAQ</option>
                        <option value="/investor/contact" {{ request()->is('investor/contact*') ? 'selected' : '' }}>📞 Section: Contact IR</option>
                        <option value="/investor/regulatory-notices" {{ request()->is('investor/regulatory-notices*') ? 'selected' : '' }}>📜 Section: Disclosures</option>
                        @auth
                        <option value="/investor/dashboard" {{ request()->is('investor/dashboard*') ? 'selected' : '' }}>📈 Section: Dashboard</option>
                        @endauth
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-500">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Right: Quick Action Buttons & Theme Toggle -->
            <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                <!-- Theme Toggle directly on Investor Bar -->
                <button @click="darkMode = !darkMode" 
                        type="button"
                        class="p-1.5 rounded-full text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors border border-slate-200 dark:border-slate-700 text-xs" 
                        title="Toggle Bright Light / Dark Mode">
                    <span x-show="!darkMode">🌙</span>
                    <span x-show="darkMode">☀️</span>
                </button>

                @guest
                <a href="/investor/login" 
                   class="px-2.5 sm:px-3 py-1.5 rounded-full text-xs font-bold text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-colors border border-slate-200 dark:border-slate-700">
                    Login
                </a>
                <a href="/investor/register" 
                   class="px-3 sm:px-3.5 py-1.5 rounded-full text-xs font-black text-slate-950 bg-amber-400 hover:bg-amber-300 shadow-xs hover:scale-105 transition-all flex items-center gap-1">
                    <span>Accredit</span>
                    <span>→</span>
                </a>
                @else
                <a href="/investor/dashboard" 
                   class="px-3 sm:px-3.5 py-1.5 rounded-full text-xs font-black text-slate-950 bg-amber-400 hover:bg-amber-300 shadow-xs hover:scale-105 transition-all flex items-center gap-1">
                    <span>Portal Hub</span>
                    <span>→</span>
                </a>
                @endguest
            </div>

        </div>
    </div>
</div>
