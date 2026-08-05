<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In — RideMyCars</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white dark:bg-[#111] text-gray-900 dark:text-white">
    <div class="flex min-h-screen">
        
        <!-- Left Banner (Dark) -->
        <div class="hidden lg:flex w-1/2 bg-[#1a1a1a] flex-col justify-between p-12 relative overflow-hidden">
            
            <!-- Logo -->
            <a class="flex items-center gap-2 group z-10" href="/">
                <img src="{{ asset('images/logo.png') }}" alt="RideMyCars Logo" class="h-16 md:h-[72px] w-auto mix-blend-multiply dark:mix-blend-normal dark:bg-white dark:rounded-xl dark:p-1">
            </a>

            <!-- Text Content -->
            <div class="z-10 max-w-lg mt-20">
                <h1 class="text-5xl font-bold text-white mb-6 leading-tight">
                    Ride. Rent. Hire.<br/>
                    All in one place.
                </h1>
                <p class="text-gray-400 dark:text-gray-500 text-lg leading-relaxed">
                    Join 50,000+ riders and drivers on the platform that combines everything you need to move.
                </p>
            </div>

            <!-- Stats Bottom -->
            <div class="flex gap-4 z-10 mt-20">
                <div class="bg-[#242424] rounded-2xl p-6 flex-1 text-center border border-gray-800">
                    <div class="text-3xl font-bold text-brand-500 mb-1">50K+</div>
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Riders</div>
                </div>
                <div class="bg-[#242424] rounded-2xl p-6 flex-1 text-center border border-gray-800">
                    <div class="text-3xl font-bold text-brand-500 mb-1">3.2K+</div>
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Drivers</div>
                </div>
                <div class="bg-[#242424] rounded-2xl p-6 flex-1 text-center border border-gray-800">
                    <div class="text-3xl font-bold text-brand-500 mb-1 flex items-center justify-center gap-1">4.9<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" stroke="none" class="text-brand-500"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg></div>
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Rating</div>
                </div>
            </div>
        </div>

        <!-- Right Form (White) -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 lg:p-24 relative">
            <div class="w-full max-w-md">
                <div class="mb-10 text-center lg:text-left">
                    <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-3 tracking-tight">Welcome back</h2>
                    <p class="text-gray-500 dark:text-gray-400 text-lg">Sign in to your RideMyCars account</p>
                </div>

                <form action="/login" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                            </div>
                            <input type="email" name="email" required value="{{ old('email') }}" placeholder="you@example.com" class="w-full pl-12 pr-4 py-3.5 bg-gray-50/50 border @error('email') border-red-500 @else border-gray-200 dark:border-white/10 @enderror rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                        </div>
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Password</label>
                            <a href="#" class="text-sm font-medium text-brand-500 hover:text-brand-600 transition-colors">Forgot password?</a>
                        </div>
                        <div class="relative" x-data="{ show: false }">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </div>
                            <input :type="show ? 'text' : 'password'" name="password" required placeholder="Your password" class="w-full pl-12 pr-12 py-3.5 bg-gray-50/50 border border-gray-200 dark:border-white/10 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                            <div @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 dark:text-gray-500 cursor-pointer hover:text-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-4 mt-2 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl transition-all shadow-md shadow-brand-500/25 active:scale-[0.98]">
                        Sign In
                    </button>
                </form>

                <div class="mt-8 flex items-center gap-4">
                    <div class="flex-1 h-px bg-gray-200"></div>
                    <span class="text-sm font-medium text-gray-400 dark:text-gray-500">Or continue with</span>
                    <div class="flex-1 h-px bg-gray-200"></div>
                </div>

                <button type="button" class="w-full py-3.5 mt-8 bg-white dark:bg-[#111] border border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/5 hover:border-gray-300 text-gray-700 dark:text-gray-300 font-bold rounded-xl transition-all flex items-center justify-center gap-3 active:scale-[0.98]">
                    <svg viewBox="0 0 24 24" width="20" height="20"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/><path d="M1 1h22v22H1z" fill="none"/></svg>
                    Continue with Google
                </button>

                <p class="text-center mt-8 text-sm text-gray-500 dark:text-gray-400 font-medium">
                    Don't have an account? <a href="/signup" class="text-brand-500 hover:text-brand-600 font-bold transition-colors">Sign up free</a>
                </p>
            </div>
        </div>

    </div>
</body>
</html>
