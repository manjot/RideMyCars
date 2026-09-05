<x-layout>
    <x-slot:title>Blogs & Mobility Insights — Stories, Guides & Fleet News | RideMyCars</x-slot>

    <main class="flex-1 pb-24 overflow-hidden" x-data="{
        activeCategory: 'all',
        searchQuery: '',
        activeArticle: null,
        articles: [
            {
                id: 1,
                title: 'How to Choose the Right Rental Vehicle for Extended Travel',
                slug: 'how-to-choose-rental-vehicle',
                category: 'guides',
                categoryLabel: 'Rental Guides',
                badgeColor: 'blue',
                date: 'March 2, 2025',
                readTime: '6 min read',
                author: 'Taylor Kim',
                authorRole: 'Head of Product',
                image: '{{ asset('images/blog-rental-guide.jpg') }}',
                excerpt: 'From fuel-efficient city compacts to high-clearance luxury SUVs, here is our comprehensive decision checklist for self-drive weekend trips and long-term rentals.',
                content: `Selecting the ideal rental vehicle requires balancing passenger comfort, luggage capacity, terrain dynamics, and fuel efficiency. On RideMyCars, vehicles are listed directly by vetted hosts and local fleet partners, giving you access to genuine photos, vehicle history, and verified mechanical inspections.\n\n### 1. Match Your Vehicle to the Terrain\nIf your trip is restricted to paved metropolitan highways, an economy sedan offers maximum fuel economy and easy parking. However, if you are planning coastal journeys, national park tours, or rural travel, choosing a compact or full-size SUV provides necessary ground clearance and all-wheel drive stability.\n\n### 2. Verify Luggage & Occupant Space\nA common traveler mistake is booking a 5-seater for 4 adults with 4 large suitcases. In reality, most standard sedans fit 2 large check-in bags and 2 carry-ons. For groups with substantial luggage, executive SUVs or luxury passenger vans ensure a relaxed ride.\n\n### 3. Transparent Host Insurance\nEvery rental booking made through RideMyCars includes our $1,000,000 comprehensive third-party liability coverage and pre-authorized security deposit hold. Always complete the digital 360° photo check-in before driving away to log pre-existing conditions.`
            },
            {
                id: 2,
                title: '10 Essential Safety Habits Every City Rider Should Follow',
                slug: '10-safety-habits-city-riders',
                category: 'safety',
                categoryLabel: 'Safety & Security',
                badgeColor: 'emerald',
                date: 'February 24, 2025',
                readTime: '5 min read',
                author: 'Sam Okafor',
                authorRole: 'Head of Operations',
                image: '{{ asset('images/blog-safety-tips.jpg') }}',
                excerpt: 'Proactive habits for safe rideshare travel at night, verifying driver identity, using in-app live GPS tracking, and discreet emergency protocols.',
                content: `Your personal safety is the bedrock of everything we engineer. While RideMyCars enforces strict 7-point background checks on all drivers, practicing smart rider hygiene guarantees total peace of mind.\n\n### 1. The 3-Point Pre-Entry Verification\nNever ask 'Who are you picking up?'. Instead, inspect the vehicle license plate, confirm the car make/model, and verify that the driver's face matches the profile photo displayed in your app.\n\n### 2. Live GPS Route Sharing\nWith one tap on your active trip screen, share your live route with family or close friends. They can monitor your ETA and speed in real-time through any mobile browser.\n\n### 3. Backseat Position & Door Checks\nAlways sit in the rear seat opposite the driver. This gives you optimal sightlines, personal space, and the ability to exit the car from either side safely onto pedestrian curbs.`
            },
            {
                id: 3,
                title: 'How Modern Businesses Save 30% on Corporate Travel & Chauffeurs',
                slug: 'businesses-save-on-corporate-travel',
                category: 'business',
                categoryLabel: 'Business & Fleet',
                badgeColor: 'purple',
                date: 'February 18, 2025',
                readTime: '7 min read',
                author: 'Alex Rivera',
                authorRole: 'CEO & Co-founder',
                image: '{{ asset('images/blog-corporate-travel.jpg') }}',
                excerpt: 'Consolidated billing, dedicated executive chauffeurs, zero expense receipt chaos, and audited travel logs for corporate teams.',
                content: `Enterprise travel has historically suffered from fragmented billing and erratic service quality. Executive staff waste hours submitting messy paper receipts, while procurement teams struggle to audit variable ride surges.\n\n### Centralized Corporate Mobility Accounts\nWith RideMyCars Corporate Accounts, companies assign team budgets with centralized monthly invoicing. Employees book airport transfers, full-day chauffeurs, and client transports under single corporate billing with zero personal card expense reports.\n\n### Guaranteed Executive Vehicles\nAll corporate tier bookings assign top-rated drivers (minimum 4.9★ rating) with spotless executive sedans and SUVs, ensuring visiting executives and VIP clients receive punctual, world-class hospitality.`
            },
            {
                id: 4,
                title: 'Driver Partner Spotlight: Keeping 90% and Building Financial Freedom',
                slug: 'driver-spotlight-keeping-90-percent',
                category: 'drivers',
                categoryLabel: 'Driver Stories',
                badgeColor: 'amber',
                date: 'February 10, 2025',
                readTime: '6 min read',
                author: 'Editorial Team',
                authorRole: 'RideMyCars Community',
                image: '{{ asset('images/blog-driver-earnings.jpg') }}',
                excerpt: 'How our flat 10% commission model puts an extra GH₵1,950 to $400 every month back into the hands of hardworking drivers and their families.',
                content: `In the traditional rideshare market, platform commissions have crept upward to 25%, 28%, or even 32% when booking fees and deductions are tallied. Drivers carry the full capital risk of vehicle loans, fuel, and tire wear while seeing their margins evaporate.\n\n### The 90% Fair Share Revolution\nRideMyCars inverted this formula by establishing a transparent 10% platform commission cap. For full-time drivers averaging GH₵500 in daily fares, keeping 90% means retaining an extra GH₵75 every single day—totaling over GH₵1,950 in additional take-home pay each month.\n\n### Real Impact for Real Families\nOur drivers report using these additional profits to settle children's school fees on time, upgrade vehicle maintenance, and invest in second vehicles to list on our host marketplace.`
            },
            {
                id: 5,
                title: 'Ride vs Rent vs Hire Chauffeur vs Delivery: Which Service Fits You?',
                slug: 'ride-vs-rent-vs-chauffeur-comparison',
                category: 'guides',
                categoryLabel: 'Service Comparison',
                badgeColor: 'blue',
                date: 'January 28, 2025',
                readTime: '4 min read',
                author: 'Jamie Chen',
                authorRole: 'CTO & Co-founder',
                image: '{{ asset('images/blog-service-comparison.jpg') }}',
                excerpt: 'Navigating our four mobility pillars to select the most cost-effective and comfortable solution for your daily urban needs.',
                content: `Unlike conventional single-purpose apps, RideMyCars combines four mobility modes under one balance. Here is when to use each service:\n\n### 1. On-Demand Ride Hailing\nBest for point-to-point urban trips, nightlife outings, and quick meetings where parking is congested or expensive.\n\n### 2. Self-Drive Vehicle Rentals\nIdeal for multi-day weekend road trips, visiting family, business road trips, or when you need temporary vehicle mobility without taxi metering.\n\n### 3. Professional Chauffeur Hire\nPerfect when you already own a personal or company vehicle but want an elite, background-verified professional driver to navigate traffic while you work from the backseat.\n\n### 4. Express Parcel Delivery\nDesigned for same-day delivery of documents, packages, and retail items with real-time GPS courier pin tracking.`
            },
            {
                id: 6,
                title: 'Expanding to 20 New Cities: Bringing Fair Mobility Across Continents',
                slug: 'expanding-to-20-new-cities',
                category: 'news',
                categoryLabel: 'Company News',
                badgeColor: 'rose',
                date: 'January 15, 2025',
                readTime: '5 min read',
                author: 'Alex Rivera',
                authorRole: 'CEO & Co-founder',
                image: '{{ asset('images/blog-city-expansion.jpg') }}',
                excerpt: 'Announcing our 2025 expansion roadmap connecting major commercial centers across North America, Southern Africa, and West Africa.',
                content: `We are thrilled to formally announce the rollout of RideMyCars across 20 additional metropolitan markets throughout 2025.\n\n### Unifying International Ground Travel\nOur expansion anchors key financial and cultural hubs, allowing international business travelers and diasporic communities to use one familiar app whether arriving in Washington DC, Johannesburg, Accra, or London.\n\n### Local Partnerships & Electric Fleets\nAs part of this expansion, RideMyCars is partnering with regional automotive dealers and solar charging networks to introduce zero-emission EV vehicle classes and localized Mobile Money integration.`
            }
        ],
        get filteredArticles() {
            return this.articles.filter(a => {
                const matchesCategory = (this.activeCategory === 'all' || a.category === this.activeCategory);
                const matchesSearch = (!this.searchQuery.trim() || 
                    a.title.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                    a.excerpt.toLowerCase().includes(this.searchQuery.toLowerCase()));
                return matchesCategory && matchesSearch;
            });
        }
    }">

        <!-- Header Section -->
        <section class="relative pt-16 pb-16 lg:pt-24 lg:pb-20 bg-gradient-to-b from-orange-500/5 via-white to-gray-50 dark:from-[#0a0a0a] dark:via-[#111] dark:to-[#0a0a0a]">
            
            <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-orange-100 dark:bg-orange-950/60 border border-orange-300 dark:border-orange-800/40 text-orange-800 dark:text-orange-300 font-extrabold text-xs uppercase tracking-widest mb-6 shadow-sm">
                    <span>📰</span> Mobility Pulse & Insights
                </div>
                <h1 class="text-4xl sm:text-6xl font-black text-gray-900 dark:text-white tracking-tight leading-[1.08] mb-6">
                    Stories, Guides & <span class="text-orange-600 dark:text-orange-400 font-black">Fleet News.</span>
                </h1>
                <p class="text-lg sm:text-xl text-gray-600 dark:text-gray-300 leading-relaxed font-normal max-w-2xl mx-auto">
                    Expert travel tips, driver wealth-building strategies, vehicle host guides, and architectural updates from the team building the future of mobility.
                </p>

                <!-- Search & Filters -->
                <div class="mt-10 max-w-xl mx-auto">
                    <div class="relative">
                        <input type="text" x-model="searchQuery" placeholder="Search mobility guides, driver tips, fleet news..." class="w-full py-4 pl-12 pr-4 rounded-2xl bg-white dark:bg-[#161616] border-2 border-gray-200 dark:border-white/15 text-gray-900 dark:text-white placeholder-gray-400 font-medium text-sm focus:outline-none focus:border-orange-500 shadow-md">
                        <svg class="w-5 h-5 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </div>
                </div>

                <!-- Category Pills -->
                <div class="flex flex-wrap items-center justify-center gap-2.5 mt-6">
                    <button type="button" @click="activeCategory = 'all'" :class="activeCategory === 'all' ? 'bg-orange-600 text-white shadow-md shadow-orange-500/30' : 'bg-white dark:bg-white/5 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-white/10 hover:border-orange-500'" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                        All Stories
                    </button>
                    <button type="button" @click="activeCategory = 'guides'" :class="activeCategory === 'guides' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/30' : 'bg-white dark:bg-white/5 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-white/10 hover:border-blue-500'" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                        Rental Guides
                    </button>
                    <button type="button" @click="activeCategory = 'safety'" :class="activeCategory === 'safety' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-500/30' : 'bg-white dark:bg-white/5 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-white/10 hover:border-emerald-500'" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                        Safety Standards
                    </button>
                    <button type="button" @click="activeCategory = 'business'" :class="activeCategory === 'business' ? 'bg-purple-600 text-white shadow-md shadow-purple-500/30' : 'bg-white dark:bg-white/5 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-white/10 hover:border-purple-500'" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                        Business & Corporate
                    </button>
                    <button type="button" @click="activeCategory = 'drivers'" :class="activeCategory === 'drivers' ? 'bg-amber-600 text-white shadow-md shadow-amber-500/30' : 'bg-white dark:bg-white/5 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-white/10 hover:border-amber-500'" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                        Driver Earnings
                    </button>
                    <button type="button" @click="activeCategory = 'news'" :class="activeCategory === 'news' ? 'bg-rose-600 text-white shadow-md shadow-rose-500/30' : 'bg-white dark:bg-white/5 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-white/10 hover:border-rose-500'" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                        Expansion News
                    </button>
                </div>
            </div>
        </section>

        <!-- Featured Article Spotlight (Shows when no active search/category filter) -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-20" x-show="activeCategory === 'all' && !searchQuery.trim()">
            <div class="bg-white dark:bg-[#141414] rounded-3xl border-2 border-gray-200 dark:border-white/10 shadow-xl overflow-hidden group cursor-pointer hover:border-orange-500 transition-all" @click="activeArticle = articles[0]">
                <div class="grid grid-cols-1 lg:grid-cols-12 items-center">
                    <div class="lg:col-span-7 h-72 sm:h-96 lg:h-[460px] overflow-hidden relative">
                        <img :src="articles[0].image" :alt="articles[0].title" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                        <div class="absolute top-6 left-6">
                            <span class="px-3.5 py-1.5 rounded-full text-xs font-black uppercase tracking-wider bg-blue-600 text-white shadow-md">
                                Featured Spotlight
                            </span>
                        </div>
                    </div>
                    <div class="lg:col-span-5 p-8 sm:p-12">
                        <div class="flex items-center gap-3 text-xs font-extrabold text-blue-600 dark:text-blue-400 mb-3 uppercase tracking-wider">
                            <span x-text="articles[0].categoryLabel"></span>
                            <span>•</span>
                            <span x-text="articles[0].readTime"></span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black text-gray-900 dark:text-white mb-4 leading-snug group-hover:text-orange-500 transition-colors" x-text="articles[0].title"></h2>
                        <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400 leading-relaxed font-normal mb-6" x-text="articles[0].excerpt"></p>
                        
                        <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-white/5">
                            <div class="text-xs">
                                <div class="font-extrabold text-gray-900 dark:text-white" x-text="articles[0].author"></div>
                                <div class="text-gray-500 font-medium" x-text="articles[0].date"></div>
                            </div>
                            <span class="inline-flex items-center gap-1.5 text-xs font-black text-orange-600 dark:text-orange-400 group-hover:translate-x-1 transition-transform">
                                Read Full Guide →
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Articles Grid -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-28">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <template x-for="article in filteredArticles" :key="article.id">
                    <div class="bg-white dark:bg-[#141414] rounded-3xl border-2 border-gray-200 dark:border-white/10 shadow-md hover:shadow-2xl hover:border-orange-500 transition-all overflow-hidden flex flex-col group cursor-pointer" @click="activeArticle = article">
                        <!-- Card Image -->
                        <div class="h-56 overflow-hidden relative">
                            <img :src="article.image" :alt="article.title" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                            <div class="absolute top-4 left-4">
                                <span class="px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-white/95 dark:bg-[#161616]/95 text-gray-900 dark:text-white shadow-md" x-text="article.categoryLabel"></span>
                            </div>
                        </div>

                        <!-- Card Content -->
                        <div class="p-6 sm:p-8 flex flex-col flex-1">
                            <div class="flex items-center gap-2 text-[11px] font-bold text-gray-500 dark:text-gray-400 mb-2">
                                <span x-text="article.readTime"></span>
                                <span>•</span>
                                <span x-text="article.date"></span>
                            </div>

                            <h3 class="text-xl font-black text-gray-900 dark:text-white mb-3 group-hover:text-orange-500 transition-colors leading-snug" x-text="article.title"></h3>

                            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 leading-relaxed font-normal mb-6 flex-1" x-text="article.excerpt"></p>

                            <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-white/5 mt-auto">
                                <div class="text-xs">
                                    <div class="font-extrabold text-gray-900 dark:text-white" x-text="article.author"></div>
                                    <div class="text-[10px] text-gray-500 font-medium" x-text="article.authorRole"></div>
                                </div>
                                <span class="text-xs font-black text-orange-600 dark:text-orange-400 flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                                    Read Story →
                                </span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- No search results fallback -->
            <div x-show="filteredArticles.length === 0" class="text-center py-20 bg-gray-50 dark:bg-[#161616] rounded-3xl border border-gray-200 dark:border-white/10">
                <div class="text-4xl mb-3">🔍</div>
                <h3 class="text-xl font-black text-gray-900 dark:text-white mb-1">No articles found</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Try searching with different keywords or clear your category filter.</p>
                <button type="button" @click="searchQuery = ''; activeCategory = 'all'" class="px-5 py-2.5 rounded-xl bg-orange-500 text-white font-bold text-xs">Reset All Filters</button>
            </div>
        </section>

        <!-- Interactive Article Reading Modal -->
        <div x-show="activeArticle" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/80 backdrop-blur-sm" @keydown.escape.window="activeArticle = null">
            <div class="bg-white dark:bg-[#141414] max-w-3xl w-full max-h-[90vh] rounded-3xl overflow-y-auto border-2 border-gray-200 dark:border-white/15 shadow-2xl relative" @click.outside="activeArticle = null">
                
                <!-- Close Button -->
                <button type="button" @click="activeArticle = null" class="absolute top-4 right-4 z-20 w-10 h-10 rounded-full bg-black/60 hover:bg-black text-white flex items-center justify-center font-bold text-lg transition-all shadow-md">
                    ✕
                </button>

                <template x-if="activeArticle">
                    <div>
                        <!-- Modal Header Banner -->
                        <div class="h-64 sm:h-80 w-full overflow-hidden relative">
                            <img :src="activeArticle.image" :alt="activeArticle.title" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent flex items-end p-6 sm:p-8">
                                <span class="px-3.5 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-orange-600 text-white" x-text="activeArticle.categoryLabel"></span>
                            </div>
                        </div>

                        <!-- Modal Body -->
                        <div class="p-6 sm:p-10 space-y-6">
                            <div class="flex items-center gap-3 text-xs text-gray-500">
                                <span class="font-extrabold text-gray-900 dark:text-white" x-text="activeArticle.author + ' (' + activeArticle.authorRole + ')'"></span>
                                <span>•</span>
                                <span x-text="activeArticle.date"></span>
                                <span>•</span>
                                <span x-text="activeArticle.readTime"></span>
                            </div>

                            <h2 class="text-2xl sm:text-4xl font-black text-gray-900 dark:text-white leading-tight" x-text="activeArticle.title"></h2>

                            <div class="p-4 rounded-2xl bg-orange-50 dark:bg-orange-950/30 border border-orange-200 dark:border-orange-800/40 text-xs sm:text-sm text-orange-950 dark:text-orange-200 font-semibold" x-text="activeArticle.excerpt"></div>

                            <div class="text-sm sm:text-base text-gray-700 dark:text-gray-300 leading-relaxed space-y-4 whitespace-pre-line font-normal" x-text="activeArticle.content"></div>

                            <div class="pt-8 border-t border-gray-200 dark:border-white/10 flex flex-wrap items-center justify-between gap-4">
                                <div class="text-xs text-gray-500 font-medium">Was this article helpful? Share with other travelers.</div>
                                <button type="button" @click="activeArticle = null" class="px-6 py-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-md">
                                    Close Story
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ subscribed: false, email: '' }">
            <div class="rounded-[2.5rem] p-8 sm:p-14 text-center text-white shadow-2xl relative overflow-hidden"
                 style="background: linear-gradient(135deg, #ea580c 0%, #f59e0b 50%, #ea580c 100%) !important; color: #ffffff !important;">
                <div class="relative z-10 max-w-2xl mx-auto">
                    <span class="px-4 py-1.5 rounded-full bg-white/20 text-white font-extrabold text-xs uppercase tracking-widest inline-block mb-4">
                        Weekly Mobility Digest
                    </span>
                    <h2 class="text-3xl sm:text-5xl font-black mb-4 tracking-tight">Stay Ahead with Mobility Pulse</h2>
                    <p class="text-white/90 text-sm sm:text-base mb-8 font-medium">
                        Get verified driver revenue tactics, rental host fleet strategies, and executive city travel reports delivered to your inbox every Thursday.
                    </p>

                    <div x-show="!subscribed">
                        <form @submit.prevent="if(email) subscribed = true" class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                            <input type="email" x-model="email" required placeholder="Enter your email address..." class="flex-1 px-5 py-4 rounded-2xl bg-white text-gray-900 placeholder-gray-400 font-medium text-sm focus:outline-none shadow-lg">
                            <button type="submit" class="px-8 py-4 rounded-2xl bg-gray-950 text-white font-black text-sm hover:bg-black transition-all shadow-xl shrink-0">
                                Subscribe Free
                            </button>
                        </form>
                    </div>

                    <div x-show="subscribed" class="p-4 rounded-2xl bg-white/20 text-white font-bold text-sm">
                        🎉 Thank you! You are subscribed to Mobility Pulse. Check your inbox for our latest fleet guide.
                    </div>
                </div>
            </div>
        </section>

    </main>
</x-layout>
