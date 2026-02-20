<?php

namespace App\Services;

use App\Models\MonthlyEarning;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

/**
 * Manual Payout Service – Admin marks monthly earnings as paid.
 * NO external API calls (IRIS disabled).
 */
class PayoutService
{
    /**
     * Mark monthly earning as paid.
     * Updates payout_status, paid_at, and transactions.paid_out_at.
     */
    public function markAsPaid(MonthlyEarning $earning): void
    {
        DB::transaction(function () use ($earning) {
            $earning->update([
                'payout_status' => MonthlyEarning::PAYOUT_STATUS_PAID,
                'paid_at' => now(),
            ]);

            $year = (int) substr($earning->month, 0, 4);
            $month = (int) substr($earning->month, 5, 2);

            Transaction::query()
                ->where('owner_user_id', $earning->user_id)
                ->where('type', 'photobooth_session')
                ->where('status', 'paid')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->update(['paid_out_at' => now()]);
        });
    }

    /**
     * Mark as pending (revert paid status – use with caution).
     */
    public function markAsPending(MonthlyEarning $earning): void
    {
        DB::transaction(function () use ($earning) {
            $earning->update([
                'payout_status' => MonthlyEarning::PAYOUT_STATUS_PENDING,
                'paid_at' => null,
            ]);

            $year = (int) substr($earning->month, 0, 4);
            $month = (int) substr($earning->month, 5, 2);

            Transaction::query()
                ->where('owner_user_id', $earning->user_id)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->update(['paid_out_at' => null]);
        });
    }
}
