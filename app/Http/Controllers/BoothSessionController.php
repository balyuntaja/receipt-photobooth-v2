<?php

namespace App\Http\Controllers;

use App\Enums\MediaTypeEnum;
use App\Enums\TransactionStatusEnum;
use App\Models\BoothSession;
use App\Models\Media;
use App\Models\SessionPreference;
use App\Models\Transaction;
use App\Models\Voucher;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * REST API for booth session operations.
 * Used by kiosk JavaScript to save frame, update state, and upload media.
 */
class BoothSessionController extends Controller
{
    /**
     * Save selected frame for session.
     * Validates frame belongs to project.
     */
    public function saveFrame(Request $request, BoothSession $session): JsonResponse
    {
        $validated = $request->validate([
            'frame_id' => ['required', 'integer', Rule::exists('frames', 'id')],
        ]);

        $project = $session->project;
        $frameIds = $project->frames()
            ->wherePivot('is_active', true)
            ->where('frames.is_active', true)
            ->pluck('frames.id');

        if (!$frameIds->contains($validated['frame_id'])) {
            return response()->json(['message' => 'Invalid frame for this project'], 422);
        }

        $session->update(['frame_id' => $validated['frame_id']]);

        return response()->json([
            'success' => true,
            'frame_id' => $session->frame_id,
        ]);
    }

    /**
     * Update session state (e.g. status).
     */
    public function update(Request $request, BoothSession $session): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['sometimes', 'string', Rule::in(['in_progress', 'completed', 'cancelled'])],
        ]);

        if (isset($validated['status'])) {
            $session->update(['status' => $validated['status']]);
        }

        return response()->json([
            'success' => true,
            'session_id' => $session->id,
        ]);
    }

    /**
     * Save captured media (photo or photostrip).
     * Accepts base64 data URL or multipart file.
     */
    public function saveMedia(Request $request, BoothSession $session): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(['image', 'strip'])],
            'data' => ['required_without:file', 'string'], // base64 data URL
            'file' => ['required_without:data', 'file', 'mimes:png,jpg,jpeg', 'max:10240'],
            'index' => ['sometimes', 'integer', 'min:1'], // photo index (1, 2, 3) or omit for strip
        ]);

        $type = $validated['type'] === 'strip' ? MediaTypeEnum::STRIP : MediaTypeEnum::IMAGE;
        $index = $validated['index'] ?? null;

        $path = $this->storeMediaFile($validated, $session);

        if (!$path) {
            return response()->json(['message' => 'Failed to store media'], 500);
        }

        $media = Media::create([
            'session_id' => $session->id,
            'type' => $type,
            'file_path' => $path,
        ]);

        return response()->json([
            'success' => true,
            'media_id' => $media->id,
            'path' => $path,
        ]);
    }

    /**
     * Validate voucher and return discount info (does not consume voucher).
     * Used by kiosk to show amount_after_discount: 100% → applyVoucher + FRAME, &lt;100% → createPayment with voucher + Snap.
     */
    public function validateVoucher(Request $request, BoothSession $session): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255'],
            'copy_count' => ['sometimes', 'integer', 'min:1'],
        ]);
        $copyCount = max(1, (int) ($validated['copy_count'] ?? 1));
        $project = $session->project;
        $setting = $project->setting;
        $voucher = Voucher::where('code', $validated['code'])
            ->where('user_id', $project->user_id)
            ->first();
        if (! $voucher) {
            return response()->json(['valid' => false, 'message' => 'Voucher tidak ditemukan'], 422);
        }
        if ($voucher->expires_at && $voucher->expires_at->isPast()) {
            return response()->json(['valid' => false, 'message' => 'Voucher sudah kadaluarsa'], 422);
        }
        if ((int) ($voucher->quota ?? 1) < 1) {
            return response()->json(['valid' => false, 'message' => 'Voucher sudah habis'], 422);
        }
        if ($session->transaction()->exists()) {
            return response()->json(['valid' => false, 'message' => 'Session sudah memiliki transaksi'], 422);
        }

        $totalBefore = (int) round($setting->getPriceForCopies($copyCount));
        $discountAmount = $this->computeVoucherDiscount($voucher, $totalBefore);
        $amountAfterDiscount = max(0, $totalBefore - $discountAmount);

        return response()->json([
            'valid' => true,
            'total_before' => $totalBefore,
            'discount_amount' => $discountAmount,
            'amount_after_discount' => $amountAfterDiscount,
        ]);
    }

    private function computeVoucherDiscount(Voucher $voucher, int $totalBefore): int
    {
        $type = $voucher->type ?? 'fixed';
        $value = (int) ($voucher->value ?? 0);
        if ($type === 'percent') {
            return (int) round($totalBefore * min(100, max(0, $value)) / 100);
        }
        return min($totalBefore, $value);
    }

    /**
     * Create Midtrans Snap payment for session (QRIS etc). Creates pending transaction and returns snap_token.
     * Accepts copy_count and optional voucher_code; if voucher_code given, amount = amount_after_discount and voucher consumed on payment success.
     */
    public function createPayment(Request $request, BoothSession $session): JsonResponse
    {
        $project = $session->project;
        $setting = $project->setting;
        $copyCount = max(1, (int) ($request->input('copy_count', 1)));
        $voucherCode = $request->input('voucher_code') ? trim((string) $request->input('voucher_code')) : null;

        $amount = (int) round($setting->getPriceForCopies($copyCount));
        $voucher = null;
        if ($voucherCode) {
            $voucher = Voucher::where('code', $voucherCode)->where('user_id', $project->user_id)->first();
            if (! $voucher) {
                return response()->json(['message' => 'Voucher tidak ditemukan'], 422);
            }
            if ($voucher->expires_at && $voucher->expires_at->isPast()) {
                return response()->json(['message' => 'Voucher sudah kadaluarsa'], 422);
            }
            if ((int) ($voucher->quota ?? 1) < 1) {
                return response()->json(['message' => 'Voucher sudah habis'], 422);
            }
            $discount = $this->computeVoucherDiscount($voucher, $amount);
            $amount = max(0, $amount - $discount);
        }

        if ($amount <= 0) {
            return response()->json(['message' => 'Session is free or fully discounted; use apply-voucher for 100% discount'], 422);
        }
        if ($session->transaction()->exists()) {
            $trx = $session->transaction;
            if ($trx->status === TransactionStatusEnum::PAID) {
                return response()->json(['message' => 'Already paid', 'snap_token' => null, 'order_id' => $trx->order_id], 200);
            }
            if ($trx->status === TransactionStatusEnum::PENDING && $trx->order_id) {
                try {
                    $owner = $project->user;
                    $snapToken = app(MidtransService::class)->createTransaction([
                        'transaction_details' => [
                            'order_id' => $trx->order_id,
                            'gross_amount' => $trx->amount,
                        ],
                        'customer_details' => [
                            'first_name' => $owner?->name ?? 'Booth',
                            'email' => $owner?->email ?? 'booth@kiosk.local',
                        ],
                    ]);
                    return response()->json(['snap_token' => $snapToken, 'order_id' => $trx->order_id]);
                } catch (\Throwable $e) {
                    return response()->json(['message' => 'Payment error: ' . $e->getMessage()], 500);
                }
            }
        }

        $orderId = 'BOOTH-' . $session->id . '-' . time();
        $trxId = 'trx_' . $session->id . '_' . time();

        Transaction::create([
            'id' => $trxId,
            'order_id' => $orderId,
            'session_id' => $session->id,
            'owner_user_id' => $project->user_id,
            'voucher_id' => $voucher?->id,
            'amount' => $amount,
            'status' => TransactionStatusEnum::PENDING,
            'type' => 'photobooth_session',
        ]);

        $this->saveSessionCopyCount($session, $copyCount);

        try {
            $owner = $project->user;
            $snapToken = app(MidtransService::class)->createTransaction([
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $amount,
                ],
                'customer_details' => [
                    'first_name' => $owner?->name ?? 'Booth',
                    'email' => $owner?->email ?? 'booth@kiosk.local',
                ],
            ]);
            return response()->json(['snap_token' => $snapToken, 'order_id' => $orderId]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Payment error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Apply voucher code for session. Validates voucher belongs to project owner and is unused.
     */
    public function applyVoucher(Request $request, BoothSession $session): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255'],
            'copy_count' => ['sometimes', 'integer', 'min:1'],
        ]);
        $copyCount = max(1, (int) ($validated['copy_count'] ?? 1));
        $project = $session->project;
        $voucher = Voucher::where('code', $validated['code'])
            ->where('user_id', $project->user_id)
            ->first();
        if (!$voucher) {
            return response()->json(['message' => 'Voucher tidak ditemukan'], 422);
        }
        if ($voucher->expires_at && $voucher->expires_at->isPast()) {
            return response()->json(['message' => 'Voucher sudah kadaluarsa'], 422);
        }
        $quota = (int) ($voucher->quota ?? 1);
        if ($quota < 1) {
            return response()->json(['message' => 'Voucher sudah habis'], 422);
        }
        if ($session->transaction()->exists()) {
            return response()->json(['message' => 'Session sudah memiliki transaksi'], 422);
        }

        if ($quota === 1) {
            $voucher->delete();
        } else {
            $voucher->decrement('quota');
        }
        $orderId = 'VOUCHER-' . $session->id . '-' . time();
        $trx = Transaction::create([
            'id' => 'trx_' . $session->id . '_' . time(),
            'order_id' => $orderId,
            'session_id' => $session->id,
            'owner_user_id' => $project->user_id,
            'amount' => 0,
            'status' => TransactionStatusEnum::PAID,
            'type' => 'photobooth_session',
        ]);
        app(\App\Services\SettlementService::class)->recordSettlement($trx, 0);
        $this->saveSessionCopyCount($session, $copyCount);

        return response()->json(['success' => true]);
    }

    /**
     * Confirm free session (price = 0). Creates PAID transaction so user can proceed to frame selection.
     */
    public function confirmFree(Request $request, BoothSession $session): JsonResponse
    {
        $project = $session->project;
        $setting = $project->setting;
        $amount = (float) ($setting->price_per_session ?? 0);
        if ($amount > 0) {
            return response()->json(['message' => 'Session is not free'], 422);
        }
        $copyCount = max(1, (int) ($request->input('copy_count', 1)));
        if ($session->transaction()->exists()) {
            $this->saveSessionCopyCount($session, $copyCount);
            return response()->json(['success' => true]);
        }
        $orderId = 'FREE-' . $session->id . '-' . time();
        $trx = Transaction::create([
            'id' => 'trx_' . $session->id . '_' . time(),
            'order_id' => $orderId,
            'session_id' => $session->id,
            'owner_user_id' => $project->user_id,
            'amount' => 0,
            'status' => TransactionStatusEnum::PAID,
            'type' => 'photobooth_session',
        ]);
        app(\App\Services\SettlementService::class)->recordSettlement($trx, 0);
        $this->saveSessionCopyCount($session, $copyCount);
        return response()->json(['success' => true]);
    }

    private function saveSessionCopyCount(BoothSession $session, int $copyCount): void
    {
        $session->preference()->updateOrCreate(
            ['session_id' => $session->id],
            ['copy_count' => $copyCount]
        );
    }

    /**
     * Get media files for a session (for QR / softfile page).
     */
    public function getMedia(BoothSession $session): JsonResponse
    {
        $media = $session->media()->get()->map(function (Media $m) {
            return [
                'id' => $m->id,
                'type' => $m->type->value,
                'url' => Storage::url($m->file_path),
            ];
        });

        return response()->json([
            'success' => true,
            'files' => $media,
        ]);
    }

    private function storeMediaFile(array $validated, BoothSession $session): ?string
    {
        $sessionDir = "booth/{$session->id}";

        if (isset($validated['file'])) {
            $file = $validated['file'];
            $ext = $file->getClientOriginalExtension() ?: 'png';
            $name = ($validated['type'] ?? 'photo') . '-' . ($validated['index'] ?? 'strip') . '-' . time() . '.' . $ext;
            return $file->storeAs($sessionDir, $name, 'public');
        }

        if (isset($validated['data'])) {
            $data = $validated['data'];
            if (!preg_match('/^data:image\/(\w+);base64,/', $data, $m)) {
                return null;
            }
            $ext = $m[1] === 'jpeg' ? 'jpg' : $m[1];
            $base64 = substr($data, strpos($data, ',') + 1);
            $contents = base64_decode($base64);
            if ($contents === false) {
                return null;
            }
            $name = ($validated['type'] ?? 'photo') . '-' . ($validated['index'] ?? 'strip') . '-' . time() . '.' . $ext;
            $fullPath = $sessionDir . '/' . $name;
            Storage::disk('public')->put($fullPath, $contents);
            return $fullPath;
        }

        return null;
    }
}
