{{--
  Reusable kiosk screen header: back (left), title + optional subtitle (center), primary action (right).
  Variables:
    $backId (optional) – id for back button, if not provided back button won't show
    $backVisible (optional) – jika true, tombol back ditampilkan (default false)
    $title (optional) – main heading
    $subtitle (optional) – text below title
    $centerId (optional) – id for center element (e.g. capture-status) so JS can update it
    $centerLabel (optional) – if set, center shows this instead of title (for dynamic status)
    $primaryId, $primaryLabel, $primaryDisabled (optional)
--}}
<header class="kiosk-screen-header flex shrink-0 items-center justify-between gap-4 px-4 py-3">
    <div class="flex min-w-0 flex-1 items-center gap-2">
        @if(!empty($backId))
        <button
            type="button"
            id="{{ $backId }}"
            class="kiosk-btn-back inline-flex items-center gap-2 rounded-xl px-3 py-2 text-gray-600 transition-colors hover:bg-white/80 hover:text-gray-900"
            aria-label="Kembali"
            @if(empty($backVisible)) hidden @endif
        >
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            <span class="hidden sm:inline">Kembali</span>
        </button>
        @else
        <div class="w-20"></div>
        @endif
    </div>
    <div class="flex min-w-0 flex-1 flex-col items-center justify-center text-center">
        @if(!empty($centerId))
            <span id="{{ $centerId }}" class="kiosk-screen-title text-lg font-semibold text-gray-900">{{ $centerLabel ?? '' }}</span>
        @else
            <h1 class="kiosk-screen-title text-lg font-semibold text-gray-800 sm:text-xl">{{ $title ?? '' }}</h1>
            @if(!empty($subtitle))
                <p class="kiosk-screen-subtitle mt-0.5 text-sm text-gray-500">{{ $subtitle }}</p>
            @endif
        @endif
    </div>
    <div class="flex min-w-0 flex-1 justify-end">
        @if(!empty($primaryId))
            <button
                type="button"
                id="{{ $primaryId }}"
                @if(!empty($primaryDisabled)) disabled @endif
                class="kiosk-btn-primary rounded-xl px-5 py-2.5 text-sm font-medium transition-all disabled:cursor-not-allowed disabled:opacity-50 {{ !empty($primaryDisabled) ? 'bg-gray-200 text-gray-500' : 'bg-gray-800 text-white hover:bg-gray-700' }}"
            >
                {{ $primaryLabel ?? 'Lanjut' }}
            </button>
        @else
            <div class="w-20"></div>
        @endif
    </div>
</header>
