<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Permission catalogue + role-default map.
 *
 * Design:
 * - Permissions are platform-wide. Each row has a unique `key`
 *   (e.g. "results.manage") and a module tag.
 * - Role defaults live in `role_permissions`. A user gets a permission if:
 *     (a) their role has the permission via role_permissions, OR
 *     (b) they have an explicit grant in user_permissions.
 * - Tenant Admins can grant permissions from a curated whitelist; they
 *   CANNOT grant platform-level SuperAdmin permissions (whitelist excludes
 *   anything not associated with the basic school modules).
 *
 * The seed below preserves existing behavior: Admin/Proprietor get
 * everything school-side; Teacher keeps results.manage but not finance.
 */
class PermissionService
{
    /** All known permission keys grouped by module label. */
    public const CATALOGUE = [
        'students' => ['students.view', 'students.manage'],
        'academics' => ['academics.view', 'academics.manage'],
        'attendance' => ['attendance.view', 'attendance.manage'],
        'results' => ['results.view', 'results.manage'],
        'report_cards' => ['report_cards.view', 'report_cards.bulk_meta', 'report_cards.approve'],
        'finance' => ['finance.view', 'finance.manage'],
        'communication' => ['communication.view', 'communication.send'],
        'staff' => ['staff.view', 'staff.manage'],
        'discipline' => ['discipline.view', 'discipline.manage'],
        'library' => ['library.view', 'library.manage'],
        'inventory' => ['inventory.view', 'inventory.manage'],
        'settings' => ['settings.view', 'settings.manage'],
        'end_of_term' => ['end_of_term.view', 'end_of_term.manage', 'end_of_term.close'],
    ];

    /** Default role → permission key matrix. */
    public const ROLE_DEFAULTS = [
        UserRole::Proprietor->value => '*',
        UserRole::Admin->value => '*',

        UserRole::Accountant->value => [
            'students.view', 'finance.view', 'finance.manage',
            'communication.view', 'communication.send',
            'inventory.view',
        ],

        UserRole::Teacher->value => [
            'students.view', 'academics.view',
            'attendance.view', 'attendance.manage',
            'results.view', 'results.manage',
            'report_cards.view', 'report_cards.bulk_meta',
            'discipline.view', 'discipline.manage',
            'library.view',
        ],

        UserRole::Parent->value => [
            'students.view', 'report_cards.view', 'finance.view',
        ],

        UserRole::Student->value => [
            'report_cards.view', 'finance.view',
        ],

        // New focused roles introduced in Phase 5:
        UserRole::Headteacher->value => [
            'students.view', 'academics.view', 'academics.manage',
            'attendance.view', 'results.view', 'results.manage',
            'report_cards.view', 'report_cards.bulk_meta', 'report_cards.approve',
            'discipline.view', 'discipline.manage',
            'end_of_term.view', 'end_of_term.manage', 'end_of_term.close',
        ],

        UserRole::ExamOfficer->value => [
            'students.view', 'academics.view',
            'results.view', 'results.manage',
            'report_cards.view', 'report_cards.bulk_meta',
            'end_of_term.view',
        ],

        UserRole::AttendanceOfficer->value => [
            'students.view', 'attendance.view', 'attendance.manage',
        ],

        UserRole::Librarian->value => [
            'students.view', 'library.view', 'library.manage',
        ],

        UserRole::InventoryOfficer->value => [
            'inventory.view', 'inventory.manage',
        ],

        UserRole::DisciplineOfficer->value => [
            'students.view', 'discipline.view', 'discipline.manage',
        ],
    ];

    /**
     * Keys that tenant Admins may grant. SuperAdmin permissions (e.g.
     * "platform.manage", anything not in the school-module catalogue) are
     * NOT in here, which is how we prevent privilege escalation.
     *
     * @return array<int, string>
     */
    public function tenantGrantableKeys(): array
    {
        return array_values(array_merge(...array_values(self::CATALOGUE)));
    }

    public function seedDefaults(): void
    {
        DB::transaction(function (): void {
            foreach (self::CATALOGUE as $module => $keys) {
                foreach ($keys as $key) {
                    Permission::query()->updateOrCreate(
                        ['key' => $key],
                        ['module' => $module, 'label' => $key]
                    );
                }
            }

            // Rebuild role defaults from scratch. Use delete() rather than
            // truncate() because TRUNCATE implicitly commits on MySQL/MariaDB,
            // which would break the surrounding DB::transaction(...) wrapper.
            DB::table('role_permissions')->delete();

            $all = Permission::query()->pluck('id', 'key')->all();

            foreach (self::ROLE_DEFAULTS as $role => $keys) {
                if ($keys === '*') {
                    $resolved = array_keys($all);
                } else {
                    $resolved = $keys;
                }
                foreach ($resolved as $key) {
                    if (! isset($all[$key])) {
                        continue;
                    }
                    DB::table('role_permissions')->insert([
                        'role' => $role,
                        'permission_id' => $all[$key],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });
    }

    /** True if the user has the given permission key via role default or explicit grant. */
    public function userCan(User $user, string $key): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        $permission = Permission::query()->where('key', $key)->first();
        if ($permission === null) {
            return false;
        }

        $roleHas = DB::table('role_permissions')
            ->where('role', (string) $user->role)
            ->where('permission_id', $permission->id)
            ->exists();
        if ($roleHas) {
            return true;
        }

        return DB::table('user_permissions')
            ->where('user_id', $user->id)
            ->where('permission_id', $permission->id)
            ->exists();
    }

    /**
     * Replace the explicit grant list for a user. Refuses any key not in
     * tenantGrantableKeys() so a tenant Admin cannot escalate to SuperAdmin.
     *
     * @param  array<int, string>  $keys
     */
    public function syncUserPermissions(User $target, array $keys, User $grantor): void
    {
        $allowed = $this->tenantGrantableKeys();
        $keys = array_values(array_intersect($keys, $allowed));

        $permissionIds = Permission::query()->whereIn('key', $keys)->pluck('id', 'key')->all();

        DB::transaction(function () use ($target, $permissionIds, $grantor): void {
            DB::table('user_permissions')->where('user_id', $target->id)->delete();
            foreach ($permissionIds as $pid) {
                DB::table('user_permissions')->insert([
                    'user_id' => $target->id,
                    'tenant_id' => $target->tenant_id,
                    'permission_id' => $pid,
                    'granted_by_user_id' => $grantor->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
