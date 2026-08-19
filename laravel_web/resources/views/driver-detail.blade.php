<x-layout>
    <x-slot:title>Driver {{ $driverProfile->user->name }} — RideMyCars</x-slot>

    <main class="flex-1 max-w-5xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <!-- Breadcrumb -->
        <nav class="mb-8">
            <ol class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                <li><a href="/hire-driver" class="hover:text-brand-500 transition-colors">Hire a Driver</a></li>
                <li><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg></li>
                <li class="font-medium text-gray-900 dark:text-white">{{ $driverProfile->user->name }}</li>
            </ol>
        </nav>

        <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 md:p-10 shadow-sm mb-8">
            <div class="flex flex-col md:flex-row gap-8 items-start">
                
                <!-- Profile Image -->
                <div class="w-32 h-32 md:w-48 md:h-48 rounded-full overflow-hidden bg-gray-100 dark:bg-[#222] shrink-0 relative border-4 border-white dark:border-[#111] shadow-lg">
                    <img src="{{ $driverProfile->photo_url }}" alt="{{ $driverProfile->user->name }}" class="w-full h-full object-cover">
                </div>

                <!-- Profile Info -->
                <div class="flex-1 w-full">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
                        <div>
                            <div class="flex items-center gap-3">
                                <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white">{{ $driverProfile->user->name }}</h1>
                                @if($driverProfile->verification_status === 'verified')
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400 border border-green-200 dark:border-green-800/30">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                        Verified License
                                    </span>
                                @endif
                            </div>
                            
                            <div class="flex items-center gap-4 mt-2">
                                <div class="flex items-center gap-1.5 bg-amber-50 dark:bg-amber-900/20 px-2.5 py-1 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-500" fill="currentColor" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    <span class="text-sm font-bold text-amber-700 dark:text-amber-400">{{ $driverProfile->rating }} Rating</span>
                                </div>
                                <span class="text-sm text-gray-500 dark:text-gray-400 font-medium">({{ $driverProfile->total_trips ?? 0 }} completed trips)</span>
                                <div class="flex items-center gap-2">
                                    <div class="w-2.5 h-2.5 rounded-full {{ $driverProfile->is_available ? 'bg-green-500' : 'bg-red-500' }}"></div>
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ $driverProfile->is_available ? 'Available Now' : 'Currently Busy' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Pricing Card -->
                        <div class="text-left md:text-right w-full md:w-auto bg-gray-50 dark:bg-[#1a1a1a] p-4 rounded-2xl border border-gray-100 dark:border-white/5">
                            <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider font-semibold mb-1">Service Rates</div>
                            <div class="text-2xl font-extrabold text-gray-900 dark:text-white">
                                {{ $countryConfig['symbol'] }}{{ number_format($driverProfile->hourly_rate ?? 25.00, 2) }}<span class="text-xs text-gray-500 dark:text-gray-400 font-normal">/hr</span>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ $countryConfig['symbol'] }}{{ number_format($driverProfile->daily_rate ?? (($driverProfile->hourly_rate ?? 25) * 8 * 0.85), 2) }}/day
                            </div>
                        </div>
                    </div>

                    <!-- Meta Badges -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 my-6 pt-4 border-t border-gray-100 dark:border-white/10">
                        <div class="bg-gray-50 dark:bg-[#1a1a1a] p-3 rounded-xl">
                            <span class="text-xs text-gray-400 block">Experience</span>
                            <span class="font-bold text-gray-900 dark:text-white text-sm">{{ $driverProfile->experience_years ?? 3 }}+ Years</span>
                        </div>
                        <div class="bg-gray-50 dark:bg-[#1a1a1a] p-3 rounded-xl">
                            <span class="text-xs text-gray-400 block">Country</span>
                            <span class="font-bold text-gray-900 dark:text-white text-sm">{{ $driverProfile->country ?? 'USA' }}</span>
                        </div>
                        <div class="bg-gray-50 dark:bg-[#1a1a1a] p-3 rounded-xl">
                            <span class="text-xs text-gray-400 block">License</span>
                            <span class="font-bold text-gray-900 dark:text-white text-sm">{{ $driverProfile->masked_license }}</span>
                        </div>
                        <div class="bg-gray-50 dark:bg-[#1a1a1a] p-3 rounded-xl">
                            <span class="text-xs text-gray-400 block">Service Area</span>
                            <span class="font-bold text-gray-900 dark:text-white text-sm">{{ $driverProfile->service_area ?? 'All Cities' }}</span>
                        </div>
                    </div>

                    <div class="pt-4">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3">About the Driver</h2>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed text-base">
                            {{ $driverProfile->bio ?: 'Experienced professional driver committed to safety, punctuality, and providing exceptional customer service for private and commercial driving jobs.' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Client Reviews Section -->
        <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 md:p-10 shadow-sm mb-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Client Reviews ({{ $driverProfile->reviews->count() }})</h2>
            
            @if($driverProfile->reviews->isEmpty())
                <p class="text-gray-500 dark:text-gray-400 text-sm italic">No reviews yet. Be the first client to book and review {{ $driverProfile->user->name }}!</p>
            @else
                <div class="space-y-4">
                    @foreach($driverProfile->reviews as $rev)
                        <div class="p-4 bg-gray-50 dark:bg-[#1a1a1a] rounded-2xl border border-gray-100 dark:border-white/5">
                            <div class="flex justify-between items-center mb-2">
                                <div class="font-bold text-gray-900 dark:text-white text-sm">{{ $rev->client->name ?? 'Verified Client' }}</div>
                                <div class="flex items-center gap-1 text-amber-500">
                                    @for($i=1; $i<=5; $i++)
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="{{ $i <= $rev->rating ? 'currentColor' : 'none' }}" stroke="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    @endfor
                                </div>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed">{{ $rev->review_text }}</p>
                            <span class="text-xs text-gray-400 mt-2 block">{{ $rev->created_at->diffForHumans() }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        
        <!-- Booking CTA -->
        <div class="text-center max-w-md mx-auto">
            <a href="/hire-driver/book/{{ $driverProfile->id }}?country={{ $driverProfile->country ?? 'USA' }}" class="w-full flex items-center justify-center gap-2 py-4 px-6 bg-brand-500 hover:bg-brand-600 text-white rounded-xl font-bold text-lg transition-all shadow-md shadow-brand-500/25">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                Book This Driver Now
            </a>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-4">Safe & secure checkout with country-specific payment options.</p>
        </div>

    </main>
</x-layout>
