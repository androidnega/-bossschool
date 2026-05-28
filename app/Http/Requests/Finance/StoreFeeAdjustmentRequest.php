<?php

namespace App\Http\Requests\Finance;

use App\Models\FeeAdjustment;
use App\Models\FeeInvoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeeAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', FeeAdjustment::class);
    }

    public function rules(): array
    {
        $tid = (int) $this->user()->tenant_id;

        return [
            'student_id' => ['required', 'integer', Rule::exists('students', 'id')->where('tenant_id', $tid)],
            'fee_invoice_id' => ['nullable', 'integer', Rule::exists('fee_invoices', 'id')->where('tenant_id', $tid)],
            'academic_year_id' => ['nullable', 'integer', Rule::exists('academic_years', 'id')->where('tenant_id', $tid)],
            'term_id' => ['nullable', 'integer', Rule::exists('terms', 'id')->where('tenant_id', $tid)],
            'type' => ['required', 'string', Rule::in(FeeAdjustment::TYPES)],
            'description' => ['nullable', 'string', 'max:191'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $validator): void {
            $invoiceId = $this->input('fee_invoice_id');
            if ($invoiceId === null || $invoiceId === '') {
                return;
            }

            $invoice = FeeInvoice::query()->find($invoiceId);
            if ($invoice && (int) $invoice->student_id !== (int) $this->input('student_id')) {
                $validator->errors()->add('fee_invoice_id', __('The selected invoice belongs to a different student.'));
            }
        });
    }
}
