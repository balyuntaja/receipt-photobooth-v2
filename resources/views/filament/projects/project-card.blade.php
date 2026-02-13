@php
    $record = $getRecord();
    $coverUrl = $record->cover_image
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($record->cover_image)
        : null;
@endphp

<div class="flex flex-col overflow-visible" dir="ltr">
    {{-- Cover image area with three-dots menu on top-right --}}
    <div class="relative w-full aspect-video overflow-visible bg-gray-100 dark:bg-gray-800 rounded-t-xl">
        @if ($coverUrl)
            <img
                src="{{ $coverUrl }}"
                alt="{{ $record->name }}"
                class="w-full h-full object-cover rounded-t-xl"
            />
        @else
            <div class="w-full h-full flex items-center justify-center rounded-t-xl text-gray-400 dark:text-gray-500">
                <x-filament::icon icon="heroicon-o-photo" class="w-16 h-16" />
            </div>
        @endif

        {{-- Three-dots menu: force top-right of cover (dir=ltr + inline position so layout/RTL cannot flip) --}}
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
                    href="{{ route('booth.start', $record) }}"
                    target="_blank"
                    rel="noopener"
                    class="flex w-full items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5"
                >
                    <x-filament::icon icon="heroicon-o-arrow-top-right-on-square" class="h-5 w-5 text-gray-400" />
                    Buka
                </a>
                <a
                    href="{{ route('filament.admin.resources.projects.settings', $record) }}"
                    class="flex w-full items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5"
                >
                    <x-filament::icon icon="heroicon-o-pencil-square" class="h-5 w-5 text-gray-400" />
                    Ubah
                </a>
                <form action="{{ route('filament.admin.projects.delete', $record) }}" method="POST" class="[&_button]:w-full [&_button]:text-left">
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
    <div class="p-4 flex flex-col gap-1">
        <span class="text-base font-bold text-gray-950 dark:text-white">
            {{ $record->name }}
        </span>
        <span class="text-sm text-gray-500 dark:text-gray-400">
            Terakhir Diperbarui: {{ $record->updated_at?->format('n/j/Y g:i:s A') }}
        </span>
        <span class="text-sm text-gray-500 dark:text-gray-400">
            Owned by Me
        </span>
    </div>
</div>
