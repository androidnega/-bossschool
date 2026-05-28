<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Fee;
use App\Models\Message;
use App\Models\Payment;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Subscription;
use App\Models\Term;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TenantOperationalResetService
{
    /**
     * Remove operational school data for one tenant. Returns a summary report
     * containing per-table row counts and the snapshot path so the action can
     * be audited and (if necessary) manually reversed.
     *
     * Keeps: tenant, school row, users, plans, subscriptions (payment_id is
     * cleared first because payments are deleted below).
     *
     * @return array{snapshot_path: ?string, counts: array<string, int>, tenant_id: int}
     */
    public function purgeTenantOperationalData(int $tenantId, bool $takeSnapshot = true): array
    {
        $snapshotPath = $takeSnapshot ? $this->snapshotTenant($tenantId) : null;

        $counts = DB::transaction(function () use ($tenantId): array {
            $counts = [];

            // Clear payment_id on subscriptions before deleting payments.
            Subscription::query()->where('tenant_id', $tenantId)->update(['payment_id' => null]);

            $counts['messages'] = $this->hardPurge(Message::class, $tenantId);
            $counts['attendance'] = $this->hardPurge(Attendance::class, $tenantId);
            $counts['results'] = $this->hardPurge(Result::class, $tenantId);
            $counts['payments'] = $this->hardPurge(Payment::class, $tenantId);
            $counts['fees'] = $this->hardPurge(Fee::class, $tenantId);
            $counts['subjects'] = $this->hardPurge(Subject::class, $tenantId);

            $counts['parent_student'] = (int) DB::table('parent_student')->where('tenant_id', $tenantId)->delete();
            $counts['teacher_class'] = (int) DB::table('teacher_class')->where('tenant_id', $tenantId)->delete();
            $counts['teacher_subject'] = (int) DB::table('teacher_subject')->where('tenant_id', $tenantId)->delete();

            User::query()->where('tenant_id', $tenantId)->update(['student_id' => null]);

            $counts['students'] = $this->hardPurge(Student::class, $tenantId);
            $counts['staff'] = $this->hardPurge(Staff::class, $tenantId);
            $counts['classes'] = $this->hardPurge(SchoolClass::class, $tenantId);
            $counts['terms'] = $this->hardPurge(Term::class, $tenantId);

            return $counts;
        });

        return [
            'tenant_id' => $tenantId,
            'snapshot_path' => $snapshotPath,
            'counts' => $counts,
        ];
    }

    /**
     * Purge operational data for every tenant. Each tenant is purged inside
     * its own transaction and its own snapshot. Returns a list of per-tenant
     * summaries.
     *
     * @return array<int, array{tenant_id: int, snapshot_path: ?string, counts: array<string, int>}>
     */
    public function purgeAllTenantsOperationalData(bool $takeSnapshot = true): array
    {
        $ids = DB::table('tenants')->pluck('id');
        $summaries = [];

        foreach ($ids as $id) {
            $summaries[] = $this->purgeTenantOperationalData((int) $id, $takeSnapshot);
        }

        return $summaries;
    }

    /**
     * Permanently remove every row for the tenant. Uses force-delete + trashed
     * scope so soft-deleted rows are also wiped. Falls back to plain delete()
     * for models that don't (yet) use SoftDeletes. Returns rows affected.
     *
     * @param  class-string<Model>  $modelClass
     */
    private function hardPurge(string $modelClass, int $tenantId): int
    {
        $usesSoftDeletes = in_array(SoftDeletes::class, class_uses_recursive($modelClass), true);

        $query = $modelClass::query()->where('tenant_id', $tenantId);

        if ($usesSoftDeletes) {
            return (int) $query->withTrashed()->forceDelete();
        }

        return (int) $query->delete();
    }

    /**
     * Write a JSON snapshot of every operational row that is about to be
     * deleted for the given tenant. The snapshot is stored on the configured
     * disk so it can be downloaded by ops if a restore is needed.
     */
    public function snapshotTenant(int $tenantId): string
    {
        $tables = [
            'messages',
            'attendance',
            'results',
            'payments',
            'fees',
            'subjects',
            'parent_student',
            'teacher_class',
            'teacher_subject',
            'students',
            'staff',
            'classes',
            'terms',
        ];

        $data = [];
        foreach ($tables as $table) {
            if (DB::getSchemaBuilder()->hasColumn($table, 'tenant_id')) {
                $data[$table] = DB::table($table)->where('tenant_id', $tenantId)->get()->all();
            }
        }

        $disk = (string) config('platform.snapshot_disk', 'local');
        $dir = (string) config('platform.snapshot_path', 'platform/reset-snapshots');
        $filename = $dir.'/tenant-'.$tenantId.'-'.now()->format('Ymd-His').'-'.bin2hex(random_bytes(4)).'.json';

        Storage::disk($disk)->put(
            $filename,
            json_encode([
                'tenant_id' => $tenantId,
                'created_at' => now()->toIso8601String(),
                'rows' => $data,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        return $filename;
    }
}
