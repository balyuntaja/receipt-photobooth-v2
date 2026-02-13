<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Models\Frame;
use App\Filament\Resources\ProjectResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->setting()->create([
            'price_per_session' => 0,
            'copies' => 1,
            'max_retakes' => 3,
            'auto_print' => true,
        ]);

        // Default cover image for new projects
        $this->record->update(['cover_image' => 'general_homescreen.png']);

        // Default frame using template-frame from storage
        $defaultFrame = Frame::create([
            'user_id' => auth()->id(),
            'name' => 'Default Frame',
            'preview_image' => 'template-frame/template-1.png',
            'frame_file' => 'template-frame/template-1.png',
            'is_active' => true,
        ]);

        $this->record->frames()->attach($defaultFrame->id, ['is_active' => true]);
    }
}
