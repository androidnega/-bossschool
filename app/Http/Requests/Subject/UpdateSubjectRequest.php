<?php

namespace App\Http\Requests\Subject;

use App\Models\Subject;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Subject $subject */
        $subject = $this->route('subject');

        return $this->user()->can('update', $subject);
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        $tenantId = (int) $this->user()->tenant_id;

        return [
            'class_id' => ['required', 'integer', Rule::exists('classes', 'id')->where('tenant_id', $tenantId)],
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
