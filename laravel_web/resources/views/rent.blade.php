<x-layout theme="theme-rent">
    <x-slot:title>Rent a Vehicle — RideMyCars</x-slot>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-12"
          x-data="{ 
              search: '', 
              selectedCategory: 'All', 
              categories: ['All', 'Economy', 'Compact', 'Midsize', 'SUV', 'Luxury', 'Van'],
              vehicles: {{ Js::from($vehicles) }},
              get filteredVehicles() {
                  return this.vehicles.filter(v => {
                      const matchesCategory = this.selectedCategory === 'All' || v.type === this.selectedCategory;
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
                <a href="/hire-driver" class="px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-sm shadow-sm flex items-center gap-2">
                    <span>👨‍✈️ Rent a Driver</span>
                    <span class="text-xs bg-white/20 px-2 py-0.5 rounded-full">Trust & Verification</span>
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
                    <div class="w-full h-48 bg-gray-100 dark:bg-[#222] rounded-xl mb-4 overflow-hidden relative">
                        <!-- Fallback for no image -->
                        <div x-show="!vehicle.image_url" class="absolute inset-0 flex items-center justify-center text-gray-300 dark:text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>
                        </div>
                        <img x-show="vehicle.image_url" :src="vehicle.image_url ? '/storage/' + vehicle.image_url : ''" class="w-full h-full object-cover transition-transform group-hover:scale-105" :alt="vehicle.make + ' ' + vehicle.model">
                        
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
    </main>

</x-layout>