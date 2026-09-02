@props([
    'serviceType' => 'ride',
    'serviceId' => 0,
    'amount' => 0,
    'currency' => 'USD',
])

<div x-data="stripePaymentHandler('{{ $serviceType }}', {{ $serviceId }}, {{ $amount }}, '{{ $currency }}')"
     x-show="isOpen"
     x-cloak
     @open-stripe-modal.window="openModal($event.detail)"
     class="fixed inset-0 z-50 overflow-y-auto"
     aria-labelledby="modal-title" role="dialog" aria-modal="true">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm transition-opacity" x-show="isOpen" x-transition.opacity></div>

    <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
        <div class="relative bg-white dark:bg-gray-800 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full border border-gray-100 dark:border-gray-700"
             @click.away="closeModal()">

            <!-- Header -->
            <div class="bg-gradient-to-r from-gray-900 via-gray-800 to-black p-6 text-white flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="p-2.5 bg-white/10 rounded-xl">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-extrabold tracking-tight">Secure Card Checkout</h3>
                        <p class="text-xs text-gray-300">Powered by Stripe PCI-DSS Level 1 Encryption</p>
                    </div>
                </div>
                <button @click="closeModal()" class="text-gray-400 hover:text-white p-1 rounded-lg hover:bg-white/10 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Content Body -->
            <div class="p-6 space-y-6">

                <!-- Amount Badge -->
                <div class="bg-gray-50 dark:bg-gray-900/60 rounded-2xl p-4 flex justify-between items-center border border-gray-200 dark:border-gray-700">
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider">Total Amount</span>
                        <div class="text-2xl font-black text-gray-900 dark:text-white" x-text="currency + ' $' + amount.toFixed(2)"></div>
                    </div>
                    <span class="px-3 py-1 bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 font-bold text-xs rounded-full">
                        🔒 Encrypted
                    </span>
                </div>

                <!-- Error Alert -->
                <div x-show="errorMessage" x-transition class="bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 p-4 rounded-2xl text-sm flex items-start space-x-3">
                    <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-text="errorMessage"></span>
                </div>

                <!-- Already Paid State -->
                <div x-show="isAlreadyPaid" class="py-6 text-center space-y-4">
                    <div class="w-16 h-16 bg-green-100 dark:bg-green-900/40 text-green-600 dark:text-green-400 rounded-full flex items-center justify-center mx-auto text-3xl font-black shadow-md">
                        ✓
                    </div>
                    <div>
                        <h4 class="text-xl font-black text-gray-900 dark:text-white">Payment Already Completed</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">This booking has already been paid in full.</p>
                    </div>
                    <div class="pt-2">
                        <a href="/my-rides" class="inline-flex items-center justify-center px-6 py-3 bg-black dark:bg-white text-white dark:text-black font-extrabold text-sm rounded-2xl shadow-lg hover:opacity-90 transition">
                            View Booking & Receipt →
                        </a>
                    </div>
                </div>

                <!-- Stripe Element Container -->
                <div x-show="!isLoading && !isAlreadyPaid && !errorMessage" class="space-y-4">
                    <!-- Cardholder Name Field -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Cardholder Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" x-model="cardholderName" placeholder="Full name as printed on card"
                               class="w-full px-4 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-sm font-semibold text-gray-900 dark:text-white focus:ring-2 focus:ring-black dark:focus:ring-white focus:outline-none transition">
                    </div>

                    <!-- Stripe Card Element -->
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                Card Information <span class="text-red-500">*</span>
                            </label>
                            <span class="text-[10px] text-gray-400 font-bold">💳 Visa, Mastercard, Amex, Discover</span>
                        </div>
                        <div id="stripe-card-element" class="p-4 border border-gray-300 dark:border-gray-600 rounded-2xl bg-white dark:bg-gray-900 shadow-sm transition-all focus-within:ring-2 focus-within:ring-black dark:focus-within:ring-white"></div>
                    </div>

                    <div class="p-3 bg-blue-50 dark:bg-blue-950/40 rounded-xl text-xs text-blue-700 dark:text-blue-300 flex items-center justify-between border border-blue-100 dark:border-blue-900">
                        <span>💡 Test Mode: Use test card <code class="font-mono font-bold bg-blue-100 dark:bg-blue-900 px-1.5 py-0.5 rounded">4242 4242 4242 4242</code></span>
                        <span class="font-mono">CVC: 123</span>
                    </div>
                </div>

                <!-- Loading State -->
                <div x-show="isLoading" class="py-8 text-center space-y-3">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-black dark:border-white border-t-transparent"></div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-300" x-text="loadingText"></p>
                </div>
            </div>

            <!-- Modal Actions -->
            <div x-show="!isAlreadyPaid" class="bg-gray-50 dark:bg-gray-900/60 px-6 py-4 flex flex-col sm:flex-row sm:justify-end gap-3 border-t border-gray-100 dark:border-gray-700">
                <button type="button" @click="closeModal()" :disabled="isProcessing" class="px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-semibold hover:bg-gray-100 dark:hover:bg-gray-800 transition disabled:opacity-50">
                    Cancel
                </button>
                <button type="button" @click="submitPayment()" :disabled="isProcessing || isLoading" class="px-6 py-2.5 rounded-xl bg-black dark:bg-white text-white dark:text-black text-sm font-extrabold hover:bg-gray-800 dark:hover:bg-gray-100 transition shadow-lg flex items-center justify-center space-x-2 disabled:opacity-50">
                    <svg x-show="isProcessing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-current" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="isProcessing ? 'Processing Payment...' : 'Pay $' + amount.toFixed(2) + ' Now'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function stripePaymentHandler(defaultType, defaultId, defaultAmount, defaultCurrency) {
    return {
        isOpen: false,
        isLoading: false,
        isProcessing: false,
        errorMessage: '',
        loadingText: 'Initializing Stripe Security...',
        isAlreadyPaid: false,
        cardholderName: '',
        serviceType: defaultType,
        serviceId: defaultId,
        amount: defaultAmount,
        currency: defaultCurrency,
        stripe: null,
        cardElement: null,
        clientSecret: null,
        publishableKey: null,

        async openModal(data = {}) {
            this.isOpen = true;
            this.errorMessage = '';
            this.isAlreadyPaid = false;
            this.isLoading = true;
            this.isProcessing = false;
            this.cardholderName = data.cardholderName || '';
            this.serviceType = data.serviceType || this.serviceType;
            this.serviceId = data.serviceId || this.serviceId;
            this.amount = data.amount || this.amount;
            this.currency = data.currency || this.currency;
            this.loadingText = 'Initializing secure payment session...';

            await this.ensureStripeLoaded();
            await this.initIntent();
        },

        closeModal() {
            if (this.isProcessing) return;
            this.isOpen = false;
        },

        async ensureStripeLoaded() {
            if (window.Stripe) return;
            return new Promise((resolve) => {
                const script = document.createElement('script');
                script.src = 'https://js.stripe.com/v3/';
                script.onload = () => resolve();
                document.head.appendChild(script);
            });
        },

        async initIntent() {
            try {
                const response = await fetch('/api/stripe/create-payment-intent', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        service_type: this.serviceType,
                        service_id: this.serviceId
                    })
                });

                const result = await response.json();
                if (result.already_paid || (result.message && result.message.toLowerCase().includes('already been paid'))) {
                    this.isAlreadyPaid = true;
                    this.isLoading = false;
                    return;
                }

                if (!result.success) {
                    throw new Error(result.message || 'Failed to initialize payment intent.');
                }

                this.clientSecret = result.data.client_secret;
                this.publishableKey = result.data.publishable_key;
                this.stripe = Stripe(this.publishableKey);

                const elements = this.stripe.elements();
                const style = {
                    base: {
                        color: document.documentElement.classList.contains('dark') ? '#ffffff' : '#1f2937',
                        fontFamily: 'Inter, sans-serif',
                        fontSmoothing: 'antialiased',
                        fontSize: '16px',
                        '::placeholder': {
                            color: '#9ca3af'
                        }
                    },
                    invalid: {
                        color: '#ef4444',
                        iconColor: '#ef4444'
                    }
                };

                this.isLoading = false;
                if (typeof this.$nextTick === 'function') {
                    await this.$nextTick();
                } else {
                    await new Promise(r => setTimeout(r, 50));
                }

                const container = document.getElementById('stripe-card-element');
                if (container) {
                    container.innerHTML = '';
                    this.cardElement = elements.create('card', { style, hidePostalCode: false });
                    this.cardElement.mount('#stripe-card-element');
                }
            } catch (err) {
                this.isLoading = false;
                this.errorMessage = err.message || 'An error occurred starting payment.';
            }
        },

        async submitPayment() {
            if (this.isProcessing || !this.stripe || !this.cardElement) return;

            this.isProcessing = true;
            this.errorMessage = '';

            try {
                const { paymentIntent, error } = await this.stripe.confirmCardPayment(this.clientSecret, {
                    payment_method: {
                        card: this.cardElement,
                        billing_details: {
                            name: this.cardholderName || 'RideMyCars Customer'
                        }
                    }
                });

                if (error) {
                    this.isProcessing = false;
                    this.errorMessage = error.message || 'Payment failed. Please check your card information.';
                    return;
                }

                if (paymentIntent.status === 'succeeded' || paymentIntent.status === 'processing') {
                    // Confirm server-side
                    const confirmRes = await fetch('/api/stripe/confirm-payment', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        body: JSON.stringify({
                            payment_intent_id: paymentIntent.id
                        })
                    });

                    const confirmData = await confirmRes.json();
                    window.location.href = '/payment/success?intent=' + paymentIntent.id + '&amount=' + this.amount;
                } else {
                    this.isProcessing = false;
                    this.errorMessage = 'Payment status: ' + paymentIntent.status;
                }
            } catch (err) {
                this.isProcessing = false;
                this.errorMessage = err.message || 'Unexpected payment error occurred.';
            }
        }
    }
}
</script>
