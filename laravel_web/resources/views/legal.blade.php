<x-layout>
    <x-slot:title>Legal & Compliance — RideMyCars</x-slot>

    <main class="flex-1 w-full max-w-4xl mx-auto px-4 py-16 sm:px-6 lg:px-8">
        <div class="mb-12 border-b border-gray-200 dark:border-white/10 pb-8">
            <span class="px-3 py-1 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-full text-xs font-bold uppercase tracking-wider">Official Policy</span>
            <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white mt-4 mb-2 tracking-tight">Legal & Regulatory Compliance</h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm">Effective Date: January 1, 2025 • Last Updated: August 2026</p>
        </div>

        <!-- Quick Navigation Bar -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-12">
            <a href="/terms" class="p-4 bg-gray-50 dark:bg-[#151515] border border-gray-200 dark:border-white/10 rounded-2xl text-center hover:border-black dark:hover:border-white transition-all group">
                <span class="text-xl block mb-1">📜</span>
                <span class="font-bold text-xs text-gray-900 dark:text-white group-hover:underline">Terms of Service</span>
            </a>
            <a href="/privacy" class="p-4 bg-gray-50 dark:bg-[#151515] border border-gray-200 dark:border-white/10 rounded-2xl text-center hover:border-black dark:hover:border-white transition-all group">
                <span class="text-xl block mb-1">🔒</span>
                <span class="font-bold text-xs text-gray-900 dark:text-white group-hover:underline">Privacy Policy</span>
            </a>
            <a href="/safety" class="p-4 bg-gray-50 dark:bg-[#151515] border border-gray-200 dark:border-white/10 rounded-2xl text-center hover:border-black dark:hover:border-white transition-all group">
                <span class="text-xl block mb-1">🛡️</span>
                <span class="font-bold text-xs text-gray-900 dark:text-white group-hover:underline">Safety Guidelines</span>
            </a>
            <a href="/refund" class="p-4 bg-gray-50 dark:bg-[#151515] border border-gray-200 dark:border-white/10 rounded-2xl text-center hover:border-black dark:hover:border-white transition-all group">
                <span class="text-xl block mb-1">💳</span>
                <span class="font-bold text-xs text-gray-900 dark:text-white group-hover:underline">Refund Policy</span>
            </a>
        </div>

        <div class="space-y-10 text-gray-700 dark:text-gray-300">
            
            <section class="bg-white dark:bg-[#111] p-6 rounded-2xl border border-gray-100 dark:border-white/10 shadow-sm">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                    <span>1. Commercial Transportation & Platform Services</span>
                </h2>
                <p class="leading-relaxed text-sm">
                    RideMyCars operates an interactive digital mobility platform connecting riders, rental vehicle seekers, and package senders with independent licensed commercial drivers and vehicle owners. RideMyCars does not own or operate a motor carrier fleet; all transportation and driver services are rendered by vetted independent contractor drivers adhering to municipal and state commercial driver safety regulations.
                </p>
            </section>

            <section class="bg-white dark:bg-[#111] p-6 rounded-2xl border border-gray-100 dark:border-white/10 shadow-sm">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                    <span>2. Driver Background Checks & Vehicle Inspections</span>
                </h2>
                <p class="leading-relaxed text-sm mb-3">
                    All drivers offering rides or hire services on the RideMyCars network undergo multi-tiered background verification prior to receiving dispatch authorization:
                </p>
                <ul class="list-disc pl-5 text-sm space-y-2 text-gray-600 dark:text-gray-400">
                    <li>Comprehensive criminal history check (national and local court registers)</li>
                    <li>Motor vehicle record (MVR) screening for zero major moving violations</li>
                    <li>Government-issued driver's license verification & identity audit</li>
                    <li>Annual commercial vehicle safety and emissions inspection verification</li>
                </ul>
            </section>

            <section class="bg-white dark:bg-[#111] p-6 rounded-2xl border border-gray-100 dark:border-white/10 shadow-sm">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                    <span>3. Zero Tolerance for Discrimination & Harassment</span>
                </h2>
                <p class="leading-relaxed text-sm">
                    RideMyCars maintains a strict zero-tolerance policy against any form of discrimination, bias, or harassment based on race, religion, gender identity, sexual orientation, disability, or national origin. Violation of this policy results in immediate and permanent account deactivation for both riders and drivers.
                </p>
            </section>

            <section class="bg-white dark:bg-[#111] p-6 rounded-2xl border border-gray-100 dark:border-white/10 shadow-sm">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                    <span>4. Financial Settlement & Payment Security</span>
                </h2>
                <p class="leading-relaxed text-sm">
                    All digital credit card transactions are processed securely using PCI-DSS Level 1 compliant gateways (Stripe, Apple Pay, Cash App). Receipts with digital tracking codes are generated for all completed rides, vehicle rentals, and parcel deliveries.
                </p>
            </section>

            <section class="bg-white dark:bg-[#111] p-6 rounded-2xl border border-gray-100 dark:border-white/10 shadow-sm">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                    <span>5. Contact Legal Counsel</span>
                </h2>
                <p class="leading-relaxed text-sm">
                    For legal notices, regulatory inquiries, or law enforcement data requests, please contact our team at <a href="mailto:support@ridemycars.com" class="font-bold text-indigo-600 dark:text-indigo-400 hover:underline">support@ridemycars.com</a> or write to:
                </p>
                <div class="mt-4 p-4 bg-gray-50 dark:bg-[#1a1a1a] rounded-xl text-xs font-mono text-gray-800 dark:text-gray-200">
                    RideMyCars Inc. — Legal & Compliance Department<br>
                    500 Howard Street, Suite 400<br>
                    San Francisco, CA 94105
                </div>
            </section>

        </div>
    </main>
</x-layout>
