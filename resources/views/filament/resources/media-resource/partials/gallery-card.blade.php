@php
    $record = $getRecord();
    $imageUrl = $record->file_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($record->file_path)
        : null;
    $sessionId = $record->session?->id ?? '-';
    $dateFormatted = $record->created_at?->format('d M Y H:i') ?? '-';
@endphp

<div class="group relative aspect-square overflow-hidden rounded-xl bg-gray-100 dark:bg-gray-800">
    @if ($imageUrl)
        <img
            src="{{ $imageUrl }}"
            alt="Gallery"
            class="h-full w-full object-cover transition"
        />
        {{-- Hover overlay: Session ID & Tanggal --}}
        <div class="absolute inset-0 flex flex-col items-center justify-center gap-1 bg-black/60 opacity-0 transition-opacity duration-200 group-hover:opacity-100">
            <span class="text-center text-sm font-medium text-white drop-shadow">{{ $sessionId }}</span>
            <span class="text-center text-xs text-white/90 drop-shadow">{{ $dateFormatted }}</span>
        </div>
    @else
        <div class="flex h-full w-full items-center justify-center text-gray-400 dark:text-gray-500">
            <x-filament::icon icon="heroicon-o-photo" class="h-16 w-16" />
        </div>
    @endif
</div>
