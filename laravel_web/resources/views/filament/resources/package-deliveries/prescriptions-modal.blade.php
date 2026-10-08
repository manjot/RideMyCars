<div x-data="{
    activeIdx: 0,
    zoomLevels: {},
    getZoom(id) { return this.zoomLevels[id] || 1; },
    zoomIn(id) { this.zoomLevels[id] = Math.min((this.getZoom(id) + 0.25), 3.5); },
    zoomOut(id) { this.zoomLevels[id] = Math.max((this.getZoom(id) - 0.25), 0.5); },
    resetZoom(id) { this.zoomLevels[id] = 1; },
    fullscreenModal: false,
    fullscreenUrl: '',
    fullscreenName: '',
    openFullscreen(url, name) {
        this.fullscreenUrl = url;
        this.fullscreenName = name;
        this.fullscreenModal = true;
    }
}" class="space-y-6">

    @if(!$prescriptions || $prescriptions->isEmpty())
        <div class="p-8 text-center bg-gray-50 dark:bg-gray-800/50 rounded-2xl border border-gray-200 dark:border-gray-700">
            <div class="text-4xl mb-2">📋</div>
            <h4 class="text-base font-bold text-gray-800 dark:text-gray-200">No Prescriptions Uploaded</h4>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">This delivery does not have any attached prescription documents.</p>
        </div>
    @else
        <!-- Header Info Card -->
        <div class="flex flex-wrap items-center justify-between gap-4 p-4 bg-emerald-50 dark:bg-emerald-950/30 rounded-2xl border border-emerald-200 dark:border-emerald-800/40">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl font-black">
                    Rx
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white text-sm">
                        Verified Doctor Prescription
                    </h3>
                    <p class="text-xs text-emerald-700 dark:text-emerald-300">
                        {{ $prescriptions->count() }} file{{ $prescriptions->count() > 1 ? 's' : '' }} uploaded for Delivery #{{ $delivery->delivery_code }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                    Pharmeasy Verified
                </span>
            </div>
        </div>

        <!-- Multiple Files Selector Tabs (if more than 1 file) -->
        @if($prescriptions->count() > 1)
            <div class="flex flex-wrap gap-2 border-b border-gray-200 dark:border-gray-700 pb-3">
                @foreach($prescriptions as $idx => $p)
                    <button type="button" 
                            @click="activeIdx = {{ $idx }}"
                            :class="activeIdx === {{ $idx }} ? 'bg-emerald-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-200'"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                        <span>{{ $p->is_pdf ? '📄' : '🖼️' }}</span>
                        <span>Page {{ $idx + 1 }}: {{ Str::limit($p->file_name, 16) }}</span>
                    </button>
                @endforeach
            </div>
        @endif

        <!-- Prescriptions Container -->
        @foreach($prescriptions as $idx => $p)
            <div x-show="activeIdx === {{ $idx }}" class="space-y-4">
                <!-- Metadata Bar -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs bg-gray-50 dark:bg-gray-800/40 p-3.5 rounded-xl border border-gray-200 dark:border-gray-700/60">
                    <div>
                        <span class="block text-gray-400 text-[10px] uppercase font-bold">File Name</span>
                        <span class="font-semibold text-gray-900 dark:text-white truncate block" title="{{ $p->file_name }}">{{ $p->file_name }}</span>
                    </div>
                    <div>
                        <span class="block text-gray-400 text-[10px] uppercase font-bold">File Type & Size</span>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $p->is_pdf ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' }}">
                                {{ strtoupper($p->file_type) }}
                            </span>
                            <span class="text-gray-600 dark:text-gray-300 font-medium">{{ $p->formatted_size }}</span>
                        </div>
                    </div>
                    <div>
                        <span class="block text-gray-400 text-[10px] uppercase font-bold">Uploaded Date & Time</span>
                        <span class="font-semibold text-gray-900 dark:text-white">
                            {{ $p->created_at ? $p->created_at->format('M d, Y • h:i A') : 'N/A' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ $p->download_url }}" 
                           target="_blank"
                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Download
                        </a>
                        <button type="button" 
                                @click="openFullscreen('{{ $p->view_url }}', '{{ addslashes($p->file_name) }}')"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                            Full Screen
                        </button>
                    </div>
                </div>

                <!-- Preview Area with Zoom Controls -->
                <div class="relative bg-gray-900/90 dark:bg-black rounded-2xl p-4 border border-gray-800 overflow-hidden min-h-[360px] flex flex-col items-center justify-center">
                    
                    @if($p->is_pdf)
                        <!-- PDF Viewer Card -->
                        <div class="w-full flex flex-col items-center justify-center py-10 text-center space-y-4">
                            <div class="w-20 h-20 rounded-2xl bg-red-500/10 text-red-500 flex items-center justify-center text-4xl shadow-inner">
                                📄
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-white">{{ $p->file_name }}</h4>
                                <p class="text-xs text-gray-400 mt-1">Medical Prescription PDF Document ({{ $p->formatted_size }})</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <a href="{{ $p->view_url }}" target="_blank" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    Open PDF in Browser
                                </a>
                                <a href="{{ $p->download_url }}" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white text-xs font-bold rounded-xl transition flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    Download PDF
                                </a>
                            </div>
                            <iframe src="{{ $p->view_url }}" class="w-full h-96 rounded-xl border border-gray-800 mt-4 bg-white"></iframe>
                        </div>
                    @else
                        <!-- Image Viewer with Interactive Zoom -->
                        <div class="absolute top-4 right-4 z-10 flex items-center gap-1.5 bg-black/70 backdrop-blur-md px-3 py-1.5 rounded-xl border border-white/20 text-white text-xs font-bold shadow-lg">
                            <button type="button" @click="zoomIn({{ $p->id }})" title="Zoom In" class="p-1 hover:text-emerald-400 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            </button>
                            <span class="px-1 text-[11px] font-mono" x-text="Math.round(getZoom({{ $p->id }}) * 100) + '%'">100%</span>
                            <button type="button" @click="zoomOut({{ $p->id }})" title="Zoom Out" class="p-1 hover:text-emerald-400 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                            </button>
                            <button type="button" @click="resetZoom({{ $p->id }})" title="Reset" class="p-1 hover:text-amber-400 transition text-[10px]">
                                ↺ Reset
                            </button>
                        </div>

                        <div class="w-full overflow-auto max-h-[500px] flex items-center justify-center p-2">
                            <img src="{{ $p->view_url }}" 
                                 alt="Prescription Preview" 
                                 :style="'transform: scale(' + getZoom({{ $p->id }}) + '); transform-origin: center center; transition: transform 0.2s ease-out; cursor: zoom-in;'"
                                 @click="openFullscreen('{{ $p->view_url }}', '{{ addslashes($p->file_name) }}')"
                                 class="max-w-full max-h-[460px] object-contain rounded-lg shadow-2xl">
                        </div>
                    @endif

                </div>
            </div>
        @endforeach

        <!-- Fullscreen Lightbox Modal -->
        <div x-show="fullscreenModal" 
             x-cloak
             class="fixed inset-0 z-50 bg-black/95 flex flex-col justify-between p-4"
             @keydown.escape.window="fullscreenModal = false">
            <div class="flex items-center justify-between text-white border-b border-white/10 pb-3">
                <span class="text-sm font-bold truncate max-w-md" x-text="fullscreenName"></span>
                <button type="button" @click="fullscreenModal = false" class="px-3 py-1 bg-white/20 hover:bg-white/30 rounded-lg text-xs font-bold">
                    ✕ Close
                </button>
            </div>
            <div class="flex-1 flex items-center justify-center p-4 overflow-auto">
                <img :src="fullscreenUrl" class="max-w-full max-h-full object-contain rounded-lg shadow-2xl">
            </div>
        </div>
    @endif
</div>
