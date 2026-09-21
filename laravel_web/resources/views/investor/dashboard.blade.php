<x-layout title="Investor Portal Dashboard | NDFG LLC">
    <div class="min-h-screen bg-slate-50/60 dark:bg-[#0b0f17] text-gray-900 dark:text-white relative overflow-hidden py-10 sm:py-14 transition-colors">
        <!-- Ambient accents -->
        <div class="absolute -top-32 -left-32 w-96 h-96 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute top-1/2 -right-32 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl 2xl:max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 relative z-10">

            <!-- Flash messages -->
            @if(session('success'))
                <div class="p-4 mb-8 rounded-2xl bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-200 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300 text-xs sm:text-sm font-bold flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs">✓</span>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- Top Header & Profile Status Strip -->
            <div class="mb-8 p-6 sm:p-8 rounded-3xl bg-white dark:bg-[#151b26] border border-slate-200/90 dark:border-white/10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 shadow-sm">
                <div>
                    <div class="flex items-center gap-3 mb-2 flex-wrap">
                        <span class="text-xs font-bold text-gray-500 dark:text-zinc-400">Investor Profile:</span>
                        <span class="text-xs font-black text-gray-900 dark:text-white px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-white/10 border border-slate-200 dark:border-white/15">
                            {{ $investor->legal_name }}
                        </span>
                        <span class="text-xs font-bold text-gray-400 dark:text-zinc-500">•</span>
                        <span class="text-xs font-bold text-amber-700 dark:text-amber-400">
                            {{ $investor->country_residence }} ({{ $regulatoryTierName }})
                        </span>
                        <span class="text-xs font-bold text-gray-400 dark:text-zinc-500">•</span>
                        <span class="text-xs font-bold text-gray-700 dark:text-zinc-300">
                            Tranche {{ $investor->selected_tranche }} ({{ $investor->equity_percentage }}% Equity)
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                        NDFG LLC Private Placement Hub
                    </h1>
                </div>

                <div class="flex items-center gap-3">
                    @if($investor->isVerified())
                        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-50 dark:bg-emerald-500/20 border border-emerald-300 dark:border-emerald-500/40 text-emerald-700 dark:text-emerald-400 font-black text-xs uppercase tracking-wider shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Accredited & Verified</span>
                        </div>
                    @elseif($investor->needsMoreDocs())
                        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-rose-50 dark:bg-rose-500/20 border border-rose-300 dark:border-rose-500/40 text-rose-700 dark:text-rose-400 font-black text-xs uppercase tracking-wider shadow-sm">
                            <span>⚠️ Action Needed: Additional Docs</span>
                        </div>
                    @else
                        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-50 dark:bg-amber-500/20 border border-amber-300 dark:border-amber-500/40 text-amber-800 dark:text-amber-400 font-black text-xs uppercase tracking-wider shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                            <span>Compliance Review in Progress</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- STATE 1: BEFORE APPROVAL (PENDING / UNDER_REVIEW / NEED_MORE_DOCS)        -->
            <!-- ========================================================================= -->
            @if(!$investor->isVerified())
                <div class="space-y-8">
                    <!-- Verification Pending Banner -->
                    <div class="p-6 sm:p-8 rounded-3xl bg-amber-50/70 dark:bg-gradient-to-r dark:from-amber-500/10 dark:via-slate-900 dark:to-white/[0.02] border border-amber-200 dark:border-amber-500/30 shadow-sm">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-amber-100 dark:bg-amber-500/20 border border-amber-200 dark:border-amber-500/30 flex items-center justify-center text-amber-700 dark:text-amber-400 text-2xl font-black shrink-0">
                                ⏳
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-lg sm:text-xl font-black text-gray-900 dark:text-white">Compliance Review Initiated</h3>
                                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300 font-extrabold border border-amber-200 dark:border-amber-500/30">
                                        Estimated Review Time: 12 to 24 Business Hours
                                    </span>
                                </div>
                                <p class="text-xs sm:text-sm text-gray-700 dark:text-zinc-300 leading-relaxed max-w-3xl">
                                    Our compliance desk, in coordination with <strong>Eminsang Group Limited (Ghana)</strong>, has received your verification credentials under the <strong>{{ $regulatoryTierName }}</strong> framework for <strong>Tranche {{ $investor->selected_tranche }}</strong>.
                                </p>
                                <p class="text-xs text-gray-500 dark:text-zinc-400">
                                    To protect all partners under international securities laws, financial telemetry, real-time ledgers, and escrow wire coordinates remain strictly locked until your credentials receive positive compliance clearance.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Interactive Verification Timeline -->
                    <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-[#151b26] border border-slate-200/90 dark:border-white/08 shadow-sm">
                        <h3 class="text-xs font-black uppercase tracking-wider text-gray-500 dark:text-zinc-400 mb-6">Verification Progress Timeline</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Stage 1 -->
                            <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400">Stage 1</span>
                                    <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400">✓ Completed</span>
                                </div>
                                <div class="font-black text-sm text-gray-900 dark:text-white">Application Submitted</div>
                                <p class="text-[11px] text-gray-500 dark:text-zinc-400 mt-1">Identity and initial credentials successfully filed.</p>
                            </div>

                            <!-- Stage 2 -->
                            <div class="p-4 rounded-2xl {{ $investor->needsMoreDocs() ? 'bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/30' : 'bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30' }}">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold {{ $investor->needsMoreDocs() ? 'text-rose-700 dark:text-rose-400' : 'text-amber-700 dark:text-amber-400' }}">Stage 2</span>
                                    <span class="text-xs font-bold {{ $investor->needsMoreDocs() ? 'text-rose-700 dark:text-rose-400' : 'text-amber-700 dark:text-amber-400' }} animate-pulse">
                                        {{ $investor->needsMoreDocs() ? 'Action Required' : 'In Review' }}
                                    </span>
                                </div>
                                <div class="font-black text-sm text-gray-900 dark:text-white">Regulatory Tier Vetting</div>
                                <p class="text-[11px] text-gray-500 dark:text-zinc-400 mt-1">Accreditation checks under {{ $regulatoryTierName }}.</p>
                            </div>

                            <!-- Stage 3 -->
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-white/[0.02] border border-slate-200 dark:border-white/10 opacity-75">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold text-gray-500 dark:text-zinc-400">Stage 3</span>
                                    <span class="text-xs text-gray-400 dark:text-zinc-500 font-semibold">Queued</span>
                                </div>
                                <div class="font-black text-sm text-gray-900 dark:text-white">Escrow & KYC Clearance</div>
                                <p class="text-[11px] text-gray-500 dark:text-zinc-400 mt-1">Validation check via Eminsang Group Limited.</p>
                            </div>

                            <!-- Stage 4 -->
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-white/[0.02] border border-slate-200 dark:border-white/10 opacity-60">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold text-gray-400 dark:text-zinc-500">Stage 4</span>
                                    <span class="text-xs text-gray-400 dark:text-zinc-500 font-semibold">🔒 Locked</span>
                                </div>
                                <div class="font-black text-sm text-gray-700 dark:text-zinc-400">Payment Vault Access</div>
                                <p class="text-[11px] text-gray-400 dark:text-zinc-500 mt-1">Wire coordinates & 3-year ledger unlocked.</p>
                            </div>
                        </div>
                    </div>

                    <!-- If Need More Docs: Compliance Feedback Banner & Re-upload Form -->
                    @if($investor->needsMoreDocs())
                        <div class="p-6 sm:p-8 rounded-3xl bg-rose-50 dark:bg-rose-500/10 border-2 border-rose-200 dark:border-rose-500/40 shadow-sm">
                            <div class="flex items-start gap-4">
                                <div class="text-3xl">⚠️</div>
                                <div class="flex-1 space-y-4">
                                    <div>
                                        <h3 class="text-lg font-black text-rose-800 dark:text-rose-300">Action Required: Compliance Officer Notes</h3>
                                        <p class="text-xs text-gray-700 dark:text-zinc-300 mt-1">
                                            {{ $investor->document_request_notes ?: 'Please upload updated identification or certification documents as requested by our compliance officers.' }}
                                        </p>
                                    </div>

                                    <!-- Re-upload Form -->
                                    <form method="POST" action="/investor/vault/reupload" enctype="multipart/form-data" class="p-5 rounded-2xl bg-white dark:bg-black/40 border border-slate-200 dark:border-white/10 space-y-4 shadow-sm">
                                        @csrf
                                        <div class="font-black text-xs text-gray-900 dark:text-white uppercase tracking-wider">Upload Additional / Corrected Document</div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-[11px] font-bold text-gray-700 dark:text-zinc-300 mb-1">Document Type *</label>
                                                <select name="document_type" required class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-[#121824] border border-slate-300 dark:border-white/10 text-xs text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500">
                                                    <option value="CPA_LETTER">Certified CPA Letter / Wealth Statement</option>
                                                    <option value="GH_CARD">Ghana Card / National ID</option>
                                                    <option value="PASSPORT">Passport Identification</option>
                                                    <option value="TAX_TIN">Tax Identification Document (TIN)</option>
                                                    <option value="ADDRESS_PROOF">Proof of Address</option>
                                                    <option value="CORP_DOCS">Corporate Registration Documents</option>
                                                    <option value="OTHER">Other Compliance Document</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-bold text-gray-700 dark:text-zinc-300 mb-1">Document Description *</label>
                                                <input type="text" name="document_title" required placeholder="e.g. Updated CPA Letter 2026" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-white/[0.04] border border-slate-300 dark:border-white/10 text-xs text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-bold text-gray-700 dark:text-zinc-300 mb-1">Select File (PDF, JPG, PNG up to 10MB) *</label>
                                            <input type="file" name="document_file" required accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-gray-600 dark:text-zinc-300 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-200 dark:file:bg-white/10 file:text-gray-900 dark:file:text-white">
                                        </div>
                                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-black text-xs uppercase tracking-wider transition-all shadow-sm">
                                            Submit Document for Expedited Review →
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Uploaded Documents Vault Status List -->
                    <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-[#151b26] border border-slate-200/90 dark:border-white/08 shadow-sm">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-base font-black text-gray-900 dark:text-white">Secure Document Vault</h3>
                                <p class="text-xs text-gray-500 dark:text-zinc-400">Vaulted documents are stored outside the public directory with streaming security checks.</p>
                            </div>
                            <span class="text-xs font-bold text-gray-600 dark:text-zinc-400">{{ $investor->documents->count() }} file(s) vaulted</span>
                        </div>

                        <div class="divide-y divide-slate-100 dark:divide-white/[0.06]">
                            @forelse($investor->documents as $doc)
                                <div class="py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <span class="text-2xl">📄</span>
                                        <div>
                                            <div class="font-bold text-sm text-gray-900 dark:text-white">{{ $doc->document_title }}</div>
                                            <div class="text-[11px] text-gray-500 dark:text-zinc-400">
                                                {{ $doc->original_filename }} • {{ $doc->formatted_file_size }} • Uploaded {{ $doc->uploaded_at->format('M d, Y') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="text-[10px] font-black uppercase px-2.5 py-1 rounded-full
                                            {{ $doc->status === 'VERIFIED' ? 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400' : ($doc->status === 'REJECTED' ? 'bg-rose-100 dark:bg-rose-500/20 text-rose-700 dark:text-rose-400' : 'bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400') }}">
                                            {{ $doc->status }}
                                        </span>
                                        <a href="/investor/vault/document/{{ $doc->id }}/download" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-white/10 dark:hover:bg-white/20 text-gray-800 dark:text-white text-xs font-bold transition-colors flex items-center gap-1">
                                            <span>⬇ Download</span>
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="py-8 text-center text-xs text-gray-500 dark:text-zinc-500">
                                    No documents vaulted yet.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Locked Data Room Teaser -->
                    <div class="p-8 rounded-3xl bg-slate-100/60 dark:bg-white/[0.01] border border-dashed border-slate-300 dark:border-white/10 text-center space-y-3">
                        <div class="text-3xl opacity-50">🔒</div>
                        <p class="text-xs text-gray-500 dark:text-zinc-500 max-w-md mx-auto">
                            Financial data room ledgers, daily cohort collection telemetry, and multi-currency escrow wire coordinates will unlock immediately upon positive administrative clearance.
                        </p>
                    </div>
                </div>

            <!-- ========================================================================= -->
            <!-- STATE 2: AFTER APPROVAL (APPROVED & UNLOCKED)                             -->
            <!-- ========================================================================= -->
            @else
                <div class="space-y-8">
                    <!-- Automated Web Portal Welcome Message (SRS Section Copy) -->
                    <div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-r from-emerald-50 via-teal-50/40 to-white dark:from-emerald-950/40 dark:via-slate-900 dark:to-slate-900 border border-emerald-200 dark:border-emerald-500/30 shadow-sm relative overflow-hidden">
                        <div class="relative z-10 space-y-3">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 dark:bg-emerald-500/20 border border-emerald-300 dark:border-emerald-500/40 text-emerald-800 dark:text-emerald-400 text-xs font-black uppercase tracking-wider">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>Verified Participant • {{ $regulatoryTierName }}</span>
                            </div>

                            <h2 class="text-xl sm:text-3xl font-black text-gray-900 dark:text-white">
                                Welcome to the NDFG LLC Development Portal
                            </h2>

                            <p class="text-xs sm:text-sm text-gray-700 dark:text-zinc-300 leading-relaxed max-w-3xl">
                                Your account has been securely verified under <strong>{{ $regulatoryTierName }}</strong> compliance protocols.
                            </p>

                            <p class="text-xs sm:text-sm text-gray-600 dark:text-zinc-300 leading-relaxed max-w-3xl">
                                You have unlocked direct access to the <strong>Ride My Cars Ghana Super-App Expansion Offering</strong>. Our single-cohort engine leverages a high-yielding, host-owned network architecture operating at an elite <strong>92% net margin</strong>. Through this portal, you can monitor your allocations, review our live daily micro-transaction revenue ledger, and manage your quarterly automated multi-currency dividend distributions.
                            </p>
                        </div>
                    </div>

                    <!-- REAL-TIME MICRO-TRANSACTION TICKER -->
                    <div class="p-5 sm:p-6 rounded-3xl bg-white dark:bg-[#151b26] border border-slate-200/90 dark:border-white/08 shadow-sm backdrop-blur-xl">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <span class="relative flex h-3 w-3">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                                </span>
                                <h3 class="text-xs font-black uppercase tracking-wider text-gray-900 dark:text-white">Real-Time Micro-Transaction Ticker</h3>
                            </div>
                            <span class="text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-500/15 text-emerald-800 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30">Live Platform Stream</span>
                        </div>

                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-white/[0.02] border border-slate-200/70 dark:border-white/[0.06]">
                                <div class="text-[10px] font-black uppercase tracking-wider text-gray-500 dark:text-zinc-400">Current Active Drivers</div>
                                <div class="text-2xl font-black text-gray-900 dark:text-white mt-1">500</div>
                                <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold mt-1">Synchronized Expansion Base</div>
                            </div>
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-white/[0.02] border border-slate-200/70 dark:border-white/[0.06]">
                                <div class="text-[10px] font-black uppercase tracking-wider text-gray-500 dark:text-zinc-400">Daily Cohort Baseline</div>
                                <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">25,000 GHC</div>
                                <div class="text-[10px] text-gray-500 dark:text-zinc-400 mt-1">50 GHC / driver daily baseline</div>
                            </div>
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-white/[0.02] border border-slate-200/70 dark:border-white/[0.06]">
                                <div class="text-[10px] font-black uppercase tracking-wider text-gray-500 dark:text-zinc-400">Net Contribution Margin</div>
                                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">92.0%</div>
                                <div class="text-[10px] text-gray-500 dark:text-zinc-400 mt-1">Host-owned agility</div>
                            </div>
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-white/[0.02] border border-slate-200/70 dark:border-white/[0.06]">
                                <div class="text-[10px] font-black uppercase tracking-wider text-gray-500 dark:text-zinc-400">Quarterly Dividend Run-Rate</div>
                                <div class="text-2xl font-black text-gray-900 dark:text-white mt-1">2,098,750 GHC</div>
                                <div class="text-[10px] text-brand-600 dark:text-brand-400 font-bold mt-1">Automated Multi-Currency Pool</div>
                            </div>
                        </div>
                    </div>

                    <!-- Locked Tranche Parameters Summary Card -->
                    <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-[#151b26] border border-slate-200/90 dark:border-white/08 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-base font-black text-gray-900 dark:text-white">Your Locked Allocation Parameters</h3>
                            <a href="/investor/agreement/download" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-white/10 dark:hover:bg-white/20 text-xs font-bold text-gray-900 dark:text-white transition-colors flex items-center gap-1.5 border border-slate-200 dark:border-white/10">
                                <span>📜 Download Signed NDFG Agreement (v3.1)</span>
                            </a>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200/80 dark:border-white/10">
                                <div class="text-[10px] font-black uppercase text-gray-500 dark:text-zinc-400">Allocation Tier</div>
                                <div class="text-xl font-black text-gray-900 dark:text-white mt-1">Tranche {{ $investor->selected_tranche }}</div>
                                <div class="text-xs text-gray-500 dark:text-zinc-400 mt-0.5">Fixed 3-Year Single Cohort</div>
                            </div>
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200/80 dark:border-white/10">
                                <div class="text-[10px] font-black uppercase text-gray-500 dark:text-zinc-400">Funding Commitment</div>
                                <div class="text-xl font-black text-amber-600 dark:text-amber-400 mt-1">
                                    {{ number_format((float) $investor->capital_commitment_ghc, 0) }} GHC
                                </div>
                                <div class="text-xs text-gray-500 dark:text-zinc-400 mt-0.5">
                                    Approx. ${{ number_format((float) $investor->capital_commitment_usd, 0) }} USD
                                </div>
                            </div>
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200/80 dark:border-white/10">
                                <div class="text-[10px] font-black uppercase text-gray-500 dark:text-zinc-400">Fixed Equity Stake</div>
                                <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1">
                                    {{ $investor->equity_percentage }}%
                                </div>
                                <div class="text-xs text-gray-500 dark:text-zinc-400 mt-0.5">Non-dilutive cohort stake</div>
                            </div>
                        </div>
                    </div>

                    <!-- PAYMENT VAULT & MULTI-CURRENCY ESCROW GATEWAY (UNLOCKED) -->
                    <div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-b from-amber-50/70 via-white to-white dark:from-amber-500/10 dark:via-white/[0.02] dark:to-white/[0.01] border-2 border-amber-400 dark:border-amber-500 shadow-md space-y-6">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-slate-200 dark:border-white/10">
                            <div>
                                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-800 dark:text-amber-400 text-xs font-black uppercase tracking-wider mb-2">
                                    <span>💳 Payment Vault & Multi-Currency Escrow Gateway</span>
                                </div>
                                <h3 class="text-xl font-black text-gray-900 dark:text-white">Authorized Capital Remittance Coordinates</h3>
                                <p class="text-xs text-gray-600 dark:text-zinc-300 mt-1">
                                    Backed by <strong>Eminsang Group Limited (Ghana)</strong> in adherence to Bank of Ghana foreign exchange and SEC directives.
                                </p>
                            </div>

                            <div class="text-left sm:text-right">
                                <div class="text-[10px] font-black uppercase text-gray-500 dark:text-zinc-400">Unique Payment Reference</div>
                                <div class="text-base font-mono font-black text-brand-600 dark:text-brand-400 bg-slate-100 dark:bg-black/50 px-3 py-1 rounded-lg border border-slate-300 dark:border-brand-500/30 inline-block mt-0.5">
                                    {{ $escrowDetails['reference_code'] }}
                                </div>
                            </div>
                        </div>

                        <!-- 5-Day Settlement Notice -->
                        <div class="p-4 rounded-2xl bg-amber-100/70 dark:bg-amber-500/15 border border-amber-300 dark:border-amber-500/30 text-amber-900 dark:text-amber-300 text-xs font-bold flex items-start gap-3">
                            <span class="text-lg">⏰</span>
                            <div>
                                <strong>Important Settlement Notice:</strong> To ensure orderly cohort deployment, all funding commitments must settle into our escrow accounts within <strong>five (5) business days</strong> of your approval notice.
                            </div>
                        </div>

                        <!-- Wire Coordinates Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- International SWIFT Wire -->
                            <div class="p-5 rounded-2xl bg-white dark:bg-black/40 border border-slate-200 dark:border-white/10 space-y-3 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-black uppercase tracking-wider text-blue-600 dark:text-blue-400">International Bank SWIFT Wire (USD / EUR / GBP / CAD)</span>
                                    <span class="text-xl">🌐</span>
                                </div>
                                <div class="space-y-1.5 text-xs text-gray-700 dark:text-zinc-300">
                                    <div><span class="text-gray-400 dark:text-zinc-500">Escrow Bank:</span> <strong class="text-gray-900 dark:text-white">{{ $escrowDetails['swift_bank_name'] }}</strong></div>
                                    <div><span class="text-gray-400 dark:text-zinc-500">Account Name:</span> <strong class="text-gray-900 dark:text-white">{{ $escrowDetails['swift_account_name'] }}</strong></div>
                                    <div><span class="text-gray-400 dark:text-zinc-500">USD Account No:</span> <strong class="text-amber-600 dark:text-amber-400 font-mono">{{ $escrowDetails['swift_account_usd'] }}</strong></div>
                                    <div><span class="text-gray-400 dark:text-zinc-500">IBAN:</span> <strong class="text-gray-900 dark:text-white font-mono">{{ $escrowDetails['swift_iban'] }}</strong></div>
                                    <div><span class="text-gray-400 dark:text-zinc-500">SWIFT/BIC:</span> <strong class="text-gray-900 dark:text-white font-mono">{{ $escrowDetails['swift_code'] }}</strong></div>
                                    <div><span class="text-gray-400 dark:text-zinc-500">Mandatory Memo:</span> <strong class="text-amber-600 dark:text-amber-400 font-mono">{{ $escrowDetails['reference_code'] }}</strong></div>
                                </div>
                            </div>

                            <!-- Ghana Local Banking & Mobile Money -->
                            <div class="p-5 rounded-2xl bg-white dark:bg-black/40 border border-slate-200 dark:border-white/10 space-y-4 shadow-sm">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-black uppercase tracking-wider text-amber-700 dark:text-amber-400">Ghana Cedis (GHC) Local Clearing</span>
                                        <span class="text-xl">🇬🇭</span>
                                    </div>
                                    <div class="space-y-1 text-xs text-gray-700 dark:text-zinc-300">
                                        <div><span class="text-gray-400 dark:text-zinc-500">Account:</span> <strong class="text-gray-900 dark:text-white">{{ $escrowDetails['local_bank_ghc'] }}</strong></div>
                                        <div><span class="text-gray-400 dark:text-zinc-500">Account No:</span> <strong class="text-amber-600 dark:text-amber-400 font-mono">{{ $escrowDetails['local_account_number'] }}</strong> ({{ $escrowDetails['local_branch'] }})</div>
                                    </div>
                                </div>

                                <div class="pt-3 border-t border-slate-200 dark:border-white/10">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Mobile Money Gateway (MTN MoMo / Telecel Cash)</span>
                                        <span class="text-xl">📱</span>
                                    </div>
                                    <div class="space-y-1 text-xs text-gray-700 dark:text-zinc-300">
                                        <div><span class="text-gray-400 dark:text-zinc-500">Merchant Name:</span> <strong class="text-gray-900 dark:text-white">{{ $escrowDetails['momo_merchant_id'] }}</strong></div>
                                        <div><span class="text-gray-400 dark:text-zinc-500">Merchant Line:</span> <strong class="text-emerald-600 dark:text-emerald-400 font-mono">{{ $escrowDetails['momo_number'] }}</strong></div>
                                        <div><span class="text-gray-400 dark:text-zinc-500">Reference:</span> <strong class="text-amber-600 dark:text-amber-400 font-mono">{{ $escrowDetails['reference_code'] }}</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3-Year Master Cash Ledger Table -->
                    <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-[#151b26] border border-slate-200/90 dark:border-white/08 shadow-sm">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-base font-black text-gray-900 dark:text-white">3-Year Master Cash Ledger & Pro Forma Model</h3>
                                <p class="text-xs text-gray-500 dark:text-zinc-400">Historical payouts and quarterly cohort projections.</p>
                            </div>
                            <span class="text-[10px] font-black uppercase px-2.5 py-1 rounded bg-slate-100 dark:bg-white/5 text-gray-700 dark:text-zinc-300 border border-slate-200 dark:border-white/10">Pro Forma Verified</span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs sm:text-sm text-gray-700 dark:text-zinc-300">
                                <thead>
                                    <tr class="border-b border-slate-200 dark:border-white/10 text-[10px] font-black uppercase tracking-wider text-gray-500 dark:text-zinc-400">
                                        <th class="pb-3">Period</th>
                                        <th class="pb-3">Cohort Size</th>
                                        <th class="pb-3">Daily Baseline</th>
                                        <th class="pb-3">Gross Cohort GMV</th>
                                        <th class="pb-3">Net 92% Pool</th>
                                        <th class="pb-3">Your Allocation Stake</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-white/[0.04]">
                                    <tr>
                                        <td class="py-3 font-bold text-gray-900 dark:text-white">Year 1 (Q1-Q4)</td>
                                        <td class="py-3">500 Drivers</td>
                                        <td class="py-3 text-amber-600 dark:text-amber-400 font-mono">25,000 GHC/day</td>
                                        <td class="py-3 font-mono">9,125,000 GHC</td>
                                        <td class="py-3 text-emerald-600 dark:text-emerald-400 font-mono font-bold">8,395,000 GHC</td>
                                        <td class="py-3 font-bold text-gray-900 dark:text-white">{{ number_format((8395000 * $investor->equity_percentage) / 100, 0) }} GHC</td>
                                    </tr>
                                    <tr>
                                        <td class="py-3 font-bold text-gray-900 dark:text-white">Year 2 (Q1-Q4)</td>
                                        <td class="py-3">1,250 Drivers</td>
                                        <td class="py-3 text-amber-600 dark:text-amber-400 font-mono">62,500 GHC/day</td>
                                        <td class="py-3 font-mono">22,812,500 GHC</td>
                                        <td class="py-3 text-emerald-600 dark:text-emerald-400 font-mono font-bold">20,987,500 GHC</td>
                                        <td class="py-3 font-bold text-gray-900 dark:text-white">{{ number_format((20987500 * $investor->equity_percentage) / 100, 0) }} GHC</td>
                                    </tr>
                                    <tr>
                                        <td class="py-3 font-bold text-gray-900 dark:text-white">Year 3 (Q1-Q4 + Exit)</td>
                                        <td class="py-3">2,500 Drivers</td>
                                        <td class="py-3 text-amber-600 dark:text-amber-400 font-mono">125,000 GHC/day</td>
                                        <td class="py-3 font-mono">45,625,000 GHC</td>
                                        <td class="py-3 text-emerald-600 dark:text-emerald-400 font-mono font-bold">41,975,000 GHC</td>
                                        <td class="py-3 font-bold text-emerald-600 dark:text-emerald-400">{{ number_format((41975000 * $investor->equity_percentage) / 100, 0) }} GHC + Buyout</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Vault Documents List -->
                    <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-[#151b26] border border-slate-200/90 dark:border-white/08 shadow-sm">
                        <h3 class="text-base font-black text-gray-900 dark:text-white mb-4">Your Verified Compliance Documents</h3>
                        <div class="divide-y divide-slate-100 dark:divide-white/[0.06]">
                            @foreach($investor->documents as $doc)
                                <div class="py-3.5 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="text-xl">📄</span>
                                        <div>
                                            <div class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white">{{ $doc->document_title }}</div>
                                            <div class="text-[11px] text-gray-500 dark:text-zinc-400">{{ $doc->original_filename }} ({{ $doc->formatted_file_size }})</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400">Verified</span>
                                        <a href="/investor/vault/document/{{ $doc->id }}/download" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 dark:bg-white/10 dark:hover:bg-white/20 text-gray-900 dark:text-white text-xs font-bold transition-colors">
                                            Download
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-layout>
