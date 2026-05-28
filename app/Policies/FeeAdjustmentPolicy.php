<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\FeeAdjustment;
use App\Models\User;

/**
 * Accountant can request adjustments and cancel their own pending ones.
 * Admin / Proprietor decide (approve / reject) and can also create new
 * adjustments outright. Teachers / Parents / Students have no access.
 */
class FeeAdjustmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->financeRole($user);
    }

    public function view(User $user, FeeAdjustment $adj): bool
    {
        return $this->sameTenant($user, $adj) && $this->financeRole($user);
    }

    public function create(User $user): bool
    {
        return $this->financeRole($user);
    }

    public function update(User $user, FeeAdjustment $adj): bool
    {
        if (! $this->sameTenant($user, $adj)) {
            return false;
        }

        // Only the row's creator can edit while it's still pending.
        if ($adj->status !== FeeAdjustment::STATUS_PENDING) {
            return false;
        }

        return $this->financeRole($user)
            && (int) $adj->created_by_user_id === (int) $user->id;
    }

    public function decide(User $user, FeeAdjustment $adj): bool
    {
        if (! $this->sameTenant($user, $adj)) {
            return false;
        }

        return in_array($user->role, [UserRole::Admin->value, UserRole::Proprietor->value], true);
    }

    public function cancel(User $user, FeeAdjustment $adj): bool
    {
        if (! $this->sameTenant($user, $adj)) {
            return false;
        }

        // Creator can cancel while pending; Admin/Proprietor can always cancel.
        if (in_array($user->role, [UserRole::Admin->value, UserRole::Proprietor->value], true)) {
            return true;
        }

        return $adj->status === FeeAdjustment::STATUS_PENDING
            && (int) $adj->created_by_user_id === (int) $user->id;
    }

    private function financeRole(User $user): bool
    {
        return in_array($user->role, [
            UserRole::Admin->value,
            UserRole::Proprietor->value,
            UserRole::Accountant->value,
        ], true);
    }

    private function sameTenant(User $user, FeeAdjustment $adj): bool
    {
        return (int) $user->tenant_id === (int) $adj->tenant_id;
    }
}
