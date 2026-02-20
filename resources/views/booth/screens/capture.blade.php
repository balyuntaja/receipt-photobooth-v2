    {{--
  Capture screen: camera preview, countdown, photo list, next.
  Uses shared header with dynamic status (capture-status).
--}}
    <div class="kiosk-screen kiosk-screen--with-header flex h-full w-full flex-col">
        @include('booth.components.screen-header', [
        'backId' => 'btn-capture-back',
        'centerId' => 'capture-status',
        'centerLabel' => '0/1 photos',
        ])

        <div class="kiosk-screen-body flex min-h-0 flex-1 flex-col overflow-y-auto p-4">
            <div class="mx-auto grid w-full max-w-4xl flex-1 grid-cols-1 gap-4 md:grid-cols-3 md:items-start">
                {{-- Kolom kiri: camera + exposure --}}
                <div class="flex flex-col gap-3 md:col-span-2">
                    {{-- Camera 4:3 + slot overlay --}}
                    <div id="camera-wrap" class="relative aspect-4/3 w-full overflow-hidden rounded-2xl bg-black/50 shadow-lg">
                        <video id="video-preview" class="absolute inset-0 h-full w-full object-cover" autoplay playsinline muted></video>
                        <div id="capture-slot-overlay" class="pointer-events-none absolute inset-0 hidden">
                            <div id="slot-top" class="absolute left-0 right-0 top-0 bg-black/50"></div>
                            <div id="slot-left" class="absolute bottom-0 left-0 top-0 bg-black/50"></div>
                            <div id="slot-right" class="absolute bottom-0 right-0 top-0 bg-black/50"></div>
                            <div id="slot-bottom" class="absolute bottom-0 left-0 right-0 bg-black/50"></div>
                        </div>
                        <div id="countdown-overlay" class="absolute inset-0 hidden items-center justify-center bg-black/50">
                            <span id="countdown-number" class="text-8xl font-bold text-white drop-shadow-lg" aria-live="polite">3</span>
                        </div>
                        <div id="camera-start-overlay" class="absolute inset-0 flex items-center justify-center bg-black/50">
                            <button type="button" id="btn-start-camera" class="rounded-full bg-white px-8 py-4 text-xl font-semibold text-black hover:opacity-90 transition-opacity">
                                Start Camera
                            </button>
                        </div>
                        <div id="capture-start-overlay" class="absolute inset-0 hidden items-center justify-center">
                            <button type="button" id="btn-start-capture" class="rounded-full bg-white px-8 py-4 text-xl font-semibold text-black hover:opacity-90 transition-opacity">
                                Start
                            </button>
                        </div>
                    </div>
                    {{-- Exposure slider --}}
                    <div class="flex flex-col gap-1.5">
                        <label for="capture-exposure" class="text-sm font-medium text-gray-700">Exposure</label>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-gray-500 w-5 shrink-0" aria-hidden="true">−</span>
                            <input
                                type="range"
                                id="capture-exposure"
                                min="0"
                                max="100"
                                value="50"
                                class="h-2.5 w-full min-w-0 flex-1 cursor-pointer rounded-lg bg-gray-200 accent-gray-700"
                                aria-label="Exposure" />
                            <span class="text-xs text-gray-500 w-5 shrink-0" aria-hidden="true">+</span>
                        </div>
                    </div>
                </div>

                {{-- Photos list + Button Selanjutnya --}}
                <div class="flex flex-col gap-4 md:h-full md:justify-between">
                    <div class="flex flex-col gap-4">
                        <h2 class="text-base font-semibold text-gray-700">Photos</h2>
                        <p class="text-xs text-gray-500">Klik foto untuk mengambil ulang</p>
                        <div id="capture-photos" class="min-h-[120px] space-y-3">
                            <div id="capture-empty" class="flex items-center justify-center rounded-xl bg-white/80 border border-gray-200/60 py-8 text-sm text-gray-500">
                                No Photos Yet
                            </div>
                        </div>
                    </div>
                    <button type="button" id="btn-capture-next" disabled class="w-full cursor-not-allowed rounded-xl bg-gray-200 px-6 py-3 font-medium text-gray-500 transition-all disabled:opacity-60">
                        Selanjutnya
                    </button>
                </div>
            </div>
        </div>
        <canvas id="capture-canvas" class="hidden" aria-hidden="true"></canvas>
    </div>