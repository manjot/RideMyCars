<x-layout title="Investor Accreditation & Onboarding | NDFG LLC">
    <x-investor-nav />

    <div class="min-h-screen bg-slate-50 dark:bg-[#0b0f17] text-slate-900 dark:text-white relative overflow-hidden py-12 sm:py-16 transition-colors selection:bg-brand-500 selection:text-black"
         x-data="investorWizard({
             initialTranche: '{{ $selectedTranche ?? 'B' }}',
             countryRules: {{ json_encode($countryRules) }}
         })">

        <!-- Background Ambient Accents -->
        <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[850px] h-[450px] bg-gradient-to-tr from-amber-500/10 via-brand-500/5 to-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-6xl 2xl:max-w-[1400px] w-full mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <!-- Header -->
            <div class="text-center max-w-2xl mx-auto mb-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-300 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300 text-xs font-black uppercase tracking-wider mb-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>NDFG LLC Private Placement • Compliance Gateway</span>
                </div>
                <h1 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">Investor Accreditation Onboarding</h1>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-2 font-medium">
                    Complete our regulatory suitability sequence to unlock the Ride My Cars Ghana project data room.
                </p>
            </div>

            <!-- 5-Step Progress Stepper with Solid Borders -->
            <div class="mb-10 p-4 sm:p-5 rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-sm">
                <div class="grid grid-cols-5 gap-2 sm:gap-4 text-center">
                    <!-- Step 1 -->
                    <div class="cursor-pointer" @click="canNavigateTo(1) && (step = 1)">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 mx-auto rounded-xl flex items-center justify-center text-xs font-black transition-all mb-1"
                             :class="step === 1 ? 'bg-brand-500 text-black shadow-md shadow-brand-500/30 ring-2 ring-brand-400/50' : (step > 1 ? 'bg-emerald-500 text-black font-extrabold' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700')">
                            <span x-show="step <= 1">1</span>
                            <span x-show="step > 1">✓</span>
                        </div>
                        <div class="text-[10px] sm:text-xs font-bold truncate" :class="step === 1 ? 'text-amber-700 dark:text-brand-400' : (step > 1 ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500')">
                            Identity
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="cursor-pointer" @click="canNavigateTo(2) && (step = 2)">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 mx-auto rounded-xl flex items-center justify-center text-xs font-black transition-all mb-1"
                             :class="step === 2 ? 'bg-brand-500 text-black shadow-md shadow-brand-500/30 ring-2 ring-brand-400/50' : (step > 2 ? 'bg-emerald-500 text-black font-extrabold' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700')">
                            <span x-show="step <= 2">2</span>
                            <span x-show="step > 2">✓</span>
                        </div>
                        <div class="text-[10px] sm:text-xs font-bold truncate" :class="step === 2 ? 'text-amber-700 dark:text-brand-400' : (step > 2 ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500')">
                            Compliance
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="cursor-pointer" @click="canNavigateTo(3) && (step = 3)">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 mx-auto rounded-xl flex items-center justify-center text-xs font-black transition-all mb-1"
                             :class="step === 3 ? 'bg-brand-500 text-black shadow-md shadow-brand-500/30 ring-2 ring-brand-400/50' : (step > 3 ? 'bg-emerald-500 text-black font-extrabold' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700')">
                            <span x-show="step <= 3">3</span>
                            <span x-show="step > 3">✓</span>
                        </div>
                        <div class="text-[10px] sm:text-xs font-bold truncate" :class="step === 3 ? 'text-amber-700 dark:text-brand-400' : (step > 3 ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500')">
                            Allocation
                        </div>
                    </div>

                    <!-- Step 4 -->
                    <div class="cursor-pointer" @click="canNavigateTo(4) && (step = 4)">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 mx-auto rounded-xl flex items-center justify-center text-xs font-black transition-all mb-1"
                             :class="step === 4 ? 'bg-brand-500 text-black shadow-md shadow-brand-500/30 ring-2 ring-brand-400/50' : (step > 4 ? 'bg-emerald-500 text-black font-extrabold' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700')">
                            <span x-show="step <= 4">4</span>
                            <span x-show="step > 4">✓</span>
                        </div>
                        <div class="text-[10px] sm:text-xs font-bold truncate" :class="step === 4 ? 'text-amber-700 dark:text-brand-400' : (step > 4 ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500')">
                            Vault Upload
                        </div>
                    </div>

                    <!-- Step 5 -->
                    <div class="cursor-pointer" @click="canNavigateTo(5) && (step = 5)">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 mx-auto rounded-xl flex items-center justify-center text-xs font-black transition-all mb-1"
                             :class="step === 5 ? 'bg-brand-500 text-black shadow-md shadow-brand-500/30 ring-2 ring-brand-400/50' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700'">
                            <span>5</span>
                        </div>
                        <div class="text-[10px] sm:text-xs font-bold truncate" :class="step === 5 ? 'text-amber-700 dark:text-brand-400' : 'text-slate-400 dark:text-slate-500'">
                            e-Sign
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Card with Solid Borders -->
            <div class="rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-xl p-6 sm:p-10">
                
                @if($errors->any())
                    <div class="p-4 mb-8 rounded-2xl bg-rose-50 dark:bg-rose-500/15 border border-rose-300 dark:border-rose-500/40 text-rose-800 dark:text-rose-300 text-xs">
                        <div class="font-bold mb-1">Please correct the following errors:</div>
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="/investor/register" enctype="multipart/form-data" id="investorOnboardingForm">
                    @csrf

                    <!-- STEP 1: IDENTITY & LOCATION -->
                    <div x-show="step === 1" x-transition:enter="transition duration-200" class="space-y-6">
                        <div>
                            <span class="text-xs font-black uppercase tracking-wider text-amber-600 dark:text-brand-400">Step 1 of 5</span>
                            <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1">Identity & Location Mapping</h2>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Please provide the legal name and jurisdiction under which your investment will be executed.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Legal Entity or Individual Name *</label>
                                <input type="text" name="legal_name" x-model="form.legal_name" required placeholder="Individual Full Name or LLC/Corp Entity Name" class="w-full px-4 py-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:bg-white focus:border-brand-500 transition-colors">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Entity Type *</label>
                                <select name="entity_type" x-model="form.entity_type" class="w-full px-4 py-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:bg-white focus:border-brand-500">
                                    <option value="individual">Natural Person / Individual</option>
                                    <option value="corporate">Corporate Entity / LLC / Ltd</option>
                                    <option value="institutional">Institutional Fund / Family Office</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Country of Residence / Incorporation *</label>
                                <select name="country_code" x-model="form.country_code" @change="onCountryChange()" class="w-full px-4 py-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:bg-white focus:border-brand-500">
                                    <option value="USA">United States (SEC Rule 506(c))</option>
                                    <option value="GHA">Ghana (SEC Ghana / Ride My Cars (Ghana))</option>
                                    <option value="CAN">Canada (NI 45-106 Exemptions)</option>
                                    <option value="GBR">United Kingdom (FCA FPO Sophisticated)</option>
                                    <option value="EU">European Union (Prospectus Reg Art 1(4))</option>
                                    <option value="AFRICA">Other African Nations (Pan-African SEC)</option>
                                    <option value="ROW">Rest of World (International Private Placement)</option>
                                </select>
                                <input type="hidden" name="country_residence" :value="currentCountryName">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Primary Contact Email *</label>
                                <input type="email" name="email" x-model="form.email" required placeholder="investor@domain.com" class="w-full px-4 py-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:bg-white focus:border-brand-500 transition-colors">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Authorized Phone Number *</label>
                                <input type="text" name="phone_number" x-model="form.phone_number" required placeholder="+1 (555) 000-0000" class="w-full px-4 py-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:bg-white focus:border-brand-500 transition-colors">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Physical / Registered Address</label>
                                <input type="text" name="address" x-model="form.address" placeholder="Street Address" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:bg-white focus:border-brand-500 mb-3">
                                <div class="grid grid-cols-3 gap-3">
                                    <input type="text" name="city" x-model="form.city" placeholder="City" class="px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm">
                                    <input type="text" name="state" x-model="form.state" placeholder="State/Province" class="px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm">
                                    <input type="text" name="postal_code" x-model="form.postal_code" placeholder="Postal Code" class="px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm">
                                </div>
                            </div>

                            @guest
                            <div class="sm:col-span-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Create Investor Portal Password *</label>
                                <input type="password" name="password" x-model="form.password" required placeholder="Choose a secure password (min 6 characters)" class="w-full px-4 py-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:bg-white focus:border-brand-500 transition-colors">
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">Used to log in to your secure investor dashboard and payment vault.</span>
                            </div>
                            @endguest
                        </div>

                        <div class="pt-6 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                            <button type="button" @click="validateStep1() && (step = 2)" class="px-7 py-3 rounded-xl bg-brand-500 hover:bg-brand-400 text-black font-black text-xs uppercase tracking-wider transition-all shadow-md shadow-brand-500/25 flex items-center gap-2 hover:scale-105">
                                <span>Continue to Dynamic Compliance Gate</span>
                                <span>→</span>
                            </button>
                        </div>
                    </div>

                    <!-- STEP 2: DYNAMIC COMPLIANCE GATE -->
                    <div x-show="step === 2" x-transition:enter="transition duration-200" class="space-y-6" style="display: none;">
                        <div>
                            <span class="text-xs font-black uppercase tracking-wider text-amber-600 dark:text-brand-400">Step 2 of 5</span>
                            <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1">Dynamic Multi-Jurisdictional Status Verification</h2>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1" x-text="'Configured specifically for ' + currentCountryName + ' under sovereign securities frameworks.'"></p>
                        </div>

                        <!-- Active Regulatory Gate Banner -->
                        <div class="p-5 rounded-2xl bg-amber-50 dark:bg-slate-800/60 border border-amber-200 dark:border-slate-700 flex items-start gap-4 shadow-xs">
                            <span class="text-3xl" x-text="countryFlag"></span>
                            <div>
                                <div class="text-xs font-bold text-amber-700 dark:text-brand-400 uppercase tracking-wider" x-text="activeRule.regulatory_body"></div>
                                <h3 class="text-base font-black text-slate-900 dark:text-white mt-0.5" x-text="activeRule.verification_gate_title"></h3>
                                <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 leading-relaxed" x-text="activeRule.verification_gate_description"></p>
                            </div>
                        </div>

                        <!-- Conditional Form Elements for Ghana & Africa -->
                        <template x-if="form.country_code === 'GHA' || form.country_code === 'AFRICA'">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                    <span x-text="form.country_code === 'GHA' ? 'National ID / Ghana Card Number / Corporate TIN *' : 'National ID / Tax Identification Number *'"></span>
                                </label>
                                <input type="text" name="tax_id_or_national_id" x-model="form.tax_id_or_national_id" required placeholder="GHA-123456789-0 or TIN Number" class="w-full px-4 py-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:bg-white focus:border-brand-500">
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">Subject to automated verification check via Ride My Cars (Ghana).</span>
                            </div>
                        </template>

                        <!-- Dynamic Accreditation Declarations Checkboxes with Solid Borders -->
                        <div class="space-y-3">
                            <div class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300">Required Legal Declarations & Certifications:</div>

                            <template x-for="(decl, idx) in activeRule.declarations" :key="idx">
                                <label class="flex items-start gap-3 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800/80 cursor-pointer transition-colors">
                                    <input type="checkbox" :name="'declarations[' + (decl.id || idx) + ']'" value="1" class="mt-0.5 w-4 h-4 rounded text-brand-500 focus:ring-brand-400 border-slate-300 dark:border-slate-600" checked>
                                    <span class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed font-medium" x-text="decl.label"></span>
                                </label>
                            </template>
                        </div>

                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 text-xs text-slate-600 dark:text-slate-400 leading-relaxed" x-text="activeRule.compliance_text"></div>

                        <div class="pt-6 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center">
                            <button type="button" @click="step = 1" class="px-5 py-2.5 rounded-xl text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white text-xs font-bold transition-colors">
                                ← Back to Step 1
                            </button>
                            <button type="button" @click="step = 3" class="px-7 py-3 rounded-xl bg-brand-500 hover:bg-brand-400 text-black font-black text-xs uppercase tracking-wider transition-all shadow-md shadow-brand-500/25 flex items-center gap-2 hover:scale-105">
                                <span>Continue to Allocation Selection</span>
                                <span>→</span>
                            </button>
                        </div>
                    </div>

                    <!-- STEP 3: ASSET COMMITMENT & ALLOCATION SELECTION -->
                    <div x-show="step === 3" x-transition:enter="transition duration-200" class="space-y-6" style="display: none;">
                        <div>
                            <span class="text-xs font-black uppercase tracking-wider text-amber-600 dark:text-brand-400">Step 3 of 5</span>
                            <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1">Asset Commitment & Allocation Selection</h2>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Select your desired investment tranche in the Ride My Cars Ghana single cohort.</p>
                        </div>

                        <!-- Tranche Radio Options with Uniform Solid Borders -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <!-- Tranche A -->
                            <label class="p-5 rounded-2xl border cursor-pointer transition-all flex flex-col justify-between"
                                   :class="form.selected_tranche === 'A' ? 'bg-amber-50 dark:bg-slate-800 border-2 border-amber-500 ring-2 ring-amber-500/20 shadow-md' : 'bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-700 hover:border-slate-400'">
                                <div class="flex items-center justify-between mb-3">
                                    <input type="radio" name="selected_tranche" value="A" x-model="form.selected_tranche" class="w-4 h-4 text-amber-500">
                                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300">Seed Tier</span>
                                </div>
                                <div class="font-black text-lg text-slate-900 dark:text-white">Tranche A</div>
                                <div class="text-xl font-black text-amber-600 dark:text-amber-400 mt-1">720,000 GHC</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">Approx. $60,000 USD</div>
                                <div class="mt-4 pt-3 border-t border-slate-200 dark:border-slate-700 text-xs font-extrabold text-emerald-600 dark:text-emerald-400">
                                    10.0% Fixed Equity Stake
                                </div>
                            </label>

                            <!-- Tranche B -->
                            <label class="p-5 rounded-2xl border cursor-pointer transition-all flex flex-col justify-between relative"
                                   :class="form.selected_tranche === 'B' ? 'bg-amber-50 dark:bg-slate-800 border-2 border-amber-500 ring-4 ring-amber-500/15 shadow-xl' : 'bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-700 hover:border-slate-400'">
                                <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-0.5 rounded-full bg-amber-500 text-black font-black text-[9px] uppercase tracking-wider shadow-xs">
                                    Recommended
                                </div>
                                <div class="flex items-center justify-between mb-3 mt-1">
                                    <input type="radio" name="selected_tranche" value="B" x-model="form.selected_tranche" class="w-4 h-4 text-amber-500">
                                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded bg-amber-100 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300">Growth Tier</span>
                                </div>
                                <div class="font-black text-lg text-slate-900 dark:text-white">Tranche B</div>
                                <div class="text-xl font-black text-amber-600 dark:text-amber-400 mt-1">1,440,000 GHC</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">Approx. $120,000 USD</div>
                                <div class="mt-4 pt-3 border-t border-slate-200 dark:border-slate-700 text-xs font-extrabold text-emerald-600 dark:text-emerald-400">
                                    14.0% Fixed Equity Stake
                                </div>
                            </label>

                            <!-- Tranche C -->
                            <label class="p-5 rounded-2xl border cursor-pointer transition-all flex flex-col justify-between"
                                   :class="form.selected_tranche === 'C' ? 'bg-emerald-50 dark:bg-slate-800 border-2 border-emerald-500 ring-2 ring-emerald-500/20 shadow-md' : 'bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-700 hover:border-slate-400'">
                                <div class="flex items-center justify-between mb-3">
                                    <input type="radio" name="selected_tranche" value="C" x-model="form.selected_tranche" class="w-4 h-4 text-emerald-500">
                                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300">Venture Tier</span>
                                </div>
                                <div class="font-black text-lg text-slate-900 dark:text-white">Tranche C</div>
                                <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1">2,640,000 GHC</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">Approx. $220,000 USD</div>
                                <div class="mt-4 pt-3 border-t border-slate-200 dark:border-slate-700 text-xs font-extrabold text-emerald-600 dark:text-emerald-400">
                                    22.0% Fixed Equity Stake
                                </div>
                            </label>
                        </div>

                        <!-- Remittance Path Selection -->
                        <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Preferred Capital Remittance Path *</label>
                            <select name="remittance_method" x-model="form.remittance_method" class="w-full px-4 py-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:bg-white focus:border-brand-500">
                                <option value="wire_swift">Bank Wire (USD / EUR / GBP / CAD SWIFT)</option>
                                <option value="local_bank_ghana">Local Banking Rail (GHC Transfer via Ride My Cars (Ghana))</option>
                                <option value="mobile_money">Mobile Money Gateway (MTN MoMo / Telecel Cash)</option>
                            </select>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">Full wire coordinates and merchant push instructions will be unlocked upon administrative compliance verification.</span>
                        </div>

                        <div class="pt-6 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center">
                            <button type="button" @click="step = 2" class="px-5 py-2.5 rounded-xl text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white text-xs font-bold transition-colors">
                                ← Back to Step 2
                            </button>
                            <button type="button" @click="step = 4" class="px-7 py-3 rounded-xl bg-brand-500 hover:bg-brand-400 text-black font-black text-xs uppercase tracking-wider transition-all shadow-md shadow-brand-500/25 flex items-center gap-2 hover:scale-105">
                                <span>Continue to Secure Vault Upload</span>
                                <span>→</span>
                            </button>
                        </div>
                    </div>

                    <!-- STEP 4: DOCUMENT UPLOAD -->
                    <div x-show="step === 4" x-transition:enter="transition duration-200" class="space-y-6" style="display: none;">
                        <div>
                            <span class="text-xs font-black uppercase tracking-wider text-amber-600 dark:text-brand-400">Step 4 of 5</span>
                            <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1">Secure Document Vault Upload</h2>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Upload required verification credentials. Stored securely with direct URL access blocked.</p>
                        </div>

                        <!-- Dynamic Document Upload Boxes with Solid Borders -->
                        <div class="space-y-4">
                            <!-- USA: Certified CPA Letter / Wealth Statement -->
                            <div x-show="form.country_code === 'USA'" class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700">
                                <label class="block text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-1">
                                    Certified CPA Letter / Wealth Statement (SEC Rule 506(c)) *
                                </label>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-3">Upload signed letter from licensed CPA, registered attorney, broker-dealer, or qualified wealth verification certificate (PDF, JPG, PNG up to 10MB).</p>
                                <input type="file" name="document_cpa_letter" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-600 dark:text-slate-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-200 hover:file:bg-slate-300 dark:file:bg-slate-700 dark:hover:file:bg-slate-600 file:text-slate-900 dark:file:text-white cursor-pointer">
                            </div>

                            <!-- Ghana: Ghana Card / National ID -->
                            <div x-show="form.country_code === 'GHA'" class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700">
                                <label class="block text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-1">
                                    Ghana Card / Government Photo ID *
                                </label>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-3">Upload clear front and back copy of ECOWAS Ghana Card or Ghanaian Passport.</p>
                                <input type="file" name="document_gh_card" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-600 dark:text-slate-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-200 hover:file:bg-slate-300 dark:file:bg-slate-700 dark:hover:file:bg-slate-600 file:text-slate-900 dark:file:text-white cursor-pointer">
                            </div>

                            <!-- Ghana / Africa: Tax TIN Proof -->
                            <div x-show="form.country_code === 'GHA' || form.country_code === 'AFRICA'" class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700">
                                <label class="block text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-1">
                                    Corporate Tax Identification Certificate (TIN)
                                </label>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-3">Tax Identification certificate issued by national revenue authority (GRA or regional authority).</p>
                                <input type="file" name="document_tax_tin" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-600 dark:text-slate-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-200 hover:file:bg-slate-300 dark:file:bg-slate-700 dark:hover:file:bg-slate-600 file:text-slate-900 dark:file:text-white cursor-pointer">
                            </div>

                            <!-- Canada: Risk Acknowledgement Form -->
                            <div x-show="form.country_code === 'CAN'" class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700">
                                <label class="block text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-1">
                                    Accredited Investor Risk Acknowledgement Schedule (NI 45-106)
                                </label>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-3">Executed Form 45-106F9 Risk Acknowledgement schedule for Canadian purchasers.</p>
                                <input type="file" name="document_risk_ack" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-600 dark:text-slate-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-200 hover:file:bg-slate-300 dark:file:bg-slate-700 dark:hover:file:bg-slate-600 file:text-slate-900 dark:file:text-white cursor-pointer">
                            </div>

                            <!-- General Valid Government Photo ID -->
                            <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700">
                                <label class="block text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-1">
                                    Valid Passport / Driver License / National ID *
                                </label>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-3">Official government issued photo identification showing legal entity representative identity.</p>
                                <input type="file" name="document_passport" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-600 dark:text-slate-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-200 hover:file:bg-slate-300 dark:file:bg-slate-700 dark:hover:file:bg-slate-600 file:text-slate-900 dark:file:text-white cursor-pointer">
                            </div>

                            <!-- Proof of Address / Corporate Docs -->
                            <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700">
                                <label class="block text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-1">
                                    Proof of Address / Corporate Registration
                                </label>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-3">Recent utility bill (under 90 days) or Articles of Incorporation (if participating as an entity).</p>
                                <input type="file" name="document_address_proof" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-600 dark:text-slate-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-200 hover:file:bg-slate-300 dark:file:bg-slate-700 dark:hover:file:bg-slate-600 file:text-slate-900 dark:file:text-white cursor-pointer">
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center">
                            <button type="button" @click="step = 3" class="px-5 py-2.5 rounded-xl text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white text-xs font-bold transition-colors">
                                ← Back to Step 3
                            </button>
                            <button type="button" @click="step = 5" class="px-7 py-3 rounded-xl bg-brand-500 hover:bg-brand-400 text-black font-black text-xs uppercase tracking-wider transition-all shadow-md shadow-brand-500/25 flex items-center gap-2 hover:scale-105">
                                <span>Continue to Mandatory Agreement</span>
                                <span>→</span>
                            </button>
                        </div>
                    </div>

                    <!-- STEP 5: MANDATORY VERIFICATION AGREEMENT & E-SIGN -->
                    <div x-show="step === 5" x-transition:enter="transition duration-200" class="space-y-6" style="display: none;">
                        <div>
                            <span class="text-xs font-black uppercase tracking-wider text-amber-600 dark:text-brand-400">Step 5 of 5</span>
                            <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1">Mandatory Verification Agreement & e-Signature</h2>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Please review the personalized NDFG LLC Operating Agreement Governance Clause.</p>
                        </div>

                        <!-- Operating Agreement Embedded Clause Box -->
                        <div class="p-6 rounded-2xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300 space-y-4 max-h-72 overflow-y-auto leading-relaxed shadow-inner">
                            <div class="font-black text-sm text-slate-900 dark:text-white border-b border-slate-200 dark:border-slate-700 pb-2 flex items-center justify-between">
                                <span>NDFG LLC OPERATING AGREEMENT — GOVERNANCE CLAUSE (v3.1)</span>
                                <span class="text-[10px] text-amber-700 dark:text-amber-400 font-bold">Confidential</span>
                            </div>
                            <p>
                                <strong>Section 4.1 Cohort Architecture & Equity Governance:</strong> The participant agrees to commit capital to Ride My Cars New Development Finance Group (NDFG) LLC specifically designated for the Ride My Cars Ghana single cohort. The equity percentage locked herein (<span x-text="form.selected_tranche === 'A' ? '10.0%' : (form.selected_tranche === 'B' ? '14.0%' : '22.0%')"></span>) shall remain fixed and protected against subsequent phase dilution throughout the three (3) year initial lifecycle.
                            </p>
                            <p>
                                <strong>Section 6.3 Escrow Custody & Cross-Border Remittance:</strong> All capital remittances, local processing, and currency disbursements are conducted in direct coordination with Ride My Cars (Ghana). The participant covenants that funds transmitted are free of encumbrances and comply with applicable anti-money laundering and Bank of Ghana foreign exchange repatriation protocols.
                            </p>
                            <p>
                                <strong>Section 8.2 Year 3 Structured Buyout & Liquidity Option:</strong> At the expiration of Year Three (3) from initial deployment, NDFG LLC guarantees structured buyout provisions based on verified cohort gross revenue matrices, allowing the participant to either monetize equity at predefined multiples or elect ongoing perpetual yield distributions.
                            </p>
                            <p>
                                <strong>Section 11.4 Non-Public Offering Representations:</strong> The participant affirms that securities acquired through this portal are held for investment purposes only and cannot be resold without compliance with applicable securities laws (including SEC Rule 506(c), UK FSMA, or Ghana SEC requirements).
                            </p>
                        </div>

                        <!-- Affirmation Checkboxes -->
                        <div class="space-y-3 pt-2">
                            <label class="flex items-start gap-3 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer transition-colors">
                                <input type="checkbox" name="governance_clause_accepted" value="1" required class="mt-0.5 w-4 h-4 rounded text-brand-500 focus:ring-brand-400 border-slate-300 dark:border-slate-600" checked>
                                <span class="text-xs text-slate-800 dark:text-slate-200 font-medium leading-relaxed">
                                    I have read, understood, and irrevocably accept the <strong>NDFG LLC Operating Agreement Governance Clause (v3.1)</strong> and acknowledge that all funding commitments must settle within five (5) business days of approval notice. *
                                </span>
                            </label>
                        </div>

                        <!-- Digital e-Signature Input -->
                        <div class="p-6 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Digital e-Signature *</label>
                                <span class="text-[10px] text-emerald-700 dark:text-emerald-400 font-bold">256-Bit Cryptographic Audit Trail Active</span>
                            </div>

                            <div>
                                <input type="text" name="signer_name" x-model="form.signer_name" required placeholder="Type your full legal name to execute electronic signature" class="w-full px-4 py-3.5 rounded-xl bg-white dark:bg-slate-900 border border-amber-500 text-slate-900 dark:text-white font-serif text-base tracking-wide focus:outline-none focus:border-brand-400">
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">Typing your legal name constitutes a legally binding electronic signature under the U.S. Electronic Signatures in Global and National Commerce Act (E-SIGN) and UNCITRAL model law.</span>
                            </div>

                            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-500 dark:text-slate-400">
                                <div>IP Address: <span class="text-slate-800 dark:text-slate-200 font-mono">{{ request()->ip() }}</span></div>
                                <div>Timestamp: <span class="text-slate-800 dark:text-slate-200 font-mono">{{ now()->toIso8601String() }}</span></div>
                                <div>Agreement ID: <span class="text-slate-800 dark:text-slate-200 font-mono">NDFG_OA_v3.1</span></div>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center">
                            <button type="button" @click="step = 4" class="px-5 py-2.5 rounded-xl text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white text-xs font-bold transition-colors">
                                ← Back to Step 4
                            </button>
                            <button type="submit" class="px-9 py-4 rounded-xl bg-brand-500 hover:bg-brand-400 text-black font-black text-sm uppercase tracking-wider transition-all shadow-md shadow-brand-500/30 hover:scale-[1.02] flex items-center gap-2">
                                <span>Execute e-Signature & Submit Application</span>
                                <span>✓</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Alpine.js Wizard Logic -->
    <script>
        function investorWizard(config) {
            return {
                step: 1,
                countryRules: config.countryRules,
                form: {
                    legal_name: '',
                    entity_type: 'individual',
                    country_code: 'USA',
                    email: '',
                    phone_number: '',
                    address: '',
                    city: '',
                    state: '',
                    postal_code: '',
                    password: '',
                    tax_id_or_national_id: '',
                    selected_tranche: config.initialTranche || 'B',
                    remittance_method: 'wire_swift',
                    signer_name: '',
                },

                get currentCountryName() {
                    const map = {
                        'USA': 'United States',
                        'GHA': 'Ghana',
                        'CAN': 'Canada',
                        'GBR': 'United Kingdom',
                        'EU': 'European Union',
                        'AFRICA': 'Other African Nations',
                        'ROW': 'Rest of World'
                    };
                    return map[this.form.country_code] || 'International';
                },

                get countryFlag() {
                    const flags = {
                        'USA': '🇺🇸',
                        'GHA': '🇬🇭',
                        'CAN': '🇨🇦',
                        'GBR': '🇬🇧',
                        'EU': '🇪🇺',
                        'AFRICA': '🌍',
                        'ROW': '🌐'
                    };
                    return flags[this.form.country_code] || '🌐';
                },

                get activeRule() {
                    return this.countryRules[this.form.country_code] || this.countryRules['ROW'] || {};
                },

                onCountryChange() {
                    if (this.form.country_code === 'GHA') {
                        this.form.remittance_method = 'local_bank_ghana';
                    } else if (this.form.country_code === 'AFRICA') {
                        this.form.remittance_method = 'mobile_money';
                    } else {
                        this.form.remittance_method = 'wire_swift';
                    }
                },

                canNavigateTo(targetStep) {
                    if (targetStep < this.step) return true;
                    if (this.step === 1) return this.validateStep1();
                    return true;
                },

                validateStep1() {
                    if (!this.form.legal_name || !this.form.email || !this.form.phone_number) {
                        alert('Please fill in your Legal Name, Email, and Phone Number before proceeding.');
                        return false;
                    }
                    if (!this.form.signer_name) {
                        this.form.signer_name = this.form.legal_name;
                    }
                    return true;
                }
            };
        }
    </script>
</x-layout>
