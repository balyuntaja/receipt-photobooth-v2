<?php

namespace App\Filament\Resources\BoothSessionResource\Pages;

use App\Filament\Resources\BoothSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBoothSessions extends ListRecords
{
    protected static string $resource = BoothSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
