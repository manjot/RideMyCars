<x-layout>
    <x-slot:title>Account Deletion & Data Purge Request — RideMyCars</x-slot>

    <main class="flex-1 max-w-5xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
        
        <!-- Breadcrumb / Header Pill -->
        <div class="mb-6 flex items-center justify-between flex-wrap gap-4">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-rose-500/10 dark:bg-rose-950/40 border border-rose-500/25 text-rose-700 dark:text-rose-300 font-extrabold text-xs uppercase tracking-wider">
                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                <span>Security & Privacy Compliance • Apple & Google Play Compliant</span>
            </div>
            <a href="/privacy-requests" class="text-xs font-bold text-gray-500 hover:text-amber-500 dark:text-gray-400 flex items-center gap-1 transition-colors">
                ← Privacy Data Rights Portal
            </a>
        </div>

        <!-- Hero Headline -->
        <div class="mb-10">
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-gray-900 dark:text-white tracking-tight leading-tight mb-3">
                Request Account Deletion & <span class="text-rose-600 dark:text-rose-500">Data Purge</span>
            </h1>
            <p class="text-base sm:text-lg text-gray-600 dark:text-gray-400 max-w-3xl leading-relaxed">
                Whether you are a <strong>Customer (Rider)</strong>, <strong>Driver / Chauffeur</strong>, or <strong>Vehicle Host / Fleet Owner</strong>, you have the statutory right to permanently delete your RideMyCars account and associated personal data at any time.
            </p>
        </div>

        <!-- Success Alert Message -->
        @if(session('deletion_success'))
            <div class="mb-10 p-6 sm:p-8 rounded-3xl bg-emerald-50 dark:bg-emerald-950/40 border-2 border-emerald-500/40 shadow-xl space-y-4">
                <div class="flex items-center gap-3 text-emerald-800 dark:text-emerald-300">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-500 text-white flex items-center justify-center text-xl font-black shrink-0 shadow-md">
                        ✓
                    </div>
                    <div>
                        <h2 class="text-lg sm:text-xl font-black">Account Deletion Successfully Processed</h2>
                        <p class="text-xs sm:text-sm text-emerald-700 dark:text-emerald-400 mt-0.5">Reference Code: <span class="font-mono font-black text-emerald-900 dark:text-emerald-200">{{ session('request_code') }}</span></p>
                    </div>
                </div>
                <div class="p-4 rounded-2xl bg-white/80 dark:bg-black/40 border border-emerald-500/20 text-xs sm:text-sm text-gray-800 dark:text-gray-200 leading-relaxed space-y-2">
                    <p>
                        All user profiles, authentication tokens, credentials, and associated records linked to <strong>{{ session('deleted_identifier') }}</strong> have been permanently deleted and logged under statutory data erasure compliance.
                    </p>
                    @if(session('deleted_count') > 0)
                        <p class="font-bold text-emerald-700 dark:text-emerald-300">
                            {{ session('deleted_count') }} user account(s) and associated profiles were completely purged from our active databases.
                        </p>
                    @else
                        <p class="text-gray-500 dark:text-gray-400">
                            Your deletion request has been formally logged. If any record matching this identifier existed, it has been expunged.
                        </p>
                    @endif
                </div>
                <div class="pt-2 flex items-center gap-4">
                    <a href="/" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs transition-colors shadow-md">
                        Return to Homepage
                    </a>
                    <a href="/privacy-policy" class="text-xs font-bold text-emerald-700 dark:text-emerald-300 hover:underline">
                        Read Data Retention Policies →
                    </a>
                </div>
            </div>
        @endif

        <!-- Error Summary -->
        @if($errors->any())
            <div class="mb-8 p-5 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-500/30 text-rose-800 dark:text-rose-300 text-xs sm:text-sm space-y-1.5 shadow-sm">
                <div class="flex items-center gap-2 font-black text-rose-700 dark:text-rose-300">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Please correct the following errors before submitting:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 pl-2 font-medium">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left 7 cols: Main Form -->
            <div class="lg:col-span-7 bg-white dark:bg-[#111] rounded-3xl border-2 border-gray-200 dark:border-white/10 p-6 sm:p-8 shadow-xl relative">
                
                <div class="mb-6 pb-6 border-b border-gray-100 dark:border-white/5">
                    <h2 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight">Account Deletion Request Form</h2>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">Enter your registered email address or mobile number. All account tiers (customer, driver, or vehicle host) are eligible.</p>
                </div>

                <form action="/delete-account" method="POST" class="space-y-6" onsubmit="return confirm('⚠️ WARNING: Are you completely sure you want to permanently delete your RideMyCars account and associated data? This action CANNOT be undone.');">
                    @csrf

                    <!-- Logged In Helper Indicator -->
                    @if(Auth::check())
                        <div class="p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-500/25 flex items-center justify-between text-xs text-amber-800 dark:text-amber-300">
                            <span class="flex items-center gap-2">
                                <span>👤</span>
                                <span>Currently signed in as: <strong>{{ Auth::user()->name }}</strong> ({{ Auth::user()->email }})</span>
                            </span>
                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-md bg-amber-500/20">{{ Auth::user()->role }}</span>
                        </div>
                    @endif

                    <!-- Identification Inputs (Email or Phone) -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                                Registered Email Address
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    ✉️
                                </span>
                                <input type="email" 
                                       name="email" 
                                       value="{{ old('email', Auth::user()->email ?? '') }}" 
                                       placeholder="name@example.com" 
                                       class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-xs sm:text-sm font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-rose-500 outline-none transition-all">
                            </div>
                            <span class="text-[11px] text-gray-400 mt-1 block">Provide the email address you used when signing up.</span>
                        </div>

                        <div class="relative flex items-center justify-center my-2">
                            <div class="border-t border-gray-200 dark:border-white/10 w-full"></div>
                            <span class="bg-white dark:bg-[#111] px-3 text-[11px] font-black text-gray-400 uppercase tracking-widest absolute">AND / OR</span>
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                                Registered Mobile Number
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    📱
                                </span>
                                <input type="text" 
                                       name="phone" 
                                       value="{{ old('phone', Auth::user()->phone ?? '') }}" 
                                       placeholder="+1 888 570 0008 or mobile number" 
                                       class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-xs sm:text-sm font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-rose-500 outline-none transition-all">
                            </div>
                            <span class="text-[11px] text-gray-400 mt-1 block">Include country code if known (e.g. +1, +27, +233) or national digits.</span>
                        </div>
                    </div>

                    <!-- Role Selector -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">
                            Account Type to Remove
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                            <label class="cursor-pointer border border-gray-200 dark:border-white/10 rounded-2xl p-3 text-center text-xs font-bold has-[:checked]:bg-rose-500 has-[:checked]:text-white has-[:checked]:border-rose-600 transition-all">
                                <input type="radio" name="role" value="all" {{ old('role', 'all') === 'all' ? 'checked' : '' }} class="sr-only">
                                <span>🌐 All Associated</span>
                            </label>
                            <label class="cursor-pointer border border-gray-200 dark:border-white/10 rounded-2xl p-3 text-center text-xs font-bold has-[:checked]:bg-rose-500 has-[:checked]:text-white has-[:checked]:border-rose-600 transition-all">
                                <input type="radio" name="role" value="customer" {{ old('role') === 'customer' ? 'checked' : '' }} class="sr-only">
                                <span>🚗 Rider</span>
                            </label>
                            <label class="cursor-pointer border border-gray-200 dark:border-white/10 rounded-2xl p-3 text-center text-xs font-bold has-[:checked]:bg-rose-500 has-[:checked]:text-white has-[:checked]:border-rose-600 transition-all">
                                <input type="radio" name="role" value="driver" {{ old('role') === 'driver' ? 'checked' : '' }} class="sr-only">
                                <span>👨‍✈️ Driver</span>
                            </label>
                            <label class="cursor-pointer border border-gray-200 dark:border-white/10 rounded-2xl p-3 text-center text-xs font-bold has-[:checked]:bg-rose-500 has-[:checked]:text-white has-[:checked]:border-rose-600 transition-all">
                                <input type="radio" name="role" value="owner" {{ old('role') === 'owner' ? 'checked' : '' }} class="sr-only">
                                <span>🔑 Vehicle Host</span>
                            </label>
                        </div>
                    </div>

                    <!-- Reason for Deletion -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                            Reason for Account Deletion *
                        </label>
                        <select name="reason" required class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-xs sm:text-sm font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-rose-500 outline-none cursor-pointer">
                            <option value="">Please select a reason...</option>
                            <option value="I no longer need this mobility service" {{ old('reason') === 'I no longer need this mobility service' ? 'selected' : '' }}>I no longer need this mobility service</option>
                            <option value="Privacy or personal data security concerns" {{ old('reason') === 'Privacy or personal data security concerns' ? 'selected' : '' }}>Privacy or personal data security concerns</option>
                            <option value="Switching to an alternative platform" {{ old('reason') === 'Switching to an alternative platform' ? 'selected' : '' }}>Switching to an alternative platform</option>
                            <option value="Created duplicate or unwanted account" {{ old('reason') === 'Created duplicate or unwanted account' ? 'selected' : '' }}>Created duplicate or unwanted account</option>
                            <option value="Temporary leave or financial reasons" {{ old('reason') === 'Temporary leave or financial reasons' ? 'selected' : '' }}>Temporary leave or financial reasons</option>
                            <option value="Unsatisfied with pricing or app performance" {{ old('reason') === 'Unsatisfied with pricing or app performance' ? 'selected' : '' }}>Unsatisfied with pricing or app performance</option>
                            <option value="Other reason" {{ old('reason') === 'Other reason' ? 'selected' : '' }}>Other reason</option>
                        </select>
                    </div>

                    <!-- Additional Feedback -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                            Additional Feedback or Explanation (Optional)
                        </label>
                        <textarea name="comments" rows="3" placeholder="Tell us how we could have improved your experience..." class="w-full px-4 py-3 bg-gray-50 dark:bg-[#1a1a1a] border border-gray-200 dark:border-white/10 rounded-2xl text-xs font-medium text-gray-900 dark:text-white focus:ring-2 focus:ring-rose-500 outline-none resize-none">{{ old('comments') }}</textarea>
                    </div>

                    <!-- Critical Warning & Confirmation Box -->
                    <div class="p-4 sm:p-5 rounded-2xl bg-rose-500/10 border-2 border-rose-500/30 space-y-3">
                        <div class="flex items-center gap-2 text-rose-700 dark:text-rose-400 font-black text-xs uppercase tracking-wider">
                            <span class="text-base">⚠️</span> Irreversible Action Acknowledgment
                        </div>
                        <p class="text-xs text-rose-900/80 dark:text-rose-200/80 leading-relaxed font-medium">
                            Once your account is deleted, all active trips, chauffeur bookings, vehicle listings, uploaded driver credentials, and loyalty records are irrevocably expunged from RideMyCars. You will not be able to recover this account.
                        </p>
                        <label class="flex items-start gap-3 cursor-pointer pt-1">
                            <input type="checkbox" name="confirm_deletion" value="1" required class="mt-1 w-4 h-4 text-rose-600 rounded border-gray-300 focus:ring-rose-500">
                            <span class="text-xs font-bold text-gray-900 dark:text-white">
                                I confirm that I wish to permanently delete my RideMyCars account and purge all associated personal data.
                            </span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full py-4 px-6 rounded-2xl bg-rose-600 hover:bg-rose-700 active:scale-[0.99] text-white font-black text-sm uppercase tracking-wider shadow-lg shadow-rose-600/30 transition-all flex items-center justify-center gap-2">
                        <span>🗑️</span>
                        <span>Permanently Delete My Account</span>
                    </button>
                    
                    <p class="text-[11px] text-gray-400 text-center">
                        Need temporary deactivation instead? Contact our support team at <a href="mailto:support@ridemycars.com" class="text-amber-500 hover:underline font-bold">support@ridemycars.com</a>.
                    </p>
                </form>

            </div>

            <!-- Right 5 cols: FAQ & Policies -->
            <div class="lg:col-span-5 space-y-6">
                
                <!-- What gets deleted card -->
                <div class="bg-white dark:bg-[#141414] rounded-3xl border border-gray-200 dark:border-white/10 p-6 shadow-md space-y-4">
                    <div class="flex items-center gap-2 text-sm font-black text-gray-900 dark:text-white">
                        <span class="w-8 h-8 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center font-bold">✕</span>
                        <span>What Data is Deleted?</span>
                    </div>
                    <ul class="space-y-2.5 text-xs text-gray-600 dark:text-gray-400">
                        <li class="flex items-start gap-2">
                            <span class="text-rose-500 font-bold shrink-0">•</span>
                            <span><strong>Profile & Credentials:</strong> Name, email, phone number, password, profile photo, and OAuth links.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-rose-500 font-bold shrink-0">•</span>
                            <span><strong>Driver Profiles:</strong> Background check documents, driver licenses, ratings, and experience bio.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-rose-500 font-bold shrink-0">•</span>
                            <span><strong>Host Fleet Listings:</strong> All vehicle records, unbooked calendars, and host profiles.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-rose-500 font-bold shrink-0">•</span>
                            <span><strong>Payment Profiles:</strong> Saved Stripe cards, payment methods, and mobile money tokens.</span>
                        </li>
                    </ul>
                </div>

                <!-- Statutory retention card -->
                <div class="bg-white dark:bg-[#141414] rounded-3xl border border-gray-200 dark:border-white/10 p-6 shadow-md space-y-3">
                    <div class="flex items-center gap-2 text-sm font-black text-gray-900 dark:text-white">
                        <span class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold">⚖️</span>
                        <span>Statutory & Tax Retention</span>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                        In accordance with applicable corporate tax, road transport safety, and financial fraud prevention laws, historical financial invoices from completed trips may be retained for statutory audit periods with personal identifiers pseudonymized.
                    </p>
                </div>

                <!-- App Store Compliance card -->
                <div class="bg-white dark:bg-[#141414] rounded-3xl border border-gray-200 dark:border-white/10 p-6 shadow-md space-y-3">
                    <div class="flex items-center gap-2 text-sm font-black text-gray-900 dark:text-white">
                        <span class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">📱</span>
                        <span>App Store Compliance Notice</span>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                        This URL satisfies the account deletion requirements mandated by <strong>Apple App Store Guideline 5.1.1(v)</strong> and <strong>Google Play Data Safety Policy</strong>. Mobile app users can initiate complete account removal without requiring app reinstallation.
                    </p>
                    <div class="pt-2 border-t border-gray-100 dark:border-white/5 flex items-center justify-between text-xs">
                        <a href="/privacy-policy" class="font-bold text-amber-600 dark:text-amber-400 hover:underline">
                            Privacy Policy
                        </a>
                        <a href="/terms-and-conditions" class="font-bold text-gray-500 hover:underline">
                            Terms & Conditions
                        </a>
                    </div>
                </div>

                <!-- Direct DPO Contact card -->
                <div class="bg-gray-50 dark:bg-white/[0.02] rounded-3xl border border-gray-200 dark:border-white/10 p-5 text-center space-y-2">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 block">Questions about your data?</span>
                    <a href="mailto:privacy@ridemycars.com" class="text-xs font-black text-gray-900 dark:text-white hover:text-amber-500 transition-colors inline-flex items-center gap-1.5">
                        <span>🛡️ Contact Data Protection Officer</span>
                    </a>
                </div>

            </div>

        </div>

    </main>
</x-layout>
