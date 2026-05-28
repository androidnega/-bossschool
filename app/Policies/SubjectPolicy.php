<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class SubjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canViewAcademics($user);
    }

    public function view(User $user, Subject $subject): bool
    {
        if (! $this->sameTenant($user, $subject)) {
            return false;
        }

        if (! $this->canViewAcademics($user)) {
            return false;
        }

        if ($user->role === UserRole::Teacher->value) {
            return $user->teachesSubjectId((int) $subject->id);
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $this->canManageSubjects($user);
    }

    public function update(User $user, Subject $subject): bool
    {
        if (! $this->sameTenant($user, $subject)) {
            return false;
        }

        // Teachers can mark results for their subjects but must not be able
        // to rename / re-class the subject definition itself - that is an
        // Admin / Proprietor concern.
        return $this->canManageSubjects($user);
    }

    public function delete(User $user, Subject $subject): bool
    {
        if (! $this->sameTenant($user, $subject)) {
            return false;
        }

        if ($user->role === UserRole::Teacher->value) {
            return false;
        }

        return $this->canManageSubjects($user);
    }

    private function sameTenant(User $user, Model $model): bool
    {
        return (int) $user->tenant_id === (int) $model->getAttribute('tenant_id');
    }

    private function canViewAcademics(User $user): bool
    {
        return in_array($user->role, [
            UserRole::Admin->value,
            UserRole::Proprietor->value,
            UserRole::Teacher->value,
        ], true);
    }

    private function canManageSubjects(User $user): bool
    {
        return in_array($user->role, [
            UserRole::Admin->value,
            UserRole::Proprietor->value,
        ], true);
    }
}
