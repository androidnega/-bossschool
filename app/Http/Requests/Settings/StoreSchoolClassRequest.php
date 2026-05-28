<?php

namespace App\Http\Requests\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSchoolClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.manage');
    }

    /**
     * Treat blank/missing section as the same row for uniqueness purposes so
     * "Basic 1" + (no section) cannot be added twice.
     */
    protected function prepareForValidation(): void
    {
        $section = $this->input('section');
        if (is_string($section) && trim($section) === '') {
            $this->merge(['section' => null]);
        }
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        $tenantId = (int) $this->user()->tenant_id;
        $section = $this->input('section');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('classes', 'name')
                    ->where(fn ($q) => $q->where('tenant_id', $tenantId)->where(function ($q2) use ($section): void {
                        if ($section === null) {
                            $q2->whereNull('section');
                        } else {
                            $q2->where('section', $section);
                        }
                    })),
            ],
            'section' => ['nullable', 'string', 'max:64'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => __('A class with this name and section already exists.'),
        ];
    }
}
