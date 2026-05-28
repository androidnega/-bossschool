<?php

namespace App\Http\Requests\Discipline;

use App\Models\DisciplineIncident;
use App\Models\Student;
use App\Policies\DisciplineIncidentPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDisciplineIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if ($user === null) {
            return false;
        }
        $student = Student::query()->whereKey((int) $this->input('student_id'))->first();
        if (! $student) {
            return false;
        }

        return app(DisciplineIncidentPolicy::class)->createFor($user, $student);
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'term_id' => ['nullable', 'integer', 'exists:terms,id'],
            'incident_date' => ['required', 'date'],
            'category' => ['required', 'string', 'max:64'],
            'description' => ['required', 'string', 'max:2000'],
            'action_taken' => ['nullable', 'string', 'max:1000'],
            'parent_notified' => ['nullable', 'boolean'],
            'severity' => ['required', Rule::in(DisciplineIncident::SEVERITIES)],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge(['parent_notified' => (bool) $this->input('parent_notified', false)]);
    }
}
