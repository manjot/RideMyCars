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
                            <a href="tel:+18552033177" class="text-sm font-black text-brand-600 dark:text-brand-400 hover:underline">
                                +1 855 203 3177
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
                    <!-- Official Instagram & Social Card -->
                    <div class="bg-white dark:bg-[#121212] p-8 rounded-3xl border border-gray-200/80 dark:border-white/10 shadow-sm flex items-start gap-5">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-500/20 via-pink-500/20 to-purple-500/20 text-pink-600 dark:text-pink-400 flex items-center justify-center shrink-0 border border-pink-500/20">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-gray-900 dark:text-white mb-1">Follow Us on Instagram</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Official RideMyCars profile, updates & community</p>
                            <a href="https://www.instagram.com/ridemycars1?igsi=ZHc2ZjltdHdiaDNj&utm_source=qr" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-pink-500 to-amber-500 hover:from-pink-600 hover:to-amber-600 text-white text-xs font-black rounded-xl shadow-md transition-all group">
                                <span>@ridemycars1</span>
                                <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>
                    <!-- Official X (Twitter) Card -->
                    <div class="bg-white dark:bg-[#121212] p-8 rounded-3xl border border-gray-200/80 dark:border-white/10 shadow-sm flex items-start gap-5">
                        <div class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-white/10 text-gray-900 dark:text-white flex items-center justify-center shrink-0 border border-gray-200 dark:border-white/20">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-gray-900 dark:text-white mb-1">Follow Us on X</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Official RideMyCars announcements, support & news</p>
                            <a href="https://x.com/ridemycars" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-900 hover:bg-black dark:bg-white dark:hover:bg-gray-100 text-white dark:text-gray-900 text-xs font-black rounded-xl shadow-md transition-all group">
                                <span>@ridemycars</span>
                                <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>
                    <!-- Official TikTok Card -->
                    <div class="bg-white dark:bg-[#121212] p-8 rounded-3xl border border-gray-200/80 dark:border-white/10 shadow-sm flex items-start gap-5">
                        <div class="w-12 h-12 rounded-2xl bg-black text-white dark:bg-white dark:text-black flex items-center justify-center shrink-0 border border-gray-200 dark:border-white/20">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 3 15.68 6.34 6.34 0 0 0 9.34 22a6.34 6.34 0 0 0 6.33-6.33V9.05a8.3 8.3 0 0 0 4.92 1.6V7.21a4.85 4.85 0 0 1-1-.52z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-gray-900 dark:text-white mb-1">Follow Us on TikTok</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Official RideMyCars videos, viral shorts & promos</p>
                            <a href="{{ site_setting('social.tiktok_url', 'https://www.tiktok.com/@ridemycars') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-4 py-2 bg-black hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-black text-xs font-black rounded-xl shadow-md transition-all group">
                                <span>@ridemycars</span>
                                <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>
                    <!-- Official Facebook Card -->
                    <div class="bg-white dark:bg-[#121212] p-8 rounded-3xl border border-gray-200/80 dark:border-white/10 shadow-sm flex items-start gap-5">
                        <div class="w-12 h-12 rounded-2xl bg-blue-600/10 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 border border-blue-600/20">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor"><path d="M9 8H6v4h3v12h5V12h3.642L18 8h-4V6.333C14 5.374 14.5 5 15.778 5H18V0h-3.808C10.593 0 9 1.583 9 4.615z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-gray-900 dark:text-white mb-1">Follow Us on Facebook</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Official RideMyCars Facebook page, news & community</p>
                            <a href="https://www.facebook.com/profile.php?id=61594184214102" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-black rounded-xl shadow-md transition-all group">
                                <span>Facebook Profile</span>
                                <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    <!-- Official LinkedIn Card -->
                    <div class="bg-white dark:bg-[#121212] p-8 rounded-3xl border border-gray-200/80 dark:border-white/10 shadow-sm flex items-start gap-5">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-600/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 border border-indigo-600/20">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-gray-900 dark:text-white mb-1">Follow Us on LinkedIn</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Official RideMyCars corporate LinkedIn profile & news</p>
                            <a href="https://www.linkedin.com/in/ride-mycars-587b03432" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black rounded-xl shadow-md transition-all group">
                                <span>LinkedIn Profile</span>
                                <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
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
                                    <img src="https://flagcdn.com/w40/us.png" alt="USA Flag" class="w-4 h-3 object-cover rounded-sm shadow-sm"> USA
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
                                    <a href="tel:+18552033177" class="font-bold hover:text-amber-500 transition-colors">+1 855 203 3177</a>
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
                                    <img src="https://flagcdn.com/w40/za.png" alt="South Africa Flag" class="w-4 h-3 object-cover rounded-sm shadow-sm"> RSA
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
                                    <a href="tel:+18552033177" class="font-bold hover:text-emerald-500 transition-colors">+1 855 203 3177</a>
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
                                    <img src="https://flagcdn.com/w40/gh.png" alt="Ghana Flag" class="w-4 h-3 object-cover rounded-sm shadow-sm"> GHA
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
                                    <a href="tel:+18552033177" class="font-bold hover:text-amber-500 transition-colors">+1 855 203 3177</a>
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
