<?php

namespace App\Services;

use App\Models\MonthlyEarning;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

/**
 * Settlement Service – Manual Monthly Payout Model
 *
 * Handles:
 * - Calculating platform fee and owner amount from gross
 * - Updating transactions with settlement data
 * - Idempotent aggregation into monthly_earnings
 *
 * NO automatic disbursement. Payout is manual via admin.
 * Future: swap to PayoutService (IRIS) when business entity ready.
 */
class SettlementService
{
    protected float $platformFeePercent;

    public function __construct(?float $platformFeePercent = null)
    {
        $this->platformFeePercent = $platformFeePercent ?? (float) config('midtrans.platform_fee_percent', 10);
    }

    /**
     * Calculate platform fee and owner amount from gross.
     */
    public function calculateSplit(int $grossAmount): array
    {
        $fee = (int) round($grossAmount * ($this->platformFeePercent / 100));
        $owner = $grossAmount - $fee;

        return [
            'gross_amount' => $grossAmount,
            'platform_fee' => $fee,
            'owner_amount' => max(0, $owner),
        ];
    }

    /**
     * Record settlement for a transaction.
     * Updates transaction with gross_amount, platform_fee, owner_amount.
     * Aggregates into monthly_earnings (idempotent).
     */
    public function recordSettlement(Transaction $transaction, int $grossAmount): void
    {
        if ($transaction->gross_amount !== null) {
            return; // Already settled (idempotent)
        }

        $ownerUserId = $transaction->owner_user_id ?? $transaction->session?->project?->user_id;
        if (! $ownerUserId) {
            return;
        }

        $split = $this->calculateSplit($grossAmount);

        DB::transaction(function () use ($transaction, $split, $ownerUserId) {
            $transaction->update([
                'gross_amount' => $split['gross_amount'],
                'platform_fee' => $split['platform_fee'],
                'owner_amount' => $split['owner_amount'],
            ]);

            $this->aggregateToMonthly($ownerUserId, $transaction->created_at, $split);
        });
    }

    /**
     * Idempotent: upsert monthly_earnings for the transaction's month.
     */
    protected function aggregateToMonthly(int $userId, \DateTimeInterface $date, array $split): void
    {
        $month = $date->format('Y-m');

        $row = MonthlyEarning::firstOrCreate(
            ['user_id' => $userId, 'month' => $month],
            ['total_gross' => 0, 'total_fee' => 0, 'total_net' => 0]
        );

        $row->increment('total_gross', $split['gross_amount']);
        $row->increment('total_fee', $split['platform_fee']);
        $row->increment('total_net', $split['owner_amount']);
    }

    /**
     * Rebuild monthly_earnings from transactions for a user/month.
     * Safe for idempotent re-runs.
     */
    public function rebuildMonthlyEarnings(int $userId, string $month): void
    {
        $rows = Transaction::query()
            ->where('owner_user_id', $userId)
            ->where('type', 'photobooth_session')
            ->where('status', 'paid')
            ->whereYear('created_at', substr($month, 0, 4))
            ->whereMonth('created_at', (int) substr($month, 5, 2))
            ->whereNotNull('gross_amount')
            ->get(['gross_amount', 'platform_fee', 'owner_amount']);

        $totalGross = $rows->sum('gross_amount');
        $totalFee = $rows->sum('platform_fee');
        $totalNet = $rows->sum('owner_amount');

        MonthlyEarning::updateOrCreate(
            ['user_id' => $userId, 'month' => $month],
            [
                'total_gross' => $totalGross,
                'total_fee' => $totalFee,
                'total_net' => $totalNet,
            ]
        );
    }
}
