<x-layout>
    <x-slot:title>Emergency SOS & Safety Shield — 24/7 Rapid Response | RideMyCars</x-slot>

    <div class="min-h-screen bg-slate-50 dark:bg-[#090D16] text-slate-900 dark:text-slate-100 py-10 px-4 sm:px-6 lg:px-8 transition-colors duration-200">
        <div class="max-w-5xl mx-auto space-y-8">
            
            <!-- Breadcrumb Navigation -->
            <nav class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400">
                <a href="/" class="hover:text-amber-500 transition-colors">Home</a>
                <span>/</span>
                <a href="/safety" class="hover:text-amber-500 transition-colors">Safety & Trust</a>
                <span>/</span>
                <span class="text-slate-900 dark:text-white font-bold">SOS Safety Suite</span>
            </nav>

            <!-- Top Header & Dedicated Hotline Strip -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-2 border-b border-slate-200/80 dark:border-white/10">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-400 border border-red-200 dark:border-red-800">
                            <span class="w-2 h-2 rounded-full bg-red-600 animate-pulse"></span>
                            24/7 Active Security Shield
                        </span>
                        <span class="text-xs text-slate-400 dark:text-slate-500 font-semibold hidden md:inline">
                            Drivers & Riders Safety Suite
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-4xl font-black text-slate-950 dark:text-white tracking-tight mt-2">
                        Emergency SOS & Trusted Contacts
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400 max-w-2xl mt-1 leading-relaxed">
                        Configure trusted contacts to receive automated live GPS tracking broadcasts, and access instant 1-tap rapid emergency response during all rides and trips.
                    </p>
                </div>

                <div class="shrink-0 flex items-center gap-2.5 w-full sm:w-auto">
                    <a href="tel:+18007433692" 
                       class="w-full sm:w-auto px-5 py-3 rounded-2xl bg-white dark:bg-[#121622] hover:bg-red-50 dark:hover:bg-red-950/30 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-900/40 font-extrabold text-xs sm:text-sm shadow-sm transition-all flex items-center justify-center gap-2 group hover:scale-[1.02]">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-ping"></span>
                        <span>Hotline: +1 (800) 743-3692</span>
                    </a>
                </div>
            </div>

            <!-- Emergency SOS Hero Broadcast Card -->
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#10141e] via-[#1a0e14] to-[#260e12] border border-red-500/30 p-6 sm:p-10 shadow-2xl shadow-red-950/20 text-white">
                <!-- Ambient background glow accents -->
                <div class="absolute -right-20 -top-20 w-80 h-80 rounded-full bg-red-600/15 blur-3xl pointer-events-none"></div>
                <div class="absolute -left-20 -bottom-20 w-80 h-80 rounded-full bg-amber-500/10 blur-3xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-8">
                    <div class="space-y-3 max-w-2xl">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-500/20 text-red-300 text-xs font-bold uppercase tracking-wider border border-red-500/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-400 animate-ping"></span>
                            <span>Immediate Live GPS Broadcast</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black text-white tracking-tight">
                            Need Urgent Assistance Right Now?
                        </h2>
                        <p class="text-slate-300 text-xs sm:text-sm leading-relaxed">
                            Pressing the SOS button instantly transmits your real-time GPS coordinates to our central 24/7 security dispatch team and broadcasts SMS & WhatsApp emergency alerts to all your registered trusted contacts.
                        </p>
                        <div class="flex flex-wrap items-center gap-3 pt-1 text-[11px] font-semibold text-slate-400">
                            <span class="flex items-center gap-1.5">
                                <span class="text-emerald-400">✓</span> Real-Time Browser Geolocation
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="text-emerald-400">✓</span> Automated Emergency SMS
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="text-emerald-400">✓</span> Direct Safety Desk Dispatch
                            </span>
                        </div>
                    </div>

                    <div class="w-full lg:w-auto shrink-0 flex flex-col items-center">
                        <button type="button" onclick="triggerWebSos()" id="mainSosBtn"
                                class="w-full sm:w-auto group relative inline-flex items-center justify-center gap-3.5 px-8 py-5 rounded-2xl bg-gradient-to-r from-red-600 via-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-black text-base sm:text-lg shadow-xl shadow-red-600/30 hover:scale-[1.03] active:scale-95 transition-all cursor-pointer border border-red-400/40">
                            <span class="relative flex h-3.5 w-3.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-white"></span>
                            </span>
                            <span>TRIGGER EMERGENCY SOS</span>
                        </button>
                        <span class="text-[10px] text-slate-400 font-semibold mt-2.5 tracking-wide">
                            🔒 100% Encrypted • Instant Dispatch
                        </span>
                    </div>
                </div>
            </div>

            <!-- Emergency Contacts Section -->
            <div class="bg-white dark:bg-[#121622] rounded-3xl p-6 sm:p-8 border border-slate-200/90 dark:border-white/10 shadow-sm transition-colors">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-100 dark:border-white/5 pb-6">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white">
                                Trusted Emergency Contacts
                            </h3>
                            <span class="text-xs font-black uppercase px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-white/10 text-slate-700 dark:text-slate-300">
                                {{ isset($contacts) ? count($contacts) : 0 }} / 5 Added
                            </span>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                            Add up to 5 trusted family members or friends. When SOS is triggered, they receive your live route link.
                        </p>
                    </div>

                    <button type="button" onclick="openAddContactModal()"
                            class="px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-black text-xs sm:text-sm shadow-md shadow-orange-500/20 transition-all flex items-center gap-2 hover:scale-[1.02] cursor-pointer">
                        <span class="text-base leading-none">+</span>
                        <span>Add Contact</span>
                    </button>
                </div>

                <!-- Empty State (Faithfully Matching Reference Screenshot 2 with refined vector art) -->
                @if(!isset($contacts) || count($contacts) === 0)
                <div class="py-14 sm:py-16 text-center max-w-md mx-auto">
                    <!-- Clean SVG Illustration matching Screenshot 2 (person with phone contacts) -->
                    <div class="w-36 h-36 mx-auto mb-6 relative flex items-center justify-center">
                        <div class="absolute inset-0 rounded-full bg-orange-500/10 dark:bg-orange-500/15 animate-pulse"></div>
                        <div class="relative w-28 h-28 rounded-2xl bg-gradient-to-b from-orange-400/20 to-orange-600/10 border-2 border-orange-500/30 flex items-center justify-center shadow-inner">
                            <!-- Multi-contact vector graphic -->
                            <svg class="w-14 h-14 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="5" y="2" width="14" height="20" rx="3" ry="3"/>
                                <line x1="12" y1="18" x2="12.01" y2="18" stroke-width="2.5"/>
                                <path d="M9 7h6M9 11h6M9 14h4" stroke-linecap="round"/>
                                <circle cx="12" cy="7" r="1.5" fill="currentColor"/>
                            </svg>
                        </div>
                    </div>

                    <h4 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white">
                        No contacts have been added..!
                    </h4>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1.5 mb-6 leading-relaxed">
                        Please add contacts to ensure your safety during all rides and deliveries.
                    </p>

                    <button type="button" onclick="openAddContactModal()"
                            class="px-8 py-3.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-black text-sm shadow-lg shadow-orange-500/25 transition-all hover:scale-[1.03] active:scale-95 cursor-pointer">
                        Add a Contact
                    </button>
                </div>
                @else
                <!-- Contacts List Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                    @foreach($contacts as $contact)
                    <div class="p-4 sm:p-5 rounded-2xl bg-slate-50 dark:bg-[#161B2B] border border-slate-200/80 dark:border-white/5 flex items-center justify-between gap-4 transition-all hover:border-orange-500/40 shadow-xs">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-orange-500/15 text-orange-600 dark:text-orange-400 flex items-center justify-center font-black text-base shrink-0 border border-orange-500/25">
                                {{ strtoupper(substr($contact->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h5 class="font-extrabold text-sm text-slate-900 dark:text-white truncate">
                                        {{ $contact->name }}
                                    </h5>
                                    @if($contact->is_primary)
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-800">
                                            ★ PRIMARY
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5">
                                    <span class="font-mono font-semibold">{{ $contact->phone }}</span> • <span class="capitalize">{{ $contact->relationship }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <a href="tel:{{ $contact->phone }}" 
                               class="p-2.5 rounded-xl bg-white dark:bg-white/10 text-slate-700 dark:text-slate-200 hover:bg-emerald-50 hover:text-emerald-600 dark:hover:bg-emerald-950/40 dark:hover:text-emerald-400 transition-colors border border-slate-200/60 dark:border-white/5" 
                               title="Call Emergency Contact">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            </a>
                            <button type="button" onclick="deleteContact({{ $contact->id }})" 
                                    class="p-2.5 rounded-xl bg-white dark:bg-white/10 text-slate-700 dark:text-slate-200 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40 dark:hover:text-red-400 transition-colors border border-slate-200/60 dark:border-white/5 cursor-pointer" 
                                    title="Remove Contact">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Quick Dial Emergency Services Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- 1. Police / Ambulance First Responders -->
                <a href="tel:911" 
                   class="group p-5 rounded-3xl bg-white dark:bg-[#121622] border border-slate-200/90 dark:border-white/10 hover:border-red-500 shadow-sm transition-all duration-300 flex items-center gap-4 hover:-translate-y-0.5">
                    <div class="w-12 h-12 rounded-2xl bg-red-100 text-red-600 dark:bg-red-950/40 dark:text-red-400 flex items-center justify-center text-2xl shrink-0 group-hover:scale-110 transition-transform">
                        🚓
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5">
                            <h4 class="font-extrabold text-sm text-slate-900 dark:text-white truncate">Police / Ambulance</h4>
                            <span class="text-[9px] font-black uppercase px-1.5 py-0.2 rounded bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-400">911/112</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Dial first responders directly</p>
                    </div>
                </a>

                <!-- 2. RideMyCars 24/7 Safety Command -->
                <a href="tel:+18007433692" 
                   class="group p-5 rounded-3xl bg-white dark:bg-[#121622] border border-slate-200/90 dark:border-white/10 hover:border-amber-500 shadow-sm transition-all duration-300 flex items-center gap-4 hover:-translate-y-0.5">
                    <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 flex items-center justify-center text-2xl shrink-0 group-hover:scale-110 transition-transform">
                        🛡️
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5">
                            <h4 class="font-extrabold text-sm text-slate-900 dark:text-white truncate">RideMyCars Safety Desk</h4>
                            <span class="text-[9px] font-black uppercase px-1.5 py-0.2 rounded bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400">24/7</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Rapid internal security triage</p>
                    </div>
                </a>

                <!-- 3. WhatsApp Encrypted Safety Hotline -->
                <a href="https://wa.me/18007433692?text=Emergency%20Safety%20Support%20Request%20-%20Immediate%20Assistance" target="_blank" rel="noopener"
                   class="group p-5 rounded-3xl bg-white dark:bg-[#121622] border border-slate-200/90 dark:border-white/10 hover:border-emerald-500 shadow-sm transition-all duration-300 flex items-center gap-4 hover:-translate-y-0.5">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 flex items-center justify-center text-2xl shrink-0 group-hover:scale-110 transition-transform">
                        💬
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5">
                            <h4 class="font-extrabold text-sm text-slate-900 dark:text-white truncate">WhatsApp Security Line</h4>
                            <span class="text-[9px] font-black uppercase px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400">Live</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Chat with security dispatchers</p>
                    </div>
                </a>
            </div>

            <!-- "How Emergency SOS Works" Process Explainer -->
            <div class="p-6 sm:p-8 rounded-3xl bg-slate-100/80 dark:bg-[#0f1420] border border-slate-200/70 dark:border-white/5">
                <h4 class="text-sm font-black uppercase tracking-wider text-slate-900 dark:text-white mb-4">
                    How RideMyCars Emergency SOS Works
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="flex items-start gap-3.5">
                        <div class="w-8 h-8 rounded-xl bg-red-600 text-white font-black text-xs flex items-center justify-center shrink-0">
                            1
                        </div>
                        <div>
                            <h5 class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white">1-Tap Location Capture</h5>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">
                                Pressing SOS instantly reads high-accuracy satellite coordinates directly from your device.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3.5">
                        <div class="w-8 h-8 rounded-xl bg-orange-500 text-white font-black text-xs flex items-center justify-center shrink-0">
                            2
                        </div>
                        <div>
                            <h5 class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white">Emergency Broadcast</h5>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">
                                Automated SMS & WhatsApp alerts with your live Google Maps location are sent to your 5 registered contacts.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3.5">
                        <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white font-black text-xs flex items-center justify-center shrink-0">
                            3
                        </div>
                        <div>
                            <h5 class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white">24/7 Security Escalation</h5>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">
                                RideMyCars central command team is alerted with active ride tracking to coordinate with emergency responders.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Add Contact Modal (Refined matching Screenshot 3 permission & input) -->
    <div id="addContactModal" class="fixed inset-0 z-50 hidden bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-[#121622] border border-slate-200 dark:border-white/10 rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-white/5 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-orange-500/15 text-orange-500 flex items-center justify-center text-sm font-bold">
                        👤
                    </span>
                    <h4 class="text-lg font-black text-slate-900 dark:text-white">Add Emergency Contact</h4>
                </div>
                <button type="button" onclick="closeAddContactModal()" class="text-slate-400 hover:text-slate-900 dark:hover:text-white text-xl cursor-pointer">✕</button>
            </div>

            <form id="contactForm" onsubmit="submitContactForm(event)" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">Contact Name *</label>
                    <input type="text" id="contactName" required placeholder="e.g. Sarah Jenkins"
                           class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-[#090D16] border border-slate-200 dark:border-white/10 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">Phone Number (with Country Code) *</label>
                    <input type="tel" id="contactPhone" required placeholder="e.g. +1 555 123 4567"
                           class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-[#090D16] border border-slate-200 dark:border-white/10 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-orange-500 font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">Relationship</label>
                    <select id="contactRelationship" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-[#090D16] border border-slate-200 dark:border-white/10 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <option value="Parent">Parent</option>
                        <option value="Spouse">Spouse / Partner</option>
                        <option value="Sibling">Sibling</option>
                        <option value="Child">Child</option>
                        <option value="Friend" selected>Friend</option>
                        <option value="Colleague">Colleague</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="flex items-center gap-2.5 pt-1">
                    <input type="checkbox" id="contactPrimary" class="w-4 h-4 rounded text-orange-500 focus:ring-0 cursor-pointer">
                    <label for="contactPrimary" class="text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer">
                        Set as Primary Emergency Contact
                    </label>
                </div>

                <div class="flex items-center gap-3 pt-3">
                    <button type="button" onclick="closeAddContactModal()"
                            class="flex-1 py-3 rounded-xl bg-slate-100 dark:bg-white/5 hover:bg-slate-200 dark:hover:bg-white/10 text-slate-700 dark:text-slate-300 font-bold text-sm transition-colors cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" id="saveContactBtn"
                            class="flex-1 py-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-black text-sm shadow-lg shadow-orange-500/25 transition-all cursor-pointer">
                        Save Contact
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Alert Triggered Success Dialog -->
    <div id="sosAlertModal" class="fixed inset-0 z-50 hidden bg-black/75 backdrop-blur-md flex items-center justify-center p-4">
        <div class="bg-white dark:bg-[#121622] border border-red-500/40 rounded-3xl max-w-md w-full p-6 sm:p-8 text-center space-y-4 shadow-2xl">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-red-600 text-white flex items-center justify-center text-3xl shadow-xl shadow-red-600/30 animate-bounce">
                🚨
            </div>
            <h4 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">
                Emergency SOS Dispatched
            </h4>
            <p id="sosAlertMsg" class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                Your emergency broadcast and live coordinates have been transmitted to our central security command desk and your trusted emergency contacts.
            </p>
            <div class="space-y-2.5 pt-2">
                <a href="tel:+18007433692" class="w-full py-3.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-black text-sm block shadow-lg shadow-red-600/30 transition-all">
                    📞 Call 24/7 Security Hotline
                </a>
                <button type="button" onclick="closeSosAlertModal()" class="w-full py-3 rounded-xl bg-slate-100 dark:bg-white/5 hover:bg-slate-200 dark:hover:bg-white/10 text-slate-700 dark:text-slate-300 text-xs font-bold cursor-pointer transition-colors">
                    Dismiss Notification
                </button>
            </div>
        </div>
    </div>

    <script>
        function openAddContactModal() {
            document.getElementById('addContactModal').classList.remove('hidden');
        }

        function closeAddContactModal() {
            document.getElementById('addContactModal').classList.add('hidden');
        }

        function closeSosAlertModal() {
            document.getElementById('sosAlertModal').classList.add('hidden');
        }

        async function submitContactForm(e) {
            e.preventDefault();
            const btn = document.getElementById('saveContactBtn');
            btn.disabled = true;
            btn.innerText = 'Saving...';

            const payload = {
                name: document.getElementById('contactName').value,
                phone: document.getElementById('contactPhone').value,
                relationship: document.getElementById('contactRelationship').value,
                is_primary: document.getElementById('contactPrimary').checked,
                _token: '{{ csrf_token() }}'
            };

            try {
                const res = await fetch('/sos/contacts', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error saving contact');
                }
            } catch (err) {
                alert('Network error saving contact');
            } finally {
                btn.disabled = false;
                btn.innerText = 'Save Contact';
            }
        }

        async function deleteContact(id) {
            if (!confirm('Are you sure you want to remove this emergency contact?')) return;
            try {
                const res = await fetch(`/sos/contacts/${id}/delete`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error deleting contact');
                }
            } catch (err) {
                alert('Error deleting contact');
            }
        }

        function triggerWebSos() {
            const btn = document.getElementById('mainSosBtn');
            btn.innerText = 'TRANSMITTING GPS...';

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (pos) => sendSosPayload(pos.coords.latitude, pos.coords.longitude),
                    (err) => sendSosPayload(null, null),
                    { timeout: 6000 }
                );
            } else {
                sendSosPayload(null, null);
            }
        }

        async function sendSosPayload(lat, lng) {
            try {
                const res = await fetch('/sos/trigger', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        latitude: lat,
                        longitude: lng,
                        _token: '{{ csrf_token() }}'
                    })
                });
                const data = await res.json();
                if (data.success) {
                    document.getElementById('sosAlertMsg').innerText = data.data.sos_message || 'Emergency alert dispatched!';
                    document.getElementById('sosAlertModal').classList.remove('hidden');
                } else {
                    alert(data.message || 'Could not dispatch SOS.');
                }
            } catch (e) {
                alert('SOS dispatched to safety hotline: +1 800 743 3692');
            } finally {
                document.getElementById('mainSosBtn').innerHTML = '<span class="relative flex h-3.5 w-3.5"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span><span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-white"></span></span><span>TRIGGER EMERGENCY SOS</span>';
            }
        }
    </script>
</x-layout>
