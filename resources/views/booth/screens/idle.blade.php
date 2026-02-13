<div class="relative flex h-full w-full flex-col items-center justify-center text-center">
    {{-- Top-right: Lock (Fullscreen) + Camera Setting --}}
    <div class="absolute right-4 top-4 z-10 flex items-center gap-2">
        <button
            type="button"
            id="btn-lock-fullscreen"
            class="rounded-lg p-2 text-white hover:opacity-70 focus:outline-none"
            title="Fullscreen"
            aria-label="Toggle fullscreen"
        >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
            </svg>
        </button>
        <button
            type="button"
            id="btn-camera-setting"
            class="rounded-lg p-2 text-white hover:opacity-70 focus:outline-none"
            title="Camera Settings"
            aria-label="Camera settings"
        >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </button>
    </div>

    {{-- Center: Start button --}}
    <button
        type="button"
        id="btn-start"
        class="animate-bounce rounded-full bg-white px-10 py-4 text-lg font-semibold text-black transition-opacity hover:opacity-90"
    >
        Tap to Start
    </button>
</div>
