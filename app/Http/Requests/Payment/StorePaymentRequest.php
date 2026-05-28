<?php

namespace App\Http\Requests\Payment;

use App\Models\FeeInvoice;
use App\Models\Payment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Payment::class);
    }

    protected function prepareForValidation(): void
    {
        // Legacy alias: `method` was the Phase 1 field for payment channel.
        // Tests, dashboards and seeders still use that name, so keep accepting
        // it transparently.
        $payload = [];
        if (! $this->filled('payment_channel') && $this->filled('method')) {
            $payload['payment_channel'] = $this->input('method');
        }
        if (! $this->filled('payment_reference') && $this->filled('reference')) {
            $payload['payment_reference'] = $this->input('reference');
        }
        if (! empty($payload)) {
            $this->merge($payload);
        }
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        $tenantId = (int) $this->user()->tenant_id;

        return [
            'student_id' => ['required', 'integer', Rule::exists('students', 'id')->where('tenant_id', $tenantId)],
            'fee_invoice_id' => [
                'nullable',
                'integer',
                Rule::exists('fee_invoices', 'id')->where('tenant_id', $tenantId),
            ],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'payment_channel' => ['required', 'string', Rule::in(Payment::CHANNELS)],
            'provider' => ['nullable', 'string', Rule::in(Payment::PROVIDERS)],
            'provider_reference' => ['nullable', 'string', 'max:191'],
            'reference' => ['nullable', 'string', 'max:255'],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date'],
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
            if (! $invoice) {
                return; // Rule::exists already failed
            }

            if ((int) $invoice->student_id !== (int) $this->input('student_id')) {
                $validator->errors()->add('fee_invoice_id', __('The selected invoice does not belong to this student.'));
            }

            if (! in_array($invoice->status, [FeeInvoice::STATUS_ISSUED, FeeInvoice::STATUS_PARTIALLY_PAID], true)) {
                $validator->errors()->add('fee_invoice_id', __('Only issued or partially-paid invoices can receive payments.'));
            }
        });
    }
}
