<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VoucherResource\Pages;
use App\Filament\Resources\VoucherResource\RelationManagers;
use App\Models\Voucher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
class VoucherResource extends Resource
{
    protected static ?string $model = Voucher::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationLabel = 'Voucher';
    protected static ?string $navigationGroup = 'Application';
    protected static ?int $navigationSort = 4;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::id());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')
                ->required()
                ->unique(
                    table: 'vouchers',
                    column: 'code',
                    modifyRuleUsing: fn ($rule, $livewire) => $rule->where('user_id', Auth::id()),
                    ignoreRecord: true
                ),

            Forms\Components\Select::make('type')
                ->options([
                    'fixed' => 'Fixed (Rp)',
                    'percent' => 'Percent (%)',
                ])
                ->required()
                ->live(),

            Forms\Components\TextInput::make('value')
                ->numeric()
                ->required()
                ->prefix(fn (Get $get) => $get('type') === 'fixed' ? 'Rp' : null)
                ->suffix(fn (Get $get) => $get('type') === 'percent' ? '%' : null)
                ->minValue(0)
                ->maxValue(fn (Get $get) => $get('type') === 'percent' ? 100 : null)
                ->helperText(fn (Get $get) => $get('type') === 'percent' ? 'Maksimal 100%' : null),

            Forms\Components\TextInput::make('quota')
                ->numeric()
                ->default(1)
                ->minValue(1)
                ->required()
                ->helperText('Jumlah kali voucher bisa dipakai. Jika 1, voucher hilang setelah sekali pakai.'),

            Forms\Components\DatePicker::make('expires_at'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('code')->searchable(),
            Tables\Columns\TextColumn::make('type'),
            Tables\Columns\TextColumn::make('value'),
            Tables\Columns\TextColumn::make('quota'),
            Tables\Columns\TextColumn::make('expires_at')->date(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVouchers::route('/'),
            'create' => Pages\CreateVoucher::route('/create'),
            'edit' => Pages\EditVoucher::route('/{record}/edit'),
        ];
    }
}

