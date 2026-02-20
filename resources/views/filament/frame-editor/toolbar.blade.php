{{-- HEADER --}}
<div class="flex items-center justify-between gap-3">
    <h3 class="text-sm font-semibold text-gray-950 dark:text-white">
        Frame Layout (8×11 cm ≈ {{ $canvasWidth }}×{{ $canvasHeight }} px)
    </h3>

    @if (!empty($slots))
        <x-filament::button size="sm" color="primary" wire:click="saveSlots">
            Save Layout
        </x-filament::button>
    @endif
</div>

{{-- EDITOR BAR --}}
<div class="flex h-12 items-center overflow-x-auto rounded-lg border border-gray-200 bg-gray-50 px-2 dark:border-gray-700 dark:bg-gray-800">
    <div class="flex min-w-max items-center gap-3">
        @if ($selectedSlotId)
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                Slot {{ $selectedSlotId }}
            </span>

            @foreach (['X' => 'editorX', 'Y' => 'editorY', 'W' => 'editorWidth', 'H' => 'editorHeight'] as $label => $model)
                <div class="flex items-center gap-1">
                    <label class="text-xs text-gray-500">{{ $label }}</label>
                    <input
                        type="number"
                        wire:model.live="{{ $model }}"
                        class="w-20 rounded border-gray-300 py-1 text-xs dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                </div>
            @endforeach

            <button
                wire:click="deselectSlot"
                class="rounded p-1 text-gray-400 hover:bg-gray-200 hover:text-gray-600 dark:hover:bg-gray-600 dark:hover:text-gray-300">
                ✕
            </button>
        @else
            <span class="text-sm text-gray-500">
                Klik / drag slot di canvas untuk mengatur posisi & ukuran
            </span>
        @endif
    </div>
</div>
