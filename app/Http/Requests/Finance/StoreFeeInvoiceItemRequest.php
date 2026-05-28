<?php

namespace App\Http\Requests\Finance;

use App\Models\FeeInvoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeeInvoiceItemRequest extends FormRequest
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
            'fee_id' => ['nullable', 'integer', Rule::exists('fees', 'id')->where('tenant_id', $tid)],
            'description' => ['required', 'string', 'max:191'],
            'category' => ['nullable', 'string', 'max:64'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'unit_amount' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
        ];
    }
}
