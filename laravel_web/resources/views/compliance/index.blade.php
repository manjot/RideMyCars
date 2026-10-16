<x-layout>
    <x-slot:title>State Regulatory Compliance & Zero-Tolerance Policies (Georgia, California, Michigan) — RideMyCars</x-slot>

    <!-- SEO Meta Tags -->
    <x-slot:meta>
        <meta name="description" content="Official State Regulatory Compliance and Zero-Tolerance Drug and Alcohol Policies for RideMyCars across Georgia (Atlanta DPS), California (CPUC TNC), and Michigan (PA 345).">
        <meta name="robots" content="index, follow">
    </x-slot>

    <!-- Print styling -->
    <style>
        @media print {
            body { background: white !important; color: black !important; }
            header, footer, nav, .no-print { display: none !important; }
            .print-full { width: 100% !important; max-width: none !important; margin: 0 !important; padding: 0 !important; }
            a { text-decoration: none !important; color: black !important; }
        }
    </style>

    <main x-data="{ 
        activeTab: 'all', // 'all', 'georgia', 'california', 'michigan'
        reportModalOpen: false,
        selectedStateReport: 'georgia'
    }" class="flex-1 w-full max-w-7xl mx-auto px-4 py-10 sm:px-6 lg:px-8 print-full">
        
        <!-- Breadcrumb Navigation -->
        <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400 mb-6 no-print">
            <a href="/" class="hover:text-amber-500 transition-colors">Home</a>
            <span>/</span>
            <a href="/legal" class="hover:text-amber-500 transition-colors">Legal & Compliance</a>
            <span>/</span>
            <span class="text-gray-900 dark:text-white font-bold">State Zero-Tolerance Policies</span>
        </nav>

        <!-- Page Header Banner -->
        <div class="mb-10 p-6 sm:p-10 bg-slate-900 text-white rounded-3xl shadow-xl relative overflow-hidden border border-slate-800">
            <!-- Background Glow Decor -->
            <div class="absolute -right-20 -top-20 w-80 h-80 bg-brand-500/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -left-20 -bottom-20 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-amber-400/20 border border-amber-400/40 rounded-full text-xs font-black uppercase tracking-widest text-amber-300 mb-4">
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                        Official State Regulatory Directory • Zero-Tolerance Master Policies
                    </div>
                    <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white uppercase">
                        State Compliance & Zero-Tolerance Policies
                    </h1>
                    <p class="text-slate-300 text-sm sm:text-base mt-3 max-w-3xl font-medium leading-relaxed">
                        RideMyCars enforces strict statutory zero-tolerance mandates regarding driver drug and alcohol impairment in compliance with the Georgia Department of Public Safety (DPS), the California Public Utilities Commission (CPUC), and Michigan Public Act 345.
                    </p>
                </div>
                
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 shrink-0 no-print">
                    <button onclick="window.print()" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 border border-slate-600 text-white font-bold text-xs rounded-xl transition-all flex items-center gap-2 shadow-sm cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 17h2a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h2m2 4h6a2 2 0 0 0 2-2v-4H7v4a2 2 0 0 0 2 2zm8-12V5a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v4h10z"/></svg>
                        <span>Print Directory</span>
                    </button>
                    <a href="tel:+18552033177" class="px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-black text-xs rounded-xl shadow-md transition-all flex items-center gap-2">
                        <span>🚨 24/7 Hotline: +1 855 203 3177</span>
                    </a>
                </div>
            </div>

            <!-- Policy Metadata Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-8 pt-6 border-t border-slate-700/80 text-xs">
                <div>
                    <span class="text-slate-400 font-bold block mb-0.5">Corporate Parent</span>
                    <strong class="text-white font-extrabold text-sm">New Development Finance Group LLC</strong>
                </div>
                <div>
                    <span class="text-slate-400 font-bold block mb-0.5">Key Jurisdictions</span>
                    <strong class="text-amber-400 font-extrabold text-sm">Georgia, California & Michigan</strong>
                </div>
                <div>
                    <span class="text-slate-400 font-bold block mb-0.5">Statutory Standard</span>
                    <strong class="text-white font-extrabold text-sm">Absolute Zero-Tolerance Impairment</strong>
                </div>
                <div>
                    <span class="text-slate-400 font-bold block mb-0.5">Audit Record Archive</span>
                    <strong class="text-white font-extrabold text-sm">3 Years Minimum Digital Ledger</strong>
                </div>
            </div>
        </div>

        <!-- State Navigation Tabs -->
        <div class="flex items-center gap-2 p-1.5 bg-gray-200/80 dark:bg-[#141414] rounded-2xl mb-8 overflow-x-auto no-scrollbar no-print">
            <button @click="activeTab = 'all'" 
                    :class="activeTab === 'all' ? 'bg-white dark:bg-[#202022] text-gray-900 dark:text-white shadow-sm font-black' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white font-bold'"
                    class="px-5 py-2.5 rounded-xl text-xs uppercase tracking-wider transition-all whitespace-nowrap cursor-pointer">
                🏛️ All States Overview
            </button>
            <button @click="activeTab = 'georgia'" 
                    :class="activeTab === 'georgia' ? 'bg-amber-400 text-slate-950 shadow-sm font-black' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white font-bold'"
                    class="px-5 py-2.5 rounded-xl text-xs uppercase tracking-wider transition-all whitespace-nowrap cursor-pointer">
                🍑 Georgia (Atlanta DPS)
            </button>
            <button @click="activeTab = 'california'" 
                    :class="activeTab === 'california' ? 'bg-blue-600 text-white shadow-sm font-black' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white font-bold'"
                    class="px-5 py-2.5 rounded-xl text-xs uppercase tracking-wider transition-all whitespace-nowrap cursor-pointer">
                🐻 California (CPUC Mandates)
            </button>
            <button @click="activeTab = 'michigan'" 
                    :class="activeTab === 'michigan' ? 'bg-emerald-600 text-white shadow-sm font-black' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white font-bold'"
                    class="px-5 py-2.5 rounded-xl text-xs uppercase tracking-wider transition-all whitespace-nowrap cursor-pointer">
                🚗 Michigan (PA 345 Sec. 37)
            </button>
        </div>

        <!-- 3 Feature State Cards Strip (Visible on 'all' tab) -->
        <div x-show="activeTab === 'all'" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            
            <!-- Georgia Card -->
            <div class="bg-white dark:bg-[#121214] p-6 sm:p-7 rounded-3xl border-2 border-amber-300 dark:border-amber-700/50 shadow-md hover:shadow-xl transition-all flex flex-col justify-between space-y-5 group">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="w-10 h-10 rounded-2xl bg-amber-400 text-slate-950 flex items-center justify-center font-black text-xl shadow-sm">
                            🍑
                        </span>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-500/30">
                            DPS & O.C.G.A. § 40-1-193
                        </span>
                    </div>
                    <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">
                        Georgia (Atlanta Metro)
                    </h3>
                    <div class="text-xs font-mono text-gray-500 dark:text-gray-400">
                        Doc ID: <strong class="text-amber-600 dark:text-amber-400">RMC-GA-ZTP-2026-V1</strong>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed font-medium">
                        Immediate platform lockout upon impairment complaint. Compliance Office managed by Marilyn Watson. 3-year digital ledger archived for Georgia DPS Motor Carrier Division within 96 hours.
                    </p>
                </div>
                <div class="pt-3 border-t border-gray-100 dark:border-white/5 space-y-2">
                    <a href="/compliance/georgia" class="flex items-center justify-center gap-2 w-full py-2.5 px-4 bg-amber-400 hover:bg-amber-500 text-slate-950 font-black text-xs rounded-xl shadow-sm transition-all">
                        <span>Read Georgia Full Policy →</span>
                    </a>
                </div>
            </div>

            <!-- California Card -->
            <div class="bg-white dark:bg-[#121214] p-6 sm:p-7 rounded-3xl border-2 border-blue-300 dark:border-blue-700/50 shadow-md hover:shadow-xl transition-all flex flex-col justify-between space-y-5 group">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="w-10 h-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-black text-xl shadow-sm">
                            🐻
                        </span>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-500/15 text-blue-700 dark:text-blue-300 border border-blue-500/30">
                            CPUC Decision 13-09-045
                        </span>
                    </div>
                    <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">
                        California (CPUC TNC)
                    </h3>
                    <div class="text-xs font-mono text-gray-500 dark:text-gray-400">
                        Doc ID: <strong class="text-blue-600 dark:text-blue-400">RMC-CA-ZTP-2026-V1</strong>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed font-medium">
                        Mandated CPUC contacts: Hotline 1-800-894-9444 & CIU_intake@cpuc.ca.gov. Covers PUCTRA fee, WAV Access for All (SB 1376 $0.10 fee), AB 5 vetting, and LAX/SFO airport permits.
                    </p>
                </div>
                <div class="pt-3 border-t border-gray-100 dark:border-white/5 space-y-2">
                    <a href="/compliance/california" class="flex items-center justify-center gap-2 w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-black text-xs rounded-xl shadow-sm transition-all">
                        <span>Read California Full Policy →</span>
                    </a>
                </div>
            </div>

            <!-- Michigan Card -->
            <div class="bg-white dark:bg-[#121214] p-6 sm:p-7 rounded-3xl border-2 border-emerald-300 dark:border-emerald-700/50 shadow-md hover:shadow-xl transition-all flex flex-col justify-between space-y-5 group">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="w-10 h-10 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-black text-xl shadow-sm">
                            🚗
                        </span>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30">
                            Public Act 345 of 2016
                        </span>
                    </div>
                    <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">
                        Michigan State (PA 345)
                    </h3>
                    <div class="text-xs font-mono text-gray-500 dark:text-gray-400">
                        Policy No: <strong class="text-emerald-600 dark:text-emerald-400">RMC-MI-001</strong>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed font-medium">
                        Immediate app deactivation executed within two (2) minutes of report. Covers alcohol, marijuana, and illegal drugs. 3-year record archive pursuant to Section 37.
                    </p>
                </div>
                <div class="pt-3 border-t border-gray-100 dark:border-white/5 space-y-2">
                    <a href="/compliance/michigan" class="flex items-center justify-center gap-2 w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs rounded-xl shadow-sm transition-all">
                        <span>Read Michigan Full Policy →</span>
                    </a>
                </div>
            </div>

        </div>

        <!-- Comparative Regulatory Matrix Table -->
        <div x-show="activeTab === 'all'" class="mb-12 bg-white dark:bg-[#121214] rounded-3xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-white/10 flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white">Statutory Regulatory Matrix by State</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Cross-jurisdictional overview of Zero-Tolerance enforcement mandates</p>
                </div>
                <span class="text-xs font-bold text-amber-600 dark:text-amber-400 bg-amber-500/10 px-3 py-1 rounded-full border border-amber-500/20">
                    Updated October 2026
                </span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 dark:bg-[#1a1a1c] text-gray-700 dark:text-gray-300 uppercase tracking-wider font-extrabold border-b border-gray-200 dark:border-white/10">
                        <tr>
                            <th class="p-4 sm:p-5">Jurisdiction</th>
                            <th class="p-4 sm:p-5">Regulatory Authority</th>
                            <th class="p-4 sm:p-5">Statutory Code</th>
                            <th class="p-4 sm:p-5">Lockout Window</th>
                            <th class="p-4 sm:p-5">Mandated Hotline / Contact</th>
                            <th class="p-4 sm:p-5">Record Retention</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5 text-gray-700 dark:text-gray-300">
                        <!-- Georgia -->
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="p-4 sm:p-5 font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span>🍑</span> Georgia (Atlanta)
                            </td>
                            <td class="p-4 sm:p-5">Georgia Department of Public Safety (DPS)</td>
                            <td class="p-4 sm:p-5 font-mono font-bold text-amber-700 dark:text-amber-400">O.C.G.A. § 40-1-193</td>
                            <td class="p-4 sm:p-5 font-semibold text-red-600 dark:text-red-400">Instant Automated Session Termination</td>
                            <td class="p-4 sm:p-5">Hotline: +1 855 203 3177<br><span class="text-gray-400">support@ridemycars.com</span></td>
                            <td class="p-4 sm:p-5 font-bold">3 Years (Audit response &le; 96 hrs)</td>
                        </tr>
                        <!-- California -->
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="p-4 sm:p-5 font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span>🐻</span> California
                            </td>
                            <td class="p-4 sm:p-5">California Public Utilities Commission (CPUC)</td>
                            <td class="p-4 sm:p-5 font-mono font-bold text-blue-700 dark:text-blue-400">CPUC Decision 13-09-045</td>
                            <td class="p-4 sm:p-5 font-semibold text-red-600 dark:text-red-400">Immediate Automated Network Lockout</td>
                            <td class="p-4 sm:p-5">
                                <strong class="text-blue-600 dark:text-blue-400">1-800-894-9444</strong> (CPUC)<br>
                                <span class="font-mono text-gray-400">CIU_intake@cpuc.ca.gov</span>
                            </td>
                            <td class="p-4 sm:p-5 font-bold">3 Years (Subject to unannounced audit)</td>
                        </tr>
                        <!-- Michigan -->
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="p-4 sm:p-5 font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span>🚗</span> Michigan State
                            </td>
                            <td class="p-4 sm:p-5">State of Michigan Legislature</td>
                            <td class="p-4 sm:p-5 font-mono font-bold text-emerald-700 dark:text-emerald-400">Public Act 345 of 2016 (Sec. 37)</td>
                            <td class="p-4 sm:p-5 font-semibold text-red-600 dark:text-red-400">Within Two (2) Minutes</td>
                            <td class="p-4 sm:p-5">Hotline: +1 855 203 3177<br><span class="text-gray-400">support@ridemycars.com</span></td>
                            <td class="p-4 sm:p-5 font-bold">3 Years (From complaint date)</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Georgia Inline Section (shown on 'georgia' tab) -->
        <div x-show="activeTab === 'georgia'" class="space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-white/10">
                <div>
                    <h2 class="text-2xl font-black text-gray-900 dark:text-white">Georgia / Atlanta Official Zero-Tolerance Policy</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">O.C.G.A. § 40-1-193 et seq. & Georgia Department of Public Safety (DPS)</p>
                </div>
                <a href="/compliance/georgia" class="text-xs font-black text-amber-600 dark:text-amber-400 hover:underline">
                    Open Dedicated Page →
                </a>
            </div>
            
            <div class="p-6 bg-white dark:bg-[#121214] rounded-3xl border border-gray-200 dark:border-white/10 space-y-4 text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                <p><strong>1. Statement of Policy:</strong> RideMyCars maintains a strict, absolute zero-tolerance policy regarding the use of drugs, alcohol, or any intoxicating substances by independent driver partners and chauffeurs. Drivers are strictly prohibited from consuming, being under the influence of, or possessing open containers while logged in, en route, or transporting passengers.</p>
                <p><strong>2. Immediate Account Suspension Mechanics:</strong> Upon receipt of a passenger complaint or a safety team alert alleging drug or alcohol impairment, the automated dispatch system instantly terminates the driver's active session and locks the driver out of the platform.</p>
                <p><strong>3. Passenger Reporting Channels:</strong> In-App SOS Emergency Suite / Report Safety Issue button; Direct 24/7 Telephone Hotline: <a href="tel:+18552033177" class="text-amber-600 dark:text-amber-400 font-bold">+1 855 203 3177</a>; Email: <a href="mailto:support@ridemycars.com" class="text-amber-600 dark:text-amber-400 font-bold">support@ridemycars.com</a> with subject "SAFETY COMPLIANCE: DRIVER IMPAIRMENT".</p>
                <p><strong>4. Vetting & Audits:</strong> Impairment investigations handled under direction of Marilyn Watson (Compliance). Digital ledger of all complaints is archived for a minimum of 3 years and accessible to Georgia DPS Motor Carrier Division within 96 hours.</p>
            </div>
        </div>

        <!-- California Inline Section (shown on 'california' tab) -->
        <div x-show="activeTab === 'california'" class="space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-white/10">
                <div>
                    <h2 class="text-2xl font-black text-gray-900 dark:text-white">California Zero-Tolerance Drug and Alcohol Policy (CPUC)</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">CPUC Decision 13-09-045 & California Public Utilities Code</p>
                </div>
                <a href="/compliance/california" class="text-xs font-black text-blue-600 dark:text-blue-400 hover:underline">
                    Open Dedicated Page →
                </a>
            </div>
            
            <div class="p-6 bg-white dark:bg-[#121214] rounded-3xl border border-gray-200 dark:border-white/10 space-y-4 text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                <div class="p-4 bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 rounded-2xl">
                    <p class="font-extrabold text-blue-900 dark:text-blue-300">Mandated CPUC Direct Filing Details:</p>
                    <p class="text-xs mt-1">CPUC Consumer Affairs Hotline: <strong>1-800-894-9444</strong> • CPUC Compliance Email: <strong>CIU_intake@cpuc.ca.gov</strong></p>
                </div>
                <p><strong>1. Zero-Tolerance Statement:</strong> No driver shall consume, possess open containers of, or be under the influence of alcohol, cannabis, illegal controlled substances, or intoxicating prescription medications at any point while logged into the Ride My Cars digital network.</p>
                <p><strong>2. Immediate Suspension:</strong> Automated network lockout upon receiving passenger complaint via app, telephone, or CPUC representative. Permanent termination across all NDFG logistics vertical platforms upon corroboration.</p>
                <p><strong>3. California Corporate Mandates:</strong> Class A TNC Permit, PUCTRA access fee remittance, SB 1376 Access for All WAV fee ($0.10/trip), AB 5 7-year background check, $1,000,000 commercial liability coverage, and LAX/SFO airport permits.</p>
            </div>
        </div>

        <!-- Michigan Inline Section (shown on 'michigan' tab) -->
        <div x-show="activeTab === 'michigan'" class="space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-white/10">
                <div>
                    <h2 class="text-2xl font-black text-gray-900 dark:text-white">Michigan Zero-Tolerance Policy (Public Act 345)</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Policy Number: RMC-MI-001 • Michigan Public Act 345 of 2016 (Sec. 37)</p>
                </div>
                <a href="/compliance/michigan" class="text-xs font-black text-emerald-600 dark:text-emerald-400 hover:underline">
                    Open Dedicated Page →
                </a>
            </div>
            
            <div class="p-6 bg-white dark:bg-[#121214] rounded-3xl border border-gray-200 dark:border-white/10 space-y-4 text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                <div class="p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 rounded-2xl">
                    <p class="font-extrabold text-emerald-900 dark:text-emerald-300">Statutory 2-Minute Lockout Window:</p>
                    <p class="text-xs mt-1">Under Michigan PA 345, flagged driver accounts are automatically suspended within two (2) minutes of receiving a complaint.</p>
                </div>
                <p><strong>1. Purpose & Scope:</strong> Applies to all active drivers operating in the State of Michigan. Zero-tolerance regarding alcohol, illegal drugs, or controlled substances.</p>
                <p><strong>2. Prohibited Conduct:</strong> Consuming, possessing, or being under influence of alcohol, marijuana, illegal drugs, or unauthorized prescription medications while Driver App is open, active, or accepting dispatches.</p>
                <p><strong>3. Reporting Mechanism:</strong> In-App "Report Driver Safety" inside active trip menu or final rating screen; Web Portal: www.ridemycars.com; Support Email: support@ridemycars.com; Phone: +1 855 203 3177.</p>
                <p><strong>4. Record Retention:</strong> Pursuant to Michigan Public Act 345, minimum duration of three (3) years secure archive.</p>
            </div>
        </div>

        <!-- 24/7 Safety Command Center Banner -->
        <div class="mt-12 p-8 rounded-3xl bg-slate-900 text-white border border-slate-800 flex flex-col md:flex-row items-center justify-between gap-6 shadow-xl">
            <div class="flex items-center gap-5">
                <div class="w-14 h-14 rounded-2xl bg-amber-400 text-slate-950 flex items-center justify-center font-black text-2xl shrink-0 shadow-md">
                    🛡️
                </div>
                <div>
                    <h3 class="text-xl font-black text-white">RideMyCars Safety Command Center</h3>
                    <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-xl leading-relaxed">
                        Dedicated 24/7 global operations desk handling driver verification audits, regulatory data requests, and emergency impairment interventions.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3 shrink-0 flex-wrap">
                <a href="tel:+18552033177" class="px-5 py-3 rounded-xl bg-amber-400 hover:bg-amber-500 text-slate-950 font-black text-xs shadow-md transition-all">
                    📞 Call +1 855 203 3177
                </a>
                <a href="mailto:support@ridemycars.com?subject=STATE%20COMPLIANCE%20INQUIRY" class="px-5 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs border border-slate-700 transition-all">
                    ✉️ Contact Compliance Desk
                </a>
            </div>
        </div>

    </main>
</x-layout>
