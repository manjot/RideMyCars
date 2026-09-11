<x-layout theme="theme-hire">
    <x-slot:title>Hire a Driver — RideMyCars</x-slot>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-12"
          x-data="{ 
              search: '{{ request('search') }}', 
              selectedCountry: '{{ $currentCountryCode ?? 'USA' }}',
              currencySymbol: '{{ $currentCurrencySymbol ?? '$' }}',
              minRating: '{{ request('rating', '') }}',
              availability: '{{ request('availability', '') }}',
              drivers: {{ Js::from($drivers) }},
              currentPage: 1,
              perPage: 6,

              init() {
                  this.$watch('search', () => { this.currentPage = 1; });
                  this.$watch('selectedCountry', () => { this.currentPage = 1; });
                  this.$watch('availability', () => { this.currentPage = 1; });
                  this.$watch('minRating', () => { this.currentPage = 1; });
                  this.$watch('perPage', () => { this.currentPage = 1; });
              },

              get filteredDrivers() {
                  return this.drivers.filter(d => {
                      const searchStr = this.search.toLowerCase();
                      const matchesSearch = !this.search 
                          || (d.user && d.user.name && d.user.name.toLowerCase().includes(searchStr)) 
                          || (d.bio && d.bio.toLowerCase().includes(searchStr))
                          || (d.service_area && d.service_area.toLowerCase().includes(searchStr));

                      const driverCountry = (d.country || '').toUpperCase();
                      const sel = (this.selectedCountry || 'All').toUpperCase();

                      const matchesCountry = sel === 'ALL' 
                          || driverCountry === sel
                          || (sel === 'GHA' && (driverCountry === 'GHANA' || driverCountry === 'GH'))
                          || (sel === 'USA' && (driverCountry === 'UNITED STATES' || driverCountry === 'US'))
                          || (sel === 'NGA' && (driverCountry === 'NIGERIA' || driverCountry === 'NG'))
                          || (sel === 'ZAF' && (driverCountry === 'SOUTH AFRICA' || driverCountry === 'ZA'));

                      const matchesAvail = !this.availability || (this.availability === 'available' ? d.is_available : true);
                      const matchesRating = !this.minRating || (parseFloat(d.rating) >= parseFloat(this.minRating));
                      return matchesSearch && matchesCountry && matchesAvail && matchesRating;
                  });
              },

              formatRate(driver, type) {
                  const symbolMap = {
                      'GHA': 'GH₵', 'GHANA': 'GH₵',
                      'USA': '$', 'UNITED STATES': '$',
                      'NGA': '₦', 'NIGERIA': '₦',
                      'ZAF': 'R', 'SOUTH AFRICA': 'R',
                  };
                  const dc = (driver.country || '').toUpperCase();
                  const sym = symbolMap[dc] || this.currencySymbol || '$';
                  if (type === 'hourly') {
                      return sym + parseFloat(driver.hourly_rate || 25).toFixed(2) + '/hr';
                  }
                  const daily = driver.daily_rate || (driver.hourly_rate * 8 * 0.85);
                  return sym + parseFloat(daily).toFixed(2) + '/day';
              },

              get totalPages() {
                  return Math.max(1, Math.ceil(this.filteredDrivers.length / this.perPage));
              },

              get paginatedDrivers() {
                  const start = (this.currentPage - 1) * this.perPage;
                  return this.filteredDrivers.slice(start, start + parseInt(this.perPage));
              },

              get paginationStart() {
                  if (this.filteredDrivers.length === 0) return 0;
                  return (this.currentPage - 1) * this.perPage + 1;
              },

              get paginationEnd() {
                  return Math.min(this.currentPage * this.perPage, this.filteredDrivers.length);
              },

              get pageNumbers() {
                  const total = this.totalPages;
                  const current = this.currentPage;
                  if (total <= 7) {
                      return Array.from({ length: total }, (_, i) => i + 1);
                  }
                  const pages = [];
                  pages.push(1);
                  if (current > 3) pages.push('...');
                  const start = Math.max(2, current - 1);
                  const end = Math.min(total - 1, current + 1);
                  for (let i = start; i <= end; i++) pages.push(i);
                  if (current < total - 2) pages.push('...');
                  pages.push(total);
                  return pages;
              },

              setPage(p) {
                  if (p >= 1 && p <= this.totalPages) {
                      this.currentPage = p;
                      const grid = document.getElementById('driver-results-container');
                      if (grid) {
                          grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
                      }
                  }
              }
          }">
        
        <!-- Header Text -->
        <div class="mb-6">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2 tracking-tight">Hire a Professional Driver</h1>
            <p class="text-gray-500 dark:text-gray-400 text-lg">Verified, experienced drivers for Private & Commercial hiring worldwide.</p>
        </div>

        @if($isUnsupportedRegion ?? false)
        <div class="mb-8 p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/25 flex items-center gap-2.5 text-xs text-amber-800 dark:text-amber-300">
            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/>
                <path d="M2 12h20"/>
            </svg>
            <span>Detected Region: <strong>{{ $detectedLocationName ?? 'Your Region' }}</strong>. We do not support your local currency right now, so you need to pay in <strong>USD ($)</strong>.</span>
        </div>
        @endif

        <!-- Search and Filters Bar -->
        <div class="flex flex-col lg:flex-row gap-4 mb-12 bg-white dark:bg-[#111] p-4 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm items-center">
            
            <!-- Search -->
            <div class="relative flex-1 w-full border-r border-gray-200 dark:border-white/10 pr-4">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </div>
                <input x-model="search" type="text" placeholder="Search driver by name..." class="w-full pl-12 pr-4 py-3 bg-transparent border-none text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-0">
            </div>

            <!-- Country / Region Select -->
            <div class="w-full lg:w-56 border-r border-gray-200 dark:border-white/10 pr-4">
                <select x-model="selectedCountry" class="w-full px-4 py-3 bg-transparent border border-gray-200 dark:border-white/10 rounded-xl text-gray-700 dark:text-gray-300 focus:outline-none appearance-none cursor-pointer">
                    <option value="All" class="dark:bg-[#111] dark:text-white">🌍 Worldwide (All)</option>
                    @foreach($countries as $cCode => $cConfig)
                        <option value="{{ $cCode }}" class="dark:bg-[#111] dark:text-white">{{ $cConfig['name'] ?? $cCode }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Availability Select -->
            <div class="w-full lg:w-48 border-r border-gray-200 dark:border-white/10 pr-4">
                <select x-model="availability" class="w-full px-4 py-3 bg-transparent border border-gray-200 dark:border-white/10 rounded-xl text-gray-700 dark:text-gray-300 focus:outline-none appearance-none cursor-pointer">
                    <option value="" class="dark:bg-[#111] dark:text-white">All Availability</option>
                    <option value="available" class="dark:bg-[#111] dark:text-white">Available Now</option>
                </select>
            </div>

            <!-- Rating Select -->
            <div class="w-full lg:w-48 pr-4">
                <select x-model="minRating" class="w-full px-4 py-3 bg-transparent border border-gray-200 dark:border-white/10 rounded-xl text-gray-700 dark:text-gray-300 focus:outline-none appearance-none cursor-pointer">
                    <option value="" class="dark:bg-[#111] dark:text-white">Any Rating</option>
                    <option value="4.5" class="dark:bg-[#111] dark:text-white">4.5+ Stars</option>
                    <option value="4.0" class="dark:bg-[#111] dark:text-white">4.0+ Stars</option>
                </select>
            </div>
            
        </div>

        <!-- Driver Grid -->
        <div id="driver-results-container" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <template x-if="filteredDrivers.length === 0">
                    <div class="col-span-full text-center py-16 bg-white dark:bg-[#111] rounded-3xl border border-gray-100 dark:border-white/10">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-white/5 flex items-center justify-center text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1">No drivers found</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Try relaxing your search or region filters.</p>
                    </div>
                </template>

                <template x-for="driver in paginatedDrivers" :key="driver.id">
                    <div class="bg-white dark:bg-[#111] rounded-2xl border border-gray-100 dark:border-white/10 p-6 shadow-sm hover:shadow-md transition-shadow flex flex-col group relative">
                        
                        <div class="flex items-start gap-4 mb-4">
                            <!-- Image -->
                            <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-[#222] shrink-0 overflow-hidden relative border-2 border-gray-100 dark:border-white/10 flex items-center justify-center text-xl font-bold text-gray-400">
                                <template x-if="driver.photo_url">
                                    <img :src="driver.photo_url" class="w-full h-full object-cover" :alt="driver.user.name">
                                </template>
                                <template x-if="!driver.photo_url">
                                    <span x-text="driver.user.name.split(' ').map(n => n[0]).join('').substring(0, 2)"></span>
                                </template>
                            </div>
                            
                            <!-- Header -->
                            <div class="flex-1 pr-2">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="font-bold text-lg text-gray-900 dark:text-white" x-text="driver.user.name"></h3>
                                    <span x-show="driver.verification_status === 'verified'" title="Verified Driver" class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-500 text-white shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <div class="flex items-center gap-1 bg-amber-50 dark:bg-amber-900/20 px-2 py-0.5 rounded-md">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="text-amber-500"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                        <span class="text-xs font-bold text-amber-700 dark:text-amber-400" x-text="driver.rating"></span>
                                    </div>
                                    <span class="text-xs text-gray-400 dark:text-gray-500" x-text="`(${driver.total_trips || 0} trips)`"></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Bio & Details -->
                        <div class="mb-6 space-y-2">
                            <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed line-clamp-2" x-text="driver.bio || 'Professional driver ready for private and commercial trips.'"></p>
                            
                            <div class="flex flex-wrap gap-2 pt-2">
                                <span class="px-2.5 py-1 bg-gray-100 dark:bg-white/5 rounded-lg text-xs text-gray-600 dark:text-gray-400 font-medium" x-text="`${driver.experience_years || 2}+ Yrs Experience`"></span>
                                <span class="px-2.5 py-1 bg-gray-100 dark:bg-white/5 rounded-lg text-xs text-gray-600 dark:text-gray-400 font-medium" x-text="driver.country"></span>
                                <template x-if="driver.service_area">
                                    <span class="px-2.5 py-1 bg-brand-500/10 text-brand-600 dark:text-brand-400 rounded-lg text-xs font-semibold flex items-center gap-1" x-text="'📍 ' + driver.service_area"></span>
                                </template>
                            </div>
                        </div>

                        <!-- Rates Preview -->
                        <div class="bg-gray-50 dark:bg-[#1a1a1a] rounded-xl p-3 mb-6 flex justify-between items-center text-xs">
                            <div>
                                <span class="text-gray-400 block">Hourly</span>
                                <span class="font-bold text-gray-900 dark:text-white text-sm" x-text="formatRate(driver, 'hourly')"></span>
                            </div>
                            <div class="text-right">
                                <span class="text-gray-400 block">Daily</span>
                                <span class="font-bold text-gray-900 dark:text-white text-sm" x-text="formatRate(driver, 'daily')"></span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mt-auto pt-4 border-t border-gray-100 dark:border-white/10 flex items-center justify-between gap-3">
                            <a :href="'/hire-driver/' + driver.id" class="flex-1 py-2.5 px-3 bg-gray-100 hover:bg-gray-200 dark:bg-white/10 dark:hover:bg-white/20 text-gray-900 dark:text-white font-bold rounded-xl transition-colors text-xs text-center">
                                View Profile
                            </a>
                            <a :href="'/hire-driver/book/' + driver.id + '?country=' + selectedCountry" class="flex-1 py-2.5 px-3 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl transition-colors text-xs text-center shadow-sm">
                                Book Driver
                            </a>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Responsive Pagination Toolbar -->
            <template x-if="filteredDrivers.length > 0">
                <div class="bg-white dark:bg-[#111] rounded-2xl border border-gray-200 dark:border-white/10 p-4 sm:p-5 flex flex-col md:flex-row items-center justify-between gap-4 shadow-sm">
                    <!-- Left: Showing items count -->
                    <div class="text-xs text-gray-500 dark:text-gray-400 font-semibold text-center md:text-left">
                        Showing <span class="font-bold text-gray-900 dark:text-white" x-text="paginationStart"></span> to <span class="font-bold text-gray-900 dark:text-white" x-text="paginationEnd"></span> of <span class="font-bold text-gray-900 dark:text-white" x-text="filteredDrivers.length"></span> drivers
                    </div>

                    <!-- Center: Page Navigation buttons -->
                    <div class="flex items-center gap-1.5" x-show="totalPages > 1">
                        <!-- Prev -->
                        <button type="button" 
                                @click="setPage(currentPage - 1)" 
                                :disabled="currentPage === 1"
                                :class="currentPage === 1 ? 'opacity-40 cursor-not-allowed text-gray-400 border-gray-200 dark:border-white/5' : 'text-gray-700 dark:text-gray-200 border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/5 active:scale-95'"
                                class="px-3 py-2 rounded-xl text-xs font-bold border transition-all flex items-center gap-1">
                            <span>←</span>
                            <span class="hidden sm:inline">Prev</span>
                        </button>

                        <!-- Page Numbers -->
                        <template x-for="(p, idx) in pageNumbers" :key="idx">
                            <div>
                                <template x-if="p === '...'">
                                    <span class="px-2 py-2 text-xs font-bold text-gray-400">...</span>
                                </template>
                                <template x-if="p !== '...'">
                                    <button type="button"
                                            @click="setPage(p)"
                                            :class="currentPage === p ? 'bg-brand-500 text-white font-black shadow-md border-brand-500 scale-105' : 'bg-transparent text-gray-700 dark:text-gray-300 border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/5'"
                                            class="w-9 h-9 rounded-xl text-xs font-bold border flex items-center justify-center transition-all"
                                            x-text="p">
                                    </button>
                                </template>
                            </div>
                        </template>

                        <!-- Next -->
                        <button type="button" 
                                @click="setPage(currentPage + 1)" 
                                :disabled="currentPage === totalPages"
                                :class="currentPage === totalPages ? 'opacity-40 cursor-not-allowed text-gray-400 border-gray-200 dark:border-white/5' : 'text-gray-700 dark:text-gray-200 border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/5 active:scale-95'"
                                class="px-3 py-2 rounded-xl text-xs font-bold border transition-all flex items-center gap-1">
                            <span class="hidden sm:inline">Next</span>
                            <span>→</span>
                        </button>
                    </div>

                    <!-- Right: Per Page Selector -->
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 font-semibold">
                        <span>Drivers per page:</span>
                        <select x-model.number="perPage" class="px-2.5 py-1.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white cursor-pointer focus:ring-1 focus:ring-brand-500">
                            <option :value="3">3</option>
                            <option :value="6">6</option>
                            <option :value="9">9</option>
                            <option :value="12">12</option>
                        </select>
                    </div>
                </div>
            </template>
        </div>

        <p class="text-[10px] text-gray-500 dark:text-gray-400 text-center mt-6">
            By booking a driver, you agree to the <a href="/terms-and-conditions" target="_blank" class="underline font-bold text-indigo-500">Terms & Conditions</a> and <a href="/refund-cancellation-policy" target="_blank" class="underline font-bold text-indigo-500">Refund & Cancellation Policy</a>.
        </p>
    </main>

    <x-stripe-modal serviceType="driver_booking" />
</x-layout>