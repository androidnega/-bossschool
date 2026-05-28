<?php

namespace App\Http\Requests\Student;

use App\Models\Student;
use App\Rules\GhanaPhone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Student::class);
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        $tenantId = (int) $this->user()->tenant_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', 'string', Rule::in(['male', 'female', 'other'])],
            'dob' => ['nullable', 'date'],
            'class_id' => [
                'required',
                'integer',
                Rule::exists('classes', 'id')->where('tenant_id', $tenantId),
            ],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'parent_phone' => ['nullable', 'string', 'max:64', new GhanaPhone],
            'address' => ['nullable', 'string'],
            'admission_date' => ['nullable', 'date'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive', 'graduated', 'transferred'])],
        ];
    }
}
