<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\EndOfTermRun;
use App\Models\User;

class EndOfTermRunPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            UserRole::Admin->value,
            UserRole::Proprietor->value,
            UserRole::Teacher->value,
        ], true);
    }

    public function view(User $user, EndOfTermRun $run): bool
    {
        return $this->sameTenant($user, $run) && $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdminOrProprietor($user);
    }

    public function update(User $user, EndOfTermRun $run): bool
    {
        return $this->sameTenant($user, $run) && $this->isAdminOrProprietor($user);
    }

    public function close(User $user, EndOfTermRun $run): bool
    {
        return $this->update($user, $run);
    }

    public function reopen(User $user, EndOfTermRun $run): bool
    {
        return $this->update($user, $run);
    }

    private function isAdminOrProprietor(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin->value, UserRole::Proprietor->value], true);
    }

    private function sameTenant(User $user, EndOfTermRun $run): bool
    {
        return (int) $user->tenant_id === (int) $run->tenant_id;
    }
}
