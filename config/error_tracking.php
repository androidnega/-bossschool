<?php

/*
| Optional external error tracking. Filled in by the operator at deploy
| time. The app itself does NOT depend on these — they're consumed by the
| Sentry/Bugsnag SDKs only when an operator chooses to install them.
*/

return [
    'sentry' => [
        'dsn' => env('SENTRY_DSN'),
        'environment' => env('SENTRY_ENVIRONMENT', env('APP_ENV', 'production')),
        'traces_sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', 0.0),
    ],

    'bugsnag' => [
        'api_key' => env('BUGSNAG_API_KEY'),
        'release_stage' => env('BUGSNAG_RELEASE_STAGE', env('APP_ENV', 'production')),
    ],
];
