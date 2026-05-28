<?php

namespace App\Services;

use App\Models\TenantSetting;

/**
 * Tenant-scoped settings access layer.
 *
 * Keys are short, predictable strings (e.g. "default_pass_mark"). Values are
 * JSON-encoded in the database so callers can store ints, bools, strings or
 * small arrays without schema churn. A two-level cache prevents repeated
 * SELECTs inside the same request (each tenant ID is loaded at most once).
 */
class TenantSettings
{
    /**
     * Sane platform-wide defaults. A tenant override always wins; if no
     * override exists for a key, this map is consulted before falling back
     * to the caller-supplied default.
     *
     * @var array<string, mixed>
     */
    public const DEFAULTS = [
        'default_pass_mark' => 50,
        'default_sms_provider' => null,
        'parent_online_payment_enabled' => false,
        'student_online_payment_enabled' => false,
        'default_report_card_grading_mode' => 'WAEC',
        'sender_id' => null,
        'default_invoice_due_days' => 14,
        'default_receipt_footer' => null,
        'default_report_card_footer' => null,
        'default_attendance_days_per_term' => 65,
        'parent_can_view_discipline' => false,
        'tenant_backups_enabled' => true,
        'audit_log_retention_days' => 365,
        'require_2fa_for_admins' => false,
    ];

    /**
     * Keys an Admin/Proprietor may write through the settings UI. Anything
     * outside this list is silently dropped — useful as a safety net.
     *
     * @var array<int, string>
     */
    public const MANAGED_KEYS = [
        'default_pass_mark',
        'default_sms_provider',
        'parent_online_payment_enabled',
        'student_online_payment_enabled',
        'default_report_card_grading_mode',
        'sender_id',
        'default_invoice_due_days',
        'default_receipt_footer',
        'default_report_card_footer',
        'default_attendance_days_per_term',
        'parent_can_view_discipline',
        'tenant_backups_enabled',
        'audit_log_retention_days',
        'require_2fa_for_admins',
    ];

    /** @var array<int, array<string, mixed>> */
    private array $cache = [];

    public function all(int $tenantId): array
    {
        if (! isset($this->cache[$tenantId])) {
            $rows = TenantSetting::query()
                ->where('tenant_id', $tenantId)
                ->get(['key', 'value']);
            $this->cache[$tenantId] = $rows->mapWithKeys(fn ($row) => [$row->key => $row->value])->toArray();
        }

        return $this->cache[$tenantId];
    }

    public function get(int $tenantId, string $key, mixed $default = null): mixed
    {
        $all = $this->all($tenantId);
        if (array_key_exists($key, $all)) {
            return $all[$key];
        }

        if (array_key_exists($key, self::DEFAULTS)) {
            return self::DEFAULTS[$key];
        }

        return $default;
    }

    public function set(int $tenantId, string $key, mixed $value): void
    {
        TenantSetting::query()->updateOrCreate(
            ['tenant_id' => $tenantId, 'key' => $key],
            ['value' => $value],
        );
        unset($this->cache[$tenantId]);
    }

    /**
     * Bulk set, dropping any keys not in MANAGED_KEYS as a safety net.
     *
     * @param  array<string, mixed>  $values
     */
    public function setMany(int $tenantId, array $values): void
    {
        foreach ($values as $key => $value) {
            if (! in_array($key, self::MANAGED_KEYS, true)) {
                continue;
            }
            $this->set($tenantId, $key, $value);
        }
    }
}
