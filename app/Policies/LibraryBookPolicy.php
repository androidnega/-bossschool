<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\LibraryBook;
use App\Models\User;

class LibraryBookPolicy
{
    public function viewAny(User $user): bool
    {
        // Books are discoverable by everyone in the school.
        return $user->tenant_id !== null;
    }

    public function view(User $user, LibraryBook $book): bool
    {
        return $this->sameTenant($user, $book) && $user->tenant_id !== null;
    }

    public function create(User $user): bool
    {
        return $this->isAdminOrProprietor($user);
    }

    public function update(User $user, LibraryBook $book): bool
    {
        return $this->sameTenant($user, $book) && $this->isAdminOrProprietor($user);
    }

    public function delete(User $user, LibraryBook $book): bool
    {
        return $this->sameTenant($user, $book) && $this->isAdminOrProprietor($user);
    }

    private function isAdminOrProprietor(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin->value, UserRole::Proprietor->value], true);
    }

    private function sameTenant(User $user, LibraryBook $book): bool
    {
        return (int) $user->tenant_id === (int) $book->tenant_id;
    }
}
