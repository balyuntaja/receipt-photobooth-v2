<?php

namespace App\Filament\Widgets;

use App\Models\BoothSession;
use App\Models\Transaction;
use App\Models\Media;
use App\Enums\TransactionStatusEnum;
use App\Enums\SessionStatusEnum;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $userId = auth()->id();

        $sessionsToday = BoothSession::whereDate('created_at', today())
            ->whereHas('project', fn ($q) => $q->where('user_id', $userId))
            ->count();

        $revenueToday = Transaction::whereDate('created_at', today())
            ->where('status', TransactionStatusEnum::PAID)
            ->whereHas('session.project', fn ($q) => $q->where('user_id', $userId))
            ->sum('amount');

        $totalSessions = BoothSession::whereHas('project', fn ($q) => $q->where('user_id', $userId))
            ->whereIn('status', [
                SessionStatusEnum::COMPLETED,
                SessionStatusEnum::IN_PROGRESS,
            ])
            ->count();

        $totalMedia = Media::whereHas('session.project', fn ($q) => $q->where('user_id', $userId))
            ->count();

        return [
            Stat::make('Sessions Today', $sessionsToday)
                ->description('Today')
                ->icon('heroicon-o-camera'),

            Stat::make('Revenue Today', 'Rp ' . number_format($revenueToday, 0, ',', '.'))
                ->description('Today')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Total Sessions', $totalSessions)
                ->description('All time')
                ->icon('heroicon-o-chart-bar'),

            Stat::make('Total Media', $totalMedia)
                ->description('Photos, Strips & Videos')
                ->icon('heroicon-o-photo'),
        ];
    }
}
