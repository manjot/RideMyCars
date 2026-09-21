<x-layout title="Contact Investor Relations | NDFG LLC & Eminsang Group">
    <div class="min-h-screen bg-[#070a0f] text-slate-100 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-20">
            <!-- Header -->
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-black uppercase tracking-widest text-brand-400">Institutional Inquiries</span>
                <h1 class="text-3xl sm:text-5xl font-black text-white mt-2">Contact Investor Relations</h1>
                <p class="text-sm sm:text-base text-zinc-300 mt-4 leading-relaxed">
                    Direct communications channel with the NDFG LLC Compliance Framework Desk and Eminsang Group Limited regional directors.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 max-w-6xl mx-auto">
                <!-- Contact Info Panel (Col 5) -->
                <div class="lg:col-span-5 space-y-6">
                    <!-- Key Personnel Card -->
                    <div class="p-6 rounded-3xl bg-white/[0.02] border border-white/[0.08]">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 rounded-2xl bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center justify-center font-black text-lg">
                                MW
                            </div>
                            <div>
                                <h3 class="text-base font-black text-white">Marilyn Watson</h3>
                                <p class="text-xs text-brand-400 font-bold">Investor Relations Manager</p>
                                <p class="text-[11px] text-zinc-400">Ride My Cars New Development Finance Group LLC</p>
                            </div>
                        </div>

                        <div class="space-y-3 pt-3 border-t border-white/[0.06] text-xs text-zinc-300">
                            <div class="flex items-center gap-2.5">
                                <span class="text-zinc-500">📧</span>
                                <a href="mailto:investors@ridemycars.com" class="hover:text-brand-400 transition-colors">investors@ridemycars.com</a>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <span class="text-zinc-500">🏢</span>
                                <span>Global Executive Office: 4301 Saddle River Dr, Bowie, MD 20720</span>
                            </div>
                        </div>
                    </div>

                    <!-- Regional Coordination Partner -->
                    <div class="p-6 rounded-3xl bg-white/[0.02] border border-white/[0.08]">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center font-black text-lg">
                                EG
                            </div>
                            <div>
                                <h3 class="text-base font-black text-white">Eminsang Group Limited</h3>
                                <p class="text-xs text-emerald-400 font-bold">Regional Escrow & Compliance Partner</p>
                                <p class="text-[11px] text-zinc-400">Republic of Ghana Operations Hub</p>
                            </div>
                        </div>

                        <div class="space-y-3 pt-3 border-t border-white/[0.06] text-xs text-zinc-300">
                            <div class="flex items-center gap-2.5">
                                <span class="text-zinc-500">📍</span>
                                <span>No 1 Airport Square, 8th FL, Airport City, Accra, Ghana</span>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <span class="text-zinc-500">🏛️</span>
                                <span>Liaison Desk for SEC Ghana & Bank of Ghana remittance compliance</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-5 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-300 leading-relaxed">
                        ⚡ <strong>Priority Response SLA:</strong> Institutional and accredited investor inquiries are responded to within 12 business hours by compliance personnel.
                    </div>
                </div>

                <!-- Form Panel (Col 7) -->
                <div class="lg:col-span-7">
                    <div class="p-8 sm:p-10 rounded-3xl bg-white/[0.02] border border-white/[0.08] shadow-2xl">
                        <h2 class="text-xl font-black text-white mb-2">Send an Official Inquiry</h2>
                        <p class="text-xs text-zinc-400 mb-6">Complete this form to connect with our Investor Relations team or schedule a private data room walk-through.</p>

                        @if(session('success'))
                            <div class="p-4 mb-6 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs font-bold flex items-center gap-2">
                                <span>✓</span>
                                <span>{{ session('success') }}</span>
                            </div>
                        @endif

                        <form method="POST" action="/investor/contact" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold text-zinc-300 uppercase tracking-wider mb-1.5">Legal Entity or Individual Name *</label>
                                <input type="text" name="name" required value="{{ old('name') }}" placeholder="e.g. Apex Holdings LLC / John Doe" class="w-full px-4 py-3 rounded-xl bg-white/[0.04] border border-white/10 text-white text-sm focus:outline-none focus:border-brand-500 transition-colors">
                                @error('name') <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-zinc-300 uppercase tracking-wider mb-1.5">Contact Email *</label>
                                    <input type="email" name="email" required value="{{ old('email') }}" placeholder="investor@domain.com" class="w-full px-4 py-3 rounded-xl bg-white/[0.04] border border-white/10 text-white text-sm focus:outline-none focus:border-brand-500 transition-colors">
                                    @error('email') <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-zinc-300 uppercase tracking-wider mb-1.5">Phone Number with Country Code *</label>
                                    <input type="text" name="phone" required value="{{ old('phone') }}" placeholder="+1 (555) 000-0000" class="w-full px-4 py-3 rounded-xl bg-white/[0.04] border border-white/10 text-white text-sm focus:outline-none focus:border-brand-500 transition-colors">
                                    @error('phone') <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-zinc-300 uppercase tracking-wider mb-1.5">Primary Country *</label>
                                    <select name="country" required class="w-full px-4 py-3 rounded-xl bg-[#121824] border border-white/10 text-white text-sm focus:outline-none focus:border-brand-500 transition-colors">
                                        <option value="USA">United States</option>
                                        <option value="Ghana">Ghana</option>
                                        <option value="Canada">Canada</option>
                                        <option value="United Kingdom">United Kingdom</option>
                                        <option value="European Union">European Union</option>
                                        <option value="Other Africa">Other African Country</option>
                                        <option value="Other">Rest of World</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-zinc-300 uppercase tracking-wider mb-1.5">Tranche of Interest</label>
                                    <select name="interested_tranche" class="w-full px-4 py-3 rounded-xl bg-[#121824] border border-white/10 text-white text-sm focus:outline-none focus:border-brand-500 transition-colors">
                                        <option value="Tranche B (Growth Tier - 14% Equity)">Tranche B: 1,440,000 GHC ($120k)</option>
                                        <option value="Tranche A (Seed Tier - 10% Equity)">Tranche A: 720,000 GHC ($60k)</option>
                                        <option value="Tranche C (Venture Tier - 22% Equity)">Tranche C: 2,640,000 GHC ($220k)</option>
                                        <option value="General Inquiry">General Private Placement Inquiry</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-zinc-300 uppercase tracking-wider mb-1.5">Message / Inquiry Details *</label>
                                <textarea name="message" rows="4" required placeholder="Specify any particular legal, compliance, or capital remittance questions..." class="w-full px-4 py-3 rounded-xl bg-white/[0.04] border border-white/10 text-white text-sm focus:outline-none focus:border-brand-500 transition-colors">{{ old('message') }}</textarea>
                                @error('message') <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <button type="submit" class="w-full py-4 rounded-xl bg-brand-500 hover:bg-brand-400 text-black font-black text-sm uppercase tracking-wider shadow-lg shadow-brand-500/25 transition-all hover:scale-[1.01]">
                                Submit Inquiry to Compliance Desk →
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
