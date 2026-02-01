<?php

namespace App\Filament\Resources\BoothSessionResource\RelationManagers;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;
use App\Enums\MediaTypeEnum;

class MediaRelationManager extends RelationManager
{
    protected static string $relationship = 'media';

    protected static ?string $title = 'Gallery';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('file_path')
                    ->label('Preview')
                    ->square(),

                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'success' => MediaTypeEnum::IMAGE->value,
                        'warning' => MediaTypeEnum::STRIP->value,
                        'info'    => MediaTypeEnum::VIDEO->value,
                    ])
                    ->label('Type'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Captured At'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(), // optional
            ])
            ->defaultSort('created_at', 'desc');
    }
}
