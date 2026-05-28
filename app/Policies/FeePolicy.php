<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Fee;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class FeePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasFinanceAccess($user);
    }

    public function view(User $user, Fee $fee): bool
    {
        return $this->sameTenant($user, $fee) && $this->hasFinanceAccess($user);
    }

    public function create(User $user): bool
    {
        return $this->canManageFinance($user);
    }

    public function update(User $user, Fee $fee): bool
    {
        return $this->sameTenant($user, $fee) && $this->canManageFinance($user);
    }

    public function delete(User $user, Fee $fee): bool
    {
        return $this->sameTenant($user, $fee) && $this->canManageFinance($user);
    }

    private function sameTenant(User $user, Model $model): bool
    {
        return (int) $user->tenant_id === (int) $model->getAttribute('tenant_id');
    }

    private function hasFinanceAccess(User $user): bool
    {
        return in_array($user->role, [
            UserRole::Admin->value,
            UserRole::Proprietor->value,
            UserRole::Accountant->value,
        ], true);
    }

    private function canManageFinance(User $user): bool
    {
        return in_array($user->role, [
            UserRole::Admin->value,
            UserRole::Proprietor->value,
            UserRole::Accountant->value,
        ], true);
    }
}
