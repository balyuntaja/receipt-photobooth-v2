<x-filament-panels::page>
    {{-- Layout: sidebar kiri + canvas kanan (selalu sejajar) --}}
    <div class="flex gap-6">

        {{-- ===================== --}}
        {{-- SIDEBAR (lebih kecil) --}}
        {{-- ===================== --}}
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

        {{-- ===================== --}}
        {{-- CANVAS AREA (lebih besar) --}}
        {{-- ===================== --}}
        <div class="min-w-0 flex-1">
            <x-filament::section class="shadow-sm">
                {{-- Wrapper section (biar tinggi diatur langsung oleh card canvas di bawah) --}}
                <div class="flex flex-col gap-3">

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
                    <div
                        class="flex h-12 items-center overflow-x-auto rounded-lg
                            border border-gray-200 bg-gray-50 px-2
                            dark:border-gray-700 dark:bg-gray-800">
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
                                    class="w-20 rounded border-gray-300 py-1 text-xs
                                                dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                            </div>
                            @endforeach

                            <button
                                wire:click="deselectSlot"
                                class="rounded p-1 text-gray-400 hover:bg-gray-200 hover:text-gray-600
                                        dark:hover:bg-gray-600 dark:hover:text-gray-300">
                                ✕
                            </button>
                            @else
                            <span class="text-sm text-gray-500">
                                Klik / drag slot di canvas untuk mengatur posisi & ukuran
                            </span>
                            @endif
                        </div>
                    </div>

                    {{-- CANVAS --}}
                    @php
                    $frameFile = $record->frame_file
                    ? asset('storage/' . $record->frame_file)
                    : null;
                    @endphp

                {{-- Workspace abu-abu + kertas putih 8×11 di dalamnya (zoomable) --}}
                <div
                    x-data="{
                        zoom: 0.3,
                        minZoom: 0.25,
                        maxZoom: 2,
                        step: 0.1,
                        baseWidth: {{ $canvasWidth }},
                        baseHeight: {{ $canvasHeight }},
                        zoomIn() { this.zoom = Math.min(this.maxZoom, Math.round((this.zoom + this.step) * 100) / 100) },
                        zoomOut() { this.zoom = Math.max(this.minZoom, Math.round((this.zoom - this.step) * 100) / 100) },
                        reset() { this.zoom = 1 },
                        fit() {
                            // Fit paper into workspace (approx, based on current element sizes)
                            this.$nextTick(() => {
                                const ws = this.$refs.workspace;
                                const paper = this.$refs.paper;
                                if (!ws || !paper) return;
                                const wsRect = ws.getBoundingClientRect();
                                const paperRect = paper.getBoundingClientRect();
                                // paperRect already scaled; normalize to base size
                                const baseW = paperRect.width / this.zoom;
                                const baseH = paperRect.height / this.zoom;
                                const availableW = wsRect.width - 32; // padding
                                const availableH = wsRect.height - 120; // toolbar + padding
                                const next = Math.min(availableW / baseW, availableH / baseH);
                                this.zoom = Math.max(this.minZoom, Math.min(this.maxZoom, Math.round(next * 100) / 100));
                            });
                        },
                    }"
                    class="flex flex-col overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700"
                    style="height: 600px;"
                >
                    {{-- Toolbar (seperti screenshot) --}}
                    <div class="flex items-center justify-center gap-3 border-b border-gray-200 bg-white py-4 dark:border-gray-700 dark:bg-gray-900">
                        <button type="button" @click="zoomOut()" class="h-10 w-16 rounded-lg border-2 border-primary-500 bg-white text-lg font-bold text-primary-600 hover:bg-primary-50 dark:bg-gray-900 dark:text-primary-400">
                            -
                        </button>
                        <span class="min-w-[64px] text-center text-sm font-semibold text-gray-700 dark:text-gray-200" x-text="Math.round(zoom * 100) + '%'"></span>
                        <button type="button" @click="zoomIn()" class="h-10 w-16 rounded-lg border-2 border-primary-500 bg-white text-lg font-bold text-primary-600 hover:bg-primary-50 dark:bg-gray-900 dark:text-primary-400">
                            +
                        </button>
                        <button type="button" @click="fit()" class="flex h-10 w-12 items-center justify-center rounded-lg border-2 border-primary-500 bg-white text-primary-600 hover:bg-primary-50 dark:bg-gray-900 dark:text-primary-400" title="Fit">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 3H5a2 2 0 00-2 2v3m13-5h3a2 2 0 012 2v3M3 16v3a2 2 0 002 2h3m13-5v3a2 2 0 01-2 2h-3"/>
                            </svg>
                        </button>
                        <button type="button" @click="reset()" class="flex h-10 w-12 items-center justify-center rounded-lg border-2 border-primary-500 bg-white text-primary-600 hover:bg-primary-50 dark:bg-gray-900 dark:text-primary-400" title="Reset">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6M20 20v-6h-6"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10a7 7 0 0112-2M19 14a7 7 0 01-12 2"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Workspace (abu) --}}
                    <div x-ref="workspace" class="flex-1 overflow-auto bg-gray-200 p-4 dark:bg-gray-800">
                        {{-- White paper canvas (8×11) --}}
                        <div class="flex justify-center pb-6">
                            {{-- SCALE WRAPPER (zoom ubah ukuran kertas secara real, tanpa transform/scale) --}}
                            @php
                                $paperStaticStyle = 'width:' . (int) $canvasWidth . 'px;height:' . (int) $canvasHeight . 'px;';
                            @endphp
                            <div
                                :style="{ width: baseWidth * zoom + 'px', height: baseHeight * zoom + 'px' }"
                                style="<?php echo e($paperStaticStyle); ?>"
                            >
                                {{-- PAPER (kertas putih 8×11, selalu isi wrapper) --}}
                                <div
                                    x-ref="paper"
                                    class="relative h-full w-full rounded-lg bg-white shadow-lg ring-1 ring-gray-200"
                                >
                                    {{-- CANVAS (tetap sama, memenuhi kertas) --}}
                                    <div
                                        id="frame-layout-canvas"
                                        class="relative h-full w-full"
                                        x-data="{
                                            draggingId: null,
                                            canvasEl: null,
                                            init() { this.canvasEl = this.$el },
                                            startDrag(e, id) {
                                                if (e.button !== 0) return
                                                this.draggingId = id
                                                const move = ev => this.onMove(ev)
                                                const up = () => {
                                                    this.draggingId = null
                                                    document.removeEventListener('mousemove', move)
                                                    document.removeEventListener('mouseup', up)
                                                }
                                                document.addEventListener('mousemove', move)
                                                document.addEventListener('mouseup', up)
                                            },
                                            onMove(e) {
                                                if (!this.canvasEl || this.draggingId === null) return
                                                const r = this.canvasEl.getBoundingClientRect()
                                                let x = (e.clientX - r.left) / r.width * {{ $canvasWidth }}
                                                let y = (e.clientY - r.top) / r.height * {{ $canvasHeight }}
                                                x = Math.max(0, Math.min({{ $canvasWidth }}, x))
                                                y = Math.max(0, Math.min({{ $canvasHeight }}, y))
                                                $wire.call('updateSlotPosition', this.draggingId, Math.round(x), Math.round(y))
                                            }
                                        }"
                                    >
                                        {{-- FRAME --}}
                                        @if ($frameFile)
                                        <img
                                            src="{{ $frameFile }}"
                                            class="absolute inset-0 h-full w-full object-contain" />
                                        @endif

                                        {{-- SLOTS --}}
                                        @foreach ($slots as $index => $slot)
                                        @php
                                        $left = $slot['x'] / $canvasWidth * 100;
                                        $top = $slot['y'] / $canvasHeight * 100;
                                        $w = $slot['width'] / $canvasWidth * 100;
                                        $h = $slot['height'] / $canvasHeight * 100;
                                        $slotStyle = 'left:' . $left . '%;top:' . $top . '%;width:' . $w . '%;height:' . $h . '%;transform:translate(-50%, -50%);';
                                        @endphp
                                        <div
                                            class="absolute cursor-move rounded border border-gray-700 bg-gray-300 text-gray-900 shadow-sm
                                                    {{ $selectedSlotId === $slot['id'] ? 'ring-2 ring-primary-500' : '' }}"
                                            style="<?php echo e($slotStyle); ?>"
                                            @mousedown.stop="startDrag($event, {{ $slot['id'] }})"
                                            wire:click.stop="selectSlot({{ $slot['id'] }})">
                                            <div class="flex h-full w-full flex-col items-center justify-center text-center">
                                                <div class="text-sm font-semibold">Slot {{ $index + 1 }}</div>
                                                <div class="text-[11px] text-gray-700">(Photo #{{ $index + 1 }})</div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>