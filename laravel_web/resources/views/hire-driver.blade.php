<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hire a Driver — RideMyCars</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#fafafa] text-gray-900 min-h-screen flex flex-col">
    
    <!-- Header -->
    <header class="top-0 left-0 right-0 z-50 bg-white border-b border-gray-100">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex h-20 items-center justify-between">
                <!-- Logo -->
                <a class="flex items-center gap-2 group" href="/">
                    <div class="w-10 h-10 rounded-xl bg-orange-500 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-white"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"></path><circle cx="7" cy="17" r="2"></circle><path d="M9 17h6"></path><circle cx="17" cy="17" r="2"></circle></svg>
                    </div>
                    <span class="font-bold text-2xl tracking-tight text-gray-900">Ride<span class="text-orange-500">MyCars</span></span>
                </a>
                
                <!-- Desktop Nav -->
                <div class="hidden lg:flex items-center gap-6">
                    <a class="text-sm font-medium transition-colors text-gray-500 hover:text-gray-900" href="/ride">Ride</a>
                    <a class="text-sm font-medium transition-colors text-gray-500 hover:text-gray-900" href="/rent">Rent Vehicle</a>
                    <a class="text-sm font-medium transition-colors text-gray-900 bg-gray-100 px-4 py-2 rounded-full" href="/hire-driver">Hire Driver</a>
                    <a class="text-sm font-medium transition-colors text-gray-500 hover:text-gray-900 flex items-center gap-1" href="/company">Company <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg></a>
                    <a class="text-sm font-medium transition-colors text-gray-500 hover:text-gray-900" href="/pricing">Pricing</a>
                </div>
                
                <!-- Actions -->
                <div class="flex items-center gap-6">
                    <button class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                    </button>
                    <a class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors" href="/login">Sign In</a>
                    <a class="px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-xl transition-all shadow-md shadow-orange-500/25" href="/signup">Get Started</a>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Application -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-12"
          x-data="{ 
              search: '', 
              maxRate: 150, 
              drivers: {{ Js::from($drivers) }},
              get filteredDrivers() {
                  return this.drivers.filter(d => {
                      const searchStr = this.search.toLowerCase();
                      const matchesSearch = d.user.name.toLowerCase().includes(searchStr);
                      const matchesRate = parseFloat(d.hourly_rate) <= this.maxRate;
                      return matchesSearch && matchesRate;
                  });
              }
          }">
        
        <!-- Header Text -->
        <div class="mb-10">
            <h1 class="text-4xl font-bold text-gray-900 mb-3 tracking-tight">Hire a Professional Driver</h1>
            <p class="text-gray-500 text-lg">Background-verified, experienced drivers for a day, week, or longer.</p>
        </div>

        <!-- Search and Filters Bar -->
        <div class="flex flex-col lg:flex-row gap-4 mb-12 bg-white p-4 rounded-2xl border border-gray-200 shadow-sm items-center">
            
            <!-- Search -->
            <div class="relative flex-1 w-full border-r border-gray-200 pr-4">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </div>
                <input x-model="search" type="text" placeholder="Search drivers..." class="w-full pl-12 pr-4 py-3 bg-transparent border-none text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-0">
            </div>

            <!-- Selects -->
            <div class="w-full lg:w-48 border-r border-gray-200 pr-4">
                <select class="w-full px-4 py-3 bg-transparent border border-gray-200 rounded-xl text-gray-700 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 appearance-none">
                    <option>All</option>
                    <option>Available Now</option>
                    <option>Available Later</option>
                </select>
            </div>

            <div class="w-full lg:w-48 border-r border-gray-200 pr-4">
                <select class="w-full px-4 py-3 bg-transparent border border-gray-200 rounded-xl text-gray-700 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 appearance-none">
                    <option>Any</option>
                    <option>5 Stars Only</option>
                    <option>4+ Stars</option>
                </select>
            </div>

            <!-- Slider -->
            <div class="w-full lg:w-64 pl-4 pt-2">
                <div class="flex justify-between items-center mb-2">
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Max hourly rate</label>
                    <span class="text-sm font-bold text-gray-900" x-text="`$${maxRate}`"></span>
                </div>
                <input type="range" min="10" max="150" x-model="maxRate" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-orange-500">
            </div>
            
        </div>

        <!-- Driver Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <template x-if="filteredDrivers.length === 0">
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500 text-lg">No drivers found matching your criteria.</p>
                </div>
            </template>

            <template x-for="driver in filteredDrivers" :key="driver.id">
                <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-shadow flex flex-col group cursor-pointer">
                    
                    <div class="flex items-start gap-4 mb-4">
                        <!-- Image -->
                        <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center text-gray-300 shrink-0 overflow-hidden relative">
                            <svg x-show="!driver.image_url" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <img x-show="driver.image_url" :src="driver.image_url ? '/storage/' + driver.image_url : ''" class="absolute inset-0 w-full h-full object-cover" :alt="driver.user.name">
                        </div>
                        
                        <!-- Header -->
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-bold text-lg text-gray-900" x-text="driver.user.name"></h3>
                                    <div class="flex items-center gap-1 mt-0.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="none" class="text-orange-500"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                        <span class="text-sm font-semibold text-gray-700" x-text="driver.rating"></span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-lg text-gray-900" x-text="`$${driver.hourly_rate}`"></div>
                                    <div class="text-xs font-medium text-gray-500">/hour</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bio -->
                    <div class="mb-6 mt-2">
                        <p class="text-gray-600 text-sm leading-relaxed line-clamp-3" x-text="driver.bio || 'Professional driver ready to provide a safe and comfortable ride.'"></p>
                    </div>

                    <!-- Actions -->
                    <div class="mt-auto pt-4 border-t border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full" :class="driver.is_available ? 'bg-green-500' : 'bg-red-500'"></div>
                            <span class="text-xs font-medium text-gray-500" x-text="driver.is_available ? 'Available' : 'Busy'"></span>
                        </div>
                        <button class="px-5 py-2 bg-orange-50 hover:bg-orange-100 text-orange-600 font-bold rounded-xl transition-colors text-sm">
                            View Profile
                        </button>
                    </div>
                </div>
            </template>
            
        </div>
    </main>

</body>
</html>
