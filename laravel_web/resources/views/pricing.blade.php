<x-layout>
    <x-slot:title>Pricing & Transparent Rates — RideMyCars Executive Mobility</x-slot>

    <!-- Ambient Glow Effects -->
    <div class="relative overflow-hidden bg-gray-50/50 dark:bg-[#0b0f17] text-gray-900 dark:text-white transition-colors">
        <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[850px] h-[450px] bg-gradient-to-tr from-amber-500/15 via-brand-500/10 to-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>

        <main class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 pb-24 space-y-16"
              x-data="{
                  activeTab: 'all', // 'all', 'rides', 'rentals', 'drivers', 'delivery', 'membership'
                  calcDistance: 12,
                  calcStops: 0,
                  calcVehicle: 'economy',
                  rentalPeriod: 'daily', // 'daily', 'weekly', 'monthly'
                  activeFaq: null,
                  vehicles: {
                      economy: { name: 'Economy', icon: '🚗', multiplier: 1.0, base: 5.00, perKm: 1.50, perMin: 0.25, min: 10.00, seats: '4 Seats', luggage: '2 Bags', desc: 'Affordable, reliable everyday city mobility' },
                      comfort: { name: 'Standard / Comfort', icon: '🚘', multiplier: 1.2, base: 6.00, perKm: 1.80, perMin: 0.30, min: 12.00, seats: '4 Seats', luggage: '3 Bags', desc: 'Spacious, newer executive sedans with climate control' },
                      suv: { name: 'Executive SUV', icon: '🚙', multiplier: 1.4, base: 7.00, perKm: 2.10, perMin: 0.35, min: 15.00, seats: '6 Seats', luggage: '5 Bags', desc: 'Luxury high-ride SUVs for comfort, safety & groups' },
                      xl: { name: 'Van XL', icon: '🚐', multiplier: 1.5, base: 7.50, perKm: 2.25, perMin: 0.38, min: 18.00, seats: '7–8 Seats', luggage: '6 Bags', desc: 'Large premium passenger vans for families & delegations' },
                      luxury: { name: 'VIP Chauffeur', icon: '🏎️', multiplier: 1.8, base: 9.00, perKm: 2.70, perMin: 0.45, min: 25.00, seats: '4 Seats', luggage: '3 Bags', desc: 'Flagship luxury sedans with suited, vetted private drivers' }
                  },
                  get calcDuration() {
                      return Math.max(5, Math.round(this.calcDistance * 1.6 + 4));
                  },
                  get calcFare() {
                      const v = this.vehicles[this.calcVehicle] || this.vehicles.economy;
                      const distFare = this.calcDistance * v.perKm;
                      const durFare = this.calcDuration * v.perMin;
                      const stopsFee = this.calcStops * 3.50;
                      const subtotal = v.base + distFare + durFare + stopsFee;
                      const finalFare = Math.max(v.min, subtotal);
                      const tax = finalFare * 0.05;
                      const total = finalFare + tax;
                      return {
                          base: v.base.toFixed(2),
                          distFare: distFare.toFixed(2),
                          durFare: durFare.toFixed(2),
                          stopsFee: stopsFee.toFixed(2),
                          subtotal: subtotal.toFixed(2),
                          tax: tax.toFixed(2),
                          total: total.toFixed(2)
                      };
                  }
              }">

            <!-- HERO HEADER -->
            <div class="text-center max-w-3xl mx-auto pt-6 sm:pt-10">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-brand-500/10 dark:bg-brand-500/15 border border-brand-500/25 text-brand-600 dark:text-brand-400 font-extrabold text-xs uppercase tracking-widest mb-5 shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>100% Upfront, Transparent Pricing</span>
                </div>
                
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-gray-900 dark:text-white tracking-tight leading-[1.1] mb-5">
                    Clear Pricing. <span class="bg-gradient-to-r from-amber-500 via-brand-500 to-amber-600 bg-clip-text text-transparent">Zero Surge Surprises.</span>
                </h1>
                
                <p class="text-base sm:text-lg text-gray-600 dark:text-gray-300 leading-relaxed font-medium max-w-2xl mx-auto">
                    From on-demand executive rides and self-drive car rentals to certified private chauffeurs and express parcel courier dispatch. What you see is exactly what you pay.
                </p>

                <!-- Core Trust Pillars -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 mt-8 max-w-4xl mx-auto">
                    <div class="p-3 bg-white dark:bg-[#151b26] rounded-2xl border border-gray-200/80 dark:border-white/10 shadow-xs flex items-center gap-2.5">
                        <span class="text-lg">🛡️</span>
                        <div class="text-left">
                            <p class="text-xs font-black text-gray-900 dark:text-white leading-tight">Price Lock</p>
                            <p class="text-[10px] text-gray-500 dark:text-gray-400">Guaranteed upfront fare</p>
                        </div>
                    </div>
                    <div class="p-3 bg-white dark:bg-[#151b26] rounded-2xl border border-gray-200/80 dark:border-white/10 shadow-xs flex items-center gap-2.5">
                        <span class="text-lg">🚫</span>
                        <div class="text-left">
                            <p class="text-xs font-black text-gray-900 dark:text-white leading-tight">No Surge Traps</p>
                            <p class="text-[10px] text-gray-500 dark:text-gray-400">Predictable rates always</p>
                        </div>
                    </div>
                    <div class="p-3 bg-white dark:bg-[#151b26] rounded-2xl border border-gray-200/80 dark:border-white/10 shadow-xs flex items-center gap-2.5">
                        <span class="text-lg">🔒</span>
                        <div class="text-left">
                            <p class="text-xs font-black text-gray-900 dark:text-white leading-tight">256-Bit SSL</p>
                            <p class="text-[10px] text-gray-500 dark:text-gray-400">Secure card & Momo pay</p>
                        </div>
                    </div>
                    <div class="p-3 bg-white dark:bg-[#151b26] rounded-2xl border border-gray-200/80 dark:border-white/10 shadow-xs flex items-center gap-2.5">
                        <span class="text-lg">💎</span>
                        <div class="text-left">
                            <p class="text-xs font-black text-gray-900 dark:text-white leading-tight">VIP Discounts</p>
                            <p class="text-[10px] text-gray-500 dark:text-gray-400">Club membership perks</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- INTERACTIVE SERVICE NAVIGATION BAR (STICKY OR PILL TABS) -->
            <div class="sticky top-20 z-30 py-2 backdrop-blur-md bg-gray-50/80 dark:bg-[#0b0f17]/80 rounded-2xl">
                <div class="flex items-center justify-start sm:justify-center gap-2 overflow-x-auto no-scrollbar py-1 px-2">
                    <button type="button" @click="activeTab = 'all'"
                            :class="activeTab === 'all' ? 'bg-black text-white dark:bg-white dark:text-black shadow-md' : 'bg-white dark:bg-[#151b26] text-gray-700 dark:text-gray-300 border border-gray-200/80 dark:border-white/10 hover:border-brand-500'"
                            class="px-4 py-2.5 rounded-xl text-xs font-extrabold transition-all whitespace-nowrap flex items-center gap-2 cursor-pointer">
                        <span>🌟</span>
                        <span>All Services</span>
                    </button>
                    <button type="button" @click="activeTab = 'rides'"
                            :class="activeTab === 'rides' ? 'bg-black text-white dark:bg-white dark:text-black shadow-md' : 'bg-white dark:bg-[#151b26] text-gray-700 dark:text-gray-300 border border-gray-200/80 dark:border-white/10 hover:border-brand-500'"
                            class="px-4 py-2.5 rounded-xl text-xs font-extrabold transition-all whitespace-nowrap flex items-center gap-2 cursor-pointer">
                        <span>🚗</span>
                        <span>Ride Hailing</span>
                    </button>
                    <button type="button" @click="activeTab = 'rentals'"
                            :class="activeTab === 'rentals' ? 'bg-black text-white dark:bg-white dark:text-black shadow-md' : 'bg-white dark:bg-[#151b26] text-gray-700 dark:text-gray-300 border border-gray-200/80 dark:border-white/10 hover:border-brand-500'"
                            class="px-4 py-2.5 rounded-xl text-xs font-extrabold transition-all whitespace-nowrap flex items-center gap-2 cursor-pointer">
                        <span>🔑</span>
                        <span>Car Rentals</span>
                    </button>
                    <button type="button" @click="activeTab = 'drivers'"
                            :class="activeTab === 'drivers' ? 'bg-black text-white dark:bg-white dark:text-black shadow-md' : 'bg-white dark:bg-[#151b26] text-gray-700 dark:text-gray-300 border border-gray-200/80 dark:border-white/10 hover:border-brand-500'"
                            class="px-4 py-2.5 rounded-xl text-xs font-extrabold transition-all whitespace-nowrap flex items-center gap-2 cursor-pointer">
                        <span>👨‍✈️</span>
                        <span>Driver Hire</span>
                    </button>
                    <button type="button" @click="activeTab = 'delivery'"
                            :class="activeTab === 'delivery' ? 'bg-black text-white dark:bg-white dark:text-black shadow-md' : 'bg-white dark:bg-[#151b26] text-gray-700 dark:text-gray-300 border border-gray-200/80 dark:border-white/10 hover:border-brand-500'"
                            class="px-4 py-2.5 rounded-xl text-xs font-extrabold transition-all whitespace-nowrap flex items-center gap-2 cursor-pointer">
                        <span>📦</span>
                        <span>Parcel Courier</span>
                    </button>
                    <button type="button" @click="activeTab = 'membership'"
                            :class="activeTab === 'membership' ? 'bg-black text-white dark:bg-white dark:text-black shadow-md' : 'bg-white dark:bg-[#151b26] text-gray-700 dark:text-gray-300 border border-gray-200/80 dark:border-white/10 hover:border-brand-500'"
                            class="px-4 py-2.5 rounded-xl text-xs font-extrabold transition-all whitespace-nowrap flex items-center gap-2 cursor-pointer">
                        <span>👑</span>
                        <span>VIP Club</span>
                    </button>
                </div>
            </div>

            <!-- INTERACTIVE FARE ESTIMATOR CALCULATOR (HIGHLIGHT FEATURE) -->
            <div x-show="['all', 'rides'].includes(activeTab)" class="w-full">
                <div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-br from-white via-white to-amber-500/5 dark:from-[#111622] dark:via-[#111622] dark:to-amber-500/10 border border-gray-200 dark:border-white/10 shadow-xl relative overflow-hidden">
                    <div class="absolute -top-24 -right-24 w-64 h-64 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="max-w-4xl mx-auto space-y-8">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 dark:border-white/10 pb-6">
                            <div>
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-brand-500/15 text-brand-600 dark:text-brand-400 border border-brand-500/25">
                                    Interactive Tool
                                </span>
                                <h2 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white mt-1.5">
                                    Instant Trip Fare Estimator
                                </h2>
                                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                    Adjust distance and select a vehicle class to calculate your exact guaranteed upfront price.
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                                <span class="text-xs font-bold text-gray-500 dark:text-gray-400">Live Formula Connected</span>
                            </div>
                        </div>

                        <!-- Calculator Controls Grid -->
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                            
                            <!-- Left: Sliders & Options (7 cols) -->
                            <div class="lg:col-span-7 space-y-6">
                                <!-- Vehicle Tier Selector -->
                                <div>
                                    <label class="block text-xs font-extrabold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2.5">
                                        1. Select Vehicle Tier
                                    </label>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                        <template x-for="(veh, key) in vehicles" :key="key">
                                            <button type="button" @click="calcVehicle = key"
                                                    :class="calcVehicle === key ? 'border-amber-500 bg-amber-50/50 dark:bg-amber-950/20 text-gray-900 dark:text-white ring-2 ring-amber-500/30' : 'border-gray-200 dark:border-white/10 hover:border-gray-300 dark:hover:border-white/20 text-gray-600 dark:text-gray-400'"
                                                    class="p-3 rounded-2xl border text-left transition-all cursor-pointer">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xl" x-text="veh.icon"></span>
                                                    <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400" x-text="'$' + veh.perKm.toFixed(2) + '/km'"></span>
                                                </div>
                                                <p class="font-extrabold text-xs mt-1.5 truncate" x-text="veh.name"></p>
                                                <p class="text-[10px] text-gray-500 truncate" x-text="veh.seats"></p>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <!-- Distance Slider -->
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <label class="text-xs font-extrabold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                            2. Estimated Trip Distance
                                        </label>
                                        <span class="text-sm font-black text-brand-600 dark:text-brand-400 font-mono" x-text="calcDistance + ' km'"></span>
                                    </div>
                                    <input type="range" min="1" max="100" step="1" x-model.number="calcDistance" 
                                           class="w-full h-2.5 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-amber-500">
                                    <div class="flex justify-between text-[10px] font-bold text-gray-400 mt-1">
                                        <span>1 km (City short)</span>
                                        <span>25 km (Suburban)</span>
                                        <span>50 km (Airport)</span>
                                        <span>100 km (Intercity)</span>
                                    </div>
                                </div>

                                <!-- Additional Stops -->
                                <div>
                                    <label class="block text-xs font-extrabold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">
                                        3. Additional Stops along the route
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <template x-for="st in [0, 1, 2, 3]" :key="st">
                                            <button type="button" @click="calcStops = st"
                                                    :class="calcStops === st ? 'bg-black text-white dark:bg-white dark:text-black font-extrabold shadow-sm' : 'bg-gray-100 dark:bg-[#1a1a1a] text-gray-700 dark:text-gray-300 font-bold hover:bg-gray-200'"
                                                    class="flex-1 py-2 rounded-xl text-xs transition-all text-center cursor-pointer">
                                                <span x-text="st === 0 ? 'Direct (0)' : (st + (st === 3 ? '+ Stops' : ' Stop'))"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Live Breakdown Receipt (5 cols) -->
                            <div class="lg:col-span-5 bg-gray-50 dark:bg-[#0b0f17] p-6 rounded-3xl border border-gray-200 dark:border-white/10 space-y-5">
                                <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-white/10">
                                    <div class="flex items-center gap-2">
                                        <span class="text-2xl" x-text="vehicles[calcVehicle].icon"></span>
                                        <div>
                                            <h4 class="text-sm font-black text-gray-900 dark:text-white" x-text="vehicles[calcVehicle].name"></h4>
                                            <p class="text-[10px] text-gray-500" x-text="vehicles[calcVehicle].seats + ' • ' + calcDuration + ' mins est.'"></p>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">Upfront Fare</span>
                                </div>

                                <!-- Breakdown Rows -->
                                <div class="space-y-2.5 text-xs">
                                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                        <span>Base Fare</span>
                                        <span class="font-mono font-bold text-gray-900 dark:text-white" x-text="'$' + calcFare.base"></span>
                                    </div>
                                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                        <span>Distance (<span x-text="calcDistance"></span> km @ $<span x-text="vehicles[calcVehicle].perKm.toFixed(2)"></span>/km)</span>
                                        <span class="font-mono font-bold text-gray-900 dark:text-white" x-text="'$' + calcFare.distFare"></span>
                                    </div>
                                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                        <span>Duration (~<span x-text="calcDuration"></span> min @ $<span x-text="vehicles[calcVehicle].perMin.toFixed(2)"></span>/min)</span>
                                        <span class="font-mono font-bold text-gray-900 dark:text-white" x-text="'$' + calcFare.durFare"></span>
                                    </div>
                                    <div x-show="calcStops > 0" class="flex justify-between text-gray-600 dark:text-gray-400">
                                        <span><span x-text="calcStops"></span> Additional Stop(s)</span>
                                        <span class="font-mono font-bold text-gray-900 dark:text-white" x-text="'$' + calcFare.stopsFee"></span>
                                    </div>
                                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                        <span>Service Tax & Regulatory (5%)</span>
                                        <span class="font-mono font-bold text-gray-900 dark:text-white" x-text="'$' + calcFare.tax"></span>
                                    </div>
                                </div>

                                <!-- Total Fare Highlight -->
                                <div class="pt-4 border-t border-gray-200 dark:border-white/10 flex items-center justify-between">
                                    <div>
                                        <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Estimated Total</p>
                                        <p class="text-3xl font-black text-emerald-600 dark:text-emerald-400 font-mono tracking-tight" x-text="'$' + calcFare.total"></p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-[10px] text-gray-400 block font-medium">No surge multiplier</span>
                                        <span class="text-[10px] text-emerald-500 font-bold block">✓ Guaranteed Cap</span>
                                    </div>
                                </div>

                                <!-- Direct CTA -->
                                <a :href="'/ride?type=' + encodeURIComponent(vehicles[calcVehicle].name)" 
                                   class="block w-full py-3.5 bg-brand-500 hover:bg-brand-600 text-slate-950 font-black text-sm rounded-2xl shadow-lg shadow-brand-500/25 transition-all text-center cursor-pointer hover:scale-[1.01] active:scale-[0.99]">
                                    Book <span x-text="vehicles[calcVehicle].name"></span> Now →
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 1: RIDE HAILING FLEET TIERS -->
            <section x-show="['all', 'rides'].includes(activeTab)" class="space-y-8">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                            <span class="text-xs font-black uppercase tracking-wider text-brand-600 dark:text-brand-400">Mobility Tier Rates</span>
                        </div>
                        <h2 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                            Ride Hailing & Executive Chauffeurs
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Transparent per-kilometer and per-minute breakdown for all ride types.
                        </p>
                    </div>
                    <a href="/ride" class="text-xs font-extrabold text-brand-600 dark:text-brand-400 hover:underline flex items-center gap-1">
                        <span>Open Live Booking Map</span>
                        <span>→</span>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    
                    <!-- 1. Economy -->
                    <div class="bg-white dark:bg-[#111622] rounded-3xl p-7 border border-gray-200 dark:border-white/10 shadow-xs hover:shadow-xl hover:border-brand-500/40 transition-all flex flex-col justify-between group">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-white/5 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">🚗</span>
                                <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300">Daily Commute</span>
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-gray-900 dark:text-white">Economy</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Toyota Yaris, Honda Fit, Hyundai Accent</p>
                            </div>
                            <div class="p-4 bg-gray-50 dark:bg-[#0b0f17] rounded-2xl space-y-2 text-xs">
                                <div class="flex justify-between"><span class="text-gray-500">Base Fare:</span><span class="font-bold text-gray-900 dark:text-white">$5.00</span></div>
                                <div class="flex justify-between"><span class="text-gray-500">Rate per km:</span><span class="font-bold text-gray-900 dark:text-white">$1.50 / km</span></div>
                                <div class="flex justify-between"><span class="text-gray-500">Rate per min:</span><span class="font-bold text-gray-900 dark:text-white">$0.25 / min</span></div>
                                <div class="flex justify-between pt-2 border-t border-gray-200 dark:border-white/10"><span class="text-gray-500 font-semibold">Minimum Fare:</span><span class="font-black text-emerald-600 dark:text-emerald-400">$10.00</span></div>
                            </div>
                            <ul class="space-y-2 text-xs text-gray-600 dark:text-gray-300">
                                <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> Up to 4 passengers & 2 bags</li>
                                <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> Fast 3–5 min average ETA</li>
                                <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> Cash, Card or Mobile Money</li>
                            </ul>
                        </div>
                        <a href="/ride?type=Economy" class="mt-6 block w-full py-3 bg-gray-900 hover:bg-black dark:bg-white dark:hover:bg-gray-100 text-white dark:text-black font-extrabold text-xs rounded-xl transition-all text-center">
                            Book Economy →
                        </a>
                    </div>

                    <!-- 2. Standard / Comfort -->
                    <div class="bg-white dark:bg-[#111622] rounded-3xl p-7 border-2 border-brand-500/50 hover:border-brand-500 shadow-md relative flex flex-col justify-between group">
                        <div class="absolute -top-3 right-6 bg-brand-500 text-slate-950 text-[10px] font-black uppercase px-3 py-1 rounded-full tracking-wider shadow-sm">
                            ★ Rider Favorite
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">🚘</span>
                                <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400">Business Class</span>
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-gray-900 dark:text-white">Standard / Comfort</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Toyota Camry, Honda Accord, Hyundai Sonata</p>
                            </div>
                            <div class="p-4 bg-gray-50 dark:bg-[#0b0f17] rounded-2xl space-y-2 text-xs">
                                <div class="flex justify-between"><span class="text-gray-500">Base Fare:</span><span class="font-bold text-gray-900 dark:text-white">$6.00</span></div>
                                <div class="flex justify-between"><span class="text-gray-500">Rate per km:</span><span class="font-bold text-gray-900 dark:text-white">$1.80 / km</span></div>
                                <div class="flex justify-between"><span class="text-gray-500">Rate per min:</span><span class="font-bold text-gray-900 dark:text-white">$0.30 / min</span></div>
                                <div class="flex justify-between pt-2 border-t border-gray-200 dark:border-white/10"><span class="text-gray-500 font-semibold">Minimum Fare:</span><span class="font-black text-emerald-600 dark:text-emerald-400">$12.00</span></div>
                            </div>
                            <ul class="space-y-2 text-xs text-gray-600 dark:text-gray-300">
                                <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> Extra legroom & newer model vehicles</li>
                                <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> Top-rated drivers (4.85★ or higher)</li>
                                <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> Temperature & quiet ride preference</li>
                            </ul>
                        </div>
                        <a href="/ride?type=Standard" class="mt-6 block w-full py-3 bg-brand-500 hover:bg-brand-600 text-slate-950 font-black text-xs rounded-xl shadow-md transition-all text-center">
                            Book Comfort →
                        </a>
                    </div>

                    <!-- 3. Executive SUV -->
                    <div class="bg-white dark:bg-[#111622] rounded-3xl p-7 border border-gray-200 dark:border-white/10 shadow-xs hover:shadow-xl hover:border-brand-500/40 transition-all flex flex-col justify-between group">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-white/5 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">🚙</span>
                                <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-blue-500/10 text-blue-600 dark:text-blue-400">Luxury SUV</span>
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-gray-900 dark:text-white">Executive SUV</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Toyota Prado, Lexus RX, Ford Explorer</p>
                            </div>
                            <div class="p-4 bg-gray-50 dark:bg-[#0b0f17] rounded-2xl space-y-2 text-xs">
                                <div class="flex justify-between"><span class="text-gray-500">Base Fare:</span><span class="font-bold text-gray-900 dark:text-white">$7.00</span></div>
                                <div class="flex justify-between"><span class="text-gray-500">Rate per km:</span><span class="font-bold text-gray-900 dark:text-white">$2.10 / km</span></div>
                                <div class="flex justify-between"><span class="text-gray-500">Rate per min:</span><span class="font-bold text-gray-900 dark:text-white">$0.35 / min</span></div>
                                <div class="flex justify-between pt-2 border-t border-gray-200 dark:border-white/10"><span class="text-gray-500 font-semibold">Minimum Fare:</span><span class="font-black text-emerald-600 dark:text-emerald-400">$15.00</span></div>
                            </div>
                            <ul class="space-y-2 text-xs text-gray-600 dark:text-gray-300">
                                <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> Up to 6 passengers with spacious luggage trunk</li>
                                <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> Elevated safety & commanding road view</li>
                                <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> Ideal for airport trips & executive travel</li>
                            </ul>
                        </div>
                        <a href="/ride?type=SUV" class="mt-6 block w-full py-3 bg-gray-900 hover:bg-black dark:bg-white dark:hover:bg-gray-100 text-white dark:text-black font-extrabold text-xs rounded-xl transition-all text-center">
                            Book SUV →
                        </a>
                    </div>

                    <!-- 4. Van XL -->
                    <div class="bg-white dark:bg-[#111622] rounded-3xl p-7 border border-gray-200 dark:border-white/10 shadow-xs hover:shadow-xl hover:border-brand-500/40 transition-all flex flex-col justify-between group">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-white/5 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">🚐</span>
                                <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-purple-500/10 text-purple-600 dark:text-purple-400">Large Groups</span>
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-gray-900 dark:text-white">Van XL</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Toyota HiAce, Mercedes V-Class, Hyundai Staria</p>
                            </div>
                            <div class="p-4 bg-gray-50 dark:bg-[#0b0f17] rounded-2xl space-y-2 text-xs">
                                <div class="flex justify-between"><span class="text-gray-500">Base Fare:</span><span class="font-bold text-gray-900 dark:text-white">$7.50</span></div>
                                <div class="flex justify-between"><span class="text-gray-500">Rate per km:</span><span class="font-bold text-gray-900 dark:text-white">$2.25 / km</span></div>
                                <div class="flex justify-between"><span class="text-gray-500">Rate per min:</span><span class="font-bold text-gray-900 dark:text-white">$0.38 / min</span></div>
                                <div class="flex justify-between pt-2 border-t border-gray-200 dark:border-white/10"><span class="text-gray-500 font-semibold">Minimum Fare:</span><span class="font-black text-emerald-600 dark:text-emerald-400">$18.00</span></div>
                            </div>
                            <ul class="space-y-2 text-xs text-gray-600 dark:text-gray-300">
                                <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> Up to 8 passengers & 6 heavy suitcases</li>
                                <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> Perfect for corporate retreats & airport groups</li>
                                <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> Split fare feature available in rider app</li>
                            </ul>
                        </div>
                        <a href="/ride?type=XL" class="mt-6 block w-full py-3 bg-gray-900 hover:bg-black dark:bg-white dark:hover:bg-gray-100 text-white dark:text-black font-extrabold text-xs rounded-xl transition-all text-center">
                            Book Van XL →
                        </a>
                    </div>

                    <!-- 5. VIP Chauffeur / Luxury -->
                    <div class="bg-white dark:bg-[#111622] rounded-3xl p-7 border border-amber-500/40 hover:border-amber-400 shadow-md relative flex flex-col justify-between group">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">🏎️</span>
                                <span class="text-[10px] font-black uppercase px-2.5 py-1 rounded-full bg-amber-500/20 text-amber-400 border border-amber-500/30">First Class</span>
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-gray-900 dark:text-white">VIP Luxury Chauffeur</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Mercedes S-Class, BMW 7 Series, Audi A8</p>
                            </div>
                            <div class="p-4 bg-gray-50 dark:bg-[#0b0f17] rounded-2xl space-y-2 text-xs">
                                <div class="flex justify-between"><span class="text-gray-500">Base Fare:</span><span class="font-bold text-gray-900 dark:text-white">$9.00</span></div>
                                <div class="flex justify-between"><span class="text-gray-500">Rate per km:</span><span class="font-bold text-gray-900 dark:text-white">$2.70 / km</span></div>
                                <div class="flex justify-between"><span class="text-gray-500">Rate per min:</span><span class="font-bold text-gray-900 dark:text-white">$0.45 / min</span></div>
                                <div class="flex justify-between pt-2 border-t border-gray-200 dark:border-white/10"><span class="text-gray-500 font-semibold">Minimum Fare:</span><span class="font-black text-amber-500">$25.00</span></div>
                            </div>
                            <ul class="space-y-2 text-xs text-gray-600 dark:text-gray-300">
                                <li class="flex items-center gap-2"><span class="text-amber-500 font-bold">✓</span> Suited, NDA-bound professional chauffeur</li>
                                <li class="flex items-center gap-2"><span class="text-amber-500 font-bold">✓</span> Bottled water, phone chargers & Wi-Fi</li>
                                <li class="flex items-center gap-2"><span class="text-amber-500 font-bold">✓</span> Priority flight tracking & meet-and-greet</li>
                            </ul>
                        </div>
                        <a href="/ride?type=Luxury" class="mt-6 block w-full py-3 bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-500 hover:to-amber-600 text-slate-950 font-black text-xs rounded-xl shadow-md transition-all text-center">
                            Book VIP Chauffeur →
                        </a>
                    </div>

                    <!-- 6. Custom Multi-City & Corporate -->
                    <div class="bg-white dark:bg-[#111622] rounded-3xl p-7 border-2 border-indigo-500/30 hover:border-indigo-500 shadow-sm hover:shadow-xl transition-all flex flex-col justify-between group">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">🏢</span>
                                <span class="text-[10px] font-black uppercase px-2.5 py-1 rounded-full bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/50">Enterprise</span>
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-gray-900 dark:text-white">Corporate & Event Fleets</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Dedicated business transportation & conferences</p>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                                Need multiple vehicles, intercity transfers, or monthly billing for your executive team? We provide customized enterprise rates with consolidated invoicing.
                            </p>
                            <div class="pt-2 space-y-2 text-xs text-gray-600 dark:text-gray-300">
                                <p class="flex items-center gap-2"><span class="text-indigo-600 font-bold">✓</span> Consolidated monthly corporate invoices</p>
                                <p class="flex items-center gap-2"><span class="text-indigo-600 font-bold">✓</span> Dedicated VIP account manager</p>
                                <p class="flex items-center gap-2"><span class="text-indigo-600 font-bold">✓</span> Custom SLA response guarantees</p>
                            </div>
                        </div>
                        <a href="/contact" class="mt-6 block w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs rounded-xl shadow-md transition-all text-center">
                            Inquire for Business →
                        </a>
                    </div>

                </div>
            </section>

            <!-- SECTION 2: CAR RENTALS -->
            <section x-show="['all', 'rentals'].includes(activeTab)" class="space-y-8">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="text-xs font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Self-Drive & Chauffeur Fleet</span>
                        </div>
                        <h2 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                            Vehicle Rental Rates
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Fully insured, sanitized vehicles with free cancellation and optional doorstep delivery.
                        </p>
                    </div>

                    <!-- Period Switcher Toggle -->
                    <div class="p-1 bg-gray-100 dark:bg-[#151b26] border border-gray-200 dark:border-white/10 rounded-2xl flex items-center gap-1 text-xs font-extrabold">
                        <button type="button" @click="rentalPeriod = 'daily'"
                                :class="rentalPeriod === 'daily' ? 'bg-white dark:bg-[#252f40] text-gray-900 dark:text-white shadow-xs' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'"
                                class="px-3.5 py-1.5 rounded-xl transition-all cursor-pointer">
                            Daily
                        </button>
                        <button type="button" @click="rentalPeriod = 'weekly'"
                                :class="rentalPeriod === 'weekly' ? 'bg-white dark:bg-[#252f40] text-gray-900 dark:text-white shadow-xs' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'"
                                class="px-3.5 py-1.5 rounded-xl transition-all flex items-center gap-1 cursor-pointer">
                            <span>Weekly</span>
                            <span class="text-[9px] px-1 py-0.2 rounded bg-emerald-500/20 text-emerald-600 dark:text-emerald-400">-15%</span>
                        </button>
                        <button type="button" @click="rentalPeriod = 'monthly'"
                                :class="rentalPeriod === 'monthly' ? 'bg-white dark:bg-[#252f40] text-gray-900 dark:text-white shadow-xs' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'"
                                class="px-3.5 py-1.5 rounded-xl transition-all flex items-center gap-1 cursor-pointer">
                            <span>Monthly</span>
                            <span class="text-[9px] px-1 py-0.2 rounded bg-brand-500/20 text-brand-600 dark:text-brand-400">-30%</span>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Economy Rental -->
                    <div class="bg-white dark:bg-[#111622] rounded-3xl p-7 border border-gray-200 dark:border-white/10 shadow-xs hover:shadow-xl transition-all flex flex-col justify-between">
                        <div class="space-y-4">
                            <span class="text-3xl">🚗</span>
                            <div>
                                <h3 class="text-xl font-black text-gray-900 dark:text-white">Economy Compact</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Toyota Corolla, Honda Civic, Hyundai Elantra</p>
                            </div>
                            <div>
                                <div class="flex items-baseline gap-1">
                                    <span class="text-4xl font-black text-gray-900 dark:text-white"
                                          x-text="rentalPeriod === 'daily' ? '$35' : (rentalPeriod === 'weekly' ? '$208' : '$735')"></span>
                                    <span class="text-xs text-gray-500" x-text="'/ ' + (rentalPeriod === 'daily' ? 'day' : (rentalPeriod === 'weekly' ? 'week' : 'month'))"></span>
                                </div>
                                <p class="text-[11px] text-emerald-600 font-bold mt-1">✓ Unlimited mileage included</p>
                            </div>
                            <ul class="space-y-2.5 text-xs text-gray-600 dark:text-gray-300 border-t border-gray-100 dark:border-white/10 pt-4">
                                <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> Comprehensive Collision Damage Waiver</li>
                                <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> Free cancellation up to 24 hours</li>
                                <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> 24/7 Roadside Assistance</li>
                            </ul>
                        </div>
                        <a href="/rent?category=Economy" class="mt-6 block w-full py-3 bg-gray-900 hover:bg-black dark:bg-white dark:hover:bg-gray-100 text-white dark:text-black font-extrabold text-xs rounded-xl transition-all text-center">
                            Browse Economy Rentals →
                        </a>
                    </div>

                    <!-- SUV / Midsize Rental -->
                    <div class="bg-white dark:bg-[#111622] rounded-3xl p-7 border-2 border-brand-500/60 shadow-lg relative flex flex-col justify-between">
                        <div class="absolute -top-3 right-6 bg-brand-500 text-slate-950 text-[10px] font-black uppercase px-3 py-1 rounded-full tracking-wider">
                            Popular Choice
                        </div>
                        <div class="space-y-4">
                            <span class="text-3xl">🚙</span>
                            <div>
                                <h3 class="text-xl font-black text-gray-900 dark:text-white">SUV & Midsize</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Toyota RAV4, Honda CR-V, Nissan X-Trail</p>
                            </div>
                            <div>
                                <div class="flex items-baseline gap-1">
                                    <span class="text-4xl font-black text-brand-600 dark:text-brand-400"
                                          x-text="rentalPeriod === 'daily' ? '$65' : (rentalPeriod === 'weekly' ? '$386' : '$1,365')"></span>
                                    <span class="text-xs text-gray-500" x-text="'/ ' + (rentalPeriod === 'daily' ? 'day' : (rentalPeriod === 'weekly' ? 'week' : 'month'))"></span>
                                </div>
                                <p class="text-[11px] text-emerald-600 font-bold mt-1">✓ Full zero-deductible insurance</p>
                            </div>
                            <ul class="space-y-2.5 text-xs text-gray-600 dark:text-gray-300 border-t border-gray-100 dark:border-white/10 pt-4">
                                <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> All-wheel drive for long trips</li>
                                <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> Integrated GPS & Apple CarPlay</li>
                                <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> Doorstep vehicle delivery available</li>
                            </ul>
                        </div>
                        <a href="/rent?category=SUV" class="mt-6 block w-full py-3 bg-brand-500 hover:bg-brand-600 text-slate-950 font-black text-xs rounded-xl shadow-md transition-all text-center">
                            Browse SUV Fleet →
                        </a>
                    </div>

                    <!-- Luxury Rental -->
                    <div class="bg-white dark:bg-[#111622] rounded-3xl p-7 border border-gray-200 dark:border-white/10 shadow-xs hover:shadow-xl transition-all flex flex-col justify-between">
                        <div class="space-y-4">
                            <span class="text-3xl">🏎️</span>
                            <div>
                                <h3 class="text-xl font-black text-gray-900 dark:text-white">Prestige & Luxury</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">BMW 5 Series, Mercedes E-Class, Range Rover</p>
                            </div>
                            <div>
                                <div class="flex items-baseline gap-1">
                                    <span class="text-4xl font-black text-gray-900 dark:text-white"
                                          x-text="rentalPeriod === 'daily' ? '$120' : (rentalPeriod === 'weekly' ? '$714' : '$2,520')"></span>
                                    <span class="text-xs text-gray-500" x-text="'/ ' + (rentalPeriod === 'daily' ? 'day' : (rentalPeriod === 'weekly' ? 'week' : 'month'))"></span>
                                </div>
                                <p class="text-[11px] text-emerald-600 font-bold mt-1">✓ White-glove concierge delivery</p>
                            </div>
                            <ul class="space-y-2.5 text-xs text-gray-600 dark:text-gray-300 border-t border-gray-100 dark:border-white/10 pt-4">
                                <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> Premium luxury trim & leather interior</li>
                                <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> VIP airport terminal drop-off & pickup</li>
                                <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> Dedicated vehicle concierge contact</li>
                            </ul>
                        </div>
                        <a href="/rent?category=Luxury" class="mt-6 block w-full py-3 bg-gray-900 hover:bg-black dark:bg-white dark:hover:bg-gray-100 text-white dark:text-black font-extrabold text-xs rounded-xl transition-all text-center">
                            Browse Luxury Fleet →
                        </a>
                    </div>
                </div>
            </section>

            <!-- SECTION 3: PRIVATE DRIVER HIRE (CHAUFFEURS FOR YOUR CAR) -->
            <section x-show="['all', 'drivers'].includes(activeTab)" class="space-y-8">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <span class="text-xs font-black uppercase tracking-wider text-amber-600 dark:text-amber-400">Driver Hire For Your Own Vehicle</span>
                        </div>
                        <h2 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                            Hire a Screened Personal Driver
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Have your own car? Hire verified professional chauffeurs by the hour, day, or week.
                        </p>
                    </div>
                    <a href="/hire-driver" class="text-xs font-extrabold text-brand-600 dark:text-brand-400 hover:underline flex items-center gap-1">
                        <span>Browse Verified Drivers Directory</span>
                        <span>→</span>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Hourly -->
                    <div class="bg-white dark:bg-[#111622] rounded-3xl p-6 border border-gray-200 dark:border-white/10 shadow-xs flex flex-col justify-between">
                        <div class="space-y-3">
                            <span class="text-xs font-black uppercase text-gray-400">1. Short Errands</span>
                            <h3 class="text-xl font-black text-gray-900 dark:text-white">Hourly Chauffeur</h3>
                            <div class="py-2">
                                <span class="text-3xl font-black text-gray-900 dark:text-white">$25</span>
                                <span class="text-xs text-gray-500 font-bold">/ hour</span>
                            </div>
                            <p class="text-xs text-gray-500 leading-relaxed">
                                Ideal for doctor appointments, shopping trips, dinner outings, or safe ride home.
                            </p>
                            <p class="text-[11px] text-gray-400 font-medium">Min. booking: 2 hours</p>
                        </div>
                        <a href="/hire-driver?type=hourly" class="mt-5 block w-full py-2.5 bg-gray-100 dark:bg-white/10 hover:bg-gray-200 text-gray-900 dark:text-white font-extrabold text-xs rounded-xl transition-all text-center">
                            Hire by Hour →
                        </a>
                    </div>

                    <!-- Half Day -->
                    <div class="bg-white dark:bg-[#111622] rounded-3xl p-6 border border-gray-200 dark:border-white/10 shadow-xs flex flex-col justify-between">
                        <div class="space-y-3">
                            <span class="text-xs font-black uppercase text-amber-500">2. Business Meetings</span>
                            <h3 class="text-xl font-black text-gray-900 dark:text-white">Half Day (4–8h)</h3>
                            <div class="py-2">
                                <span class="text-3xl font-black text-amber-500">$85</span>
                                <span class="text-xs text-gray-500 font-bold">/ 4h block</span>
                            </div>
                            <p class="text-xs text-gray-500 leading-relaxed">
                                Continuous standby for busy executives, client visits, or multi-stop city schedules.
                            </p>
                            <p class="text-[11px] text-emerald-600 font-bold">5% discount applied</p>
                        </div>
                        <a href="/hire-driver?type=daily" class="mt-5 block w-full py-2.5 bg-gray-100 dark:bg-white/10 hover:bg-gray-200 text-gray-900 dark:text-white font-extrabold text-xs rounded-xl transition-all text-center">
                            Hire Half-Day →
                        </a>
                    </div>

                    <!-- Full Day -->
                    <div class="bg-white dark:bg-[#111622] rounded-3xl p-6 border-2 border-brand-500/60 shadow-md flex flex-col justify-between">
                        <div class="space-y-3">
                            <span class="text-xs font-black uppercase text-brand-600 dark:text-brand-400">3. Full Day Freedom</span>
                            <h3 class="text-xl font-black text-gray-900 dark:text-white">Full Day (8–12h)</h3>
                            <div class="py-2">
                                <span class="text-3xl font-black text-brand-600 dark:text-brand-400">$160</span>
                                <span class="text-xs text-gray-500 font-bold">/ full day</span>
                            </div>
                            <p class="text-xs text-gray-500 leading-relaxed">
                                Complete day trip, inter-city driving, weddings, or VIP delegations. Zero mileage caps.
                            </p>
                            <p class="text-[11px] text-emerald-600 font-bold">15% discount applied</p>
                        </div>
                        <a href="/hire-driver?type=daily" class="mt-5 block w-full py-2.5 bg-brand-500 hover:bg-brand-600 text-slate-950 font-black text-xs rounded-xl shadow-md transition-all text-center">
                            Hire Full Day →
                        </a>
                    </div>

                    <!-- Weekly -->
                    <div class="bg-white dark:bg-[#111622] rounded-3xl p-6 border border-gray-200 dark:border-white/10 shadow-xs flex flex-col justify-between">
                        <div class="space-y-3">
                            <span class="text-xs font-black uppercase text-purple-500">4. Family & Corporate</span>
                            <h3 class="text-xl font-black text-gray-900 dark:text-white">Weekly Dedicated</h3>
                            <div class="py-2">
                                <span class="text-3xl font-black text-gray-900 dark:text-white">$890</span>
                                <span class="text-xs text-gray-500 font-bold">/ 7 days</span>
                            </div>
                            <p class="text-xs text-gray-500 leading-relaxed">
                                Assigned dedicated personal chauffeur for the full week. School runs, work commutes & weekend travel.
                            </p>
                            <p class="text-[11px] text-emerald-600 font-bold">Best value for families</p>
                        </div>
                        <a href="/hire-driver?type=weekly" class="mt-5 block w-full py-2.5 bg-gray-100 dark:bg-white/10 hover:bg-gray-200 text-gray-900 dark:text-white font-extrabold text-xs rounded-xl transition-all text-center">
                            Hire Weekly Driver →
                        </a>
                    </div>
                </div>
            </section>

            <!-- SECTION 4: PACKAGE COURIER & DELIVERY RATES -->
            <section x-show="['all', 'delivery'].includes(activeTab)" class="space-y-8">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            <span class="text-xs font-black uppercase tracking-wider text-blue-600 dark:text-blue-400">Logistics & Courier Rates</span>
                        </div>
                        <h2 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                            Package Delivery Rates
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Same-day, instant courier and cargo freight across core metropolitan zones.
                        </p>
                    </div>
                    <a href="/delivery" class="text-xs font-extrabold text-brand-600 dark:text-brand-400 hover:underline flex items-center gap-1">
                        <span>Send a Parcel Now</span>
                        <span>→</span>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Bike Courier -->
                    <div class="bg-white dark:bg-[#111622] rounded-3xl p-7 border border-gray-200 dark:border-white/10 shadow-xs flex flex-col justify-between">
                        <div class="space-y-4">
                            <span class="text-3xl">🛵</span>
                            <div>
                                <h3 class="text-xl font-black text-gray-900 dark:text-white">Hyperlocal Bike Courier</h3>
                                <p class="text-xs text-gray-500 mt-0.5">Documents, keys, pharmacy, lightweight goods (≤ 5 kg)</p>
                            </div>
                            <div class="p-4 bg-gray-50 dark:bg-[#0b0f17] rounded-2xl space-y-1.5 text-xs">
                                <div class="flex justify-between"><span class="text-gray-500">Base Fare:</span><span class="font-bold text-gray-900 dark:text-white">$8.00</span></div>
                                <div class="flex justify-between"><span class="text-gray-500">Distance Rate:</span><span class="font-bold text-gray-900 dark:text-white">$1.00 / km</span></div>
                                <div class="flex justify-between"><span class="text-gray-500">Typical Speed:</span><span class="font-bold text-emerald-600">~30–45 mins</span></div>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-300">Live GPS tracking link generated automatically for sender & recipient.</p>
                        </div>
                        <a href="/delivery" class="mt-6 block w-full py-3 bg-gray-900 hover:bg-black dark:bg-white dark:hover:bg-gray-100 text-white dark:text-black font-extrabold text-xs rounded-xl transition-all text-center">
                            Dispatch Bike Courier →
                        </a>
                    </div>

                    <!-- Sedan Delivery -->
                    <div class="bg-white dark:bg-[#111622] rounded-3xl p-7 border-2 border-brand-500/50 shadow-md flex flex-col justify-between">
                        <div class="space-y-4">
                            <span class="text-3xl">🚗</span>
                            <div>
                                <h3 class="text-xl font-black text-gray-900 dark:text-white">Sedan Express Parcel</h3>
                                <p class="text-xs text-gray-500 mt-0.5">Electronics, retail purchases, fragile boxes (≤ 25 kg)</p>
                            </div>
                            <div class="p-4 bg-gray-50 dark:bg-[#0b0f17] rounded-2xl space-y-1.5 text-xs">
                                <div class="flex justify-between"><span class="text-gray-500">Base Fare:</span><span class="font-bold text-gray-900 dark:text-white">$15.00</span></div>
                                <div class="flex justify-between"><span class="text-gray-500">Distance Rate:</span><span class="font-bold text-gray-900 dark:text-white">$1.50 / km</span></div>
                                <div class="flex justify-between"><span class="text-gray-500">Cargo Space:</span><span class="font-bold text-amber-500">Full Trunk & Backseat</span></div>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-300">Climate-controlled transport with digital photo proof of delivery at dropoff.</p>
                        </div>
                        <a href="/delivery" class="mt-6 block w-full py-3 bg-brand-500 hover:bg-brand-600 text-slate-950 font-black text-xs rounded-xl shadow-md transition-all text-center">
                            Dispatch Sedan Parcel →
                        </a>
                    </div>

                    <!-- Cargo Van -->
                    <div class="bg-white dark:bg-[#111622] rounded-3xl p-7 border border-gray-200 dark:border-white/10 shadow-xs flex flex-col justify-between">
                        <div class="space-y-4">
                            <span class="text-3xl">🚐</span>
                            <div>
                                <h3 class="text-xl font-black text-gray-900 dark:text-white">Cargo Van & Bulk Dispatch</h3>
                                <p class="text-xs text-gray-500 mt-0.5">Furniture, commercial supplies, wholesale pallets (≤ 250 kg)</p>
                            </div>
                            <div class="p-4 bg-gray-50 dark:bg-[#0b0f17] rounded-2xl space-y-1.5 text-xs">
                                <div class="flex justify-between"><span class="text-gray-500">Base Fare:</span><span class="font-bold text-gray-900 dark:text-white">$35.00</span></div>
                                <div class="flex justify-between"><span class="text-gray-500">Distance Rate:</span><span class="font-bold text-gray-900 dark:text-white">$2.20 / km</span></div>
                                <div class="flex justify-between"><span class="text-gray-500">Loading Help:</span><span class="font-bold text-blue-500">Driver Assistance Included</span></div>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-300">Heavy payload dispatch with scheduled multi-point routes available.</p>
                        </div>
                        <a href="/delivery" class="mt-6 block w-full py-3 bg-gray-900 hover:bg-black dark:bg-white dark:hover:bg-gray-100 text-white dark:text-black font-extrabold text-xs rounded-xl transition-all text-center">
                            Dispatch Cargo Van →
                        </a>
                    </div>
                </div>
            </section>

            <!-- SECTION 5: VIP CLUB MEMBERSHIPS -->
            <section x-show="['all', 'membership'].includes(activeTab)" class="space-y-8">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                            <span class="text-xs font-black uppercase tracking-wider text-brand-600 dark:text-brand-400">Executive Travel Pass</span>
                        </div>
                        <h2 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                            VIP Club Memberships
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Unlock guaranteed priority dispatch, free rental vehicle delivery, and consolidated corporate billing.
                        </p>
                    </div>
                    <a href="/membership" class="text-xs font-extrabold text-brand-600 dark:text-brand-400 hover:underline flex items-center gap-1">
                        <span>View Membership Dashboard</span>
                        <span>→</span>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                    <!-- Club Membership -->
                    <div class="bg-white dark:bg-[#111622] rounded-3xl p-8 border-2 border-brand-500 shadow-xl relative flex flex-col justify-between">
                        <div class="absolute -top-3 right-6 bg-brand-500 text-slate-950 text-[10px] font-black uppercase px-3 py-1 rounded-full tracking-wider">
                            Most Popular
                        </div>
                        <div class="space-y-6">
                            <div class="w-14 h-14 rounded-2xl bg-brand-500/15 text-brand-600 dark:text-brand-400 flex items-center justify-center text-3xl">
                                👑
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-gray-900 dark:text-white">VIP Club Member</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">For frequent business flyers, entrepreneurs & executive travelers.</p>
                            </div>
                            <div>
                                <span class="text-5xl font-black text-gray-900 dark:text-white">$250</span>
                                <span class="text-xs text-gray-500 font-bold">/ month</span>
                            </div>
                            <ul class="space-y-3 text-xs text-gray-700 dark:text-gray-300">
                                <li class="flex items-center gap-2.5"><span class="w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 text-center leading-5 font-black text-[10px]">✓</span> Guaranteed 15-min priority dispatch in core zones</li>
                                <li class="flex items-center gap-2.5"><span class="w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 text-center leading-5 font-black text-[10px]">✓</span> Complimentary luxury vehicle delivery & retrieval</li>
                                <li class="flex items-center gap-2.5"><span class="w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 text-center leading-5 font-black text-[10px]">✓</span> Zero cancellation penalties on scheduled rides</li>
                                <li class="flex items-center gap-2.5"><span class="w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 text-center leading-5 font-black text-[10px]">✓</span> 24/7 VIP Concierge private telephone hotline</li>
                            </ul>
                        </div>
                        <a href="/membership" class="mt-8 block w-full py-4 bg-brand-500 hover:bg-brand-600 text-slate-950 font-black text-sm rounded-2xl shadow-lg transition-all text-center">
                            Subscribe to Club VIP →
                        </a>
                    </div>

                    <!-- Corporate Enterprise -->
                    <div class="bg-white dark:bg-[#111622] rounded-3xl p-8 border border-gray-200 dark:border-white/10 shadow-xs flex flex-col justify-between">
                        <div class="space-y-6">
                            <div class="w-14 h-14 rounded-2xl bg-blue-500/15 text-blue-600 dark:text-blue-400 flex items-center justify-center text-3xl">
                                🏢
                            </div>
                            <div>
                                <h3 class="text-2xl font-black text-gray-900 dark:text-white">Corporate Program</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">For organizations managing executive teams, delegates & staff travel.</p>
                            </div>
                            <div>
                                <span class="text-5xl font-black text-gray-900 dark:text-white">Custom</span>
                                <span class="text-xs text-gray-500 font-bold">/ corporate invoice</span>
                            </div>
                            <ul class="space-y-3 text-xs text-gray-700 dark:text-gray-300">
                                <li class="flex items-center gap-2.5"><span class="w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 text-center leading-5 font-black text-[10px]">✓</span> Centralized monthly company billing & tax invoices</li>
                                <li class="flex items-center gap-2.5"><span class="w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 text-center leading-5 font-black text-[10px]">✓</span> Employee allowances & cost center travel tracking</li>
                                <li class="flex items-center gap-2.5"><span class="w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 text-center leading-5 font-black text-[10px]">✓</span> Tailored airport & hotel shuttle dispatch arrangements</li>
                                <li class="flex items-center gap-2.5"><span class="w-5 h-5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 text-center leading-5 font-black text-[10px]">✓</span> Dedicated Corporate Key Account Manager</li>
                            </ul>
                        </div>
                        <a href="/contact" class="mt-8 block w-full py-4 bg-gray-900 hover:bg-black dark:bg-white dark:hover:bg-gray-100 text-white dark:text-black font-extrabold text-sm rounded-2xl transition-all text-center">
                            Request Corporate Account →
                        </a>
                    </div>
                </div>
            </section>

            <!-- SECTION 6: SERVICE COMPARISON TABLE -->
            <section class="space-y-6 pt-6">
                <div class="text-center max-w-2xl mx-auto">
                    <h2 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white">Service Matrix at a Glance</h2>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">Compare policies, payment flexibility and features across our services.</p>
                </div>

                <div class="overflow-x-auto bg-white dark:bg-[#111622] rounded-3xl border border-gray-200 dark:border-white/10 shadow-xs">
                    <table class="w-full text-left text-xs border-collapse min-w-[650px]">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-white/10 bg-gray-50/75 dark:bg-[#0b0f17]">
                                <th class="py-4 px-5 font-extrabold text-gray-500 uppercase tracking-wider">Features & Terms</th>
                                <th class="py-4 px-4 font-black text-gray-900 dark:text-white">🚗 Ride Hailing</th>
                                <th class="py-4 px-4 font-black text-gray-900 dark:text-white">🔑 Car Rentals</th>
                                <th class="py-4 px-4 font-black text-gray-900 dark:text-white">👨‍✈️ Driver Hire</th>
                                <th class="py-4 px-4 font-black text-gray-900 dark:text-white">📦 Package Delivery</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5 font-medium">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                <td class="py-3.5 px-5 font-bold text-gray-900 dark:text-white">Upfront Price Lock</td>
                                <td class="py-3.5 px-4 text-emerald-600 font-bold">✓ Guaranteed</td>
                                <td class="py-3.5 px-4 text-emerald-600 font-bold">✓ Fixed Daily/Weekly</td>
                                <td class="py-3.5 px-4 text-emerald-600 font-bold">✓ Flat Tier Rate</td>
                                <td class="py-3.5 px-4 text-emerald-600 font-bold">✓ Fixed by Distance</td>
                            </tr>
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                <td class="py-3.5 px-5 font-bold text-gray-900 dark:text-white">Cancellation Policy</td>
                                <td class="py-3.5 px-4 text-gray-600 dark:text-gray-300">Free within 2 mins</td>
                                <td class="py-3.5 px-4 text-gray-600 dark:text-gray-300">Free up to 24h prior</td>
                                <td class="py-3.5 px-4 text-gray-600 dark:text-gray-300">Free up to 4h prior</td>
                                <td class="py-3.5 px-4 text-gray-600 dark:text-gray-300">Free before pickup</td>
                            </tr>
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                <td class="py-3.5 px-5 font-bold text-gray-900 dark:text-white">Driver / Chauffeur Included</td>
                                <td class="py-3.5 px-4 text-emerald-600 font-bold">✓ Yes</td>
                                <td class="py-3.5 px-4 text-gray-600 dark:text-gray-300">Optional Add-on</td>
                                <td class="py-3.5 px-4 text-emerald-600 font-bold">✓ Yes (For your car)</td>
                                <td class="py-3.5 px-4 text-emerald-600 font-bold">✓ Yes (Courier)</td>
                            </tr>
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                <td class="py-3.5 px-5 font-bold text-gray-900 dark:text-white">Real-Time GPS Tracking</td>
                                <td class="py-3.5 px-4 text-emerald-600 font-bold">✓ Live Map & Link</td>
                                <td class="py-3.5 px-4 text-gray-600 dark:text-gray-300">In-car navigation</td>
                                <td class="py-3.5 px-4 text-emerald-600 font-bold">✓ Live Dispatch</td>
                                <td class="py-3.5 px-4 text-emerald-600 font-bold">✓ Live Share Link</td>
                            </tr>
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02]">
                                <td class="py-3.5 px-5 font-bold text-gray-900 dark:text-white">Payment Options</td>
                                <td class="py-3.5 px-4 text-gray-600 dark:text-gray-300">Card, Momo, Cash</td>
                                <td class="py-3.5 px-4 text-gray-600 dark:text-gray-300">Credit Card, Momo</td>
                                <td class="py-3.5 px-4 text-gray-600 dark:text-gray-300">Card, Momo, Cash</td>
                                <td class="py-3.5 px-4 text-gray-600 dark:text-gray-300">Card, Momo, Cash</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- SECTION 7: TRANSPARENT PRICING FAQ -->
            <section class="space-y-6 max-w-4xl mx-auto pt-6">
                <div class="text-center">
                    <span class="text-xs font-black uppercase text-amber-500 tracking-wider">Got Questions?</span>
                    <h2 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white mt-1">Frequently Asked Pricing Questions</h2>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">Everything you need to know about our rates, policies & payments.</p>
                </div>

                <div class="space-y-3">
                    <!-- FAQ 1 -->
                    <div class="bg-white dark:bg-[#111622] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden shadow-xs">
                        <button type="button" @click="activeFaq = (activeFaq === 1 ? null : 1)" 
                                class="w-full p-5 text-left flex items-center justify-between gap-4 font-extrabold text-sm text-gray-900 dark:text-white cursor-pointer">
                            <span>How does RideMyCars calculate trip fares?</span>
                            <span class="text-gray-400 text-lg transition-transform" :class="activeFaq === 1 ? 'rotate-180' : ''">▾</span>
                        </button>
                        <div x-show="activeFaq === 1" x-collapse class="px-5 pb-5 text-xs text-gray-600 dark:text-gray-300 leading-relaxed border-t border-gray-100 dark:border-white/5 pt-3">
                            Trip fares are calculated deterministically: Base Fare + (Distance in km × Per Km Rate) + (Estimated Duration in minutes × Per Minute Rate) + any additional stop fees, plus 5% local regulatory tax. What is shown on your screen upon confirmation is the exact price you pay.
                        </div>
                    </div>

                    <!-- FAQ 2 -->
                    <div class="bg-white dark:bg-[#111622] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden shadow-xs">
                        <button type="button" @click="activeFaq = (activeFaq === 2 ? null : 2)" 
                                class="w-full p-5 text-left flex items-center justify-between gap-4 font-extrabold text-sm text-gray-900 dark:text-white cursor-pointer">
                            <span>Does RideMyCars have surprise surge multipliers in rain or rush hour?</span>
                            <span class="text-gray-400 text-lg transition-transform" :class="activeFaq === 2 ? 'rotate-180' : ''">▾</span>
                        </button>
                        <div x-show="activeFaq === 2" x-collapse class="px-5 pb-5 text-xs text-gray-600 dark:text-gray-300 leading-relaxed border-t border-gray-100 dark:border-white/5 pt-3">
                            No. RideMyCars does not employ hidden surge multipliers that 3x or 4x your rate unexpectedly. All our rates are capped and transparently declared prior to booking.
                        </div>
                    </div>

                    <!-- FAQ 3 -->
                    <div class="bg-white dark:bg-[#111622] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden shadow-xs">
                        <button type="button" @click="activeFaq = (activeFaq === 3 ? null : 3)" 
                                class="w-full p-5 text-left flex items-center justify-between gap-4 font-extrabold text-sm text-gray-900 dark:text-white cursor-pointer">
                            <span>How does Driver Hire work if I already have my own car?</span>
                            <span class="text-gray-400 text-lg transition-transform" :class="activeFaq === 3 ? 'rotate-180' : ''">▾</span>
                        </button>
                        <div x-show="activeFaq === 3" x-collapse class="px-5 pb-5 text-xs text-gray-600 dark:text-gray-300 leading-relaxed border-t border-gray-100 dark:border-white/5 pt-3">
                            You can hire a verified, screened, and licensed driver to drive your personal or company vehicle. You can book them by the hour, for a half-day (4h), a full-day (8h+), or for a full week. The driver arrives at your designated pickup address ready to operate your car safely.
                        </div>
                    </div>

                    <!-- FAQ 4 -->
                    <div class="bg-white dark:bg-[#111622] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden shadow-xs">
                        <button type="button" @click="activeFaq = (activeFaq === 4 ? null : 4)" 
                                class="w-full p-5 text-left flex items-center justify-between gap-4 font-extrabold text-sm text-gray-900 dark:text-white cursor-pointer">
                            <span>What insurance is included in car rentals?</span>
                            <span class="text-gray-400 text-lg transition-transform" :class="activeFaq === 4 ? 'rotate-180' : ''">▾</span>
                        </button>
                        <div x-show="activeFaq === 4" x-collapse class="px-5 pb-5 text-xs text-gray-600 dark:text-gray-300 leading-relaxed border-t border-gray-100 dark:border-white/5 pt-3">
                            All listed vehicle rentals include comprehensive insurance (Collision Damage Waiver and Third-Party Liability). Premium and luxury rentals include zero-deductible coverage and 24/7 emergency roadside support.
                        </div>
                    </div>

                    <!-- FAQ 5 -->
                    <div class="bg-white dark:bg-[#111622] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden shadow-xs">
                        <button type="button" @click="activeFaq = (activeFaq === 5 ? null : 5)" 
                                class="w-full p-5 text-left flex items-center justify-between gap-4 font-extrabold text-sm text-gray-900 dark:text-white cursor-pointer">
                            <span>Can I get a corporate VAT invoice for business tax claims?</span>
                            <span class="text-gray-400 text-lg transition-transform" :class="activeFaq === 5 ? 'rotate-180' : ''">▾</span>
                        </button>
                        <div x-show="activeFaq === 5" x-collapse class="px-5 pb-5 text-xs text-gray-600 dark:text-gray-300 leading-relaxed border-t border-gray-100 dark:border-white/5 pt-3">
                            Yes. All receipts generated by RideMyCars include itemized breakdowns with VAT/Tax details and company credentials suitable for expense reporting. Corporate accounts also receive consolidated monthly statements.
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION 8: BOTTOM CONVERSION CTA -->
            <div class="p-8 sm:p-14 rounded-3xl bg-gradient-to-br from-amber-500/10 via-white to-amber-500/5 dark:from-[#111622] dark:via-[#161d2b] dark:to-[#0f141c] border-2 border-brand-500/40 dark:border-brand-500/30 shadow-2xl relative overflow-hidden text-center space-y-6">
                <!-- Subtle Ambient Glow -->
                <div class="absolute -top-24 -right-24 w-80 h-80 bg-brand-500/15 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -left-24 w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 max-w-2xl mx-auto space-y-3">
                    <span class="px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-wider bg-amber-500/15 text-amber-800 dark:text-amber-300 border border-amber-500/30 inline-block">
                        Experience Premium Mobility
                    </span>
                    <h3 class="text-2xl sm:text-4xl font-black text-gray-900 dark:text-white tracking-tight">
                        Ready to book your next journey?
                    </h3>
                    <p class="text-xs sm:text-base text-gray-600 dark:text-gray-300 leading-relaxed font-medium">
                        Join thousands of business travelers, executives, and daily commuters who trust RideMyCars for transparent, verified mobility.
                    </p>
                </div>

                <div class="relative z-10 flex flex-wrap items-center justify-center gap-4 pt-2">
                    <a href="/ride" class="px-8 py-4 bg-brand-500 hover:bg-brand-400 text-slate-950 font-black text-sm rounded-2xl shadow-xl shadow-brand-500/30 transition-all hover:scale-105 active:scale-95">
                        Book a Ride Now →
                    </a>
                    <a href="/rent" class="px-8 py-4 bg-gray-900 hover:bg-black dark:bg-white dark:hover:bg-gray-100 text-white dark:text-gray-900 font-black text-sm rounded-2xl shadow-md transition-all hover:scale-105 active:scale-95">
                        Rent a Vehicle →
                    </a>
                    <a href="/membership" class="px-8 py-4 bg-amber-100 hover:bg-amber-200 dark:bg-amber-950/40 dark:hover:bg-amber-900/50 text-amber-900 dark:text-amber-300 border border-amber-500/40 font-black text-sm rounded-2xl shadow-sm transition-all hover:scale-105 active:scale-95">
                        Join VIP Club →
                    </a>
                </div>
            </div>

        </main>
    </div>
</x-layout>