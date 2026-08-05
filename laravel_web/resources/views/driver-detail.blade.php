<x-layout>
    <x-slot:title>Hire {{ $driverProfile->user->name }} — RideMyCars</x-slot>

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
                    @if($driverProfile->image_url)
                        <img src="{{ Storage::url($driverProfile->image_url) }}" alt="{{ $driverProfile->user->name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-300 dark:text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        </div>
                    @endif
                </div>

                <!-- Profile Info -->
                <div class="flex-1 w-full">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
                        <div>
                            <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white">{{ $driverProfile->user->name }}</h1>
                            <div class="flex items-center gap-4 mt-2">
                                <div class="flex items-center gap-1.5 bg-brand-50 dark:bg-brand-900/20 px-2.5 py-1 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-brand-500" fill="currentColor" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    <span class="text-sm font-bold text-brand-700 dark:text-brand-400">{{ $driverProfile->rating }} Rating</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-2.5 h-2.5 rounded-full {{ $driverProfile->is_available ? 'bg-green-500' : 'bg-red-500' }}"></div>
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ $driverProfile->is_available ? 'Available Now' : 'Currently Busy' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-left md:text-right w-full md:w-auto bg-gray-50 dark:bg-[#1a1a1a] p-4 rounded-2xl border border-gray-100 dark:border-white/5">
                            <div class="text-sm text-gray-500 dark:text-gray-400 font-medium mb-1">Hourly Rate</div>
                            <div class="text-3xl font-extrabold text-gray-900 dark:text-white">${{ $driverProfile->hourly_rate }}<span class="text-base text-gray-500 dark:text-gray-400 font-normal">/hr</span></div>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-100 dark:border-white/10">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3">About the Driver</h2>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed text-lg">
                            {{ $driverProfile->bio ?: 'A professional, experienced driver dedicated to providing a safe, comfortable, and efficient ride for all passengers.' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center max-w-md mx-auto">
            <a href="/ride" class="w-full flex items-center justify-center gap-2 py-4 px-6 bg-gray-900 dark:bg-white text-white dark:text-gray-900 hover:bg-gray-800 dark:hover:bg-gray-200 rounded-xl font-bold text-lg transition-colors shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                Book this Driver
            </a>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-4">You can request a specific driver during the booking process.</p>
        </div>

    </main>
</x-layout>
