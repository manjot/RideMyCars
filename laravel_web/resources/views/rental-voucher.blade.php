<x-layout>
    <x-slot:title>Rental Voucher #{{ $ride->digital_receipt_code }} — RideMyCars</x-slot>

    <main class="flex-1 max-w-4xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <!-- Action Bar -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-8 print:hidden">
            <div>
                <a href="/my-rides" class="text-sm font-bold text-gray-500 hover:text-brand-500 transition-colors flex items-center gap-1">
                    &larr; Back to My Bookings
                </a>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="window.print()" class="px-5 py-2.5 bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-extrabold rounded-xl text-xs shadow-md hover:opacity-90 flex items-center gap-2 transition-all cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    Print Rental Voucher
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-8 p-5 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800/30 text-emerald-800 dark:text-emerald-200 font-semibold flex items-center gap-3 shadow-sm print:hidden">
                <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-bold text-lg shrink-0">
                    ✓
                </div>
                <div>
                    <h4 class="font-bold text-base text-gray-900 dark:text-white">Reservation Confirmed!</h4>
                    <p class="text-xs text-emerald-700 dark:text-emerald-300 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Voucher Card Container -->
        <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 md:p-10 shadow-lg space-y-8 print:border-none print:shadow-none print:p-0">
            
            <!-- Header Logo & Voucher Code -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 pb-6 border-b border-gray-100 dark:border-white/10">
                <div>
                    <div class="flex items-center gap-2 text-2xl font-black text-gray-900 dark:text-white tracking-tight mb-1">
                        <span class="text-brand-500">Ride</span>MyCars
                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-brand-50 dark:bg-brand-900/40 text-brand-600 dark:text-brand-400 font-extrabold uppercase tracking-wider border border-brand-200 dark:border-brand-800/30">Rental Voucher</span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Official Car Rental Booking Confirmation & Pickup Pass</p>
                </div>

                <div class="text-left sm:text-right bg-gray-50 dark:bg-[#1a1a1a] p-4 rounded-2xl border border-gray-100 dark:border-white/5 w-full sm:w-auto">
                    <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest block mb-0.5">Voucher Reference</span>
                    <span class="font-mono text-xl font-black text-brand-500 tracking-wider">#{{ $ride->digital_receipt_code }}</span>
                </div>
            </div>

            <!-- Vehicle Summary Banner -->
            <div class="p-6 rounded-2xl bg-gradient-to-r from-gray-900 via-black to-gray-900 text-white flex flex-col md:flex-row items-center justify-between gap-6 shadow-md">
                <div class="flex items-center gap-5">
                    <div class="w-24 h-16 bg-white/10 rounded-xl overflow-hidden p-2 flex items-center justify-center shrink-0">
                        <img src="{{ $ride->vehicle ? $ride->vehicle->image_src : '/images/hero-rent.png' }}" alt="Vehicle" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <span class="text-xs text-brand-400 font-bold uppercase tracking-wider block mb-1">Confirmed Vehicle</span>
                        <h2 class="text-2xl font-extrabold">{{ $ride->vehicle ? ($ride->vehicle->year . ' ' . $ride->vehicle->make . ' ' . $ride->vehicle->model) : $ride->vehicle_type }}</h2>
                        <div class="flex items-center gap-3 text-xs text-gray-300 mt-1">
                            <span>License Plate: <strong class="text-white">{{ $ride->vehicle->license_plate ?? 'REG-9988' }}</strong></span>
                            <span>•</span>
                            <span>Transmission: <strong class="text-white">{{ ucfirst($ride->vehicle->transmission ?? 'Automatic') }}</strong></span>
                        </div>
                    </div>
                </div>

                <div class="text-center md:text-right border-t md:border-t-0 border-white/10 pt-4 md:pt-0 w-full md:w-auto">
                    <span class="text-xs text-gray-400 block uppercase font-bold tracking-wider mb-1">Status</span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold uppercase bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                        ✓ {{ strtoupper($ride->status) }}
                    </span>
                </div>
            </div>

            <!-- Rental Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-sm">
                
                <!-- Pickup Information -->
                <div class="p-5 rounded-2xl bg-gray-50 dark:bg-[#1a1a1a] border border-gray-100 dark:border-white/5 space-y-3">
                    <div class="flex items-center gap-2 text-gray-900 dark:text-white font-extrabold text-base border-b border-gray-200 dark:border-white/10 pb-2">
                        <span class="text-emerald-500">📍</span> Pick-up Location & Schedule
                    </div>
                    <div class="space-y-1.5 text-gray-600 dark:text-gray-400">
                        <p><strong class="text-gray-900 dark:text-white">Location:</strong> {{ $ride->pickup_location }}</p>
                        <p><strong class="text-gray-900 dark:text-white">Date & Time:</strong> {{ $ride->pickup_date ? $ride->pickup_date->format('M d, Y') : '' }} at {{ $ride->pickup_time }}</p>
                        <p><strong class="text-gray-900 dark:text-white">Supplier:</strong> {{ $ride->vehicle && $ride->vehicle->owner ? $ride->vehicle->owner->name : 'RideMyCars Partner Fleet' }}</p>
                    </div>
                </div>

                <!-- Return Information -->
                <div class="p-5 rounded-2xl bg-gray-50 dark:bg-[#1a1a1a] border border-gray-100 dark:border-white/5 space-y-3">
                    <div class="flex items-center gap-2 text-gray-900 dark:text-white font-extrabold text-base border-b border-gray-200 dark:border-white/10 pb-2">
                        <span class="text-rose-500">🏁</span> Drop-off Location & Schedule
                    </div>
                    <div class="space-y-1.5 text-gray-600 dark:text-gray-400">
                        <p><strong class="text-gray-900 dark:text-white">Location:</strong> {{ $ride->dropoff_location }}</p>
                        <p><strong class="text-gray-900 dark:text-white">Date & Time:</strong> {{ $ride->return_date ? $ride->return_date->format('M d, Y') : '' }} at {{ $ride->return_time ?? $ride->pickup_time }}</p>
                        <p><strong class="text-gray-900 dark:text-white">Return Mode:</strong> {{ $ride->different_dropoff ? 'Different Drop-off Location' : 'Same Location Return' }}</p>
                    </div>
                </div>

            </div>

            <!-- Inclusions & Protection Breakdown -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                <div class="p-5 rounded-2xl bg-gray-50 dark:bg-[#1a1a1a] border border-gray-100 dark:border-white/5 space-y-2">
                    <h3 class="font-extrabold text-gray-900 dark:text-white text-sm mb-2">Coverage & Protection</h3>
                    <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400">
                        <span>Selected Cover:</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400 uppercase">{{ $ride->protection_option === 'full_cover' ? 'Full Protection / Zero Excess' : 'Basic Standard Protection' }}</span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400">
                        <span>Fuel Policy:</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ $ride->fuel_policy ?? 'Full-to-Full' }}</span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400">
                        <span>Mileage Policy:</span>
                        <span class="font-bold text-gray-900 dark:text-white">Unlimited Mileage</span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400">
                        <span>Refundable Security Deposit:</span>
                        <span class="font-bold text-amber-600 dark:text-amber-400">${{ number_format($ride->vehicle->security_deposit_amount ?? 200, 2) }}</span>
                    </div>
                </div>

                <!-- Driver Information -->
                <div class="p-5 rounded-2xl bg-gray-50 dark:bg-[#1a1a1a] border border-gray-100 dark:border-white/5 space-y-2">
                    <h3 class="font-extrabold text-gray-900 dark:text-white text-sm mb-2">Driver Details</h3>
                    <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400">
                        <span>Primary Driver:</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ $ride->rider->name ?? 'Primary Renter' }}</span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400">
                        <span>Driver Age:</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ $ride->customer_age ?? 25 }} Years</span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400">
                        <span>Residence Country:</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ $ride->driver_country ?? 'USA' }}</span>
                    </div>
                </div>
            </div>

            <!-- Financial Itemized Summary -->
            <div class="p-6 rounded-2xl bg-brand-50/50 dark:bg-brand-950/20 border border-brand-200 dark:border-brand-800/30 space-y-3">
                <h3 class="font-extrabold text-gray-900 dark:text-white text-base border-b border-brand-200 dark:border-brand-800/30 pb-2">Itemized Financial Breakdown</h3>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Total Rental Fare:</span>
                        <span class="font-semibold text-gray-900 dark:text-white">${{ number_format($ride->total_amount, 2) }}</span>
                    </div>
                    @if($ride->protection_fee > 0)
                        <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400">
                            <span>Includes Full Protection Cover:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">+${{ number_format($ride->protection_fee, 2) }}</span>
                        </div>
                    @endif
                    @if($ride->extras_fee > 0)
                        <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400">
                            <span>Includes Optional Extras:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">+${{ number_format($ride->extras_fee, 2) }}</span>
                        </div>
                    @endif

                    <div class="pt-2 border-t border-brand-200 dark:border-brand-800/30 flex justify-between items-center text-sm font-bold">
                        <span class="text-emerald-700 dark:text-emerald-400">Amount Paid Online (20% Deposit):</span>
                        <span class="text-emerald-700 dark:text-emerald-400 text-lg">${{ number_format($ride->paid_amount, 2) }}</span>
                    </div>

                    <div class="flex justify-between items-center text-sm font-extrabold text-amber-700 dark:text-amber-400 pt-1">
                        <span>Remaining Balance Payable at Pickup (80%):</span>
                        <span class="text-lg">${{ number_format($ride->remaining_balance, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Mandatory Documents Checklist for Pickup -->
            <div class="p-5 rounded-2xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/30 text-amber-900 dark:text-amber-200 space-y-2 text-xs">
                <div class="font-extrabold flex items-center gap-1.5 text-sm text-amber-800 dark:text-amber-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Mandatory Checklist for Vehicle Pickup</span>
                </div>
                <ul class="list-disc list-inside space-y-1 text-amber-800 dark:text-amber-300">
                    <li>Valid Driver's License in driver's name matching voucher profile.</li>
                    <li>Passport / Government-issued National ID Card.</li>
                    <li>Credit Card / Mobile Money account for pre-authorization hold of security deposit (${{ number_format($ride->vehicle->security_deposit_amount ?? 200, 2) }}).</li>
                    <li>Printed copy or digital access to this Voucher Code: <strong>#{{ $ride->digital_receipt_code }}</strong>.</li>
                </ul>
            </div>

        </div>

    </main>
</x-layout>
