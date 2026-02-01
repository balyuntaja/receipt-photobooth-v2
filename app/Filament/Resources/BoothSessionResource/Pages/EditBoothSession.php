<?php

namespace App\Filament\Resources\BoothSessionResource\Pages;

use App\Filament\Resources\BoothSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBoothSession extends EditRecord
{
    protected static string $resource = BoothSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
