<div class="h-full w-full flex flex-col p-4 overflow-auto">
    <div class="mb-4">
        <button type="button" id="btn-preview-back" class="px-4 py-2 text-gray-600 hover:text-gray-900 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back
        </button>
        <h1 class="text-2xl font-bold mt-2 text-gray-900">Pilih Satu Foto Terbaik</h1>
    </div>
    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h2 class="text-lg font-semibold mb-4 text-gray-900">Foto Anda</h2>
            <div id="preview-photo-grid" class="grid grid-cols-2 gap-4">
                {{-- Photos injected by JS --}}
            </div>
        </div>
        <div>
            <h2 class="text-lg font-semibold mb-4 text-gray-900">Preview</h2>
            <div id="preview-merged" class="aspect-3/5 max-h-[400px] bg-gray-100 rounded-2xl flex items-center justify-center overflow-hidden">
                <p class="text-gray-500">Pilih foto untuk melihat preview</p>
            </div>
            <div class="mt-4 flex flex-col gap-3">
                <label class="flex items-center gap-2 text-gray-700">
                    <input type="checkbox" id="preview-mirror" class="rounded">
                    <span>Mirror image</span>
                </label>
                <button type="button" id="btn-preview-print" disabled class="w-full px-6 py-3 rounded-full bg-gray-200 text-gray-500 font-semibold cursor-not-allowed transition-colors">
                    Lanjut ke Print
                </button>
            </div>
        </div>
    </div>
</div>
