<x-layout>
    <x-slot:title>Overcoming Compliance & Onboarding Friction — RideMyCars</x-slot>

    <!-- SEO Meta Tags -->
    <x-slot:meta>
        <meta name="description" content="RideMyCars operational compliance framework: Overcoming onboarding friction with the Document Concierge system, real-time communication push, and segmented driver incentives.">
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

    <main class="flex-1 w-full max-w-7xl mx-auto px-4 py-10 sm:px-6 lg:px-8 print-full">
        
        <!-- Page Header Banner -->
        <div class="mb-10 p-6 sm:p-10 bg-slate-900 text-white rounded-3xl shadow-xl relative overflow-hidden border border-slate-800">
            <!-- Background Glow Decor -->
            <div class="absolute -right-20 -top-20 w-80 h-80 bg-brand-500/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -left-20 -bottom-20 w-80 h-80 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-amber-400/20 border border-amber-400/40 rounded-full text-xs font-black uppercase tracking-widest text-amber-300 mb-4">
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                        Official Compliance Framework • Operations Policy
                    </div>
                    <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white uppercase">
                        Overcoming Compliance & Onboarding Friction
                    </h1>
                    <p class="text-slate-300 text-sm sm:text-base mt-3 max-w-3xl font-medium leading-relaxed">
                        Operational guardrails, streamlined driver verification standards, backend real-time communications, and performance incentive structures designed to maximize fleet availability while guaranteeing 100% passenger safety compliance.
                    </p>
                </div>
                
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 shrink-0 no-print">
                    <button onclick="window.print()" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 border border-slate-600 text-white font-bold text-xs rounded-xl transition-all flex items-center gap-2 shadow-sm cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 17h2a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h2m2 4h6a2 2 0 0 0 2-2v-4H7v4a2 2 0 0 0 2 2zm8-12V5a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v4h10z"/></svg>
                        <span>Print Document</span>
                    </button>
                    <a href="/legal" class="px-4 py-2.5 bg-amber-400 hover:bg-amber-500 text-slate-950 font-black text-xs rounded-xl shadow-md transition-all flex items-center gap-2">
                        <span>🛡️ Compliance & Trust</span>
                    </a>
                </div>
            </div>

            <!-- Document Metadata Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-8 pt-6 border-t border-slate-700/80 text-xs">
                <div>
                    <span class="text-slate-400 font-bold block mb-0.5">Policy Classification</span>
                    <strong class="text-white font-extrabold text-sm">Operational Guardrails & Compliance</strong>
                </div>
                <div>
                    <span class="text-slate-400 font-bold block mb-0.5">Target Audience</span>
                    <strong class="text-white font-extrabold text-sm">Customers, Drivers & Regulators</strong>
                </div>
                <div>
                    <span class="text-slate-400 font-bold block mb-0.5">Enforcement Scope</span>
                    <strong class="text-amber-400 font-extrabold text-sm">Global Fleet (USA, SA, Ghana)</strong>
                </div>
                <div>
                    <span class="text-slate-400 font-bold block mb-0.5">Review Authority</span>
                    <strong class="text-white font-extrabold text-sm">Legal, Risk & Fleet Operations</strong>
                </div>
            </div>
        </div>

        <!-- Main Body Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left Sticky Sidebar: Navigation -->
            <aside class="lg:col-span-4 space-y-6 no-print lg:sticky lg:top-24">
                
                <div class="bg-white dark:bg-[#121214] p-6 rounded-3xl border border-gray-200 dark:border-white/10 shadow-sm space-y-4">
                    <h3 class="text-xs font-black uppercase tracking-wider text-gray-400 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                        Document Contents
                    </h3>
                    <nav class="space-y-1 text-sm font-semibold">
                        <a href="#executive-summary" class="block px-3 py-2 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                            1. Executive Context & Friction Analysis
                        </a>
                        <a href="#document-concierge" class="block px-3 py-2 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                            2. The "Document Concierge" System
                        </a>
                        <a href="#communication-push" class="block px-3 py-2 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                            3. Real-Time Backend Communication Push
                        </a>
                        <a href="#segmented-incentives" class="block px-3 py-2 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                            4. Segmented Driver Incentive Program
                        </a>
                        <a href="#rider-safety" class="block px-3 py-2 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                            5. Customer & Rider Safety Guarantees
                        </a>
                        <a href="#contact-governance" class="block px-3 py-2 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                            6. Regulatory & Compliance Contacts
                        </a>
                    </nav>
                </div>

                <!-- Quick Driver Action Card -->
                <div class="bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-transparent border border-amber-500/30 p-6 rounded-3xl space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-400 text-slate-950 flex items-center justify-center font-black text-lg shadow-sm">
                        🚗
                    </div>
                    <h4 class="font-extrabold text-gray-900 dark:text-white text-base">Ready to Drive with Us?</h4>
                    <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                        Experience our fast Document Concierge onboarding. Start with your phone number and unlock flexible full-time or peak-hour bonuses.
                    </p>
                    <a href="/onboarding" class="inline-flex items-center justify-center w-full py-3 px-4 bg-brand-500 hover:bg-brand-600 text-slate-950 font-black text-xs rounded-xl shadow-md transition-all">
                        Start Driver Registration →
                    </a>
                </div>

            </aside>

            <!-- Right Column: Structured Policy Articles -->
            <div class="lg:col-span-8 space-y-8">
                
                <!-- Section 1: Executive Context & Challenge -->
                <section id="executive-summary" class="bg-white dark:bg-[#121214] p-6 sm:p-8 rounded-3xl border border-gray-200 dark:border-white/10 shadow-sm space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-brand-500/15 text-brand-600 dark:text-brand-400">Section 1.0</span>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Problem Statement & Strategy</span>
                    </div>
                    
                    <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                        Executive Context & Friction Analysis
                    </h2>

                    <!-- Prominent Quote of the Core Prompt -->
                    <div class="p-5 bg-amber-50/80 dark:bg-amber-950/20 border-l-4 border-amber-500 rounded-2xl text-gray-900 dark:text-gray-100 space-y-2">
                        <p class="text-sm font-semibold leading-relaxed italic">
                            "Since background, license, and insurance checks create a drop-off point in the sign-up funnel, we will implement these two operational guardrails:"
                        </p>
                        <div class="inline-block px-3 py-1 bg-amber-400 text-slate-950 rounded-lg text-xs font-black uppercase tracking-wider shadow-xs">
                            LETS ADOPT THIS SYSTEM FOR QUICK SIGN UP
                        </div>
                    </div>

                    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                        In digital ridesharing and transportation networks, candidate drop-off during onboarding typically occurs when prospective drivers are confronted with cumbersome upfront document upload requirements before understanding the value proposition of the platform.
                    </p>
                    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                        RideMyCars solves this critical operational bottleneck through a <strong class="text-gray-900 dark:text-white">staged, friction-free registration workflow</strong> combined with <strong class="text-gray-900 dark:text-white">strict compliance gatekeeping</strong>. This guarantees that driver recruitment conversion remains high while <strong>zero unvetted drivers can ever access or accept a ride</strong>.
                    </p>
                </section>

                <!-- Section 2: Guardrail 1 - The Document Concierge System -->
                <section id="document-concierge" class="bg-white dark:bg-[#121214] p-6 sm:p-8 rounded-3xl border border-gray-200 dark:border-white/10 shadow-sm space-y-6">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-blue-500/15 text-blue-600 dark:text-blue-400">Operational Guardrail 1</span>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Onboarding Velocity</span>
                    </div>

                    <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                        <span>The "Document Concierge" System</span>
                    </h2>

                    <!-- Highlight Box with Document Text -->
                    <div class="p-5 bg-blue-50/70 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-900/40 rounded-2xl space-y-2">
                        <h4 class="text-xs font-black uppercase tracking-wider text-blue-800 dark:text-blue-300">
                            Core Operating Principle:
                        </h4>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 leading-relaxed">
                            "Allow drivers to start the profile creation process using just their phone number. Only prompt for the license and insurance images right before they can see available rides, keeping the initial signup drop-off low."
                        </p>
                    </div>

                    <!-- Step by Step Workflow Cards -->
                    <div class="space-y-3">
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            How The Document Concierge Workflow Functions:
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                            
                            <!-- Step 1 -->
                            <div class="p-4 bg-gray-50 dark:bg-[#1a1a1c] border border-gray-100 dark:border-white/5 rounded-2xl space-y-2">
                                <div class="w-8 h-8 rounded-xl bg-amber-400 text-slate-950 font-black text-sm flex items-center justify-center">
                                    1
                                </div>
                                <h4 class="font-extrabold text-sm text-gray-900 dark:text-white">Instant Mobile Initiation</h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                                    Prospective drivers sign up with just their verified mobile phone number (via instant SMS OTP) and basic contact profile in under 60 seconds.
                                </p>
                            </div>

                            <!-- Step 2 -->
                            <div class="p-4 bg-gray-50 dark:bg-[#1a1a1c] border border-gray-100 dark:border-white/5 rounded-2xl space-y-2">
                                <div class="w-8 h-8 rounded-xl bg-amber-400 text-slate-950 font-black text-sm flex items-center justify-center">
                                    2
                                </div>
                                <h4 class="font-extrabold text-sm text-gray-900 dark:text-white">Driver Portal Orientation</h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                                    Drivers explore the driver dashboard, review high-earnings zones, and explore vehicle specifications without being immediately blocked.
                                </p>
                            </div>

                            <!-- Step 3 -->
                            <div class="p-4 bg-blue-50/50 dark:bg-blue-950/20 border border-blue-200/60 dark:border-blue-800/30 rounded-2xl space-y-2">
                                <div class="w-8 h-8 rounded-xl bg-blue-600 text-white font-black text-sm flex items-center justify-center">
                                    3
                                </div>
                                <h4 class="font-extrabold text-sm text-gray-900 dark:text-white">Concierge Document Upload</h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                                    Right before rides become visible, our smart Document Concierge prompts for valid driver's license, vehicle commercial insurance, and roadworthiness inspection.
                                </p>
                            </div>

                            <!-- Step 4 -->
                            <div class="p-4 bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-200/60 dark:border-emerald-800/30 rounded-2xl space-y-2">
                                <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white font-black text-sm flex items-center justify-center">
                                    4
                                </div>
                                <h4 class="font-extrabold text-sm text-gray-900 dark:text-white">Audited Dispatch Unlock</h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                                    Our safety operations team verifies credentials. Only once approved is dispatch unlocked, completely securing customer safety.
                                </p>
                            </div>

                        </div>
                    </div>
                </section>

                <!-- Section 3: Backend Communication Push -->
                <section id="communication-push" class="bg-white dark:bg-[#121214] p-6 sm:p-8 rounded-3xl border border-gray-200 dark:border-white/10 shadow-sm space-y-6">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-purple-500/15 text-purple-600 dark:text-purple-400">Operations Technology</span>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Fleet Communication</span>
                    </div>

                    <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                        Real-Time Backend Communication Push
                    </h2>

                    <!-- Document Prompt Highlight -->
                    <div class="p-5 bg-purple-50/70 dark:bg-purple-950/20 border-l-4 border-purple-500 rounded-2xl space-y-2">
                        <div class="flex items-center gap-2 text-purple-900 dark:text-purple-300 text-xs font-black uppercase tracking-wider">
                            <span>📡 Fleet Broadcast Directives</span>
                        </div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wide leading-relaxed">
                            "THIS PUSH BUTTON MUST BE DONE AT BACK END TO SEND INSTANT MESSAGE TO ALL DRIVERS. THAT SHOULD BE OUR COMMUNICATION PUSH."
                        </p>
                    </div>

                    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                        To maintain fleet responsiveness, rapid dispatch, and urgent compliance enforcement, the RideMyCars administrative backend features an instant <strong class="text-gray-900 dark:text-white">Operational Push Broadcast System</strong>. 
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                        <div class="p-4 bg-gray-50 dark:bg-[#1a1a1c] rounded-2xl border border-gray-100 dark:border-white/5 space-y-1">
                            <span class="text-xl block">⚡</span>
                            <h4 class="font-extrabold text-xs text-gray-900 dark:text-white">Instant Dispatch</h4>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">Pushes actionable alerts directly to all driver handsets in sub-second latency.</p>
                        </div>
                        <div class="p-4 bg-gray-50 dark:bg-[#1a1a1c] rounded-2xl border border-gray-100 dark:border-white/5 space-y-1">
                            <span class="text-xl block">🚨</span>
                            <h4 class="font-extrabold text-xs text-gray-900 dark:text-white">Safety & Protocol Alerts</h4>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">Broadcasts weather alerts, municipal road closures, and policy updates immediately.</p>
                        </div>
                        <div class="p-4 bg-gray-50 dark:bg-[#1a1a1c] rounded-2xl border border-gray-100 dark:border-white/5 space-y-1">
                            <span class="text-xl block">📈</span>
                            <h4 class="font-extrabold text-xs text-gray-900 dark:text-white">Surge Opportunities</h4>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">Notifies available drivers of high-demand airport arrivals and stadium events.</p>
                        </div>
                    </div>
                </section>

                <!-- Section 4: Guardrail 2 - Segmented Driver Incentive Program -->
                <section id="segmented-incentives" class="bg-white dark:bg-[#121214] p-6 sm:p-8 rounded-3xl border border-gray-200 dark:border-white/10 shadow-sm space-y-6">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">Operational Guardrail 2</span>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Driver Economics</span>
                    </div>

                    <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                        The Segmented Incentive Program
                    </h2>

                    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                        To guarantee reliable coverage for both everyday travelers and intense weekend peak surges, RideMyCars implements a <strong class="text-gray-900 dark:text-white">two-tiered segmented reward model</strong> tailored to driver working patterns:
                    </p>

                    <!-- Segmented Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        <!-- Tier A: Full-Time Focus -->
                        <div class="p-6 bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-transparent border border-amber-500/30 rounded-3xl space-y-3 relative overflow-hidden">
                            <div class="flex items-center justify-between">
                                <span class="px-3 py-1 bg-amber-400 text-slate-950 rounded-full text-[11px] font-black uppercase tracking-wider">
                                    Full-Time Focus
                                </span>
                                <span class="text-xl">🏆</span>
                            </div>
                            <h3 class="text-lg font-black text-gray-900 dark:text-white">
                                Reward Consistent Volume
                            </h3>
                            <div class="p-3 bg-white/80 dark:bg-black/40 rounded-xl border border-amber-400/30 text-xs font-mono font-bold text-amber-900 dark:text-amber-300">
                                🎯 "Complete 40 rides a week for an extra bonus"
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                                Tailored for professional career chauffeurs and daily drivers. By incentivizing weekly trip thresholds, RideMyCars ensures constant city-wide vehicle density across business hours.
                            </p>
                            <ul class="text-xs space-y-1.5 text-gray-700 dark:text-gray-300 font-medium pt-2">
                                <li class="flex items-center gap-2">✓ Priority airport dispatch privileges</li>
                                <li class="flex items-center gap-2">✓ Weekly tiered performance payouts</li>
                                <li class="flex items-center gap-2">✓ Preferred vehicle maintenance subsidies</li>
                            </ul>
                        </div>

                        <!-- Tier B: Part-Time Focus -->
                        <div class="p-6 bg-gradient-to-br from-indigo-500/10 via-indigo-500/5 to-transparent border border-indigo-500/30 rounded-3xl space-y-3 relative overflow-hidden">
                            <div class="flex items-center justify-between">
                                <span class="px-3 py-1 bg-indigo-600 text-white rounded-full text-[11px] font-black uppercase tracking-wider">
                                    Part-Time Focus
                                </span>
                                <span class="text-xl">⚡</span>
                            </div>
                            <h3 class="text-lg font-black text-gray-900 dark:text-white">
                                Reward Peak-Hour Availability
                            </h3>
                            <div class="p-3 bg-white/80 dark:bg-black/40 rounded-xl border border-indigo-400/30 text-xs font-mono font-bold text-indigo-900 dark:text-indigo-300">
                                🚀 "Complete 5 rides during Friday/Saturday night rushes for an extra bonus"
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                                Tailored for flexible drivers who prefer weekend and evening shifts. Targeted micro-bonuses mobilize supplemental fleets precisely when customer ride demand surges.
                            </p>
                            <ul class="text-xs space-y-1.5 text-gray-700 dark:text-gray-300 font-medium pt-2">
                                <li class="flex items-center gap-2">✓ Rush-hour surge multipliers</li>
                                <li class="flex items-center gap-2">✓ Weekend night performance bonuses</li>
                                <li class="flex items-center gap-2">✓ Flexible shifts with zero minimum weekly quotas</li>
                            </ul>
                        </div>

                    </div>
                </section>

                <!-- Section 5: Rider Safety & Peace of Mind -->
                <section id="rider-safety" class="bg-white dark:bg-[#121214] p-6 sm:p-8 rounded-3xl border border-gray-200 dark:border-white/10 shadow-sm space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">Customer Assurance</span>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Passenger Peace of Mind</span>
                    </div>

                    <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                        What This Means for Our Customers & Riders
                    </h2>

                    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                        When you book a ride with RideMyCars, you can be 100% confident in the integrity of the vehicle and driver arriving at your door:
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                        <div class="p-4 bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-200/70 dark:border-emerald-800/40 rounded-2xl flex items-start gap-3">
                            <span class="text-emerald-600 dark:text-emerald-400 text-lg font-black shrink-0">🛡️</span>
                            <div>
                                <h4 class="font-extrabold text-xs text-gray-900 dark:text-white mb-1">Zero Unverified Dispatches</h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                                    No driver can accept a passenger trip without verified commercial insurance, background screening clearance, and an audited operator license.
                                </p>
                            </div>
                        </div>

                        <div class="p-4 bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-200/70 dark:border-emerald-800/40 rounded-2xl flex items-start gap-3">
                            <span class="text-emerald-600 dark:text-emerald-400 text-lg font-black shrink-0">⏱️</span>
                            <div>
                                <h4 class="font-extrabold text-xs text-gray-900 dark:text-white mb-1">Faster Pickup Times</h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                                    Segmented incentives ensure drivers are available when you need them most, eliminating endless search times during Friday and Saturday rush hours.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Section 6: Governance & Legal Department Contacts -->
                <section id="contact-governance" class="bg-white dark:bg-[#121214] p-6 sm:p-8 rounded-3xl border border-gray-200 dark:border-white/10 shadow-sm space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-gray-200 dark:bg-white/10 text-gray-700 dark:text-gray-300">Governance</span>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Compliance Inquiries</span>
                    </div>

                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        Regulatory & Compliance Department
                    </h2>
                    
                    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                        For questions regarding our driver verification standards, municipal licensing partnerships, or fleet safety audits, contact our Legal, Risk & Compliance Department:
                    </p>

                    <div class="p-5 bg-gray-50 dark:bg-[#1a1a1c] rounded-2xl text-xs font-mono space-y-2 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-white/10">
                        <p><strong>Parent Corporate Entity:</strong> New Development Finance Group</p>
                        <p><strong>Division:</strong> Legal, Risk & Fleet Compliance Department</p>
                        <div class="border-t border-gray-200 dark:border-white/10 pt-2.5 mt-2.5 space-y-1">
                            <p><strong>🇺🇸 USA Global HQ:</strong> 4301 Saddle River Drive, Bowie, MD 20720, United States</p>
                            <p><strong>🇿🇦 South Africa Hub:</strong> 11 Corona Road, Sandhurst, Sandton, Gauteng 2196, South Africa</p>
                            <p><strong>🇬🇭 Ghana Hub:</strong> No 1 Airport Square, 8th Floor, Airport City, Accra, Ghana</p>
                        </div>
                        <div class="border-t border-gray-200 dark:border-white/10 pt-2.5 mt-2.5 space-y-1">
                            <p><strong>Compliance Hotline:</strong> <a href="tel:+18552033177" class="text-amber-600 dark:text-amber-400 font-bold hover:underline">+1 855 203 3177</a></p>
                            <p><strong>Compliance Email:</strong> <a href="mailto:legal@ridemycars.com" class="text-amber-600 dark:text-amber-400 font-bold hover:underline">legal@ridemycars.com</a></p>
                        </div>
                    </div>
                </section>

            </div>

        </div>

    </main>
</x-layout>
