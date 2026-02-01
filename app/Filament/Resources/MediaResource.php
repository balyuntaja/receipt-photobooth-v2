<?php

namespace App\Filament\Resources;

use App\Enums\MediaTypeEnum;
use App\Filament\Resources\MediaResource\Pages;
use App\Models\Media;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationLabel = 'Gallery';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?int $navigationSort = 3;

    /**
     * 🔐 USER-SCOPED (KRITIKAL SAAS)
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('session.project', fn ($q) =>
                $q->where('user_id', auth()->id())
            );
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('file_path')
                    ->label('Preview')
                    ->square()
                    ->size(80),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'success' => MediaTypeEnum::IMAGE,
                        'warning' => MediaTypeEnum::STRIP,
                        'info'    => MediaTypeEnum::VIDEO,
                    ])
                    ->formatStateUsing(fn (MediaTypeEnum $state) => $state->name),

                Tables\Columns\TextColumn::make('session.id')
                    ->label('Session ID')
                    ->copyable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('session.project.name')
                    ->label('Project')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Captured At')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('project')
                    ->label('Project')
                    ->relationship('session.project', 'name'),

                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        MediaTypeEnum::IMAGE->value => 'Image',
                        MediaTypeEnum::STRIP->value => 'Strip',
                        MediaTypeEnum::VIDEO->value => 'Video',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(), // optional
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No media found')
            ->emptyStateDescription('Photos will appear after booth sessions are completed.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedia::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
