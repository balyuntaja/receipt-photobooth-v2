<div class="h-full w-full flex flex-col p-4">
    <div class="flex items-center justify-between mb-4">
        <button type="button" id="btn-capture-back" class="px-4 py-2 text-gray-600 hover:text-gray-900 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back
        </button>
        <span class="text-gray-600" id="capture-status">0/1 photos</span>
    </div>
    <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2 relative aspect-video bg-black/40 rounded-xl overflow-hidden">
            <video id="video-preview" class="w-full h-full object-cover rounded-xl" autoplay playsinline muted></video>
            <div id="countdown-overlay" class="absolute inset-0 hidden items-center justify-center bg-black/50 rounded-xl">
                <span id="countdown-number" class="text-8xl font-bold text-white drop-shadow-lg">3</span>
            </div>
            <div id="camera-start-overlay" class="absolute inset-0 flex items-center justify-center bg-black/50 rounded-xl">
                <button type="button" id="btn-start-camera" class="px-8 py-4 text-xl font-semibold rounded-full bg-white text-black hover:opacity-90">
                    Start Camera
                </button>
            </div>
            <div id="capture-start-overlay" class="absolute inset-0 hidden items-center justify-center rounded-xl">
                <button type="button" id="btn-start-capture" class="px-8 py-4 text-xl font-semibold rounded-full bg-white text-black hover:opacity-90">
                    Start
                </button>
            </div>
        </div>
        <div class="flex flex-col">
            <h2 class="text-lg font-semibold mb-2 text-gray-900">Photos</h2>
            <div id="capture-photos" class="flex-1 space-y-3 min-h-[120px]">
                <div id="capture-empty" class="flex items-center justify-center py-8 text-gray-500 text-sm rounded-lg bg-gray-100">
                    No Photos Yet
                </div>
            </div>
            <button type="button" id="btn-capture-next" disabled class="mt-4 px-6 py-3 rounded-full bg-gray-200 text-gray-500 cursor-not-allowed w-full">
                Selanjutnya
            </button>
        </div>
    </div>
    <canvas id="capture-canvas" class="hidden"></canvas>
</div>
