<?php

namespace App\Http\Requests\Staff;

use App\Rules\GhanaPhone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('staff.manage');
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('salary') && $this->input('salary') === '') {
            $this->merge(['salary' => null]);
        }
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:128'],
            'subject' => ['nullable', 'string', 'max:128'],
            'phone' => ['nullable', 'string', 'max:64', new GhanaPhone],
            'salary' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
        ];
    }
}
