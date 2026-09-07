<div class="fi-topbar-quick-chips flex items-center gap-2 mr-4">
    <!-- Live Platform Operational Indicator -->
    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/25 text-emerald-700 dark:text-emerald-400 text-xs font-bold shadow-xs">
        <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
        </span>
        <span class="tracking-wide uppercase text-[10px]">Live System</span>
    </div>

    <!-- Quick Link: Live Delivery Tracker -->
    <a href="{{ url('/admin/live-delivery-tracker-standalone') }}" 
       title="Live Interactive Dispatch & Delivery Radar"
       class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl text-xs font-bold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-white/5 hover:bg-emerald-500/15 hover:text-emerald-600 dark:hover:text-emerald-400 border border-transparent hover:border-emerald-500/30 transition-all duration-200">
        <span class="text-sm">⚡</span>
        <span class="hidden md:inline">Radar</span>
    </a>

    <!-- Quick Link: App Settings Hub -->
    <a href="{{ url('/admin/manage-app-settings') }}" 
       title="Manage Payment Keys, Twilio, SMTP & OAuth"
       class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl text-xs font-bold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-white/5 hover:bg-amber-500/15 hover:text-amber-600 dark:hover:text-amber-400 border border-transparent hover:border-amber-500/30 transition-all duration-200">
        <span class="text-sm">⚙️</span>
        <span class="hidden md:inline">Settings Hub</span>
    </a>

    <!-- Quick Link: Dynamic Ride Categories -->
    <a href="{{ url('/admin/ride-categories') }}" 
       title="Manage Vehicle Categories & Pricing Rules"
       class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl text-xs font-bold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-white/5 hover:bg-cyan-500/15 hover:text-cyan-600 dark:hover:text-cyan-400 border border-transparent hover:border-cyan-500/30 transition-all duration-200">
        <span class="text-sm">🚕</span>
        <span class="hidden lg:inline">Fleet & Fares</span>
    </a>

    <!-- External Link: Public Website -->
    <a href="{{ url('/') }}" 
       target="_blank" 
       rel="noopener noreferrer"
       title="Open Public Customer Website in New Tab"
       class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl text-xs font-bold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-white/5 hover:bg-indigo-500/15 hover:text-indigo-600 dark:hover:text-indigo-400 border border-transparent hover:border-indigo-500/30 transition-all duration-200">
        <span class="text-sm">🌐</span>
        <span class="hidden lg:inline">Live Site</span>
        <svg class="w-3 h-3 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
        </svg>
    </a>
</div>
