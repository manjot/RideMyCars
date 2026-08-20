<x-layout>
    <x-slot:title>Account — RideMyCars</x-slot>

    <main x-data="{ currentTab: 'home' }" class="flex-1 w-full max-w-[1200px] mx-auto px-4 py-12 sm:px-6 lg:px-8">
        
        <div class="flex flex-col lg:flex-row gap-8 lg:gap-16">
            
            <!-- Sidebar -->
            <div class="w-full lg:w-[240px] shrink-0">
                <nav class="flex flex-col space-y-1">
                    <button @click="currentTab = 'home'" 
                            :class="currentTab === 'home' ? 'bg-gray-100 dark:bg-[#222] font-semibold text-gray-900 dark:text-white' : 'hover:bg-gray-50 dark:hover:bg-[#1a1a1a] text-gray-700 dark:text-gray-300'" 
                            class="w-full text-left px-4 py-3.5 text-base transition-colors rounded-none">
                        Home
                    </button>
                    <button @click="currentTab = 'personal_info'" 
                            :class="currentTab === 'personal_info' ? 'bg-gray-100 dark:bg-[#222] font-semibold text-gray-900 dark:text-white' : 'hover:bg-gray-50 dark:hover:bg-[#1a1a1a] text-gray-700 dark:text-gray-300'" 
                            class="w-full text-left px-4 py-3.5 text-base transition-colors rounded-none">
                        Personal info
                    </button>
                    <button @click="currentTab = 'security'" 
                            :class="currentTab === 'security' ? 'bg-gray-100 dark:bg-[#222] font-semibold text-gray-900 dark:text-white' : 'hover:bg-gray-50 dark:hover:bg-[#1a1a1a] text-gray-700 dark:text-gray-300'" 
                            class="w-full text-left px-4 py-3.5 text-base transition-colors rounded-none">
                        Security
                    </button>
                    <button @click="currentTab = 'privacy'" 
                            :class="currentTab === 'privacy' ? 'bg-gray-100 dark:bg-[#222] font-semibold text-gray-900 dark:text-white' : 'hover:bg-gray-50 dark:hover:bg-[#1a1a1a] text-gray-700 dark:text-gray-300'" 
                            class="w-full text-left px-4 py-3.5 text-base transition-colors rounded-none">
                        Privacy & data
                    </button>
                </nav>
            </div>

            <!-- Content Area -->
            <div class="w-full flex-1 max-w-3xl">
                
                <!-- HOME TAB -->
                <div x-show="currentTab === 'home'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                    
                    <div class="text-center mb-10">
                        <div class="w-24 h-24 rounded-full bg-brand-500 text-white flex items-center justify-center text-4xl mx-auto mb-4 font-bold overflow-hidden shadow-md">
                            @if(isset($user->avatar))
                                <img src="{{ $user->avatar }}" class="w-full h-full object-cover">
                            @else
                                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                            @endif
                        </div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ strtoupper($user->name ?? 'GUEST') }}</h1>
                        <p class="text-gray-500 dark:text-gray-400 mt-1">{{ $user->email ?? 'guest@ridemycars.com' }}</p>
                    </div>

                    <!-- Shortcut Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-12">
                        <button @click="currentTab = 'personal_info'" class="bg-gray-50 hover:bg-gray-100 dark:bg-[#1a1a1a] dark:hover:bg-[#222] rounded-xl p-6 flex flex-col items-center justify-center text-center transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mb-3 text-gray-900 dark:text-white"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <span class="font-semibold text-gray-900 dark:text-white">Personal info</span>
                        </button>
                        
                        <button @click="currentTab = 'security'" class="bg-gray-50 hover:bg-gray-100 dark:bg-[#1a1a1a] dark:hover:bg-[#222] rounded-xl p-6 flex flex-col items-center justify-center text-center transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mb-3 text-gray-900 dark:text-white"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                            <span class="font-semibold text-gray-900 dark:text-white">Security</span>
                        </button>
                        
                        <button @click="currentTab = 'privacy'" class="bg-gray-50 hover:bg-gray-100 dark:bg-[#1a1a1a] dark:hover:bg-[#222] rounded-xl p-6 flex flex-col items-center justify-center text-center transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mb-3 text-gray-900 dark:text-white"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <span class="font-semibold text-gray-900 dark:text-white">Privacy & data</span>
                        </button>
                    </div>

                    <!-- Profiles Section -->
                    <div class="mb-10">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-5 tracking-tight">Profiles</h2>
                        
                        <div class="border-t border-gray-200 dark:border-white/10">
                            <!-- Personal Profile -->
                            <a href="#" @click.prevent="currentTab = 'personal_info'" class="flex items-center gap-4 py-5 border-b border-gray-200 dark:border-white/10 group hover:bg-gray-50 dark:hover:bg-white/5 -mx-2 px-2 rounded-lg transition-colors">
                                <div class="w-12 h-12 rounded-full bg-gray-900 dark:bg-white flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-white dark:text-gray-900"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-gray-900 dark:text-white text-[15px]">Personal</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ ucfirst($user->membership_type ?? 'Cash') }}</p>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors shrink-0"><path d="m9 18 6-6-6-6"/></svg>
                            </a>

                            <!-- Business Profile (show if corporate info exists) -->
                            @if($user->corporate_company_name)
                            <a href="#" class="flex items-center gap-4 py-5 border-b border-gray-200 dark:border-white/10 group hover:bg-gray-50 dark:hover:bg-white/5 -mx-2 px-2 rounded-lg transition-colors">
                                <div class="w-12 h-12 rounded-full bg-indigo-600 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-white"><path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/><rect width="20" height="14" x="2" y="6" rx="2"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-gray-900 dark:text-white text-[15px]">{{ $user->corporate_company_name }}</p>
                                    @if($user->corporate_billing_email)
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->corporate_billing_email }}</p>
                                    @endif
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors shrink-0"><path d="m9 18 6-6-6-6"/></svg>
                            </a>
                            @else
                            <a href="/memberships" class="flex items-center gap-4 py-5 border-b border-gray-200 dark:border-white/10 group hover:bg-gray-50 dark:hover:bg-white/5 -mx-2 px-2 rounded-lg transition-colors">
                                <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-gray-900 dark:text-white text-[15px]">Add business profile</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Get a corporate membership for business rides</p>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors shrink-0"><path d="m9 18 6-6-6-6"/></svg>
                            </a>
                            @endif
                        </div>
                    </div>

                    <!-- Shared with you -->
                    <div class="mb-10">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Shared with you</p>
                        <a href="#" class="flex items-center gap-4 py-4 group hover:bg-gray-50 dark:hover:bg-white/5 -mx-2 px-2 rounded-lg transition-colors">
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

                    <!-- Vouchers -->
                    <div class="mb-10">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-5 tracking-tight">Vouchers</h2>
                        <a href="#" class="flex items-center gap-3 text-gray-900 dark:text-white font-medium hover:text-brand-500 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
                            Add voucher
                        </a>
                    </div>

                    <!-- Suggestions -->
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 tracking-tight">Suggestions</h2>
                        
                        <div class="border border-gray-200 dark:border-white/10 rounded-2xl p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 hover:shadow-sm transition-shadow bg-white dark:bg-[#111]">
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Complete your account check-up</h3>
                                <p class="text-gray-600 dark:text-gray-400">Complete your account check-up to make RideMyCars work better for you and keep you secure.</p>
                                <button class="mt-4 px-6 py-3 bg-gray-100 hover:bg-gray-200 dark:bg-[#222] dark:hover:bg-[#333] text-gray-900 dark:text-white font-bold rounded-full transition-colors">
                                    Begin check-up
                                </button>
                            </div>
                            <div class="w-20 h-16 bg-blue-600 rounded-lg flex items-center justify-center text-white shrink-0 relative overflow-hidden">
                                <div class="absolute right-0 w-4 h-full bg-blue-800"></div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- PERSONAL INFO TAB -->
                <div x-show="currentTab === 'personal_info'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8 tracking-tight">Personal info</h1>
                    
                    <div class="w-24 h-24 rounded-full bg-brand-500 text-white flex items-center justify-center text-4xl mb-8 font-bold overflow-hidden shadow-md">
                        @if(isset($user->avatar))
                            <img src="{{ $user->avatar }}" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                        @endif
                    </div>

                    <div class="space-y-0 border-t border-gray-100 dark:border-white/10">
                        <button class="w-full text-left py-5 border-b border-gray-100 dark:border-white/10 flex justify-between items-center group">
                            <div>
                                <div class="font-bold text-gray-900 dark:text-white mb-1">Name</div>
                                <div class="text-gray-500 dark:text-gray-400">{{ $user->name ?? 'Not set' }}</div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                        </button>
                        
                        <button class="w-full text-left py-5 border-b border-gray-100 dark:border-white/10 flex justify-between items-center group">
                            <div>
                                <div class="font-bold text-gray-900 dark:text-white mb-1">Phone number</div>
                                <div class="text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                    {{ $user->phone ?? '+1 (555) 000-0000' }}
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" class="text-green-600"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2m-2 15-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                                </div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                        </button>

                        <button class="w-full text-left py-5 border-b border-gray-100 dark:border-white/10 flex justify-between items-center group">
                            <div>
                                <div class="font-bold text-gray-900 dark:text-white mb-1">Email</div>
                                <div class="text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                    {{ $user->email ?? 'Not set' }}
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" class="text-green-600"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2m-2 15-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                                </div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                        </button>
                        
                        <button class="w-full text-left py-5 border-b border-gray-100 dark:border-white/10 flex justify-between items-center group">
                            <div>
                                <div class="font-bold text-gray-900 dark:text-white mb-1">Language</div>
                                <div class="text-gray-500 dark:text-gray-400">English (US)</div>
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
                        <button class="w-full text-left py-5 border-b border-gray-100 dark:border-white/10 flex justify-between items-center group">
                            <div>
                                <div class="font-bold text-gray-900 dark:text-white mb-1">Passkeys</div>
                                <div class="text-gray-500 dark:text-gray-400">One passkey created</div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                        </button>
                        
                        <button class="w-full text-left py-5 border-b border-gray-100 dark:border-white/10 flex justify-between items-center group">
                            <div>
                                <div class="font-bold text-gray-900 dark:text-white mb-1">Password</div>
                                <div class="text-gray-900 dark:text-white text-xl leading-none -mt-1 tracking-widest">••••••••••</div>
                                <div class="text-gray-500 dark:text-gray-400 mt-1">Last changed 27 September 2023</div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                        </button>

                        <button class="w-full text-left py-5 border-b border-gray-100 dark:border-white/10 flex justify-between items-center group">
                            <div>
                                <div class="font-bold text-gray-900 dark:text-white mb-1">Authenticator app</div>
                                <div class="text-gray-500 dark:text-gray-400">Set up your authenticator app to add an extra layer of security.</div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                        </button>
                        
                        <button class="w-full text-left py-5 border-b border-gray-100 dark:border-white/10 flex justify-between items-center group">
                            <div>
                                <div class="font-bold text-gray-900 dark:text-white mb-1">2-step verification</div>
                                <div class="text-gray-500 dark:text-gray-400">Add additional security to your account with 2-step verification.</div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                        </button>
                        
                        <button class="w-full text-left py-5 border-b border-gray-100 dark:border-white/10 flex justify-between items-center group">
                            <div>
                                <div class="font-bold text-gray-900 dark:text-white mb-1">Recovery phone</div>
                                <div class="text-gray-500 dark:text-gray-400">Add a backup phone number to access your account</div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                        </button>
                    </div>

                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mt-10 mb-4">Connected social apps</h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Manage your connected social apps to sign in to your RideMyCars account here.</p>
                </div>

                <!-- PRIVACY TAB -->
                <div x-show="currentTab === 'privacy'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8 tracking-tight">Privacy & data</h1>
                    
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Privacy</h2>
                    
                    <div class="space-y-0 border-t border-gray-100 dark:border-white/10">
                        <button class="w-full text-left py-5 border-b border-gray-100 dark:border-white/10 flex justify-between items-center group">
                            <div>
                                <div class="font-bold text-gray-900 dark:text-white mb-1">Privacy Centre</div>
                                <div class="text-gray-500 dark:text-gray-400">Take control of your privacy and learn how we protect it.</div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                        </button>
                        
                        <button class="w-full text-left py-5 border-b border-gray-100 dark:border-white/10 flex justify-between items-center group">
                            <div>
                                <div class="font-bold text-gray-900 dark:text-white mb-1">Communication preferences</div>
                                <div class="text-gray-500 dark:text-gray-400">Manage how RideMyCars contacts you.</div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                        </button>
                    </div>

                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mt-10 mb-4">Third-party apps with account access</h2>
                    <p class="text-gray-600 dark:text-gray-400">Once you allow access to third-party apps, you'll see them here. <a href="#" class="text-gray-900 dark:text-white underline font-medium">Learn more</a></p>
                </div>

            </div>
        </div>
    </main>

</x-layout>
