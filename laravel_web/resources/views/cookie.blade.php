<x-layout>
    <x-slot:title>Cookie Policy — RideMyCars</x-slot>

    <main class="flex-1 w-full max-w-4xl mx-auto px-4 py-16 sm:px-6 lg:px-8">
        <div class="mb-12 border-b border-gray-200 dark:border-white/10 pb-8">
            <span class="px-3 py-1 bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 rounded-full text-xs font-bold uppercase tracking-wider">Privacy & Storage</span>
            <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white mt-4 mb-2 tracking-tight">Cookie Policy</h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm">Effective Date: January 1, 2025 • Last Updated: August 2026</p>
        </div>

        <div class="space-y-8 text-gray-700 dark:text-gray-300 text-sm leading-relaxed">
            <section class="bg-white dark:bg-[#111] p-6 rounded-2xl border border-gray-100 dark:border-white/10 shadow-sm">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3">1. What Are Cookies?</h2>
                <p>Cookies and web storage (localStorage, sessionStorage) are small data files saved on your browser or mobile device when you visit RideMyCars. They help us remember your authentication session, dark/light theme preference, map pickup choices, and security tokens.</p>
            </section>

            <section class="bg-white dark:bg-[#111] p-6 rounded-2xl border border-gray-100 dark:border-white/10 shadow-sm">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3">2. Essential Cookies We Use</h2>
                <ul class="list-disc pl-5 space-y-2 text-gray-600 dark:text-gray-400">
                    <li><strong>XSRF-TOKEN & Session Cookies:</strong> Required for secure login, form protection against CSRF, and account authentication.</li>
                    <li><strong>theme & darkMode:</strong> Stores your dark or light mode preference locally.</li>
                    <li><strong>rmc_bank_account & rmc_gift_cash:</strong> Persists your local wallet and payment preferences across sessions.</li>
                </ul>
            </section>

            <section class="bg-white dark:bg-[#111] p-6 rounded-2xl border border-gray-100 dark:border-white/10 shadow-sm">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3">3. Managing Cookie Preferences</h2>
                <p>You can choose to disable or clear cookies in your browser settings. Note that disabling essential session cookies will require you to log in on every page navigation.</p>
            </section>
        </div>
    </main>
</x-layout>
