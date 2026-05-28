<?php

namespace App\Http\Requests\Result;

use App\Enums\UserRole;
use App\Models\Result;
use App\Models\Student;
use App\Models\Subject;
use App\Services\AcademicContext;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Result $result */
        $result = $this->route('result');

        return $this->user()->can('update', $result);
    }

    protected function prepareForValidation(): void
    {
        /** @var Result $result */
        $result = $this->route('result');
        /** @var AcademicContext $ctx */
        $ctx = app(AcademicContext::class);
        $user = $this->user();

        $canOverride = $user && in_array($user->role, [UserRole::Admin->value, UserRole::Proprietor->value], true);

        if (! $canOverride || $this->input('academic_year_id') === null) {
            $this->merge(['academic_year_id' => $result->academic_year_id ?? $ctx->currentYear()?->id]);
        }
        if (! $canOverride || $this->input('term_id') === null) {
            $this->merge(['term_id' => $result->term_id ?? $ctx->currentTerm()?->id]);
        }
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        $tenantId = (int) $this->user()->tenant_id;
        $maxPerComponent = (int) config('schools.score.max_per_component', 100);

        return [
            'student_id' => ['required', 'integer', Rule::exists('students', 'id')->where('tenant_id', $tenantId)],
            'subject_id' => ['required', 'integer', Rule::exists('subjects', 'id')->where('tenant_id', $tenantId)],
            'academic_year_id' => ['required', Rule::exists('academic_years', 'id')->where('tenant_id', $tenantId)],
            'term_id' => ['required', Rule::exists('terms', 'id')->where('tenant_id', $tenantId)],
            'class_test' => ['nullable', 'numeric', 'min:0', 'max:'.$maxPerComponent],
            'midterm' => ['nullable', 'numeric', 'min:0', 'max:'.$maxPerComponent],
            'exam' => ['nullable', 'numeric', 'min:0', 'max:'.$maxPerComponent],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var Result $result */
            $result = $this->route('result');

            $student = Student::query()->find((int) $this->input('student_id'));
            $subject = Subject::query()->find((int) $this->input('subject_id'));

            if ($student === null || $subject === null) {
                return;
            }

            if ((int) $student->class_id !== (int) $subject->class_id) {
                $validator->errors()->add('subject_id', __('The selected subject does not belong to the student’s class.'));
            }

            $user = $this->user();
            if ($user && $user->role === UserRole::Teacher->value && ! $user->teachesSubjectId((int) $subject->id)) {
                $validator->errors()->add('subject_id', __('You are not assigned to this subject.'));
            }

            $duplicate = Result::query()
                ->where('tenant_id', (int) $this->user()->tenant_id)
                ->where('student_id', $student->id)
                ->where('subject_id', $subject->id)
                ->where('academic_year_id', (int) $this->input('academic_year_id'))
                ->where('term_id', (int) $this->input('term_id'))
                ->whereKeyNot($result->id)
                ->exists();

            if ($duplicate) {
                $validator->errors()->add('subject_id', __('A result for this student, subject, term and year already exists.'));
            }

            $maxTotal = (int) config('schools.score.max_total', 300);
            $ct = (float) $this->input('class_test', 0);
            $mid = (float) $this->input('midterm', 0);
            $ex = (float) $this->input('exam', 0);
            if (round($ct + $mid + $ex, 2) > $maxTotal) {
                $validator->errors()->add('exam', __('The combined total cannot exceed :max.', ['max' => $maxTotal]));
            }
        });
    }
}
