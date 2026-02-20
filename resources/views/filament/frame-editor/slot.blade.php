@php
    $style = $this->getSlotStyle($slot);
    $styleString = sprintf(
        'left:%s%%;top:%s%%;width:%s%%;height:%s%%;transform:translate(-50%%, -50%%);',
        $style['left'],
        $style['top'],
        $style['width'],
        $style['height']
    );
@endphp
<div
    wire:key="slot-{{ $slot['id'] }}"
    class="absolute cursor-move rounded border border-gray-700 bg-gray-300 text-gray-900 shadow-sm {{ $selectedSlotId === $slot['id'] ? 'ring-2 ring-primary-500' : '' }}"
    style="<?php echo e($styleString); ?>"
    @mousedown.stop="startDrag($event, {{ $slot['id'] }})"
    wire:click.stop="selectSlot({{ $slot['id'] }})">
    <div class="flex h-full w-full flex-col items-center justify-center text-center">
        <div class="text-sm font-semibold">Slot {{ $index + 1 }}</div>
        <div class="text-[11px] text-gray-700">(Photo #{{ $index + 1 }})</div>
    </div>
</div>
