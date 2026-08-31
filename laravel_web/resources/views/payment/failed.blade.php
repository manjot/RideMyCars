<x-layout title="Payment Failed - RideMyCars">
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8 flex items-center justify-center">
        <div class="max-w-md w-full bg-white dark:bg-gray-800 rounded-3xl shadow-2xl p-8 border border-red-100 dark:border-red-900/30 text-center">
            
            <!-- Failure Icon -->
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 mb-6">
                <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>

            <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                Payment Could Not Be Processed
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ request('error', 'Your payment attempt was declined or cancelled. Your booking remains active and unpaid.') }}
            </p>

            <!-- Error Notice -->
            <div class="mt-6 bg-red-50 dark:bg-red-950/40 rounded-2xl p-4 border border-red-200 dark:border-red-900/50 text-left text-xs text-red-700 dark:text-red-300">
                <div class="font-bold mb-1">Why did this happen?</div>
                <ul class="list-disc pl-4 space-y-1 text-red-600 dark:text-red-400">
                    <li>Insufficient funds or strict fraud check by issuing bank</li>
                    <li>Incorrect card number, expiration date, or CVC code</li>
                    <li>Transaction attempt timed out or was cancelled</li>
                </ul>
            </div>

            <!-- Actions -->
            <div class="mt-8 space-y-3">
                <button onclick="window.history.back()" class="w-full flex items-center justify-center px-6 py-3.5 border border-transparent text-base font-semibold rounded-2xl text-white bg-black hover:bg-gray-800 dark:bg-white dark:text-black dark:hover:bg-gray-100 transition-all shadow-lg">
                    🔄 Retry Payment
                </button>
                <a href="/" class="w-full flex items-center justify-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-2xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    Return to Homepage
                </a>
            </div>

        </div>
    </div>
</x-layout>
