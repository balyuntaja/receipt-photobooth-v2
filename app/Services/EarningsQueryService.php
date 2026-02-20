<?php

namespace App\Services;

use App\Models\MonthlyEarning;
use App\Models\Transaction;
use Illuminate\Support\Collection;

/**
 * User earnings query for manual monthly settlement dashboard.
 */
class EarningsQueryService
{
    /**
     * Get current month earnings summary for a user.
     */
    public function getCurrentMonthSummary(int $userId): array
    {
        $month = now()->format('Y-m');
        $row = MonthlyEarning::where('user_id', $userId)
            ->where('month', $month)
            ->first();

        if (! $row) {
            return [
                'total_gross' => 0,
                'total_fee' => 0,
                'total_net' => 0,
                'payout_status' => 'pending',
                'paid_at' => null,
            ];
        }

        return [
            'total_gross' => $row->total_gross,
            'total_fee' => $row->total_fee,
            'total_net' => $row->total_net,
            'payout_status' => $row->payout_status,
            'paid_at' => $row->paid_at?->toDateTimeString(),
        ];
    }

    /**
     * Get monthly earnings history for a user.
     */
    public function getMonthlyHistory(int $userId, int $limit = 12): Collection
    {
        return MonthlyEarning::where('user_id', $userId)
            ->orderByDesc('month')
            ->limit($limit)
            ->get();
    }

    /**
     * Get total earnings across all months for a user.
     */
    public function getTotalEarnings(int $userId): array
    {
        $rows = MonthlyEarning::where('user_id', $userId)->get();

        return [
            'total_gross' => $rows->sum('total_gross'),
            'total_fee' => $rows->sum('total_fee'),
            'total_net' => $rows->sum('total_net'),
        ];
    }
}
