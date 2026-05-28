<?php

namespace App\Models;

use App\Models\Concerns\RecordsAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Backup catalogue row. NOT tenant-scoped on read — SuperAdmin needs to see
 * all backups across tenants, and Proprietor/Admin queries pass tenant_id
 * explicitly. We deliberately use plain `Model` instead of `BaseModel` for
 * this reason; tenant isolation is enforced in the controller/policy layer.
 */
class TenantBackup extends Model
{
    use RecordsAuditTrail;

    public const TYPE_FULL = 'full_tenant';

    public const TYPE_ACADEMICS = 'academics';

    public const TYPE_FINANCE = 'finance';

    public const TYPE_STUDENTS = 'students';

    public const TYPE_SETTINGS = 'settings';

    public const TYPES = [
        self::TYPE_FULL,
        self::TYPE_ACADEMICS,
        self::TYPE_FINANCE,
        self::TYPE_STUDENTS,
        self::TYPE_SETTINGS,
    ];

    public const STATUS_PENDING = 'pending';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_RESTORED = 'restored';

    /** Backup metadata is fine to log; the underlying file content is not loaded into audit. */
    public array $auditHidden = [];

    protected $fillable = [
        'tenant_id',
        'backup_type',
        'file_path',
        'file_disk',
        'size_bytes',
        'checksum',
        'status',
        'created_by_user_id',
        'restored_by_user_id',
        'restored_at',
        'failure_reason',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'metadata' => 'array',
            'restored_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by_user_id');
    }
}
