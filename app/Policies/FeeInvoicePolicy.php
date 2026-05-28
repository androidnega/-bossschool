<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\FeeInvoice;
use App\Models\User;

/**
 * Access rules:
 *   Admin / Proprietor / Accountant -> full read + write
 *   Teacher                         -> denied (finance is not their job)
 *   Parent                          -> read invoices for linked children
 *   Student                         -> read their own invoices
 */
class FeeInvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasFinanceAccess($user)
            || in_array($user->role, [UserRole::Parent->value, UserRole::Student->value], true);
    }

    public function view(User $user, FeeInvoice $invoice): bool
    {
        if (! $this->sameTenant($user, $invoice)) {
            return false;
        }

        if ($this->hasFinanceAccess($user)) {
            return true;
        }

        if ($user->role === UserRole::Parent->value) {
            return $user->children()->where('students.id', $invoice->student_id)->exists();
        }

        if ($user->role === UserRole::Student->value) {
            return (int) $user->student_id === (int) $invoice->student_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->hasFinanceAccess($user);
    }

    public function update(User $user, FeeInvoice $invoice): bool
    {
        if (! $this->sameTenant($user, $invoice)) {
            return false;
        }

        if (! $this->hasFinanceAccess($user)) {
            return false;
        }

        // Cancelled invoices are frozen — re-open via a dedicated action.
        return $invoice->status !== FeeInvoice::STATUS_CANCELLED;
    }

    public function delete(User $user, FeeInvoice $invoice): bool
    {
        if (! $this->sameTenant($user, $invoice)) {
            return false;
        }

        // Don't soft-delete invoices that already have payments — they must
        // be cancelled instead so the audit trail stays clean.
        if ($invoice->amount_paid > 0) {
            return false;
        }

        return $this->hasFinanceAccess($user);
    }

    public function cancel(User $user, FeeInvoice $invoice): bool
    {
        if (! $this->sameTenant($user, $invoice)) {
            return false;
        }

        return $this->hasFinanceAccess($user);
    }

    private function sameTenant(User $user, FeeInvoice $invoice): bool
    {
        return (int) $user->tenant_id === (int) $invoice->tenant_id;
    }

    private function hasFinanceAccess(User $user): bool
    {
        return in_array($user->role, [
            UserRole::Admin->value,
            UserRole::Proprietor->value,
            UserRole::Accountant->value,
        ], true);
    }
}
