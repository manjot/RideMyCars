<x-layout theme="theme-delivery">
    <x-slot:title>Package Delivery — RideMyCars Express Parcel Dispatch</x-slot>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10" x-data="packageDeliveryBooking">

        <!-- Category Banner Component -->
        <x-category-banner category="Delivery" />

        <!-- Page Header & Official Accra-Tema Campaign Banner -->
        <div class="mb-10 space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="px-3 py-1 rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 font-extrabold text-xs uppercase tracking-wider border border-amber-200 dark:border-amber-800/30">RideMyCars Parcel Dispatch</span>
                    <h1 class="text-3xl md:text-4xl font-black text-gray-900 dark:text-white mt-1 tracking-tight">On-Demand & Scheduled Parcel Delivery</h1>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Fast, secure door-to-door courier delivery for documents, electronics, supplies & personal items.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="tel:0559776761" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-2xl shadow-md transition-all shrink-0">
                        <span>📞</span>
                        <span>Call Dispatch: 0559776761</span>
                    </a>
                    <a href="/admin/package-delivery-tracker" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-extrabold text-xs rounded-2xl shadow-md transition-all shrink-0">
                        <span>🚚</span>
                        <span>Live Courier Tracker</span>
                    </a>
                </div>
            </div>

            <!-- Official Accra & Tema Delivery Showcase Banner -->
            <div class="rounded-3xl bg-gradient-to-r from-amber-500/10 via-orange-500/5 to-purple-500/10 border-2 border-amber-300/80 dark:border-amber-500/30 p-6 sm:p-8 shadow-xl relative overflow-hidden"
                 x-data="{ showDeliveryPosterModal: false }">
                <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-amber-400/15 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    <!-- Left: Promo Copy & Highlights -->
                    <div class="lg:col-span-8 space-y-4">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white dark:bg-white/10 text-gray-900 dark:text-white font-bold text-xs shadow-xs border border-gray-200 dark:border-white/10">
                            <span class="text-base">🇬🇭</span>
                            <span>Official Service: Accra & Tema Metropolitan Zone</span>
                        </div>

                        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black text-gray-950 dark:text-white tracking-tight leading-tight">
                            Same Day Express Delivery across <span class="text-amber-600 dark:text-amber-400">Accra & Tema</span>
                        </h2>

                        <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed font-medium">
                            Motorcycle couriers and branded delivery vans ready 24/7. Trusted by thousands across Greater Accra for business logistics, retail parcels, documents, and personal deliveries.
                        </p>

                        <!-- Key Pillars Grid from Flyer -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
                            <div class="p-3 rounded-2xl bg-white dark:bg-[#181818] border border-amber-200/60 dark:border-white/10 text-center shadow-xs">
                                <span class="text-xl block mb-1">⚡</span>
                                <span class="text-xs font-black text-gray-900 dark:text-white block">Same Day</span>
                                <span class="text-[10px] text-gray-500">Fast pickup</span>
                            </div>
                            <div class="p-3 rounded-2xl bg-white dark:bg-[#181818] border border-amber-200/60 dark:border-white/10 text-center shadow-xs">
                                <span class="text-xl block mb-1">📍</span>
                                <span class="text-xs font-black text-gray-900 dark:text-white block">GPS Tracked</span>
                                <span class="text-[10px] text-gray-500">Live turn-by-turn</span>
                            </div>
                            <div class="p-3 rounded-2xl bg-white dark:bg-[#181818] border border-amber-200/60 dark:border-white/10 text-center shadow-xs">
                                <span class="text-xl block mb-1">👛</span>
                                <span class="text-xs font-black text-gray-900 dark:text-white block">Smart Wallet</span>
                                <span class="text-[10px] text-gray-500">Seamless credit</span>
                            </div>
                            <div class="p-3 rounded-2xl bg-white dark:bg-[#181818] border border-amber-200/60 dark:border-white/10 text-center shadow-xs">
                                <span class="text-xl block mb-1">📱</span>
                                <span class="text-xs font-black text-gray-900 dark:text-white block">Instant MoMo</span>
                                <span class="text-[10px] text-gray-500">MTN & Telecel</span>
                            </div>
                        </div>

                        <!-- CTA row -->
                        <div class="flex flex-wrap items-center gap-3 pt-3">
                            <a href="tel:0559776761" class="inline-flex items-center gap-2 px-5 py-3 bg-amber-500 hover:bg-amber-600 text-gray-950 font-black text-xs rounded-xl shadow-md transition-all">
                                <span>📞 Direct Line: 0559776761</span>
                            </a>
                            <button type="button" 
                                    @click="showDeliveryPosterModal = true"
                                    class="inline-flex items-center gap-2 px-4 py-3 bg-white dark:bg-white/10 hover:bg-gray-100 dark:hover:bg-white/20 text-gray-900 dark:text-white font-bold text-xs rounded-xl border border-gray-200 dark:border-white/10 transition-all cursor-pointer">
                                <span>🔍 View Official Campaign Poster</span>
                            </button>
                        </div>
                    </div>

                    <!-- Right: Poster Visual -->
                    <div class="lg:col-span-4 flex justify-center">
                        <div class="relative group cursor-pointer max-w-[260px] rounded-2xl overflow-hidden shadow-2xl border-2 border-amber-400 dark:border-amber-500/40"
                             @click="showDeliveryPosterModal = true">
                            <img src="{{ asset('images/promo-delivery-accra.jpg') }}" 
                                 alt="Fast Package Delivery Accra & Tema Flyer" 
                                 class="w-full h-auto object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute inset-0 bg-black/30 group-hover:bg-black/10 transition-colors flex items-center justify-center">
                                <span class="px-3 py-1.5 rounded-full bg-black/70 backdrop-blur-sm text-white font-bold text-xs opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1.5 shadow-lg">
                                    🔍 Click to Enlarge
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Poster Modal Lightbox -->
                <template x-teleport="body">
                    <div x-show="showDeliveryPosterModal" 
                         style="display: none;"
                         class="fixed inset-0 z-[999999] flex items-center justify-center p-4 bg-black/80 backdrop-blur-md"
                         @click.self="showDeliveryPosterModal = false"
                         @keydown.escape.window="showDeliveryPosterModal = false">
                        <div class="relative max-w-xl w-full bg-white dark:bg-[#181818] rounded-3xl p-4 shadow-2xl border border-amber-300">
                            <button type="button" 
                                    @click="showDeliveryPosterModal = false"
                                    class="absolute -top-3 -right-3 w-9 h-9 rounded-full bg-black text-white flex items-center justify-center text-sm font-bold shadow-lg">
                                ✕
                            </button>
                            <img src="{{ asset('images/promo-delivery-accra.jpg') }}" 
                                 alt="Fast Package Delivery Accra & Tema" 
                                 class="w-full h-auto rounded-2xl object-contain max-h-[85vh]">
                        </div>
                    </div>
                </template>
            </div>
        </div>

        @if($isUnsupportedRegion ?? false)
        <div class="mb-6 p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/25 flex items-center gap-2.5 text-xs text-amber-800 dark:text-amber-300">
            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/>
                <path d="M2 12h20"/>
            </svg>
            <span>Detected Region: <strong>{{ $detectedLocationName ?? 'Your Region' }}</strong>. We do not support your local currency right now, so you need to pay in <strong>USD ($)</strong>.</span>
        </div>
        @endif

        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800/30 text-emerald-800 dark:text-emerald-200 text-xs font-bold flex items-center gap-2">
                <span>📦 {{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800/30 text-rose-800 dark:text-rose-200 text-xs font-bold space-y-1">
                @foreach($errors->all() as $error)
                    <p>• {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- 6-Step Wizard Navigation Tabs -->
        <div class="mb-8 overflow-x-auto pb-2">
            <div class="flex items-center gap-2 min-w-max bg-white dark:bg-[#111] p-2 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm text-xs font-extrabold">
                <button type="button" @click="currentStep = 1" :class="currentStep === 1 ? 'bg-amber-500 text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'" class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-1.5">
                    <span>1.</span> 📍 Pickup & Drop
                </button>
                <span class="text-gray-300 dark:text-gray-700">→</span>
                <button type="button" @click="currentStep = 2" :class="currentStep === 2 ? 'bg-amber-500 text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'" class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-1.5">
                    <span>2.</span> ⏱️ Delivery Type
                </button>
                <span class="text-gray-300 dark:text-gray-700">→</span>
                <button type="button" @click="currentStep = 3" :class="currentStep === 3 ? 'bg-amber-500 text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'" class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-1.5">
                    <span>3.</span> 👤 Sender & Recipient
                </button>
                <span class="text-gray-300 dark:text-gray-700">→</span>
                <button type="button" @click="currentStep = 4" :class="currentStep === 4 ? 'bg-amber-500 text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'" class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-1.5">
                    <span>4.</span> 📦 Package Specs
                </button>
                <span class="text-gray-300 dark:text-gray-700">→</span>
                <button type="button" @click="currentStep = 5" :class="currentStep === 5 ? 'bg-amber-500 text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'" class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-1.5">
                    <span>5.</span> 💳 Price & Payment
                </button>
                <span class="text-gray-300 dark:text-gray-700">→</span>
                <button type="button" @click="currentStep = 6" :class="currentStep === 6 ? 'bg-amber-500 text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'" class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-1.5">
                    <span>6.</span> 🚀 Confirmation
                </button>
            </div>
        </div>

        <form action="/delivery/book" method="POST" @submit.prevent="submitDeliveryForm($event)" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            @csrf
            <input type="hidden" name="pickup_lat" x-model="pickupLat" id="pickup_lat_input">
            <input type="hidden" name="pickup_lng" x-model="pickupLng" id="pickup_lng_input">
            <input type="hidden" name="dropoff_lat" x-model="dropoffLat" id="dropoff_lat_input">
            <input type="hidden" name="dropoff_lng" x-model="dropoffLng" id="dropoff_lng_input">
            <input type="hidden" name="country" value="{{ $currentCountryCode ?? 'USA' }}">

            <!-- Left & Middle: Step Form Container -->
            <div class="lg:col-span-2 space-y-6">

                <!-- STEP 1: PICKUP & DROP-OFF -->
                <div x-show="currentStep === 1" class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 md:p-8 shadow-sm space-y-6">
                    <div>
                        <h2 class="text-xl font-black text-gray-900 dark:text-white">STEP 1: Pickup & Destination Locations</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Specify where your parcel should be picked up and delivered.</p>
                    </div>

                    <!-- Pickup Address -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Pickup Address *</label>
                            <button type="button" id="use_my_location_btn_delivery" class="text-amber-500 hover:text-amber-600 text-xs font-extrabold flex items-center gap-1 transition-colors">
                                📍 Use My Location
                            </button>
                        </div>
                        <div class="relative">
                            <input type="text" id="pickup_location_input" name="pickup_location" x-model="pickupLocation" required placeholder="Enter street address, building, or landmark..." class="w-full px-4 py-3.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-xs font-bold text-gray-900 dark:text-white">
                        </div>
                    </div>

                    <!-- Drop-off Address -->
                    <div class="space-y-2">
                        <label class="block text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Drop-off / Destination Address *</label>
                        <div class="relative">
                            <input type="text" id="dropoff_location_input" name="dropoff_location" x-model="dropoffLocation" required placeholder="Enter recipient delivery address..." class="w-full px-4 py-3.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-xs font-bold text-gray-900 dark:text-white">
                        </div>
                    </div>

                    <!-- Map Preview Box -->
                    <div class="bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl h-56 overflow-hidden relative">
                        <div id="map" class="w-full h-full"></div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="button" @click="currentStep = 2" class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs rounded-xl shadow-md transition-all">
                            Next: Delivery Type & Schedule →
                        </button>
                    </div>
                </div>

                <!-- STEP 2: DELIVERY TYPE & SCHEDULE -->
                <div x-show="currentStep === 2" class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 md:p-8 shadow-sm space-y-6">
                    <div>
                        <h2 class="text-xl font-black text-gray-900 dark:text-white">STEP 2: Delivery Type & Schedule</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Select dispatch speed and schedule window.</p>
                    </div>

                    <!-- Delivery Type Options (Three Parcel Style) -->
                    <div class="space-y-3">
                        <label class="block text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Delivery Speed Option *</label>
                        <input type="hidden" name="delivery_type" x-model="deliveryType">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs font-bold">
                            <template x-for="dt in [
                                { name: 'Hyperlocal', desc: '🛵 City Local Bike Courier' },
                                { name: 'Scheduled', desc: '🕒 Pick your exact time window' },
                                { name: 'Same Day', desc: '📅 Delivered by end of today' },
                                { name: 'Express', desc: '🚀 Priority Direct Route (< 2 hrs)' },
                                { name: 'Instant', desc: '⚡ Immediate Courier Pickup (~30 mins)' }
                            ]" :key="dt.name">
                                <div @click="deliveryType = dt.name"
                                     :class="deliveryType === dt.name ? 'border-amber-500 bg-amber-50/40 dark:bg-amber-950/20' : 'border-gray-200 dark:border-white/10 hover:border-amber-300'"
                                     class="border-2 rounded-2xl p-4 cursor-pointer transition-all flex items-start justify-between gap-2">
                                    <div>
                                        <h4 class="font-extrabold text-sm text-gray-900 dark:text-white" x-text="dt.name"></h4>
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 font-medium mt-0.5" x-text="dt.desc"></p>
                                    </div>
                                    <span class="text-xs font-black text-amber-600 dark:text-amber-400" x-text="getSpeedOptionFee(dt.name)"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Now vs Schedule Later -->
                    <div class="space-y-3 pt-3 border-t border-gray-100 dark:border-white/10">
                        <label class="block text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Dispatch Schedule *</label>
                        <input type="hidden" name="schedule_mode" x-model="scheduleMode">

                        <div class="grid grid-cols-2 gap-3">
                            <button type="button" @click="scheduleMode = 'now'"
                                    :class="scheduleMode === 'now' ? 'bg-amber-500 text-white font-extrabold shadow-sm' : 'bg-gray-50 dark:bg-[#1a1a1a] text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-white/10 font-bold'"
                                    class="py-3 px-4 rounded-xl text-xs transition-all text-center">
                                ⚡ Deliver Now (Immediate)
                            </button>
                            <button type="button" @click="scheduleMode = 'later'"
                                    :class="scheduleMode === 'later' ? 'bg-amber-500 text-white font-extrabold shadow-sm' : 'bg-gray-50 dark:bg-[#1a1a1a] text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-white/10 font-bold'"
                                    class="py-3 px-4 rounded-xl text-xs transition-all text-center">
                                📅 Schedule for Later
                            </button>
                        </div>

                        <div x-show="scheduleMode === 'later'" class="grid grid-cols-2 gap-3 pt-2">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Pickup Date *</label>
                                <input type="date" name="pickup_date" x-model="pickupDate" min="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-bold text-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Pickup Time *</label>
                                <input type="time" name="pickup_time" x-model="pickupTime" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-bold text-gray-900 dark:text-white">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between pt-2">
                        <button type="button" @click="currentStep = 1" class="px-5 py-2.5 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 font-extrabold text-xs rounded-xl">
                            ← Back
                        </button>
                        <button type="button" @click="currentStep = 3" class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs rounded-xl shadow-md">
                            Next: Sender & Recipient →
                        </button>
                    </div>
                </div>

                <!-- STEP 3: SENDER & RECIPIENT -->
                <div x-show="currentStep === 3" class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 md:p-8 shadow-sm space-y-6">
                    <div>
                        <h2 class="text-xl font-black text-gray-900 dark:text-white">STEP 3: Sender & Recipient Details</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Provide contact details for parcel pickup & delivery notification.</p>
                    </div>

                    <!-- Sender Box -->
                    <div class="p-4 bg-gray-50 dark:bg-[#1a1a1a] rounded-2xl border border-gray-200 dark:border-white/10 space-y-3">
                        <h3 class="font-extrabold text-sm text-gray-900 dark:text-white uppercase tracking-wider">📤 Sender Information</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            <div>
                                <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Sender Full Name *</label>
                                <input type="text" name="sender_name" x-model="senderName" required class="w-full px-3.5 py-2.5 bg-white dark:bg-[#111] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Sender Phone Number *</label>
                                <input type="tel" name="sender_phone" x-model="senderPhone" required class="w-full px-3.5 py-2.5 bg-white dark:bg-[#111] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white">
                            </div>
                        </div>
                    </div>

                    <!-- Recipient Box -->
                    <div class="p-4 bg-amber-50/40 dark:bg-amber-950/20 rounded-2xl border border-amber-200 dark:border-amber-800/30 space-y-3">
                        <h3 class="font-extrabold text-sm text-amber-800 dark:text-amber-300 uppercase tracking-wider">📥 Recipient Information</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            <div>
                                <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Recipient Full Name *</label>
                                <input type="text" name="recipient_name" x-model="recipientName" required class="w-full px-3.5 py-2.5 bg-white dark:bg-[#111] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Recipient Phone Number (For PIN SMS) *</label>
                                <input type="tel" name="recipient_phone" x-model="recipientPhone" required class="w-full px-3.5 py-2.5 bg-white dark:bg-[#111] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white">
                            </div>
                        </div>

                        <div class="text-xs">
                            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Delivery Notes / Instructions (Optional)</label>
                            <textarea name="delivery_instructions" x-model="deliveryInstructions" rows="2" placeholder="Gate code, call before arrival, leave at reception..." class="w-full px-3.5 py-2 bg-white dark:bg-[#111] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white resize-none"></textarea>
                        </div>
                    </div>

                    <div class="flex justify-between pt-2">
                        <button type="button" @click="currentStep = 2" class="px-5 py-2.5 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 font-extrabold text-xs rounded-xl">
                            ← Back
                        </button>
                        <button type="button" @click="currentStep = 4" class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs rounded-xl shadow-md">
                            Next: Package Specifications →
                        </button>
                    </div>
                </div>

                <!-- STEP 4: PACKAGE DETAILS & SPECS -->
                <div x-show="currentStep === 4" class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 md:p-8 shadow-sm space-y-6">
                    <div>
                        <h2 class="text-xl font-black text-gray-900 dark:text-white">STEP 4: Package Category & Specifications</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Specify parcel category, size, weight, and handling rules.</p>
                    </div>

                    <!-- Category Pills -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Package Category *</label>
                            <span x-show="selectedCategoryRequiresRx" class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                                <span>💊 Doctor Prescription Required</span>
                            </span>
                        </div>
                        <input type="hidden" name="package_category" x-model="packageCategory">

                        <div class="flex flex-wrap gap-2 text-xs font-bold">
                            <template x-for="cat in availableCategories" :key="cat.name">
                                <button type="button" @click="selectCategory(cat.name)"
                                        :class="packageCategory === cat.name ? (cat.requires_prescription || cat.name === 'Pharmeasy' ? 'bg-emerald-600 text-white shadow-md ring-2 ring-emerald-500/50' : 'bg-amber-500 text-white shadow-sm') : 'bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 hover:bg-gray-200'"
                                        class="py-2 px-3.5 rounded-xl transition-all flex items-center gap-1.5">
                                    <span x-show="cat.icon" x-text="cat.icon"></span>
                                    <span x-text="cat.name"></span>
                                    <span x-show="cat.badge_text" x-text="cat.badge_text" class="px-1.5 py-0.2 rounded-md bg-white/20 text-[9px] uppercase font-black tracking-wider"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- MANDATORY DOCTOR PRESCRIPTION UPLOAD (Pharmeasy / Rx Categories) -->
                    <div x-show="selectedCategoryRequiresRx" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform -translate-y-2"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         class="p-5 bg-gradient-to-br from-emerald-50/90 via-teal-50/50 to-white dark:from-emerald-950/30 dark:via-teal-950/20 dark:to-[#161616] rounded-2xl border-2 border-emerald-500/40 shadow-sm space-y-4">
                        
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-emerald-200/60 dark:border-emerald-800/40 pb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-black text-sm shadow-sm">
                                    Rx
                                </div>
                                <div>
                                    <h3 class="text-sm font-black text-gray-900 dark:text-white flex items-center gap-2">
                                        Upload Doctor Prescription
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-red-500 text-white uppercase tracking-wider">Mandatory</span>
                                    </h3>
                                    <p class="text-[11px] text-gray-600 dark:text-gray-300 mt-0.5">
                                        Please provide a valid prescription from a registered medical practitioner.
                                    </p>
                                </div>
                            </div>
                            <span class="text-[11px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-900/60 px-2.5 py-1 rounded-lg">
                                JPG, PNG, PDF (Max 10MB)
                            </span>
                        </div>

                        <!-- Validation Error Display -->
                        <div x-show="prescriptionError" 
                             x-cloak
                             class="p-3 bg-red-50 dark:bg-red-950/50 border border-red-300 dark:border-red-800 rounded-xl text-xs text-red-700 dark:text-red-300 flex items-start gap-2 animate-shake">
                            <span class="text-base leading-none">⚠️</span>
                            <span class="font-bold flex-1" x-text="prescriptionError"></span>
                        </div>

                        <!-- Drag and Drop / File Input Box -->
                        <div @dragover.prevent="isDraggingPrescription = true"
                             @dragleave.prevent="isDraggingPrescription = false"
                             @drop.prevent="isDraggingPrescription = false; handlePrescriptionDrop($event)"
                             :class="isDraggingPrescription ? 'border-emerald-500 bg-emerald-100/50 dark:bg-emerald-900/40' : 'border-gray-300 dark:border-gray-700 hover:border-emerald-400 bg-white dark:bg-[#111]'"
                             class="border-2 border-dashed rounded-2xl p-6 text-center transition cursor-pointer relative group">
                            
                            <input type="file" 
                                   id="prescriptionFileInput"
                                   multiple 
                                   accept=".jpg,.jpeg,.png,.pdf" 
                                   @change="handlePrescriptionFiles($event)"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">

                            <div class="flex flex-col items-center justify-center space-y-2 pointer-events-none">
                                <div class="w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                                    📸
                                </div>
                                <div class="text-xs">
                                    <span class="font-black text-emerald-600 dark:text-emerald-400 hover:underline">Click to upload</span> 
                                    <span class="text-gray-600 dark:text-gray-400 font-semibold">or drag and drop your prescription here</span>
                                </div>
                                <p class="text-[10px] text-gray-500 dark:text-gray-400">
                                    Camera capture, gallery images, or multi-page PDF documents accepted
                                </p>
                            </div>
                        </div>

                        <!-- Upload Spinner -->
                        <div x-show="isUploadingPrescription" class="flex items-center justify-center gap-2 p-3 text-xs font-bold text-emerald-700 dark:text-emerald-300">
                            <svg class="animate-spin h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Uploading and verifying prescription document...</span>
                        </div>

                        <!-- Uploaded Files Preview Cards -->
                        <div x-show="prescriptionFiles.length > 0" class="space-y-2 pt-1">
                            <div class="flex items-center justify-between text-xs font-bold text-gray-700 dark:text-gray-300">
                                <span class="flex items-center gap-1.5">
                                    <span>Uploaded Prescriptions</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-emerald-500 text-white font-extrabold" x-text="prescriptionFiles.length"></span>
                                </span>
                                <span class="text-[11px] text-gray-400">Click preview to zoom / full screen</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <template x-for="(file, idx) in prescriptionFiles" :key="file.id || idx">
                                    <div class="flex items-center justify-between p-2.5 bg-white dark:bg-[#121212] rounded-xl border border-gray-200 dark:border-white/10 hover:border-emerald-500/50 shadow-sm transition">
                                        <div class="flex items-center gap-2.5 min-w-0 cursor-pointer flex-1" @click="openPreview(file)">
                                            <!-- Thumbnail / Icon -->
                                            <div class="w-12 h-12 rounded-lg bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 overflow-hidden flex items-center justify-center flex-shrink-0">
                                                <template x-if="file.isImage">
                                                    <img :src="file.viewUrl" alt="Rx Preview" class="w-full h-full object-cover">
                                                </template>
                                                <template x-if="file.isPdf">
                                                    <span class="text-xl">📄</span>
                                                </template>
                                            </div>

                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase"
                                                          :class="file.isPdf ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300'"
                                                          x-text="file.type"></span>
                                                    <h4 class="text-xs font-bold text-gray-900 dark:text-white truncate" x-text="file.name"></h4>
                                                </div>
                                                <p class="text-[10px] text-gray-400 mt-0.5">
                                                    <span x-text="file.size"></span> • <span x-text="'Uploaded ' + file.uploadedAt"></span>
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Actions -->
                                        <div class="flex items-center gap-1 pl-2">
                                            <button type="button" 
                                                    @click="openPreview(file)"
                                                    title="Zoom / View Full Screen"
                                                    class="p-1.5 text-gray-500 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 rounded-lg transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path></svg>
                                            </button>
                                            <button type="button" 
                                                    @click="removePrescription(idx)"
                                                    title="Remove Prescription"
                                                    class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40 rounded-lg transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                    </div>

                    <!-- Description & Value -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Package Description</label>
                            <input type="text" name="package_description" x-model="packageDescription" placeholder="e.g. Legal documents & laptop" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Declared Value ($)</label>
                            <input type="number" name="declared_value" min="0" x-model="declaredValue" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white">
                        </div>
                    </div>

                    <!-- Package Size Selector -->
                    <div class="space-y-2 pt-2 border-t border-gray-100 dark:border-white/10">
                        <label class="block text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Package Size *</label>
                        <input type="hidden" name="package_size" x-model="packageSize">

                        <div class="grid grid-cols-3 gap-3 text-center text-xs">
                            <div @click="packageSize = 'Small'; packageWeight = 1.5;"
                                 :class="packageSize === 'Small' ? 'border-amber-500 bg-amber-50/40 dark:bg-amber-950/20 font-black' : 'border-gray-200 dark:border-white/10'"
                                 class="border-2 rounded-2xl p-4 cursor-pointer transition-all">
                                <div class="text-3xl mb-1">✉️</div>
                                <h4 class="font-extrabold text-sm text-gray-900 dark:text-white">Small</h4>
                                <p class="text-[10px] text-gray-400">Up to 2 kg (Envelopes / Small Box)</p>
                            </div>

                            <div @click="packageSize = 'Medium'; packageWeight = 5.0;"
                                 :class="packageSize === 'Medium' ? 'border-amber-500 bg-amber-50/40 dark:bg-amber-950/20 font-black' : 'border-gray-200 dark:border-white/10'"
                                 class="border-2 rounded-2xl p-4 cursor-pointer transition-all">
                                <div class="text-3xl mb-1">📦</div>
                                <h4 class="font-extrabold text-sm text-gray-900 dark:text-white">Medium</h4>
                                <p class="text-[10px] text-gray-400">Up to 8 kg (Shoebox / Groceries)</p>
                            </div>

                            <div @click="packageSize = 'Large'; packageWeight = 15.0;"
                                 :class="packageSize === 'Large' ? 'border-amber-500 bg-amber-50/40 dark:bg-amber-950/20 font-black' : 'border-gray-200 dark:border-white/10'"
                                 class="border-2 rounded-2xl p-4 cursor-pointer transition-all">
                                <div class="text-3xl mb-1">🚚</div>
                                <h4 class="font-extrabold text-sm text-gray-900 dark:text-white">Large</h4>
                                <p class="text-[10px] text-gray-400">Up to 25 kg (Cartons / Heavy Items)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Weight & Quantity -->
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Weight (kg) *</label>
                            <input type="number" step="0.1" name="package_weight_kg" x-model="packageWeight" required class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Quantity *</label>
                            <input type="number" min="1" name="quantity" x-model="quantity" required class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl font-bold text-gray-900 dark:text-white">
                        </div>
                    </div>

                    <!-- Special Handling Checkboxes -->
                    <div class="p-4 bg-gray-50 dark:bg-[#1a1a1a] rounded-2xl border border-gray-200 dark:border-white/10 space-y-2 text-xs">
                        <label class="block font-extrabold text-gray-900 dark:text-white uppercase tracking-wider">Special Handling Options</label>
                        
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="special_handling[]" value="signature_required" @change="toggleHandling('signature_required')" checked class="w-4 h-4 text-amber-500 rounded border-gray-300">
                            <span class="font-bold text-gray-800 dark:text-gray-200">Signature required on delivery</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="special_handling[]" value="climate_control" @change="toggleHandling('climate_control')" class="w-4 h-4 text-amber-500 rounded border-gray-300">
                            <span class="font-bold text-gray-800 dark:text-gray-200">Climate-controlled transport (Temperature sensitive)</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="special_handling[]" value="discreet" @change="toggleHandling('discreet')" class="w-4 h-4 text-amber-500 rounded border-gray-300">
                            <span class="font-bold text-gray-800 dark:text-gray-200">Discreet white-glove packaging</span>
                        </label>
                    </div>

                    <div class="flex justify-between pt-2">
                        <button type="button" @click="currentStep = 3" class="px-5 py-2.5 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 font-extrabold text-xs rounded-xl">
                            ← Back
                        </button>
                        <button type="button" @click="proceedFromStep4()" class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs rounded-xl shadow-md flex items-center gap-2">
                            <span>Next: Price & Payment →</span>
                        </button>
                    </div>
                </div>

                <!-- STEP 5: PRICE & PAYMENT -->
                <div x-show="currentStep === 5" class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 md:p-8 shadow-sm space-y-6">
                    <div>
                        <h2 class="text-xl font-black text-gray-900 dark:text-white">STEP 5: Payment Method</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Select payment method for parcel dispatch.</p>
                    </div>

                    <!-- Payment Method Selector (Stripe, MoMo Pay & Cash) -->
                    <x-payment-method-selector modelName="paymentMethod" phoneModel="momoPhone" networkModel="momoNetwork" :allowCash="true" />

                    <div class="flex justify-between pt-2">
                        <button type="button" @click="currentStep = 4" class="px-5 py-2.5 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 font-extrabold text-xs rounded-xl">
                            ← Back
                        </button>
                        <button type="button" @click="currentStep = 6" class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs rounded-xl shadow-md">
                            Next: Summary & Confirmation →
                        </button>
                    </div>
                </div>

                <!-- STEP 6: CONFIRMATION SUMMARY -->
                <div x-show="currentStep === 6" class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 md:p-8 shadow-sm space-y-6">
                    <div>
                        <h2 class="text-xl font-black text-gray-900 dark:text-white">STEP 6: Confirm & Dispatch Parcel</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Review complete parcel order details before dispatching courier.</p>
                    </div>

                    <div class="space-y-4 text-xs font-semibold">
                        <div class="p-4 bg-gray-50 dark:bg-[#1a1a1a] rounded-2xl border border-gray-200 dark:border-white/10 space-y-2">
                            <h4 class="font-extrabold text-gray-900 dark:text-white uppercase">📍 Locations</h4>
                            <p><strong class="text-gray-900 dark:text-white">Pickup:</strong> <span x-text="pickupLocation || 'Pickup Address'"></span></p>
                            <p><strong class="text-gray-900 dark:text-white">Destination:</strong> <span x-text="dropoffLocation || 'Drop-off Address'"></span></p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="p-4 bg-gray-50 dark:bg-[#1a1a1a] rounded-2xl border border-gray-200 dark:border-white/10 space-y-1">
                                <h4 class="font-extrabold text-gray-900 dark:text-white uppercase">📤 Sender</h4>
                                <p x-text="senderName"></p>
                                <p x-text="senderPhone" class="text-gray-500"></p>
                            </div>
                            <div class="p-4 bg-gray-50 dark:bg-[#1a1a1a] rounded-2xl border border-gray-200 dark:border-white/10 space-y-1">
                                <h4 class="font-extrabold text-gray-900 dark:text-white uppercase">📥 Recipient</h4>
                                <p x-text="recipientName"></p>
                                <p x-text="recipientPhone" class="text-gray-500"></p>
                            </div>
                        </div>

                        <div class="p-4 bg-gray-50 dark:bg-[#1a1a1a] rounded-2xl border border-gray-200 dark:border-white/10 space-y-1">
                            <h4 class="font-extrabold text-gray-900 dark:text-white uppercase">📦 Package Details</h4>
                            <p><strong class="text-gray-900 dark:text-white">Category:</strong> <span x-text="packageCategory"></span> (<span x-text="packageSize"></span> Size, <span x-text="packageWeight"></span> kg)</p>
                            <p><strong class="text-gray-900 dark:text-white">Speed:</strong> <span x-text="deliveryType"></span> Delivery</p>
                        </div>

                        <!-- Doctor Prescriptions Summary -->
                        <template x-if="selectedCategoryRequiresRx">
                            <div class="p-4 bg-emerald-50/80 dark:bg-emerald-950/30 rounded-2xl border border-emerald-300 dark:border-emerald-800/50 space-y-2">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-extrabold text-emerald-900 dark:text-emerald-200 uppercase flex items-center gap-1.5 text-xs">
                                        <span>💊 Doctor Prescriptions Attached</span>
                                    </h4>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500 text-white" x-text="prescriptionFiles.length + ' Document' + (prescriptionFiles.length > 1 ? 's' : '')"></span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                                    <template x-for="(file, idx) in prescriptionFiles" :key="idx">
                                        <div class="flex items-center gap-2 p-2 bg-white dark:bg-[#111] rounded-xl border border-emerald-200/60 dark:border-emerald-800/40 text-xs">
                                            <span class="text-base" x-text="file.isPdf ? '📄' : '🖼️'"></span>
                                            <div class="min-w-0 flex-1">
                                                <p class="font-bold text-gray-900 dark:text-white truncate" x-text="file.name"></p>
                                                <p class="text-[10px] text-gray-400" x-text="file.type + ' • ' + file.size"></p>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Prohibited Consignment Declaration Box -->
                        <div class="p-4 bg-red-50/70 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 rounded-2xl space-y-2 text-xs">
                            <h4 class="font-extrabold text-red-900 dark:text-red-200 uppercase tracking-wider flex items-center gap-1.5">
                                <span>🚫 Prohibited Consignment Declaration</span>
                            </h4>
                            <p class="text-gray-600 dark:text-gray-300 text-[11px] leading-relaxed">
                                Per Article VII of our Terms & Conditions, consignments must NOT contain illegal narcotics, weapons/firearms, explosives, flammable liquids, cash/bullion, biohazards, or stolen goods.
                            </p>
                            <label class="flex items-start gap-2.5 pt-1 cursor-pointer select-none">
                                <input type="checkbox" name="prohibited_items_acknowledged" value="1" required class="w-4 h-4 mt-0.5 rounded text-amber-500 border-gray-300 focus:ring-amber-500">
                                <span class="font-bold text-gray-900 dark:text-white text-xs">
                                    I confirm and declare that this parcel does NOT contain any prohibited, illegal, or hazardous items.
                                </span>
                            </label>
                        </div>
                        <!-- Validation / Submit Error Alert -->
                        <div x-show="submitError" x-transition class="p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 rounded-2xl text-xs font-bold flex items-center gap-2">
                            <span>⚠️</span>
                            <span x-text="submitError"></span>
                        </div>
                    </div>

                    <div class="flex justify-between pt-2">
                        <button type="button" @click="currentStep = 5" :disabled="isSubmitting" class="px-5 py-2.5 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 font-extrabold text-xs rounded-xl disabled:opacity-50">
                            ← Back
                        </button>
                        <button type="submit" :disabled="isSubmitting" class="px-8 py-4 bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white font-black text-sm rounded-2xl shadow-lg shadow-amber-500/25 uppercase tracking-wider flex items-center justify-center gap-2">
                            <svg x-show="isSubmitting" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span x-text="isSubmitting ? 'Dispatching Parcel...' : '🚀 Confirm & Dispatch Parcel (' + (priceBreakdown.currency_symbol || '$') + Number(priceBreakdown.total_price || 0).toFixed(2) + ')'"></span>
                        </button>
                    </div>
                </div>

            </div>

            <!-- Right Column: Sticky Fare Breakdown -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 shadow-xl sticky top-24 space-y-6">
                    
                    <div>
                        <span class="text-xs font-extrabold text-amber-500 uppercase tracking-widest block mb-1">Price Estimate</span>
                        <h2 class="text-2xl font-black text-gray-900 dark:text-white">Delivery Fare</h2>
                        <p class="text-xs text-gray-400 mt-1" x-text="deliveryType + ' Parcel Delivery'"></p>
                    </div>

                    <!-- Price Itemized List -->
                    <div class="space-y-3 text-xs border-t border-b border-gray-100 dark:border-white/10 py-4">
                        <div class="flex justify-between text-gray-600 dark:text-gray-400">
                            <span>Delivery Fee:</span>
                            <span class="font-bold text-gray-900 dark:text-white" x-text="priceBreakdown.currency_symbol + Number(priceBreakdown.subtotal || 0).toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between text-gray-600 dark:text-gray-400">
                            <span x-text="'Service Fee (' + (priceBreakdown.service_fee_percent || selectedCategoryServiceFeePercent) + '%):'"></span>
                            <span class="font-bold text-gray-900 dark:text-white" x-text="priceBreakdown.currency_symbol + Number(priceBreakdown.service_fee || 0).toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between text-gray-600 dark:text-gray-400">
                            <span>Taxes (5%):</span>
                            <span class="font-bold text-gray-900 dark:text-white" x-text="priceBreakdown.currency_symbol + Number(priceBreakdown.tax || 0).toFixed(2)"></span>
                        </div>

                        <div class="pt-2 border-t border-gray-100 dark:border-white/10 flex justify-between items-center text-sm font-black">
                            <span class="text-gray-900 dark:text-white">Total Amount:</span>
                            <span class="text-2xl text-amber-500" x-text="priceBreakdown.currency_symbol + Number(priceBreakdown.total_price || 0).toFixed(2)"></span>
                        </div>
                    </div>

                    <div class="p-3 bg-amber-50 dark:bg-amber-950/20 rounded-xl text-amber-800 dark:text-amber-300 text-xs font-bold flex items-center gap-2">
                        <span>🔒</span>
                        <span>4-Digit Secure PIN Verification Included</span>
                    </div>

                </div>
            </div>

        </form>
    </main>

    <!-- Maps Integration with Resilient Auto-Fallback -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    @php
        $gmapsKey = trim((string) config('services.google_maps.api_key'));
        if (empty($gmapsKey)) {
            $gmapsKey = trim((string) env('GOOGLE_MAPS_API_KEY'));
        }
        if (empty($gmapsKey)) {
            $gmapsKey = 'AIzaSyACN52o17kFjtg_K45rKU_ETTJ6WaXvkC0';
        }
    @endphp
    <script src="https://maps.googleapis.com/maps/api/js?key={{ $gmapsKey }}&libraries=places,geometry"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const pInput = document.getElementById("pickup_location_input");
            const dInput = document.getElementById("dropoff_location_input");
            const locBtn = document.getElementById("use_my_location_btn_delivery");
            const pLatInput = document.getElementById("pickup_lat_input");
            const pLngInput = document.getElementById("pickup_lng_input");
            const dLatInput = document.getElementById("dropoff_lat_input");
            const dLngInput = document.getElementById("dropoff_lng_input");

            let currentMapEngine = 'google'; // 'google' | 'leaflet'
            let gMapInstance = null;
            let lMapInstance = null;
            let fallbackActive = false;

            let gPickupMarker = null;
            let gDropoffMarker = null;
            let gRouteLine = null;
            let gCourierMarkers = [];

            let lPickupMarker = null;
            let lDropoffMarker = null;
            let lRouteLine = null;
            let lCourierMarkers = [];

            const countryCoordinates = {
                'IND': { lat: 28.6139, lng: 77.2090 }, // New Delhi / India
                'USA': { lat: 40.7128, lng: -74.0060 }, // New York / USA
                'GHA': { lat: 5.6037, lng: -0.1870 },   // Accra / Ghana
                'NGA': { lat: 6.5244, lng: 3.3792 },    // Lagos / Nigeria
                'ZAF': { lat: -26.2041, lng: 28.0473 }, // Johannesburg / South Africa
                'GBR': { lat: 51.5074, lng: -0.1278 },  // London / UK
                'CAN': { lat: 43.6532, lng: -79.3832 }, // Toronto / Canada
                'ARE': { lat: 25.2048, lng: 55.2708 },  // Dubai / UAE
                'KEN': { lat: -1.2921, lng: 36.8219 },  // Nairobi / Kenya
                'MWI': { lat: -13.9626, lng: 33.7741 }, // Lilongwe / Malawi
                'AUS': { lat: -33.8688, lng: 151.2093 } // Sydney / Australia
            };
            const currentCountry = @json($currentCountryCode ?? 'USA');
            const defaultCenter = countryCoordinates[currentCountry] || countryCoordinates['USA'];
            let defaultLat = defaultCenter.lat;
            let defaultLng = defaultCenter.lng;

            function dismissGoogleErrorDialogs() {
                document.querySelectorAll('div').forEach(el => {
                    if (el.innerText && (
                        el.innerText.includes("This page can't load Google Maps correctly") ||
                        el.innerText.includes("Do you own this website?")
                    )) {
                        el.remove();
                    }
                });
            }

            function triggerLeafletFallback() {
                if (fallbackActive) return;
                fallbackActive = true;
                currentMapEngine = 'leaflet';
                console.warn("Google Maps notice detected on delivery page. Switching to OpenStreetMap fallback.");

                dismissGoogleErrorDialogs();
                setTimeout(dismissGoogleErrorDialogs, 100);
                setTimeout(dismissGoogleErrorDialogs, 500);

                const mapEl = document.getElementById('map');
                if (!mapEl) return;
                mapEl.innerHTML = '';

                initLeafletMap();
            }

            window.gm_authFailure = function() {
                console.error("Google Maps API auth notice received.");
            };



            // Custom Google Maps Icons
            const createGoogleMapIcon = (emoji, bg) => ({
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="42" viewBox="0 0 36 42">
                        <path d="M18 0C8.06 0 0 8.06 0 18c0 12.6 18 24 18 24s18-11.4 18-24c0-9.94-8.06-18-18-18z" fill="${bg}"/>
                        <circle cx="18" cy="18" r="14" fill="#ffffff"/>
                        <text x="18" y="22" font-size="14" text-anchor="middle" dominant-baseline="central">${emoji}</text>
                    </svg>
                `),
                scaledSize: new google.maps.Size(34, 40),
                anchor: new google.maps.Point(17, 40)
            });

            const googleCourierIcon = () => ({
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                    <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 34 34">
                        <circle cx="17" cy="17" r="16" fill="#10b981" stroke="#ffffff" stroke-width="2"/>
                        <text x="17" y="21" font-size="15" text-anchor="middle" dominant-baseline="central">🛵</text>
                    </svg>
                `),
                scaledSize: new google.maps.Size(34, 34),
                anchor: new google.maps.Point(17, 17)
            });

            // Custom Leaflet Icons
            const createLeafletIcon = (emoji, bg) => L.divIcon({
                className: 'custom-map-marker',
                html: `<div style="background: ${bg}; color: white; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); border: 2px solid white;">${emoji}</div>`,
                iconSize: [34, 34],
                iconAnchor: [17, 17]
            });

            const courierOffsets = [
                [0.008, 0.006],
                [-0.007, 0.009],
                [0.005, -0.008],
                [-0.006, -0.005]
            ];

            function updateNearbyCouriers(centerLat, centerLng) {
                if (currentMapEngine === 'leaflet') {
                    if (!lMapInstance) return;
                    lCourierMarkers.forEach(m => {
                        try { lMapInstance.removeLayer(m); } catch (e) {}
                    });
                    lCourierMarkers = [];
                    const cIcon = createLeafletIcon('🛵', '#10b981');
                    courierOffsets.forEach((off, i) => {
                        const m = L.marker([centerLat + off[0], centerLng + off[1]], { icon: cIcon }).addTo(lMapInstance)
                            .bindPopup(`<b>🛵 Active Courier #${i+1}</b><br><span style="color:#10b981;font-size:12px;">● Available (2-4 mins away)</span>`);
                        lCourierMarkers.push(m);
                    });
                } else {
                    if (!gMapInstance || typeof google === 'undefined') return;
                    gCourierMarkers.forEach(m => m.setMap(null));
                    gCourierMarkers = [];
                    courierOffsets.forEach((off, i) => {
                        const marker = new google.maps.Marker({
                            position: { lat: centerLat + off[0], lng: centerLng + off[1] },
                            map: gMapInstance,
                            icon: googleCourierIcon(),
                            title: `Active Courier #${i+1}`
                        });
                        const infoWindow = new google.maps.InfoWindow({
                            content: `<div style="font-weight:700;padding:4px;">🛵 Active Courier #${i+1}<br><span style="color:#10b981;font-size:12px;">● Available (2-4 mins away)</span></div>`
                        });
                        marker.addListener('click', () => infoWindow.open(gMapInstance, marker));
                        gCourierMarkers.push(marker);
                    });
                }
            }

            function initGoogleMap() {
                const mapEl = document.getElementById('map');
                if (!mapEl) return;
                if (typeof google === 'undefined' || !google.maps) {
                    setTimeout(initGoogleMap, 150);
                    return;
                }

                try {
                    gMapInstance = new google.maps.Map(mapEl, {
                        center: { lat: defaultLat, lng: defaultLng },
                        zoom: 13,
                        mapTypeControl: false,
                        streetViewControl: false,
                        fullscreenControl: false,
                        zoomControl: true,
                        styles: [
                            { "featureType": "poi", "elementType": "labels", "stylers": [{ "visibility": "off" }] }
                        ]
                    });

                    gPickupMarker = new google.maps.Marker({
                        position: { lat: defaultLat, lng: defaultLng },
                        map: gMapInstance,
                        draggable: true,
                        icon: createGoogleMapIcon('📍', '#f59e0b'),
                        title: 'Pickup Location'
                    });

                    gPickupMarker.addListener('dragend', function() {
                        const pos = gPickupMarker.getPosition();
                        setPickup(pos.lat(), pos.lng(), true);
                    });

                    updateNearbyCouriers(defaultLat, defaultLng);

                    const curPLat = parseFloat(pLatInput?.value);
                    const curPLng = parseFloat(pLngInput?.value);
                    if (!isNaN(curPLat) && !isNaN(curPLng)) {
                        gMapInstance.setCenter({ lat: curPLat, lng: curPLng });
                        setPickup(curPLat, curPLng, false);
                    }
                    const curDLat = parseFloat(dLatInput?.value);
                    const curDLng = parseFloat(dLngInput?.value);
                    if (!isNaN(curDLat) && !isNaN(curDLng)) {
                        setDropoff(curDLat, curDLng, false);
                    }

                    gMapInstance.addListener('click', function(e) {
                        const lat = e.latLng.lat();
                        const lng = e.latLng.lng();
                        if (!pLatInput.value || (pLatInput.value && dLatInput.value)) {
                            setPickup(lat, lng, true);
                        } else {
                            setDropoff(lat, lng, true);
                        }
                    });
                } catch (e) {
                    console.warn("Google Maps init error, falling back to Leaflet:", e);
                    triggerLeafletFallback();
                }
            }

            function initLeafletMap() {
                const mapEl = document.getElementById('map');
                if (!mapEl || typeof L === 'undefined') return;

                try {
                    lMapInstance = L.map('map', { zoomControl: true }).setView([defaultLat, defaultLng], 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap'
                    }).addTo(lMapInstance);

                    const pickupIcon = createLeafletIcon('📍', '#f59e0b');
                    lPickupMarker = L.marker([defaultLat, defaultLng], { icon: pickupIcon, draggable: true }).addTo(lMapInstance)
                        .bindPopup('<b>📍 Pickup Location</b><br><span style="font-size:11px;color:#666;">Drag to refine location</span>');

                    lPickupMarker.on('dragend', function(e) {
                        const pos = e.target.getLatLng();
                        setPickup(pos.lat, pos.lng, true);
                    });

                    updateNearbyCouriers(defaultLat, defaultLng);

                    lMapInstance.on('click', function(e) {
                        const { lat, lng } = e.latlng;
                        if (!pLatInput.value || (pLatInput.value && dLatInput.value)) {
                            setPickup(lat, lng, true);
                        } else {
                            setDropoff(lat, lng, true);
                        }
                    });

                    // If coordinates were already filled, reflect on Leaflet
                    const curPLat = parseFloat(pLatInput?.value);
                    const curPLng = parseFloat(pLngInput?.value);
                    if (!isNaN(curPLat) && !isNaN(curPLng)) {
                        setPickup(curPLat, curPLng, false);
                    }
                    const curDLat = parseFloat(dLatInput?.value);
                    const curDLng = parseFloat(dLngInput?.value);
                    if (!isNaN(curDLat) && !isNaN(curDLng)) {
                        setDropoff(curDLat, curDLng, false);
                    }

                    setTimeout(() => lMapInstance.invalidateSize(), 300);
                } catch (e) {
                    console.warn("Leaflet map initialization error:", e);
                }
            }

            function setPickup(lat, lng, reverseGeocode = false) {
                if (pLatInput) { pLatInput.value = lat; pLatInput.dispatchEvent(new Event('input')); }
                if (pLngInput) { pLngInput.value = lng; pLngInput.dispatchEvent(new Event('input')); }

                if (currentMapEngine === 'leaflet') {
                    if (lPickupMarker) {
                        lPickupMarker.setLatLng([lat, lng]);
                    } else if (lMapInstance) {
                        lPickupMarker = L.marker([lat, lng], { icon: createLeafletIcon('📍', '#f59e0b'), draggable: true }).addTo(lMapInstance);
                        lPickupMarker.on('dragend', function(e) {
                            const pos = e.target.getLatLng();
                            setPickup(pos.lat, pos.lng, true);
                        });
                    }
                } else {
                    if (gPickupMarker) {
                        gPickupMarker.setPosition({ lat, lng });
                    } else if (gMapInstance && typeof google !== 'undefined') {
                        gPickupMarker = new google.maps.Marker({
                            position: { lat, lng },
                            map: gMapInstance,
                            draggable: true,
                            icon: createGoogleMapIcon('📍', '#f59e0b'),
                            title: 'Pickup Location'
                        });
                        gPickupMarker.addListener('dragend', function() {
                            const pos = gPickupMarker.getPosition();
                            setPickup(pos.lat(), pos.lng(), true);
                        });
                    }
                }

                updateNearbyCouriers(lat, lng);

                if (reverseGeocode && pInput) {
                    fetch(`/api/places/reverse?lat=${lat}&lng=${lng}`)
                        .then(r => r.json())
                        .then(data => {
                            if (data && data.place) {
                                pInput.value = data.place.formatted_address || data.place.name;
                                pInput.dispatchEvent(new Event('input'));
                            }
                        }).catch(() => {});
                }

                updateRouteAndBounds();
                window.dispatchEvent(new CustomEvent('delivery-location-changed', { detail: { type: 'pickup', lat, lng } }));
            }

            function setDropoff(lat, lng, reverseGeocode = false) {
                if (dLatInput) { dLatInput.value = lat; dLatInput.dispatchEvent(new Event('input')); }
                if (dLngInput) { dLngInput.value = lng; dLngInput.dispatchEvent(new Event('input')); }

                if (currentMapEngine === 'leaflet') {
                    if (lDropoffMarker) {
                        lDropoffMarker.setLatLng([lat, lng]);
                    } else if (lMapInstance) {
                        lDropoffMarker = L.marker([lat, lng], { icon: createLeafletIcon('🏁', '#ef4444'), draggable: true }).addTo(lMapInstance);
                        lDropoffMarker.on('dragend', function(e) {
                            const pos = e.target.getLatLng();
                            setDropoff(pos.lat, pos.lng, true);
                        });
                    }
                } else {
                    if (gDropoffMarker) {
                        gDropoffMarker.setPosition({ lat, lng });
                    } else if (gMapInstance && typeof google !== 'undefined') {
                        gDropoffMarker = new google.maps.Marker({
                            position: { lat, lng },
                            map: gMapInstance,
                            draggable: true,
                            icon: createGoogleMapIcon('🏁', '#ef4444'),
                            title: 'Dropoff Location'
                        });
                        gDropoffMarker.addListener('dragend', function() {
                            const pos = gDropoffMarker.getPosition();
                            setDropoff(pos.lat(), pos.lng(), true);
                        });
                    }
                }

                if (reverseGeocode && dInput) {
                    fetch(`/api/places/reverse?lat=${lat}&lng=${lng}`)
                        .then(r => r.json())
                        .then(data => {
                            if (data && data.place) {
                                dInput.value = data.place.formatted_address || data.place.name;
                                dInput.dispatchEvent(new Event('input'));
                            }
                        }).catch(() => {});
                }

                updateRouteAndBounds();
                window.dispatchEvent(new CustomEvent('delivery-location-changed', { detail: { type: 'dropoff', lat, lng } }));
            }

            function updateRouteAndBounds() {
                const pLat = parseFloat(pLatInput?.value);
                const pLng = parseFloat(pLngInput?.value);
                const dLat = parseFloat(dLatInput?.value);
                const dLng = parseFloat(dLngInput?.value);

                if (currentMapEngine === 'leaflet') {
                    if (!lMapInstance) return;
                    if (!isNaN(pLat) && !isNaN(pLng) && !isNaN(dLat) && !isNaN(dLng)) {
                        if (lRouteLine) lMapInstance.removeLayer(lRouteLine);
                        lRouteLine = L.polyline([[pLat, pLng], [dLat, dLng]], {
                            color: '#f59e0b',
                            weight: 4,
                            opacity: 0.85,
                            dashArray: '8, 8',
                            lineCap: 'round'
                        }).addTo(lMapInstance);

                        const bounds = L.latLngBounds([[pLat, pLng], [dLat, dLng]]);
                        lMapInstance.fitBounds(bounds, { padding: [40, 40] });
                    } else if (!isNaN(pLat) && !isNaN(pLng)) {
                        lMapInstance.setView([pLat, pLng], 14);
                    }
                } else {
                    if (!gMapInstance || typeof google === 'undefined') return;
                    if (!isNaN(pLat) && !isNaN(pLng) && !isNaN(dLat) && !isNaN(dLng)) {
                        if (gRouteLine) gRouteLine.setMap(null);

                        gRouteLine = new google.maps.Polyline({
                            path: [
                                { lat: pLat, lng: pLng },
                                { lat: dLat, lng: dLng }
                            ],
                            geodesic: true,
                            strokeColor: '#f59e0b',
                            strokeOpacity: 0.85,
                            strokeWeight: 4,
                            map: gMapInstance
                        });

                        const bounds = new google.maps.LatLngBounds();
                        bounds.extend({ lat: pLat, lng: pLng });
                        bounds.extend({ lat: dLat, lng: dLng });
                        gMapInstance.fitBounds(bounds, { top: 50, right: 50, bottom: 50, left: 50 });
                    } else if (!isNaN(pLat) && !isNaN(pLng)) {
                        gMapInstance.panTo({ lat: pLat, lng: pLng });
                        gMapInstance.setZoom(14);
                    }
                }
            }

            initGoogleMap();

            // Auto-detect user current location on load (GPS with fast IP fallback)
            let autoLocationResolved = false;

            const applyAutoLocation = (lat, lng, isGps = false) => {
                if (autoLocationResolved && !isGps) return;
                autoLocationResolved = true;
                defaultLat = lat;
                defaultLng = lng;

                const centerActiveMap = () => {
                    if (gMapInstance && typeof google !== 'undefined') {
                        gMapInstance.setCenter({ lat: Number(lat), lng: Number(lng) });
                        gMapInstance.setZoom(isGps ? 15 : 14);
                        if (gPickupMarker) {
                            gPickupMarker.setPosition({ lat: Number(lat), lng: Number(lng) });
                        }
                    } else if (lMapInstance) {
                        lMapInstance.setView([lat, lng], isGps ? 15 : 14);
                        if (lPickupMarker) {
                            lPickupMarker.setLatLng([lat, lng]);
                        }
                    } else {
                        setTimeout(centerActiveMap, 100);
                    }
                };
                centerActiveMap();

                setPickup(lat, lng, true);
                updateNearbyCouriers(lat, lng);
            };

            const fetchIpLocation = async () => {
                try {
                    let lat = null, lng = null;
                    try {
                        const res = await fetch('https://get.geojs.io/v1/ip/geo.json', { cache: 'no-store' });
                        if (res.ok) {
                            const data = await res.json();
                            lat = parseFloat(data.latitude);
                            lng = parseFloat(data.longitude);
                        }
                    } catch (e1) {}

                    if (isNaN(lat) || isNaN(lng) || !lat) {
                        try {
                            const res2 = await fetch('https://ipapi.co/json/');
                            if (res2.ok) {
                                const data2 = await res2.json();
                                lat = parseFloat(data2.latitude);
                                lng = parseFloat(data2.longitude);
                            }
                        } catch (e2) {}
                    }

                    if (!isNaN(lat) && !isNaN(lng) && lat) {
                        applyAutoLocation(lat, lng, false);
                    }
                } catch (e) {
                    console.warn("IP geolocation fallback failed:", e);
                }
            };

            if (navigator.geolocation) {
                // Request fast IP in parallel so map centers instantly even before user clicks "Allow" on GPS
                fetchIpLocation();

                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        applyAutoLocation(pos.coords.latitude, pos.coords.longitude, true);
                    },
                    (err) => {
                        console.warn("GPS auto-detect failed, using IP location:", err);
                        fetchIpLocation();
                    },
                    { enableHighAccuracy: true, timeout: 8000, maximumAge: 60000 }
                );
            } else {
                fetchIpLocation();
            }

            // Google Places Autocomplete if available
            window.addEventListener('load', () => {
                if (window.google && google.maps && google.maps.places) {
                    try {
                        if (pInput) {
                            const acP = new google.maps.places.Autocomplete(pInput);
                            acP.addListener('place_changed', () => {
                                const place = acP.getPlace();
                                if (place.geometry && place.geometry.location) {
                                    setPickup(place.geometry.location.lat(), place.geometry.location.lng(), false);
                                }
                            });
                        }
                        if (dInput) {
                            const acD = new google.maps.places.Autocomplete(dInput);
                            acD.addListener('place_changed', () => {
                                const place = acD.getPlace();
                                if (place.geometry && place.geometry.location) {
                                    setDropoff(place.geometry.location.lat(), place.geometry.location.lng(), false);
                                }
                            });
                        }
                    } catch (e) {}
                }
            });

            // Geolocation Button ("Use My Location")
            if (locBtn) {
                locBtn.addEventListener("click", () => {
                    const orig = locBtn.innerHTML;
                    locBtn.disabled = true;
                    locBtn.innerText = "Locating...";

                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            (pos) => {
                                applyAutoLocation(pos.coords.latitude, pos.coords.longitude, true);
                                locBtn.disabled = false;
                                locBtn.innerHTML = orig;
                            },
                            async () => {
                                await fetchIpLocation();
                                locBtn.disabled = false;
                                locBtn.innerHTML = orig;
                            },
                            { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 }
                        );
                    } else {
                        fetchIpLocation().then(() => {
                            locBtn.disabled = false;
                            locBtn.innerHTML = orig;
                        });
                    }
                });
            }
        });
    </script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('packageDeliveryBooking', () => ({
                currentStep: 1,
                pickupLocation: @json($pickup ?? ''),
                dropoffLocation: @json($dropoff ?? ''),
                pickupLat: null,
                pickupLng: null,
                dropoffLat: null,
                dropoffLng: null,

                deliveryType: 'Hyperlocal',
                scheduleMode: 'now',
                pickupDate: @json(date('Y-m-d')),
                pickupTime: '09:00',

                senderName: @json(auth()->user()->name ?? 'Jane Sender'),
                senderPhone: @json(auth()->user()->phone ?? '+1 855 203 3177'),
                senderAddress: '',

                recipientName: 'Robert Johnson',
                recipientPhone: '+1 855 203 3177',
                recipientAddress: '',
                deliveryInstructions: '',

                packageCategory: 'Documents',
                packageDescription: 'Important Legal Contracts & Office Supplies',
                packageSize: 'Small',
                packageWeight: 1.5,
                quantity: 1,
                declaredValue: 150,
                specialHandling: ['signature_required'],

                // Pharmeasy Prescription State
                prescriptionFiles: [],
                prescriptionError: '',
                isUploadingPrescription: false,
                isDraggingPrescription: false,
                previewModal: false,
                previewItem: null,
                previewZoom: 1,
                tempPrescriptionToken: 'temp_rx_' + Math.random().toString(36).substring(2, 15) + Date.now(),

                paymentMethod: 'stripe',
                momoPhone: @json(auth()->user()->phone ?? ''),
                momoNetwork: 'MTN',

                priceBreakdown: {
                    subtotal: 0,
                    service_fee: 0,
                    service_fee_percent: 5,
                    tax: 0,
                    total_price: 0,
                    currency_symbol: '{{ $currentCurrencySymbol ?? "$" }}'
                },

                getSpeedOptionFee(name) {
                    const sym = this.priceBreakdown.currency_symbol || '{{ $currentCurrencySymbol ?? "$" }}';
                    const addons = this.priceBreakdown.addons || {
                        'Instant': {{ $currentPricing->delivery_instant_addon ?? 10.00 }},
                        'Express': {{ $currentPricing->delivery_express_addon ?? 8.00 }},
                        'Same Day': {{ $currentPricing->delivery_same_day_addon ?? 4.00 }},
                        'Scheduled': {{ $currentPricing->delivery_scheduled_addon ?? 2.00 }},
                        'Hyperlocal': 0.00
                    };
                    const val = addons[name] || 0.00;
                    return `+${sym}${Number(val).toFixed(2)}`;
                },

                init() {
                    this.updatePrice();
                    this.$watch('deliveryType', () => this.updatePrice());
                    this.$watch('packageSize', () => this.updatePrice());
                    this.$watch('packageWeight', () => this.updatePrice());
                    this.$watch('packageCategory', () => this.updatePrice());
                    this.$watch('pickupLat', () => this.updatePrice());
                    this.$watch('dropoffLat', () => this.updatePrice());

                    window.addEventListener('delivery-location-changed', (e) => {
                        if (e.detail.type === 'pickup') {
                            this.pickupLat = e.detail.lat;
                            this.pickupLng = e.detail.lng;
                        } else if (e.detail.type === 'dropoff') {
                            this.dropoffLat = e.detail.lat;
                            this.dropoffLng = e.detail.lng;
                        }
                        this.updatePrice();
                    });
                },

                async updatePrice() {
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                        const res = await fetch('/delivery/calculate-price', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                pickup_lat: this.pickupLat,
                                pickup_lng: this.pickupLng,
                                dropoff_lat: this.dropoffLat,
                                dropoff_lng: this.dropoffLng,
                                delivery_type: this.deliveryType,
                                package_size: this.packageSize,
                                package_weight_kg: this.packageWeight,
                                package_category: this.packageCategory,
                                country: '{{ $currentCountryCode ?? "USA" }}'
                            })
                        });
                        if (res.ok) {
                            const data = await res.json();
                            if (data && data.total_price) {
                                this.priceBreakdown = data;
                            }
                        }
                    } catch (e) {
                        console.error("Delivery price calculation error:", e);
                    }
                },
                isSubmitting: false,
                availableCategories: @json($packageCategories ?? \App\Models\PackageCategory::getActiveCategories()),
                get selectedCategoryRequiresRx() {
                    const cur = this.availableCategories.find(c => c.name.toLowerCase() === (this.packageCategory || '').toLowerCase());
                    return cur ? !!cur.requires_prescription : (this.packageCategory === 'Pharmeasy');
                },
                get selectedCategoryServiceFeePercent() {
                    const cur = this.availableCategories.find(c => c.name.toLowerCase() === (this.packageCategory || '').toLowerCase());
                    if (cur && cur.service_fee_percent !== undefined) {
                        return Number(cur.service_fee_percent);
                    }
                    return (this.packageCategory === 'Pharmeasy') ? 10 : 5;
                },

                selectCategory(catName) {
                    this.packageCategory = catName;
                    const catObj = this.availableCategories.find(c => c.name.toLowerCase() === catName.toLowerCase());
                    if (catObj && catObj.default_description) {
                        if (!this.packageDescription || this.packageDescription.includes('Legal Contracts') || this.packageDescription.includes('Prescription Medicines')) {
                            this.packageDescription = catObj.default_description;
                        }
                    } else if (catName === 'Pharmeasy') {
                        if (!this.packageDescription || this.packageDescription.includes('Legal Contracts')) {
                            this.packageDescription = 'Prescription Medicines & Healthcare Supplies';
                        }
                    }
                    if (this.priceBreakdown && this.priceBreakdown.subtotal > 0) {
                        const feePercent = catObj && catObj.service_fee_percent !== undefined ? Number(catObj.service_fee_percent) : (catName === 'Pharmeasy' ? 10 : 5);
                        const feeRate = feePercent / 100;
                        const sub = Number(this.priceBreakdown.subtotal);
                        const fee = Number((sub * feeRate).toFixed(2));
                        const tax = Number(this.priceBreakdown.tax || (sub * 0.05).toFixed(2));
                        this.priceBreakdown.service_fee = fee;
                        this.priceBreakdown.service_fee_percent = feePercent;
                        this.priceBreakdown.total_price = Number((sub + fee + tax).toFixed(2));
                    }
                    this.updatePrice();
                },

                proceedFromStep4() {
                    this.prescriptionError = '';
                    if (this.selectedCategoryRequiresRx) {
                        if (!this.prescriptionFiles || this.prescriptionFiles.length === 0) {
                            this.prescriptionError = 'Doctor prescription upload is mandatory for ' + this.packageCategory + ' delivery. Please upload at least one valid prescription (JPG, PNG, or PDF) to continue.';
                            const el = document.getElementById('prescriptionFileInput');
                            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            return;
                        }
                    }
                    this.currentStep = 5;
                },

                handlePrescriptionFiles(event) {
                    const files = Array.from(event.target.files);
                    this.uploadPrescriptionFiles(files);
                    event.target.value = '';
                },

                handlePrescriptionDrop(event) {
                    const files = Array.from(event.dataTransfer.files);
                    this.uploadPrescriptionFiles(files);
                },

                async uploadPrescriptionFiles(files) {
                    this.prescriptionError = '';
                    if (!files || files.length === 0) return;

                    const allowed = ['jpg', 'jpeg', 'png', 'pdf'];
                    const validFiles = [];

                    for (const f of files) {
                        const ext = (f.name.split('.').pop() || '').toLowerCase();
                        if (!allowed.includes(ext)) {
                            this.prescriptionError = `File "${f.name}" has an invalid extension. Only JPG, PNG, and PDF files are allowed.`;
                            return;
                        }
                        if (f.size > 10 * 1024 * 1024) {
                            this.prescriptionError = `File "${f.name}" exceeds the maximum 10MB limit.`;
                            return;
                        }
                        validFiles.push(f);
                    }

                    if (validFiles.length === 0) return;

                    this.isUploadingPrescription = true;

                    try {
                        const formData = new FormData();
                        validFiles.forEach(f => formData.append('files[]', f));
                        formData.append('temp_token', this.tempPrescriptionToken);

                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                        const res = await fetch('/delivery/prescriptions/upload', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: formData
                        });

                        const data = await res.json();
                        if (res.ok && data.success && data.prescriptions) {
                            data.prescriptions.forEach(p => {
                                this.prescriptionFiles.push({
                                    id: p.id,
                                    name: p.file_name,
                                    size: p.formatted_size,
                                    type: p.file_type.toUpperCase(),
                                    isPdf: p.is_pdf,
                                    isImage: p.is_image,
                                    viewUrl: p.view_url,
                                    downloadUrl: p.download_url,
                                    uploadedAt: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                                });
                            });
                            this.prescriptionError = '';
                        } else {
                            this.prescriptionError = data.message || 'Failed to upload prescription. Please try again.';
                        }
                    } catch (e) {
                        console.error('Prescription upload failed:', e);
                        this.prescriptionError = 'An error occurred while uploading prescription. Please try again.';
                    } finally {
                        this.isUploadingPrescription = false;
                    }
                },

                async removePrescription(idx) {
                    const item = this.prescriptionFiles[idx];
                    if (!item) return;

                    if (item.id) {
                        try {
                            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                            await fetch(`/delivery/prescriptions/${item.id}?token=${this.tempPrescriptionToken}`, {
                                method: 'DELETE',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                }
                            });
                        } catch (err) {
                            console.warn('Prescription delete error:', err);
                        }
                    }

                    this.prescriptionFiles.splice(idx, 1);
                    if (this.prescriptionFiles.length === 0 && this.packageCategory === 'Pharmeasy') {
                        this.prescriptionError = 'Doctor prescription upload is mandatory for Pharmeasy deliveries.';
                    }
                },

                openPreview(item) {
                    this.previewItem = item;
                    this.previewZoom = 1;
                    this.previewModal = true;
                },

                submitError: '',

                toggleHandling(val) {
                    const idx = this.specialHandling.indexOf(val);
                    if (idx > -1) {
                        this.specialHandling.splice(idx, 1);
                    } else {
                        this.specialHandling.push(val);
                    }
                },

                async submitDeliveryForm(event) {
                    this.submitError = '';

                    // Validate Step 1
                    if (!this.pickupLocation || !this.pickupLocation.trim()) {
                        this.currentStep = 1;
                        this.submitError = 'Please enter a valid Pickup Address.';
                        return;
                    }
                    if (!this.dropoffLocation || !this.dropoffLocation.trim()) {
                        this.currentStep = 1;
                        this.submitError = 'Please enter a valid Drop-off / Destination Address.';
                        return;
                    }

                    // Validate Step 3
                    if (!this.senderName || !this.senderName.trim() || !this.senderPhone || !this.senderPhone.trim()) {
                        this.currentStep = 3;
                        this.submitError = 'Please fill in Sender Name and Phone Number.';
                        return;
                    }
                    if (!this.recipientName || !this.recipientName.trim() || !this.recipientPhone || !this.recipientPhone.trim()) {
                        this.currentStep = 3;
                        this.submitError = 'Please fill in Recipient Name and Phone Number.';
                        return;
                    }

                    // Validate Step 4 Prescription if required
                    if (this.selectedCategoryRequiresRx && this.prescriptionFiles.length === 0) {
                        this.currentStep = 4;
                        this.submitError = 'Doctor prescription upload is mandatory for ' + this.packageCategory + ' delivery. Please upload at least one valid prescription.';
                        this.prescriptionError = 'Doctor prescription upload is mandatory for ' + this.packageCategory + ' delivery.';
                        return;
                    }

                    // Validate Step 6 Checkbox
                    const prohibitedCheckbox = document.querySelector('input[name="prohibited_items_acknowledged"]');
                    if (prohibitedCheckbox && !prohibitedCheckbox.checked) {
                        this.currentStep = 6;
                        this.submitError = 'Please confirm the Prohibited Consignment Declaration.';
                        return;
                    }

                    this.isSubmitting = true;

                    try {
                        const formData = new FormData(event.target);
                        if (this.selectedCategoryRequiresRx) {
                            formData.append('prescription_temp_token', this.tempPrescriptionToken);
                            formData.append('prescription_ids', this.prescriptionFiles.map(p => p.id).join(','));
                        }

                        const response = await fetch('/delivery/book', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: formData
                        });

                        const text = await response.text();
                        let data;
                        try {
                            data = JSON.parse(text);
                        } catch (parseErr) {
                            console.error("Non-JSON response from /delivery/book:", text);
                            this.isSubmitting = false;
                            this.submitError = response.status >= 500
                                ? `Server encountered an issue (${response.status}). Please try again or contact support.`
                                : `Request failed with status ${response.status}. Please check your inputs.`;
                            return;
                        }

                        if (!response.ok || !data.success) {
                            this.isSubmitting = false;
                            this.submitError = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Failed to dispatch parcel. Please check form fields.');
                            return;
                        }

                        if (this.paymentMethod === 'stripe' || this.paymentMethod === 'card' || (data.redirect_url && data.redirect_url.includes('verify-details'))) {
                            window.location.href = data.redirect_url || ('/payment/verify-details/package_delivery/' + data.delivery_id);
                        } else {
                            window.location.href = data.redirect_url || ('/admin/package-delivery-tracker/' + data.delivery_id);
                        }
                    } catch (err) {
                        console.error("Delivery submission error:", err);
                        this.isSubmitting = false;
                        this.submitError = err.message || 'An unexpected error occurred. Please try again.';
                    }
                }
            }));
        });
    </script>

    <!-- Fullscreen Prescription Lightbox Modal -->
    <div x-show="previewModal" 
         x-cloak
         class="fixed inset-0 z-50 bg-black/95 flex flex-col justify-between p-4"
         @keydown.escape.window="previewModal = false">
        <div class="flex items-center justify-between text-white border-b border-white/10 pb-3">
            <div class="flex items-center gap-2">
                <span class="text-sm font-black text-emerald-400">Rx Document:</span>
                <span class="text-sm font-bold truncate max-w-md" x-text="previewItem?.name"></span>
                <span class="text-xs text-gray-400" x-text="'(' + previewItem?.size + ')'"></span>
            </div>
            <div class="flex items-center gap-2">
                <template x-if="previewItem?.isImage">
                    <div class="flex items-center gap-1 bg-white/10 rounded-lg px-2 py-1 mr-2 text-xs">
                        <button type="button" @click="previewZoom = Math.min(previewZoom + 0.25, 3.5)" class="px-2 py-0.5 hover:text-emerald-400 font-bold">+</button>
                        <span x-text="Math.round(previewZoom * 100) + '%'" class="px-1 text-[11px] font-mono"></span>
                        <button type="button" @click="previewZoom = Math.max(previewZoom - 0.25, 0.5)" class="px-2 py-0.5 hover:text-emerald-400 font-bold">-</button>
                        <button type="button" @click="previewZoom = 1" class="text-[10px] text-amber-400 ml-1">Reset</button>
                    </div>
                </template>
                <a :href="previewItem?.downloadUrl" target="_blank" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition">
                    Download
                </a>
                <button type="button" @click="previewModal = false" class="px-3 py-1 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-bold transition">
                    ✕ Close
                </button>
            </div>
        </div>
        <div class="flex-1 flex items-center justify-center p-4 overflow-auto">
            <template x-if="previewItem?.isImage">
                <img :src="previewItem?.viewUrl" 
                     :style="'transform: scale(' + previewZoom + '); transition: transform 0.2s ease-out;'"
                     class="max-w-full max-h-full object-contain rounded-lg shadow-2xl">
            </template>
            <template x-if="previewItem?.isPdf">
                <iframe :src="previewItem?.viewUrl" class="w-full h-full max-h-[85vh] rounded-xl border border-gray-700 bg-white"></iframe>
            </template>
        </div>
    </div>

    <x-stripe-modal serviceType="package_delivery" />
</x-layout>