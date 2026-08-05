<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'RideMyCars' }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#fafafa] text-gray-900 min-h-screen flex flex-col">
    
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
                    
                    <!-- Company Dropdown -->
                    <div x-data="{ open: false }" class="relative" @click.away="open = false">
                        <button @click="open = !open" class="text-sm font-medium transition-colors flex items-center gap-1 text-gray-900 hover:bg-gray-100 px-4 py-2 rounded-full">
                            Company 
                            <svg :class="{'rotate-180': open}" class="transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div x-show="open" x-transition class="absolute top-full left-0 mt-2 w-48 bg-white border border-gray-100 shadow-xl rounded-xl py-2 z-50" style="display: none;">
                            <a href="/about" class="block px-4 py-2.5 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-colors">About Us</a>
                            <a href="#" class="block px-4 py-2.5 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-colors">Safety</a>
                            <a href="#" class="block px-4 py-2.5 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-colors">Become a Driver</a>
                            <a href="#" class="block px-4 py-2.5 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-colors">List Your Vehicle</a>
                            <a href="#" class="block px-4 py-2.5 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-colors">Blogs</a>
                        </div>
                    </div>

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

    {{ $slot }}

    <!-- Footer -->
    <footer class="bg-[#0a0a0a] text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                <div class="col-span-1 md:col-span-1">
                    <a class="flex items-center gap-2 mb-6" href="/">
                        <div class="w-10 h-10 rounded-xl bg-orange-500 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"></path><circle cx="7" cy="17" r="2"></circle><path d="M9 17h6"></path><circle cx="17" cy="17" r="2"></circle></svg>
                        </div>
                        <span class="font-bold text-2xl tracking-tight text-white">Ride<span class="text-orange-500">MyCars</span></span>
                    </a>
                    <p class="text-gray-400 text-sm leading-relaxed">
                        Your unified mobility platform. Book rides, rent vehicles, and hire professional drivers — all in one place.
                    </p>
                    <div class="flex gap-4 mt-6">
                        <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-gray-400 hover:text-white transition-colors cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"/></svg>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-gray-400 hover:text-white transition-colors cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-gray-400 hover:text-white transition-colors cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-gray-400 hover:text-white transition-colors cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/></svg>
                        </div>
                    </div>
                </div>
                <div>
                    <h4 class="font-bold text-xs tracking-widest text-gray-500 uppercase mb-6">Services</h4>
                    <ul class="space-y-4">
                        <li><a href="/ride" class="text-sm text-gray-400 hover:text-white transition-colors">Book a Ride</a></li>
                        <li><a href="/rent" class="text-sm text-gray-400 hover:text-white transition-colors">Rent a Vehicle</a></li>
                        <li><a href="/hire-driver" class="text-sm text-gray-400 hover:text-white transition-colors">Hire a Driver</a></li>
                        <li><a href="/pricing" class="text-sm text-gray-400 hover:text-white transition-colors">Pricing</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-xs tracking-widest text-gray-500 uppercase mb-6">Company</h4>
                    <ul class="space-y-4">
                        <li><a href="/about" class="text-sm text-gray-400 hover:text-white transition-colors">About Us</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">Safety</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">Blog</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">Careers</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-xs tracking-widest text-gray-500 uppercase mb-6">Partners</h4>
                    <ul class="space-y-4">
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">Become a Driver</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">List Your Vehicle</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">Partner Portal</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-xs tracking-widest text-gray-500 uppercase mb-6">Support</h4>
                    <ul class="space-y-4">
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">Help Center</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">Contact Us</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">FAQ</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">Support Tickets</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-xs tracking-widest text-gray-500 uppercase mb-6">Legal</h4>
                    <ul class="space-y-4">
                        <li><a href="/terms" class="text-sm text-gray-400 hover:text-white transition-colors">Terms of Service</a></li>
                        <li><a href="/privacy" class="text-sm text-gray-400 hover:text-white transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">Refund Policy</a></li>
                        <li><a href="#" class="text-sm text-gray-400 hover:text-white transition-colors">Cookie Policy</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Contact Row -->
            <div class="border-t border-white/10 pt-8 pb-8 flex flex-col md:flex-row gap-8">
                <div class="flex items-center gap-2 text-sm text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    support@ridemycars.com
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    +1 800 123 4567
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                    San Francisco, CA
                </div>
            </div>

            <!-- Bottom Row -->
            <div class="border-t border-white/10 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-gray-500 text-sm">© 2026 RideMyCars. All rights reserved.</p>
                <div class="flex items-center gap-4 text-xs font-medium text-gray-500">
                    <span>Available on</span>
                    <span class="px-3 py-1.5 rounded-full bg-white/5 text-white border border-white/10">iOS — Coming Soon</span>
                    <span class="px-3 py-1.5 rounded-full bg-white/5 text-white border border-white/10">Android — Coming Soon</span>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
