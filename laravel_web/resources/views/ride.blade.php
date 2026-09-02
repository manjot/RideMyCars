<x-layout theme="theme-ride">
    <x-slot:title>Book a Ride — RideMyCars</x-slot>
    <x-slot:head>
        <style>
            /* Make Google Maps Autocomplete dropdown text wrap instead of crop */
            .pac-container {
                border-radius: 12px;
                box-shadow: 0 8px 30px rgba(0,0,0,0.2);
                border: 1px solid rgba(0,0,0,0.1);
                margin-top: 4px;
                z-index: 9999999 !important;
                pointer-events: auto !important;
            }
            .pac-item {
                white-space: normal !important;
                word-wrap: break-word !important;
                height: auto !important;
                padding: 10px 12px !important;
                line-height: 1.4 !important;
            }
            .pac-item-query {
                display: inline !important;
                font-size: 15px !important;
            }
            @keyframes searching-bar {
                0% { width: 0%; margin-left: 0; }
                50% { width: 60%; margin-left: 20%; }
                100% { width: 0%; margin-left: 100%; }
            }
            .animate-searching-bar {
                animation: searching-bar 1.8s ease-in-out infinite;
            }
        </style>
    </x-slot>

    <main class="w-full mx-auto px-4 py-8 sm:px-6 lg:px-8" style="max-width: 1500px;" x-data="rideBooking">
        <!-- Category Banner Component -->
        <x-category-banner category="Ride" />
        
        @if(session('success'))
            <div class="mb-6 p-5 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800/30 text-emerald-800 dark:text-emerald-200 font-semibold flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-brand-500 text-white flex items-center justify-center font-bold text-xl shrink-0 shadow-md">
                        🚘
                    </div>
                    <div>
                        <h4 class="font-extrabold text-lg text-gray-900 dark:text-white">Ride Booked Successfully!</h4>
                        <p class="text-sm text-emerald-700 dark:text-emerald-300 font-medium mt-0.5">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800/30 text-rose-800 dark:text-rose-200 font-semibold flex items-center gap-3 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="flex flex-col lg:flex-row gap-6 lg:gap-8 items-start relative">
            
            <form @submit.prevent="submitBooking" action="/ride/book" method="POST" class="flex flex-col lg:flex-row gap-6 lg:gap-8 w-full lg:w-auto z-10 shrink-0">
                @csrf
                
                <div x-show="!isConfirming" class="w-full lg:w-96 bg-white dark:bg-[#111] lg:p-6 lg:shadow-[0_8px_30px_rgb(0,0,0,0.08)] lg:dark:shadow-[0_8px_30px_rgb(0,0,0,0.3)] lg:rounded-[24px] border-0 lg:border border-gray-100 dark:border-white/10 shrink-0 h-fit" style="max-width: 100%;">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6 tracking-tight">Find a trip</h1>
                    
                    <input type="hidden" name="schedule_type" x-model="schedule_type">

                    <div class="flex mb-4" x-data="{ open: false }">
                        <div class="relative inline-block w-full">
                            <button type="button" @click="open = !open" @click.away="open = false" 
                                    class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] hover:bg-gray-100 dark:hover:bg-[#222] text-gray-900 dark:text-white font-bold rounded-xl cursor-pointer focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-colors text-sm shadow-sm border border-gray-200 dark:border-white/10">
                                <div class="flex items-center gap-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8Zm1-8h4a1 1 0 0 1 0 2h-5a1 1 0 0 1-1-1V7a1 1 0 0 1 2 0Z"/></svg>
                                    <span x-text="schedule_type === 'now' ? 'Pickup now' : 'Schedule for later'"></span>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                            </button>

                            <div x-show="open" style="display: none;" 
                                 x-transition.opacity.duration.200ms
                                 class="absolute left-0 mt-2 w-full bg-white dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] z-50 py-1 overflow-hidden">
                                <button type="button" @click="schedule_type = 'now'; open = false;" class="w-full text-left px-4 py-3 text-sm font-semibold flex items-center gap-3 transition-colors hover:bg-gray-50 dark:hover:bg-[#222]" :class="schedule_type === 'now' ? 'text-brand-500 bg-brand-50/50 dark:bg-brand-900/20' : 'text-gray-700 dark:text-gray-300'">
                                    <svg x-show="schedule_type === 'now'" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    <span :class="schedule_type !== 'now' ? 'ml-7' : ''">Pickup now</span>
                                </button>
                                <button type="button" @click="schedule_type = 'later'; open = false;" class="w-full text-left px-4 py-3 text-sm font-semibold flex items-center gap-3 transition-colors hover:bg-gray-50 dark:hover:bg-[#222]" :class="schedule_type === 'later' ? 'text-brand-500 bg-brand-50/50 dark:bg-brand-900/20' : 'text-gray-700 dark:text-gray-300'">
                                    <svg x-show="schedule_type === 'later'" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    <span :class="schedule_type !== 'later' ? 'ml-7' : ''">Schedule for later</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div x-show="schedule_type === 'later'" style="display: none;" class="grid grid-cols-2 gap-4 mb-4">
                        <input type="date" name="schedule_date" :required="schedule_type === 'later'" class="w-full px-4 py-3.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all text-sm">
                        <input type="time" name="schedule_time" :required="schedule_type === 'later'" class="w-full px-4 py-3.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all text-sm">
                    </div>

                    <!-- Rider Selection Dropdown (For me / Order ride for someone else) -->
                    <div class="mb-4" x-data="{ riderOpen: false, riderType: 'me', passengerName: '', passengerPhone: '' }">
                        <div class="relative inline-block w-full" @click.outside="riderOpen = false">
                            <!-- Trigger Button -->
                            <button type="button" @click="riderOpen = !riderOpen" 
                                    class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] hover:bg-gray-100 dark:hover:bg-[#222] text-gray-900 dark:text-white font-bold rounded-xl cursor-pointer focus:outline-none focus:ring-2 focus:ring-black/20 dark:focus:ring-white/20 transition-all text-sm shadow-sm border border-gray-200 dark:border-white/10">
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 rounded-full bg-black text-white dark:bg-white dark:text-black flex items-center justify-center font-bold text-xs shrink-0 shadow-sm">
                                        <span x-text="riderType === 'me' ? '{{ strtoupper(substr(auth()->user()->name ?? 'M', 0, 1)) }}' : '👤'"></span>
                                    </div>
                                    <span class="font-semibold text-gray-900 dark:text-white" x-text="riderType === 'me' ? 'For me' : ('For ' + (passengerName.trim() ? passengerName : 'someone else'))"></span>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500 transition-transform duration-200" :class="{'rotate-180': riderOpen}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="riderOpen" style="display: none;" 
                                 x-transition.opacity.duration.200ms
                                 class="absolute left-0 mt-2 w-full bg-white dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl shadow-[0_8px_30px_rgb(0,0,0,0.15)] z-50 p-2 space-y-1">
                                
                                <!-- Option 1: Me -->
                                <button type="button" @click="riderType = 'me'; riderOpen = false;" 
                                        class="w-full text-left px-3 py-2.5 text-sm font-semibold flex items-center justify-between rounded-lg transition-colors hover:bg-gray-100 dark:hover:bg-[#222]" 
                                        :class="riderType === 'me' ? 'text-black dark:text-white bg-gray-100/80 dark:bg-white/10 font-bold' : 'text-gray-700 dark:text-gray-300'">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-6 h-6 rounded-full bg-black text-white dark:bg-white dark:text-black flex items-center justify-center font-bold text-xs shrink-0">
                                            {{ strtoupper(substr(auth()->user()->name ?? 'M', 0, 1)) }}
                                        </div>
                                        <span>Me</span>
                                    </div>
                                    <svg x-show="riderType === 'me'" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-black dark:text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </button>

                                <!-- Option 2: Order ride for someone else -->
                                <button type="button" @click="riderType = 'someone_else';" 
                                        class="w-full text-left px-3 py-2.5 text-sm font-semibold flex items-center justify-between rounded-lg transition-colors hover:bg-gray-100 dark:hover:bg-[#222]" 
                                        :class="riderType === 'someone_else' ? 'text-black dark:text-white bg-gray-100/80 dark:bg-white/10 font-bold' : 'text-gray-700 dark:text-gray-300'">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-6 h-6 rounded-full bg-gray-200 dark:bg-white/20 text-gray-900 dark:text-white flex items-center justify-center text-xs shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                                        </div>
                                        <span>Order ride for someone else</span>
                                    </div>
                                    <svg x-show="riderType === 'someone_else'" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-black dark:text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </button>

                                <!-- Passenger Inputs (Expanded when 'someone_else' is active) -->
                                <div x-show="riderType === 'someone_else'" style="display: none;" x-transition.opacity.duration.200ms class="p-2.5 space-y-2 bg-gray-50 dark:bg-[#111] rounded-lg mt-1 border border-gray-200 dark:border-white/10">
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1">Passenger Name</label>
                                        <input type="text" x-model="passengerName" placeholder="Full name" class="w-full px-3 py-2 bg-white dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-md text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-black text-xs font-medium">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-1">Passenger Phone</label>
                                        <input type="tel" x-model="passengerPhone" placeholder="Phone number" class="w-full px-3 py-2 bg-white dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-md text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-black text-xs font-medium">
                                    </div>
                                    <button type="button" @click="riderOpen = false" class="w-full py-2 bg-black dark:bg-white text-white dark:text-black rounded-md text-xs font-bold transition-colors mt-1">
                                        Done
                                    </button>
                                </div>

                            </div>
                        </div>

                        <!-- Hidden inputs for rider selection -->
                        <input type="hidden" name="is_for_someone_else" :value="riderType === 'someone_else' ? '1' : '0'">
                        <input type="hidden" name="passenger_name" :value="passengerName">
                        <input type="hidden" name="passenger_phone" :value="passengerPhone">
                    </div>

                    <div class="space-y-4">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <div class="w-2.5 h-2.5 rounded-full bg-gray-900 dark:bg-white"></div>
                            </div>
                            <input type="text" id="pickup_location" name="pickup_location" x-model="pickup" required placeholder="Pickup location" class="w-full pl-10 pr-10 py-3.5 bg-gray-100 dark:bg-[#222] border-none rounded-xl text-gray-900 dark:text-white placeholder-gray-500 font-medium focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all">
                            <button type="button" id="use_my_location_btn" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors" title="Use my location">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            </button>
                        </div>

                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <div class="w-2.5 h-2.5 bg-gray-900 dark:bg-white"></div>
                            </div>
                            <input type="text" id="dropoff_location" name="dropoff_location" x-model="dropoff" required placeholder="Dropoff location" class="w-full pl-10 pr-4 py-3.5 bg-gray-100 dark:bg-[#222] border-none rounded-xl text-gray-900 dark:text-white placeholder-gray-500 font-medium focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all">
                        </div>

                        <!-- Dynamic Additional Destinations / Stops -->
                        <div class="space-y-3 pt-1">
                            <template x-for="(stop, index) in stops" :key="stop.id">
                                <div class="relative flex items-center gap-2">
                                    <div class="w-full relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none z-10">
                                            <div class="w-2.5 h-2.5 rounded-full bg-amber-500"></div>
                                        </div>
                                        <input type="text" 
                                               :placeholder="`Additional Stop ${index + 1} (search map/address)...`" 
                                               x-model="stop.location" 
                                               @input.debounce.300ms="searchStopLocation(stop)"
                                               @focus="if(stop.suggestions && stop.suggestions.length > 0) stop.showSuggestions = true"
                                               @click.outside="stop.showSuggestions = false"
                                               x-init="initStopAutocomplete($el, stop)"
                                               required 
                                               class="w-full pl-10 pr-10 py-3 bg-gray-100 dark:bg-[#222] border-none rounded-xl text-gray-900 dark:text-white placeholder-gray-500 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-amber-500/50 transition-all">

                                        <!-- Map Search Suggestions Dropdown -->
                                        <div x-show="stop.showSuggestions && stop.suggestions && stop.suggestions.length > 0"
                                             x-transition.opacity
                                             class="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-[#1f1f1f] border border-gray-200 dark:border-white/10 rounded-xl shadow-xl z-50 overflow-hidden divide-y divide-gray-100 dark:divide-white/5">
                                            <template x-for="item in stop.suggestions" :key="item.place_id">
                                                <button type="button" 
                                                        @click="stop.location = item.display_name; stop.showSuggestions = false;" 
                                                        class="w-full px-3.5 py-2.5 text-left text-xs text-gray-800 dark:text-gray-200 hover:bg-amber-50 dark:hover:bg-amber-950/40 flex items-start gap-2 transition-colors">
                                                    <span class="text-amber-500 shrink-0 mt-0.5">📍</span>
                                                    <span class="font-bold line-clamp-2" x-text="item.display_name"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                    <button type="button" @click="removeStop(index)" class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition-colors shrink-0" title="Remove stop">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </template>
                            <div class="flex flex-wrap items-center gap-2 pt-1">
                                <button type="button" @click="addStop()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/50 text-xs font-bold transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                    <span>+ Add Stop</span>
                                </button>

                                <!-- Quick Home Button -->
                                <div class="relative inline-flex items-center gap-1">
                                    <button type="button" 
                                            @click="useSavedLocation('home')" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg font-bold text-xs transition-all border cursor-pointer"
                                            :class="savedLocations.home ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/40 shadow-sm' : 'bg-gray-100 dark:bg-[#222] text-gray-700 dark:text-gray-300 border-transparent hover:border-amber-300'">
                                        <span>🏠</span>
                                        <span x-text="savedLocations.home ? 'Home' : '+ Save Home'"></span>
                                    </button>
                                    <template x-if="savedLocations.home">
                                        <button type="button" @click.stop="openSavedLocationModal('home')" class="p-1 text-gray-400 hover:text-amber-500 font-bold text-xs rounded transition-colors" title="Edit Home Address">
                                            ✏️
                                        </button>
                                    </template>
                                </div>

                                <!-- Quick Office Button -->
                                <div class="relative inline-flex items-center gap-1">
                                    <button type="button" 
                                            @click="useSavedLocation('office')" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg font-bold text-xs transition-all border cursor-pointer"
                                            :class="savedLocations.office ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/40 shadow-sm' : 'bg-gray-100 dark:bg-[#222] text-gray-700 dark:text-gray-300 border-transparent hover:border-amber-300'">
                                        <span>🏢</span>
                                        <span x-text="savedLocations.office ? 'Office' : '+ Save Office'"></span>
                                    </button>
                                    <template x-if="savedLocations.office">
                                        <button type="button" @click.stop="openSavedLocationModal('office')" class="p-1 text-gray-400 hover:text-amber-500 font-bold text-xs rounded transition-colors" title="Edit Office Address">
                                            ✏️
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>



                        <!-- Mandatory Phone Number Field -->
                        <div class="relative pt-1">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            </div>
                            <input type="tel" name="phone_number" x-model="phone" required placeholder="Mobile Phone Number (Required) *" class="w-full pl-10 pr-4 py-3 bg-gray-100 dark:bg-[#222] border-none rounded-xl text-gray-900 dark:text-white placeholder-gray-500 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all">
                        </div>
                    </div>

                    <div class="mt-8 space-y-4 hidden lg:block" x-show="!showRides" x-transition.opacity.duration.300ms>
                        <div class="flex items-start gap-4 p-4 rounded-xl hover:bg-gray-50 dark:hover:bg-[#1a1a1a] transition-colors cursor-default">
                            <div class="p-2 bg-brand-50 rounded-lg text-brand-500 shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white text-sm">Fully insured</h4>
                                <p class="text-xs text-gray-500 mt-0.5">Every ride covered by platform insurance.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 p-4 rounded-xl hover:bg-gray-50 dark:hover:bg-[#1a1a1a] transition-colors cursor-default">
                            <div class="p-2 bg-brand-50 rounded-lg text-brand-500 shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white text-sm">Verified drivers</h4>
                                <p class="text-xs text-gray-500 mt-0.5">Background-checked, rated 4.5 and above.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full bg-white dark:bg-[#111] lg:p-6 lg:shadow-[0_8px_30px_rgb(0,0,0,0.08)] lg:dark:shadow-[0_8px_30px_rgb(0,0,0,0.3)] lg:rounded-[24px] border-0 lg:border border-gray-100 dark:border-white/10 shrink-0 h-fit relative pb-28 lg:pb-24" x-show="showRides" style="display: none; max-width: 440px;" x-transition.opacity.duration.300ms>
                    
                    @guest
                        <div class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-white/70 dark:bg-[#111]/80 backdrop-blur-[2px] rounded-[24px]">
                            <div class="bg-white dark:bg-[#1a1a1a] p-6 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.4)] max-w-sm w-[90%] mx-auto border border-gray-100 dark:border-white/10 text-center relative mt-[-20px]">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Log in to see trip options</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Please take a moment to log in so we can show you your trip options.</p>
                                <a href="/login" class="block w-full py-3.5 bg-black dark:bg-white hover:bg-gray-800 dark:hover:bg-gray-200 text-white dark:text-black font-bold rounded-xl transition-all shadow-md active:scale-[0.98]">
                                    Continue
                                </a>
                            </div>
                        </div>
                    @endguest

                    <div class="@guest opacity-30 pointer-events-none select-none blur-[1px] @endguest transition-all duration-300">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-1 tracking-tight">Choose a ride</h2>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Recommended</h3>
                        
                        <input type="hidden" name="vehicle_type" x-model="vehicle_type">
                        <input type="hidden" name="payment_method" x-model="paymentMethod">

                        <div class="space-y-3 pr-1" style="max-height: calc(100vh - 300px); overflow-y: auto;">
                            @forelse($vehicles as $vehicle)
                            <div @click="vehicle_type = '{{ $vehicle->make }} {{ $vehicle->model }}'; selectedFare = $el.querySelector('.dynamic-price').innerText" 
                                 :class="vehicle_type === '{{ $vehicle->make }} {{ $vehicle->model }}' ? 'border-gray-900 ring-[1.5px] ring-gray-900 dark:border-white dark:ring-white bg-gray-50 dark:bg-[#222]' : 'border-transparent hover:bg-gray-50 dark:hover:bg-[#222]'"
                                 class="flex items-center justify-between p-4 rounded-[14px] border-[1.5px] cursor-pointer transition-colors bg-white dark:bg-[#1a1a1a] shadow-sm">
                                <div class="flex items-center gap-4">
                                    @if($vehicle->image_url)
                                        <img src="{{ Storage::url($vehicle->image_url) }}" alt="{{ $vehicle->make }}" class="w-[72px] h-14 object-contain">
                                    @else
                                        <div class="text-[52px] leading-none">
                                            @if(str_contains(strtolower($vehicle->type), 'sedan') || str_contains(strtolower($vehicle->type), 'luxury'))
                                                🚘
                                            @elseif(str_contains(strtolower($vehicle->type), 'suv') || str_contains(strtolower($vehicle->type), 'van'))
                                                🚙
                                            @elseif(str_contains(strtolower($vehicle->type), 'bike') || str_contains(strtolower($vehicle->type), 'moto'))
                                                🛵
                                            @else
                                                🚗
                                            @endif
                                        </div>
                                    @endif
                                    <div>
                                        <div class="flex items-center gap-1.5 mb-0.5">
                                            <h4 class="font-bold text-gray-900 dark:text-white text-lg leading-none">{{ $vehicle->make }} {{ $vehicle->model }}</h4>
                                            <div class="flex items-center text-xs font-bold text-gray-900 dark:text-white gap-0.5 mt-0.5">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                                {{ str_contains(strtolower($vehicle->type), 'bike') ? '1' : '4' }}
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 font-medium">3 mins away • 11:27 PM</p>
                                        @if(str_contains(strtolower($vehicle->type), 'bike'))
                                            <p class="text-[11px] text-gray-500 dark:text-gray-500 mt-0.5">Affordable 2 wheeler rides</p>
                                        @else
                                            <p class="text-[11px] text-gray-500 dark:text-gray-500 mt-0.5">Affordable rides</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right shrink-0 ml-2">
                                    <div class="dynamic-price font-bold text-xl text-gray-900 dark:text-white" data-daily-rate="{{ $vehicle->daily_rate }}">${{ number_format($vehicle->daily_rate, 2) }}</div>
                                    @if(str_contains(strtolower($vehicle->type), 'bike'))
                                        <div class="dynamic-price-strike text-xs text-gray-500 line-through" data-daily-rate="{{ $vehicle->daily_rate }}">${{ number_format($vehicle->daily_rate * 1.05, 2) }}</div>
                                    @endif
                                </div>
                            </div>
                            @empty
                                <div class="text-center p-6 bg-gray-50 dark:bg-[#1a1a1a] rounded-xl border border-gray-200 dark:border-white/10">
                                    <p class="text-gray-500 dark:text-gray-400 font-medium">No vehicles are currently available in your area.</p>
                                </div>
                            @endforelse
                        </div>

                        <div class="absolute bottom-0 left-0 w-full bg-white dark:bg-[#111] border-t border-gray-100 dark:border-white/10 p-4 lg:rounded-b-[24px] z-10">
                            <div class="flex items-center gap-3">
                                <button type="button" @click="paymentModal = true" class="flex items-center gap-2 px-3 py-2.5 bg-gray-100 dark:bg-[#222] hover:bg-gray-200 dark:hover:bg-[#333] transition-colors rounded-xl min-w-[140px]">
                                    <div class="p-1.5 bg-black dark:bg-white text-white dark:text-black rounded-lg">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                    </div>
                                    <div class="text-left flex-1">
                                        <div class="text-sm font-bold text-gray-900 dark:text-white" x-text="profileType"></div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 capitalize" x-text="paymentMethod"></div>
                                    </div>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400"><path d="m6 9 6 6 6-6"/></svg>
                                </button>

                                <button type="submit" class="flex-1 bg-black dark:bg-white hover:bg-gray-800 dark:hover:bg-gray-200 text-white dark:text-black font-bold py-3.5 rounded-xl text-[17px] transition-colors flex items-center justify-center shadow-md active:scale-[0.98]">
Request <span x-text="vehicle_type || 'Ride'" class="ml-1 truncate max-w-[130px]"></span>
                                </button>
                            </div>
                            <p class="text-[10px] text-gray-500 dark:text-gray-400 text-center mt-2">
                                Transportation is provided by independent licensed drivers. By requesting, you agree to our <a href="/terms-and-conditions" target="_blank" class="underline font-bold text-indigo-500">Terms & Conditions</a>.
                            </p>
                        </div>

                        <!-- Payment Modal (Uber Style - Flex Layout for 100% Viewport Safety) -->
                        <div x-show="paymentModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto">
                            <div @click.away="paymentModal = false" class="bg-white dark:bg-[#1a1a1a] rounded-3xl w-full max-w-md max-h-[90vh] flex flex-col shadow-2xl border border-gray-200 dark:border-white/10 overflow-hidden my-auto" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4">
                                
                                <!-- Sticky Header -->
                                <div class="p-4 flex items-center justify-between border-b border-gray-100 dark:border-white/10 shrink-0 bg-white dark:bg-[#1a1a1a]">
                                    <button type="button" @click="paymentModal = false" class="p-2 hover:bg-gray-100 dark:hover:bg-[#333] rounded-full transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                    </button>
                                    <h2 class="text-base font-extrabold text-gray-900 dark:text-white">Payment Methods</h2>
                                    <span class="text-xs font-bold text-gray-400">SSL Encrypted</span>
                                </div>

                                <!-- Scrollable Body -->
                                <div class="p-5 overflow-y-auto flex-1 space-y-5">
                                    <h3 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">Pay with</h3>
                                    
                                    <div class="flex bg-gray-100 dark:bg-[#222] rounded-xl p-1">
                                        <button type="button" @click="profileType = 'Personal'" :class="profileType === 'Personal' ? 'bg-white dark:bg-[#333] shadow-sm text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400'" class="flex-1 py-2.5 rounded-lg font-bold text-sm flex items-center justify-center gap-2 transition-all">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                            Personal
                                        </button>
                                        <button type="button" @click="profileType = 'Business'" :class="profileType === 'Business' ? 'bg-white dark:bg-[#333] shadow-sm text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400'" class="flex-1 py-2.5 rounded-lg font-bold text-sm flex items-center justify-center gap-2 transition-all">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M20 6h-4V4c0-1.11-.89-2-2-2h-4c-1.11 0-2 .89-2 2v2H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-6 0h-4V4h4v2z"/></svg>
                                            Business
                                        </button>
                                    </div>
                                    
                                    <div>
                                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Available Options</h4>
                                        <div class="space-y-2">
                                            <label class="flex items-center justify-between p-3.5 rounded-2xl border border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-[#222] cursor-pointer transition-colors group" :class="paymentMethod === 'Cash' ? 'border-gray-900 dark:border-white bg-gray-50 dark:bg-[#222]' : ''">
                                                <div class="flex items-center gap-3.5">
                                                    <div class="w-10 h-7 bg-[#85bb65] rounded-lg text-white flex items-center justify-center font-bold text-xs shadow-sm">💵</div>
                                                    <span class="font-extrabold text-gray-900 dark:text-white text-sm">Cash on Arrival</span>
                                                </div>
                                                <div class="w-5 h-5 rounded-full border-2 border-gray-300 dark:border-gray-600 flex items-center justify-center" :class="paymentMethod === 'Cash' ? 'border-black dark:border-white bg-black dark:bg-white' : 'group-hover:border-gray-400'">
                                                    <svg x-show="paymentMethod === 'Cash'" xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-white dark:text-black" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                </div>
                                                <input type="radio" x-model="paymentMethod" value="Cash" class="hidden">
                                            </label>
                                            
                                            <label class="flex items-center justify-between p-3.5 rounded-2xl border border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-[#222] cursor-pointer transition-colors group" :class="paymentMethod === 'stripe' ? 'border-gray-900 dark:border-white bg-gray-50 dark:bg-[#222]' : ''">
                                                <div class="flex items-center gap-3.5">
                                                    <div class="w-10 h-7 bg-[#635BFF] rounded-lg text-white flex items-center justify-center font-black text-base shadow-sm">S</div>
                                                    <span class="font-extrabold text-gray-900 dark:text-white text-sm">Stripe / Credit Card</span>
                                                </div>
                                                <div class="w-5 h-5 rounded-full border-2 border-gray-300 dark:border-gray-600 flex items-center justify-center" :class="paymentMethod === 'stripe' ? 'border-black dark:border-white bg-black dark:bg-white' : 'group-hover:border-gray-400'">
                                                    <svg x-show="paymentMethod === 'stripe'" xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-white dark:text-black" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                </div>
                                                <input type="radio" x-model="paymentMethod" value="stripe" class="hidden">
                                            </label>

                                            <!-- Card Fillup Information for Stripe -->
                                            <x-stripe-card-input modelName="paymentMethod" value="stripe" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Sticky Footer Button -->
                                <div class="p-4 bg-gray-50 dark:bg-[#111] border-t border-gray-100 dark:border-white/10 shrink-0">
                                    <button type="button" @click="paymentModal = false" class="w-full py-3.5 bg-black dark:bg-white text-white dark:text-black font-black text-base rounded-2xl shadow-xl hover:opacity-90 transition-all active:scale-[0.99]">
                                        Save & Continue →
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                </div> <!-- Closes w-[440px] panel -->

                <!-- Confirming State UI -->
                <div class="w-full lg:w-[440px] bg-white dark:bg-[#111] lg:p-6 lg:shadow-[0_8px_30px_rgb(0,0,0,0.08)] lg:dark:shadow-[0_8px_30px_rgb(0,0,0,0.3)] lg:rounded-[24px] border-0 lg:border border-gray-100 dark:border-white/10 shrink-0 h-fit" style="display: none; max-width: 100%;" x-show="isConfirming" x-cloak x-transition>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-8 tracking-tight">Confirming your ride</h2>
                    
                    <div class="relative pl-6 ml-3 mb-8 border-l-[3px] border-gray-900 dark:border-white">
                        <div class="absolute -left-[7.5px] top-0 w-3 h-3 bg-gray-900 dark:bg-white rounded-full"></div>
                        <div class="absolute -left-[7.5px] bottom-0 w-3 h-3 border-[3px] border-gray-900 dark:border-white bg-white dark:bg-[#111]"></div>
                        
                        <div class="pb-6 -mt-1.5">
                            <p class="font-bold text-[15px] text-gray-900 dark:text-white">Meet at the pick-up point for</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5 pr-16 leading-snug break-words" x-text="pickup"></p>
                        </div>
                        <div class="pb-1 -mb-1.5">
                            <div class="flex justify-between items-start gap-4">
                                <p class="font-bold text-[15px] text-gray-900 dark:text-white break-words min-w-0 flex-1" x-text="dropoff"></p>
                                <button type="button" @click="cancelRide()" class="shrink-0 px-3 py-1.5 bg-gray-100 dark:bg-[#333] hover:bg-gray-200 transition-colors rounded-full text-[13px] font-bold text-gray-900 dark:text-white">Change</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4 mb-6 pt-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-900 dark:text-white"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                        <div>
                            <p class="font-bold text-[15px] text-gray-900 dark:text-white" x-text="(!selectedFare || selectedFare === '$0.00') ? '$28.50' : selectedFare"></p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 capitalize" x-text="paymentMethod || 'Cash'"></p>
                        </div>
                    </div>

                    <!-- Ride Lifecycle Timeline -->
                    <div class="mb-6 space-y-0">
                        <!-- Step 1: Looking for drivers -->
                        <div class="flex items-start gap-3 pb-4">
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" :class="rideStatus === 'pending' ? 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 animate-pulse' : (rideStatus !== 'pending' ? 'bg-green-100 dark:bg-green-900/40 text-green-600' : 'bg-gray-100 text-gray-400')">
                                    <template x-if="rideStatus === 'pending'"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></template>
                                    <template x-if="rideStatus !== 'pending'"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg></template>
                                </div>
                                <div class="w-0.5 h-4 bg-gray-200 dark:bg-gray-700"></div>
                            </div>
                            <div class="-mt-0.5">
                                <p class="font-bold text-sm" :class="rideStatus === 'pending' ? 'text-indigo-700 dark:text-indigo-300' : 'text-green-700 dark:text-green-400'" x-text="rideStatus === 'pending' ? 'Looking for nearby drivers...' : 'Driver found!'"></p>
                                <p class="text-xs text-gray-500 mt-0.5" x-show="rideStatus === 'pending'">Sending request to all available drivers</p>
                                <p class="text-xs text-gray-500 mt-0.5" x-show="rideStatus !== 'pending' && driverName" x-text="driverName + ' accepted your ride'"></p>
                            </div>
                        </div>

                        <!-- Step 2: Driver en route -->
                        <div class="flex items-start gap-3 pb-4" x-show="['en_route','arrived','in_progress','completed'].includes(rideStatus) || rideStatus === 'accepted'">
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" :class="rideStatus === 'en_route' ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-600 animate-pulse' : (rideStatus === 'accepted' ? 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-600 animate-pulse' : (['arrived','in_progress','completed'].includes(rideStatus) ? 'bg-green-100 dark:bg-green-900/40 text-green-600' : 'bg-gray-100 text-gray-400'))">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M5 17H3v-6l2-5h9l4 5h1a2 2 0 0 1 2 2v4h-2m-4 0H9"/></svg>
                                </div>
                                <div class="w-0.5 h-4 bg-gray-200 dark:bg-gray-700"></div>
                            </div>
                            <div class="-mt-0.5">
                                <p class="font-bold text-sm" :class="rideStatus === 'en_route' ? 'text-blue-700 dark:text-blue-300' : (rideStatus === 'accepted' ? 'text-yellow-700 dark:text-yellow-300' : 'text-green-700 dark:text-green-400')" x-text="rideStatus === 'accepted' ? 'Driver is preparing...' : (rideStatus === 'en_route' ? 'Driver is on the way!' : 'Driver reached pickup')"></p>
                                <p class="text-xs text-gray-500 mt-0.5" x-show="rideStatus === 'en_route'" x-text="driverName + ' is coming to your location'"></p>
                            </div>
                        </div>

                        <!-- Step 3: Arrived at pickup -->
                        <div class="flex items-start gap-3 pb-4" x-show="['arrived','in_progress','completed'].includes(rideStatus)">
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" :class="rideStatus === 'arrived' ? 'bg-amber-100 dark:bg-amber-900/40 text-amber-600 animate-pulse' : (['in_progress','completed'].includes(rideStatus) ? 'bg-green-100 dark:bg-green-900/40 text-green-600' : 'bg-gray-100 text-gray-400')">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                </div>
                                <div class="w-0.5 h-4 bg-gray-200 dark:bg-gray-700"></div>
                            </div>
                            <div class="-mt-0.5">
                                <p class="font-bold text-sm" :class="rideStatus === 'arrived' ? 'text-amber-700 dark:text-amber-300' : 'text-green-700 dark:text-green-400'">Driver has arrived at pickup!</p>
                                <p class="text-xs text-gray-500 mt-0.5" x-show="rideStatus === 'arrived'">Please meet your driver</p>
                            </div>
                        </div>

                        <!-- Step 4: Trip in progress -->
                        <div class="flex items-start gap-3 pb-4" x-show="['in_progress','completed'].includes(rideStatus)">
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" :class="rideStatus === 'in_progress' ? 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 animate-pulse' : (rideStatus === 'completed' ? 'bg-green-100 dark:bg-green-900/40 text-green-600' : 'bg-gray-100 text-gray-400')">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                                </div>
                                <div class="w-0.5 h-4 bg-gray-200 dark:bg-gray-700"></div>
                            </div>
                            <div class="-mt-0.5">
                                <p class="font-bold text-sm" :class="rideStatus === 'in_progress' ? 'text-emerald-700 dark:text-emerald-300' : 'text-green-700 dark:text-green-400'">Trip in progress</p>
                                <p class="text-xs text-gray-500 mt-0.5" x-show="rideStatus === 'in_progress'">You're on your way!</p>
                            </div>
                        </div>

                        <!-- Step 5: Completed -->
                        <div class="flex items-start gap-3" x-show="rideStatus === 'completed'">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 bg-green-500 text-white">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div class="-mt-0.5">
                                <p class="font-bold text-sm text-green-700 dark:text-green-400">Trip completed!</p>
                                <p class="text-xs text-gray-500 mt-0.5">Thank you for riding with RideMyCars</p>
                            </div>
                        </div>

                        <!-- Progress bar for pending -->
                        <div x-show="rideStatus === 'pending'" class="mt-2 h-1.5 bg-indigo-100 dark:bg-indigo-900/50 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-500 rounded-full animate-searching-bar"></div>
                        </div>
                    </div>

                    <!-- Cancel / Rate buttons -->
                    <template x-if="rideStatus !== 'completed'">
                        <button type="button" @click="cancelRide()" class="w-full py-4 bg-gray-100 dark:bg-[#222] hover:bg-gray-200 dark:hover:bg-[#333] text-red-600 dark:text-red-500 font-bold rounded-xl text-[17px] transition-colors">
                            Cancel trip
                        </button>
                    </template>
                    <template x-if="rideStatus === 'completed' && !reviewSubmitted">
                        <button type="button" @click="showReviewModal = true" class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-[17px] transition-colors">
                            ⭐ Rate your driver
                        </button>
                    </template>
                    <template x-if="rideStatus === 'completed' && reviewSubmitted">
                        <div class="text-center py-4">
                            <p class="text-green-600 dark:text-green-400 font-bold">✓ Review submitted!</p>
                            <a href="/my-rides" class="text-indigo-600 dark:text-indigo-400 text-sm font-semibold hover:underline mt-1 inline-block">View My Rides →</a>
                        </div>
                    </template>
                </div>

                <!-- Rating Modal -->
                <div x-show="showReviewModal" x-cloak style="display: none;" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
                    <div class="bg-white dark:bg-[#1a1a1a] rounded-3xl p-8 w-full max-w-md shadow-2xl" @click.outside="showReviewModal = false">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Rate Your Driver</h3>
                        <p class="text-sm text-gray-500 mb-6" x-text="'How was your ride with ' + driverName + '?'"></p>
                        
                        <div class="flex justify-center gap-2 mb-6">
                            <template x-for="star in [1,2,3,4,5]" :key="star">
                                <button @click="reviewRating = star" class="text-3xl transition-transform hover:scale-110" :class="star <= reviewRating ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'">★</button>
                            </template>
                        </div>
                        
                        <textarea x-model="reviewComment" placeholder="Leave a comment (optional)..." rows="3" class="w-full bg-gray-50 dark:bg-[#111] border border-gray-200 dark:border-white/10 rounded-xl px-4 py-3 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 mb-4 resize-none"></textarea>
                        
                        <div class="flex gap-3">
                            <button @click="submitReview()" :disabled="reviewRating < 1" class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-bold rounded-xl transition-colors">
                                Submit Review
                            </button>
                            <button @click="showReviewModal = false" class="px-6 py-3 bg-gray-100 dark:bg-[#333] text-gray-700 dark:text-gray-300 font-bold rounded-xl transition-colors">
                                Skip
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Choose a Rider Modal (Uber Style - Teleported to body for top-level overlay) -->
                <template x-teleport="body">
                    <div x-show="showRiderModal" style="display: none;" 
                         class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95">
                        
                        <div class="bg-white dark:bg-[#181818] w-full max-w-md rounded-2xl shadow-2xl p-6 relative border border-gray-100 dark:border-white/10" @click.away="showRiderModal = false">
                            
                            <!-- Header -->
                            <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-white/10 mb-4">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">Choose a rider</h3>
                                <button type="button" @click="showRiderModal = false" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center text-gray-500 hover:text-gray-900 dark:hover:text-white transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <!-- Rider Options -->
                            <div class="space-y-3">
                                
                                <!-- Option 1: Me -->
                                <div @click="riderType = 'me'" class="flex items-center justify-between p-4 rounded-xl cursor-pointer border transition-all" :class="riderType === 'me' ? 'border-black dark:border-white bg-gray-50 dark:bg-white/5 font-semibold' : 'border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/5'">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-black text-white dark:bg-white dark:text-black font-bold flex items-center justify-center text-sm shadow-sm">
                                            {{ strtoupper(substr(auth()->user()->name ?? 'M', 0, 1)) }}
                                        </div>
                                        <span class="text-base font-bold text-gray-900 dark:text-white">Me</span>
                                    </div>
                                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors" :class="riderType === 'me' ? 'border-black dark:border-white' : 'border-gray-300 dark:border-gray-600'">
                                        <div x-show="riderType === 'me'" class="w-2.5 h-2.5 rounded-full bg-black dark:bg-white"></div>
                                    </div>
                                </div>

                                <!-- Option 2: Order ride for someone else -->
                                <div @click="riderType = 'someone_else'" class="flex items-center justify-between p-4 rounded-xl cursor-pointer border transition-all" :class="riderType === 'someone_else' ? 'border-black dark:border-white bg-gray-50 dark:bg-white/5 font-semibold' : 'border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/5'">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-white/10 text-gray-900 dark:text-white flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                                        </div>
                                        <span class="text-base font-bold text-gray-900 dark:text-white">Order ride for someone else</span>
                                    </div>
                                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors" :class="riderType === 'someone_else' ? 'border-black dark:border-white' : 'border-gray-300 dark:border-gray-600'">
                                        <div x-show="riderType === 'someone_else'" class="w-2.5 h-2.5 rounded-full bg-black dark:bg-white"></div>
                                    </div>
                                </div>

                                <!-- Passenger Inputs (Shown when someone_else is selected) -->
                                <div x-show="riderType === 'someone_else'" style="display: none;" x-transition.opacity.duration.200ms class="pt-2 space-y-3">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Passenger Name</label>
                                        <input type="text" x-model="passengerName" placeholder="Enter passenger's full name" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-black dark:focus:ring-white text-sm font-medium">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Passenger Phone Number</label>
                                        <input type="tel" x-model="passengerPhone" placeholder="Enter phone number (+1 555 000-1234)" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-black dark:focus:ring-white text-sm font-medium">
                                    </div>
                                </div>

                            </div>

                            <!-- Footer Button -->
                            <div class="mt-6 pt-4 border-t border-gray-100 dark:border-white/10">
                                <button type="button" @click="showRiderModal = false" class="w-full bg-black hover:bg-gray-900 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-black font-bold py-3.5 rounded-xl text-base transition-colors shadow-md">
                                    Done
                                </button>
                            </div>

                        </div>
                    </div>
                </template>
            </form>
        
            <!-- Right Side: Map -->
            <div class="w-full lg:flex-1 h-96 lg:h-auto bg-gray-50 dark:bg-[#1a1a1a] rounded-[24px] border border-gray-200 dark:border-white/10 overflow-hidden relative shadow-sm shrink-0 lg:shrink" style="min-width: 300px; min-height: 500px;">
                <div id="map" class="w-full h-full absolute inset-0"></div>
            </div>

        </div>

        <!-- Saved Location Setup / Edit Modal (Top-Level Container for Absolute Stacking Order above Map) -->
        <div x-show="showSavedLocationModal" 
             x-transition.opacity
             style="display: none;"
             class="fixed inset-0 z-[9999999] flex items-center justify-center p-4 bg-black/70 backdrop-blur-md">
            <div @click.outside="showSavedLocationModal = false" class="bg-white dark:bg-[#181818] rounded-3xl border border-gray-200 dark:border-white/10 shadow-2xl max-w-md w-full p-6 space-y-4 relative z-[99999999]">
                <div class="flex items-center justify-between">
                    <h3 class="font-extrabold text-base text-gray-900 dark:text-white flex items-center gap-2">
                        <span x-text="editingLabel === 'home' ? '🏠' : '🏢'"></span>
                        <span x-text="(savedLocations[editingLabel] ? 'Edit ' : 'Set ') + (editingLabel === 'home' ? 'Home' : 'Office') + ' Address'"></span>
                    </h3>
                    <button type="button" @click="showSavedLocationModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-sm">✕</button>
                </div>

                <p class="text-xs text-gray-500 dark:text-gray-400">Search and select your <span x-text="editingLabel"></span> address from the map. It will be saved for one-tap booking.</p>

                <div class="relative space-y-2">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Map Address Search</label>
                    <div class="relative">
                        <input type="text" 
                               x-model="modalTempLocation.location"
                               @input.debounce.300ms="searchModalLocation()"
                               @focus="if(modalSuggestions.length > 0) showModalSuggestions = true"
                               @click.outside="showModalSuggestions = false"
                               x-init="initModalAutocomplete($el)"
                               placeholder="Search address, city, landmark..." 
                               class="w-full pl-9 pr-8 py-3 bg-gray-50 dark:bg-[#222] border rounded-xl text-xs font-bold text-gray-900 dark:text-white transition-colors"
                               :class="modalTempLocation.isSelected ? 'border-emerald-500 ring-1 ring-emerald-500/30' : 'border-gray-200 dark:border-white/10'">
                        <span class="absolute left-3 top-3.5 text-amber-500">📍</span>
                        <span x-show="modalTempLocation.isSelected" class="absolute right-3 top-3.5 text-emerald-500 font-bold text-xs" title="Verified Location">✓</span>
                    </div>

                    <!-- Selection Confirmation Badge -->
                    <div x-show="modalTempLocation.isSelected" class="flex items-center gap-1.5 p-2 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/40 rounded-xl text-[11px] font-extrabold text-emerald-700 dark:text-emerald-300">
                        <span class="text-emerald-500 shrink-0">✓ Verified Location:</span>
                        <span class="truncate" x-text="modalTempLocation.location"></span>
                    </div>

                    <!-- Suggestions Dropdown -->
                    <div x-show="showModalSuggestions && modalSuggestions.length > 0"
                         x-transition.opacity
                         class="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-[#1f1f1f] border border-amber-300 dark:border-white/20 rounded-2xl shadow-2xl z-[999999999] overflow-hidden divide-y divide-gray-100 dark:divide-white/5 max-h-56 overflow-y-auto">
                        <template x-for="item in modalSuggestions" :key="item.place_id || item.osm_id">
                            <button type="button" 
                                    @click="selectModalSuggestion(item)" 
                                    class="w-full px-3.5 py-3 text-left text-xs text-gray-800 dark:text-gray-200 hover:bg-amber-50 dark:hover:bg-amber-950/40 flex items-start gap-2.5 transition-colors cursor-pointer">
                                <span class="text-amber-500 shrink-0 mt-0.5">📍</span>
                                <div>
                                    <span class="font-bold block text-xs" x-text="item.display_name"></span>
                                    <span class="text-[10px] text-gray-400 font-normal block" x-text="item.type ? (item.type.toUpperCase() + ' • ' + (item.class || 'location')) : 'Map Location'"></span>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-gray-100 dark:border-white/10">
                    <button type="button" @click="showSavedLocationModal = false" class="px-4 py-2.5 rounded-xl bg-gray-100 dark:bg-[#222] text-gray-700 dark:text-gray-300 font-bold text-xs">Cancel</button>
                    <button type="button" @click="saveLocationFromModal()" :disabled="!modalTempLocation.isSelected" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 disabled:opacity-40 text-white font-extrabold text-xs shadow-md transition-all cursor-pointer">Save & Apply</button>
                </div>
            </div>
        </div>
    </main>

    @php
        $gmapsKey = config('services.google_maps.api_key');
        $hasValidKey = !empty($gmapsKey) && !str_contains($gmapsKey, 'AIzaSyDemoKey');
    @endphp

    <!-- Maps & Places Integration -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    @if($hasValidKey)
        <script src="https://maps.googleapis.com/maps/api/js?key={{ $gmapsKey }}&libraries=places" async defer></script>
    @endif
    <script>
        function initModalAutocomplete(el) {
            if (window.google && google.maps && google.maps.places) {
                try {
                    const ac = new google.maps.places.Autocomplete(el);
                    ac.addListener('place_changed', () => {
                        const place = ac.getPlace();
                        const component = Alpine.$data(el.closest('[x-data]'));
                        if (component) {
                            if (place.geometry && place.geometry.location) {
                                component.modalTempLocation.lat = place.geometry.location.lat();
                                component.modalTempLocation.lng = place.geometry.location.lng();
                            }
                            if (place.place_id) component.modalTempLocation.place_id = place.place_id;
                            const addr = place.formatted_address || place.name;
                            if (addr) {
                                component.modalTempLocation.location = addr;
                                component.modalTempLocation.isSelected = true;
                                component.showModalSuggestions = false;
                                el.value = addr;
                                el.dispatchEvent(new Event('input'));
                            }
                        }
                    });
                } catch (e) {}
            }
        }

        function initStopAutocomplete(el, stopObj) {
            if (window.google && google.maps && google.maps.places) {
                try {
                    const ac = new google.maps.places.Autocomplete(el);
                    ac.addListener('place_changed', () => {
                        const place = ac.getPlace();
                        const addr = place.formatted_address || place.name;
                        if (addr) {
                            stopObj.location = addr;
                            el.value = addr;
                            el.dispatchEvent(new Event('input'));
                        }
                    });
                } catch (e) {}
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            let map = null;
            let marker = null;
            let directionsService = null;
            let directionsRenderer = null;
            let pickupLoc = null;
            let dropoffLoc = null;
            let vehicleMarkers = [];

            const pickupInput = document.getElementById("pickup_location");
            const dropoffInput = document.getElementById("dropoff_location");
            const locBtn = document.getElementById("use_my_location_btn");

            function drawVehicles(location) {
                // Clear existing
                vehicleMarkers.forEach(m => m.setMap(null));
                vehicleMarkers = [];
                
                const lat = typeof location.lat === 'function' ? location.lat() : location.lat;
                const lng = typeof location.lng === 'function' ? location.lng() : location.lng;

                // Spawn 4-5 vehicles around pickup
                for(let i=0; i<5; i++) {
                    let offsetLat = (Math.random() - 0.5) * 0.015;
                    let offsetLng = (Math.random() - 0.5) * 0.015;
                    let carMarker = new google.maps.Marker({
                        position: { lat: lat + offsetLat, lng: lng + offsetLng },
                        map: map,
                        icon: {
                            // A simple top-down car SVG path
                            path: 'M17.4 0H5.6C2.5 0 0 3.5 0 6.6v34.8C0 44.5 2.5 47 5.6 47h11.8c3.1 0 5.6-2.5 5.6-5.6V6.6C23 3.5 20.5 0 17.4 0z',
                            fillColor: "white",
                            fillOpacity: 1,
                            strokeWeight: 2,
                            strokeColor: "black",
                            rotation: Math.random() * 360,
                            scale: 0.4,
                            anchor: new google.maps.Point(11.5, 23.5)
                        }
                    });
                    vehicleMarkers.push(carMarker);
                }
            }

            function calculateRoute() {
                if (pickupLoc && dropoffLoc && directionsService && directionsRenderer) {
                    directionsService.route({
                        origin: pickupLoc,
                        destination: dropoffLoc,
                        travelMode: google.maps.TravelMode.DRIVING
                    }, (response, status) => {
                        if (status === 'OK') {
                            directionsRenderer.setDirections(response);
                            if (marker) marker.setMap(null); // Hide default single marker
                            drawVehicles(pickupLoc);

                            // Calculate dynamic prices based on route
                            const route = response.routes[0].legs[0];
                            const distanceKm = route.distance.value / 1000;
                            const durationMin = route.duration.value / 60;
                            
                            // Adjust traffic multiplier (simulate traffic based on avg speed)
                            // avg speed in km/h
                            const avgSpeed = distanceKm / (durationMin / 60);
                            const trafficMultiplier = avgSpeed < 20 ? 1.2 : (avgSpeed > 40 ? 0.9 : 1.0);

                            document.querySelectorAll('.dynamic-price').forEach(el => {
                                const dailyRate = parseFloat(el.getAttribute('data-daily-rate'));
                                // Split daily rate into base, per km, and per min
                                const basePrice = dailyRate / 10;
                                const perKm = dailyRate / 50;
                                const perMin = dailyRate / 100;
                                
                                const fare = (basePrice + (distanceKm * perKm) + (durationMin * perMin)) * trafficMultiplier;
                                el.innerText = '$' + fare.toFixed(2);
                            });
                            
                            document.querySelectorAll('.dynamic-price-strike').forEach(el => {
                                const dailyRate = parseFloat(el.getAttribute('data-daily-rate'));
                                const basePrice = dailyRate / 10;
                                const perKm = dailyRate / 50;
                                const perMin = dailyRate / 100;
                                
                                const fare = (basePrice + (distanceKm * perKm) + (durationMin * perMin)) * trafficMultiplier;
                                el.innerText = '$' + (fare * 1.05).toFixed(2);
                            });

                        } else {
                            console.warn("Directions request failed: " + status);
                        }
                    });
                }
            }

            if (typeof google !== 'undefined' && google.maps) {
                try {
                    map = new google.maps.Map(document.getElementById("map"), {
                        center: { lat: 40.7128, lng: -74.0060 }, // Default to NY
                        zoom: 12,
                        mapTypeControl: false,
                        streetViewControl: false,
                        fullscreenControl: false
                    });
                    marker = new google.maps.Marker({ map: map });
                    
                    directionsService = new google.maps.DirectionsService();
                    directionsRenderer = new google.maps.DirectionsRenderer({
                        map: map,
                        polylineOptions: { strokeColor: '#000000', strokeWeight: 4 }
                    });
                    
                    if (pickupInput && google.maps.places) {
                        const pickupAutocomplete = new google.maps.places.Autocomplete(pickupInput);
                        pickupAutocomplete.addListener("place_changed", () => {
                            const place = pickupAutocomplete.getPlace();
                            if (place.geometry) {
                                pickupLoc = place.geometry.location;
                                map.setCenter(pickupLoc);
                                if (marker) marker.setPosition(pickupLoc);
                                map.setZoom(15);
                                calculateRoute();
                            }
                            if (place.formatted_address) {
                                pickupInput.value = place.formatted_address;
                            } else {
                                pickupInput.value = place.name;
                            }
                            pickupInput.dispatchEvent(new Event('input'));
                            pickupInput.blur();
                            const hidePac = () => document.querySelectorAll('.pac-container').forEach(c => c.style.display = 'none');
                            hidePac();
                            setTimeout(hidePac, 100);
                            setTimeout(hidePac, 300);
                            setTimeout(hidePac, 600);
                        });
                    }

                    if (dropoffInput && google.maps.places) {
                        const dropoffAutocomplete = new google.maps.places.Autocomplete(dropoffInput);
                        dropoffAutocomplete.addListener("place_changed", () => {
                            const place = dropoffAutocomplete.getPlace();
                            if (place.geometry) {
                                dropoffLoc = place.geometry.location;
                                calculateRoute();
                            }
                            if (place.formatted_address) {
                                dropoffInput.value = place.formatted_address;
                            } else {
                                dropoffInput.value = place.name;
                            }
                            dropoffInput.dispatchEvent(new Event('input'));
                            dropoffInput.blur();
                            const hidePacDrop = () => document.querySelectorAll('.pac-container').forEach(c => c.style.display = 'none');
                            hidePacDrop();
                            setTimeout(hidePacDrop, 100);
                            setTimeout(hidePacDrop, 300);
                            setTimeout(hidePacDrop, 600);
                        });
                    }
                } catch (e) {
                    console.warn("Google Maps init skipped or failed:", e);
                }
            } else if (typeof L !== 'undefined') {
                try {
                    const leafletMap = L.map('map').setView([40.7128, -74.0060], 12);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '© OpenStreetMap'
                    }).addTo(leafletMap);

                    L.marker([40.7128, -74.0060]).addTo(leafletMap)
                        .bindPopup('<b>New York, NY</b><br>Pickup Location')
                        .openPopup();

                    for (let i = 0; i < 5; i++) {
                        let offsetLat = (Math.random() - 0.5) * 0.015;
                        let offsetLng = (Math.random() - 0.5) * 0.015;
                        L.circleMarker([40.7128 + offsetLat, -74.0060 + offsetLng], {
                            radius: 8,
                            fillColor: '#f59e0b',
                            color: '#000000',
                            weight: 2,
                            opacity: 1,
                            fillOpacity: 0.9
                        }).addTo(leafletMap).bindPopup('<b>Available Driver</b>');
                    }
                } catch (e) {
                    console.warn("Leaflet map init failed:", e);
                }
            }

            if (locBtn && pickupInput) {
                locBtn.addEventListener("click", () => {
                    if (!navigator.geolocation) {
                        alert("Error: Your browser doesn't support geolocation.");
                        return;
                    }

                    const originalHTML = locBtn.innerHTML;
                    locBtn.disabled = true;
                    locBtn.innerHTML = `
                        <svg class="animate-spin h-3.5 w-3.5 text-brand-500 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Locating...</span>
                    `;

                    navigator.geolocation.getCurrentPosition(
                        async (position) => {
                            const pos = {
                                lat: position.coords.latitude,
                                lng: position.coords.longitude,
                            };

                            if (map && marker) {
                                map.setCenter(pos);
                                marker.setPosition(pos);
                                map.setZoom(15);
                            }
                            
                            pickupLoc = pos;
                            calculateRoute();

                            let addressSet = false;

                            // 1. Try Google Maps Geocoder if loaded
                            if (typeof google !== 'undefined' && google.maps && google.maps.Geocoder) {
                                try {
                                    const geocoder = new google.maps.Geocoder();
                                    const res = await new Promise((resolve) => {
                                        geocoder.geocode({ location: pos }, (results, status) => {
                                            if (status === "OK" && results && results[0]) {
                                                resolve(results[0].formatted_address);
                                            } else {
                                                resolve(null);
                                            }
                                        });
                                    });
                                    if (res) {
                                        pickupInput.value = res;
                                        addressSet = true;
                                    }
                                } catch (e) {
                                    console.warn("Google Geocoder error:", e);
                                }
                            }

                            // 2. Fallback to OpenStreetMap Nominatim API
                            if (!addressSet) {
                                try {
                                    const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${pos.lat}&lon=${pos.lng}`);
                                    if (response.ok) {
                                        const data = await response.json();
                                        if (data && data.display_name) {
                                            pickupInput.value = data.display_name;
                                            addressSet = true;
                                        }
                                    }
                                } catch (e) {
                                    console.warn("OSM Nominatim reverse geocode error:", e);
                                }
                            }

                            // 3. Fallback to lat/lng text if reverse geocode failed
                            if (!addressSet) {
                                pickupInput.value = `Current Location (${pos.lat.toFixed(4)}, ${pos.lng.toFixed(4)})`;
                            }
                            
                            pickupInput.dispatchEvent(new Event('input'));

                            locBtn.disabled = false;
                            locBtn.innerHTML = originalHTML;
                        },
                        (error) => {
                            locBtn.disabled = false;
                            locBtn.innerHTML = originalHTML;

                            if (error.code === error.PERMISSION_DENIED) {
                                alert("Location permission was denied. Please allow location access in your browser settings or enter your address manually.");
                            } else {
                                alert("Unable to retrieve your location automatically. Please enter your address manually.");
                            }
                        },
                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                    );
                });
            }
        });
    </script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('rideBooking', () => ({
                vehicle_type: '{{ request("type", "") }}', 
                schedule_type: 'now',
                pickup: '',
                dropoff: '',
                phone: '{{ auth()->user()->phone ?? "" }}',
                stops: [],
                savedLocations: { home: null, office: null },
                showSavedLocationModal: false,
                editingLabel: 'home',
                modalTempLocation: { location: '', lat: null, lng: null, place_id: null, isSelected: false },
                modalSuggestions: [],
                showModalSuggestions: false,
                riderType: 'me',
                showRiderModal: false,
                passengerName: '',
                passengerPhone: '',
                addStop() {
                    this.stops.push({ id: Date.now() + Math.random(), location: '', suggestions: [], showSuggestions: false });
                },
                removeStop(idx) {
                    this.stops.splice(idx, 1);
                },
                async searchStopLocation(stop) {
                    if (!stop.location || stop.location.trim().length < 3) {
                        stop.suggestions = [];
                        stop.showSuggestions = false;
                        return;
                    }
                    try {
                        const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(stop.location)}&limit=5`);
                        if (res.ok) {
                            const data = await res.json();
                            stop.suggestions = data;
                            stop.showSuggestions = data.length > 0;
                        }
                    } catch (e) {
                        console.warn('Map search error:', e);
                    }
                },
                get showRides() { 
                    return !this.isConfirming && this.pickup.trim().length > 0 && this.dropoff.trim().length > 0; 
                },
                isConfirming: false,
                paymentModal: false,
                paymentMethod: 'cash',
                profileType: 'Personal',
                selectedFare: '$28.50',
                
                // Lifecycle tracking
                rideId: null,
                rideStatus: 'pending', // pending, accepted, en_route, arrived, in_progress, completed, failed
                driverName: '',
                driverPhoto: '',
                driverRating: 5.0,
                driverPlate: '',
                driverModel: '',
                pollingUrl: null,
                pollingTimer: null,
                
                // Review
                showReviewModal: false,
                reviewRating: 0,
                reviewComment: '',
                reviewSubmitted: false,
                
                init() {
                    const urlParams = new URLSearchParams(window.location.search);
                    const resumeId = urlParams.get('resume');
                    if (resumeId) {
                        this.rideId = resumeId;
                        this.pollingUrl = `/api/ride/${resumeId}/status`;
                        this.isConfirming = true;
                        this.startPolling();
                    }
                    this.fetchSavedLocations();
                },

                async fetchSavedLocations() {
                    try {
                        const res = await fetch('/api/user/saved-locations');
                        if (res.ok) {
                            const data = await res.json();
                            if (data.success && data.locations) {
                                this.savedLocations.home = data.locations.home || null;
                                this.savedLocations.office = data.locations.office || null;
                            }
                        }
                    } catch (e) {
                        console.warn('Error fetching saved locations:', e);
                    }
                },

                useSavedLocation(label) {
                    const saved = this.savedLocations[label];
                    if (saved && saved.address) {
                        if (this.stops.length < 5) {
                            this.stops.push({
                                id: Date.now() + Math.random(),
                                location: saved.address,
                                lat: saved.latitude ? parseFloat(saved.latitude) : null,
                                lng: saved.longitude ? parseFloat(saved.longitude) : null,
                                place_id: saved.place_id || null,
                                isSelected: true,
                                suggestions: [],
                                showSuggestions: false
                            });
                        }
                    } else {
                        this.openSavedLocationModal(label);
                    }
                },

                openSavedLocationModal(label) {
                    this.editingLabel = label;
                    const existing = this.savedLocations[label];
                    if (existing) {
                        this.modalTempLocation = {
                            location: existing.address || '',
                            lat: existing.latitude ? parseFloat(existing.latitude) : null,
                            lng: existing.longitude ? parseFloat(existing.longitude) : null,
                            place_id: existing.place_id || null,
                            isSelected: true
                        };
                    } else {
                        this.modalTempLocation = { location: '', lat: null, lng: null, place_id: null, isSelected: false };
                    }
                    this.modalSuggestions = [];
                    this.showModalSuggestions = false;
                    this.showSavedLocationModal = true;
                },

                async searchModalLocation() {
                    this.modalTempLocation.isSelected = false;
                    if (!this.modalTempLocation.location || this.modalTempLocation.location.trim().length < 3) {
                        this.modalSuggestions = [];
                        this.showModalSuggestions = false;
                        return;
                    }
                    try {
                        const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.modalTempLocation.location)}&limit=5`);
                        if (res.ok) {
                            const data = await res.json();
                            this.modalSuggestions = data;
                            this.showModalSuggestions = data.length > 0;
                        }
                    } catch (e) {
                        console.warn('Modal map search error:', e);
                    }
                },

                selectModalSuggestion(item) {
                    this.modalTempLocation.location = item.display_name;
                    this.modalTempLocation.lat = parseFloat(item.lat);
                    this.modalTempLocation.lng = parseFloat(item.lon);
                    this.modalTempLocation.place_id = item.place_id ? String(item.place_id) : null;
                    this.modalTempLocation.isSelected = true;
                    this.showModalSuggestions = false;
                },

                async saveLocationFromModal() {
                    if (!this.modalTempLocation.location || !this.modalTempLocation.isSelected) {
                        alert('Please select a valid location suggestion from the map dropdown.');
                        return;
                    }
                    try {
                        const csrfToken = '{{ csrf_token() }}';
                        const res = await fetch('/api/user/saved-locations', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                label: this.editingLabel,
                                address: this.modalTempLocation.location,
                                latitude: this.modalTempLocation.lat,
                                longitude: this.modalTempLocation.lng,
                                place_id: this.modalTempLocation.place_id
                            })
                        });
                        if (res.ok) {
                            const data = await res.json();
                            if (data.success && data.location) {
                                this.savedLocations[this.editingLabel] = data.location;
                                this.showSavedLocationModal = false;
                                
                                if (this.stops.length < 5) {
                                    this.stops.push({
                                        id: Date.now() + Math.random(),
                                        location: data.location.address,
                                        lat: data.location.latitude ? parseFloat(data.location.latitude) : null,
                                        lng: data.location.longitude ? parseFloat(data.location.longitude) : null,
                                        place_id: data.location.place_id || null,
                                        isSelected: true,
                                        suggestions: [],
                                        showSuggestions: false
                                    });
                                }
                            }
                        } else {
                            const errData = await res.json().catch(() => ({}));
                            alert(errData.message || 'Failed to save location. Please try again.');
                        }
                    } catch (e) {
                        console.error('Error saving location:', e);
                        alert('An error occurred while saving the location.');
                    }
                },
                
                async submitBooking() {
                    const activePhone = (this.riderType === 'someone_else' && this.passengerPhone.trim()) ? this.passengerPhone : this.phone;
                    if (!activePhone || activePhone.trim().length < 5) {
                        alert('A valid Mobile Phone Number is required to book a ride request.');
                        return;
                    }

                    this.isConfirming = true;
                    this.rideStatus = 'pending';
                    document.querySelectorAll('.pac-container').forEach(el => el.style.display = 'none');
                    document.activeElement.blur();
                    
                    try {
                        const csrfToken = document.querySelector('input[name="_token"]').value;
                        const response = await fetch('/ride/book', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                pickup_location: this.pickup,
                                dropoff_location: this.dropoff,
                                phone_number: activePhone,
                                passenger_phone: activePhone,
                                is_for_someone_else: this.riderType === 'someone_else' ? 1 : 0,
                                passenger_name: this.passengerName,
                                stops: this.stops.map(s => s.location).filter(l => l && l.trim().length > 0),
                                vehicle_type: this.vehicle_type,
                                payment_method: this.paymentMethod,
                                amount: parseFloat(this.selectedFare.replace('$', '')) || 28.50,
                                schedule_type: this.schedule_type
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (!response.ok || data.error) {
                            alert('Booking error: ' + (data.error || data.message || 'Unknown server error'));
                            this.isConfirming = false;
                            return;
                        }
                        
                        this.rideId = data.ride_id;
                        this.pollingUrl = data.polling_url;
                        
                        const m = (this.paymentMethod || '').toLowerCase();
                        if (m === 'stripe' || m === 'card' || m === 'credit_card' || m === 'credit card' || data.stripe_client_secret || data.redirect_url) {
                            window.location.href = data.redirect_url || ('/payment/verify-details/ride/' + data.ride_id);
                            return;
                        }
                        
                        // Start polling for status updates
                        this.startPolling();
                        
                    } catch (error) {
                        console.error('Error booking ride:', error);
                        alert('Booking failed: ' + error.message);
                        this.isConfirming = false;
                    }
                },
                
                startPolling() {
                    if (this.pollingTimer) clearInterval(this.pollingTimer);
                    this.pollStatus();
                    this.pollingTimer = setInterval(() => this.pollStatus(), 3000);
                },
                
                async pollStatus() {
                    if (!this.isConfirming || !this.pollingUrl) return;
                    try {
                        const res = await fetch(this.pollingUrl);
                        const data = await res.json();
                        this.rideStatus = data.status;
                        if (data.driver_name) this.driverName = data.driver_name;
                        if (data.driver) {
                            this.driverName = data.driver.name || this.driverName;
                            this.driverPhoto = data.driver.photo_url || '';
                            this.driverRating = data.driver.rating || 5.0;
                            this.driverPlate = data.driver.vehicle_plate || '';
                            this.driverModel = data.driver.vehicle_model || '';
                        }
                        if (data.has_review) this.reviewSubmitted = true;
                        
                        // Sync Fare, Locations, and Payment Method from database
                        if (data.fare) {
                            this.selectedFare = '$' + parseFloat(data.fare).toFixed(2);
                        } else if (!this.selectedFare || this.selectedFare === '$0.00') {
                            this.selectedFare = '$28.50';
                        }
                        if (data.pickup && (!this.pickup || this.pickup === '')) {
                            this.pickup = data.pickup;
                        }
                        if (data.dropoff && (!this.dropoff || this.dropoff === '')) {
                            this.dropoff = data.dropoff;
                        }
                        if (data.payment_method) {
                            this.paymentMethod = data.payment_method;
                        }
                        
                        if (data.status === 'failed' || data.status === 'cancelled') {
                            clearInterval(this.pollingTimer);
                            if (data.status === 'failed') alert('No drivers available right now. Please try again later.');
                            this.isConfirming = false;
                            this.rideId = null;
                        }
                        if (data.status === 'completed') {
                            clearInterval(this.pollingTimer);
                            if (!data.has_review) this.showReviewModal = true;
                        }
                    } catch (e) {
                        console.error('Polling error', e);
                    }
                },
                
                async submitReview() {
                    if (this.reviewRating < 1) return;
                    try {
                        const csrfToken = document.querySelector('input[name="_token"]').value;
                        const res = await fetch(`/api/ride/${this.rideId}/review`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({ rating: this.reviewRating, comment: this.reviewComment })
                        });
                        if (res.ok) {
                            this.reviewSubmitted = true;
                            this.showReviewModal = false;
                        }
                    } catch (e) {
                        console.error('Review error', e);
                    }
                },
                
                async cancelRide() {
                    if (this.pollingTimer) clearInterval(this.pollingTimer);
                    if (this.rideId) {
                        try {
                            const csrfToken = document.querySelector('input[name="_token"]')?.value;
                            await fetch(`/api/ride/${this.rideId}/cancel`, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
                            });
                        } catch(e) {}
                    }
                    this.isConfirming = false;
                    this.rideStatus = 'pending';
                    this.rideId = null;
                }
            }));
        });
    </script>

    <x-stripe-modal serviceType="ride" />
</x-layout>