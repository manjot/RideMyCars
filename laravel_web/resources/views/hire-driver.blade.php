<x-layout theme="theme-hire">
    <x-slot:title>Hire a Driver — RideMyCars</x-slot>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-12"
          x-data="{ 
              search: '{{ request('search') }}', 
              selectedCountry: '{{ request('country', 'USA') }}',
              minRating: '{{ request('rating', '') }}',
              availability: '{{ request('availability', '') }}',
              drivers: {{ Js::from($drivers) }},
              countries: {{ Js::from($countries) }},
              countryDropdownOpen: false,
              regionSearch: '',
              countryList: [
                  { key: 'All', name: 'All Regions', fullName: 'All Global Regions', code: 'ALL', flagUrl: '', symbol: '$' }
              ],
              init() {
                  if (window.WORLD_COUNTRIES && window.WORLD_COUNTRIES.length) {
                      this.countryList = [
                          { key: 'All', name: 'All Regions', fullName: 'All Global Regions', code: 'ALL', flagUrl: '', symbol: '$' },
                          ...window.WORLD_COUNTRIES.map(c => ({
                              key: c.cca3 || c.name,
                              name: c.name,
                              fullName: c.name,
                              code: c.code,
                              cca3: c.cca3,
                              flagUrl: c.flagUrl,
                              symbol: c.symbol
                          }))
                      ];
                  }
              },
              get filteredCountryList() {
                  if (!this.regionSearch) return this.countryList;
                  const q = this.regionSearch.toLowerCase().trim();
                  return this.countryList.filter(c => 
                      c.name.toLowerCase().includes(q) || 
                      c.fullName.toLowerCase().includes(q) || 
                      c.symbol.toLowerCase().includes(q) || 
                      c.key.toLowerCase().includes(q)
                  );
              },
              get selectedCountryObj() {
                  return this.countryList.find(c => c.key === this.selectedCountry) || { key: this.selectedCountry, name: this.selectedCountry, flag: '🌍', symbol: '$' };
              },
              get currencySymbol() {
                  return this.selectedCountryObj.symbol || '$';
              },
              get filteredDrivers() {
                  return this.drivers.filter(d => {
                      const searchStr = this.search.toLowerCase();
                      const matchesSearch = !this.search || d.user.name.toLowerCase().includes(searchStr);
                      const matchesCountry = this.selectedCountry === 'All' || d.country === this.selectedCountry;
                      const matchesAvail = !this.availability || (this.availability === 'available' ? d.is_available : true);
                      const matchesRating = !this.minRating || (parseFloat(d.rating) >= parseFloat(this.minRating));
                      return matchesSearch && matchesCountry && matchesAvail && matchesRating;
                  });
              }
          }">
        
        <!-- Header Text -->
        <div class="mb-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2 tracking-tight">Hire a Professional Driver</h1>
                <p class="text-gray-500 dark:text-gray-400 text-lg">Verified, experienced drivers for Private & Commercial hiring across USA & Africa.</p>
            </div>

            <!-- Country Switcher Dropdown with Search -->
            <div class="relative shrink-0" @click.away="countryDropdownOpen = false">
                <div class="flex items-center gap-2 bg-white dark:bg-[#111] p-1.5 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider pl-2.5">Region:</span>
                    
                    <button type="button" 
                            @click="countryDropdownOpen = !countryDropdownOpen; if(countryDropdownOpen) $nextTick(() => $refs.regionSearchInput?.focus({ preventScroll: true }))"
                            class="flex items-center gap-2 bg-gray-50 hover:bg-gray-100 dark:bg-[#1a1a1a] dark:hover:bg-[#222] text-gray-900 dark:text-white font-bold py-2 px-3 rounded-xl border border-gray-200 dark:border-white/10 text-sm transition-all cursor-pointer select-none">
                        <template x-if="selectedCountryObj.key === 'All'">
                            <span class="text-base leading-none">🌐</span>
                        </template>
                        <template x-if="selectedCountryObj.key !== 'All'">
                            <img :src="selectedCountryObj.flagUrl || `https://flagcdn.com/w40/${(selectedCountryObj.code || 'us').toLowerCase()}.png`" 
                                 :alt="selectedCountryObj.name" 
                                 class="w-5 h-3.5 object-cover rounded-sm shadow-sm border border-black/10 shrink-0">
                        </template>
                        <span class="text-xs font-bold" x-text="`${selectedCountryObj.name} (${selectedCountryObj.symbol})`">USA ($)</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500 transition-transform duration-200 shrink-0" :class="countryDropdownOpen ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <!-- Floating Searchable Dropdown -->
                <div x-show="countryDropdownOpen" 
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                     class="absolute right-0 top-full mt-2 w-72 sm:w-80 bg-white dark:bg-[#141414] rounded-2xl shadow-2xl border border-gray-200 dark:border-white/10 z-50 overflow-hidden"
                     style="display: none;">
                    
                    <!-- Search Input -->
                    <div class="p-3 bg-gray-50 dark:bg-[#1a1a1a] border-b border-gray-100 dark:border-white/10 sticky top-0 z-10">
                        <div class="relative flex items-center bg-white dark:bg-[#222] rounded-xl border border-gray-200 dark:border-white/10 focus-within:border-brand-500 dark:focus-within:border-brand-500 transition-all px-3 py-2">
                            <svg class="w-4 h-4 text-gray-400 shrink-0 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input type="text" 
                                   x-ref="regionSearchInput"
                                   x-model="regionSearch" 
                                   placeholder="Search region or currency..." 
                                   class="w-full bg-transparent text-xs font-semibold text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none border-none p-0">
                            <button type="button" 
                                    x-show="regionSearch" 
                                    @click="regionSearch = ''; $refs.regionSearchInput?.focus({ preventScroll: true })" 
                                    class="text-gray-400 hover:text-gray-600 dark:hover:text-white shrink-0 ml-1 p-0.5"
                                    style="display: none;">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Country List -->
                    <div class="max-h-64 overflow-y-auto p-1.5 space-y-0.5 text-sm">
                        <template x-for="c in filteredCountryList" :key="c.key">
                            <button type="button" 
                                    @click="selectedCountry = c.key; countryDropdownOpen = false; regionSearch = ''; window.history.pushState({}, '', '/hire-driver?country=' + c.key);"
                                    class="w-full px-3 py-2.5 rounded-xl flex items-center justify-between hover:bg-gray-100 dark:hover:bg-white/5 transition-all text-left group cursor-pointer"
                                    :class="selectedCountry === c.key ? 'bg-brand-500 text-white hover:bg-brand-600 font-bold' : 'text-gray-800 dark:text-gray-200'">
                                <div class="flex items-center gap-2.5 min-w-0 pr-2">
                                    <template x-if="c.key === 'All'">
                                        <span class="text-base leading-none shrink-0">🌐</span>
                                    </template>
                                    <template x-if="c.key !== 'All'">
                                        <img :src="c.flagUrl || `https://flagcdn.com/w40/${(c.code || 'us').toLowerCase()}.png`" 
                                             :alt="c.name" 
                                             loading="lazy"
                                             class="w-5 h-3.5 object-cover rounded-sm shadow-sm border border-black/10 shrink-0">
                                    </template>
                                    <span class="text-xs truncate" 
                                          :class="selectedCountry === c.key ? 'text-white' : 'text-gray-900 dark:text-white group-hover:text-black dark:group-hover:text-white font-medium'" 
                                          x-text="c.name"></span>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-md" 
                                          :class="selectedCountry === c.key ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-gray-400 group-hover:text-black dark:group-hover:text-white'"
                                          x-text="c.symbol"></span>
                                    <span x-show="selectedCountry === c.key" class="text-xs font-bold text-white">✓</span>
                                </div>
                            </button>
                        </template>
                        
                        <div x-show="filteredCountryList.length === 0" class="py-6 text-center" style="display: none;">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">No countries found</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filters Bar -->
        <div class="flex flex-col lg:flex-row gap-4 mb-12 bg-white dark:bg-[#111] p-4 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm items-center">
            
            <!-- Search -->
            <div class="relative flex-1 w-full border-r border-gray-200 dark:border-white/10 pr-4">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </div>
                <input x-model="search" type="text" placeholder="Search driver by name..." class="w-full pl-12 pr-4 py-3 bg-transparent border-none text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-0">
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

            <template x-for="driver in filteredDrivers" :key="driver.id">
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
                        </div>
                    </div>

                    <!-- Rates Preview -->
                    <div class="bg-gray-50 dark:bg-[#1a1a1a] rounded-xl p-3 mb-6 flex justify-between items-center text-xs">
                        <div>
                            <span class="text-gray-400 block">Hourly</span>
                            <span class="font-bold text-gray-900 dark:text-white text-sm" x-text="currencySymbol + (driver.hourly_rate || '25.00') + '/hr'"></span>
                        </div>
                        <div class="text-right">
                            <span class="text-gray-400 block">Daily</span>
                            <span class="font-bold text-gray-900 dark:text-white text-sm" x-text="currencySymbol + (driver.daily_rate || (driver.hourly_rate * 8 * 0.85).toFixed(2)) + '/day'"></span>
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

        <p class="text-[10px] text-gray-500 dark:text-gray-400 text-center mt-6">
            By booking a driver, you agree to the <a href="/terms-and-conditions" target="_blank" class="underline font-bold text-indigo-500">Terms & Conditions</a> and <a href="/refund-cancellation-policy" target="_blank" class="underline font-bold text-indigo-500">Refund & Cancellation Policy</a>.
        </p>
    </main>

    <x-stripe-modal serviceType="driver_booking" />
</x-layout>