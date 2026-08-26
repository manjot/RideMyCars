@props(['category' => 'Ride'])

@php
    $catName = $category;
    $banners = \App\Models\Banner::with('category')
        ->where('status', 'active')
        ->whereHas('category', function ($q) use ($catName) {
            $q->where('name', $catName)
              ->orWhere('slug', \Illuminate\Support\Str::slug($catName));
        })
        ->get();
@endphp

@if($banners->count() > 0)
    <div class="w-full my-6">
        <div class="grid grid-cols-1 md:grid-cols-{{ min($banners->count(), 2) }} gap-4">
            @foreach($banners as $banner)
                <div class="relative overflow-hidden rounded-3xl border border-amber-200/60 dark:border-amber-900/30 bg-gradient-to-r from-amber-500/10 via-amber-400/5 to-transparent p-6 shadow-sm hover:shadow-md transition-all group">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="space-y-2 max-w-xl">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-500 text-white font-extrabold text-[10px] uppercase tracking-wider shadow-xs">
                                    🏷️ {{ $banner->category->name }}
                                </span>
                                <span class="text-[11px] font-bold text-amber-600 dark:text-amber-400">Featured Offer</span>
                            </div>
                            <h3 class="text-xl font-black text-gray-900 dark:text-white group-hover:text-amber-500 transition-colors">
                                {{ $banner->title }}
                            </h3>
                            @if($banner->description)
                                <p class="text-xs text-gray-600 dark:text-gray-400 font-medium leading-relaxed">
                                    {{ $banner->description }}
                                </p>
                            @endif
                            @if($banner->link)
                                <div class="pt-1">
                                    <button type="button"
                                            onclick="handleExploreBannerOffer('{{ $banner->link }}', '{{ addslashes($banner->title) }}')"
                                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs rounded-xl shadow-sm transition-all cursor-pointer">
                                        <span>Explore Offer</span>
                                        <span>→</span>
                                    </button>
                                </div>
                            @endif
                        </div>
                        @if($banner->image)
                            @php
                                $img = $banner->image;
                                if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) {
                                    $imageUrl = $img;
                                } elseif ($img && file_exists(public_path($img))) {
                                    $imageUrl = asset($img);
                                } elseif ($img && file_exists(public_path('storage/' . $img))) {
                                    $imageUrl = asset('storage/' . $img);
                                } else {
                                    $imageUrl = asset($img);
                                }
                            @endphp
                            <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl overflow-hidden border border-amber-200/80 dark:border-amber-800/40 shrink-0 shadow-md">
                                <img src="{{ $imageUrl }}" 
                                     alt="{{ $banner->title }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                     onError="this.onerror=null;this.src='{{ asset('images/hero-delivery.png') }}';">
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script>
        if (typeof window.handleExploreBannerOffer === 'undefined') {
            window.handleExploreBannerOffer = function(linkUrl, bannerTitle) {
                const currentPath = window.location.pathname;
                
                // If link points to another page, navigate there
                if (linkUrl && linkUrl !== '#' && linkUrl !== currentPath && !currentPath.endsWith(linkUrl)) {
                    window.location.href = linkUrl;
                    return;
                }

                // If on the same page, scroll smoothly to the main input/form
                const targetInput = document.getElementById('pickup_location') 
                                 || document.getElementById('pickup_location_input')
                                 || document.getElementById('search')
                                 || document.querySelector('form input[type="text"]');

                if (targetInput) {
                    targetInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    setTimeout(() => {
                        targetInput.focus();
                        targetInput.classList.add('ring-4', 'ring-amber-500/50');
                        setTimeout(() => targetInput.classList.remove('ring-4', 'ring-amber-500/50'), 2500);
                    }, 500);
                }
            };
        }
    </script>
@endif
