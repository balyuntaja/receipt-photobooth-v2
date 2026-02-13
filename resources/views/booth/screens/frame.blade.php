<div class="h-full w-full flex flex-col items-center justify-center p-6">
    <div class="mb-6 flex items-center justify-between w-full max-w-4xl">
        <button type="button" id="btn-frame-back" class="px-4 py-2 text-gray-600 hover:text-gray-900 transition-colors flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back
        </button>
        <h1 class="text-2xl font-bold text-gray-900">Pilih Frame</h1>
        <button type="button" id="btn-frame-next" disabled class="px-6 py-2 rounded-full bg-gray-200 text-gray-500 cursor-not-allowed transition-colors">
            Continue
        </button>
    </div>
    <p class="text-gray-600 mb-6">Pilih desain receipt favoritmu</p>
    <div id="frame-grid" class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full max-w-4xl">
        @forelse ($frames as $frame)
            <button type="button" class="frame-card flex flex-col items-center p-4 rounded-2xl bg-gray-100 border-2 border-transparent hover:border-gray-400 transition-all cursor-pointer text-left"
                data-frame-id="{{ $frame->id }}">
                <img src="{{ asset('storage/' . $frame->preview_image) }}" alt="{{ $frame->name }}"
                    class="w-full aspect-3/5 object-cover rounded-xl mb-3">
                <span class="font-medium text-gray-900">{{ $frame->name }}</span>
            </button>
        @empty
            <p class="col-span-full text-gray-500 text-center py-8">Tidak ada frame tersedia.</p>
        @endforelse
    </div>
</div>
