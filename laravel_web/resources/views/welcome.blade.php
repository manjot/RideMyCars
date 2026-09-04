<x-layout>
    <x-slot:title>RideMyCars — One App. Four Ways to Move.</x-slot>

    <main class="flex-1">
        
        <!-- Hero Section -->
        <section class="relative pt-10 pb-16 lg:pt-16 lg:pb-24 overflow-hidden bg-[#fafafa] dark:bg-[#0a0a0a]">
            <!-- Colorful Ambient Background Light Orbs -->
            <div class="absolute top-6 left-1/4 w-[450px] h-[450px] glow-ambient-amber blur-3xl pointer-events-none -z-10 opacity-75"></div>
            <div class="absolute top-12 right-1/4 w-[450px] h-[450px] glow-ambient-blue blur-3xl pointer-events-none -z-10 opacity-65"></div>
            <div class="absolute bottom-8 left-1/3 w-[550px] h-[300px] glow-ambient-purple blur-3xl pointer-events-none -z-10 opacity-55"></div>
            <div class="absolute inset-0 bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] dark:bg-[radial-gradient(#262626_1px,transparent_1px)] [background-size:24px_24px] opacity-40 -z-10"></div>
            
            <div class="relative max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 pt-2">
                <!-- Header -->
                <div class="text-center max-w-4xl mx-auto mb-14">
                    <!-- Pill Badge -->
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-amber-100 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-700/60 text-amber-800 dark:text-amber-300 font-extrabold text-xs uppercase tracking-widest mb-5 shadow-sm">
                        <span class="flex h-2 w-2 rounded-full bg-amber-500 animate-ping"></span>
                        <span>Unified Mobility & Logistics Super-App</span>
                    </div>

                    <h1 class="text-5xl md:text-7xl font-black text-gray-950 dark:text-white tracking-tight leading-[1.08] mb-5">
                        One App. <span class="text-[#e40e1b] font-black">Four Ways</span> to Move.
                    </h1>
                    <p class="text-base md:text-xl text-gray-700 dark:text-gray-200 leading-relaxed max-w-3xl mx-auto font-medium">
                        Time is the ultimate luxury. How you spend it—and how you move through it—should be entirely under your control. Welcome to Ride My Cars, built for the discerning professional.
                    </p>
                </div>

                <!-- 4 Column Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8 mb-14">
                    
                    <!-- Ride Card (Warm Amber & Orange) -->
                    <div class="relative bg-gradient-to-b from-amber-50 via-white to-white dark:from-amber-950/30 dark:via-[#141414] dark:to-[#141414] rounded-[2rem] shadow-[0_10px_35px_rgba(249,115,22,0.10)] overflow-hidden border-2 border-amber-300 dark:border-amber-500/30 flex flex-col pt-8 pb-7 px-6 text-center group transition-all hover:-translate-y-2 hover:border-amber-500 hover:shadow-2xl hover:shadow-amber-500/25 duration-300">
                        <!-- Top Accent Line -->
                        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-amber-400 via-orange-500 to-amber-500"></div>
                        <div class="absolute top-0 left-0 w-full h-32 bg-gradient-to-b from-amber-200/50 to-transparent dark:from-amber-500/10 pointer-events-none"></div>
                        
                        <div class="relative z-10 flex-1 flex flex-col items-center">
                            <!-- Solid Visible Icon -->
                            <div class="w-14 h-14 rounded-2xl bg-orange-500 text-white flex items-center justify-center mb-3.5 shadow-lg shadow-orange-500/40 transition-transform group-hover:scale-110">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/>
                                    <circle cx="7" cy="17" r="2"/>
                                    <path d="M9 17h6"/>
                                    <circle cx="17" cy="17" r="2"/>
                                </svg>
                            </div>

                            <!-- Badge -->
                            <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-700 mb-2">⚡ Instant On-Demand</span>
                            
                            <h3 class="text-2xl font-black mb-1.5 text-gray-900 dark:text-white group-hover:text-orange-500 transition-colors">Ride</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-5 max-w-[250px] text-xs font-medium leading-relaxed">On-demand executive transport with vetted city drivers.</p>
                            
                            <!-- Vehicle Image with Radiant Glow Behind -->
                            <div class="relative w-[120%] h-40 -mx-[10%] mt-auto flex items-center justify-center overflow-visible mb-5">
                                <div class="absolute w-28 h-28 rounded-full bg-amber-400/30 blur-2xl -z-10 pointer-events-none"></div>
                                <img src="{{ asset('images/hero-ride.png') }}" alt="Ride" class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-105">
                            </div>
                            
                            <!-- Button -->
                            <a href="/ride" class="mt-auto w-full inline-flex items-center justify-center text-white font-extrabold py-3.5 px-6 rounded-full transition-all bg-orange-500 hover:bg-orange-600 shadow-md shadow-orange-500/35 hover:shadow-lg hover:shadow-orange-500/50 hover:scale-[1.02]">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                Book Ride
                            </a>
                        </div>
                    </div>

                    <!-- Rent Card (Sapphire/Cobalt Blue) -->
                    <div class="relative bg-gradient-to-b from-blue-50 via-white to-white dark:from-blue-950/30 dark:via-[#141414] dark:to-[#141414] rounded-[2rem] shadow-[0_10px_35px_rgba(59,130,246,0.10)] overflow-hidden border-2 border-blue-300 dark:border-blue-500/30 flex flex-col pt-8 pb-7 px-6 text-center group transition-all hover:-translate-y-2 hover:border-blue-500 hover:shadow-2xl hover:shadow-blue-500/25 duration-300">
                        <!-- Top Accent Line -->
                        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-blue-400 via-indigo-500 to-blue-600"></div>
                        <div class="absolute top-0 left-0 w-full h-32 bg-gradient-to-b from-blue-200/50 to-transparent dark:from-blue-500/10 pointer-events-none"></div>
                        
                        <div class="relative z-10 flex-1 flex flex-col items-center">
                            <!-- Solid Visible Icon -->
                            <div class="w-14 h-14 rounded-2xl bg-blue-600 text-white flex items-center justify-center mb-3.5 shadow-lg shadow-blue-500/40 transition-transform group-hover:scale-110">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>
                                </svg>
                            </div>

                            <!-- Badge -->
                            <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-blue-100 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 border border-blue-300 dark:border-blue-700 mb-2">🔑 Luxury Fleet</span>

                            <h3 class="text-2xl font-black mb-1.5 text-gray-900 dark:text-white group-hover:text-blue-500 transition-colors">Rent</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-5 max-w-[250px] text-xs font-medium leading-relaxed">Tier-one luxury vehicles for self-drive or extended trips.</p>
                            
                            <!-- Vehicle Image with Radiant Glow Behind -->
                            <div class="relative w-[120%] h-40 -mx-[10%] mt-auto flex items-center justify-center overflow-visible mb-5">
                                <div class="absolute w-28 h-28 rounded-full bg-blue-400/30 blur-2xl -z-10 pointer-events-none"></div>
                                <img src="{{ asset('images/hero-rent.png') }}" alt="Rent" class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-105">
                            </div>
                            
                            <!-- Button -->
                            <a href="/rent" class="mt-auto w-full inline-flex items-center justify-center text-white font-extrabold py-3.5 px-6 rounded-full transition-all bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-500/35 hover:shadow-lg hover:shadow-blue-500/50 hover:scale-[1.02]">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                                Rent Now
                            </a>
                        </div>
                    </div>

                    <!-- Driver Card (Emerald/Mint Green) -->
                    <div class="relative bg-gradient-to-b from-emerald-50 via-white to-white dark:from-emerald-950/30 dark:via-[#141414] dark:to-[#141414] rounded-[2rem] shadow-[0_10px_35px_rgba(16,185,129,0.10)] overflow-hidden border-2 border-emerald-300 dark:border-emerald-500/30 flex flex-col pt-8 pb-7 px-6 text-center group transition-all hover:-translate-y-2 hover:border-emerald-500 hover:shadow-2xl hover:shadow-emerald-500/25 duration-300">
                        <!-- Top Accent Line -->
                        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-emerald-400 via-teal-500 to-emerald-600"></div>
                        <div class="absolute top-0 left-0 w-full h-32 bg-gradient-to-b from-emerald-200/50 to-transparent dark:from-emerald-500/10 pointer-events-none"></div>
                        
                        <div class="relative z-10 flex-1 flex flex-col items-center">
                            <!-- Solid Visible Icon -->
                            <div class="w-14 h-14 rounded-2xl bg-emerald-600 text-white flex items-center justify-center mb-3.5 shadow-lg shadow-emerald-500/40 transition-transform group-hover:scale-110">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                            </div>

                            <!-- Badge -->
                            <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700 mb-2">🛡️ Vetted Chauffeur</span>

                            <h3 class="text-2xl font-black mb-1.5 text-gray-900 dark:text-white group-hover:text-emerald-500 transition-colors">Driver</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-5 max-w-[250px] text-xs font-medium leading-relaxed">Secure an elite, background-verified chauffeur for your car.</p>
                            
                            <!-- Vehicle Image with Radiant Glow Behind -->
                            <div class="relative w-[120%] h-40 -mx-[10%] mt-auto flex items-center justify-center overflow-visible mb-5">
                                <div class="absolute w-28 h-28 rounded-full bg-emerald-400/30 blur-2xl -z-10 pointer-events-none"></div>
                                <img src="{{ asset('images/hero-hire.png') }}" alt="Driver" class="w-full h-full object-contain object-bottom transition-transform duration-500 group-hover:scale-105">
                            </div>
                            
                            <!-- Button -->
                            <a href="/hire-driver" class="mt-auto w-full inline-flex items-center justify-center text-white font-extrabold py-3.5 px-6 rounded-full transition-all bg-emerald-600 hover:bg-emerald-700 shadow-md shadow-emerald-500/35 hover:shadow-lg hover:shadow-emerald-500/50 hover:scale-[1.02]">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                Hire Driver
                            </a>
                        </div>
                    </div>

                    <!-- Deliver Card (Royal Purple) -->
                    <div class="relative bg-gradient-to-b from-purple-50 via-white to-white dark:from-purple-950/30 dark:via-[#141414] dark:to-[#141414] rounded-[2rem] shadow-[0_10px_35px_rgba(168,85,247,0.10)] overflow-hidden border-2 border-purple-300 dark:border-purple-500/30 flex flex-col pt-8 pb-7 px-6 text-center group transition-all hover:-translate-y-2 hover:border-purple-500 hover:shadow-2xl hover:shadow-purple-500/25 duration-300">
                        <!-- Top Accent Line -->
                        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-purple-400 via-fuchsia-500 to-purple-600"></div>
                        <div class="absolute top-0 left-0 w-full h-32 bg-gradient-to-b from-purple-200/50 to-transparent dark:from-purple-500/10 pointer-events-none"></div>
                        
                        <div class="relative z-10 flex-1 flex flex-col items-center">
                            <!-- Solid Visible Icon -->
                            <div class="w-14 h-14 rounded-2xl bg-purple-600 text-white flex items-center justify-center mb-3.5 shadow-lg shadow-purple-500/40 transition-transform group-hover:scale-110">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                                </svg>
                            </div>

                            <!-- Badge -->
                            <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-purple-100 dark:bg-purple-950/60 text-purple-800 dark:text-purple-300 border border-purple-300 dark:border-purple-700 mb-2">📦 Express Courier</span>

                            <h3 class="text-2xl font-black mb-1.5 text-gray-900 dark:text-white group-hover:text-purple-500 transition-colors">Deliver</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-5 max-w-[250px] text-xs font-medium leading-relaxed">Flawless white-glove parcel dispatch with turn-by-turn live GPS.</p>
                            
                            <!-- Vehicle Image with Radiant Glow Behind -->
                            <div class="relative w-[120%] h-40 -mx-[10%] mt-auto flex items-center justify-center overflow-visible mb-5">
                                <div class="absolute w-28 h-28 rounded-full bg-purple-400/30 blur-2xl -z-10 pointer-events-none"></div>
                                <img src="{{ asset('images/hero-delivery.png') }}" alt="Deliver" class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-105">
                            </div>
                            
                            <!-- Button -->
                            <a href="/delivery" class="mt-auto w-full inline-flex items-center justify-center text-white font-extrabold py-3.5 px-6 rounded-full transition-all bg-purple-600 hover:bg-purple-700 shadow-md shadow-purple-500/35 hover:shadow-lg hover:shadow-purple-500/50 hover:scale-[1.02]">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                                Send Delivery
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Trust indicators (Floating Glass Bar) -->
                <div class="flex flex-wrap items-center justify-center gap-6 lg:gap-12 px-8 py-4 bg-white/95 dark:bg-[#161616]/95 glass-colorful rounded-full shadow-[0_12px_40px_rgba(0,0,0,0.08)] border border-gray-200 dark:border-white/10 max-w-4xl mx-auto">
                    <div class="flex items-center gap-2.5 text-xs lg:text-sm font-bold text-gray-800 dark:text-gray-200">
                        <span class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </span>
                        <span>Fully Insured</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-xs lg:text-sm font-bold text-gray-800 dark:text-gray-200">
                        <span class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        </span>
                        <span>Verified Drivers</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-xs lg:text-sm font-bold text-gray-800 dark:text-gray-200">
                        <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center shrink-0 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </span>
                        <span>24/7 Support</span>
                    </div>
                    <div class="flex items-center gap-3 text-xs lg:text-sm font-bold text-gray-800 dark:text-gray-200 pl-4 border-l border-gray-200 dark:border-white/10">
                        <span class="w-8 h-8 rounded-full bg-yellow-100 text-yellow-600 flex items-center justify-center shrink-0 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        </span>
                        <div class="flex flex-col text-[11px] leading-tight font-medium text-gray-500 dark:text-gray-400">
                            <span>Avg Rating</span>
                            <span class="text-sm font-extrabold text-gray-900 dark:text-white">4.9 / 5.0</span>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- Stats Section (Solid, High-Contrast Colorful Numbers) -->
        <section class="border-y border-gray-200/80 dark:border-white/10 bg-white dark:bg-[#111] py-10 relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                    
                    <!-- Stat 1: Riders (Orange) -->
                    <div class="p-6 rounded-2xl bg-orange-50/70 dark:bg-amber-950/20 border-2 border-orange-200 dark:border-amber-500/20 flex flex-col items-center text-center transition-transform hover:-translate-y-1">
                        <div class="w-11 h-11 rounded-xl bg-orange-500 text-white flex items-center justify-center mb-3 shadow-md shadow-orange-500/30">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <div class="text-4xl font-black text-orange-600 dark:text-orange-400 mb-1">50K+</div>
                        <div class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Happy Riders</div>
                    </div>

                    <!-- Stat 2: Drivers (Blue) -->
                    <div class="p-6 rounded-2xl bg-blue-50/70 dark:bg-blue-950/20 border-2 border-blue-200 dark:border-blue-500/20 flex flex-col items-center text-center transition-transform hover:-translate-y-1">
                        <div class="w-11 h-11 rounded-xl bg-blue-600 text-white flex items-center justify-center mb-3 shadow-md shadow-blue-500/30">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <div class="text-4xl font-black text-blue-600 dark:text-blue-400 mb-1">3.2K+</div>
                        <div class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Verified Drivers</div>
                    </div>

                    <!-- Stat 3: Vehicles (Emerald) -->
                    <div class="p-6 rounded-2xl bg-emerald-50/70 dark:bg-emerald-950/20 border-2 border-emerald-200 dark:border-emerald-500/20 flex flex-col items-center text-center transition-transform hover:-translate-y-1">
                        <div class="w-11 h-11 rounded-xl bg-emerald-600 text-white flex items-center justify-center mb-3 shadow-md shadow-emerald-500/30">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>
                        </div>
                        <div class="text-4xl font-black text-emerald-600 dark:text-emerald-400 mb-1">1.5K+</div>
                        <div class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Vehicles Listed</div>
                    </div>

                    <!-- Stat 4: Rating (Purple) -->
                    <div class="p-6 rounded-2xl bg-purple-50/70 dark:bg-purple-950/20 border-2 border-purple-200 dark:border-purple-500/20 flex flex-col items-center text-center transition-transform hover:-translate-y-1">
                        <div class="w-11 h-11 rounded-xl bg-purple-600 text-white flex items-center justify-center mb-3 shadow-md shadow-purple-500/30">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        </div>
                        <div class="text-4xl font-black text-purple-600 dark:text-purple-400 mb-1">4.9 ★</div>
                        <div class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Average Rating</div>
                    </div>

                </div>
            </div>
        </section>

        <!-- Customer Categories Section (Tailored Solutions) -->
        <section class="py-16 lg:py-20 bg-[#f8fafc] dark:bg-[#0c0c0c] border-t border-gray-200/80 dark:border-white/10 relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center mb-12">
                    <span class="px-3.5 py-1 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 font-black text-xs uppercase tracking-widest border border-amber-300 dark:border-amber-700">Tailored Solutions</span>
                    <h2 class="text-3xl sm:text-5xl font-black text-gray-950 dark:text-white mt-3 mb-3 tracking-tight">Crafted for Every Journey</h2>
                    <p class="text-sm sm:text-base text-gray-600 dark:text-gray-300 max-w-2xl mx-auto font-medium">Customized mobility logistics engineered to exceed the expectations of corporate leaders, travelers, and residents.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Business Travelers Section -->
                    <div class="bg-white dark:bg-[#141414] border-2 border-amber-300/80 hover:border-amber-500 dark:border-amber-500/20 dark:hover:border-amber-400 rounded-3xl p-8 shadow-lg shadow-amber-500/5 flex flex-col justify-between transition-all hover:-translate-y-1.5 duration-300">
                        <div>
                            <div class="w-14 h-14 bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400 rounded-2xl flex items-center justify-center mb-6 shadow-sm text-2xl font-black border border-amber-200">
                                💼
                            </div>
                            <div class="text-xs font-black text-amber-700 dark:text-amber-400 uppercase tracking-widest mb-2">Corporate & Executive</div>
                            <h3 class="text-2xl font-black text-gray-950 dark:text-white mb-3">Elite Mobility for the Corporate Traveler</h3>
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed text-sm">
                                Command your schedule with executive-grade transport. Seamlessly secure a premium ride, reserve an upscale vehicle, or utilize a vetted professional driver for your entire itinerary. Deliveries and logistics are handled instantly, leaving you free to focus on business.
                            </p>
                        </div>
                        <div class="mt-8 pt-6 border-t border-gray-100 dark:border-white/10 flex flex-wrap gap-2 text-xs font-bold text-amber-900 dark:text-amber-200">
                            <span class="px-3 py-1 bg-amber-50 dark:bg-amber-950/40 rounded-full border border-amber-200 dark:border-amber-800">Executive Rides</span>
                            <span class="px-3 py-1 bg-amber-50 dark:bg-amber-950/40 rounded-full border border-amber-200 dark:border-amber-800">Luxury Rentals</span>
                            <span class="px-3 py-1 bg-amber-50 dark:bg-amber-950/40 rounded-full border border-amber-200 dark:border-amber-800">Pro Chauffeurs</span>
                            <span class="px-3 py-1 bg-amber-50 dark:bg-amber-950/40 rounded-full border border-amber-200 dark:border-amber-800">Instant Logistics</span>
                        </div>
                    </div>

                    <!-- Tourists Section -->
                    <div class="bg-white dark:bg-[#141414] border-2 border-blue-300/80 hover:border-blue-500 dark:border-blue-500/20 dark:hover:border-blue-400 rounded-3xl p-8 shadow-lg shadow-blue-500/5 flex flex-col justify-between transition-all hover:-translate-y-1.5 duration-300">
                        <div>
                            <div class="w-14 h-14 bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-400 rounded-2xl flex items-center justify-center mb-6 shadow-sm text-2xl font-black border border-blue-200">
                                ✈️
                            </div>
                            <div class="text-xs font-black text-blue-700 dark:text-blue-400 uppercase tracking-widest mb-2">Tourists & Explorers</div>
                            <h3 class="text-2xl font-black text-gray-950 dark:text-white mb-3">First-Class Exploration, Simplified</h3>
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed text-sm">
                                Experience your destination with unparalleled comfort and ease. Whether arriving in style via private transport, renting a luxury vehicle for a scenic drive, or hiring a dedicated local chauffeur, your journey is entirely curated. Enjoy effortless luggage and parcel delivery directly to your resort.
                            </p>
                        </div>
                        <div class="mt-8 pt-6 border-t border-gray-100 dark:border-white/10 flex flex-wrap gap-2 text-xs font-bold text-blue-900 dark:text-blue-200">
                            <span class="px-3 py-1 bg-blue-50 dark:bg-blue-950/40 rounded-full border border-blue-200 dark:border-blue-800">Private Transport</span>
                            <span class="px-3 py-1 bg-blue-50 dark:bg-blue-950/40 rounded-full border border-blue-200 dark:border-blue-800">Scenic Rentals</span>
                            <span class="px-3 py-1 bg-blue-50 dark:bg-blue-950/40 rounded-full border border-blue-200 dark:border-blue-800">Local Chauffeur</span>
                            <span class="px-3 py-1 bg-blue-50 dark:bg-blue-950/40 rounded-full border border-blue-200 dark:border-blue-800">Luggage Delivery</span>
                        </div>
                    </div>

                    <!-- Locals Section -->
                    <div class="bg-white dark:bg-[#141414] border-2 border-emerald-300/80 hover:border-emerald-500 dark:border-emerald-500/20 dark:hover:border-emerald-400 rounded-3xl p-8 shadow-lg shadow-emerald-500/5 flex flex-col justify-between transition-all hover:-translate-y-1.5 duration-300">
                        <div>
                            <div class="w-14 h-14 bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400 rounded-2xl flex items-center justify-center mb-6 shadow-sm text-2xl font-black border border-emerald-200">
                                🏙️
                            </div>
                            <div class="text-xs font-black text-emerald-700 dark:text-emerald-400 uppercase tracking-widest mb-2">City Residents</div>
                            <h3 class="text-2xl font-black text-gray-950 dark:text-white mb-3">Redefining Daily Transit</h3>
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed text-sm">
                                Bring white-glove service to your everyday routine. Access top-tier vehicles, secure certified drivers for special events, or coordinate white-glove courier deliveries across the city. Enjoy absolute convenience and reliability at the touch of a button.
                            </p>
                        </div>
                        <div class="mt-8 pt-6 border-t border-gray-100 dark:border-white/10 flex flex-wrap gap-2 text-xs font-bold text-emerald-900 dark:text-emerald-200">
                            <span class="px-3 py-1 bg-emerald-50 dark:bg-emerald-950/40 rounded-full border border-emerald-200 dark:border-emerald-800">Premium Vehicles</span>
                            <span class="px-3 py-1 bg-emerald-50 dark:bg-emerald-950/40 rounded-full border border-emerald-200 dark:border-emerald-800">Certified Drivers</span>
                            <span class="px-3 py-1 bg-emerald-50 dark:bg-emerald-950/40 rounded-full border border-emerald-200 dark:border-emerald-800">Special Events</span>
                            <span class="px-3 py-1 bg-emerald-50 dark:bg-emerald-950/40 rounded-full border border-emerald-200 dark:border-emerald-800">City Courier</span>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- How it Works (Connected Steps) -->
        <section class="py-16 lg:py-20 bg-white dark:bg-[#111] border-t border-gray-200/80 dark:border-white/10 relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center mb-14">
                    <span class="px-3.5 py-1 rounded-full bg-blue-100 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 font-black text-xs uppercase tracking-widest border border-blue-300 dark:border-blue-700">Simple Workflow</span>
                    <h2 class="text-3xl sm:text-5xl font-black text-gray-950 dark:text-white mt-3 mb-3 tracking-tight">Ready in Four Easy Steps</h2>
                    <p class="text-sm sm:text-base text-gray-600 dark:text-gray-300 max-w-2xl mx-auto font-medium">From instant booking to destination arrival, every step is transparent and seamless.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-8 relative">
                    <!-- Line connecting steps (desktop) -->
                    <div class="hidden md:block absolute top-12 left-[12%] right-[12%] h-1 bg-gradient-to-r from-blue-400 via-amber-400 via-emerald-400 to-purple-400 rounded-full z-0 opacity-60"></div>

                    <!-- Step 1 -->
                    <div class="text-center relative z-10 group">
                        <div class="w-24 h-24 mx-auto bg-blue-600 text-white rounded-3xl flex items-center justify-center mb-5 relative shadow-xl shadow-blue-500/30 transition-transform group-hover:scale-110">
                            <div class="absolute -top-2.5 -right-2.5 w-8 h-8 bg-white dark:bg-gray-900 text-blue-600 font-black text-xs rounded-full flex items-center justify-center border-2 border-blue-500 shadow-md">01</div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        </div>
                        <h4 class="text-lg font-black text-gray-950 dark:text-white mb-1.5">Search & Choose</h4>
                        <p class="text-gray-600 dark:text-gray-300 text-xs font-medium leading-relaxed">Search rides, luxury vehicles, or certified drivers. Filter by rates, ratings, and instant availability.</p>
                    </div>

                    <!-- Step 2 -->
                    <div class="text-center relative z-10 group">
                        <div class="w-24 h-24 mx-auto bg-amber-500 text-white rounded-3xl flex items-center justify-center mb-5 relative shadow-xl shadow-amber-500/30 transition-transform group-hover:scale-110">
                            <div class="absolute -top-2.5 -right-2.5 w-8 h-8 bg-white dark:bg-gray-900 text-amber-600 font-black text-xs rounded-full flex items-center justify-center border-2 border-amber-500 shadow-md">02</div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                        </div>
                        <h4 class="text-lg font-black text-gray-950 dark:text-white mb-1.5">Book & Pay</h4>
                        <p class="text-gray-600 dark:text-gray-300 text-xs font-medium leading-relaxed">Instant confirmation with Stripe card, digital wallet, or cash. Receive verified PDF receipts & invoices.</p>
                    </div>

                    <!-- Step 3 -->
                    <div class="text-center relative z-10 group">
                        <div class="w-24 h-24 mx-auto bg-emerald-600 text-white rounded-3xl flex items-center justify-center mb-5 relative shadow-xl shadow-emerald-500/30 transition-transform group-hover:scale-110">
                            <div class="absolute -top-2.5 -right-2.5 w-8 h-8 bg-white dark:bg-gray-900 text-emerald-600 font-black text-xs rounded-full flex items-center justify-center border-2 border-emerald-500 shadow-md">03</div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <h4 class="text-lg font-black text-gray-950 dark:text-white mb-1.5">Track in Real Time</h4>
                        <p class="text-gray-600 dark:text-gray-300 text-xs font-medium leading-relaxed">Watch your chauffeur or parcel arrive live on Google Maps with real-time ETA and status alerts.</p>
                    </div>

                    <!-- Step 4 -->
                    <div class="text-center relative z-10 group">
                        <div class="w-24 h-24 mx-auto bg-purple-600 text-white rounded-3xl flex items-center justify-center mb-5 relative shadow-xl shadow-purple-500/30 transition-transform group-hover:scale-110">
                            <div class="absolute -top-2.5 -right-2.5 w-8 h-8 bg-white dark:bg-gray-900 text-purple-600 font-black text-xs rounded-full flex items-center justify-center border-2 border-purple-500 shadow-md">04</div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        </div>
                        <h4 class="text-lg font-black text-gray-950 dark:text-white mb-1.5">Rate & Enjoy</h4>
                        <p class="text-gray-600 dark:text-gray-300 text-xs font-medium leading-relaxed">Leave your honest review. Earn loyalty rewards and enjoy seamless recurring trips anytime.</p>
                    </div>
                </div>

            </div>
        </section>

        <!-- Why RideMyCars (6 Colorful Highlight Cards with Explicit Icons) -->
        <section class="py-16 lg:py-20 bg-[#f8fafc] dark:bg-[#0a0a0a] border-t border-gray-200/80 dark:border-white/10 relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center mb-12">
                    <span class="px-3.5 py-1 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 font-black text-xs uppercase tracking-widest border border-emerald-300 dark:border-emerald-700">Why Choose Us</span>
                    <h2 class="text-3xl sm:text-5xl font-black text-gray-950 dark:text-white mt-3 mb-3 tracking-tight">Built Different. For Good Reason.</h2>
                    <p class="text-sm sm:text-base text-gray-600 dark:text-gray-300 max-w-2xl mx-auto font-medium">We unified three fragmented markets into one premier super-app, raising the bar on luxury, safety, and speed.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    
                    <!-- 1. Safety First (Emerald) -->
                    <div class="bg-white dark:bg-[#141414] border-2 border-emerald-200 dark:border-emerald-500/20 hover:border-emerald-500 rounded-3xl p-7 transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-emerald-500/10">
                        <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 flex items-center justify-center mb-4 shadow-sm border border-emerald-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <h4 class="text-lg font-black text-gray-950 dark:text-white mb-2">Safety First</h4>
                        <p class="text-gray-600 dark:text-gray-300 text-xs font-medium leading-relaxed">Every driver is ID and criminal background-verified. All vehicles undergo strict roadworthiness inspections.</p>
                    </div>

                    <!-- 2. Lightning Fast (Amber) -->
                    <div class="bg-white dark:bg-[#141414] border-2 border-amber-300 dark:border-amber-500/30 hover:border-amber-500 rounded-3xl p-7 transition-all hover:-translate-y-1 shadow-md shadow-amber-500/5">
                        <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400 flex items-center justify-center mb-4 shadow-sm border border-amber-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                        </div>
                        <h4 class="text-lg font-black text-gray-950 dark:text-white mb-2">Lightning Fast Dispatch</h4>
                        <p class="text-gray-600 dark:text-gray-300 text-xs font-medium leading-relaxed">Average driver dispatch under 2 minutes. Intelligent algorithmic routing gets your ride moving immediately.</p>
                    </div>

                    <!-- 3. 24/7 Support (Blue) -->
                    <div class="bg-white dark:bg-[#141414] border-2 border-blue-200 dark:border-blue-500/20 hover:border-blue-500 rounded-3xl p-7 transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-blue-500/10">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-400 flex items-center justify-center mb-4 shadow-sm border border-blue-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
                        </div>
                        <h4 class="text-lg font-black text-gray-950 dark:text-white mb-2">24/7 Dedicated Concierge</h4>
                        <p class="text-gray-600 dark:text-gray-300 text-xs font-medium leading-relaxed">Our support team is live 24/7. Live chat, phone, and direct email assistance in minutes, not days.</p>
                    </div>

                    <!-- 4. Transparent Pricing (Rose) -->
                    <div class="bg-white dark:bg-[#141414] border-2 border-rose-200 dark:border-rose-500/20 hover:border-rose-500 rounded-3xl p-7 transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-rose-500/10">
                        <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400 flex items-center justify-center mb-4 shadow-sm border border-rose-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                        </div>
                        <h4 class="text-lg font-black text-gray-950 dark:text-white mb-2">Transparent Pricing</h4>
                        <p class="text-gray-600 dark:text-gray-300 text-xs font-medium leading-relaxed">Zero hidden charges. Complete price breakdown with fuel, tolls, and insurance calculated upfront before you book.</p>
                    </div>

                    <!-- 5. Live Tracking (Indigo) -->
                    <div class="bg-white dark:bg-[#141414] border-2 border-indigo-200 dark:border-indigo-500/20 hover:border-indigo-500 rounded-3xl p-7 transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-indigo-500/10">
                        <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400 flex items-center justify-center mb-4 shadow-sm border border-indigo-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <h4 class="text-lg font-black text-gray-950 dark:text-white mb-2">Live Radar GPS Tracking</h4>
                        <p class="text-gray-600 dark:text-gray-300 text-xs font-medium leading-relaxed">Track vehicles, chauffeurs, and couriers turn-by-turn. Share dynamic live trip links with loved ones.</p>
                    </div>

                    <!-- 6. Trusted Community (Fuchsia) -->
                    <div class="bg-white dark:bg-[#141414] border-2 border-purple-200 dark:border-purple-500/20 hover:border-purple-500 rounded-3xl p-7 transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-purple-500/10">
                        <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-400 flex items-center justify-center mb-4 shadow-sm border border-purple-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        </div>
                        <h4 class="text-lg font-black text-gray-950 dark:text-white mb-2">Elite Verified Community</h4>
                        <p class="text-gray-600 dark:text-gray-300 text-xs font-medium leading-relaxed">50,000+ authentic user ratings. Two-way reviews ensure driver and passenger excellence on every single trip.</p>
                    </div>

                </div>

            </div>
        </section>

        <!-- Platform Features (8 Color-Coded SaaS Feature Tiles with Visible Icons) -->
        <section class="py-16 lg:py-20 bg-white dark:bg-[#111] border-t border-gray-200/80 dark:border-white/10 relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center mb-12">
                    <span class="px-3.5 py-1 rounded-full bg-purple-100 dark:bg-purple-950/60 text-purple-800 dark:text-purple-300 font-black text-xs uppercase tracking-widest border border-purple-300 dark:border-purple-700">Platform Features</span>
                    <h2 class="text-3xl sm:text-5xl font-black text-gray-950 dark:text-white mt-3 mb-3 tracking-tight">Everything Built-In. Standard.</h2>
                    <p class="text-sm sm:text-base text-gray-600 dark:text-gray-300 max-w-2xl mx-auto font-medium">No confusing add-ons. Every executive feature you need comes standard out of the box.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    
                    <!-- 1. Smart Wallet (Amber) -->
                    <div class="bg-amber-50/40 dark:bg-[#161616] border-2 border-amber-200 dark:border-amber-500/20 rounded-2xl p-6 hover:shadow-lg hover:border-amber-400 transition-all">
                        <div class="w-10 h-10 bg-amber-500 text-white rounded-xl flex items-center justify-center mb-3.5 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>
                        </div>
                        <h4 class="text-base font-black text-gray-950 dark:text-white mb-1">Smart Wallet</h4>
                        <p class="text-gray-600 dark:text-gray-300 text-xs font-medium leading-relaxed">Top up once, pay across all 4 verticals. Instant refunds and full transaction ledger.</p>
                    </div>

                    <!-- 2. Real-time Notifications (Indigo) -->
                    <div class="bg-indigo-50/40 dark:bg-[#161616] border-2 border-indigo-200 dark:border-indigo-500/20 rounded-2xl p-6 hover:shadow-lg hover:border-indigo-400 transition-all">
                        <div class="w-10 h-10 bg-indigo-600 text-white rounded-xl flex items-center justify-center mb-3.5 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                        </div>
                        <h4 class="text-base font-black text-gray-950 dark:text-white mb-1">Real-time Alerts</h4>
                        <p class="text-gray-600 dark:text-gray-300 text-xs font-medium leading-relaxed">Instant SMS, in-app push alerts, and chauffeur arrival notifications at every step.</p>
                    </div>

                    <!-- 3. Instant Invoices (Cyan) -->
                    <div class="bg-cyan-50/40 dark:bg-[#161616] border-2 border-cyan-200 dark:border-cyan-500/20 rounded-2xl p-6 hover:shadow-lg hover:border-cyan-400 transition-all">
                        <div class="w-10 h-10 bg-cyan-600 text-white rounded-xl flex items-center justify-center mb-3.5 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/></svg>
                        </div>
                        <h4 class="text-base font-black text-gray-950 dark:text-white mb-1">Instant PDF Invoices</h4>
                        <p class="text-gray-600 dark:text-gray-300 text-xs font-medium leading-relaxed">Automated VAT receipts and travel vouchers ready for corporate accounting & expenses.</p>
                    </div>

                    <!-- 4. Coupons & Referrals (Pink) -->
                    <div class="bg-pink-50/40 dark:bg-[#161616] border-2 border-pink-200 dark:border-pink-500/20 rounded-2xl p-6 hover:shadow-lg hover:border-pink-400 transition-all">
                        <div class="w-10 h-10 bg-pink-600 text-white rounded-xl flex items-center justify-center mb-3.5 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l8.29-8.29c.94-.94.94-2.48 0-3.42L12 2Z"/><path d="M7 7h.01"/></svg>
                        </div>
                        <h4 class="text-base font-black text-gray-950 dark:text-white mb-1">Coupons & Credits</h4>
                        <p class="text-gray-600 dark:text-gray-300 text-xs font-medium leading-relaxed">Share referral codes to earn wallet credits. Auto-apply promotional discounts anytime.</p>
                    </div>

                    <!-- 5. Saved Locations (Teal) -->
                    <div class="bg-teal-50/40 dark:bg-[#161616] border-2 border-teal-200 dark:border-teal-500/20 rounded-2xl p-6 hover:shadow-lg hover:border-teal-400 transition-all">
                        <div class="w-10 h-10 bg-teal-600 text-white rounded-xl flex items-center justify-center mb-3.5 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <h4 class="text-base font-black text-gray-950 dark:text-white mb-1">Saved Favorites</h4>
                        <p class="text-gray-600 dark:text-gray-300 text-xs font-medium leading-relaxed">One-tap booking for frequent airports, offices, hotels, and residential hubs.</p>
                    </div>

                    <!-- 6. Ratings & Reviews (Purple) -->
                    <div class="bg-purple-50/40 dark:bg-[#161616] border-2 border-purple-200 dark:border-purple-500/20 rounded-2xl p-6 hover:shadow-lg hover:border-purple-400 transition-all">
                        <div class="w-10 h-10 bg-purple-600 text-white rounded-xl flex items-center justify-center mb-3.5 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        </div>
                        <h4 class="text-base font-black text-gray-950 dark:text-white mb-1">Verified Reviews</h4>
                        <p class="text-gray-600 dark:text-gray-300 text-xs font-medium leading-relaxed">Only verified riders and chauffeurs can rate, ensuring trustworthy transparent scores.</p>
                    </div>

                    <!-- 7. Identity Verification (Emerald) -->
                    <div class="bg-emerald-50/40 dark:bg-[#161616] border-2 border-emerald-300 dark:border-emerald-500/40 rounded-2xl p-6 hover:shadow-lg hover:border-emerald-500 transition-all">
                        <div class="w-10 h-10 bg-emerald-600 text-white rounded-xl flex items-center justify-center mb-3.5 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                        </div>
                        <h4 class="text-base font-black text-gray-950 dark:text-white mb-1">Government ID Check</h4>
                        <p class="text-gray-600 dark:text-gray-300 text-xs font-medium leading-relaxed">Biometric driver license and vehicle document verification guarantee complete trust.</p>
                    </div>

                    <!-- 8. Easy Refunds (Orange) -->
                    <div class="bg-orange-50/40 dark:bg-[#161616] border-2 border-orange-200 dark:border-orange-500/20 rounded-2xl p-6 hover:shadow-lg hover:border-orange-400 transition-all">
                        <div class="w-10 h-10 bg-orange-500 text-white rounded-xl flex items-center justify-center mb-3.5 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        </div>
                        <h4 class="text-base font-black text-gray-950 dark:text-white mb-1">Guaranteed Refunds</h4>
                        <p class="text-gray-600 dark:text-gray-300 text-xs font-medium leading-relaxed">Automated instant cancellation refunds credited straight back to your wallet or card.</p>
                    </div>

                </div>

            </div>
        </section>

        <!-- Dual Download Apps CTA Section (Clean Light Theme with Vibrant Cards - No Black Block) -->
        <section class="py-16 lg:py-20 bg-gradient-to-b from-[#f8fafc] via-white to-gray-50 dark:from-[#0a0a0a] dark:via-[#111] dark:to-[#0a0a0a] border-t border-gray-200/80 dark:border-white/10 relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-amber-100 dark:bg-amber-950/60 border border-amber-300 dark:border-amber-700/60 text-amber-800 dark:text-amber-300 font-extrabold text-xs uppercase tracking-widest mb-4 shadow-sm">
                        📱 Mobile Experience
                    </span>
                    <h2 class="text-3xl sm:text-5xl font-black text-gray-950 dark:text-white mb-4 tracking-tight">Experience RideMyCars on Your Mobile Device</h2>
                    <p class="text-base sm:text-lg text-gray-700 dark:text-gray-300 font-medium">Download our dedicated native mobile applications designed for seamless riding or maximum driving earnings.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-5xl mx-auto">
                    
                    <!-- Rider App Card (Light & Gold Accent) -->
                    <div class="p-8 rounded-3xl bg-white dark:bg-[#161616] border-2 border-amber-300 hover:border-amber-500 dark:border-amber-500/30 dark:hover:border-amber-400 transition-all flex flex-col justify-between group shadow-xl hover:shadow-2xl hover:shadow-amber-500/10">
                        <div class="space-y-4 mb-8">
                            <div class="flex items-center justify-between gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-amber-500 text-gray-950 flex items-center justify-center shadow-lg shadow-amber-500/30">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 2.8C2.1 10.9 2 11.1 2 11.4V16c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>
                                </div>
                                <span class="text-xs font-black text-amber-800 dark:text-amber-300 bg-amber-100 dark:bg-amber-950/60 px-3 py-1 rounded-full border border-amber-300 dark:border-amber-700 uppercase tracking-wider">Rider App</span>
                            </div>
                            <h3 class="text-2xl font-black text-gray-950 dark:text-white">RideMyCars for Riders</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed font-medium">Book luxury rides, rent executive vehicles, schedule private verified chauffeurs, and track parcels live.</p>
                            <ul class="text-xs text-gray-700 dark:text-gray-300 space-y-2 pt-1 font-semibold">
                                <li class="flex items-center gap-2.5"><span class="text-orange-600 dark:text-orange-400 font-black">✓</span> Instant on-demand rides & airport pickups</li>
                                <li class="flex items-center gap-2.5"><span class="text-orange-600 dark:text-orange-400 font-black">✓</span> Live GPS driver & chauffeur tracking</li>
                                <li class="flex items-center gap-2.5"><span class="text-orange-600 dark:text-orange-400 font-black">✓</span> Stripe card checkout & multi-currency wallet</li>
                            </ul>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 pt-4 border-t border-gray-100 dark:border-white/10">
                            <a href="{{ site_setting('rider.ios_url', 'https://apps.apple.com/app/ridemycars/id123456789') }}" target="_blank" rel="noopener" class="flex items-center gap-2.5 px-4 py-3 bg-gray-900 hover:bg-black text-white rounded-xl text-xs font-bold transition-all shadow-sm">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.62-.75 1.04-1.8 0.92-2.85-.9.04-1.99.6-2.61 1.35-.55.63-1.03 1.68-.9 2.7.99.08 2.01-.5 2.59-1.2z"/></svg>
                                <span>App Store</span>
                            </a>
                            <a href="{{ route('download.rider') }}" download="RideMyCars-Rider.apk" class="flex items-center gap-2.5 px-4 py-3 bg-amber-500 hover:bg-amber-600 text-gray-950 font-black rounded-xl text-xs transition-all shadow-md shadow-amber-500/30">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M3.609 1.814L13.792 12 3.61 22.186a1.994 1.994 0 0 1-.61-.954V2.768c.118-.363.33-.687.609-.954zm11.233 11.233l2.257 2.257-11.83 6.697 9.573-8.954zm2.257-2.094l2.845 1.611c.907.514.907 1.353 0 1.867l-2.845 1.611-2.09-2.09 2.09-2.999zm-2.257-2.093L5.27 0.906l11.83 6.697-2.258 2.257z"/></svg>
                                <span>Google Play / APK</span>
                            </a>
                        </div>
                    </div>

                    <!-- Driver App Card (Light & Orange Accent) -->
                    <div class="p-8 rounded-3xl bg-white dark:bg-[#161616] border-2 border-orange-300 hover:border-orange-500 dark:border-orange-500/30 dark:hover:border-orange-400 transition-all flex flex-col justify-between group shadow-xl hover:shadow-2xl hover:shadow-orange-500/10">
                        <div class="space-y-4 mb-8">
                            <div class="flex items-center justify-between gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-orange-500 text-white flex items-center justify-center shadow-lg shadow-orange-500/30">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>
                                </div>
                                <span class="text-xs font-black text-orange-800 dark:text-orange-300 bg-orange-100 dark:bg-orange-950/60 px-3 py-1 rounded-full border border-orange-300 dark:border-orange-700 uppercase tracking-wider">Driver Partner App</span>
                            </div>
                            <h3 class="text-2xl font-black text-gray-950 dark:text-white">RideMyCars Driver</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed font-medium">Keep 90% of revenue with our 10% commission model. Accept trips, turn-by-turn navigation & instant payouts.</p>
                            <ul class="text-xs text-gray-700 dark:text-gray-300 space-y-2 pt-1 font-semibold">
                                <li class="flex items-center gap-2.5"><span class="text-orange-600 dark:text-orange-400 font-black">✓</span> Keep 90% from trip #1 (only 10% platform fee)</li>
                                <li class="flex items-center gap-2.5"><span class="text-orange-600 dark:text-orange-400 font-black">✓</span> Instant sound alerts & background dispatch</li>
                                <li class="flex items-center gap-2.5"><span class="text-orange-600 dark:text-orange-400 font-black">✓</span> Live daily & weekly earnings dashboard</li>
                            </ul>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 pt-4 border-t border-gray-100 dark:border-white/10">
                            <a href="{{ site_setting('driver.ios_url', 'https://apps.apple.com/app/ridemycars-driver/id987654321') }}" target="_blank" rel="noopener" class="flex items-center gap-2.5 px-4 py-3 bg-gray-900 hover:bg-black text-white rounded-xl text-xs font-bold transition-all shadow-sm">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.62-.75 1.04-1.8 0.92-2.85-.9.04-1.99.6-2.61 1.35-.55.63-1.03 1.68-.9 2.7.99.08 2.01-.5 2.59-1.2z"/></svg>
                                <span>App Store</span>
                            </a>
                            <a href="{{ route('download.driver') }}" download="RideMyCars-Driver.apk" class="flex items-center gap-2.5 px-4 py-3 bg-orange-500 hover:bg-orange-600 text-white font-black rounded-xl text-xs transition-all shadow-md shadow-orange-500/30">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M3.609 1.814L13.792 12 3.61 22.186a1.994 1.994 0 0 1-.61-.954V2.768c.118-.363.33-.687.609-.954zm11.233 11.233l2.257 2.257-11.83 6.697 9.573-8.954zm2.257-2.094l2.845 1.611c.907.514.907 1.353 0 1.867l-2.845 1.611-2.09-2.09 2.09-2.999zm-2.257-2.093L5.27 0.906l11.83 6.697-2.258 2.257z"/></svg>
                                <span>Google Play / APK</span>
                            </a>
                        </div>
                    </div>

                </div>

                <div class="mt-10 text-center">
                    <a href="/apps" class="inline-flex items-center gap-2 text-sm font-bold text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300 transition-colors">
                        <span>View complete mobile apps details, QR codes & installation guide</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </section>

    </main>

</x-layout>