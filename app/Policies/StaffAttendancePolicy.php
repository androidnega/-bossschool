<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\StaffAttendance;
use App\Models\User;

class StaffAttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isAdminOrProprietor($user);
    }

    public function view(User $user, StaffAttendance $row): bool
    {
        return $this->sameTenant($user, $row) && $this->isAdminOrProprietor($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdminOrProprietor($user);
    }

    public function update(User $user, StaffAttendance $row): bool
    {
        return $this->sameTenant($user, $row) && $this->isAdminOrProprietor($user);
    }

    private function isAdminOrProprietor(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin->value, UserRole::Proprietor->value], true);
    }

    private function sameTenant(User $user, StaffAttendance $row): bool
    {
        return (int) $user->tenant_id === (int) $row->tenant_id;
    }
}
