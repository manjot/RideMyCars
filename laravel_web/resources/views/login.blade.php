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
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-white text-black" x-data="{ view: 'mobile', emailForOtp: '', otpError: '', isLoading: false, c1: '', c2: '', c3: '', c4: '' }">
    
    <!-- Header -->
    <header class="w-full bg-black h-16 flex items-center px-4 md:px-8">
        <a href="/" class="flex items-center">
            <img src="{{ asset('images/logo.png') }}" alt="RideMyCars" class="h-8 brightness-0 invert object-contain">
        </a>
    </header>

    <div class="flex flex-col items-center justify-center min-h-[calc(100vh-64px)] py-12 px-4 sm:px-6">
        
        <div class="w-full max-w-[400px]">
            
            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm font-semibold flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Mobile Number View (Matches Screenshot) -->
            <div x-show="view === 'mobile'" x-transition.opacity.duration.300ms>
                <h1 class="text-2xl font-semibold mb-6 text-gray-900 tracking-tight">Enter your mobile number</h1>
                
                <form action="#" method="GET" @submit.prevent="view = 'otp'">
                    <div class="flex mb-4 h-[52px]">
                        <button type="button" class="flex items-center justify-center gap-2 px-4 bg-gray-100 hover:bg-gray-200 rounded-l-lg border-r border-gray-300/50 transition-colors">
                            <span class="text-lg leading-none">🇮🇳</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-900" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <input type="tel" placeholder="+91 Mobile number" class="flex-1 bg-gray-100 rounded-r-lg px-4 text-gray-900 placeholder-gray-500 font-medium focus:outline-none focus:ring-2 focus:ring-black border-none text-base">
                    </div>

                    <button type="submit" class="w-full bg-black hover:bg-gray-900 text-white font-medium py-3.5 rounded-lg text-base transition-colors">
                        Continue
                    </button>
                </form>

                <div class="flex items-center my-6">
                    <div class="flex-1 border-t border-gray-300"></div>
                    <span class="px-4 text-sm text-gray-500 font-medium">or</span>
                    <div class="flex-1 border-t border-gray-300"></div>
                </div>

                <div class="space-y-3">
                    <button type="button" class="w-full flex items-center justify-center gap-3 py-3.5 bg-gray-100 hover:bg-gray-200 text-gray-900 font-semibold rounded-lg transition-colors">
                        <svg viewBox="0 0 24 24" width="20" height="20"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/><path d="M1 1h22v22H1z" fill="none"/></svg>
                        Continue with Google
                    </button>
                    <button type="button" class="w-full flex items-center justify-center gap-3 py-3.5 bg-gray-100 hover:bg-gray-200 text-gray-900 font-semibold rounded-lg transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 32 32" fill="currentColor"><path d="M16 0C7.164 0 0 7.164 0 16s7.164 16 16 16 16-7.164 16-16S24.836 0 16 0zm-1.844 11.238c-.027-2.586 2.113-4.992 4.922-5.184 1.14.04 2.22.56 2.97 1.43.05.07.09.13.12.2.02.06.05.12.06.18.39 1.13.41 2.39-.02 3.48-.9 2.18-3.08 3.52-5.46 3.36a3.81 3.81 0 0 1-2.59-3.466zM22.06 25c-1.398 2.016-2.906 4.02-5.32 4.06-2.355.04-3.11-1.395-5.836-1.395-2.723 0-3.586 1.356-5.82 1.43-2.375.078-4.105-2.227-5.52-4.266C6.676 20.672 5.094 15.684 6.55 12.23c.723-1.71 2.375-2.82 4.22-2.85 2.26-.04 4.39 1.53 5.86 1.53 1.47 0 4.05-1.92 6.78-1.64 1.15.12 3.31.59 4.7 2.65-.13.09-2.81 1.63-2.77 4.87.04 3.91 3.34 5.2 3.42 5.24-.03.09-1.52 5.3-6.7 5.97z"/></svg>
                        Continue with Apple
                    </button>
                    <button type="button" @click="view = 'email_otp'" class="w-full flex items-center justify-center gap-3 py-3.5 bg-gray-100 hover:bg-gray-200 text-gray-900 font-semibold rounded-lg transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        Continue with email
                    </button>
                </div>

                <div class="flex items-center my-6">
                    <div class="flex-1 border-t border-gray-300"></div>
                    <span class="px-4 text-sm text-gray-500 font-medium">or</span>
                    <div class="flex-1 border-t border-gray-300"></div>
                </div>

                <button type="button" class="w-full flex items-center justify-center gap-3 py-3.5 bg-gray-100 hover:bg-gray-200 text-gray-900 font-semibold rounded-lg transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/><path d="M21 16h-3a2 2 0 0 0-2 2v3"/><path d="M21 21v.01"/><path d="M12 7v3a2 2 0 0 1-2 2H7"/><path d="M3 12h.01"/><path d="M12 3h.01"/><path d="M12 16v.01"/><path d="M16 12h1"/><path d="M21 12v.01"/><path d="M12 21v-1"/></svg>
                    Log in with QR code
                </button>

                <p class="text-[13px] text-gray-500 mt-8 leading-relaxed">
                    You consent to receive a verification code by text or WhatsApp. Message and data rates may apply.
                </p>
            </div>

            <!-- OTP Verification View -->
            <div x-show="view === 'otp'" x-transition.opacity.duration.300ms style="display: none;">
                <h1 class="text-2xl font-bold mb-4 text-gray-900 tracking-tight">Enter your code</h1>
                
                <p class="text-[15px] text-gray-700 leading-relaxed mb-4">
                    Enter the 4-digit code sent to <span class="font-semibold" x-text="emailForOtp"></span>.
                </p>

                <button type="button" @click="view = 'email_otp'" class="text-sm text-black font-semibold underline underline-offset-4 mb-8 hover:text-gray-600 transition-colors">
                    Changed your email?
                </button>

                <div class="flex items-center gap-3 mb-2">
                    <input type="text" maxlength="1" x-model="c1" @input="$event.target.value ? $refs.c2.focus() : null" class="w-12 h-14 bg-gray-100 rounded-xl text-center text-xl font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-black border-2 border-transparent focus:border-black transition-all">
                    <input type="text" maxlength="1" x-model="c2" x-ref="c2" @input="$event.target.value ? $refs.c3.focus() : $refs.c1.focus()" class="w-12 h-14 bg-gray-100 rounded-xl text-center text-xl font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-black border-2 border-transparent focus:border-black transition-all">
                    <input type="text" maxlength="1" x-model="c3" x-ref="c3" @input="$event.target.value ? $refs.c4.focus() : $refs.c2.focus()" class="w-12 h-14 bg-gray-100 rounded-xl text-center text-xl font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-black border-2 border-transparent focus:border-black transition-all">
                    <input type="text" maxlength="1" x-model="c4" x-ref="c4" @input="!$event.target.value ? $refs.c3.focus() : null" class="w-12 h-14 bg-gray-100 rounded-xl text-center text-xl font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-black border-2 border-transparent focus:border-black transition-all">
                </div>
                <p class="text-red-500 text-sm font-semibold mb-6 h-5" x-text="otpError"></p>

                <!-- Footer Navigation -->
                <div class="flex items-center justify-between mt-12">
                    <button type="button" @click="view = 'email_otp'" class="w-12 h-12 flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-full transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    </button>
                    
                    <button type="button" 
                            @click="
                                if(c1 && c2 && c3 && c4) {
                                    isLoading = true;
                                    otpError = '';
                                    const csrfToken = '{{ csrf_token() }}';
                                    fetch('/api/otp/verify', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                                        body: JSON.stringify({ email: emailForOtp, otp: c1+c2+c3+c4 })
                                    })
                                    .then(res => res.json())
                                    .then(data => {
                                        isLoading = false;
                                        if (data.error) {
                                            otpError = data.error;
                                        } else {
                                            window.location.href = data.redirect || '/';
                                        }
                                    })
                                    .catch(() => { isLoading = false; otpError = 'Network error. Please try again.'; });
                                }
                            "
                            class="h-12 px-6 rounded-full font-semibold flex items-center gap-2 transition-colors"
                            :class="(c1 && c2 && c3 && c4 && !isLoading) ? 'bg-black text-white hover:bg-gray-900 cursor-pointer shadow-md' : 'bg-gray-100 text-gray-400 cursor-not-allowed'"
                            :disabled="isLoading || !(c1 && c2 && c3 && c4)">
                        <span x-text="isLoading ? 'Verifying...' : 'Verify'"></span>
                        <svg x-show="!isLoading" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                </div>
            </div>
            
            <!-- Email OTP Request View -->
            <div x-show="view === 'email_otp'" x-transition.opacity.duration.300ms style="display: none;">
                <h1 class="text-2xl font-semibold mb-6 text-gray-900 tracking-tight">Enter your email</h1>
                
                <form @submit.prevent="
                    isLoading = true;
                    otpError = '';
                    const csrfToken = '{{ csrf_token() }}';
                    fetch('/api/otp/send', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ email: emailForOtp })
                    })
                    .then(res => {
                        isLoading = false;
                        if(res.ok) {
                            view = 'otp';
                            c1 = c2 = c3 = c4 = '';
                        } else {
                            otpError = 'Failed to send OTP. Try again.';
                        }
                    })
                    .catch(() => { isLoading = false; otpError = 'Network error.'; });
                " class="space-y-4">
                    <div>
                        <input type="email" x-model="emailForOtp" required placeholder="you@example.com" class="w-full bg-gray-100 rounded-lg px-4 py-3.5 text-gray-900 placeholder-gray-500 font-medium focus:outline-none focus:ring-2 focus:ring-black border-none text-base">
                        <p class="text-red-500 text-sm font-semibold mt-1" x-text="otpError"></p>
                    </div>
                    
                    <button type="submit" :disabled="isLoading" class="w-full bg-black hover:bg-gray-900 text-white font-medium py-3.5 rounded-lg text-base transition-colors mt-2" :class="isLoading ? 'opacity-70 cursor-wait' : ''">
                        <span x-text="isLoading ? 'Sending code...' : 'Continue'"></span>
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <button type="button" @click="view = 'email'" class="text-sm font-semibold text-gray-600 hover:text-black transition-colors mb-4 block w-full">
                        Or login with password instead
                    </button>
                    <button type="button" @click="view = 'mobile'" class="text-sm font-semibold text-gray-600 hover:text-black transition-colors block w-full">
                        ← Back to mobile number
                    </button>
                </div>
            </div>

            <!-- Email Password View (For Backward Compatibility) -->
            <div x-show="view === 'email'" x-transition.opacity.duration.300ms style="display: none;">
                <h1 class="text-2xl font-semibold mb-6 text-gray-900 tracking-tight">Login with password</h1>
                
                <form action="/login" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <input type="email" name="email" required value="{{ old('email') }}" placeholder="you@example.com" class="w-full bg-gray-100 rounded-lg px-4 py-3.5 text-gray-900 placeholder-gray-500 font-medium focus:outline-none focus:ring-2 focus:ring-black border-none text-base">
                        @error('email')
                            <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div x-data="{ show: false }" class="relative">
                        <input :type="show ? 'text' : 'password'" name="password" required placeholder="Your password" class="w-full bg-gray-100 rounded-lg px-4 py-3.5 pr-12 text-gray-900 placeholder-gray-500 font-medium focus:outline-none focus:ring-2 focus:ring-black border-none text-base">
                        <div @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-500 cursor-pointer hover:text-gray-900">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-black hover:bg-gray-900 text-white font-medium py-3.5 rounded-lg text-base transition-colors mt-2">
                        Continue
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <button type="button" @click="view = 'mobile'" class="text-sm font-semibold text-gray-600 hover:text-black transition-colors">
                        ← Back to mobile number
                    </button>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
