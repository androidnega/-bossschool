<?php

namespace App\Http\Requests\Discipline;

use App\Models\DisciplineIncident;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDisciplineIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('discipline_incident')) ?? false;
    }

    public function rules(): array
    {
        return [
            'action_taken' => ['nullable', 'string', 'max:1000'],
            'parent_notified' => ['nullable', 'boolean'],
            'severity' => ['nullable', Rule::in(DisciplineIncident::SEVERITIES)],
            'status' => ['nullable', Rule::in(DisciplineIncident::STATUSES)],
            'resolution_note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge(['parent_notified' => (bool) $this->input('parent_notified', false)]);
    }
}
