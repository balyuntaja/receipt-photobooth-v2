<?php

namespace App\Filament\Resources;

use App\Enums\SessionStatusEnum;
use App\Filament\Resources\BoothSessionResource\Pages;
use App\Filament\Resources\BoothSessionResource\RelationManagers\TransactionRelationManager;
use App\Filament\Resources\BoothSessionResource\RelationManagers\MediaRelationManager;
use App\Models\BoothSession;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BoothSessionResource extends Resource
{
    protected static ?string $model = BoothSession::class;

    protected static ?string $navigationIcon = 'heroicon-o-camera';
    protected static ?string $navigationLabel = 'Booth Sessions';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?int $navigationSort = 1;

    /**
     * Disable manual create (sessions dibuat oleh sistem booth)
     */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('project', function ($query) {
                $query->where('user_id', auth()->id());
            });
    }

    /**
     * =======================
     * FORM
     * =======================
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Session Information')
                    ->schema([
                        Forms\Components\TextInput::make('id')
                            ->label('Session ID')
                            ->disabled(),

                        Forms\Components\Select::make('project_id')
                            ->relationship('project', 'name')
                            ->label('Project')
                            ->disabled(),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(
                                collect(SessionStatusEnum::cases())
                                    ->mapWithKeys(fn(SessionStatusEnum $case) => [
                                        $case->value => $case->label(),
                                    ])
                                    ->toArray()
                            )
                            ->required(),

                        Forms\Components\DateTimePicker::make('started_at')
                            ->label('Started At')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('ended_at')
                            ->label('Ended At')
                            ->disabled(),

                        Forms\Components\TextInput::make('duration_sec')
                            ->label('Duration (sec)')
                            ->disabled(),
                    ])
                    ->columns(2),
            ]);
    }

    /**
     * =======================
     * TABLE
     * =======================
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Session ID')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('project.name')
                    ->label('Project')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => SessionStatusEnum::IN_PROGRESS,
                        'success' => SessionStatusEnum::COMPLETED,
                        'danger' => SessionStatusEnum::CANCELLED,
                    ])
                    ->formatStateUsing(
                        fn(SessionStatusEnum $state) => $state->label()
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('started_at')
                    ->label('Start')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('ended_at')
                    ->label('End')
                    ->dateTime('d M Y H:i'),

                Tables\Columns\TextColumn::make('duration_sec')
                    ->label('Duration')
                    ->suffix(' sec')
                    ->sortable(),

                Tables\Columns\TextColumn::make('transaction.amount')
                    ->label('Amount')
                    ->money('IDR'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(
                        collect(SessionStatusEnum::cases())
                            ->mapWithKeys(fn(SessionStatusEnum $case) => [
                                $case->value => $case->label(),
                            ])
                            ->toArray()
                    ),

                Tables\Filters\SelectFilter::make('project')
                    ->relationship('project', 'name')
                    ->label('Project'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('started_at', 'desc')
            ->emptyStateHeading('No sessions found')
            ->emptyStateDescription(
                'Sessions will appear automatically when the booth is used.'
            );
    }

    /**
     * =======================
     * RELATIONS
     * =======================
     */
    public static function getRelations(): array
    {
        return [
            TransactionRelationManager::class,
            MediaRelationManager::class,
        ];
    }

    /**
     * =======================
     * PAGES
     * =======================
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBoothSessions::route('/'),
            'edit' => Pages\EditBoothSession::route('/{record}/edit'),
        ];
    }
}
