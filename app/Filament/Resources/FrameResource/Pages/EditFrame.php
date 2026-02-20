<?php

namespace App\Filament\Resources\FrameResource\Pages;

use App\Filament\Resources\FrameResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditFrame extends EditRecord
{
    protected static string $resource = FrameResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);
        $this->redirect(FrameResource::getUrl('layout', ['record' => $this->record]));
    }

    public function getHeading(): string | Htmlable
    {
        return $this->record->name ?: 'Untitled Frame';
    }

    public function getSubheading(): ?string
    {
        return null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return FrameResource::getUrl('index');
    }

    protected function getSaveFormAction(): \Filament\Actions\Action
    {
        return parent::getSaveFormAction()->label('Simpan');
    }
}
