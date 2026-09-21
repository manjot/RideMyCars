<!-- Interactive Investor Portal Sub-Navigation Bar -->
<div class="sticky top-20 z-40 bg-white/95 dark:bg-[#0f172a]/95 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 transition-colors shadow-xs">
    <div class="max-w-7xl 2xl:max-w-[1600px] w-full mx-auto px-4 sm:px-6 lg:px-8 xl:px-12">
        <div class="flex items-center justify-between py-2.5 gap-4">
            
            <!-- Left: Live Cohort Status Pill -->
            <div class="hidden lg:flex items-center gap-2 shrink-0">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                </span>
                <span class="text-[11px] font-black uppercase tracking-wider text-slate-700 dark:text-slate-300">
                    Ghana Cohort: <span class="text-amber-600 dark:text-amber-400 font-bold">50 GHC/day Basic Rev</span>
                </span>
                <span class="text-slate-300 dark:text-slate-700">•</span>
                <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                    85–92% Targeted Margin
                </span>
            </div>

            <!-- Center: Horizontal Navigation Tabs -->
            <nav class="flex items-center gap-1 sm:gap-1.5 overflow-x-auto no-scrollbar py-0.5 w-full lg:w-auto justify-start lg:justify-center">
                <a href="/investor" 
                   class="px-3 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap {{ request()->is('investor') ? 'bg-slate-900 text-white dark:bg-amber-500 dark:text-slate-950 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/70' }}">
                    Overview
                </a>
                <a href="/investor/why-invest" 
                   class="px-3 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap {{ request()->is('investor/why-invest*') ? 'bg-slate-900 text-white dark:bg-amber-500 dark:text-slate-950 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/70' }}">
                    Why Invest
                </a>
                <a href="/investor/opportunity" 
                   class="px-3 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap {{ request()->is('investor/opportunity*') ? 'bg-slate-900 text-white dark:bg-amber-500 dark:text-slate-950 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/70' }}">
                    Financial Model
                </a>
                <a href="/investor/plans" 
                   class="px-3 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap {{ request()->is('investor/plans*') ? 'bg-slate-900 text-white dark:bg-amber-500 dark:text-slate-950 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/70' }}">
                    Tranches & Plans
                </a>
                <a href="/investor/faq" 
                   class="px-3 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap {{ request()->is('investor/faq*') ? 'bg-slate-900 text-white dark:bg-amber-500 dark:text-slate-950 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/70' }}">
                    Compliance FAQ
                </a>
                <a href="/investor/contact" 
                   class="px-3 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap {{ request()->is('investor/contact*') ? 'bg-slate-900 text-white dark:bg-amber-500 dark:text-slate-950 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/70' }}">
                    Contact IR
                </a>
                <a href="/investor/regulatory-notices" 
                   class="px-3 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap {{ request()->is('investor/regulatory-notices*') ? 'bg-slate-900 text-white dark:bg-amber-500 dark:text-slate-950 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/70' }}">
                    Disclosures
                </a>
                @auth
                <a href="/investor/dashboard" 
                   class="px-3 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap {{ request()->is('investor/dashboard*') ? 'bg-slate-900 text-white dark:bg-amber-500 dark:text-slate-950 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/70' }}">
                    Dashboard
                </a>
                @endauth
            </nav>

            <!-- Right: Quick Action Buttons & Instant Theme Toggle -->
            <div class="hidden sm:flex items-center gap-2 shrink-0">
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
                   class="px-3 py-1.5 rounded-full text-xs font-bold text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/70 transition-colors border border-slate-200 dark:border-slate-700">
                    Login
                </a>
                <a href="/investor/register" 
                   class="px-3.5 py-1.5 rounded-full text-xs font-black text-slate-950 bg-amber-400 hover:bg-amber-300 shadow-xs hover:scale-105 transition-all flex items-center gap-1">
                    <span>Accredit</span>
                    <span>→</span>
                </a>
                @else
                <a href="/investor/dashboard" 
                   class="px-3.5 py-1.5 rounded-full text-xs font-black text-slate-950 bg-amber-400 hover:bg-amber-300 shadow-xs hover:scale-105 transition-all flex items-center gap-1">
                    <span>Portal Hub</span>
                    <span>→</span>
                </a>
                @endguest
            </div>

        </div>
    </div>
</div>
