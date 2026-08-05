<x-layout>
    <x-slot:title>Driver Dashboard — RideMyCars</x-slot>
    <div class="pt-24 pb-12 bg-gray-50 dark:bg-[#09090b] min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Welcome back, {{ $user->name }}</h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">Here's your driver dashboard summary</p>
                </div>
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-medium {{ $profile->kyc_status === 'approved' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $profile->kyc_status === 'approved' ? 'bg-green-600 dark:bg-green-400' : 'bg-yellow-600 dark:bg-yellow-400' }}"></span>
                        KYC: {{ ucfirst($profile->kyc_status) }}
                    </span>
                </div>
            </div>

            <!-- Earnings Overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-2xl p-6 shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Today's Earnings</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">${{ number_format($dailyEarnings, 2) }}</p>
                </div>
                <div class="bg-white dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-2xl p-6 shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">This Week</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">${{ number_format($weeklyEarnings, 2) }}</p>
                </div>
                <div class="bg-white dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-2xl p-6 shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">This Month</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">${{ number_format($monthlyEarnings, 2) }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content (Jobs) -->
                <div class="lg:col-span-2 space-y-8">
                    
                    <!-- Active Jobs -->
                    <div class="bg-white dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-3xl p-6 shadow-sm">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Active Jobs</h2>
                        @if($activeRides->isEmpty())
                            <p class="text-gray-500 dark:text-gray-400 text-sm italic">No active jobs right now.</p>
                        @else
                            <div class="space-y-4">
                                @foreach($activeRides as $ride)
                                    <div class="p-4 border border-brand-100 dark:border-brand-900/30 bg-brand-50 dark:bg-brand-900/10 rounded-xl">
                                        <div class="flex justify-between items-start mb-2">
                                            <span class="text-xs font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-400">In Progress</span>
                                            <span class="font-bold text-gray-900 dark:text-white">${{ number_format($ride->fare, 2) }}</span>
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-300"><strong>From:</strong> {{ $ride->pickup_location }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-300"><strong>To:</strong> {{ $ride->dropoff_location }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Pending Jobs -->
                    <div class="bg-white dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-3xl p-6 shadow-sm">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Pending Requests</h2>
                        @if($pendingRides->isEmpty())
                            <p class="text-gray-500 dark:text-gray-400 text-sm italic">No pending requests.</p>
                        @else
                            <div class="space-y-4">
                                @foreach($pendingRides as $ride)
                                    <div class="p-4 border border-gray-100 dark:border-white/10 rounded-xl">
                                        <p class="text-sm text-gray-600 dark:text-gray-300"><strong>From:</strong> {{ $ride->pickup_location }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-300 mb-3"><strong>To:</strong> {{ $ride->dropoff_location }}</p>
                                        <div class="flex gap-2">
                                            <button class="px-4 py-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-sm font-medium rounded-lg">Accept</button>
                                            <button class="px-4 py-2 border border-gray-200 dark:border-white/10 text-gray-600 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-white/5">Decline</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Sidebar (Profile & Vehicles) -->
                <div class="space-y-8">
                    <!-- Driver Profile -->
                    <div class="bg-white dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-3xl p-6 shadow-sm">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Driver Profile</h2>
                        <ul class="space-y-3 text-sm">
                            <li class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">License No</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $profile->license_number }}</span>
                            </li>
                            <li class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Rating</span>
                                <span class="font-medium text-gray-900 dark:text-white flex items-center gap-1">
                                    <svg class="w-4 h-4 text-brand-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    {{ $profile->rating }}
                                </span>
                            </li>
                            <li class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Hourly Rate</span>
                                <span class="font-medium text-gray-900 dark:text-white">${{ $profile->hourly_rate ?? '0.00' }}</span>
                            </li>
                            <li class="flex justify-between items-center mt-2 pt-2 border-t border-gray-100 dark:border-white/10">
                                <span class="text-gray-500 dark:text-gray-400">Status</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                  <input type="checkbox" class="sr-only peer" {{ $profile->is_available ? 'checked' : '' }}>
                                  <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-brand-500"></div>
                                </label>
                            </li>
                        </ul>
                    </div>

                    <!-- Vehicles -->
                    <div class="bg-white dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-3xl p-6 shadow-sm">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">My Vehicles</h2>
                        @if($vehicles->isEmpty())
                            <p class="text-gray-500 dark:text-gray-400 text-sm italic mb-3">No vehicles registered.</p>
                            <a href="/owner-signup" class="text-sm text-brand-500 hover:text-brand-600 font-medium">Add a vehicle &rarr;</a>
                        @else
                            <ul class="space-y-4">
                                @foreach($vehicles as $vehicle)
                                    <li class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-white/10 flex items-center justify-center text-gray-500 dark:text-gray-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $vehicle->make }} {{ $vehicle->model }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $vehicle->license_plate }}</p>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</x-layout>
