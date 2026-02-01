<?php

namespace App\Filament\Resources\BoothSessionResource\RelationManagers;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class TransactionRelationManager extends RelationManager
{
    protected static string $relationship = 'transaction';

    protected static ?string $title = 'Transaction';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Transaction ID')
                    ->copyable(),

                Tables\Columns\TextColumn::make('amount')
                    ->money('IDR')
                    ->label('Amount'),

                Tables\Columns\TextColumn::make('discount')
                    ->money('IDR')
                    ->label('Discount'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->label('Status'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Created At'),
            ])
            ->actions([]) // no edit/delete
            ->bulkActions([])
            ->headerActions([]);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
