<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\EndOfTermRun;
use App\Models\Result;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ResultPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canViewAcademics($user);
    }

    public function view(User $user, Result $result): bool
    {
        if (! $this->sameTenant($user, $result)) {
            return false;
        }

        if (! $this->canViewAcademics($user)) {
            return false;
        }

        if ($user->role === UserRole::Teacher->value) {
            return $user->teachesSubjectId((int) $result->subject_id);
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $this->canManageAcademics($user);
    }

    public function update(User $user, Result $result): bool
    {
        if (! $this->sameTenant($user, $result)) {
            return false;
        }

        if (! $this->canManageAcademics($user)) {
            return false;
        }

        // Once a term has been closed via the end-of-term workflow, ordinary
        // teachers cannot edit results for that term. Admin / Proprietor can
        // still amend them (e.g. to fix a typo) and are also the only ones
        // allowed to reopen the term explicitly.
        if ($user->role === UserRole::Teacher->value) {
            if ($this->isTermClosed((int) $result->tenant_id, (int) $result->term_id)) {
                return false;
            }

            return $user->teachesSubjectId((int) $result->subject_id);
        }

        return true;
    }

    public function delete(User $user, Result $result): bool
    {
        if (! $this->sameTenant($user, $result)) {
            return false;
        }

        if (! $this->canManageAcademics($user)) {
            return false;
        }

        if ($user->role === UserRole::Teacher->value) {
            if ($this->isTermClosed((int) $result->tenant_id, (int) $result->term_id)) {
                return false;
            }

            return $user->teachesSubjectId((int) $result->subject_id);
        }

        return true;
    }

    private function isTermClosed(int $tenantId, int $termId): bool
    {
        return EndOfTermRun::query()
            ->where('tenant_id', $tenantId)
            ->where('term_id', $termId)
            ->where('status', EndOfTermRun::STATUS_CLOSED)
            ->exists();
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

    private function canManageAcademics(User $user): bool
    {
        return in_array($user->role, [
            UserRole::Admin->value,
            UserRole::Proprietor->value,
            UserRole::Teacher->value,
        ], true);
    }
}
