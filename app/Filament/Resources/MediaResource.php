<?php

namespace App\Filament\Resources;

use App\Enums\MediaTypeEnum;
use App\Filament\Resources\MediaResource\Pages;
use App\Models\Media;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Layout\View;
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
                View::make('filament.resources.media-resource.partials.gallery-card'),
                Tables\Columns\TextColumn::make('session_id')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('session_id', 'like', "%{$search}%");
                    })
                    ->hidden(),
            ])
            ->contentGrid([
                'sm' => 2,
                'md' => 3,
                'xl' => 4,
            ])
            ->filters([
                Tables\Filters\Filter::make('sort_order')
                    ->form([
                        Select::make('value')
                            ->label('Sort')
                            ->options([
                                'newest' => 'Newest First',
                                'oldest' => 'Oldest First',
                            ])
                            ->default('newest'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? 'newest';
                        return $value === 'oldest'
                            ? $query->reorder('created_at', 'asc')
                            : $query->reorder('created_at', 'desc');
                    }),

                Tables\Filters\SelectFilter::make('project')
                    ->label('Project')
                    ->placeholder('All Projects')
                    ->relationship('session.project', 'name'),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        MediaTypeEnum::IMAGE->value => 'Image',
                        MediaTypeEnum::STRIP->value => 'Strip',
                        MediaTypeEnum::VIDEO->value => 'Video',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Detail Foto')
                    ->modalContent(fn (Media $record) => view('filament.resources.media-resource.partials.view-modal', ['record' => $record])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->searchable()
            ->searchPlaceholder('Search by Session ID')
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
