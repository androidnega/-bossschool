<?php

namespace App\Http\Requests\ReportCardMeta;

use App\Models\Student;
use App\Policies\ReportCardMetaPolicy;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates a single per-student report-card meta save. The set of allowed
 * fields depends on role (Admin/Proprietor: full; Teacher: subset). Anything
 * not in the role's allow-list is dropped in {@see safe()}.
 */
class UpdateReportCardMetaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $student = $this->route('student');
        if (! $student instanceof Student) {
            return false;
        }

        return app(ReportCardMetaPolicy::class)->editForStudent($this->user(), $student);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $allowed = ReportCardMetaPolicy::editableFieldsFor($this->user());

        $rules = [
            'days_school_opened' => ['nullable', 'integer', 'min:0', 'max:400'],
            'days_present' => ['nullable', 'integer', 'min:0', 'max:400'],
            'days_absent' => ['nullable', 'integer', 'min:0', 'max:400'],
            'conduct' => ['nullable', 'string', 'max:64'],
            'attitude' => ['nullable', 'string', 'max:64'],
            'interest' => ['nullable', 'string', 'max:64'],
            'class_teacher_remark' => ['nullable', 'string', 'max:1000'],
            'head_teacher_remark' => ['nullable', 'string', 'max:1000'],
            'next_term_fee' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'vacation_date' => ['nullable', 'date'],
            'reopening_date' => ['nullable', 'date', 'after_or_equal:vacation_date'],
            'class_teacher_signature' => ['nullable', 'string', 'max:191'],
            'head_teacher_signature' => ['nullable', 'string', 'max:191'],
        ];

        return array_intersect_key($rules, array_flip($allowed));
    }

    /**
     * Coerce empty strings to null so nullable validation doesn't trip on them
     * (HTML forms emit '' instead of null for cleared optional fields).
     */
    protected function prepareForValidation(): void
    {
        $payload = $this->all();
        foreach ($payload as $k => $v) {
            if ($v === '') {
                $payload[$k] = null;
            }
        }
        $this->merge($payload);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $opened = $this->input('days_school_opened');
            $present = $this->input('days_present');
            $absent = $this->input('days_absent');

            if ($opened !== null && $present !== null && (int) $present > (int) $opened) {
                $validator->errors()->add('days_present', __('Days present cannot exceed days the school was open.'));
            }
            if ($opened !== null && $absent !== null && (int) $absent > (int) $opened) {
                $validator->errors()->add('days_absent', __('Days absent cannot exceed days the school was open.'));
            }
        });
    }
}
