<?php

namespace App\Http\Requests\Finance;

use App\Models\FeeInvoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFeeInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $invoice = $this->route('feeInvoice');

        return $invoice instanceof FeeInvoice && $this->user()->can('update', $invoice);
    }

    public function rules(): array
    {
        $tid = (int) $this->user()->tenant_id;

        return [
            'academic_year_id' => ['nullable', 'integer', Rule::exists('academic_years', 'id')->where('tenant_id', $tid)],
            'term_id' => ['nullable', 'integer', Rule::exists('terms', 'id')->where('tenant_id', $tid)],
            'class_id' => ['nullable', 'integer', Rule::exists('classes', 'id')->where('tenant_id', $tid)],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
