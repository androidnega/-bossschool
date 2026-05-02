<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasStaffRole($user);
    }

    public function view(User $user, Student $student): bool
    {
        return $this->sameTenant($user, $student) && $this->hasStaffRole($user);
    }

    public function create(User $user): bool
    {
        return $this->canManageStudents($user);
    }

    public function update(User $user, Student $student): bool
    {
        return $this->sameTenant($user, $student) && $this->canManageStudents($user);
    }

    public function delete(User $user, Student $student): bool
    {
        return $this->sameTenant($user, $student)
            && in_array($user->role, [UserRole::Admin->value, UserRole::Proprietor->value], true);
    }

    private function sameTenant(User $user, Model $model): bool
    {
        return (int) $user->tenant_id === (int) $model->getAttribute('tenant_id');
    }

    private function hasStaffRole(User $user): bool
    {
        return in_array($user->role, [
            UserRole::Admin->value,
            UserRole::Proprietor->value,
            UserRole::Accountant->value,
            UserRole::Teacher->value,
        ], true);
    }

    private function canManageStudents(User $user): bool
    {
        return in_array($user->role, [
            UserRole::Admin->value,
            UserRole::Proprietor->value,
            UserRole::Teacher->value,
        ], true);
    }
}
