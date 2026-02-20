<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use Filament\Resources\Pages\ListRecords;

class ListMedia extends ListRecords
{
    protected static string $resource = MediaResource::class;

    public function getHeading(): string
    {
        return 'Gallery';
    }

    public function getSubheading(): ?string
    {
        return 'Manage all user generated photos';
    }
}
