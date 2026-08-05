<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RideMyCars — Ride, Rent, Hire</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-background text-foreground">
    <div class="flex min-h-screen flex-col">
        <header class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 bg-transparent backdrop-blur-sm border-b border-white/5">
            <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <a class="flex items-center gap-2 group" href="/">
                        <div class="w-8 h-8 rounded-xl bg-indigo-500 flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-white"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"></path><circle cx="7" cy="17" r="2"></circle><path d="M9 17h6"></path><circle cx="17" cy="17" r="2"></circle></svg>
                        </div>
                        <span class="font-bold text-xl tracking-tight text-gray-900 dark:text-white">Ride<span class="text-indigo-500">MyCars</span></span>
                    </a>
                    
                    <div class="hidden lg:flex items-center gap-1">
                        <a class="px-4 py-2 rounded-lg text-sm font-medium transition-all text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800" href="/ride">Ride</a>
                        <a class="px-4 py-2 rounded-lg text-sm font-medium transition-all text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800" href="/rent">Rent Vehicle</a>
                        <a class="px-4 py-2 rounded-lg text-sm font-medium transition-all text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800" href="/hire-driver">Hire Driver</a>
                        <a class="px-4 py-2 rounded-lg text-sm font-medium transition-all text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800" href="/pricing">Pricing</a>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <div class="hidden lg:flex items-center gap-2">
                            <a class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors" href="/login">Sign In</a>
                            <a class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-semibold rounded-xl transition-all hover:shadow-lg hover:shadow-indigo-500/25 active:scale-95" href="/signup">Get Started</a>
                        </div>
                    </div>
                </div>
            </nav>
        </header>

        <main class="flex-1">
            <section class="relative min-h-screen flex items-center overflow-hidden bg-gradient-to-br from-white via-white to-indigo-50/30 dark:from-gray-950 dark:via-gray-950 dark:to-indigo-950/20">
                <div class="absolute inset-0 pointer-events-none overflow-hidden">
                    <div class="absolute -top-40 -right-40 w-[600px] h-[600px] rounded-full bg-indigo-500/5 blur-3xl"></div>
                    <div class="absolute -bottom-40 -left-40 w-[500px] h-[500px] rounded-full bg-indigo-600/5 blur-3xl"></div>
                </div>
                
                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-16 w-full">
                    <div class="grid lg:grid-cols-2 gap-12 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-indigo-500/10 border border-indigo-500/20 mb-6">
                                <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Trusted by 50,000+ riders worldwide</span>
                            </div>
                            
                            <h1 class="text-5xl sm:text-6xl lg:text-7xl font-bold tracking-tight leading-[1.05] mb-6 text-gray-900 dark:text-white">
                                One App.<br/>
                                <span class="bg-gradient-to-r from-indigo-500 to-indigo-600 bg-clip-text text-transparent">Three Ways</span><br/>
                                to Move.
                            </h1>
                            
                            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-lg mb-10 leading-relaxed">
                                Book a ride, rent a vehicle, or hire a professional driver — all from a single platform built for the modern traveler.
                            </p>
                            
                            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-xl shadow-black/5 overflow-hidden mb-10">
                                <div class="flex border-b border-gray-200 dark:border-gray-800">
                                    <button class="flex-1 py-3 text-sm font-semibold transition-all relative text-indigo-500">
                                        Ride
                                        <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-500 rounded-full"></div>
                                    </button>
                                    <button class="flex-1 py-3 text-sm font-semibold transition-all relative text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Rent Vehicle</button>
                                    <button class="flex-1 py-3 text-sm font-semibold transition-all relative text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Hire Driver</button>
                                </div>
                                <div class="p-3 flex gap-2">
                                    <div class="flex-1 relative">
                                        <input type="text" placeholder="Where to?" class="w-full pl-4 pr-4 py-3 bg-gray-50 dark:bg-gray-800/50 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/30 transition-all text-gray-900 dark:text-white" />
                                    </div>
                                    <button class="flex items-center gap-2 px-5 py-3 bg-indigo-500 hover:bg-indigo-600 text-white font-semibold text-sm rounded-xl transition-all hover:shadow-lg hover:shadow-indigo-500/30 active:scale-95">
                                        Book Ride
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="relative hidden lg:block">
                            <div class="relative w-full aspect-square max-w-lg mx-auto">
                                <div class="absolute inset-0 rounded-3xl bg-gradient-to-br from-indigo-500/20 to-indigo-600/10 border border-indigo-500/20"></div>
                                <div class="absolute inset-8 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center shadow-2xl shadow-indigo-500/30 overflow-hidden">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="w-32 h-32 text-white/80"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"></path><circle cx="7" cy="17" r="2"></circle><path d="M9 17h6"></path><circle cx="17" cy="17" r="2"></circle></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
