<x-layout :hideFooter="true">
    <x-slot:title>Receipt #{{ $receipt->receipt_number }} — RideMyCars</x-slot:title>

    <x-slot:head>
        <style>
            @media print {
                @page {
                    size: A4 portrait;
                    margin: 8mm 10mm;
                }
                header, footer, nav, #live-ride-tracker, .print\:hidden {
                    display: none !important;
                    visibility: hidden !important;
                    height: 0 !important;
                    overflow: hidden !important;
                }
                body {
                    background: #ffffff !important;
                    color: #000000 !important;
                    padding: 0 !important;
                    margin: 0 !important;
                }
                main {
                    max-width: 100% !important;
                    width: 100% !important;
                    padding: 0 !important;
                    margin: 0 !important;
                }
                .receipt-card {
                    border: 1px solid #e2e8f0 !important;
                    box-shadow: none !important;
                    border-radius: 16px !important;
                    page-break-inside: avoid;
                }
                html.dark body, html.dark main, html.dark .receipt-card {
                    background-color: #ffffff !important;
                    color: #0f172a !important;
                }
                html.dark .text-white, html.dark .text-gray-900 {
                    color: #0f172a !important;
                }
                html.dark .text-gray-300, html.dark .text-gray-400, html.dark .text-gray-500 {
                    color: #475569 !important;
                }
                html.dark .bg-gray-50, html.dark .bg-gray-100\/60, html.dark [class*="bg-white\/"] {
                    background-color: #f8fafc !important;
                }
                html.dark [class*="border-white\/"] {
                    border-color: #e2e8f0 !important;
                }
            }
        </style>
    </x-slot:head>

    <main class="flex-1 max-w-4xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10">
        
        <!-- Action Header (Hidden in Print) -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-8 print:hidden">
            <div>
                <a href="/account?tab=receipts" class="text-sm font-bold text-gray-500 hover:text-amber-500 transition-colors flex items-center gap-1.5">
                    &larr; Back to Booking History & Receipts
                </a>
            </div>
            
            <div class="flex items-center gap-2">
                <!-- Resend Email Form -->
                <form action="/receipts/{{ $receipt->id }}/resend" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-white/10 dark:hover:bg-white/20 text-gray-800 dark:text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition-all cursor-pointer">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                        Re-send to Email
                    </button>
                </form>

                <!-- Print Button -->
                <button onclick="window.print()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-white/10 dark:hover:bg-white/20 text-gray-800 dark:text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.075-.341-2.173-.341-3.291 0-5.799 4.701-10.5 10.5-10.5s10.5 4.701 10.5 10.5c0 1.118-.101 2.216-.341 3.291m-10.5-7.5v12m0 0l-3.75-3.75m3.75 3.75l3.75-3.75"/></svg>
                    Print
                </button>

                <!-- Download PDF Button -->
                <a href="{{ $receipt->download_url }}" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-black font-extrabold rounded-xl text-xs shadow-md flex items-center gap-1.5 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Download PDF
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 font-bold text-sm flex items-center gap-2 print:hidden">
                <span>✓</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 font-bold text-sm flex items-center gap-2 print:hidden">
                <span>✕</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Ola-style Receipt Card -->
        <div class="receipt-card bg-white dark:bg-[#121212] rounded-3xl border border-gray-200 dark:border-white/10 shadow-xl overflow-hidden print:border print:border-gray-200 print:shadow-none print:m-0">
            
            <!-- Top Brand & Date Row -->
            <div class="px-6 py-5 sm:px-8 border-b border-gray-100 dark:border-white/5 flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Official Receipt</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ $receipt->created_at->format('d M, Y \a\t h:i A') }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-amber-400/10 text-amber-500 border border-amber-400/20">
                        {{ $receipt->type_label }}
                    </span>
                    <a href="/" class="block transition-transform hover:scale-105" title="RideMyCars Homepage">
                        <img src="{{ asset('images/logo.png') }}" alt="RideMyCars" class="h-9 sm:h-11 w-auto object-contain" />
                    </a>
                </div>
            </div>

            <!-- Big Price Hero -->
            <div class="text-center py-8 px-6 bg-gray-50/50 dark:bg-white/[0.02] border-b border-gray-100 dark:border-white/5">
                <div class="text-4xl sm:text-5xl font-black text-gray-900 dark:text-white tracking-tight">
                    {{ $receipt->currency }} {{ number_format($receipt->total_amount, 2) }}
                </div>
                <div class="mt-1 font-mono text-xs font-bold text-gray-400 tracking-wider">
                    CRN: {{ $receipt->receipt_number }}
                </div>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300 font-medium">
                    Thanks for choosing RideMyCars, <span class="font-bold text-gray-900 dark:text-white">{{ $snapshot['customer_name'] ?? ($receipt->user->name ?? 'Valued Customer') }}</span>!
                </p>
            </div>

            <!-- 2-Column Split: Booking Details & Bill Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-100 dark:divide-white/5">
                
                <!-- Left Column: Booking Details -->
                <div class="p-6 sm:p-8 space-y-6">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-white/5 pb-3">
                        <h2 class="text-xs font-extrabold uppercase tracking-wider text-gray-400">
                            {{ $receipt->type_label }} Details
                        </h2>
                    </div>

                    <!-- Driver / Courier Card -->
                    @if(!empty($snapshot['driver_name']))
                        <div class="flex items-center gap-4 p-4 rounded-2xl bg-gray-50 dark:bg-white/[0.03] border border-gray-100 dark:border-white/5">
                            <div class="w-12 h-12 rounded-full bg-amber-400 text-black font-black text-lg flex items-center justify-center shrink-0">
                                {{ strtoupper(substr($snapshot['driver_name'], 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-sm text-gray-900 dark:text-white">{{ $snapshot['driver_name'] }}</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    @if($receipt->booking_type === 'delivery') Courier Partner
                                    @elseif($receipt->booking_type === 'driver_booking') Private Chauffeur
                                    @else Assigned Driver Partner
                                    @endif
                                </p>
                                @if(!empty($snapshot['driver_phone']))
                                    <p class="text-xs font-mono text-gray-400 mt-0.5">📞 {{ $snapshot['driver_phone'] }}</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Vehicle & Metrics -->
                    <div class="space-y-3">
                        @if(!empty($snapshot['vehicle_title']))
                            <div class="flex items-center justify-between text-xs py-1">
                                <span class="text-gray-500 dark:text-gray-400 font-medium">Vehicle</span>
                                <span class="font-bold text-gray-900 dark:text-white text-right">
                                    {{ $snapshot['vehicle_title'] }}
                                    @if(!empty($snapshot['vehicle_plate']))
                                        <span class="text-[10px] font-mono bg-gray-100 dark:bg-white/10 px-1.5 py-0.5 rounded text-gray-600 dark:text-gray-300 ml-1">
                                            {{ $snapshot['vehicle_plate'] }}
                                        </span>
                                    @endif
                                </span>
                            </div>
                        @endif

                        @if(!empty($snapshot['distance_km']) || !empty($snapshot['duration_minutes']))
                            <div class="flex items-center justify-between text-xs py-1">
                                <span class="text-gray-500 dark:text-gray-400 font-medium">Distance & Time</span>
                                <span class="font-bold text-gray-900 dark:text-white">
                                    @if(!empty($snapshot['distance_km'])) {{ $snapshot['distance_km'] }} km @endif
                                    @if(!empty($snapshot['distance_km']) && !empty($snapshot['duration_minutes'])) &bull; @endif
                                    @if(!empty($snapshot['duration_minutes'])) {{ $snapshot['duration_minutes'] }} mins @endif
                                </span>
                            </div>
                        @endif
                    </div>

                    <!-- Pickup & Dropoff Timeline -->
                    <div class="space-y-4 pt-2">
                        <div class="flex items-start gap-3">
                            <div class="mt-1 w-3 h-3 rounded-full bg-emerald-500 shrink-0 ring-4 ring-emerald-500/20"></div>
                            <div>
                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 block">
                                    Pickup Location
                                    @if(!empty($snapshot['pickup_time']))
                                        &bull; {{ $snapshot['pickup_time'] }}
                                    @endif
                                </span>
                                <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 mt-0.5 leading-relaxed">
                                    {{ $snapshot['pickup_location'] ?? 'Pickup Point' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="mt-1 w-3 h-3 rounded-full bg-rose-500 shrink-0 ring-4 ring-rose-500/20"></div>
                            <div>
                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-rose-600 dark:text-rose-400 block">
                                    Dropoff Location
                                </span>
                                <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 mt-0.5 leading-relaxed">
                                    {{ $snapshot['dropoff_location'] ?? 'Destination Point' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Bill Details -->
                <div class="p-6 sm:p-8 space-y-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between border-b border-gray-100 dark:border-white/5 pb-3 mb-4">
                            <h2 class="text-xs font-extrabold uppercase tracking-wider text-gray-400">
                                Bill Details
                            </h2>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-500 dark:text-gray-400 font-medium">Base Fare / Service Price</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $receipt->currency }} {{ number_format($receipt->subtotal, 2) }}</span>
                            </div>

                            @if($receipt->discount_amount > 0)
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold">Special Discount</span>
                                    <span class="font-bold text-emerald-600 dark:text-emerald-400">-{{ $receipt->currency }} {{ number_format($receipt->discount_amount, 2) }}</span>
                                </div>
                            @endif

                            @if(!empty($snapshot['extras_fee']) && floatval($snapshot['extras_fee']) > 0)
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-500 dark:text-gray-400 font-medium">Selected Extras</span>
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $receipt->currency }} {{ number_format(floatval($snapshot['extras_fee']), 2) }}</span>
                                </div>
                            @endif

                            @if(!empty($snapshot['protection_fee']) && floatval($snapshot['protection_fee']) > 0)
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-500 dark:text-gray-400 font-medium">Protection Option Fee</span>
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $receipt->currency }} {{ number_format(floatval($snapshot['protection_fee']), 2) }}</span>
                                </div>
                            @endif

                            @if($receipt->fee_amount > 0)
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-500 dark:text-gray-400 font-medium">Platform & Booking Fee</span>
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $receipt->currency }} {{ number_format($receipt->fee_amount, 2) }}</span>
                                </div>
                            @endif

                            @if($receipt->tax_amount > 0)
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-500 dark:text-gray-400 font-medium">Taxes (GST / VAT)</span>
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $receipt->currency }} {{ number_format($receipt->tax_amount, 2) }}</span>
                                </div>
                            @endif

                            <div class="pt-4 border-t-2 border-gray-900 dark:border-white/20 flex items-center justify-between">
                                <span class="font-black text-sm text-gray-900 dark:text-white">Total Bill (Paid)</span>
                                <span class="font-black text-lg text-gray-900 dark:text-white">{{ $receipt->currency }} {{ number_format($receipt->total_amount, 2) }}</span>
                            </div>
                        </div>

                        <p class="text-[11px] text-gray-400 mt-4 leading-relaxed">
                            Have questions regarding this fare? <a href="/contact" class="text-amber-500 font-bold hover:underline">Contact Customer Support</a>.
                        </p>
                    </div>

                    <!-- Payment Mode Box -->
                    <div class="mt-6 p-4 rounded-2xl bg-gray-50 dark:bg-white/[0.03] border border-gray-100 dark:border-white/5 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-base">💳</span>
                            <span class="text-xs font-bold text-gray-900 dark:text-white">
                                Paid by {{ ucfirst($receipt->payment_method) }}
                            </span>
                        </div>
                        <span class="text-xs font-extrabold text-emerald-500 bg-emerald-500/10 px-2.5 py-1 rounded-full border border-emerald-500/20">
                            {{ strtoupper($receipt->payment_status) }}
                        </span>
                    </div>
                </div>

            </div>

            <!-- Digital Verification Bar -->
            <div class="p-6 bg-gray-50/50 dark:bg-white/[0.02] border-t border-gray-100 dark:border-white/5 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
                <div>
                    <span class="text-gray-400 font-medium block">Verification Security Token:</span>
                    <span class="font-mono text-gray-700 dark:text-gray-300 font-bold select-all">{{ $receipt->verification_token }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-emerald-500 font-bold">🔒 Tamper-Proof Stored Receipt</span>
                </div>
            </div>

            <!-- Grievance & Legal Footer -->
            <div class="p-6 sm:p-8 bg-gray-100/60 dark:bg-black/40 border-t border-gray-200 dark:border-white/10 text-center text-xs text-gray-500 dark:text-gray-400 space-y-3">
                <div class="max-w-2xl mx-auto p-4 rounded-xl bg-white dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 text-left text-[11px] space-y-1">
                    <div class="font-bold text-gray-900 dark:text-white">Official Tax & Grievance Contact:</div>
                    <div>{{ $snapshot['company_name'] ?? 'RideMyCars Technologies Inc.' }}</div>
                    <div>{{ $snapshot['company_address'] ?? 'Corporate Headquarters' }}</div>
                    <div class="text-gray-400">
                        Email: <a href="mailto:{{ $snapshot['company_email'] ?? 'support@ridemycars.com' }}" class="text-amber-500">{{ $snapshot['company_email'] ?? 'support@ridemycars.com' }}</a>
                        &bull; Phone: {{ $snapshot['company_phone'] ?? '+1 (800) 555-RIDE' }}
                        &bull; GST/VAT: {{ $snapshot['company_gst_vat'] ?? 'N/A' }}
                    </div>
                </div>

                <p class="text-[10px] text-gray-400">
                    This document is electronically generated and securely stored in your RideMyCars account.
                </p>
            </div>

        </div>

    </main>
</x-layout>
