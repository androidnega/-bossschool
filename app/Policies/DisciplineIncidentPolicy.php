<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\DisciplineIncident;
use App\Models\Student;
use App\Models\User;
use App\Services\TenantSettings;

class DisciplineIncidentPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            UserRole::Admin->value,
            UserRole::Proprietor->value,
            UserRole::Teacher->value,
            UserRole::Parent->value,
        ], true);
    }

    public function view(User $user, DisciplineIncident $incident): bool
    {
        if ((int) $user->tenant_id !== (int) $incident->tenant_id) {
            return false;
        }

        if ($this->isAdminOrProprietor($user)) {
            return true;
        }

        if ($user->role === UserRole::Teacher->value) {
            return $user->teachesStudent($incident->student ?? new Student());
        }

        if ($user->role === UserRole::Parent->value) {
            // Tenant setting controls whether parents may see discipline info.
            $settings = app(TenantSettings::class);
            $allowed = (bool) $settings->get((int) $user->tenant_id, 'parent_can_view_discipline', false);
            if (! $allowed) {
                return false;
            }

            return $user->children()->where('students.id', $incident->student_id)->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [
            UserRole::Admin->value,
            UserRole::Proprietor->value,
            UserRole::Teacher->value,
        ], true);
    }

    public function createFor(User $user, Student $student): bool
    {
        if ((int) $user->tenant_id !== (int) $student->tenant_id) {
            return false;
        }

        if ($this->isAdminOrProprietor($user)) {
            return true;
        }

        if ($user->role === UserRole::Teacher->value) {
            return $user->teachesStudent($student);
        }

        return false;
    }

    public function update(User $user, DisciplineIncident $incident): bool
    {
        if ((int) $user->tenant_id !== (int) $incident->tenant_id) {
            return false;
        }

        if ($this->isAdminOrProprietor($user)) {
            return true;
        }

        if ($user->role === UserRole::Teacher->value) {
            // Teachers can only edit incidents they reported, and only while
            // the incident is still open.
            return (int) $incident->reported_by_user_id === (int) $user->id
                && $incident->status === DisciplineIncident::STATUS_OPEN;
        }

        return false;
    }

    public function resolve(User $user, DisciplineIncident $incident): bool
    {
        return $this->update($user, $incident) && $this->isAdminOrProprietor($user);
    }

    public function delete(User $user, DisciplineIncident $incident): bool
    {
        return (int) $user->tenant_id === (int) $incident->tenant_id
            && $this->isAdminOrProprietor($user);
    }

    private function isAdminOrProprietor(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin->value, UserRole::Proprietor->value], true);
    }
}
