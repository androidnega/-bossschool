<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            UserRole::Admin->value,
            UserRole::Proprietor->value,
            UserRole::Accountant->value,
            UserRole::Teacher->value,
        ], true);
    }

    public function view(User $user, Message $message): bool
    {
        if ($message->tenant_id === null) {
            return $user->isSuperAdmin();
        }

        if ((int) $user->tenant_id !== (int) $message->tenant_id) {
            return false;
        }

        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [
            UserRole::Admin->value,
            UserRole::Proprietor->value,
        ], true);
    }

    public function manage(User $user): bool
    {
        return $this->create($user);
    }

    public function sendFeeReminder(User $user): bool
    {
        return $user->role === UserRole::Accountant->value;
    }

    public function sendClassNotice(User $user): bool
    {
        return $user->role === UserRole::Teacher->value;
    }
}
