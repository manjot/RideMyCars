<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Privacy Policy — RideMyCars</title>
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
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Privacy Policy</h1>
            <p class="text-gray-500 mb-12">Last updated: January 1, 2025</p>

            <div class="prose prose-gray max-w-none text-gray-600 space-y-8">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-3">1. Information We Collect</h2>
                    <p class="leading-relaxed">We collect information you provide directly (name, email, phone, payment information), information collected automatically (device data, IP address, location), and information from third parties (Google, Apple for OAuth login).</p>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-3">2. How We Use Your Information</h2>
                    <p class="leading-relaxed">We use your data to provide and improve our services, process payments, communicate with you, ensure safety, comply with legal obligations, and personalize your experience on the platform.</p>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-3">3. Location Data</h2>
                    <p class="leading-relaxed">We collect your location data when you use our ride and rental services. This data is used to match you with nearby drivers and vehicles, provide live tracking, and improve our routing algorithms.</p>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-3">4. Data Sharing</h2>
                    <p class="leading-relaxed">We share your data with service providers (drivers, vehicle owners) necessary to complete bookings, and with trusted third-party vendors who help operate our platform. We do not sell your personal data.</p>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-3">5. Data Security</h2>
                    <p class="leading-relaxed">We implement industry-standard security measures including TLS encryption, hashed passwords, and regular security audits. No system is 100% secure, but we take every reasonable precaution.</p>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-3">6. Cookies</h2>
                    <p class="leading-relaxed">We use essential cookies for authentication and session management, and analytics cookies to understand usage patterns. You can manage cookie preferences in your browser settings.</p>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-3">7. Your Rights</h2>
                    <p class="leading-relaxed">You have the right to access, correct, or delete your personal data. To exercise these rights, contact <a href="mailto:privacy@ridemycars.com" class="text-orange-500 hover:underline">privacy@ridemycars.com</a> or use the data management tools in your account settings.</p>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-3">8. Data Retention</h2>
                    <p class="leading-relaxed">We retain your data for as long as your account is active and for a reasonable period thereafter for legal compliance. Booking and transaction records are retained for 7 years per financial regulations.</p>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-3">9. Contact Us</h2>
                    <p class="leading-relaxed">For privacy inquiries, contact our Data Protection Officer at <a href="mailto:privacy@ridemycars.com" class="text-orange-500 hover:underline">privacy@ridemycars.com</a> or write to RideMyCars Inc., San Francisco, CA 94102.</p>
                </div>
            </div>
        </main>
        
    </div>
</body>
</html>
