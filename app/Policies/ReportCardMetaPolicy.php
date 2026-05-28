<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\ReportCardMeta;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Report-card meta access:
 *  - Admin / Proprietor : full read + write of every field
 *  - Teacher            : read everything for students in their assigned classes,
 *                         and write only the subset of fields that
 *                         {@see editableFieldsFor()} returns
 *  - Anyone else        : denied (parents/students consume the rendered card, they
 *                         don't author it; accountants have no academic role)
 */
class ReportCardMetaPolicy
{
    /**
     * Fields a Teacher is allowed to edit.
     *
     * Admin / Proprietor have no limit (handled in the caller). Anything not in
     * this list is silently dropped from a Teacher's POST body.
     *
     * @return array<int, string>
     */
    public static function editableFieldsFor(User $user): array
    {
        if ($user->role === UserRole::Teacher->value) {
            return [
                'days_school_opened',
                'days_present',
                'days_absent',
                'conduct',
                'attitude',
                'interest',
                'class_teacher_remark',
                'class_teacher_signature',
            ];
        }

        return [
            'days_school_opened', 'days_present', 'days_absent',
            'conduct', 'attitude', 'interest',
            'class_teacher_remark', 'head_teacher_remark',
            'next_term_fee', 'vacation_date', 'reopening_date',
            'class_teacher_signature', 'head_teacher_signature',
        ];
    }

    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            UserRole::Admin->value,
            UserRole::Proprietor->value,
            UserRole::Teacher->value,
        ], true);
    }

    public function view(User $user, ReportCardMeta $meta): bool
    {
        if (! $this->sameTenant($user, $meta)) {
            return false;
        }

        return $this->editForStudent($user, $meta->student) !== null;
    }

    public function update(User $user, ReportCardMeta $meta): bool
    {
        if (! $this->sameTenant($user, $meta)) {
            return false;
        }

        return $this->editForStudent($user, $meta->student);
    }

    /**
     * Centralised "can this user write meta for this student?" check, used by
     * both update() above and by the controller when working with a brand-new
     * (not-yet-persisted) ReportCardMeta row.
     */
    public function editForStudent(User $user, ?Student $student): bool
    {
        if ($student === null) {
            return false;
        }

        if ((int) $student->tenant_id !== (int) $user->tenant_id) {
            return false;
        }

        if (in_array($user->role, [UserRole::Admin->value, UserRole::Proprietor->value], true)) {
            return true;
        }

        if ($user->role === UserRole::Teacher->value) {
            return $user->teachesStudent($student);
        }

        return false;
    }

    private function sameTenant(User $user, Model $model): bool
    {
        return (int) $user->tenant_id === (int) $model->getAttribute('tenant_id');
    }
}
