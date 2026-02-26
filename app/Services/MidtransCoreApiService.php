<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Midtrans Core API (v2) for QRIS-only integration.
 * No Snap; uses POST /v2/charge and GET /v2/{order_id}/status.
 */
class MidtransCoreApiService
{
    protected string $serverKey;

    protected string $baseUrl;

    public function __construct()
    {
        $this->serverKey = config('midtrans.server_key', '');
        $this->baseUrl = rtrim(config('midtrans.base_url', 'https://api.sandbox.midtrans.com'), '/');
    }

    /**
     * Create QRIS charge via Core API.
     *
     * @param  array{order_id: string, gross_amount: int}  $transactionDetails
     * @return array{status_code?: string, transaction_id?: string, order_id?: string, actions?: array, qr_string?: string, ...}
     */
    public function chargeQris(string $orderId, int $grossAmount, array $customerDetails = []): array
    {
        $payload = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'qris' => [
                'acquirer' => 'gopay',
            ],
        ];

        if (! empty($customerDetails)) {
            $payload['customer_details'] = $customerDetails;
        }

        $response = $this->post('/v2/charge', $payload);

        if (! $response->successful()) {
            throw new \RuntimeException(
                'Midtrans charge failed: ' . ($response->json('status_message') ?? $response->body())
            );
        }

        return $response->json();
    }

    /**
     * Get transaction status from Core API.
     *
     * @return array{transaction_status?: string, order_id?: string, ...}
     */
    public function getStatus(string $orderId): array
    {
        $response = $this->get("/v2/{$orderId}/status");

        if (! $response->successful()) {
            throw new \RuntimeException(
                'Midtrans status check failed: ' . ($response->json('status_message') ?? $response->body())
            );
        }

        return $response->json();
    }

    /**
     * Fetch QR code image from Midtrans (GET full action URL from charge response).
     * Returns raw PNG body or null on failure. Never throws.
     */
    public function fetchQrCodeImage(string $url): ?string
    {
        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->timeout(15)
                ->withOptions(['allow_redirects' => true])
                ->get($url);

            if (! $response->successful()) {
                Log::warning('Midtrans fetchQrCodeImage: non-2xx response', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 200),
                ]);
                return null;
            }

            $body = $response->body();
            $contentType = $response->header('Content-Type', '');

            if (str_contains($contentType, 'application/json')) {
                $data = $response->json();
                return $data['qr_string'] ?? null;
            }

            return $body ?: null;
        } catch (\Throwable $e) {
            Log::warning('Midtrans fetchQrCodeImage: exception', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }
    }

    protected function post(string $path, array $body): Response
    {
        return Http::withBasicAuth($this->serverKey, '')
            ->withHeaders(['Accept' => 'application/json'])
            ->timeout(30)
            ->post($this->baseUrl . $path, $body);
    }

    protected function get(string $path): Response
    {
        return Http::withBasicAuth($this->serverKey, '')
            ->withHeaders(['Accept' => 'application/json'])
            ->timeout(15)
            ->get($this->baseUrl . $path);
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}
