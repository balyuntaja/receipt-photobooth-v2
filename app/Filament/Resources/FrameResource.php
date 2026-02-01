<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FrameResource\Pages;
use App\Filament\Resources\FrameResource\RelationManagers;
use App\Models\Frame;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FrameResource extends Resource
{
    protected static ?string $model = Frame::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationLabel = 'Bingkai';
    protected static ?string $navigationGroup = 'Application';
    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required(),

            Forms\Components\FileUpload::make('preview_image')
                ->image()
                ->directory('frames/previews'),

            Forms\Components\FileUpload::make('frame_file')
                ->required()
                ->directory('frames/files')
                ->acceptedFileTypes(['image/png'])
                ->helperText('PNG transparan'),

            Forms\Components\Toggle::make('is_active'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('preview_image')
                ->label('Preview'),

            Tables\Columns\TextColumn::make('name')
                ->searchable(),

            Tables\Columns\IconColumn::make('is_active')
                ->boolean(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFrames::route('/'),
            'create' => Pages\CreateFrame::route('/create'),
            'edit' => Pages\EditFrame::route('/{record}/edit'),
        ];
    }
}

