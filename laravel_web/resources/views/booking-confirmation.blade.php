<x-layout>
    <x-slot:title>Booking #{{ $booking->booking_code }} — Confirmation</x-slot>

    <main class="flex-1 max-w-4xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <!-- Header -->
        <div class="text-center mb-10">
            <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white">Booking Confirmed!</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Booking Reference: <span class="font-bold text-gray-900 dark:text-white">#{{ $booking->booking_code }}</span></p>
        </div>

        <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 md:p-8 shadow-sm space-y-6 mb-8">
            
            <!-- Status Row -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-6 border-b border-gray-100 dark:border-white/10">
                <div>
                    <span class="text-xs text-gray-400 block uppercase font-bold tracking-wider mb-1">Booking Status</span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase
                        {{ $booking->booking_status === 'completed' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 
                          ($booking->booking_status === 'accepted' || $booking->booking_status === 'in_progress' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400') }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                        {{ $booking->booking_status }}
                    </span>
                </div>

                <div>
                    <span class="text-xs text-gray-400 block uppercase font-bold tracking-wider mb-1">Payment Status</span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase
                        {{ $booking->payment_status === 'paid' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' }}">
                        {{ $booking->payment_status }} ({{ strtoupper($booking->payment_method) }})
                    </span>
                </div>
            </div>

            <!-- Booking Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white mb-3 text-base">Service Details</h3>
                    <ul class="space-y-2 text-gray-600 dark:text-gray-400">
                        <li><strong class="text-gray-900 dark:text-white">Category:</strong> {{ ucfirst($booking->service_category) }} Driver Hiring</li>
                        <li><strong class="text-gray-900 dark:text-white">Country:</strong> {{ $booking->country }}</li>
                        <li><strong class="text-gray-900 dark:text-white">Driver:</strong> {{ $booking->driver->name ?? 'Assigned Driver' }}</li>
                        <li><strong class="text-gray-900 dark:text-white">Schedule:</strong> {{ $booking->start_date }} at {{ $booking->start_time }}</li>
                        <li><strong class="text-gray-900 dark:text-white">Duration:</strong> {{ $booking->duration_count }} {{ $booking->duration_type }}(s)</li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white mb-3 text-base">Location & Specs</h3>
                    <ul class="space-y-2 text-gray-600 dark:text-gray-400">
                        <li><strong class="text-gray-900 dark:text-white">Pickup:</strong> {{ $booking->pickup_location }}</li>
                        @if($booking->dropoff_location)
                            <li><strong class="text-gray-900 dark:text-white">Destination:</strong> {{ $booking->dropoff_location }}</li>
                        @endif
                        @if($booking->service_category === 'private')
                            <li><strong class="text-gray-900 dark:text-white">Car:</strong> {{ $booking->car_type }} ({{ $booking->transmission }})</li>
                            <li><strong class="text-gray-900 dark:text-white">Reg No:</strong> {{ $booking->registration_number }}</li>
                        @else
                            <li><strong class="text-gray-900 dark:text-white">Commercial Job:</strong> {{ $booking->commercial_service_type }}</li>
                            <li><strong class="text-gray-900 dark:text-white">Cargo Specs:</strong> {{ $booking->cargo_details ?? 'N/A' }}</li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Price Breakdown Summary -->
            <div class="bg-gray-50 dark:bg-[#1a1a1a] p-4 rounded-2xl border border-gray-100 dark:border-white/5 space-y-2 text-sm">
                <div class="flex justify-between text-gray-600 dark:text-gray-400">
                    <span>Subtotal</span>
                    <span>{{ $countryConfig['symbol'] }}{{ number_format($booking->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-gray-600 dark:text-gray-400">
                    <span>Service Fee & Tax</span>
                    <span>{{ $countryConfig['symbol'] }}{{ number_format($booking->service_fee + $booking->tax, 2) }}</span>
                </div>
                <div class="flex justify-between font-extrabold text-gray-900 dark:text-white pt-2 border-t border-gray-200 dark:border-white/10 text-base">
                    <span>Total Charged</span>
                    <span class="text-brand-500">{{ $countryConfig['symbol'] }}{{ number_format($booking->total_price, 2) }} {{ $booking->currency }}</span>
                </div>
            </div>

        </div>

        <!-- Rating & Review Form for Completed Bookings -->
        @if($booking->booking_status === 'completed')
            <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 md:p-8 shadow-sm">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Rate Your Driver Experience</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Leave an honest review for {{ $booking->driver->name ?? 'your driver' }}.</p>

                @if($booking->review)
                    <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-2xl border border-green-200 dark:border-green-800/30">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-bold text-green-700 dark:text-green-400 text-sm">Your Review:</span>
                            <div class="flex text-amber-500">
                                @for($i=1; $i<=5; $i++)
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="{{ $i <= $booking->review->rating ? 'currentColor' : 'none' }}" stroke="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                @endfor
                            </div>
                        </div>
                        <p class="text-sm text-green-800 dark:text-green-300">{{ $booking->review->review_text }}</p>
                    </div>
                @else
                    <form action="/driver-booking/{{ $booking->id }}/review" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Rating (1 to 5 Stars)</label>
                            <select name="rating" required class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white font-bold cursor-pointer">
                                <option value="5">⭐⭐⭐⭐⭐ 5 - Excellent</option>
                                <option value="4">⭐⭐⭐⭐ 4 - Very Good</option>
                                <option value="3">⭐⭐⭐ 3 - Average</option>
                                <option value="2">⭐⭐ 2 - Poor</option>
                                <option value="1">⭐ 1 - Very Bad</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Your Feedback</label>
                            <textarea name="review_text" required rows="3" placeholder="How was your trip with the driver?" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white text-sm resize-none"></textarea>
                        </div>

                        <button type="submit" class="px-6 py-3 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl transition-all shadow-md shadow-brand-500/25 text-sm">
                            Submit Review
                        </button>
                    </form>
                @endif
            </div>
        @endif

        <div class="mt-8 text-center">
            <a href="/hire-driver" class="text-brand-500 font-bold hover:underline text-sm">&larr; Back to Driver Hiring</a>
        </div>

    </main>
</x-layout>
