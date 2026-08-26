<x-layout>
    <x-slot:title>RideMyCars — RideMyCars</x-slot>

    <main class="flex-1">
        
        <!-- Hero Section -->
        <section class="relative pt-12 pb-16 lg:pt-20 lg:pb-24 overflow-hidden bg-white dark:bg-[#111]">
            <!-- Background pattern/gradient if needed -->
            <div class="absolute inset-0 bg-[radial-gradient(#f3f4f6_1px,transparent_1px)] dark:bg-[radial-gradient(#333_1px,transparent_1px)] [background-size:20px_20px] opacity-30 -z-10"></div>
            
            <div class="relative max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 pt-8">
                <!-- Header -->
                <div class="text-center max-w-4xl mx-auto mb-16">
                    <h1 class="text-5xl md:text-7xl font-extrabold text-gray-900 dark:text-white tracking-tight leading-[1.1] mb-6">
                        One App. <span class="text-[#e40e1b]">Four Ways</span> to Move.
                    </h1>
                    <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 leading-relaxed max-w-4xl mx-auto">
                        Time is the ultimate luxury. How you spend it—and how you move through it—should be entirely under your control. Welcome to Ride My Cars, a single platform built to streamline travel logistics for the discerning professional.
                    </p>
                </div>

                <!-- 4 Column Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8 mb-16">
                    
                    <!-- Ride Card -->
                    <div class="relative bg-white dark:bg-[#161616] rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.12)] overflow-hidden border border-gray-100 dark:border-white/10 flex flex-col pt-12 pb-8 px-6 text-center group transition-transform hover:-translate-y-1 duration-300">
                        <div class="absolute top-0 left-0 w-full h-40" style="background: linear-gradient(180deg, #ffedd5 0%, rgba(255,237,213,0) 100%);"></div>
                        <div class="relative z-10 flex-1 flex flex-col items-center">
                            <div class="w-16 h-16 text-white rounded-full flex items-center justify-center mb-6" style="background: linear-gradient(135deg, #f97316, #ea580c); box-shadow: 0 10px 25px -5px rgba(249, 115, 22, 0.45);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>
                            </div>
                            <h3 class="text-2xl font-bold mb-3" style="color: #f97316;">Ride</h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-8 max-w-[250px] leading-relaxed">On-demand executive transport.</p>
                            
                            <!-- Vehicle Image -->
                            <div class="relative w-[120%] h-48 -mx-[10%] mt-auto flex items-center justify-center overflow-visible mb-6">
                                <img src="{{ asset('images/hero-ride.png') }}" alt="Ride" class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-105" style="mix-blend-mode: multiply;">
                            </div>
                            
                            <a href="/ride" class="mt-auto w-full inline-flex items-center justify-center text-white font-bold py-4 px-8 rounded-full transition-all hover:opacity-95 hover:shadow-lg hover:-translate-y-0.5" style="background: linear-gradient(90deg, #f97316, #ea580c); box-shadow: 0 10px 20px -5px rgba(249, 115, 22, 0.4);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                Book Ride
                            </a>
                        </div>
                    </div>

                    <!-- Rent Card -->
                    <div class="relative bg-white dark:bg-[#161616] rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.12)] overflow-hidden border border-gray-100 dark:border-white/10 flex flex-col pt-12 pb-8 px-6 text-center group transition-transform hover:-translate-y-1 duration-300">
                        <div class="absolute top-0 left-0 w-full h-40" style="background: linear-gradient(180deg, #dbeafe 0%, rgba(219,234,254,0) 100%);"></div>
                        <div class="relative z-10 flex-1 flex flex-col items-center">
                            <div class="w-16 h-16 text-white rounded-full flex items-center justify-center mb-6" style="background: linear-gradient(135deg, #3b82f6, #2563eb); box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.45);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
                            </div>
                            <h3 class="text-2xl font-bold mb-3" style="color: #3b82f6;">Rent</h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-8 max-w-[250px] leading-relaxed">Tier-one luxury vehicles at your fingertips.</p>
                            
                            <!-- Vehicle Image -->
                            <div class="relative w-[120%] h-48 -mx-[10%] mt-auto flex items-center justify-center overflow-visible mb-6">
                                <img src="{{ asset('images/hero-rent.png') }}" alt="Rent" class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-105" style="mix-blend-mode: multiply;">
                            </div>
                            
                            <a href="/rent" class="mt-auto w-full inline-flex items-center justify-center text-white font-bold py-4 px-8 rounded-full transition-all hover:opacity-95 hover:shadow-lg hover:-translate-y-0.5" style="background: linear-gradient(90deg, #3b82f6, #2563eb); box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.4);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                                Rent Now
                            </a>
                        </div>
                    </div>

                    <!-- Driver Card -->
                    <div class="relative bg-white dark:bg-[#161616] rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.12)] overflow-hidden border border-gray-100 dark:border-white/10 flex flex-col pt-12 pb-8 px-6 text-center group transition-transform hover:-translate-y-1 duration-300">
                        <div class="absolute top-0 left-0 w-full h-40" style="background: linear-gradient(180deg, #dcfce7 0%, rgba(220,252,231,0) 100%);"></div>
                        <div class="relative z-10 flex-1 flex flex-col items-center">
                            <div class="w-16 h-16 text-white rounded-full flex items-center justify-center mb-6" style="background: linear-gradient(135deg, #22c55e, #16a34a); box-shadow: 0 10px 25px -5px rgba(34, 197, 94, 0.45);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                            <h3 class="text-2xl font-bold mb-3" style="color: #22c55e;">Driver</h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-8 max-w-[250px] leading-relaxed">Secure an elite, vetted chauffeur for your itinerary.</p>
                            
                            <!-- Vehicle Image -->
                            <div class="relative w-[120%] h-48 -mx-[10%] mt-auto flex items-center justify-center overflow-visible mb-6">
                                <img src="{{ asset('images/hero-hire.png') }}" alt="Driver" class="w-full h-full object-contain object-bottom transition-transform duration-500 group-hover:scale-105" style="mix-blend-mode: multiply;">
                            </div>
                            
                            <a href="/hire-driver" class="mt-auto w-full inline-flex items-center justify-center text-white font-bold py-4 px-8 rounded-full transition-all hover:opacity-95 hover:shadow-lg hover:-translate-y-0.5" style="background: linear-gradient(90deg, #22c55e, #16a34a); box-shadow: 0 10px 20px -5px rgba(34, 197, 94, 0.4);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                Hire Driver
                            </a>
                        </div>
                    </div>

                    <!-- Deliver Card -->
                    <div class="relative bg-white dark:bg-[#161616] rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.12)] overflow-hidden border border-gray-100 dark:border-white/10 flex flex-col pt-12 pb-8 px-6 text-center group transition-transform hover:-translate-y-1 duration-300">
                        <div class="absolute top-0 left-0 w-full h-40" style="background: linear-gradient(180deg, #f3e8ff 0%, rgba(243,232,255,0) 100%);"></div>
                        <div class="relative z-10 flex-1 flex flex-col items-center">
                            <div class="w-16 h-16 text-white rounded-full flex items-center justify-center mb-6" style="background: linear-gradient(135deg, #a855f7, #9333ea); box-shadow: 0 10px 25px -5px rgba(168, 85, 247, 0.45);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                            </div>
                            <h3 class="text-2xl font-bold mb-3" style="color: #a855f7;">Deliver</h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-8 max-w-[250px] leading-relaxed">Flawless, white-glove courier dispatch.</p>
                            
                            <!-- Vehicle Image -->
                            <div class="relative w-[120%] h-48 -mx-[10%] mt-auto flex items-center justify-center overflow-visible mb-6">
                                <img src="{{ asset('images/hero-delivery.png') }}" alt="Deliver" class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-105" style="mix-blend-mode: multiply;">
                            </div>
                            
                            <a href="/delivery" class="mt-auto w-full inline-flex items-center justify-center text-white font-bold py-4 px-8 rounded-full transition-all hover:opacity-95 hover:shadow-lg hover:-translate-y-0.5" style="background: linear-gradient(90deg, #a855f7, #9333ea); box-shadow: 0 10px 20px -5px rgba(168, 85, 247, 0.4);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                                Send Delivery
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Trust indicators (Bottom Bar) -->
                <div class="flex flex-wrap items-center justify-center gap-6 lg:gap-12 px-8 py-5 bg-white dark:bg-[#1a1a1a] rounded-full shadow-[0_8px_30px_rgb(0,0,0,0.06)] border border-gray-100 dark:border-white/5 max-w-4xl mx-auto">
                    <div class="flex items-center gap-2 text-sm lg:text-base font-bold text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        Fully Insured
                    </div>
                    <div class="flex items-center gap-3 text-sm lg:text-base font-bold text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-yellow-500"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        Verified Drivers
                    </div>
                    <div class="flex items-center gap-2 text-sm lg:text-base font-bold text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-500"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        24/7 Support
                    </div>
                    <div class="flex items-center gap-3 text-sm lg:text-base font-bold text-gray-700 dark:text-gray-300 pl-4 border-l border-gray-200 dark:border-gray-800">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-yellow-500 fill-yellow-500"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <div class="flex flex-col text-xs leading-tight font-medium text-gray-500 dark:text-gray-400">
                            Avg Rating
                            <span class="text-sm font-bold text-gray-900 dark:text-white">4.9 / 5.0</span>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- Stats Section (Screenshot 1) -->
        <section class="border-y border-gray-100 dark:border-white/10 bg-white dark:bg-[#111] py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center divide-x divide-gray-100">
                    <div>
                        <div class="text-4xl font-black text-gray-900 dark:text-white mb-1">50K+</div>
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Happy Riders</div>
                    </div>
                    <div>
                        <div class="text-4xl font-black text-gray-900 dark:text-white mb-1">3.2K+</div>
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Verified Drivers</div>
                    </div>
                    <div>
                        <div class="text-4xl font-black text-gray-900 dark:text-white mb-1">1.5K+</div>
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Vehicles Listed</div>
                    </div>
                    <div>
                        <div class="text-4xl font-black text-gray-900 dark:text-white mb-1 flex items-center justify-center gap-1">4.9<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" stroke="none" class="text-gray-900 dark:text-white"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></div>
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Average Rating</div>
                    </div>
                </div>
            </div>
        </section>


        <!-- Customer Categories Section (Business Travelers, Tourists, Locals) -->
        <section class="py-16 bg-gray-50 dark:bg-[#1a1a1a] border-t border-gray-100 dark:border-white/10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center mb-12">
                    <h3 class="text-brand-500 font-bold text-sm tracking-widest uppercase mb-3">Tailored Solutions</h3>
                    <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 dark:text-white mb-4 tracking-tight">Crafted for Every Journey</h2>
                    <p class="text-lg text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">Customized mobility logistics engineered to exceed the expectations of corporate leaders, travelers, and residents.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Business Travelers Section -->
                    <div class="bg-white dark:bg-[#111] border border-gray-200 dark:border-white/10 rounded-3xl p-8 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="w-12 h-12 bg-gray-100 dark:bg-white/10 text-gray-900 dark:text-white rounded-2xl flex items-center justify-center mb-6 font-bold">
                                💼
                            </div>
                            <div class="text-xs font-bold text-brand-500 uppercase tracking-widest mb-2">Business Travelers</div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Elite Mobility for the Corporate Traveler.</h3>
                            <p class="text-gray-600 dark:text-gray-400 leading-relaxed text-sm">
                                Command your schedule with executive-grade transport. Seamlessly secure a premium ride, reserve an upscale vehicle, or utilize a vetted professional driver for your entire itinerary. Deliveries and logistics are handled instantly, leaving you free to focus on business.
                            </p>
                        </div>
                        <div class="mt-8 pt-6 border-t border-gray-100 dark:border-white/10 flex flex-wrap gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400">
                            <span class="px-2.5 py-1 bg-gray-100 dark:bg-white/5 rounded-full">• Executive Rides</span>
                            <span class="px-2.5 py-1 bg-gray-100 dark:bg-white/5 rounded-full">• Luxury Rentals</span>
                            <span class="px-2.5 py-1 bg-gray-100 dark:bg-white/5 rounded-full">• Pro Chauffeurs</span>
                            <span class="px-2.5 py-1 bg-gray-100 dark:bg-white/5 rounded-full">• Instant Logistics</span>
                        </div>
                    </div>

                    <!-- Tourists Section -->
                    <div class="bg-white dark:bg-[#111] border border-gray-200 dark:border-white/10 rounded-3xl p-8 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="w-12 h-12 bg-gray-100 dark:bg-white/10 text-gray-900 dark:text-white rounded-2xl flex items-center justify-center mb-6 font-bold">
                                ✈️
                            </div>
                            <div class="text-xs font-bold text-brand-500 uppercase tracking-widest mb-2">Tourists</div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">First-Class Exploration, Simplified.</h3>
                            <p class="text-gray-600 dark:text-gray-400 leading-relaxed text-sm">
                                Experience your destination with unparalleled comfort and ease. Whether arriving in style via private transport, renting a luxury vehicle for a scenic drive, or hiring a dedicated local chauffeur, your journey is entirely curated. Enjoy effortless luggage and parcel delivery directly to your resort.
                            </p>
                        </div>
                        <div class="mt-8 pt-6 border-t border-gray-100 dark:border-white/10 flex flex-wrap gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400">
                            <span class="px-2.5 py-1 bg-gray-100 dark:bg-white/5 rounded-full">• Private Transport</span>
                            <span class="px-2.5 py-1 bg-gray-100 dark:bg-white/5 rounded-full">• Scenic Rentals</span>
                            <span class="px-2.5 py-1 bg-gray-100 dark:bg-white/5 rounded-full">• Local Chauffeur</span>
                            <span class="px-2.5 py-1 bg-gray-100 dark:bg-white/5 rounded-full">• Luggage Delivery</span>
                        </div>
                    </div>

                    <!-- Locals Section -->
                    <div class="bg-white dark:bg-[#111] border border-gray-200 dark:border-white/10 rounded-3xl p-8 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="w-12 h-12 bg-gray-100 dark:bg-white/10 text-gray-900 dark:text-white rounded-2xl flex items-center justify-center mb-6 font-bold">
                                🏙️
                            </div>
                            <div class="text-xs font-bold text-brand-500 uppercase tracking-widest mb-2">Locals</div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Redefining Daily Transit.</h3>
                            <p class="text-gray-600 dark:text-gray-400 leading-relaxed text-sm">
                                Bring white-glove service to your everyday routine. Access top-tier vehicles, secure certified drivers for special events, or coordinate white-glove courier deliveries across the city. Enjoy absolute convenience and reliability at the touch of a button.
                            </p>
                        </div>
                        <div class="mt-8 pt-6 border-t border-gray-100 dark:border-white/10 flex flex-wrap gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400">
                            <span class="px-2.5 py-1 bg-gray-100 dark:bg-white/5 rounded-full">• Premium Vehicles</span>
                            <span class="px-2.5 py-1 bg-gray-100 dark:bg-white/5 rounded-full">• Certified Drivers</span>
                            <span class="px-2.5 py-1 bg-gray-100 dark:bg-white/5 rounded-full">• Special Events</span>
                            <span class="px-2.5 py-1 bg-gray-100 dark:bg-white/5 rounded-full">• City Courier</span>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- How it Works (Screenshot 3) -->
        <section class="py-16 bg-white dark:bg-[#111] border-t border-gray-100 dark:border-white/10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center mb-12">
                    <h3 class="text-brand-500 font-bold text-sm tracking-widest uppercase mb-3">How It Works</h3>
                    <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 dark:text-white mb-4 tracking-tight">Ready in four steps</h2>
                    <p class="text-lg text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">From search to destination, we've made every step simple and transparent.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-12 relative">
                    <!-- Line connecting steps (desktop) -->
                    <div class="hidden md:block absolute top-12 left-1/8 right-1/8 h-px bg-gray-200 w-3/4 mx-auto z-0"></div>

                    <!-- Step 1 -->
                    <div class="text-center relative z-10">
                        <div class="w-24 h-24 mx-auto bg-blue-50 rounded-3xl flex items-center justify-center mb-6 relative">
                            <div class="absolute -top-3 -right-3 w-8 h-8 bg-brand-500 text-white font-bold rounded-full flex items-center justify-center border-4 border-white shadow-sm">1</div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-500"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Search & Choose</h4>
                        <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">Search for rides, vehicles, or drivers. Filter by price, rating, distance, and availability.</p>
                    </div>

                    <!-- Step 2 -->
                    <div class="text-center relative z-10">
                        <div class="w-24 h-24 mx-auto bg-brand-50 rounded-3xl flex items-center justify-center mb-6 relative">
                            <div class="absolute -top-3 -right-3 w-8 h-8 bg-brand-500 text-white font-bold rounded-full flex items-center justify-center border-4 border-white shadow-sm">2</div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-brand-500"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Book & Pay</h4>
                        <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">Instant booking with secure payments via card or wallet. Get a full receipt and invoice.</p>
                    </div>

                    <!-- Step 3 -->
                    <div class="text-center relative z-10">
                        <div class="w-24 h-24 mx-auto bg-green-50 rounded-3xl flex items-center justify-center mb-6 relative">
                            <div class="absolute -top-3 -right-3 w-8 h-8 bg-brand-500 text-white font-bold rounded-full flex items-center justify-center border-4 border-white shadow-sm">3</div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Track in Real Time</h4>
                        <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">Watch your driver arrive on a live map. Get SMS and push updates at every step.</p>
                    </div>

                    <!-- Step 4 -->
                    <div class="text-center relative z-10">
                        <div class="w-24 h-24 mx-auto bg-purple-50 rounded-3xl flex items-center justify-center mb-6 relative">
                            <div class="absolute -top-3 -right-3 w-8 h-8 bg-brand-500 text-white font-bold rounded-full flex items-center justify-center border-4 border-white shadow-sm">4</div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-purple-500"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Rate Your Experience</h4>
                        <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">Leave a review after your trip. Your feedback keeps our community safe and high quality.</p>
                    </div>
                </div>

            </div>
        </section>

        <!-- Why RideMyCars (Screenshot 4) -->
        <section class="py-16 bg-white dark:bg-[#111] border-t border-gray-100 dark:border-white/10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center mb-10">
                    <h3 class="text-brand-500 font-bold text-sm tracking-widest uppercase mb-3">Why RideMyCars</h3>
                    <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 dark:text-white mb-4 tracking-tight">Built different. For good reason.</h2>
                    <p class="text-lg text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">We combined three separate markets into one platform and raised the bar on every dimension.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    
                    <div class="border border-gray-100 dark:border-white/10 rounded-3xl p-8 hover:border-gray-200 transition-colors">
                        <div class="w-12 h-12 bg-green-50 text-green-500 rounded-2xl flex items-center justify-center mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Safety First</h4>
                        <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">Every driver is background-verified. Vehicles are insured. Your safety is our foundation.</p>
                    </div>

                    <div class="border border-brand-200 rounded-3xl p-8 bg-white dark:bg-[#111] shadow-xl shadow-brand-50/50">
                        <div class="w-12 h-12 bg-yellow-50 text-yellow-500 rounded-2xl flex items-center justify-center mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Lightning Fast</h4>
                        <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">Average driver assignment in under 2 minutes. Real-time matching powered by smart algorithms.</p>
                    </div>

                    <div class="border border-gray-100 dark:border-white/10 rounded-3xl p-8 hover:border-gray-200 transition-colors">
                        <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-2xl flex items-center justify-center mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-3">24/7 Support</h4>
                        <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">Our support team is always on. Chat, email, or call — we respond in minutes, not days.</p>
                    </div>

                    <div class="border border-gray-100 dark:border-white/10 rounded-3xl p-8 hover:border-gray-200 transition-colors">
                        <div class="w-12 h-12 bg-brand-50 text-brand-500 rounded-2xl flex items-center justify-center mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Transparent Pricing</h4>
                        <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">No hidden fees. See your full fare before you book. Pay via card, wallet, or cash.</p>
                    </div>

                    <div class="border border-gray-100 dark:border-white/10 rounded-3xl p-8 hover:border-gray-200 transition-colors">
                        <div class="w-12 h-12 bg-purple-50 text-purple-500 rounded-2xl flex items-center justify-center mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Live Tracking</h4>
                        <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">Track your driver or vehicle in real time on Google Maps. Share your trip for extra safety.</p>
                    </div>

                    <div class="border border-gray-100 dark:border-white/10 rounded-3xl p-8 hover:border-gray-200 transition-colors">
                        <div class="w-12 h-12 bg-pink-50 text-pink-500 rounded-2xl flex items-center justify-center mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Trusted Community</h4>
                        <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">50,000+ reviews from real riders. Our dual rating system keeps quality high on both sides.</p>
                    </div>

                </div>

            </div>
        </section>

        <!-- Platform Features (Screenshot 5) -->
        <section class="py-16 bg-gray-50 dark:bg-[#1a1a1a] border-t border-gray-200 dark:border-white/10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center mb-10">
                    <h3 class="text-brand-500 font-bold text-sm tracking-widest uppercase mb-3">Platform Features</h3>
                    <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 dark:text-white mb-4 tracking-tight">Everything built in</h2>
                    <p class="text-lg text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">No add-ons needed. Every feature you need comes standard.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    
                    <!-- Row 1 -->
                    <div class="bg-white dark:bg-[#111] border border-gray-100 dark:border-white/10 rounded-2xl p-6 hover:shadow-md transition-shadow">
                        <div class="w-10 h-10 bg-brand-50 text-brand-500 rounded-xl flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Smart Wallet</h4>
                        <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">Top up once, pay for everything. Instant refunds. Full transaction history.</p>
                    </div>

                    <div class="bg-white dark:bg-[#111] border border-gray-100 dark:border-white/10 rounded-2xl p-6 hover:shadow-md transition-shadow">
                        <div class="w-10 h-10 bg-brand-50 text-brand-500 rounded-xl flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Real-time Notifications</h4>
                        <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">Email, SMS, and in-app alerts at every step of your booking journey.</p>
                    </div>

                    <div class="bg-white dark:bg-[#111] border border-gray-100 dark:border-white/10 rounded-2xl p-6 hover:shadow-md transition-shadow">
                        <div class="w-10 h-10 bg-brand-50 text-brand-500 rounded-xl flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Instant Invoices</h4>
                        <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">Download detailed PDF invoices for every booking. Perfect for expense reports.</p>
                    </div>

                    <div class="bg-white dark:bg-[#111] border border-gray-100 dark:border-white/10 rounded-2xl p-6 hover:shadow-md transition-shadow">
                        <div class="w-10 h-10 bg-brand-50 text-brand-500 rounded-xl flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l8.29-8.29c.94-.94.94-2.48 0-3.42L12 2Z"/><path d="M7 7h.01"/></svg>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Coupons & Referrals</h4>
                        <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">Share your code, earn credits. Use coupons to save on every booking.</p>
                    </div>

                    <!-- Row 2 -->
                    <div class="bg-white dark:bg-[#111] border border-gray-100 dark:border-white/10 rounded-2xl p-6 hover:shadow-md transition-shadow">
                        <div class="w-10 h-10 bg-brand-50 text-brand-500 rounded-xl flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Saved Locations</h4>
                        <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">Save home, work, and frequent destinations. Book in one tap.</p>
                    </div>

                    <div class="bg-white dark:bg-[#111] border border-gray-100 dark:border-white/10 rounded-2xl p-6 hover:shadow-md transition-shadow">
                        <div class="w-10 h-10 bg-brand-50 text-brand-500 rounded-xl flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Ratings & Reviews</h4>
                        <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">Two-way reviews build trust. Only book highly-rated drivers and vehicles.</p>
                    </div>

                    <div class="bg-white dark:bg-[#111] border-2 border-brand-200 rounded-2xl p-6 shadow-lg shadow-brand-50">
                        <div class="w-10 h-10 bg-brand-500 text-white rounded-xl flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Identity Verification</h4>
                        <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">Verified profiles mean safer trips. We verify drivers, owners, and renters.</p>
                    </div>

                    <div class="bg-white dark:bg-[#111] border border-gray-100 dark:border-white/10 rounded-2xl p-6 hover:shadow-md transition-shadow">
                        <div class="w-10 h-10 bg-brand-50 text-brand-500 rounded-xl flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Easy Refunds</h4>
                        <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">Automatic refunds for cancellations. Wallet credits appear instantly.</p>
                    </div>

                </div>

            </div>
        </section>

        <!-- Download App CTA Section (Requirement #6) -->
        <section class="py-16 bg-gradient-to-r from-brand-600 to-brand-500 text-white border-t border-brand-400">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl md:text-5xl font-extrabold mb-4 tracking-tight">Your invitation to experience seamless travel is now active.</h2>
                <p class="text-lg md:text-xl text-white/90 max-w-2xl mx-auto mb-8 font-medium">Access rides, rentals, chauffeurs, and white-glove deliveries on iOS and Android.</p>
                <div class="flex flex-wrap items-center justify-center gap-4">
                    <a href="{{ asset('ridemycars.apk') }}" download class="inline-flex items-center gap-3 px-8 py-4 bg-white text-gray-900 font-extrabold rounded-2xl shadow-xl hover:bg-gray-100 transition-all text-base">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Download the App
                    </a>
                </div>
            </div>
        </section>

    </main>

</x-layout>