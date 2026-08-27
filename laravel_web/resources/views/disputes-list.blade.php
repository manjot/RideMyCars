<x-layout>
    <x-slot:title>My Disputes & Claims — RideMyCars</x-slot>

    <main class="flex-1 w-full max-w-7xl mx-auto px-4 py-10 sm:px-6 lg:px-8">
        
        <!-- Header Banner -->
        <div class="mb-10 p-6 sm:p-8 bg-slate-900 text-white rounded-3xl shadow-xl relative overflow-hidden border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <span class="inline-flex items-center gap-2 px-3.5 py-1 bg-amber-400/20 border border-amber-400/40 rounded-full text-xs font-bold uppercase tracking-widest text-amber-300 mb-3">
                    Legal & Compliance Arbitration Desk
                </span>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white">Disputes & Claims Dashboard</h1>
                <p class="text-slate-200 text-sm mt-2 max-w-2xl font-medium leading-relaxed">
                    Track status, resolution logs, and administrative actions for submitted fee waiver requests, security deposit holds, and service disputes.
                </p>
            </div>
            
            <a href="/disputes/create" class="px-5 py-3.5 bg-amber-400 hover:bg-amber-500 text-slate-950 font-black text-xs rounded-2xl shadow-md transition-all shrink-0 flex items-center gap-2">
                <span>⚖️ Submit New Dispute (72h Window)</span>
            </a>
        </div>

        @if(session('success'))
            <div class="mb-8 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/40 text-emerald-800 dark:text-emerald-200 text-xs font-bold flex items-center gap-2">
                <span>✅ {{ session('success') }}</span>
            </div>
        @endif

        <div class="bg-white dark:bg-[#141414] border border-gray-100 dark:border-white/10 rounded-3xl p-6 shadow-sm">
            <h2 class="text-lg font-black text-gray-900 dark:text-white mb-6">Filed Dispute Claims</h2>

            @if(isset($disputes) && $disputes->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-white/10 text-gray-400 dark:text-gray-500 uppercase tracking-wider font-extrabold">
                                <th class="pb-3">Dispute Ref</th>
                                <th class="pb-3">Service</th>
                                <th class="pb-3">Booking Ref</th>
                                <th class="pb-3">Category</th>
                                <th class="pb-3">Submitted</th>
                                <th class="pb-3">Within 72h</th>
                                <th class="pb-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @foreach($disputes as $disp)
                                <tr>
                                    <td class="py-4 font-mono font-bold text-gray-900 dark:text-white">{{ $disp->dispute_code }}</td>
                                    <td class="py-4 font-bold uppercase text-gray-700 dark:text-gray-300">{{ $disp->service_type }}</td>
                                    <td class="py-4 font-mono text-gray-500">{{ $disp->booking_reference ?? 'N/A' }}</td>
                                    <td class="py-4 font-medium text-gray-900 dark:text-white">{{ $disp->category }}</td>
                                    <td class="py-4 text-gray-500">{{ $disp->created_at->format('M d, Y H:i') }}</td>
                                    <td class="py-4">
                                        @if($disp->is_within_72h)
                                            <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300 font-bold text-[10px]">✅ Timely (72h)</span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300 font-bold text-[10px]">⚠️ Late Claim</span>
                                        @endif
                                    </td>
                                    <td class="py-4">
                                        <span class="px-2.5 py-1 rounded-full font-bold text-[10px] uppercase 
                                            {{ $disp->status === 'resolved' ? 'bg-emerald-500 text-white' : ($disp->status === 'rejected' ? 'bg-rose-500 text-white' : 'bg-amber-400 text-slate-950') }}">
                                            {{ str_replace('_', ' ', $disp->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12 text-gray-400 space-y-3">
                    <span class="text-4xl block">⚖️</span>
                    <p class="font-bold text-sm text-gray-600 dark:text-gray-400">No dispute claims filed yet.</p>
                    <a href="/disputes/create" class="inline-block px-4 py-2.5 bg-amber-400 hover:bg-amber-500 text-slate-950 font-bold text-xs rounded-xl transition-all">
                        File First Dispute Claim
                    </a>
                </div>
            @endif
        </div>
    </main>
</x-layout>
