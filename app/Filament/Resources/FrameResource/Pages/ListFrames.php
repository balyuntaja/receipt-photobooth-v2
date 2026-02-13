<?php

namespace App\Filament\Resources\FrameResource\Pages;

use App\Filament\Resources\FrameResource;
use App\Models\Frame;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class ListFrames extends ListRecords
{
    protected static string $resource = FrameResource::class;

    public function getHeading(): string | Htmlable
    {
        return 'Manajemen Bingkai';
    }

    public function getSubheading(): ?string
    {
        return 'Buat dan kelola bingkai photobooth Anda';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('createWithLayout')
                ->label('Buat Baru')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->action(function () {
                    $frame = Frame::create([
                        'user_id' => Auth::id(),
                        'name' => 'Untitled Frame',
                        'preview_image' => null,
                        'frame_file' => '',
                        'is_active' => true,
                    ]);

                    return redirect()->route('filament.admin.resources.frames.layout', $frame);
                }),
        ];
    }
}
