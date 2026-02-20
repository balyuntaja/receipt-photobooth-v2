@php
    $imageUrl = $record->file_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($record->file_path)
        : null;
    $sessionId = $record->session?->id ?? '-';
    $dateFormatted = $record->created_at?->format('d M Y H:i') ?? '-';
@endphp

<div class="flex flex-col gap-4">
    @if ($imageUrl)
        <img
            src="{{ $imageUrl }}"
            alt="Photo"
            class="mx-auto max-h-[20vh] max-w-full object-contain rounded-lg"
            />
        <div class="text-sm text-gray-600 dark:text-gray-400">
            <p><strong>Session ID:</strong> {{ $sessionId }}</p>
            <p><strong>Tanggal:</strong> {{ $dateFormatted }}</p>
        </div>
    @else
        <p class="text-gray-500">No image</p>
    @endif
</div>
