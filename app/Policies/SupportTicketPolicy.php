<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\SupportTicket;
use App\Models\User;

/**
 * SupportTicket access rules:
 *
 * - SuperAdmin can do anything on any ticket.
 * - A tenant user can view + reply to tickets where ticket.tenant_id ===
 *   user.tenant_id AND they are the creator OR an Admin/Proprietor of the
 *   same tenant.
 * - Internal notes can only be created by SuperAdmin (platform support).
 * - Tickets from another tenant are completely invisible.
 * - Closed tickets cannot be replied to.
 */
class SupportTicketPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SupportTicket $ticket): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($ticket->tenant_id !== (int) $user->tenant_id) {
            return false;
        }

        if ((int) $ticket->created_by_user_id === (int) $user->id) {
            return true;
        }

        return in_array((string) $user->role, [UserRole::Admin->value, UserRole::Proprietor->value], true);
    }

    public function create(User $user): bool
    {
        // Tenants can create their own tickets. Students/Parents are
        // intentionally not allowed to file support tickets during the
        // pilot (they channel feedback via their school admin).
        return $user->isSuperAdmin() || in_array(
            (string) $user->role,
            [UserRole::Admin->value, UserRole::Proprietor->value, UserRole::Headteacher->value,
             UserRole::Accountant->value, UserRole::Teacher->value],
            true
        );
    }

    public function reply(User $user, SupportTicket $ticket): bool
    {
        if ($ticket->status === 'closed') {
            return false;
        }

        return $this->view($user, $ticket);
    }

    public function addInternalNote(User $user, SupportTicket $ticket): bool
    {
        return $user->isSuperAdmin();
    }

    public function changeStatus(User $user, SupportTicket $ticket): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $this->view($user, $ticket)
            && in_array((string) $user->role, [UserRole::Admin->value, UserRole::Proprietor->value], true);
    }

    public function downloadAttachment(User $user, SupportTicket $ticket): bool
    {
        return $this->view($user, $ticket);
    }
}
