<?php

return [
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION'),
    'is_sanitazed' => env('MIDTRANS_IS_SANITIZED'),
    'is3ds' => env('MIDTRANS_IS_3DS'),
    'validate_webhook_signature' => env('MIDTRANS_VALIDATE_WEBHOOK_SIGNATURE', true),

    // Platform fee (percentage 0-100, applied to gross_amount)
    'platform_fee_percent' => (float) env('PLATFORM_FEE_PERCENT', 10),

    // Payout/Disbursement (IRIS) - FUTURE USE, butuh onboarding terpisah
    'payout_client_key' => env('MIDTRANS_PAYOUT_CLIENT_KEY'),
    'payout_client_secret' => env('MIDTRANS_PAYOUT_CLIENT_SECRET'),
    'payout_partner_id' => env('MIDTRANS_PAYOUT_PARTNER_ID'),
    'payout_channel_id' => env('MIDTRANS_PAYOUT_CHANNEL_ID'),
];
