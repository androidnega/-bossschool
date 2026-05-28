<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\InventoryItem;
use App\Models\User;

class InventoryItemPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            UserRole::Admin->value,
            UserRole::Proprietor->value,
            UserRole::Accountant->value,
        ], true);
    }

    public function view(User $user, InventoryItem $item): bool
    {
        return $this->sameTenant($user, $item) && $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdminOrProprietor($user);
    }

    public function update(User $user, InventoryItem $item): bool
    {
        return $this->sameTenant($user, $item) && $this->isAdminOrProprietor($user);
    }

    public function delete(User $user, InventoryItem $item): bool
    {
        return $this->sameTenant($user, $item) && $this->isAdminOrProprietor($user);
    }

    /** Only Admin/Proprietor can move stock; Accountant can view valuation but not adjust. */
    public function move(User $user, InventoryItem $item): bool
    {
        return $this->sameTenant($user, $item) && $this->isAdminOrProprietor($user);
    }

    private function isAdminOrProprietor(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin->value, UserRole::Proprietor->value], true);
    }

    private function sameTenant(User $user, InventoryItem $item): bool
    {
        return (int) $user->tenant_id === (int) $item->tenant_id;
    }
}
