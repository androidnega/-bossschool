<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\FeeInvoice;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PaymentTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isFinanceRole() || $this->isParentOrStudent($user);
    }

    public function view(User $user, PaymentTransaction $transaction): bool
    {
        if (! $this->sameTenant($user, $transaction)) {
            return false;
        }

        if ($user->isFinanceRole()) {
            return true;
        }

        if ($user->role === UserRole::Parent->value) {
            return $user->children()->where('students.id', $transaction->student_id)->exists();
        }

        if ($user->role === UserRole::Student->value) {
            return (int) $user->student_id === (int) $transaction->student_id;
        }

        return false;
    }

    /**
     * Who may initiate a payment for the given invoice?
     *
     *  - Admin / Proprietor / Accountant : always (staff-side recording)
     *  - Parent : only if linked to the invoice's student
     *  - Student: only for their own invoice
     *  - Teacher: never
     */
    public function initiate(User $user, FeeInvoice $invoice): bool
    {
        if ((int) $user->tenant_id !== (int) $invoice->tenant_id) {
            return false;
        }

        if (! in_array($invoice->status, [FeeInvoice::STATUS_ISSUED, FeeInvoice::STATUS_PARTIALLY_PAID], true)) {
            return false;
        }

        if ($user->isFinanceRole()) {
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

    private function isParentOrStudent(User $user): bool
    {
        return in_array($user->role, [UserRole::Parent->value, UserRole::Student->value], true);
    }

    private function sameTenant(User $user, Model $model): bool
    {
        return (int) $user->tenant_id === (int) $model->getAttribute('tenant_id');
    }
}
