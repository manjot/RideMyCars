<x-layout>
    <x-slot:title>{{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }} — Rent | RideMyCars</x-slot>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <!-- Breadcrumb -->
        <nav class="mb-8">
            <ol class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                <li><a href="/rent" class="hover:text-orange-500 transition-colors">Rent</a></li>
                <li><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg></li>
                <li class="font-medium text-gray-900 dark:text-white">{{ $vehicle->make }} {{ $vehicle->model }}</li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <!-- Left Column: Images & Details -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Main Image -->
                <div class="bg-gray-100 dark:bg-[#111] rounded-3xl overflow-hidden aspect-video relative flex items-center justify-center border border-gray-200 dark:border-white/10">
                    @if($vehicle->image_url)
                        <img src="{{ Storage::url($vehicle->image_url) }}" alt="{{ $vehicle->make }} {{ $vehicle->model }}" class="w-full h-full object-cover">
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>
                    @endif
                    <div class="absolute top-4 left-4 bg-white/90 dark:bg-black/80 backdrop-blur-sm px-4 py-1.5 rounded-full text-sm font-bold text-gray-900 dark:text-white shadow-sm">
                        {{ $vehicle->type }}
                    </div>
                </div>

                <!-- Features -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Features & Specifications</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-gray-50 dark:bg-[#111] p-4 rounded-2xl border border-gray-100 dark:border-white/5 flex flex-col items-center justify-center text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $vehicle->year }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Year</span>
                        </div>
                        <div class="bg-gray-50 dark:bg-[#111] p-4 rounded-2xl border border-gray-100 dark:border-white/5 flex flex-col items-center justify-center text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">5 Seats</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Capacity</span>
                        </div>
                        <div class="bg-gray-50 dark:bg-[#111] p-4 rounded-2xl border border-gray-100 dark:border-white/5 flex flex-col items-center justify-center text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">Auto</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Transmission</span>
                        </div>
                        <div class="bg-gray-50 dark:bg-[#111] p-4 rounded-2xl border border-gray-100 dark:border-white/5 flex flex-col items-center justify-center text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">AC</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Climate</span>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Description</h2>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed text-lg">
                        The {{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }} is a premium {{ strtolower($vehicle->type) }} that offers a perfect blend of comfort, style, and efficiency. Whether you're navigating city streets or heading out on a road trip, this vehicle provides a smooth and reliable experience. It is fully inspected and maintained to the highest standards.
                    </p>
                </div>
            </div>

            <!-- Right Column: Pricing & Booking -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 shadow-sm sticky top-24">
                    <div class="mb-6">
                        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $vehicle->make }}</h1>
                        <p class="text-xl text-gray-500 dark:text-gray-400 font-medium">{{ $vehicle->model }}</p>
                    </div>

                    <div class="flex items-end gap-2 mb-8 pb-8 border-b border-gray-100 dark:border-white/10">
                        <span class="text-4xl font-extrabold text-gray-900 dark:text-white">${{ $vehicle->daily_rate }}</span>
                        <span class="text-lg text-gray-500 dark:text-gray-400 font-medium pb-1">/ day</span>
                    </div>

                    <div class="space-y-4 mb-8">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Free cancellation up to 24h</span>
                            <span class="text-green-500 font-semibold flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Included</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Basic insurance</span>
                            <span class="text-green-500 font-semibold flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Included</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Unlimited mileage</span>
                            <span class="text-green-500 font-semibold flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Included</span>
                        </div>
                    </div>

                    <a href="/ride" class="w-full block text-center py-4 px-6 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-bold text-lg transition-colors shadow-sm shadow-orange-500/20">
                        Continue to Book
                    </a>
                </div>
            </div>
        </div>
    </main>
</x-layout>
