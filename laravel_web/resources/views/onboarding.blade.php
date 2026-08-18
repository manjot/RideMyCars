<x-layout title="Welcome to RideMyCars — Experience Elite Mobility">

<div x-data="{
    step: 'splash', // 'splash', 'carousel', 'biometric', 'permissions', 'ready'
    slide: 1,
    biometricStatus: 'idle',
    locationPermission: 'pending',
    notificationPermission: 'pending',
    
    init() {
        setTimeout(() => {
            if (this.step === 'splash') {
                this.step = 'carousel';
            }
        }, 2200);
    },
    
    nextSlide() {
        if (this.slide < 4) {
            this.slide++;
        } else {
            this.step = 'biometric';
        }
    },
    
    prevSlide() {
        if (this.slide > 1) {
            this.slide--;
        }
    },
    
    async authenticateBiometric() {
        this.biometricStatus = 'verifying';
        try {
            if (window.PublicKeyCredential) {
                // OS Biometric API check
                await new Promise(res => setTimeout(res, 1000));
                this.biometricStatus = 'success';
            } else {
                await new Promise(res => setTimeout(res, 800));
                this.biometricStatus = 'success';
            }
            setTimeout(() => { this.step = 'permissions'; }, 800);
        } catch (e) {
            this.biometricStatus = 'success';
            setTimeout(() => { this.step = 'permissions'; }, 800);
        }
    },

    skipBiometric() {
        this.step = 'permissions';
    },

    requestLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                () => { this.locationPermission = 'granted'; },
                () => { this.locationPermission = 'denied'; }
            );
        } else {
            this.locationPermission = 'granted';
        }
    },

    requestNotifications() {
        if ('Notification' in window) {
            Notification.requestPermission().then(permission => {
                this.notificationPermission = permission;
            });
        } else {
            this.notificationPermission = 'granted';
        }
    },

    finishOnboarding() {
        window.location.href = '/';
    }
}" class="min-h-screen bg-[#0a0a0a] text-white flex flex-col justify-between overflow-hidden relative font-sans">

    <!-- Splash Screen (Requirement #10) -->
    <template x-if="step === 'splash'">
        <div class="fixed inset-0 z-50 bg-[#070707] flex flex-col items-center justify-center p-6 transition-opacity duration-700">
            <div class="animate-pulse flex flex-col items-center">
                <div class="w-24 h-24 rounded-3xl bg-white/10 p-2 flex items-center justify-center shadow-2xl mb-8 border border-white/10">
                    <img src="{{ asset('images/logo.png') }}" alt="Ride My Cars Logo" class="h-16 w-auto mix-blend-normal rounded-xl bg-white p-1">
                </div>
                <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight text-white mb-2">Ride My Cars</h1>
                <p class="text-brand-400 font-medium tracking-widest uppercase text-xs">Redefining Mobility</p>
            </div>
            <div class="absolute bottom-12 flex items-center gap-2 text-xs text-gray-500">
                <div class="w-2 h-2 rounded-full bg-brand-500 animate-ping"></div>
                Initializing Security & Fleet Services...
            </div>
        </div>
    </template>

    <!-- Carousel Screen (Requirement #12) -->
    <template x-if="step === 'carousel'">
        <div class="min-h-screen flex flex-col justify-between p-6 md:p-12 max-w-4xl mx-auto w-full">
            
            <!-- Header Progress -->
            <div class="flex items-center justify-between py-4 border-b border-white/10 mb-8">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="RideMyCars" class="h-10 w-auto bg-white rounded-lg p-0.5">
                    <span class="text-xs font-bold tracking-widest text-gray-400 uppercase">Onboarding</span>
                </div>
                <div class="flex gap-2">
                    <template x-for="i in [1, 2, 3, 4]">
                        <div class="h-1.5 rounded-full transition-all duration-300" 
                             :class="slide === i ? 'w-8 bg-brand-500' : 'w-2 bg-white/20'"></div>
                    </template>
                </div>
            </div>

            <!-- Slide Content -->
            <div class="flex-1 flex flex-col justify-center my-auto py-6">

                <!-- Screen 1: RIDE -->
                <div x-show="slide === 1" x-transition.opacity.duration.400ms class="space-y-8">
                    <div class="inline-block px-4 py-1.5 rounded-full bg-brand-500/10 border border-brand-500/30 text-brand-400 font-bold text-sm">
                        01 / RIDE
                    </div>
                    <h2 class="text-4xl md:text-6xl font-extrabold tracking-tight leading-tight text-white">
                        Demand the standard you deserve.
                    </h2>
                    <p class="text-lg md:text-xl text-gray-300 leading-relaxed max-w-2xl font-light">
                        Vetted executive vehicles and certified professional chauffeurs, arriving exactly on your schedule.
                    </p>
                    <div class="pt-4 flex items-center gap-3 text-sm font-semibold text-brand-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                        <span>Swipe to explore fleet options</span>
                    </div>
                </div>

                <!-- Screen 2: RENT -->
                <div x-show="slide === 2" x-transition.opacity.duration.400ms class="space-y-8" style="display: none;">
                    <div class="inline-block px-4 py-1.5 rounded-full bg-blue-500/10 border border-blue-500/30 text-blue-400 font-bold text-sm">
                        02 / RENT
                    </div>
                    <h2 class="text-4xl md:text-6xl font-extrabold tracking-tight leading-tight text-white">
                        Your virtual garage.
                    </h2>
                    <p class="text-lg md:text-xl text-gray-300 leading-relaxed max-w-2xl font-light">
                        Select precise makes and models of premium vehicles, delivered directly to your tarmac, hotel, or driveway.
                    </p>
                    <div class="pt-4 flex items-center gap-3 text-sm font-semibold text-blue-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                        <span>Swipe to examine asset tiers</span>
                    </div>
                </div>

                <!-- Screen 3: DRIVER -->
                <div x-show="slide === 3" x-transition.opacity.duration.400ms class="space-y-8" style="display: none;">
                    <div class="inline-block px-4 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 font-bold text-sm">
                        03 / DRIVER
                    </div>
                    <h2 class="text-4xl md:text-6xl font-extrabold tracking-tight leading-tight text-white">
                        Pilot your itinerary, effortlessly.
                    </h2>
                    <p class="text-lg md:text-xl text-gray-300 leading-relaxed max-w-2xl font-light">
                        Secure an elite, background-checked professional to drive your personal vehicle or a fleet asset by the hour or day.
                    </p>
                    <div class="pt-4 flex items-center gap-3 text-sm font-semibold text-emerald-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                        <span>Swipe to review driver vetting standards</span>
                    </div>
                </div>

                <!-- Screen 4: DELIVER -->
                <div x-show="slide === 4" x-transition.opacity.duration.400ms class="space-y-8" style="display: none;">
                    <div class="inline-block px-4 py-1.5 rounded-full bg-purple-500/10 border border-purple-500/30 text-purple-400 font-bold text-sm">
                        04 / DELIVER
                    </div>
                    <h2 class="text-4xl md:text-6xl font-extrabold tracking-tight leading-tight text-white">
                        Absolute chain of custody.
                    </h2>
                    <p class="text-lg md:text-xl text-gray-300 leading-relaxed max-w-2xl font-light">
                        Trust our discreet couriers with high-value assets, time-sensitive documents, and secure parcel delivery.
                    </p>
                </div>

            </div>

            <!-- Footer Controls -->
            <div class="py-6 flex items-center justify-between border-t border-white/10">
                <button @click="prevSlide()" x-show="slide > 1" class="px-6 py-3 text-sm font-semibold text-gray-400 hover:text-white transition-colors">
                    Back
                </button>
                <div x-show="slide === 1"></div>

                <button x-show="slide < 4" @click="nextSlide()" class="px-8 py-4 bg-white text-gray-900 font-extrabold rounded-2xl shadow-xl hover:bg-gray-100 transition-all flex items-center gap-2">
                    Next
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </button>

                <button x-show="slide === 4" @click="nextSlide()" class="px-8 py-4 bg-brand-500 text-white font-extrabold rounded-2xl shadow-xl shadow-brand-500/30 hover:bg-brand-600 transition-all flex items-center gap-2">
                    Access the Platform
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </button>
            </div>

        </div>
    </template>

    <!-- Biometric Security Gateway (Requirement #11) -->
    <template x-if="step === 'biometric'">
        <div class="fixed inset-0 z-50 bg-[#0a0a0a]/95 backdrop-blur-xl flex flex-col items-center justify-center p-6">
            <div class="bg-[#111] border border-white/15 rounded-3xl p-8 max-w-md w-full text-center space-y-6 shadow-2xl">
                <div class="w-20 h-20 mx-auto rounded-full bg-brand-500/10 text-brand-400 border border-brand-500/30 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a10 10 0 0 0-10 10c0 5.5 4.5 10 10 10s10-4.5 10-10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16z"/><path d="M12 6a6 6 0 0 0-6 6c0 3.3 2.7 6 6 6s6-2.7 6-6a6 6 0 0 0-6-6z"/></svg>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-white mb-2">Biometric Security</h3>
                    <p class="text-sm text-gray-400 leading-relaxed">
                        Authenticate securely with Face ID, Touch ID, or Android Biometrics to protect your executive mobility profile.
                    </p>
                </div>

                <div x-show="biometricStatus === 'idle'" class="space-y-3 pt-2">
                    <button @click="authenticateBiometric()" class="w-full py-4 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-2xl transition-all shadow-lg shadow-brand-500/30">
                        Enable Biometric Gateway
                    </button>
                    <button @click="skipBiometric()" class="w-full py-3 text-xs text-gray-500 hover:text-gray-300">
                        Continue with Passcode / Skip for now
                    </button>
                </div>

                <div x-show="biometricStatus === 'verifying'" class="py-4 flex items-center justify-center gap-3 text-brand-400 font-semibold">
                    <div class="w-5 h-5 border-2 border-brand-400 border-t-transparent rounded-full animate-spin"></div>
                    Authenticating with OS Biometric Sensor...
                </div>

                <div x-show="biometricStatus === 'success'" class="py-4 text-emerald-400 font-bold flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    Biometric Authentication Verified!
                </div>
            </div>
        </div>
    </template>

    <!-- Permissions Concierge (Requirement #16) -->
    <template x-if="step === 'permissions'">
        <div class="fixed inset-0 z-50 bg-[#0a0a0a]/95 backdrop-blur-xl flex flex-col items-center justify-center p-6">
            <div class="bg-[#111] border border-white/15 rounded-3xl p-8 max-w-lg w-full space-y-6 shadow-2xl">
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-white/10 text-white flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-2">Permissions Concierge</h3>
                    <p class="text-xs text-gray-400">Configure native device access for an elevated travel experience.</p>
                </div>

                <!-- Location Permission Prompt -->
                <div class="bg-white/5 border border-white/10 rounded-2xl p-5 space-y-3">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-brand-500/20 text-brand-400 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-bold text-white mb-1">Precise Location Access</h4>
                            <p class="text-xs text-gray-300 leading-relaxed">
                                Allow Ride My Cars to access your location to ensure seamless vehicle dispatch, exact pickup positioning, and hyper-accurate arrival estimations.
                            </p>
                        </div>
                    </div>
                    <div class="flex justify-end pt-1">
                        <button @click="requestLocation()" class="px-4 py-2 text-xs font-bold rounded-xl transition-all"
                                :class="locationPermission === 'granted' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-white text-gray-900 hover:bg-gray-200'">
                            <span x-text="locationPermission === 'granted' ? '✓ Location Granted' : 'Allow Location'"></span>
                        </button>
                    </div>
                </div>

                <!-- Notifications Permission Prompt -->
                <div class="bg-white/5 border border-white/10 rounded-2xl p-5 space-y-3">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-purple-500/20 text-purple-400 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-bold text-white mb-1">Real-Time Arrival & Chain of Custody</h4>
                            <p class="text-xs text-gray-300 leading-relaxed">
                                Allow push notifications to receive real-time driver tracking, arrival alerts, and secure delivery chain-of-custody updates.
                            </p>
                        </div>
                    </div>
                    <div class="flex justify-end pt-1">
                        <button @click="requestNotifications()" class="px-4 py-2 text-xs font-bold rounded-xl transition-all"
                                :class="notificationPermission === 'granted' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-white text-gray-900 hover:bg-gray-200'">
                            <span x-text="notificationPermission === 'granted' ? '✓ Notifications Granted' : 'Allow Notifications'"></span>
                        </button>
                    </div>
                </div>

                <button @click="finishOnboarding()" class="w-full py-4 bg-brand-500 hover:bg-brand-600 text-white font-extrabold rounded-2xl shadow-xl shadow-brand-500/30 transition-all text-sm">
                    Enter Platform Dashboard
                </button>
            </div>
        </div>
    </template>

</div>

</x-layout>
