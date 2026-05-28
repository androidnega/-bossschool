<?php

namespace App\Http\Requests\Settings;

use App\Models\AcademicYear;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', AcademicYear::class);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $tenantId = (int) $this->user()->tenant_id;

        return [
            // Ghanaian school years are commonly written as "2025/2026".
            'name' => [
                'required',
                'string',
                'max:32',
                'regex:/^\d{4}\/\d{4}$/',
                Rule::unique('academic_years', 'name')->where('tenant_id', $tenantId)->whereNull('deleted_at'),
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
