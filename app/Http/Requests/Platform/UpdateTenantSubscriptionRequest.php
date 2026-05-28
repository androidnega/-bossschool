<?php

namespace App\Http\Requests\Platform;

use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('billing_cycle') === '' || $this->input('billing_cycle') === null) {
            $this->merge(['billing_cycle' => null]);
        }
        if ($this->input('amount') === '' || $this->input('amount') === null) {
            $this->merge(['amount' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'string', Rule::in([
                Subscription::STATUS_ACTIVE,
                Subscription::STATUS_CANCELLED,
                Subscription::STATUS_EXPIRED,
                Subscription::STATUS_PAST_DUE,
            ])],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'billing_cycle' => ['nullable', 'string', Rule::in([Plan::BILLING_MONTHLY, Plan::BILLING_YEARLY])],
            'note' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
