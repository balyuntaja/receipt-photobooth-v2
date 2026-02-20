<div class="w-80 shrink-0 space-y-4">
    {{-- SETTINGS --}}
    <x-filament::section heading="Settings" class="shadow-sm">
        <div class="space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Nama Bingkai
                </label>
                <input
                    type="text"
                    wire:model.defer="record.name"
                    class="mt-1 block w-full rounded-lg border-gray-300 py-2 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Frame Overlay (PNG transparan)
                </label>
                <input
                    type="file"
                    wire:model="frameFileUpload"
                    accept="image/png"
                    class="mt-1 block w-full text-sm text-gray-500
                        file:mr-4 file:rounded-lg file:border-0
                        file:bg-primary-50 file:px-4 file:py-2
                        file:text-sm file:font-semibold
                        file:text-primary-700 hover:file:bg-primary-100
                        dark:text-gray-400 dark:file:bg-gray-700 dark:file:text-gray-200" />
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Wajib PNG dengan area foto transparan.
                </p>
            </div>
        </div>
    </x-filament::section>

    {{-- PHOTO SLOTS --}}
    <x-filament::section heading="Photo Slots" class="shadow-sm">
        <div class="space-y-2">
            <x-filament::button
                color="primary"
                icon="heroicon-o-plus"
                wire:click="addSlot"
                class="w-full">
                Add Slot
            </x-filament::button>

            @if (empty($slots))
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Belum ada slot foto. Tekan <strong>Add Slot</strong> untuk menambah.
                </p>
            @else
                <ul class="max-h-80 space-y-1 overflow-auto pr-1">
                    @foreach ($slots as $index => $slot)
                        @php
                            $isActive = $selectedSlotId === $slot['id'];
                        @endphp
                        <li
                            class="flex items-center justify-between rounded-lg px-2 py-1 text-sm
                                {{ $isActive
                                    ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-200'
                                    : 'bg-gray-50 text-gray-700 dark:bg-gray-800 dark:text-gray-200' }}">
                            <button
                                type="button"
                                wire:click="selectSlot({{ $slot['id'] }})"
                                class="flex flex-1 items-center justify-between gap-2 text-left">
                                <span>Slot {{ $index + 1 }}</span>
                                <span class="text-xs text-gray-400">
                                    {{ $slot['width'] }}×{{ $slot['height'] }}
                                </span>
                            </button>

                            <button
                                type="button"
                                wire:click="deleteSlot({{ $slot['id'] }})"
                                class="ml-1 rounded p-1 text-gray-400
                                    hover:bg-red-50 hover:text-red-600
                                    dark:hover:bg-red-900/20"
                                title="Hapus slot">
                                <x-filament::icon icon="heroicon-o-trash" class="h-4 w-4" />
                            </button>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </x-filament::section>
</div>
