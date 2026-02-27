<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatusEnum;
use App\Models\Transaction;
use App\Models\Voucher;
use App\Services\SettlementService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Notification;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $orderId = $request->input('order_id');
        $transactionStatus = $request->input('transaction_status');
        $paymentType = $request->input('payment_type');
        $statusCode = $request->input('status_code');
        $grossAmount = $request->input('gross_amount');
        $signatureKey = $request->input('signature_key');

        // 1. Signature validation (WAJIB di production)
        $serverKey = config('midtrans.server_key');
        if (config('midtrans.validate_webhook_signature') && $serverKey && $orderId && $statusCode !== null && $grossAmount !== null && $signatureKey) {
            $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

            if (! hash_equals($expectedSignature, $signatureKey)) {
                Log::warning('Midtrans webhook: invalid signature', ['order_id' => $orderId]);
                return response()->json(['message' => 'Invalid signature'], 403);
            }
        }

        // RAW payload untuk debug (simpan persis apa yang Midtrans kirim)
        $rawPayload = $request->all();

        // Try Notification class (calls Midtrans API); fallback to raw request if it fails
        try {
            $notif = new Notification();
            $transactionStatus = $notif->transaction_status ?? $transactionStatus;
            $orderId = $notif->order_id ?? $orderId;
            $paymentType = $notif->payment_type ?? $paymentType;
        } catch (\Throwable $e) {
            Log::warning('Midtrans Notification API fallback to raw payload', [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
            ]);
        }

        $transaction = Transaction::where('order_id', $orderId)->first();

        if (! $transaction) {
            Log::info('Midtrans notification: unknown order_id', ['order_id' => $orderId]);
            return response()->json(['message' => 'ok']);
        }

        // 2. Anti double process (IDEMPOTENT)
        if ($transaction->status === TransactionStatusEnum::PAID) {
            Log::info('Midtrans notification: already processed', ['order_id' => $orderId]);
            return response()->json(['message' => 'Already processed']);
        }

        try {
            DB::transaction(function () use ($transaction, $transactionStatus, $paymentType, $rawPayload, $orderId) {
                if ($transactionStatus === 'capture' || $transactionStatus === 'settlement') {
                    $transaction->status = 'paid';
                } elseif ($transactionStatus === 'pending') {
                    $transaction->status = 'pending';
                } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
                    $transaction->status = 'failed';
                }

                $transaction->payment_type = $paymentType;
                $transaction->payload = $rawPayload;
                $transaction->save();

                // Manual Monthly Settlement: record gross/platform_fee/owner_amount, aggregate to monthly_earnings.
                // NO wallet credit (instant withdraw disabled). Payout is manual by admin.
                if ($transaction->type === 'photobooth_session' && $transaction->status === TransactionStatusEnum::PAID) {
                    $grossAmount = (int) ($rawPayload['gross_amount'] ?? $transaction->amount ?? 0);
                    if ($grossAmount > 0) {
                        app(SettlementService::class)->recordSettlement($transaction, $grossAmount);
                        Log::info('Settlement recorded for photobooth session', ['order_id' => $orderId]);
                    }
                    if ($transaction->voucher_id) {
                        $voucher = Voucher::find($transaction->voucher_id);
                        if ($voucher) {
                            $vid = $voucher->id;
                            $quota = (int) ($voucher->quota ?? 1);
                            if ($quota <= 1) {
                                $voucher->delete();
                            } else {
                                $voucher->decrement('quota');
                            }
                            Log::info('Voucher consumed after payment', ['order_id' => $orderId, 'voucher_id' => $vid]);
                        }
                    }
                }
            });
        } catch (\Throwable $e) {
            Log::error('Midtrans webhook processing error', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'ack']);
        }

        return response()->json(['message' => 'ok']);
    }
}
