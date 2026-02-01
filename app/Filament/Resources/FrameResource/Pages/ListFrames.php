<?php

namespace App\Filament\Resources\FrameResource\Pages;

use App\Filament\Resources\FrameResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFrames extends ListRecords
{
    protected static string $resource = FrameResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
