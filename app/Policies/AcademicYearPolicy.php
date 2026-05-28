<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\User;

class AcademicYearPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canManageSetup($user)
            || in_array($user->role, [UserRole::Teacher->value, UserRole::Accountant->value], true);
    }

    public function view(User $user, AcademicYear $year): bool
    {
        return $this->sameTenant($user, $year) && $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->canManageSetup($user);
    }

    public function update(User $user, AcademicYear $year): bool
    {
        return $this->sameTenant($user, $year) && $this->canManageSetup($user);
    }

    public function delete(User $user, AcademicYear $year): bool
    {
        return $this->sameTenant($user, $year) && $this->canManageSetup($user);
    }

    public function setCurrent(User $user, AcademicYear $year): bool
    {
        return $this->update($user, $year);
    }

    private function sameTenant(User $user, AcademicYear $year): bool
    {
        return (int) $user->tenant_id === (int) $year->tenant_id;
    }

    private function canManageSetup(User $user): bool
    {
        return in_array($user->role, [
            UserRole::Admin->value,
            UserRole::Proprietor->value,
        ], true);
    }
}
