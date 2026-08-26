<x-layout>
    <x-slot:title>Package Delivery Live Tracker — Admin Dashboard | RideMyCars</x-slot>

    <script>
        window.INITIAL_DELIVERY_ORDERS = @json($initialOrders ?? []);
        window.INITIAL_AVAILABLE_DRIVERS = @json($initialAvailableDrivers ?? []);
    </script>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="liveDeliveryTracker()" x-init="init()">
        
        <!-- Top Operations Status Banner -->
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between p-6 bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 shadow-sm gap-4 mb-8">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-brand-500/10 text-brand-500 flex items-center justify-center font-bold text-2xl shrink-0 shadow-sm">
                    🚚
                </div>
                <div>
                    <h1 class="text-2xl font-black text-gray-900 dark:text-white">Package Delivery Live Tracker</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Logistics & Support Operations • 15% Platform Fee / 85% Driver Payout Model</p>
                </div>
            </div>

            <div class="flex items-center gap-4 text-xs font-bold shrink-0">
                <div class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400 border border-green-200 dark:border-green-800/30">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse"></span>
                    <span>Live GPS Polling Stream (10s)</span>
                </div>
                <button @click="refreshStream()" :disabled="isRefreshing" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 active:scale-95 text-white rounded-xl font-bold transition-all shadow-md flex items-center gap-2 cursor-pointer">
                    <svg :class="isRefreshing ? 'animate-spin' : ''" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M3 21v-5h5"/><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/></svg>
                    <span x-text="isRefreshing ? 'Refreshing...' : 'Refresh'"></span>
                </button>
            </div>
        </div>

        <!-- 3-Zone Layout Container -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start w-full">
            
            <!-- LEFT COLUMN: ZONE 1 (Order Feed) & ZONE 2 (Order Details Desk) -->
            <div class="lg:col-span-5 space-y-8">
                
                <!-- ZONE 1: Active Order Feed -->
                <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 shadow-sm space-y-5">
                    <div class="flex items-center justify-between">
                        <h3 class="font-extrabold text-lg text-gray-900 dark:text-white flex items-center gap-2">
                            <span>Active Orders</span>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300" x-text="filteredOrders.length"></span>
                        </h3>
                        <span class="text-xs font-bold text-gray-400 bg-gray-50 dark:bg-white/5 px-2.5 py-1 rounded-lg">Zone 1</span>
                    </div>

                    <!-- Search Bar -->
                    <div class="relative">
                        <input x-model="search" type="text" placeholder="Search Order ID, Driver, Merchant..." class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        </div>
                    </div>

                    <!-- Status Filters -->
                    <div class="flex flex-wrap gap-2">
                        <button @click="selectedFilter = 'ALL'" :class="selectedFilter === 'ALL' ? 'bg-brand-500 text-white font-bold' : 'bg-gray-100 dark:bg-white/5 text-gray-600 dark:text-gray-400 hover:bg-gray-200'" class="px-3.5 py-1.5 rounded-xl text-xs transition-colors">
                            All
                        </button>
                        <button @click="selectedFilter = 'PENDING PICKUP'" :class="selectedFilter === 'PENDING PICKUP' ? 'bg-amber-500 text-white font-bold' : 'bg-gray-100 dark:bg-white/5 text-gray-600 dark:text-gray-400 hover:bg-gray-200'" class="px-3.5 py-1.5 rounded-xl text-xs transition-colors">
                            Pending Pickup
                        </button>
                        <button @click="selectedFilter = 'IN-TRANSIT'" :class="selectedFilter === 'IN-TRANSIT' ? 'bg-blue-600 text-white font-bold' : 'bg-gray-100 dark:bg-white/5 text-gray-600 dark:text-gray-400 hover:bg-gray-200'" class="px-3.5 py-1.5 rounded-xl text-xs transition-colors">
                            In-Transit
                        </button>
                        <button @click="selectedFilter = 'DELAYED'" :class="selectedFilter === 'DELAYED' ? 'bg-rose-600 text-white font-bold' : 'bg-gray-100 dark:bg-white/5 text-gray-600 dark:text-gray-400 hover:bg-gray-200'" class="px-3.5 py-1.5 rounded-xl text-xs transition-colors">
                            Delayed
                        </button>
                        <button @click="selectedFilter = 'COMPLETED'" :class="selectedFilter === 'COMPLETED' ? 'bg-emerald-600 text-white font-bold' : 'bg-gray-100 dark:bg-white/5 text-gray-600 dark:text-gray-400 hover:bg-gray-200'" class="px-3.5 py-1.5 rounded-xl text-xs transition-colors">
                            Completed
                        </button>
                    </div>

                    <!-- Order Cards List -->
                    <div class="space-y-3 max-h-[380px] overflow-y-auto pr-1">
                        <template x-if="filteredOrders.length === 0">
                            <div class="p-8 text-center text-gray-400 text-xs italic">
                                No active delivery orders match your filters.
                            </div>
                        </template>

                        <template x-for="order in filteredOrders" :key="order.id">
                            <div @click="selectOrder(order)" 
                                 :class="selectedOrder && selectedOrder.id === order.id ? 'border-2 border-brand-500 bg-brand-50/20 dark:bg-brand-950/20 shadow-md' : 'border border-gray-100 dark:border-white/10 bg-gray-50/50 dark:bg-[#161616] hover:border-gray-300'"
                                 class="p-4 rounded-2xl cursor-pointer transition-all relative">
                                
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-extrabold text-sm text-gray-900 dark:text-white" x-text="order.digital_receipt_code"></span>
                                    
                                    <span :class="{
                                        'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border-amber-300': order.status_label === 'PENDING PICKUP',
                                        'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border-blue-300': order.status_label === 'IN-TRANSIT',
                                        'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border-rose-300 animate-pulse': order.status_label === 'DELAYED',
                                        'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-300': order.status_label === 'COMPLETED'
                                    }" class="px-3 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border">
                                        <span x-text="order.status_label"></span>
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 gap-2 text-xs mb-2">
                                    <div>
                                        <span class="text-gray-400 block text-[10px]">Driver</span>
                                        <span class="font-bold text-gray-900 dark:text-white" x-text="order.driver ? order.driver.name : 'Unassigned'"></span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-gray-400 block text-[10px]">Vehicle / Time</span>
                                        <span class="font-semibold text-gray-800 dark:text-gray-200" x-text="`${order.vehicle_type} (${order.elapsed_time})`"></span>
                                    </div>
                                </div>

                                <div class="text-[11px] text-gray-500 dark:text-gray-400 space-y-0.5 border-t border-gray-200 dark:border-white/5 pt-2">
                                    <div class="truncate">📍 <strong class="text-gray-700 dark:text-gray-300">From:</strong> <span x-text="order.pickup_location"></span></div>
                                    <div class="truncate">🏁 <strong class="text-gray-700 dark:text-gray-300">To:</strong> <span x-text="order.dropoff_location"></span></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- ZONE 2: Selected Order Details Desk -->
                <template x-if="selectedOrder">
                    <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 shadow-sm space-y-6">
                        
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <span class="text-xs font-extrabold text-brand-500 uppercase tracking-wider block">Zone 2 — Selected Order Desk</span>
                                    <h3 class="text-2xl font-black text-gray-900 dark:text-white" x-text="selectedOrder.digital_receipt_code"></h3>
                                </div>
                                <div class="text-right bg-brand-50 dark:bg-brand-950/40 p-3 rounded-2xl border border-brand-200 dark:border-brand-900/40">
                                    <span class="text-[10px] font-bold text-gray-400 block uppercase">Est. Arrival (ETA)</span>
                                    <span class="text-2xl font-black text-brand-600 dark:text-brand-400" x-text="`${selectedOrder.estimated_minutes} min`"></span>
                                </div>
                            </div>

                            <template x-if="selectedOrder.is_delayed">
                                <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/40 text-rose-800 dark:text-rose-300 flex items-center justify-between gap-3 shadow-sm">
                                    <div class="flex items-center gap-2 text-xs font-bold">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <span>Order Delayed — Courier delay / Traffic hold</span>
                                    </div>
                                    <button @click="openReassignModal()" class="px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-extrabold shadow-sm shrink-0">
                                        RE-ASSIGN ORDER
                                    </button>
                                </div>
                            </template>
                        </div>

                        <!-- 1. Participant Information -->
                        <div class="border-t border-gray-100 dark:border-white/10 pt-5 space-y-4">
                            <h4 class="text-xs font-extrabold uppercase tracking-wider text-gray-400">PARTICIPANT INFORMATION</h4>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                                <div class="p-4 bg-gray-50 dark:bg-[#161616] rounded-2xl border border-gray-100 dark:border-white/5 space-y-1">
                                    <span class="font-extrabold text-brand-600 dark:text-brand-400 uppercase tracking-wider block text-[10px]">SENDER</span>
                                    <div class="font-bold text-gray-900 dark:text-white text-sm" x-text="selectedOrder.sender.name"></div>
                                    <div class="text-gray-500 leading-snug">
                                        <strong class="text-gray-700 dark:text-gray-300">Accra Digital Address:</strong><br>
                                        <span x-text="selectedOrder.sender.address"></span>
                                    </div>
                                    <div class="text-[10px] text-gray-400 pt-1" x-text="`Merchant: ${selectedOrder.merchant_account}`"></div>
                                </div>

                                <div class="p-4 bg-gray-50 dark:bg-[#161616] rounded-2xl border border-gray-100 dark:border-white/5 space-y-1">
                                    <span class="font-extrabold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider block text-[10px]">RECEIVER</span>
                                    <div class="font-bold text-gray-900 dark:text-white text-sm" x-text="selectedOrder.receiver.name"></div>
                                    <div class="text-gray-500 leading-snug">
                                        <strong class="text-gray-700 dark:text-gray-300">Contact Phone:</strong> <span class="font-bold text-gray-800 dark:text-gray-200" x-text="selectedOrder.receiver.phone"></span><br>
                                        <strong class="text-gray-700 dark:text-gray-300">Destination:</strong> <span x-text="selectedOrder.receiver.address"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Dynamic Financial Split (15% / 85%) -->
                        <div class="border-t border-gray-100 dark:border-white/10 pt-5 space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-extrabold uppercase tracking-wider text-gray-400">FINANCIAL SPLIT LEDGER</h4>
                                <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-900/30">
                                    15% Platform / 85% Driver Model
                                </span>
                            </div>

                            <div class="p-4 bg-gray-50 dark:bg-[#161616] rounded-2xl border border-gray-100 dark:border-white/5 space-y-2 text-xs">
                                <div class="flex justify-between font-semibold text-gray-700 dark:text-gray-300">
                                    <span>Gross Delivery Fare:</span>
                                    <span class="font-black text-gray-900 dark:text-white" x-text="`GH₵ ${selectedOrder.gross_fare}`"></span>
                                </div>
                                <div class="flex justify-between font-semibold text-brand-600 dark:text-brand-400">
                                    <span>Platform Fee — 15%:</span>
                                    <span class="font-black" x-text="`GH₵ ${selectedOrder.platform_fee_15}`"></span>
                                </div>
                                <div class="flex justify-between font-semibold text-green-600 dark:text-green-400 pt-1.5 border-t border-gray-200 dark:border-white/10">
                                    <span>Driver / Fleet Owner Payout — 85%:</span>
                                    <span class="font-black text-base" x-text="`GH₵ ${selectedOrder.driver_payout_85}`"></span>
                                </div>
                                <div class="text-[10px] text-gray-400 pt-1 flex justify-between">
                                    <span>Payment Method: <strong class="text-gray-700 dark:text-gray-300" x-text="selectedOrder.payment_method"></strong></span>
                                    <span>Calculated dynamically from fare</span>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Proof of Delivery (PoD) -->
                        <div class="border-t border-gray-100 dark:border-white/10 pt-5 space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-extrabold uppercase tracking-wider text-gray-400">PROOF OF DELIVERY (PoD)</h4>
                                <span :class="selectedOrder.pod.status === 'VERIFIED' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'" class="px-3 py-0.5 rounded-full text-[10px] font-black uppercase">
                                    <span x-text="selectedOrder.pod.status"></span>
                                </span>
                            </div>

                            <div class="p-4 bg-gray-50 dark:bg-[#161616] rounded-2xl border border-gray-100 dark:border-white/5 text-xs space-y-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-gray-400 block text-[10px]">PoD Timestamp</span>
                                        <span class="font-semibold text-gray-800 dark:text-gray-200" x-text="selectedOrder.pod.timestamp || 'Awaiting Delivery Completion'"></span>
                                    </div>
                                    <button @click="openPodModal()" class="px-4 py-2 bg-gray-900 hover:bg-black text-white rounded-xl font-bold text-xs transition-colors">
                                        VIEW PROOF OF DELIVERY
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Actions Desk -->
                        <div class="pt-2 flex items-center gap-3">
                            <button @click="openReassignModal()" class="flex-1 py-3.5 px-4 bg-brand-500 hover:bg-brand-600 text-white rounded-2xl font-bold text-xs transition-all shadow-md text-center">
                                🔄 Re-Assign Order
                            </button>
                            <button @click="centerMapOnSelected()" class="py-3.5 px-4 bg-gray-100 dark:bg-white/10 hover:bg-gray-200 text-gray-900 dark:text-white rounded-2xl font-bold text-xs transition-all text-center">
                                📍 Center Map
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- RIGHT COLUMN: ZONE 3 (Live Geospatial Map ~65% Width) -->
            <div class="lg:col-span-7">
                <div class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 shadow-sm space-y-4 sticky top-24">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-brand-500 uppercase tracking-wider">Zone 3 — Live Geospatial Map</span>
                            <span class="px-2.5 py-0.5 bg-green-500/10 text-green-500 font-bold text-[10px] rounded-full">Real-time Stream</span>
                        </div>
                        <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400 font-semibold">
                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-green-500 inline-block"></span> Pickup</span>
                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-brand-500 inline-block"></span> Driver</span>
                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block"></span> Dropoff</span>
                        </div>
                    </div>

                    <!-- Map Render Container -->
                    <div class="relative w-full h-[600px] rounded-2xl overflow-hidden border border-gray-200 dark:border-white/10 bg-gray-100 dark:bg-[#1a1a1a]">
                        <div id="live_tracker_map_standalone" class="w-full h-full"></div>

                        <!-- Floating Live Info Overlay -->
                        <template x-if="selectedOrder">
                            <div class="absolute bottom-4 left-4 right-4 bg-white/95 dark:bg-black/90 backdrop-blur-md p-4 rounded-2xl border border-gray-200 dark:border-white/10 shadow-xl flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                                <div>
                                    <span class="font-extrabold text-gray-900 dark:text-white text-sm" x-text="selectedOrder.digital_receipt_code"></span>
                                    <span class="text-gray-500 dark:text-gray-400 block" x-text="`Driver: ${selectedOrder.driver ? selectedOrder.driver.name : 'Unassigned'} (${selectedOrder.vehicle_type})`"></span>
                                </div>
                                <div class="flex items-center gap-3 text-right">
                                    <div>
                                        <span class="text-gray-400 block text-[10px]">Distance / Route</span>
                                        <span class="font-bold text-gray-900 dark:text-white">Active Polyline Breadcrumb</span>
                                    </div>
                                    <div class="bg-brand-500 text-white font-black px-4 py-2 rounded-xl text-sm">
                                        <span x-text="`${selectedOrder.estimated_minutes} min`"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

        </div>

        <!-- RE-ASSIGN DRIVER MODAL -->
        <div x-show="showReassignModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <div @click.away="showReassignModal = false" class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 md:p-8 max-w-lg w-full shadow-2xl space-y-6">
                
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-xl font-extrabold text-gray-900 dark:text-white">Re-Assign Delivery Order</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="`Order: ${selectedOrder ? selectedOrder.digital_receipt_code : ''}`"></p>
                    </div>
                    <button @click="showReassignModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">✕</button>
                </div>

                <div class="space-y-3">
                    <label class="block text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Select Available Verified Driver</label>
                    <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                        <template x-for="driver in availableDrivers" :key="driver.id">
                            <div @click="selectedDriverForReassign = driver"
                                 :class="selectedDriverForReassign && selectedDriverForReassign.id === driver.id ? 'border-2 border-brand-500 bg-brand-50/20' : 'border border-gray-200 dark:border-white/10 hover:border-gray-300'"
                                 class="p-3.5 rounded-xl cursor-pointer flex items-center justify-between transition-colors">
                                <div>
                                    <div class="font-bold text-xs text-gray-900 dark:text-white" x-text="driver.name"></div>
                                    <div class="text-[11px] text-gray-500" x-text="`${driver.location} • ${driver.distance} from pickup`"></div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs font-bold text-amber-600" x-text="`★ ${driver.rating}`"></div>
                                    <div class="text-[10px] text-green-600 font-semibold">Available</div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <template x-if="selectedDriverForReassign">
                    <div class="p-3.5 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 text-amber-800 dark:text-amber-300 rounded-xl text-xs">
                        Confirmation: Reassign this delivery order to <strong x-text="selectedDriverForReassign.name"></strong>?
                    </div>
                </template>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-white/10">
                    <button @click="showReassignModal = false" class="px-5 py-2.5 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-bold">
                        CANCEL
                    </button>
                    <button @click="confirmReassignment()" :disabled="!selectedDriverForReassign" class="px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-xs font-extrabold shadow-md disabled:opacity-50">
                        CONFIRM REASSIGNMENT
                    </button>
                </div>
            </div>
        </div>

        <!-- PROOF OF DELIVERY DISPUTE MODAL -->
        <div x-show="showPodModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <div @click.away="showPodModal = false" class="bg-white dark:bg-[#111] rounded-3xl border border-gray-200 dark:border-white/10 p-6 md:p-8 max-w-lg w-full shadow-2xl space-y-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-xl font-extrabold text-gray-900 dark:text-white">Proof of Delivery (PoD) Record</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="`Dispute Resolution Desk — Order ${selectedOrder ? selectedOrder.digital_receipt_code : ''}`"></p>
                    </div>
                    <button @click="showPodModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">✕</button>
                </div>

                <template x-if="selectedOrder">
                    <div class="space-y-4 text-xs">
                        <div class="grid grid-cols-2 gap-3 p-3.5 bg-gray-50 dark:bg-[#161616] rounded-xl border">
                            <div>
                                <span class="text-gray-400 block text-[10px]">Order ID</span>
                                <span class="font-bold text-gray-900 dark:text-white" x-text="selectedOrder.digital_receipt_code"></span>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[10px]">PoD Status</span>
                                <span class="font-black text-green-600" x-text="selectedOrder.pod.status"></span>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[10px]">Driver</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-200" x-text="selectedOrder.driver ? selectedOrder.driver.name : 'N/A'"></span>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[10px]">Completion Timestamp</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-200" x-text="selectedOrder.pod.timestamp || 'N/A'"></span>
                            </div>
                        </div>

                        <div>
                            <span class="font-bold text-gray-900 dark:text-white block mb-2">Delivery Photo Proof</span>
                            <div class="w-full h-48 rounded-xl bg-gray-100 dark:bg-[#222] border overflow-hidden flex items-center justify-center">
                                <template x-if="selectedOrder.pod.photo_url">
                                    <img :src="selectedOrder.pod.photo_url" class="w-full h-full object-cover" alt="PoD Delivery Photo">
                                </template>
                                <template x-if="!selectedOrder.pod.photo_url">
                                    <span class="text-gray-400 italic">No delivery photo attached</span>
                                </template>
                            </div>
                        </div>

                        <div>
                            <span class="font-bold text-gray-900 dark:text-white block mb-2">Recipient Signature Proof</span>
                            <div class="p-3 rounded-xl bg-gray-50 dark:bg-[#161616] border flex items-center gap-3">
                                <template x-if="selectedOrder.pod.signature_url">
                                    <img :src="selectedOrder.pod.signature_url" class="h-10 rounded border" alt="Signature">
                                </template>
                                <span class="text-gray-600 dark:text-gray-300 font-semibold" x-text="`Verified Recipient: ${selectedOrder.receiver.name}`"></span>
                            </div>
                        </div>
                    </div>
                </template>

                <div class="pt-4 border-t border-gray-100 dark:border-white/10 text-right">
                    <button @click="showPodModal = false" class="px-6 py-2.5 bg-gray-900 text-white rounded-xl text-xs font-bold">
                        CLOSE PROOF OF DELIVERY
                    </button>
                </div>
            </div>
        </div>
    </main>

    @php
        $gmapsKey = config('services.google_maps.api_key');
        $hasValidKey = !empty($gmapsKey) && !str_contains($gmapsKey, 'AIzaSyDemoKey');
    @endphp

    @if($hasValidKey)
        <script src="https://maps.googleapis.com/maps/api/js?key={{ $gmapsKey }}&libraries=places"></script>
    @else
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @endif

    <script>
        function liveDeliveryTracker() {
            const initialOrders = window.INITIAL_DELIVERY_ORDERS || [];
            const initialDrivers = window.INITIAL_AVAILABLE_DRIVERS || [];

            return {
                search: '',
                selectedFilter: 'ALL',
                orders: initialOrders,
                availableDrivers: initialDrivers,
                selectedOrder: initialOrders.length > 0 ? initialOrders[0] : null,
                selectedDriverForReassign: null,
                showReassignModal: false,
                showPodModal: false,
                map: null,
                pickupMarker: null,
                driverMarker: null,
                dropoffMarker: null,
                routePolyline: null,
                isRefreshing: false,
                refreshToast: false,

                async refreshStream() {
                    this.isRefreshing = true;
                    await this.fetchData();
                    if (this.selectedOrder) {
                        this.updateMapMarkers();
                    }
                    this.refreshToast = true;
                    setTimeout(() => {
                        this.isRefreshing = false;
                    }, 500);
                    setTimeout(() => {
                        this.refreshToast = false;
                    }, 2500);
                },

                get filteredOrders() {
                    return this.orders.filter(o => {
                        const searchLower = this.search.toLowerCase();
                        const matchesSearch = !this.search || 
                            o.digital_receipt_code.toLowerCase().includes(searchLower) ||
                            (o.driver && o.driver.name.toLowerCase().includes(searchLower)) ||
                            o.merchant_account.toLowerCase().includes(searchLower);

                        const matchesFilter = this.selectedFilter === 'ALL' || o.status_label === this.selectedFilter;
                        return matchesSearch && matchesFilter;
                    });
                },

                async init() {
                    if (!this.orders || this.orders.length === 0) {
                        await this.fetchData();
                    }
                    if (!this.selectedOrder && this.orders.length > 0) {
                        this.selectedOrder = this.orders[0];
                    }
                    setTimeout(() => {
                        this.initMap();
                    }, 150);
                    
                    setInterval(() => {
                        this.fetchData(true);
                    }, 10000);
                },

                async fetchData(silent = false) {
                    try {
                        const response = await fetch('/admin/live-delivery-tracker/data');
                        if (response.ok) {
                            const data = await response.json();
                            if (data.success) {
                                this.orders = data.orders || [];
                                this.availableDrivers = data.available_drivers || [];

                                if (!this.selectedOrder && this.orders.length > 0) {
                                    this.selectedOrder = this.orders[0];
                                } else if (this.selectedOrder) {
                                    const updated = this.orders.find(o => o.id === this.selectedOrder.id);
                                    if (updated) {
                                        this.selectedOrder = updated;
                                    }
                                }

                                if (this.map && this.selectedOrder) {
                                    this.updateMapMarkers();
                                }
                            }
                        }
                    } catch (e) {}
                },

                selectOrder(order) {
                    this.selectedOrder = order;
                    this.updateMapMarkers();
                },

                initMap() {
                    const mapEl = document.getElementById("live_tracker_map_standalone");
                    if (!mapEl) return;

                    const defaultLat = this.selectedOrder ? this.selectedOrder.current_lat : 5.6037;
                    const defaultLng = this.selectedOrder ? this.selectedOrder.current_lng : -0.1870;

                    if (typeof google !== 'undefined' && google.maps) {
                        try {
                            this.map = new google.maps.Map(mapEl, {
                                center: { lat: defaultLat, lng: defaultLng },
                                zoom: 13,
                                mapTypeControl: false,
                                streetViewControl: false,
                            });
                        } catch (e) {}
                    } else if (typeof L !== 'undefined') {
                        try {
                            if (this.map) {
                                this.map.remove();
                            }
                            this.map = L.map(mapEl).setView([defaultLat, defaultLng], 13);
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                maxZoom: 19,
                                attribution: '© OpenStreetMap'
                            }).addTo(this.map);
                        } catch (e) {}
                    }

                    this.updateMapMarkers();
                },

                updateMapMarkers() {
                    if (!this.map || !this.selectedOrder) return;

                    const pLat = this.selectedOrder.pickup_lat;
                    const pLng = this.selectedOrder.pickup_lng;
                    const dLat = this.selectedOrder.dropoff_lat;
                    const dLng = this.selectedOrder.dropoff_lng;
                    const cLat = this.selectedOrder.current_lat;
                    const cLng = this.selectedOrder.current_lng;

                    if (typeof google !== 'undefined' && google.maps) {
                        if (this.pickupMarker) this.pickupMarker.setMap(null);
                        if (this.driverMarker) this.driverMarker.setMap(null);
                        if (this.dropoffMarker) this.dropoffMarker.setMap(null);

                        this.pickupMarker = new google.maps.Marker({
                            position: { lat: pLat, lng: pLng },
                            map: this.map,
                            title: "Pickup: " + this.selectedOrder.pickup_location,
                            icon: "http://maps.google.com/mapfiles/ms/icons/green-dot.png"
                        });

                        this.driverMarker = new google.maps.Marker({
                            position: { lat: cLat, lng: cLng },
                            map: this.map,
                            title: "Driver Location",
                            icon: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png"
                        });

                        this.dropoffMarker = new google.maps.Marker({
                            position: { lat: dLat, lng: dLng },
                            map: this.map,
                            title: "Dropoff: " + this.selectedOrder.dropoff_location,
                            icon: "http://maps.google.com/mapfiles/ms/icons/red-dot.png"
                        });

                        if (this.routePolyline) this.routePolyline.setMap(null);
                        this.routePolyline = new google.maps.Polyline({
                            path: [
                                { lat: pLat, lng: pLng },
                                { lat: cLat, lng: cLng },
                                { lat: dLat, lng: dLng }
                            ],
                            geodesic: true,
                            strokeColor: "#F59E0B",
                            strokeOpacity: 0.8,
                            strokeWeight: 4
                        });
                        this.routePolyline.setMap(this.map);
                        this.map.setCenter({ lat: cLat, lng: cLng });
                    } else if (typeof L !== 'undefined') {
                        if (this.pickupMarker) this.map.removeLayer(this.pickupMarker);
                        if (this.driverMarker) this.map.removeLayer(this.driverMarker);
                        if (this.dropoffMarker) this.map.removeLayer(this.dropoffMarker);
                        if (this.routePolyline) this.map.removeLayer(this.routePolyline);

                        this.pickupMarker = L.marker([pLat, pLng]).addTo(this.map).bindPopup("🟢 Pickup: " + this.selectedOrder.pickup_location);
                        this.driverMarker = L.marker([cLat, cLng]).addTo(this.map).bindPopup("🚚 Driver: " + (this.selectedOrder.driver ? this.selectedOrder.driver.name : 'Courier'));
                        this.dropoffMarker = L.marker([dLat, dLng]).addTo(this.map).bindPopup("🔴 Dropoff: " + this.selectedOrder.dropoff_location);

                        this.routePolyline = L.polyline([
                            [pLat, pLng],
                            [cLat, cLng],
                            [dLat, dLng]
                        ], { color: '#F59E0B', weight: 4, opacity: 0.8 }).addTo(this.map);

                        this.map.setView([cLat, cLng], 13);
                    }
                },

                centerMapOnSelected() {
                    if (!this.map) {
                        this.initMap();
                    }
                    if (this.selectedOrder) {
                        const lat = parseFloat(this.selectedOrder.current_lat) || parseFloat(this.selectedOrder.pickup_lat) || 5.6037;
                        const lng = parseFloat(this.selectedOrder.current_lng) || parseFloat(this.selectedOrder.pickup_lng) || -0.1870;

                        if (this.map) {
                            if (typeof google !== 'undefined' && google.maps && typeof this.map.setCenter === 'function') {
                                this.map.setCenter({ lat, lng });
                                this.map.setZoom(15);
                            } else if (typeof L !== 'undefined' && typeof this.map.setView === 'function') {
                                try { this.map.invalidateSize(); } catch(e){}
                                this.map.setView([lat, lng], 15);
                            }
                        }

                        const mapEl = document.getElementById("live_tracker_map_standalone");
                        if (mapEl && window.innerWidth < 1024) {
                            mapEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }
                },

                openReassignModal() {
                    this.selectedDriverForReassign = null;
                    this.showReassignModal = true;
                },

                openPodModal() {
                    this.showPodModal = true;
                },

                async confirmReassignment() {
                    if (!this.selectedOrder || !this.selectedDriverForReassign) return;

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        const response = await fetch('/admin/live-delivery-tracker/reassign', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken || '',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                order_id: this.selectedOrder.id,
                                driver_id: this.selectedDriverForReassign.id,
                            })
                        });

                        if (response.ok) {
                            const result = await response.json();
                            if (result.success) {
                                alert(result.message);
                                this.showReassignModal = false;
                                await this.fetchData();
                            } else {
                                alert(result.error || 'Failed to reassign driver.');
                            }
                        }
                    } catch (e) {
                        alert('Network error during driver reassignment.');
                    }
                }
            }
        }
    </script>

    <!-- Refresh Toast Notification -->
    <div x-show="refreshToast" x-cloak x-transition.opacity.duration.300ms class="fixed top-6 right-6 z-50 bg-emerald-600 text-white font-extrabold text-xs px-5 py-3.5 rounded-2xl shadow-2xl flex items-center gap-2 border border-emerald-400/30">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        <span>Live Delivery Stream Refreshed Successfully!</span>
    </div>
</x-layout>
