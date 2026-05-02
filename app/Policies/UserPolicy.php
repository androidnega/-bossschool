<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isAdminOrProprietor($user);
    }

    public function view(User $user, User $model): bool
    {
        return $this->sameTenant($user, $model)
            && ($this->isAdminOrProprietor($user) || $user->id === $model->id);
    }

    public function create(User $user): bool
    {
        return $this->isAdminOrProprietor($user);
    }

    public function update(User $user, User $model): bool
    {
        if (! $this->sameTenant($user, $model)) {
            return false;
        }

        if ($user->id === $model->id) {
            return true;
        }

        return $this->isAdminOrProprietor($user);
    }

    public function delete(User $user, User $model): bool
    {
        if (! $this->sameTenant($user, $model) || $user->id === $model->id) {
            return false;
        }

        return $user->role === UserRole::Admin->value;
    }

    private function sameTenant(User $user, Model $model): bool
    {
        return (int) $user->tenant_id === (int) $model->getAttribute('tenant_id');
    }

    private function isAdminOrProprietor(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin->value, UserRole::Proprietor->value], true);
    }
}
