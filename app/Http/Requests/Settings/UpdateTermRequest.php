<?php

namespace App\Http\Requests\Settings;

use App\Models\Term;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTermRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.manage');
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        $tenantId = (int) $this->user()->tenant_id;
        /** @var Term $term */
        $term = $this->route('term');

        return [
            'name' => ['required', 'string', 'max:128'],
            'academic_year_id' => [
                'required',
                Rule::exists('academic_years', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId)),
            ],
            'term_order' => [
                'required',
                'integer',
                'min:1',
                'max:6',
                Rule::unique('terms', 'term_order')
                    ->where(fn ($q) => $q->where('tenant_id', $tenantId)->where('academic_year_id', (int) $this->input('academic_year_id')))
                    ->ignore($term->id),
            ],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after:starts_on'],
            'is_current' => ['nullable', 'boolean'],
            'status' => ['nullable', 'in:'.Term::STATUS_ACTIVE.','.Term::STATUS_ARCHIVED],
        ];
    }
}
