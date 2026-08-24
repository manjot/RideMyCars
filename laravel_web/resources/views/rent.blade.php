<x-layout theme="theme-rent">
    <x-slot:title>Rent a Vehicle — RideMyCars</x-slot>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-12"
          x-data="{ 
              search: '', 
              selectedCategory: '{{ request('category', 'All') }}', 
              categories: ['All', 'Economy', 'Compact', 'Midsize', 'SUV', 'Luxury', 'Van'],
              vehicles: {{ Js::from($vehicles) }},
              get filteredVehicles() {
                  return this.vehicles.filter(v => {
                      const selCat = this.selectedCategory.toLowerCase();
                      const vType = (v.type || '').toLowerCase();
                      const matchesCategory = this.selectedCategory === 'All' || 
                                            vType === selCat || 
                                            (selCat === 'economy' && (vType === 'economy' || vType === 'compact')) ||
                                            (selCat === 'suv' && (vType.includes('suv') || vType.includes('midsize')));
                      const searchStr = this.search.toLowerCase();
                      const matchesSearch = v.make.toLowerCase().includes(searchStr) || v.model.toLowerCase().includes(searchStr);
                      return matchesCategory && matchesSearch;
                  });
              }
          }">
        
        <!-- Header Text -->
        <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-3 tracking-tight">Rent a Vehicle</h1>
                <p class="text-gray-500 dark:text-gray-400 text-lg" x-text="`${filteredVehicles.length} vehicles available — filter by type, price, and features.`"></p>
            </div>
            
            <div class="flex items-center gap-3 shrink-0">
                <a href="/hire-driver" style="background-color: #059669 !important; color: #FFFFFF !important;" class="px-5 py-3 rounded-xl font-bold text-sm shadow-md flex items-center gap-2 transition-all hover:brightness-95 cursor-pointer">
                    <span style="color: #FFFFFF !important;">👨‍✈️ Rent a Driver</span>
                    <span style="background-color: rgba(255, 255, 255, 0.25) !important; color: #FFFFFF !important;" class="text-xs px-2 py-0.5 rounded-full font-semibold">Trust & Verification</span>
                </a>
            </div>
        </div>

        <!-- Search and Filters Bar -->
        <div class="flex flex-col md:flex-row gap-4 mb-8">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </div>
                <input x-model="search" type="text" placeholder="Search by make, model..." class="w-full pl-12 pr-4 py-3.5 bg-white dark:bg-[#111] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all shadow-sm">
            </div>
            <button class="flex items-center justify-center gap-2 px-6 py-3.5 bg-white dark:bg-[#111] border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition-all shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Filters
            </button>
        </div>

        <!-- Category Pills -->
        <div class="flex flex-wrap gap-3 mb-10">
            <template x-for="category in categories" :key="category">
                <button 
                    @click="selectedCategory = category"
                    :class="selectedCategory === category ? 'bg-brand-500 text-white border-brand-500' : 'bg-white dark:bg-[#1a1a1a] text-gray-700 dark:text-gray-300 border-gray-200 dark:border-white/10 hover:border-gray-300 dark:hover:border-white/20'"
                    class="px-5 py-2 border rounded-full font-medium text-sm transition-all shadow-sm"
                    x-text="category">
                </button>
            </template>
        </div>

        <!-- Vehicle Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <template x-if="filteredVehicles.length === 0">
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500 dark:text-gray-400 text-lg">No vehicles found matching your criteria.</p>
                </div>
            </template>

            <template x-for="vehicle in filteredVehicles" :key="vehicle.id">
                <div class="bg-white dark:bg-[#111] rounded-2xl border border-gray-100 dark:border-white/10 p-4 shadow-sm hover:shadow-md transition-shadow flex flex-col group cursor-pointer" @click="window.location.href = '/rent/' + vehicle.id">
                    <!-- Image -->
                    <div class="w-full h-48 bg-gray-100 dark:bg-[#222] rounded-xl mb-4 overflow-hidden relative flex items-center justify-center p-2">
                        <img :src="vehicle.image_src || '/images/hero-rent.png'" class="w-full h-full object-contain transition-transform group-hover:scale-105" :alt="vehicle.make + ' ' + vehicle.model" onerror="this.onerror=null;this.src='/images/hero-rent.png';">
                        
                        <!-- Badge -->
                        <div class="absolute top-3 left-3 bg-white/90 dark:bg-black/80 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-bold text-gray-700 dark:text-gray-300 shadow-sm" x-text="vehicle.type"></div>
                    </div>
                    
                    <!-- Details -->
                    <div class="mt-auto">
                        <div class="flex justify-between items-start mb-1">
                            <h3 class="font-bold text-lg text-gray-900 dark:text-white" x-text="`${vehicle.year} ${vehicle.make}`"></h3>
                            <div class="font-bold text-lg text-gray-900 dark:text-white" x-text="`$${vehicle.daily_rate}`"><span class="text-sm font-normal text-gray-500 dark:text-gray-400">/day</span></div>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-4" x-text="vehicle.model"></p>
                        <a :href="'/rent/' + vehicle.id" class="w-full py-2.5 bg-gray-50 dark:bg-[#1a1a1a] hover:bg-brand-50 dark:hover:bg-brand-900/20 text-gray-900 dark:text-white hover:text-brand-600 dark:hover:text-brand-400 border border-gray-200 dark:border-white/10 hover:border-brand-200 dark:hover:border-brand-500/30 rounded-xl font-bold transition-colors text-center block">
                            View Details
                        </a>
                    </div>
                </div>
            </template>
            
        </div>

        <!-- Vehicle Owner / Rental Benefits Section (Section 6 Requirements) -->
        <section class="mt-20 pt-16 border-t border-gray-200 dark:border-white/10">
            <div class="text-center max-w-3xl mx-auto mb-12">
                <span class="px-3.5 py-1.5 rounded-full bg-brand-50 dark:bg-brand-950/60 border border-brand-200 dark:border-brand-800/40 text-brand-600 dark:text-brand-400 font-bold text-xs uppercase tracking-widest inline-block mb-3">Vehicle Owner Partnership</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-4">Earn More From Your Vehicle</h2>
                <p class="text-lg text-gray-600 dark:text-gray-400 leading-relaxed">
                    Vehicle owners can list eligible vehicles for rental through RideMyCars and keep <strong class="text-gray-900 dark:text-white">80% of every rental fare</strong>. Turn your idle car into passive income today.
                </p>
            </div>

            <!-- Owner Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                <!-- 1. Vehicle Listing -->
                <div class="bg-white dark:bg-[#111] rounded-2xl p-6 border border-gray-100 dark:border-white/10 shadow-sm">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-500 flex items-center justify-center mb-4 text-2xl font-bold">
                        🚗
                    </div>
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-2">Vehicle Listing</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">List your car in under 5 minutes with custom photos, daily rates, and specifications.</p>
                </div>

                <!-- 2. Rental Availability -->
                <div class="bg-white dark:bg-[#111] rounded-2xl p-6 border border-gray-100 dark:border-white/10 shadow-sm">
                    <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-500 flex items-center justify-center mb-4 text-2xl font-bold">
                        📅
                    </div>
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-2">Rental Availability</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">Set custom rental schedules, blocked dates, and preferred pickup locations effortlessly.</p>
                </div>

                <!-- 3. Booking Management -->
                <div class="bg-white dark:bg-[#111] rounded-2xl p-6 border border-gray-100 dark:border-white/10 shadow-sm">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-500 flex items-center justify-center mb-4 text-2xl font-bold">
                        📊
                    </div>
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-2">Booking Management</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">Review incoming rental requests, track active trips, and manage booking dates seamlessly.</p>
                </div>

                <!-- 4. Vehicle Information -->
                <div class="bg-white dark:bg-[#111] rounded-2xl p-6 border border-gray-100 dark:border-white/10 shadow-sm">
                    <div class="w-12 h-12 rounded-xl bg-teal-50 dark:bg-teal-950/60 text-teal-500 flex items-center justify-center mb-4 text-2xl font-bold">
                        📋
                    </div>
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-2">Vehicle Information</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">Highlight capacity, transmission type, climate control, and insurance details to attract renters.</p>
                </div>

                <!-- 5. Rental Earnings -->
                <div class="bg-white dark:bg-[#111] rounded-2xl p-6 border border-gray-100 dark:border-white/10 shadow-sm">
                    <div class="w-12 h-12 rounded-xl bg-green-50 dark:bg-green-950/60 text-green-500 flex items-center justify-center mb-4 text-2xl font-bold">
                        💰
                    </div>
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-2">Rental Earnings</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">Keep 80% of all rental proceeds under our transparent 20% platform / 80% owner model.</p>
                </div>

                <!-- 6. Vehicle Inspection -->
                <div class="bg-white dark:bg-[#111] rounded-2xl p-6 border border-gray-100 dark:border-white/10 shadow-sm">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-500 flex items-center justify-center mb-4 text-2xl font-bold">
                        🔍
                    </div>
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-2">Vehicle Inspection</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">Simple pre-rental inspection verification ensures your vehicle remains in top condition.</p>
                </div>

                <!-- 7. Security Deposit Info -->
                <div class="bg-white dark:bg-[#111] rounded-2xl p-6 border border-gray-100 dark:border-white/10 shadow-sm md:col-span-2">
                    <div class="w-12 h-12 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-500 flex items-center justify-center mb-4 text-2xl font-bold">
                        🛡️
                    </div>
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-2">Security Deposit & Protection</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">Built-in refundable security deposits and verified renter profiles protect your asset on every trip.</p>
                </div>
            </div>

            <!-- Owner CTA -->
            <div class="bg-gradient-to-r from-gray-900 via-black to-gray-900 text-white rounded-3xl p-8 text-center max-w-3xl mx-auto shadow-xl">
                <h3 class="text-2xl font-bold mb-2">Ready to list your vehicle?</h3>
                <p class="text-gray-400 text-sm mb-6">Join vehicle owners earning passive income on RideMyCars.</p>
                <a href="/owner-signup" class="inline-flex items-center gap-2 px-8 py-3.5 bg-brand-500 hover:bg-brand-600 text-white font-extrabold rounded-xl transition-all shadow-md">
                    List Your Vehicle Now
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
            </div>
        </section>
    </main>

</x-layout>