<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\TenantBackup;
use App\Models\User;
use App\Services\TenantSettings;

class TenantBackupPolicy
{
    public function __construct(private readonly TenantSettings $tenantSettings) {}

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $this->isTenantOwner($user);
    }

    public function view(User $user, TenantBackup $backup): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        if (! $this->isTenantOwner($user)) {
            return false;
        }

        return (int) $user->tenant_id === (int) $backup->tenant_id
            && $this->tenantBackupAllowed((int) $backup->tenant_id);
    }

    public function download(User $user, TenantBackup $backup): bool
    {
        return $this->view($user, $backup);
    }

    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        if (! $this->isTenantOwner($user)) {
            return false;
        }

        return $this->tenantBackupAllowed((int) $user->tenant_id);
    }

    /**
     * Only SuperAdmin can restore a backup; this protects against a
     * compromised tenant Admin overwriting current data with stale rows.
     */
    public function restore(User $user, TenantBackup $backup): bool
    {
        return $user->isSuperAdmin();
    }

    private function isTenantOwner(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin->value, UserRole::Proprietor->value], true);
    }

    private function tenantBackupAllowed(int $tenantId): bool
    {
        return (bool) $this->tenantSettings->get($tenantId, 'tenant_backups_enabled', true);
    }
}
