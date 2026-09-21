<x-layout title="Investor FAQ | NDFG LLC Regulatory & Investment Questions">
    <x-investor-nav />

    <div class="min-h-screen bg-slate-50 dark:bg-[#0b0f17] text-slate-900 dark:text-white relative overflow-hidden transition-colors selection:bg-brand-500 selection:text-black">
        <!-- Ambient glows -->
        <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[850px] h-[450px] bg-gradient-to-tr from-amber-500/10 via-brand-500/5 to-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-5xl 2xl:max-w-[1400px] w-full mx-auto px-4 sm:px-6 lg:px-8 pt-12 sm:pt-16 pb-20 relative z-10">
            <!-- Header -->
            <div class="text-center max-w-3xl mx-auto mb-16">
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-amber-50 dark:bg-amber-500/15 border border-amber-200 dark:border-amber-500/30 text-amber-800 dark:text-amber-300 text-xs font-black uppercase tracking-wider mb-3">
                    ❓ Questions & Clarifications
                </div>
                <h1 class="text-3xl sm:text-5xl font-black text-slate-900 dark:text-white tracking-tight">Frequently Asked Questions</h1>
                <p class="text-base sm:text-lg text-slate-600 dark:text-slate-300 mt-4 leading-relaxed font-medium">
                    Everything you need to know about our regulatory compliance gates, escrow custody, tranche commitments, and dividend distributions.
                </p>
            </div>

            <!-- FAQ Accordion using Alpine.js with Solid Visible Borders -->
            <div class="space-y-4" x-data="{ active: 1 }">
                <!-- Q1 -->
                <div class="rounded-2xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden transition-all">
                    <button @click="active = (active === 1 ? null : 1)" class="w-full p-6 text-left flex items-center justify-between gap-4 font-bold text-base sm:text-lg text-slate-900 dark:text-white hover:text-amber-600 dark:hover:text-amber-400 transition-colors">
                        <span>Why does Ride My Cars gate its investment opportunity behind dynamic accreditation?</span>
                        <span class="text-amber-600 dark:text-brand-400 text-xl font-black" x-text="active === 1 ? '−' : '+'"></span>
                    </button>
                    <div x-show="active === 1" x-collapse class="px-6 pb-6 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed space-y-2 border-t border-slate-200 dark:border-slate-800 pt-4">
                        <p>International securities regulations differ significantly across sovereign jurisdictions. Because this offering is an exempt private placement under U.S. SEC Regulation D (Rule 506(c)), UK FCA FPO guidelines, Canadian NI 45-106, and Ghana SEC rules, we cannot present an open public offer.</p>
                        <p>Our dynamic onboarding gateway determines your regulatory tier based on your residency and requires appropriate documentation to protect both NDFG LLC and our investor partners from regulatory friction.</p>
                    </div>
                </div>

                <!-- Q2 -->
                <div class="rounded-2xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden transition-all">
                    <button @click="active = (active === 2 ? null : 2)" class="w-full p-6 text-left flex items-center justify-between gap-4 font-bold text-base sm:text-lg text-slate-900 dark:text-white hover:text-amber-600 dark:hover:text-amber-400 transition-colors">
                        <span>What is the role of Eminsang Group Limited in this transaction?</span>
                        <span class="text-amber-600 dark:text-brand-400 text-xl font-black" x-text="active === 2 ? '−' : '+'"></span>
                    </button>
                    <div x-show="active === 2" x-collapse class="px-6 pb-6 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed space-y-2 border-t border-slate-100 dark:border-slate-800 pt-4">
                        <p>Eminsang Group Limited (Ghana) acts as our licensed regional corporate framework partner. They oversee local financial processing, regulatory tracking, domestic escrow custody, and cross-border capital repatriation routes to guarantee full compliance with the Bank of Ghana and Ghana Securities and Exchange Commission (SEC).</p>
                    </div>
                </div>

                <!-- Q3 -->
                <div class="rounded-2xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden transition-all">
                    <button @click="active = (active === 3 ? null : 3)" class="w-full p-6 text-left flex items-center justify-between gap-4 font-bold text-base sm:text-lg text-slate-900 dark:text-white hover:text-amber-600 dark:hover:text-amber-400 transition-colors">
                        <span>How are dividend distributions calculated and paid?</span>
                        <span class="text-amber-600 dark:text-brand-400 text-xl font-black" x-text="active === 3 ? '−' : '+'"></span>
                    </button>
                    <div x-show="active === 3" x-collapse class="px-6 pb-6 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed space-y-2 border-t border-slate-100 dark:border-slate-800 pt-4">
                        <p>Dividends are paid quarterly directly to your verified remittance path (International SWIFT wire, domestic Ghana Cedis transfer, or Mobile Money push). Distributions are drawn from net cohort cash flow after operational reserves, reflecting your tranche equity stake (10.0%, 14.0%, or 22.0%).</p>
                    </div>
                </div>

                <!-- Q4 -->
                <div class="rounded-2xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden transition-all">
                    <button @click="active = (active === 4 ? null : 4)" class="w-full p-6 text-left flex items-center justify-between gap-4 font-bold text-base sm:text-lg text-slate-900 dark:text-white hover:text-amber-600 dark:hover:text-amber-400 transition-colors">
                        <span>What happens at the end of the 3-Year Single Cohort term?</span>
                        <span class="text-amber-600 dark:text-brand-400 text-xl font-black" x-text="active === 4 ? '−' : '+'"></span>
                    </button>
                    <div x-show="active === 4" x-collapse class="px-6 pb-6 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed space-y-2 border-t border-slate-100 dark:border-slate-800 pt-4">
                        <p>Under the NDFG LLC Operating Agreement (v3.1), Year 3 initiates structured buyout provisions. Partners can elect either an institutional capital buyout multiple based on platform performance clawback matrices, or transition into perpetual equity holdings for subsequent international rollout stages.</p>
                    </div>
                </div>

                <!-- Q5 -->
                <div class="rounded-2xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden transition-all">
                    <button @click="active = (active === 5 ? null : 5)" class="w-full p-6 text-left flex items-center justify-between gap-4 font-bold text-base sm:text-lg text-slate-900 dark:text-white hover:text-amber-600 dark:hover:text-amber-400 transition-colors">
                        <span>How is my personal data and financial information protected?</span>
                        <span class="text-amber-600 dark:text-brand-400 text-xl font-black" x-text="active === 5 ? '−' : '+'"></span>
                    </button>
                    <div x-show="active === 5" x-collapse class="px-6 pb-6 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed space-y-2 border-t border-slate-100 dark:border-slate-800 pt-4">
                        <p>All sensitive documents uploaded during KYC/AML accreditation (Passports, CPA Letters, Tax Identification documents) are stored in an isolated, non-public Secure Document Vault (`storage/app/investor_vault/`). Direct URL access is blocked, files are served only through authorized streaming controllers with permission checks, and an immutable audit trail records every access attempt.</p>
                    </div>
                </div>

                <!-- Q6 -->
                <div class="rounded-2xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden transition-all">
                    <button @click="active = (active === 6 ? null : 6)" class="w-full p-6 text-left flex items-center justify-between gap-4 font-bold text-base sm:text-lg text-slate-900 dark:text-white hover:text-amber-600 dark:hover:text-amber-400 transition-colors">
                        <span>Why can I not view wire instructions or payment coordinates before approval?</span>
                        <span class="text-amber-600 dark:text-brand-400 text-xl font-black" x-text="active === 6 ? '−' : '+'"></span>
                    </button>
                    <div x-show="active === 6" x-collapse class="px-6 pb-6 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed space-y-2 border-t border-slate-100 dark:border-slate-800 pt-4">
                        <p>Global securities and anti-money laundering regulations strictly forbid accepting investment funds prior to positive identity verification and regulatory suitability vetting. Wire routing codes, escrow account coordinates, and MoMo payment references unlock only after compliance officers approve your profile.</p>
                    </div>
                </div>
            </div>

            <!-- Still have questions -->
            <div class="mt-16 p-8 rounded-3xl bg-white dark:bg-[#131926] border border-slate-200 dark:border-slate-700 shadow-sm text-center max-w-xl mx-auto">
                <div class="text-3xl mb-3">💬</div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white">Have Specific Legal or Financial Inquiries?</h3>
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 mb-5 font-medium">Our Investor Relations Desk in Bethesda & Accra is standing by.</p>
                <a href="/investor/contact" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-brand-500 hover:bg-brand-400 text-black font-black text-xs uppercase tracking-wider transition-all shadow-md shadow-brand-500/25 hover:scale-105">
                    Contact Investor Relations Desk →
                </a>
            </div>
        </div>
    </div>
</x-layout>
