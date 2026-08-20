<x-layout>
    <x-slot:title>Activity — RideMyCars</x-slot>

    <div class="max-w-[1200px] mx-auto w-full px-4 sm:px-6 lg:px-8 py-10 flex flex-col md:flex-row gap-8">
        
        <!-- Sidebar -->
        <aside class="hidden md:block w-48 shrink-0 mt-2">
            <nav class="space-y-1">
                <a href="#" class="block px-3 py-2 text-[15px] font-medium text-gray-700 dark:text-gray-300 hover:text-black dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">
                    Tax profile
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 min-w-0">
            
                <!-- Upcoming Section -->
            <div class="mb-14">
                <h1 class="text-[32px] font-bold text-black dark:text-white mb-6">Upcoming</h1>
                
                @forelse($upcomingRides as $ride)
                <div class="rounded-2xl overflow-hidden border border-gray-200 dark:border-white/10 bg-[#f8f8f8] dark:bg-[#1a1a1a] mb-4 p-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-black dark:text-white mb-1">{{ $ride->dropoff_location }}</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400">From: {{ $ride->pickup_location }}</p>
                        <div class="text-xs font-bold mt-2 text-blue-600 dark:text-blue-400 uppercase tracking-wide px-2 py-1 bg-blue-50 dark:bg-blue-900/30 inline-block rounded">{{ $ride->status }}</div>
                    </div>
                    <div class="text-right">
                        <span class="text-4xl">
                            @if(stripos($ride->vehicle_type, 'auto') !== false || stripos($ride->vehicle_type, 'rickshaw') !== false)
                                🛺
                            @elseif(stripos($ride->vehicle_type, 'bike') !== false || stripos($ride->vehicle_type, 'moto') !== false)
                                🛵
                            @else
                                🚗
                            @endif
                        </span>
                    </div>
                </div>
                @empty
                <!-- Upcoming Card -->
                <div class="rounded-2xl overflow-hidden border border-gray-200 dark:border-white/10 bg-[#f8f8f8] dark:bg-[#1a1a1a]">
                    <!-- Banner Graphic (Placeholder) -->
                    <div class="h-32 bg-gradient-to-r from-green-700 via-green-600 to-emerald-800 relative flex items-end">
                        <div class="absolute inset-0 opacity-20 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
                        <!-- Abstract road/car shapes -->
                        <div class="absolute bottom-0 left-[40%] w-24 h-16 bg-white/20 skew-x-[30deg]"></div>
                        <div class="absolute bottom-4 right-8 w-32 h-12 bg-white/90 rounded-t-lg">
                            <div class="absolute bottom-0 -left-2 w-4 h-4 bg-black rounded-full"></div>
                            <div class="absolute bottom-0 -right-2 w-4 h-4 bg-black rounded-full"></div>
                        </div>
                    </div>
                    
                    <!-- Card Content -->
                    <div class="p-5">
                        <h2 class="text-lg font-bold text-black dark:text-white mb-4">You have no upcoming trips</h2>
                        <a href="/ride" class="inline-flex items-center gap-2 bg-[#e8e8e8] hover:bg-[#d8d8d8] dark:bg-[#333] dark:hover:bg-[#444] text-black dark:text-white px-4 py-2 rounded-full font-bold text-sm transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18 4H6c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zM8 11H6V9h2v2zm0-4H6V5h2v2zm4 4h-2V9h2v2zm0-4h-2V5h2v2zm4 4h-2V9h2v2zm0-4h-2V5h2v2zm2 10H6v-2h12v2z"/></svg>
                            Reserve trip
                        </a>
                    </div>
                </div>
                @endforelse
            </div>

            <!-- Past Section -->
            <div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <h1 class="text-[32px] font-bold text-black dark:text-white">Past</h1>
                    
                    <div class="flex items-center gap-2">
                        <!-- Profile Dropdown -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" @click.away="open = false" class="flex items-center gap-2 bg-[#f0f0f0] hover:bg-[#e4e4e4] dark:bg-[#333] dark:hover:bg-[#444] text-black dark:text-white px-4 py-2 rounded-full font-bold text-[15px] transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a5 5 0 1 0 5 5 5 5 0 0 0-5-5Zm0 8a3 3 0 1 1 3-3 3 3 0 0 1-3 3Zm9 11v-1a7 7 0 0 0-7-7h-4a7 7 0 0 0-7 7v1h2v-1a5 5 0 0 1 5-5h4a5 5 0 0 1 5 5v1h2Z"/></svg>
                                Personal
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" :class="{'rotate-180': open}" class="transition-transform duration-200"><path d="m6 9 6 6 6-6"/></svg>
                            </button>
                            
                            <div x-show="open" x-transition.opacity.duration.200ms class="absolute right-0 top-full mt-2 w-56 bg-white dark:bg-[#1a1a1a] border border-gray-100 dark:border-white/10 rounded-2xl shadow-xl py-2 z-50" style="display: none;">
                                <a href="#" class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-white/5 text-[17px] text-gray-900 dark:text-gray-100 transition-colors">Personal</a>
                                <a href="#" class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-white/5 text-[17px] font-bold text-black dark:text-white bg-gray-50 dark:bg-white/5 transition-colors">Business</a>
                                <a href="#" class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-white/5 text-[17px] text-gray-900 dark:text-gray-100 transition-colors">Family</a>
                                <a href="#" class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-white/5 text-[17px] text-gray-900 dark:text-gray-100 transition-colors">Delegate</a>
                            </div>
                        </div>

                        <!-- Time Filter Dropdown -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" @click.away="open = false" class="flex items-center gap-2 bg-[#f0f0f0] hover:bg-[#e4e4e4] dark:bg-[#333] dark:hover:bg-[#444] text-black dark:text-white px-4 py-2 rounded-full font-bold text-[15px] transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                All trips
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" :class="{'rotate-180': open}" class="transition-transform duration-200"><path d="m6 9 6 6 6-6"/></svg>
                            </button>
                            
                            <div x-show="open" x-transition.opacity.duration.200ms class="absolute right-0 top-full mt-2 w-48 bg-white dark:bg-[#1a1a1a] border border-gray-100 dark:border-white/10 rounded-2xl shadow-xl py-2 z-50" style="display: none;">
                                <a href="#" class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-white/5 text-[17px] font-bold text-black dark:text-white bg-gray-50 dark:bg-white/5 transition-colors">All trips</a>
                                <a href="#" class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-white/5 text-[17px] text-gray-900 dark:text-gray-100 transition-colors">Past 30 days</a>
                                <a href="#" class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-white/5 text-[17px] text-gray-900 dark:text-gray-100 transition-colors">Past 6 months</a>
                                <a href="#" class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-white/5 text-[17px] text-gray-900 dark:text-gray-100 transition-colors">2023</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Trips Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    
                    @forelse($pastRides as $index => $ride)
                        @if($index === 0)
                        <!-- Map Card for most recent past trip -->
                        <div class="border border-gray-200 dark:border-white/10 rounded-2xl flex flex-col sm:flex-row overflow-hidden hover:shadow-md transition-shadow cursor-pointer bg-white dark:bg-[#111]">
                            <div class="w-full sm:w-[45%] h-40 sm:h-auto bg-[#e8eaed] dark:bg-[#222] relative flex-shrink-0">
                                <!-- Fake Map Graphic -->
                                <div class="absolute inset-0 opacity-50 bg-[url('https://www.transparenttextures.com/patterns/cartographer.png')]"></div>
                                <svg class="absolute inset-0 w-full h-full" preserveAspectRatio="none" viewBox="0 0 100 100">
                                    <path d="M 20 80 Q 40 60 50 30 T 80 20" fill="none" stroke="black" stroke-width="2" class="dark:stroke-white"/>
                                    <circle cx="20" cy="80" r="3" fill="black" class="dark:fill-white"/>
                                    <rect x="77" y="17" width="6" height="6" fill="black" class="dark:fill-white"/>
                                </svg>
                            </div>
                            <div class="p-4 flex flex-col justify-center flex-1 min-w-0">
                                <h3 class="font-bold text-[17px] text-black dark:text-white mb-1 leading-tight truncate">{{ $ride->dropoff_location }}</h3>
                                <div class="text-[13px] text-gray-600 dark:text-gray-400 mb-1">{{ $ride->created_at->format('d M • H:i') }}</div>
                                <div class="text-[15px] font-medium text-black dark:text-white mb-4">
                                    ₹{{ number_format($ride->fare ?? 0, 2) }}
                                    @if($ride->status === 'cancelled') <span class="text-red-500 text-sm ml-1">• Cancelled</span> @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    <button class="inline-flex items-center gap-1.5 bg-[#f0f0f0] hover:bg-[#e4e4e4] dark:bg-[#333] dark:hover:bg-[#444] px-3 py-1.5 rounded-full font-bold text-xs text-black dark:text-white transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                                        Help
                                    </button>
                                    <button class="inline-flex items-center gap-1.5 bg-[#f0f0f0] hover:bg-[#e4e4e4] dark:bg-[#333] dark:hover:bg-[#444] px-3 py-1.5 rounded-full font-bold text-xs text-black dark:text-white transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                                        Details
                                    </button>
                                </div>
                            </div>
                        </div>
                        @else
                        <!-- Standard Trip Card -->
                        <div class="border border-gray-200 dark:border-white/10 rounded-2xl flex p-4 hover:shadow-md transition-shadow cursor-pointer bg-white dark:bg-[#111] items-center gap-4">
                            <div class="w-[72px] h-[72px] bg-[#f8f8f8] dark:bg-[#222] rounded-xl flex items-center justify-center shrink-0 relative">
                                <span class="text-3xl">
                                    @if(stripos($ride->vehicle_type, 'auto') !== false || stripos($ride->vehicle_type, 'rickshaw') !== false)
                                        🛺
                                    @elseif(stripos($ride->vehicle_type, 'bike') !== false || stripos($ride->vehicle_type, 'moto') !== false)
                                        🛵
                                    @else
                                        🚗
                                    @endif
                                </span>
                            </div>
                            <div class="flex flex-col justify-center flex-1 min-w-0">
                                <h3 class="font-bold text-[17px] text-black dark:text-white mb-0.5 leading-tight truncate">{{ $ride->dropoff_location }}</h3>
                                <div class="text-[13px] text-gray-600 dark:text-gray-400 mb-0.5">{{ $ride->created_at->format('d M • H:i') }}</div>
                                <div class="text-[15px] font-medium text-black dark:text-white mb-3">
                                    ₹{{ number_format($ride->fare ?? 0, 2) }}
                                    @if($ride->status === 'cancelled') <span class="text-red-500 text-sm ml-1">• Cancelled</span> @endif
                                </div>
                                <div>
                                    <button class="inline-flex items-center gap-1.5 bg-[#f0f0f0] hover:bg-[#e4e4e4] dark:bg-[#333] dark:hover:bg-[#444] px-3 py-1.5 rounded-full font-bold text-xs text-black dark:text-white transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                                        Help
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif
                    @empty
                        <div class="col-span-full py-10 text-center text-gray-500 font-medium">
                            You have no past trips.
                        </div>
                    @endforelse

                </div>
                
                <div class="mt-8 flex justify-end">
                    <button class="bg-[#f0f0f0] hover:bg-[#e4e4e4] dark:bg-[#333] dark:hover:bg-[#444] text-black dark:text-white font-bold text-[15px] py-2.5 px-5 rounded-full transition-colors">
                        More
                    </button>
                </div>
            </div>

        </main>
    </div>
</x-layout>
