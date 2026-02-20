<x-filament-panels::page>
    @vite(['resources/js/frame-editor.js'])

    <div class="flex gap-6">
        @include('filament.frame-editor.sidebar')
        @include('filament.frame-editor.canvas')
    </div>
</x-filament-panels::page>
