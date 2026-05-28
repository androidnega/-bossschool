<?php

namespace App\Http\Requests\ReportCardMeta;

use App\Models\ReportCardMeta;
use App\Models\SchoolClass;
use App\Policies\ReportCardMetaPolicy;
use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateReportCardMetaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if ($user === null || ! $user->can('viewAny', ReportCardMeta::class)) {
            return false;
        }

        // Teachers can only bulk-update classes they are assigned to.
        if ($user->role === \App\Enums\UserRole::Teacher->value) {
            $classId = (int) $this->input('class_id');
            $schoolClass = SchoolClass::query()->whereKey($classId)->first();
            if (! $schoolClass) {
                return false;
            }

            return $user->assignedClasses()->where('classes.id', $schoolClass->id)->exists();
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'term_id' => ['required', 'integer', 'exists:terms,id'],

            'days_school_opened' => ['nullable', 'integer', 'min:0', 'max:200'],
            'class_teacher_remark' => ['nullable', 'string', 'max:500'],
            'head_teacher_remark' => ['nullable', 'string', 'max:500'],
            'next_term_fee' => ['nullable', 'numeric', 'min:0'],
            'vacation_date' => ['nullable', 'date'],
            'reopening_date' => ['nullable', 'date'],

            'prefill_attendance' => ['nullable', 'boolean'],
        ];
    }
}
