{{--
  Frame selection screen.
  Variables: $frames (from parent kiosk view).
  Layout: fixed header + scrollable frame grid.
--}}
<div class="kiosk-screen kiosk-screen--with-header flex h-full w-full flex-col">
    @include('booth.components.screen-header', [
        'backId' => 'btn-frame-back',
        'title' => 'Pilih Frame',
        'subtitle' => 'Pilih desain receipt favoritmu',
        'primaryId' => 'btn-frame-next',
        'primaryLabel' => 'Lanjut',
        'primaryDisabled' => true,
    ])

    <div class="kiosk-screen-body flex-1 overflow-y-auto overflow-x-hidden p-4 sm:p-6">
        <div id="frame-grid" class="kiosk-frame-grid mx-auto grid w-full max-w-4xl grid-cols-3 gap-4 sm:gap-5 lg:gap-6">
            @forelse ($frames as $frame)
                <button
                    type="button"
                    class="frame-card kiosk-frame-card flex flex-col items-stretch rounded-2xl border border-gray-200/80 p-3 text-left transition-all hover:border-gray-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2 focus:ring-offset-[#f5f5f7]"
                    data-frame-id="{{ $frame->id }}"
                >
                    {{-- 8cm x 11cm aspect ratio (8:11) - preview tidak terpotong --}}
                    <span class="kiosk-frame-card__image-wrapper mb-2 flex aspect-8/11 w-full items-center justify-center overflow-hidden rounded-xl">
                        <img
                            src="{{ (strpos($frame->preview_image ?? '', 'http') === 0) ? $frame->preview_image : asset('storage/' . $frame->preview_image) }}"
                            alt="{{ $frame->name }}"
                            class="h-full w-full object-contain"
                            loading="lazy"
                        />
                    </span>
                    <span class="kiosk-frame-card__label line-clamp-2 text-center text-sm font-medium text-gray-700">{{ $frame->name }}</span>
                </button>
            @empty
                <div class="col-span-full flex flex-col items-center justify-center py-16 text-center">
                    <p class="text-gray-500 text-sm">Tidak ada frame tersedia.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
