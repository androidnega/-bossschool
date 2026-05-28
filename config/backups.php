<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tenant backup storage
    |--------------------------------------------------------------------------
    |
    | Backups are written as JSON files to the disk below. The path is the
    | top-level directory under the disk root (a per-tenant subdirectory is
    | always added). Set BACKUP_DISK=s3 (or any custom disk) in production.
    |
    */
    'disk' => env('BACKUP_DISK', 'local'),

    'path' => env('BACKUP_PATH', 'tenant-backups'),

    /*
    |--------------------------------------------------------------------------
    | Safety
    |--------------------------------------------------------------------------
    |
    | "reset_requires_backup" forces every destructive reset (web UI + CLI)
    | to refuse unless the tenant has at least one completed backup within
    | the past N seconds.
    |
    */
    'reset_requires_backup' => (bool) env('BACKUP_REQUIRED_FOR_RESET', true),

    'reset_backup_max_age_seconds' => (int) env('BACKUP_MAX_AGE_FOR_RESET', 60 * 60 * 24), // 24h
];
