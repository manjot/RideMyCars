<x-layout title="Investor Portal Dashboard | NDFG LLC">
    <!-- Sleek Institutional Investor Sub-Navigation -->
    <x-investor-nav />

    <div class="min-h-screen bg-slate-50 dark:bg-[#0b0f17] text-slate-900 dark:text-white relative overflow-hidden py-10 sm:py-14 transition-colors selection:bg-brand-500 selection:text-black">
        <!-- Ambient accents -->
        <div class="absolute -top-32 -left-32 w-96 h-96 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute top-1/2 -right-32 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl 2xl:max-w-[1600px] w-full mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 relative z-10">

            <!-- Flash messages -->
            @if(session('success'))
                <div class="p-4 mb-8 rounded-2xl bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-300 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300 text-xs sm:text-sm font-bold flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs">✓</span>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- Top Header & Profile Status Strip -->
            <div class="mb-8 p-6 sm:p-8 rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 shadow-sm">
                <div>
                    <div class="flex items-center gap-3 mb-2 flex-wrap">
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Investor Profile:</span>
                        <span class="text-xs font-black text-slate-900 dark:text-white px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                            {{ $investor->legal_name }}
                        </span>
                        <span class="text-xs font-bold text-slate-400 dark:text-slate-600">•</span>
                        <span class="text-xs font-bold text-amber-700 dark:text-amber-400">
                            {{ $investor->country_residence }} ({{ $regulatoryTierName }})
                        </span>
                        <span class="text-xs font-bold text-slate-400 dark:text-slate-600">•</span>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                            Tranche {{ $investor->selected_tranche }} ({{ $investor->investmentPlan?->tier_name ? $investor->investmentPlan->tier_name . ' • ' : '' }}{{ $investor->equity_percentage }}% Equity)
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
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
                                    Our compliance desk, in coordination with <strong>Ride My Cars (Ghana)</strong>, has received your verification credentials under the <strong>{{ $regulatoryTierName }}</strong> framework for <strong>Tranche {{ $investor->selected_tranche }}</strong>.
                                </p>
                                <p class="text-xs text-gray-500 dark:text-zinc-400">
                                    To protect all partners under international securities laws, financial telemetry, real-time ledgers, and escrow wire coordinates remain strictly locked until your credentials receive positive compliance clearance.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Interactive Verification Timeline -->
                    <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-sm">
                        <h3 class="text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-6">Verification Progress Timeline</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Stage 1 -->
                            <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-300 dark:border-emerald-500/30">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400">Stage 1</span>
                                    <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400">✓ Completed</span>
                                </div>
                                <div class="font-black text-sm text-slate-900 dark:text-white">Application Submitted</div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Identity and initial credentials successfully filed.</p>
                            </div>

                            <!-- Stage 2 -->
                            <div class="p-4 rounded-2xl {{ $investor->needsMoreDocs() ? 'bg-rose-50 dark:bg-rose-500/10 border border-rose-300 dark:border-rose-500/30' : 'bg-amber-50 dark:bg-amber-500/10 border border-amber-300 dark:border-amber-500/30' }}">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold {{ $investor->needsMoreDocs() ? 'text-rose-700 dark:text-rose-400' : 'text-amber-700 dark:text-amber-400' }}">Stage 2</span>
                                    <span class="text-xs font-bold {{ $investor->needsMoreDocs() ? 'text-rose-700 dark:text-rose-400' : 'text-amber-700 dark:text-amber-400' }} animate-pulse">
                                        {{ $investor->needsMoreDocs() ? 'Action Required' : 'In Review' }}
                                    </span>
                                </div>
                                <div class="font-black text-sm text-slate-900 dark:text-white">Regulatory Tier Vetting</div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Accreditation checks under {{ $regulatoryTierName }}.</p>
                            </div>

                            <!-- Stage 3 -->
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 opacity-75">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Stage 3</span>
                                    <span class="text-xs text-slate-400 dark:text-slate-500 font-semibold">Queued</span>
                                </div>
                                <div class="font-black text-sm text-slate-900 dark:text-white">Escrow & KYC Clearance</div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Validation check via Ride My Cars (Ghana).</p>
                            </div>

                            <!-- Stage 4 -->
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 opacity-60">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold text-slate-400 dark:text-slate-500">Stage 4</span>
                                    <span class="text-xs text-slate-400 dark:text-slate-500 font-semibold">🔒 Locked</span>
                                </div>
                                <div class="font-black text-sm text-slate-700 dark:text-slate-400">Payment Vault Access</div>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Wire coordinates & 3-year ledger unlocked.</p>
                            </div>
                        </div>
                    </div>

                    <!-- If Need More Docs: Compliance Feedback Banner & Re-upload Form -->
                    @if($investor->needsMoreDocs())
                        <div class="p-6 sm:p-8 rounded-3xl bg-rose-50 dark:bg-rose-500/10 border-2 border-rose-300 dark:border-rose-500/40 shadow-sm">
                            <div class="flex items-start gap-4">
                                <div class="text-3xl">⚠️</div>
                                <div class="flex-1 space-y-4">
                                    <div>
                                        <h3 class="text-lg font-black text-rose-800 dark:text-rose-300">Action Required: Compliance Officer Notes</h3>
                                        <p class="text-xs text-slate-700 dark:text-slate-300 mt-1">
                                            {{ $investor->document_request_notes ?: 'Please upload updated identification or certification documents as requested by our compliance officers.' }}
                                        </p>
                                    </div>

                                    <!-- Re-upload Form -->
                                    <form method="POST" action="/investor/vault/reupload" enctype="multipart/form-data" class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 space-y-4 shadow-sm">
                                        @csrf
                                        <div class="font-black text-xs text-slate-900 dark:text-white uppercase tracking-wider">Upload Additional / Corrected Document</div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Document Type *</label>
                                                <select name="document_type" required class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500">
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
                                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Document Description *</label>
                                                <input type="text" name="document_title" required placeholder="e.g. Updated CPA Letter 2026" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Select File (PDF, JPG, PNG up to 10MB) *</label>
                                            <input type="file" name="document_file" required accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-600 dark:text-slate-300 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-200 dark:file:bg-slate-700 file:text-slate-900 dark:file:text-white">
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
                    <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-sm">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-base font-black text-slate-900 dark:text-white">Secure Document Vault</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Vaulted documents are stored outside the public directory with streaming security checks.</p>
                            </div>
                            <span class="text-xs font-bold text-slate-600 dark:text-slate-400">{{ $investor->documents->count() }} file(s) vaulted</span>
                        </div>

                        <div class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse($investor->documents as $doc)
                                <div class="py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <span class="text-2xl">📄</span>
                                        <div>
                                            <div class="font-bold text-sm text-slate-900 dark:text-white">{{ $doc->document_title }}</div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                                {{ $doc->original_filename }} • {{ $doc->formatted_file_size }} • Uploaded {{ $doc->uploaded_at->format('M d, Y') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="text-[10px] font-black uppercase px-2.5 py-1 rounded-full
                                            {{ $doc->status === 'VERIFIED' ? 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-500/40' : ($doc->status === 'REJECTED' ? 'bg-rose-100 dark:bg-rose-500/20 text-rose-700 dark:text-rose-400 border border-rose-300 dark:border-rose-500/40' : 'bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400 border border-amber-300 dark:border-amber-500/40') }}">
                                            {{ $doc->status }}
                                        </span>
                                        <a href="/investor/vault/document/{{ $doc->id }}/download" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-white text-xs font-bold transition-colors flex items-center gap-1 border border-slate-200 dark:border-slate-700 shadow-xs">
                                            <span>⬇ Download</span>
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="py-8 text-center text-xs text-slate-500 dark:text-slate-400">
                                    No documents vaulted yet.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Locked Data Room Teaser -->
                    <div class="p-8 rounded-3xl bg-slate-100/70 dark:bg-slate-800/30 border border-dashed border-slate-300 dark:border-slate-700 text-center space-y-3">
                        <div class="text-3xl opacity-50">🔒</div>
                        <p class="text-xs text-slate-600 dark:text-slate-400 max-w-md mx-auto font-medium">
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
                    <div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-r from-emerald-50 via-teal-50/40 to-white dark:from-emerald-950/40 dark:via-slate-900 dark:to-slate-900 border border-emerald-300 dark:border-emerald-500/30 shadow-sm relative overflow-hidden">
                        <div class="relative z-10 space-y-3">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 dark:bg-emerald-500/20 border border-emerald-300 dark:border-emerald-500/40 text-emerald-800 dark:text-emerald-300 text-xs font-black uppercase tracking-wider">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>Verified Participant • {{ $regulatoryTierName }}</span>
                            </div>

                            <h2 class="text-xl sm:text-3xl font-black text-slate-900 dark:text-white">
                                Welcome to the NDFG LLC Development Portal
                            </h2>

                            <p class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 leading-relaxed max-w-3xl">
                                Your account has been securely verified under <strong>{{ $regulatoryTierName }}</strong> compliance protocols.
                            </p>

                            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed max-w-3xl">
                                You have unlocked direct access to the <strong>Ride My Cars Ghana Super-App Expansion Offering</strong>. Our single-cohort engine leverages a high-yielding, host-owned network architecture operating at an elite <strong>92% net margin</strong>. Through this portal, you can monitor your allocations, review our live daily micro-transaction revenue ledger, and manage your quarterly automated multi-currency dividend distributions.
                            </p>
                        </div>
                    </div>

                    <!-- REAL-TIME MICRO-TRANSACTION TICKER -->
                    <div class="p-5 sm:p-6 rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-sm backdrop-blur-xl">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <span class="relative flex h-3 w-3">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                                </span>
                                <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white">Real-Time Micro-Transaction Ticker</h3>
                            </div>
                            <span class="text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-500/15 text-emerald-800 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-500/30">Live Platform Stream</span>
                        </div>

                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700">
                                <div class="text-[10px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Active Platform Fleet Units</div>
                                <div class="text-2xl font-black text-slate-900 dark:text-white mt-1">500 Units</div>
                                <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold mt-1">Synchronized Expansion Base</div>
                            </div>
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700">
                                <div class="text-[10px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Daily Cohort Baseline</div>
                                <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">25,000 GHC</div>
                                <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">50 GHC / active unit baseline</div>
                            </div>
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700">
                                <div class="text-[10px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Net Contribution Margin</div>
                                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">92.0%</div>
                                <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">Host-owned agility</div>
                            </div>
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700">
                                <div class="text-[10px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Quarterly Dividend Run-Rate</div>
                                <div class="text-2xl font-black text-slate-900 dark:text-white mt-1">2,098,750 GHC</div>
                                <div class="text-[10px] text-brand-600 dark:text-brand-400 font-bold mt-1">Automated Multi-Currency Pool</div>
                            </div>
                        </div>
                    </div>

                    <!-- Locked Tranche Parameters Summary Card -->
                    <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-base font-black text-slate-900 dark:text-white">Your Locked Allocation Parameters</h3>
                            <a href="/investor/agreement/download" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-xs font-bold text-slate-900 dark:text-white transition-colors flex items-center gap-1.5 border border-slate-200 dark:border-slate-700 shadow-xs">
                                <span>📜 Download Signed NDFG Agreement (v3.1)</span>
                            </a>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700">
                                <div class="text-[10px] font-black uppercase text-slate-500 dark:text-slate-400">Allocation Tier</div>
                                <div class="text-xl font-black text-slate-900 dark:text-white mt-1">Tranche {{ $investor->selected_tranche }} {{ $investor->investmentPlan?->tier_name ? '• ' . $investor->investmentPlan->tier_name : '' }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Fixed 3-Year Single Cohort</div>
                            </div>
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700">
                                <div class="text-[10px] font-black uppercase text-slate-500 dark:text-slate-400">Funding Commitment</div>
                                <div class="text-xl font-black text-amber-600 dark:text-amber-400 mt-1">
                                    {{ number_format((float) $investor->capital_commitment_ghc, 0) }} GHC
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    Approx. ${{ number_format((float) $investor->capital_commitment_usd, 0) }} USD
                                </div>
                            </div>
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700">
                                <div class="text-[10px] font-black uppercase text-slate-500 dark:text-slate-400">Fixed Equity Stake</div>
                                <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1">
                                    {{ $investor->equity_percentage }}%
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Non-dilutive cohort stake</div>
                            </div>
                        </div>
                    </div>

                    <!-- PAYMENT VAULT & MULTI-CURRENCY ESCROW GATEWAY (UNLOCKED WITH DYNAMIC COPY BUTTONS) -->
                    <div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-b from-amber-50/70 via-white to-white dark:from-amber-500/10 dark:via-[#131926] dark:to-[#131926] border-2 border-amber-400 dark:border-amber-500 shadow-md space-y-6"
                         x-data="{ copiedItem: '' }">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-700">
                            <div>
                                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-800 dark:text-amber-400 text-xs font-black uppercase tracking-wider mb-2">
                                    <span>💳 Payment Vault & Multi-Currency Escrow Gateway</span>
                                </div>
                                <h3 class="text-xl font-black text-slate-900 dark:text-white">Authorized Capital Remittance Coordinates</h3>
                                <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">
                                    Backed by <strong>Ride My Cars (Ghana)</strong> in adherence to Bank of Ghana foreign exchange and SEC directives.
                                </p>
                            </div>

                            <div class="text-left sm:text-right">
                                <div class="text-[10px] font-black uppercase text-slate-500 dark:text-slate-400">Unique Payment Reference</div>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="text-base font-mono font-black text-amber-700 dark:text-amber-400 bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded-lg border border-slate-300 dark:border-amber-500/30">
                                        {{ $escrowDetails['reference_code'] }}
                                    </span>
                                    <button type="button" @click="navigator.clipboard.writeText('{{ $escrowDetails['reference_code'] }}'); copiedItem = 'ref'; setTimeout(() => copiedItem = '', 2000)"
                                            class="px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-amber-500 hover:text-black border border-slate-300 dark:border-slate-700 transition-colors">
                                        <span x-text="copiedItem === 'ref' ? '✓ Copied' : 'Copy'"></span>
                                    </button>
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

                        <!-- Wire Coordinates Grid (Point 12: GT Bank Ghana & JPMorgan Chase Bank) -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- BANK 1: GUARANTY TRUST BANK GHANA LIMITED -->
                            <div class="p-6 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 space-y-4 shadow-sm flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                                        <div>
                                            <span class="text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-800 dark:text-amber-400">Primary Bank Wire Coordinates</span>
                                            <h4 class="text-sm font-black text-slate-900 dark:text-white mt-1">{{ $escrowDetails['gtb_bank_name'] ?? 'GUARANTY TRUST BANK GHANA LIMITED' }}</h4>
                                        </div>
                                        <span class="text-2xl">🇬🇭</span>
                                    </div>

                                    <div class="space-y-2.5 text-xs text-slate-700 dark:text-slate-300 pt-3">
                                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1">
                                            <span class="text-slate-400">ACCOUNT NAME:</span>
                                            <strong class="text-slate-900 dark:text-white font-mono uppercase">{{ $escrowDetails['gtb_account_name'] ?? 'RIDE MY CARS (GHANA)' }}</strong>
                                        </div>

                                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1">
                                            <span class="text-slate-400">SWIFT CODE:</span>
                                            <div class="flex items-center gap-1.5">
                                                <strong class="text-slate-900 dark:text-white font-mono">{{ $escrowDetails['gtb_swift_code'] ?? 'GTBIGHAC' }}</strong>
                                                <button type="button" @click="navigator.clipboard.writeText('{{ $escrowDetails['gtb_swift_code'] ?? 'GTBIGHAC' }}'); copiedItem = 'gtb_swift'; setTimeout(() => copiedItem = '', 2000)" class="text-[10px] px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-amber-500 hover:text-black transition-colors">
                                                    <span x-text="copiedItem === 'gtb_swift' ? '✓ Copied' : 'Copy'"></span>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Multi-Currency Account Numbers -->
                                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                                            <div class="text-[11px] font-black uppercase text-slate-500 dark:text-slate-400 mb-2">Account Numbers (A/C No.):</div>
                                            <div class="space-y-1.5">
                                                <!-- 1. Cedis -->
                                                <div class="flex justify-between items-center p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                                                    <div>
                                                        <span class="text-[10px] font-bold text-slate-500 uppercase">1. CEDIS (GHC)</span>
                                                        <div class="font-mono font-black text-amber-600 dark:text-amber-400 text-xs">{{ $escrowDetails['gtb_acc_cedis'] ?? '3202001005777' }}</div>
                                                    </div>
                                                    <button type="button" @click="navigator.clipboard.writeText('{{ $escrowDetails['gtb_acc_cedis'] ?? '3202001005777' }}'); copiedItem = 'gtb_cedis'; setTimeout(() => copiedItem = '', 2000)" class="text-[10px] px-2 py-1 rounded bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 font-bold hover:bg-amber-500 hover:text-black transition-colors">
                                                        <span x-text="copiedItem === 'gtb_cedis' ? '✓ Copied' : 'Copy'"></span>
                                                    </button>
                                                </div>

                                                <!-- 2. US Dollars -->
                                                <div class="flex justify-between items-center p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                                                    <div>
                                                        <span class="text-[10px] font-bold text-slate-500 uppercase">2. US DOLLARS (USD)</span>
                                                        <div class="font-mono font-black text-amber-600 dark:text-amber-400 text-xs">{{ $escrowDetails['gtb_acc_usd'] ?? '3202002006422' }}</div>
                                                    </div>
                                                    <button type="button" @click="navigator.clipboard.writeText('{{ $escrowDetails['gtb_acc_usd'] ?? '3202002006422' }}'); copiedItem = 'gtb_usd'; setTimeout(() => copiedItem = '', 2000)" class="text-[10px] px-2 py-1 rounded bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 font-bold hover:bg-amber-500 hover:text-black transition-colors">
                                                        <span x-text="copiedItem === 'gtb_usd' ? '✓ Copied' : 'Copy'"></span>
                                                    </button>
                                                </div>

                                                <!-- 3. Pounds -->
                                                <div class="flex justify-between items-center p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                                                    <div>
                                                        <span class="text-[10px] font-bold text-slate-500 uppercase">3. POUNDS (GBP)</span>
                                                        <div class="font-mono font-black text-amber-600 dark:text-amber-400 text-xs">{{ $escrowDetails['gtb_acc_pounds'] ?? '3202003002530' }}</div>
                                                    </div>
                                                    <button type="button" @click="navigator.clipboard.writeText('{{ $escrowDetails['gtb_acc_pounds'] ?? '3202003002530' }}'); copiedItem = 'gtb_gbp'; setTimeout(() => copiedItem = '', 2000)" class="text-[10px] px-2 py-1 rounded bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 font-bold hover:bg-amber-500 hover:text-black transition-colors">
                                                        <span x-text="copiedItem === 'gtb_gbp' ? '✓ Copied' : 'Copy'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-[11px] leading-relaxed">
                                            <span class="text-slate-400 font-bold">BANK ADDRESS:</span>
                                            <span class="text-slate-700 dark:text-slate-300 font-medium block mt-0.5">{{ $escrowDetails['gtb_bank_address'] ?? '25A CASTLE ROAD, AMBASSADORIAL AREA. RIDGE PMB CT 416 CANTOMENT-ACCRA. GHANA' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center text-xs">
                                    <span class="text-slate-400">Mandatory Memo:</span>
                                    <strong class="text-amber-600 dark:text-amber-400 font-mono">{{ $escrowDetails['reference_code'] }}</strong>
                                </div>
                            </div>

                            <!-- BANK 2: JPMORGAN CHASE BANK N.A -->
                            <div class="p-6 rounded-2xl bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700 space-y-4 shadow-sm flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                                        <div>
                                            <span class="text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full bg-blue-100 dark:bg-blue-500/20 text-blue-800 dark:text-blue-400">US International Wire Rail</span>
                                            <h4 class="text-sm font-black text-slate-900 dark:text-white mt-1">{{ $escrowDetails['chase_bank_name'] ?? 'JPMORGAN CHASE BANK N.A' }}</h4>
                                        </div>
                                        <span class="text-2xl">🇺🇸</span>
                                    </div>

                                    <div class="space-y-2.5 text-xs text-slate-700 dark:text-slate-300 pt-3">
                                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1">
                                            <span class="text-slate-400">ACCOUNT NAME:</span>
                                            <strong class="text-slate-900 dark:text-white font-mono uppercase">{{ $escrowDetails['chase_account_name'] ?? 'NEW DEVELOPMENT FINANCE GROUP LLC' }}</strong>
                                        </div>

                                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1">
                                            <span class="text-slate-400">SWIFT CODE:</span>
                                            <div class="flex items-center gap-1.5">
                                                <strong class="text-slate-900 dark:text-white font-mono">{{ $escrowDetails['chase_swift_code'] ?? 'CHASUS33XXX' }}</strong>
                                                <button type="button" @click="navigator.clipboard.writeText('{{ $escrowDetails['chase_swift_code'] ?? 'CHASUS33XXX' }}'); copiedItem = 'chase_swift'; setTimeout(() => copiedItem = '', 2000)" class="text-[10px] px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-amber-500 hover:text-black transition-colors">
                                                    <span x-text="copiedItem === 'chase_swift' ? '✓ Copied' : 'Copy'"></span>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Account Numbers -->
                                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                                            <div class="text-[11px] font-black uppercase text-slate-500 dark:text-slate-400 mb-2">Account Numbers (A/C No.):</div>
                                            <div class="space-y-1.5">
                                                <!-- 1. US Dollars -->
                                                <div class="flex justify-between items-center p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                                                    <div>
                                                        <span class="text-[10px] font-bold text-slate-500 uppercase">1. US DOLLARS (USD)</span>
                                                        <div class="font-mono font-black text-amber-600 dark:text-amber-400 text-xs">{{ $escrowDetails['chase_acc_usd'] ?? '2911538135' }}</div>
                                                    </div>
                                                    <button type="button" @click="navigator.clipboard.writeText('{{ $escrowDetails['chase_acc_usd'] ?? '2911538135' }}'); copiedItem = 'chase_usd'; setTimeout(() => copiedItem = '', 2000)" class="text-[10px] px-2 py-1 rounded bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 font-bold hover:bg-amber-500 hover:text-black transition-colors">
                                                        <span x-text="copiedItem === 'chase_usd' ? '✓ Copied' : 'Copy'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/40 text-[11px] text-blue-800 dark:text-blue-300 mt-2">
                                            Dedicated institutional escrow receiver rail for domestic US ACH and international Fedwire remittances.
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center text-xs">
                                    <span class="text-slate-400">Mandatory Memo:</span>
                                    <strong class="text-amber-600 dark:text-amber-400 font-mono">{{ $escrowDetails['reference_code'] }}</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Direct Payment Gateways: Stripe & MoMo Pay Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                            <!-- Stripe Gateway -->
                            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-700 space-y-3 shadow-sm">
                                <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                                    <span class="text-xs font-black uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Stripe Gateway (Cards & Apple Pay)</span>
                                    <span class="text-xl">💳</span>
                                </div>
                                <div class="space-y-1.5 text-xs text-slate-700 dark:text-slate-300">
                                    <div class="flex justify-between items-center"><span class="text-slate-400">Merchant:</span> <strong class="text-slate-900 dark:text-white">{{ $escrowDetails['stripe_merchant_name'] ?? 'Ride My Cars LLC (Stripe Verified)' }}</strong></div>
                                    <div class="flex justify-between items-center"><span class="text-slate-400">Accepted:</span> <span class="font-bold text-slate-800 dark:text-slate-200">{{ $escrowDetails['stripe_support'] ?? 'Visa, MasterCard, Apple Pay' }}</span></div>
                                    <div class="flex justify-between items-center"><span class="text-slate-400">Mandatory Memo:</span> <strong class="text-amber-600 dark:text-amber-400 font-mono">{{ $escrowDetails['reference_code'] }}</strong></div>
                                </div>
                            </div>

                            <!-- MoMo Pay Gateway -->
                            <div class="p-5 rounded-2xl bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-700 space-y-3 shadow-sm">
                                <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                                    <span class="text-xs font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-400">MoMo Pay Gateway (MTN / Telecel Direct)</span>
                                    <span class="text-xl">📱</span>
                                </div>
                                <div class="space-y-1.5 text-xs text-slate-700 dark:text-slate-300">
                                    <div class="flex justify-between items-center"><span class="text-slate-400">Merchant Name:</span> <strong class="text-slate-900 dark:text-white">{{ $escrowDetails['momo_merchant_id'] }}</strong></div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-slate-400">Merchant Line:</span>
                                        <div class="flex items-center gap-1">
                                            <strong class="text-emerald-600 dark:text-emerald-400 font-mono">{{ $escrowDetails['momo_number'] }}</strong>
                                            <button type="button" @click="navigator.clipboard.writeText('{{ $escrowDetails['momo_number'] }}'); copiedItem = 'momo'; setTimeout(() => copiedItem = '', 2000)" class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                                <span x-text="copiedItem === 'momo' ? '✓' : 'Copy'"></span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center"><span class="text-slate-400">Reference:</span> <strong class="text-amber-600 dark:text-amber-400 font-mono">{{ $escrowDetails['reference_code'] }}</strong></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3-Year Master Cash Ledger Table -->
                    <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-sm">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-base font-black text-slate-900 dark:text-white">3-Year Master Cash Ledger & Pro Forma Model</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Historical payouts and quarterly cohort projections.</p>
                            </div>
                            <span class="text-[10px] font-black uppercase px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 font-bold">Pro Forma Verified</span>
                        </div>

                        <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-700">
                            <table class="w-full text-left text-xs sm:text-sm text-slate-700 dark:text-slate-300 border-collapse">
                                <thead>
                                    <tr class="border-b-2 border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 text-[10px] font-black uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                        <th class="p-3.5 border-r border-slate-200 dark:border-slate-700">Period</th>
                                        <th class="p-3.5 border-r border-slate-200 dark:border-slate-700">Cohort Size</th>
                                        <th class="p-3.5 border-r border-slate-200 dark:border-slate-700">Daily Baseline</th>
                                        <th class="p-3.5 border-r border-slate-200 dark:border-slate-700">Gross Cohort GMV</th>
                                        <th class="p-3.5 border-r border-slate-200 dark:border-slate-700">Net 92% Pool</th>
                                        <th class="p-3.5">Your Allocation Stake ({{ $investor->equity_percentage }}%)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-[#131926]">
                                    <tr>
                                        <td class="p-3.5 font-bold text-slate-900 dark:text-white border-r border-slate-200 dark:border-slate-700">Year 1 (Q1-Q4)</td>
                                        <td class="p-3.5 border-r border-slate-200 dark:border-slate-700 font-medium">500 UPD (Users Per Day)</td>
                                        <td class="p-3.5 text-amber-600 dark:text-amber-400 font-mono font-bold border-r border-slate-200 dark:border-slate-700">25,000 GHC/day</td>
                                        <td class="p-3.5 font-mono border-r border-slate-200 dark:border-slate-700">9,125,000 GHC</td>
                                        <td class="p-3.5 text-emerald-600 dark:text-emerald-400 font-mono font-bold border-r border-slate-200 dark:border-slate-700">8,395,000 GHC</td>
                                        <td class="p-3.5 font-bold text-slate-900 dark:text-white">{{ number_format((8395000 * $investor->equity_percentage) / 100, 0) }} GHC</td>
                                    </tr>
                                    <tr class="bg-slate-50/50 dark:bg-slate-800/30">
                                        <td class="p-3.5 font-bold text-slate-900 dark:text-white border-r border-slate-200 dark:border-slate-700">Year 2 (Q1-Q4)</td>
                                        <td class="p-3.5 border-r border-slate-200 dark:border-slate-700 font-medium">1,250 UPD</td>
                                        <td class="p-3.5 text-amber-600 dark:text-amber-400 font-mono font-bold border-r border-slate-200 dark:border-slate-700">62,500 GHC/day</td>
                                        <td class="p-3.5 font-mono border-r border-slate-200 dark:border-slate-700">22,812,500 GHC</td>
                                        <td class="p-3.5 text-emerald-600 dark:text-emerald-400 font-mono font-bold border-r border-slate-200 dark:border-slate-700">20,987,500 GHC</td>
                                        <td class="p-3.5 font-bold text-slate-900 dark:text-white">{{ number_format((20987500 * $investor->equity_percentage) / 100, 0) }} GHC</td>
                                    </tr>
                                    <tr class="bg-emerald-50/70 dark:bg-emerald-950/20 font-semibold border-t-2 border-b-2 border-emerald-300 dark:border-emerald-700/60">
                                        <td class="p-3.5 font-bold text-slate-900 dark:text-white border-r border-slate-200 dark:border-slate-700">Year 3 (Q1-Q4 + Exit)</td>
                                        <td class="p-3.5 border-r border-slate-200 dark:border-slate-700 font-medium">2,500 UPD</td>
                                        <td class="p-3.5 text-amber-600 dark:text-amber-400 font-mono font-bold border-r border-slate-200 dark:border-slate-700">125,000 GHC/day</td>
                                        <td class="p-3.5 font-mono border-r border-slate-200 dark:border-slate-700">45,625,000 GHC</td>
                                        <td class="p-3.5 text-emerald-600 dark:text-emerald-400 font-mono font-bold border-r border-slate-200 dark:border-slate-700">41,975,000 GHC</td>
                                        <td class="p-3.5 font-bold text-emerald-700 dark:text-emerald-300">{{ number_format((41975000 * $investor->equity_percentage) / 100, 0) }} GHC + Buyout</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Vault Documents List -->
                    <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-sm">
                        <h3 class="text-base font-black text-slate-900 dark:text-white mb-4">Your Verified Compliance Documents</h3>
                        <div class="divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach($investor->documents as $doc)
                                <div class="py-3.5 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="text-xl">📄</span>
                                        <div>
                                            <div class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white">{{ $doc->document_title }}</div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400">{{ $doc->original_filename }} ({{ $doc->formatted_file_size }})</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-black uppercase px-2.5 py-1 rounded-full bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-500/40 font-bold">Verified</span>
                                        <a href="/investor/vault/document/{{ $doc->id }}/download" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-900 dark:text-white text-xs font-bold transition-colors border border-slate-200 dark:border-slate-700 shadow-xs">
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
