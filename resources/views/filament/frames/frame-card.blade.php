@php
    $record = $getRecord();
    $previewUrl = $record->preview_image
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($record->preview_image)
        : null;
@endphp

<div class="flex flex-col overflow-visible" dir="ltr">
    {{-- Preview image area with three-dots menu on top-right --}}
    <div class="relative w-full aspect-video overflow-visible rounded-t-xl bg-gray-100 dark:bg-gray-800">
        @if ($previewUrl)
            <img
                src="{{ $previewUrl }}"
                alt="{{ $record->name }}"
                class="h-full w-full rounded-t-xl object-cover"
            />
        @else
            <div class="flex h-full w-full items-center justify-center rounded-t-xl text-gray-400 dark:text-gray-500">
                <x-filament::icon icon="heroicon-o-photo" class="h-16 w-16" />
            </div>
        @endif

        {{-- Three-dots menu: top-right --}}
        <div
            class="absolute z-20"
            dir="ltr"
            style="top: 0.5rem; right: 0.5rem; left: auto; bottom: auto;"
            x-data="{ open: false }"
            @click.away="open = false"
        >
            <button
                type="button"
                class="flex h-9 w-9 items-center justify-center rounded-lg bg-white shadow ring-1 ring-gray-200 hover:bg-gray-50 dark:ring-gray-600 dark:hover:bg-gray-100"
                aria-label="Opsi"
                x-on:click.stop="open = ! open"
            >
                <svg class="h-5 w-5 text-gray-600 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 3a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM10 8.5a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM10 14a1.5 1.5 0 110 3 1.5 1.5 0 010-3z" />
                </svg>
            </button>
            <div
                x-show="open"
                x-transition
                x-cloak
                class="absolute right-0 top-full z-50 mt-1 w-48 origin-top-right rounded-lg bg-white py-1 shadow-lg ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
            >
                <a
                    href="{{ route('filament.admin.resources.frames.edit', $record) }}"
                    class="flex w-full items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5"
                >
                    <x-filament::icon icon="heroicon-o-pencil-square" class="h-5 w-5 text-gray-400" />
                    Ubah
                </a>
                <a
                    href="{{ route('filament.admin.resources.frames.layout', $record) }}"
                    class="flex w-full items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5"
                >
                    <x-filament::icon icon="heroicon-o-rectangle-stack" class="h-5 w-5 text-gray-400" />
                    Atur Layout
                </a>
                <form action="{{ route('filament.admin.frames.delete', $record) }}" method="POST" class="[&_button]:w-full [&_button]:text-left">
                    @csrf
                    @method('DELETE')
                    <button
                        type="submit"
                        class="flex w-full items-center gap-2 px-3 py-2 text-sm text-danger-600 hover:bg-danger-50 dark:text-danger-400 dark:hover:bg-danger-400/10"
                    >
                        <x-filament::icon icon="heroicon-o-trash" class="h-5 w-5" />
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Metadata --}}
    <div class="flex flex-col gap-1 p-4">
        <span class="text-base font-bold text-gray-950 dark:text-white">
            {{ $record->name }}
        </span>
        <span class="inline-flex w-fit items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $record->is_active ? 'bg-success-100 text-success-700 dark:bg-success-400/10 dark:text-success-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
            {{ $record->is_active ? 'Public' : 'Private' }}
        </span>
    </div>
</div>
