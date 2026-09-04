<x-layout>
    <x-slot:title>Sign In — RideMyCars</x-slot>

    <style>
        .country-scroll::-webkit-scrollbar { width: 6px; }
        .country-scroll::-webkit-scrollbar-track { background: #f8fafc; border-radius: 8px; }
        .dark .country-scroll::-webkit-scrollbar-track { background: #1a1a1a; }
        .country-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }
        .dark .country-scroll::-webkit-scrollbar-thumb { background: #374151; }
        .country-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>

    <main class="flex-1 flex flex-col items-center justify-center py-12 lg:py-20 px-4 sm:px-6 bg-[#fafafa] dark:bg-[#0a0a0a] min-h-[calc(100vh-80px)] transition-colors duration-200" x-data="loginApp()">
        <div class="w-full max-w-[440px] bg-white dark:bg-[#141414] rounded-3xl p-7 sm:p-9 shadow-xl border border-gray-200/80 dark:border-white/10 transition-colors">
            
            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800/40 text-red-700 dark:text-red-400 text-sm font-semibold flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- 1. Mobile Number View -->
            <div x-show="view === 'mobile'" x-transition.opacity.duration.300ms>
                <h1 class="text-2xl sm:text-3xl font-black mb-2 text-gray-900 dark:text-white tracking-tight">Enter your mobile number</h1>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mb-6 font-medium">We'll send you a 4-digit code to verify your account.</p>
                
                <form action="#" method="GET" @submit.prevent="emailForOtp = selectedCountry.dial + ' ' + mobileNumber; view = 'otp';">
                    <div class="relative mb-5" @click.away="countryDropdownOpen = false">
                        <!-- Main Phone Input Bar -->
                        <div class="flex items-center h-[54px] bg-[#f3f4f6] dark:bg-white/5 rounded-xl border border-gray-200 dark:border-white/10 focus-within:border-black dark:focus-within:border-brand-500 focus-within:bg-white dark:focus-within:bg-[#121212] focus-within:ring-2 focus-within:ring-black/10 dark:focus-within:ring-brand-500/20 transition-all duration-200 shadow-sm overflow-hidden">
                            <!-- Country Code Trigger Button -->
                            <button type="button" 
                                    @click="countryDropdownOpen = !countryDropdownOpen; if(countryDropdownOpen) $nextTick(() => $refs.countrySearchInput?.focus())"
                                    class="flex items-center gap-2 h-full px-3.5 bg-[#f3f4f6] hover:bg-[#e5e7eb] dark:bg-white/5 dark:hover:bg-white/10 border-r border-gray-300/80 dark:border-white/10 transition-colors shrink-0 cursor-pointer select-none">
                                <span class="text-2xl leading-none" x-text="selectedCountry.flag">🇺🇸</span>
                                <span class="text-sm font-bold text-gray-900 dark:text-white tracking-tight" x-text="selectedCountry.dial">+1</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500 dark:text-gray-400 transition-transform duration-200 shrink-0" :class="countryDropdownOpen ? 'rotate-180 text-black dark:text-white' : ''" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            <!-- Mobile Input -->
                            <input type="tel" 
                                   x-model="mobileNumber"
                                   :placeholder="`${selectedCountry.dial} Mobile number`"
                                   required
                                   class="flex-1 h-full bg-transparent px-4 text-gray-900 dark:text-white placeholder-gray-400 font-medium focus:outline-none border-none text-base">
                        </div>

                        <!-- Country Dropdown Floating Card -->
                        <div x-show="countryDropdownOpen" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2 scale-98"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-y-2 scale-98"
                             class="absolute left-0 right-0 top-full mt-2 bg-white dark:bg-[#181818] rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.18)] border border-gray-200 dark:border-white/15 z-50 overflow-hidden ring-1 ring-black/5"
                             style="display: none;">
                            
                            <!-- Search Bar Header -->
                            <div class="p-3 bg-gray-50/90 dark:bg-white/5 border-b border-gray-100 dark:border-white/10 sticky top-0 z-10 backdrop-blur-sm">
                                <div class="relative flex items-center bg-white dark:bg-[#121212] rounded-xl border border-gray-200 dark:border-white/10 focus-within:border-black dark:focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-black/10 dark:focus-within:ring-brand-500/20 transition-all shadow-inner px-3 py-2">
                                    <svg class="w-4 h-4 text-gray-400 shrink-0 mr-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    <input type="text" 
                                           x-ref="countrySearchInput"
                                           x-model="countrySearch" 
                                           placeholder="Search country or code (e.g. +1, USA)..." 
                                           class="w-full bg-transparent text-xs font-semibold text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none border-none p-0">
                                    <button type="button" 
                                            x-show="countrySearch" 
                                            @click="countrySearch = ''; $refs.countrySearchInput?.focus()" 
                                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 shrink-0 ml-1 p-0.5"
                                            style="display: none;">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Country List -->
                            <div class="max-h-64 sm:max-h-72 overflow-y-auto country-scroll p-1.5 space-y-0.5 text-sm">
                                <template x-for="country in filteredCountries" :key="country.code + country.dial">
                                    <button type="button" 
                                            @click="selectCountry(country)"
                                            class="w-full px-3 py-2 rounded-xl flex items-center justify-between hover:bg-gray-100/90 dark:hover:bg-white/10 active:bg-gray-200/80 transition-all text-left group cursor-pointer"
                                            :class="selectedCountry.code === country.code ? 'bg-black dark:bg-brand-500 text-white dark:text-black hover:bg-gray-900 dark:hover:bg-brand-400' : 'text-gray-800 dark:text-gray-200'">
                                        <div class="flex items-center gap-3 min-w-0 pr-2">
                                            <span class="text-xl leading-none shrink-0" x-text="country.flag"></span>
                                            <span class="text-xs font-semibold truncate" 
                                                  :class="selectedCountry.code === country.code ? 'text-white dark:text-black' : 'text-gray-900 dark:text-gray-100 group-hover:text-black dark:group-hover:text-white'" 
                                                  x-text="country.name"></span>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-md" 
                                                  :class="selectedCountry.code === country.code ? 'bg-white/20 dark:bg-black/20 text-white dark:text-black' : 'bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-gray-400 group-hover:text-black dark:group-hover:text-white group-hover:bg-gray-200'" 
                                                  x-text="country.dial"></span>
                                            <span x-show="selectedCountry.code === country.code" class="text-xs font-bold text-white dark:text-black">✓</span>
                                        </div>
                                    </button>
                                </template>
                                
                                <div x-show="filteredCountries.length === 0" class="py-8 text-center" style="display: none;">
                                    <div class="text-2xl mb-1">🌍</div>
                                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">No countries found</p>
                                    <p class="text-[11px] text-gray-400 mt-0.5">Try searching with a different name or dial code</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-black hover:bg-gray-900 dark:bg-brand-500 dark:hover:bg-brand-400 text-white dark:text-black font-extrabold py-3.5 rounded-xl text-base transition-all shadow-md shadow-black/10 dark:shadow-brand-500/20 active:scale-[0.99] cursor-pointer">
                        Continue
                    </button>
                </form>

                <div class="flex items-center my-6">
                    <div class="flex-1 border-t border-gray-200 dark:border-white/10"></div>
                    <span class="px-4 text-xs uppercase tracking-wider text-gray-400 dark:text-gray-500 font-bold">or</span>
                    <div class="flex-1 border-t border-gray-200 dark:border-white/10"></div>
                </div>

                <div class="space-y-3">
                    <button type="button" class="w-full flex items-center justify-center gap-3 py-3.5 bg-gray-100 hover:bg-gray-200 dark:bg-white/5 dark:hover:bg-white/10 text-gray-900 dark:text-white font-semibold rounded-xl border border-transparent dark:border-white/5 transition-colors">
                        <svg viewBox="0 0 24 24" width="20" height="20"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/><path d="M1 1h22v22H1z" fill="none"/></svg>
                        <span>Continue with Google</span>
                    </button>
                    <button type="button" class="w-full flex items-center justify-center gap-3 py-3.5 bg-gray-100 hover:bg-gray-200 dark:bg-white/5 dark:hover:bg-white/10 text-gray-900 dark:text-white font-semibold rounded-xl border border-transparent dark:border-white/5 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 32 32" fill="currentColor"><path d="M16 0C7.164 0 0 7.164 0 16s7.164 16 16 16 16-7.164 16-16S24.836 0 16 0zm-1.844 11.238c-.027-2.586 2.113-4.992 4.922-5.184 1.14.04 2.22.56 2.97 1.43.05.07.09.13.12.2.02.06.05.12.06.18.39 1.13.41 2.39-.02 3.48-.9 2.18-3.08 3.52-5.46 3.36a3.81 3.81 0 0 1-2.59-3.466zM22.06 25c-1.398 2.016-2.906 4.02-5.32 4.06-2.355.04-3.11-1.395-5.836-1.395-2.723 0-3.586 1.356-5.82 1.43-2.375.078-4.105-2.227-5.52-4.266C6.676 20.672 5.094 15.684 6.55 12.23c.723-1.71 2.375-2.82 4.22-2.85 2.26-.04 4.39 1.53 5.86 1.53 1.47 0 4.05-1.92 6.78-1.64 1.15.12 3.31.59 4.7 2.65-.13.09-2.81 1.63-2.77 4.87.04 3.91 3.34 5.2 3.42 5.24-.03.09-1.52 5.3-6.7 5.97z"/></svg>
                        <span>Continue with Apple</span>
                    </button>
                    <button type="button" @click="view = 'email'" class="w-full flex items-center justify-center gap-3 py-3.5 bg-gray-100 hover:bg-gray-200 dark:bg-white/5 dark:hover:bg-white/10 text-gray-900 dark:text-white font-semibold rounded-xl border border-transparent dark:border-white/5 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        <span>Continue with email</span>
                    </button>
                </div>

                <div class="flex items-center my-6">
                    <div class="flex-1 border-t border-gray-200 dark:border-white/10"></div>
                    <span class="px-4 text-xs uppercase tracking-wider text-gray-400 dark:text-gray-500 font-bold">or</span>
                    <div class="flex-1 border-t border-gray-200 dark:border-white/10"></div>
                </div>

                <button type="button" class="w-full flex items-center justify-center gap-3 py-3.5 bg-gray-100 hover:bg-gray-200 dark:bg-white/5 dark:hover:bg-white/10 text-gray-900 dark:text-white font-semibold rounded-xl border border-transparent dark:border-white/5 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/><path d="M21 16h-3a2 2 0 0 0-2 2v3"/><path d="M21 21v.01"/><path d="M12 7v3a2 2 0 0 1-2 2H7"/><path d="M3 12h.01"/><path d="M12 3h.01"/><path d="M12 16v.01"/><path d="M16 12h1"/><path d="M21 12v.01"/><path d="M12 21v-1"/></svg>
                    <span>Log in with QR code</span>
                </button>

                <p class="text-xs text-gray-500 dark:text-gray-400 mt-8 leading-relaxed text-center">
                    You consent to receive a verification code by text or WhatsApp. Message and data rates may apply.
                </p>
            </div>

            <!-- 2. OTP Verification View -->
            <div x-show="view === 'otp'" x-transition.opacity.duration.300ms style="display: none;">
                <h1 class="text-2xl sm:text-3xl font-black mb-2 text-gray-900 dark:text-white tracking-tight">Enter your code</h1>
                
                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed mb-4">
                    Enter the 4-digit code sent to <span class="font-bold text-gray-900 dark:text-white" x-text="emailForOtp"></span>.
                </p>

                <button type="button" @click="view = 'email_otp'" class="text-xs text-amber-600 dark:text-amber-400 font-bold underline underline-offset-4 mb-8 hover:opacity-80 transition-opacity block">
                    Changed your email or phone?
                </button>

                <div class="flex items-center justify-center gap-3 mb-2">
                    <input type="text" maxlength="1" x-model="c1" @input="$event.target.value ? $refs.c2.focus() : null" class="w-12 h-14 bg-gray-100 dark:bg-white/5 rounded-xl text-center text-xl font-bold text-gray-900 dark:text-white border-2 border-transparent focus:border-black dark:focus:border-brand-500 focus:bg-white dark:focus:bg-[#121212] transition-all">
                    <input type="text" maxlength="1" x-model="c2" x-ref="c2" @input="$event.target.value ? $refs.c3.focus() : $refs.c1.focus()" class="w-12 h-14 bg-gray-100 dark:bg-white/5 rounded-xl text-center text-xl font-bold text-gray-900 dark:text-white border-2 border-transparent focus:border-black dark:focus:border-brand-500 focus:bg-white dark:focus:bg-[#121212] transition-all">
                    <input type="text" maxlength="1" x-model="c3" x-ref="c3" @input="$event.target.value ? $refs.c4.focus() : $refs.c2.focus()" class="w-12 h-14 bg-gray-100 dark:bg-white/5 rounded-xl text-center text-xl font-bold text-gray-900 dark:text-white border-2 border-transparent focus:border-black dark:focus:border-brand-500 focus:bg-white dark:focus:bg-[#121212] transition-all">
                    <input type="text" maxlength="1" x-model="c4" x-ref="c4" @input="!$event.target.value ? $refs.c3.focus() : null" class="w-12 h-14 bg-gray-100 dark:bg-white/5 rounded-xl text-center text-xl font-bold text-gray-900 dark:text-white border-2 border-transparent focus:border-black dark:focus:border-brand-500 focus:bg-white dark:focus:bg-[#121212] transition-all">
                </div>
                <p class="text-red-500 text-sm font-semibold mb-6 h-5 text-center" x-text="otpError"></p>

                <!-- Footer Navigation -->
                <div class="flex items-center justify-between mt-8 pt-4 border-t border-gray-100 dark:border-white/10">
                    <button type="button" @click="view = 'mobile'" class="w-12 h-12 flex items-center justify-center bg-gray-100 hover:bg-gray-200 dark:bg-white/5 dark:hover:bg-white/10 rounded-full transition-colors text-gray-900 dark:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    </button>
                    
                    <button type="button" 
                            @click="
                                if(c1 && c2 && c3 && c4) {
                                    isLoading = true;
                                    otpError = '';
                                    const csrfToken = '{{ csrf_token() }}';
                                    fetch('/api/otp/verify', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                                        body: JSON.stringify({ email: emailForOtp, otp: c1+c2+c3+c4 })
                                    })
                                    .then(res => res.json())
                                    .then(data => {
                                        isLoading = false;
                                        if (data.error) {
                                            otpError = data.error;
                                        } else {
                                            window.location.href = data.redirect || '/';
                                        }
                                    })
                                    .catch(() => { isLoading = false; otpError = 'Network error. Please try again.'; });
                                }
                            "
                            class="h-12 px-6 rounded-xl font-bold flex items-center gap-2 transition-all"
                            :class="(c1 && c2 && c3 && c4 && !isLoading) ? 'bg-black hover:bg-gray-900 dark:bg-brand-500 dark:hover:bg-brand-400 text-white dark:text-black cursor-pointer shadow-md' : 'bg-gray-100 dark:bg-white/5 text-gray-400 cursor-not-allowed'"
                            :disabled="isLoading || !(c1 && c2 && c3 && c4)">
                        <span x-text="isLoading ? 'Verifying...' : 'Verify'"></span>
                        <svg x-show="!isLoading" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                </div>
            </div>
            
            <!-- 3. Email OTP Request View -->
            <div x-show="view === 'email_otp'" x-transition.opacity.duration.300ms style="display: none;">
                <h1 class="text-2xl sm:text-3xl font-black mb-2 text-gray-900 dark:text-white tracking-tight">Enter your email</h1>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mb-6 font-medium">We'll send a 4-digit verification code to your email.</p>
                
                <form @submit.prevent="
                    isLoading = true;
                    otpError = '';
                    const csrfToken = '{{ csrf_token() }}';
                    fetch('/api/otp/send', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ email: emailForOtp })
                    })
                    .then(res => res.json())
                    .then(data => {
                        isLoading = false;
                        if(data.mail_error) {
                            otpError = 'Email failed: ' + data.mail_error + '. Your code is: ' + data.debug_otp;
                            view = 'otp';
                            c1 = c2 = c3 = c4 = '';
                        } else {
                            view = 'otp';
                            c1 = c2 = c3 = c4 = '';
                        }
                    })
                    .catch(() => { isLoading = false; otpError = 'Network error.'; });
                " class="space-y-4">
                    <div>
                        <input type="email" x-model="emailForOtp" required placeholder="you@example.com" class="w-full bg-gray-100 dark:bg-white/5 rounded-xl px-4 py-3.5 text-gray-900 dark:text-white placeholder-gray-400 font-medium focus:outline-none focus:ring-2 focus:ring-black dark:focus:ring-brand-500 border border-transparent dark:border-white/10 text-base">
                        <p class="text-red-500 text-sm font-semibold mt-1" x-text="otpError"></p>
                    </div>
                    
                    <button type="submit" :disabled="isLoading" class="w-full bg-black hover:bg-gray-900 dark:bg-brand-500 dark:hover:bg-brand-400 text-white dark:text-black font-extrabold py-3.5 rounded-xl text-base transition-all mt-2" :class="isLoading ? 'opacity-70 cursor-wait' : ''">
                        <span x-text="isLoading ? 'Sending code...' : 'Continue'"></span>
                    </button>
                </form>

                <div class="mt-6 text-center space-y-2">
                    <button type="button" @click="view = 'email'" class="text-xs sm:text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-black dark:hover:text-white transition-colors block w-full py-1">
                        Or login with password instead
                    </button>
                    <button type="button" @click="view = 'mobile'" class="text-xs sm:text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-black dark:hover:text-white transition-colors block w-full py-1">
                        ← Back to mobile number
                    </button>
                </div>
            </div>

            <!-- 4. Email & Password Login View -->
            <div x-show="view === 'email'" x-transition.opacity.duration.300ms style="display: none;">
                <h1 class="text-2xl sm:text-3xl font-black mb-2 text-gray-900 dark:text-white tracking-tight">Sign in with email</h1>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mb-6 font-medium">Use your registered email address and password.</p>
                
                <form action="/login" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Email Address</label>
                        <input type="email" name="email" required value="{{ old('email') }}" placeholder="you@example.com" class="w-full bg-gray-100 dark:bg-white/5 rounded-xl px-4 py-3.5 text-gray-900 dark:text-white placeholder-gray-400 font-medium focus:outline-none focus:ring-2 focus:ring-black dark:focus:ring-brand-500 border border-transparent dark:border-white/10 text-base">
                        @error('email')
                            <p class="text-red-600 text-sm mt-1.5 font-semibold flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Password</label>
                        <div x-data="{ show: false }" class="relative">
                            <input :type="show ? 'text' : 'password'" name="password" required placeholder="Enter your password" class="w-full bg-gray-100 dark:bg-white/5 rounded-xl px-4 py-3.5 pr-12 text-gray-900 dark:text-white placeholder-gray-400 font-medium focus:outline-none focus:ring-2 focus:ring-black dark:focus:ring-brand-500 border border-transparent dark:border-white/10 text-base">
                            <div @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-700 dark:hover:text-white cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </div>
                        </div>
                        @error('password')
                            <p class="text-red-600 text-sm mt-1.5 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="w-full bg-black hover:bg-gray-900 dark:bg-brand-500 dark:hover:bg-brand-400 text-white dark:text-black font-extrabold py-3.5 rounded-xl text-base transition-all shadow-md shadow-black/10 dark:shadow-brand-500/20 active:scale-[0.99] cursor-pointer mt-2">
                        Sign In
                    </button>
                </form>

                <div class="mt-6 text-center space-y-2">
                    <button type="button" @click="view = 'email_otp'" class="text-xs sm:text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-black dark:hover:text-white transition-colors block w-full py-1">
                        Or log in with a 4-digit code (OTP) instead
                    </button>
                    <button type="button" @click="view = 'mobile'" class="text-xs sm:text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-black dark:hover:text-white transition-colors block w-full py-1">
                        ← Back to mobile number
                    </button>
                </div>
            </div>

        </div>
    </main>

    <script>
        function initLoginApp() {
            if (window.Alpine && !window.__loginAppRegistered) {
                window.__loginAppRegistered = true;
                Alpine.data('loginApp', () => ({
                    view: '{{ $errors->any() || old("email") ? "email" : "mobile" }}',
                    emailForOtp: '',
                    otpError: '',
                    isLoading: false,
                    c1: '', c2: '', c3: '', c4: '',
                    
                    // Country Code Selector
                    countryDropdownOpen: false,
                    countrySearch: '',
                    mobileNumber: '',
                    selectedCountry: { name: 'United States', code: 'US', dial: '+1', flag: '🇺🇸' },
                    
                    countries: [
                        { name: 'United States', code: 'US', dial: '+1', flag: '🇺🇸' },
                    { name: 'South Africa', code: 'ZA', dial: '+27', flag: '🇿🇦' },
                    { name: 'Ghana', code: 'GH', dial: '+233', flag: '🇬🇭' },
                    { name: 'United Kingdom', code: 'GB', dial: '+44', flag: '🇬🇧' },
                    { name: 'Canada', code: 'CA', dial: '+1', flag: '🇨🇦' },
                    { name: 'India', code: 'IN', dial: '+91', flag: '🇮🇳' },
                    { name: 'Nigeria', code: 'NG', dial: '+234', flag: '🇳🇬' },
                    { name: 'Kenya', code: 'KE', dial: '+254', flag: '🇰🇪' },
                    { name: 'United Arab Emirates', code: 'AE', dial: '+971', flag: '🇦🇪' },
                    { name: 'Australia', code: 'AU', dial: '+61', flag: '🇦🇺' },
                    { name: 'Germany', code: 'DE', dial: '+49', flag: '🇩🇪' },
                    { name: 'France', code: 'FR', dial: '+33', flag: '🇫🇷' },
                    { name: 'Afghanistan', code: 'AF', dial: '+93', flag: '🇦🇫' },
                    { name: 'Albania', code: 'AL', dial: '+355', flag: '🇦🇱' },
                    { name: 'Algeria', code: 'DZ', dial: '+213', flag: '🇩🇿' },
                    { name: 'Andorra', code: 'AD', dial: '+376', flag: '🇦🇩' },
                    { name: 'Angola', code: 'AO', dial: '+244', flag: '🇦🇴' },
                    { name: 'Antigua & Barbuda', code: 'AG', dial: '+1268', flag: '🇦🇬' },
                    { name: 'Argentina', code: 'AR', dial: '+54', flag: '🇦🇷' },
                    { name: 'Armenia', code: 'AM', dial: '+374', flag: '🇦🇲' },
                    { name: 'Austria', code: 'AT', dial: '+43', flag: '🇦🇹' },
                    { name: 'Azerbaijan', code: 'AZ', dial: '+994', flag: '🇦🇿' },
                    { name: 'Bahamas', code: 'BS', dial: '+1242', flag: '🇧🇸' },
                    { name: 'Bahrain', code: 'BH', dial: '+973', flag: '🇧🇭' },
                    { name: 'Bangladesh', code: 'BD', dial: '+880', flag: '🇧🇩' },
                    { name: 'Barbados', code: 'BB', dial: '+1246', flag: '🇧🇧' },
                    { name: 'Belarus', code: 'BY', dial: '+375', flag: '🇧🇾' },
                    { name: 'Belgium', code: 'BE', dial: '+32', flag: '🇧🇪' },
                    { name: 'Belize', code: 'BZ', dial: '+501', flag: '🇧🇿' },
                    { name: 'Benin', code: 'BJ', dial: '+229', flag: '🇧🇯' },
                    { name: 'Bermuda', code: 'BM', dial: '+1441', flag: '🇧🇲' },
                    { name: 'Bolivia', code: 'BO', dial: '+591', flag: '🇧🇴' },
                    { name: 'Bosnia & Herzegovina', code: 'BA', dial: '+387', flag: '🇧🇦' },
                    { name: 'Botswana', code: 'BW', dial: '+267', flag: '🇧🇼' },
                    { name: 'Brazil', code: 'BR', dial: '+55', flag: '🇧🇷' },
                    { name: 'Brunei', code: 'BN', dial: '+673', flag: '🇧🇳' },
                    { name: 'Bulgaria', code: 'BG', dial: '+359', flag: '🇧🇬' },
                    { name: 'Burkina Faso', code: 'BF', dial: '+226', flag: '🇧🇫' },
                    { name: 'Cambodia', code: 'KH', dial: '+855', flag: '🇰🇭' },
                    { name: 'Cameroon', code: 'CM', dial: '+237', flag: '🇨🇲' },
                    { name: 'Chile', code: 'CL', dial: '+56', flag: '🇨🇱' },
                    { name: 'China', code: 'CN', dial: '+86', flag: '🇨🇳' },
                    { name: 'Colombia', code: 'CO', dial: '+57', flag: '🇨🇴' },
                    { name: 'Costa Rica', code: 'CR', dial: '+506', flag: '🇨🇷' },
                    { name: 'Croatia', code: 'HR', dial: '+385', flag: '🇭🇷' },
                    { name: 'Cyprus', code: 'CY', dial: '+357', flag: '🇨🇾' },
                    { name: 'Czech Republic', code: 'CZ', dial: '+420', flag: '🇨🇿' },
                    { name: 'Denmark', code: 'DK', dial: '+45', flag: '🇩🇰' },
                    { name: 'Dominican Republic', code: 'DO', dial: '+1809', flag: '🇩🇴' },
                    { name: 'Ecuador', code: 'EC', dial: '+593', flag: '🇪🇨' },
                    { name: 'Egypt', code: 'EG', dial: '+20', flag: '🇪🇬' },
                    { name: 'El Salvador', code: 'SV', dial: '+503', flag: '🇸🇻' },
                    { name: 'Estonia', code: 'EE', dial: '+372', flag: '🇪🇪' },
                    { name: 'Ethiopia', code: 'ET', dial: '+251', flag: '🇪🇹' },
                    { name: 'Fiji', code: 'FJ', dial: '+679', flag: '🇫🇯' },
                    { name: 'Finland', code: 'FI', dial: '+358', flag: '🇫🇮' },
                    { name: 'Gabon', code: 'GA', dial: '+241', flag: '🇬🇦' },
                    { name: 'Gambia', code: 'GM', dial: '+220', flag: '🇬🇲' },
                    { name: 'Georgia', code: 'GE', dial: '+995', flag: '🇬🇪' },
                    { name: 'Greece', code: 'GR', dial: '+30', flag: '🇬🇷' },
                    { name: 'Guatemala', code: 'GT', dial: '+502', flag: '🇬🇹' },
                    { name: 'Guinea', code: 'GN', dial: '+224', flag: '🇬🇳' },
                    { name: 'Guyana', code: 'GY', dial: '+592', flag: '🇬🇾' },
                    { name: 'Honduras', code: 'HN', dial: '+504', flag: '🇭🇳' },
                    { name: 'Hong Kong', code: 'HK', dial: '+852', flag: '🇭🇰' },
                    { name: 'Hungary', code: 'HU', dial: '+36', flag: '🇭🇺' },
                    { name: 'Iceland', code: 'IS', dial: '+354', flag: '🇮🇸' },
                    { name: 'Indonesia', code: 'ID', dial: '+62', flag: '🇮🇩' },
                    { name: 'Ireland', code: 'IE', dial: '+353', flag: '🇮🇪' },
                    { name: 'Israel', code: 'IL', dial: '+972', flag: '🇮🇱' },
                    { name: 'Italy', code: 'IT', dial: '+39', flag: '🇮🇹' },
                    { name: 'Ivory Coast', code: 'CI', dial: '+225', flag: '🇨🇮' },
                    { name: 'Jamaica', code: 'JM', dial: '+1876', flag: '🇯🇲' },
                    { name: 'Japan', code: 'JP', dial: '+81', flag: '🇯🇵' },
                    { name: 'Jordan', code: 'JO', dial: '+962', flag: '🇯🇴' },
                    { name: 'Kazakhstan', code: 'KZ', dial: '+7', flag: '🇰🇿' },
                    { name: 'Kuwait', code: 'KW', dial: '+965', flag: '🇰🇼' },
                    { name: 'Lebanon', code: 'LB', dial: '+961', flag: '🇱🇧' },
                    { name: 'Liberia', code: 'LR', dial: '+231', flag: '🇱🇷' },
                    { name: 'Luxembourg', code: 'LU', dial: '+352', flag: '🇱🇺' },
                    { name: 'Malaysia', code: 'MY', dial: '+60', flag: '🇲🇾' },
                    { name: 'Maldives', code: 'MV', dial: '+960', flag: '🇲🇻' },
                    { name: 'Malta', code: 'MT', dial: '+356', flag: '🇲🇹' },
                    { name: 'Mauritius', code: 'MU', dial: '+230', flag: '🇲🇺' },
                    { name: 'Mexico', code: 'MX', dial: '+52', flag: '🇲🇽' },
                    { name: 'Monaco', code: 'MC', dial: '+377', flag: '🇲🇨' },
                    { name: 'Morocco', code: 'MA', dial: '+212', flag: '🇲🇦' },
                    { name: 'Mozambique', code: 'MZ', dial: '+258', flag: '🇲🇿' },
                    { name: 'Namibia', code: 'NA', dial: '+264', flag: '🇳🇦' },
                    { name: 'Nepal', code: 'NP', dial: '+977', flag: '🇳🇵' },
                    { name: 'Netherlands', code: 'NL', dial: '+31', flag: '🇳🇱' },
                    { name: 'New Zealand', code: 'NZ', dial: '+64', flag: '🇳🇿' },
                    { name: 'Nicaragua', code: 'NI', dial: '+505', flag: '🇳🇮' },
                    { name: 'Norway', code: 'NO', dial: '+47', flag: '🇳🇴' },
                    { name: 'Oman', code: 'OM', dial: '+968', flag: '🇴🇲' },
                    { name: 'Pakistan', code: 'PK', dial: '+92', flag: '🇵🇰' },
                    { name: 'Panama', code: 'PA', dial: '+507', flag: '🇵🇦' },
                    { name: 'Paraguay', code: 'PY', dial: '+595', flag: '🇵🇾' },
                    { name: 'Peru', code: 'PE', dial: '+51', flag: '🇵🇪' },
                    { name: 'Philippines', code: 'PH', dial: '+63', flag: '🇵🇭' },
                    { name: 'Poland', code: 'PL', dial: '+48', flag: '🇵🇱' },
                    { name: 'Portugal', code: 'PT', dial: '+351', flag: '🇵🇹' },
                    { name: 'Puerto Rico', code: 'PR', dial: '+1787', flag: '🇵🇷' },
                    { name: 'Qatar', code: 'QA', dial: '+974', flag: '🇶🇦' },
                    { name: 'Romania', code: 'RO', dial: '+40', flag: '🇷🇴' },
                    { name: 'Rwanda', code: 'RW', dial: '+250', flag: '🇷🇼' },
                    { name: 'Saudi Arabia', code: 'SA', dial: '+966', flag: '🇸🇦' },
                    { name: 'Senegal', code: 'SN', dial: '+221', flag: '🇸🇳' },
                    { name: 'Serbia', code: 'RS', dial: '+381', flag: '🇷🇸' },
                    { name: 'Seychelles', code: 'SC', dial: '+248', flag: '🇸🇨' },
                    { name: 'Sierra Leone', code: 'SL', dial: '+232', flag: '🇸🇱' },
                    { name: 'Singapore', code: 'SG', dial: '+65', flag: '🇸🇬' },
                    { name: 'Slovakia', code: 'SK', dial: '+421', flag: '🇸🇰' },
                    { name: 'Slovenia', code: 'SI', dial: '+386', flag: '🇸🇮' },
                    { name: 'South Korea', code: 'KR', dial: '+82', flag: '🇰🇷' },
                    { name: 'Spain', code: 'ES', dial: '+34', flag: '🇪🇸' },
                    { name: 'Sri Lanka', code: 'LK', dial: '+94', flag: '🇱🇰' },
                    { name: 'Sweden', code: 'SE', dial: '+46', flag: '🇸🇪' },
                    { name: 'Switzerland', code: 'CH', dial: '+41', flag: '🇨🇭' },
                    { name: 'Taiwan', code: 'TW', dial: '+886', flag: '🇹🇼' },
                    { name: 'Tanzania', code: 'TZ', dial: '+255', flag: '🇹🇿' },
                    { name: 'Thailand', code: 'TH', dial: '+66', flag: '🇹🇭' },
                    { name: 'Togo', code: 'TG', dial: '+228', flag: '🇹🇬' },
                    { name: 'Trinidad & Tobago', code: 'TT', dial: '+1868', flag: '🇹🇹' },
                    { name: 'Tunisia', code: 'TN', dial: '+216', flag: '🇹🇳' },
                    { name: 'Turkey', code: 'TR', dial: '+90', flag: '🇹🇷' },
                    { name: 'Uganda', code: 'UG', dial: '+256', flag: '🇺🇬' },
                    { name: 'Ukraine', code: 'UA', dial: '+380', flag: '🇺🇦' },
                    { name: 'Uruguay', code: 'UY', dial: '+598', flag: '🇺🇾' },
                    { name: 'Uzbekistan', code: 'UZ', dial: '+998', flag: '🇺🇿' },
                    { name: 'Venezuela', code: 'VE', dial: '+58', flag: '🇻🇪' },
                    { name: 'Vietnam', code: 'VN', dial: '+84', flag: '🇻🇳' },
                    { name: 'Zambia', code: 'ZM', dial: '+260', flag: '🇿🇲' },
                    { name: 'Zimbabwe', code: 'ZW', dial: '+263', flag: '🇿🇼' }
                    ],

                    get filteredCountries() {
                        if (!this.countrySearch) return this.countries;
                        const q = this.countrySearch.toLowerCase().trim();
                        return this.countries.filter(c => 
                            c.name.toLowerCase().includes(q) || 
                            c.dial.includes(q) || 
                            c.code.toLowerCase().includes(q)
                        );
                    },

                    selectCountry(country) {
                        this.selectedCountry = country;
                        this.countryDropdownOpen = false;
                        this.countrySearch = '';
                    }
                }));
            }
        }

        if (window.Alpine) {
            initLoginApp();
        } else {
            document.addEventListener('alpine:init', initLoginApp);
        }
    </script>
</x-layout>
