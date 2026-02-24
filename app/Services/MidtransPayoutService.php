<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Midtrans Payout API (IRIS) – FUTURE USE
 *
 * Untuk disbursement ke rekening bank. Memerlukan onboarding terpisah:
 * https://docs.midtrans.com/reference/getting-started-payouts
 *
 * Saat ini: Manual monthly settlement. IRIS akan diaktifkan setelah
 * business entity (PT/CV) tersedia.
 */
class MidtransPayoutService
{
    protected string $baseUrl;

    protected ?string $clientKey;

    protected ?string $clientSecret;

    protected ?string $partnerId;

    protected ?string $channelId;

    public function __construct()
    {
        $isProduction = config('midtrans.is_production', false);
        $this->baseUrl = $isProduction
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';
        $this->clientKey = config('midtrans.payout_client_key');
        $this->clientSecret = config('midtrans.payout_client_secret');
        $this->partnerId = config('midtrans.payout_partner_id');
        $this->channelId = config('midtrans.payout_channel_id');
    }

    public function isConfigured(): bool
    {
        return $this->clientKey && $this->clientSecret && $this->partnerId && $this->channelId;
    }

    /**
     * Create single payout to bank account.
     *
     * @return array{status: string, reference_no?: string, error?: string}
     */
    public function createPayout(string $beneficiaryName, string $beneficiaryAccount, string $beneficiaryBank, int $amount, string $notes = ''): array
    {
        if (! $this->isConfigured()) {
            return [
                'status' => 'failed',
                'error' => 'Midtrans Payout API belum dikonfigurasi. Silakan hubungi admin untuk menambahkan PAYOUT_* credentials.',
            ];
        }

        $payload = [
            'payouts' => [
                [
                    'beneficiary_name' => $beneficiaryName,
                    'beneficiary_account' => $beneficiaryAccount,
                    'beneficiary_bank' => strtolower($beneficiaryBank),
                    'amount' => number_format($amount, 2, '.', ''),
                    'notes' => substr($notes, 0, 100),
                ],
            ],
        ];

        $response = Http::withHeaders($this->getHeaders())
            ->post("{$this->baseUrl}/api/v1/payouts", $payload);

        $body = $response->json();

        if (! $response->successful()) {
            Log::error('Midtrans Payout API error', [
                'status' => $response->status(),
                'body' => $body,
            ]);
            return [
                'status' => 'failed',
                'error' => $body['error_message'] ?? $body['message'] ?? 'Payout request failed',
            ];
        }

        $payout = $body['payouts'][0] ?? null;
        if (! $payout) {
            return ['status' => 'failed', 'error' => 'Invalid response from Payout API'];
        }

        return [
            'status' => $payout['status'] ?? 'queued',
            'reference_no' => $payout['reference_no'] ?? null,
        ];
    }

    protected function getHeaders(): array
    {
        $timestamp = now()->format('Y-m-d\TH:i:s.v\Z');
        $signature = hash_hmac('sha256', $this->clientKey . $timestamp, $this->clientSecret);

        return [
            'Content-Type' => 'application/json',
            'X-CLIENT-KEY' => $this->clientKey,
            'X-TIMESTAMP' => $timestamp,
            'X-SIGNATURE' => $signature,
            'X-PARTNER-ID' => $this->partnerId,
            'CHANNEL-ID' => $this->channelId,
        ];
    }
}
