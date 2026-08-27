<x-layout>
    <x-slot:title>File a Service Dispute Claim — RideMyCars</x-slot>

    <main class="flex-1 w-full max-w-4xl mx-auto px-4 py-10 sm:px-6 lg:px-8">
        
        <!-- Header Banner -->
        <div class="mb-10 p-6 sm:p-8 bg-slate-900 text-white rounded-3xl shadow-xl relative overflow-hidden border border-slate-800">
            <div class="relative z-10">
                <span class="inline-flex items-center gap-2 px-3.5 py-1 bg-amber-400/20 border border-amber-400/40 rounded-full text-xs font-bold uppercase tracking-widest text-amber-300 mb-3">
                    Contractual 72-Hour Resolution Window
                </span>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white">Submit Refund & Dispute Claim</h1>
                <p class="text-slate-200 text-sm mt-2 max-w-2xl font-medium leading-relaxed">
                    Submit claims for cancellation fee waivers, deposit pre-authorization holds, late delivery transit issues, or service misconduct.
                </p>
            </div>
        </div>

        <div class="bg-white dark:bg-[#141414] border border-gray-100 dark:border-white/10 rounded-3xl p-6 sm:p-8 shadow-sm">
            <form action="/disputes" method="POST" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Service Type *</label>
                        <select name="service_type" required class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none">
                            <option value="ride">On-Demand Ride</option>
                            <option value="rental">Vehicle Rental</option>
                            <option value="chauffeur">Chauffeur / Driver Hiring</option>
                            <option value="delivery">Package Delivery</option>
                            <option value="other">Other / Billing</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Booking / Receipt Reference Code</label>
                        <input type="text" name="booking_reference" placeholder="e.g. RIDE-99201 or RENT-2026-4421" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Dispute Category *</label>
                        <input type="text" name="category" required placeholder="e.g. Cancellation Fee Waiver Request" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Event Completion Date & Time</label>
                        <input type="datetime-local" name="event_completed_at" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Contact Email *</label>
                        <input type="email" name="contact_email" required value="{{ auth()->user()->email ?? old('contact_email') }}" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Contact Phone</label>
                        <input type="text" name="contact_phone" value="{{ auth()->user()->phone ?? old('contact_phone') }}" placeholder="+1 410 570 6639" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Statement & Detailed Evidence *</label>
                    <textarea name="description" rows="5" required placeholder="Provide a detailed statement explaining why this charge or service issue should be refunded or waived..." class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none"></textarea>
                </div>

                <button type="submit" class="w-full py-4 bg-amber-400 hover:bg-amber-500 text-slate-950 font-black text-sm rounded-2xl shadow-md transition-all">
                    ⚖️ Submit Dispute Claim
                </button>
            </form>
        </div>
    </main>
</x-layout>
