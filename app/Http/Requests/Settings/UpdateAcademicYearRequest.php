<?php

namespace App\Http\Requests\Settings;

use App\Models\AcademicYear;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var AcademicYear $year */
        $year = $this->route('academic_year');

        return $this->user()->can('update', $year);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $tenantId = (int) $this->user()->tenant_id;
        /** @var AcademicYear $year */
        $year = $this->route('academic_year');

        return [
            'name' => [
                'required',
                'string',
                'max:32',
                'regex:/^\d{4}\/\d{4}$/',
                Rule::unique('academic_years', 'name')
                    ->where('tenant_id', $tenantId)
                    ->whereNull('deleted_at')
                    ->ignore($year->id),
            ],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after:starts_on'],
            'is_current' => ['nullable', 'boolean'],
            'status' => ['nullable', 'in:'.AcademicYear::STATUS_ACTIVE.','.AcademicYear::STATUS_ARCHIVED],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => __('Use the format YYYY/YYYY, e.g. 2025/2026.'),
        ];
    }
}
