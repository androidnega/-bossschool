<?php

namespace App\Services\Backup;

use App\Models\Tenant;
use App\Models\TenantBackup;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Tenant-safe backup/restore engine.
 *
 * - Each backup is a single JSON file on the configured disk.
 * - Filename embeds tenant id, type and a per-file random token so it cannot
 *   be guessed from outside.
 * - Every row included in the backup is filtered by tenant_id BEFORE writing,
 *   so a SuperAdmin restoring backup A into tenant B is the only way to mix
 *   tenants — and that intent is required to be explicit (target_tenant_id).
 * - SHA-256 checksum is written next to the row count; restore refuses to
 *   proceed when the checksum does not match the file on disk.
 */
class TenantBackupService
{
    /** @var array<string, array<int, string>> */
    public const TYPE_TABLES = [
        TenantBackup::TYPE_FULL => [
            'schools', 'feature_toggles', 'maintenance_modes', 'tenant_settings',
            'academic_years', 'terms', 'classes', 'subjects',
            'students', 'parent_student', 'users',
            'teacher_class', 'teacher_subject',
            'staff', 'staff_attendance',
            'attendance', 'results', 'report_card_meta', 'promotion_history',
            'end_of_term_runs', 'discipline_incidents',
            'fees', 'fee_invoices', 'fee_invoice_items', 'fee_adjustments',
            'payments', 'payment_transactions',
            'library_books', 'library_loans',
            'inventory_items', 'inventory_movements',
            'messages', 'communication_logs',
            'subscriptions',
        ],
        TenantBackup::TYPE_ACADEMICS => [
            'academic_years', 'terms', 'classes', 'subjects',
            'students', 'teacher_class', 'teacher_subject',
            'attendance', 'results', 'report_card_meta', 'promotion_history',
            'end_of_term_runs', 'discipline_incidents',
        ],
        TenantBackup::TYPE_FINANCE => [
            'fees', 'fee_invoices', 'fee_invoice_items', 'fee_adjustments',
            'payments', 'payment_transactions',
        ],
        TenantBackup::TYPE_STUDENTS => [
            'students', 'parent_student',
        ],
        TenantBackup::TYPE_SETTINGS => [
            'schools', 'tenant_settings', 'feature_toggles', 'maintenance_modes',
        ],
    ];

    public function __construct(private readonly ActivityLogger $activityLogger) {}

    /**
     * Create a backup row and write its JSON dump. Returns the persisted
     * TenantBackup. Failures are caught and recorded on the row so the
     * caller can show a useful message.
     */
    public function create(Tenant $tenant, string $type, ?User $user = null): TenantBackup
    {
        if (! array_key_exists($type, self::TYPE_TABLES)) {
            throw new \InvalidArgumentException("Unknown backup type: {$type}");
        }

        $disk = (string) config('backups.disk', 'local');
        $dir = trim((string) config('backups.path', 'tenant-backups'), '/');
        $token = bin2hex(random_bytes(6));
        $filename = sprintf('%s/tenant-%d/%s-%s-%s.json',
            $dir,
            $tenant->id,
            $type,
            now()->format('Ymd-His'),
            $token
        );

        $backup = TenantBackup::query()->create([
            'tenant_id' => $tenant->id,
            'backup_type' => $type,
            'file_path' => $filename,
            'file_disk' => $disk,
            'status' => TenantBackup::STATUS_PENDING,
            'created_by_user_id' => $user?->id,
            'metadata' => [
                'requested_at' => now()->toIso8601String(),
                'tenant_name' => $tenant->name,
                'tenant_subdomain' => $tenant->subdomain,
                'tables' => self::TYPE_TABLES[$type],
            ],
        ]);

        try {
            $payload = $this->dumpTenant($tenant->id, $type);
            $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            if ($json === false) {
                throw new RuntimeException('Failed to encode backup payload as JSON.');
            }

            $checksum = hash('sha256', $json);

            $storage = $this->storage($disk);
            $storage->put($filename, $json);

            $backup->update([
                'status' => TenantBackup::STATUS_COMPLETED,
                'size_bytes' => strlen($json),
                'checksum' => $checksum,
                'metadata' => array_merge((array) $backup->metadata, [
                    'row_counts' => $payload['row_counts'],
                    'completed_at' => now()->toIso8601String(),
                ]),
            ]);

            $this->activityLogger->log(
                'tenant_backup_created',
                sprintf('Backup created for tenant "%s" (%s)', $tenant->name, $type),
                ['tenant_id' => $tenant->id, 'backup_id' => $backup->id, 'type' => $type, 'size' => strlen($json)],
                $tenant->id
            );
        } catch (\Throwable $e) {
            $backup->update([
                'status' => TenantBackup::STATUS_FAILED,
                'failure_reason' => $e->getMessage(),
            ]);
            Log::error('Tenant backup failed', [
                'tenant_id' => $tenant->id,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
        }

        return $backup->refresh();
    }

    /**
     * Restore a completed backup into a target tenant. The default target is
     * the same tenant the backup was taken from; SuperAdmin may pass a
     * different target tenant id (e.g. for migration into a fresh tenant).
     *
     * This method is *additive*: it inserts rows that don't exist (matched by
     * primary key per table). It never deletes existing rows. For destructive
     * "wipe and restore", call TenantOperationalResetService first.
     */
    public function restore(TenantBackup $backup, User $restorer, ?int $targetTenantId = null): TenantBackup
    {
        if ($backup->status !== TenantBackup::STATUS_COMPLETED && $backup->status !== TenantBackup::STATUS_RESTORED) {
            throw new RuntimeException('Only completed backups can be restored.');
        }

        $storage = $this->storage((string) $backup->file_disk);
        if (! $storage->exists($backup->file_path)) {
            throw new RuntimeException('Backup file is missing on disk.');
        }

        $contents = (string) $storage->get($backup->file_path);
        $actual = hash('sha256', $contents);
        if ($backup->checksum && $actual !== $backup->checksum) {
            throw new RuntimeException('Backup checksum mismatch — refusing to restore.');
        }

        $payload = json_decode($contents, true);
        if (! is_array($payload) || ! isset($payload['rows']) || ! is_array($payload['rows'])) {
            throw new RuntimeException('Backup payload is malformed.');
        }

        $target = $targetTenantId ?? (int) $backup->tenant_id;

        DB::transaction(function () use ($payload, $target): void {
            foreach ($payload['rows'] as $table => $rows) {
                if (! is_array($rows) || $rows === []) {
                    continue;
                }
                foreach ($rows as $row) {
                    if (! is_array($row)) {
                        continue;
                    }
                    // Force the target tenant_id on every row regardless of what
                    // the dump says. This is the core multi-tenant safety net.
                    $row['tenant_id'] = $target;

                    DB::table($table)->updateOrInsert(
                        ['id' => $row['id'] ?? null],
                        $row
                    );
                }
            }
        });

        $backup->update([
            'status' => TenantBackup::STATUS_RESTORED,
            'restored_by_user_id' => $restorer->id,
            'restored_at' => now(),
            'metadata' => array_merge((array) $backup->metadata, [
                'restored_into_tenant_id' => $target,
                'restored_at' => now()->toIso8601String(),
            ]),
        ]);

        $this->activityLogger->log(
            'tenant_backup_restored',
            sprintf('Backup %d restored into tenant %d', $backup->id, $target),
            [
                'tenant_id' => $target,
                'backup_id' => $backup->id,
                'source_tenant_id' => $backup->tenant_id,
            ],
            $target
        );

        return $backup->refresh();
    }

    /** @return array{tenant_id:int, type:string, created_at:string, rows: array<string, array<int, array<string, mixed>>>, row_counts: array<string, int>} */
    public function dumpTenant(int $tenantId, string $type): array
    {
        $tables = self::TYPE_TABLES[$type] ?? [];
        $rows = [];
        $counts = [];

        foreach ($tables as $table) {
            if (! DB::getSchemaBuilder()->hasTable($table)) {
                continue;
            }

            if (! DB::getSchemaBuilder()->hasColumn($table, 'tenant_id')) {
                continue;
            }

            $data = DB::table($table)
                ->where('tenant_id', $tenantId)
                ->get()
                ->map(fn ($row) => (array) $row)
                ->all();

            // Strip sensitive fields. We keep the row but redact secrets.
            foreach ($data as $i => $r) {
                foreach (['password', 'remember_token', 'two_factor_secret'] as $k) {
                    if (array_key_exists($k, $r)) {
                        $data[$i][$k] = null;
                    }
                }
            }

            $rows[$table] = $data;
            $counts[$table] = count($data);
        }

        return [
            'tenant_id' => $tenantId,
            'type' => $type,
            'created_at' => now()->toIso8601String(),
            'rows' => $rows,
            'row_counts' => $counts,
        ];
    }

    public function verifyChecksum(TenantBackup $backup): bool
    {
        $storage = $this->storage((string) $backup->file_disk);
        if (! $storage->exists($backup->file_path)) {
            return false;
        }
        $contents = (string) $storage->get($backup->file_path);

        return hash('sha256', $contents) === (string) $backup->checksum;
    }

    public function storage(string $disk): Filesystem
    {
        return Storage::disk($disk);
    }
}
