<x-layout>
    <x-slot:title>Sign In — RideMyCars</x-slot>

    <style>
        * {
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        input, button, select, textarea {
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        }
        .country-scroll::-webkit-scrollbar { width: 6px; }
        .country-scroll::-webkit-scrollbar-track { background: #f8fafc; border-radius: 8px; }
        .dark .country-scroll::-webkit-scrollbar-track { background: #1a1a1a; }
        .country-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }
        .dark .country-scroll::-webkit-scrollbar-thumb { background: #374151; }
        .country-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Bulletproof Phone Input Bar & Country Trigger */
        .phone-input-bar {
            display: flex !important;
            align-items: center !important;
            width: 100% !important;
            height: 52px !important;
            min-height: 52px !important;
            max-height: 52px !important;
            border-radius: 12px !important;
            background-color: #f3f4f6 !important;
            border: 1px solid #e5e7eb !important;
            transition: all 0.2s ease !important;
            overflow: hidden !important;
            box-sizing: border-box !important;
        }
        .dark .phone-input-bar {
            background-color: rgba(255, 255, 255, 0.05) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }
        .phone-input-bar:focus-within {
            border-color: #000000 !important;
            background-color: #ffffff !important;
            box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.1) !important;
        }
        .dark .phone-input-bar:focus-within {
            border-color: #f59e0b !important;
            background-color: #121212 !important;
            box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2) !important;
        }
        .phone-country-btn {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 8px !important;
            height: 50px !important;
            min-height: 50px !important;
            padding: 0 14px 0 16px !important;
            background: transparent !important;
            border: none !important;
            border-right: 1px solid #e5e7eb !important;
            cursor: pointer !important;
            flex-shrink: 0 !important;
            user-select: none !important;
            transition: background-color 0.15s ease !important;
            box-sizing: border-box !important;
        }
        .dark .phone-country-btn {
            border-right-color: rgba(255, 255, 255, 0.1) !important;
        }
        .phone-country-btn:hover {
            background-color: rgba(0, 0, 0, 0.04) !important;
        }
        .dark .phone-country-btn:hover {
            background-color: rgba(255, 255, 255, 0.08) !important;
        }
        .phone-flag-img {
            width: 24px !important;
            height: 16px !important;
            object-fit: cover !important;
            border-radius: 3px !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1) !important;
            border: 1px solid rgba(0, 0, 0, 0.1) !important;
            flex-shrink: 0 !important;
            display: block !important;
        }
        .phone-dial-code {
            font-size: 14px !important;
            font-weight: 700 !important;
            color: #111827 !important;
            letter-spacing: -0.01em !important;
            line-height: 1 !important;
            white-space: nowrap !important;
        }
        .dark .phone-dial-code {
            color: #ffffff !important;
        }
        .phone-arrow-icon {
            width: 14px !important;
            height: 14px !important;
            color: #9ca3af !important;
            flex-shrink: 0 !important;
            transition: transform 0.2s ease, color 0.2s ease !important;
        }
        .phone-country-btn:hover .phone-arrow-icon {
            color: #4b5563 !important;
        }
        .dark .phone-country-btn:hover .phone-arrow-icon {
            color: #d1d5db !important;
        }
        .phone-text-input {
            flex: 1 1 0% !important;
            width: 100% !important;
            height: 50px !important;
            min-height: 50px !important;
            padding: 0 16px !important;
            margin: 0 !important;
            background: transparent !important;
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            font-size: 15px !important;
            font-weight: 500 !important;
            color: #111827 !important;
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif !important;
            box-sizing: border-box !important;
        }
        .dark .phone-text-input {
            color: #ffffff !important;
        }
        .phone-text-input::placeholder {
            color: #9ca3af !important;
            font-weight: 400 !important;
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif !important;
        }

        /* Dropdown Card & Options */
        .country-dropdown-card {
            position: absolute !important;
            left: 0 !important;
            right: 0 !important;
            top: calc(100% + 8px) !important;
            background-color: #ffffff !important;
            border-radius: 16px !important;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18), 0 4px 12px rgba(0, 0, 0, 0.08) !important;
            border: 1px solid #e5e7eb !important;
            z-index: 50 !important;
            overflow: hidden !important;
        }
        .dark .country-dropdown-card {
            background-color: #181818 !important;
            border-color: rgba(255, 255, 255, 0.15) !important;
        }
        .country-search-wrapper {
            padding: 12px !important;
            background-color: rgba(249, 250, 251, 0.95) !important;
            border-bottom: 1px solid #f1f5f9 !important;
        }
        .dark .country-search-wrapper {
            background-color: rgba(255, 255, 255, 0.04) !important;
            border-bottom-color: rgba(255, 255, 255, 0.08) !important;
        }
        .country-search-box {
            display: flex !important;
            align-items: center !important;
            background-color: #ffffff !important;
            border-radius: 10px !important;
            border: 1px solid #e2e8f0 !important;
            padding: 8px 12px !important;
            gap: 10px !important;
        }
        .dark .country-search-box {
            background-color: #121212 !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
        }
        .country-search-input {
            width: 100% !important;
            background: transparent !important;
            border: none !important;
            outline: none !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            color: #0f172a !important;
            padding: 0 !important;
        }
        .dark .country-search-input {
            color: #ffffff !important;
        }
        .country-search-input::placeholder {
            color: #94a3b8 !important;
        }
        .country-options-list {
            max-height: 240px !important;
            overflow-y: auto !important;
            padding: 6px !important;
        }
        .country-option-item {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            width: 100% !important;
            min-height: 42px !important;
            padding: 8px 12px !important;
            border-radius: 10px !important;
            border: none !important;
            background: transparent !important;
            cursor: pointer !important;
            transition: background-color 0.15s ease, color 0.15s ease !important;
            text-align: left !important;
            margin-bottom: 2px !important;
            gap: 8px !important;
            box-sizing: border-box !important;
        }
        .country-option-item:hover {
            background-color: #f1f5f9 !important;
        }
        .dark .country-option-item:hover {
            background-color: rgba(255, 255, 255, 0.08) !important;
        }
        .country-option-item.selected-item {
            background-color: #000000 !important;
            color: #ffffff !important;
        }
        .dark .country-option-item.selected-item {
            background-color: #f59e0b !important;
            color: #000000 !important;
        }
        .country-option-item.selected-item span {
            color: #ffffff !important;
        }
        .dark .country-option-item.selected-item span {
            color: #000000 !important;
        }
        .country-option-left {
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            min-width: 0 !important;
            padding-right: 8px !important;
        }
        .country-option-flag {
            width: 24px !important;
            height: 16px !important;
            object-fit: cover !important;
            border-radius: 2px !important;
            flex-shrink: 0 !important;
            border: 1px solid rgba(0, 0, 0, 0.1) !important;
        }
        .country-option-name {
            font-size: 13px !important;
            font-weight: 600 !important;
            color: #1e293b !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }
        .dark .country-option-name {
            color: #e2e8f0 !important;
        }
        .country-option-right {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            flex-shrink: 0 !important;
        }
        .country-option-badge {
            font-size: 12px !important;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
            font-weight: 700 !important;
            padding: 2px 8px !important;
            border-radius: 6px !important;
            background-color: #f1f5f9 !important;
            color: #475569 !important;
        }
        .dark .country-option-badge {
            background-color: rgba(255, 255, 255, 0.1) !important;
            color: #94a3b8 !important;
        }
        .country-option-item.selected-item .country-option-badge {
            background-color: rgba(255, 255, 255, 0.2) !important;
            color: #ffffff !important;
        }
        .dark .country-option-item.selected-item .country-option-badge {
            background-color: rgba(0, 0, 0, 0.15) !important;
            color: #000000 !important;
        }
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
                
                <form action="#" method="POST" @submit.prevent="submitMobile()">
                    <div class="phone-input-container relative mb-3" @click.away="countryDropdownOpen = false">
                        <!-- Main Phone Input Bar -->
                        <div class="phone-input-bar" style="height: 52px; min-height: 52px;">
                            <!-- Country Code Trigger Button -->
                            <button type="button" 
                                    @click.prevent.stop="toggleCountryDropdown()"
                                    class="phone-country-btn"
                                    style="height: 50px; min-height: 50px; padding: 0 14px 0 16px;">
                                <img :src="selectedCountry.flagUrl || `https://flagcdn.com/w40/${(selectedCountry.code || 'us').toLowerCase()}.png`" 
                                     :alt="selectedCountry.name" 
                                     class="phone-flag-img">
                                <span class="phone-dial-code" x-text="selectedCountry.dial">+1</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="phone-arrow-icon" :class="countryDropdownOpen ? 'rotate-180 text-black dark:text-white' : ''" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            <!-- Mobile Input -->
                            <input type="tel" 
                                   x-model="mobileNumber"
                                   placeholder="Mobile number"
                                   required
                                   class="phone-text-input"
                                   style="height: 50px; min-height: 50px; padding: 0 16px;">
                        </div>

                        <!-- Country Dropdown Floating Card -->
                        <div x-show="countryDropdownOpen" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2 scale-98"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-y-2 scale-98"
                             class="country-dropdown-card"
                             style="display: none;">
                            
                            <!-- Search Bar Header -->
                            <div class="country-search-wrapper">
                                <div class="country-search-box">
                                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    <input type="text" 
                                           x-ref="countrySearchInput"
                                           x-model="countrySearch" 
                                           placeholder="Search country or code (e.g. +1, USA)..." 
                                           class="country-search-input">
                                    <button type="button" 
                                            x-show="countrySearch" 
                                            @click.prevent.stop="countrySearch = ''; $refs.countrySearchInput?.focus({ preventScroll: true })" 
                                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 shrink-0 ml-1 p-0.5"
                                            style="display: none;">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Country List -->
                            <div class="country-options-list country-scroll">
                                <template x-for="country in filteredCountries" :key="country.code + country.dial">
                                    <button type="button" 
                                            @click="selectCountry(country)"
                                            class="country-option-item"
                                            :class="selectedCountry.code === country.code ? 'selected-item' : ''">
                                        <div class="country-option-left">
                                            <img :src="country.flagUrl || `https://flagcdn.com/w40/${(country.code || 'us').toLowerCase()}.png`" 
                                                 :alt="country.name" 
                                                 loading="lazy"
                                                 class="country-option-flag">
                                            <span class="country-option-name" 
                                                  x-text="country.name"></span>
                                        </div>
                                        <div class="country-option-right">
                                            <span class="country-option-badge" 
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

                    <!-- Registration Start Notice (when phone not in DB) -->
                    <div x-show="registerNotice" x-transition class="mb-3 p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-700/50 text-amber-900 dark:text-amber-200 text-xs shadow-sm" style="display: none;">
                        <div class="flex items-start gap-2.5">
                            <span class="text-base shrink-0">📝</span>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-xs text-gray-900 dark:text-white">Phone not registered yet</h4>
                                <p class="text-[11px] text-gray-600 dark:text-gray-300 mt-0.5">
                                    No account found for <span class="font-bold text-black dark:text-white" x-text="registerNotice?.phone"></span>. Starting registration...
                                </p>
                                <div class="mt-2 flex items-center gap-2">
                                    <a :href="registerNotice?.url" class="inline-flex items-center gap-1 px-3 py-1.5 bg-black dark:bg-brand-500 text-white dark:text-black text-xs font-bold rounded-lg hover:bg-gray-800 dark:hover:bg-brand-400 transition-all shadow-sm">
                                        <span>Create Account Now</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p x-show="mobileError" x-text="mobileError" class="text-red-500 text-xs font-semibold mb-3 px-1" style="display: none;"></p>

                    <button type="submit" :disabled="isLoading || !mobileNumber" class="w-full bg-black hover:bg-gray-900 dark:bg-brand-500 dark:hover:bg-brand-400 text-white dark:text-black font-extrabold py-3.5 rounded-xl text-base transition-all shadow-md shadow-black/10 dark:shadow-brand-500/20 active:scale-[0.99] cursor-pointer flex items-center justify-center gap-2" :class="isLoading ? 'opacity-70 cursor-wait' : ''">
                        <svg x-show="isLoading" class="animate-spin h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display: none;"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="isLoading ? 'Sending SMS code...' : 'Continue'">Continue</span>
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
                
                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed mb-2">
                    Enter the 4-digit code sent to <span class="font-bold text-gray-900 dark:text-white" x-text="targetDestination"></span>.
                </p>

                <div class="flex items-center gap-2 mb-4">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>OTP valid for 2 minutes</span>
                    </span>
                    <span x-show="timer > 0" class="text-xs font-mono font-bold text-gray-500 dark:text-gray-400" x-text="'(' + formattedTimer + ')'"></span>
                </div>

                <button type="button" @click="view = isPhoneAuth ? 'mobile' : 'email_otp'" class="text-xs text-amber-600 dark:text-amber-400 font-bold underline underline-offset-4 mb-6 hover:opacity-80 transition-opacity block">
                    Change phone number or email?
                </button>

                <!-- 4 Digit Input Boxes -->
                <div class="flex items-center justify-center gap-3 mb-2">
                    <input type="text" maxlength="1" inputmode="numeric" x-ref="c1" x-model="c1" 
                           @input="handleDigit($event, 'c1', 'c2')" 
                           @keydown.backspace="handleBackspace($event, 'c1', null)" 
                           class="w-12 h-14 bg-gray-100 dark:bg-white/5 rounded-xl text-center text-xl font-bold text-gray-900 dark:text-white border-2 border-transparent focus:border-black dark:focus:border-brand-500 focus:bg-white dark:focus:bg-[#121212] transition-all shadow-inner">
                    <input type="text" maxlength="1" inputmode="numeric" x-ref="c2" x-model="c2" 
                           @input="handleDigit($event, 'c2', 'c3')" 
                           @keydown.backspace="handleBackspace($event, 'c2', 'c1')" 
                           class="w-12 h-14 bg-gray-100 dark:bg-white/5 rounded-xl text-center text-xl font-bold text-gray-900 dark:text-white border-2 border-transparent focus:border-black dark:focus:border-brand-500 focus:bg-white dark:focus:bg-[#121212] transition-all shadow-inner">
                    <input type="text" maxlength="1" inputmode="numeric" x-ref="c3" x-model="c3" 
                           @input="handleDigit($event, 'c3', 'c4')" 
                           @keydown.backspace="handleBackspace($event, 'c3', 'c2')" 
                           class="w-12 h-14 bg-gray-100 dark:bg-white/5 rounded-xl text-center text-xl font-bold text-gray-900 dark:text-white border-2 border-transparent focus:border-black dark:focus:border-brand-500 focus:bg-white dark:focus:bg-[#121212] transition-all shadow-inner">
                    <input type="text" maxlength="1" inputmode="numeric" x-ref="c4" x-model="c4" 
                           @input="handleDigit($event, 'c4', null)" 
                           @keydown.backspace="handleBackspace($event, 'c4', 'c3')" 
                           class="w-12 h-14 bg-gray-100 dark:bg-white/5 rounded-xl text-center text-xl font-bold text-gray-900 dark:text-white border-2 border-transparent focus:border-black dark:focus:border-brand-500 focus:bg-white dark:focus:bg-[#121212] transition-all shadow-inner">
                </div>
                
                <p class="text-red-500 text-xs font-semibold mb-2 h-4 text-center" x-text="otpError"></p>
                <p class="text-emerald-600 dark:text-emerald-400 text-xs font-semibold mb-2 h-4 text-center" x-text="otpSuccess"></p>

                <!-- Resend Link -->
                <div class="text-center mb-6">
                    <button type="button" 
                            x-show="timer === 0" 
                            @click="resendOtp()" 
                            class="text-xs font-bold text-black dark:text-brand-400 hover:underline cursor-pointer"
                            style="display: none;">
                        Didn't receive code? Resend SMS OTP
                    </button>
                    <span x-show="timer > 0" class="text-xs text-gray-400">
                        Resend code in <span class="font-mono font-bold" x-text="formattedTimer"></span>
                    </span>
                </div>

                <!-- Footer Navigation -->
                <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-white/10">
                    <button type="button" @click="view = isPhoneAuth ? 'mobile' : 'email_otp'" class="w-12 h-12 flex items-center justify-center bg-gray-100 hover:bg-gray-200 dark:bg-white/5 dark:hover:bg-white/10 rounded-full transition-colors text-gray-900 dark:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    </button>
                    
                    <button type="button" 
                            @click="verifyOtp()"
                            class="h-12 px-6 rounded-xl font-bold flex items-center gap-2 transition-all"
                            :class="(c1 && c2 && c3 && c4 && !isLoading) ? 'bg-black hover:bg-gray-900 dark:bg-brand-500 dark:hover:bg-brand-400 text-white dark:text-black cursor-pointer shadow-md' : 'bg-gray-100 dark:bg-white/5 text-gray-400 cursor-not-allowed'"
                            :disabled="isLoading || !(c1 && c2 && c3 && c4)">
                        <svg x-show="isLoading" class="animate-spin h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display: none;"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="isLoading ? 'Verifying...' : 'Verify & Continue'">Verify & Continue</span>
                        <svg x-show="!isLoading" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                </div>
            </div>
            
            <!-- 3. Email OTP Request View -->
            <div x-show="view === 'email_otp'" x-transition.opacity.duration.300ms style="display: none;">
                <h1 class="text-2xl sm:text-3xl font-black mb-2 text-gray-900 dark:text-white tracking-tight">Enter your email</h1>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mb-6 font-medium">We'll send a 4-digit verification code to your email.</p>
                
                <form @submit.prevent="submitEmail()" class="space-y-4">
                    <div>
                        <input type="email" x-model="emailForOtp" required placeholder="you@example.com" class="w-full bg-gray-100 dark:bg-white/5 rounded-xl px-4 py-3.5 text-gray-900 dark:text-white placeholder-gray-400 font-medium focus:outline-none focus:ring-2 focus:ring-black dark:focus:ring-brand-500 border border-transparent dark:border-white/10 text-base">
                        <p class="text-red-500 text-sm font-semibold mt-1" x-text="otpError"></p>
                    </div>
                    
                    <button type="submit" :disabled="isLoading || !emailForOtp" class="w-full bg-black hover:bg-gray-900 dark:bg-brand-500 dark:hover:bg-brand-400 text-white dark:text-black font-extrabold py-3.5 rounded-xl text-base transition-all mt-2 flex items-center justify-center gap-2" :class="isLoading ? 'opacity-70 cursor-wait' : ''">
                        <svg x-show="isLoading" class="animate-spin h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display: none;"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="isLoading ? 'Sending code...' : 'Continue'">Continue</span>
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
                    targetDestination: '',
                    isPhoneAuth: true,
                    mobileError: '',
                    otpError: '',
                    otpSuccess: '',
                    isLoading: false,
                    timer: 120,
                    timerInterval: null,
                    c1: '', c2: '', c3: '', c4: '',
                    
                    // Country Code Selector
                    countryDropdownOpen: false,
                    countrySearch: '',
                    mobileNumber: '',
                    registerNotice: null,
                    selectedCountry: (window.WORLD_COUNTRIES && window.WORLD_COUNTRIES.length) ? window.WORLD_COUNTRIES[0] : { name: 'United States', code: 'US', dial: '+1', flagUrl: 'https://flagcdn.com/w40/us.png' },
                    countries: (window.WORLD_COUNTRIES && window.WORLD_COUNTRIES.length) ? window.WORLD_COUNTRIES : [],

                    toggleCountryDropdown() {
                        this.countryDropdownOpen = !this.countryDropdownOpen;
                        if (this.countryDropdownOpen) {
                            this.countrySearch = '';
                            this.$nextTick(() => {
                                if (this.$refs.countrySearchInput) {
                                    this.$refs.countrySearchInput.focus({ preventScroll: true });
                                }
                            });
                        }
                    },

                    init() {
                        if (window.WORLD_COUNTRIES && window.WORLD_COUNTRIES.length) {
                            this.countries = window.WORLD_COUNTRIES;
                            if (!this.selectedCountry || !this.selectedCountry.flagUrl) {
                                this.selectedCountry = this.countries[0];
                            }
                        }
                    },

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
                    },

                    startTimer(seconds = 120) {
                        this.timer = seconds;
                        if (this.timerInterval) clearInterval(this.timerInterval);
                        this.timerInterval = setInterval(() => {
                            if (this.timer > 0) {
                                this.timer--;
                            } else {
                                clearInterval(this.timerInterval);
                            }
                        }, 1000);
                    },

                    get formattedTimer() {
                        const m = Math.floor(this.timer / 60);
                        const s = this.timer % 60;
                        return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
                    },

                    submitMobile() {
                        if (!this.mobileNumber || this.isLoading) return;
                        this.isLoading = true;
                        this.mobileError = '';
                        this.otpError = '';
                        this.otpSuccess = '';
                        this.registerNotice = null;
                        
                        const fullPhone = this.selectedCountry.dial + this.mobileNumber.replace(/\s+/g, '');
                        this.targetDestination = fullPhone;
                        this.isPhoneAuth = true;

                        fetch('/api/otp/send', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ phone: fullPhone, action: 'login' })
                        })
                        .then(async res => {
                            let data = {};
                            try {
                                data = await res.json();
                            } catch (e) {
                                data = {};
                            }
                            return { status: res.status, data };
                        })
                        .then(({ status, data }) => {
                            this.isLoading = false;
                            if (data.success) {
                                this.view = 'otp';
                                this.c1 = this.c2 = this.c3 = this.c4 = '';
                                this.startTimer(120);
                                this.$nextTick(() => { this.$refs.c1?.focus(); });
                            } else if (data.not_found || !data.user_exists || status === 404) {
                                const targetUrl = data.redirect || ('/signup?phone=' + encodeURIComponent(fullPhone) + '&from=login');
                                this.registerNotice = {
                                    phone: fullPhone,
                                    url: targetUrl
                                };
                                setTimeout(() => {
                                    window.location.href = targetUrl;
                                }, 1800);
                            } else {
                                this.mobileError = data.error || 'Failed to send SMS code. Please try again.';
                            }
                        })
                        .catch(err => {
                            this.isLoading = false;
                            this.mobileError = 'Network error while contacting server. Please try again.';
                        });
                    },

                    submitEmail() {
                        if (!this.emailForOtp || this.isLoading) return;
                        this.isLoading = true;
                        this.otpError = '';
                        this.otpSuccess = '';
                        this.targetDestination = this.emailForOtp;
                        this.isPhoneAuth = false;

                        fetch('/api/otp/send', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ email: this.emailForOtp })
                        })
                        .then(res => res.json())
                        .then(data => {
                            this.isLoading = false;
                            if (data.success || data.debug_otp) {
                                this.view = 'otp';
                                this.c1 = this.c2 = this.c3 = this.c4 = '';
                                this.startTimer(120);
                                if (data.debug_otp) {
                                    this.otpError = 'Demo mode code: ' + data.debug_otp;
                                }
                                this.$nextTick(() => { this.$refs.c1?.focus(); });
                            } else {
                                this.otpError = data.error || data.message || 'Failed to send email verification code.';
                            }
                        })
                        .catch(() => {
                            this.isLoading = false;
                            this.otpError = 'Network error. Please try again.';
                        });
                    },

                    resendOtp() {
                        if (this.timer > 0 || this.isLoading) return;
                        this.isLoading = true;
                        this.otpError = '';
                        this.otpSuccess = '';
                        const payload = this.isPhoneAuth 
                            ? { phone: this.targetDestination } 
                            : { email: this.targetDestination };

                        fetch('/api/otp/send', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(payload)
                        })
                        .then(res => res.json())
                        .then(data => {
                            this.isLoading = false;
                            if (data.success) {
                                this.startTimer(120);
                                this.otpSuccess = 'A new 4-digit code was sent!';
                                setTimeout(() => { this.otpSuccess = ''; }, 3500);
                            } else {
                                this.otpError = data.error || 'Failed to resend code.';
                            }
                        })
                        .catch(() => {
                            this.isLoading = false;
                            this.otpError = 'Network error. Please try again.';
                        });
                    },

                    handleDigit(e, currentKey, nextKey) {
                        const val = e.target.value.replace(/\D/g, '');
                        this[currentKey] = val.slice(-1);
                        if (this[currentKey] && nextKey && this.$refs[nextKey]) {
                            this.$refs[nextKey].focus();
                        }
                        if (this.c1 && this.c2 && this.c3 && this.c4) {
                            this.verifyOtp();
                        }
                    },

                    handleBackspace(e, currentKey, prevKey) {
                        if (!this[currentKey] && prevKey && this.$refs[prevKey]) {
                            this.$refs[prevKey].focus();
                        }
                    },

                    verifyOtp() {
                        if (!(this.c1 && this.c2 && this.c3 && this.c4) || this.isLoading) return;
                        this.isLoading = true;
                        this.otpError = '';
                        this.otpSuccess = '';
                        const code = (this.c1 + this.c2 + this.c3 + this.c4).trim();
                        const payload = this.isPhoneAuth
                            ? { phone: this.targetDestination, otp: code }
                            : { email: this.targetDestination, otp: code };

                        fetch('/api/otp/verify', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(payload)
                        })
                        .then(res => res.json())
                        .then(data => {
                            this.isLoading = false;
                            if (data.success) {
                                window.location.href = data.redirect || '/';
                            } else {
                                this.otpError = data.error || 'Invalid verification code. Please check and try again.';
                                this.c1 = this.c2 = this.c3 = this.c4 = '';
                                this.$nextTick(() => { this.$refs.c1?.focus(); });
                            }
                        })
                        .catch(() => {
                            this.isLoading = false;
                            this.otpError = 'Network error during verification. Please try again.';
                        });
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
