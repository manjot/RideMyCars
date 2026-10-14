<x-layout title="Investor Accreditation & Onboarding | NDFG LLC">
    <x-investor-nav />

    <div class="min-h-screen bg-slate-50 dark:bg-[#0b0f17] text-slate-900 dark:text-white relative overflow-hidden py-12 sm:py-16 transition-colors selection:bg-brand-500 selection:text-black"
         x-data="investorWizard({
             initialTranche: '{{ $selectedTranche ?? 'B' }}',
             countryRules: {{ json_encode($countryRules) }},
             plans: {{ json_encode($plans) }}
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

            <!-- In-App Notification / Validation Modal (Replacing browser alert) -->
            <div x-show="modal.show"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="fixed inset-0 z-50 overflow-y-auto bg-black/75 backdrop-blur-xs flex items-center justify-center p-4"
                 style="display: none;"
                 @keydown.escape.window="closeModal()">
                <div class="relative bg-white dark:bg-[#131926] border-2 border-amber-400 dark:border-amber-500 rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl text-left"
                     @click.away="closeModal()">
                    <div class="flex items-start gap-4 mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400 flex items-center justify-center text-2xl font-black shrink-0 border border-amber-300 dark:border-amber-500/30">
                            ⚠️
                        </div>
                        <div>
                            <div class="inline-block px-2.5 py-0.5 rounded-full bg-rose-100 dark:bg-rose-500/20 text-rose-700 dark:text-rose-300 text-[10px] font-black uppercase tracking-wider mb-1">
                                Action Required
                            </div>
                            <h3 class="text-lg font-black text-slate-900 dark:text-white" x-text="modal.title"></h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Please address the required missing item(s) below to continue.</p>
                        </div>
                    </div>

                    <!-- Missing Fields Listing (Point 3) -->
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 mb-6">
                        <div class="text-[11px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">
                            Missing Required Field(s):
                        </div>
                        <ul class="space-y-2">
                            <template x-for="(item, idx) in modal.items" :key="idx">
                                <li class="flex items-start gap-2.5 text-xs text-rose-700 dark:text-rose-400 font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mt-1.5 shrink-0"></span>
                                    <span x-text="item" class="leading-snug"></span>
                                </li>
                            </template>
                        </ul>
                    </div>

                    <div class="flex justify-end">
                        <button type="button" @click="closeModal()" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-brand-500 hover:bg-brand-400 text-black font-black text-xs uppercase tracking-wider transition-all shadow-md shadow-brand-500/30 hover:scale-[1.02] flex items-center justify-center gap-2">
                            <span>Acknowledge & Complete Missing Fields</span>
                            <span>→</span>
                        </button>
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

                <form method="POST" action="/investor/register" enctype="multipart/form-data" id="investorOnboardingForm" @submit="validateFinalSubmit($event)">
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
                                <select name="entity_type" x-model="form.entity_type" required class="w-full px-4 py-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:bg-white focus:border-brand-500">
                                    <option value="" disabled selected>-- Select Entity Type --</option>
                                    <option value="individual">Natural Person / Individual</option>
                                    <option value="corporate">Corporate Entity / LLC / Ltd</option>
                                    <option value="institutional">Institutional Fund / Family Office</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Country of Residence / Incorporation *</label>
                                <select name="country_code" x-model="form.country_code" @change="onCountryChange()" required class="w-full px-4 py-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:bg-white focus:border-brand-500">
                                    <option value="" disabled selected>-- Select Country of Residence / Incorporation --</option>
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
                                <div class="flex gap-2">
                                    <select x-model="form.phone_country_code" class="w-28 sm:w-32 px-2.5 py-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs sm:text-sm font-semibold focus:outline-none focus:bg-white focus:border-brand-500">
                                        <option value="+1">+1 (US/CA)</option>
                                        <option value="+233">+233 (GH)</option>
                                        <option value="+44">+44 (UK)</option>
                                        <option value="+234">+234 (NG)</option>
                                        <option value="+254">+254 (KE)</option>
                                        <option value="+27">+27 (ZA)</option>
                                        <option value="+49">+49 (DE)</option>
                                        <option value="+33">+33 (FR)</option>
                                        <option value="+971">+971 (AE)</option>
                                        <option value="+86">+86 (CN)</option>
                                        <option value="+91">+91 (IN)</option>
                                        <option value="+61">+61 (AU)</option>
                                        <option value="+41">+41 (CH)</option>
                                    </select>
                                    <input type="text" x-model="form.phone_number" @input="form.phone_number = form.phone_number.replace(/[^0-9]/g, '')" required placeholder="Digits only (e.g. 244123456)" class="flex-1 px-4 py-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:bg-white focus:border-brand-500 transition-colors">
                                </div>
                                <input type="hidden" name="phone_number" :value="fullPhoneNumber">
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">Numbers only. Select country dial code from selector. Letters or symbols are rejected.</span>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Physical / Registered Address *</label>
                                <input type="text" name="address" x-model="form.address" required placeholder="Street Address *" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:bg-white focus:border-brand-500 mb-3">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">City *</label>
                                        <input type="text" name="city" x-model="form.city" required placeholder="City *" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">State / Province *</label>
                                        <input type="text" name="state" x-model="form.state" required placeholder="State / Province *" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">Postal Code *</label>
                                        <input type="text" name="postal_code" x-model="form.postal_code" required placeholder="Postal Code *" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm">
                                    </div>
                                </div>
                            </div>

                            @guest
                            <div class="sm:col-span-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Create Investor Portal Password *</label>
                                <input type="password" name="password" x-model="form.password" required placeholder="Choose a secure password (min 6 characters)" class="w-full px-4 py-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:bg-white focus:border-brand-500 transition-colors">
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">Used to log in to your secure investor dashboard and payment vault (required, minimum 6 characters).</span>
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
                            <div class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300">Required Legal Declarations & Certifications (All required) *:</div>

                            <template x-for="(decl, idx) in activeRule.declarations" :key="idx">
                                <label class="flex items-start gap-3 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800/80 cursor-pointer transition-colors">
                                    <input type="checkbox" :name="'declarations[' + (decl.id || idx) + ']'" value="1" class="declaration-checkbox mt-0.5 w-4 h-4 rounded text-brand-500 focus:ring-brand-400 border-slate-300 dark:border-slate-600">
                                    <span class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed font-medium" x-text="decl.label"></span>
                                </label>
                            </template>
                        </div>

                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 text-xs text-slate-600 dark:text-slate-400 leading-relaxed" x-text="activeRule.compliance_text"></div>

                        <div class="pt-6 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center">
                            <button type="button" @click="step = 1" class="px-5 py-2.5 rounded-xl text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white text-xs font-bold transition-colors">
                                ← Back to Step 1
                            </button>
                            <button type="button" @click="validateStep2() && (step = 3)" class="px-7 py-3 rounded-xl bg-brand-500 hover:bg-brand-400 text-black font-black text-xs uppercase tracking-wider transition-all shadow-md shadow-brand-500/25 flex items-center gap-2 hover:scale-105">
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

                        @php
                            $onboardingPlans = (isset($plans) && $plans->isNotEmpty()) ? $plans : collect([
                                (object) [
                                    'tranche_code' => 'A',
                                    'tier_name' => 'Seed Tier',
                                    'capital_commitment_ghc' => 720000.00,
                                    'capital_commitment_usd' => 60000.00,
                                    'equity_percentage' => 7.00,
                                ],
                                (object) [
                                    'tranche_code' => 'D',
                                    'tier_name' => 'Wealth builder',
                                    'capital_commitment_ghc' => 120000.00,
                                    'capital_commitment_usd' => 10000.00,
                                    'equity_percentage' => 1.20,
                                ],
                                (object) [
                                    'tranche_code' => 'B',
                                    'tier_name' => 'Growth Tier',
                                    'capital_commitment_ghc' => 1440000.00,
                                    'capital_commitment_usd' => 120000.00,
                                    'equity_percentage' => 14.00,
                                ],
                                (object) [
                                    'tranche_code' => 'C',
                                    'tier_name' => 'Venture Tier',
                                    'capital_commitment_ghc' => 2640000.00,
                                    'capital_commitment_usd' => 220000.00,
                                    'equity_percentage' => 22.00,
                                ],
                            ]);
                            $gridCols = count($onboardingPlans) >= 4 ? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4' : 'grid-cols-1 sm:grid-cols-3';
                        @endphp
                        <!-- Tranche Radio Options with Uniform Solid Borders -->
                        <div class="grid {{ $gridCols }} gap-4">
                            @foreach($onboardingPlans as $plan)
                                @php
                                    $isRecommended = ($plan->tranche_code === 'B');
                                @endphp
                                <label class="p-5 rounded-2xl border cursor-pointer transition-all flex flex-col justify-between relative"
                                       :class="form.selected_tranche === '{{ $plan->tranche_code }}' ? 'bg-amber-50 dark:bg-slate-800 border-2 border-amber-500 ring-4 ring-amber-500/15 shadow-xl' : 'bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-700 hover:border-slate-400'">
                                    @if($isRecommended)
                                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-0.5 rounded-full bg-amber-500 text-black font-black text-[9px] uppercase tracking-wider shadow-xs">
                                            Recommended
                                        </div>
                                    @endif
                                    <div class="flex items-center justify-between mb-3 {{ $isRecommended ? 'mt-1' : '' }}">
                                        <input type="radio" name="selected_tranche" value="{{ $plan->tranche_code }}" x-model="form.selected_tranche" class="w-4 h-4 text-amber-500">
                                        <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded {{ $isRecommended ? 'bg-amber-100 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300' }}">{{ $plan->tier_name }}</span>
                                    </div>
                                    <div class="font-black text-lg text-slate-900 dark:text-white">Tranche {{ $plan->tranche_code }}</div>
                                    <div class="text-xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ number_format($plan->capital_commitment_ghc, 0) }} GHC</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Approx. ${{ number_format($plan->capital_commitment_usd, 0) }} USD</div>
                                    <div class="mt-4 pt-3 border-t border-slate-200 dark:border-slate-700 text-xs font-extrabold text-emerald-600 dark:text-emerald-400">
                                        {{ number_format($plan->equity_percentage, 2) }}% Fixed Equity Stake
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        <!-- Remittance Path Selection (Point 8) -->
                        <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Preferred Capital Remittance Path *</label>
                            <select name="remittance_method" x-model="form.remittance_method" class="w-full px-4 py-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:bg-white focus:border-brand-500">
                                <option value="ndfg_usa_wire">New Development Finance Group LLC (USA) - Bank Wire / ACH</option>
                                <option value="rmc_ghana_wire">Ride My Cars LLC - Commercial Operating Wire</option>
                                <option value="stripe">Stripe (Cards & Apple Pay Gateway)</option>
                                <option value="momo_pay">MoMo Pay (MTN MoMo / Telecel Cash Gateway)</option>
                                <option value="wire_swift">Bank Wire (USD / EUR / GBP / CAD SWIFT)</option>
                                <option value="local_bank_ghana">Local Banking Rail (GHC Transfer via Ride My Cars (Ghana))</option>
                            </select>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">Full settlement coordinates, Stripe checkout link, and MoMo Pay merchant push instructions will be unlocked upon administrative compliance verification.</span>
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

                    <!-- STEP 4: DOCUMENT UPLOAD (Points 9 & 10) -->
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
                                    Certified CPA Letter / Wealth Statement (SEC Rule 506(c))
                                </label>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-3">Upload signed letter from licensed CPA, registered attorney, broker-dealer, or qualified wealth verification certificate (PDF, JPG, PNG up to 12MB).</p>
                                <input type="file" name="document_cpa_letter" @change="onFileChange($event, 'document_cpa_letter')" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-600 dark:text-slate-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-200 hover:file:bg-slate-300 dark:file:bg-slate-700 dark:hover:file:bg-slate-600 file:text-slate-900 dark:file:text-white cursor-pointer">
                            </div>

                            <!-- Annual Tax Returns File Upload Option (Point 9) -->
                            <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700">
                                <label class="block text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-1">
                                    Annual Tax Returns (Form 1040 / W-2 / Corporate Return)
                                </label>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-3">Upload signed copy of recent annual tax returns, IRS Form 1040, W-2, or corporate tax return filing (PDF, JPG, PNG up to 12MB).</p>
                                <input type="file" name="document_tax_returns" @change="onFileChange($event, 'document_tax_returns')" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-600 dark:text-slate-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-200 hover:file:bg-slate-300 dark:file:bg-slate-700 dark:hover:file:bg-slate-600 file:text-slate-900 dark:file:text-white cursor-pointer">
                            </div>

                            <!-- Ghana: Ghana Card / National ID -->
                            <div x-show="form.country_code === 'GHA'" class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700">
                                <label class="block text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-1">
                                    Ghana Card / Government Photo ID *
                                </label>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-3">Upload clear front and back copy of ECOWAS Ghana Card or Ghanaian Passport.</p>
                                <input type="file" name="document_gh_card" @change="onFileChange($event, 'document_gh_card')" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-600 dark:text-slate-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-200 hover:file:bg-slate-300 dark:file:bg-slate-700 dark:hover:file:bg-slate-600 file:text-slate-900 dark:file:text-white cursor-pointer">
                            </div>

                            <!-- Ghana / Africa: Tax TIN Proof -->
                            <div x-show="form.country_code === 'GHA' || form.country_code === 'AFRICA'" class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700">
                                <label class="block text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-1">
                                    Corporate Tax Identification Certificate (TIN)
                                </label>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-3">Tax Identification certificate issued by national revenue authority (GRA or regional authority).</p>
                                <input type="file" name="document_tax_tin" @change="onFileChange($event, 'document_tax_tin')" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-600 dark:text-slate-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-200 hover:file:bg-slate-300 dark:file:bg-slate-700 dark:hover:file:bg-slate-600 file:text-slate-900 dark:file:text-white cursor-pointer">
                            </div>

                            <!-- Canada: Risk Acknowledgement Form -->
                            <div x-show="form.country_code === 'CAN'" class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700">
                                <label class="block text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-1">
                                    Accredited Investor Risk Acknowledgement Schedule (NI 45-106)
                                </label>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-3">Executed Form 45-106F9 Risk Acknowledgement schedule for Canadian purchasers.</p>
                                <input type="file" name="document_risk_ack" @change="onFileChange($event, 'document_risk_ack')" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-600 dark:text-slate-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-200 hover:file:bg-slate-300 dark:file:bg-slate-700 dark:hover:file:bg-slate-600 file:text-slate-900 dark:file:text-white cursor-pointer">
                            </div>

                            <!-- General Valid Government Photo ID -->
                            <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700">
                                <label class="block text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-1">
                                    Valid Passport / Driver License / National ID *
                                </label>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-3">Official government issued photo identification showing legal entity representative identity.</p>
                                <input type="file" name="document_passport" @change="onFileChange($event, 'document_passport')" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-600 dark:text-slate-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-200 hover:file:bg-slate-300 dark:file:bg-slate-700 dark:hover:file:bg-slate-600 file:text-slate-900 dark:file:text-white cursor-pointer">
                            </div>

                            <!-- Proof of Address / Corporate Docs -->
                            <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700">
                                <label class="block text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-1">
                                    Proof of Address / Corporate Registration
                                </label>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-3">Recent utility bill (under 90 days) or Articles of Incorporation (if participating as an entity).</p>
                                <input type="file" name="document_address_proof" @change="onFileChange($event, 'document_address_proof')" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-xs text-slate-600 dark:text-slate-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-200 hover:file:bg-slate-300 dark:file:bg-slate-700 dark:hover:file:bg-slate-600 file:text-slate-900 dark:file:text-white cursor-pointer">
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center">
                            <button type="button" @click="step = 3" class="px-5 py-2.5 rounded-xl text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white text-xs font-bold transition-colors">
                                ← Back to Step 3
                            </button>
                            <button type="button" @click="validateStep4() && (step = 5)" class="px-7 py-3 rounded-xl bg-brand-500 hover:bg-brand-400 text-black font-black text-xs uppercase tracking-wider transition-all shadow-md shadow-brand-500/25 flex items-center gap-2 hover:scale-105">
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
                                <strong>Section 4.1 Cohort Architecture & Equity Governance:</strong> The participant agrees to commit capital to Ride My Cars New Development Finance Group (NDFG) LLC specifically designated for the Ride My Cars Ghana single cohort. The equity percentage locked herein (<span x-text="selectedEquityPercentage"></span>) shall remain fixed and protected against subsequent phase dilution throughout the three (3) year initial lifecycle.
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

                        <!-- Digital e-Signature Input (Point 11: Letters & spaces only) -->
                        <div class="p-6 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Digital e-Signature *</label>
                                <span class="text-[10px] text-emerald-700 dark:text-emerald-400 font-bold">256-Bit Cryptographic Audit Trail Active</span>
                            </div>

                            <div>
                                <input type="text" name="signer_name" x-model="form.signer_name"
                                       @input="form.signer_name = form.signer_name.replace(/[^a-zA-Z\s]/g, '')"
                                       required placeholder="Type your full legal name to execute electronic signature" class="w-full px-4 py-3.5 rounded-xl bg-white dark:bg-slate-900 border border-amber-500 text-slate-900 dark:text-white font-serif text-base tracking-wide focus:outline-none focus:border-brand-400">
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 block">Letters and spaces only. Numbers or special characters are strictly not accepted.</span>
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
                plans: config.plans || [],
                uploadedDocs: {},
                modal: {
                    show: false,
                    title: '',
                    items: [],
                },
                showModal(title, items) {
                    this.modal.title = title;
                    this.modal.items = Array.isArray(items) ? items : [items];
                    this.modal.show = true;
                },
                closeModal() {
                    this.modal.show = false;
                },
                onFileChange(e, docKey) {
                    if (e.target && e.target.files && e.target.files.length > 0) {
                        this.uploadedDocs[docKey] = e.target.files[0].name;
                    } else {
                        delete this.uploadedDocs[docKey];
                    }
                },
                get selectedEquityPercentage() {
                    if (this.plans && this.plans.length) {
                        const match = this.plans.find(p => p.tranche_code === this.form.selected_tranche);
                        if (match) {
                            return Number(match.equity_percentage).toFixed(2) + '%';
                        }
                    }
                    const fallbackMap = { 'A': '7.00%', 'D': '1.20%', 'B': '14.00%', 'C': '22.00%' };
                    return fallbackMap[this.form.selected_tranche] || '1.20%';
                },
                form: {
                    legal_name: '',
                    entity_type: '',
                    country_code: '',
                    phone_country_code: '+1',
                    email: '',
                    phone_number: '',
                    address: '',
                    city: '',
                    state: '',
                    postal_code: '',
                    password: '',
                    tax_id_or_national_id: '',
                    selected_tranche: config.initialTranche || 'B',
                    remittance_method: 'ndfg_usa_wire',
                    signer_name: '',
                },

                get fullPhoneNumber() {
                    const dial = (this.form.phone_country_code || '').trim();
                    const num = (this.form.phone_number || '').trim();
                    return num ? `${dial} ${num}`.trim() : '';
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
                    return this.countryRules[this.form.country_code] || this.countryRules['ROW'] || { declarations: [] };
                },

                onCountryChange() {
                    if (this.form.country_code === 'GHA') {
                        this.form.phone_country_code = '+233';
                        this.form.remittance_method = 'rmc_ghana_wire';
                    } else if (this.form.country_code === 'AFRICA') {
                        this.form.phone_country_code = '+233';
                        this.form.remittance_method = 'momo_pay';
                    } else if (this.form.country_code === 'GBR') {
                        this.form.phone_country_code = '+44';
                        this.form.remittance_method = 'wire_swift';
                    } else if (this.form.country_code === 'USA' || this.form.country_code === 'CAN') {
                        this.form.phone_country_code = '+1';
                        this.form.remittance_method = 'ndfg_usa_wire';
                    } else {
                        this.form.remittance_method = 'wire_swift';
                    }
                },

                canNavigateTo(targetStep) {
                    if (targetStep < this.step) return true;
                    if (this.step === 1 && !this.validateStep1()) return false;
                    if (this.step === 2 && !this.validateStep2()) return false;
                    if (this.step === 3 && targetStep > 3) return true;
                    if (this.step === 4 && !this.validateStep4()) return false;
                    return true;
                },

                validateStep1() {
                    const missing = [];
                    if (!this.form.legal_name || !this.form.legal_name.trim()) {
                        missing.push('Legal Entity or Individual Name');
                    }
                    if (!this.form.entity_type) {
                        missing.push('Entity Type (Select from dropdown)');
                    }
                    if (!this.form.country_code) {
                        missing.push('Country of Residence / Incorporation (Select from dropdown)');
                    }
                    if (!this.form.email || !this.form.email.trim()) {
                        missing.push('Primary Contact Email');
                    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email.trim())) {
                        missing.push('Primary Contact Email (Enter a valid email address)');
                    }
                    if (!this.form.phone_number || !this.form.phone_number.trim()) {
                        missing.push('Authorized Phone Number (Numbers only)');
                    }
                    if (!this.form.address || !this.form.address.trim()) {
                        missing.push('Physical / Registered Address (Street Address)');
                    }
                    if (!this.form.city || !this.form.city.trim()) {
                        missing.push('City');
                    }
                    if (!this.form.state || !this.form.state.trim()) {
                        missing.push('State / Province');
                    }
                    if (!this.form.postal_code || !this.form.postal_code.trim()) {
                        missing.push('Postal Code');
                    }
                    @guest
                    if (!this.form.password || this.form.password.length < 6) {
                        missing.push('Create Investor Portal Password (Minimum 6 characters required)');
                    }
                    @endguest

                    if (missing.length > 0) {
                        this.showModal('Identity & Location Verification Required', missing);
                        return false;
                    }

                    if (!this.form.signer_name) {
                        this.form.signer_name = this.form.legal_name.replace(/[^a-zA-Z\s]/g, '');
                    }
                    return true;
                },

                validateStep2() {
                    const missing = [];
                    if ((this.form.country_code === 'GHA' || this.form.country_code === 'AFRICA') && (!this.form.tax_id_or_national_id || !this.form.tax_id_or_national_id.trim())) {
                        missing.push('National ID / Ghana Card Number / Corporate TIN');
                    }

                    const declarations = document.querySelectorAll('.declaration-checkbox');
                    let allChecked = declarations.length > 0;
                    declarations.forEach(chk => {
                        if (!chk.checked) allChecked = false;
                    });
                    if (!allChecked) {
                        missing.push('All Legal Declarations & Certifications must be reviewed and checked');
                    }

                    if (missing.length > 0) {
                        this.showModal('Legal Declarations Incomplete', missing);
                        return false;
                    }
                    return true;
                },

                validateStep4() {
                    const missing = [];
                    if (this.form.country_code === 'USA') {
                        if (!this.uploadedDocs['document_cpa_letter'] && !this.uploadedDocs['document_tax_returns']) {
                            missing.push('Certified CPA Letter OR Annual Tax Returns (Required for SEC Rule 506(c))');
                        }
                    } else if (this.form.country_code === 'GHA') {
                        if (!this.uploadedDocs['document_gh_card'] && !this.uploadedDocs['document_passport']) {
                            missing.push('Ghana Card / Government Photo ID or Ghanaian Passport');
                        }
                    }

                    if (!this.uploadedDocs['document_passport'] && !this.uploadedDocs['document_gh_card']) {
                        missing.push('Valid Passport / Driver License / National ID Photo');
                    }

                    if (missing.length > 0) {
                        this.showModal('Mandatory Verification Documents Required', missing);
                        return false;
                    }
                    return true;
                },

                validateFinalSubmit(e) {
                    const missing = [];
                    const governanceAccepted = document.querySelector('input[name="governance_clause_accepted"]');
                    if (governanceAccepted && !governanceAccepted.checked) {
                        missing.push('Acceptance of NDFG LLC Operating Agreement Governance Clause (v3.1)');
                    }
                    if (!this.form.signer_name || !this.form.signer_name.trim()) {
                        missing.push('Digital e-Signature (Type your legal name)');
                    } else if (/[^a-zA-Z\s]/.test(this.form.signer_name)) {
                        this.form.signer_name = this.form.signer_name.replace(/[^a-zA-Z\s]/g, '');
                        missing.push('Digital e-Signature cannot contain numbers or special characters');
                    }

                    if (missing.length > 0) {
                        e.preventDefault();
                        this.showModal('e-Signature Submission Incomplete', missing);
                        return false;
                    }
                    return true;
                }
            };
        }
    </script>
</x-layout>
