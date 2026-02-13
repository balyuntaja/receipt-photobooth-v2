<?php

namespace App\Filament\Resources\FrameResource\Pages;

use App\Filament\Resources\FrameResource;
use App\Models\Frame;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Livewire\WithFileUploads;

class FrameLayout extends Page
{
    use WithFileUploads;
    protected static string $resource = FrameResource::class;

    protected static string $view = 'filament.frames.layout';

    public Frame $record;

    /**
     * Virtual canvas size in pixels for 8cm x 11cm at ~300 DPI.
     */
    public int $canvasWidth = 945;
    public int $canvasHeight = 1299;

    /**
     * Photo slots [{ id, x, y, width, height }]
     */
    public array $slots = [];

    public ?int $selectedSlotId = null;

    public int $editorX = 0;
    public int $editorY = 0;
    public int $editorWidth = 300;
    public int $editorHeight = 400;

    public $frameFileUpload = null;

    public function getSubNavigation(): array
    {
        return [];
    }

    public function mount(Frame $record): void
    {
        $this->record = $record;
        $this->loadSlots();
    }

    public function loadSlots(): void
    {
        $raw = $this->record->photo_slots ?? [];
        $this->slots = [];

        foreach ($raw as $slot) {
            $this->slots[] = [
                'id' => (int) ($slot['id'] ?? (count($this->slots) + 1)),
                'x' => (int) ($slot['x'] ?? (int) ($this->canvasWidth / 2)),
                'y' => (int) ($slot['y'] ?? (int) ($this->canvasHeight / 2)),
                'width' => (int) ($slot['width'] ?? 400),
                'height' => (int) ($slot['height'] ?? 300),
            ];
        }

        // Ensure deterministic IDs
        $this->slots = array_values($this->slots);
    }

    protected function nextSlotId(): int
    {
        if (empty($this->slots)) {
            return 1;
        }

        return max(array_column($this->slots, 'id')) + 1;
    }

    public function addSlot(): void
    {
        $id = $this->nextSlotId();

        $slot = [
            'id' => $id,
            'x' => (int) ($this->canvasWidth / 2),
            'y' => (int) ($this->canvasHeight / 2),
            'width' => 400,
            'height' => 300,
        ];

        $this->slots[] = $slot;
        $this->selectSlot($id);
    }

    public function selectSlot(int $id): void
    {
        foreach ($this->slots as $slot) {
            if ($slot['id'] === $id) {
                $this->selectedSlotId = $id;
                $this->editorX = $slot['x'];
                $this->editorY = $slot['y'];
                $this->editorWidth = $slot['width'];
                $this->editorHeight = $slot['height'];
                return;
            }
        }

        $this->selectedSlotId = null;
    }

    public function deselectSlot(): void
    {
        $this->selectedSlotId = null;
    }

    public function deleteSlot(int $id): void
    {
        $this->slots = array_values(array_filter(
            $this->slots,
            fn ($slot) => $slot['id'] !== $id
        ));

        if ($this->selectedSlotId === $id) {
            $this->selectedSlotId = null;
        }
    }

    /**
     * Update slot position from canvas drag (preview only until saved).
     */
    public function updateSlotPosition(int $id, int $x, int $y): void
    {
        $x = max(0, min($this->canvasWidth, $x));
        $y = max(0, min($this->canvasHeight, $y));

        foreach ($this->slots as $i => $slot) {
            if ($slot['id'] === $id) {
                $this->slots[$i]['x'] = $x;
                $this->slots[$i]['y'] = $y;
                if ($this->selectedSlotId === $id) {
                    $this->editorX = $x;
                    $this->editorY = $y;
                }
                break;
            }
        }
    }

    protected function syncEditorToSlots(): void
    {
        if (!$this->selectedSlotId) {
            return;
        }

        $x = max(0, min($this->canvasWidth, $this->editorX));
        $y = max(0, min($this->canvasHeight, $this->editorY));
        $w = max(10, min($this->canvasWidth, $this->editorWidth));
        $h = max(10, min($this->canvasHeight, $this->editorHeight));

        foreach ($this->slots as $i => $slot) {
            if ($slot['id'] === $this->selectedSlotId) {
                $this->slots[$i]['x'] = $x;
                $this->slots[$i]['y'] = $y;
                $this->slots[$i]['width'] = $w;
                $this->slots[$i]['height'] = $h;
                break;
            }
        }
    }

    public function updatedEditorX(): void
    {
        $this->syncEditorToSlots();
    }

    public function updatedEditorY(): void
    {
        $this->syncEditorToSlots();
    }

    public function updatedEditorWidth(): void
    {
        $this->syncEditorToSlots();
    }

    public function updatedEditorHeight(): void
    {
        $this->syncEditorToSlots();
    }

    /**
     * Auto-save frame overlay file as soon as it is uploaded,
     * so the template immediately appears on the canvas.
     */
    public function updatedFrameFileUpload(): void
    {
        $this->saveSlots();
    }

    public function saveSlots(): void
    {
        $this->syncEditorToSlots();

        $data = [
            'photo_slots' => array_values($this->slots),
        ];

        if ($this->frameFileUpload) {
            $path = $this->frameFileUpload->store('frames/files', 'public');
            $data['frame_file'] = $path;

            if (! $this->record->preview_image) {
                $data['preview_image'] = $path;
            }

            $this->frameFileUpload = null;
        }

        $this->record->update($data);

        Notification::make()
            ->title('Photo slots saved')
            ->success()
            ->send();
    }
}

