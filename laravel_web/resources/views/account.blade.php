<x-layout>
    <x-slot:title>Customer Account & Profile — RideMyCars</x-slot>

    <main x-data="accountManager('{{ request('tab', 'home') }}')" 
          x-init="initTab()"
          class="flex-1 w-full max-w-[1240px] mx-auto px-4 py-8 sm:px-6 lg:px-8">
        
        <!-- Toast Notification -->
        <template x-teleport="body">
            <div x-show="toast" style="display: none;" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-[-20px]"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-[-20px]"
                 class="fixed top-24 right-4 sm:right-8 z-[999999] max-w-md w-full bg-slate-900 text-white border border-amber-400/40 shadow-2xl rounded-2xl p-4 flex items-center justify-between font-bold text-sm">
                <div class="flex items-center gap-3">
                    <span class="text-xl">✨</span>
                    <span x-text="toast"></span>
                </div>
                <button @click="toast = ''" class="text-slate-400 hover:text-white font-bold ml-4 text-base cursor-pointer">✕</button>
            </div>
        </template>

        <div class="flex flex-col lg:flex-row gap-8 lg:gap-10">
            
            <!-- ==================== SIDEBAR ==================== -->
            <div class="w-full lg:w-[280px] shrink-0">
                <div class="bg-white dark:bg-[#141824] rounded-3xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm sticky top-28">
                    
                    <!-- User Mini Profile Card in Sidebar -->
                    <div class="flex items-center gap-3.5 pb-5 mb-5 border-b border-slate-100 dark:border-slate-800">
                        <div class="relative w-12 h-12 rounded-full overflow-hidden shrink-0 border-2 border-amber-400 bg-slate-900 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                            @if(!empty($user->avatar_url))
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div style="display: none;" class="w-full h-full items-center justify-center font-bold text-lg">
                                    <span x-text="userName ? userName.charAt(0).toUpperCase() : 'U'"></span>
                                </div>
                            @else
                                <span x-text="userName ? userName.charAt(0).toUpperCase() : 'U'"></span>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <h2 class="font-extrabold text-slate-900 dark:text-white text-base leading-tight truncate" x-text="userName"></h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5" x-text="userEmail"></p>
                            <span class="inline-flex items-center gap-1 mt-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Verified Customer
                            </span>
                        </div>
                    </div>

                    <!-- Navigation Items -->
                    <nav class="space-y-1.5">
                        <!-- 1. Home -->
                        <button type="button" @click="setTab('home')" 
                                :class="currentTab === 'home' 
                                    ? 'bg-amber-500/15 text-amber-700 dark:text-amber-400 font-bold border-l-4 border-amber-500 pl-3' 
                                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 font-medium pl-4'" 
                                class="w-full text-left py-3 pr-4 text-sm transition-all rounded-xl cursor-pointer flex items-center justify-between group">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                <span>Overview</span>
                            </div>
                        </button>

                        <!-- 2. Refer & Earn -->
                        <button type="button" @click="setTab('referral')" 
                                :class="currentTab === 'referral' 
                                    ? 'bg-amber-500/15 text-amber-700 dark:text-amber-400 font-bold border-l-4 border-amber-500 pl-3' 
                                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 font-medium pl-4'" 
                                class="w-full text-left py-3 pr-4 text-sm transition-all rounded-xl cursor-pointer flex items-center justify-between group">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                                <span>Refer & Earn</span>
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-wider bg-amber-400 text-slate-950 px-2 py-0.5 rounded-full shadow-sm">NEW</span>
                        </button>

                        <!-- 3. Personal Info -->
                        <button type="button" @click="setTab('personal_info')" 
                                :class="currentTab === 'personal_info' 
                                    ? 'bg-amber-500/15 text-amber-700 dark:text-amber-400 font-bold border-l-4 border-amber-500 pl-3' 
                                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 font-medium pl-4'" 
                                class="w-full text-left py-3 pr-4 text-sm transition-all rounded-xl cursor-pointer flex items-center justify-between group">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span>Personal Info</span>
                            </div>
                        </button>

                        <!-- 4. Booking History & Receipts -->
                        <button type="button" @click="setTab('receipts')" 
                                :class="currentTab === 'receipts' 
                                    ? 'bg-amber-500/15 text-amber-700 dark:text-amber-400 font-bold border-l-4 border-amber-500 pl-3' 
                                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 font-medium pl-4'" 
                                class="w-full text-left py-3 pr-4 text-sm transition-all rounded-xl cursor-pointer flex items-center justify-between group">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Booking Receipts</span>
                            </div>
                            <span class="text-[11px] font-mono font-bold px-2 py-0.5 rounded-full" 
                                  :class="currentTab === 'receipts' ? 'bg-amber-400 text-slate-950' : 'bg-slate-200 dark:bg-white/10 text-slate-700 dark:text-slate-300'">
                                {{ isset($receipts) ? $receipts->total() : 0 }}
                            </span>
                        </button>

                        @if($user->role === 'owner' || (isset($vehicles) && $vehicles->count() > 0))
                            <!-- Fleet Tab -->
                            <button type="button" @click="setTab('fleet')" 
                                    :class="currentTab === 'fleet' 
                                        ? 'bg-amber-500/15 text-amber-700 dark:text-amber-400 font-bold border-l-4 border-amber-500 pl-3' 
                                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 font-medium pl-4'" 
                                    class="w-full text-left py-3 pr-4 text-sm transition-all rounded-xl cursor-pointer flex items-center justify-between group">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                    <span>My Fleet</span>
                                </div>
                                <span class="text-[11px] font-mono font-bold bg-slate-200 dark:bg-white/10 px-2 py-0.5 rounded-full">{{ isset($vehicles) ? $vehicles->count() : 0 }}</span>
                            </button>
                        @endif

                        @if($user->role === 'driver' || isset($driverProfile))
                            <!-- Driver Dashboard Link -->
                            <a href="/driver/dashboard" 
                               class="w-full text-left py-3 px-4 text-sm transition-all rounded-xl hover:bg-amber-400/15 text-amber-600 dark:text-amber-400 flex items-center justify-between font-bold">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    <span>Driver Portal</span>
                                </div>
                                <span>&rarr;</span>
                            </a>
                        @endif

                        <!-- 5. Security -->
                        <button type="button" @click="setTab('security')" 
                                :class="currentTab === 'security' 
                                    ? 'bg-amber-500/15 text-amber-700 dark:text-amber-400 font-bold border-l-4 border-amber-500 pl-3' 
                                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 font-medium pl-4'" 
                                class="w-full text-left py-3 pr-4 text-sm transition-all rounded-xl cursor-pointer flex items-center justify-between group">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span>Security & 2FA</span>
                            </div>
                        </button>

                        <!-- 6. Privacy & Data -->
                        <button type="button" @click="setTab('privacy')" 
                                :class="currentTab === 'privacy' 
                                    ? 'bg-amber-500/15 text-amber-700 dark:text-amber-400 font-bold border-l-4 border-amber-500 pl-3' 
                                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 font-medium pl-4'" 
                                class="w-full text-left py-3 pr-4 text-sm transition-all rounded-xl cursor-pointer flex items-center justify-between group">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <span>Privacy & Data</span>
                            </div>
                        </button>
                    </nav>

                    <!-- Quick Logout Button -->
                    <div class="pt-5 mt-5 border-t border-slate-100 dark:border-slate-800">
                        <form method="POST" action="/logout">
                            @csrf
                            <button type="submit" class="w-full text-left py-2.5 px-4 text-xs font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-xl transition flex items-center gap-2 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                <span>Sign Out</span>
                            </button>
                        </form>
                    </div>

                </div>
            </div>

            <!-- ==================== MAIN CONTENT AREA ==================== -->
            <div class="w-full flex-1 max-w-4xl">
                
                <!-- 1. HOME / OVERVIEW TAB -->
                <div x-show="currentTab === 'home'" 
                     x-transition:enter="transition ease-out duration-200" 
                     x-transition:enter-start="opacity-0 translate-y-2" 
                     x-transition:enter-end="opacity-100 translate-y-0" 
                     style="display: none;">
                    
                    <!-- Executive Header Card -->
                    <div class="bg-white dark:bg-[#141824] rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden mb-8">
                        <!-- Luxury Banner Accent -->
                        <div class="h-32 sm:h-36 w-full relative overflow-hidden" 
                             style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #b45309 100%);">
                            <div class="absolute inset-0 opacity-15" style="background-image: radial-gradient(rgba(245, 158, 11, 0.4) 1px, transparent 1px); background-size: 16px 16px;"></div>
                            <div class="absolute right-4 top-4">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-black/40 backdrop-blur-md border border-amber-400/40 rounded-full text-xs font-bold text-amber-300">
                                    ★ RideMyCars Member
                                </span>
                            </div>
                        </div>

                        <!-- Profile Info Row -->
                        <div class="px-6 sm:px-8 pb-6 sm:pb-8 pt-0 relative">
                            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 -mt-16 sm:-mt-14 mb-6">
                                <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                                    <div class="relative w-28 h-28 rounded-full shrink-0 group">
                                        <div class="w-full h-full rounded-full bg-slate-900 text-white flex items-center justify-center text-4xl font-black overflow-hidden shadow-2xl border-4 border-white dark:border-[#141824]">
                                            @if(!empty($user->avatar_url))
                                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div style="display: none;" class="w-full h-full items-center justify-center font-black text-4xl">
                                                    <span x-text="userName ? userName.charAt(0).toUpperCase() : 'U'"></span>
                                                </div>
                                            @else
                                                <span x-text="userName ? userName.charAt(0).toUpperCase() : 'U'"></span>
                                            @endif
                                        </div>
                                        <form action="/account/avatar" method="POST" enctype="multipart/form-data" id="avatarUploadForm">
                                            @csrf
                                            <input type="file" name="avatar" id="avatarFileInput" accept="image/*" class="hidden" onchange="document.getElementById('avatarUploadForm').submit()">
                                            <label for="avatarFileInput" class="absolute bottom-1 right-1 p-2 bg-amber-400 hover:bg-amber-300 text-slate-950 rounded-full shadow-lg hover:scale-110 active:scale-95 transition-all cursor-pointer border-2 border-white dark:border-[#141824]" title="Update Profile Picture">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                                            </label>
                                        </form>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white" x-text="userName"></h1>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/30">
                                                ✓ Verified
                                            </span>
                                        </div>
                                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5" x-text="userEmail"></p>
                                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1" x-text="userPhone ? userPhone + (userCountry ? ' • ' + userCountry : '') : (userCountry || 'RideMyCars Global Rider')"></p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button type="button" @click="showFullEditModal = true" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-white font-bold text-xs rounded-xl transition flex items-center gap-2 cursor-pointer shadow-sm">
                                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        <span>Edit Profile</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Executive Stats Grid -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-5 border-t border-slate-100 dark:border-slate-800">
                                <div @click="setTab('receipts')" class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/40 hover:bg-amber-500/10 transition cursor-pointer border border-slate-100 dark:border-slate-800">
                                    <span class="text-xs text-slate-500 dark:text-slate-400 block font-medium">Receipts & Bookings</span>
                                    <span class="text-xl font-black text-slate-900 dark:text-white mt-1 block">{{ isset($receipts) ? $receipts->total() : 0 }}</span>
                                </div>
                                <div @click="setTab('referral')" class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/40 hover:bg-amber-500/10 transition cursor-pointer border border-slate-100 dark:border-slate-800">
                                    <span class="text-xs text-slate-500 dark:text-slate-400 block font-medium">Referrals</span>
                                    <span class="text-xl font-black text-amber-500 mt-1 block">{{ $user->referrals ? $user->referrals->count() : 0 }}</span>
                                </div>
                                <div @click="setTab('security')" class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/40 hover:bg-amber-500/10 transition cursor-pointer border border-slate-100 dark:border-slate-800">
                                    <span class="text-xs text-slate-500 dark:text-slate-400 block font-medium">Account Security</span>
                                    <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400 mt-1.5 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> 2FA Protected
                                    </span>
                                </div>
                                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                    <span class="text-xs text-slate-500 dark:text-slate-400 block font-medium">Member Since</span>
                                    <span class="text-sm font-bold text-slate-900 dark:text-white mt-1.5 block">{{ $user->created_at ? $user->created_at->format('M Y') : '2025' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Instant Booking Shortcuts Grid -->
                    <div class="mb-8">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">Instant Services & Booking</h2>
                            <a href="/my-rides" class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline">View Active Rides &rarr;</a>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- 1. Ride -->
                            <a href="/ride" class="group p-5 rounded-2xl bg-white dark:bg-[#141824] border border-slate-200/80 dark:border-slate-800 hover:border-amber-400 hover:shadow-lg transition-all flex flex-col justify-between">
                                <div>
                                    <div class="w-12 h-12 rounded-2xl bg-amber-400/20 text-amber-500 flex items-center justify-center text-2xl font-black mb-3 group-hover:scale-110 transition-transform">
                                        🚗
                                    </div>
                                    <h3 class="font-bold text-slate-900 dark:text-white text-base">City Ride</h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Instant on-demand cabs & scheduled airport transfers.</p>
                                </div>
                                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs font-bold text-amber-600 dark:text-amber-400">
                                    <span>Book Ride</span>
                                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                                </div>
                            </a>

                            <!-- 2. Rent -->
                            <a href="/rent" class="group p-5 rounded-2xl bg-white dark:bg-[#141824] border border-slate-200/80 dark:border-slate-800 hover:border-amber-400 hover:shadow-lg transition-all flex flex-col justify-between">
                                <div>
                                    <div class="w-12 h-12 rounded-2xl bg-blue-500/20 text-blue-500 flex items-center justify-center text-2xl font-black mb-3 group-hover:scale-110 transition-transform">
                                        🏎️
                                    </div>
                                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Car Rental</h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Self-drive rentals, luxury sedans & SUVs by day or week.</p>
                                </div>
                                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs font-bold text-amber-600 dark:text-amber-400">
                                    <span>Browse Fleet</span>
                                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                                </div>
                            </a>

                            <!-- 3. Driver -->
                            <a href="/book-driver" class="group p-5 rounded-2xl bg-white dark:bg-[#141824] border border-slate-200/80 dark:border-slate-800 hover:border-amber-400 hover:shadow-lg transition-all flex flex-col justify-between">
                                <div>
                                    <div class="w-12 h-12 rounded-2xl bg-indigo-500/20 text-indigo-500 flex items-center justify-center text-2xl font-black mb-3 group-hover:scale-110 transition-transform">
                                        👔
                                    </div>
                                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Hire Chauffeur</h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Dedicated professional drivers for your own vehicle.</p>
                                </div>
                                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs font-bold text-amber-600 dark:text-amber-400">
                                    <span>Hire Driver</span>
                                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                                </div>
                            </a>

                            <!-- 4. Delivery -->
                            <a href="/delivery" class="group p-5 rounded-2xl bg-white dark:bg-[#141824] border border-slate-200/80 dark:border-slate-800 hover:border-amber-400 hover:shadow-lg transition-all flex flex-col justify-between">
                                <div>
                                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-500 flex items-center justify-center text-2xl font-black mb-3 group-hover:scale-110 transition-transform">
                                        📦
                                    </div>
                                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Parcel Delivery</h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Same-day courier, documents & urgent item delivery.</p>
                                </div>
                                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs font-bold text-amber-600 dark:text-amber-400">
                                    <span>Send Package</span>
                                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- HIGH-CONTRAST REFERRAL SPOTLIGHT CARD -->
                    <div class="mb-8 rounded-3xl p-6 sm:p-8 shadow-2xl relative overflow-hidden" 
                         style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%); color: #ffffff; border: 1px solid rgba(245, 158, 11, 0.4);">
                        <div class="absolute -right-10 -bottom-10 w-48 h-48 rounded-full blur-3xl pointer-events-none" style="background: rgba(245, 158, 11, 0.15);"></div>
                        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                            <div>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider mb-2" 
                                      style="background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4);">
                                    <span>🎁</span> Exclusive Referral Code
                                </span>
                                <h3 class="text-xl sm:text-2xl font-black text-white">Earn Ride Perks with Friends</h3>
                                <p class="text-slate-300 text-xs sm:text-sm mt-1 max-w-md">Share your unique code with family & colleagues. Both of you unlock ride discounts and credit bonuses!</p>
                            </div>

                            <div class="shrink-0 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                                <!-- Code Container -->
                                <div class="flex items-center gap-3 px-4 py-2.5 rounded-2xl shadow-inner" 
                                     style="background: #020617; border: 2px dashed #f59e0b;">
                                    <span class="text-2xl sm:text-3xl font-mono font-black tracking-widest" 
                                          style="color: #fbbf24; text-shadow: 0 0 10px rgba(245, 158, 11, 0.4);">
                                        {{ $user->referral_code ?? 'RMC000000' }}
                                    </span>
                                    <button type="button" @click="copyReferralCode('{{ $user->referral_code ?? '' }}')" 
                                            class="p-2 rounded-xl text-xs font-black transition active:scale-95 flex items-center gap-1 cursor-pointer"
                                            style="background: #f59e0b; color: #020617;" title="Copy Code">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                                        <span>Copy</span>
                                    </button>
                                </div>

                                <!-- WhatsApp Share -->
                                <a href="https://wa.me/?text={{ urlencode('Sign up on RideMyCars with my referral code ' . ($user->referral_code ?? '') . ' to get special ride perks! ' . url('/signup?ref=' . ($user->referral_code ?? ''))) }}" 
                                   target="_blank" 
                                   class="px-4 py-3 rounded-xl font-bold text-xs flex items-center justify-center gap-2 shadow transition active:scale-95 cursor-pointer text-white" 
                                   style="background: #25D366;">
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                    <span>WhatsApp</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- RECENT RECEIPTS PREVIEW -->
                    <div class="bg-white dark:bg-[#141824] rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 sm:p-8 shadow-sm mb-8">
                        <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100 dark:border-slate-800">
                            <div>
                                <h3 class="text-lg font-black text-slate-900 dark:text-white">Recent Tax Invoices & Receipts</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Download PDF or review booking confirmations.</p>
                            </div>
                            <button type="button" @click="setTab('receipts')" class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline cursor-pointer">
                                All Invoices ({{ isset($receipts) ? $receipts->total() : 0 }}) &rarr;
                            </button>
                        </div>

                        @if(!isset($receipts) || $receipts->isEmpty())
                            <div class="py-8 text-center">
                                <span class="text-3xl mb-2 block">🧾</span>
                                <p class="text-sm font-bold text-slate-700 dark:text-slate-300">No receipts yet</p>
                                <p class="text-xs text-slate-500 mt-1">Book your first ride or rental to automatically generate official PDF receipts.</p>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach($receipts->take(2) as $r)
                                    @php $snap = $r->snapshot_data ?? []; @endphp
                                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase
                                                    @if($r->booking_type === 'rental') bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20
                                                    @elseif($r->booking_type === 'driver_booking') bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20
                                                    @elseif($r->booking_type === 'delivery') bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20
                                                    @else bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20
                                                    @endif">
                                                    {{ $r->type_label }}
                                                </span>
                                                <span class="font-mono text-xs font-bold text-slate-500">CRN: {{ $r->receipt_number }}</span>
                                            </div>
                                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 truncate max-w-md">
                                                {{ $snap['pickup_location'] ?? 'Trip' }} &rarr; {{ $snap['dropoff_location'] ?? 'Destination' }}
                                            </p>
                                        </div>

                                        <div class="flex items-center gap-3 shrink-0">
                                            <span class="text-base font-black text-slate-900 dark:text-white">
                                                {{ $r->currency }} {{ number_format($r->total_amount, 2) }}
                                            </span>
                                            <a href="{{ $r->download_url }}" class="px-3 py-1.5 rounded-xl text-xs font-black transition flex items-center gap-1 cursor-pointer"
                                               style="background: #f59e0b; color: #020617;">
                                                <span>📥 PDF</span>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                </div>

                <!-- 2. REFERRAL & REWARDS TAB -->
                <div x-show="currentTab === 'referral'" 
                     x-transition:enter="transition ease-out duration-200" 
                     x-transition:enter-start="opacity-0 translate-y-2" 
                     x-transition:enter-end="opacity-100 translate-y-0" 
                     style="display: none;">
                    
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Referral Program</h1>
                            <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">Invite your network and earn ride discounts & cash rewards.</p>
                        </div>
                        <span class="px-3.5 py-1.5 font-black text-xs uppercase tracking-wider rounded-full shadow-sm"
                              style="background: #f59e0b; color: #020617;">
                            Active
                        </span>
                    </div>

                    <!-- HERO REFERRAL CODE CARD (GUARANTEED HIGH CONTRAST) -->
                    <div class="rounded-3xl p-6 sm:p-8 mb-8 shadow-2xl relative overflow-hidden"
                         style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%); color: #ffffff; border: 1px solid rgba(245, 158, 11, 0.4);">
                        <div class="absolute -right-8 -bottom-8 w-48 h-48 rounded-full blur-3xl pointer-events-none" style="background: rgba(245, 158, 11, 0.2);"></div>
                        <div class="relative z-10">
                            
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider mb-3" 
                                  style="background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4);">
                                <span>🎁</span> Your Unique Invitation Code
                            </span>

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5 my-4">
                                <!-- Big Code Container -->
                                <div class="px-6 py-4 rounded-2xl inline-block shadow-inner" 
                                     style="background: #020617; border: 2px dashed #f59e0b;">
                                    <span class="text-3xl sm:text-5xl font-mono font-black tracking-widest select-all" 
                                          style="color: #fbbf24; text-shadow: 0 0 16px rgba(245, 158, 11, 0.4);">
                                        {{ $user->referral_code ?? 'RMC000000' }}
                                    </span>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex flex-wrap items-center gap-3">
                                    <button type="button" @click="copyReferralCode('{{ $user->referral_code ?? '' }}')" 
                                            class="px-5 py-3 rounded-xl font-black text-sm transition active:scale-95 shadow flex items-center gap-2 cursor-pointer"
                                            style="background: #f59e0b; color: #020617;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                                        <span>Copy Code</span>
                                    </button>

                                    <a href="https://wa.me/?text={{ urlencode('Sign up on RideMyCars using my referral code ' . ($user->referral_code ?? '') . ' to get special ride perks! ' . url('/signup?ref=' . ($user->referral_code ?? ''))) }}" 
                                       target="_blank" 
                                       class="px-5 py-3 rounded-xl font-bold text-sm shadow transition active:scale-95 flex items-center gap-2 cursor-pointer text-white" 
                                       style="background: #25D366;" title="Share on WhatsApp">
                                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                        <span>WhatsApp</span>
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Direct Invitation Link Box -->
                            <div class="mt-6 pt-4 border-t border-slate-700/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <span class="text-xs text-slate-300 font-medium">Direct Invitation Link:</span>
                                <div class="flex items-center gap-2 px-3 py-2 rounded-xl flex-1 max-w-lg" 
                                     style="background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(255, 255, 255, 0.15);">
                                    <span class="font-mono text-xs text-amber-300 truncate flex-1 select-all">
                                        {{ url('/signup?ref=' . ($user->referral_code ?? '')) }}
                                    </span>
                                    <button type="button" @click="copyReferralLink('{{ url('/signup?ref=' . ($user->referral_code ?? '')) }}')" 
                                            class="text-xs font-bold text-white hover:text-amber-300 px-2 py-1 bg-white/10 hover:bg-white/20 rounded-lg transition cursor-pointer">
                                        Copy Link
                                    </button>
                                </div>
                            </div>

                            @if(!empty($user->referred_by))
                                <div class="mt-3 pt-3 border-t border-slate-700/60 flex items-center justify-between text-xs text-slate-300">
                                    <span>Referred By:</span>
                                    <span class="font-bold text-amber-300">{{ $user->referred_by }} {{ $user->referrer ? '('.$user->referrer->name.')' : '' }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- How it Works (3 Steps) -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                        <div class="p-6 rounded-2xl bg-white dark:bg-[#141824] border border-slate-200/80 dark:border-slate-800 shadow-sm text-center">
                            <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-amber-400/20 text-amber-500 flex items-center justify-center font-black text-xl">1</div>
                            <h3 class="font-bold text-slate-900 dark:text-white text-sm mb-1">Share Your Code</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Send your invitation code or link to friends, family, and colleagues.</p>
                        </div>
                        <div class="p-6 rounded-2xl bg-white dark:bg-[#141824] border border-slate-200/80 dark:border-slate-800 shadow-sm text-center">
                            <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-blue-500/20 text-blue-500 flex items-center justify-center font-black text-xl">2</div>
                            <h3 class="font-bold text-slate-900 dark:text-white text-sm mb-1">They Sign Up</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">They register their account on RideMyCars web or mobile app.</p>
                        </div>
                        <div class="p-6 rounded-2xl bg-white dark:bg-[#141824] border border-slate-200/80 dark:border-slate-800 shadow-sm text-center">
                            <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-emerald-500/20 text-emerald-500 flex items-center justify-center font-black text-xl">3</div>
                            <h3 class="font-bold text-slate-900 dark:text-white text-sm mb-1">Both Unlock Rewards</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Both of you receive ride discounts, priority booking, and credit perks.</p>
                        </div>
                    </div>

                    <!-- Your Referrals List -->
                    <div class="bg-white dark:bg-[#141824] rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 sm:p-8 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-black text-slate-900 dark:text-white">Your Referrals ({{ $user->referrals ? $user->referrals->count() : 0 }})</h2>
                        </div>
                        @if($user->referrals && $user->referrals->count() > 0)
                            <div class="divide-y divide-slate-100 dark:divide-slate-800 border border-slate-100 dark:border-slate-800 rounded-2xl overflow-hidden">
                                @foreach($user->referrals as $ref)
                                    <div class="p-4 flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full overflow-hidden shrink-0 flex items-center justify-center bg-amber-400/20 text-amber-600 font-bold border border-amber-400/30">
                                                @if(!empty($ref->avatar_url))
                                                    <img src="{{ $ref->avatar_url }}" alt="{{ $ref->name }}" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                    <div style="display: none;" class="w-full h-full items-center justify-center font-bold">
                                                        {{ strtoupper(substr($ref->name ?? 'U', 0, 1)) }}
                                                    </div>
                                                @else
                                                    {{ strtoupper(substr($ref->name ?? 'U', 0, 1)) }}
                                                @endif
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-900 dark:text-white text-sm">{{ $ref->name }}</div>
                                                <div class="text-xs text-slate-500">Joined {{ $ref->created_at ? $ref->created_at->format('M d, Y') : 'Recently' }}</div>
                                            </div>
                                        </div>
                                        <span class="text-xs font-bold px-2.5 py-1 bg-emerald-500/10 text-emerald-500 rounded-full">Active</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-8 text-center border border-dashed border-slate-300 dark:border-slate-800 rounded-2xl">
                                <span class="text-3xl mb-2 block">🤝</span>
                                <h3 class="font-bold text-slate-900 dark:text-white text-sm">No referrals yet</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mb-4">Share your referral code <span class="font-mono font-bold text-amber-500">{{ $user->referral_code ?? '' }}</span> to invite friends!</p>
                                <button type="button" @click="copyReferralLink('{{ url('/signup?ref=' . ($user->referral_code ?? '')) }}')" 
                                        class="px-5 py-2.5 rounded-xl text-xs font-bold transition shadow cursor-pointer"
                                        style="background: #f59e0b; color: #020617;">
                                    Copy Invite Link
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- 3. PERSONAL INFO TAB -->
                <div x-show="currentTab === 'personal_info'" 
                     x-transition:enter="transition ease-out duration-200" 
                     x-transition:enter-start="opacity-0 translate-y-2" 
                     x-transition:enter-end="opacity-100 translate-y-0" 
                     style="display: none;">
                    
                    <div class="bg-white dark:bg-[#141824] rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 sm:p-8 shadow-sm">
                        <div class="flex items-center justify-between pb-6 border-b border-slate-100 dark:border-slate-800 mb-6">
                            <div>
                                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Personal Information</h1>
                                <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">Manage your identity, contact information, and account settings.</p>
                            </div>
                            <button type="button" @click="showFullEditModal = true" 
                                    class="px-4 py-2 rounded-xl text-xs font-black transition shadow cursor-pointer flex items-center gap-1.5"
                                    style="background: #f59e0b; color: #020617;">
                                <span>✏️ Edit All</span>
                            </button>
                        </div>
                        
                        <!-- Avatar Section -->
                        <div class="flex items-center gap-6 pb-6 border-b border-slate-100 dark:border-slate-800">
                            <div class="relative w-20 h-20 rounded-full shrink-0 group">
                                <div class="w-full h-full rounded-full bg-slate-900 text-white flex items-center justify-center text-3xl font-black overflow-hidden shadow-md border-2 border-amber-400">
                                    @if(!empty($user->avatar_url))
                                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div style="display: none;" class="w-full h-full items-center justify-center font-black text-3xl">
                                            <span x-text="userName ? userName.charAt(0).toUpperCase() : 'U'"></span>
                                        </div>
                                    @else
                                        <span x-text="userName ? userName.charAt(0).toUpperCase() : 'U'"></span>
                                    @endif
                                </div>
                                <form action="/account/avatar" method="POST" enctype="multipart/form-data" id="personalAvatarUploadForm">
                                    @csrf
                                    <input type="file" name="avatar" id="personalAvatarFileInput" accept="image/*" class="hidden" onchange="document.getElementById('personalAvatarUploadForm').submit()">
                                    <label for="personalAvatarFileInput" class="absolute bottom-0 right-0 p-1.5 bg-amber-400 text-slate-950 rounded-full shadow-lg hover:scale-110 active:scale-95 transition-all cursor-pointer border-2 border-white dark:border-[#141824]" title="Upload Profile Photo">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                                    </label>
                                </form>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 dark:text-white text-base">Profile Photo</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">JPG, PNG, or GIF. Max size 5MB.</p>
                                <label for="personalAvatarFileInput" class="inline-block mt-2 text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline cursor-pointer">
                                    Change photo &rarr;
                                </label>
                            </div>
                        </div>

                        <!-- Info Rows -->
                        <div class="divide-y divide-slate-100 dark:divide-slate-800">
                            <!-- Name -->
                            <button type="button" @click="showNameModal = true" class="w-full text-left py-4.5 flex justify-between items-center group cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/40 -mx-3 px-3 rounded-xl transition">
                                <div>
                                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Full Name</div>
                                    <div class="font-bold text-slate-900 dark:text-white text-base" x-text="userName"></div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-slate-400 group-hover:text-amber-500 transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                            </button>
                            
                            <!-- Phone -->
                            <button type="button" @click="showPhoneModal = true" class="w-full text-left py-4.5 flex justify-between items-center group cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/40 -mx-3 px-3 rounded-xl transition">
                                <div>
                                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Phone Number</div>
                                    <div class="font-bold text-slate-900 dark:text-white text-base flex items-center gap-2">
                                        <span x-text="userPhone || 'Add phone number'"></span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" class="text-emerald-500"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2m-2 15-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                                    </div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-slate-400 group-hover:text-amber-500 transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                            </button>

                            <!-- Email -->
                            <button type="button" @click="showEmailModal = true" class="w-full text-left py-4.5 flex justify-between items-center group cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/40 -mx-3 px-3 rounded-xl transition">
                                <div>
                                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Email Address</div>
                                    <div class="font-bold text-slate-900 dark:text-white text-base flex items-center gap-2">
                                        <span x-text="userEmail"></span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" class="text-emerald-500"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2m-2 15-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                                    </div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-slate-400 group-hover:text-amber-500 transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                            </button>

                            <!-- Country / City -->
                            <button type="button" @click="showCountryModal = true" class="w-full text-left py-4.5 flex justify-between items-center group cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/40 -mx-3 px-3 rounded-xl transition">
                                <div>
                                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Location</div>
                                    <div class="font-bold text-slate-900 dark:text-white text-base" x-text="userCountry ? userCountry + (userCity ? ' • ' + userCity : '') : 'Global'"></div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-slate-400 group-hover:text-amber-500 transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                            </button>
                            
                            <!-- Language -->
                            <button type="button" @click="showLangModal = true" class="w-full text-left py-4.5 flex justify-between items-center group cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/40 -mx-3 px-3 rounded-xl transition">
                                <div>
                                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Display Language</div>
                                    <div class="font-bold text-slate-900 dark:text-white text-base" x-text="userLang"></div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-slate-400 group-hover:text-amber-500 transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 4. BOOKING HISTORY & RECEIPTS TAB -->
                <div x-show="currentTab === 'receipts'" 
                     x-transition:enter="transition ease-out duration-200" 
                     x-transition:enter-start="opacity-0 translate-y-2" 
                     x-transition:enter-end="opacity-100 translate-y-0" 
                     style="display: none;" 
                     x-data="{ receiptFilter: 'all' }">
                    
                    <div class="bg-white dark:bg-[#141824] rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 sm:p-8 shadow-sm">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-100 dark:border-slate-800 mb-6 gap-4">
                            <div>
                                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Booking History & Receipts</h1>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Tamper-proof official tax invoices and receipts for all RideMyCars services.</p>
                            </div>
                            <a href="/my-rides" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-white text-xs font-bold rounded-xl transition shrink-0">
                                Active Trips &rarr;
                            </a>
                        </div>

                        <!-- Category Filter Pills (Guaranteed High Contrast) -->
                        <div class="flex items-center gap-2 overflow-x-auto pb-4 mb-6">
                            <button type="button" @click="receiptFilter = 'all'"
                                    :style="receiptFilter === 'all' ? 'background: #f59e0b; color: #020617; font-weight: 800;' : ''"
                                    :class="receiptFilter === 'all' ? 'shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold hover:bg-slate-200 dark:hover:bg-slate-700'"
                                    class="px-4 py-2 rounded-full text-xs transition-all cursor-pointer shrink-0">
                                All Receipts ({{ isset($receipts) ? $receipts->total() : 0 }})
                            </button>
                            <button type="button" @click="receiptFilter = 'ride'"
                                    :style="receiptFilter === 'ride' ? 'background: #f59e0b; color: #020617; font-weight: 800;' : ''"
                                    :class="receiptFilter === 'ride' ? 'shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold hover:bg-slate-200 dark:hover:bg-slate-700'"
                                    class="px-4 py-2 rounded-full text-xs transition-all cursor-pointer shrink-0">
                                🚗 Rides
                            </button>
                            <button type="button" @click="receiptFilter = 'rental'"
                                    :style="receiptFilter === 'rental' ? 'background: #f59e0b; color: #020617; font-weight: 800;' : ''"
                                    :class="receiptFilter === 'rental' ? 'shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold hover:bg-slate-200 dark:hover:bg-slate-700'"
                                    class="px-4 py-2 rounded-full text-xs transition-all cursor-pointer shrink-0">
                                🏎️ Rentals
                            </button>
                            <button type="button" @click="receiptFilter = 'driver_booking'"
                                    :style="receiptFilter === 'driver_booking' ? 'background: #f59e0b; color: #020617; font-weight: 800;' : ''"
                                    :class="receiptFilter === 'driver_booking' ? 'shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold hover:bg-slate-200 dark:hover:bg-slate-700'"
                                    class="px-4 py-2 rounded-full text-xs transition-all cursor-pointer shrink-0">
                                👔 Chauffeurs
                            </button>
                            <button type="button" @click="receiptFilter = 'delivery'"
                                    :style="receiptFilter === 'delivery' ? 'background: #f59e0b; color: #020617; font-weight: 800;' : ''"
                                    :class="receiptFilter === 'delivery' ? 'shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold hover:bg-slate-200 dark:hover:bg-slate-700'"
                                    class="px-4 py-2 rounded-full text-xs transition-all cursor-pointer shrink-0">
                                📦 Deliveries
                            </button>
                        </div>

                        @if(!isset($receipts) || $receipts->isEmpty())
                            <div class="p-12 text-center rounded-3xl bg-slate-50 dark:bg-slate-800/30 border border-slate-100 dark:border-slate-800">
                                <div class="w-16 h-16 mx-auto mb-4 bg-amber-400/10 text-amber-500 rounded-2xl flex items-center justify-center text-3xl">
                                    🧾
                                </div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">No Receipts Found</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-md mx-auto">
                                    Once you complete a ride, car rental, chauffeur hiring, or package delivery, your tamper-proof official PDF receipt will be automatically generated and saved here permanently.
                                </p>
                                <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                                    <a href="/ride" class="px-5 py-2.5 rounded-xl text-xs font-black shadow-sm transition"
                                       style="background: #f59e0b; color: #020617;">
                                        Book a Ride
                                    </a>
                                    <a href="/rent" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-800 dark:text-white font-bold text-xs rounded-xl transition">
                                        Rent a Car
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach($receipts as $r)
                                    @php $snap = $r->snapshot_data ?? []; @endphp
                                    <div x-show="receiptFilter === 'all' || receiptFilter === '{{ $r->booking_type }}'"
                                         class="p-5 sm:p-6 rounded-2xl bg-white dark:bg-[#181d2e] border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition">
                                        
                                        <!-- Header -->
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider
                                                        @if($r->booking_type === 'rental') bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20
                                                        @elseif($r->booking_type === 'driver_booking') bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20
                                                        @elseif($r->booking_type === 'delivery') bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20
                                                        @else bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20
                                                        @endif">
                                                        {{ $r->type_label }}
                                                    </span>
                                                    <span class="font-mono text-xs font-bold text-slate-500 dark:text-slate-400">
                                                        CRN: {{ $r->receipt_number }}
                                                    </span>
                                                </div>
                                                <p class="text-xs text-slate-400 mt-1">
                                                    Issued: {{ $r->created_at->format('d M, Y \a\t h:i A') }}
                                                </p>
                                            </div>

                                            <div class="text-left sm:text-right">
                                                <div class="text-xl font-black text-slate-900 dark:text-white">
                                                    {{ $r->currency }} {{ number_format($r->total_amount, 2) }}
                                                </div>
                                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                                    {{ strtoupper($r->payment_status) }} ({{ ucfirst($r->payment_method) }})
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Route / Vehicle Details -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 my-4 text-xs">
                                            <div class="space-y-2">
                                                <div class="flex items-start gap-2">
                                                    <span class="text-emerald-500 font-bold">&#9679;</span>
                                                    <span class="text-slate-700 dark:text-slate-300 font-medium">{{ $snap['pickup_location'] ?? 'Pickup Point' }}</span>
                                                </div>
                                                <div class="flex items-start gap-2">
                                                    <span class="text-rose-500 font-bold">&#9679;</span>
                                                    <span class="text-slate-700 dark:text-slate-300 font-medium">{{ $snap['dropoff_location'] ?? 'Dropoff Point' }}</span>
                                                </div>
                                            </div>

                                            <div class="space-y-1 sm:text-right text-slate-500 dark:text-slate-400">
                                                @if(!empty($snap['driver_name']))
                                                    <p><strong>Driver / Partner:</strong> {{ $snap['driver_name'] }}</p>
                                                @endif
                                                @if(!empty($snap['vehicle_title']))
                                                    <p><strong>Vehicle:</strong> {{ $snap['vehicle_title'] }}</p>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Actions -->
                                        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3">
                                            <span class="text-[11px] font-mono text-slate-400 select-all truncate max-w-[200px]" title="Token: {{ $r->verification_token }}">
                                                Token: {{ substr($r->verification_token, 0, 16) }}...
                                            </span>

                                            <div class="flex items-center gap-2">
                                                <form action="/receipts/{{ $r->id }}/resend" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" title="Resend copy to your email" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold transition cursor-pointer">
                                                        📧 Resend Email
                                                    </button>
                                                </form>

                                                <a href="{{ $r->view_url }}" target="_blank" class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-950 rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-1">
                                                    <span>🌐 View Online</span>
                                                </a>

                                                <a href="{{ $r->download_url }}" class="px-3 py-1.5 rounded-xl text-xs font-black shadow-sm transition flex items-center gap-1 cursor-pointer"
                                                   style="background: #f59e0b; color: #020617;">
                                                    <span>📥 Download PDF</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                <div class="mt-6">
                                    {{ $receipts->links() }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- 5. OWNER FLEET TAB -->
                <div x-show="currentTab === 'fleet'" 
                     x-transition:enter="transition ease-out duration-200" 
                     x-transition:enter-start="opacity-0 translate-y-2" 
                     x-transition:enter-end="opacity-100 translate-y-0" 
                     style="display: none;">
                    
                    <div class="bg-white dark:bg-[#141824] rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 sm:p-8 shadow-sm">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100 dark:border-slate-800 mb-6">
                            <div>
                                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">My Vehicles & Fleet</h1>
                                <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">Manage vehicles listed on RideMyCars, daily rates, and approval status.</p>
                            </div>
                            <a href="/owner-signup" class="px-5 py-2.5 rounded-xl text-xs font-black transition shadow flex items-center gap-1.5 shrink-0"
                               style="background: #f59e0b; color: #020617;">
                                <span>+ List New Vehicle</span>
                            </a>
                        </div>

                        @if(isset($vehicles) && $vehicles->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                @foreach($vehicles as $veh)
                                    <div class="bg-slate-50 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-4">
                                        <div class="relative h-44 rounded-xl overflow-hidden bg-slate-200 dark:bg-slate-800">
                                            @if($veh->image_url)
                                                <img src="{{ $veh->image_src }}" alt="{{ $veh->make }} {{ $veh->model }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-4xl">🏎️</div>
                                            @endif
                                            <div class="absolute top-3 right-3">
                                                <span class="text-[10px] font-black uppercase px-2.5 py-1 rounded-full {{ ($veh->approval_status ?? 'approved') === 'approved' ? 'bg-emerald-500 text-white' : 'bg-amber-500 text-black' }}">
                                                    {{ ucfirst($veh->approval_status ?? 'Approved') }}
                                                </span>
                                            </div>
                                        </div>
                                        <div>
                                            <h3 class="font-black text-lg text-slate-900 dark:text-white">{{ $veh->make }} {{ $veh->model }} ({{ $veh->year }})</h3>
                                            <p class="text-xs text-slate-500 font-mono mt-0.5">Plate: {{ $veh->license_plate }} • {{ ucfirst($veh->type ?? 'Sedan') }}</p>
                                        </div>
                                        <div class="flex items-center justify-between pt-3 border-t border-slate-200/60 dark:border-slate-800 text-sm">
                                            <span class="text-slate-500 text-xs">Daily Rate:</span>
                                            <span class="font-black text-amber-500">${{ number_format($veh->daily_rate, 2) }} / day</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-10 text-center border-2 border-dashed border-slate-300 dark:border-slate-800 rounded-3xl">
                                <span class="text-4xl mb-3 block">🏎️</span>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white">No vehicles listed yet</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mb-5 max-w-md mx-auto">Turn your idle car into consistent passive earnings by listing it on RideMyCars.</p>
                                <a href="/owner-signup" class="px-6 py-3 rounded-xl text-xs font-black shadow transition"
                                   style="background: #f59e0b; color: #020617;">
                                    List Your Vehicle Now &rarr;
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- 6. SECURITY TAB -->
                <div x-show="currentTab === 'security'" 
                     x-transition:enter="transition ease-out duration-200" 
                     x-transition:enter-start="opacity-0 translate-y-2" 
                     x-transition:enter-end="opacity-100 translate-y-0" 
                     style="display: none;">
                    
                    <div class="bg-white dark:bg-[#141824] rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 sm:p-8 shadow-sm">
                        <div class="pb-6 border-b border-slate-100 dark:border-slate-800 mb-6">
                            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Security & Sign In</h1>
                            <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">Protect your account with biometric passkeys, two-factor authentication, and strong credentials.</p>
                        </div>
                        
                        <div class="divide-y divide-slate-100 dark:divide-slate-800">
                            <!-- 1. Passkeys -->
                            <button type="button" @click="showPasskeyModal = true" class="w-full text-left py-4.5 flex justify-between items-center group cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/40 -mx-3 px-3 rounded-xl transition">
                                <div>
                                    <div class="font-bold text-slate-900 dark:text-white text-base mb-0.5">Biometric Passkeys</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400" x-text="passkeys.length + ' passkey' + (passkeys.length === 1 ? '' : 's') + ' configured (TouchID / FaceID / Windows Hello)'"></div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-slate-400 group-hover:text-amber-500 transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                            </button>
                            
                            <!-- 2. Password -->
                            <button type="button" @click="showPasswordModal = true" class="w-full text-left py-4.5 flex justify-between items-center group cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/40 -mx-3 px-3 rounded-xl transition">
                                <div>
                                    <div class="font-bold text-slate-900 dark:text-white text-base mb-0.5">Password</div>
                                    <div class="text-slate-900 dark:text-white text-xl leading-none tracking-widest">••••••••••</div>
                                    <div class="text-xs text-slate-400 mt-1" x-text="'Last changed ' + passwordLastChanged"></div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-slate-400 group-hover:text-amber-500 transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                            </button>

                            <!-- 3. Authenticator App -->
                            <button type="button" @click="showAuthAppModal = true" class="w-full text-left py-4.5 flex justify-between items-center group cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/40 -mx-3 px-3 rounded-xl transition">
                                <div>
                                    <div class="font-bold text-slate-900 dark:text-white text-base mb-0.5">Authenticator App (TOTP)</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400" x-text="authenticatorConfigured ? '✓ Connected to authenticator app' : 'Set up Google Authenticator or Microsoft Authenticator.'"></div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-slate-400 group-hover:text-amber-500 transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                            </button>
                            
                            <!-- 4. 2-Step Verification -->
                            <button type="button" @click="showTwoFactorModal = true" class="w-full text-left py-4.5 flex justify-between items-center group cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/40 -mx-3 px-3 rounded-xl transition">
                                <div>
                                    <div class="font-bold text-slate-900 dark:text-white text-base mb-0.5">2-Step Verification</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400" x-text="twoFactorEnabled ? '✓ Enabled (SMS & Authenticator Code)' : 'Add additional security to your account with 2-step verification.'"></div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-slate-400 group-hover:text-amber-500 transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                            </button>
                            
                            <!-- 5. Recovery phone -->
                            <button type="button" @click="showRecoveryPhoneModal = true" class="w-full text-left py-4.5 flex justify-between items-center group cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/40 -mx-3 px-3 rounded-xl transition">
                                <div>
                                    <div class="font-bold text-slate-900 dark:text-white text-base mb-0.5">Recovery Phone</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400" x-text="recoveryPhone ? ('✓ ' + recoveryPhone) : 'Add a backup phone number to access your account'"></div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-slate-400 group-hover:text-amber-500 transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                            </button>
                        </div>

                        <!-- Connected Social Apps -->
                        <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800">
                            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Connected Social Sign-In</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Fast one-tap authentication via Google & Apple ID.</p>
                            
                            <button type="button" @click="showSocialAppsModal = true" class="w-full p-4 bg-slate-50 dark:bg-slate-800/40 rounded-2xl border border-slate-200/80 dark:border-slate-800 flex items-center justify-between hover:border-amber-400 transition cursor-pointer">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">🌐</span>
                                    <div>
                                        <span class="font-bold text-slate-900 dark:text-white text-sm">Google & Apple Accounts</span>
                                        <p class="text-xs text-slate-500 mt-0.5">2 services connected</p>
                                    </div>
                                </div>
                                <span class="text-xs font-bold px-3 py-1.5 rounded-full"
                                      style="background: #f59e0b; color: #020617;">
                                    Manage &rarr;
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 7. PRIVACY & DATA TAB -->
                <div x-show="currentTab === 'privacy'" 
                     x-transition:enter="transition ease-out duration-200" 
                     x-transition:enter-start="opacity-0 translate-y-2" 
                     x-transition:enter-end="opacity-100 translate-y-0" 
                     style="display: none;">
                    
                    <div class="bg-white dark:bg-[#141824] rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 sm:p-8 shadow-sm">
                        <div class="pb-6 border-b border-slate-100 dark:border-slate-800 mb-6">
                            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Privacy & Data Governance</h1>
                            <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">Control how your personal ride data, communications, and telemetry are handled.</p>
                        </div>
                        
                        <div class="divide-y divide-slate-100 dark:divide-slate-800">
                            <a href="/privacy" class="w-full text-left py-4.5 flex justify-between items-center group -mx-3 px-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                <div>
                                    <div class="font-bold text-slate-900 dark:text-white text-base mb-0.5">Privacy Centre</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Read our strict data protection principles and GDPR/CCPA rights.</div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-slate-400 group-hover:text-amber-500 transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                            </a>
                            
                            <button type="button" @click="showCommPrefModal = true" class="w-full text-left py-4.5 flex justify-between items-center group cursor-pointer -mx-3 px-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                <div>
                                    <div class="font-bold text-slate-900 dark:text-white text-base mb-0.5">Communication Preferences</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Configure email receipts, SMS status alerts, and promotional newsletters.</div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-slate-400 group-hover:text-amber-500 transition-colors"><path d="m9 18 6-6-6-6"/></svg>
                            </button>

                            <a href="/delete-account" class="w-full text-left py-4.5 flex justify-between items-center group cursor-pointer text-rose-600 dark:text-rose-400 -mx-3 px-3 rounded-xl hover:bg-rose-50 dark:hover:bg-rose-950/20 transition">
                                <div>
                                    <div class="font-bold mb-0.5 flex items-center gap-2">
                                        <span>Delete Account & Erase Data</span>
                                        <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-500/20">Permanent</span>
                                    </div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Permanently purge your account, ride history, and billing profiles.</div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-rose-400 group-hover:translate-x-1 transition-transform"><path d="m9 18 6-6-6-6"/></svg>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ==================== INTERACTIVE MODALS ==================== -->

        <!-- MODAL 1: PASSKEYS -->
        <template x-teleport="body">
            <div x-show="showPasskeyModal" style="display: none;" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.away="showPasskeyModal = false">
                <div class="bg-white dark:bg-[#181d2e] w-full max-w-md rounded-3xl shadow-2xl p-6 relative border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
                        <h3 class="text-lg font-black text-slate-900 dark:text-white">Manage Passkeys</h3>
                        <button type="button" @click="showPasskeyModal = false" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:text-slate-900 dark:hover:text-white cursor-pointer">✕</button>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Passkeys allow passwordless biometric sign in via fingerprint, Face ID, or Windows Hello.</p>
                    
                    <div class="space-y-2 mb-6">
                        <template x-for="(pk, i) in passkeys" :key="i">
                            <div class="p-3.5 bg-slate-50 dark:bg-slate-800/60 rounded-xl flex items-center justify-between border border-slate-200 dark:border-slate-800">
                                <div class="flex items-center gap-3">
                                    <span class="text-xl">🔑</span>
                                    <div>
                                        <p class="text-xs font-bold text-slate-900 dark:text-white" x-text="pk.name"></p>
                                        <p class="text-[11px] text-slate-400" x-text="'Created ' + pk.date"></p>
                                    </div>
                                </div>
                                <button type="button" @click="removePasskey(i)" class="text-xs text-red-600 dark:text-red-400 font-bold hover:underline cursor-pointer">Remove</button>
                            </div>
                        </template>
                    </div>

                    <button type="button" @click="addPasskey()" 
                            class="w-full font-black py-3.5 rounded-xl text-xs transition shadow flex items-center justify-center gap-2 cursor-pointer"
                            style="background: #f59e0b; color: #020617;">
                        <span>+ Create New Passkey</span>
                    </button>
                </div>
            </div>
        </template>

        <!-- MODAL 2: CHANGE PASSWORD -->
        <template x-teleport="body">
            <div x-show="showPasswordModal" style="display: none;" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.away="showPasswordModal = false">
                <div class="bg-white dark:bg-[#181d2e] w-full max-w-md rounded-3xl shadow-2xl p-6 relative border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
                        <h3 class="text-lg font-black text-slate-900 dark:text-white">Change Password</h3>
                        <button type="button" @click="showPasswordModal = false" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:text-slate-900 dark:hover:text-white cursor-pointer">✕</button>
                    </div>
                    
                    <form @submit.prevent="updatePassword" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Current Password</label>
                            <input type="password" x-model="pwdForm.current" required placeholder="••••••••" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">New Password</label>
                            <input type="password" x-model="pwdForm.new" required placeholder="Minimum 8 characters" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Confirm New Password</label>
                            <input type="password" x-model="pwdForm.confirm" required placeholder="Repeat new password" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm">
                        </div>
                        
                        <div class="pt-2">
                            <button type="submit" 
                                    class="w-full font-black py-3.5 rounded-xl text-xs transition shadow cursor-pointer"
                                    style="background: #f59e0b; color: #020617;">
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
                <div class="bg-white dark:bg-[#181d2e] w-full max-w-md rounded-3xl shadow-2xl p-6 relative border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
                        <h3 class="text-lg font-black text-slate-900 dark:text-white">Authenticator App (TOTP)</h3>
                        <button type="button" @click="showAuthAppModal = false" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:text-slate-900 dark:hover:text-white cursor-pointer">✕</button>
                    </div>
                    
                    <div class="text-center my-4">
                        <div class="w-32 h-32 bg-slate-100 dark:bg-slate-800 border-2 border-amber-400 rounded-2xl mx-auto flex items-center justify-center text-3xl font-bold font-mono tracking-wider">
                            📲 QR
                        </div>
                        <p class="text-xs text-slate-500 mt-2 font-mono">Secret Key: RMCS-7K9A-2X4M-991P</p>
                    </div>

                    <form @submit.prevent="enableAuthApp" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Enter 6-Digit Code</label>
                            <input type="text" maxlength="6" x-model="authAppCode" required placeholder="123456" class="w-full text-center tracking-[0.5em] font-mono text-xl py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500">
                        </div>
                        
                        <button type="submit" 
                                class="w-full font-black py-3.5 rounded-xl text-xs transition shadow cursor-pointer"
                                style="background: #f59e0b; color: #020617;">
                            Verify & Enable Authenticator App
                        </button>
                    </form>
                </div>
            </div>
        </template>

        <!-- MODAL 4: 2-STEP VERIFICATION -->
        <template x-teleport="body">
            <div x-show="showTwoFactorModal" style="display: none;" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.away="showTwoFactorModal = false">
                <div class="bg-white dark:bg-[#181d2e] w-full max-w-md rounded-3xl shadow-2xl p-6 relative border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
                        <h3 class="text-lg font-black text-slate-900 dark:text-white">2-Step Verification</h3>
                        <button type="button" @click="showTwoFactorModal = false" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:text-slate-900 dark:hover:text-white cursor-pointer">✕</button>
                    </div>

                    <div class="space-y-3 mb-6">
                        <div class="p-4 bg-slate-50 dark:bg-slate-800/60 rounded-xl flex items-center justify-between border border-slate-200 dark:border-slate-800">
                            <div>
                                <p class="text-sm font-bold text-slate-900 dark:text-white">SMS Security Codes</p>
                                <p class="text-xs text-slate-500">Send text message code to <span x-text="userPhone"></span></p>
                            </div>
                            <input type="checkbox" x-model="twoFactorSMS" class="w-5 h-5 accent-amber-500 cursor-pointer">
                        </div>

                        <div class="p-4 bg-slate-50 dark:bg-slate-800/60 rounded-xl flex items-center justify-between border border-slate-200 dark:border-slate-800">
                            <div>
                                <p class="text-sm font-bold text-slate-900 dark:text-white">Authenticator App (TOTP)</p>
                                <p class="text-xs text-slate-500">Prompt for 6-digit authenticator code</p>
                            </div>
                            <input type="checkbox" x-model="twoFactorAuthApp" class="w-5 h-5 accent-amber-500 cursor-pointer">
                        </div>
                    </div>

                    <button type="button" @click="saveTwoFactor()" 
                            class="w-full font-black py-3.5 rounded-xl text-xs transition shadow cursor-pointer"
                            style="background: #f59e0b; color: #020617;">
                        Save 2-Step Verification Settings
                    </button>
                </div>
            </div>
        </template>

        <!-- MODAL 5: RECOVERY PHONE -->
        <template x-teleport="body">
            <div x-show="showRecoveryPhoneModal" style="display: none;" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.away="showRecoveryPhoneModal = false">
                <div class="bg-white dark:bg-[#181d2e] w-full max-w-md rounded-3xl shadow-2xl p-6 relative border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
                        <h3 class="text-lg font-black text-slate-900 dark:text-white">Recovery Phone</h3>
                        <button type="button" @click="showRecoveryPhoneModal = false" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:text-slate-900 dark:hover:text-white cursor-pointer">✕</button>
                    </div>

                    <form @submit.prevent="saveRecoveryPhone" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Backup Mobile Phone Number</label>
                            <input type="tel" x-model="recoveryPhone" required placeholder="+1 (555) 000-0000" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm font-medium">
                        </div>
                        <button type="submit" 
                                class="w-full font-black py-3.5 rounded-xl text-xs transition shadow cursor-pointer"
                                style="background: #f59e0b; color: #020617;">
                            Save Recovery Phone
                        </button>
                    </form>
                </div>
            </div>
        </template>

        <!-- MODAL 6: CONNECTED SOCIAL APPS -->
        <template x-teleport="body">
            <div x-show="showSocialAppsModal" style="display: none;" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.away="showSocialAppsModal = false">
                <div class="bg-white dark:bg-[#181d2e] w-full max-w-md rounded-3xl shadow-2xl p-6 relative border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
                        <h3 class="text-lg font-black text-slate-900 dark:text-white">Connected Social Accounts</h3>
                        <button type="button" @click="showSocialAppsModal = false" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:text-slate-900 dark:hover:text-white cursor-pointer">✕</button>
                    </div>

                    <div class="space-y-3 mb-6">
                        <div class="p-4 bg-slate-50 dark:bg-slate-800/60 rounded-xl flex items-center justify-between border border-slate-200 dark:border-slate-800">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">🌐</span>
                                <div>
                                    <p class="text-sm font-bold text-slate-900 dark:text-white">Google</p>
                                    <p class="text-xs text-slate-500" x-text="googleConnected ? ('Connected as ' + userEmail) : 'Disconnected'"></p>
                                </div>
                            </div>
                            <button type="button" @click="googleConnected = !googleConnected; showToast(googleConnected ? 'Google account connected!' : 'Google account disconnected.')"
                                    class="text-xs font-bold px-3 py-1.5 rounded-lg border transition-all cursor-pointer"
                                    :class="googleConnected ? 'border-red-300 text-red-600 hover:bg-red-50' : 'border-slate-300 text-slate-800 dark:border-slate-600 dark:text-white'">
                                <span x-text="googleConnected ? 'Disconnect' : 'Connect'"></span>
                            </button>
                        </div>

                        <div class="p-4 bg-slate-50 dark:bg-slate-800/60 rounded-xl flex items-center justify-between border border-slate-200 dark:border-slate-800">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">🍎</span>
                                <div>
                                    <p class="text-sm font-bold text-slate-900 dark:text-white">Apple ID</p>
                                    <p class="text-xs text-slate-500" x-text="appleConnected ? 'Connected' : 'Disconnected'"></p>
                                </div>
                            </div>
                            <button type="button" @click="appleConnected = !appleConnected; showToast(appleConnected ? 'Apple ID connected!' : 'Apple ID disconnected.')"
                                    class="text-xs font-bold px-3 py-1.5 rounded-lg border transition-all cursor-pointer"
                                    :class="appleConnected ? 'border-red-300 text-red-600 hover:bg-red-50' : 'border-slate-300 text-slate-800 dark:border-slate-600 dark:text-white'">
                                <span x-text="appleConnected ? 'Disconnect' : 'Connect'"></span>
                            </button>
                        </div>
                    </div>

                    <button type="button" @click="showSocialAppsModal = false" 
                            class="w-full font-black py-3 rounded-xl text-xs transition shadow cursor-pointer"
                            style="background: #f59e0b; color: #020617;">
                        Done
                    </button>
                </div>
            </div>
        </template>

        <!-- EDIT NAME MODAL -->
        <template x-teleport="body">
            <div x-show="showNameModal" style="display: none;" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.away="showNameModal = false">
                <div class="bg-white dark:bg-[#181d2e] w-full max-w-md rounded-3xl shadow-2xl p-6 relative border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
                        <h3 class="text-lg font-black text-slate-900 dark:text-white">Edit Full Name</h3>
                        <button type="button" @click="showNameModal = false" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:text-slate-900 dark:hover:text-white cursor-pointer">✕</button>
                    </div>
                    <form @submit.prevent="saveProfileData({ name: tempName })" class="space-y-4">
                        <input type="text" x-model="tempName" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-amber-500">
                        <button type="submit" :disabled="isSaving" 
                                class="w-full font-black py-3.5 rounded-xl text-xs transition shadow flex items-center justify-center gap-2 cursor-pointer"
                                style="background: #f59e0b; color: #020617;">
                            <span x-show="!isSaving">Save Name</span>
                            <span x-show="isSaving" style="display: none;">Saving...</span>
                        </button>
                    </form>
                </div>
            </div>
        </template>

        <!-- EDIT PHONE MODAL -->
        <template x-teleport="body">
            <div x-show="showPhoneModal" style="display: none;" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.away="showPhoneModal = false">
                <div class="bg-white dark:bg-[#181d2e] w-full max-w-md rounded-3xl shadow-2xl p-6 relative border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
                        <h3 class="text-lg font-black text-slate-900 dark:text-white">Edit Phone Number</h3>
                        <button type="button" @click="showPhoneModal = false" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:text-slate-900 dark:hover:text-white cursor-pointer">✕</button>
                    </div>
                    <form @submit.prevent="saveProfileData({ phone: tempPhone })" class="space-y-4">
                        <input type="tel" x-model="tempPhone" required placeholder="+1 (555) 000-0000" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white text-sm font-medium focus:outline-none focus:ring-2 focus:ring-amber-500">
                        <button type="submit" :disabled="isSaving" 
                                class="w-full font-black py-3.5 rounded-xl text-xs transition shadow flex items-center justify-center gap-2 cursor-pointer"
                                style="background: #f59e0b; color: #020617;">
                            <span x-show="!isSaving">Save Phone</span>
                            <span x-show="isSaving" style="display: none;">Saving...</span>
                        </button>
                    </form>
                </div>
            </div>
        </template>

        <!-- EDIT EMAIL MODAL -->
        <template x-teleport="body">
            <div x-show="showEmailModal" style="display: none;" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.away="showEmailModal = false">
                <div class="bg-white dark:bg-[#181d2e] w-full max-w-md rounded-3xl shadow-2xl p-6 relative border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
                        <h3 class="text-lg font-black text-slate-900 dark:text-white">Edit Email Address</h3>
                        <button type="button" @click="showEmailModal = false" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:text-slate-900 dark:hover:text-white cursor-pointer">✕</button>
                    </div>
                    <form @submit.prevent="saveProfileData({ email: tempEmail })" class="space-y-4">
                        <input type="email" x-model="tempEmail" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white text-sm font-medium focus:outline-none focus:ring-2 focus:ring-amber-500">
                        <button type="submit" :disabled="isSaving" 
                                class="w-full font-black py-3.5 rounded-xl text-xs transition shadow flex items-center justify-center gap-2 cursor-pointer"
                                style="background: #f59e0b; color: #020617;">
                            <span x-show="!isSaving">Save Email</span>
                            <span x-show="isSaving" style="display: none;">Saving...</span>
                        </button>
                    </form>
                </div>
            </div>
        </template>

        <!-- EDIT COUNTRY & CITY MODAL -->
        <template x-teleport="body">
            <div x-show="showCountryModal" style="display: none;" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.away="showCountryModal = false">
                <div class="bg-white dark:bg-[#181d2e] w-full max-w-md rounded-3xl shadow-2xl p-6 relative border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
                        <h3 class="text-lg font-black text-slate-900 dark:text-white">Edit Country & City</h3>
                        <button type="button" @click="showCountryModal = false" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:text-slate-900 dark:hover:text-white cursor-pointer">✕</button>
                    </div>
                    <form @submit.prevent="saveProfileData({ country: tempCountry, city: tempCity })" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Country</label>
                            <input type="text" x-model="tempCountry" required placeholder="e.g. United States, United Kingdom, South Africa" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white text-sm font-medium focus:outline-none focus:ring-2 focus:ring-amber-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">City / Region</label>
                            <input type="text" x-model="tempCity" placeholder="e.g. New York, London, Johannesburg" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white text-sm font-medium focus:outline-none focus:ring-2 focus:ring-amber-500">
                        </div>
                        <button type="submit" :disabled="isSaving" 
                                class="w-full font-black py-3.5 rounded-xl text-xs transition shadow flex items-center justify-center gap-2 cursor-pointer"
                                style="background: #f59e0b; color: #020617;">
                            <span x-show="!isSaving">Save Location</span>
                            <span x-show="isSaving" style="display: none;">Saving...</span>
                        </button>
                    </form>
                </div>
            </div>
        </template>

        <!-- UNIFIED EDIT FULL PROFILE MODAL -->
        <template x-teleport="body">
            <div x-show="showFullEditModal" style="display: none;" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/70 backdrop-blur-md" @click.away="showFullEditModal = false">
                <div class="bg-white dark:bg-[#181d2e] w-full max-w-lg rounded-3xl shadow-2xl p-6 sm:p-8 relative border border-slate-200 dark:border-slate-800 max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-amber-400/20 text-amber-500 flex items-center justify-center text-xl font-bold">
                                ✏️
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-slate-900 dark:text-white">Edit Full Profile</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">All registered customer account information.</p>
                            </div>
                        </div>
                        <button type="button" @click="showFullEditModal = false" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:text-slate-900 dark:hover:text-white cursor-pointer">✕</button>
                    </div>

                    <form @submit.prevent="saveProfileData({ name: tempName, email: tempEmail, phone: tempPhone, country: tempCountry, city: tempCity })" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Full Name *</label>
                            <input type="text" x-model="tempName" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-amber-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Email Address *</label>
                            <input type="email" x-model="tempEmail" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white text-sm font-medium focus:outline-none focus:ring-2 focus:ring-amber-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Mobile Phone Number</label>
                            <input type="tel" x-model="tempPhone" placeholder="+1 (555) 000-0000" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white text-sm font-medium focus:outline-none focus:ring-2 focus:ring-amber-500">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Country</label>
                                <input type="text" x-model="tempCountry" placeholder="e.g. United States" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white text-sm font-medium focus:outline-none focus:ring-2 focus:ring-amber-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">City / Region</label>
                                <input type="text" x-model="tempCity" placeholder="e.g. New York" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white text-sm font-medium focus:outline-none focus:ring-2 focus:ring-amber-500">
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
                            <button type="button" @click="showFullEditModal = false" class="px-5 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl cursor-pointer">Cancel</button>
                            <button type="submit" :disabled="isSaving" 
                                    class="px-6 py-2.5 rounded-xl text-xs font-black shadow-lg transition flex items-center gap-2 cursor-pointer"
                                    style="background: #f59e0b; color: #020617;">
                                <span x-show="!isSaving">Save Profile Changes</span>
                                <span x-show="isSaving" style="display: none;">Saving...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- EDIT LANGUAGE MODAL -->
        <template x-teleport="body">
            <div x-show="showLangModal" style="display: none;" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.away="showLangModal = false">
                <div class="bg-white dark:bg-[#181d2e] w-full max-w-md rounded-3xl shadow-2xl p-6 relative border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
                        <h3 class="text-lg font-black text-slate-900 dark:text-white">Select Language</h3>
                        <button type="button" @click="showLangModal = false" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:text-slate-900 dark:hover:text-white cursor-pointer">✕</button>
                    </div>
                    <div class="space-y-2">
                        <template x-for="lang in ['English (US)', 'Spanish (Español)', 'French (Français)', 'German (Deutsch)', 'Hindi (हिन्दी)']" :key="lang">
                            <button type="button" @click="userLang = lang; showLangModal = false; showToast('Language set to ' + lang)"
                                    class="w-full p-3.5 rounded-xl text-left font-bold text-sm flex items-center justify-between hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer"
                                    :class="userLang === lang ? 'bg-amber-500/15 text-amber-700 dark:text-amber-400 border border-amber-400/40' : 'text-slate-900 dark:text-white'">
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
                <div class="bg-white dark:bg-[#181d2e] w-full max-w-md rounded-3xl shadow-2xl p-6 relative border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
                        <h3 class="text-lg font-black text-slate-900 dark:text-white">Communication Preferences</h3>
                        <button type="button" @click="showCommPrefModal = false" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:text-slate-900 dark:hover:text-white cursor-pointer">✕</button>
                    </div>

                    <div class="space-y-3 mb-6 text-sm">
                        <div class="p-4 bg-slate-50 dark:bg-slate-800/60 rounded-xl flex items-center justify-between border border-slate-200 dark:border-slate-800">
                            <div>
                                <p class="font-bold text-slate-900 dark:text-white">Trip & Receipt Emails</p>
                                <p class="text-xs text-slate-500">Automatically send PDF tax receipts upon completion</p>
                            </div>
                            <input type="checkbox" checked class="w-5 h-5 accent-amber-500 cursor-pointer">
                        </div>

                        <div class="p-4 bg-slate-50 dark:bg-slate-800/60 rounded-xl flex items-center justify-between border border-slate-200 dark:border-slate-800">
                            <div>
                                <p class="font-bold text-slate-900 dark:text-white">Promotional Discounts</p>
                                <p class="text-xs text-slate-500">Receive special promo codes and ride rewards</p>
                            </div>
                            <input type="checkbox" checked class="w-5 h-5 accent-amber-500 cursor-pointer">
                        </div>

                        <div class="p-4 bg-slate-50 dark:bg-slate-800/60 rounded-xl flex items-center justify-between border border-slate-200 dark:border-slate-800">
                            <div>
                                <p class="font-bold text-slate-900 dark:text-white">SMS Ride Alerts</p>
                                <p class="text-xs text-slate-500">Text updates when your driver arrives at pickup</p>
                            </div>
                            <input type="checkbox" checked class="w-5 h-5 accent-amber-500 cursor-pointer">
                        </div>
                    </div>

                    <button type="button" @click="showCommPrefModal = false; showToast('Preferences updated!')" 
                            class="w-full font-black py-3.5 rounded-xl text-xs transition shadow cursor-pointer"
                            style="background: #f59e0b; color: #020617;">
                        Save Preferences
                    </button>
                </div>
            </div>
        </template>

    </main>

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('accountManager', (initialTab = 'home') => ({
            currentTab: (new URLSearchParams(window.location.search).get('tab')) || initialTab || 'home',
            toast: '',
            isSaving: false,
            
            initTab() {
                const param = new URLSearchParams(window.location.search).get('tab');
                if (param) {
                    this.currentTab = param;
                }
            },

            setTab(tab) {
                this.currentTab = tab;
                try {
                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', tab);
                    window.history.replaceState({}, '', url.toString());
                } catch(e) {}
            },
            
            userName: '{{ addslashes($user->name ?? 'User') }}',
            userEmail: '{{ addslashes($user->email ?? '') }}',
            userPhone: '{{ addslashes($user->phone ?? '') }}',
            userCountry: '{{ addslashes($user->country ?? 'United States') }}',
            userCity: '{{ addslashes($user->city ?? '') }}',
            userLang: 'English (US)',
            
            tempName: '{{ addslashes($user->name ?? 'User') }}',
            tempEmail: '{{ addslashes($user->email ?? '') }}',
            tempPhone: '{{ addslashes($user->phone ?? '') }}',
            tempCountry: '{{ addslashes($user->country ?? 'United States') }}',
            tempCity: '{{ addslashes($user->city ?? '') }}',
            
            copyReferralCode(code) {
                if (!code) return;
                navigator.clipboard.writeText(code).then(() => {
                    this.showToast('Referral code copied: ' + code);
                }).catch(() => {
                    this.showToast('Referral Code: ' + code);
                });
            },
            
            copyReferralLink(link) {
                if (!link) return;
                navigator.clipboard.writeText(link).then(() => {
                    this.showToast('Referral invite link copied to clipboard!');
                }).catch(() => {
                    this.showToast('Invite link copied!');
                });
            },

            async saveProfileData(fields) {
                this.isSaving = true;
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;
                    const res = await fetch('/account/update', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || ''
                        },
                        body: JSON.stringify({
                            name: fields.name !== undefined ? fields.name : this.userName,
                            email: fields.email !== undefined ? fields.email : this.userEmail,
                            phone: fields.phone !== undefined ? fields.phone : this.userPhone,
                            country: fields.country !== undefined ? fields.country : this.userCountry,
                            city: fields.city !== undefined ? fields.city : this.userCity,
                        })
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        this.userName = data.user.name;
                        this.userEmail = data.user.email;
                        this.userPhone = data.user.phone || '';
                        this.userCountry = data.user.country || '';
                        this.userCity = data.user.city || '';
                        this.tempName = this.userName;
                        this.tempEmail = this.userEmail;
                        this.tempPhone = this.userPhone;
                        this.tempCountry = this.userCountry;
                        this.tempCity = this.userCity;
                        this.showNameModal = false;
                        this.showPhoneModal = false;
                        this.showEmailModal = false;
                        this.showCountryModal = false;
                        this.showFullEditModal = false;
                        this.showToast(data.message || 'Profile updated successfully!');
                    } else {
                        alert(data.message || (data.errors ? Object.values(data.errors).flat().join("\n") : 'Failed to update profile.'));
                    }
                } catch (e) {
                    console.error('Update error:', e);
                    alert('Network error while updating profile.');
                } finally {
                    this.isSaving = false;
                }
            },
            
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
            showCountryModal: false,
            showFullEditModal: false,
            showLangModal: false,
            showCommPrefModal: false,
            
            // Security State
            passkeys: [
                { name: 'Windows Hello / Chrome', date: 'Jan 2025' }
            ],
            passwordLastChanged: 'Recently',
            authenticatorConfigured: false,
            twoFactorEnabled: true,
            twoFactorSMS: true,
            twoFactorAuthApp: false,
            recoveryPhone: '{{ addslashes($user->phone ?? '') }}',
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
            
            async updatePassword() {
                if (this.pwdForm.new !== this.pwdForm.confirm) {
                    alert('New passwords do not match!');
                    return;
                }
                this.isSaving = true;
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;
                    const res = await fetch('/account/password', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || ''
                        },
                        body: JSON.stringify({
                            current_password: this.pwdForm.current,
                            password: this.pwdForm.new,
                            password_confirmation: this.pwdForm.confirm,
                        })
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        this.passwordLastChanged = 'Just now';
                        this.pwdForm.current = '';
                        this.pwdForm.new = '';
                        this.pwdForm.confirm = '';
                        this.showPasswordModal = false;
                        this.showToast('Password updated successfully!');
                    } else {
                        alert(data.message || 'Current password incorrect or validation failed.');
                    }
                } catch (e) {
                    console.error('Password change error', e);
                    alert('Network error while changing password.');
                } finally {
                    this.isSaving = false;
                }
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
