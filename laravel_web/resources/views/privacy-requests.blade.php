<x-layout>
    <x-slot:title>Data Privacy & Statutory Rights Portal — RideMyCars</x-slot>

    <main class="flex-1 w-full max-w-7xl mx-auto px-4 py-10 sm:px-6 lg:px-8">
        
        <!-- Header Banner -->
        <div class="mb-10 p-6 sm:p-8 bg-slate-900 text-white rounded-3xl shadow-xl relative overflow-hidden border border-slate-800">
            <div class="relative z-10">
                <span class="inline-flex items-center gap-2 px-3.5 py-1 bg-amber-400/20 border border-amber-400/40 rounded-full text-xs font-bold uppercase tracking-widest text-amber-300 mb-3">
                    GDPR / CCPA / POPIA Compliance Desk
                </span>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white">Data Privacy & Statutory Rights Portal</h1>
                <p class="text-slate-200 text-sm mt-2 max-w-2xl font-medium leading-relaxed">
                    Submit statutory requests for personal data access, rectification, data portability, processing restriction, or account data erasure.
                </p>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-8 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/40 text-emerald-800 dark:text-emerald-200 text-xs font-bold flex items-center gap-2">
                <span>✅ {{ session('success') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left Column: Form -->
            <div class="lg:col-span-7 bg-white dark:bg-[#141414] border border-gray-100 dark:border-white/10 rounded-3xl p-6 sm:p-8 shadow-sm">
                <h2 class="text-xl font-black text-gray-900 dark:text-white mb-2">Submit Statutory Privacy Request</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">Requests are officially acknowledged within 72 hours and completed within 30 days per data protection law.</p>

                <form action="/privacy-requests" method="POST" class="space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Full Name *</label>
                            <input type="text" name="name" required value="{{ auth()->user()->name ?? old('name') }}" placeholder="John Doe" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Email Address *</label>
                            <input type="email" name="email" required value="{{ auth()->user()->email ?? old('email') }}" placeholder="john@example.com" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Contact Phone</label>
                            <input type="text" name="phone" value="{{ auth()->user()->phone ?? old('phone') }}" placeholder="+1 888 570 0008" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Statutory Request Type *</label>
                            <select name="request_type" required class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none">
                                <option value="access">Right of Access (Copy of My Data)</option>
                                <option value="erasure">Right to Erasure (Account Deletion & Data Purge)</option>
                                <option value="portability">Right to Data Portability (JSON/CSV Export)</option>
                                <option value="rectification">Right to Rectification (Correct False Data)</option>
                                <option value="restriction">Right to Restrict Processing</option>
                                <option value="objection">Right to Object to Automated Profiling</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Request Details & Justification *</label>
                        <textarea name="details" rows="5" required placeholder="Specify exact data records or reasons for this privacy request..." class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-xs font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500 outline-none"></textarea>
                    </div>

                    <button type="submit" class="w-full py-4 bg-amber-400 hover:bg-amber-500 text-slate-950 font-black text-sm rounded-2xl shadow-md transition-all">
                        🛡️ Submit Privacy Request
                    </button>
                </form>
            </div>

            <!-- Right Column: DPO Information Card -->
            <div class="lg:col-span-5 space-y-6">
                
                <div class="bg-white dark:bg-[#141414] border border-gray-100 dark:border-white/10 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
                    <h2 class="text-xl font-black text-gray-900 dark:text-white border-b border-gray-100 dark:border-white/10 pb-4 flex items-center gap-2">
                        <span>⚖️</span>
                        <span>Data Protection Officer Desk</span>
                    </h2>

                    <div class="space-y-4 text-xs">
                        <div class="p-4 bg-gray-50 dark:bg-white/5 rounded-2xl space-y-2 font-mono text-gray-800 dark:text-gray-200 border border-gray-100 dark:border-white/10">
                            <p><strong>Parent Entity:</strong> New Development Finance Group</p>
                            <p><strong>Department:</strong> Legal & Compliance Department</p>
                            <div class="border-t border-gray-200 dark:border-white/10 pt-2 space-y-1">
                                <p><strong>🇺🇸 USA HQ:</strong> 4301 Saddle River Drive, Bowie, MD 20720, United States</p>
                                <p><strong>🇿🇦 South Africa Hub:</strong> 11 Corona Road, Sandhurst, Sandton, Gauteng 2196, South Africa</p>
                                <p><strong>🇬🇭 Ghana Hub:</strong> No 1 Airport Square, 8th Floor, Airport City, Accra, Ghana</p>
                            </div>
                            <div class="border-t border-gray-200 dark:border-white/10 pt-2 space-y-1">
                                <p><strong>Contact Telephone:</strong> <a href="tel:+18552033177" class="text-amber-500 font-bold hover:underline">+1 855 203 3177</a></p>
                                <p><strong>DPO Direct Email:</strong> <a href="mailto:privacy@ridemycars.com" class="text-amber-500 font-bold hover:underline">privacy@ridemycars.com</a></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- My Existing Requests (if authenticated) -->
                @if(auth()->check() && isset($requests) && $requests->count() > 0)
                    <div class="bg-white dark:bg-[#141414] border border-gray-100 dark:border-white/10 rounded-3xl p-6 shadow-sm space-y-4">
                        <h3 class="font-extrabold text-sm text-gray-900 dark:text-white">Your Submitted Requests</h3>
                        <div class="space-y-3">
                            @foreach($requests as $req)
                                <div class="p-3 bg-gray-50 dark:bg-white/5 rounded-2xl text-xs flex items-center justify-between">
                                    <div>
                                        <p class="font-mono font-bold text-gray-900 dark:text-white">{{ $req->request_code }}</p>
                                        <p class="text-gray-500 uppercase text-[10px] font-bold">{{ $req->request_type }}</p>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full font-bold text-[10px] uppercase bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                        {{ $req->status }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

        </div>
    </main>
</x-layout>
