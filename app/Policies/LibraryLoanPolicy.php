<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\LibraryLoan;
use App\Models\User;

class LibraryLoanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->tenant_id !== null;
    }

    public function view(User $user, LibraryLoan $loan): bool
    {
        if ((int) $user->tenant_id !== (int) $loan->tenant_id) {
            return false;
        }

        if ($this->isAdminOrProprietor($user) || $user->role === UserRole::Teacher->value) {
            return true;
        }

        if ($user->role === UserRole::Student->value) {
            return (int) $user->student_id === (int) $loan->student_id;
        }

        if ($user->role === UserRole::Parent->value && $loan->student_id) {
            return $user->children()->where('students.id', $loan->student_id)->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->isAdminOrProprietor($user);
    }

    public function update(User $user, LibraryLoan $loan): bool
    {
        return (int) $user->tenant_id === (int) $loan->tenant_id && $this->isAdminOrProprietor($user);
    }

    private function isAdminOrProprietor(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin->value, UserRole::Proprietor->value], true);
    }
}
