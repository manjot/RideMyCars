<x-layout theme="theme-rent">
    <x-slot:title>Car Rental Search & Comparison — RideMyCars</x-slot>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10"
          x-data="{
              search: '{{ request('search', '') }}',
              selectedCategory: '{{ request('category', 'All') }}',
              selectedTransmission: '{{ request('transmission', 'All') }}',
              selectedFuel: '{{ request('fuel_type', 'All') }}',
              selectedSeats: '{{ request('seats', 'All') }}',
              selectedFuelPolicy: '{{ request('fuel_policy', 'All') }}',
              sortOption: '{{ request('sort', 'recommended') }}',
              differentDropoff: {{ $differentDropoff ? 'true' : 'false' }},
              vehicles: {{ Js::from($vehicles) }},
              categories: ['All', 'Economy', 'Compact', 'Sedan', 'SUV', 'Luxury', 'Van'],
              transmissions: ['All', 'Automatic', 'Manual'],
              fuelTypes: ['All', 'Petrol', 'Diesel', 'Hybrid', 'Electric'],
              seatsList: ['All', '2+', '4+', '5+', '7+'],
              
              get filteredVehicles() {
                  let res = this.vehicles.filter(v => {
                      // Category
                      const catLower = this.selectedCategory.toLowerCase();
                      const vType = (v.type || v.category || '').toLowerCase();
                      const matchesCategory = this.selectedCategory === 'All' || 
                                            vType.includes(catLower) || 
                                            (catLower === 'economy' && (vType.includes('economy') || vType.includes('compact')));

                      // Transmission
                      const transLower = this.selectedTransmission.toLowerCase();
                      const matchesTrans = this.selectedTransmission === 'All' || (v.transmission || 'automatic').toLowerCase() === transLower;

                      // Fuel
                      const fuelLower = this.selectedFuel.toLowerCase();
                      const matchesFuel = this.selectedFuel === 'All' || (v.fuel_type || 'petrol').toLowerCase() === fuelLower;

                      // Seats
                      const minS = this.selectedSeats === 'All' ? 0 : parseInt(this.selectedSeats);
                      const matchesSeats = (v.seats || 5) >= minS;

                      // Fuel Policy
                      const matchesFuelPolicy = this.selectedFuelPolicy === 'All' || (v.fuel_policy || 'Full-to-Full') === this.selectedFuelPolicy;

                      // Search Text
                      const sStr = this.search.toLowerCase();
                      const matchesSearch = !this.search || 
                                            v.make.toLowerCase().includes(sStr) || 
                                            v.model.toLowerCase().includes(sStr) ||
                                            (v.type || '').toLowerCase().includes(sStr);

                      return matchesCategory && matchesTrans && matchesFuel && matchesSeats && matchesFuelPolicy && matchesSearch;
                  });

                  // Sorting
                  if (this.sortOption === 'price_asc') {
                      return res.sort((a, b) => a.daily_rate - b.daily_rate);
                  } else if (this.sortOption === 'price_desc') {
                      return res.sort((a, b) => b.daily_rate - a.daily_rate);
                  }
                  return res.sort((a, b) => (b.year || 2024) - (a.year || 2024));
              }
          }">

        <!-- Category Banner Component -->
        <x-category-banner category="Rent" />

        <!-- Hero & RideMyCars Rental Search Bar Card -->
        <div class="mb-10 bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 md:p-8 shadow-xl relative z-20">
            <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="px-3 py-1 rounded-full bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400 font-extrabold text-xs uppercase tracking-wider border border-brand-200 dark:border-brand-800/30">RideMyCars Premium Car Rental</span>
                    <h1 class="text-3xl md:text-4xl font-black text-gray-900 dark:text-white mt-2 tracking-tight">Compare & Book Rental Cars</h1>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Transparent pricing, zero hidden fees, 20% online deposit, and 80% balance at pickup.</p>
                </div>
                <div class="shrink-0 flex items-center gap-3">
                    <a href="/hire-driver" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md flex items-center gap-2 transition-all">
                        👨‍✈️ Need a Chauffeur? Hire Driver
                    </a>
                </div>
            </div>

            <!-- Search Form -->
            <form action="/rent" method="GET" class="space-y-6">
                <!-- Location Toggle Row -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-extrabold text-gray-800 dark:text-gray-200">
                        <input type="checkbox" name="different_dropoff" value="1" x-model="differentDropoff" class="w-4 h-4 text-brand-500 rounded focus:ring-brand-500">
                        <span>Return car to a different location</span>
                    </label>
                </div>

                <!-- Locations & Date Pickers Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Pickup Location -->
                    <div class="relative">
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Pick-up Location *</label>
                            <button type="button" id="use_my_location_rent_main" class="text-brand-500 hover:text-brand-600 text-[11px] font-bold flex items-center gap-0.5">
                                📍 Use My Location
                            </button>
                        </div>
                        <input type="text" id="pickup_location_main" name="pickup_location" value="{{ $pickupLocation }}" required placeholder="City, Airport, or Address..." class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20">
                    </div>

                    <!-- Dropoff Location (Conditional) -->
                    <div x-show="differentDropoff" class="relative">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Drop-off Location *</label>
                        <input type="text" id="dropoff_location_main" name="dropoff_location" value="{{ $dropoffLocation }}" placeholder="Return city or location..." class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-bold text-gray-900 dark:text-white">
                    </div>

                    <!-- Pickup Date & Time -->
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Pick-up Date *</label>
                            <input type="date" name="start_date" value="{{ $startDate }}" min="{{ date('Y-m-d') }}" required class="w-full px-3 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-bold text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Time *</label>
                            <input type="time" name="pickup_time" value="{{ $pickupTime }}" required class="w-full px-3 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-bold text-gray-900 dark:text-white">
                        </div>
                    </div>

                    <!-- Return Date & Time -->
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Return Date *</label>
                            <input type="date" name="return_date" value="{{ $returnDate }}" min="{{ date('Y-m-d') }}" required class="w-full px-3 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-bold text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Time *</label>
                            <input type="time" name="return_time" value="{{ $returnTime }}" required class="w-full px-3 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-bold text-gray-900 dark:text-white">
                        </div>
                    </div>
                </div>

                <!-- Driver Details Row & Submit -->
                <div class="flex flex-col sm:flex-row items-end justify-between gap-4 pt-2 border-t border-gray-100 dark:border-white/10">
                    <div class="grid grid-cols-2 gap-4 w-full sm:w-auto">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Driver Age (18+) *</label>
                            <input type="number" name="driver_age" value="{{ $driverAge }}" min="18" max="120" required class="w-32 px-3 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-bold text-gray-900 dark:text-white">
                        </div>
                        <div x-data="{
                            open: false,
                            search: '',
                            countries: (window.WORLD_COUNTRIES && window.WORLD_COUNTRIES.length) ? window.WORLD_COUNTRIES : [],
                            init() {
                                if (!this.countries.length && window.WORLD_COUNTRIES) {
                                    this.countries = window.WORLD_COUNTRIES;
                                }
                            },
                            selected: (window.WORLD_COUNTRIES || []).find(c => c.cca3 === '{{ $driverCountry }}' || c.name === '{{ $driverCountry }}' || c.code === '{{ $driverCountry }}') || (window.WORLD_COUNTRIES && window.WORLD_COUNTRIES[0]) || { name: 'United States', code: 'US', cca3: 'USA', flagUrl: 'https://flagcdn.com/w40/us.png' },
                            get list() {
                                const all = (this.countries && this.countries.length) ? this.countries : (window.WORLD_COUNTRIES || []);
                                if (!this.search || !this.search.trim()) return all;
                                const q = this.search.toLowerCase().trim();
                                return all.filter(c => (c.name && c.name.toLowerCase().includes(q)) || (c.code && c.code.toLowerCase().includes(q)) || (c.cca3 && c.cca3.toLowerCase().includes(q)));
                            }
                        }" class="relative">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Residence *</label>
                            <input type="hidden" name="driver_country" :value="selected.cca3 || selected.name">
                            
                            <button type="button" @click="open = !open; if(open) $nextTick(() => $refs.residenceSearch?.focus({ preventScroll: true }))"
                                    class="w-44 sm:w-48 px-3.5 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-bold text-gray-900 dark:text-white flex items-center justify-between transition-colors cursor-pointer select-none"
                                    :class="open ? 'ring-2 ring-brand-500 border-brand-500' : ''">
                                <div class="flex items-center gap-2 min-w-0 pr-1">
                                    <img :src="selected.flagUrl || `https://flagcdn.com/w40/${(selected.code || 'us').toLowerCase()}.png`" 
                                         :alt="selected.name" 
                                         class="w-4 h-3 object-cover rounded-sm shadow-sm border border-black/10 shrink-0">
                                    <span class="truncate" x-text="selected.name"></span>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-400 shrink-0 ml-1 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            
                            <!-- Country Selection Popover Dropdown -->
                            <div x-show="open" 
                                 @click.away="open = false" 
                                 x-cloak
                                 style="display: none;"
                                 class="absolute left-0 sm:left-auto sm:right-0 md:left-0 top-full mt-2 w-72 sm:w-80 max-w-[calc(100vw-2.5rem)] bg-white dark:bg-[#181818] rounded-2xl shadow-2xl border border-gray-200 dark:border-white/10 z-[99999] overflow-hidden">
                                
                                <!-- Search Header -->
                                <div class="p-2.5 border-b border-gray-100 dark:border-white/5 sticky top-0 bg-white dark:bg-[#181818] z-10 flex items-center gap-2">
                                    <span class="text-gray-400 text-xs pl-1">🔍</span>
                                    <input type="text" x-ref="residenceSearch" x-model="search" placeholder="Search from 250 countries..."
                                           class="w-full px-2 py-1.5 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-lg text-xs font-semibold text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-brand-500">
                                    <button type="button" x-show="search" @click="search = ''" class="text-gray-400 hover:text-gray-600 text-xs px-1">✕</button>
                                </div>

                                <!-- Countries Count Subtitle -->
                                <div class="px-3 py-1 bg-gray-50/70 dark:bg-white/5 border-b border-gray-100 dark:border-white/5 flex items-center justify-between text-[10px] text-gray-400 font-semibold uppercase tracking-wider">
                                    <span>Select Residence Country</span>
                                    <span x-text="list.length + ' countries'"></span>
                                </div>

                                <!-- Countries List -->
                                <div class="max-h-64 overflow-y-auto p-1 text-xs space-y-0.5 divide-y divide-gray-50 dark:divide-white/5">
                                    <template x-for="c in list" :key="c.code">
                                        <button type="button" @click="selected = c; open = false; search = ''"
                                                class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-left transition-colors hover:bg-gray-100 dark:hover:bg-white/10 cursor-pointer"
                                                :class="selected.code === c.code ? 'bg-brand-500 text-slate-950 font-black hover:bg-brand-600' : 'text-gray-800 dark:text-gray-200'">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <img :src="c.flagUrl || `https://flagcdn.com/w40/${(c.code || 'us').toLowerCase()}.png`" 
                                                     :alt="c.name" 
                                                     loading="lazy"
                                                     class="w-5 h-3.5 object-cover rounded-sm shadow-sm border border-black/10 shrink-0">
                                                <span class="truncate" x-text="c.name"></span>
                                            </div>
                                            <span class="font-mono text-[10px] opacity-70 shrink-0 uppercase" x-text="c.cca3 || c.code"></span>
                                        </button>
                                    </template>
                                    <div x-show="list.length === 0" class="p-4 text-center text-xs text-gray-400 font-semibold">
                                        No matching country found
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full sm:w-auto px-8 py-3.5 bg-brand-500 hover:bg-brand-600 text-slate-950 font-black text-sm rounded-xl transition-all shadow-md shadow-brand-500/25 flex items-center justify-center gap-2 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        Search Available Cars
                    </button>
                </div>
            </form>
        </div>

        <!-- Filter & Sorting Bar -->
        <div class="bg-white dark:bg-[#111] p-4 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm mb-8 flex flex-col lg:flex-row gap-4 items-center justify-between">
            
            <!-- Category Tabs -->
            <div class="flex items-center gap-2 overflow-x-auto w-full lg:w-auto pb-2 lg:pb-0 scrollbar-none">
                <template x-for="cat in categories" :key="cat">
                    <button @click="selectedCategory = cat"
                            :class="selectedCategory === cat ? 'bg-brand-500 text-slate-950 shadow-sm font-black' : 'bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 font-semibold hover:bg-gray-200 dark:hover:bg-white/10'"
                            class="px-4 py-2 rounded-xl text-xs whitespace-nowrap transition-all">
                        <span x-text="cat"></span>
                    </button>
                </template>
            </div>

            <!-- Inline Specs Filters -->
            <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto justify-end text-xs">
                <!-- Search text -->
                <input x-model="search" type="text" placeholder="Search make, model..." class="px-3 py-2 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white">

                <!-- Transmission -->
                <select x-model="selectedTransmission" class="px-3 py-2 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white cursor-pointer">
                    <option value="All">All Transmissions</option>
                    <option value="Automatic">Automatic</option>
                    <option value="Manual">Manual</option>
                </select>

                <!-- Fuel -->
                <select x-model="selectedFuel" class="px-3 py-2 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white cursor-pointer">
                    <option value="All">All Fuel Types</option>
                    <option value="Petrol">Petrol</option>
                    <option value="Diesel">Diesel</option>
                    <option value="Hybrid">Hybrid</option>
                    <option value="Electric">Electric</option>
                </select>

                <!-- Sort -->
                <select x-model="sortOption" class="px-3 py-2 bg-brand-50 dark:bg-brand-900/30 border border-brand-200 dark:border-brand-800/30 rounded-xl font-extrabold text-brand-700 dark:text-brand-300 cursor-pointer">
                    <option value="recommended">Sort: Recommended</option>
                    <option value="price_asc">Price: Low to High</option>
                    <option value="price_desc">Price: High to Low</option>
                </select>
            </div>
        </div>

        <!-- Vehicle Results Grid (RideMyCars Card Comparison) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <template x-if="filteredVehicles.length === 0">
                <div class="col-span-full text-center py-16 bg-white dark:bg-[#111] rounded-3xl border border-gray-100 dark:border-white/10">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-white/5 flex items-center justify-center text-gray-400">
                        🚗
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1">No vehicles available</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Try adjusting your pickup dates, locations, or filter criteria.</p>
                </div>
            </template>

            <template x-for="vehicle in filteredVehicles" :key="vehicle.id">
                <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 shadow-sm hover:shadow-xl transition-all flex flex-col group relative">
                    
                    <!-- Top Category & Supplier Badge -->
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-3 py-1 bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300 text-[11px] font-extrabold rounded-lg uppercase tracking-wider border border-brand-200 dark:border-brand-800/30" x-text="vehicle.type || vehicle.category || 'Sedan'"></span>
                        <span class="text-[11px] text-gray-400 font-semibold" x-text="vehicle.owner ? 'Owner: ' + vehicle.owner.name : 'Verified Fleet'"></span>
                    </div>

                    <!-- Vehicle Image -->
                    <div class="w-full h-44 bg-gray-50 dark:bg-[#181818] rounded-2xl mb-4 overflow-hidden relative flex items-center justify-center p-3 border border-gray-100 dark:border-white/5">
                        <img :src="vehicle.image_src || '/images/hero-rent.png'" class="w-full h-full object-contain transition-transform group-hover:scale-105" :alt="vehicle.make + ' ' + vehicle.model" onerror="this.onerror=null;this.src='/images/hero-rent.png';">
                    </div>

                    <!-- Make & Model Title -->
                    <div class="mb-4">
                        <h3 class="font-extrabold text-xl text-gray-900 dark:text-white tracking-tight" x-text="`${vehicle.year} ${vehicle.make} ${vehicle.model}`"></h3>
                        <span class="text-xs text-gray-400 font-medium">Or Similar Category Vehicle</span>
                    </div>

                    <!-- Specs Icons Bar -->
                    <div class="grid grid-cols-4 gap-2 py-3 px-3 bg-gray-50 dark:bg-[#1a1a1a] rounded-xl text-[11px] text-gray-600 dark:text-gray-300 mb-4 border border-gray-100 dark:border-white/5 font-semibold text-center">
                        <div title="Transmission">⚙️ <span x-text="vehicle.transmission === 'manual' ? 'Manual' : 'Auto'"></span></div>
                        <div title="Fuel Type">⛽ <span x-text="vehicle.fuel_type || 'Petrol'"></span></div>
                        <div title="Passengers">👤 <span x-text="(vehicle.seats || 5) + ' Seats'"></span></div>
                        <div title="Luggage">🧳 <span x-text="(vehicle.luggage || 2) + ' Bags'"></span></div>
                    </div>

                    <!-- Inclusions Badges -->
                    <div class="space-y-1.5 mb-6 text-xs font-semibold text-gray-600 dark:text-gray-400">
                        <div class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400">
                            ✓ <span x-text="vehicle.fuel_policy || 'Full-to-Full Tank'"></span>
                        </div>
                        <div class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400">
                            ✓ <span x-text="vehicle.mileage_policy || 'Unlimited Mileage'"></span>
                        </div>
                        <div class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400">
                            🛡️ $<span x-text="vehicle.security_deposit_amount || 200"></span> Refundable Deposit
                        </div>
                    </div>

                    <!-- Price & Part Payment Summary Card -->
                    <div class="mt-auto pt-4 border-t border-gray-100 dark:border-white/10">
                        <div class="flex items-baseline justify-between mb-1">
                            <span class="text-xs font-bold text-gray-400">Rate / Day</span>
                            <div>
                                <span class="text-2xl font-black text-gray-900 dark:text-white" x-text="`$${parseFloat(vehicle.daily_rate).toFixed(2)}`"></span>
                                <span class="text-xs text-gray-400">/day</span>
                            </div>
                        </div>

                        <div class="bg-brand-50/50 dark:bg-brand-950/20 p-2.5 rounded-xl border border-brand-200 dark:border-brand-800/30 mb-4 space-y-1 text-xs">
                            <div class="flex justify-between font-bold text-brand-700 dark:text-brand-300">
                                <span>Total (<span x-text="vehicle.rental_days || 1"></span> Days):</span>
                                <span x-text="`$${(vehicle.total_rental_price || vehicle.daily_rate).toFixed(2)}`"></span>
                            </div>
                            <div class="flex justify-between text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold">
                                <span>20% Online Deposit:</span>
                                <span x-text="`$${(vehicle.deposit_amount || (vehicle.daily_rate * 0.20)).toFixed(2)}`"></span>
                            </div>
                            <div class="flex justify-between text-[11px] text-amber-600 dark:text-amber-400 font-semibold">
                                <span>80% Balance at Pickup:</span>
                                <span x-text="`$${(vehicle.pickup_balance || (vehicle.daily_rate * 0.80)).toFixed(2)}`"></span>
                            </div>
                        </div>

                        <a :href="`/rent/${vehicle.id}?start_date={{ $startDate }}&pickup_time={{ $pickupTime }}&return_date={{ $returnDate }}&return_time={{ $returnTime }}&pickup_location=${encodeURIComponent('{{ $pickupLocation }}')}&dropoff_location=${encodeURIComponent('{{ $dropoffLocation }}')}&driver_age={{ $driverAge }}&driver_country={{ $driverCountry }}`"
                           class="w-full py-3.5 bg-brand-500 hover:bg-brand-600 text-white font-extrabold rounded-xl transition-all shadow-md text-xs text-center block uppercase tracking-wider">
                            View Deal & Customize
                        </a>
                    </div>

                </div>
            </template>
            
        </div>
    </main>

    <!-- Google Places Autocomplete Script for Rent Page -->
    @php
        $gmapsKey = config('services.google_maps.api_key');
        $hasValidKey = !empty($gmapsKey) && !str_contains($gmapsKey, 'AIzaSyDemoKey');
    @endphp

    @if($hasValidKey)
        <script src="https://maps.googleapis.com/maps/api/js?key={{ $gmapsKey }}&libraries=places"></script>
    @endif

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const pInput = document.getElementById("pickup_location_main");
            const dInput = document.getElementById("dropoff_location_main");
            const locBtn = document.getElementById("use_my_location_rent_main");

            @if($hasValidKey)
                if (window.google && google.maps && google.maps.places) {
                    try {
                        if (pInput) new google.maps.places.Autocomplete(pInput);
                        if (dInput) new google.maps.places.Autocomplete(dInput);
                    } catch (e) {}
                }
            @endif

            if (locBtn && pInput) {
                locBtn.addEventListener("click", () => {
                    if (!navigator.geolocation) {
                        alert("Geolocation is not supported by your browser.");
                        return;
                    }
                    locBtn.disabled = true;
                    locBtn.innerText = "Locating...";

                    navigator.geolocation.getCurrentPosition(
                        async (pos) => {
                            try {
                                const res = await fetch(`/api/places/reverse?lat=${pos.coords.latitude}&lng=${pos.coords.longitude}`);
                                if (res.ok) {
                                    const data = await res.json();
                                    if (data && data.place) {
                                        pInput.value = data.place.formatted_address || data.place.name;
                                    }
                                }
                            } catch (e) {}
                            locBtn.disabled = false;
                            locBtn.innerText = "📍 Use My Location";
                        },
                        () => {
                            locBtn.disabled = false;
                            locBtn.innerText = "📍 Use My Location";
                            alert("Unable to detect location automatically. Please type manually.");
                        }
                    );
                });
            }
        });
    </script>
    <x-stripe-modal serviceType="rental" />
</x-layout>