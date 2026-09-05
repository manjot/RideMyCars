<x-layout title="Executive Memberships — RideMyCars" theme="theme-ride">
    <main class="flex-1 w-full max-w-7xl mx-auto px-4 py-16 sm:px-6 lg:px-8">
        
        <!-- Flash Alerts -->
        @if(session('success'))
            <div class="max-w-3xl mx-auto mb-8 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800/30 text-emerald-800 dark:text-emerald-200 font-semibold flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="max-w-3xl mx-auto mb-8 p-4 rounded-2xl bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800/30 text-rose-800 dark:text-rose-200 font-semibold flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Active Membership Banner (if user is logged in & has active membership) -->
        @auth
            @if(auth()->user()->membership_type && auth()->user()->membership_type !== 'none')
                <div class="max-w-4xl mx-auto mb-12 p-6 rounded-3xl bg-gradient-to-r from-brand-500/10 via-brand-500/5 to-transparent border border-brand-500/30 flex flex-col sm:flex-row items-center justify-between gap-6 shadow-md">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-brand-500 text-slate-950 flex items-center justify-center font-black text-2xl shadow-lg shadow-brand-500/30 shrink-0">
                            👑
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-extrabold text-xl text-gray-900 dark:text-white">
                                    Active Membership: <span class="capitalize text-brand-500">{{ auth()->user()->membership_type }} Tier</span>
                                </h3>
                                <span class="bg-brand-500/20 text-brand-600 dark:text-brand-400 border border-brand-500/30 px-2.5 py-0.5 rounded-full text-xs font-extrabold uppercase">
                                    {{ auth()->user()->membership_status }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                @if(auth()->user()->membership_type === 'club')
                                    $250.00/month billing • Guaranteed priority dispatch & complimentary luxury vehicle delivery
                                @elseif(auth()->user()->membership_type === 'corporate')
                                    Company: <span class="font-bold text-gray-900 dark:text-white">{{ auth()->user()->corporate_company_name ?? 'Corporate Account' }}</span> • Monthly consolidated billing & 24/7 concierge contact
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="shrink-0">
                        <span class="bg-brand-500 text-slate-950 inline-flex items-center gap-1.5 px-5 py-2.5 rounded-2xl text-xs font-black shadow-md shadow-brand-500/25">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            Privileges Active
                        </span>
                    </div>
                </div>
            @endif
        @endauth

        <div class="text-center max-w-3xl mx-auto mb-16">
            <div class="inline-block px-4 py-1.5 rounded-full bg-brand-500/10 border border-brand-500/30 text-brand-500 font-bold text-xs tracking-widest uppercase mb-4">
                Exclusive Access
            </div>
            <h1 class="text-4xl md:text-6xl font-extrabold text-gray-900 dark:text-white mb-6 tracking-tight">
                Executive Membership Tiers
            </h1>
            <p class="text-lg md:text-xl text-gray-500 dark:text-gray-400 leading-relaxed">
                Elevate your travel experience with prioritized dispatch, complimentary luxury deliveries, and corporate expense consolidation.
            </p>
        </div>

        <!-- Membership Tiers Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12 max-w-5xl mx-auto mb-16">
            
            <!-- Tier 1: Club Membership -->
            <div class="bg-white dark:bg-[#111] border-2 border-brand-500 rounded-3xl p-8 lg:p-10 shadow-2xl relative flex flex-col justify-between overflow-hidden">
                <div class="absolute top-6 right-6 bg-brand-500 text-slate-950 text-xs font-black px-3 py-1 rounded-full uppercase tracking-wider shadow-sm">
                    Popular
                </div>
                <div>
                    <div class="w-14 h-14 bg-brand-50 text-brand-500 rounded-2xl flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    </div>
                    <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-2">Club Membership</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Designed for frequent business travelers and high-net-worth individuals.</p>
                    
                    <div class="flex items-baseline gap-2 mb-8">
                        <span class="text-5xl font-extrabold text-gray-900 dark:text-white">$250</span>
                        <span class="text-gray-500 dark:text-gray-400 font-semibold">/ month</span>
                    </div>

                    <ul class="space-y-4 mb-8">
                        <li class="flex items-start gap-3 text-sm text-gray-700 dark:text-gray-300 font-medium">
                            <div class="w-5 h-5 rounded-full bg-brand-100 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 flex items-center justify-center shrink-0 mt-0.5">✓</div>
                            <span>Guaranteed 15-minute on-demand ride availability in core operational zones.</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-gray-700 dark:text-gray-300 font-medium">
                            <div class="w-5 h-5 rounded-full bg-brand-100 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 flex items-center justify-center shrink-0 mt-0.5">✓</div>
                            <span>Complimentary vehicle delivery and collection for luxury rentals.</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-gray-700 dark:text-gray-300 font-medium">
                            <div class="w-5 h-5 rounded-full bg-brand-100 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 flex items-center justify-center shrink-0 mt-0.5">✓</div>
                            <span>Preferred access to limited-edition supercars and flagship SUVs.</span>
                        </li>
                    </ul>
                </div>

                <form action="/membership/subscribe" method="POST" x-data="{ paymentMethod: 'stripe' }" class="space-y-4">
                    @csrf
                    <input type="hidden" name="membership_type" value="club">

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Select Payment Method</label>
                        <div class="relative">
                            <select name="payment_method" x-model="paymentMethod" class="w-full px-4 py-3 pr-10 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white text-sm font-medium cursor-pointer appearance-none">
                                <option value="stripe">💳 Stripe</option>
                                <option value="momo">📱 Momo Pay</option>
                                <option value="cash">💵 Cash</option>
                                <option value="applepay">🍏 Apple Pay</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-gray-500 dark:text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>

                        <!-- Card Fillup Information for Stripe -->
                        <x-stripe-card-input modelName="paymentMethod" value="stripe" />
                    </div>

                    @auth
                        @if(auth()->user()->membership_type === 'club')
                            <button type="submit" class="w-full py-4 bg-brand-500 hover:bg-brand-600 text-white font-extrabold rounded-2xl shadow-xl shadow-brand-500/25 transition-all text-base flex items-center justify-center gap-2 cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                Active Member — Renew $250/mo
                            </button>
                        @else
                            <button type="submit" class="w-full py-4 bg-brand-500 hover:bg-brand-600 text-white font-extrabold rounded-2xl shadow-xl shadow-brand-500/25 transition-all text-base">
                                Subscribe for $250/mo
                            </button>
                        @endif
                    @else
                        <button type="submit" class="w-full py-4 bg-brand-500 hover:bg-brand-600 text-white font-extrabold rounded-2xl shadow-xl shadow-brand-500/25 transition-all text-base">
                            Subscribe for $250/mo
                        </button>
                    @endauth
                </form>
            </div>

            <!-- Tier 2: Corporate Membership -->
            <div class="bg-white dark:bg-[#111] border-2 border-brand-500/50 hover:border-brand-500 rounded-3xl p-8 lg:p-10 shadow-xl transition-colors flex flex-col justify-between overflow-hidden">
                <div>
                    <div class="w-14 h-14 bg-brand-50 text-brand-500 rounded-2xl flex items-center justify-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-2">Corporate Membership</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Tailored for executive assistants, corporate travel managers, and family offices.</p>
                    
                    <div class="flex items-baseline gap-2 mb-8">
                        <span class="text-3xl font-extrabold text-gray-900 dark:text-white">Custom Enterprise Contract</span>
                    </div>

                    <ul class="space-y-4 mb-8">
                        <li class="flex items-start gap-3 text-sm text-gray-700 dark:text-gray-300 font-medium">
                            <div class="w-5 h-5 rounded-full bg-brand-100 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 flex items-center justify-center shrink-0 mt-0.5">✓</div>
                            <span>Consolidated monthly corporate billing and invoice management.</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-gray-700 dark:text-gray-300 font-medium">
                            <div class="w-5 h-5 rounded-full bg-brand-100 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 flex items-center justify-center shrink-0 mt-0.5">✓</div>
                            <span>Expense categorization with cost-center tracking.</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-gray-700 dark:text-gray-300 font-medium">
                            <div class="w-5 h-5 rounded-full bg-brand-100 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 flex items-center justify-center shrink-0 mt-0.5">✓</div>
                            <span>Dedicated 24/7 concierge account contact.</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-gray-700 dark:text-gray-300 font-medium">
                            <div class="w-5 h-5 rounded-full bg-brand-100 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 flex items-center justify-center shrink-0 mt-0.5">✓</div>
                            <span>Shared pool of vetted recurring drivers for company executives.</span>
                        </li>
                    </ul>
                </div>

                <form action="/membership/corporate-request" method="POST" class="space-y-3">
                    @csrf
                    <input type="text" name="company_name" required value="{{ auth()->user()->corporate_company_name ?? '' }}" placeholder="Company / Organization Name" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-xl text-sm">
                    @auth
                        @if(auth()->user()->membership_type === 'corporate')
                            <button type="submit" class="w-full py-4 bg-brand-500 hover:bg-brand-600 text-white font-extrabold rounded-2xl shadow-xl shadow-brand-500/25 transition-all text-base flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                Request Updated Corporate Plan
                            </button>
                        @else
                            <button type="submit" class="w-full py-4 bg-brand-500 hover:bg-brand-600 text-white font-extrabold rounded-2xl shadow-xl shadow-brand-500/25 transition-all text-base">
                                Request Corporate Account
                            </button>
                        @endif
                    @else
                        <button type="submit" class="w-full py-4 bg-brand-500 hover:bg-brand-600 text-white font-extrabold rounded-2xl shadow-xl shadow-brand-500/25 transition-all text-base">
                            Request Corporate Account
                        </button>
                    @endauth
                </form>
            </div>

        </div>
    </main>
    <x-stripe-modal serviceType="membership" />
</x-layout>
