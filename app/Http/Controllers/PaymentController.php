<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatusEnum;
use App\Models\BoothSession;
use App\Models\Transaction;
use App\Services\MidtransCoreApiService;
use App\Services\SettlementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Custom QRIS payment page and status (Core API, no Snap).
 */
class PaymentController extends Controller
{
    /**
     * Show custom QRIS pay page (fullscreen, centered, large QR).
     * Expects ?order_id= in query. Validates transaction belongs to session.
     */
    public function showPayPage(Request $request, BoothSession $session): View|JsonResponse|RedirectResponse
    {
        $orderId = $request->query('order_id');
        if (! $orderId) {
            abort(404, 'Missing order_id');
        }

        $transaction = Transaction::where('session_id', $session->id)
            ->where('order_id', $orderId)
            ->first();

        if (! $transaction) {
            abort(404, 'Transaction not found');
        }

        if ($transaction->status === TransactionStatusEnum::PAID) {
            return redirect()->route('booth.session.continue', [$session]);
        }

        $amount = (int) $transaction->amount;
        $project = $session->project;
        $paymentStatusUrl = url()->route('booth.session.payment-status', [$session]) . '?order_id=' . urlencode($orderId);
        $continueUrl = url()->route('booth.session.continue', [$session]);

        // Prefer qr_code_url (proxy from Midtrans) — no imagick/gd required. Only use qr_string if no URL.
        $qrImageDataUrl = null;
        $qrImageUrl = null;
        if ($transaction->qr_code_url) {
            $qrImageUrl = route('booth.session.qris-image', [$session]) . '?order_id=' . urlencode($orderId);
        } elseif (! empty(trim((string) $transaction->qr_string))) {
            try {
                $png = app('qrcode')->format('png')->size(320)->margin(1)->generate(trim((string) $transaction->qr_string));
                $qrImageDataUrl = 'data:image/png;base64,' . base64_encode((string) $png);
            } catch (\Throwable $e) {
                Log::warning('QR generate from qr_string failed', ['order_id' => $orderId, 'error' => $e->getMessage()]);
            }
        }

        $isSandbox = ! config('midtrans.is_production');
        $simulatorQrCodeUrl = $isSandbox ? ($transaction->qr_code_url ?? '') : '';

        return view('booth.pay-qris', [
            'session' => $session,
            'transaction' => $transaction,
            'orderId' => $orderId,
            'amount' => $amount,
            'amountFormatted' => 'Rp ' . number_format($amount, 0, ',', '.'),
            'paymentStatusUrl' => $paymentStatusUrl,
            'continueUrl' => $continueUrl,
            'qrImageDataUrl' => $qrImageDataUrl,
            'qrImageUrl' => $qrImageUrl,
            'projectName' => $project->name ?? 'Photobooth',
            'isSandbox' => $isSandbox,
            'simulatorQrCodeUrl' => $simulatorQrCodeUrl,
            'simulatorPageUrl' => 'https://simulator.sandbox.midtrans.com/v2/qris/index',
        ]);
    }

    /**
     * Check payment status (for polling). GET ?order_id=.
     * If Midtrans transaction_status == 'settlement', update local transaction to PAID and return status: 'paid'.
     */
    public function checkStatus(Request $request, BoothSession $session): JsonResponse
    {
        $orderId = $request->query('order_id');
        if (! $orderId) {
            return response()->json(['status' => 'error', 'message' => 'Missing order_id'], 400);
        }

        $transaction = Transaction::where('session_id', $session->id)
            ->where('order_id', $orderId)
            ->first();

        if (! $transaction) {
            return response()->json(['status' => 'error', 'message' => 'Transaction not found'], 404);
        }

        if ($transaction->status === TransactionStatusEnum::PAID) {
            return response()->json([
                'status' => 'paid',
                'redirect_url' => url()->route('booth.session.continue', [$session]),
            ]);
        }

        try {
            $coreApi = app(MidtransCoreApiService::class);
            $data = $coreApi->getStatus($orderId);
        } catch (\Throwable $e) {
            Log::warning('Midtrans status check failed', ['order_id' => $orderId, 'error' => $e->getMessage()]);
            return response()->json(['status' => 'pending']);
        }

        $transactionStatus = $data['transaction_status'] ?? '';

        if (in_array($transactionStatus, ['settlement', 'capture'], true)) {
            $transaction->update(['status' => TransactionStatusEnum::PAID]);
            app(SettlementService::class)->recordSettlement($transaction, (int) $transaction->amount);

            if ($transaction->voucher_id) {
                $voucher = $transaction->voucher;
                if ($voucher) {
                    $quota = (int) ($voucher->quota ?? 1);
                    if ($quota <= 1) {
                        $voucher->delete();
                    } else {
                        $voucher->decrement('quota');
                    }
                    Log::info('Voucher consumed after payment', ['order_id' => $orderId, 'voucher_id' => $transaction->voucher_id]);
                }
            }

            return response()->json([
                'status' => 'paid',
                'redirect_url' => url()->route('booth.session.continue', [$session]),
            ]);
        }

        return response()->json(['status' => 'pending']);
    }

    /**
     * Stream QR code image (proxy from Midtrans or generate from qr_string).
     */
    public function qrisImage(Request $request, BoothSession $session): SymfonyResponse|StreamedResponse|JsonResponse
    {
        try {
            $orderId = $request->query('order_id');
            if (! $orderId) {
                return response()->json(['message' => 'Missing order_id'], 400);
            }

            $transaction = Transaction::where('session_id', $session->id)
                ->where('order_id', $orderId)
                ->first();

            if (! $transaction) {
                return response()->json(['message' => 'Transaction not found'], 404);
            }

            // Prefer qr_code_url (proxy from Midtrans) — no imagick/gd. Only generate from qr_string if no URL.
            if ($transaction->qr_code_url) {
                $coreApi = app(MidtransCoreApiService::class);
                $body = $coreApi->fetchQrCodeImage($transaction->qr_code_url);
                if ($body !== null && $body !== '') {
                    return response($body, 200, [
                        'Content-Type' => 'image/png',
                        'Cache-Control' => 'no-store',
                    ]);
                }
                Log::warning('qrisImage: fetchQrCodeImage returned empty', ['order_id' => $orderId]);
            }

            if (! empty(trim((string) $transaction->qr_string))) {
                try {
                    $png = app('qrcode')->format('png')->size(320)->margin(1)->generate(trim((string) $transaction->qr_string));
                    $content = $png instanceof \Illuminate\Support\HtmlString ? $png->toHtml() : (string) $png;
                    if ($content === '') {
                        throw new \RuntimeException('Empty QR output');
                    }
                    return response($content, 200, [
                        'Content-Type' => 'image/png',
                        'Cache-Control' => 'no-store',
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('qrisImage: QR generate failed', ['order_id' => $orderId, 'error' => $e->getMessage()]);
                    return response()->json(['message' => 'QR generation failed'], 500);
                }
            }

            return response()->json(['message' => 'No QR data'], 404);
        } catch (\Throwable $e) {
            Log::error('qrisImage: unexpected error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Server error loading QR'], 500);
        }
    }
}
