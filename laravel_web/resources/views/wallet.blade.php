<x-layout :robots="'noindex, nofollow'">
    <x-slot:title>Wallet & Withdrawals — RideMyCars</x-slot>

    @php
        $currUser = auth()->user() ?? (\App\Models\User::where('role', 'driver')->first() ?? \App\Models\User::first());
        $summary = $summary ?? ($currUser ? \App\Services\WalletService::getWalletSummary($currUser) : [
            'balance' => 215.95,
            'currency' => '₹',
            'pending_withdrawals_sum' => 0.0,
            'approved_withdrawals_sum' => 0.0,
            'withdrawals_count' => ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'total' => 0],
            'settings' => \App\Services\WalletService::getSettings(),
        ]);
        $withdrawalsList = $withdrawals ?? ($currUser ? \App\Models\WalletWithdrawal::where('user_id', $currUser->id)->latest()->take(25)->get() : collect());
        $savedPayouts = $payoutMethods ?? ($currUser ? \App\Models\UserPayoutMethod::where('user_id', $currUser->id)->orderBy('is_default', 'desc')->get() : collect());
        $transactionsList = $transactions ?? ($currUser ? \App\Models\WalletTransaction::where('user_id', $currUser->id)->latest()->take(25)->get() : collect());
    @endphp

    <main class="flex-1 w-full max-w-4xl mx-auto px-4 py-8 sm:px-6 lg:px-8 bg-white dark:bg-[#0a0a0a]"
          x-data="walletManager({
              initialBalance: {{ (float) ($summary['balance'] ?? 0.00) }},
              currency: '{{ $summary['currency'] ?? '₹' }}',
              settings: {{ json_encode($summary['settings'] ?? \App\Services\WalletService::getSettings()) }},
              withdrawals: {{ json_encode($withdrawalsList) }},
              payoutMethods: {{ json_encode($savedPayouts) }},
              transactions: {{ json_encode($transactionsList) }}
          })">
        
        <!-- Success / Error Toast Notification -->
        <template x-teleport="body">
            <div x-show="toast.message" style="display: none;" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-[-20px]"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-[-20px]"
                 class="fixed top-24 right-4 sm:right-8 z-[999999] max-w-md w-full text-white shadow-2xl rounded-2xl p-4 flex items-center justify-between font-bold text-sm"
                 :class="toast.type === 'error' ? 'bg-rose-600' : 'bg-emerald-600'">
                <div class="flex items-center gap-3">
                    <span class="text-xl" x-text="toast.type === 'error' ? '⚠️' : '🎉'"></span>
                    <span x-text="toast.message"></span>
                </div>
                <button @click="toast.message = ''" class="text-white/80 hover:text-white font-bold ml-4 text-base">✕</button>
            </div>
        </template>

        <!-- Back & Title Header -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <a href="{{ auth()->user() && auth()->user()->role === 'driver' ? '/driver/dashboard' : '/my-rides' }}" 
                   class="w-10 h-10 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-white/20 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                </a>
                <h1 class="text-2xl font-black text-gray-900 dark:text-white">Wallet</h1>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="activeTab = 'withdrawals'" 
                        class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all border"
                        :class="activeTab === 'withdrawals' ? 'bg-amber-500 text-gray-950 border-amber-500' : 'bg-transparent text-gray-600 dark:text-gray-400 border-gray-200 dark:border-white/10 hover:border-gray-400'">
                    Withdrawal History (<span x-text="withdrawals.length"></span>)
                </button>
            </div>
        </div>

        <!-- HERO WALLET CARD (Matching Reference 1 & 2) -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#f97316] via-[#ea580c] to-[#c2410c] text-white p-7 sm:p-8 shadow-xl shadow-orange-500/20 mb-8 border border-white/10">
            <!-- Decorative Background Graphic -->
            <div class="absolute -right-8 -bottom-8 w-48 h-48 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
            <div class="absolute -left-8 -top-8 w-40 h-40 rounded-full bg-black/10 blur-xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col items-center text-center">
                <span class="text-xs sm:text-sm font-semibold tracking-wide uppercase text-white/80 mb-2">
                    Wallet Balance
                </span>
                
                <div class="text-4xl sm:text-5xl font-black tracking-tight text-white mb-6 flex items-baseline justify-center gap-1.5">
                    <span x-text="balance.toFixed(2)"></span>
                    <span class="text-3xl sm:text-4xl font-extrabold text-white/90" x-text="currency"></span>
                </div>

                <!-- Action Buttons Row: Add Money & Withdraw (Circled in Reference 1) -->
                <div class="w-full max-w-md grid grid-cols-2 gap-3 sm:gap-4 pt-2 border-t border-white/20">
                    <button type="button" @click="showAddMoneyModal = true"
                            class="flex items-center justify-center gap-2 py-3 px-4 rounded-2xl bg-white/20 hover:bg-white/30 text-white font-bold text-sm transition-all shadow-sm active:scale-95 border border-white/20 backdrop-blur-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v8"/><path d="M8 12h8"/></svg>
                        <span>Add Money</span>
                    </button>

                    <button type="button" @click="openWithdrawModal()"
                            id="wallet-withdraw-btn"
                            class="flex items-center justify-center gap-2 py-3 px-4 rounded-2xl bg-white hover:bg-gray-100 text-orange-950 font-black text-sm transition-all shadow-lg shadow-black/10 active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v8"/><path d="m8 12 4 4 4-4"/></svg>
                        <span>Withdraw</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- NAVIGATION TABS -->
        <div class="flex items-center border-b border-gray-200 dark:border-white/10 mb-6 gap-6 sm:gap-8 text-sm font-bold">
            <button type="button" @click="activeTab = 'transactions'" 
                    class="pb-3 border-b-2 transition-all flex items-center gap-2 cursor-pointer"
                    :class="activeTab === 'transactions' ? 'border-orange-500 text-orange-600 dark:text-orange-400 font-black' : 'border-transparent text-gray-500 hover:text-gray-900 dark:hover:text-white'">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 16V4M7 4L3 8M7 4L11 8M17 8V20M17 20L21 16M17 20L13 16"/></svg>
                <span>Recent Transactions</span>
            </button>

            <button type="button" @click="activeTab = 'withdrawals'" 
                    class="pb-3 border-b-2 transition-all flex items-center gap-2 cursor-pointer relative"
                    :class="activeTab === 'withdrawals' ? 'border-orange-500 text-orange-600 dark:text-orange-400 font-black' : 'border-transparent text-gray-500 hover:text-gray-900 dark:hover:text-white'">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                <span>Recent Withdrawal</span>
                <template x-if="pendingCount > 0">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                </template>
            </button>

            <button type="button" @click="activeTab = 'payout_methods'" 
                    class="pb-3 border-b-2 transition-all flex items-center gap-2 cursor-pointer"
                    :class="activeTab === 'payout_methods' ? 'border-orange-500 text-orange-600 dark:text-orange-400 font-black' : 'border-transparent text-gray-500 hover:text-gray-900 dark:hover:text-white'">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                <span>Payout Accounts</span>
            </button>
        </div>

        <!-- ======================================================== -->
        <!-- TAB 1: RECENT TRANSACTIONS (Matching Reference 1) -->
        <!-- ======================================================== -->
        <div x-show="activeTab === 'transactions'" x-transition:enter="transition ease-out duration-200">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Recent Transactions</h3>
                <span class="text-xs text-gray-500" x-text="transactions.length + ' records'"></span>
            </div>

            <template x-if="transactions.length === 0">
                <div class="p-8 text-center rounded-2xl border border-gray-100 dark:border-white/10 bg-gray-50/50 dark:bg-white/[0.02]">
                    <div class="w-12 h-12 rounded-2xl bg-orange-500/10 text-orange-500 flex items-center justify-center mx-auto mb-3 font-bold text-xl">
                        💳
                    </div>
                    <p class="text-sm font-bold text-gray-800 dark:text-gray-200">No transactions recorded yet</p>
                    <p class="text-xs text-gray-500 mt-1">Earnings, ride commissions, incentives, and approved withdrawals will appear here.</p>
                </div>
            </template>

            <div class="space-y-3">
                <template x-for="(trx, idx) in transactions" :key="trx.id || idx">
                    <div class="flex items-center justify-between p-4 rounded-2xl border border-gray-100 dark:border-white/10 bg-white dark:bg-[#121212] shadow-sm hover:border-orange-500/30 transition-all">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0"
                                 :class="trx.type === 'withdrawal' ? 'bg-orange-100 text-orange-600 dark:bg-orange-950/40 dark:text-orange-400' : 'bg-emerald-100 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400'">
                                <template x-if="trx.type === 'withdrawal'">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                                </template>
                                <template x-if="trx.type !== 'withdrawal'">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m12 5 7 7-7 7"/><path d="M5 12h14"/></svg>
                                </template>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white" x-text="trx.description || trx.type"></h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5" x-text="formatDate(trx.created_at)"></p>
                            </div>
                        </div>

                        <div class="text-right">
                            <div class="text-base font-black"
                                 :class="trx.type === 'withdrawal' ? 'text-red-500 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400'">
                                <span x-text="trx.type === 'withdrawal' ? '- ' : '+ '"></span>
                                <span x-text="(trx.currency || currency) + parseFloat(trx.amount).toFixed(2)"></span>
                            </div>
                            <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full"
                                  :class="trx.status === 'completed' ? 'bg-emerald-100 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400' : 'bg-gray-100 text-gray-600'">
                                <span x-text="trx.status"></span>
                            </span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- TAB 2: RECENT WITHDRAWAL (Matching Reference 2) -->
        <!-- ======================================================== -->
        <div x-show="activeTab === 'withdrawals'" x-transition:enter="transition ease-out duration-200">
            <!-- Filter Pills -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Withdrawal History</h3>
                <div class="flex items-center gap-1.5 p-1 bg-gray-100 dark:bg-white/5 rounded-xl border border-gray-200 dark:border-white/10 text-xs font-bold">
                    <button type="button" @click="filterStatus = 'all'" 
                            :class="filterStatus === 'all' ? 'bg-white dark:bg-white/20 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'"
                            class="px-2.5 py-1 rounded-lg transition-all">All</button>
                    <button type="button" @click="filterStatus = 'pending'" 
                            :class="filterStatus === 'pending' ? 'bg-amber-500 text-gray-950 shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'"
                            class="px-2.5 py-1 rounded-lg transition-all">Pending</button>
                    <button type="button" @click="filterStatus = 'approved'" 
                            :class="filterStatus === 'approved' ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'"
                            class="px-2.5 py-1 rounded-lg transition-all">Approved</button>
                    <button type="button" @click="filterStatus = 'rejected'" 
                            :class="filterStatus === 'rejected' ? 'bg-red-500 text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'"
                            class="px-2.5 py-1 rounded-lg transition-all">Rejected</button>
                </div>
            </div>

            <!-- EMPTY STATE (Matching Reference 2) -->
            <template x-if="filteredWithdrawals.length === 0">
                <div class="py-16 px-4 text-center rounded-3xl border border-gray-100 dark:border-white/10 bg-gray-50/50 dark:bg-white/[0.02]">
                    <div class="w-32 h-32 mx-auto mb-6 flex items-center justify-center rounded-full bg-orange-500/10 text-orange-500">
                        <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/><path d="M7 15h.01"/><path d="M11 15h2"/></svg>
                    </div>
                    <h4 class="text-base font-bold text-gray-900 dark:text-white">No payment history yet.</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-sm mx-auto">
                        Start your journey by booking a ride or providing trips today! All your payout requests will be listed here.
                    </p>
                    <div class="mt-6">
                        <button type="button" @click="openWithdrawModal()"
                                class="px-7 py-3 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-gray-950 font-black rounded-2xl text-sm transition-all shadow-lg shadow-orange-500/25 active:scale-95">
                            Request Withdraw
                        </button>
                    </div>
                </div>
            </template>

            <!-- WITHDRAWAL LIST -->
            <div class="space-y-4" x-show="filteredWithdrawals.length > 0">
                <template x-for="item in filteredWithdrawals" :key="item.id || item.withdrawal_ref">
                    <div class="p-5 rounded-2xl border border-gray-100 dark:border-white/10 bg-white dark:bg-[#121212] shadow-sm hover:shadow-md transition-all">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100 dark:border-white/10">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-mono font-bold text-gray-500" x-text="item.withdrawal_ref"></span>
                                    <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full"
                                          :class="{
                                              'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300 dark:border-amber-700': item.status === 'pending',
                                              'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700': item.status === 'approved',
                                              'bg-red-100 text-red-800 dark:bg-red-950/60 dark:text-red-300 border border-red-300 dark:border-red-700': item.status === 'rejected'
                                          }"
                                          x-text="item.status"></span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1" x-text="'Requested: ' + formatDate(item.created_at)"></p>
                            </div>

                            <div class="text-left sm:text-right">
                                <div class="text-xl font-black text-gray-900 dark:text-white"
                                     x-text="(item.currency || currency) + parseFloat(item.amount).toFixed(2)"></div>
                                <div class="text-[11px] font-bold text-gray-500" x-text="item.payout_method === 'momo' ? '📱 Mobile Money (MoMo)' : '🏦 Bank Transfer'"></div>
                            </div>
                        </div>

                        <!-- Payout Account Snapshot -->
                        <div class="pt-3 flex flex-col sm:flex-row sm:items-center justify-between text-xs text-gray-600 dark:text-gray-300 gap-2">
                            <div>
                                <template x-if="item.payout_method === 'momo'">
                                    <span>
                                        <strong>Network:</strong> <span x-text="item.payout_details?.momo_network || 'MoMo'"></span> • 
                                        <strong>Number:</strong> <span x-text="item.payout_details?.momo_phone || '—'"></span>
                                    </span>
                                </template>
                                <template x-if="item.payout_method !== 'momo'">
                                    <span>
                                        <strong>Bank:</strong> <span x-text="item.payout_details?.bank_name || 'Bank'"></span> • 
                                        <strong>Acc:</strong> <span x-text="item.payout_details?.account_number ? '•••• ' + item.payout_details.account_number.slice(-4) : '••••'"></span>
                                    </span>
                                </template>
                            </div>

                            <!-- Approved / Transaction Ref -->
                            <template x-if="item.status === 'approved'">
                                <div class="text-emerald-600 dark:text-emerald-400 font-bold">
                                    ✓ Disbursed <span x-show="item.transaction_reference" x-text="'• Ref: ' + item.transaction_reference"></span>
                                </div>
                            </template>
                        </div>

                        <!-- REJECTION REASON CALLOUT (If Rejected) -->
                        <template x-if="item.status === 'rejected'">
                            <div class="mt-3 p-3 rounded-xl bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/40 text-xs">
                                <p class="font-bold text-red-700 dark:text-red-300">Declined by Administration:</p>
                                <p class="text-red-600 dark:text-red-400 mt-0.5" x-text="item.rejection_reason || 'Please verify payout account details and request again.'"></p>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Sticky Bottom Request Withdraw CTA (Matching Reference 2) -->
            <div class="mt-8 pt-4 border-t border-gray-100 dark:border-white/10 text-center">
                <button type="button" @click="openWithdrawModal()"
                        class="w-full sm:w-auto px-10 py-3.5 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-gray-950 font-black rounded-2xl text-sm transition-all shadow-lg shadow-orange-500/25 active:scale-95 inline-flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                    <span>Request Withdraw</span>
                </button>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- TAB 3: PAYOUT ACCOUNTS (Bank & MoMo) -->
        <!-- ======================================================== -->
        <div x-show="activeTab === 'payout_methods'" x-transition:enter="transition ease-out duration-200">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Linked Payout Accounts</h3>
                    <p class="text-xs text-gray-500">Accounts where approved withdrawals are directly disbursed.</p>
                </div>
                <button type="button" @click="showPayoutMethodModal = true"
                        class="px-4 py-2 rounded-xl bg-black dark:bg-white text-white dark:text-black font-bold text-xs transition-all flex items-center gap-1.5 shadow-sm active:scale-95">
                    + Add Account
                </button>
            </div>

            <template x-if="payoutMethods.length === 0">
                <div class="p-8 text-center rounded-2xl border border-gray-100 dark:border-white/10 bg-gray-50/50 dark:bg-white/[0.02]">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center mx-auto mb-3 font-bold text-xl">
                        🏦
                    </div>
                    <p class="text-sm font-bold text-gray-800 dark:text-gray-200">No linked payout account yet</p>
                    <p class="text-xs text-gray-500 mt-1">Add your Bank Account or Mobile Money (MoMo) number to withdraw your earnings.</p>
                    <button type="button" @click="showPayoutMethodModal = true" class="mt-4 px-4 py-2 bg-orange-500 text-gray-950 font-bold text-xs rounded-xl">
                        + Add Payout Account
                    </button>
                </div>
            </template>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <template x-for="(pm, idx) in payoutMethods" :key="pm.id || idx">
                    <div class="p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#121212] relative shadow-sm hover:border-orange-500/40 transition-all flex flex-col justify-between min-h-[140px]">
                        <div>
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg" x-text="pm.type === 'momo' ? '📱' : '🏦'"></span>
                                    <h4 class="font-bold text-sm text-gray-900 dark:text-white" x-text="pm.type === 'momo' ? (pm.momo_network || 'MoMo') : (pm.bank_name || 'Bank Account')"></h4>
                                </div>
                                <template x-if="pm.is_default">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300">Default</span>
                                </template>
                            </div>
                            <div class="mt-3 text-xs text-gray-600 dark:text-gray-300 space-y-0.5">
                                <p x-text="pm.type === 'momo' ? 'Phone: ' + pm.momo_phone : 'Acc: •••• ' + (pm.account_number ? pm.account_number.slice(-4) : '••••')"></p>
                                <p class="text-[11px] text-gray-500" x-text="'Holder: ' + (pm.account_holder_name || pm.momo_account_name || '{{ auth()->user()->name ?? 'Account Holder' }}')"></p>
                            </div>
                        </div>

                        <div class="pt-3 mt-2 border-t border-gray-100 dark:border-white/10 flex justify-between items-center text-xs">
                            <span class="text-gray-400 text-[11px]" x-text="pm.type === 'momo' ? 'Mobile Money' : (pm.routing_code ? 'IFSC: ' + pm.routing_code : 'Direct Transfer')"></span>
                            <button type="button" @click="deletePayoutMethod(pm.id)" class="text-red-500 hover:text-red-700 font-bold text-xs">Remove</button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- MODAL: WITHDRAWAL REQUEST (Matching Reference 3) -->
        <!-- ======================================================== -->
        <template x-teleport="body">
            <div x-show="showWithdrawModal" style="display: none;"
                 class="fixed inset-0 z-[99999] flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/60 backdrop-blur-sm"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95">
                
                <div class="bg-white dark:bg-[#181818] w-full max-w-lg rounded-t-3xl sm:rounded-3xl shadow-2xl p-6 relative border border-gray-100 dark:border-white/10 max-h-[90vh] overflow-y-auto"
                     @click.away="showWithdrawModal = false">
                    
                    <!-- Top Handle on Mobile -->
                    <div class="w-12 h-1.5 rounded-full bg-gray-300 dark:bg-white/20 mx-auto mb-4 sm:hidden"></div>

                    <!-- Modal Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-white/10 mb-5">
                        <div>
                            <h3 class="text-lg font-black text-gray-900 dark:text-white">Withdraw Funds</h3>
                            <p class="text-xs text-gray-500">Disburse your earnings to Bank or Mobile Money</p>
                        </div>
                        <button type="button" @click="showWithdrawModal = false" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center text-gray-500 hover:text-gray-900 dark:hover:text-white">✕</button>
                    </div>

                    <!-- Available Balance Badge -->
                    <div class="p-3.5 rounded-2xl bg-orange-50 dark:bg-orange-950/30 border border-orange-200 dark:border-orange-800/40 flex items-center justify-between mb-5">
                        <span class="text-xs font-bold text-orange-900 dark:text-orange-200">Available Wallet Balance</span>
                        <span class="text-base font-black text-orange-600 dark:text-orange-400" x-text="(currency || '₹') + balance.toFixed(2)"></span>
                    </div>

                    <form @submit.prevent="submitWithdrawal()">
                        <!-- 1. Amount Input (Matching Reference 3) -->
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Enter Amount</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-lg font-black text-gray-500" x-text="currency || '₹'"></span>
                                <input type="number" step="0.01" min="1" x-model="withdrawForm.amount" required
                                       placeholder="Enter Amount Here"
                                       class="w-full pl-11 pr-4 py-3.5 bg-gray-50 dark:bg-[#222] border-2 rounded-2xl text-gray-900 dark:text-white font-black text-lg focus:outline-none transition-all"
                                       :class="amountError ? 'border-red-500 focus:border-red-500' : 'border-gray-200 dark:border-white/10 focus:border-orange-500'">
                            </div>

                            <!-- Validation Error Message -->
                            <template x-if="amountError">
                                <p class="text-xs font-bold text-red-500 mt-1.5 flex items-center gap-1">
                                    <span>⚠️</span>
                                    <span x-text="amountError"></span>
                                </p>
                            </template>
                        </div>

                        <!-- 2. Quick Preset Chips (Matching Reference 3) -->
                        <div class="flex items-center gap-2 mb-6 flex-wrap">
                            <template x-for="chip in [50, 100, 150, 200]" :key="chip">
                                <button type="button" @click="setAmount(chip)"
                                        class="px-4 py-2 rounded-xl border text-xs font-bold transition-all shadow-sm active:scale-95"
                                        :class="parseFloat(withdrawForm.amount) === chip ? 'bg-orange-500 text-gray-950 border-orange-500 font-black' : 'bg-gray-50 dark:bg-white/5 border-gray-200 dark:border-white/10 text-gray-800 dark:text-gray-200 hover:border-orange-500/40'">
                                    <span x-text="(currency || '₹') + ' ' + chip.toFixed(1)"></span>
                                </button>
                            </template>
                            <button type="button" @click="setAmount(balance)"
                                    class="px-3.5 py-2 rounded-xl border border-dashed text-xs font-bold transition-all"
                                    :class="parseFloat(withdrawForm.amount) === balance && balance > 0 ? 'bg-orange-500 text-gray-950 font-black border-orange-500' : 'border-orange-400 text-orange-500 hover:bg-orange-500/10'">
                                Max Balance
                            </button>
                        </div>

                        <!-- 3. Payout Method Selector -->
                        <div class="mb-5">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Select Payout Method</label>
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button" @click="withdrawForm.payout_method = 'bank_account'"
                                        class="p-3.5 rounded-2xl border-2 flex items-center gap-2.5 transition-all text-left"
                                        :class="withdrawForm.payout_method === 'bank_account' ? 'border-orange-500 bg-orange-50/50 dark:bg-orange-950/20 text-orange-950 dark:text-orange-200 font-bold' : 'border-gray-200 dark:border-white/10 text-gray-600 dark:text-gray-400 hover:border-gray-300'">
                                    <span class="text-xl">🏦</span>
                                    <div>
                                        <div class="text-xs font-black">Bank Account</div>
                                        <div class="text-[10px] opacity-75">Direct NEFT / Transfer</div>
                                    </div>
                                </button>

                                <button type="button" @click="withdrawForm.payout_method = 'momo'"
                                        class="p-3.5 rounded-2xl border-2 flex items-center gap-2.5 transition-all text-left"
                                        :class="withdrawForm.payout_method === 'momo' ? 'border-orange-500 bg-orange-50/50 dark:bg-orange-950/20 text-orange-950 dark:text-orange-200 font-bold' : 'border-gray-200 dark:border-white/10 text-gray-600 dark:text-gray-400 hover:border-gray-300'">
                                    <span class="text-xl">📱</span>
                                    <div>
                                        <div class="text-xs font-black">Mobile Money</div>
                                        <div class="text-[10px] opacity-75">MTN / Telecel / MoMo</div>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- 4. Payout Account Details -->
                        <!-- A. Bank Form -->
                        <div x-show="withdrawForm.payout_method === 'bank_account'" class="space-y-3 mb-5 p-4 rounded-2xl bg-gray-50 dark:bg-[#202020] border border-gray-100 dark:border-white/10">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase mb-1">Bank Name</label>
                                <input type="text" x-model="withdrawForm.bank_details.bank_name" placeholder="e.g. State Bank of India, Chase, GCB Bank"
                                       class="w-full px-3.5 py-2.5 bg-white dark:bg-[#151515] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-medium text-gray-900 dark:text-white focus:outline-none focus:border-orange-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase mb-1">Account Holder Name</label>
                                <input type="text" x-model="withdrawForm.bank_details.account_holder_name" placeholder="Name as per bank records"
                                       class="w-full px-3.5 py-2.5 bg-white dark:bg-[#151515] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-medium text-gray-900 dark:text-white focus:outline-none focus:border-orange-500">
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase mb-1">Account Number</label>
                                    <input type="text" x-model="withdrawForm.bank_details.account_number" placeholder="Full Account Number"
                                           class="w-full px-3.5 py-2.5 bg-white dark:bg-[#151515] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-medium text-gray-900 dark:text-white focus:outline-none focus:border-orange-500">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase mb-1">IFSC / Routing / Sort Code</label>
                                    <input type="text" x-model="withdrawForm.bank_details.routing_code" placeholder="e.g. SBIN0001234 or Routing #"
                                           class="w-full px-3.5 py-2.5 bg-white dark:bg-[#151515] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-medium text-gray-900 dark:text-white focus:outline-none focus:border-orange-500">
                                </div>
                            </div>
                        </div>

                        <!-- B. MoMo Form -->
                        <div x-show="withdrawForm.payout_method === 'momo'" class="space-y-3 mb-5 p-4 rounded-2xl bg-gray-50 dark:bg-[#202020] border border-gray-100 dark:border-white/10">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase mb-1">Mobile Money Network</label>
                                <select x-model="withdrawForm.momo_details.momo_network"
                                        class="w-full px-3.5 py-2.5 bg-white dark:bg-[#151515] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-medium text-gray-900 dark:text-white focus:outline-none focus:border-orange-500">
                                    <option value="MTN">MTN Mobile Money</option>
                                    <option value="Telecel">Telecel / Vodafone Cash</option>
                                    <option value="AirtelTigo">AirtelTigo Money</option>
                                    <option value="MPesa">M-Pesa</option>
                                    <option value="Other">Other Wallet Provider</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase mb-1">MoMo Phone Number</label>
                                    <input type="tel" 
                                           inputmode="tel"
                                           pattern="[0-9+\s\-]*"
                                           x-model="withdrawForm.momo_details.momo_phone" 
                                           @input="withdrawForm.momo_details.momo_phone = withdrawForm.momo_details.momo_phone.replace(/[^0-9+\s\-]/g, '')"
                                           @keypress="if (!/[0-9+\s\-]/.test($event.key) && $event.key.length === 1 && !$event.ctrlKey && !$event.metaKey) $event.preventDefault();"
                                           @paste="setTimeout(() => { withdrawForm.momo_details.momo_phone = withdrawForm.momo_details.momo_phone.replace(/[^0-9+\s\-]/g, ''); }, 0)"
                                           placeholder="e.g. 0244123456"
                                           class="w-full px-3.5 py-2.5 bg-white dark:bg-[#151515] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-medium text-gray-900 dark:text-white focus:outline-none focus:border-orange-500">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase mb-1">Subscriber / Account Name</label>
                                    <input type="text" x-model="withdrawForm.momo_details.momo_account_name" placeholder="Name on MoMo SIM"
                                           class="w-full px-3.5 py-2.5 bg-white dark:bg-[#151515] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-medium text-gray-900 dark:text-white focus:outline-none focus:border-orange-500">
                                </div>
                            </div>
                        </div>

                        <!-- Save Payout Method Checkbox -->
                        <div class="flex items-center gap-2 mb-6">
                            <input type="checkbox" id="save_payout_account" x-model="withdrawForm.save_as_default" class="w-4 h-4 text-orange-500 rounded border-gray-300 focus:ring-orange-500">
                            <label for="save_payout_account" class="text-xs text-gray-600 dark:text-gray-400 font-medium">Save this account as my default payout method</label>
                        </div>

                        <!-- Action Buttons (Matching Reference 3: Cancel & Withdraw) -->
                        <div class="grid grid-cols-2 gap-3 pt-2">
                            <button type="button" @click="showWithdrawModal = false"
                                    class="py-3.5 px-4 rounded-2xl border border-gray-300 dark:border-white/20 text-gray-800 dark:text-gray-200 font-bold text-sm hover:bg-gray-50 dark:hover:bg-white/5 transition-all text-center">
                                Cancel
                            </button>

                            <button type="submit" :disabled="submitting || Boolean(amountError)"
                                    class="py-3.5 px-4 rounded-2xl bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 disabled:opacity-50 text-gray-950 font-black text-sm transition-all shadow-lg shadow-orange-500/25 active:scale-95 text-center flex items-center justify-center gap-2">
                                <span x-show="!submitting">Withdraw</span>
                                <span x-show="submitting">Processing...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- MODAL: ADD MONEY / TOP UP -->
        <template x-teleport="body">
            <div x-show="showAddMoneyModal" style="display: none;" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.away="showAddMoneyModal = false">
                <div class="bg-white dark:bg-[#181818] w-full max-w-md rounded-3xl shadow-2xl p-6 relative border border-gray-100 dark:border-white/10">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-white/10 mb-4">
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Add Funds to Wallet</h3>
                        <button type="button" @click="showAddMoneyModal = false" class="text-gray-500 hover:text-white">✕</button>
                    </div>
                    <p class="text-xs text-gray-500 mb-4">Top-up your wallet using Credit/Debit Card or instant Mobile Money.</p>
                    <div class="space-y-3">
                        <a href="/checkout?type=wallet_topup&amount=50" class="block w-full p-3.5 rounded-2xl bg-orange-500/10 border border-orange-500/30 hover:border-orange-500 text-center font-bold text-sm text-orange-600 dark:text-orange-400 transition-all">
                            Add <span x-text="currency || '₹'"></span> 50.00
                        </a>
                        <a href="/checkout?type=wallet_topup&amount=100" class="block w-full p-3.5 rounded-2xl bg-orange-500/10 border border-orange-500/30 hover:border-orange-500 text-center font-bold text-sm text-orange-600 dark:text-orange-400 transition-all">
                            Add <span x-text="currency || '₹'"></span> 100.00
                        </a>
                        <a href="/checkout?type=wallet_topup&amount=250" class="block w-full p-3.5 rounded-2xl bg-orange-500/10 border border-orange-500/30 hover:border-orange-500 text-center font-bold text-sm text-orange-600 dark:text-orange-400 transition-all">
                            Add <span x-text="currency || '₹'"></span> 250.00
                        </a>
                    </div>
                </div>
            </div>
        </template>

        <!-- MODAL: ADD PAYOUT ACCOUNT -->
        <template x-teleport="body">
            <div x-show="showPayoutMethodModal" style="display: none;" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.away="showPayoutMethodModal = false">
                <div class="bg-white dark:bg-[#181818] w-full max-w-md rounded-3xl shadow-2xl p-6 relative border border-gray-100 dark:border-white/10">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-white/10 mb-4">
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Add Payout Account</h3>
                        <button type="button" @click="showPayoutMethodModal = false" class="text-gray-500 hover:text-white">✕</button>
                    </div>

                    <form @submit.prevent="saveNewPayoutMethod()">
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 uppercase mb-1">Account Type</label>
                            <select x-model="newMethodForm.type" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-medium text-gray-900 dark:text-white">
                                <option value="bank_account">Bank Account</option>
                                <option value="momo">Mobile Money (MoMo)</option>
                            </select>
                        </div>

                        <template x-if="newMethodForm.type === 'bank_account'">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase mb-1">Bank Name</label>
                                    <input type="text" x-model="newMethodForm.bank_name" required placeholder="Bank Name" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-medium text-gray-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase mb-1">Account Number</label>
                                    <input type="text" x-model="newMethodForm.account_number" required placeholder="Account Number" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-medium text-gray-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase mb-1">Account Holder Name</label>
                                    <input type="text" x-model="newMethodForm.account_holder_name" required placeholder="Full Name" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-medium text-gray-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase mb-1">Routing / IFSC Code</label>
                                    <input type="text" x-model="newMethodForm.routing_code" placeholder="Optional" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-medium text-gray-900 dark:text-white">
                                </div>
                            </div>
                        </template>

                        <template x-if="newMethodForm.type === 'momo'">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase mb-1">Network</label>
                                    <select x-model="newMethodForm.momo_network" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-medium text-gray-900 dark:text-white">
                                        <option value="MTN">MTN Mobile Money</option>
                                        <option value="Telecel">Telecel / Vodafone</option>
                                        <option value="AirtelTigo">AirtelTigo Money</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase mb-1">MoMo Phone Number</label>
                                    <input type="tel" 
                                           inputmode="tel"
                                           pattern="[0-9+\s\-]*"
                                           x-model="newMethodForm.momo_phone" 
                                           @input="newMethodForm.momo_phone = newMethodForm.momo_phone.replace(/[^0-9+\s\-]/g, '')"
                                           @keypress="if (!/[0-9+\s\-]/.test($event.key) && $event.key.length === 1 && !$event.ctrlKey && !$event.metaKey) $event.preventDefault();"
                                           @paste="setTimeout(() => { newMethodForm.momo_phone = newMethodForm.momo_phone.replace(/[^0-9+\s\-]/g, ''); }, 0)"
                                           required 
                                           placeholder="0244..." 
                                           class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-medium text-gray-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 dark:text-gray-400 uppercase mb-1">Account Holder Name</label>
                                    <input type="text" x-model="newMethodForm.momo_account_name" required placeholder="Account Name" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-[#222] border border-gray-200 dark:border-white/10 rounded-xl text-xs font-medium text-gray-900 dark:text-white">
                                </div>
                            </div>
                        </template>

                        <div class="mt-6 pt-3 border-t border-gray-100 dark:border-white/10 flex justify-end gap-2">
                            <button type="button" @click="showPayoutMethodModal = false" class="px-4 py-2 text-xs font-bold text-gray-600 hover:text-white">Cancel</button>
                            <button type="submit" class="px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-gray-950 font-bold text-xs rounded-xl shadow-md">Save Payout Method</button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </main>

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('walletManager', (config = {}) => ({
            balance: config.initialBalance || 0.00,
            currency: config.currency || '₹',
            settings: config.settings || { min_amount: 50, max_amount: 10000, enabled: true },
            withdrawals: config.withdrawals || [],
            payoutMethods: config.payoutMethods || [],
            transactions: config.transactions || [],

            activeTab: 'transactions', // transactions, withdrawals, payout_methods
            filterStatus: 'all',

            showWithdrawModal: false,
            showAddMoneyModal: false,
            showPayoutMethodModal: false,
            submitting: false,

            toast: { message: '', type: 'success' },

            withdrawForm: {
                amount: '',
                payout_method: 'bank_account',
                save_as_default: false,
                bank_details: {
                    bank_name: '',
                    account_holder_name: '{{ auth()->user()->name ?? 'Account Holder' }}',
                    account_number: '',
                    routing_code: ''
                },
                momo_details: {
                    momo_network: 'MTN',
                    momo_phone: '{{ auth()->user()->phone ?? '' }}',
                    momo_account_name: '{{ auth()->user()->name ?? 'Account Holder' }}'
                }
            },

            newMethodForm: {
                type: 'bank_account',
                bank_name: '',
                account_holder_name: '{{ auth()->user()->name ?? '' }}',
                account_number: '',
                routing_code: '',
                momo_network: 'MTN',
                momo_phone: '',
                momo_account_name: '{{ auth()->user()->name ?? '' }}'
            },

            init() {
                // If saved bank exists, preload into withdrawForm
                const defaultBank = this.payoutMethods.find(m => m.type === 'bank_account' && m.is_default) || this.payoutMethods.find(m => m.type === 'bank_account');
                if (defaultBank) {
                    this.withdrawForm.bank_details.bank_name = defaultBank.bank_name || '';
                    this.withdrawForm.bank_details.account_holder_name = defaultBank.account_holder_name || '';
                    this.withdrawForm.bank_details.account_number = defaultBank.account_number || '';
                    this.withdrawForm.bank_details.routing_code = defaultBank.routing_code || '';
                }

                const defaultMoMo = this.payoutMethods.find(m => m.type === 'momo' && m.is_default) || this.payoutMethods.find(m => m.type === 'momo');
                if (defaultMoMo) {
                    this.withdrawForm.momo_details.momo_network = defaultMoMo.momo_network || 'MTN';
                    this.withdrawForm.momo_details.momo_phone = defaultMoMo.momo_phone || '';
                    this.withdrawForm.momo_details.momo_account_name = defaultMoMo.momo_account_name || '';
                }
            },

            get pendingCount() {
                return this.withdrawals.filter(w => w.status === 'pending').length;
            },

            get filteredWithdrawals() {
                if (this.filterStatus === 'all') return this.withdrawals;
                return this.withdrawals.filter(w => w.status === this.filterStatus);
            },

            get amountError() {
                const val = parseFloat(this.withdrawForm.amount);
                if (!val || isNaN(val)) return null;
                if (val > this.balance) {
                    return `Amount exceeds available wallet balance (${this.currency}${this.balance.toFixed(2)})`;
                }
                const min = this.settings.min_amount || 50;
                if (val < min) {
                    return `Minimum withdrawal amount is ${this.currency}${min.toFixed(2)}`;
                }
                const max = this.settings.max_amount || 10000;
                if (val > max) {
                    return `Maximum withdrawal amount is ${this.currency}${max.toFixed(2)}`;
                }
                return null;
            },

            setAmount(amt) {
                this.withdrawForm.amount = parseFloat(amt).toFixed(2);
            },

            openWithdrawModal() {
                this.showWithdrawModal = true;
                if (!this.withdrawForm.amount || parseFloat(this.withdrawForm.amount) <= 0) {
                    this.withdrawForm.amount = Math.min(this.balance, 50).toFixed(2);
                }
            },

            showToast(message, type = 'success') {
                this.toast = { message, type };
                setTimeout(() => { this.toast.message = ''; }, 5000);
            },

            formatDate(iso) {
                if (!iso) return '—';
                try {
                    const d = new Date(iso);
                    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
                } catch(e) {
                    return iso;
                }
            },

            async submitWithdrawal() {
                if (this.amountError) return;
                const amt = parseFloat(this.withdrawForm.amount);
                if (!amt || amt <= 0) return;

                this.submitting = true;

                const details = this.withdrawForm.payout_method === 'momo'
                    ? { ...this.withdrawForm.momo_details }
                    : { ...this.withdrawForm.bank_details };

                try {
                    const res = await fetch('/wallet/withdraw', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        body: JSON.stringify({
                            amount: amt,
                            payout_method: this.withdrawForm.payout_method,
                            payout_details: details,
                            save_payout_method: this.withdrawForm.save_as_default
                        })
                    });

                    const data = await res.json();
                    if (data.success) {
                        this.showWithdrawModal = false;
                        this.showToast('Withdrawal request submitted! Payout will be processed upon approval.', 'success');
                        
                        // Add to withdrawals list at the top
                        this.withdrawals.unshift(data.data);
                        this.activeTab = 'withdrawals';
                    } else {
                        this.showToast(data.message || 'Withdrawal failed.', 'error');
                    }
                } catch(err) {
                    this.showToast('Unable to connect to server. Please try again.', 'error');
                } finally {
                    this.submitting = false;
                }
            },

            async saveNewPayoutMethod() {
                try {
                    const res = await fetch('/wallet/payout-methods', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        body: JSON.stringify(this.newMethodForm)
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.payoutMethods.unshift(data.data);
                        this.showPayoutMethodModal = false;
                        this.showToast('Payout method saved successfully!');
                    } else {
                        this.showToast(data.message || 'Failed to save payout method.', 'error');
                    }
                } catch(e) {
                    this.showToast('Error saving payout method.', 'error');
                }
            },

            async deletePayoutMethod(id) {
                if (!confirm('Are you sure you want to remove this payout account?')) return;
                try {
                    const res = await fetch('/wallet/payout-methods/' + id, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        }
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.payoutMethods = this.payoutMethods.filter(m => m.id !== id);
                        this.showToast('Payout method removed.');
                    }
                } catch(e) {
                    this.showToast('Error removing method.', 'error');
                }
            }
        }));
    });
    </script>
</x-layout>
