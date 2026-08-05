<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terms of Service — RideMyCars</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white text-gray-900 overflow-x-hidden">
    <div class="flex min-h-screen flex-col">
        
        <!-- Header -->
        <header class="top-0 left-0 right-0 z-50 bg-white border-b border-gray-100">
            <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-20 items-center justify-between">
                    <!-- Logo -->
                    <a class="flex items-center gap-2 group" href="/">
                        <div class="w-10 h-10 rounded-xl bg-orange-500 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-white"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"></path><circle cx="7" cy="17" r="2"></circle><path d="M9 17h6"></path><circle cx="17" cy="17" r="2"></circle></svg>
                        </div>
                        <span class="font-bold text-2xl tracking-tight text-gray-900">Ride<span class="text-orange-500">MyCars</span></span>
                    </a>
                    
                    <!-- Desktop Nav -->
                    <div class="hidden lg:flex items-center gap-6">
                        <a class="text-sm font-medium transition-colors text-gray-500 hover:text-gray-900" href="/ride">Ride</a>
                        <a class="text-sm font-medium transition-colors text-gray-500 hover:text-gray-900" href="/rent">Rent Vehicle</a>
                        <a class="text-sm font-medium transition-colors text-gray-500 hover:text-gray-900" href="/hire-driver">Hire Driver</a>
                        <a class="text-sm font-medium transition-colors text-gray-500 hover:text-gray-900 flex items-center gap-1" href="/company">Company <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg></a>
                        <a class="text-sm font-medium transition-colors text-gray-500 hover:text-gray-900" href="/pricing">Pricing</a>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex items-center gap-6">
                        <button class="text-gray-400 hover:text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                        </button>
                        <a class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors" href="/login">Sign In</a>
                        <a class="px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-xl transition-all shadow-md shadow-orange-500/25" href="/signup">Get Started</a>
                    </div>
                </div>
            </nav>
        </header>

        <!-- Main Content -->
        <main class="flex-1 w-full max-w-3xl mx-auto px-4 py-16 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Terms of Service</h1>
            <p class="text-gray-500 mb-12">Last updated: January 1, 2025</p>

            <div class="prose prose-gray max-w-none text-gray-600 space-y-8">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-3">1. Acceptance of Terms</h2>
                    <p class="leading-relaxed">By accessing or using the RideMyCars platform, you agree to be bound by these Terms of Service and our Privacy Policy. If you do not agree with any part of these terms, you may not use our services.</p>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-3">2. Services Provided</h2>
                    <p class="leading-relaxed">RideMyCars provides a technology platform connecting riders with drivers, vehicle owners with renters, and passengers with professional drivers for hire. We are not a transportation provider — we connect users to independent service providers.</p>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-3">3. User Accounts</h2>
                    <p class="leading-relaxed">You must be at least 18 years of age to create an account. You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account.</p>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-3">4. Booking and Cancellation</h2>
                    <p class="leading-relaxed">Bookings are confirmed upon payment. Cancellation policies vary by service type and are disclosed at the time of booking. Refunds are processed according to the applicable cancellation policy.</p>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-3">5. Payments and Fees</h2>
                    <p class="leading-relaxed">All payments are processed securely through Stripe or your RideMyCars wallet. Prices displayed include all applicable platform fees. Additional taxes may apply based on your jurisdiction.</p>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-3">6. Prohibited Conduct</h2>
                    <p class="leading-relaxed">You may not use our platform for any unlawful purpose, to harass or harm other users, to submit false information, or to attempt to circumvent our safety systems. Violations may result in immediate account termination.</p>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-3">7. Limitation of Liability</h2>
                    <p class="leading-relaxed">To the maximum extent permitted by law, RideMyCars shall not be liable for any indirect, incidental, special, or consequential damages arising from your use of the platform.</p>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-3">8. Changes to Terms</h2>
                    <p class="leading-relaxed">We reserve the right to modify these terms at any time. Continued use of the platform after changes constitutes acceptance of the new terms. We will provide notice of significant changes via email.</p>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-3">9. Contact</h2>
                    <p class="leading-relaxed">For questions about these Terms, contact us at <a href="mailto:legal@ridemycars.com" class="text-orange-500 hover:underline">legal@ridemycars.com</a>.</p>
                </div>
            </div>
        </main>
        
    </div>
</body>
</html>
