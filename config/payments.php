<?php

/*
| Ghana payment-gateway provider configuration. Phase 3 prepares the wiring
| only — no real charges are made yet. Operators set the secrets per
| environment via env vars; missing/blank secrets cause webhook signature
| verification to fail closed.
*/

return [
    'providers' => [
        'hubtel' => [
            'enabled' => env('PAYMENTS_HUBTEL_ENABLED', false),
            'secret' => env('PAYMENTS_HUBTEL_SECRET'),
            'client_id' => env('PAYMENTS_HUBTEL_CLIENT_ID'),
        ],
        'paystack' => [
            'enabled' => env('PAYMENTS_PAYSTACK_ENABLED', false),
            'secret' => env('PAYMENTS_PAYSTACK_SECRET'),
        ],
        'flutterwave' => [
            'enabled' => env('PAYMENTS_FLUTTERWAVE_ENABLED', false),
            'secret' => env('PAYMENTS_FLUTTERWAVE_SECRET'),
        ],
        'expresspay' => [
            'enabled' => env('PAYMENTS_EXPRESSPAY_ENABLED', false),
            'secret' => env('PAYMENTS_EXPRESSPAY_SECRET'),
            'merchant_id' => env('PAYMENTS_EXPRESSPAY_MERCHANT_ID'),
        ],
    ],
];
