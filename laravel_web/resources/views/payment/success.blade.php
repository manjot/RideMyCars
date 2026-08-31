<x-layout title="Payment Successful - RideMyCars">
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8 flex items-center justify-center">
        <div class="max-w-md w-full bg-white dark:bg-gray-800 rounded-3xl shadow-2xl p-8 border border-gray-100 dark:border-gray-700 text-center">
            
            <!-- Success Icon -->
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 mb-6">
                <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                Payment Confirmed!
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Thank you for your payment. Your booking has been confirmed.
            </p>

            <!-- Transaction Summary -->
            <div class="mt-6 bg-gray-50 dark:bg-gray-900/60 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 text-left space-y-3">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Transaction Ref</span>
                    <span class="font-mono font-bold text-gray-900 dark:text-white">{{ $transaction->transaction_ref ?? request('txn', 'N/A') }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Amount Paid</span>
                    <span class="font-extrabold text-green-600 dark:text-green-400 text-base">
                        {{ $transaction->currency ?? 'USD' }} ${{ number_format($transaction->amount ?? request('amount', 0), 2) }}
                    </span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Payment Method</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 dark:bg-purple-900/40 text-purple-800 dark:text-purple-300">
                        💳 Stripe Secure Card
                    </span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Status</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">
                        ✓ PAID
                    </span>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-8 space-y-3">
                <a href="/" class="w-full flex items-center justify-center px-6 py-3.5 border border-transparent text-base font-semibold rounded-2xl text-white bg-black hover:bg-gray-800 dark:bg-white dark:text-black dark:hover:bg-gray-100 transition-all shadow-lg">
                    Return to Homepage
                </a>
                <a href="/ride" class="w-full flex items-center justify-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-2xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    View My Bookings
                </a>
            </div>

        </div>
    </div>
</x-layout>
