<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';
    protected static ?string $navigationLabel = 'Projects';

    protected static ?string $navigationGroup = 'Application';
    protected static ?int $navigationSort = 1;


    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id());
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),

                Forms\Components\Textarea::make('description')
                    ->rows(3),

                Forms\Components\Toggle::make('is_active')
                    ->default(true),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Grid::make()
                    ->columns(3)
                    ->schema([
                        Stack::make([
                            ImageColumn::make('cover_image')
                                ->width('100%')
                                ->height('auto')
                                ->extraImgAttributes([
                                    'class' => 'w-full aspect-video object-cover rounded-lg',
                                ]),

                            TextColumn::make('name')
                                ->weight('bold')
                                ->size('lg'),

                            TextColumn::make('updated_at')
                                ->label('Last updated')
                                ->since(),
                        ]),
                    ]),
            ])
            ->actions([
                // 🔹 BUKA → KIOSK MODE
                Action::make('open')
                    ->label('Buka')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('warning')
                    ->url(fn(Project $record) => route('booth.start', $record))
                    ->openUrlInNewTab(),

                // 🔹 UBAH → PROJECT SETTINGS
                Action::make('settings')
                    ->label('Ubah')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->url(
                        fn(Project $record) =>
                        route('filament.admin.resources.projects.settings', $record)
                    ),

                // 🔹 HAPUS
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus'),
            ])

            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->paginated(false);
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
            'settings' => Pages\ProjectSettings::route('/{record}/settings'),
        ];
    }
}
