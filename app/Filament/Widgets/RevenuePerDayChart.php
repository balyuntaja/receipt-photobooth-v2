<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Enums\TransactionStatusEnum;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RevenuePerDayChart extends ChartWidget
{
    protected static ?string $heading = 'Revenue per Day';

    protected static ?int $sort = 5;

    protected static ?int $height = 300;

    protected int|string|array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $days = 14;
        $userId = Auth::id();
        $today = Carbon::today();

        $data = Transaction::query()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as total')
            )
            ->whereDate('created_at', '>=', now()->subDays($days))
            ->where('status', TransactionStatusEnum::PAID)
            ->whereHas(
                'session.project',
                fn($query) =>
                $query->where('user_id', $userId)
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $values = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i)->toDateString();

            $labels[] = Carbon::parse($date)->format('d M');

            $dayData = $data->firstWhere('date', $date);
            $values[] = $dayData ? (int) $dayData->total : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $values,
                    'fill' => false,
                    'tension' => 0.3,
                    'borderColor' => '#f97316',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getEmptyStateDescription(): ?string
    {
        return 'No revenue data available';
    }
}
