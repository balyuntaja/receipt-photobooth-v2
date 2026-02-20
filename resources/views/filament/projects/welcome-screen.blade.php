<x-filament-panels::page>
    <div class="flex gap-6">
        {{-- ===========================
             SIDEBAR: Component List
             =========================== --}}
        <div class="w-80 shrink-0 space-y-4">
            {{-- Back Button --}}
            <x-filament::button
                color="gray"
                icon="heroicon-o-arrow-left"
                wire:click="back"
                class="w-full"
            >
                Back to Settings
            </x-filament::button>

            {{-- Background Color (fallback when no background image) --}}
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <h3 class="mb-3 text-sm font-semibold text-gray-950 dark:text-white">
                    Background Color
                </h3>
                <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Dipakai jika tidak ada background image (hex / rgb).</p>
                <div class="flex items-end gap-2">
                    <input
                        type="color"
                        wire:model="backgroundColor"
                        class="h-10 w-14 cursor-pointer rounded border border-gray-300 dark:border-gray-600"
                        title="Pilih warna"
                    />
                    <input
                        type="text"
                        wire:model="backgroundColor"
                        class="min-w-0 flex-1 rounded-lg border-gray-300 py-2 text-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        placeholder="#667eea atau rgb(102,126,234)"
                    />
                    <x-filament::button size="sm" wire:click="saveBackgroundColor">
                        Simpan
                    </x-filament::button>
                </div>
            </div>

            {{-- Component List --}}
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <h3 class="mb-3 text-sm font-semibold text-gray-950 dark:text-white">
                    Components
                </h3>

                @if (count($components) === 0)
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        No components yet. Add one below.
                    </p>
                @else
                    <ul
                        id="welcome-sidebar-sortable"
                        class="space-y-2"
                        x-data="{ initSidebarSortable(retries = 0) {
                            const self = this;
                            if (typeof Sortable === 'undefined') {
                                if (retries < 30) setTimeout(() => self.initSidebarSortable(retries + 1), 100);
                                return;
                            }
                            const list = this.$el;
                            if (list._sortableInstance) { list._sortableInstance.destroy(); list._sortableInstance = null; }
                            list._sortableInstance = Sortable.create(list, {
                                animation: 150,
                                handle: '.welcome-sidebar-drag-handle',
                                ghostClass: 'opacity-50',
                                chosenClass: 'welcome-sortable-chosen',
                                dragClass: 'welcome-sortable-dragging',
                                onEnd: () => {
                                    const ids = Array.from(list.querySelectorAll('[data-component-id]')).map(el => parseInt(el.dataset.componentId, 10));
                                    if (ids.length) $wire.call('reorderComponents', JSON.stringify(ids));
                                }
                            });
                        } }"
                        x-init="$nextTick(() => initSidebarSortable()); $wire.on('components-updated', () => $nextTick(() => initSidebarSortable()))"
                    >
                        @foreach ($components as $index => $comp)
                            <li
                                class="flex items-center justify-between gap-2 rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-800"
                                data-component-id="{{ $comp['id'] }}"
                            >
                                <span class="welcome-sidebar-drag-handle cursor-grab touch-none select-none rounded p-1 text-gray-400 hover:bg-gray-200 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200" title="Drag to reorder">
                                    <x-filament::icon icon="heroicon-o-bars-3" class="h-4 w-4" />
                                </span>
                                <div class="flex min-w-0 flex-1 items-center gap-2">
                                    @if ($comp['type'] === 'text')
                                        <x-filament::icon icon="heroicon-o-document-text" class="h-4 w-4 text-gray-400" />
                                        <span class="text-sm text-gray-700 dark:text-gray-300">
                                            {{ Str::limit($comp['content']['text'] ?? 'Text', 20) }}
                                        </span>
                                    @elseif ($comp['type'] === 'image')
                                        <x-filament::icon icon="heroicon-o-photo" class="h-4 w-4 text-gray-400" />
                                        <span class="text-sm text-gray-700 dark:text-gray-300">Image</span>
                                    @elseif ($comp['type'] === 'background')
                                        <x-filament::icon icon="heroicon-o-square-2-stack" class="h-4 w-4 text-gray-400" />
                                        <span class="text-sm text-gray-700 dark:text-gray-300">Background</span>
                                    @elseif ($comp['type'] === 'button')
                                        <x-filament::icon icon="heroicon-o-play" class="h-4 w-4 text-gray-400" />
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $comp['content']['text'] ?? 'Button' }}</span>
                                    @endif
                                </div>
                                <div class="flex shrink-0 items-center gap-1">
                                    {{-- Move Up --}}
                                    @if ($index > 0)
                                        <button
                                            type="button"
                                            wire:click="moveUp({{ $comp['id'] }})"
                                            class="rounded p-1 text-gray-400 hover:bg-gray-200 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200"
                                            title="Move Up"
                                        >
                                            <x-filament::icon icon="heroicon-o-chevron-up" class="h-4 w-4" />
                                        </button>
                                    @endif
                                    {{-- Move Down --}}
                                    @if ($index < count($components) - 1)
                                        <button
                                            type="button"
                                            wire:click="moveDown({{ $comp['id'] }})"
                                            class="rounded p-1 text-gray-400 hover:bg-gray-200 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200"
                                            title="Move Down"
                                        >
                                            <x-filament::icon icon="heroicon-o-chevron-down" class="h-4 w-4" />
                                        </button>
                                    @endif
                                    {{-- Edit --}}
                                    <button
                                        type="button"
                                        wire:click="editComponent({{ $comp['id'] }})"
                                        class="rounded p-1 text-gray-400 hover:bg-gray-200 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200"
                                        title="Edit"
                                    >
                                        <x-filament::icon icon="heroicon-o-pencil" class="h-4 w-4" />
                                    </button>
                                    {{-- Delete --}}
                                    <button
                                        type="button"
                                        wire:click="deleteComponent({{ $comp['id'] }})"
                                        wire:confirm="Are you sure you want to delete this component?"
                                        class="rounded p-1 text-red-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20"
                                        title="Delete"
                                    >
                                        <x-filament::icon icon="heroicon-o-trash" class="h-4 w-4" />
                                    </button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Add Component --}}
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <h3 class="mb-3 text-sm font-semibold text-gray-950 dark:text-white">
                    Add Component
                </h3>
                <div class="flex flex-col gap-2">
                    <x-filament::button
                        color="gray"
                        icon="heroicon-o-document-text"
                        wire:click="openAddModal('text')"
                        class="w-full justify-start"
                    >
                        Add Text
                    </x-filament::button>

                    <x-filament::button
                        color="gray"
                        icon="heroicon-o-photo"
                        wire:click="openAddModal('image')"
                        class="w-full justify-start"
                    >
                        Add Image
                    </x-filament::button>

                    <x-filament::button
                        color="gray"
                        icon="heroicon-o-square-2-stack"
                        wire:click="openAddModal('background')"
                        class="w-full justify-start"
                    >
                        Add Background
                    </x-filament::button>

                    <x-filament::button
                        color="gray"
                        icon="heroicon-o-play"
                        wire:click="openAddModal('button')"
                        class="w-full justify-start"
                    >
                        Add Button
                    </x-filament::button>
                </div>
            </div>
        </div>

        {{-- ===========================
             CANVAS: Preview Area
             =========================== --}}
        <div class="min-w-0 flex-1">
            <div class="min-w-0 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <h3 class="text-sm font-semibold text-gray-950 dark:text-white">
                        Preview (16:9 - 1920×1080)
                    </h3>
                    @if ($selectedComponentId)
                        @php $selTitle = collect($components)->firstWhere('id', $selectedComponentId); @endphp
                        @if ($selTitle && $selTitle['type'] !== 'background')
                            <x-filament::button size="sm" color="primary" wire:click="applyComponentLayout">
                                Save
                            </x-filament::button>
                        @endif
                    @endif
                </div>

                {{-- Editor Panel: hanya bar ini yang scroll (horizontal); lebar dibatasi, canvas tidak ikut --}}
                <div class="editor-panel-scroll mb-4 flex h-12 min-w-0 shrink-0 items-center overflow-x-auto overflow-y-hidden rounded-lg border border-gray-200 bg-gray-50 px-2 py-0 dark:border-gray-700 dark:bg-gray-800" style="contain: layout;">
                    <div class="flex min-w-max flex-nowrap items-center gap-3 py-1">
                        @if ($selectedComponentId)
                            @php
                                $sel = collect($components)->firstWhere('id', $selectedComponentId);
                            @endphp
                            @if ($sel && $sel['type'] !== 'background')
                                <span class="shrink-0 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ $sel['type'] === 'text' ? 'Text' : ($sel['type'] === 'image' ? 'Image' : 'Button') }}:
                                </span>
                                <div class="flex shrink-0 items-center gap-1">
                                    <label class="text-xs text-gray-500">X</label>
                                    <input type="number" wire:model.live="editorX" min="0" max="1920" class="w-16 rounded border-gray-300 py-1 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                </div>
                                <div class="flex shrink-0 items-center gap-1">
                                    <label class="text-xs text-gray-500">Y</label>
                                    <input type="number" wire:model.live="editorY" min="0" max="1080" class="w-16 rounded border-gray-300 py-1 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                </div>
                                <div class="flex shrink-0 items-center gap-1">
                                    <label class="text-xs text-gray-500">W</label>
                                    <input type="text" wire:model.live="editorWidth" placeholder="auto" class="w-16 rounded border-gray-300 py-1 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                </div>
                                <div class="flex shrink-0 items-center gap-1">
                                    <label class="text-xs text-gray-500">H</label>
                                    <input type="text" wire:model.live="editorHeight" placeholder="auto" class="w-16 rounded border-gray-300 py-1 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                </div>
                                @if (in_array($sel['type'], ['text', 'button']))
                                    <div class="flex shrink-0 items-center gap-1">
                                        <label class="text-xs text-gray-500">Font</label>
                                        <select wire:model.live="editorFontSize" class="rounded border-gray-300 py-1 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                            <option value="small">S</option>
                                            <option value="medium">M</option>
                                            <option value="large">L</option>
                                        </select>
                                    </div>
                                @endif
                                @if ($sel['type'] === 'text')
                                    <div class="flex shrink-0 items-center gap-1">
                                        <label class="text-xs text-gray-500">Color</label>
                                        <input type="color" wire:model.live="editorTextColor" class="h-8 w-10 cursor-pointer rounded border border-gray-300 dark:border-gray-600" title="Text color" />
                                        <input type="text" wire:model.live="editorTextColor" class="w-24 rounded border-gray-300 py-1 text-xs dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="#ffffff" />
                                    </div>
                                @endif
                                @if ($sel['type'] === 'button')
                                    <div class="flex shrink-0 items-center gap-1">
                                        <label class="text-xs text-gray-500">BG</label>
                                        <input type="color" wire:model.live="editorButtonBackgroundColor" class="h-8 w-10 cursor-pointer rounded border border-gray-300 dark:border-gray-600" title="Background" />
                                        <input type="text" wire:model.live="editorButtonBackgroundColor" class="w-20 rounded border-gray-300 py-1 text-xs dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="#fff" />
                                    </div>
                                    <div class="flex shrink-0 items-center gap-1">
                                        <label class="text-xs text-gray-500">Text</label>
                                        <input type="color" wire:model.live="editorButtonTextColor" class="h-8 w-10 cursor-pointer rounded border border-gray-300 dark:border-gray-600" title="Text color" />
                                        <input type="text" wire:model.live="editorButtonTextColor" class="w-20 rounded border-gray-300 py-1 text-xs dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="#111" />
                                    </div>
                                @endif
                                <div class="flex shrink-0 items-center gap-1">
                                    <label class="text-xs text-gray-500">Radius</label>
                                    <input type="number" wire:model.live="editorBorderRadius" min="0" max="100" class="w-14 rounded border-gray-300 py-1 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                </div>
                                <button type="button" wire:click="deselectComponent" class="shrink-0 rounded p-1 text-gray-400 hover:bg-gray-200 hover:text-gray-600 dark:hover:bg-gray-600 dark:hover:text-gray-300" title="Tutup panel komponen">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            @else
                                <span class="shrink-0 text-sm text-gray-500 dark:text-gray-400">Canvas: 1920×1080</span>
                                <span class="shrink-0 text-xs text-gray-400 dark:text-gray-500">Klik komponen di preview untuk mengedit.</span>
                            @endif
                        @else
                            <span class="shrink-0 text-sm font-medium text-gray-600 dark:text-gray-400">Canvas</span>
                            <span class="shrink-0 text-sm text-gray-500 dark:text-gray-400">1920 × 1080</span>
                            <span class="shrink-0 text-xs text-gray-400 dark:text-gray-500">Klik komponen di preview untuk mengedit posisi & ukuran.</span>
                        @endif
                    </div>
                </div>

                {{-- Canvas Preview (tetap seperti sebelumnya, tidak ikut scroll) --}}
                <div
                    data-canvas-container
                    class="relative mx-auto overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800"
                    style="aspect-ratio: 16/9; max-width: 100%;"
                >
                    {{-- Background Layer (always at bottom) --}}
                    @php
                        $backgroundComp = collect($components)->firstWhere('type', 'background');
                    @endphp
                    @if ($backgroundComp && isset($backgroundComp['content']['path']))
                        <img
                            src="{{ Storage::disk('public')->url($backgroundComp['content']['path']) }}"
                            alt="Background"
                            class="absolute inset-0 h-full w-full object-cover"
                        />
                    @elseif ($backgroundColor)
                        @php $canvasBgStyle = 'background-color:' . e($backgroundColor) . ';'; @endphp
                        <div class="absolute inset-0 h-full w-full" style="<?php echo e($canvasBgStyle); ?>"></div>
                    @endif

                    {{-- Content Layers (absolute positioning by x,y; reorder via sidebar list) --}}
                    <div id="welcome-preview-content" class="relative z-10 h-full w-full">
                        @php
                            $contentComponents = collect($components)->where('type', '!=', 'background');
                        @endphp
                        @foreach ($contentComponents as $comp)
                            @php
                                $rawX = $comp['content']['x'] ?? 960;
                                $rawY = $comp['content']['y'] ?? 540;
                                $cx = (int) $rawX;
                                $cy = (int) $rawY;
                                $leftPct = $cx <= 100 ? $cx : ($cx / 1920) * 100;
                                $topPct = $cy <= 100 ? $cy : ($cy / 1080) * 100;
                                $cw = $comp['content']['layoutWidth'] ?? $comp['content']['width'] ?? 'auto';
                                $ch = $comp['content']['layoutHeight'] ?? $comp['content']['height'] ?? 'auto';
                                $br = (int) ($comp['content']['borderRadius'] ?? 12);
                                $fs = $comp['content']['fontSize'] ?? 'medium';
                                $posStyle = 'left:' . $leftPct . '%;top:' . $topPct . '%;transform:translate(-50%,-50%);';
                                $posStyle .= 'width:' . (is_numeric($cw) ? $cw . 'px' : $cw) . ';';
                                $posStyle .= 'height:' . (is_numeric($ch) ? $ch . 'px' : $ch) . ';';
                                $posStyle .= 'border-radius:' . $br . 'px;';
                                $isSel = $selectedComponentId === $comp['id'];
                                $selBorderClass = ($isSel && $comp['type'] === 'button') ? 'border-transparent ring-0' : ($isSel ? 'border-primary-500 ring-2 ring-primary-400' : 'border-transparent hover:border-primary-400 hover:bg-primary-50/50 dark:hover:border-primary-500 dark:hover:bg-primary-500/10');
                            @endphp
                            <div
                                class="welcome-preview-item absolute cursor-grab border-2 border-dashed p-2 transition-colors select-none {{ $selBorderClass }}"
                                :class="{ 'cursor-grabbing': dragging }"
                                data-component-id="{{ $comp['id'] }}"
                                style="{{ $posStyle }}"
                                x-data="{
                                    didDrag: false,
                                    dragging: false,
                                    canvas: null,
                                    startClientX: 0,
                                    startClientY: 0,
                                    init() { this.canvas = this.$el.closest('[data-canvas-container]'); },
                                    startDrag(e) {
                                        if (e.button !== 0) return;
                                        this.didDrag = false;
                                        this.dragging = true;
                                        this.startClientX = e.clientX;
                                        this.startClientY = e.clientY;
                                        const move = (e2) => this.onMove(e2);
                                        const up = (e2) => { this.onUp(e2); this.dragging = false; document.removeEventListener('mousemove', move); document.removeEventListener('mouseup', up); };
                                        document.addEventListener('mousemove', move);
                                        document.addEventListener('mouseup', up);
                                    },
                                    onMove(e) {
                                        if (!this.canvas) return;
                                        if (Math.abs(e.clientX - this.startClientX) > 5 || Math.abs(e.clientY - this.startClientY) > 5) this.didDrag = true;
                                        const r = this.canvas.getBoundingClientRect();
                                        let x = (e.clientX - r.left) / r.width * 1920;
                                        let y = (e.clientY - r.top) / r.height * 1080;
                                        x = Math.max(0, Math.min(1920, x));
                                        y = Math.max(0, Math.min(1080, y));
                                        this.$el.style.left = (x/1920*100) + '%';
                                        this.$el.style.top = (y/1080*100) + '%';
                                    },
                                    onUp(e) {
                                        if (!this.canvas) return;
                                        const r = this.canvas.getBoundingClientRect();
                                        let x = Math.round((e.clientX - r.left) / r.width * 1920);
                                        let y = Math.round((e.clientY - r.top) / r.height * 1080);
                                        x = Math.max(0, Math.min(1920, x));
                                        y = Math.max(0, Math.min(1080, y));
                                        const id = parseInt(this.$el.dataset.componentId, 10);
                                        $wire.call('updateComponentPosition', id, x, y);
                                    },
                                    handleClick() { if (!this.didDrag) $wire.call('selectComponent', parseInt(this.$el.dataset.componentId, 10)); }
                                }"
                                @mousedown.stop="startDrag($event)"
                                @click.stop="handleClick()"
                            >
                                @if ($comp['type'] === 'text')
                                    @php
                                        $fontSize = match ($fs) {
                                            'small' => 'text-lg',
                                            'large' => 'text-4xl',
                                            default => 'text-2xl',
                                        };
                                        $alignment = match ($comp['content']['alignment'] ?? 'center') {
                                            'left' => 'text-left',
                                            'right' => 'text-right',
                                            default => 'text-center',
                                        };
                                        $previewTextColor = $comp['content']['textColor'] ?? '#ffffff';
                                        $previewTextStyle = 'color:' . e($previewTextColor) . ';';
                                    @endphp
                                    <p class="{{ $fontSize }} {{ $alignment }} w-full font-semibold pointer-events-none" style="<?php echo e($previewTextStyle); ?>">
                                        {{ $comp['content']['text'] ?? '' }}
                                    </p>
                                @elseif ($comp['type'] === 'image' && isset($comp['content']['path']))
                                    @php
                                        $imgWidthPx = ($comp['content']['width'] ?? 'auto') === 'custom'
                                            ? (int) ($comp['content']['customWidth'] ?? 200)
                                            : null;
                                        $imgPath = $comp['content']['path'];
                                        $imgUrl = (strpos($imgPath, 'http') === 0) 
                                            ? $imgPath 
                                            : Storage::disk('public')->url($imgPath);
                                    @endphp
                                    <img
                                        src="{{ $imgUrl }}"
                                        alt="Image"
                                        class="pointer-events-none max-h-full w-auto object-contain"
                                        @if ($imgWidthPx) width="{{ $imgWidthPx }}" @endif
                                    />
                                @elseif ($comp['type'] === 'button')
                                    @php
                                        $btnFs = match ($fs) {
                                            'small' => 'text-base',
                                            'large' => 'text-xl',
                                            default => 'text-lg',
                                        };
                                        $btnBg = $comp['content']['backgroundColor'] ?? '#ffffff';
                                        $btnColor = $comp['content']['buttonTextColor'] ?? '#111827';
                                        $btnPreviewStyle = 'border-radius:' . (int) $br . 'px;background-color:' . e($btnBg) . ';color:' . e($btnColor) . ';box-sizing:border-box;min-width:0;min-height:0;width:100%;height:100%;display:flex;align-items:center;justify-content:center;';
                                    @endphp
                                    <button
                                        type="button"
                                        class="block w-full font-semibold shadow pointer-events-none outline-none focus:outline-none focus:ring-0 border-0 focus:border-0 {{ $btnFs }}"
                                        style="<?php echo e($btnPreviewStyle); ?>"
                                    >
                                        {{ $comp['content']['text'] ?? 'Tap to Start' }}
                                    </button>
                                @endif
                            </div>
                        @endforeach

                        {{-- Empty State --}}
                        @if ($contentComponents->isEmpty())
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 dark:text-gray-500">
                                <x-filament::icon icon="heroicon-o-rectangle-stack" class="mb-2 h-12 w-12" />
                                <p class="text-sm">Add components to preview. Drag on canvas to move; drag in list (left) to reorder; click to edit.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===========================
         MODAL: Add/Edit Component
         =========================== --}}
    <x-filament::modal
        id="add-component-modal"
        :heading="$editingComponentId ? 'Edit Component' : 'Add Component'"
        width="md"
    >
        <form wire:submit.prevent="saveComponent">
            @if ($addType === 'text')
                {{-- TEXT FORM --}}
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Content
                        </label>
                        <textarea
                            wire:model="textContent"
                            rows="3"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            placeholder="Enter your text..."
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Font Size
                        </label>
                        <select
                            wire:model="textFontSize"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="small">Small</option>
                            <option value="medium">Medium</option>
                            <option value="large">Large</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Alignment
                        </label>
                        <select
                            wire:model="textAlignment"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="left">Left</option>
                            <option value="center">Center</option>
                            <option value="right">Right</option>
                        </select>
                    </div>
                    <p class="text-xs text-gray-500">Warna teks atur di panel editor setelah komponen dipilih.</p>
                </div>
            @elseif ($addType === 'image')
                {{-- IMAGE FORM --}}
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Upload Image
                        </label>
                        <input
                            type="file"
                            wire:model="imageFile"
                            accept="image/*"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-700 hover:file:bg-primary-100 dark:text-gray-400 dark:file:bg-gray-700 dark:file:text-gray-200"
                        />
                        @if ($editingComponentId)
                            <p class="mt-1 text-xs text-gray-500">Leave empty to keep current image</p>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Width
                        </label>
                        <select
                            wire:model.live="imageWidth"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="auto">Auto</option>
                            <option value="custom">Custom (px)</option>
                        </select>
                    </div>

                    @if ($imageWidth === 'custom')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Custom Width (px)
                            </label>
                            <input
                                type="number"
                                wire:model="imageCustomWidth"
                                min="50"
                                max="1920"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            />
                        </div>
                    @endif
                </div>
            @elseif ($addType === 'background')
                {{-- BACKGROUND FORM --}}
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Upload Background Image
                        </label>
                        <input
                            type="file"
                            wire:model="backgroundFile"
                            accept="image/*"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-700 hover:file:bg-primary-100 dark:text-gray-400 dark:file:bg-gray-700 dark:file:text-gray-200"
                        />
                        @if ($editingComponentId)
                            <p class="mt-1 text-xs text-gray-500">Leave empty to keep current background</p>
                    @else
                        <p class="mt-1 text-xs text-gray-500">Only one background is allowed. Adding a new one will replace the existing.</p>
                        @endif
                    </div>
                </div>
            @elseif ($addType === 'button')
                {{-- BUTTON FORM --}}
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Button Text
                        </label>
                        <input
                            type="text"
                            wire:model="buttonText"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            placeholder="Tap to Start"
                        />
                        <p class="mt-1 text-xs text-gray-500">This button appears on the homescreen and starts the session. Warna tombol atur di panel editor setelah komponen dipilih.</p>
                    </div>
                </div>
            @endif
        </form>

        <x-slot name="footerActions">
            <x-filament::button
                color="gray"
                wire:click="closeAddModal"
            >
                Cancel
            </x-filament::button>
            <x-filament::button
                type="button"
                wire:click="saveComponent"
            >
                {{ $editingComponentId ? 'Update' : 'Add' }}
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

    @push('styles')
        <style>
            .welcome-sortable-chosen { outline: 2px solid rgb(59 130 246); outline-offset: 2px; }
            .welcome-sortable-dragging { cursor: grabbing; }
            /* Hilangkan border/outline saat menekan tombol di preview canvas */
            .welcome-preview-item button {
                outline: none !important;
                box-shadow: none !important;
            }
            .welcome-preview-item button:focus,
            .welcome-preview-item button:active {
                outline: none !important;
                box-shadow: none !important;
                border: none !important;
            }
            /* Hilangkan border & ring saat komponen button dipilih (tekan) */
            .welcome-preview-item.ring-0 {
                --tw-ring-shadow: 0 0 #0000 !important;
                box-shadow: none !important;
            }
            /* Hanya editor panel yang scroll horizontal; canvas tidak ikut */
            .editor-panel-scroll {
                max-width: 100%;
                width: 100%;
                overflow-x: auto !important;
                overflow-y: hidden !important;
                -webkit-overflow-scrolling: touch;
            }
        </style>
    @endpush
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    @endpush
</x-filament-panels::page>
