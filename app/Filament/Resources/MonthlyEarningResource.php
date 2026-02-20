<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MonthlyEarningResource\Pages;
use App\Models\MonthlyEarning;
use App\Services\PayoutService;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Notifications\Notification;

class MonthlyEarningResource extends Resource
{
    protected static ?string $model = MonthlyEarning::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Penghasilan Bulanan';

    protected static ?string $modelLabel = 'Penghasilan Bulanan';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 10;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('month')
                    ->label('Bulan')
                    ->formatStateUsing(fn (string $state) => \Carbon\Carbon::parse($state . '-01')->translatedFormat('F Y'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_gross')
                    ->label('Kotor')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_fee')
                    ->label('Biaya Platform')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_net')
                    ->label('Bersih')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('payout_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => $state === 'paid' ? 'success' : 'warning')
                    ->formatStateUsing(fn (string $state) => $state === 'paid' ? 'Sudah dicairkan' : 'Menunggu'),
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Tgl Cair')
                    ->dateTime('d M Y')
                    ->placeholder('-'),
            ])
            ->defaultSort('month', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('payout_status')
                    ->options([
                        'pending' => 'Menunggu',
                        'paid' => 'Sudah dicairkan',
                    ]),
                Tables\Filters\Filter::make('month')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('month')
                            ->label('Bulan (YYYY-MM)')
                            ->placeholder('2026-02'),
                    ])
                    ->query(function ($query, array $data) {
                        if (! empty($data['month'])) {
                            $query->where('month', $data['month']);
                        }
                        return $query;
                    }),
            ])
            ->actions([
                Action::make('markPaid')
                    ->label('Tandai Dicairkan')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (MonthlyEarning $record) => $record->payout_status === 'pending' && $record->total_net > 0)
                    ->requiresConfirmation()
                    ->modalHeading('Tandai pencairan')
                    ->modalDescription('Pastikan transfer sudah dilakukan ke rekening user. Transaksi di bulan ini akan ditandai paid_out_at.')
                    ->action(function (MonthlyEarning $record) {
                        app(PayoutService::class)->markAsPaid($record);
                        Notification::make()
                            ->title('Pencairan ditandai selesai')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMonthlyEarnings::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
