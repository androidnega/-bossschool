<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\User;

class AttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            UserRole::Admin->value,
            UserRole::Proprietor->value,
            UserRole::Teacher->value,
        ], true);
    }

    public function view(User $user, Attendance $attendance): bool
    {
        if ((int) $user->tenant_id !== (int) $attendance->tenant_id) {
            return false;
        }

        if ($this->isAdminOrProprietor($user)) {
            return true;
        }

        if ($user->role === UserRole::Teacher->value) {
            return (bool) $attendance->student && $user->teachesStudent($attendance->student);
        }

        return false;
    }

    public function markForClass(User $user, SchoolClass $class): bool
    {
        if ((int) $user->tenant_id !== (int) $class->tenant_id) {
            return false;
        }

        if ($this->isAdminOrProprietor($user)) {
            return true;
        }

        if ($user->role === UserRole::Teacher->value) {
            return $user->assignedClasses()->where('classes.id', $class->id)->exists();
        }

        return false;
    }

    private function isAdminOrProprietor(User $user): bool
    {
        return in_array($user->role, [
            UserRole::Admin->value,
            UserRole::Proprietor->value,
        ], true);
    }
}
