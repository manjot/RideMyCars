<x-filament-widgets::widget>
    <div class="relative overflow-hidden rounded-3xl border border-gray-200/80 dark:border-white/10 shadow-xl bg-gradient-to-br from-slate-900 via-[#0f172a] to-[#1e1b4b] text-white p-6 sm:p-8">
        
        <!-- Decorative Ambient Glow Gradients -->
        <div class="absolute -right-20 -top-20 w-80 h-80 bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-20 -bottom-20 w-80 h-80 bg-cyan-500/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute left-1/2 top-0 w-96 h-48 bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10">
            <!-- Header Row: Greeting & Live Badges -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-6 border-b border-white/10">
                <div>
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="px-2.5 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[11px] font-extrabold uppercase tracking-wider">
                            Executive Dashboard
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[11px] font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            Live System
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white flex items-center gap-2">
                        Welcome to RideMyCars Control Center
                        <span class="inline-block animate-bounce text-xl">✨</span>
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-300 font-medium mt-1">
                        Full platform dispatch, multi-currency pricing, and real-time operations across rides, rentals, and parcel delivery.
                    </p>
                </div>

                <!-- Quick Live Metric Chips -->
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <div class="flex items-center gap-2 px-3 py-2 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md">
                        <span class="text-lg">🚕</span>
                        <div>
                            <div class="text-[10px] uppercase font-bold text-slate-400">Categories</div>
                            <div class="text-sm font-extrabold text-amber-400">{{ $activeCategoriesCount }} Active</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 px-3 py-2 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md">
                        <span class="text-lg">🚘</span>
                        <div>
                            <div class="text-[10px] uppercase font-bold text-slate-400">Fleet</div>
                            <div class="text-sm font-extrabold text-cyan-400">{{ $totalVehiclesCount }} Cars</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 px-3 py-2 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md">
                        <span class="text-lg">👨‍✈️</span>
                        <div>
                            <div class="text-[10px] uppercase font-bold text-slate-400">Chauffeurs</div>
                            <div class="text-sm font-extrabold text-emerald-400">{{ $verifiedDriversCount }} Verified</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4 Colorful Quick Action Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 pt-6">
                
                <!-- Card 1: App Settings Hub -->
                <a href="{{ url('/admin/manage-app-settings') }}" 
                   class="group relative p-4 rounded-2xl bg-white/[0.04] hover:bg-white/[0.09] border border-white/10 hover:border-amber-400/40 transition-all duration-200 shadow-sm hover:shadow-lg hover:-translate-y-0.5 flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-xl shadow-md shadow-amber-500/25 shrink-0 group-hover:scale-105 transition-transform">
                        ⚙️
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-xs font-black text-white group-hover:text-amber-300 transition-colors flex items-center justify-between">
                            <span>Settings Hub</span>
                            <span class="text-amber-400 text-xs">→</span>
                        </div>
                        <div class="text-[11px] text-slate-300 font-medium truncate">Stripe, Twilio, SMTP, OAuth</div>
                    </div>
                </a>

                <!-- Card 2: Ride Categories & Pricing -->
                <a href="{{ url('/admin/ride-categories') }}" 
                   class="group relative p-4 rounded-2xl bg-white/[0.04] hover:bg-white/[0.09] border border-white/10 hover:border-cyan-400/40 transition-all duration-200 shadow-sm hover:shadow-lg hover:-translate-y-0.5 flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-xl shadow-md shadow-cyan-500/25 shrink-0 group-hover:scale-105 transition-transform">
                        🚕
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-xs font-black text-white group-hover:text-cyan-300 transition-colors flex items-center justify-between">
                            <span>Ride Categories</span>
                            <span class="text-cyan-400 text-xs">→</span>
                        </div>
                        <div class="text-[11px] text-slate-300 font-medium truncate">Base fares, rates & surge</div>
                    </div>
                </a>

                <!-- Card 3: Live Delivery Radar -->
                <a href="{{ url('/admin/live-delivery-tracker-standalone') }}" 
                   class="group relative p-4 rounded-2xl bg-white/[0.04] hover:bg-white/[0.09] border border-white/10 hover:border-emerald-400/40 transition-all duration-200 shadow-sm hover:shadow-lg hover:-translate-y-0.5 flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-xl shadow-md shadow-emerald-500/25 shrink-0 group-hover:scale-105 transition-transform">
                        ⚡
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-xs font-black text-white group-hover:text-emerald-300 transition-colors flex items-center justify-between">
                            <span>Delivery Radar</span>
                            <span class="text-emerald-400 text-xs">→</span>
                        </div>
                        <div class="text-[11px] text-slate-300 font-medium truncate">Interactive live GPS tracking</div>
                    </div>
                </a>

                <!-- Card 4: Fleet & Vehicles -->
                <a href="{{ url('/admin/vehicles') }}" 
                   class="group relative p-4 rounded-2xl bg-white/[0.04] hover:bg-white/[0.09] border border-white/10 hover:border-purple-400/40 transition-all duration-200 shadow-sm hover:shadow-lg hover:-translate-y-0.5 flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-xl shadow-md shadow-purple-500/25 shrink-0 group-hover:scale-105 transition-transform">
                        🚗
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-xs font-black text-white group-hover:text-purple-300 transition-colors flex items-center justify-between">
                            <span>Fleet & Rentals</span>
                            <span class="text-purple-400 text-xs">→</span>
                        </div>
                        <div class="text-[11px] text-slate-300 font-medium truncate">Manage vehicles & inspections</div>
                    </div>
                </a>

            </div>
        </div>
    </div>
</x-filament-widgets::widget>
