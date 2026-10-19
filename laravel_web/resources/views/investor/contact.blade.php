<x-layout title="Contact Investor Relations | NDFG LLC & Ride My Cars (Ghana)">
    <x-investor-nav />

    <div class="min-h-screen bg-slate-50 dark:bg-[#0b0f17] text-slate-900 dark:text-white relative overflow-hidden transition-colors selection:bg-brand-500 selection:text-black">
        <!-- Ambient glows -->
        <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[850px] h-[450px] bg-gradient-to-tr from-amber-500/10 via-brand-500/5 to-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl 2xl:max-w-[1600px] w-full mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 pt-12 sm:pt-16 pb-20 relative z-10">
            <!-- Header -->
            <div class="text-center max-w-3xl mx-auto mb-16">
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-amber-50 dark:bg-amber-500/15 border border-amber-200 dark:border-amber-500/30 text-amber-800 dark:text-amber-300 text-xs font-black uppercase tracking-wider mb-3">
                    ✉️ Institutional Inquiries
                </div>
                <h1 class="text-3xl sm:text-5xl font-black text-slate-900 dark:text-white tracking-tight">Contact Investor Relations</h1>
                <p class="text-base sm:text-lg text-slate-600 dark:text-slate-300 mt-4 leading-relaxed font-medium">
                    Direct institutional communications channel for <strong>California</strong>, <strong>Atlanta</strong>, and <strong>Georgia</strong> offerings ($200k/4%, $500k/11%, $1M/22%) and Ride My Cars <strong>Ghana</strong> regional expansion.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 max-w-7xl 2xl:max-w-[1400px] mx-auto">
                <!-- Contact Info Panel (Col 5) -->
                <div class="lg:col-span-5 space-y-6">
                    <!-- Key Personnel Card with Solid Borders -->
                    <div class="p-7 rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-sm">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 rounded-2xl bg-amber-100 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-500/40 flex items-center justify-center font-black text-lg shadow-xs">
                                MW
                            </div>
                            <div>
                                <h3 class="text-base font-black text-slate-900 dark:text-white">Marilyn Watson</h3>
                                <p class="text-xs text-amber-700 dark:text-amber-400 font-bold">Investor Relations Manager</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Ride My Cars New Development Finance Group LLC</p>
                            </div>
                        </div>

                        <div class="space-y-3 pt-3 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-300">
                            <div class="flex items-center gap-2.5">
                                <span class="text-slate-400">📧</span>
                                <a href="mailto:investors@ridemycars.com" class="hover:text-amber-600 dark:hover:text-brand-400 transition-colors font-medium">investors@ridemycars.com</a>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <span class="text-slate-400">📧</span>
                                <a href="mailto:marilyn@newdevelopmentfinance.org" class="hover:text-amber-600 dark:hover:text-brand-400 transition-colors font-medium">marilyn@newdevelopmentfinance.org</a>
                            </div>
                        </div>
                    </div>

                    <!-- Regional Coordination Partner with Solid Borders -->
                    <div class="p-7 rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-sm">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-500/40 flex items-center justify-center font-black text-sm shadow-xs">
                                RMC
                            </div>
                            <div>
                                <h3 class="text-base font-black text-slate-900 dark:text-white">Ride My Cars (Ghana)</h3>
                                <p class="text-xs text-emerald-700 dark:text-emerald-400 font-bold">Regional Escrow & Compliance Partner</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Republic of Ghana Operations Hub</p>
                            </div>
                        </div>

                        <div class="space-y-3 pt-3 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-300">
                            <div class="flex items-start gap-2.5">
                                <span class="text-slate-400">📍</span>
                                <span>No 1 Airport Square, 8th FL, Airport City, Accra, Ghana</span>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <span class="text-slate-400">🏛️</span>
                                <span>Liaison Desk for SEC Ghana & Bank of Ghana remittance compliance</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-5 rounded-2xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 text-xs text-amber-900 dark:text-amber-300 leading-relaxed font-medium">
                        ⚡ <strong>Priority Response SLA:</strong> Institutional and accredited investor inquiries are responded to within 12 business hours by compliance personnel.
                    </div>
                </div>

                <!-- Form Panel (Col 7) with High Contrast Inputs -->
                <div class="lg:col-span-7">
                    <div class="p-8 sm:p-10 rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-lg">
                        <h2 class="text-xl font-black text-slate-900 dark:text-white mb-2">Send an Official Inquiry</h2>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mb-6">Complete this form to connect with our Investor Relations team or schedule a private data room walk-through.</p>

                        @if(session('success'))
                            <div class="p-4 mb-6 rounded-2xl bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-300 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300 text-xs font-bold flex items-center gap-2">
                                <span>✓</span>
                                <span>{{ session('success') }}</span>
                            </div>
                        @endif

                        <form method="POST" action="/investor/contact" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Legal Entity or Individual Name *</label>
                                <input type="text" name="name" required value="{{ old('name') }}" placeholder="e.g. Apex Holdings LLC / John Doe" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:bg-white focus:border-brand-500 transition-colors">
                                @error('name') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Contact Email *</label>
                                    <input type="email" name="email" required value="{{ old('email') }}" placeholder="investor@domain.com" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:bg-white focus:border-brand-500 transition-colors">
                                    @error('email') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Phone Number with Country Code *</label>
                                    <input type="text" name="phone" required value="{{ old('phone') }}" placeholder="+1 (555) 000-0000" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:bg-white focus:border-brand-500 transition-colors">
                                    @error('phone') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Primary Jurisdiction / State *</label>
                                    @php
                                        $reqCountry = strtolower(request('country', request('state', '')));
                                    @endphp
                                    <select name="country" required class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:bg-white focus:border-brand-500 transition-colors">
                                        <optgroup label="🇺🇸 US State Offerings (Live & Open)">
                                            <option value="California" {{ $reqCountry === 'california' ? 'selected' : '' }}>🐻 California (Live Offering • $200k / $500k / $1M)</option>
                                            <option value="Atlanta" {{ $reqCountry === 'atlanta' ? 'selected' : '' }}>🍑 Atlanta Metro (Live Offering • $200k / $500k / $1M)</option>
                                            <option value="Georgia" {{ $reqCountry === 'georgia' ? 'selected' : '' }}>🏛️ Georgia (Live Offering • $200k / $500k / $1M)</option>
                                            <option value="USA" {{ ($reqCountry === 'usa' || $reqCountry === 'united states') ? 'selected' : '' }}>🇺🇸 United States (Other / Multi-State)</option>
                                        </optgroup>
                                        <optgroup label="🌍 International Offerings">
                                            <option value="Ghana" {{ ($reqCountry === 'ghana' || $reqCountry === 'gha') ? 'selected' : '' }}>🇬🇭 Ghana (Live Offering)</option>
                                            <option value="Canada" {{ $reqCountry === 'canada' ? 'selected' : '' }}>🇨🇦 Canada</option>
                                            <option value="United Kingdom" {{ ($reqCountry === 'united kingdom' || $reqCountry === 'uk') ? 'selected' : '' }}>🇬🇧 United Kingdom</option>
                                            <option value="European Union" {{ $reqCountry === 'european union' ? 'selected' : '' }}>🇪🇺 European Union</option>
                                            <option value="South Africa" {{ $reqCountry === 'south africa' ? 'selected' : '' }}>🇿🇦 South Africa</option>
                                            <option value="Other Africa" {{ $reqCountry === 'other africa' ? 'selected' : '' }}>🌍 Other African Country</option>
                                            <option value="Other">Rest of World</option>
                                        </optgroup>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Tranche of Interest</label>
                                    @php
                                        $reqTranche = request('interested_tranche', request('tranche', ''));
                                    @endphp
                                    <select name="interested_tranche" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:bg-white focus:border-brand-500 transition-colors">
                                        <optgroup label="🐻 California Offerings ($200k / $500k / $1M)">
                                            <option value="California Tranche CA-1 ($200,000 USD • 4% Equity)" {{ (str_contains($reqTranche, 'CA-1') || str_contains($reqTranche, 'California Tier 1')) ? 'selected' : '' }}>
                                                California Tier 1: $200,000 USD (4% Fixed Equity)
                                            </option>
                                            <option value="California Tranche CA-2 ($500,000 USD • 11% Equity)" {{ (str_contains($reqTranche, 'CA-2') || str_contains($reqTranche, 'California Tier 2')) ? 'selected' : '' }}>
                                                California Tier 2: $500,000 USD (11% Fixed Equity) ★
                                            </option>
                                            <option value="California Tranche CA-3 ($1,000,000 USD • 22% Equity)" {{ (str_contains($reqTranche, 'CA-3') || str_contains($reqTranche, 'California Tier 3')) ? 'selected' : '' }}>
                                                California Tier 3: $1,000,000 USD (22% Fixed Equity)
                                            </option>
                                        </optgroup>
                                        <optgroup label="🍑 Atlanta Metro Offerings ($200k / $500k / $1M)">
                                            <option value="Atlanta Tranche ATL-1 ($200,000 USD • 4% Equity)" {{ (str_contains($reqTranche, 'ATL-1') || str_contains($reqTranche, 'Atlanta Tier 1')) ? 'selected' : '' }}>
                                                Atlanta Tier 1: $200,000 USD (4% Fixed Equity)
                                            </option>
                                            <option value="Atlanta Tranche ATL-2 ($500,000 USD • 11% Equity)" {{ (str_contains($reqTranche, 'ATL-2') || str_contains($reqTranche, 'Atlanta Tier 2')) ? 'selected' : '' }}>
                                                Atlanta Tier 2: $500,000 USD (11% Fixed Equity) ★
                                            </option>
                                            <option value="Atlanta Tranche ATL-3 ($1,000,000 USD • 22% Equity)" {{ (str_contains($reqTranche, 'ATL-3') || str_contains($reqTranche, 'Atlanta Tier 3')) ? 'selected' : '' }}>
                                                Atlanta Tier 3: $1,000,000 USD (22% Fixed Equity)
                                            </option>
                                        </optgroup>
                                        <optgroup label="🏛️ Georgia State Offerings ($200k / $500k / $1M)">
                                            <option value="Georgia Tranche GA-1 ($200,000 USD • 4% Equity)" {{ (str_contains($reqTranche, 'GA-1') || str_contains($reqTranche, 'Georgia Tier 1')) ? 'selected' : '' }}>
                                                Georgia Tier 1: $200,000 USD (4% Fixed Equity)
                                            </option>
                                            <option value="Georgia Tranche GA-2 ($500,000 USD • 11% Equity)" {{ (str_contains($reqTranche, 'GA-2') || str_contains($reqTranche, 'Georgia Tier 2')) ? 'selected' : '' }}>
                                                Georgia Tier 2: $500,000 USD (11% Fixed Equity) ★
                                            </option>
                                            <option value="Georgia Tranche GA-3 ($1,000,000 USD • 22% Equity)" {{ (str_contains($reqTranche, 'GA-3') || str_contains($reqTranche, 'Georgia Tier 3')) ? 'selected' : '' }}>
                                                Georgia Tier 3: $1,000,000 USD (22% Fixed Equity)
                                            </option>
                                        </optgroup>
                                        <optgroup label="🇬🇭 Ghana Cohort Offerings">
                                            <option value="Tranche B (Growth Tier - 14% Equity)" {{ ($reqTranche === 'B' || str_contains($reqTranche, 'Tranche B')) ? 'selected' : '' }}>Tranche B: 1,440,000 GHC ($120k • 14% Equity)</option>
                                            <option value="Tranche A (Seed Tier - 7% Equity)" {{ ($reqTranche === 'A' || str_contains($reqTranche, 'Tranche A')) ? 'selected' : '' }}>Tranche A: 720,000 GHC ($60k • 7% Equity)</option>
                                            <option value="Tranche C (Venture Tier - 22% Equity)" {{ ($reqTranche === 'C' || str_contains($reqTranche, 'Tranche C')) ? 'selected' : '' }}>Tranche C: 2,640,000 GHC ($220k • 22% Equity)</option>
                                            <option value="Tranche D (Wealth builder - 1.20% Equity)" {{ ($reqTranche === 'D' || str_contains($reqTranche, 'Tranche D')) ? 'selected' : '' }}>Tranche D: 120,000 GHC ($10k • 1.2% Equity)</option>
                                        </optgroup>
                                        <option value="General Inquiry" {{ empty($reqTranche) ? 'selected' : '' }}>General Private Placement Inquiry</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Message / Inquiry Details *</label>
                                <textarea name="message" rows="4" required placeholder="Specify any particular legal, compliance, or capital remittance questions..." class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:bg-white focus:border-brand-500 transition-colors">{{ old('message') }}</textarea>
                                @error('message') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <button type="submit" class="w-full py-4 rounded-xl bg-brand-500 hover:bg-brand-400 text-black font-black text-sm uppercase tracking-wider shadow-md shadow-brand-500/25 transition-all hover:scale-[1.01]">
                                Submit Inquiry to Compliance Desk →
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
