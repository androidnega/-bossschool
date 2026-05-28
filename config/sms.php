<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default SMS provider key
    |--------------------------------------------------------------------------
    |
    | The dispatcher uses this provider unless a tenant overrides it via
    | tenant_settings.default_sms_provider. Allowed keys match the keys of
    | the `providers` array below.
    |
    */
    'default' => env('SMS_DEFAULT_PROVIDER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Global sender ID and sandbox flag
    |--------------------------------------------------------------------------
    |
    | sender_id is the alphanumeric ID that recipients see (e.g. "MYSCHOOL").
    | Most Ghana providers require a pre-approved sender ID — keep this in
    | env, never in the database.
    |
    | sandbox=true forces the dispatcher to write communication_logs as
    | "sent" without actually contacting the provider. Tests rely on this.
    |
    */
    'sender_id' => env('SMS_SENDER_ID', 'MYSCHOOL'),
    'sandbox' => (bool) env('SMS_SANDBOX', false),

    /*
    |--------------------------------------------------------------------------
    | Bill via internal SMS credits
    |--------------------------------------------------------------------------
    |
    | When true, the dispatcher debits one credit from `tenants.sms_credits_balance`
    | for every SMS it sends (and refunds on provider failure). When false,
    | the dispatcher behaves the way it did before the credit system existed
    | — every send hits the upstream provider regardless of balance. Tests
    | turn this off by default; the dedicated credit-billing test re-enables
    | it for a single tenant.
    |
    */
    'bill_via_credits' => (bool) env('SMS_BILL_VIA_CREDITS', true),

    /*
    |--------------------------------------------------------------------------
    | Providers
    |--------------------------------------------------------------------------
    |
    | Each provider is keyed by name and may be individually enabled/disabled
    | so a tenant can swap providers without code changes. Credentials live
    | only in env vars.
    |
    */
    'providers' => [

        'log' => [
            'class' => \App\Services\Sms\LogSmsProvider::class,
            'enabled' => true,
        ],

        'hubtel' => [
            'class' => \App\Services\Sms\HubtelSmsProvider::class,
            'enabled' => (bool) env('SMS_HUBTEL_ENABLED', false),
            'client_id' => env('SMS_HUBTEL_CLIENT_ID'),
            'client_secret' => env('SMS_HUBTEL_CLIENT_SECRET'),
            'sender_id' => env('SMS_HUBTEL_SENDER_ID', env('SMS_SENDER_ID')),
            'endpoint' => env('SMS_HUBTEL_ENDPOINT', 'https://smsc.hubtel.com/v1/messages/send'),
        ],

        'mnotify' => [
            'class' => \App\Services\Sms\MNotifySmsProvider::class,
            'enabled' => (bool) env('SMS_MNOTIFY_ENABLED', false),
            'api_key' => env('SMS_MNOTIFY_API_KEY'),
            'sender_id' => env('SMS_MNOTIFY_SENDER_ID', env('SMS_SENDER_ID')),
            'endpoint' => env('SMS_MNOTIFY_ENDPOINT', 'https://api.mnotify.com/api/sms/quick'),
        ],

        'arkesel' => [
            'class' => \App\Services\Sms\ArkeselSmsProvider::class,
            'enabled' => (bool) env('SMS_ARKESEL_ENABLED', false),
            'api_key' => env('SMS_ARKESEL_API_KEY'),
            'sender_id' => env('SMS_ARKESEL_SENDER_ID', env('SMS_SENDER_ID')),
            'endpoint' => env('SMS_ARKESEL_ENDPOINT', 'https://sms.arkesel.com/api/v2/sms/send'),
        ],

        'twilio' => [
            'class' => \App\Services\Sms\TwilioSmsProvider::class,
            'enabled' => (bool) env('SMS_TWILIO_ENABLED', false),
            'account_sid' => env('SMS_TWILIO_ACCOUNT_SID'),
            'auth_token' => env('SMS_TWILIO_AUTH_TOKEN'),
            'from' => env('SMS_TWILIO_FROM'),
        ],

    ],

];
