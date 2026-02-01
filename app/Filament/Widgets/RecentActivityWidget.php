<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentActivityWidget extends BaseWidget
{
    /**
     * Urutan widget di dashboard
     */
    protected static ?int $sort = 5;

    /**
     * Full width
     */
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            // Judul table (search akan otomatis sejajar di kanan)
            ->heading('Recent Transactions')

            // Tidak ada action di header (opsional, tapi bersih)
            ->headerActions([])

            // Global search (render di kanan judul)
            ->searchable()

            // Query user-scoped (AMAN SAAS)
            ->query(
                Transaction::query()
                    ->with(['session.project'])
                    ->whereHas(
                        'session.project',
                        fn($query) =>
                        $query->where('user_id', auth()->id())
                    )
                    ->latest()
                    ->limit(10)
            )

            // Kolom table
            ->columns([
                Tables\Columns\TextColumn::make('session.project.name')
                    ->label('Project')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime('d M Y • H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('IDR')
                    ->alignRight(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => ['paid'],
                        'danger' => ['failed'],
                        'gray' => ['pending', 'free'],
                    ]),
            ])

            // Tidak pakai pagination (recent activity)
            ->paginated(false)

            // Empty state UX
            ->emptyStateHeading('No transactions yet')
            ->emptyStateDescription(
                'Transactions will appear here once sessions are completed.'
            );
    }
}
