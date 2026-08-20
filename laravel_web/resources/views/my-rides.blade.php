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
            <div class="space-y-4">
                @foreach($rides as $ride)
                    <div class="bg-white dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-3 mb-3">
                                    <span class="text-xs font-extrabold uppercase px-2.5 py-1 rounded-lg
                                        @if($ride->status === 'completed') bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400
                                        @elseif($ride->status === 'failed' || $ride->status === 'cancelled') bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400
                                        @elseif(in_array($ride->status, ['accepted','en_route','arrived','in_progress'])) bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400
                                        @else bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400
                                        @endif">
                                        {{ strtoupper(str_replace('_', ' ', $ride->status)) }}
                                    </span>
                                    <span class="text-xs text-gray-400">{{ $ride->created_at->format('M d, Y · h:i A') }}</span>
                                </div>

                                <div class="relative pl-5 ml-2 border-l-2 border-gray-200 dark:border-gray-700 space-y-3 mb-3">
                                    <div class="relative">
                                        <div class="absolute -left-[21px] top-1 w-2.5 h-2.5 rounded-full bg-gray-900 dark:bg-white"></div>
                                        <p class="text-sm text-gray-900 dark:text-white font-semibold">{{ $ride->pickup_location }}</p>
                                    </div>
                                    <div class="relative">
                                        <div class="absolute -left-[21px] top-1 w-2.5 h-2.5 rounded-full border-2 border-gray-900 dark:border-white bg-white dark:bg-[#111]"></div>
                                        <p class="text-sm text-gray-900 dark:text-white font-semibold">{{ $ride->dropoff_location }}</p>
                                    </div>
                                </div>

                                @if($ride->driver)
                                    <p class="text-sm text-gray-500"><strong>Driver:</strong> {{ $ride->driver->name }}</p>
                                @endif
                            </div>

                            <div class="text-right shrink-0">
                                <p class="text-2xl font-extrabold text-gray-900 dark:text-white">${{ number_format($ride->fare, 2) }}</p>
                                <p class="text-xs text-gray-400 capitalize mt-0.5">{{ $ride->payment_method }}</p>
                            </div>
                        </div>

                        <!-- Reviews Section -->
                        @if($ride->status === 'completed')
                            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-white/10 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                {{-- Rider's review of driver --}}
                                @if($ride->riderReview)
                                    <div class="bg-indigo-50/50 dark:bg-indigo-900/10 border border-indigo-100 dark:border-indigo-800/30 rounded-xl p-3">
                                        <p class="text-xs font-bold text-indigo-700 dark:text-indigo-300 mb-1">Your Review</p>
                                        <div class="flex items-center gap-1 mb-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                <span class="{{ $i <= $ride->riderReview->rating ? 'text-yellow-400' : 'text-gray-300' }}">★</span>
                                            @endfor
                                            <span class="text-xs text-gray-500 ml-1">({{ $ride->riderReview->rating }}/5)</span>
                                        </div>
                                        @if($ride->riderReview->comment)
                                            <p class="text-xs text-gray-600 dark:text-gray-400 italic">"{{ $ride->riderReview->comment }}"</p>
                                        @endif
                                    </div>
                                @endif

                                {{-- Driver's review of rider --}}
                                @if($ride->driverReview)
                                    <div class="bg-emerald-50/50 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-800/30 rounded-xl p-3">
                                        <p class="text-xs font-bold text-emerald-700 dark:text-emerald-300 mb-1">Driver's Review</p>
                                        <div class="flex items-center gap-1 mb-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                <span class="{{ $i <= $ride->driverReview->rating ? 'text-yellow-400' : 'text-gray-300' }}">★</span>
                                            @endfor
                                            <span class="text-xs text-gray-500 ml-1">({{ $ride->driverReview->rating }}/5)</span>
                                        </div>
                                        @if($ride->driverReview->comment)
                                            <p class="text-xs text-gray-600 dark:text-gray-400 italic">"{{ $ride->driverReview->comment }}"</p>
                                        @endif
                                    </div>
                                @endif
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
