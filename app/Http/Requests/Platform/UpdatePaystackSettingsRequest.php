<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaystackSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'paystack_public_key' => ['nullable', 'string', 'max:128'],
            // Secret is optional on every save (empty = keep existing).
            // When provided we accept a wide max so test/live keys both fit.
            'paystack_secret_key' => ['nullable', 'string', 'max:255'],
            'paystack_enabled_sms' => ['sometimes', 'boolean'],
            'paystack_enabled_subscription' => ['sometimes', 'boolean'],
            // Per-SMS price in pesewas, stored as a decimal string so the
            // 0.38 default keeps its fractional precision. Range 0.01..50.
            'sms_price_pesewas' => ['required', 'numeric', 'min:0.01', 'max:50'],
        ];
    }
}
