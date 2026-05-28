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
        return $this->hasAcademicStaffRole($user);
    }

    public function view(User $user, Student $student): bool
    {
        if (! $this->sameTenant($user, $student)) {
            return false;
        }

        if ($user->role === UserRole::Parent->value) {
            return $user->isGuardianOf($student);
        }

        if ($user->role === UserRole::Student->value) {
            return (int) $user->student_id === (int) $student->id;
        }

        if (! $this->hasAcademicStaffRole($user)) {
            return false;
        }

        if ($user->role === UserRole::Teacher->value) {
            return $user->teachesStudent($student);
        }

        return true;
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

    /**
     * Accountants run the finance side and should not browse student profiles.
     * Only academic staff (Admin / Proprietor / Teacher) can view students.
     */
    private function hasAcademicStaffRole(User $user): bool
    {
        return in_array($user->role, [
            UserRole::Admin->value,
            UserRole::Proprietor->value,
            UserRole::Teacher->value,
        ], true);
    }

    private function canManageStudents(User $user): bool
    {
        return in_array($user->role, [
            UserRole::Admin->value,
            UserRole::Proprietor->value,
        ], true);
    }
}
