@php
    $frameFile = $record->frame_file 
        ? ((strpos($record->frame_file, 'http') === 0) 
            ? $record->frame_file 
            : asset('storage/' . $record->frame_file))
        : null;
@endphp

<div
    x-data="zoomWorkspace({{ $canvasWidth }}, {{ $canvasHeight }})"
    class="flex h-[600px] flex-col overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
    @include('filament.frame-editor.zoom-controls')
    <div x-ref="workspace" class="flex-1 overflow-auto bg-gray-200 p-4 dark:bg-gray-800">
        <div class="flex justify-center pb-6">
            @php
                $paperStaticStyle = 'width:' . (int) $canvasWidth . 'px;height:' . (int) $canvasHeight . 'px;';
            @endphp
            <div
                :style="{ width: baseWidth * zoom + 'px', height: baseHeight * zoom + 'px' }"
                style="<?php echo e($paperStaticStyle); ?>">
                <div
                    x-ref="paper"
                    class="relative h-full w-full rounded-lg bg-white shadow-lg ring-1 ring-gray-200">
                    <div
                        id="frame-layout-canvas"
                        x-data="frameCanvas({{ $canvasWidth }}, {{ $canvasHeight }})"
                        class="relative h-full w-full">
                        @if ($frameFile)
                            <img
                                src="{{ $frameFile }}"
                                class="absolute inset-0 h-full w-full object-contain" />
                        @endif

                        @foreach ($slots as $index => $slot)
                            @include('filament.frame-editor.slot', ['slot' => $slot, 'index' => $index])
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
