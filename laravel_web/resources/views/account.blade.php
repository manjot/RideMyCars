<x-layout>
    <x-slot:title>Account Settings — RideMyCars</x-slot>

    <main x-data="accountManager()" class="flex-1 w-full max-w-[1200px] mx-auto px-4 py-12 sm:px-6 lg:px-8">
        
        <!-- Toast Notification -->
        <template x-teleport="body">
            <div x-show="toast" style="display: none;" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-[-20px]"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-[-20px]"
                 class="fixed top-24 right-4 sm:right-8 z-[999999] max-w-md w-full bg-emerald-600 text-white shadow-2xl rounded-2xl p-4 flex items-center justify-between font-bold text-sm">
                <div class="flex items-center gap-3">
                    <span class="text-xl">🎉</span>
                    <span x-text="toast"></span>
                </div>
                <button @click="toast = ''" class="text-white/80 hover:text-white font-bold ml-4 text-base">✕</button>
            </div>
        </template>

        <div class="flex flex-col lg:flex-row gap-8 lg:gap-16">
            
            <!-- Sidebar -->
            <div class="w-full lg:w-[240px] shrink-0">
                <nav class="flex flex-col space-y-1">
                    <button @click="currentTab = 'home'" 
                            :class="currentTab === 'home' ? 'bg-gray-100 dark:bg-[#222] font-semibold text-gray-900 dark:text-white' : 'hover:bg-gray-50 dark:hover:bg-[#1a1a1a] text-gray-700 dark:text-gray-300'" 
                            class="w-full text-left px-4 py-3.5 text-base transition-colors rounded-xl cursor-pointer">
                        Home
                    </button>
                    <button @click="currentTab = 'personal_info'" 
                            :class="currentTab === 'personal_info' ? 'bg-gray-100 dark:bg-[#222] font-semibold text-gray-900 dark:text-white' : 'hover:bg-gray-50 dark:hover:bg-[#1a1a1a] text-gray-700 dark:text-gray-300'" 
                            class="w-full text-left px-4 py-3.5 text-base transition-colors rounded-xl cursor-pointer">
                        Personal info
                    </button>
                    <button @click="currentTab = 'security'" 
                            :class="currentTab === 'security' ? 'bg-gray-100 dark:bg-[#222] font-semibold text-gray-900 dark:text-white' : 'hover:bg-gray-50 dark:hover:bg-[#1a1a1a] text-gray-700 dark:text-gray-300'" 
                            class="w-full text-left px-4 py-3.5 text-base transition-colors rounded-xl cursor-pointer">
                        Security
                    </button>
                    <button @click="currentTab = 'privacy'" 
                            :class="currentTab === 'privacy' ? 'bg-gray-100 dark:bg-[#222] font-semibold text-gray-900 dark:text-white' : 'hover:bg-gray-50 dark:hover:bg-[#1a1a1a] text-gray-700 dark:text-gray-300'" 
                            class="w-full text-left px-4 py-3.5 text-base transition-colors rounded-xl cursor-pointer">
                        Privacy & data
                    </button>
                </nav>
            </div>

            <!-- Content Area -->
            <div class="w-full flex-1 max-w-3xl">
                
                <!-- HOME TAB -->
                <div x-show="currentTab === 'home'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                    
                    <div class="text-center mb-10">
                        <div class="w-24 h-24 rounded-full bg-black text-white dark:bg-white dark:text-black flex items-center justify-center text-4xl mx-auto mb-4 font-bold overflow-hidden shadow-md">
                            @if(isset($user->avatar))
                                <img src="{{ $user->avatar }}" class="w-full h-full object-cover">
                            @else
                                <span x-text="userName ? userName.charAt(0).toUpperCase() : 'U'"></span>
                            @endif
                        </div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white" x-text="userName"></h1>
                        <p class="text-gray-500 dark:text-gray-400 mt-1" x-text="userEmail"></p>
                    </div>

                    <!-- Profiles -->
                    <div class="mb-10">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Profiles</p>
                        <div class="space-y-1">
                            <button @click="showNameModal = true" class="w-full flex items-center gap-4 py-4 border-b border-gray-200 dark:border-white/10 group hover:bg-gray-50 dark:hover:bg-white/5 -mx-2 px-2 rounded-lg transition-colors text-left cursor-pointer">
                                <div class="w-12 h-12 rounded-full bg-black text-white dark:bg-white dark:text-black flex items-center justify-center shrink-0 font-bold text-lg">
                                    <span x-text="userName ? userName.charAt(0).toUpperCase() : 'U'"></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-gray-900 dark:text-white text-[15px]">Personal</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Default profile • Personal rides & rentals</p>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors shrink-0"><path d="m9 18 6-6-6-6"/></svg>
                            </button>

                            <a href="/wallet" class="flex items-center gap-4 py-5 border-b border-gray-200 dark:border-white/10 group hover:bg-gray-50 dark:hover:bg-white/5 -mx-2 px-2 rounded-lg transition-colors">
                                <div class="w-12 h-12 rounded-full bg-blue-600 text-white flex items-center justify-center shrink-0 font-bold text-lg">
                                    💼
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-gray-900 dark:text-white text-[15px]">ajath Infotech private limited</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Corporate Account • Expense Code Active</p>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors shrink-0"><path d="m9 18 6-6-6-6"/></svg>
                            </a>
                        </div>
                    </div>

                    <!-- Shared with you -->
                    <div class="mb-10">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Shared with you</p>
                        <a href="/wallet" class="flex items-center gap-4 py-4 group hover:bg-gray-50 dark:hover:bg-white/5 -mx-2 px-2 rounded-lg transition-colors">
                            <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-500"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-gray-900 dark:text-white text-[15px]">Manage business rides for others</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Request access to their business profile</p>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors shrink-0"><path d="m9 18 6-6-6-6"/></svg>
                        </a>
                    </div>

                    <hr class="border-gray-200 dark:border-white/10 mb-10">

                    <!-- Suggestions -->
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 tracking-tight">Suggestions</h2>
                        
                        <div class="border border-gray-200 dark:border-white/10 rounded-2xl p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 hover:shadow-sm transition-shadow bg-white dark:bg-[#111]">
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Complete your account check-up</h3>
                                <p class="text-gray-600 dark:text-gray-400 text-sm">Complete your account check-up to make RideMyCars work better for you and keep you secure.</p>
                                <button type="button" @click="currentTab = 'security'" class="mt-4 px-6 py-3 bg-black hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-black font-bold rounded-full transition-colors cursor-pointer text-sm">
                                    Begin check-up →
                                </button>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- PERSONAL INFO TAB -->
                <div x-show="currentTab === 'personal_info'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8 tracking-tight">Personal info</h1>
                    
                    <div class="w-24 h-24 rounded-full bg-black text-white dark:bg-white dark:text-black flex items-center justify-center text-4xl mb-8 font-bold overflow-hidden shadow-md">
                        <span x-text="userName ? userName.charAt(0).toUpperCase() : 'U'"></span>
                    </div>

                    <div class="space-y-0 border-t border-gray-100 dark:border-white/10">
                        <button type="button" @click="showNameModal = true" class="w-full text-left py-5 border-b border-gray-100 dark:border-white/10 flex justify-between items-center group cursor-pointer">
                            <div>
                                <div class="font-bold text-gray-900 dark:text-white mb-1">Name</div>
                                <div class="text-gray-500 dark:text-gray-400" x-text="userName"></div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                        </button>
                        
                        <button type="button" @click="showPhoneModal = true" class="w-full text-left py-5 border-b border-gray-100 dark:border-white/10 flex justify-between items-center group cursor-pointer">
                            <div>
                                <div class="font-bold text-gray-900 dark:text-white mb-1">Phone number</div>
                                <div class="text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                    <span x-text="userPhone"></span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" class="text-green-600"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2m-2 15-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                                </div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                        </button>

                        <button type="button" @click="showEmailModal = true" class="w-full text-left py-5 border-b border-gray-100 dark:border-white/10 flex justify-between items-center group cursor-pointer">
                            <div>
                                <div class="font-bold text-gray-900 dark:text-white mb-1">Email</div>
                                <div class="text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                    <span x-text="userEmail"></span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" class="text-green-600"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2m-2 15-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                                </div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                        </button>
                        
                        <button type="button" @click="showLangModal = true" class="w-full text-left py-5 border-b border-gray-100 dark:border-white/10 flex justify-between items-center group cursor-pointer">
                            <div>
                                <div class="font-bold text-gray-900 dark:text-white mb-1">Language</div>
                                <div class="text-gray-500 dark:text-gray-400" x-text="userLang"></div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" x2="21" y1="14" y2="3"/></svg>
                        </button>
                    </div>
                </div>

                <!-- SECURITY TAB -->
                <div x-show="currentTab === 'security'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8 tracking-tight">Security</h1>
                    
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Logging in to RideMyCars</h2>
                    
                    <div class="space-y-0 border-t border-gray-100 dark:border-white/10">
                        
                        <!-- 1. Passkeys -->
                        <button type="button" @click="showPasskeyModal = true" class="w-full text-left py-5 border-b border-gray-100 dark:border-white/10 flex justify-between items-center group cursor-pointer">
                            <div>
                                <div class="font-bold text-gray-900 dark:text-white mb-1">Passkeys</div>
                                <div class="text-gray-500 dark:text-gray-400" x-text="passkeys.length + ' passkey' + (passkeys.length === 1 ? '' : 's') + ' created'"></div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                        </button>
                        
                        <!-- 2. Password -->
                        <button type="button" @click="showPasswordModal = true" class="w-full text-left py-5 border-b border-gray-100 dark:border-white/10 flex justify-between items-center group cursor-pointer">
                            <div>
                                <div class="font-bold text-gray-900 dark:text-white mb-1">Password</div>
                                <div class="text-gray-900 dark:text-white text-xl leading-none -mt-1 tracking-widest">••••••••••</div>
                                <div class="text-gray-500 dark:text-gray-400 mt-1" x-text="'Last changed ' + passwordLastChanged"></div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                        </button>

                        <!-- 3. Authenticator App -->
                        <button type="button" @click="showAuthAppModal = true" class="w-full text-left py-5 border-b border-gray-100 dark:border-white/10 flex justify-between items-center group cursor-pointer">
                            <div>
                                <div class="font-bold text-gray-900 dark:text-white mb-1">Authenticator app</div>
                                <div class="text-gray-500 dark:text-gray-400" x-text="authenticatorConfigured ? '✓ Connected to authenticator app' : 'Set up your authenticator app to add an extra layer of security.'"></div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                        </button>
                        
                        <!-- 4. 2-step verification -->
                        <button type="button" @click="showTwoFactorModal = true" class="w-full text-left py-5 border-b border-gray-100 dark:border-white/10 flex justify-between items-center group cursor-pointer">
                            <div>
                                <div class="font-bold text-gray-900 dark:text-white mb-1">2-step verification</div>
                                <div class="text-gray-500 dark:text-gray-400" x-text="twoFactorEnabled ? '✓ Enabled (SMS & Authenticator Code)' : 'Add additional security to your account with 2-step verification.'"></div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                        </button>
                        
                        <!-- 5. Recovery phone -->
                        <button type="button" @click="showRecoveryPhoneModal = true" class="w-full text-left py-5 border-b border-gray-100 dark:border-white/10 flex justify-between items-center group cursor-pointer">
                            <div>
                                <div class="font-bold text-gray-900 dark:text-white mb-1">Recovery phone</div>
                                <div class="text-gray-500 dark:text-gray-400" x-text="recoveryPhone ? ('✓ ' + recoveryPhone) : 'Add a backup phone number to access your account'"></div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                        </button>
                    </div>

                    <!-- 6. Connected Social Apps -->
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mt-10 mb-4">Connected social apps</h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6 text-sm">Manage your connected social apps to sign in to your RideMyCars account here.</p>
                    
                    <button type="button" @click="showSocialAppsModal = true" class="w-full text-left p-4 bg-gray-50 dark:bg-[#111] rounded-2xl border border-gray-200 dark:border-white/10 flex items-center justify-between group hover:border-black dark:hover:border-white transition-all cursor-pointer">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">🌐</span>
                            <div>
                                <span class="font-bold text-gray-900 dark:text-white text-sm">Google & Apple Accounts</span>
                                <p class="text-xs text-gray-500 mt-0.5">2 services connected</p>
                            </div>
                        </div>
                        <span class="text-xs font-bold bg-black text-white dark:bg-white dark:text-black px-3 py-1.5 rounded-full">Manage →</span>
                    </button>
                </div>

                <!-- PRIVACY TAB -->
                <div x-show="currentTab === 'privacy'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8 tracking-tight">Privacy & data</h1>
                    
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Privacy</h2>
                    
                    <div class="space-y-0 border-t border-gray-100 dark:border-white/10">
                        <a href="/privacy" class="w-full text-left py-5 border-b border-gray-100 dark:border-white/10 flex justify-between items-center group">
                            <div>
                                <div class="font-bold text-gray-900 dark:text-white mb-1">Privacy Centre</div>
                                <div class="text-gray-500 dark:text-gray-400">Take control of your privacy and learn how we protect it.</div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                        </a>
                        
                        <button type="button" @click="showCommPrefModal = true" class="w-full text-left py-5 border-b border-gray-100 dark:border-white/10 flex justify-between items-center group cursor-pointer">
                            <div>
                                <div class="font-bold text-gray-900 dark:text-white mb-1">Communication preferences</div>
                                <div class="text-gray-500 dark:text-gray-400">Manage how RideMyCars contacts you.</div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                        </button>
                    </div>

                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mt-10 mb-4">Third-party apps with account access</h2>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Once you allow access to third-party apps, you'll see them here. <a href="/privacy" class="text-gray-900 dark:text-white underline font-medium">Learn more</a></p>
                </div>

            </div>
        </div>

        <!-- ==================== INTERACTIVE MODALS ==================== -->

        <!-- MODAL 1: PASSKEYS -->
        <template x-teleport="body">
            <div x-show="showPasskeyModal" style="display: none;" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.away="showPasskeyModal = false">
                <div class="bg-white dark:bg-[#181818] w-full max-w-md rounded-2xl shadow-2xl p-6 relative border border-gray-100 dark:border-white/10">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-white/10 mb-4">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Manage Passkeys</h3>
                        <button type="button" @click="showPasskeyModal = false" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center text-gray-500 hover:text-gray-900 dark:hover:text-white">✕</button>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Passkeys allow passwordless biometric sign in via fingerprint, Face ID, or Windows Hello.</p>
                    
                    <div class="space-y-2 mb-6">
                        <template x-for="(pk, i) in passkeys" :key="i">
                            <div class="p-3.5 bg-gray-50 dark:bg-[#222] rounded-xl flex items-center justify-between border border-gray-200 dark:border-white/10">
                                <div class="flex items-center gap-3">
                                    <span class="text-xl">🔑</span>
                                    <div>
                                        <p class="text-xs font-bold text-gray-900 dark:text-white" x-text="pk.name"></p>
                                        <p class="text-[11px] text-gray-400" x-text="'Created ' + pk.date"></p>
                                    </div>
                                </div>
                                <button type="button" @click="removePasskey(i)" class="text-xs text-red-600 dark:text-red-400 font-bold hover:underline">Remove</button>
                            </div>
                        </template>
                    </div>

                    <button type="button" @click="addPasskey()" class="w-full bg-black hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-black font-bold py-3.5 rounded-xl text-sm transition-all shadow-md flex items-center justify-center gap-2">
                        <span>+ Create new passkey</span>
                    </button>
                </div>
            </div>
        </template>

        <!-- MODAL 2: CHANGE PASSWORD -->
        <template x-teleport="body">
            <div x-show="showPasswordModal" style="display: none;" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.away="showPasswordModal = false">
                <div class="bg-white dark:bg-[#181818] w-full max-w-md rounded-2xl shadow-2xl p-6 relative border border-gray-100 dark:border-white/10">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-white/10 mb-4">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Change Password</h3>
                        <button type="button" @click="showPasswordModal = false" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center text-gray-500 hover:text-gray-900 dark:hover:text-white">✕</button>
                    </div>
                    
                    <form @submit.prevent="updatePassword" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Current Password</label>
                            <input type="password" x-model="pwdForm.current" required placeholder="••••••••" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-black text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">New Password</label>
                            <input type="password" x-model="pwdForm.new" required placeholder="Minimum 8 characters" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-black text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Confirm New Password</label>
                            <input type="password" x-model="pwdForm.confirm" required placeholder="Repeat new password" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-black text-sm">
                        </div>
                        
                        <div class="pt-2">
                            <button type="submit" class="w-full bg-black hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-black font-bold py-3.5 rounded-xl text-sm transition-all shadow-md">
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- MODAL 3: AUTHENTICATOR APP -->
        <template x-teleport="body">
            <div x-show="showAuthAppModal" style="display: none;" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.away="showAuthAppModal = false">
                <div class="bg-white dark:bg-[#181818] w-full max-w-md rounded-2xl shadow-2xl p-6 relative border border-gray-100 dark:border-white/10">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-white/10 mb-4">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Authenticator App (TOTP)</h3>
                        <button type="button" @click="showAuthAppModal = false" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center text-gray-500 hover:text-gray-900 dark:hover:text-white">✕</button>
                    </div>
                    
                    <div class="text-center my-4">
                        <div class="w-32 h-32 bg-gray-100 dark:bg-[#222] border-2 border-black dark:border-white rounded-2xl mx-auto flex items-center justify-center text-3xl font-bold font-mono tracking-wider">
                            📲 QR
                        </div>
                        <p class="text-xs text-gray-500 mt-2 font-mono">Secret Key: RMCS-7K9A-2X4M-991P</p>
                    </div>

                    <form @submit.prevent="enableAuthApp" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Enter 6-Digit Code</label>
                            <input type="text" maxlength="6" x-model="authAppCode" required placeholder="123456" class="w-full text-center tracking-[0.5em] font-mono text-xl py-3 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-black">
                        </div>
                        
                        <button type="submit" class="w-full bg-black hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-black font-bold py-3.5 rounded-xl text-sm transition-all shadow-md">
                            Verify & Enable Authenticator App
                        </button>
                    </form>
                </div>
            </div>
        </template>

        <!-- MODAL 4: 2-STEP VERIFICATION -->
        <template x-teleport="body">
            <div x-show="showTwoFactorModal" style="display: none;" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.away="showTwoFactorModal = false">
                <div class="bg-white dark:bg-[#181818] w-full max-w-md rounded-2xl shadow-2xl p-6 relative border border-gray-100 dark:border-white/10">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-white/10 mb-4">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">2-Step Verification</h3>
                        <button type="button" @click="showTwoFactorModal = false" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center text-gray-500 hover:text-gray-900 dark:hover:text-white">✕</button>
                    </div>

                    <div class="space-y-4 mb-6">
                        <div class="p-4 bg-gray-50 dark:bg-[#222] rounded-xl flex items-center justify-between border border-gray-200 dark:border-white/10">
                            <div>
                                <p class="text-sm font-bold text-gray-900 dark:text-white">SMS Security Codes</p>
                                <p class="text-xs text-gray-500">Send text message code to <span x-text="userPhone"></span></p>
                            </div>
                            <input type="checkbox" x-model="twoFactorSMS" class="w-5 h-5 accent-black dark:accent-white cursor-pointer">
                        </div>

                        <div class="p-4 bg-gray-50 dark:bg-[#222] rounded-xl flex items-center justify-between border border-gray-200 dark:border-white/10">
                            <div>
                                <p class="text-sm font-bold text-gray-900 dark:text-white">Authenticator App (TOTP)</p>
                                <p class="text-xs text-gray-500">Prompt for 6-digit authenticator code</p>
                            </div>
                            <input type="checkbox" x-model="twoFactorAuthApp" class="w-5 h-5 accent-black dark:accent-white cursor-pointer">
                        </div>
                    </div>

                    <button type="button" @click="saveTwoFactor()" class="w-full bg-black hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-black font-bold py-3.5 rounded-xl text-sm transition-all shadow-md">
                        Save 2-Step Verification Settings
                    </button>
                </div>
            </div>
        </template>

        <!-- MODAL 5: RECOVERY PHONE -->
        <template x-teleport="body">
            <div x-show="showRecoveryPhoneModal" style="display: none;" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.away="showRecoveryPhoneModal = false">
                <div class="bg-white dark:bg-[#181818] w-full max-w-md rounded-2xl shadow-2xl p-6 relative border border-gray-100 dark:border-white/10">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-white/10 mb-4">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Recovery Phone</h3>
                        <button type="button" @click="showRecoveryPhoneModal = false" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center text-gray-500 hover:text-gray-900 dark:hover:text-white">✕</button>
                    </div>

                    <form @submit.prevent="saveRecoveryPhone" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Backup Mobile Phone Number</label>
                            <input type="tel" x-model="recoveryPhone" required placeholder="+1 (555) 000-0000" class="w-full px-4 py-3 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-black text-sm font-medium">
                        </div>
                        <button type="submit" class="w-full bg-black hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-black font-bold py-3.5 rounded-xl text-sm transition-all shadow-md">
                            Save Recovery Phone
                        </button>
                    </form>
                </div>
            </div>
        </template>

        <!-- MODAL 6: CONNECTED SOCIAL APPS -->
        <template x-teleport="body">
            <div x-show="showSocialAppsModal" style="display: none;" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.away="showSocialAppsModal = false">
                <div class="bg-white dark:bg-[#181818] w-full max-w-md rounded-2xl shadow-2xl p-6 relative border border-gray-100 dark:border-white/10">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-white/10 mb-4">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Connected Social Apps</h3>
                        <button type="button" @click="showSocialAppsModal = false" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center text-gray-500 hover:text-gray-900 dark:hover:text-white">✕</button>
                    </div>

                    <div class="space-y-3 mb-6">
                        <div class="p-4 bg-gray-50 dark:bg-[#222] rounded-xl flex items-center justify-between border border-gray-200 dark:border-white/10">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">🌐</span>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">Google</p>
                                    <p class="text-xs text-gray-500" x-text="googleConnected ? ('Connected as ' + userEmail) : 'Disconnected'"></p>
                                </div>
                            </div>
                            <button type="button" @click="googleConnected = !googleConnected; showToast(googleConnected ? 'Google account connected!' : 'Google account disconnected.')"
                                    class="text-xs font-bold px-3 py-1.5 rounded-lg border transition-all"
                                    :class="googleConnected ? 'border-red-300 text-red-600 hover:bg-red-50' : 'border-black text-black dark:border-white dark:text-white'">
                                <span x-text="googleConnected ? 'Disconnect' : 'Connect'"></span>
                            </button>
                        </div>

                        <div class="p-4 bg-gray-50 dark:bg-[#222] rounded-xl flex items-center justify-between border border-gray-200 dark:border-white/10">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">🍎</span>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">Apple ID</p>
                                    <p class="text-xs text-gray-500" x-text="appleConnected ? 'Connected' : 'Disconnected'"></p>
                                </div>
                            </div>
                            <button type="button" @click="appleConnected = !appleConnected; showToast(appleConnected ? 'Apple ID connected!' : 'Apple ID disconnected.')"
                                    class="text-xs font-bold px-3 py-1.5 rounded-lg border transition-all"
                                    :class="appleConnected ? 'border-red-300 text-red-600 hover:bg-red-50' : 'border-black text-black dark:border-white dark:text-white'">
                                <span x-text="appleConnected ? 'Disconnect' : 'Connect'"></span>
                            </button>
                        </div>
                    </div>

                    <button type="button" @click="showSocialAppsModal = false" class="w-full bg-black hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-black font-bold py-3.5 rounded-xl text-sm transition-all shadow-md">
                        Done
                    </button>
                </div>
            </div>
        </template>

        <!-- EDIT NAME MODAL -->
        <template x-teleport="body">
            <div x-show="showNameModal" style="display: none;" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.away="showNameModal = false">
                <div class="bg-white dark:bg-[#181818] w-full max-w-md rounded-2xl shadow-2xl p-6 relative border border-gray-100 dark:border-white/10">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-white/10 mb-4">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Edit Full Name</h3>
                        <button type="button" @click="showNameModal = false" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center text-gray-500 hover:text-gray-900 dark:hover:text-white">✕</button>
                    </div>
                    <form @submit.prevent="userName = tempName; showNameModal = false; showToast('Name updated successfully!')" class="space-y-4">
                        <input type="text" x-model="tempName" required class="w-full px-4 py-3 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white text-sm font-medium">
                        <button type="submit" class="w-full bg-black hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-black font-bold py-3.5 rounded-xl text-sm transition-all shadow-md">Save Name</button>
                    </form>
                </div>
            </div>
        </template>

        <!-- EDIT PHONE MODAL -->
        <template x-teleport="body">
            <div x-show="showPhoneModal" style="display: none;" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.away="showPhoneModal = false">
                <div class="bg-white dark:bg-[#181818] w-full max-w-md rounded-2xl shadow-2xl p-6 relative border border-gray-100 dark:border-white/10">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-white/10 mb-4">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Edit Phone Number</h3>
                        <button type="button" @click="showPhoneModal = false" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center text-gray-500 hover:text-gray-900 dark:hover:text-white">✕</button>
                    </div>
                    <form @submit.prevent="userPhone = tempPhone; showPhoneModal = false; showToast('Phone number updated successfully!')" class="space-y-4">
                        <input type="tel" x-model="tempPhone" required class="w-full px-4 py-3 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white text-sm font-medium">
                        <button type="submit" class="w-full bg-black hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-black font-bold py-3.5 rounded-xl text-sm transition-all shadow-md">Save Phone</button>
                    </form>
                </div>
            </div>
        </template>

        <!-- EDIT EMAIL MODAL -->
        <template x-teleport="body">
            <div x-show="showEmailModal" style="display: none;" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.away="showEmailModal = false">
                <div class="bg-white dark:bg-[#181818] w-full max-w-md rounded-2xl shadow-2xl p-6 relative border border-gray-100 dark:border-white/10">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-white/10 mb-4">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Edit Email Address</h3>
                        <button type="button" @click="showEmailModal = false" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center text-gray-500 hover:text-gray-900 dark:hover:text-white">✕</button>
                    </div>
                    <form @submit.prevent="userEmail = tempEmail; showEmailModal = false; showToast('Email updated successfully!')" class="space-y-4">
                        <input type="email" x-model="tempEmail" required class="w-full px-4 py-3 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white text-sm font-medium">
                        <button type="submit" class="w-full bg-black hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-black font-bold py-3.5 rounded-xl text-sm transition-all shadow-md">Save Email</button>
                    </form>
                </div>
            </div>
        </template>

        <!-- EDIT LANGUAGE MODAL -->
        <template x-teleport="body">
            <div x-show="showLangModal" style="display: none;" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.away="showLangModal = false">
                <div class="bg-white dark:bg-[#181818] w-full max-w-md rounded-2xl shadow-2xl p-6 relative border border-gray-100 dark:border-white/10">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-white/10 mb-4">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Select Language</h3>
                        <button type="button" @click="showLangModal = false" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center text-gray-500 hover:text-gray-900 dark:hover:text-white">✕</button>
                    </div>
                    <div class="space-y-2">
                        <template x-for="lang in ['English (US)', 'Spanish (Español)', 'French (Français)', 'German (Deutsch)', 'Hindi (हिन्दी)']" :key="lang">
                            <button type="button" @click="userLang = lang; showLangModal = false; showToast('Language set to ' + lang)"
                                    class="w-full p-3.5 rounded-xl text-left font-bold text-sm flex items-center justify-between hover:bg-gray-100 dark:hover:bg-[#222]"
                                    :class="userLang === lang ? 'bg-black text-white dark:bg-white dark:text-black' : 'text-gray-900 dark:text-white'">
                                <span x-text="lang"></span>
                                <span x-show="userLang === lang">✓</span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </template>

        <!-- COMMUNICATION PREFERENCES MODAL -->
        <template x-teleport="body">
            <div x-show="showCommPrefModal" style="display: none;" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.away="showCommPrefModal = false">
                <div class="bg-white dark:bg-[#181818] w-full max-w-md rounded-2xl shadow-2xl p-6 relative border border-gray-100 dark:border-white/10">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-white/10 mb-4">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Communication Preferences</h3>
                        <button type="button" @click="showCommPrefModal = false" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center text-gray-500 hover:text-gray-900 dark:hover:text-white">✕</button>
                    </div>

                    <div class="space-y-4 mb-6 text-sm">
                        <div class="p-4 bg-gray-50 dark:bg-[#222] rounded-xl flex items-center justify-between">
                            <div>
                                <p class="font-bold text-gray-900 dark:text-white">Trip & Receipt Emails</p>
                                <p class="text-xs text-gray-500">Send digital receipts on completion</p>
                            </div>
                            <input type="checkbox" checked class="w-5 h-5 accent-black dark:accent-white cursor-pointer">
                        </div>

                        <div class="p-4 bg-gray-50 dark:bg-[#222] rounded-xl flex items-center justify-between">
                            <div>
                                <p class="font-bold text-gray-900 dark:text-white">Promotional Discounts</p>
                                <p class="text-xs text-gray-500">Receive special offers and promo codes</p>
                            </div>
                            <input type="checkbox" checked class="w-5 h-5 accent-black dark:accent-white cursor-pointer">
                        </div>

                        <div class="p-4 bg-gray-50 dark:bg-[#222] rounded-xl flex items-center justify-between">
                            <div>
                                <p class="font-bold text-gray-900 dark:text-white">SMS Ride Status Alerts</p>
                                <p class="text-xs text-gray-500">Text updates when driver reaches pickup</p>
                            </div>
                            <input type="checkbox" checked class="w-5 h-5 accent-black dark:accent-white cursor-pointer">
                        </div>
                    </div>

                    <button type="button" @click="showCommPrefModal = false; showToast('Communication preferences saved!')" class="w-full bg-black hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-black font-bold py-3.5 rounded-xl text-sm transition-all shadow-md">
                        Save Preferences
                    </button>
                </div>
            </div>
        </template>

    </main>

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('accountManager', () => ({
            currentTab: 'security',
            toast: '',
            
            userName: '{{ $user->name ?? 'Michael Driver' }}',
            userEmail: '{{ $user->email ?? 'michael@example.com' }}',
            userPhone: '{{ $user->phone ?? '+1 (555) 000-0000' }}',
            userLang: 'English (US)',
            
            tempName: '{{ $user->name ?? 'Michael Driver' }}',
            tempEmail: '{{ $user->email ?? 'michael@example.com' }}',
            tempPhone: '{{ $user->phone ?? '+1 (555) 000-0000' }}',
            
            // Security Modals
            showPasskeyModal: false,
            showPasswordModal: false,
            showAuthAppModal: false,
            showTwoFactorModal: false,
            showRecoveryPhoneModal: false,
            showSocialAppsModal: false,
            
            // Personal & Privacy Modals
            showNameModal: false,
            showPhoneModal: false,
            showEmailModal: false,
            showLangModal: false,
            showCommPrefModal: false,
            
            // Security State
            passkeys: [
                { name: 'Windows Hello / Chrome', date: 'Jan 2025' }
            ],
            passwordLastChanged: '27 September 2023',
            authenticatorConfigured: false,
            twoFactorEnabled: true,
            twoFactorSMS: true,
            twoFactorAuthApp: false,
            recoveryPhone: '{{ $user->phone ?? '+1 (555) 000-0000' }}',
            googleConnected: true,
            appleConnected: true,
            
            pwdForm: {
                current: '',
                new: '',
                confirm: ''
            },
            authAppCode: '',
            
            addPasskey() {
                this.passkeys.push({
                    name: 'Biometric Passkey / Device',
                    date: 'Just Now'
                });
                this.showToast('New passkey created successfully!');
            },
            
            removePasskey(index) {
                this.passkeys.splice(index, 1);
                this.showToast('Passkey removed.');
            },
            
            updatePassword() {
                if (this.pwdForm.new !== this.pwdForm.confirm) {
                    alert('New passwords do not match!');
                    return;
                }
                this.passwordLastChanged = 'Just now';
                this.pwdForm.current = '';
                this.pwdForm.new = '';
                this.pwdForm.confirm = '';
                this.showPasswordModal = false;
                this.showToast('Password updated successfully!');
            },
            
            enableAuthApp() {
                if (!this.authAppCode || this.authAppCode.length < 6) {
                    alert('Please enter a valid 6-digit code from your authenticator app.');
                    return;
                }
                this.authenticatorConfigured = true;
                this.authAppCode = '';
                this.showAuthAppModal = false;
                this.showToast('Authenticator app configured & connected!');
            },
            
            saveTwoFactor() {
                this.twoFactorEnabled = this.twoFactorSMS || this.twoFactorAuthApp;
                this.showTwoFactorModal = false;
                this.showToast('2-step verification settings updated!');
            },
            
            saveRecoveryPhone() {
                this.showRecoveryPhoneModal = false;
                this.showToast('Recovery phone updated!');
            },
            
            showToast(msg) {
                this.toast = msg;
                setTimeout(() => { this.toast = ''; }, 4500);
            }
        }));
    });
    </script>
</x-layout>
