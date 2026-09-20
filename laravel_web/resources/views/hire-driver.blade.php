<x-layout theme="theme-hire">
    <x-slot:title>Hire a Driver — RideMyCars</x-slot>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-12"
          x-data="{ 
              search: '{{ request('search') }}', 
              selectedCountry: '{{ $selectedCountry ?? ($currentCountryCode ?? 'All') }}',
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

              resetFilters() {
                  this.search = '';
                  this.selectedCountry = 'All';
                  this.availability = '';
                  this.minRating = '';
                  this.currentPage = 1;
              },

              get isFiltered() {
                  return Boolean(this.search) || (this.selectedCountry && this.selectedCountry !== 'All') || Boolean(this.availability) || Boolean(this.minRating);
              },

              get filteredDrivers() {
                  return this.drivers.filter(d => {
                      const searchStr = (this.search || '').trim().toLowerCase();
                      const driverName = ((d.user && d.user.name) || d.name || '').toLowerCase();
                      const bio = (d.bio || '').toLowerCase();
                      const serviceArea = (d.service_area || '').toLowerCase();

                      const matchesSearch = !searchStr 
                          || driverName.includes(searchStr) 
                          || bio.includes(searchStr)
                          || serviceArea.includes(searchStr);

                      const driverCountry = (d.country || '').trim().toUpperCase();
                      const sel = (this.selectedCountry || 'All').trim().toUpperCase();

                      const matchesCountry = sel === 'ALL' 
                          || driverCountry === sel
                          || (sel === 'GHA' && (driverCountry === 'GHANA' || driverCountry === 'GH'))
                          || (sel === 'USA' && (driverCountry === 'UNITED STATES' || driverCountry === 'US' || driverCountry === 'AMERICA'))
                          || (sel === 'NGA' && (driverCountry === 'NIGERIA' || driverCountry === 'NG'))
                          || (sel === 'ZAF' && (driverCountry === 'SOUTH AFRICA' || driverCountry === 'ZA'))
                          || (sel === 'IND' && (driverCountry === 'INDIA' || driverCountry === 'IN'))
                          || (sel === 'GBR' && (driverCountry === 'UNITED KINGDOM' || driverCountry === 'UK' || driverCountry === 'GB'))
                          || (sel === 'EUR' && (driverCountry === 'EUROPE' || driverCountry === 'EU' || driverCountry === 'GERMANY' || driverCountry === 'FRANCE'))
                          || (sel === 'ARE' && (driverCountry === 'UNITED ARAB EMIRATES' || driverCountry === 'UAE' || driverCountry === 'AE'))
                          || (sel === 'KEN' && (driverCountry === 'KENYA' || driverCountry === 'KE'));

                      const matchesAvail = !this.availability || (this.availability === 'available' ? (d.is_available == 1 || d.is_available === true) : true);
                      const matchesRating = !this.minRating || (parseFloat(d.rating || 0) >= parseFloat(this.minRating));
                      return matchesSearch && matchesCountry && matchesAvail && matchesRating;
                  });
              },

              formatRate(driver, type) {
                  const symbolMap = {
                      'GHA': 'GH₵', 'GHANA': 'GH₵',
                      'USA': '$', 'UNITED STATES': '$',
                      'NGA': '₦', 'NIGERIA': '₦',
                      'ZAF': 'R', 'SOUTH AFRICA': 'R',
                      'IND': '₹', 'INDIA': '₹',
                      'GBR': '£', 'UNITED KINGDOM': '£', 'UK': '£',
                      'EUR': '€', 'EUROPE': '€',
                      'ARE': 'AED', 'UNITED ARAB EMIRATES': 'AED', 'UAE': 'AED',
                      'KEN': 'KSh', 'KENYA': 'KSh',
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
        <div class="mb-8 bg-white dark:bg-[#111] p-3 sm:p-4 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
                
                <!-- Search Input -->
                <div class="relative lg:col-span-4">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </div>
                    <input x-model="search" type="text" placeholder="Search driver by name or city..." class="w-full pl-10 pr-9 py-2.5 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                    <button x-show="search" @click="search = ''" type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Country / Region Select -->
                <div class="relative lg:col-span-3">
                    <select x-model="selectedCountry" class="w-full pl-3.5 pr-10 py-2.5 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 appearance-none cursor-pointer transition-all">
                        <option value="All" class="dark:bg-[#181818] dark:text-white">🌍 Worldwide (All)</option>
                        @foreach($countries as $cCode => $cConfig)
                            <option value="{{ $cCode }}" class="dark:bg-[#181818] dark:text-white">{{ $cConfig['flag'] ?? '' }} {{ $cConfig['name'] ?? $cCode }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>

                <!-- Availability Select -->
                <div class="relative lg:col-span-2">
                    <select x-model="availability" class="w-full pl-3.5 pr-10 py-2.5 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 appearance-none cursor-pointer transition-all">
                        <option value="" class="dark:bg-[#181818] dark:text-white">All Availability</option>
                        <option value="available" class="dark:bg-[#181818] dark:text-white">Available Now</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>

                <!-- Rating Select -->
                <div class="relative lg:col-span-2">
                    <select x-model="minRating" class="w-full pl-3.5 pr-10 py-2.5 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 appearance-none cursor-pointer transition-all">
                        <option value="" class="dark:bg-[#181818] dark:text-white">Any Rating</option>
                        <option value="4.5" class="dark:bg-[#181818] dark:text-white">⭐ 4.5+ Stars</option>
                        <option value="4.0" class="dark:bg-[#181818] dark:text-white">⭐ 4.0+ Stars</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>

                <!-- Reset Button -->
                <div class="lg:col-span-1 flex justify-end">
                    <button x-show="isFiltered" @click="resetFilters()" type="button" title="Reset all filters" class="w-full lg:w-auto px-3 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white text-xs font-semibold flex items-center justify-center gap-1 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span class="lg:hidden">Reset</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Driver Grid -->
        <div id="driver-results-container" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <template x-if="filteredDrivers.length === 0">
                    <div class="col-span-full text-center py-16 bg-white dark:bg-[#111] rounded-3xl border border-gray-100 dark:border-white/10 px-6">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-amber-500/10 flex items-center justify-center text-amber-500">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No drivers found in this view</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm max-w-md mx-auto mb-6">We don't have available drivers matching this specific country or search criteria right now.</p>
                        <button @click="resetFilters()" type="button" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl text-sm transition-all shadow-md hover:shadow-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Explore Worldwide Drivers
                        </button>
                    </div>
                </template>

                <template x-for="driver in paginatedDrivers" :key="driver.id">
                    <div class="bg-white dark:bg-[#111] rounded-2xl border border-gray-100 dark:border-white/10 p-6 shadow-sm hover:shadow-md transition-shadow flex flex-col group relative">
                        
                        <div class="flex items-start gap-4 mb-4">
                            <!-- Image -->
                            <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-[#222] shrink-0 overflow-hidden relative border-2 border-gray-100 dark:border-white/10 flex items-center justify-center text-xl font-bold text-gray-400">
                                <template x-if="driver.photo_url">
                                    <img :src="driver.photo_url" class="w-full h-full object-cover" :alt="((driver.user && driver.user.name) || driver.name || 'Driver')">
                                </template>
                                <template x-if="!driver.photo_url">
                                    <span x-text="(((driver.user && driver.user.name) || driver.name || 'DR').split(' ').map(n => n[0]).join('').substring(0, 2)).toUpperCase()"></span>
                                </template>
                            </div>
                            
                            <!-- Header -->
                            <div class="flex-1 pr-2">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="font-bold text-lg text-gray-900 dark:text-white" x-text="(driver.user && driver.user.name) || driver.name || 'Professional Driver'"></h3>
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