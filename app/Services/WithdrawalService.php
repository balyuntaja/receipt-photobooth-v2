<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserBankAccount;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Withdrawal Service – IRIS/Disbursement DISABLED
 *
 * Saat ini: Manual monthly settlement. Payout dilakukan admin setiap akhir bulan.
 * IRIS (Midtrans Payout) akan diaktifkan setelah business entity tersedia.
 * Dengan demikian withdraw() akan throw; tidak ada automatic payout.
 */
class WithdrawalService
{
    public function __construct(
        protected WalletService $walletService,
        protected MidtransPayoutService $payoutService
    ) {}

    protected int $minimumAmount = 10000; // Rp 10.000

    /**
     * IRIS DISABLED: Manual monthly settlement only.
     * Throws immediately. NO automatic disbursement.
     */
    public function withdraw(User $user, int $amount, UserBankAccount $bankAccount): Withdrawal
    {
        // IRIS disabled – manual monthly settlement only. NO automatic disbursement.
        throw ValidationException::withMessages([
            'amount' => ['Pencairan dilakukan setiap akhir bulan ke rekening terdaftar. Tidak ada penarikan instan.'],
        ]);

        // FUTURE: Uncomment below when IRIS is enabled (business entity ready).
        // if ($amount < $this->minimumAmount) { ... }
        // $existingProcessing = Withdrawal::where(...)->exists(); ...
        // return DB::transaction(function () { ... $this->payoutService->createPayout(...); ... });
    }

    public function getMinimumAmount(): int
    {
        return $this->minimumAmount;
    }
}
