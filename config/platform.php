<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Reset tool safety
    |--------------------------------------------------------------------------
    |
    | The platform "Reset Tool" is destructive: it deletes a tenant's
    | operational data (results, payments, fees, attendance, messages, etc.).
    |
    | - "allow_reset_all" controls whether the "Reset all schools" action is
    |   reachable from the web UI. In production we recommend leaving this
    |   off and instead using `php artisan tinker` (or a future CLI command)
    |   to perform global resets while connected to a database backup.
    |
    | - "snapshot_path" is the storage directory used to write a JSON
    |   snapshot of every row that is about to be deleted, so the reset can
    |   be reviewed and (manually) restored if needed.
    |
    */

    'allow_reset_all' => (bool) env('PLATFORM_ALLOW_RESET_ALL', false),

    'snapshot_disk' => env('PLATFORM_RESET_SNAPSHOT_DISK', 'local'),

    'snapshot_path' => env('PLATFORM_RESET_SNAPSHOT_PATH', 'platform/reset-snapshots'),

    /*
    | Demo provisioning safety
    |
    | Tenant provisioning can optionally seed a small Ghana-themed demo data
    | set and demo accounts. To prevent shipping demo accounts to live pilot
    | schools, both flags are OFF by default and ignored in production unless
    | "demo_allowed_in_prod" is explicitly set true via env.
    */
    'demo_allowed_in_prod' => (bool) env('DEMO_ALLOWED_IN_PROD', false),
];
