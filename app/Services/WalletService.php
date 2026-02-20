<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletService
{
    /**
     * Credit owner's wallet when photobooth_session payment succeeds.
     * WAJIB pakai DB transaction.
     */
    public function creditOwner(int $userId, int $amount, ?Transaction $transaction = null): void
    {
        DB::transaction(function () use ($userId, $amount, $transaction) {
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $userId],
                ['balance' => 0, 'pending_balance' => 0]
            );

            $wallet->increment('balance', $amount);

            WalletTransaction::create([
                'user_id' => $userId,
                'transaction_id' => $transaction?->order_id,
                'type' => 'credit',
                'amount' => $amount,
                'description' => 'Photobooth session payment',
                'reference_type' => $transaction ? Transaction::class : null,
                'reference_id' => $transaction?->id,
            ]);
        });
    }

    /**
     * Debit wallet for withdrawal. Used inside WithdrawalService.
     */
    public function debitForWithdrawal(int $userId, int $amount, string $withdrawalId, string $description): void
    {
        DB::transaction(function () use ($userId, $amount, $withdrawalId, $description) {
            $wallet = Wallet::where('user_id', $userId)->lockForUpdate()->first();

            if (! $wallet || $wallet->balance < $amount) {
                throw new \InvalidArgumentException('Saldo tidak cukup');
            }

            $wallet->decrement('balance', $amount);

            WalletTransaction::create([
                'user_id' => $userId,
                'type' => 'debit',
                'amount' => $amount,
                'description' => $description,
                'reference_type' => \App\Models\Withdrawal::class,
                'reference_id' => $withdrawalId,
            ]);
        });
    }
}
