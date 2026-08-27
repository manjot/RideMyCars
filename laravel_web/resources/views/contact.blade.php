<x-layout>
    <x-slot:title>Contact Us — RideMyCars Support & Inquiries</x-slot>

    <main class="flex-1 bg-gray-50 dark:bg-[#0a0a0a] py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="px-4 py-1.5 rounded-full bg-brand-500/10 text-brand-600 dark:text-brand-400 font-extrabold text-xs uppercase tracking-wider border border-brand-500/20 inline-block mb-3">
                    24/7 Concierge & Support
                </span>
                <h1 class="text-4xl md:text-5xl font-black text-gray-900 dark:text-white tracking-tight mb-4">
                    How Can We Help You?
                </h1>
                <p class="text-lg text-gray-600 dark:text-gray-400 leading-relaxed">
                    Have questions about booking a ride, renting a luxury vehicle, hiring a personal chauffeur, or tracking parcel dispatch? Reach out to our dedicated team.
                </p>
            </div>

            <!-- Flash Success Message -->
            @if(session('success'))
                <div class="max-w-4xl mx-auto mb-8 p-5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/40 text-emerald-800 dark:text-emerald-200 text-sm font-bold flex items-center gap-3 shadow-sm">
                    <svg class="w-6 h-6 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 max-w-6xl mx-auto">
                
                <!-- Left Column: Contact Cards -->
                <div class="lg:col-span-5 space-y-6">
                    
                    <!-- Email Support Card -->
                    <div class="bg-white dark:bg-[#121212] p-8 rounded-3xl border border-gray-200/80 dark:border-white/10 shadow-sm flex items-start gap-5">
                        <div class="w-12 h-12 rounded-2xl bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center shrink-0 border border-brand-500/20">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-gray-900 dark:text-white mb-1">Email Support</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">For customer inquiries, help & assistance</p>
                            <a href="mailto:support@ridemycars.com" class="text-sm font-black text-brand-600 dark:text-brand-400 hover:underline">
                                support@ridemycars.com
                            </a>
                        </div>
                    </div>

                    <!-- Direct Phone Card -->
                    <div class="bg-white dark:bg-[#121212] p-8 rounded-3xl border border-gray-200/80 dark:border-white/10 shadow-sm flex items-start gap-5">
                        <div class="w-12 h-12 rounded-2xl bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center shrink-0 border border-brand-500/20">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-gray-900 dark:text-white mb-1">Phone Line</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Available 24 hours a day, 7 days a week</p>
                            <a href="tel:+18885700008" class="text-sm font-black text-brand-600 dark:text-brand-400 hover:underline">
                                +1 888 570 0008
                            </a>
                        </div>
                    </div>

                    <!-- Headquarters Location -->
                    <div class="bg-white dark:bg-[#121212] p-8 rounded-3xl border border-gray-200/80 dark:border-white/10 shadow-sm flex items-start gap-5">
                        <div class="w-12 h-12 rounded-2xl bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center shrink-0 border border-brand-500/20">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-gray-900 dark:text-white mb-1">Headquarters</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">RideMyCars Operations</p>
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200">
                                Washington, DC, USA
                            </span>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Interactive Inquiry Form -->
                <div class="lg:col-span-7 bg-white dark:bg-[#121212] p-8 md:p-10 rounded-3xl border border-gray-200/80 dark:border-white/10 shadow-sm">
                    <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight mb-2">
                        Send Us a Message
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-8">
                        Our executive concierge will review your message and respond directly to your email.
                    </p>

                    <form action="/contact/send" method="POST" class="space-y-6">
                        @csrf
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <!-- Full Name -->
                            <div class="space-y-2">
                                <label class="block text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Your Name *</label>
                                <input type="text" name="name" required value="{{ old('name', auth()->user()->name ?? '') }}" placeholder="e.g. John Doe" class="w-full px-4 py-3.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-sm font-medium text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 outline-none transition-all">
                            </div>

                            <!-- Email Address -->
                            <div class="space-y-2">
                                <label class="block text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Email Address *</label>
                                <input type="email" name="email" required value="{{ old('email', auth()->user()->email ?? '') }}" placeholder="you@example.com" class="w-full px-4 py-3.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-sm font-medium text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 outline-none transition-all">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <!-- Phone -->
                            <div class="space-y-2">
                                <label class="block text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Phone Number (Optional)</label>
                                <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="+1 555 123 4567" class="w-full px-4 py-3.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-sm font-medium text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 outline-none transition-all">
                            </div>

                            <!-- Subject / Topic -->
                            <div class="space-y-2">
                                <label class="block text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Inquiry Topic *</label>
                                <select name="subject" required class="w-full px-4 py-3.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-sm font-medium text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 outline-none transition-all">
                                    <option value="General Inquiry">General Platform Inquiry</option>
                                    <option value="Ride Booking Support">Ride Booking Support</option>
                                    <option value="Vehicle Rental Inquiry">Vehicle Rental & Fleet</option>
                                    <option value="Driver Hiring Concierge">Driver Hiring Concierge</option>
                                    <option value="Package Delivery Dispatch">Package Delivery & Tracking</option>
                                    <option value="Corporate / Club Membership">Corporate & VIP Membership</option>
                                    <option value="Partnership / Fleet Owner">Partnership & Vehicle Listing</option>
                                </select>
                            </div>
                        </div>

                        <!-- Message Body -->
                        <div class="space-y-2">
                            <label class="block text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Your Message *</label>
                            <textarea name="message" rows="5" required placeholder="Tell us how we can assist you with your mobility or logistics needs..." class="w-full px-4 py-3.5 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-sm font-medium text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 outline-none transition-all">{{ old('message') }}</textarea>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="w-full py-4 px-8 rounded-2xl text-white font-extrabold text-sm transition-all shadow-lg hover:opacity-95 transform hover:-translate-y-0.5 flex items-center justify-center gap-2" style="background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.4);">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            <span>Send Message to Concierge</span>
                        </button>
                    </form>
                </div>

            </div>

            <!-- Global Corporate Headquarters & Regional Offices (New Development Finance Group) -->
            <div class="mt-20 pt-16 border-t border-gray-200 dark:border-white/10 max-w-6xl mx-auto">
                <div class="text-center max-w-3xl mx-auto mb-12">
                    <span class="px-4 py-1.5 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 font-extrabold text-xs uppercase tracking-wider border border-amber-500/20 inline-block mb-3">
                        New Development Finance Group
                    </span>
                    <h2 class="text-3xl md:text-4xl font-black text-gray-900 dark:text-white tracking-tight mb-3">
                        Global Corporate & Regional Offices
                    </h2>
                    <p class="text-sm md:text-base text-gray-600 dark:text-gray-400">
                        Operating under the global corporate umbrella of New Development Finance Group across the Americas and Africa.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    
                    <!-- 1. USA Headquarters -->
                    <div class="bg-white dark:bg-[#121212] rounded-3xl p-8 border border-gray-200/80 dark:border-white/10 shadow-sm hover:shadow-xl hover:border-amber-500/30 transition-all duration-300 flex flex-col justify-between group relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-amber-500/5 rounded-full blur-2xl group-hover:bg-amber-500/10 transition-all"></div>
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-5">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/40">
                                    <span>🇺🇸</span> USA
                                </span>
                                <span class="text-[11px] font-extrabold uppercase tracking-wider text-amber-600 dark:text-amber-400">Global HQ</span>
                            </div>
                            <h3 class="text-lg font-black text-gray-900 dark:text-white mb-2">
                                United States Headquarters
                            </h3>
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-4">
                                New Development Finance Group LLC
                            </p>
                            <div class="p-4 rounded-2xl bg-gray-50 dark:bg-[#181818] border border-gray-100 dark:border-white/5 space-y-2 text-xs font-medium text-gray-700 dark:text-gray-300 mb-6">
                                <div class="flex items-start gap-2.5">
                                    <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span class="leading-relaxed">4301 Saddle River Drive, Bowie, MD 20720, United States</span>
                                </div>
                                <div class="flex items-center gap-2.5 pt-1">
                                    <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    <a href="tel:+18885700008" class="font-bold hover:text-amber-500 transition-colors">+1 888 570 0008</a>
                                </div>
                            </div>
                        </div>
                        <a href="https://maps.google.com/?q=4301+Saddle+River+Drive,+Bowie,+MD+20720" target="_blank" rel="noopener" class="w-full py-2.5 px-4 bg-gray-100 hover:bg-amber-500 hover:text-black dark:bg-white/5 dark:hover:bg-amber-500 dark:hover:text-black text-gray-800 dark:text-gray-200 font-bold text-xs rounded-xl transition-all flex items-center justify-center gap-2 group/btn">
                            <span>Open in Maps</span>
                            <svg class="w-3.5 h-3.5 group-hover/btn:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>

                    <!-- 2. RSA Regional Hub -->
                    <div class="bg-white dark:bg-[#121212] rounded-3xl p-8 border border-gray-200/80 dark:border-white/10 shadow-sm hover:shadow-xl hover:border-amber-500/30 transition-all duration-300 flex flex-col justify-between group relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/5 rounded-full blur-2xl group-hover:bg-emerald-500/10 transition-all"></div>
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-5">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/40">
                                    <span>🇿🇦</span> RSA
                                </span>
                                <span class="text-[11px] font-extrabold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Southern Africa</span>
                            </div>
                            <h3 class="text-lg font-black text-gray-900 dark:text-white mb-2">
                                South Africa Regional Hub
                            </h3>
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-4">
                                New Development Finance Group (Pty) Ltd
                            </p>
                            <div class="p-4 rounded-2xl bg-gray-50 dark:bg-[#181818] border border-gray-100 dark:border-white/5 space-y-2 text-xs font-medium text-gray-700 dark:text-gray-300 mb-6">
                                <div class="flex items-start gap-2.5">
                                    <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span class="leading-relaxed">11 Corona Road, Sandhurst, Sandton, Gauteng 2196, South Africa</span>
                                </div>
                                <div class="flex items-center gap-2.5 pt-1">
                                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    <a href="tel:+18885700008" class="font-bold hover:text-emerald-500 transition-colors">+1 888 570 0008</a>
                                </div>
                            </div>
                        </div>
                        <a href="https://maps.google.com/?q=11+Corona+Road,+Sandhurst,+Sandton,+Gauteng+2196" target="_blank" rel="noopener" class="w-full py-2.5 px-4 bg-gray-100 hover:bg-emerald-500 hover:text-black dark:bg-white/5 dark:hover:bg-emerald-500 dark:hover:text-black text-gray-800 dark:text-gray-200 font-bold text-xs rounded-xl transition-all flex items-center justify-center gap-2 group/btn">
                            <span>Open in Maps</span>
                            <svg class="w-3.5 h-3.5 group-hover/btn:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>

                    <!-- 3. Ghana Regional Hub -->
                    <div class="bg-white dark:bg-[#121212] rounded-3xl p-8 border border-gray-200/80 dark:border-white/10 shadow-sm hover:shadow-xl hover:border-amber-500/30 transition-all duration-300 flex flex-col justify-between group relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-amber-500/5 rounded-full blur-2xl group-hover:bg-amber-500/10 transition-all"></div>
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-5">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/40">
                                    <span>🇬🇭</span> GHA
                                </span>
                                <span class="text-[11px] font-extrabold uppercase tracking-wider text-amber-600 dark:text-amber-400">West Africa</span>
                            </div>
                            <h3 class="text-lg font-black text-gray-900 dark:text-white mb-2">
                                Ghana Regional Hub
                            </h3>
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-4">
                                New Development Finance Group Ghana Ltd
                            </p>
                            <div class="p-4 rounded-2xl bg-gray-50 dark:bg-[#181818] border border-gray-100 dark:border-white/5 space-y-2 text-xs font-medium text-gray-700 dark:text-gray-300 mb-6">
                                <div class="flex items-start gap-2.5">
                                    <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span class="leading-relaxed">No 1 Airport Square, 8th Floor, Airport City, Accra, Ghana</span>
                                </div>
                                <div class="flex items-center gap-2.5 pt-1">
                                    <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    <a href="tel:+18885700008" class="font-bold hover:text-amber-500 transition-colors">+1 888 570 0008</a>
                                </div>
                            </div>
                        </div>
                        <a href="https://maps.google.com/?q=No+1+Airport+Square,+Airport+City,+Accra,+Ghana" target="_blank" rel="noopener" class="w-full py-2.5 px-4 bg-gray-100 hover:bg-amber-500 hover:text-black dark:bg-white/5 dark:hover:bg-amber-500 dark:hover:text-black text-gray-800 dark:text-gray-200 font-bold text-xs rounded-xl transition-all flex items-center justify-center gap-2 group/btn">
                            <span>Open in Maps</span>
                            <svg class="w-3.5 h-3.5 group-hover/btn:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </main>
</x-layout>
