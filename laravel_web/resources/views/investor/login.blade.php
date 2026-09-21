<x-layout title="Investor Portal Login | NDFG LLC">
    <div class="min-h-screen bg-[#070a0f] text-slate-100 flex items-center justify-center py-16 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
        <!-- Ambient accents -->
        <div class="absolute -top-32 -left-32 w-80 h-80 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -right-32 w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-md w-full space-y-8 relative z-10">
            <!-- Header -->
            <div class="text-center">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-xs font-black uppercase tracking-wider mb-3">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>NDFG LLC Private Placement</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Investor Portal Login</h1>
                <p class="text-xs text-zinc-400 mt-2">
                    Enter your verified investor credentials to access the data room and payment vault.
                </p>
            </div>

            <!-- Login Card -->
            <div class="rounded-3xl bg-white/[0.02] border border-white/[0.08] shadow-2xl p-7 sm:p-9 backdrop-blur-xl">
                @if(session('error'))
                    <div class="p-4 mb-6 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs font-bold">
                        {{ session('error') }}
                    </div>
                @endif
                @if(session('success'))
                    <div class="p-4 mb-6 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs font-bold">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="/investor/login" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-zinc-300 uppercase tracking-wider mb-2">Registered Email Address *</label>
                        <input type="email" name="email" required value="{{ old('email') }}" placeholder="investor@domain.com" class="w-full px-4 py-3.5 rounded-xl bg-white/[0.04] border border-white/10 text-white text-sm focus:outline-none focus:border-brand-500 transition-colors">
                        @error('email') <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-bold text-zinc-300 uppercase tracking-wider">Password *</label>
                        </div>
                        <input type="password" name="password" required placeholder="••••••••••••" class="w-full px-4 py-3.5 rounded-xl bg-white/[0.04] border border-white/10 text-white text-sm focus:outline-none focus:border-brand-500 transition-colors">
                        @error('password') <span class="text-rose-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center justify-between text-xs">
                        <label class="flex items-center gap-2 cursor-pointer text-zinc-400">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded text-brand-500 focus:ring-brand-400 border-white/20 bg-white/5">
                            <span>Keep me signed in</span>
                        </label>
                    </div>

                    <button type="submit" class="w-full py-4 rounded-xl bg-brand-500 hover:bg-brand-400 text-black font-black text-sm uppercase tracking-wider transition-all shadow-lg shadow-brand-500/25 hover:scale-[1.01]">
                        Log In to Investor Vault →
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t border-white/[0.06] text-center text-xs text-zinc-400">
                    Not yet accredited?
                    <a href="/investor/register" class="text-brand-400 hover:text-brand-300 font-bold ml-1">
                        Start Multi-Step Onboarding →
                    </a>
                </div>
            </div>

            <!-- Escrow Footer Notice -->
            <div class="text-center text-[11px] text-zinc-500">
                🔒 In coordination with Eminsang Group Limited (Ghana). All logins are digitally logged in the NDFG compliance audit ledger.
            </div>
        </div>
    </div>
</x-layout>
