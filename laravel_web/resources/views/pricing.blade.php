<x-layout>
    <x-slot:title>Pricing — RideMyCars</x-slot>

    <main class="flex-1 pb-24">
        
        <!-- Header -->
        <div class="max-w-4xl mx-auto text-center px-4 pt-20 pb-16">
            <h3 class="text-orange-500 font-bold text-sm tracking-widest uppercase mb-4">Pricing</h3>
            <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 dark:text-white mb-4 tracking-tight">Simple, transparent pricing</h1>
            <p class="text-lg text-gray-500 dark:text-gray-400">No surge surprises, no hidden fees. What you see is what you pay.</p>
        </div>

        <!-- Section 1: Ride Hailing -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-24">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">Ride Hailing</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start">
                
                <!-- Economy -->
                <div class="bg-white dark:bg-[#111] rounded-3xl p-8 border border-gray-100 dark:border-white/10 shadow-sm hover:shadow-md transition-shadow">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1">Economy</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-8">Affordable daily rides</p>
                    
                    <div class="space-y-4 mb-8">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Base fare</span>
                            <span class="font-bold text-gray-900 dark:text-white">$2.50</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Per km</span>
                            <span class="font-bold text-gray-900 dark:text-white">$0.90</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Per minute</span>
                            <span class="font-bold text-gray-900 dark:text-white">$0.15</span>
                        </div>
                        <div class="pt-4 mt-4 border-t border-gray-100 dark:border-white/10 flex justify-between items-center text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Minimum fare</span>
                            <span class="font-bold text-orange-500">$4.00</span>
                        </div>
                    </div>
                    
                    <button class="w-full py-3 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl transition-colors">Book Economy</button>
                </div>

                <!-- Comfort (Popular) -->
                <div class="bg-white dark:bg-[#111] rounded-3xl p-8 border-2 border-orange-500 shadow-lg shadow-orange-50 relative transform md:-translate-y-4">
                    <div class="absolute -top-3 inset-x-0 flex justify-center">
                        <span class="bg-orange-500 text-white text-xs font-bold px-3 py-1 rounded-full">Most Popular</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1">Comfort</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-8">Spacious, newer vehicles</p>
                    
                    <div class="space-y-4 mb-8">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Base fare</span>
                            <span class="font-bold text-gray-900 dark:text-white">$3.00</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Per km</span>
                            <span class="font-bold text-gray-900 dark:text-white">$1.20</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Per minute</span>
                            <span class="font-bold text-gray-900 dark:text-white">$0.18</span>
                        </div>
                        <div class="pt-4 mt-4 border-t border-gray-100 dark:border-white/10 flex justify-between items-center text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Minimum fare</span>
                            <span class="font-bold text-orange-500">$5.00</span>
                        </div>
                    </div>
                    
                    <button class="w-full py-3 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl transition-colors shadow-md shadow-orange-500/25">Book Comfort</button>
                </div>

                <!-- Premium -->
                <div class="bg-white dark:bg-[#111] rounded-3xl p-8 border border-gray-100 dark:border-white/10 shadow-sm hover:shadow-md transition-shadow">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1">Premium</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-8">Luxury vehicles & top-rated drivers</p>
                    
                    <div class="space-y-4 mb-8">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Base fare</span>
                            <span class="font-bold text-gray-900 dark:text-white">$4.50</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Per km</span>
                            <span class="font-bold text-gray-900 dark:text-white">$1.80</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Per minute</span>
                            <span class="font-bold text-gray-900 dark:text-white">$0.25</span>
                        </div>
                        <div class="pt-4 mt-4 border-t border-gray-100 dark:border-white/10 flex justify-between items-center text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Minimum fare</span>
                            <span class="font-bold text-orange-500">$8.00</span>
                        </div>
                    </div>
                    
                    <button class="w-full py-3 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl transition-colors">Book Premium</button>
                </div>

            </div>

            <p class="mt-6 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                A 15% platform fee is included in all displayed fares. Prices may vary by city.
            </p>
        </section>


        <!-- Section 2: Vehicle Rentals -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-24">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">Vehicle Rentals — From (per day)</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- Economy Rental -->
                <div class="bg-white dark:bg-[#111] rounded-3xl p-8 border border-gray-100 dark:border-white/10 shadow-sm hover:shadow-md transition-shadow">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1">Economy</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Toyota Corolla, Honda Civic</p>
                    
                    <div class="mb-8">
                        <span class="text-4xl font-extrabold text-orange-500">$35</span><span class="text-gray-500 dark:text-gray-400 font-medium">/day</span>
                    </div>

                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><polyline points="20 6 9 17 4 12"/></svg>
                            Unlimited mileage
                        </li>
                        <li class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><polyline points="20 6 9 17 4 12"/></svg>
                            Basic insurance
                        </li>
                        <li class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><polyline points="20 6 9 17 4 12"/></svg>
                            Free cancellation 24h
                        </li>
                    </ul>

                    <button class="w-full py-3 bg-white dark:bg-[#111] border border-orange-200 text-orange-500 hover:bg-orange-50 font-bold rounded-xl transition-colors">Browse Economy</button>
                </div>

                <!-- SUV Rental -->
                <div class="bg-white dark:bg-[#111] rounded-3xl p-8 border border-orange-100 shadow-lg shadow-orange-50">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1">SUV / Midsize</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Toyota RAV4, Honda CR-V</p>
                    
                    <div class="mb-8">
                        <span class="text-4xl font-extrabold text-orange-500">$65</span><span class="text-gray-500 dark:text-gray-400 font-medium">/day</span>
                    </div>

                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><polyline points="20 6 9 17 4 12"/></svg>
                            Unlimited mileage
                        </li>
                        <li class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><polyline points="20 6 9 17 4 12"/></svg>
                            Full insurance
                        </li>
                        <li class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><polyline points="20 6 9 17 4 12"/></svg>
                            Free cancellation 24h
                        </li>
                        <li class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><polyline points="20 6 9 17 4 12"/></svg>
                            GPS included
                        </li>
                    </ul>

                    <button class="w-full py-3 bg-white dark:bg-[#111] border border-orange-200 text-orange-500 hover:bg-orange-50 font-bold rounded-xl transition-colors">Browse SUV / Midsize</button>
                </div>

                <!-- Luxury Rental -->
                <div class="bg-white dark:bg-[#111] rounded-3xl p-8 border border-gray-100 dark:border-white/10 shadow-sm hover:shadow-md transition-shadow">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1">Luxury</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">BMW 5 Series, Mercedes E-Class</p>
                    
                    <div class="mb-8">
                        <span class="text-4xl font-extrabold text-orange-500">$120</span><span class="text-gray-500 dark:text-gray-400 font-medium">/day</span>
                    </div>

                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><polyline points="20 6 9 17 4 12"/></svg>
                            Unlimited mileage
                        </li>
                        <li class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><polyline points="20 6 9 17 4 12"/></svg>
                            Premium insurance
                        </li>
                        <li class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><polyline points="20 6 9 17 4 12"/></svg>
                            Priority support
                        </li>
                        <li class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><polyline points="20 6 9 17 4 12"/></svg>
                            Doorstep delivery
                        </li>
                        <li class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><polyline points="20 6 9 17 4 12"/></svg>
                            GPS included
                        </li>
                    </ul>

                    <button class="w-full py-3 bg-white dark:bg-[#111] border border-orange-200 text-orange-500 hover:bg-orange-50 font-bold rounded-xl transition-colors">Browse Luxury</button>
                </div>
            </div>
        </section>


        <!-- Section 3: Driver Hire -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Professional Driver Hire</h2>
            <p class="text-gray-500 dark:text-gray-400 mb-8 max-w-2xl">Driver rates are set by each professional and vary based on experience, languages, and availability. Typical ranges:</p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white dark:bg-[#111] rounded-3xl p-8 text-center border border-gray-100 dark:border-white/10 shadow-sm">
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Half Day (4h)</h4>
                    <span class="text-3xl font-extrabold text-orange-500">$60 - $120</span>
                </div>
                <div class="bg-white dark:bg-[#111] rounded-3xl p-8 text-center border border-gray-100 dark:border-white/10 shadow-sm">
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Full Day (8h)</h4>
                    <span class="text-3xl font-extrabold text-orange-500">$100 - $200</span>
                </div>
                <div class="bg-white dark:bg-[#111] rounded-3xl p-8 text-center border border-gray-100 dark:border-white/10 shadow-sm">
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Weekly</h4>
                    <span class="text-3xl font-extrabold text-orange-500">$500 - $1,000</span>
                </div>
            </div>

            <button class="px-8 py-3 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl transition-colors shadow-md shadow-orange-500/25">Browse Drivers & Compare</button>

        </section>

    </main>

</x-layout>