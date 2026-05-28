<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasFinanceAccess($user)
            || in_array($user->role, [UserRole::Parent->value, UserRole::Student->value], true);
    }

    public function view(User $user, Payment $payment): bool
    {
        if (! $this->sameTenant($user, $payment)) {
            return false;
        }

        if ($this->hasFinanceAccess($user)) {
            return true;
        }

        if ($user->role === UserRole::Parent->value) {
            return $user->children()->where('students.id', $payment->student_id)->exists();
        }

        if ($user->role === UserRole::Student->value) {
            return (int) $user->student_id === (int) $payment->student_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->hasFinanceAccess($user);
    }

    public function update(User $user, Payment $payment): bool
    {
        return $this->sameTenant($user, $payment) && $this->hasFinanceAccess($user);
    }

    public function delete(User $user, Payment $payment): bool
    {
        // We don't hard-delete payments. Soft-delete only after we've already
        // reversed them; otherwise force a reversal first.
        if (! $this->sameTenant($user, $payment) || ! $this->hasFinanceAccess($user)) {
            return false;
        }

        return $payment->status === Payment::STATUS_REVERSED;
    }

    public function reverse(User $user, Payment $payment): bool
    {
        if (! $this->sameTenant($user, $payment) || ! $this->hasFinanceAccess($user)) {
            return false;
        }

        return in_array($payment->status, [Payment::STATUS_SUCCESSFUL, Payment::STATUS_PENDING], true);
    }

    private function sameTenant(User $user, Model $model): bool
    {
        return (int) $user->tenant_id === (int) $model->getAttribute('tenant_id');
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
