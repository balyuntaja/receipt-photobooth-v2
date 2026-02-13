<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\Project;
use App\Models\WelcomeScreenComponent;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class WelcomeScreen extends Page
{
    use WithFileUploads;

    protected static string $resource = ProjectResource::class;
    protected static string $view = 'filament.projects.welcome-screen';

    public Project $record;

    // Welcome screen settings (per project)
    public string $backgroundColor = '';

    // Component list state
    public array $components = [];

    // Add component modal state
    public string $addType = '';

    // Text form fields
    public string $textContent = '';
    public string $textFontSize = 'medium';
    public string $textAlignment = 'center';
    public string $textColor = '#ffffff';

    // Image form fields
    public $imageFile = null;
    public string $imageWidth = 'auto';
    public int $imageCustomWidth = 200;

    // Background form fields
    public $backgroundFile = null;

    // Button form fields
    public string $buttonText = 'Tap to Start';
    public string $buttonBackgroundColor = '';
    public string $buttonTextColor = '';

    // Edit mode
    public ?int $editingComponentId = null;

    // Selected component for editor panel
    public ?int $selectedComponentId = null;

    // Editor panel form fields (px, canvas 1920×1080)
    public int $editorX = 960;
    public int $editorY = 540;
    public string $editorWidth = 'auto';
    public string $editorHeight = 'auto';
    public string $editorFontSize = 'medium';
    public int $editorBorderRadius = 12;
    public string $editorTextColor = '#ffffff';
    public string $editorButtonBackgroundColor = '';
    public string $editorButtonTextColor = '';

    public function getSubNavigation(): array
    {
        return [];
    }

    public function mount(Project $record): void
    {
        $this->record = $record;
        $this->backgroundColor = (string) ($record->welcome_background_color ?? '');
        $this->loadComponents();
    }

    public function loadComponents(): void
    {
        $this->components = $this->record
            ->welcomeScreenComponents()
            ->ordered()
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'type' => $c->type,
                'content' => $c->content,
                'sort_order' => $c->sort_order,
            ])
            ->toArray();
    }

    /**
     * Save welcome screen background color (fallback when no background image).
     */
    public function saveBackgroundColor(): void
    {
        $this->record->update(['welcome_background_color' => $this->backgroundColor ?: null]);
        Notification::make()->title('Background color saved')->success()->send();
    }

    // ===========================
    // ADD COMPONENT MODAL
    // ===========================

    public function openAddModal(string $type): void
    {
        $this->resetFormFields();
        $this->addType = $type;
        $this->editingComponentId = null;
        $this->dispatch('open-modal', id: 'add-component-modal');
    }

    public function closeAddModal(): void
    {
        $this->resetFormFields();
        $this->dispatch('close-modal', id: 'add-component-modal');
    }

    protected function resetFormFields(): void
    {
        $this->addType = '';
        $this->textContent = '';
        $this->textFontSize = 'medium';
        $this->textAlignment = 'center';
        $this->textColor = '#ffffff';
        $this->imageFile = null;
        $this->imageWidth = 'auto';
        $this->imageCustomWidth = 200;
        $this->backgroundFile = null;
        $this->buttonText = 'Tap to Start';
        $this->buttonBackgroundColor = '';
        $this->buttonTextColor = '';
        $this->editingComponentId = null;
    }

    protected function getExistingContent(int $id): array
    {
        $c = WelcomeScreenComponent::find($id);
        return $c ? ($c->content ?? []) : [];
    }

    protected function getDefaultLayoutContent(): array
    {
        return [
            'x' => 960,
            'y' => 540,
            'layoutWidth' => 'auto',
            'layoutHeight' => 'auto',
            'fontSize' => 'medium',
            'borderRadius' => 12,
        ];
    }

    // ===========================
    // SAVE COMPONENT
    // ===========================

    public function saveComponent(): void
    {
        $content = [];
        $filePath = null;

        switch ($this->addType) {
            case 'text':
                if (empty(trim($this->textContent))) {
                    Notification::make()
                        ->title('Text content is required')
                        ->danger()
                        ->send();
                    return;
                }
                $base = ['text' => $this->textContent, 'fontSize' => $this->textFontSize, 'alignment' => $this->textAlignment, 'textColor' => $this->textColor ?: '#ffffff'];
                $content = $this->editingComponentId
                    ? array_merge($this->getExistingContent($this->editingComponentId), $base)
                    : array_merge($this->getDefaultLayoutContent(), $base);
                break;

            case 'image':
                if ($this->imageFile) {
                    $filePath = $this->imageFile->store('welcome-screen', 'public');
                } elseif ($this->editingComponentId) {
                    // Keep existing image if not uploading new one
                    $existing = WelcomeScreenComponent::find($this->editingComponentId);
                    $filePath = $existing?->content['path'] ?? null;
                }

                if (!$filePath) {
                    Notification::make()
                        ->title('Image is required')
                        ->danger()
                        ->send();
                    return;
                }

                $base = ['path' => $filePath, 'width' => $this->imageWidth, 'customWidth' => $this->imageCustomWidth];
                $content = $this->editingComponentId
                    ? array_merge($this->getExistingContent($this->editingComponentId), $base)
                    : array_merge($this->getDefaultLayoutContent(), $base);
                break;

            case 'background':
                if ($this->backgroundFile) {
                    $filePath = $this->backgroundFile->store('welcome-screen', 'public');
                } elseif ($this->editingComponentId) {
                    $existing = WelcomeScreenComponent::find($this->editingComponentId);
                    $filePath = $existing?->content['path'] ?? null;
                }

                if (!$filePath) {
                    Notification::make()
                        ->title('Background image is required')
                        ->danger()
                        ->send();
                    return;
                }

                // Remove existing background if any (only one allowed)
                if (!$this->editingComponentId) {
                    $this->record->welcomeScreenComponents()
                        ->where('type', 'background')
                        ->delete();
                }

                $content = [
                    'path' => $filePath,
                ];
                break;

            case 'button':
                $base = ['text' => trim($this->buttonText) ?: 'Tap to Start'];
                if ($this->buttonBackgroundColor !== '') {
                    $base['backgroundColor'] = $this->buttonBackgroundColor;
                }
                if ($this->buttonTextColor !== '') {
                    $base['buttonTextColor'] = $this->buttonTextColor;
                }
                $content = $this->editingComponentId
                    ? array_merge($this->getExistingContent($this->editingComponentId), $base)
                    : array_merge($this->getDefaultLayoutContent(), $base);
                break;
        }

        if ($this->editingComponentId) {
            // Update existing
            WelcomeScreenComponent::where('id', $this->editingComponentId)
                ->update([
                    'content' => $content,
                ]);
        } else {
            // Create new
            $maxSort = $this->record->welcomeScreenComponents()->max('sort_order') ?? -1;

            WelcomeScreenComponent::create([
                'project_id' => $this->record->id,
                'type' => $this->addType,
                'content' => $content,
                'sort_order' => $maxSort + 1,
            ]);
        }

        $this->closeAddModal();
        $this->loadComponents();

        Notification::make()
            ->title('Component saved')
            ->success()
            ->send();
    }

    // ===========================
    // EDIT COMPONENT
    // ===========================

    public function editComponent(int $id): void
    {
        $component = WelcomeScreenComponent::find($id);
        if (!$component || $component->project_id !== $this->record->id) {
            return;
        }

        $this->resetFormFields();
        $this->editingComponentId = $id;
        $this->addType = $component->type;
        $this->dispatch('open-modal', id: 'add-component-modal');

        switch ($component->type) {
            case 'text':
                $this->textContent = $component->content['text'] ?? '';
                $this->textFontSize = $component->content['fontSize'] ?? 'medium';
                $this->textAlignment = $component->content['alignment'] ?? 'center';
                $this->textColor = (string) ($component->content['textColor'] ?? '#ffffff');
                break;

            case 'image':
                $this->imageWidth = $component->content['width'] ?? 'auto';
                $this->imageCustomWidth = $component->content['customWidth'] ?? 200;
                break;

            case 'background':
                // Background only has path, no additional fields to populate
                break;

            case 'button':
                $this->buttonText = $component->content['text'] ?? 'Tap to Start';
                $this->buttonBackgroundColor = (string) ($component->content['backgroundColor'] ?? '');
                $this->buttonTextColor = (string) ($component->content['buttonTextColor'] ?? '');
                break;
        }
    }

    // ===========================
    // DELETE COMPONENT
    // ===========================

    public function deleteComponent(int $id): void
    {
        $component = WelcomeScreenComponent::find($id);
        if (!$component || $component->project_id !== $this->record->id) {
            return;
        }

        // Delete file from storage
        if (isset($component->content['path'])) {
            Storage::disk('public')->delete($component->content['path']);
        }

        $component->delete();
        $this->loadComponents();

        Notification::make()
            ->title('Component deleted')
            ->success()
            ->send();
    }

    // ===========================
    // REORDER COMPONENTS
    // ===========================

    public function moveUp(int $id): void
    {
        $components = $this->record->welcomeScreenComponents()->ordered()->get();
        $index = $components->search(fn($c) => $c->id === $id);

        if ($index === false || $index === 0) {
            return;
        }

        $current = $components[$index];
        $previous = $components[$index - 1];

        // Swap sort_order
        $tempSort = $current->sort_order;
        $current->update(['sort_order' => $previous->sort_order]);
        $previous->update(['sort_order' => $tempSort]);

        $this->loadComponents();
    }

    public function moveDown(int $id): void
    {
        $components = $this->record->welcomeScreenComponents()->ordered()->get();
        $index = $components->search(fn($c) => $c->id === $id);

        if ($index === false || $index === $components->count() - 1) {
            return;
        }

        $current = $components[$index];
        $next = $components[$index + 1];

        // Swap sort_order
        $tempSort = $current->sort_order;
        $current->update(['sort_order' => $next->sort_order]);
        $next->update(['sort_order' => $tempSort]);

        $this->loadComponents();
    }

    /**
     * Select a component to show in the editor panel.
     */
    public function selectComponent(int $id): void
    {
        $component = WelcomeScreenComponent::find($id);
        if (!$component || $component->project_id !== $this->record->id || $component->type === 'background') {
            return;
        }
        $this->selectedComponentId = $id;
        $content = $component->content ?? [];
        $rawX = $content['x'] ?? 960;
        $rawY = $content['y'] ?? 540;
        $this->editorX = $rawX <= 100 ? (int) round($rawX / 100 * 1920) : (int) $rawX;
        $this->editorY = $rawY <= 100 ? (int) round($rawY / 100 * 1080) : (int) $rawY;
        $this->editorWidth = (string) ($content['layoutWidth'] ?? $content['width'] ?? 'auto');
        $this->editorHeight = (string) ($content['layoutHeight'] ?? $content['height'] ?? 'auto');
        $this->editorFontSize = (string) ($content['fontSize'] ?? 'medium');
        $this->editorBorderRadius = (int) ($content['borderRadius'] ?? 12);
        $this->editorTextColor = (string) ($content['textColor'] ?? '#ffffff');
        $this->editorButtonBackgroundColor = (string) ($content['backgroundColor'] ?? '');
        $this->editorButtonTextColor = (string) ($content['buttonTextColor'] ?? '');
    }

    /**
     * Deselect component and hide editor panel.
     */
    public function deselectComponent(): void
    {
        $this->selectedComponentId = null;
    }

    /**
     * Update component position (x, y) from canvas drag. Updates in-memory $components and editor panel if selected.
     */
    public function updateComponentPosition(int $id, int $x, int $y): void
    {
        $x = max(0, min(1920, $x));
        $y = max(0, min(1080, $y));
        foreach ($this->components as $i => $comp) {
            if (($comp['id'] ?? null) === $id) {
                $content = $this->components[$i]['content'] ?? [];
                $content['x'] = $x;
                $content['y'] = $y;
                $this->components[$i]['content'] = $content;
                if ($this->selectedComponentId === $id) {
                    $this->editorX = $x;
                    $this->editorY = $y;
                }
                break;
            }
        }
    }

    /**
     * Sync editor panel values to the selected component in $components (preview only, no DB).
     * Called on every editor field change so preview updates immediately.
     */
    protected function syncEditorToComponents(): void
    {
        if (!$this->selectedComponentId) {
            return;
        }
        foreach ($this->components as $i => $comp) {
            if (($comp['id'] ?? null) === $this->selectedComponentId) {
                $content = $this->components[$i]['content'] ?? [];
                $content['x'] = $this->editorX;
                $content['y'] = $this->editorY;
                $content['layoutWidth'] = $this->editorWidth;
                $content['layoutHeight'] = $this->editorHeight;
                $content['fontSize'] = $this->editorFontSize;
                $content['borderRadius'] = $this->editorBorderRadius;
                if ($comp['type'] === 'text') {
                    $content['textColor'] = $this->editorTextColor ?: '#ffffff';
                }
                if ($comp['type'] === 'button') {
                    if ($this->editorButtonBackgroundColor !== '') {
                        $content['backgroundColor'] = $this->editorButtonBackgroundColor;
                    }
                    if ($this->editorButtonTextColor !== '') {
                        $content['buttonTextColor'] = $this->editorButtonTextColor;
                    }
                }
                $this->components[$i]['content'] = $content;
                break;
            }
        }
    }

    public function updatedEditorX(): void
    {
        $this->syncEditorToComponents();
    }

    public function updatedEditorY(): void
    {
        $this->syncEditorToComponents();
    }

    public function updatedEditorWidth(): void
    {
        $this->syncEditorToComponents();
    }

    public function updatedEditorHeight(): void
    {
        $this->syncEditorToComponents();
    }

    public function updatedEditorFontSize(): void
    {
        $this->syncEditorToComponents();
    }

    public function updatedEditorBorderRadius(): void
    {
        $this->syncEditorToComponents();
    }

    public function updatedEditorTextColor(): void
    {
        $this->syncEditorToComponents();
    }

    public function updatedEditorButtonBackgroundColor(): void
    {
        $this->syncEditorToComponents();
    }

    public function updatedEditorButtonTextColor(): void
    {
        $this->syncEditorToComponents();
    }

    /**
     * Apply editor panel values to the selected component (persist to DB).
     */
    public function applyComponentLayout(): void
    {
        if (!$this->selectedComponentId) {
            return;
        }
        $component = WelcomeScreenComponent::find($this->selectedComponentId);
        if (!$component || $component->project_id !== $this->record->id) {
            return;
        }
        $content = $component->content ?? [];
        $content['x'] = $this->editorX;
        $content['y'] = $this->editorY;
        $content['layoutWidth'] = $this->editorWidth;
        $content['layoutHeight'] = $this->editorHeight;
        $content['fontSize'] = $this->editorFontSize;
        $content['borderRadius'] = $this->editorBorderRadius;
        if ($component->type === 'text') {
            $content['textColor'] = $this->editorTextColor ?: '#ffffff';
        }
        if ($component->type === 'button') {
            if ($this->editorButtonBackgroundColor !== '') {
                $content['backgroundColor'] = $this->editorButtonBackgroundColor;
            }
            if ($this->editorButtonTextColor !== '') {
                $content['buttonTextColor'] = $this->editorButtonTextColor;
            }
        }
        $component->update(['content' => $content]);
        $this->loadComponents();
        $this->dispatch('components-updated');
        Notification::make()->title('Layout diterapkan')->success()->send();
    }

    /**
     * Reorder components from drag-and-drop in preview.
     * @param string $idsJson JSON array of component IDs in new order.
     */
    public function reorderComponents(string $idsJson = '[]'): void
    {
        $orderedIds = json_decode($idsJson, true);
        if (!is_array($orderedIds) || empty($orderedIds)) {
            return;
        }

        // Ensure background stays first (sort_order = -1)
        $this->record->welcomeScreenComponents()
            ->where('type', 'background')
            ->update(['sort_order' => -1]);

        // Content components get sort_order 0, 1, 2, ...
        foreach ($orderedIds as $index => $id) {
            WelcomeScreenComponent::where('id', (int) $id)
                ->where('project_id', $this->record->id)
                ->where('type', '!=', 'background')
                ->update(['sort_order' => $index]);
        }
        $this->loadComponents();
        $this->dispatch('components-updated');

        Notification::make()
            ->title('Urutan komponen diperbarui')
            ->success()
            ->send();
    }

    // ===========================
    // NAVIGATION
    // ===========================

    public function back()
    {
        return redirect()->route(
            'filament.admin.resources.projects.settings',
            ['record' => $this->record]
        );
    }
}
