<?php

namespace App\Http\Requests\Fee;

use App\Models\Fee;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Fee::class);
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        $tenantId = (int) $this->user()->tenant_id;

        return [
            'class_id' => ['required', 'integer', Rule::exists('classes', 'id')->where('tenant_id', $tenantId)],
            'term_id' => ['required', 'integer', Rule::exists('terms', 'id')->where('tenant_id', $tenantId)],
            'fee_type' => ['required', 'string', 'max:128'],
            'amount' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
        ];
    }
}
