{{--
    Camera Settings Modal (Pengaturan Perangkat)
    Camera, printer.
--}}

<div
    id="camera-settings-modal"
    class="booth-modal-backdrop hidden"
    aria-hidden="true"
    role="dialog"
    aria-labelledby="camera-settings-title"
    aria-modal="true">
    <div class="booth-modal">
        {{-- Header --}}
        <div class="mb-4 flex items-center justify-between">
            <h2 id="camera-settings-title" class="text-lg font-semibold text-gray-900">
                Pengaturan Perangkat
            </h2>
            <button
                type="button"
                id="camera-settings-close"
                class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                aria-label="Close">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Camera Selection --}}
        <div class="mb-4">
            <label for="camera-settings-select" class="mb-2 block text-sm font-medium text-gray-700">
                Pilih Kamera
            </label>
            <select
                id="camera-settings-select"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                <option value="">Loading cameras...</option>
            </select>
        </div>

        {{-- Camera Preview --}}
        <div class="mb-4">
            <label class="mb-2 block text-sm font-medium text-gray-700">
                Preview
            </label>
            <div class="relative overflow-hidden rounded-lg bg-gray-100" style="aspect-ratio: 4/3;">
                <video
                    id="camera-settings-preview"
                    class="h-full w-full object-cover"
                    autoplay
                    playsinline
                    muted></video>
                {{-- Placeholder when no stream --}}
                <div class="absolute inset-0 flex items-center justify-center text-gray-400" id="camera-settings-placeholder">
                    <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Printer Section --}}
        <div class="mb-4 border-t border-gray-200 pt-4">
            <label class="mb-2 block text-sm font-medium text-gray-700">
                Printer
            </label>
            <div class="mb-3 flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2">
                <span class="text-sm text-gray-600">Status:</span>
                <span id="camera-settings-printer-status" class="text-sm font-medium text-red-600">
                    Tidak Terhubung
                </span>
            </div>
            <button
                type="button"
                id="camera-settings-connect-printer"
                class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Hubungkan Printer
            </button>
        </div>

        {{-- Footer Actions --}}
        <div class="flex gap-2 border-t border-gray-200 pt-4">
            <button
                type="button"
                id="camera-settings-save"
                class="flex-1 rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                Simpan
            </button>
        </div>
    </div>
</div>