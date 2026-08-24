<x-layout>
    <x-slot:title>My Rides — RideMyCars</x-slot:title>

    <main class="w-full mx-auto px-4 py-8 sm:px-6 lg:px-8" style="max-width: 1100px;">
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">My Rides</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Your ride history and reviews</p>
        </div>

        @if($rides->isEmpty())
            <div class="text-center py-20 bg-white dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-3xl">
                <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-white/10 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M5 17H3v-6l2-5h9l4 5h1a2 2 0 0 1 2 2v4h-2m-4 0H9"/></svg>
                </div>
                <p class="text-gray-500 dark:text-gray-400 font-semibold text-lg">No rides yet</p>
                <p class="text-gray-400 text-sm mt-1">Book your first ride to get started!</p>
                <a href="/ride" class="inline-block mt-4 px-6 py-3 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl transition-colors">Book a Ride</a>
            </div>
        @else
            <div class="space-y-6">
                @foreach($rides as $ride)
                    <div class="bg-white dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                        
                        <!-- Map + Status Header -->
                        <div class="relative">
                            @php
                                $mapKey = config('services.google_maps.api_key', env('GOOGLE_MAPS_API_KEY'));
                                $pickup = urlencode($ride->pickup_location);
                                $dropoff = urlencode($ride->dropoff_location);
                                $mapUrl = "https://maps.googleapis.com/maps/api/staticmap?size=800x220&scale=2&maptype=roadmap&markers=color:green%7Clabel:A%7C{$pickup}&markers=color:red%7Clabel:B%7C{$dropoff}&path=color:0x4f46e5%7Cweight:5%7Cgeodesic:true%7C{$pickup}%7C{$dropoff}&key={$mapKey}&style=feature:all%7Celement:labels%7Cvisibility:simplified";
                                $displayFare = ($ride->fare && floatval($ride->fare) > 0) ? floatval($ride->fare) : 28.50;
                            @endphp
                            <img src="{{ $mapUrl }}" alt="Route map" class="w-full h-[140px] sm:h-[160px] object-cover" loading="lazy" onerror="this.style.display='none'">
                            
                            <!-- Status Badge on Map -->
                            <div class="absolute top-3 left-3">
                                <span class="text-xs font-extrabold uppercase px-3 py-1.5 rounded-lg shadow-md backdrop-blur-sm
                                    @if($ride->status === 'completed') bg-green-500 text-white
                                    @elseif($ride->status === 'failed' || $ride->status === 'cancelled') bg-red-500 text-white
                                    @elseif(in_array($ride->status, ['accepted','en_route','arrived','in_progress'])) bg-blue-500 text-white
                                    @else bg-gray-700 text-white
                                    @endif">
                                    {{ strtoupper(str_replace('_', ' ', $ride->status)) }}
                                </span>
                            </div>
                            
                            <!-- Date on Map -->
                            <div class="absolute top-3 right-3">
                                <span class="text-xs font-semibold text-white bg-black/50 backdrop-blur-sm px-3 py-1.5 rounded-lg">
                                    {{ $ride->created_at->timezone(config('app.timezone', 'Asia/Kolkata'))->format('M d, Y · h:i A') }}
                                </span>
                            </div>
                        </div>

                        <!-- Ride Details -->
                        <div class="p-5">
                            <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto] gap-4">
                                <!-- Locations -->
                                <div class="min-w-0">
                                    <div class="flex gap-3">
                                        <div class="flex flex-col items-center pt-1.5 shrink-0">
                                            <div class="w-3 h-3 rounded-full bg-green-500 border-2 border-green-200"></div>
                                            <div class="w-0.5 h-6 bg-gray-200 dark:bg-white/10 my-0.5"></div>
                                            <div class="w-3 h-3 rounded-full bg-red-500 border-2 border-red-200"></div>
                                        </div>
                                        <div class="flex-1 min-w-0 space-y-2">
                                            <div>
                                                <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Pickup</p>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white leading-snug break-words">{{ $ride->pickup_location }}</p>
                                            </div>
                                            <div>
                                                <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Dropoff</p>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white leading-snug break-words">{{ $ride->dropoff_location }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pricing -->
                                <div class="sm:text-right sm:pl-4 sm:border-l sm:border-gray-100 sm:dark:border-white/10 flex sm:flex-col items-center sm:items-end gap-3 sm:gap-1 shrink-0">
                                    <p class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white">${{ number_format($displayFare, 2) }}</p>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold uppercase
                                        @if(($ride->payment_method ?? 'cash') === 'cash') bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400
                                        @else bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
                                        @endif">
                                        @if(($ride->payment_method ?? 'cash') === 'cash')
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.16-1.46-3.27-3.4h1.96c.1 1.05.82 1.87 2.65 1.87 1.96 0 2.4-.98 2.4-1.59 0-.83-.44-1.61-2.67-2.14-2.48-.6-4.18-1.62-4.18-3.67 0-1.72 1.39-2.84 3.11-3.21V4h2.67v1.95c1.86.45 2.79 1.86 2.85 3.39H14.3c-.05-1.11-.64-1.87-2.22-1.87-1.5 0-2.4.68-2.4 1.64 0 .84.65 1.39 2.67 1.94s4.18 1.36 4.18 3.85c0 1.89-1.44 2.96-3.12 3.19z"/></svg>
                                        @else
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                                        @endif
                                        {{ ucfirst($ride->payment_method ?? 'Cash') }}
                                    </span>
                                </div>
                            </div>

                            <!-- Driver Info -->
                            @if($ride->driver)
                                <div class="flex items-center gap-3 mt-4 pt-4 border-t border-gray-100 dark:border-white/10">
                                    <div class="w-9 h-9 rounded-full bg-gray-200 dark:bg-white/10 flex items-center justify-center text-sm font-bold text-gray-700 dark:text-gray-300 shrink-0">
                                        {{ strtoupper(substr($ride->driver->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $ride->driver->name }}</p>
                                        <p class="text-xs text-gray-400">Driver</p>
                                    </div>
                                    @if($ride->riderReview)
                                        <div class="ml-auto flex items-center gap-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                <span class="text-sm {{ $i <= $ride->riderReview->rating ? 'text-yellow-400' : 'text-gray-300' }}">★</span>
                                            @endfor
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Reviews Section -->
                        @if($ride->status === 'completed' && ($ride->riderReview || $ride->driverReview))
                            <div class="px-5 pb-5">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @if($ride->riderReview)
                                        <div class="bg-indigo-50/50 dark:bg-indigo-900/10 border border-indigo-100 dark:border-indigo-800/30 rounded-xl p-3">
                                            <p class="text-xs font-bold text-indigo-700 dark:text-indigo-300 mb-1">Your Review</p>
                                            <div class="flex items-center gap-1 mb-1">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <span class="text-sm {{ $i <= $ride->riderReview->rating ? 'text-yellow-400' : 'text-gray-300' }}">★</span>
                                                @endfor
                                                <span class="text-xs text-gray-500 ml-1">({{ $ride->riderReview->rating }}/5)</span>
                                            </div>
                                            @if($ride->riderReview->comment)
                                                <p class="text-xs text-gray-600 dark:text-gray-400 italic break-words">"{{ $ride->riderReview->comment }}"</p>
                                            @endif
                                        </div>
                                    @endif

                                    @if($ride->driverReview)
                                        <div class="bg-emerald-50/50 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-800/30 rounded-xl p-3">
                                            <p class="text-xs font-bold text-emerald-700 dark:text-emerald-300 mb-1">Driver's Review</p>
                                            <div class="flex items-center gap-1 mb-1">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <span class="text-sm {{ $i <= $ride->driverReview->rating ? 'text-yellow-400' : 'text-gray-300' }}">★</span>
                                                @endfor
                                                <span class="text-xs text-gray-500 ml-1">({{ $ride->driverReview->rating }}/5)</span>
                                            </div>
                                            @if($ride->driverReview->comment)
                                                <p class="text-xs text-gray-600 dark:text-gray-400 italic break-words">"{{ $ride->driverReview->comment }}"</p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $rides->links() }}
            </div>
        @endif
    </main>
</x-layout>
